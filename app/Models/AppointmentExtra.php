<?php

namespace BookneticApp\Models;

use BookneticApp\Providers\DB\Model;

class AppointmentExtra extends Model
{

	public static $relations = [
		'customer'  =>  [ Customer::class ],
		'extra'     =>  [ ServiceExtra::class, 'id', 'extra_id' ]
	];

}
