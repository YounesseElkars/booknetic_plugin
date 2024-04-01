<?php

namespace BookneticApp\Backend\Appointments\Helpers;

use BookneticApp\Providers\Helpers\Date;

class BreakTimeObject implements \JsonSerializable
{

	private $breakTime;

	public function __construct( $breakTime = [] )
	{
		$this->breakTime = $breakTime;
	}

	public function startTime( $formatTime = false )
	{
		$start = $this->breakTime[0];

		return $formatTime ? Date::time( $start ) : Date::timeSQL( $start );
	}

	public function endTime( $formatTime = false )
	{
		$end = $this->breakTime[1];

		return $formatTime ? Date::time( $end ) : Date::timeSQL( $end );
	}

	public function toArr()
	{
		return $this->breakTime;
	}

	public function jsonSerialize()
	{
		return $this->toArr();
	}

	public function isTheTimeslotABreakTime( $checkTimeStart, $checkTimeEnd )
	{
		$date               = Date::dateSQL( $checkTimeStart );
		$checkTimeStart     = Date::epoch( $checkTimeStart );
		$checkTimeEnd       = Date::epoch( $checkTimeEnd );
		$breakStart         = Date::epoch( $date . ' ' . $this->startTime() );
		$breakEnd           = Date::epoch( $date . ' ' . $this->endTime() );

		// doit: FS Posterde vaxtiken bu hissede nese bug variydi, fix etmishdik. O ne idi? Burda da bug var?
		if( Date::epoch( $this->startTime() ) > Date::epoch( $this->endTime() ) )
		{
			$breakEnd = Date::epoch( $breakEnd, '+1 days' );
		}

		if(
			( $breakStart <= $checkTimeStart && $breakEnd > $checkTimeStart )
			|| ( $breakStart < $checkTimeEnd && $breakEnd >= $checkTimeEnd )
			|| ( $breakStart > $checkTimeStart && $breakEnd < $checkTimeEnd )
		)
		{
			return true;
		}

		return false;
	}

}