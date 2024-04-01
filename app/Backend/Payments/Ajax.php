<?php

namespace BookneticApp\Backend\Payments;

use BookneticApp\Backend\Appointments\Helpers\AppointmentSmartObject;
use BookneticApp\Config;
use BookneticApp\Models\Appointment;
use BookneticApp\Models\AppointmentPrice;
use BookneticApp\Providers\Core\Backend;
use BookneticApp\Providers\Core\Capabilities;
use BookneticApp\Providers\DB\DB;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Helpers\Math;
use BookneticApp\Providers\Core\Permission;

class Ajax extends \BookneticApp\Providers\Core\Controller
{

	public function info()
	{
		Capabilities::must( 'payments' );

		$id     = Helper::_post('id', '0', 'integer');
		$info   = AppointmentSmartObject::load( $id );

		if( ! $info->validate() )
		{
			return $this->response(false, bkntc__('Appointment not found or permission denied!'));
		}

		return $this->modalView( 'info', [ 'info' => $info ] );
	}

	public function edit_payment()
	{
		Capabilities::must( 'payments_edit' );

		$paymentId		=	Helper::_post('payment', '0', 'integer');
		$mn2			=	Helper::_post('mn2', '0', 'integer');
		$info	        =   AppointmentSmartObject::load( $paymentId );

		if( ! $info->validate() )
		{
			return $this->response(false, bkntc__('Payment not found or permission denied!'));
		}

		return $this->modalView( 'edit_payment', [
			'payment'	=>	$info,
			'mn2'		=>	$mn2
		] );
	}

	public function save_payment()
	{
		Capabilities::must( 'payments_edit' );

        $isUpdated = false;

		$paymentId		= Helper::_post('id', 0, 'integer');
		$prices	        = Helper::_post('prices', null, 'string');
		$paid_amount	= Helper::_post('paid_amount', null, 'float');
		$status			= Helper::_post('status', null, 'string', ['paid', 'canceled', 'pending', 'not_paid']);

		$prices         = json_decode( $prices, true );

		if( $paymentId <= 0 || ! is_array( $prices ) || empty( $prices ) || is_null( $paid_amount ) || is_null( $status ) )
		{
			return $this->response( false );
		}

		$info = AppointmentSmartObject::load( $paymentId );

		if( ! $info->validate() )
		{
			return $this->response(false, bkntc__('Payment not found or permission denied!'));
		}

		if( count( $info->getPrices() ) != count( $prices ) )
		{
			return $this->response( false );
		}

		foreach ( $prices AS $priceUniqueKey => $priceValue )
		{
			if( ! $info->getPrice( $priceUniqueKey ) || ! is_numeric( $priceValue ) || $priceValue < 0 )
			{
				return $this->response( false );
			}

            if ( Math::floor( $priceValue ) !== Math::floor( $info->getPrice( $priceUniqueKey )->price ) )
            {
                $isUpdated = true;
            }
		}

        if ( $isUpdated )
        {
            Appointment::setData($info->getId(), 'price_updated', 1);
        }

        foreach ( $prices AS $priceUniqueKey => $priceValue )
        {
            AppointmentPrice::where('appointment_id', $paymentId)
				->where('unique_key', $priceUniqueKey)
				->update([
					'price' =>  Math::floor( $priceValue )
				]);
		}

		Appointment::where('id', $paymentId)->update([
			'paid_amount'		=>	$paid_amount,
			'payment_status'	=>	$status
		]);

		return $this->response(true, [ 'id' => $paymentId ]);
	}

	public function complete_payment()
	{
		Capabilities::must( 'payments_edit' );

		$id     = Helper::_post('id', '0', 'integer');
		$info   = AppointmentSmartObject::load( $id );

		if( ! $info->validate() )
		{
			return $this->response( false, bkntc__('Appointment not found or permission denied!') );
		}

        $appointment = Appointment::where( 'id', $id );

		$appointment->update([
			'payment_status'    =>  'paid',
			'paid_amount'       =>  $info->getTotalAmount()
		]);

        $appointment = $appointment->fetch();

        do_action( 'bkntc_payment_confirmed_backend', $appointment->id );

        Config::getWorkflowEventsManager()->trigger( 'appointment_paid', [
            'appointment_id' => $appointment->id,
            'location_id' => $appointment->location_id,
            'service_id' => $appointment->service_id,
            'staff_id' => $appointment->staff_id,
            'customer_id' => $appointment->customer_id
        ] );

		return $this->response( true );
	}

}
