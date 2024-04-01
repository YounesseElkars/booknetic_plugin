<?php

namespace BookneticApp\Backend\Appointments\Helpers;

use BookneticApp\Providers\Helpers\Date;

trait RecurringAppointmentService
{
	private static $recurringDates;

	public static function getRecurringDates ( AppointmentRequestData $appointmentObj )
	{
		if( is_null( self::$recurringDates ) )
		{
			$recurringType      = $appointmentObj->serviceInf['repeat_type'];
			$startCursor        = Date::epoch( $appointmentObj->recurringStartDate );
			$endDateEpoch       = Date::epoch( $appointmentObj->recurringEndDate );

			if( $recurringType == 'weekly' )
			{
				$allDates = self::iterateRecurringDatesWeekly( (string)$appointmentObj->recurringTimes, $startCursor, $endDateEpoch );
			}
			else if( $recurringType == 'daily' )
			{
				$allDates = self::iterateRecurringDatesDaily( (int)$appointmentObj->recurringTimes, $startCursor, $endDateEpoch, $appointmentObj->time );
			}
			else if( $recurringType == 'monthly' )
			{
				$allDates = self::iterateRecurringDatesMonthly( (string)$appointmentObj->recurringTimes, $startCursor, $endDateEpoch, $appointmentObj->time );
			}

			$calendarService    = new CalendarService( $appointmentObj->recurringStartDate, $appointmentObj->recurringEndDate );
			$calendarService->setDefaultsFrom( $appointmentObj );

			$day_offs           = $calendarService->getDayOffs();
			$appointments       = [];

			/**
			 * @var WeeklyTimeSheetObject $timesheetService
			 */
			$timesheetService   = $day_offs['timesheet'];
			$day_offs           = $day_offs['day_offs'];

			foreach ( $allDates AS $dateAndTime )
			{
				$weekDay    = Date::dayOfWeek( $dateAndTime[0] );

				$isHoliday  = isset( $day_offs[ $dateAndTime[0] ] );
				$isDayOff   = $timesheetService->getDay( $weekDay - 1 )->isDayOff();

				if( ! $isHoliday && ! $isDayOff )
				{
					$timeSlot = new TimeSlotService( $dateAndTime[0], $dateAndTime[1]  );
					$timeSlot->setDefaultsFrom( $appointmentObj );

					$appointments[] = $timeSlot;
				}
			}

			self::$recurringDates = $appointments;
		}

		return self::$recurringDates;
	}

	private static function iterateRecurringDatesWeekly( $recurringTimes, $startCursor, $endDateEpoch )
	{
		$allDates = [];

		$recurringTimes = json_decode( $recurringTimes, true );

		while( $startCursor <= $endDateEpoch )
		{
			$weekDay = Date::format( 'w', $startCursor  );
			$weekDay = ($weekDay == 0 ? 7 : $weekDay);

			if( isset( $recurringTimes[ $weekDay ] ) && is_string( $recurringTimes[ $weekDay ] ) && !empty( $recurringTimes[ $weekDay ] ) )
			{
				$allDates[] = [ Date::dateSQL( $startCursor ), $recurringTimes[ $weekDay ] ];
			}

			$startCursor = Date::epoch( $startCursor, '+1 days' );
		}

		return $allDates;
	}

	private static function iterateRecurringDatesDaily( $everyNdays, $startCursor, $endDateEpoch, $recurringTimes )
	{
		$allDates = [];

		while( $startCursor <= $endDateEpoch )
		{
			$allDates[]     = [ Date::dateSQL( $startCursor ), $recurringTimes ];
			$startCursor    = Date::epoch( $startCursor, '+' . $everyNdays . ' days' );
		}

		return $allDates;
	}

	private static function iterateRecurringDatesMonthly( $recurringTimes, $startCursor, $endDateEpoch, $time )
	{
		$allDates       = [];

		$recurringTimes = explode(':', $recurringTimes);
		$monthlyType    = $recurringTimes[0];
		$monthlyDays    = $recurringTimes[1];

		if( $monthlyType == 'specific_day' )
		{
			$monthlyDays = empty( $monthlyDays ) ? [] : explode( ',', $monthlyDays );
		}

		while( $startCursor <= $endDateEpoch )
		{
			if( $monthlyType == 'specific_day' )
			{
				if( in_array( Date::format( 'j', $startCursor ), $monthlyDays ) )
				{
					$allDates[] = [ Date::dateSQL( $startCursor ), $time ];
				}
			}
			else if( static::getMonthWeekInfo( $startCursor, $monthlyType, $monthlyDays ) )
			{
				$allDates[] = [ Date::dateSQL( $startCursor ), $time ];
			}

			$startCursor = Date::epoch( $startCursor, '+1 days' );
		}

		return $allDates;
	}

	private static function getMonthWeekInfo( $epoch, $type, $dayOfWeek )
	{
		$weekd = Date::format( 'w', $epoch );
		$weekd = $weekd == 0 ? 7 : $weekd;

		if( $weekd != $dayOfWeek )
		{
			return false;
		}

		$month = Date::format('m', $epoch);

		if( $type == 'last' )
		{
			$nextWeekMonth = Date::format( 'm', $epoch, '+1 week' );

			return $nextWeekMonth != $month;
		}

		$firstDayOfMonth = Date::format( 'Y-m-01', $epoch );
		$firstWeekDay = Date::format(  'w', $firstDayOfMonth );
		$firstWeekDay = $firstWeekDay == 0 ? 7 : $firstWeekDay;

		$dif = ( $dayOfWeek >= $firstWeekDay ? $dayOfWeek : $dayOfWeek + 7 ) - $firstWeekDay;

		$days = Date::format('d', $epoch) - $dif;
		$dNumber = (int)($days / 7) + 1;

		return $type == $dNumber;
	}

    public static function emptyRecurringDates()
    {
        self::$recurringDates = null;
    }

}