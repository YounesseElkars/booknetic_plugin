<?php

namespace BookneticApp\Backend\Appointments\Helpers;

use BookneticApp\Models\Appointment;
use BookneticApp\Providers\Helpers\Date;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Backend\Appointments\Helpers\AppointmentRequests as Request;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Exception;

class CalendarService extends ServiceDefaults
{
    public $dateFrom;
    public $dateTo;

    public DateTimeZone $serverTz;
    public DateTimeZone $clientTz;

    public int $serviceTotalDuration;
    public int $serviceMarginAfter;
    public int $serviceMarginBefore;

    public bool $flexibleTimeslot;
    public ?bool $showBusySlots = null;

    private DateTime $minTimePriorBooking;

    private string $groupBy = '';

    private array $busySlots = [];
    private array $timesheet = [];

    private static bool $includeCart = false;

    private static bool $skipCurrentRequest = false;

    private array $appointments;

    private array $calendarData = [ 'dates' => [], 'fills' => [] ];

    private DateTimeImmutable $dateToImmutable;

    public function __construct( $dateFrom = null, $dateTo = null )
	{
		$this->dateFrom = $dateFrom;
		$this->dateTo   = is_null( $dateTo ) ? $dateFrom : $dateTo;

        $this->serverTz = Date::getTimeZone();
        $this->clientTz = Date::getTimeZone(true);

        $this->flexibleTimeslot = Helper::getOption( 'flexible_timeslot', '1' ) == '1';

        $this->showBusySlots = apply_filters( 'bkntc_show_busy_time_slot' , $this->showBusySlots );

		/**
		 * Odenish edilmemish appointmentlerin statusunu cancel edek ki, orani da booking ede bilsin...
		 */
		AppointmentService::cancelUnpaidAppointments();
	}

    /**
     * @throws Exception
     * @return int
     */
    public function getFirstAvailableDay()
    {
        $theDay = $this->getFirstAvailableDayOfMonth();

        if ( ! empty( $theDay ) )
            return $theDay;

        //if there were no available days for the given month, try for the next one

        $this->dateFrom = $this->dateTo;
        $this->dateTo   = Date::dateSQL( Date::epoch( $this->dateTo, '+1 month' ) );

        return $this->getFirstAvailableDayOfMonth();
    }

    /**
     * @throws Exception
     * @return int
     */
    private function getFirstAvailableDayOfMonth()
    {
        $calendar = $this->getCalendar();

        if ( empty( $calendar[ 'dates' ] ) )
            return 0;

        foreach ( $calendar[ 'dates' ] as $day => $slots )
        {
            if ( ! empty( $slots ) )
                return Date::epoch( $day );
        }

        return 0;
    }

    /**
     * @param  string $groupBy
     * @throws Exception
     * @return array
     */
    public function getCalendar( string $groupBy = 'day' ): array
    {
        if ( ! $this->groupBy )
        {
            $this->groupBy = $groupBy;
        }

        if( ! ( $this->staffId > 0 ) )
            return $this->getAllStaffCalendars();

        return $this->getStaffCalendar();
    }

    /**
     * @description Returns the staff calendar data.
     *
     * @throws Exception
     *
     * @return array The staff calendar data.
     */
    private function getStaffCalendar(): array
    {
        // Check if the function is called from the admin dashboard(backend).
        if ( ! $this->calledFromBackEnd )
        {
            // Get the minimum time required prior to booking.
            $minTimePriorBooking = intval( Helper::getMinTimeRequiredPriorBooking( $this->serviceId ) );
            // Calculate the date from which the service can be booked.
            $calculatedDateFrom = Date::epoch() + ( $minTimePriorBooking * 60 );

            // If the current date is earlier than the calculated date from,
            // set the date from to the calculated date from.
            if ( Date::epoch( $this->dateFrom ) < $calculatedDateFrom )
            {
                $this->dateFrom = Date::dateSQL( $calculatedDateFrom );
            }

            // If the end date is earlier than the start date, return an empty calendar.
            if ( Date::epoch( $this->dateTo ) < Date::epoch( $this->dateFrom ) )
                return [ 'dates' => [], 'fills' => [] ];
        }

        // Calculate the service duration and margins.
        $this->calculateServiceDurationAndMargins();
        // Initialize the staff calendars.
        $this->initStaffCalendar();

        // Return the calendar data.
        return $this->calendarData;
    }

