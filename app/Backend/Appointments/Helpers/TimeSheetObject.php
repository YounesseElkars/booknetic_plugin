<?php

namespace BookneticApp\Backend\Appointments\Helpers;

use BookneticApp\Providers\Helpers\Date;

class TimeSheetObject implements \JsonSerializable
{

	private $timesheet;

	public function __construct( $timesheet = [] )
	{
		$default = [
			'start'                 =>  '',
			'end'                   =>  '',
			'day_off'               =>  1,
			'breaks'                =>  [],
			'holiday'               =>  0,
			'special_timesheet'     =>  0
		];

		$this->timesheet = array_merge( $default, $timesheet );
	}

	/**
	 * @return bool
	 */
	public function isDayOff()
	{
		return $this->timesheet['day_off'] == 1;
	}

	public function isHoliday()
	{
		return $this->timesheet['holiday'] == 1;
	}

	public function isSpecialTimesheet()
	{
		return $this->timesheet['special_timesheet'] == 1;
	}

	/**
	 * @param bool $formatTime
	 *
	 * @return string
	 */
	public function startTime( $formatTime = false )
	{
		$start = $this->timesheet['start'];

		return $formatTime ? Date::time( $start ) : Date::timeSQL( $start );
	}

	/**
	 * @param bool $formatTime
	 *
	 * @return string
	 */
	public function endTime()
	{
		$end = $this->timesheet['end'];

		if( $end == '24:00' )
        {
            return '24:00';
        }

		return Date::timeSQL( $end );
	}

	/**
	 * @return BreakTimeObject[]
	 */
	public function breaks()
	{
		$breaks = [];

		foreach ( $this->timesheet['breaks'] AS $breakTime )
		{
			$breaks[] = new BreakTimeObject( $breakTime );
		}

		return $breaks;
	}

	public function toArr()
	{
		return $this->timesheet;
	}

	public function jsonSerialize()
	{
		return $this->toArr();
	}

}