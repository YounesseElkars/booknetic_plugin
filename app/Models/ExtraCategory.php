<?php

namespace BookneticApp\Models;

use BookneticApp\Providers\DB\Model;
use BookneticApp\Providers\DB\MultiTenant;
use BookneticApp\Providers\Translation\Translator;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read int $parent_id
 * @property-read int $tenant_id
 */
class ExtraCategory extends Model
{
    use MultiTenant;

    protected static $tableName = 'service_extra_categories';

//    protected static $translations = [ 'name' ];
}