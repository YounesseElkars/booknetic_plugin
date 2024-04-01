<?php

namespace BookneticApp\Providers\Core;

use BookneticApp\Backend\Appearance\Helpers\Theme;
use BookneticApp\Backend\Appointments\Helpers\AppointmentChangeStatus;
use BookneticApp\Frontend\Controller\ForgotPasswordAjax;
use BookneticApp\Frontend\Controller\SigninAjax;
use BookneticApp\Frontend\Controller\SignupAjax;
use BookneticApp\Models\Appearance;
use BookneticApp\Backend\Appointments\Helpers\AppointmentService;
use BookneticApp\Models\Appointment;
use BookneticApp\Models\Customer;
use BookneticApp\Models\Location;
use BookneticApp\Models\Service;
use BookneticApp\Backend\Settings\Helpers\LocalizationService;
use BookneticApp\Models\ServiceCategory;
use BookneticApp\Models\Staff;
use BookneticApp\Integrations\LoginButtons\FacebookLogin;
use BookneticApp\Integrations\LoginButtons\GoogleLogin;
use BookneticApp\Providers\Helpers\Date;
use BookneticApp\Providers\Helpers\Helper;

class Frontend
{

	const FRONT_DIR		= __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Frontend' . DIRECTORY_SEPARATOR;
	const VIEW_DIR		= __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Frontend' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR;

	public static function init()
	{
		do_action( 'bkntc_frontend' );

		self::checkSocialLogin();

		LocalizationService::changeLanguageIfNeed();

		self::initAjaxRequests();

        self::initAjaxRequests( SigninAjax::class );

        self::initAjaxRequests( SignupAjax::class );

        self::initAjaxRequests( ForgotPasswordAjax::class );

		if( !(defined('DOING_AJAX') && DOING_AJAX) )
		{
			add_shortcode('booknetic', [self::class, 'addBookneticShortCode']);
            add_shortcode('booknetic-booking-button', [ static::class, 'addBookingPopupShortcode' ]);
            add_shortcode('booknetic-change-status', [ static::class, 'addChangeStatusShortcode' ]);

            add_shortcode( 'booknetic-signin', [ static::class, 'addSigninShortcode' ] );
            add_shortcode( 'booknetic-signup', [ static::class, 'addSignUpShortcode' ] );
            add_shortcode( 'booknetic-forgot-password', [ static::class, 'addForgotPasswordShortcode' ] );
        }
	}

    public static function addSigninShortcode( $attrs )
    {
        wp_enqueue_script( 'booknetic-signin', Helper::assets('js/booknetic-signin.js', 'front-end'), [ 'jquery' ] );

        if( Permission::userId() > 0 && ! ( isset($_GET['bkntc_preview']) || isset($_GET['elementor-preview']) ) )
        {
            $redirectToUrl = Helper::getURLOfUsersDashboard();
            wp_add_inline_script( 'booknetic-signin', 'location.href="' . $redirectToUrl . '";' );
            return bkntc__('You are already signed in. Please wait, you are being redirected...');
        }

        wp_enqueue_style('Booknetic-font', '//fonts.googleapis.com/css?family=Poppins:200,200i,300,300i,400,400i,500,500i,600,600i,700&display=swap');
        wp_enqueue_style('booknetic-signin', Helper::assets('css/booknetic-signin.css', 'front-end'));

        wp_localize_script( 'booknetic-signin', 'BookneticDataSI', [
            'ajax_url'		    => admin_url( 'admin-ajax.php' ),
            'assets_url'	    => Helper::assets('/', 'front-end') ,
            'localization'      => []
        ]);

        return Helper::renderView(self::FRONT_DIR . 'view' . DIRECTORY_SEPARATOR . 'signin' . DIRECTORY_SEPARATOR . 'signin.php' , $attrs );
    }

