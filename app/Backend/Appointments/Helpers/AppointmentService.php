<?php

namespace BookneticApp\Backend\Appointments\Helpers;

use BookneticApp\Models\Appointment;
use BookneticApp\Models\AppointmentPrice;
use BookneticApp\Models\AppointmentExtra;
use BookneticApp\Models\Data;
use BookneticApp\Models\Service;
use BookneticApp\Providers\Helpers\Date;
use BookneticApp\Providers\DB\DB;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Helpers\Math;
use Exception;

class AppointmentService
{
	use RecurringAppointmentService;

    /**
     * @throws Exception
     */
    public static function createAppointment()
	{
        $paymentId = md5( uniqid() );
        $clientTz  = self::getClientTimezone();

        AppointmentRequests::self()->setPaymentId( $paymentId );

        foreach ( AppointmentRequests::appointments() as $appointmentData )
        {
            self::createSingle( $appointmentData, $paymentId, $clientTz );
        }
	}

    private static function createSingle( AppointmentRequestData $appointmentData, $pmId, $clientTz )
    {
        $recurringId = $appointmentData->isRecurring() ? md5(uniqid()) : null;

        $payableSlotsCount = $appointmentData->getPayableAppointmentsCount();

        foreach ($appointmentData->getAllTimeslots() AS $appointment )
        {
            $paidAmount = $payableSlotsCount > 0 ? $appointmentData->getPayableToday() : 0;
            $paymentMethod = $payableSlotsCount > 0 ? $appointmentData->paymentMethod : 'local';
            $paymentStatus = $paymentMethod == 'local' ? 'not_paid' : 'pending';

            $appointmentInsertData = apply_filters( 'bkntc_appointment_insert_data', [
                'location_id'				=>	$appointmentData->locationId,
                'service_id'				=>	$appointmentData->serviceId,
                'staff_id'					=>	$appointmentData->staffId,
                'customer_id'               =>  $appointmentData->customerId,
                'status'                    =>  $appointmentData->status,
                'starts_at'                 =>  $appointment->getTimestamp(),
                'ends_at'                   =>  $appointment->getTimestamp() + ((int) $appointmentData->serviceInf->duration + (int) $appointmentData->getExtrasDuration()) * 60,
                'busy_from'                 =>  $appointment->getTimestamp() - ((int) $appointmentData->serviceInf->buffer_before) * 60,
                'busy_to'                   =>  $appointment->getTimestamp() + ((int) $appointmentData->serviceInf->duration + (int) $appointmentData->getExtrasDuration() + (int) $appointmentData->serviceInf->buffer_after) * 60,
                'weight'                    =>  $appointmentData->weight,
                'paid_amount'			    =>	$paidAmount,
                'payment_method'		    =>	$paymentMethod,
                'payment_status'		    =>	$paymentStatus,
                'payment_id'                =>  $pmId,
                'recurring_id'              =>  $recurringId,
                'note'                      =>  $appointmentData->note,
                'locale'                    =>  get_locale(),
                'client_timezone'           =>  $clientTz,
                'created_at'                =>  (new \DateTime())->getTimestamp()
            ], $appointmentData );

            $payableSlotsCount--;

            Appointment::insert( $appointmentInsertData );

            $appointmentData->createdAt = $appointmentInsertData["created_at"];

            $appointmentId = DB::lastInsertedId();

            foreach ( $appointmentData->getServiceExtras() AS $extra )
            {
                AppointmentExtra::insert([
                    'appointment_id'        =>  $appointmentId,
                    'extra_id'				=>	$extra['id'],
                    'quantity'				=>	$extra['quantity'],
                    'price'					=>	$extra['price'],
                    'duration'				=>	(int)$extra['duration']
                ]);
            }

            foreach ( $appointmentData->getPrices( true) AS $priceKey => $priceInf )
            {
                AppointmentPrice::insert([
                    'appointment_id'            =>  $appointmentId,
                    'unique_key'                =>  $priceKey,
                    'price'                     =>  Math::abs( $priceInf->getPrice() ),
                    'negative_or_positive'      =>  $priceInf->getNegativeOrPositive()
                ]);
            }

            if( $appointmentData->setBillingData )
            {
                $billingArray = [
                    "customer_first_name" => "",
                    "customer_last_name" => "",
                    "customer_phone" => ""
                ];

                if( ! empty($appointmentData->customerData['first_name']) )
                {
                    $billingArray['customer_first_name'] = $appointmentData->customerData['first_name'];
                }
                if( ! empty($appointmentData->customerData['last_name']) )
                {
                    $billingArray['customer_last_name'] = $appointmentData->customerData['last_name'];
                }
                if( ! empty($appointmentData->customerData['phone']) )
                {
                    $billingArray['customer_phone'] = $appointmentData->customerData['phone'];
                }
                $billingArray = json_encode( $billingArray );
                Appointment::setData( $appointmentId, 'customer_billing_data', $billingArray );

            }

            $appointmentData->createdAppointments[] = $appointmentId;

                $appointmentData->appointmentId = $appointmentId;

                /**
                 * @doc bkntc_appointment_created Action triggered when an appointment created
                 */
                do_action( 'bkntc_appointment_created', $appointmentData );
//                do_action( 'bkntc_appointment_after_mutation', $appointmentId );
        }
    }

