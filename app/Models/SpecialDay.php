<?php

namespace BookneticApp\Models;

use BookneticApp\Models\Location;
use BookneticApp\Models\Service;
use BookneticApp\Models\Staff;
use BookneticApp\Providers\DB\Model;
use BookneticApp\Providers\DB\MultiTenant;

class SpecialDay extends Model
{
	use MultiTenant;

	public static $relations = [
		'service'       => [ Service::class, 'id', 'service_id' ],
		'staff'         => [ Staff::class, 'id', 'staff_id' ]
	];

}
