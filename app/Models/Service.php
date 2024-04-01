<?php

namespace BookneticApp\Models;

use BookneticApp\Providers\DB\Model;
use BookneticApp\Providers\DB\MultiTenant;
use BookneticApp\Providers\Translation\Translator;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read float $price
 * @property-read int $category_id
 * @property-read int $is_visible
 * @property-read int $duration
 * @property-read int $timeslot_length
 * @property-read int $buffer_before
 * @property-read int $buffer_after
 * @property-read string $notes
 * @property-read string $image
 * @property-read int $is_recurring
 * @property-read string $full_period_type
 * @property-read int $full_period_value
 * @property-read string $repeat_type
 * @property-read string $recurring_payment_type
 * @property-read int $repeat_frequency
 * @property-read int $max_capacity
 * @property-read string $color
 * @property-read string $deposit_type
 * @property-read float $deposit
 * @property-read int $is_active
 * @property-read int $hide_price
 * @property-read int $hide_duration
 * @property-read int $tenant_id
 */
class Service extends Model
{
    use MultiTenant{
        booted as private tenantBoot;
    }
	use Translator;

    protected static $translations = [ 'name', 'note' ];

	public static $relations = [
		'staff'		=>	[ ServiceStaff::class ],
		'extras'	=>	[ ServiceExtra::class ],
		'category'	=>	[ ServiceCategory::class, 'id', 'category_id' ]
	];


    public static function booted()
    {
        self::tenantBoot();

        do_action( 'bkntc_service_model_scopes', self::class );
    }
}