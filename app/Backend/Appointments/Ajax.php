<?php

namespace BookneticApp\Backend\Appointments;

use BookneticApp\Backend\Appointments\Helpers\AppointmentRequests;
use BookneticApp\Config;
use BookneticApp\Models\AppointmentPrice;
use BookneticApp\Models\Service;
use BookneticApp\Providers\Common\PaymentGatewayService;
use BookneticApp\Providers\Core\CapabilitiesException;
use BookneticApp\Providers\UI\TabUI;
use BookneticApp\Backend\Appointments\Helpers\AppointmentSmartObject;
use BookneticApp\Backend\Appointments\Helpers\AppointmentService;
use BookneticApp\Backend\Appointments\Helpers\CalendarService;
use BookneticApp\Models\Appointment;
use BookneticApp\Models\AppointmentExtra;
use BookneticApp\Models\Customer;
use BookneticApp\Models\Location;
use BookneticApp\Models\ServiceCategory;
use BookneticApp\Models\ServiceExtra;
use BookneticApp\Models\ServiceStaff;
use BookneticApp\Models\Staff;
use BookneticApp\Providers\Core\Capabilities;
use BookneticApp\Providers\Helpers\Date;
use BookneticApp\Providers\DB\DB;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Core\Permission;

class Ajax extends \BookneticApp\Providers\Core\Controller
{

	public function add_new()
	{
		Capabilities::must( 'appointments_add' );

		$date           = Helper::_post('date', '', 'string');
		$locations      = Location::where('is_active', 1)->fetchAll();
		$locationInf    = count( $locations ) == 1 ? $locations[0] : false;

        TabUI::get( 'appointments_add_new' )
             ->item( 'details' )
             ->setTitle( bkntc__( 'Appointment details' ) )
             ->addView( __DIR__ . '/view/tab/details.php' )
             ->setPriority( 1 );

        TabUI::get( 'appointments_add_new' )
             ->item( 'extras' )
             ->setTitle( bkntc__( 'Extras' ) )
             ->addView( __DIR__ . '/view/tab/extras.php' )
             ->setPriority( 2 );

        $data = [
            'location'  => $locationInf,
            'date'      => $date,
        ];

		return $this->modalView( 'add_new', [
            'data' => $data
        ] );
	}

    /**
     * @throws CapabilitiesException
     * @throws \Exception
     */
    public function create_appointment()
	{
		Capabilities::must( 'appointments_add' );

        $run_workflows = Helper::_post('run_workflows', 1, 'num');
        Config::getWorkflowEventsManager()->setEnabled($run_workflows === 1);
        $appointmentRequests = AppointmentRequests::load( true );

        if( ! $appointmentRequests->validate() )
        {
            return $this->response(false,$appointmentRequests->getFirstError());
        }

        $appointmentData = $appointmentRequests->currentRequest();

		if( $appointmentData->isRecurring() && empty( $appointmentData->recurringAppointmentsList ) )
		{
			return $this->response(true, [ 'dates' => AppointmentService::getRecurringDates( $appointmentData ) ]);
		}

        AppointmentService::createAppointment();

        PaymentGatewayService::find('local')->doPayment($appointmentRequests);

		return $this->response(true );
	}