    public static function addSignUpShortcode( $atts )
    {
        wp_enqueue_script( 'booknetic-signup', Helper::assets('js/booknetic-signup.js', 'front-end'), [ 'jquery' ] );

        if( Permission::userId() > 0 && ! ( isset($_GET['bkntc_preview']) || isset($_GET['elementor-preview']) ) )
        {
            $redirectToUrl = Helper::getURLOfUsersDashboard();
            wp_add_inline_script( 'booknetic-signup', 'location.href="' . $redirectToUrl . '";' );
            return bkntc__('You are already signed in. Please wait, you are being redirected...');
        }

        $customerPanelUrl = Helper::customerPanelURL();

        $activation_token = Helper::_get('activation_token', '', 'string');
        $redirectToUrl    = Helper::_get( 'redirect_to', $_COOKIE[ 'SigninRedirectURL' ] ?? $customerPanelUrl ?: site_url(), 'string' );

        if( ! empty( $activation_token ) )
        {
            $tokenParts = explode( '.', $activation_token );

            if (count($tokenParts) !== 3)
            {
                wp_add_inline_script( 'booknetic-signup', 'location.href="' . htmlspecialchars(site_url()) . '";' );
                return bkntc__( 'Something went wrong. Redirecting...' );
            }

            $header = json_decode( base64_decode( $tokenParts[0] ), true );
            $payload = json_decode( base64_decode( $tokenParts[1] ), true );

            if ( is_array ( $header ) &&
                is_array( $payload ) &&
                array_key_exists ( 'id', $header ) && is_numeric ( $header['id']) &&
                array_key_exists ( 'expire', $header ) && is_numeric ( $header['expire']) &&
                array_key_exists ( 'email', $payload ) )
            {
                $customerId = $header['id'];
                $expire     = $header['expire'];
                $email      = $payload['email'];
            }
            else
            {
                wp_add_inline_script( 'booknetic-signup', 'location.href="' . htmlspecialchars(site_url()) . '";' );
                return bkntc__( 'Something went wrong. Redirecting...' );
            }

            $secret = Helper::getOption( 'purchase_code', '', false );
            $secret = hash_hmac('SHA256', $email, $secret, true);

            if ( ! Helper::validateToken( $activation_token, $secret ) )
            {
                wp_add_inline_script( 'booknetic-signup', 'location.href="' . htmlspecialchars(site_url()) . '";' );
                return bkntc__( 'Something went wrong. Redirecting...' );
            }

            $customerInfo = Customer::get( $customerId );

            if( ! $customerInfo || Customer::getData( $customerId, 'pending_activation' ) != 1 )
            {
                wp_add_inline_script( 'booknetic-signup', 'location.href="' . urldecode( htmlspecialchars( $redirectToUrl ) ) . '";' );
                return bkntc__('Redirecting...');
            }

            if ( $expire < Date::epoch() )
            {
                wp_delete_user( $customerInfo->user_id );
                Customer::deleteData( $customerInfo->id, 'pending_activation' );
                Customer::deleteData( $customerInfo->id, 'activation_last_sent' );

                Customer::where( 'id', $customerInfo->id )->delete();

                wp_add_inline_script( 'booknetic-signup', 'location.href="' . htmlspecialchars(site_url()) . '";' );
                return bkntc__( 'Expired token. Redirecting...' );
            }

            wp_enqueue_style('Booknetic-font', '//fonts.googleapis.com/css?family=Poppins:200,200i,300,300i,400,400i,500,500i,600,600i,700&display=swap');
            wp_enqueue_style('booknetic-signup', Helper::assets('css/booknetic-signup.css', 'front-end'));

            wp_localize_script( 'booknetic-signup', 'BookneticDataSP', [
                'ajax_url'		    => admin_url( 'admin-ajax.php' ),
                'date_format'	    => Helper::getOption('date_format', 'Y-m-d'),
                'assets_url'	    => Helper::assets('/', 'front-end') ,
                'localization'      => []
            ]);

            Customer::deleteData( $customerId, 'pending_activation' );
            Customer::setData( $customerId, 'activated_on', Date::epoch() );

            if( isset( $customerInfo -> email ) )
            {
                $user = get_user_by( 'email', $customerInfo -> email );

                wp_set_current_user( $user -> ID );
                wp_set_auth_cookie( $user -> ID, true );
                do_action( 'wp_login', $user -> user_login, $user );

                $redirectToUrl = $customerPanelUrl ?: $redirectToUrl;
            }

            return Helper::renderView(self::FRONT_DIR . 'view' . DIRECTORY_SEPARATOR . 'signup' . DIRECTORY_SEPARATOR . 'signup-completed.php' , [ 'activation_token' => $activation_token, 'redirect_to' => $redirectToUrl ] );
        }

        wp_enqueue_style('Booknetic-font', '//fonts.googleapis.com/css?family=Poppins:200,200i,300,300i,400,400i,500,500i,600,600i,700&display=swap');
        wp_enqueue_style('booknetic-signup', Helper::assets('css/booknetic-signup.css', 'front-end'));

        wp_localize_script( 'booknetic-signup', 'BookneticDataSP', [
            'ajax_url'		    => admin_url( 'admin-ajax.php' ),
            'assets_url'	    => Helper::assets('/', 'front-end') ,
            'localization'      => []
        ]);

        if( Helper::getOption( 'google_recaptcha', 'off', false ) == 'on' )
        {
            $siteKey = Helper::getOption( 'google_recaptcha_site_key', '', false );
            $secretKey = Helper::getOption( 'google_recaptcha_secret_key', '', false );

            if( ! empty( $siteKey ) && ! empty( $secretKey ) )
            {
                wp_enqueue_script( 'google-recaptcha', 'https://www.google.com/recaptcha/api.js?render=' . urlencode( $siteKey ) );

                wp_localize_script( 'booknetic-signup', 'ReCaptcha', [ 'google_recaptcha_site_key' => $siteKey ] );
            }
        }

        return Helper::renderView(self::FRONT_DIR . 'view' . DIRECTORY_SEPARATOR . 'signup' . DIRECTORY_SEPARATOR . 'signup.php' , $atts );
    }