	public static function editAppointment( AppointmentRequestData $appointmentObj )
	{
        $timeslot = $appointmentObj->getAllTimeslots()[ 0 ];

        $shouldChangePrices = (bool) $appointmentObj->getData( 'change_prices', 1, 'int', [ 1, 0 ] );

        /*doit add_filter()*/
		$appointmentUpdateData = apply_filters( 'bkntc_appointment_update_data', [
			'location_id' => $appointmentObj->locationId,
			'service_id'  => $appointmentObj->serviceId,
			'staff_id'    => $appointmentObj->staffId,
            'customer_id' => $appointmentObj->customerId,
            'status'      => $appointmentObj->status,
            'weight'      => $appointmentObj->weight,
            'starts_at'   => $timeslot->getTimestamp(),
            'ends_at'     => $timeslot->getTimestamp() + ((int) $appointmentObj->serviceInf->duration + (int) $appointmentObj->getExtrasDuration()) * 60,
            'busy_from'   => $timeslot->getTimestamp() - ((int) $appointmentObj->serviceInf->buffer_before) * 60,
            'busy_to'     => $timeslot->getTimestamp() + ((int) $appointmentObj->serviceInf->duration + (int) $appointmentObj->getExtrasDuration() + (int) $appointmentObj->serviceInf->buffer_after) * 60,
            'note'        => $appointmentObj->note,
        ], $appointmentObj );

		Appointment::where( 'id', $appointmentObj->appointmentId )->update( $appointmentUpdateData );

        if ( ! $shouldChangePrices )
            return;

        AppointmentPrice::where( 'appointment_id', $appointmentObj->appointmentId )->delete();
        AppointmentExtra::where( 'appointment_id', $appointmentObj->appointmentId )->delete();

        foreach ( $appointmentObj->getServiceExtras() as $extra )
        {
            AppointmentExtra::insert( [
                'appointment_id' => $appointmentObj->appointmentId,
                'extra_id'		 =>	$extra[ 'id' ],
                'quantity'		 =>	$extra[ 'quantity' ],
                'price'			 =>	$extra[ 'price' ],
                'duration'		 =>	( int ) $extra[ 'duration' ]
            ] );
        }
        foreach ( $appointmentObj->getPrices( true ) as $priceKey => $priceInf )
        {
            AppointmentPrice::insert( [
                'appointment_id'       =>  $appointmentObj->appointmentId,
                'unique_key'           =>  $priceKey,
                'price'                =>  Math::abs( $priceInf->getPrice() ),
                'negative_or_positive' =>  $priceInf->getNegativeOrPositive()
            ] );
        }

        Appointment::setData( $appointmentObj->appointmentId, 'price_updated', 0 );
    }

	public static function deleteAppointment( $appointmentsIDs )
	{
		$appointmentsIDs = is_array( $appointmentsIDs ) ? $appointmentsIDs : [ $appointmentsIDs ];

		foreach ( $appointmentsIDs as $appointmentId )
		{
            do_action('bkntc_appointment_before_mutation', $appointmentId);
            do_action('bkntc_appointment_after_mutation', null);

		    do_action('bkntc_appointment_deleted', $appointmentId );

            AppointmentExtra::where( 'appointment_id', $appointmentId )->delete();
            AppointmentPrice::where('appointment_id', $appointmentId)->delete();
			Appointment::where('id', $appointmentId)->delete();
		    Data::where('row_id', $appointmentId )->where('table_name', Appointment::getTableName())->delete();
        }
	}

