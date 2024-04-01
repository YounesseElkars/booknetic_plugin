<?php

namespace BookneticApp\Backend\Appointments\Helpers;

use BookneticApp\Models\Holiday;
use BookneticApp\Models\SpecialDay;
use BookneticApp\Models\Timesheet;
use BookneticApp\Providers\Core\Capabilities;
use BookneticApp\Providers\Helpers\Date;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Core\Permission;

class TimeSheetService extends ServiceDefaults
{

	/**
	 * @var WeeklyTimeSheetObject
	 */
	private $timesheet;

	/**
	 * @var TimeSheetObject[][]
	 */
	private $specialTimesheetsByDate = [];

	/**
	 * @var bool[]
	 */
	private $holidays = [];


	/**
	 * @param $date
	 *
	 * @return TimeSheetObject
	 */
	public function getTimesheetByDate( $date )
	{
        if( $this->getInsideWorkingHouse() )
        {
            goto place;
        }
		if( $this->canBookAnyTime() )
		{
			return new TimeSheetObject( [
				"day_off"   => 0,
				"start"     => "00:00",
				"end"       => "24:00",
				"breaks"    => []
			] );
		}
        place:
		$specialTimesheets = $this->getSpecialTimesheet( $date );

		if( ! empty( $specialTimesheets ) )
		{
			return static::mergeSpecialTimesheets( $specialTimesheets );
		}

		if( $this->isHoliday( $date ) )
		{
			return new TimeSheetObject([ 'holiday' => 1 ]);
		}

		$dayOfWeek = Date::dayOfWeek( $date ) - 1;

		return $this->getDay( $dayOfWeek );
	}

	/**
	 * @return WeeklyTimeSheetObject
	 */
	public function getWeeklyTimesheet()
	{
		if( is_null( $this->timesheet ) )
		{
			$this->init();
		}

		return $this->timesheet;
	}

	/**
	 * @param $dayOfWeek
	 *
	 * @return TimeSheetObject
	 */
	public function getDay( $dayOfWeek )
	{
		return $this->getWeeklyTimesheet()->getDay( $dayOfWeek );
	}

	/**
	 * @param $date
	 *
	 * @return TimeSheetObject[]
	 */
	public function getSpecialTimesheet( $date )
	{
		if( ! key_exists( $date, $this->specialTimesheetsByDate ) )
		{
			$startDate  = Date::format( 'Y-m-01', $date );
			$endDate    = Date::format( 'Y-m-t', $date );

			$this->fetchSpecialtimesheets( $startDate, $endDate );
		}

		return $this->specialTimesheetsByDate[ $date ];
	}

	/**
	 * @param $date
	 *
	 * @return bool
	 */
	public function isHoliday( $date )
	{
		if( ! key_exists( $date, $this->holidays ) )
		{
			$startDate  = Date::format( 'Y-m-01', $date );
			$endDate    = Date::format( 'Y-m-t', $date );

			$this->fetchHolidays( $startDate, $endDate );
		}

		return $this->holidays[ $date ];
	}


	private function init()
	{
		if( ! is_null( $this->timesheet ) )
			return;

		if( $this->staffId == -1 )
		{
			$this->timesheet = $this->getAnyStaffTimeSheet();
		}
		else
		{
			$this->timesheet = $this->getTimeSheet();
		}
	}

	private function getTimeSheet()
	{

        $timesheet = Timesheet::where(function ($qb){
            $qb->where('service_id' , 'is' , null)
                ->where('staff_id' , 'is' , null);
        })->orWhere('service_id' , $this->serviceId )
            ->orWhere('staff_id' , $this->staffId )
            ->orderBy(['staff_id DESC','service_id DESC'])->fetch();

		return new WeeklyTimeSheetObject( json_decode( $timesheet['timesheet'], true ) );
	}

