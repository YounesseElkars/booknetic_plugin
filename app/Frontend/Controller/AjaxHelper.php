<?php

namespace BookneticApp\Frontend\Controller;

use BookneticApp\Backend\Appointments\Helpers\AppointmentRequestData;
use BookneticApp\Backend\Appointments\Helpers\AppointmentService;
use BookneticApp\Models\Customer;
use BookneticApp\Models\ServiceExtra;
use BookneticApp\Providers\Helpers\Curl;
use BookneticApp\Providers\Helpers\Date;
use BookneticApp\Providers\DB\DB;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Core\Permission;

class AjaxHelper
{

	public static function validateGoogleReCaptcha()
	{
		$googleRecaptchaOption = Helper::getOption('google_recaptcha', 'off', false);

		/**
		 * If the Google ReCaptcha setting has enabled...
		 */
		if( $googleRecaptchaOption == 'on' )
		{
			$google_site_key    = Helper::getOption('google_recaptcha_site_key', '', false);
			$google_secret_key  = Helper::getOption('google_recaptcha_secret_key', '', false);

			if( !empty( $google_site_key ) && !empty( $google_secret_key ) )
			{
				$google_recaptcha_token	    = Helper::_post('google_recaptcha_token', '', 'string');
				$google_recaptcha_action    = Helper::_post('google_recaptcha_action', '', 'string');

				if( empty( $google_recaptcha_token ) || empty( $google_recaptcha_action ) )
				{
					throw new \Exception( bkntc__('Please refresh the page and try again.') );
				}

				$checkToken = Curl::getURL( 'https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($google_secret_key) . '&response=' . urlencode($google_recaptcha_token) );
				$checkToken = json_decode( $checkToken, true );

				if( !($checkToken['success'] == '1' && $checkToken['action'] == $google_recaptcha_action && $checkToken['score'] >= 0.5) )
				{
					throw new \Exception( bkntc__('Please refresh the page and try again.') );
				}
			}
		}
	}

	public static function addToGoogleCalendarURL( AppointmentRequestData $appointmentObj )
	{
		$allAppointments        = $appointmentObj->getAllTimeslots();
		$firstAppointment       = $allAppointments[0];

		$firstAppointmentDate   = $firstAppointment->getDate();
		$firstAppointmentTime   = $firstAppointment->getTime();

		return 'https://www.google.com/calendar/render?action=TEMPLATE&text='
		                          . urlencode( $appointmentObj->serviceInf['name'] )
		                          . '&dates=' . ( Date::UTCDateTime($firstAppointmentDate . ' ' . $firstAppointmentTime, 'Ymd\THis\Z') . '/'
		                                          . Date::UTCDateTime($firstAppointmentDate . ' ' . $firstAppointmentTime, 'Ymd\THis\Z', '+' . ($appointmentObj->serviceInf['duration'] + $appointmentObj->getExtrasDuration()) . ' minutes') )
		                          . '&details=&location=' . urlencode( $appointmentObj->locationInf['name'] ) . '&sprop=&sprop=name:';
	}

    public static function addToiCalendarURL( AppointmentRequestData $appointmentObj )
    {
        $allAppointments        = $appointmentObj->getAllTimeslots();
        $firstAppointment       = $allAppointments[0];

        $firstAppointmentDate   = $firstAppointment->getDate();
        $firstAppointmentTime   = $firstAppointment->getTime();

        return  "data:text/calendar;charset=utf-8," .
                "BEGIN:VCALENDAR" . PHP_EOL .
                "VERSION:2.0" . PHP_EOL .
                "PRODID:-//" . Helper::getOption('company_name') . "//EN" . PHP_EOL .
                "BEGIN:VEVENT" . PHP_EOL .
                "DTSTART:" . Date::UTCDateTime($firstAppointmentDate . ' ' . $firstAppointmentTime, 'Ymd\THis\Z') . PHP_EOL .
                "DTEND:" . Date::UTCDateTime($firstAppointmentDate . ' ' . $firstAppointmentTime, 'Ymd\THis\Z', '+' . ($appointmentObj->serviceInf['duration'] + $appointmentObj->getExtrasDuration()) . ' minutes') . PHP_EOL .
                "SUMMARY:" . $appointmentObj->serviceInf->name . PHP_EOL .
                "LOCATION:" . $appointmentObj->locationInf->name . PHP_EOL .
                "DESCRIPTION:" . $appointmentObj->serviceInf->notes . PHP_EOL .
                "END:VEVENT" . PHP_EOL .
                "END:VCALENDAR";

    }

    public static function generateUserActivationToken( $customerId, $email )
    {
        $headers = [
            'id'        => $customerId,
            'expire'    => Date::epoch( 'now', '+ 48 hours' ),
        ];

        $body = [
            'email' => $email
        ];

        $secret = Helper::getOption( 'purchase_code', '', false );

        if ( empty( $secret ) || $secret === 'purchase_code' )
        {
            throw new \Exception( bkntc__('Illegal version of Booknetic detected.') );
        }

        $secret = hash_hmac('SHA256', $email, $secret, true);

        return Helper::generateToken( $headers, $body, $secret );
    }

}