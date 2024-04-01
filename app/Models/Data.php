<?php

namespace BookneticApp\Models;

use BookneticApp\Providers\DB\Model;
use BookneticApp\Providers\DB\MultiTenant;

class Data extends Model
{
    use MultiTenant;

	protected static $tableName = 'data';

	public static $relations = [
		'appointments'          => [ Appointment::class, 'id', 'row_id' ],
		'location'              => [ Location::class, 'id', 'row_id' ],
		'service'               => [ Service::class, 'id', 'row_id' ],
		'staff'                 => [ Staff::class, 'id', 'row_id' ],
		'customers'             => [ Customer::class, 'id', 'row_id' ]
	];
}
