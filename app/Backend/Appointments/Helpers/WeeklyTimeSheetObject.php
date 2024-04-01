<?php

namespace BookneticApp\Backend\Appointments\Helpers;

use BookneticApp\Providers\Helpers\Date;

class WeeklyTimeSheetObject implements \JsonSerializable
{

	/**
	 * @var TimeSheetObject[]
	 */
	private $weeklyTimesheet;

	public function __construct( $weeklyTimesheet )
	{
		foreach ( $weeklyTimesheet AS $timesheetArr )
		{
			$this->weeklyTimesheet[] = $timesheetArr instanceof TimeSheetObject ? $timesheetArr : new TimeSheetObject( $timesheetArr );
		}
	}

	public function isCorrect()
	{
		if( empty( $this->weeklyTimesheet ) )
			return false;

		if( count( $this->weeklyTimesheet ) !== 7 )
			return false;

		return true;
	}

	public function getDay( $dayOfWeek )
	{
		return ! isset( $this->weeklyTimesheet[ $dayOfWeek ] ) ? new TimeSheetObject() : $this->weeklyTimesheet[ $dayOfWeek ];
	}

	public function minStartTime( $formatTime = false )
	{
		$minStartTime = Date::epoch('23:59:59');

		foreach( $this->weeklyTimesheet AS $timesheetOfDay )
		{
			if( $timesheetOfDay->isDayOff() )
				continue;

			if( $minStartTime > Date::epoch( $timesheetOfDay->startTime() ) )
			{
				$minStartTime = Date::epoch( $timesheetOfDay->startTime() );
			}
		}

		return $formatTime ? Date::time( $minStartTime ) : Date::timeSQL( $minStartTime );
	}

	public function maxStartTime()
	{
		$maxEndTime = Date::epoch('00:00:01');
        $timeString = '';

		foreach( $this->weeklyTimesheet AS $timesheetOfDay )
		{
			if( $timesheetOfDay->isDayOff() )
				continue;

			if( $maxEndTime < Date::epoch( $timesheetOfDay->endTime() ) )
			{
                $timeString = $timesheetOfDay->endTime();
				$maxEndTime = Date::epoch( $timeString );
			}
		}

        if ( $timeString === '24:00' )
        {
            return $timeString;
        }

		return Date::timeSQL( $maxEndTime );
	}

	public function toArr()
	{
		$timesheets = [];

		foreach ( $this->weeklyTimesheet AS $timesheet )
		{
			$timesheets[] = $timesheet->toArr();
		}

		return $timesheets;
	}

	public function jsonSerialize()
	{
		return $this->toArr();
	}

}