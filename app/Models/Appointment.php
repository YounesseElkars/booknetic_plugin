<?php

namespace BookneticApp\Models;

use BookneticApp\Providers\Core\Permission;
use BookneticApp\Providers\DB\MultiTenant;
use BookneticApp\Providers\DB\QueryBuilder;
use BookneticApp\Providers\DB\Model;
use BookneticApp\Providers\Helpers\Helper;

/**
 * @property-read   int     $id
 * @property        int     $location_id
 * @property        int     $service_id
 * @property        int     $staff_id
 * @property        int     $starts_at
 * @property        int     $ends_at
 * @property        int     $busy_from
 * @property        int     $busy_to
 * @property        int     $customer_id
 * @property        string  $status
 * @property        int     $weight
 * @property        string  $payment_id
 * @property        string  $recurring_id
 * @property        string  $payment_method
 * @property        string  $payment_status
 * @property        float   $paid_amount
 * @property        string  $note
 * @property        string  $locale
 * @property        string  $client_timezone
 * @property        int     $created_at
 * @property        int     $tenant_id
 */
class Appointment extends Model
{

	use MultiTenant {
		booted as private tenantBoot;
	}

	public static $relations = [
		'extras'                => [ AppointmentExtra::class ],
		'location'              => [ Location::class, 'id', 'location_id' ],
		'service'               => [ Service::class, 'id', 'service_id' ],
		'staff'                 => [ Staff::class, 'id', 'staff_id' ],
        'customer'              => [ Customer::class, 'id', 'customer_id' ],
        'prices'                => [ AppointmentPrice::class, 'appointment_id', 'id' ]
	];

	public static function booted()
	{
		self::tenantBoot();

		self::addGlobalScope('staff_id', function ( QueryBuilder $builder, $queryType )
		{
			if( ! Permission::isBackEnd() || Permission::isAdministrator() )
				return;

            if( apply_filters('bkntc_query_builder_global_scope',false,'appointments') )
                return;

			$builder->where('staff_id', Permission::myStaffId());
		});
	}

    public static function getStatusNameAttribute( $appointmentInf )
    {
        $statuses = Helper::getAppointmentStatuses();

        if ( array_key_exists( $appointmentInf->status, $statuses ) )
        {
            return $statuses[$appointmentInf->status]['title'];
        }

        return $appointmentInf->status;
    }

    public static function getStatusColorAttribute( $appointmentInf )
    {
        $statuses = Helper::getAppointmentStatuses();

        if ( array_key_exists( $appointmentInf->status, $statuses ) )
        {
            return $statuses[$appointmentInf->status]['color'];
        }

        return '#000';
    }

}
