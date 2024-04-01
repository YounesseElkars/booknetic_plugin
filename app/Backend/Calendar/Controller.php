<?php

namespace BookneticApp\Backend\Calendar;

use BookneticApp\Models\Location;
use BookneticApp\Models\Service;
use BookneticApp\Models\Staff;
use BookneticApp\Providers\Core\Capabilities;
use BookneticApp\Providers\Helpers\Helper;

class Controller extends \BookneticApp\Providers\Core\Controller
{

	public function index()
	{
		Capabilities::must( 'calendar' );

		$locations	= Location::fetchAll();
		$services	= Service::fetchAll();
		$staff		= Staff::fetchAll();
		$payments   = Helper::getPaymentStatuses();
		$statuses   = Helper::getAppointmentStatuses();

		$this->view( 'index' , [
			'locations'	=> $locations,
			'services'	=> $services,
			'staff'		=> $staff,
			'statuses'  => $statuses,
			'payments'  => $payments
		] );
	}

}