    private function calculateServiceDurationAndMargins(): void
    {
        $this->serviceTotalDuration = $this->getServiceInf()->duration + ExtrasService::calcExtrasDuration($this->serviceExtras);
        $this->serviceMarginBefore = (int)$this->getServiceInf()->buffer_before;
        $this->serviceMarginAfter = $this->serviceTotalDuration + $this->getServiceInf()->buffer_after;
    }

    /**
     * @throws Exception
     *
     * @returns array
     */
    private function initStaffCalendar(): void
    {
        // Initialize the date ranges for the staff based on their timesheet and existing appointments.
        $this->initRangesFromTimesheet();
        $this->initStaffAppointments();

        // Get the minimum time required prior to booking and convert it to a DateTime object.
        $minTimePriorBooking       = intval( Helper::getMinTimeRequiredPriorBooking( $this->serviceId ) );

        //todo:// why are we not setting serverTz here? the value for the DateTime object will be assumed to be on UTC+0 if a specific timezone is not provided
        // We could do it like this( test needed ): $this->minTimePriorBooking = $this->getDatetimeImmutable( 'now' )->modify( "+$minTimePriorBooking minutes" );
        $this->minTimePriorBooking = ( new DateTime() )->modify( "+$minTimePriorBooking minutes" );

        // Convert the start and end dates for the staff's schedule to DateTimeImmutable objects.
        $dateFrom              = $this->getDatetimeImmutable( $this->dateFrom . ' 00:00:00' );
        $this->dateToImmutable = $this->getDatetimeImmutable( $this->dateTo . ' 24:00:00' );

        // Add an extra day to the end date to include the entire last day in the schedule.
        $dateTo = $this->dateToImmutable->modify( '+1 day' );

        // Initialize the timeslots for each day in the schedule.
        for ( $i = $dateFrom; $i < $dateTo; $i = $i->modify( '+1 day' ) )
        {
            $this->initTimeslotsByDay( $i );
        }

        // Calculate the filled time slots for the staff.
        $this->calculateZoomedFills();
    }

    /**
     * @throws Exception
     *
     * @returns void
     */
    private function initRangesFromTimesheet(): void
    {
        $dateFrom = $this->getDatetimeImmutable( $this->dateFrom )
            ->modify( '-' . $this->serviceMarginBefore . ' minutes')
            ->setTime( 0, 0 );

        $dateTo = $this->getDatetimeImmutable( $this->dateTo . ' 24:00:00' )
            ->modify( '+' . $this->serviceMarginAfter . ' minutes' )
            ->setTime( 24, 0 );

        $timesheetService = new TimeSheetService();
        $timesheetService->setDefaultsFrom( $this );
        $busySlots = [];
        $timesheetForAllDay = [];

        for ( $dateFromEpoch = $dateFrom->getTimestamp(); $dateFromEpoch <= $dateTo->getTimestamp(); $dateFromEpoch = Date::epoch( $dateFromEpoch, '+1 day' ) )
        {
            $timesheet = $timesheetService->getTimesheetByDate( Date::dateSQL( $dateFromEpoch ) );
            $timesheetForAllDay[ Date::dateSQL( $dateFromEpoch ) ] = $timesheet->toArr();

            // Diqqet: isDayOff() ichinde isHoliday()-i de yoxlayir.
            if( $timesheet->isDayOff() )
            {
                $busySlots[] = [ $dateFromEpoch , Date::epoch( $dateFromEpoch, '+1 day' ) ];
                continue;
            }

            if( $this->isDateBasedService() )
            {
                $timesheetForAllDay[ Date::dateSQL( $dateFromEpoch ) ][ 'start' ] = '00:00';
                $timesheetForAllDay[ Date::dateSQL( $dateFromEpoch ) ][ 'end' ]   = '24:00';
                continue;
            }

            foreach ( $timesheet->breaks() as $break )
            {
                $busySlots[] = [ Date::epoch( Date::dateSQL( $dateFromEpoch ) . " " . $break->startTime() ) , Date::epoch( Date::dateSQL( $dateFromEpoch ) . " " . $break->endTime() ) ];
            }

            if( $dateFromEpoch != Date::epoch( Date::dateSQL( $dateFromEpoch ) . " " . $timesheet->startTime() ) )
                $busySlots[] = [ $dateFromEpoch , Date::epoch( Date::dateSQL( $dateFromEpoch ) . " " . $timesheet->startTime() ) ];

            if( Date::epoch( Date::dateSQL( $dateFromEpoch ) . " " . $timesheet->endTime() ) != Date::epoch( $dateFromEpoch, '+1 day' ) )
                $busySlots[] = [ Date::epoch( Date::dateSQL( $dateFromEpoch ) . " " . $timesheet->endTime() ) , Date::epoch( $dateFromEpoch, '+1 day' ) ];
        }

        $this->busySlots = apply_filters( 'bkntc_busy_slots' , $busySlots , $this );
        $this->timesheet = $timesheetForAllDay;
    }

