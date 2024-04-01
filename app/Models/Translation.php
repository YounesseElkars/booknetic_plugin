<?php

namespace BookneticApp\Models;

use BookneticApp\Providers\DB\Model;
use BookneticApp\Providers\DB\MultiTenant;

class Translation extends Model
{
    use MultiTenant;
    protected static $tableName = "translations";

}