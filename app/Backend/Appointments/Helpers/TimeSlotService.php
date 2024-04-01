<?php

namespace BookneticApp\Backend\Appointments\Helpers;

use BookneticApp\Backend\Appointments\Helpers\AppointmentRequests as Request;
use BookneticApp\Models\Service;
use BookneticApp\Providers\Helpers\Date;
use BookneticApp\Providers\Helpers\Helper;

class TimeSlotService extends ServiceDefaults implements \JsonSerializable
{
	private string $date;
	private string $time;

    private ?bool $bookable = null;

	public function __construct( ?string $date, ?string $time )
	{
		$this->date = $date ?? '';
		$this->time = $time ?? '';
	}

	public function getDate( bool $formatDate = false ): string
    {
		return $formatDate ? Date::datee( $this->date ) : $this->date;
	}

	public function getTime( bool $formatTime = false ): string
    {
		return $formatTime ? Date::time( $this->time ) : $this->time;
	}

    public function getTimestamp(): int
    {
        return Date::epoch( $this->date . ' ' . $this->time );
    }

    public function setBookable( ?bool $bool ) :self
    {
        $this->bookable = $bool;
        return $this;
    }

    /**
     * @throws \Exception
     */
    public function isBookable() :bool
	{
		if( ! is_null( $this->bookable ) )
            return $this->bookable;

        $this->initBookable(); //initialize value for isBookable property

        return $this->bookable;
	}

    /**
     * @throws \Exception
     */
    public function getInfo(): array
    {
        $result = [ 'info' => [] ];

		$allTimeslotsForToday = new CalendarService( Date::dateSQL( $this->getDate(), '-1 days' ), Date::dateSQL( $this->getDate(), '+1 days' ) );
		$allTimeslotsForToday->setDefaultsFrom( $this );

		$slots = $allTimeslotsForToday->getCalendar( 'timestamp' );

        // todo True will be an option in the future
        $result[ 'combinedSlots' ] = true ? $this->combinedSlots( $slots[ 'dates' ] ) : [];

        if (array_key_exists($this->getTimestamp(), $slots['dates']))
        {
            $result['info'] = $slots['dates'][$this->getTimestamp()];
        }

		return $result;
	}

    private function combinedSlots( array $items ) :array
    {
        $newArr           = [];
        $lastEndTimestamp = null;
        $lastKey          = null;

        foreach ( $items as $key => $item )
        {
            $currentItemEndTimestamp = $key + $item[ 'duration' ] * 60;

            if ( $lastEndTimestamp == $key )
            {
                $newArr[ $lastKey ][ 'end' ] = $currentItemEndTimestamp;
            }
            else
            {
                $lastKey = $key;
                $newArr[ $lastKey ] = [ 'start' => $key, 'end' => $currentItemEndTimestamp ];
            }

            $lastEndTimestamp = $currentItemEndTimestamp;
        }

        return $newArr;
    }

	public function toArr(): array
    {
		return [
			'date'        => $this->getDate(),
			'time'        => $this->getTime(),
			'date_format' => $this->getDate( true ),
			'time_format' => $this->getTime( true ),
			'is_bookable' => $this->isBookable()
		];
	}

	public function jsonSerialize() :array
	{
		return $this->toArr();
	}

    /**
     * @throws \Exception
     */
    private function initBookable() :void
    {
        $dayDif = (int)( (Date::epoch( $this->date ) - Date::epoch()) / 60 / 60 / 24 );
        $decodedInfo = Helper::decodeInfo( Helper::_post( 'info', '', 'string' ) );

        if ( $decodedInfo && isset( $decodedInfo[ 'limited_booking_days' ] ) )
        {
            $limitedBookingDays = $decodedInfo[ 'limited_booking_days' ];
        }
        else
        {
            if ( Request::self() )
            {
                $currentRequest = Request::self()->currentRequest();
            }
            else
            {
                $currentRequest = Request::load()->currentRequest();
            }

            $availableDaysForBookingForService = Service::getData( $currentRequest->serviceId, 'available_days_for_booking' );

            if ( $availableDaysForBookingForService !== 0 && empty( $availableDaysForBookingForService ) )
            {
                $limitedBookingDays = Helper::getOption( 'available_days_for_booking', '365' );
            }
            else
            {
                $limitedBookingDays = $availableDaysForBookingForService;
            }
        }

        if( ! $this->calledFromBackEnd && $dayDif > $limitedBookingDays )
        {
            $this->bookable = false;
            return;
        }

        $result               = $this->getInfo();
        $selectedTimeSlotInfo = $result[ 'info' ];

        if( empty( $selectedTimeSlotInfo ) )
        {
            $appointmentStart = $this->getTimestamp();

            $appointmentEnd   = $appointmentStart + $this->getServiceInf()->duration * 60  + ExtrasService::calcExtrasDuration( $this->serviceExtras );

            $this->bookable = false;

            foreach ( $result[ 'combinedSlots' ] as $combinedSlot )
            {
                if ( $appointmentStart >= $combinedSlot[ 'start' ] && $appointmentEnd <= $combinedSlot[ 'end' ] )
                {
                    $this->bookable = true;
                    break;
                }
            }

            return;
        }

        if( ( $selectedTimeSlotInfo[ 'weight' ] + $this->totalCustomerCount ) > $selectedTimeSlotInfo[ 'max_capacity' ] )
        {
            $this->bookable = false;
            return;
        }

        $this->bookable = true;
    }

}