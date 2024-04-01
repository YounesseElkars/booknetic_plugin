<?php

namespace BookneticApp\Backend\Appointments\Helpers;

use BookneticApp\Models\Appointment;
use BookneticApp\Models\AppointmentExtra;
use BookneticApp\Providers\Helpers\Date;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Core\Permission;

class AppointmentChangeStatus {

    public static function validateToken($token)
    {
        $verifiedData = self::checkProvidedToken($token);

        if (!is_array($verifiedData)) return $verifiedData;

        if ( Helper::isSaaSVersion() )
            self::setTenantId( $verifiedData['payload'] );

        $appointment = Appointment::get( $verifiedData['id'] );
        if ( empty($appointment) ) return false;


        $secret = $appointment->payment_id . $verifiedData['payload']['expire'];

        if ( !Helper::validateToken($token, $secret) )
            return false;

        if ( !self::canChangeAppointmentStatus( $verifiedData['payload'], $appointment->starts_at ) )
            return bkntc__('Your token has been expired.');
	    if ( hash_equals($verifiedData['payload']['changeTo'], $appointment->status )  )
		    return bkntc__('Your appointment status is already changed.');
	    if ( ! in_array($verifiedData['payload']['changeTo'], Helper::getBusyAppointmentStatuses()) )
		    return true;
        if ( !self::isBookable( $appointment ) )
            return bkntc__('Timeslot for your appointment is occupied');
        return true;
    }

    private static function checkProvidedToken($token)
    {
        $verifiedData = [];

        if ( empty($token)) return false;

        $tokenParts = explode('.', $token);

        if (count($tokenParts) !== 3) return false;

        $header = json_decode( base64_decode( $tokenParts[0] ), true );
        $payload = json_decode( base64_decode( $tokenParts[1] ), true );


        if ( is_array ( $header ) &&
            is_array( $payload ) &&
            array_key_exists ( 'id', $header ) && is_numeric ( $header['id']) &&
            array_key_exists ( 'currentStatus', $payload ) &&
            array_key_exists ( 'changeTo', $payload ) &&
            array_key_exists ( 'expire', $payload ) )
        {
            $verifiedData['id'] = $header['id'];
            $verifiedData['payload'] = $payload;
        }
        else
        {
            return false;
        }

        return $verifiedData;
    }

    private static function setTenantId( $payload )
    {
        if ( array_key_exists( 'tenant_id', $payload ) && is_numeric( $payload['tenant_id'] ) )
            Permission::setTenantId( $payload['tenant_id'] );

    }

    private static function canChangeAppointmentStatus ( $payload, $appointment_starts )
    {
        $expireType = Helper::getOption( 'restriction_type_to_change_status', 'static' );
        $expireTime = Helper::getOption('time_restriction_to_change_status', 0);

        if ( $expireType === 'static' )
        {
            return $expireTime == 0 || Date::epoch( 'now' ) < Date::epoch( $payload[ 'expire' ] );
        }

        if ( isset( $payload[ 'limit' ] ) )
        {
            return $expireTime == 0 || Date::epoch('+'. $payload[ 'limit' ] . ' minutes') < Date::epoch( $appointment_starts );
        }

        return false;
    }

    private static function getChangeStatusPageURL()
    {
        $changeStatusPageID = Helper::getOption('change_status_page_id', '', false);

        if( empty( $changeStatusPageID ) )
            return '';

        return get_page_link( (int)$changeStatusPageID );

    }

    private static function isBookable( $appointment )
    {
        $appointmentExtras = AppointmentExtra::noTenant()->where('appointment_id', $appointment->id)->fetchAll();
        $extras_arr = [];
        foreach ( $appointmentExtras AS $extra )
        {
            $extra_inf = $extra->extra()->fetch();
            $extra_inf['quantity'] = $extra['quantity'];
            $extra_inf['customer'] = $appointment->customer_id;

            $extras_arr[] = $extra_inf;
        }

        $selectedTimeSlotInfo = new TimeSlotService( Date::dateSQL( $appointment->starts_at ), Date::time( $appointment->starts_at ) );
        $selectedTimeSlotInfo->setStaffId( $appointment->staff_id )
            ->setServiceId( $appointment->service_id )
            ->setServiceExtras( $extras_arr )
            ->setLocationId( $appointment->location_id )
            ->setCalledFromBackEnd( true )
            ->setShowExistingTimeSlots( true )
            ->setExcludeAppointmentId( $appointment->id );

        if( ! $selectedTimeSlotInfo->isBookable() )
            return false;

        return true;
    }

    public static function replaceShortCode( $text, $data )
    {
        if(! isset($data['appointment_id']))
            return $text;

        $appointmentId = $data['appointment_id'];
        $appointment = Appointment::get($appointmentId);

        $statustoRegex = '/({link_to_change_appointment_status_to_)(\w+)}/';
        preg_match_all($statustoRegex, $text, $match, PREG_SET_ORDER);

        if (empty($match)) return $text;

        $header = [
            'id' => $appointment->id
        ];

        foreach ($match as $rawStatus) {

            $statusTo = $rawStatus[2];
            $expireTime   = Helper::getOption( 'time_restriction_to_change_status', '0' );
            $expire   = Date::epoch( 'now', '+' . $expireTime . 'minutes' );

            $payload = [
                'currentStatus' => $appointment->status,
                'changeTo' => $statusTo,
                'title'    => Helper::getAppointmentStatuses()[$statusTo]['title'],
                'expire'   => $expire,
                'limit'    => $expireTime
            ];

            if ( Helper::isSaaSVersion() )
            $payload['tenant_id'] = Permission::tenantId();

            $secret = $appointment->payment_id . $expire;

            if( ! empty($appointment ) )
            {
                $token = Helper::generateToken( $header, $payload, $secret );

                $url = self::getChangeStatusPageURL();
                if ( empty( $url ) ) return $text;
//                $url .= "?bkntc_token={$token}";

                $url = add_query_arg( 'bkntc_token', $token, $url );

                $text = str_replace($rawStatus[0], $url , $text );
            }

        }

        return $text;
    }

}