	public function edit ()
	{
		Capabilities::must( 'appointments_edit' );

		$id = Helper::_post( 'id', '0', 'integer' );

		$appointmentSO = AppointmentSmartObject::load( $id );

		$appointmentInfo = Appointment::leftJoin( 'location',   [ 'name' ] )
                                      ->leftJoin( 'service',    [ 'name' ] )
                                      ->leftJoin( 'staff',      [ 'name' ] )
                                      ->where( Appointment::getField( 'id' ), $id )
                                      ->fetch();

		if( ! $appointmentSO->validate() )
		{
            return $this->response( false, bkntc__( 'Selected appointment not found!' ) );
		}

		// get service categories...
		$serviceInfo = $appointmentSO->getServiceInf();

		$categories = [];

		$categoryId = $serviceInfo['category_id'];
		$deep = 15;
		while( true )
		{
			$categoryInf = ServiceCategory::get( $categoryId );
			$categories[] = $categoryInf;

			$categoryId = (int)$categoryInf['parent_id'];

			if( ($deep--) < 0 || $categoryId <= 0 )
			{
				break;
			}
		}

        TabUI::get( 'appointments_edit' )
             ->item( 'details' )
             ->setTitle( bkntc__( 'Appointment details' ) )
             ->addView( __DIR__ . '/view/tab/edit_details.php', [
                 'id'            => $id,
                 'appointment'   => $appointmentSO,
                 'categories'    => array_reverse( $categories )
             ] )
             ->setPriority( 1 );

        TabUI::get( 'appointments_edit' )
             ->item( 'extras' )
             ->setTitle( bkntc__( 'Extras' ) )
             ->addView( __DIR__ . '/view/tab/edit_extras.php' )
             ->setPriority( 2 );

		return $this->modalView( 'edit', [
			'id'				=> $id,
			'service_capacity'	=> $serviceInfo['max_capacity'],
            'priceUpdated'      => Appointment::getData( $appointmentSO->getId(), 'price_updated', 0 ),
		] );
	}

	public function save_edited_appointment()
	{
		Capabilities::must( 'appointments_edit' );

        $run_workflows = Helper::_post('run_workflows', 1, 'num');
        Config::getWorkflowEventsManager()->setEnabled($run_workflows === 1);

        $appointmentRequests = AppointmentRequests::load( true );

        if( ! $appointmentRequests->validate() )
        {
            return $this->response(false,$appointmentRequests->getFirstError());
        }

        $appointmentObj = $appointmentRequests->currentRequest();

		do_action( 'bkntc_appointment_before_edit', $appointmentObj );
        do_action( 'bkntc_appointment_before_mutation', $appointmentObj->appointmentId );

		AppointmentService::editAppointment( $appointmentObj );

        do_action( 'bkntc_appointment_after_edit', $appointmentObj );
        do_action( 'bkntc_appointment_after_mutation', $appointmentObj->appointmentId );

		return $this->response(true, ['id' => $appointmentObj->appointmentId]);
	}

	public function info()
	{
		Capabilities::must( 'appointments' );

		$id = Helper::_post('id', '0', 'integer');

		$appointmentInfo = Appointment::leftJoin( 'customer', ['first_name', 'last_name', 'phone_number', 'email', 'profile_image'])
            ->leftJoin( 'location', ['name'] )
            ->leftJoin( 'service', ['name'] )
            ->leftJoin( 'staff', ['name', 'profile_image', 'email', 'phone_number'])
            ->where( Appointment::getField('id'), $id )->fetch();

		if( !$appointmentInfo )
		{
			return $this->response(false, bkntc__('Appointment not found!'));
		}

		$extrasArr = AppointmentExtra::where('appointment_id', $id)
            ->leftJoin(ServiceExtra::class, ['name', 'image'], ServiceExtra::getField('id'), AppointmentExtra::getField('extra_id'))
            ->fetchAll();


        $paymentGatewayList = [];
        $appointmentPrice = AppointmentPrice::where('appointment_id',  $appointmentInfo->id)
            ->select('sum(price * negative_or_positive) as total_amount', true)->fetch();

        if( $appointmentPrice->total_amount != $appointmentInfo->paid_amount )
        {
            $paymentGatewayList = PaymentGatewayService::getInstalledGatewayNames();
            $paymentGatewayList = array_filter($paymentGatewayList , function ($paymentGateway){
                return property_exists( PaymentGatewayService::find($paymentGateway) , 'createPaymentLink');
            });
        }

        TabUI::get( 'appointments_info' )
             ->item( 'details' )
             ->setTitle( bkntc__( 'Appointment details' ) )
             ->addView( __DIR__ . '/view/tab/info_details.php', [
                 'info' => $appointmentInfo,
                 'paymentGateways'=>$paymentGatewayList
             ] )
             ->setPriority( 1 );

        TabUI::get( 'appointments_info' )
             ->item( 'extras' )
             ->setTitle( bkntc__( 'Extras' ) )
             ->addView( __DIR__ . '/view/tab/info_extras.php', [
	             'info'     => $appointmentInfo,
                 'extras'   => $extrasArr
             ] )
             ->setPriority( 2 );

		return $this->modalView( 'info', [
            'id'            => $id,
        ] );
	}