    /**
     * @param DateTimeImmutable $i The date for which to initialize timeslots.
     *
     * @description Initializes timeslots for a single day based on the given DateTimeImmutable object.
     *
     * @throws Exception
     *
     * @returns void
     */
    private function initTimeslotsByDay( DateTimeImmutable $i ): void
    {
        // Set the input date time to midnight
        $i = $i->setTime( 0, 0 );

        // Calculate the end of the day (i.e., midnight of the next day)
        $dayEnd = $i->modify( '+1 day' );

        // Get the date in Y-m-d format
        $ymd = $i->format( 'Y-m-d' );

        // Get the start time for the given day
        $startTime = $this->timesheet[ $ymd ][ 'start' ];

        // Create a new DateTime object with the input date and start time
        // Note: We assume that $startTime is in the format 'H:i:s'.
        $j = new DateTime( $ymd . ' ' . $startTime , $this->serverTz );

        // Initialize timeslots between the start and end of the day
        while ( $j < $dayEnd )
        {
            //This condition prevents initializing timeslots for a date beyond the staff's schedule.
            if ( $j >= $this->dateToImmutable )
                return;

            $this->initTimeslotByTime( $j );
        }
    }

    /**
     * @param DateTime $j
     *
     * @description Calculates a single timeslot, its availability, start/end time as well as other information for the given $j time.

     * @note Please, keep in mind that $j is a reference, as any changes to it will affect the variable passed as the parameter outside of this function.
     *
     * @returns void
     */
    private function initTimeslotByTime( DateTime &$j )
    {
        // References to the 'dates' and 'fills' properties are created to enable modifying them via $dates and $fills variables.
        // If $dates or $fills are changed inside this function, the changes will be reflected on the 'dates' and 'fills' properties outside the function.
        $dates    = &$this->calendarData[ 'dates' ];
        $fills    = &$this->calendarData[ 'fills' ];
        $serverTz = $this->serverTz;
        $clientTz = $this->clientTz;
        $jConst   = DateTimeImmutable::createFromMutable( $j );
        $jFormat  = $jConst->setTimezone( $clientTz )->format( 'Y-m-d' );

        if ( $this->groupBy === 'day' && ! array_key_exists( $jFormat, $dates ) )
        {
            $dates[ $jFormat ] = [];
        }

        // If the $j date is less than the minimum time prior to booking and not called from the backend, $j is modified to increase its value with the timeslot length.
        if ( $j < $this->minTimePriorBooking && ! $this->calledFromBackEnd )
        {
            $j->modify( "+" . $this->getTimeSlotLength() . " minutes" );
            return;
        }

        // The 'range_overlap_with_ranges' function from RangeService is called to determine if there is any overlap between the $j timeslot and the busy slots in the 'busySlots' property.
        $collision = RangeService::range_overlap_with_ranges( $this->busySlots , [ $jConst->modify("-" . $this->serviceMarginBefore . " minutes")->getTimestamp() , $jConst->modify("+" . ($this->serviceMarginAfter) . " minutes")->getTimestamp()]);

        if( $collision !== false )
        {
            //If there is an overlap between the timeslot and busy slots and the timeslot is flexible, $j is set to the end time of the busy slot with the addition of the service margin before, converted to the server timezone.
            if ( $this->flexibleTimeslot )
            {
                $j = DateTime::createFromFormat( 'U', $collision[ 1 ] )->setTimezone( $serverTz )->modify( "+" . $this->serviceMarginBefore . ' minutes' );
                return;
            }


            //if the timeslot is not flexible, $j is modified to increase its value with the timeslot length.
            $j->modify("+" . $this->getTimeSlotLength() . " minutes");
            return;
        }

        // The 'getMatchedAppointment' function is called to find an appointment in the 'appointments' array that overlaps with the $j timeslot.
        $match = $this->getMatchedAppointment( $jConst );

        if( ! $match[ 'appointment' ] || $match[ 'can_book' ] )
        {
            $start = $jConst;
            $end   = $jConst->modify( '+' . $this->serviceTotalDuration . ' minutes' );

            // doit: original timelar H:i ile yox da, birbasha epoch ile getmelidi ki, 0 bug qalsin DST-da. Meselen Berlinde saat chekilmesi geriye oldugda 02:00-03:00 2 defe tekrarlanacaq timeslot olacaq... ve original time-larida eyni qaydada tekrarlanacaq... cunki ora H:i gedir. amma full epoch getse (timestamp(Y-m-d H:i:s)) o halda 0 bug olacag...

            $cSlot = [
                'date'              => $start->format( 'Y-m-d' ),
                'start_time'        => $start->format( 'H:i' ),
                'end_time'          => $end->format( 'H:i' ),
                'start_time_format' => $start->setTimezone( $clientTz )->format( Date::formatTime() ),
                'end_time_format'   => $end->setTimezone( $clientTz )->format( Date::formatTime() ),
                'buffer_before'     => '0',
                'buffer_after'      => '0',
                'duration'          => $this->serviceTotalDuration,
                'max_capacity'      => $this->getServiceInf()->max_capacity,
                'weight'            => empty( $match[ 'appointment' ] ) ? 0 : $match[ 'appointment' ]->total_weight,
            ];

            if ( $this->groupBy === 'day' )
            {
                $dates[ $jFormat ][] = $cSlot;
            }
            else if ( $this->groupBy === 'timestamp' )
            {
                $dates[ $start->getTimestamp() ] = $cSlot;
            }

            $fills[ $jFormat ][] = 1;
        }
        else
        {
            /**
             * Check if we are allowed to show slots,
             * And the appointment is not a pseudo slot - is not an appointment on the cart,
             * And the timeslots are matched - equal_time
             */
            if( $this->showBusySlots && ! $match[ 'appointment' ]->pseudoSlot  && $match[ 'equal_time' ] )
            {
                $start = $jConst;
                $end   = $jConst->modify( '+' . $this->serviceTotalDuration . ' minutes' );

                $cSlot = [
                    'date'              => $start->format( 'Y-m-d' ),
                    'start_time'        => $start->format( 'H:i' ),
                    'end_time'          => $end->format( 'H:i' ),
                    'start_time_format' => $start->setTimezone( $clientTz )->format( Date::formatTime() ),
                    'end_time_format'   => $end->setTimezone( $clientTz )->format( Date::formatTime() ),
                    'buffer_before'     => '0',
                    'buffer_after'      => '0',
                    'duration'          => $this->serviceTotalDuration,
                    'max_capacity'      => $this->getServiceInf()->max_capacity,
                    'weight'            => empty( $match[ 'appointment' ] ) ? 0 : $match[ 'appointment' ]->total_weight,
                    'busy'              => true,
                ];

                if ( $this->groupBy === 'day' )
                {
                    $dates[ $jFormat ][] = $cSlot;
                }
                else if ( $this->groupBy === 'timestamp' )
                {
                    $dates[ $start->getTimestamp() ] = $cSlot;
                }
            }

            $fills[ $jFormat ][] = 0;
        }

        if ( ! $this->showBusySlots && !! $match[ 'appointment' ] && $this->flexibleTimeslot && ! $match[ 'can_book_for_other_slots' ] )
        {
            $j = ( clone $match[ 'appointment' ]->realEndDT )->modify( '+' . $this->serviceMarginBefore . ' minutes' );

            return;
        }

        $j->modify( '+' . $this->getTimeSlotLength() . ' minutes' );
    }

