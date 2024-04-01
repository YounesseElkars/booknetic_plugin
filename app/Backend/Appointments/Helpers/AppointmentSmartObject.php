<?php

namespace BookneticApp\Backend\Appointments\Helpers;

use BookneticApp\Models\Appointment;
use BookneticApp\Models\AppointmentPrice;
use BookneticApp\Models\Customer;
use BookneticApp\Models\Location;
use BookneticApp\Models\Service;
use BookneticApp\Models\ServiceCategory;
use BookneticApp\Models\Staff;
use BookneticApp\Providers\DB\Collection;
use BookneticApp\Providers\Helpers\Date;
use BookneticApp\Providers\Helpers\Math;

class AppointmentSmartObject
{

	private $appointmentId;

	/**
	 * @var Appointment
	 */
	private $appointmentInf;

	/**
	 * @var Staff
	 */
	private $staffInf;

	/**
	 * @var Location
	 */
	private $locationInf;

	/**
	 * @var Service
	 */
	private $serviceInf;

	/**
	 * @var ServiceCategory
	 */
	private $serviceCategoryInf;

	/**
	 * @var Customer
	 */
	private $customerInf;

	/**
	 * @var AppointmentPrice[]
	 */
	private $prices;

    /**
     * @var bool
     */
    private $noTenant;

    public function __construct( $appointmentId, $noTenant = false )
	{
		$this->appointmentId = $appointmentId;
        $this->noTenant = $noTenant;
	}

	/**
	 * Appointment`in movcudlugunu ve
	 * Appointment`e permissionun movcudlugunu validate edir.
	 * Permission classindan avtomatik olaraq Appointment modeline staff_id`e gore filter elave edilir.
	 * Bu filterin admine aidiyyati yoxdu. Yalniz staff`lar uchundur ki, bir staff ancaq oz appointmentlerini gore bilsin.
	 *
	 * @return bool
	 */
	public function validate()
	{
		return $this->getInfo() ? true : false;
	}

	public static function load( $appointmentId, $noTenant = false )
	{
        return new AppointmentSmartObject( $appointmentId, $noTenant );
	}

	public function getInfo()
	{
		if( is_null( $this->appointmentInf ) )
		{
			$this->appointmentInf = Appointment::where('id', $this->getId())->noTenant($this->noTenant)->fetch();
		}

		return $this->appointmentInf;
	}

    public function addLocaleFilter()
    {
        add_filter( 'locale', function( $locale )
        {
            $loc = $this->appointmentInf->locale;

            if ( !! $loc )
                return $loc;

            return $locale;
        } );
    }
	public function getId()
	{
		return $this->appointmentId;
	}

	/**
	 * @deprecated
	 * */
	public function getAppointmentInfo()
	{
		return $this->getInfo();
	}

	public function getStaffInf()
	{
		if( is_null( $this->staffInf ) )
		{
			$this->staffInf = $this->getAppointmentInfo() ? $this->getAppointmentInfo()->staff()->noTenant($this->noTenant)->fetch() : false;
		}

		return $this->staffInf;
	}

	public function getServiceInf()
	{
		if( is_null( $this->serviceInf ) )
		{
			$this->serviceInf = $this->getAppointmentInfo() ? $this->getAppointmentInfo()->service()->noTenant($this->noTenant)->fetch() : false;
		}

		return $this->serviceInf;
	}

	public function getServiceCategoryInf()
	{
		if( is_null( $this->serviceCategoryInf ) )
		{
			$this->serviceCategoryInf = $this->getServiceInf() ? $this->getServiceInf()->category()->noTenant($this->noTenant)->fetch() : false;
		}

		return $this->serviceCategoryInf;
	}

	public function getLocationInf()
	{
		if( is_null( $this->locationInf ) )
		{
			$this->locationInf = $this->getAppointmentInfo() ? $this->getAppointmentInfo()->location()->noTenant($this->noTenant)->fetch() : false;
		}

		return $this->locationInf;
	}

	public function getCustomerInf()
	{
		if( is_null( $this->customerInf ) )
		{
			$this->customerInf = $this->getInfo() ? $this->getInfo()->customer()->noTenant($this->noTenant)->fetch() : false;
		}

		return $this->customerInf;
	}


	public function getPrices()
	{
		if( is_null( $this->prices ) )
		{
			$this->prices = $this->getInfo() ? $this->getInfo()->prices()->noTenant($this->noTenant)->fetchAll() : [];
		}

		return $this->prices;
	}

	public function getPrice( $uniqueKey )
	{
        $prices = $this->getPrices();

		foreach ( $prices AS $priceInf )
		{
			if( $uniqueKey == $priceInf->unique_key )
				return $priceInf;
		}

		return new Collection();
	}

	public function getTotalAmount( $sumForAllRecurringAppointments = false )
	{
		$subTotal = 0;

        if ( $sumForAllRecurringAppointments )
        {
            $appointmentIds = $this->getAllRecurringAppointmentIds();
        }
        else
        {
            $appointmentIds = [ $this->getId() ];
        }

        foreach ( $appointmentIds as $appointmentId )
        {
            $appointmentSmartObject = AppointmentSmartObject::load( $appointmentId, $this->noTenant );

            foreach ( $appointmentSmartObject->getPrices() AS $priceInf )
            {
                $subTotal += $priceInf->price * $priceInf->negative_or_positive;
            }
        }

		return Math::floor( $subTotal );
	}

    /**
     * @returns string
     */
    public function getRecurringDateAndTimes( $withTime = false, $modify = false, $client_timezone = null, $saved_timezone = '-') {
        if( $this->getServiceInf()->is_recurring )
        {
            $appointments = Appointment::where( 'recurring_id', $this->appointmentInf->recurring_id )->fetchAll();
        } else {
            $appointments = Appointment::where( 'id', $this->appointmentId )->fetchAll();
        }

        $datesStr = "";
        if ( $withTime ) {
            foreach ( $appointments as $appointment ) {
                $datesStr .= Date::dateTime($appointment->starts_at, $modify, $client_timezone, $saved_timezone) . "<br>";
            }
        } else {
            foreach ( $appointments as $appointment ) {
                $datesStr .= Date::datee( $appointment->starts_at, $modify, $client_timezone, $saved_timezone ) . "<br>";
            }
        }

        return $datesStr;
    }

	public function getPaidAmount()
	{
		return Math::floor( $this->getInfo()->paid_amount );
	}

    public function getRealPaidAmount()
    {
        return Math::floor( $this->getInfo()->payment_status == 'paid' ? $this->getInfo()->paid_amount : 0 );
    }

	public function getDueAmount()
	{
		return Math::floor( $this->getTotalAmount() - $this->getRealPaidAmount() );
	}

    public function getAllRecurringAppointmentIds()
    {
        if( ! $this->getServiceInf()->is_recurring )
            return [ $this->getInfo()->id ];

        $appointments   = Appointment::where( 'recurring_id', $this->getInfo()->recurring_id )
            ->select([Appointment::getField('id')], true)
            ->fetchAll();
        $idList         = [];

        foreach ( $appointments AS $appointment )
        {
            $idList[] = $appointment->id;
        }

        return $idList;
    }


}