	public function get_services()
	{
        $search		= Helper::_post( 'q', '', 'string' );
        $category	= Helper::_post( 'category', '', 'int' );

        $services = Service::where( 'is_active', 1 );

        if( ! empty( $category ) )
        {
            $services = $services->where( 'category_id', $category );
        }

        if ( ! empty( $search ) )
        {
            $services = $services->like('name', $search );
        }

        $data = [];

        foreach ( $services->fetchAll() as $service )
        {
            $data[] = [
                'id'				=>	(int)$service['id'],
                'text'				=>	htmlspecialchars($service['name']),
                'repeatable'		=>	(int)$service['is_recurring'],
                'repeat_type'		=>	htmlspecialchars( $service['repeat_type'] ),
                'repeat_frequency'	=>	htmlspecialchars( $service['repeat_frequency'] ),
                'full_period_type'	=>	htmlspecialchars( $service['full_period_type'] ),
                'full_period_value'	=>	(int)$service['full_period_value'],
                'max_capacity'		=>	(int)$service['max_capacity'],
                'date_based'		=>	$service['duration'] >= 1440
            ];
        }

        return $this->response(true, [ 'results' => $data ]);
	}

	public function get_locations()
	{
		$search		= Helper::_post('q', '', 'string');
		$locations  = Location::where('is_active', 1)->where('name', 'LIKE', '%' . $search . '%')->fetchAll();

		$data = [];

		foreach ( $locations AS $location )
		{
			$data[] = [
				'id'	=> (int)$location['id'],
				'text'	=> htmlspecialchars($location['name'])
			];
		}

		return $this->response(true, [ 'results' => $data ]);
	}

	public function get_service_categories()
	{
		$search		= Helper::_post('q', '', 'string');
		$category	= Helper::_post('category', 0, 'int');

        $filters = [ '%' . $search . '%' , (int)$category ];

        $services = DB::DB()->get_results(
            DB::DB()->prepare( "SELECT *, (SELECT COUNT(0) FROM " . DB::table('service_categories') . " WHERE parent_id=tb1.id) AS sub_categs FROM " . DB::table('service_categories') . " tb1 WHERE `name` LIKE %s AND parent_id=%d" . DB::tenantFilter() , $filters ),
            ARRAY_A
        );

		$data = [];

            foreach ( $services AS $service )
            {
                $data[] = [
                    'id'                => (int)$service['id'],
                    'text'                => htmlspecialchars($service['name']),
                    'have_sub_categ'    => $service['sub_categs']
                ];
            }

		return $this->response(true, [ 'results' => $data ]);
	}

	public function get_staff()
	{
		$search		= Helper::_post('q', '', 'string');
		$location	= Helper::_post('location', 0, 'int');
		$service	= Helper::_post('service', 0, 'int');

		$staff = Staff::where('is_active', 1)
		                 ->where('name', 'like', "%$search%");

		if( !empty( $location ) )
		{
			$staff->whereFindInSet( 'locations', $location );
		}

		if( !empty( $service ) )
		{
			$serviceStaffSubQuery = ServiceStaff::where( 'service_id', $service )->select('staff_id');
			$staff->where( 'id', 'IN', $serviceStaffSubQuery );
		}

		$staff = $staff->fetchAll();

		$data = [];
		foreach ( $staff AS $staffInf )
		{
			$data[] = [
				'id'	=> (int)$staffInf['id'],
				'text'	=> htmlspecialchars($staffInf['name'])
			];
		}

		return $this->response(true, [ 'results' => $data ]);
	}

