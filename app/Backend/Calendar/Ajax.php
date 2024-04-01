<?php

namespace BookneticApp\Backend\Calendar;

use BookneticAddon\Googlecalendar\Integration\GoogleCalendarService;
use BookneticApp\Backend\Appointments\Helpers\AppointmentService;
use BookneticApp\Models\Appointment;
use BookneticApp\Models\Staff;
use BookneticApp\Models\Timesheet;
use BookneticApp\Providers\Core\Capabilities;
use BookneticApp\Providers\Helpers\Date;
use BookneticApp\Providers\DB\DB;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Core\Permission;

class Ajax extends \BookneticApp\Providers\Core\Controller
{

	public function get_calendar()
	{
		Capabilities::must( 'calendar' );

		$startTime		= Helper::_post('start', '', 'string');
		$endTime		= Helper::_post('end', '', 'string');

		$startTime		= Date::epoch( $startTime );
		$endTime		= Date::epoch( $endTime );

		$stafFilter	    = Helper::_post( 'staff', [], 'array' );
		$locationFilter	= Helper::_post( 'locations', [], 'array' );
		$servicesFilter	= Helper::_post( 'services', [], 'array' );
		$statusesFilter = Helper::_post( 'statuses', [], 'array' );
		$paymentFilter  = Helper::_post( 'payments', [], 'array' );

        $staffFilterSanitized = [];
		foreach ( $stafFilter AS $staffId )
		{
			if( is_numeric( $staffId ) && $staffId > 0 )
			{
				$staffFilterSanitized[] = (int)$staffId;
			}
		}

        // De Morgan's law
        // not ( a OR b ) = not(a) AND not(b)
        // not ( starts_at > $endTime OR ends_at < $startTime )
        // starts_at <= $endTime AND ends_at >= $startTime
        $appointments = Appointment::where('starts_at', '<=', $endTime)
            ->where('ends_at', '>=', $startTime);

        if ( ! empty( $staffFilterSanitized ) )
        {
            $appointments = $appointments->where('staff_id', 'in', $staffFilterSanitized);
        }

        if ( ! empty( $locationFilter ) )
        {
            $appointments = $appointments->where('location_id', $locationFilter);
        }

        if ( ! empty( $servicesFilter ) )
        {
            $appointments = $appointments->where('service_id', $servicesFilter);
        }

		if( ! empty( $statusesFilter ) )
		{
			$appointments = $appointments->where( 'status', $statusesFilter );
		}
		if( ! empty( $paymentFilter ) )
		{
			$appointments = $appointments->where( 'payment_status', $paymentFilter );
		}

        $appointments = $appointments->leftJoin('staff', ['name', 'profile_image'])
            ->leftJoin('location', ['name'])
            ->leftJoin('service', ['name', 'color'])
            ->leftJoin('customer', ['first_name', 'last_name']);
        $appointments = $appointments->fetchAll();

        $events = [];
        foreach ($appointments as $appointment)
        {
            $events[] = [
                'appointment_id'		=>	$appointment['id'],
                'title'					=>	htmlspecialchars( $appointment['service_name'] ),
                'event_title'			=>	'',
                'color'					=>	empty($appointment['service_color']) ? '#ff7675' : $appointment['service_color'],
                'text_color'			=>	static::getContrastColor( empty($appointment['service_color']) ? '#ff7675' : $appointment['service_color'] ),
                'location_name'			=>	htmlspecialchars( $appointment['location_name'] ),
                'service_name'			=>	htmlspecialchars( $appointment['service_name'] ),
                'staff_name'			=>	htmlspecialchars( $appointment['staff_name'] ),
                'staff_id'			    =>	$appointment['staff_id'] ,
                'resourceId'			=>	$appointment['staff_id'] ,
                'staff_profile_image'	=>	Helper::profileImage( $appointment['staff_profile_image'], 'Staff' ),
                'start_time'			=>	Date::time( $appointment['starts_at'] ),
                'end_time'				=>	Date::time( $appointment['ends_at'] ),
                'start'					=>	Date::format( 'Y-m-d\TH:i:s', $appointment['starts_at']),
                'end'                   =>  Date::format( 'Y-m-d\TH:i:s', $appointment['ends_at']),
                'duration'              =>  Date::epoch( $appointment['ends_at'] ) - Date::epoch( $appointment['starts_at'] ),
                'customer'				=>	$appointment['customer_first_name'] . ' ' . $appointment['customer_last_name'],
                'customers_count'		=>	1,
                'status'				=>	Helper::appointmentStatus( $appointment['status'] )
            ];
        }

		$events = apply_filters('bkntc_calendar_events', $events, $startTime, $endTime , $staffFilterSanitized);
        $businessHours  = Timesheet::where('service_id', 'is', null)->where('staff_id', 'is', null)->fetch();

		return $this->response( true, [
			'data'	=>	$events,
            'businessHours' => $businessHours
		] );
	}

    public function reschedule_appointment()
    {
        $dateTimeISO = Helper::_post( 'new_date_time', '', 'string' );
        $appointmentID = Helper::_post('appointment_id', 0, 'int' );
        $triggerWorkflows = Helper::_post( 'trigger_workflows', 0, 'int' );
        $staffId = Helper::_post( 'staff_id', false, 'int' );

        if ( empty( $appointmentID ) )
        {
            return $this->response(false);
        }

        if ( ! Date::isValid( $dateTimeISO ) )
        {
            return $this->response( false, bkntc__( 'Incorrect Date and Time' ) );
        }

        AppointmentService::reschedule( $appointmentID, $dateTimeISO, $dateTimeISO, boolval( $triggerWorkflows ), false, $staffId );

        return $this->response( true );
    }

	private static function getContrastColor( $hexcolor )
	{
		Capabilities::must( 'calendar' );

		$r = hexdec(substr($hexcolor, 1, 2));
		$g = hexdec(substr($hexcolor, 3, 2));
		$b = hexdec(substr($hexcolor, 5, 2));
		$yiq = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;

		return ($yiq >= 185) ? '#292D32' : '#FFF';
	}

}