	private function getAnyStaffTimeSheet()
	{
		$staffIDs = AnyStaffService::staffByService( $this->serviceId, $this->locationId );

		$timesheets = [];
		foreach ( $staffIDs AS $staffID )
		{
			$timesheetServPerStaff = new TimeSheetService();
			$timesheetServPerStaff->setDefaultsFrom( $this );
			$timesheetServPerStaff->setStaffId( $staffID );

			$timesheets[] = $timesheetServPerStaff->getWeeklyTimesheet();
		}

		return static::mergeTimesheets( $timesheets );
	}

	private function fetchSpecialtimesheets( $startDate, $endDate )
	{

        $specialDays = SpecialDay::where(function ($qb) {
            $qb->orWhere('service_id' , $this->serviceId )
                ->orWhere('staff_id' , $this->staffId );
        })->where('date' ,'>=' , Date::dateSQL( $startDate ) )
            ->where('date' , '<=' , Date::dateSQL( $endDate ) )->fetchAll();


		foreach ( $specialDays AS $specialDayInf )
		{
			$date   = Date::dateSQL( $specialDayInf['date'] );
			$spDay  = new TimeSheetObject( json_decode( $specialDayInf['timesheet'], true ) );

			if( !isset( $this->specialTimesheetsByDate[ $date ] ) )
			{
				$this->specialTimesheetsByDate[ $date ] = [];
			}

			$this->specialTimesheetsByDate[ $date ][] = $spDay;
		}

		$startEpoch = Date::epoch( $startDate );
		$endEpoch   = Date::epoch( $endDate );

		while ( $startEpoch <= $endEpoch )
		{
			$date = Date::dateSQL( $startEpoch );

			if( !isset( $this->specialTimesheetsByDate[ $date ] ) )
			{
				$this->specialTimesheetsByDate[ $date ] = [];
			}

			$startEpoch = Date::epoch( $startEpoch, '+1 day' );
		}
	}

	private function fetchHolidays( $startDate, $endDate )
	{

        $holidays = Holiday::where(function ($qb) {
            $qb->where(function ($qb){
                $qb->where('service_id' , 'is' , null)
                    ->where('staff_id' , 'is' , null);
            })->orWhere('service_id' , $this->serviceId )
                ->orWhere('staff_id' , $this->staffId );
        })->where('date' ,'>=' , Date::dateSQL( $startDate ) )
            ->where('date' , '<=' , Date::dateSQL( $endDate ) )
            ->fetchAll();

		foreach ( $holidays AS $holidayInf )
		{
			$date = Date::dateSQL( $holidayInf['date'] );

			$this->holidays[ $date ] = true;
		}

		$startEpoch = Date::epoch( $startDate );
		$endEpoch   = Date::epoch( $endDate );

		while ( $startEpoch <= $endEpoch )
		{
			$date = Date::dateSQL( $startEpoch );

			if( !isset( $this->holidays[ $date ] ) )
			{
				$this->holidays[ $date ] = false;
			}

			$startEpoch = Date::epoch( $startEpoch, '+1 day' );
		}
	}

	private function canBookAnyTime()
	{
		if( $this->calledFromBackEnd )
		{
			$allow_admins_to_book_outside_working_hours = Helper::getOption('allow_admins_to_book_outside_working_hours', 'off');

			if( $allow_admins_to_book_outside_working_hours == 'on' && Permission::isAdministrator() )
			{
				return true;
			}

            if( ! Permission::isAdministrator() && Capabilities::userCan( 'appointment_book_outside_working_hours' ) ) return true;
		}

		return false;
	}

