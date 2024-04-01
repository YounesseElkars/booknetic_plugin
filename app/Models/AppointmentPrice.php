<?php

namespace BookneticApp\Models;

use BookneticApp\Models\Customer;
use BookneticApp\Providers\DB\Model;

/**
 * @property-read int $id
 * @property-read int $appointment_id
 * @property-read string $unique_key
 * @property-read float $price
 * @property-read int $negative_or_positive
 * @property-read string $name
 */
class AppointmentPrice extends Model
{

	public static $relations = [

	];

	/**
	 * @param self $price
	 *
	 * @return string
	 */
	public function getNameAttribute( $price )
	{
        return apply_filters('bkntc_price_name', $price->unique_key);
	}

}
