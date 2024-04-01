<?php

namespace BookneticApp\Backend\Appointments;

use BookneticApp\Backend\Appointments\Helpers\AppointmentService;
use BookneticApp\Models\Appointment;
use BookneticApp\Models\AppointmentExtra;
use BookneticApp\Models\AppointmentPrice;
use BookneticApp\Models\Customer;
use BookneticApp\Models\Location;
use BookneticApp\Models\Service;
use BookneticApp\Models\ServiceExtra;
use BookneticApp\Models\Staff;
use BookneticApp\Providers\Core\Permission;
use BookneticApp\Providers\UI\DataTableUI;
use BookneticApp\Providers\Core\Capabilities;
use BookneticApp\Providers\Helpers\Date;
use BookneticApp\Providers\DB\DB;
use BookneticApp\Providers\Helpers\Helper;

class Controller extends \BookneticApp\Providers\Core\Controller
{

	public function index()
	{
		Capabilities::must( 'appointments' );

        $appointmentStatuses = Helper::getAppointmentStatuses();

        $totalAmountQuery = AppointmentPrice::where('appointment_id', DB::field( Appointment::getField('id') ))
            ->select('sum(price * negative_or_positive)', true);

        $appointments = Appointment::leftJoin('customer', ['first_name', 'last_name', 'email', 'profile_image', 'phone_number'])
            ->leftJoin('staff', ['name', 'profile_image'])
            ->leftJoin('location', ['name'])
            ->leftJoin('service', ['name'])
            ->leftJoin( AppointmentExtra::class, [ 'quantity', 'price', 'duration', 'extra_id' ], AppointmentExtra::getField('appointment_id'), Appointment::getField('id'), true )->groupBy([ 'id' ])
            ->leftJoin( ServiceExtra::class, [ 'name' ], ServiceExtra::getField('id'), AppointmentExtra::getField('extra_id'), true )
            ->selectSubQuery( $totalAmountQuery, 'total_price' );

		$dataTable = new DataTableUI( $appointments );

		$dataTable->activateExportBtn();
        $dataTable->setModule('appointments');

		$dataTable->addFilter( Appointment::getField('date'), 'date', bkntc__('Date'), function ($val, $query)
        {
            return $query->where('starts_at', '<=', Date::epoch($val, '+1 day'))->where('ends_at', '>=', Date::epoch($val));
        });
		$dataTable->addFilter( Service::getField('id'), 'select', bkntc__('Service'), '=', [ 'model' => new Service() ] );
		$dataTable->addFilter( Customer::getField('id'), 'select', bkntc__('Customer'), '=', [
			'model'			    =>	Customer::my(),
			'name_field'	    =>	'CONCAT(`first_name`, \' \', last_name)'
		] );



		$dataTable->addFilter( Staff::getField('id'), 'select', bkntc__('Staff'), '=', [ 'model' => new Staff() ] );

        $statusFilter = [];
        foreach ($appointmentStatuses as $k => $v)
        {
            $statusFilter[$k] = $v['title'];
        }
        $dataTable->addFilter( Appointment::getField('status'), 'select', bkntc__('Status'), '=', [
			'list'	=>	$statusFilter
		], 1 );

        $dataTable->addFilter( null, 'select', bkntc__('Filter'), function ($val, $query)
        {
            switch ($val) {
                case 0:
                    return $query->where(Appointment::getField('ends_at'), '<', Date::epoch());
                case 1:
                    return $query->where(Appointment::getField('starts_at'), '>', Date::epoch());
                default:
                    return $query;
            }
        }, [
            'list'	=>	[ 0 => bkntc__('Finished'), 1 => bkntc__('Upcoming') ]
        ], 1 );

        $dataTable->addAction('info', bkntc__('Info'));
        $dataTable->addAction('edit', bkntc__('Edit'));
        $dataTable->addAction('delete', bkntc__('Delete'), [static::class , '_delete'], DataTableUI::ACTION_FLAG_SINGLE | DataTableUI::ACTION_FLAG_BULK);

		$dataTable->setTitle(bkntc__('Appointments'));

        if ( Capabilities::userCan( 'appointments_add' ) )
        {
            $dataTable->addNewBtn(bkntc__('NEW APPOINTMENT'));
        }

		$dataTable->searchBy([
			Appointment::getField('id'),
			Location::getField('name'),
			Service::getField('name'),
			Staff::getField('name'),
			'CONCAT(' . Customer::getField('first_name') . ", ' ', " . Customer::getField('last_name') . ')',
			Customer::getField('email'),
			Customer::getField('phone_number'),
            ServiceExtra::getField( 'name' )
		]);

		$dataTable->addColumns(bkntc__('ID'), 'id');

		$dataTable->addColumns(bkntc__('START DATE'), function( $row )
		{
			if( $row['ends_at'] - $row['starts_at'] >= 24 * 60 * 60 )
			{
				return Date::datee( $row['starts_at'] );
			}
			else
			{
				return Date::dateTime( $row['starts_at'] );
			}
		}, ['order_by_field' => 'starts_at']);

		$dataTable->addColumns(bkntc__('CUSTOMER'), function( $row ) use ($appointmentStatuses) {

            if (array_key_exists($row['status'], $appointmentStatuses))
            {
                $status = $appointmentStatuses[$row['status']];
                $badge = '<div class="appointment-status-icon ml-3" style="background-color: ' . htmlspecialchars( $status[ 'color' ] ) . '2b">
                                    <i style="color: ' . htmlspecialchars( $status[ 'color' ] ) . '" class="' . htmlspecialchars( $status[ 'icon' ] ) .  '"></i>
                                </div>';
            } else {
                $badge = '<span class="badge badge-dark">' . $row['status']  . '</span>';
            }


            $customerHtml = Helper::profileCard( $row['customer_first_name'] . ' ' . $row['customer_last_name'], $row['customer_profile_image'], $row['customer_email'], 'Customers' ) . $badge;

            return '<div class="d-flex align-items-center justify-content-between">'.$customerHtml.'</div>';
		}, ['is_html' => true, 'order_by_field' => 'customer_first_name'], true);

		$dataTable->addColumnsForExport(bkntc__('Customer'), function( $appointment )
		{
			return $appointment['customer_first_name'] . ' ' . $appointment['customer_last_name'];
		});

        $allExtras = AppointmentExtra::select( 'DISTINCT ' . AppointmentExtra::getField( 'extra_id' ) )
                                     ->leftJoin( ServiceExtra::class, [ 'id', 'name' ], ServiceExtra::getField( 'id' ), AppointmentExtra::getField( 'extra_id' ), true );

        if( Helper::isSaaSVersion() )
        {
            $allExtras = $allExtras->where( ServiceExtra::getField( 'tenant_id' ), Permission::tenantId());
        }

        foreach( $allExtras->fetchAll() AS $appointmentExtra  )
        {
            $dataTable->addColumnsForExport( $appointmentExtra[ 'service_extras_name' ], function ( $appointment ) use ( $appointmentExtra )
            {
                if ( isset( $appointment[ 'appointment_extras_extra_id' ] ) && $appointmentExtra[ 'service_extras_id' ] === $appointment[ 'appointment_extras_extra_id' ] )
                {
                    return sprintf( 'Quantity: %s | Price: %s | Duration: %s', $appointment[ 'appointment_extras_quantity' ], $appointment[ 'appointment_extras_price' ], $appointment[ 'appointment_extras_duration' ] ) ;
                }

                return '-';
            });
        }

		$dataTable->addColumnsForExport(bkntc__('Customer Email'), 'customer_email');
		$dataTable->addColumnsForExport(bkntc__('Customer Phone Number'), 'customer_phone_number');

		$dataTable->addColumns(bkntc__('STAFF'), function($appointment)
		{
			return Helper::profileCard( $appointment['staff_name'], $appointment['staff_profile_image'], '', 'staff' );
		}, ['is_html' => true, 'order_by_field' => 'staff_name']);

		$dataTable->addColumns(bkntc__('SERVICE'), 'service_name');
		$dataTable->addColumns(bkntc__('PAYMENT'), function( $row )
		{
			$badge = ' <img class="invoice-icon" data-load-modal="payments.info" data-parameter-id="' . (int)$row['id'] . '" src="' . Helper::icon('invoice.svg') . '"> ';
			return Helper::price( $row['total_price'] ) . $badge;
		}, ['is_html' => true]);

		$dataTable->addColumns(bkntc__('DURATION'), function( $row )
		{
			return Helper::secFormat( ((int)$row['ends_at'] - (int)$row['starts_at']) );
		}, ['is_html' => true, 'order_by_field' => '( ends_at - starts_at )']);

		$dataTable->addColumns(bkntc__('CREATED AT'), function ($row)
        {
            return Date::dateTime($row['created_at']);
        });

		$dataTable->setRowsPerPage(12);

		$table = $dataTable->renderHTML();

		$this->view( 'index', ['table' => $table] );
	}

	public static function _delete( $deleteIDs )
	{
		Capabilities::must( 'appointments_delete' );

		AppointmentService::deleteAppointment( $deleteIDs );

		return false;
	}

}