	public function get_customers()
	{
		$search = Helper::_post( 'q', '', 'string' );

		$customers = Customer::my();

        if( ! empty( $search ) )
        {
            $customers = $customers->where( 'CONCAT(`first_name`, \' \', `last_name`)', 'like', "%{$search}%" )
                ->orWhere( 'email', 'like', "%{$search}%" )
                ->orWhere( 'phone_number', 'like', "%{$search}%" );
        }

        $customers = $customers->select( [ 'id', 'first_name', 'last_name' ] )->limit( 100 )->fetchAll();

        $data = array_map( fn( $elem ) => [
            'id'	=> (int) $elem[ 'id' ],
            'text'	=> htmlspecialchars($elem[ 'first_name' ] . ' ' . $elem[ 'last_name' ] )
        ], $customers );

		return $this->response( true, [ 'results' => $data ] );
	}

	public function get_available_times( $calledFromBackend = true )
	{
        $id				= Helper::_post('id', -1, 'int');
        $search			= Helper::_post('q', '', 'string');
        $date			= Helper::_post('date', '', 'string');

        $date           = Date::reformatDateFromCustomFormat( $date );
        $calendarData   = new CalendarService( $date );

        if( $calledFromBackend )
        {
            $location		= Helper::_post('location', 0, 'int');
            $service		= Helper::_post('service', 0, 'int');
            $staff			= Helper::_post('staff', 0, 'int');
            $service_extras	= Helper::_post('service_extras', '[]', 'string');

            $calendarData->initServiceInf( $service );
        }
        else
        {
            $appointmentRequestData = AppointmentRequests::load()->currentRequest();
            $location		= $appointmentRequestData->getData('location', 0, 'int');
            $service		= $appointmentRequestData->getData('service', 0, 'int');
            $staff			= $appointmentRequestData->getData('staff', 0, 'int');
            $service_extras	= $appointmentRequestData->getData('service_extras', '[]', 'string');

            $calendarData->setServiceInf( $appointmentRequestData->serviceInf );
        }

        $service_extras	= json_decode($service_extras, true);

		$extras_arr	= [];
		foreach ( $service_extras AS $extraInf )
		{
			if( !( is_array( $extraInf )
			       && isset($extraInf['customer']) && is_numeric( $extraInf['customer'] ) && $extraInf['customer'] > 0
			       && isset($extraInf['extra']) && is_numeric( $extraInf['extra'] ) && $extraInf['extra'] > 0
			       && isset($extraInf['quantity']) && is_numeric($extraInf['quantity']) && $extraInf['quantity'] > 0)
			)
			{
				continue;
			}

			$extra_inf = ServiceExtra::where('service_id', $service)->where('id', $extraInf['extra'])->fetch();

			if( $extra_inf && $extra_inf['max_quantity'] >= $extraInf['quantity'] )
			{
				$extra_inf['quantity'] = $extraInf['quantity'];
				$extra_inf['customer'] = $extraInf['customer'];

				$extras_arr[] = $extra_inf;
			}
		}

		$dataForReturn = [];

		$calendarData->setStaffId( $staff )
		             ->setLocationId( $location )
		             ->setServiceExtras( $extras_arr )
		             ->setExcludeAppointmentId( $id )
		             ->setShowExistingTimeSlots( false )
		             ->setCalledFromBackEnd( $calledFromBackend );

		$calendarData = $calendarData->getCalendar();
		$data = $calendarData['dates'];

		if( isset( $data[ $date ] ) )
		{
			foreach ( $data[ $date ] AS $dataInf )
			{
				$startTime = $dataInf['start_time_format'];

				// search...
				if( !empty( $search ) && strpos( $startTime, $search ) === false )
				{
					continue;
				}

				$result = [
					'id'					=>	$dataInf['start_time'],
					'text'					=>	$startTime,
					'max_capacity'			=>	$dataInf['max_capacity'],
					'weight'                =>	$dataInf['weight']
				];
                $dataForReturn[] = apply_filters('bkntc_backend_appointment_date_time' , $result , $dataInf );
			}
		}

		return $this->response(true, [ 'results' => $dataForReturn ]);
	}