    /**
     * Returns DateTime object with the given time value on server's timezone.
     * By default, assumes the given time is on client's timezone.
     * You can change this behavior by setting $setClientTz parameter to false.
     * @throws Exception
     */
    private function getDatetimeImmutable( string $time, bool $setClientTz = true ): DateTimeImmutable
    {
        if ( $setClientTz )
        {
            $t = new DateTimeImmutable( $time, $this->clientTz );
        }
        else
        {
            $t = new DateTimeImmutable( $time );
        }

        return $t->setTimezone( $this->serverTz );
    }

    /**
     * @throws Exception
     *
     * @returns void
     */
    private function initStaffAppointments()
    {
        $busyStatuses = Helper::getBusyAppointmentStatuses();

        $busyFrom = $this->getDatetimeImmutable( $this->dateTo . ' 24:00:00' )->modify("+" . $this->serviceMarginAfter . " minutes")->getTimestamp();
        $busyTo   = $this->getDatetimeImmutable( $this->dateFrom . ' 00:00:00' )->modify("-" . $this->serviceMarginBefore . " minutes" )->getTimestamp();

        $appointments = Appointment::where('busy_from', '<=', $busyFrom )
            ->where( 'busy_to', '>=', $busyTo )
            ->where( 'staff_id', $this->staffId )
            ->where( 'status', 'in', $busyStatuses )
            ->select([ 'location_id', 'service_id', 'busy_from', 'busy_to', 'starts_at', 'ends_at' ] )
            ->select( 'SUM(weight) as total_weight' )
            ->groupBy( ['starts_at', 'staff_id', 'location_id', 'service_id' ] );

        if( is_numeric( $this->excludeAppointmentId ) && $this->excludeAppointmentId> 0 )
        {
            $appointments->where( Appointment::getField( 'id' ), '<>', (int) $this->excludeAppointmentId );
        }

        $this->appointments = $appointments->fetchAll();

        if ( self::$includeCart )
        {
            $this->mergeCart();
        }
    }

