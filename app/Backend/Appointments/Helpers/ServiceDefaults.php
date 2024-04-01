<?php

namespace BookneticApp\Backend\Appointments\Helpers;

use BookneticApp\Models\Service;
use BookneticApp\Models\Staff;

class ServiceDefaults
{

	protected $staffId;
	protected $serviceId;
	protected $locationId;
	protected $serviceExtras;
	protected $serviceInf;
	protected $staffInf;
	protected $locationInf;
	protected $showExistingTimeSlots = true;
	protected $excludeAppointmentId;
	protected $calledFromBackEnd = false;
	protected $totalCustomerCount = 1;
    protected ?bool $showBusySlots = false;
    protected $insideWorkingHouse;


	public function setDefaultsFrom( $instance )
	{
		if( $instance instanceof AppointmentRequestData )
		{
			$this->staffId                  = $instance->staffId;
			$this->serviceId                = $instance->serviceId;
			$this->locationId               = $instance->locationId;
			$this->serviceExtras            = $instance->getServiceExtras();
			$this->serviceInf               = $instance->serviceInf;
			$this->staffInf                 = $instance->staffInf;
			$this->calledFromBackEnd        = $instance->calledFromBackend;
			$this->totalCustomerCount       = $instance->totalCustomerCount;
			$this->excludeAppointmentId     = $instance->isEdit() ? $instance->appointmentId : null;
		}
		else if( $instance instanceof ServiceDefaults )
		{
			$this->staffId                  = $instance->getStaffId();
			$this->serviceId                = $instance->getServiceId();
			$this->locationId               = $instance->getLocationId();
			$this->serviceExtras            = $instance->getServiceExtras();
			$this->serviceInf               = $instance->getServiceInf();
			$this->staffInf                 = $instance->getStaffInf();
			$this->calledFromBackEnd        = $instance->getCalledFromBackEnd();
			$this->totalCustomerCount       = $instance->getTotalCustomerCount();
			$this->excludeAppointmentId     = $instance->getExcludeAppointmentId();
            $this->showBusySlots            = $instance->getShowbusySlots();
            $this->insideWorkingHouse       = $instance->getInsideWorkingHouse();
		}

		return $this;
	}

    public function getInsideWorkingHouse()
    {
        return $this->insideWorkingHouse;
    }

    public function setInsideWorkingHouse( $bool )
    {
        $this->insideWorkingHouse = (bool) $bool;
    }

	public function setStaffId( $staffId )
	{
		$this->staffId = $staffId;

		return $this;
	}

    public function setStaffInf( $staffInf )
    {
        $this->staffInf = $staffInf;

        return $this;
    }

	public function setServiceId( $serviceId )
	{
		$this->serviceId = $serviceId;

		return $this;
	}

    /**
     * @param Service $serviceInf
    */
    public function setServiceInf( $serviceInf )
    {
        $this->serviceInf = $serviceInf;
        $this->serviceId  = $serviceInf->id;

        return $this;
    }

    public function initServiceInf( $serviceId )
    {
        $this->serviceId  = $serviceId;
        $this->serviceInf = Service::get( $serviceId );

        do_action( 'bkntc_service_defaults_serviceInf', $this->serviceInf );

        return $this;
    }

	public function setLocationId( $locationId )
	{
		$this->locationId = $locationId;

		return $this;
	}

    public function setLocationInf( $locationInf )
    {
        $this->locationInf = $locationInf;

        return $this;
    }

	public function setServiceExtras( $serviceExtras )
	{
		$this->serviceExtras = $serviceExtras;

		return $this;
	}

	public function setShowExistingTimeSlots( $bool )
	{
		$this->showExistingTimeSlots = $bool;

		return $this;
	}

	public function setExcludeAppointmentId( $appointmentId )
	{
		$this->excludeAppointmentId = $appointmentId;

		return $this;
	}

    public function setShowBusySlots($bool)
    {
        $this->showBusySlots = $bool;

        return $this;
    }

    public function getShowBusySlots()
    {
        return $this->showBusySlots;
    }

	public function setCalledFromBackEnd( $bool )
	{
		$this->calledFromBackEnd = $bool;

		return $this;
	}

	public function setTotalCustomerCount( $totalCustomerCount )
	{
		$this->totalCustomerCount = $totalCustomerCount;

		return $this;
	}


	public function getStaffId()
	{
		return $this->staffId;
	}

	public function getServiceId()
	{
		return $this->serviceId;
	}

	public function getLocationId()
	{
		return $this->locationId;
	}

	public function getServiceExtras()
	{
		return $this->serviceExtras;
	}

	public function getShowExistingTimeSlots()
	{
		return $this->showExistingTimeSlots;
	}

	public function getExcludeAppointmentId()
	{
		return $this->excludeAppointmentId;
	}

	public function getCalledFromBackEnd()
	{
		return $this->calledFromBackEnd;
	}

	public function getTotalCustomerCount()
	{
		return $this->totalCustomerCount;
	}

	public function getServiceInf()
	{
        /* todo:// Bu shert burdan silinmelidi. getter-in icinde property-ini set etmeyi duz deyil. Doğru olar ki, CalendarService instance-i yarananda  serviceInf set olunduğundan əmin olaq.
         *  tekce serviceId set etmek yerine butov info-nu elave etmek lazimdi.
         */
		if( is_null( $this->serviceInf ) )
		{
			$this->serviceInf = Service::get( $this->serviceId );

            do_action( 'bkntc_service_defaults_serviceInf', $this->serviceInf );
		}

		return $this->serviceInf;
	}

	public function getStaffInf()
	{
		if( is_null( $this->staffInf ) )
		{
			$this->staffInf = Staff::get( $this->staffId );
		}

		return $this->staffInf;
	}

}