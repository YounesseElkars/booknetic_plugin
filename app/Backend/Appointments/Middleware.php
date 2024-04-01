<?php

namespace BookneticApp\Backend\Appointments;

use BookneticApp\Backend\Appointments\Helpers\AppointmentService;
use BookneticApp\Providers\Core\Capabilities;

class Middleware
{

	public function handle( $next )
	{
		AppointmentService::cancelUnpaidAppointments();

		return $next();
	}

}