    private function calculateZoomedFills(): void
    {
        $dayFillPercentsZoomed = [];

        foreach ( $this->calendarData[ 'fills' ] as $k => $v )
        {
            $dayFillPercentsZoomed[ $k ] = RangeService::zoom( $v, 17 );
        }

        $this->calendarData[ 'fills' ] = $dayFillPercentsZoomed;
    }

    /**
     * Checks whether staff has an appointment for the given $j time,
     * If matched, returns the appointment, as well as the other information regarding slot.
     */
    private function getMatchedAppointment( DateTimeImmutable $j ): array
    {
        $serverTz = $this->serverTz;
        $match    = [
            'appointment'              => [],
            'can_book'                 => false,
            'can_book_for_other_slots' => false,
            'equal_time'               => false
        ];

        /**
         * this property initialized through initStaffAppointments method
         */
        foreach ( $this->appointments as $sa )
        {
            //appointment start, end, realStart and realEnd times
            $start     = ( new DateTime() )->setTimezone( $serverTz )->setTimestamp( $sa->starts_at );
            $end       = ( new DateTime() )->setTimezone( $serverTz )->setTimestamp( $sa->ends_at );
            $realStart = ( new DateTime() )->setTimezone( $serverTz )->setTimestamp( $sa->busy_from );
            $realEnd   = ( new DateTime() )->setTimezone( $serverTz )->setTimestamp( $sa->busy_to );

            if ( $j->modify( '-' . $this->serviceMarginBefore . ' minutes' ) >= $realEnd || $j->modify( '+' . $this->serviceMarginAfter . ' minutes' ) <= $realStart )
                continue;

            /**
             * <b>! $this->getLocationId()
             * means if location step is after date & time</b>
             */

            //set the matched appointment
            $match[ 'can_book_for_other_slots' ] = (
                ( ! $this->getLocationId() || $sa->location_id == $this->getLocationId() ) &&
                $sa->service_id == $this->getServiceId() &&
                $sa->total_weight < $this->getServiceInf()->max_capacity
            );

            $match[ 'can_book' ] = (
                ( ! $this->getLocationId() || $sa->location_id == $this->getLocationId() ) &&
                $sa->service_id == $this->getServiceId() &&
                $sa->total_weight < $this->getServiceInf()->max_capacity &&
                $start->getTimestamp() == $j->getTimestamp() &&
                $end->getTimestamp() == ( $j->modify( '+' . $this->serviceTotalDuration . ' minutes' ) )->getTimestamp()
            );

            $match[ 'equal_time' ] = (
                ( ! $this->getLocationId() || $sa->location_id == $this->getLocationId() ) &&
                $start->getTimestamp() == $j->getTimestamp() &&
                $end->getTimestamp() == ( $j->modify( '+' . $this->serviceTotalDuration . ' minutes' ) )->getTimestamp()
            );

            $sa->realEndDT = $realEnd;

            $match[ 'appointment' ] = $sa;
            break;
        }

        return $match;
    }

