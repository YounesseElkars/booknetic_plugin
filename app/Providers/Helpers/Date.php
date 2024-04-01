<?php

namespace BookneticApp\Providers\Helpers;

use BookneticApp\Providers\Helpers\Helper;

class Date
{

	private static $time_zone;
	private static $time_zone_option;

	public static function getTimeZone( $client_time_zone = false, $saved_timezone = '-' )
	{
		if( ($client_time_zone && Helper::_any('client_time_zone', '-', 'str') !== '-' ||  $saved_timezone !== '-') && self::clientTimezoneOptionIsEnabled() )
		{
			$clientTimeZone = Helper::_any( 'client_time_zone', '-', 'str' );

			if( $clientTimeZone === '-' && $saved_timezone !== '-' )
            {
                $clientTimeZone = $saved_timezone;
            }
			// Detect if javascript sent the timezone string
			if ( preg_match( '/^[a-zA-Z_-]+\/[a-zA-Z_-]+\/*[a-zA-Z_-]*$/', $clientTimeZone ) )
			{
				$timezone = $clientTimeZone;
			}
			// else javascript sent the timezone offset
			else if ( intval( $clientTimeZone ) == $clientTimeZone )
			{
				$clientTimeZoneOffset = intval( $clientTimeZone ) * -1;

				$hours = abs( (int)($clientTimeZoneOffset / 60) );
				$minutes = abs($clientTimeZoneOffset) - $hours * 60;

				$timezone = ($clientTimeZoneOffset > 0 ? '+' : '-') . sprintf('%02d:%02d', $hours, $minutes);
			}

			return new \DateTimeZone( $timezone );
		}

		if( is_null( self::$time_zone ) )
		{
			list( $tz_string, $tz_offset ) = self::getTimeZoneStringAndOffset();

			if ( !empty( $tz_string ) )
			{
				$timezone = $tz_string;
			}
			else if ( !empty( $tz_offset ) )
			{
				$hours = abs( (int)$tz_offset );
				$minutes = ( abs($tz_offset) - $hours ) * 60;

				$timezone = ($tz_offset >= 0 ? '+' : '-') . sprintf('%02d:%02d', $hours, $minutes);
			}
			else
			{
				$timezone = 'UTC';
			}

			self::$time_zone = new \DateTimeZone( $timezone );
		}

		return self::$time_zone;
	}

	public static function getTimeZoneStringAndOffset()
	{
		$tz_string = get_option( 'timezone_string' );
		$tz_offset = get_option( 'gmt_offset', 0 );

		if( Helper::isSaaSVersion() )
		{
			$getTimeZoneFromSettings = Helper::getOption('timezone', '');

			if( !empty( $getTimeZoneFromSettings ) )
			{
				$tz_string = strpos( $getTimeZoneFromSettings, 'UTC' ) === 0 ? '' : $getTimeZoneFromSettings;
				$tz_offset = !empty( $tz_string ) ? '' : (float)(str_replace('UTC', '', $getTimeZoneFromSettings));
			}
		}

		return [ $tz_string, $tz_offset ];
	}

	public static function getTimeZoneStringWP()
	{
		if( Helper::isSaaSVersion() )
		{
			$getTimeZoneFromSettings = Helper::getOption('timezone', '');

			if( !empty( $getTimeZoneFromSettings ) )
				return $getTimeZoneFromSettings;
		}

		$current_offset = get_option( 'gmt_offset' );
		$tzstring       = get_option( 'timezone_string' );

		if ( false !== strpos( $tzstring, 'Etc/GMT' ) )
		{
			$tzstring = '';
		}

		if ( empty( $tzstring ) )
		{
			if ( 0 == $current_offset )
			{
				$tzstring = 'UTC+0';
			}
			else if ( $current_offset < 0 )
			{
				$tzstring = 'UTC' . $current_offset;
			}
			else
			{
				$tzstring = 'UTC+' . $current_offset;
			}
		}

		return $tzstring;
	}

	public static function checkTimezoneIsActive( $timezoneInf )
	{
		$tz_string = get_option( 'timezone_string' );
		$tz_offset = get_option( 'gmt_offset', 0 );

		if( $timezoneInf['timezone_id'] == $tz_string || $timezoneInf['offset'] == $tz_offset )
			return true;
		else
			return false;
	}

