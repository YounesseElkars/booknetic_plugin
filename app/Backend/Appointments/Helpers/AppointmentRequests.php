<?php

namespace BookneticApp\Backend\Appointments\Helpers;

use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Helpers\Math;

class AppointmentRequests
{

    public string $paymentMethod;
    public string $paymentId;

    /**
     * @var AppointmentRequestData[]
     */
    public array $appointments = [];

    private int $current = 0;

    private string $previousStep = '';

    private string $currentStep = '';

    private array $errors = [];

    public bool $calledFromBackend;

    private static int $appointmentCount;

    private static ?self $instance = null;

    public array $queryParams = [];

    public static function load( $calledFromBackend = false ) :self
    {
        $instance = self::$instance = new self();

        $instance->calledFromBackend = $calledFromBackend;

        $instance->paymentMethod = Helper::_post('payment_method', '', 'string' );
        $instance->current		 = Helper::_post('current', 0, 'int' );
        $instance->previousStep  = Helper::_post('previous_step', '', 'string' );
        $instance->currentStep   = Helper::_post('current_step', '', 'string' );
        $instance->queryParams   = Helper::_post( 'query_params', [], 'json' );

        if( $calledFromBackend )
        {
            $instance->paymentMethod = 'local';
        }

        $sampleJsonData = Helper::_post('cart', [], 'json' );

        if ( empty( $sampleJsonData ) || ! is_array( $sampleJsonData ) )
        {
            $sampleJsonData = [ [] ];
        }

        self::$appointmentCount = count( $sampleJsonData );

        CalendarService::setIncludeCart( true );

        foreach ( $sampleJsonData as $datum )
        {
            $datum[ 'payment_method' ] = $instance->paymentMethod;

            $instance->appointments[] = new AppointmentRequestData( $instance, $datum );
        }

        CalendarService::setIncludeCart( false );

        foreach ( $instance->appointments as $appointment )
        {
            do_action( 'bkntc_appointment_request_data_load', $appointment );
        }

        do_action('bkntc_appointment_requests_load' );

        return $instance;
    }

    public static function self(): ?self
    {
        return self::$instance;
    }

    public static function appointmentCount(): int
    {
        return self::$appointmentCount;
    }

    /**
     * @return AppointmentRequestData[]
     */
    public static function appointments(): array
    {
        return self::$instance->appointments;
    }

    public function validate(): bool
    {
        $this->errors = [];

        try
        {
            do_action('bkntc_appointment_requests_validate', $this );
        }
        catch (\Exception $e)
        {
            $this->errors[] = [ 'message' => $e->getMessage() ];
        }

        foreach ($this->appointments as $key=>$appointment)
        {
            try
            {
                $appointment->validate();
            }
            catch (\Exception $e)
            {
                $this->errors[] = [ 'message' => $e->getMessage(), 'cart_item' => $key ];
            }
        }

        try
        {
            do_action('bkntc_after_appointment_requests_validate', $this );
        }
        catch (\Exception $e)
        {
            $this->errors[] = [ 'message' => $e->getMessage() ];
        }

        return empty( $this->errors );
    }

    public function getErrors(): array
    {
        return $this->errors;
    }


    /**
     * @return false| string
     */
    public function getFirstError()
    {
        $first = reset($this->errors);
        return empty($first) ? false : reset($first);
    }

    public function currentRequestKey(): int
    {
        if ( array_key_exists( $this->current, $this->appointments ) )
            return $this->current;

        return count( $this->appointments ) - 1;
    }

    /**
     * @return false|AppointmentRequestData
     */
    public function currentRequest()
    {
        return array_key_exists($this->current, $this->appointments) ? $this->appointments[$this->current] : end($this->appointments);
    }

    /**
     * @return float
     */
    public function getPayableToday (): float
    {
        $payableToday = 0;

        foreach ( $this->appointments as $appointmentObj )
        {
            $payableToday += $appointmentObj->getPayableToday( true );
        }

        return Math::floor( $payableToday );
    }

    /**
     * @return float
     */
    public function getSubTotal( $sumForAllRecurringAppointments = false ): float
    {
        $subTotal = 0;

        foreach ( $this->appointments as $appointment )
        {
            $subTotal += $appointment->getSubTotal( $sumForAllRecurringAppointments );
        }

        return Math::floor( $subTotal );
    }

