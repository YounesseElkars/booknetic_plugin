<?php

namespace BookneticApp\Models;

use BookneticApp\Providers\DB\Model;
use BookneticApp\Providers\DB\MultiTenant;
use BookneticApp\Providers\Translation\Translator;

class ServiceExtra extends Model
{
    use MultiTenant, Translator;

    protected static $translations = [ 'name', 'notes' ];
}