	/**
	 * @param $timesheets
	 *
	 * @return WeeklyTimeSheetObject
	 */
	public static function mergeTimesheets( $timesheets )
	{
		$timesheet = [];

		foreach ( $timesheets AS $timesheetInf )
		{
			$timesheetInf = $timesheetInf instanceof WeeklyTimeSheetObject ? $timesheetInf->toArr() : $timesheetInf;

			foreach( $timesheetInf AS $weekDay => $tSheet )
			{
				$tSheet = $tSheet instanceof TimeSheetObject ? $tSheet->toArr() : $tSheet;

				if( !isset( $timesheet[ $weekDay ] ) )
				{
					$timesheet[ $weekDay ] = [
						'day_off' 	=>	$tSheet['day_off'],
						'start'		=>	$tSheet['start'],
						'end'		=>	$tSheet['end'],
						'breaks'	=>	$tSheet['breaks']
					];

					continue;
				}

				if( $tSheet['day_off'] )
				{
					continue;
				}

				$timesheet[ $weekDay ]['day_off'] = 0;

				if( Date::epoch( $tSheet['start'] ) < Date::epoch( $timesheet[ $weekDay ]['start'] ) )
				{
					$timesheet[ $weekDay ]['start'] = $tSheet['start'];
				}

				if( Date::epoch( $tSheet['end'] ) > Date::epoch( $timesheet[ $weekDay ]['end'] ) )
				{
					$timesheet[ $weekDay ]['end'] = $tSheet['end'];
				}

				$timesheet[ $weekDay ]['breaks'] = static::mutualBreaks( $timesheet[ $weekDay ]['breaks'], $tSheet['breaks'] );
			}
		}

		return new WeeklyTimeSheetObject( $timesheet );
	}

	public static function mutualBreaks( $breaks1, $breaks2 )
	{
		$breaks = [];

		foreach ( $breaks1 AS $break1 )
		{
			foreach ( $breaks2 AS $break2 )
			{
				if(
					(Date::epoch( $break1[0] ) <= Date::epoch( $break2[0] ) && Date::epoch( $break1[1] ) > Date::epoch( $break2[0] )) ||
					(Date::epoch( $break1[0] ) < Date::epoch( $break2[1] ) && Date::epoch( $break1[1] ) >= Date::epoch( $break2[1] )) ||
					(Date::epoch( $break1[0] ) > Date::epoch( $break2[0] ) && Date::epoch( $break1[1] ) < Date::epoch( $break2[1] ))
				)
				{
					$breaks[] = [
						Date::epoch( $break1[0] ) > Date::epoch( $break2[0] ) ? $break1[0] : $break2[0],
						Date::epoch( $break1[1] ) > Date::epoch( $break2[1] ) ? $break2[1] : $break1[1]
					];
				}
			}
		}

		return $breaks;
	}

	/**
	 * @param TimeSheetObject[] $timesheets
	 *
	 * @return TimeSheetObject
	 */
	public static function mergeSpecialTimesheets( $timesheets )
	{
		$timesheet  = array_shift( $timesheets );
		$timesheet  = $timesheet instanceof TimeSheetObject ? $timesheet->toArr() : $timesheet;

		foreach ( $timesheets AS $tSheet )
		{
			if( $timesheet['day_off'] )
				break;

            $tSheet = $tSheet instanceof TimeSheetObject ? $tSheet->toArr() : $tSheet;

			if( $tSheet['day_off'] == 1 )
			{
				$timesheet['day_off'] = 1;
				$timesheet['start'] = '';
				$timesheet['end'] = '';
				$timesheet['breaks'] = [];
				break;
			}

			if( Date::epoch( $tSheet['start'] ) > Date::epoch( $timesheet['start'] ) )
			{
				$timesheet['start'] = $tSheet['start'];
			}

			if( Date::epoch( $tSheet['end'] ) < Date::epoch( $timesheet['end'] ) )
			{
				$timesheet['end'] = $tSheet['end'];
			}

			foreach ( $tSheet['breaks'] AS $break )
			{
				if( !in_array( $break , $timesheet['breaks'] ) )
				{
					$timesheet['breaks'][] = $break;
				}
			}
		}

		$timesheet['special_timesheet'] = 1;

		return new TimeSheetObject( $timesheet );
	}

}