    public static function addForgotPasswordShortcode( $atts )
    {
        wp_enqueue_script( 'booknetic-forgot-password', Helper::assets('js/booknetic-forgot-password.js', 'front-end'), [ 'jquery' ] );

        if( Permission::userId() > 0 && ! ( isset($_GET['bkntc_preview']) || isset($_GET['elementor-preview']) ) )
        {
            $redirectToUrl = Helper::getURLOfUsersDashboard();
            wp_add_inline_script( 'booknetic-forgot-password', 'location.href="' . $redirectToUrl . '";' );
            return bkntc__('You are already signed in. Please wait, you are being redirected...');
        }

        $reset_token = Helper::_get('reset_token', '', 'string');

        if( ! empty( $reset_token ) )
        {
            $tokenParts = explode('.', $reset_token);

            if (count($tokenParts) !== 3)
            {
                wp_add_inline_script('booknetic-forgot-password', 'location.href="' . htmlspecialchars(site_url()) . '";');
                return bkntc__('Something went wrong. Redirecting...');
            }

            $header = json_decode(base64_decode($tokenParts[0]), true);
            $payload = json_decode(base64_decode($tokenParts[1]), true);

            if (is_array($header) &&
                is_array($payload) &&
                array_key_exists('id', $header) && is_numeric($header['id']) &&
                array_key_exists('expire', $header) && is_numeric($header['expire']) &&
                array_key_exists('email', $payload))

            {
                $customerId = $header['id'];
                $expire = $header['expire'];
                $email = $payload['email'];
            }
            else
            {
                wp_add_inline_script('booknetic-forgot-password', 'location.href="' . htmlspecialchars(site_url()) . '";');
                return bkntc__('Something went wrong. Redirecting...');
            }

            $secret = Helper::getOption('purchase_code', '', false);
            $secret = hash_hmac('SHA256', $email, $secret, true);

            if (!Helper::validateToken($reset_token, $secret))
            {
                wp_add_inline_script('booknetic-forgot-password', 'location.href="' . htmlspecialchars(site_url()) . '";');
                return bkntc__('Something went wrong. Redirecting...');
            }

            if ( Customer::getData( $customerId, 'pending_password_reset' ) != 1 )
            {
                wp_add_inline_script('booknetic-forgot-password', 'location.href="' . htmlspecialchars(site_url()) . '";');
                return bkntc__('Redirecting...');
            }

            if ( $expire < Date::epoch() )
            {
                wp_add_inline_script('booknetic-forgot-password', 'location.href="' . htmlspecialchars(site_url()) . '";');
                return bkntc__('Token expired. Redirecting...');
            }

            wp_enqueue_style('Booknetic-font', '//fonts.googleapis.com/css?family=Poppins:200,200i,300,300i,400,400i,500,500i,600,600i,700&display=swap');
            wp_enqueue_style('booknetic-forgot-password', Helper::assets('css/booknetic-forgot-password.css', 'front-end'));

            wp_localize_script('booknetic-forgot-password', 'BookneticDataFP', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'date_format' => Helper::getOption('date_format', 'Y-m-d'),
                'assets_url' => Helper::assets('/', 'front-end'),
                'localization' => []
            ]);

            return Helper::renderView(self::FRONT_DIR . 'view' . DIRECTORY_SEPARATOR . 'forgot_password' . DIRECTORY_SEPARATOR . 'forgot_password_complete.php', [ 'reset_token' => $reset_token ]);
        }