    /**
     * @throws Exception
     */
    public static function reschedule( $appointmentId, $date, $time, $send_notifications = true, $reset_status = true, $staff_changed = false )
	{
        $appointmentInfo			= Appointment::get( $appointmentId );
		$customer_id				= $appointmentInfo->customer_id;

		if( !$appointmentInfo )
		{
			throw new Exception('');
		}

		$serviceInf		= apply_filters( 'bkntc_set_service_duration_frontend', Service::get( $appointmentInfo->service_id ), $appointmentId );

		$staff			= $staff_changed ?: $appointmentInfo->staff_id ;

		$extras_arr = [];
		$appointmentExtras = AppointmentExtra::where('appointment_id', $appointmentId)->fetchAll();

		foreach ( $appointmentExtras AS $extra )
		{
			$extra_inf = $extra->extra()->fetch();
			$extra_inf['quantity'] = $extra['quantity'];
			$extra_inf['customer'] = $customer_id;

			$extras_arr[] = $extra_inf;
		}

		$date = Date::dateSQL( $date );
		$time = Date::timeSQL( $time );

		$selectedTimeSlotInfo = new TimeSlotService( $date, $time );

		$selectedTimeSlotInfo->setStaffId( $staff )
			->setServiceId( $serviceInf->id )
			->setServiceExtras( $extras_arr )
            ->setLocationId( $appointmentInfo->location_id )
			->setExcludeAppointmentId( $appointmentInfo->id )
			->setCalledFromBackEnd( false )
			->setShowExistingTimeSlots( true );

        $selectedTimeSlotInfo = apply_filters('bkntc_selected_time_slot_info' , $selectedTimeSlotInfo);

        $selectedTimeSlotInfo->setCalledFromBackEnd( true );

		if( ! $selectedTimeSlotInfo->isBookable() )
		{
			throw new Exception( bkntc__('Please select a valid time! ( %s %s is busy! )', [$date, $time]) );
		}

		$appointmentStatus = $reset_status ? Helper::getDefaultAppointmentStatus() : $appointmentInfo->status;

        $duration = ($serviceInf->duration + ExtrasService::calcExtrasDuration( $extras_arr )) * 60;

        if ( $send_notifications )
            do_action('bkntc_appointment_before_mutation', $appointmentId);

        $updateData = [
            'status'     =>  $appointmentStatus,
            'staff_id'   =>  $staff,
            'starts_at'  =>  $selectedTimeSlotInfo->getTimestamp(),
            'ends_at'    =>  $selectedTimeSlotInfo->getTimestamp() + $duration,
            'busy_from'  =>  $selectedTimeSlotInfo->getTimestamp() + (int) $serviceInf->buffer_before * 60,
            'busy_to'    =>  $selectedTimeSlotInfo->getTimestamp() + $duration + (int) $serviceInf->buffer_after * 60,
        ];
        $updateData = apply_filters( 'bkntc_appointment_reschedule', $updateData );

        do_action( 'bkntc_validate_appointment_reschedule', [
            'appointmentInfo' => $appointmentInfo,
            'staffId' => $staff,
            'starts_at' => $selectedTimeSlotInfo->getTimestamp()
        ] );

        Appointment::where( 'id', $appointmentId )->update($updateData);

        if ( $send_notifications )
            do_action('bkntc_appointment_after_mutation', $appointmentId);

        return [
            'appointment_status' => $appointmentStatus
        ];
	}

	public static function setStatus( $appointmentId, $status )
	{
        $appointmentInf = Appointment::get( $appointmentId );

        if ( empty($appointmentInf) || $appointmentInf->status == $status )
            return true;

        do_action('bkntc_appointment_before_mutation', $appointmentId);

		Appointment::where('id', $appointmentId)->update([
			'status'	=>	$status
		]);

        do_action('bkntc_appointment_after_mutation', $appointmentId);

		return true;
	}

	/**
	 * Mushterilere odenish etmeleri uchun 10 deqiqe vaxt verilir.
	 * 10 deqiqe erzinde sechdiyi timeslot busy olacaq ki, odenish zamani diger mushteri bu timeslotu seche bilmesin.
	 * Eger 10 deqiqeden chox kechib ve odenish helede olunmayibsa o zaman avtomatik bu appointmente cancel statusu verir.
	 */
	public static function cancelUnpaidAppointments()
	{
        $failedStatus = Helper::getOption('failed_payment_status');
        if (empty($failedStatus))
            return;

		$timeLimit          = Helper::getOption( 'max_time_limit_for_payment', '10' );
		$compareTimestamp   = Date::epoch('-' . $timeLimit . ' minutes');

        $getAppointmentsList = Appointment::where('payment_method', '<>', 'local')
            ->where('payment_status', 'pending')
            ->where('created_at', '<', $compareTimestamp)
            ->fetchAll();

        foreach ( $getAppointmentsList AS $appointmentInf )
        {
            AppointmentService::setStatus( $appointmentInf->id, $failedStatus );
        }
	}

    private static function getClientTimezone()
    {
        $requests = AppointmentRequests::self();

        if( ! $requests->calledFromBackend )
            return $requests->currentRequest()->clientTimezone;

        $appointment = Appointment::where( 'customer_id', $requests->currentRequest()->customerId )
            ->where( 'client_timezone', '<>', '-' )
            ->select( [ 'client_timezone' ] )
            ->fetch();

        if ( empty( $appointment ) )
            return '-';

        return $appointment->client_timezone;
    }

}