    public function getDepositPrice( $sumForAllRecurringAppointments = false )
    {
        $depositPrice = 0;

        foreach ( $this->appointments as $appointment )
        {
            $depositPrice += $appointment->getDepositPrice( $sumForAllRecurringAppointments );
        }

        return Math::floor( $depositPrice );
    }

    public function getPrices( $sumForAllRecurringAppointments = false ): array
    {
        if ( count($this->appointments) == 1)
        {
            return $this->appointments[0]->getPrices();
        }

        $pricesTop = [];
        $pricesMerged = [];

        foreach ($this->appointments as $key => $appointment)
        {
            $topPrice = new AppointmentPriceObject('cart-item-' . $key);
            $topPrice->setLabel($appointment->serviceInf->name);
            $newSumForAllRecurringAppointments = $appointment->serviceInf->recurring_payment_type == 'full' && $sumForAllRecurringAppointments;
            foreach ($appointment->getPrices($newSumForAllRecurringAppointments) as $price)
            {
                if (!$price->isMergeable())
                {
                    $topPrice->setPrice( $topPrice->getPrice($newSumForAllRecurringAppointments) + $price->getPrice($newSumForAllRecurringAppointments) );
                }
                else
                {
                    if (array_key_exists($price->getId(), $pricesMerged))
                    {
                        $pricesMerged[$price->getId()]->setPrice( $pricesMerged[$price->getId()]->getPrice() + $price->getPrice($newSumForAllRecurringAppointments) );
                    }
                    else
                    {
                        $newPrice = new AppointmentPriceObject($price->getId());
                        $newPrice->setLabel($price->getLabel());
                        $newPrice->setHidden( $price->isHidden() );
                        $newPrice->setPrice($price->getPrice($newSumForAllRecurringAppointments));
                        $newPrice->setColor( $price->getColor() );
                        $pricesMerged[$price->getId()] = $newPrice;
                    }
                }
            }
            $pricesTop[] = $topPrice;
        }

        return array_merge($pricesTop, array_values($pricesMerged));
    }

    public function getPricesHTML( $sumForAllRecurringAppointments = false ): string
    {
        $pricesHTML = '';

        /** @var $price \BookneticApp\Backend\Appointments\Helpers\AppointmentPriceObject */
        foreach ( $this->getPrices($sumForAllRecurringAppointments) AS $price )
        {
            $priceView = $price->getPriceView( $sumForAllRecurringAppointments );
            $priceValue = $price->getPrice();

            $pricesHTML .=
                '<div class="booknetic_confirm_details ' . ($price->isHidden() ? ' booknetic_hidden' : '') . '" data-price-id="' . htmlspecialchars($price->getId()) . '">
                    <div ' . ($price->getColor() ? 'style="color:' . $price->getColor() . ' !important"' : '') . ' class="booknetic_confirm_details_title">' . $price->getLabel() . '</div>
                    <div ' . ($price->getColor() ? 'style="color:' . $price->getColor() . ' !important"' : '') . ' class="booknetic_confirm_details_price">' . ( (float)$priceValue !== 0.0 && $price->getNegativeOrPositive() == -1 ? '-' : '' ) . $priceView . '</div>
                </div>';
        }

        return $pricesHTML;
    }

    /**
     * @param string $paymentId
     */
    public function setPaymentId( string $paymentId ): void
    {
        $this->paymentId = $paymentId;
    }

    /**
     * @param string $paymentMethod
     */
    public function setPaymentMethod( string $paymentMethod ): void
    {
        $this->paymentMethod = $paymentMethod;
    }

    /**
     * @return string
     */
    public function getPaymentId(): string
    {
        return $this->paymentId;
    }

    /**
     * @return string
     */
    public function getPaymentMethod(): string
    {
        return $this->paymentMethod;
    }

    public function getCurrentStep(): string
    {
        return $this->currentStep;
    }

    public function getPreviousStep(): string
    {
        return $this->previousStep;
    }

    public function isFromBackend(): bool
	{
		return $this->calledFromBackend;
	}
}