        wp_enqueue_style('Booknetic-font', '//fonts.googleapis.com/css?family=Poppins:200,200i,300,300i,400,400i,500,500i,600,600i,700&display=swap');
        wp_enqueue_style('booknetic-forgot-password', Helper::assets('css/booknetic-forgot-password.css', 'front-end'));

        wp_localize_script( 'booknetic-forgot-password', 'BookneticDataFP', [
            'ajax_url'		    => admin_url( 'admin-ajax.php' ),
            'assets_url'	    => Helper::assets('/', 'front-end') ,
            'localization'      => []
        ]);

        return Helper::renderView(self::FRONT_DIR . 'view' . DIRECTORY_SEPARATOR . 'forgot_password' . DIRECTORY_SEPARATOR . 'forgot_password.php', $atts );
    }

    public static function addBookingPopupShortcode( $atts )
    {
        $bookneticShortcode =  do_shortcode('[booknetic]');

        wp_enqueue_script( 'booknetic-popup', Helper::assets('js/booknetic-popup.js', 'front-end'), [ 'jquery' ] );
        wp_enqueue_style( 'booknetic-popup', Helper::assets('css/booknetic-popup.css', 'front-end'));

        return Helper::renderView( self::FRONT_DIR . 'view' . DIRECTORY_SEPARATOR . 'popup/index.php', $atts );
	}

    public static function addChangeStatusShortcode( $atts )
    {
        $atts = empty( $atts ) ? [] : $atts;

        $token = Helper::_get('bkntc_token' , '' ,'string');
        $validateToken = AppointmentChangeStatus::validateToken($token);
        $isPreviewMode = false;

        if ( Permission::userId() > 0 && isset($_GET['bkntc_preview']) )
        {
            $isPreviewMode = true;
            $token = base64_encode('booknetic') . '.' . base64_encode(json_encode(['title' => '{status}']));
        }

        if ( $isPreviewMode !== true && $validateToken !== true )
        {
            return $validateToken;
        }

        wp_enqueue_script( 'booknetic-change-status-blocks', Helper::assets('js/booknetic-change-status.js', 'front-end'), [ 'jquery' ] );
        wp_enqueue_style('Booknetic-font', '//fonts.googleapis.com/css?family=Poppins:200,200i,300,300i,400,400i,500,500i,600,600i,700&display=swap');
        wp_enqueue_style('booknetic-change-status-blocks', Helper::assets('css/booknetic-change-status.css', 'front-end' ) );

        wp_localize_script( 'booknetic-change-status-blocks', 'BookneticChangeStatusData', [
            'ajax_url'		    => admin_url( 'admin-ajax.php' ),
            'assets_url'	    => Helper::assets('/', 'front-end') ,
            'date_format'	    => Helper::getOption('date_format', 'Y-m-d'),
            'week_starts_on'    => Helper::getOption('week_starts_on', 'sunday') == 'monday' ? 'monday' : 'sunday',
            'client_timezone'   => htmlspecialchars(Helper::getOption('client_timezone_enable', 'off')),
            'tz_offset_param'   => htmlspecialchars(Helper::_get('client_time_zone', '-', 'str')),
            'localization'      => [
                // months
                'January'               => bkntc__('January'),
                'February'              => bkntc__('February'),
                'March'                 => bkntc__('March'),
                'April'                 => bkntc__('April'),
                'May'                   => bkntc__('May'),
                'June'                  => bkntc__('June'),
                'July'                  => bkntc__('July'),
                'August'                => bkntc__('August'),
                'September'             => bkntc__('September'),
                'October'               => bkntc__('October'),
                'November'              => bkntc__('November'),
                'December'              => bkntc__('December'),

                //days of week
                'Mon'                   => bkntc__('Mon'),
                'Tue'                   => bkntc__('Tue'),
                'Wed'                   => bkntc__('Wed'),
                'Thu'                   => bkntc__('Thu'),
                'Fri'                   => bkntc__('Fri'),
                'Sat'                   => bkntc__('Sat'),
                'Sun'                   => bkntc__('Sun'),

                // select placeholders
                'select'                => bkntc__('Select...'),
                'searching'				=> bkntc__('Searching...'),
            ],
            'token'    => $token,
        ]);

        $atts['isSaaS']         = Helper::isSaaSVersion();
        $atts['companyImage']   = Helper::profileImage(Helper::getOption('company_image', ''), 'Settings');
        $atts['uploadLogoCapability']     = Capabilities::tenantCan( 'upload_logo_to_booking_panel' );
        $atts['displayLogo']    = Helper::getOption('display_logo_on_booking_panel', 'off') == 'on';

        $viewPath = self::FRONT_DIR . 'view' . DIRECTORY_SEPARATOR . 'change_status/index.php';
        return Helper::renderView( $viewPath, $atts);

    }

	private static function checkSocialLogin()
	{
		$booknetic_action = Helper::_get( Helper::getSlugName() . '_action', '', 'string' );
		if( $booknetic_action == 'facebook_login' )
		{
			Helper::redirect( FacebookLogin::getLoginURL() );
		}
		else if( $booknetic_action == 'facebook_login_callback' )
		{
			$data = FacebookLogin::getUserData();
			echo bkntc__('Loading...');
			echo '<script>var booknetic_user_data = ' . json_encode( $data ) . ';</script>';
			exit;
		}
		else if( $booknetic_action == 'google_login' )
		{
			Helper::redirect( GoogleLogin::getLoginURL() );
		}
		else if( $booknetic_action == 'google_login_callback' )
		{
			$data = GoogleLogin::getUserData();
			echo bkntc__('Loading...');
			echo '<script>var booknetic_user_data = ' . json_encode( $data ) . ';</script>';
			exit;
		}

	}

	public static function initAjaxRequests( $class = false )
	{
		$controllerClass = $class !== false ? $class : \BookneticApp\Frontend\Controller\Ajax::class;
		$methods = get_class_methods( $controllerClass );
		$actionPrefix = (is_user_logged_in() ? 'wp_ajax_' : 'wp_ajax_nopriv_') . 'bkntc_';
		$controllerClass = new $controllerClass();

		foreach( $methods AS $method )
		{
			// break helper methods
			if( strpos( $method, '_' ) === 0 )
				continue;

			add_action( $actionPrefix . $method, function () use ( $controllerClass, $method )
			{
				do_action( "bkntc_before_frontend_request_" . $method );

				$result = call_user_func( [ $controllerClass, $method ] );

				$result = apply_filters('bkntc_after_frontend_request_' . $method, $result);

				if( is_array( $result ) )
				{
					echo json_encode( $result );
				}
				else
				{
					echo $result;
				}

				exit();
			});
		}
	}

	public static function addBookneticShortCode( $atts )
	{
        $atts = empty( $atts ) ? [] : $atts;
        $info = [];

        wp_enqueue_script( 'booknetic', Helper::assets('js/booknetic.js', 'front-end'), [ 'jquery' ] );

		if( Helper::getOption('only_registered_users_can_book', 'off') == 'on' && !is_user_logged_in() )
		{
			wp_add_inline_script( 'booknetic', 'location.href="'. Helper::getRedirectURL() .'";' );

			return bkntc__('Redirecting...');
		}
		$theme = null;
		if( isset( $atts['theme'] ) && is_numeric( $atts['theme'] ) && $atts['theme'] > 0 )
		{
			$theme = Appearance::get( $atts['theme'] );
		}
		if( empty( $theme ) )
		{
			$theme = Appearance::where('is_default', '1')->fetch();
		}
		$fontfamily = $theme ? $theme['fontfamily'] : 'Poppins';

		$bookneticJSData = Helper::getBookneticJSData();

		wp_enqueue_script( 'select2-bkntc', Helper::assets('js/select2.min.js') );
		wp_enqueue_script( 'booknetic.datapicker', Helper::assets('js/datepicker.min.js', 'front-end') );
		wp_enqueue_script( 'jquery.nicescroll', Helper::assets('js/jquery.nicescroll.min.js', 'front-end'), [ 'jquery' ] );
		wp_enqueue_script( 'intlTelInput', Helper::assets('js/intlTelInput.min.js', 'front-end'), [ 'jquery' ] );

		if( Helper::getOption('google_recaptcha', 'off', false) == 'on' )
		{
			$google_site_key = Helper::getOption('google_recaptcha_site_key', '', false);
			$google_secret_key = Helper::getOption('google_recaptcha_secret_key', '', false);

			if( !empty( $google_site_key ) && !empty( $google_secret_key ) )
			{
				wp_enqueue_script( 'google-recaptcha', 'https://www.google.com/recaptcha/api.js?render=' . urlencode($google_site_key) );
				$bookneticJSData['google_recaptcha_site_key'] = $google_site_key;
			}
		}

		wp_localize_script( 'booknetic', 'BookneticData', $bookneticJSData);

		wp_enqueue_style('Booknetic-font', '//fonts.googleapis.com/css?family='.urlencode($fontfamily).':200,200i,300,300i,400,400i,500,500i,600,600i,700&display=swap');
		wp_enqueue_style('bootstrap-booknetic', Helper::assets('css/bootstrap-booknetic.css', 'front-end'));

		wp_enqueue_style('booknetic', Helper::assets('css/booknetic.css', 'front-end') ,['bootstrap-booknetic']);

		wp_enqueue_style('select2', Helper::assets('css/select2.min.css'));
		wp_enqueue_style('select2-bootstrap', Helper::assets('css/select2-bootstrap.css'));
		wp_enqueue_style('booknetic.datapicker', Helper::assets('css/datepicker.min.css', 'front-end'));
		wp_enqueue_style('intlTelInput', Helper::assets('css/intlTelInput.min.css', 'front-end'));

		$theme_id = $theme ? $theme['id'] : 0;

		if( $theme_id > 0 )
		{
			$themeCssFile = Theme::getThemeCss( $theme_id );
			wp_enqueue_style('booknetic-theme', str_replace(['http://', 'https://'], '//', $themeCssFile), [], rand( 0, 10000 ) );
		}

		$company_phone_number = Helper::getOption('company_phone', '');

		$steps = [
			'service'			=> [
				'value'			=>	'',
				'hidden'		=>	false,
				'loader'		=>	'card2',
				'title'			=>	bkntc__('Service'),
				'head_title'	=>	bkntc__('Select service'),
				'attrs'			=>	' data-service-category="'.(isset($atts['category']) && is_numeric($atts['category']) && $atts['category'] > 0 ? $atts['category'] : '').'"'
			],
			'staff'				=> [
				'value'			=>	'',
				'hidden'		=>	false,
				'loader'		=>	'card1',
				'title'			=>	bkntc__('Staff'),
				'head_title'	=>	bkntc__('Select staff')
			],
			'location'			=> [
				'value'			=>	isset($select_location_id) && $select_location_id > 0 ? $select_location_id : '',
				'hidden'		=>	false,
				'loader'		=>	'card1',
				'title'			=>	bkntc__('Location'),
				'head_title'	=>	bkntc__('Select location')
			],
			'service_extras'	=> [
				'value'			=>	'',
				'hidden'		=>	( Capabilities::tenantCan( 'services' ) == false ) || Helper::getOption('show_step_service_extras', 'on') == 'off',
				'loader'		=>	'card2',
				'title'			=>	bkntc__('Service Extras'),
				'head_title'	=>	bkntc__('Select service extras')
			],
			'information'		=> [
				'value'			=>	'',
				'hidden'		=>	false,
				'loader'		=>	'card3',
				'title'			=>	bkntc__('Information'),
				'head_title'	=>	bkntc__('Fill information')
			],
			'cart'		=> [
				'value'			=>	'',
				'hidden'		=>	Helper::getOption('show_step_cart', 'on') == 'off',
				'loader'		=>	'card3',
				'title'			=>	bkntc__('Cart'),
				'head_title'	=>	bkntc__('Add to cart')
			],
			'date_time'			=> [
				'value'			=>	'',
				'hidden'		=>	false,
				'loader'		=>	'card3',
				'title'			=>	bkntc__('Date & Time'),
				'head_title'	=>	bkntc__('Select Date & Time')
			],
			'recurring_info'	=> [
				'value'			=>	'',
				'hidden'		=>	true,
				'loader'		=>	'card3',
				'title'			=>	bkntc__('Recurring info'),
				'head_title'	=>	bkntc__('Recurring info')
			],
			'confirm_details'	=> [
				'value'			=>	'',
				'hidden'		=>	Helper::getOption('show_step_confirm_details', 'on') == 'off',
				'loader'		=>	'card3',
				'title'			=>	bkntc__('Confirmation'),
				'head_title'	=>	bkntc__('Confirm Details')
			],
		];

        $customStepsOrder = null;

        if ( ! empty( $atts['steps_order'] ) ) {
            if ( empty( array_diff( explode( ',', $atts['steps_order'] ), [ 'location', 'staff', 'service', 'service_extras', 'date_time', 'information' ] ) ) ) {
                $customStepsOrder = $atts['steps_order'];
            }
        }

		$steps_order = Helper::getBookingStepsOrder(true, $customStepsOrder);

		if( ( Capabilities::tenantCan( 'locations' ) == false ) || ( Helper::getOption('show_step_location', 'on') == 'off' ) && ($location = Location::where('is_active', '1')->fetch()) )
		{
			$steps['location']['hidden'] = true;
			$steps['location']['value'] = -1;
		}

        if( isset($_GET['location']) && is_numeric($_GET['location']) && $_GET['location'] > 0)
        {
            $atts['location'] = $_GET['location'];
        }

		if( isset($atts['location']) && is_numeric($atts['location']) && $atts['location'] > 0 )
		{
			$locationInfo = Location::get( $atts['location'] );

			if( $locationInfo )
			{
				$steps['location']['hidden'] = true;
				$steps['location']['value'] = (int)$locationInfo['id'];
			}
		}

		if( ( Capabilities::tenantCan( 'staff' ) == false ) || ( Helper::getOption('show_step_staff', 'on') == 'off' ) && ($staff = Staff::where('is_active', '1')->fetch()) )
		{
			$steps['staff']['hidden'] = true;
			$steps['staff']['value'] = -1;
		}

		if( isset($_GET['staff']) && is_numeric($_GET['staff']) && $_GET['staff'] > 0)
        {
            $atts['staff'] = $_GET['staff'];
        }

		if( isset( $atts['staff'] ) )
		{
            if ( $atts[ 'staff' ] === 'any' )
            {
                $steps[ 'staff' ][ 'hidden' ] = true;
                $steps[ 'staff' ][ 'value' ] = -1;
            }
            else if ( is_numeric( $atts[ 'staff' ] ) && $atts[ 'staff' ] > 0 )
            {
                $steps[ 'staff' ][ 'hidden' ] = true;
                $steps[ 'staff' ][ 'value' ] = $atts[ 'staff' ];
            }
		}

        if ( isset( $atts[ 'limited_booking_days' ] ) )
        {
            $info[ 'limited_booking_days' ] = ( int )$atts[ 'limited_booking_days' ];
        }

        $serviceRecurringAttrs = '';
		if(
			(
				( Capabilities::tenantCan( 'services' ) == false ) ||
				( Helper::getOption('show_step_service', 'on') == 'off' )
			)
			&& ($service = Service::where('is_active', '1')->fetch())
		)
		{
			$steps['service']['hidden'] = true;
			$steps['service']['value'] = $service['id'];
            $serviceRecurringAttrs = ' data-is-recurring="' . (int)$service['is_recurring'] . '"';

			if( $service['is_recurring'] == 1 )
			{
				$steps['recurring_info']['hidden'] = false;
			}
		}

        if( isset($_GET['service']) && is_numeric($_GET['service']) && $_GET['service'] > 0)
        {
            $atts['service'] = $_GET['service'];
        }

        if( isset($_GET['show_service']) && is_numeric($_GET['show_service']) && $_GET['show_service'] > 0)
        {
            $atts['show_service'] = $_GET['show_service'];
        }

		if( isset($atts['service']) && is_numeric($atts['service']) && $atts['service'] > 0 )
		{
			$serviceInfo = Service::get( $atts['service'] );

			if( $serviceInfo )
			{
				$steps['service']['hidden'] = empty( $atts[ 'show_service' ] );
				$steps['service']['value'] = $serviceInfo['id'];
                $serviceRecurringAttrs = ' data-is-recurring="' . (int)$serviceInfo['is_recurring'] . '"';

				if( $serviceInfo['is_recurring'] == 1 )
				{
					$steps['recurring_info']['hidden'] = false;
				}
			}
		}
        $steps['service']['attrs'] .= $serviceRecurringAttrs;
		$hide_confirmation_number = Helper::getOption('hide_confirmation_number', 'off') == 'on';


        if ( isset($_GET['category']) && is_numeric($_GET['category']) && $_GET['category'] > 0 )
        {
            $result = ServiceCategory::get($_GET['category']);

            $atts['category'] = $_GET['category'];

            if ($result)
            {
                $steps['service']['attrs'] = ' data-service-category="' . $result->id .'"';
            }
        }

        $info = Helper::encodeInfo( $info );

        ob_start();
		require self::FRONT_DIR . 'view' . DIRECTORY_SEPARATOR . 'booking_panel/booknetic.php';
        do_action('bkntc_after_booking_panel_shortcode');
		$viewOutput = ob_get_clean();

		return $viewOutput;
	}

}