	public static function setTimeZone( $time_zone )
	{
		$hours = abs( (int)($time_zone / 60) );
		$minutes = abs($time_zone) - $hours * 60;

		$timezone = ($time_zone > 0 ? '+' : '-') . sprintf('%02d:%02d', $hours, $minutes);

		self::$time_zone = new \DateTimeZone( $timezone );
	}

	public static function dateTime( $date = 'now', $modify = false, $client_time_zone = false, $saved_timezone = '-' )
	{
		if( !is_numeric( $date ) )
		{
			$date = self::epoch( $date );
		}

		$datetime = new \DateTime( 'now', self::getTimeZone( $client_time_zone, $saved_timezone ) );
		$datetime->setTimestamp( $date );

		if( !empty( $modify ) )
		{
			$datetime->modify( $modify );
		}

		return $datetime->format( self::formatDateTime() );
	}

	public static function datee( $date = 'now', $modify = false, $client_time_zone = false, $saved_timezone = '-' )
	{
		if( !is_numeric( $date ) )
		{
			$date = self::epoch( $date );
		}

		$datetime = new \DateTime( 'now', self::getTimeZone( $client_time_zone, $saved_timezone ) );
		$datetime->setTimestamp( $date );

		if( !empty( $modify ) )
		{
			$datetime->modify( $modify );
		}

		return $datetime->format( self::formatDate() );
	}

	public static function time( $date = 'now', $modify = false, $client_time_zone = false, $saved_timezone = '-' )
	{
		if( !is_numeric( $date ) )
		{
			$date = self::epoch( $date );
		}

		$datetime = new \DateTime( 'now', self::getTimeZone( $client_time_zone, $saved_timezone ) );
		$datetime->setTimestamp( $date );

		if( !empty( $modify ) )
		{
			$datetime->modify( $modify );
		}

		return $datetime->format( self::formatTime() );
	}

	public static function isValid( $time )
	{
		$time = trim( $time );

		if( empty( $time ) )
		{
			return false;
		}

		try
		{
			$datetime = new \DateTime( $time, self::getTimeZone() );

			return true;
		}
		catch (\Exception $e)
		{
			return false;
		}
	}

	public static function dateTimeSQL( $date = 'now', $modify = false, $client_time_zone = false  )
	{
		if( !is_numeric( $date ) )
		{
			$date = self::epoch( $date );
		}

		$datetime = new \DateTime( 'now', self::getTimeZone($client_time_zone) );
		$datetime->setTimestamp( $date );

		if( !empty( $modify ) )
		{
			$datetime->modify( $modify );
		}

		return $datetime->format( self::formatDateTime( true ) );
	}

	public static function dateSQL( $date = 'now', $modify = false,$client_time_zone = false )
	{
		if( !is_numeric( $date ) )
		{
			$date = self::epoch( $date );
		}

		$datetime = new \DateTime( 'now', self::getTimeZone($client_time_zone) );
		$datetime->setTimestamp( $date );

		if( !empty( $modify ) )
		{
			$datetime->modify( $modify );
		}

		return $datetime->format( self::formatDate( true ) );
	}

	public static function format( $format , $date = 'now', $modify = false, $client_time_zone = false  )
	{
		if( !is_numeric( $date ) )
		{
			$date = self::epoch( $date );
		}

		$datetime = new \DateTime( 'now', self::getTimeZone($client_time_zone) );
		$datetime->setTimestamp( $date );

		if( !empty( $modify ) )
		{
			$datetime->modify( $modify );
		}

		return $datetime->format( $format );
	}

	public static function timeSQL( $date = 'now', $modify = false, $client_time_zone = false )
	{
		if( !is_numeric( $date ) )
		{
			$date = self::epoch( $date );
		}

		$datetime = new \DateTime( 'now', self::getTimeZone($client_time_zone) );
		$datetime->setTimestamp( $date );

		if( !empty( $modify ) )
		{
			$datetime->modify( $modify );
		}

		return $datetime->format( self::formatTime( true ) );
	}

	public static function epoch( $date = 'now', $modify = false )
	{
		if( is_numeric( $date ) )
		{
			$datetime = new \DateTime( 'now', self::getTimeZone() );
			$datetime->setTimestamp( $date );
		}
		else
		{
			$datetime = new \DateTime( $date, self::getTimeZone() );
		}

		if( !empty( $modify ) )
		{
			$datetime->modify( $modify );
		}

		return $datetime->getTimestamp();
	}

