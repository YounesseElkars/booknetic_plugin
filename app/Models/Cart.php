<?php

namespace BookneticApp\Models;

use BookneticApp\Providers\DB\Model;

/**
 * @property-read int $id
 * @property string $slug
 * @property bool $active
 * @property int $created_at
 * @property int $removed_at
 */
class Cart extends Model
{
    protected static $tableName = 'cart';
}