    public function getCalendarByDayOfWeek( $dayOfWeek, $search = '' ): array
    {
        $calendarData       = [];
        $timesheetService   = new TimeSheetService();
        $timesheetService->setDefaultsFrom( $this );

        $weeklyTimeSheet    = $timesheetService->getWeeklyTimesheet();

        if( ! $weeklyTimeSheet->isCorrect() )
        {
            return $calendarData;
        }

        if( $dayOfWeek == -1 || Helper::getOption( 'allow_admins_to_book_outside_working_hours', 'off' ) === 'on' )
        {
            $tStart = $weeklyTimeSheet->minStartTime();
            $tEnd   = $weeklyTimeSheet->maxStartTime();

            if( Date::epoch( $tStart ) > Date::epoch( $tEnd ) )
            {
                $tStart   = '00:00';
                $tEnd     = '23:59';
            }

            $timesheetObj = new TimeSheetObject( [
                "day_off"   => 0,
                "start"     => $tStart,
                "end"       => $tEnd,
                "breaks"    => []
            ] );
        }
        else
        {
            $timesheetObj = $weeklyTimeSheet->getDay( $dayOfWeek );
        }

        $tStart		= Date::epoch( $timesheetObj->startTime() ) + $this->getServiceInf()->buffer_before * 60;
        $tEnd		= Date::epoch( $timesheetObj->endTime() ) - ( $this->getServiceInf()->buffer_before + $this->getServiceInf()->buffer_after + $this->getServiceInf()->duration ) * 60;

        if( $timesheetObj->isDayOff() )
        {
            return $calendarData;
        }

        $timeslotLength = $this->getTimeSlotLength();
        $extrasDuration     = ExtrasService::calcExtrasDuration( $this->serviceExtras );

        while( $tStart <= $tEnd )
        {
            $fullTimeStart      = $tStart - $this->getServiceInf()->buffer_before * 60;
            $fullTimeEnd        = $fullTimeStart + ( $this->getServiceInf()->buffer_before + $this->getServiceInf()->duration + $this->getServiceInf()->buffer_after + $extrasDuration ) * 60;


            $timeId     = Date::timeSQL( $tStart );
            $timeText   = Date::time( $tStart );
            $tStart     += $timeslotLength * 60;



            if( !empty( $search ) && strpos( $timeText, $search ) === false )
            {
                continue;
            }

            $isBreakTime = false;

            foreach ( $timesheetObj->breaks() AS $break )
            {
                if( $break->isTheTimeslotABreakTime( $fullTimeStart, $fullTimeEnd) )
                {
                    $isBreakTime    = true;
                    $tStart     = Date::epoch(  $break->endTime() ) + $this->getServiceInf()->buffer_before * 60;
                    break;
                }
            }

            if ( $isBreakTime )
                continue;

            $calendarData[] = [
                'id'	=>	$timeId,
                'text'	=>	$timeText
            ];
        }

        return $calendarData;
    }

