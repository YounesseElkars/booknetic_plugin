<?php

namespace BookneticApp\Backend\Payments;

use BookneticApp\Backend\Appointments\Helpers\AppointmentSmartObject;
use BookneticApp\Models\Appointment;
use BookneticApp\Models\AppointmentPrice;
use BookneticApp\Models\Customer;
use BookneticApp\Models\Location;
use BookneticApp\Models\Service;
use BookneticApp\Models\Staff;
use BookneticApp\Providers\Helpers\Math;
use BookneticApp\Providers\UI\DataTableUI;
use BookneticApp\Providers\Core\Capabilities;
use BookneticApp\Providers\Helpers\Date;
use BookneticApp\Providers\DB\DB;
use BookneticApp\Providers\Helpers\Helper;

class Controller extends \BookneticApp\Providers\Core\Controller
{

	public function index()
	{
		Capabilities::must( 'payments' );

        $totalAmountQuery = AppointmentPrice::where('appointment_id', DB::field(Appointment::getField('id')))
            ->select('sum(price * negative_or_positive)', true);

		$appointments = Appointment::leftJoin('customer', ['first_name', 'last_name', 'email', 'profile_image', 'phone_number'])
                ->leftJoin('staff', ['name', 'profile_image'])
                ->leftJoin('location', ['name'])
                ->leftJoin('service', ['name'])
                ->selectSubQuery($totalAmountQuery, 'total_amount')
                ->select("if(payment_status = 'paid', paid_amount, 0) as real_paid_amount");

		$dataTable = new DataTableUI( $appointments );

        $dataTable->addAction('info', bkntc__('Info'));
        $dataTable->setModule('payments');

		$dataTable->activateExportBtn()
			->setTitle(bkntc__('Payments'))
			->searchBy([ Appointment::getField('id'), Location::getField('name'), Service::getField('name'), Staff::getField('name'), Customer::getField('first_name'), Customer::getField('last_name'), Customer::getField('email'), Customer::getField('phone_number')]);

        $dataTable->addFilter( Appointment::getField('date'), 'date', bkntc__('Date'), function ($val, $query)
        {
            return $query->where('starts_at', '>=', Date::epoch($val))->where('ends_at', '<', Date::epoch($val, '+1 day'));
        });
		$dataTable->addFilter( 'service_id', 'select', bkntc__('Service'), '=', [ 'model' => new Service() ] );
		$dataTable->addFilter( Appointment::getField("customer_id"), 'select', bkntc__('Customer'), '=', [
			'model'			    =>	Customer::my(),
			'name_field'	    =>	'CONCAT(`first_name`, \' \', last_name)'
		] );
		$dataTable->addFilter( 'staff_id', 'select', bkntc__('Staff'), '=', [ 'model' => new Staff() ] );
		$dataTable->addFilter( Appointment::getField('payment_status'), 'select', bkntc__('Status'), '=', [
			'list'	=>	[
				'pending'		=>	bkntc__('Pending'),
				'paid'			=>	bkntc__('Paid'),
                'canceled'		=>	bkntc__('Canceled'),
                'not_paid'		=>	bkntc__('Not paid'),
			]
		] );

		$dataTable->addColumns(bkntc__('ID'), 'id');
		$dataTable->addColumns(bkntc__('APPOINTMENT DATE'), function( $row )
		{
			if( ($row->ends_at - $row->starts_at) >= 24 * 60 * 60 )
			{
				return Date::datee( $row['starts_at'] );
			}

			return Date::dateTime( $row['starts_at'] );
		}, ['order_by_field' => 'starts_at']);
		$dataTable->addColumns(bkntc__('CUSTOMER'), function( $appointment )
		{
			return Helper::profileCard( $appointment['customer_first_name'] . ' ' . $appointment['customer_last_name'], $appointment['customer_profile_image'], $appointment['customer_email'], 'Customers' );
		}, ['is_html' => true, 'order_by_field' => 'customer_first_name, customer_last_name'], true);

		$dataTable->addColumnsForExport(bkntc__('Customer'), function( $appointment )
		{
			return $appointment['customer_first_name'] . ' ' . $appointment['customer_last_name'];
		});
		$dataTable->addColumnsForExport(bkntc__('Customer Email'), 'customer_email');
		$dataTable->addColumnsForExport(bkntc__('Customer Phone Number'), 'customer_phone_number');

		$dataTable->addColumns(bkntc__('STAFF'), 'staff_name');
		$dataTable->addColumns(bkntc__('SERVICE'), 'service_name');
		$dataTable->addColumns(bkntc__('METHOD'), function ( $appointment )
		{
			return Helper::paymentMethod( $appointment['payment_method'] );
		}, ['order_by_field' => 'payment_method', 'is_html' => true]);
		$dataTable->addColumns(bkntc__('TOTAL AMOUNT'), function( $appointment )
		{
			return Helper::price($appointment['total_amount']);
		});
		$dataTable->addColumns(bkntc__('PAID AMOUNT'), function( $appointment )
		{
			return Helper::price( $appointment['real_paid_amount'] );
		});
		$dataTable->addColumns(bkntc__('DUE AMOUNT'), function( $appointment )
		{
			return Helper::price( Math::sub($appointment['total_amount'], $appointment['real_paid_amount']) );
		});
		$dataTable->addColumns(bkntc__('STATUS'), function( $appointment )
		{
            $totalAmount = (float) $appointment['total_amount'];
            $paidAmount = (float) $appointment['real_paid_amount'];

			if( $appointment['payment_status'] == 'pending' )
			{
				$statusBtn = '<button type="button" class="btn btn-xs btn-light-warning">'.bkntc__('Pending').'</button>';
			}
			else if( $appointment['payment_status'] == 'paid' )
			{
                if ($paidAmount < $totalAmount)
                {
                    $statusBtn = '<button type="button" class="btn btn-xs btn-light-primary">'.bkntc__('Paid (deposit)').'</button>';
                }
                else
                {
                    $statusBtn = '<button type="button" class="btn btn-xs btn-light-success">'.bkntc__('Paid').'</button>';
                }
			}
			else if ($appointment['payment_status'] == 'canceled')
			{
				$statusBtn = '<button type="button" class="btn btn-xs btn-light-danger">'.bkntc__('Canceled').'</button>';
			}
            else if ($appointment['payment_status'] == 'not_paid')
            {
                $statusBtn = '<button type="button" class="btn btn-xs btn-light-default text-nowrap">'.bkntc__('Not paid').'</button>';
            }

			return $statusBtn;
		}, ['is_html' => true, 'order_by_field' => 'payment_status']);

		$table = $dataTable->renderHTML();

		$this->view( 'index', ['table' => $table] );
	}

}
