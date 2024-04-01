<?php

namespace BookneticApp\Models;

use BookneticApp\Models\Staff;
use BookneticApp\Models\Service;
use BookneticApp\Providers\DB\Model;
use BookneticApp\Providers\Translation\Translator;

class ServiceStaff extends Model
{

    use Translator;

    protected static $translations = [ 'name', 'profession', 'about' ];

	protected static $tableName = 'service_staff';

	public static $relations = [
		'service'   =>  [ Service::class, 'id', 'service_id' ],
		'staff'     =>  [ Staff::class, 'id', 'staff_id' ]
	];

}
