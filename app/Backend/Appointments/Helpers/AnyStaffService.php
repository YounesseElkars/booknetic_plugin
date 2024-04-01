<?php

namespace BookneticApp\Backend\Appointments\Helpers;

use BookneticApp\Models\Appointment;
use BookneticApp\Models\ServiceStaff;
use BookneticApp\Models\Staff;
use BookneticApp\Providers\Helpers\Date;
use BookneticApp\Providers\DB\DB;
use BookneticApp\Providers\Helpers\Helper;

class AnyStaffService
{

	public static function staffByService( $serviceId, $locationId, $sortByRule = false, $date = null ): array
    {
        //todo:// Burdaki query-ler optimallashdirila biler

		$staffIDs = [];

		if( $serviceId > 0 )
		{
            $staffCount = Staff::select( 'count(0)' )
                ->where( Staff::getField( 'id' ), DB::field( ServiceStaff::getField( 'staff_id' ) ) )
                ->where( 'is_active', 1 );

            if ( $locationId > 0 )
            {
                $staffCount = $staffCount->whereFindInSet( 'locations', $locationId );
            }

            $staffList = ServiceStaff::select( 'staff_id' )
                ->where( 'service_id', $serviceId )
                ->where( $staffCount, '>', 0 )
                ->fetchAll();

            foreach ( $staffList AS $staff )
			{
				$staffIDs[] = (int)$staff['staff_id'];
			}
		}
		else
		{
			$allStaff = Staff::select( [ 'id', 'locations' ] )
                ->where('is_active', 1)
                ->fetchAll();
			foreach ( $allStaff AS $staff )
			{
				$staffLocations = empty( $staff['locations'] ) ? [] : explode( ',', $staff['locations'] );

				if( !($locationId > 0) || in_array( $locationId, $staffLocations ) )
				{
					$staffIDs[] = (int)$staff['id'];
				}
			}
		}

		if( !empty( $staffIDs ) && $sortByRule && !empty( $date ) )
		{
			$staffIDs = self::sortStaffByRule( $staffIDs, $date, $serviceId );
		}

		return $staffIDs;
	}

	public static function sortStaffByRule( $staffIDs, $date, $service )
	{
		$rule = Helper::getOption('any_staff_rule', 'least_assigned_by_day');

		if( $rule == 'most_expensive' || $rule == 'least_expensive' )
		{
			$getStaff = ServiceStaff::where('staff_id', $staffIDs);

			if( $service > 0 )
			{
				$getStaff = $getStaff->where('service_id', $service);
			}

			$getStaff = $getStaff->orderBy('price ' . ($rule == 'least_expensive' ? 'ASC' : 'DESC'))->fetchAll();
		}
		else
		{
			preg_match('/_([a-z]+)$/', $rule, $dateRule);
			$dateRule = isset($dateRule[1]) ? $dateRule[1] : '';

			if( $dateRule == 'day' )
			{
				$startDate	= Date::epoch($date, 'today');
				$endDate	= Date::epoch($date, 'tomorrow');
			}
			else if( $dateRule == 'week' )
			{
				$startDate	= Date::epoch($date, 'monday this week');
				$endDate	= Date::epoch($date, 'monday next week');
			}
			else
			{
				$startDate	= Date::epoch($date, 'first day of this month');
				$endDate	= Date::epoch($date, 'first day of next month');
			}

			$orderType = strpos( $rule, 'most_' ) === 0 ? 'DESC' : 'ASC';

			$subQuery = Appointment::where('staff_id', DB::field('id', 'staff'))
			                       ->where('starts_at', '>=', $startDate)
			                       ->where('ends_at', '<=', $endDate)
				->select('count(0)');

			$getStaff = Staff::select('id')
				->selectSubQuery( $subQuery, 'appointments_count' )
				->where('id', $staffIDs)
				->orderBy('appointments_count ' . $orderType)
				->fetchAll();
		}

		$sortedList = [];
		foreach ( (!empty($getStaff) ? $getStaff : []) AS $staff )
		{
			$sortedList[] = isset( $staff->staff_id ) ? (string)$staff->staff_id : (string)$staff->id;
		}

		foreach ( $staffIDs AS $staffID )
		{
			if( !in_array( (string)$staffID, $sortedList ) )
			{
				$sortedList[] = (string)$staffID;
			}
		}

		return $sortedList;
	}

}