    public function getDayOffs(): array
    {
        $cursor 				= Date::epoch( $this->dateFrom );
        $endDate 				= Date::epoch( $this->dateTo );
        $dayOffsArr 			= [];
        $disabledDaysOfWeek 	= [ true, true, true, true, true, true, true ];

        $timesheetService = new TimeSheetService();
        $timesheetService->setDefaultsFrom( $this );

        while( $cursor <= $endDate )
        {
            $curDate		= Date::dateSQL( $cursor );
            $curDayOfWeek	= Date::dayOfWeek( $cursor ) - 1;

            $timesheetOfDay = $timesheetService->getTimesheetByDate( $curDate );

            if( ! $timesheetOfDay->isDayOff() )
            {
                $disabledDaysOfWeek[ $curDayOfWeek ] = false;
            }

            if( $timesheetOfDay->isHoliday() )
            {
                $dayOffsArr[ $curDate ] = 1;
            }
            else if( $timesheetOfDay->isSpecialTimesheet() && $timesheetOfDay->isDayOff() )
            {
                $dayOffsArr[ $curDate ] = 1;
            }

            $cursor = Date::epoch( $cursor, '+1 days' );
        }

        return [
            'day_offs'				=> $dayOffsArr,
            'disabled_days_of_week'	=> $disabledDaysOfWeek,
            'timesheet'				=> $timesheetService->getWeeklyTimesheet()
        ];
    }

    /**
     * @throws Exception
     */
    private function getAllStaffCalendars(): array
    {
        $allStaffIDs = AnyStaffService::staffByService( $this->serviceId, $this->locationId );

        $dates = [];
        $fills = [];

        self::$includeCart = true;

        foreach ( $allStaffIDs AS $staffID )
        {
            $calendar = ( clone $this )
                ->setStaffId( $staffID )
                ->getStaffCalendar();

            $dates[] = $calendar[ 'dates' ];

            foreach ( $calendar[ 'fills' ] as $k => $v )
            {
                if ( ! array_key_exists( $k, $fills ) )
                {
                    $fills[ $k ] = RangeService::zoom( [ 0 ], 17 );
                }

                $fills[ $k ] = RangeService::orArr( $fills[ $k ], $v );
            }
        }

        return [
            'dates' => $this->sortTimeslotsAtoZ( $this->mergeDates( $dates ) ),
            'fills' => $fills
        ];
    }

    private function mergeDates( array $staffs ): array
    {
        $dates = $staffs[ 0 ] ?? [];

        foreach ( $staffs as $staff )
        {
            foreach ( $staff as $key => $datesValue )
            {
                if( ! isset( $dates[ $key ] ) )
                {
                    $dates[ $key ] = $datesValue;
                    continue;
                }

                /**
                 * The operations below are to calculate whether there's a different timeslot of any $staff on a given $key day
                 * When grouping timeslots by timestamp, $key is equal to a timestamp and $dates[ $key ] holds the value of a timeslot,
                 * Whereas when grouped by day the $key will be a date string and the $dates[ $key ]
                 * Will have a value of an array of the timeslots on a given $key day
                */
                if ( $this->groupBy === 'timestamp' )
                    continue;

                foreach ( $datesValue as $dateValueInfo )
                {
                    $hasSameTimeSlot = false;

                    foreach ( $dates[ $key ] as $savedDates )
                    {
                        if( $savedDates[ 'start_time' ] == $dateValueInfo[ 'start_time' ] )
                        {
                            $hasSameTimeSlot = true;
                            break;
                        }
                    }

                    if( ! $hasSameTimeSlot )
                    {
                        $dates[ $key ][] = $dateValueInfo;
                    }
                }
            }
        }

        return $dates;
    }

    private function sortTimeslotsAtoZ( $dates )
    {
        if ( $this->groupBy !== 'day' )
            return $dates;

        foreach ( $dates AS $dateKey => $timesValue )
        {
            $sortByKey = $this->calledFromBackEnd ? 'start_time' : 'start_time_format';

            usort($timesValue, function ($a, $b) use ( $sortByKey )
            {
                if ( strtotime( $a[ $sortByKey ] ) == strtotime( $b[ $sortByKey ] ) )
                {
                    return 0;
                }

                return ( strtotime( $a[ $sortByKey ] ) < strtotime( $b[ $sortByKey ] ) ) ? -1 : 1;
            });

            $dates[ $dateKey ] = $timesValue;
        }

        return $dates;
    }

    private function isDateBasedService(): bool
    {
        return $this->getServiceInf()->duration >= 24 * 60;
    }

