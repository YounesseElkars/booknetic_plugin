<?php

namespace BookneticApp\Models;

use BookneticApp\Providers\DB\Model;
use BookneticApp\Providers\DB\MultiTenant;

class Appearance extends Model
{
	use MultiTenant;

	protected static $tableName = 'appearance';

}
