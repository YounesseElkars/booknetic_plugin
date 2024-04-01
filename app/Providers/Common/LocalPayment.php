<?php

namespace BookneticApp\Providers\Common;

use BookneticApp\Backend\Appointments\Helpers\AppointmentRequestData;
use BookneticApp\Backend\Appointments\Helpers\AppointmentRequests;
use BookneticApp\Providers\Core\Backend;
use BookneticApp\Providers\Helpers\Helper;

class LocalPayment extends PaymentGatewayService
{

    protected $slug = 'local';


    public function __construct ()
    {
        $this->setDefaultTitle(bkntc__('Local'));
        $this->setDefaultIcon( Helper::icon( 'local.svg', 'front-end' ) );

        $this->init();

        add_action( 'bkntc_appointment_request_data_load', [ self::class, 'appointmentPayableToday' ] );
    }

    public function when ( $status, $appointmentRequests = null )
    {
        if ( !$status )
        {
            if ( Helper::getOption( 'hide_confirm_details_step', 'off' ) == 'on' )
            {
                return true;
            }

            if ( !empty( $appointmentRequests ) && $appointmentRequests->getPayableToday() <= 0 )
            {
                return true;
            }
        }

        return $status;
    }

    /**
     * @param AppointmentRequests $appointmentRequests
     * @return object
     */
    public function doPayment ( $appointmentRequests )
    {
        $response = (object) [
            'status' => true,
            'data' => []
        ];

	    if ( $appointmentRequests->getSubTotal( true ) === 0.0 )
        {
            self::confirmPayment( $appointmentRequests->paymentId );
            return $response;
        }

        foreach ( $appointmentRequests->appointments as $appointment )
        {
            foreach ( $appointment->createdAppointments as $createdAppointmentId )
            {
                do_action( 'bkntc_appointment_before_mutation', null );
                do_action( 'bkntc_appointment_after_mutation', $createdAppointmentId );
            }

            do_action('bkntc_payment_confirmed', $appointment->getFirstAppointmentId());

            PaymentGatewayService::triggerCustomerCreated( $appointment->customerId );
        }

        return $response;
    }

    public static function appointmentPayableToday ( AppointmentRequestData $appointmentObj )
    {
        if ( $appointmentObj->paymentMethod == 'local' )
        {
            $appointmentObj->setPayableToday( 0 );
        }
    }

}