    private function getTimeSlotLength()
    {
        if( $this->getServiceInf()->timeslot_length == 0 )
        {
            $slot_length_as_service_duration = Helper::getOption('slot_length_as_service_duration', '0');

            $timeslotLength = $slot_length_as_service_duration ? $this->getServiceInf()->duration : Helper::getOption('timeslot_length', 5);
        }
        else if( $this->getServiceInf()->timeslot_length == -1 )
        {
            $timeslotLength = $this->getServiceInf()->duration;
        }
        else
        {
            $timeslotLength = (int)$this->getServiceInf()->timeslot_length;

            $timeslotLength = $timeslotLength > 0 && $timeslotLength <= 300 ? $timeslotLength : 5;
        }

        if( $this->isDateBasedService() && $timeslotLength < 24*60 )
        {
            $timeslotLength = 24*60;
        }

        return $timeslotLength;
    }

    public static function setIncludeCart( bool $includeCart ): void
    {
        self::$includeCart = $includeCart;
    }

    public static function setSkipCurrentRequest( bool $skipCurrentRequest ): void
    {
        self::$skipCurrentRequest = $skipCurrentRequest;
    }

    /**
     * Merges appointments on the cart to the existing staff appointments
     */
    public function mergeCart(): void
    {
        if ( is_null( Request::self() ) )
            return;

        /**
         * If there are no more than 1 appointment on the cart, there is no need to merge them.
         * The purpose of this method is to consider previous appointments on the cart,
         * Ensuring there are no overlaps among them.
         */
        if ( Request::appointmentCount() <= 1 )
            return;

        foreach ( Request::appointments() as $k => $appointment )
        {
            /**
             * The first part of this condition checks whether the request is still on the process of loading or not.
             * The second part ensures that the current request appointment of the cart
             * is not included as a pseudoSlot.
             * The pseudoSlots are needed to fill empty timeslots,
             * Which correspond to the appointment timeslots on the cart.
             * This functionality is added
             * to prevent users from booking for the same timeframe+staff within one booking cart session.
             */
            if ( self::$skipCurrentRequest && Request::self()->currentRequestKey() === $k )
                continue;

            /**
             * Checks whether the cart appointment in question has the same staff id as the current staff of this calendar object
            */
            if ( $appointment->staffId < 0 || $appointment->staffId != $this->getStaffId() )
                continue;

            foreach ( $appointment->getAllTimeslots() as $timeslot )
            {
                /**
                 * add or merge $timeslot into $appointments
                 */
                $merged = false;

                foreach ( $this->appointments as $a )
                {
                    if (
                        $a->starts_at == $timeslot->getTimestamp() &&
                        $a->service_id == $timeslot->getServiceId() &&
                        $a->location_id == $timeslot->getLocationId()
                    )
                    {
                        $a->total_weight += $appointment->weight;
                        $merged = true;
                        break;
                    }
                }

                if ( $merged )
                    continue;

                /**
                 * At this point,
                 * A pseudo appointment slot is created from a cart appointment
                 * To imitate actual appointment,
                 * Therefore making the cart appointment's selected timeslot
                 * Busy for the latter appointments in the same cart
                */
                $pseudoSlot = new Appointment();

                $pseudoSlot->staff_id     = $timeslot->getStaffId();
                $pseudoSlot->location_id  = $timeslot->getLocationId();
                $pseudoSlot->service_id   = $timeslot->getServiceId();
                $pseudoSlot->starts_at    = $timeslot->getTimestamp();
                $pseudoSlot->ends_at      = $timeslot->getTimestamp() + ( $appointment->serviceInf->duration + (int) $appointment->getExtrasDuration()) * 60;
                $pseudoSlot->busy_from    = $timeslot->getTimestamp() - ( $appointment->serviceInf->buffer_before) * 60;
                $pseudoSlot->busy_to      = $timeslot->getTimestamp() + ( $appointment->serviceInf->duration + (int) $appointment->getExtrasDuration() + $appointment->serviceInf->buffer_after ) * 60;
                $pseudoSlot->total_weight = $appointment->weight;

                /**
                 * An indicator of the pseudoSlot
                */
                $pseudoSlot->pseudoSlot = true;

                $this->appointments[] = $pseudoSlot;
            }
        }
    }
}