	public function get_available_times_all()
	{
		$search		= Helper::_post('q', '', 'string');
		$service	= Helper::_post('service', 0, 'int');
		$location	= Helper::_post('location', 0, 'int');
		$staff		= Helper::_post('staff', 0, 'int');
		$dayOfWeek	= Helper::_post('day_number', 1, 'int');

		if( $dayOfWeek != -1 )
		{
			$dayOfWeek -= 1;
		}

		$calendarServ = new CalendarService();

		$calendarServ->setStaffId( $staff )
		             ->setServiceId( $service )
		             ->setLocationId( $location );

		return $this->response(true, [
			'results' => $calendarServ->getCalendarByDayOfWeek( $dayOfWeek, $search )
		]);
	}

	public function get_day_offs()
	{
        $appointmentRequests = AppointmentRequests::load();
        $appointmentObj = $appointmentRequests->currentRequest();

		if(
			! Date::isValid( $appointmentObj->recurringStartDate )
			|| ! Date::isValid( $appointmentObj->recurringEndDate )
			|| $appointmentObj->serviceId <= 0
		)
		{
			return $this->response(false, bkntc__('Please fill in all required fields correctly!'));
		}

		$calendarService = new CalendarService( $appointmentObj->recurringStartDate, $appointmentObj->recurringEndDate );
		$calendarService->setDefaultsFrom( $appointmentObj );

		return $this->response( true, $calendarService->getDayOffs() );
	}

	public function get_service_extras()
	{
		$appointment_id			= Helper::_post('appointment_id', 0, 'integer');
		$service_id	            = Helper::_post('service_id', 0, 'integer');

		$extras = ServiceExtra::where('service_id', $service_id)->fetchAll();

        $appointmentExtras = AppointmentExtra::where('appointment_id', $appointment_id)->fetchAll();
        $appointmentExtras = Helper::assocByKey($appointmentExtras, 'extra_id');

        foreach ($extras as $extra)
        {
            $extra->quantity = array_key_exists($extra->id, $appointmentExtras) ? $appointmentExtras[$extra->id]->quantity : 0;
        }

		return $this->modalView( 'service_extras', [
			'extras'				=> $extras,
		] );
	}

    public function create_payment_link()
    {
        $paymentGateway = Helper::_post('payment_gateway','','str');
        $id = Helper::_post('id',0,'int');

        $totalAmountQuery = AppointmentPrice::where('appointment_id', DB::field( Appointment::getField('id') ))
            ->select('sum(price * negative_or_positive)', true);

        $appointments = Appointment::leftJoin('customer', ['first_name', 'last_name', 'email', 'profile_image', 'phone_number'])
            ->leftJoin('staff', ['name', 'profile_image'])
            ->leftJoin('location', ['name'])
            ->leftJoin('service', ['name'])
            ->where(Appointment::getField('id') , $id )
            ->selectSubQuery( $totalAmountQuery, 'total_price' );

        $appointment = $appointments->fetch();
        if( empty($appointments) )
        {
            return $this->response(false);
        }

        $paymentGatewayService = PaymentGatewayService::find( $paymentGateway );

        if(! property_exists( $paymentGatewayService  ,'createPaymentLink'))
        {
            return $this->response(false);
        }

        $data = $paymentGatewayService->createPaymentLink([$appointment]);

        return $this->response(true ,['url'=>$data->data['url']]);
    }


}