	public static function formatDate( $forSQL = false )
	{
		if( $forSQL )
		{
			return 'Y-m-d';
		}
		else
		{
			return Helper::getOption('date_format', 'Y-m-d');
		}
	}

	public static function formatTime( $forSQL = false )
	{
		if( $forSQL )
		{
			return 'H:i';
		}
		else
		{
			return Helper::getOption('time_format', 'H:i');
		}
	}

	public static function formatDateTime( $forSQL = false )
	{
		return self::formatDate( $forSQL ) . ' ' . self::formatTime( $forSQL );
	}

	public static function UTCDateTime( $date, $format = 'Y-m-d\TH:i:sP', $modify = false )
	{
		if( !is_numeric( $date ) )
		{
			$date = self::epoch( $date );
		}

		$datetime = new \DateTime( 'now', self::getTimeZone() );
		$datetime->setTimestamp( $date );

		if( !empty( $modify ) )
		{
			$datetime->modify( $modify );
		}

		$datetime->setTimezone( new \DateTimeZone('UTC') );

		return $datetime->format( $format );
	}

	public static function reformatDateFromCustomFormat( $date )
    {
	    if ( empty( $date ) ||  preg_match( '/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $date ) )
	    {
		    return $date;
	    }

        $format = Helper::getOption('date_format', 'Y-m-d');

        if( $format == 'Y-m-d' )
        {
            return $date;
        }
        else if( $format == 'm/d/Y' )
        {
            $date = explode( '/', $date );
            return $date[2].'-'.$date[0].'-'.$date[1];
        }
        else if( $format == 'd-m-Y' )
        {
            $date = explode( '-', $date );
            return $date[2].'-'.$date[1].'-'.$date[0];
        }
        else if( $format == 'd/m/Y' )
        {
            $date = explode( '/', $date );
            return $date[2].'-'.$date[1].'-'.$date[0];
        }
        else if( $format == 'd.m.Y' )
        {
            $date = explode( '.', $date );
            return $date[2].'-'.$date[1].'-'.$date[0];
        }

        return $date;
    }

    public static function convertDateFormat( $date , $format = null )
    {
        if (! preg_match( '/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $date ) )
        {
            return $date;
        }
        $format = is_null($format) ? Helper::getOption('date_format', 'Y-m-d') : $format;
        return date($format, strtotime($date));
    }

    public static function identifyDateFormat( $date )
    {
        $formats = [
            'Y-m-d' => '/^([0-9]{4})\-([0-9]{2})\-([0-9]{2})$/',
            'd-m-Y' => '/^([0-9]{2})\-([0-9]{2})\-([0-9]{4})$/',
            'd.m.Y' => '/^([0-9]{2})\.([0-9]{2})\.([0-9]{4})$/',
            'm/d/Y' => '/^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$/',
            'd/m/Y' => '/^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$/',
        ];

        $matchedFormats = [];

        foreach( $formats as $format => $regex )
        {
            if( preg_match( $regex, $date ) )
            {
                $matchedFormats[] = $format;
            }
        }

        $matchedDateFormats = [];

        foreach( $matchedFormats AS $dateFormat )
        {
            $dateTime = \DateTime::createFromFormat( $dateFormat, $date );

            if ( $dateTime !== false && ! array_sum( $dateTime::getLastErrors() ) )
            {
                $matchedDateFormats[] = $dateFormat;
            }
        }

        if ( empty( $matchedDateFormats ) )
        {
            return null;
        }

        if ( count( $matchedDateFormats ) > 1 )
        {
            // if more than one format matches, we can't be sure which one is correct

            if ( in_array( Helper::getOption('date_format', 'Y-m-d'), $matchedDateFormats ) )
            {
                // if the default date format is one of the matched formats, we can assume that is the correct one
                return Helper::getOption('date_format', 'Y-m-d');
            }
        }

        return $matchedDateFormats[0];

    }

    public static function dayOfWeek( $date )
    {
	    $dayOfWeek = Date::format(  'w', $date );

	    return $dayOfWeek == 0 ? 7 : $dayOfWeek;
    }

    public static function resetTimezone()
    {
    	self::$time_zone = null;
    }

    private static function clientTimezoneOptionIsEnabled()
    {
    	if( is_null( self::$time_zone_option ) )
	    {
		    self::$time_zone_option = Helper::getOption('client_timezone_enable', 'off') === 'on' ? true : false;
	    }

	    return self::$time_zone_option;
    }

}
