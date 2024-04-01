<?php

namespace BookneticApp\Providers\Helpers;

use BookneticApp\Models\Appointment;
use BookneticApp\Models\Data;
use BookneticApp\Models\Service;
use BookneticApp\Models\ServiceCategory;
use BookneticApp\Models\Translation;
use BookneticApp\Providers\Core\Backend;
use BookneticApp\Providers\DB\DB;
use BookneticApp\Providers\Common\PaymentGatewayService;
use BookneticApp\Providers\Core\Permission;

class Helper
{

    private static $translationsCache = [];

	public static function secFormat( $seconds )
	{
		$weeks = floor($seconds /  ( 60 * 60 * 24 * 7 ) );

		$seconds = $seconds % ( 60 * 60 * 24 * 7 );

		$days = floor($seconds /  ( 60 * 60 * 24 ) );

		$seconds = $seconds % ( 60 * 60 * 24 );

		$hours = floor($seconds /  ( 60 * 60 ) );

		$seconds = $seconds % ( 60 * 60 );

		$minutes = floor($seconds / 60 );

		$seconds = $seconds % 60;

		if($weeks == 0)
        {
            $result = rtrim(
                ( $weeks > 0 ? $weeks . bkntc__('w').' ' : '' ) .
                ( $days > 0 ? $days . bkntc__('d').' ' : '' ) .
                ( $hours > 0 ? $hours . bkntc__('h').' ' : '' ) .
                ( $minutes > 0 ? $minutes . bkntc__('m').' ' : '' ) .
                ( $seconds > 0 ? $seconds . bkntc__('s').' ' : '' )
            );
        }
		else if($days)
        {
            $days += 7 * $weeks;
            $result = rtrim($days > 0 ? $days . bkntc__('d').' ' : '');
        }
		else if($weeks)
        {
            $result = rtrim($weeks > 0 ? $weeks . bkntc__('w').' ' : '');
        }

		return empty( $result ) ? '0' : $result;
	}

    public static function secFormatWithName( $seconds )
    {
        $weeks = floor($seconds /  ( 60 * 60 * 24 * 7 ) );

        $seconds = $seconds % ( 60 * 60 * 24 * 7 );

        $days = floor($seconds /  ( 60 * 60 * 24 ) );

        $seconds = $seconds % ( 60 * 60 * 24 );

        $hours = floor($seconds /  ( 60 * 60 ) );

        $seconds = $seconds % ( 60 * 60 );

        $minutes = floor($seconds / 60 );

        $seconds = $seconds % 60;

        if($weeks == 0) {
            $result = rtrim(
                ($weeks > 0 ? $weeks . ' ' . ($weeks == 1 ? bkntc__('week') : bkntc__('weeks')) . ' ' : '') .
                ($days > 0 ? $days . ' ' . ($days == 1 ? bkntc__('day') : bkntc__('days')) . ' ' : '') .
                ($hours > 0 ? $hours . ' ' . ($hours == 1 ? bkntc__('hour') : bkntc__('hours')) . ' ' : '') .
                ($minutes > 0 ? $minutes . ' ' . ($minutes == 1 ? bkntc__('minute') : bkntc__('minutes')) . ' ' : '') .
                ($seconds > 0 ? $seconds . ' ' . ($seconds == 1 ? bkntc__('second') : bkntc__('seconds')) . ' ' : '')
            );
        }
        else if($days)
        {
            $days += 7 * $weeks;
            $result = rtrim($days > 0 ? $days . ' ' . ($days == 1 ? bkntc__('day') : bkntc__('days')) . ' ' : '');
        }
        else if($weeks)
        {
            $result = rtrim($weeks > 0 ? $weeks . ' ' . ($weeks == 1 ? bkntc__('week') : bkntc__('weeks')) . ' ' : '');
        }

        return empty( $result ) ? '0' : $result;
    }

	public static function response( $status , $arr = [], $returnResult = false )
	{
		$arr = is_array($arr) ? $arr : ( is_string($arr) ? ['error_msg' => $arr] : [] );

		if( $status )
		{
			$arr['status'] = 'ok';
		}
		else
		{
			$arr['status'] = 'error';

			if( !isset($arr['error_msg']) )
			{
				$arr['error_msg'] = 'Error!';
			}

			if( self::isModal() )
			{
				$arr['status'] = 'ok';
				$arr['html'] = '
					<div class="fs-modal-body mt-5">
						<div class="text-center mt-5 text-secondary">' . $arr['error_msg'] . '</div>
					</div>
					<div class="fs-modal-footer">
						<button type="button" class="btn btn-lg btn-default" data-dismiss="modal">' . bkntc__('CLOSE') . '</button>
					</div>';
				unset($arr['error_msg']);
			}
		}

		$result = apply_filters( 'bkntc_response', $arr );

		if( $returnResult )
		{
			return $result;
		}
		else
		{
			echo json_encode( $result );
			exit();
		}
	}

	public static function isModal()
	{
		$isModalRequest = Helper::_post('_mn', false, 'int');

		return $isModalRequest === false ? false : true;
	}

	public static function _post( $key , $default = null , $check_type = null , $whiteList = [] )
	{
		$res = isset($_POST[$key]) ? $_POST[$key] : $default;

		if( $res !== $default && !is_null( $check_type ) )
		{
			if( $check_type == 'num' || $check_type == 'int' || $check_type == 'integer' )
			{
				$res = is_numeric( $res ) ? (int)$res : $default;
			}
			else if($check_type == 'str' || $check_type == 'string')
			{
				$res = is_string( $res ) ? trim( stripslashes_deep((string)$res) ) : $default;
			}
			else if($check_type == 'arr' || $check_type == 'array')
			{
				$res = is_array( $res ) ? stripslashes_deep((array)$res) : $default;
			}
			else if($check_type == 'float')
			{
				$res = is_numeric( $res ) ? (float)$res : $default;
			}
			else if($check_type == 'email')
			{
				$res = is_string( $res ) && filter_var($res, FILTER_VALIDATE_EMAIL) !== false ? trim( (string)$res ) : $default;
			}
            else if($check_type == 'json')
            {
                $res = json_decode( trim( stripslashes_deep( $res ) ), true );

                $res = is_array( $res ) ? $res : $default;
            }
            else if($check_type == 'price')
            {
                $price = self::deFormatPrice($res);


                $res = ! is_null($price) ? $price : $default;
            }
		}

		if( !empty( $whiteList ) && !in_array( $res , $whiteList ) )
		{
			$res = $default;
		}

		return $res;
	}

	public static function _get( $key , $default = null , $check_type = null , $whiteList = [] )
	{
		$res = isset($_GET[$key]) ? $_GET[$key] : $default;

		if( $res !== $default && !is_null( $check_type ) )
		{
			if( $check_type == 'num' || $check_type == 'int' || $check_type == 'integer' )
			{
				$res = is_numeric( $res ) ? (int)$res : $default;
			}
			else if($check_type == 'str' || $check_type == 'string')
			{
				$res = is_string( $res ) ? trim( (string)$res ) : $default;
			}
			else if($check_type == 'arr' || $check_type == 'array')
			{
				$res = is_array( $res ) ? (array)$res : $default;
			}
			else if($check_type == 'float')
			{
				$res = is_numeric( $res ) ? (float)$res : $default;
			}
			else if($check_type == 'json')
			{
				$res = json_decode( (string)$res, true );
				$res = is_array( $res ) ? $res : $default;
			}
		}

		if( !empty( $whiteList ) && !in_array( $res , $whiteList ) )
		{
			$res = $default;
		}

		return $res;
	}

	public static function _any( $key , $default = null , $check_type = null , $whiteList = [] )
	{
		$res = isset($_REQUEST[$key]) ? $_REQUEST[$key] : $default;

		if( $res !== $default && !is_null( $check_type ) )
		{
			if( $check_type == 'num' || $check_type == 'int' || $check_type == 'integer' )
			{
				$res = is_numeric( $res ) ? (int)$res : $default;
			}
			else if($check_type == 'str' || $check_type == 'string')
			{
				$res = is_string( $res ) ? trim( (string)$res ) : $default;
			}
			else if($check_type == 'arr' || $check_type == 'array')
			{
				$res = is_array( $res ) ? (array)$res : $default;
			}
			else if($check_type == 'float')
			{
				$res = is_numeric( $res ) ? (float)$res : $default;
			}
			else if($check_type == 'json')
			{
				$res = json_decode( (string)$res, true );
				$res = is_array( $res ) ? $res : $default;
			}
		}

		if( !empty( $whiteList ) && !in_array( $res , $whiteList ) )
		{
			$res = $default;
		}

		return $res;
	}

	public static function cutText( $text , $n = 35 )
	{
		return mb_strlen($text , 'UTF-8') > $n ? mb_substr($text , 0 , $n , 'UTF-8') . '...' : $text;
	}

	public static function checkRequirements()
	{
		if( !ini_get('allow_url_fopen') )
		{
			self::response(false , bkntc__( "\"allow_url_fopen\" disabled in your php.ini settings! Please actiavte id and try again!" ));
		}
	}

	public static function getVersion()
	{
		$plugin_data = get_file_data(__DIR__ . '/../../../init.php' , array('Version' => 'Version') , false);

		return isset($plugin_data['Version']) ? $plugin_data['Version'] : '1.0.0';
	}

	public static function getInstalledVersion()
	{
		$ver = self::getOption( 'plugin_version' , '1.0.0', false );

		return ( $ver === '1' || empty($ver) ) ? '1.0.0' : $ver;
	}

	public static function fsDebug()
	{
		error_reporting(E_ALL);
		ini_set('display_errors' , 'on');
	}

	public static function assets( $url, $module = 'Base', $is_addon = false )
	{
		if( preg_match('/\.(js|css)$/i', $url) && $is_addon !== true)
		{
		    $url .= '?v=' . self::getVersion();
		}

		if ( $is_addon === true )
		{
            // doit: tezbazar yazildi bu, http/htps meselesine gore. yeniden baxilsin bura
            return plugin_dir_url(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR ) . $module . '/' . $url;
		}

		if( $module == 'front-end' )
		{
			return rtrim(plugin_dir_url( dirname( __DIR__ ) ), '/') . '/Frontend/assets/' . ltrim($url, '/');
		}

		return rtrim(plugin_dir_url( dirname( __DIR__ ) ), '/') . '/Backend/' . urlencode( ucfirst($module) ) . '/assets/' . ltrim($url, '/');
	}

	public static function icon( $icon, $module = 'Base', $is_addon = false )
	{
		if( $module == 'front-end' )
		{
			return rtrim(plugin_dir_url( dirname( __DIR__ ) ), '/') . '/Frontend/assets/icons/' . ltrim($icon, '/');
		}

		return rtrim(plugin_dir_url( dirname( __DIR__ ) ), '/') . '/Backend/' . urlencode( ucfirst($module) ) . '/assets/icons/' . ltrim($icon, '/');
	}

	public static function profileImage( $image, $module = 'Base' )
	{
		if( empty( $image ) )
		{
			return self::assets( 'images/no-photo.png', $module );
		}
		else if( $image === 'logo' )
		{
			return self::assets( 'images/logo-white.svg', $module );
		}
		else if( $image === 'logo-sm' )
		{
			return self::assets( 'images/logo-sm.svg', $module );
		}

		return self::uploadedFileURL( $image, $module );
	}

	public static function uploadFolderURL( $module )
	{
		$upload_dir	= wp_upload_dir();
		$upload_dir = $upload_dir['baseurl'] . '/booknetic/' . strtolower( $module ) . ( empty($module) ? '' : '/' );

		if( !is_dir( $upload_dir ) )
			wp_mkdir_p( $upload_dir );

		return $upload_dir;
	}

	public static function uploadedFileURL( $fileName, $module = 'Base' )
	{
		return self::uploadFolderURL( $module ) . basename( $fileName );
	}

	public static function uploadFolder( $module )
	{
		$upload_dir	= wp_upload_dir();
		$upload_dir = $upload_dir['basedir'] . '/booknetic/' . strtolower( $module ) . ( empty($module) ? '' : '/' );

		if( !is_dir( $upload_dir ) )
			wp_mkdir_p( $upload_dir );

		return $upload_dir;
	}

	public static function uploadedFile( $fileName, $module = 'Base' )
	{
		return self::uploadFolder( $module ) . basename( $fileName );
	}

    public static function svgRemoveScriptTags( $filePath )
    {
        $svgContent = file_get_contents( $filePath );
        $svgContent = preg_replace("#<script(.*?)>(.*?)</script>#is",'',$svgContent);
        file_put_contents( $filePath , $svgContent);
    }

    private static function getTranslationsCache()
    {
        if ( ! isset( self::$translationsCache[ 'bkntc_waiting_for_wp_functions_to_load' ] ) && function_exists('wp_get_current_user') && ! Permission::isBackEnd() )
        {
            self::$translationsCache = Helper::assocByKey(Translation::where([
                'table_name' => 'options',
                'locale'     => Helper::getLocale(),
            ] )->fetchAll(), 'column_name');

            if ( empty( self::$translationsCache ) )
            {
                self::$translationsCache[ 'bkntc_eternal_emptiness' ] = true;
            }
        }

        //set a value to it wouldn't jump here next time
        if ( empty( self::$translationsCache ) )
        {
            self::$translationsCache[ 'bkntc_waiting_for_wp_functions_to_load' ] = true;
        }
        else if ( function_exists('wp_get_current_user') && isset( self::$translationsCache[ 'bkntc_waiting_for_wp_functions_to_load' ] ) )
        {
            self::$translationsCache[ 'bkntc_wp_functions_loaded' ] = true;
        }

        return self::$translationsCache;
    }

	public static function getOption( $optionName, $default = null, $multi_tenant_option = true )
	{
        if( $optionName == 'woocommerce_order_details' )
        {
            $translation = Translation ::where( 'column_name', 'woocommerce_order_details' ) -> where( 'locale', get_locale() ) -> fetch();

            if( $translation ) return $translation -> value;
        }

		$prefix = 'bkntc_';

		if( Helper::isSaaSVersion() && $multi_tenant_option )
		{
            $tenantId = ! is_numeric( $multi_tenant_option ) ? Permission::tenantId() : $multi_tenant_option;

			if( $tenantId > 0 )
			{
				$prefix .= 't' . $tenantId . '_';
			}
		}

        if ( array_key_exists( $optionName, self::getTranslationsCache() ) )
        {
            return Helper::getTranslationsCache()[ $optionName ][ 'value' ];
        }

		return get_option( $prefix . $optionName, $default );
	}

	public static function setOption( $optionName, $optionValue, $multi_tenant_option = true, $autoLoad = null )
	{
		$prefix = 'bkntc_';

		if( Helper::isSaaSVersion() && $multi_tenant_option )
		{
			$tenant = Permission::tenantId();

			if( $tenant > 0 )
			{
				$prefix .= 't' . $tenant . '_';
			}
		}

		return update_option( $prefix . $optionName, $optionValue, $autoLoad );
	}

    public static function setTranslatedOption($translations, $options = [], $multi_tenant_option = true ) {
        $translations = json_decode( $translations, TRUE );

        if ( empty( $translations ) || ! is_array( $translations ) )
        {
            return false;
        }

        foreach ( $translations as $optionName => $translation )
        {
            if ( ! in_array( $optionName, $options ) ) continue;

            foreach ( $translation as $t ) {
                if ( isset( $t[ 'locale' ] ) && isset( $t[ 'value' ] ) )
                {
                    $prevValue = Translation::where( [
                        'column_name' => $optionName,
                        'locale'      => $t[ 'locale' ],
                        'table_name'  => 'options'
                    ])->fetch();

                    if ( $prevValue )
                    {
                        Translation::where( 'id', $prevValue[ 'id' ] )
                            ->update([
                                'value' => $t[ 'value' ]
                            ]);
                    } else
                    {
                        Translation::insert([
                            'table_name'  => 'options',
                            'column_name' => $optionName,
                            'locale'      => $t[ 'locale' ],
                            'value'       => $t[ 'value' ]
                        ]);
                    }
                }
            }

        }

        return true;
    }

    public static function getLocaleForFrontend()
    {
        #WHEN customer use tranlatepress plugin THEN return current locale
        if ( is_plugin_active( 'translatepress-multilingual/index.php' ) )
            return get_locale();

        return self::getLocale();
    }

    public static function getLocale()
    {
        if ( Helper::isSaaSVersion() && !Permission::isSuperAdministrator() )
        {
            return Helper::getOption( 'default_language' );
        }

        return get_locale();
    }

    public static function getLocaleForTenant()
    {
        if ( !empty(Session::get('active_language')) )
        {
            return Session::get('active_language');
        }

        return self::getLocale();
    }

	public static function deleteOption( $optionName, $multi_tenant_option = true )
	{
		$prefix = 'bkntc_';

		if( Helper::isSaaSVersion() && $multi_tenant_option )
		{
			$tenant = Permission::tenantId();

			if( $tenant > 0 )
			{
				$prefix .= 't' . $tenant . '_';
			}
		}

		return delete_option( $prefix . $optionName );
	}

	public static function getBookingStepsOrder($recurringInfoStep = false, $customStepsOrder = null)
	{
		$steps_order = ! empty( $customStepsOrder ) ? $customStepsOrder : Helper::getOption('steps_order', 'location,staff,service,service_extras,date_time,information,cart');

		if( $recurringInfoStep )
		{
			$steps_order = ',' . $steps_order . ',';
			$steps_order = str_replace(',date_time,', ',date_time,recurring_info,', $steps_order);
			$steps_order = trim($steps_order, ',');
		}

		$steps_order = explode(',', $steps_order);

		if( $recurringInfoStep )
		{
			$requiredSteps = explode(',', 'location,staff,service,service_extras,date_time,information,recurring_info,cart,confirm_details,finish');
		}
		else
		{
			$requiredSteps = explode(',', 'location,staff,service,service_extras,date_time,information,cart,confirm_details,finish');
		}

		foreach ( $requiredSteps AS $requiredStep )
		{
			if( !in_array( $requiredStep, $steps_order ) )
			{
				$steps_order[] = $requiredStep;
			}
		}

		foreach ( $steps_order AS $key => $item )
		{
			if( !in_array( $item, $requiredSteps ) )
			{
				array_splice( $steps_order, $key, 1 );
			}
		}

		return $steps_order;
	}

	public static function profileCard( $name, $image, $email, $module, $line_break = false )
	{
		if( $line_break )
		{
			$pos = strpos($name, ' ');

			if ($pos !== false)
			{
				$name = substr_replace( htmlspecialchars( $name ), '<br>', $pos, 1);
			}
		}
		else
		{
			$name = htmlspecialchars( $name );
		}

		return '<div class="user_visit_card">
					<div class="circle_image"><img src="' . Helper::profileImage( $image , $module) . '"></div>
					<div class="user_visit_details">
						<span>' . $name . '</span>
						<span>' . htmlspecialchars( $email ) . '</span>
					</div>
				</div>';
	}

	public static function paymentMethod( $key )
	{
		$paymentGateway = PaymentGatewayService::find( $key );

		if( ! $paymentGateway )
			return htmlspecialchars($key);

		return $paymentGateway->getTitle();
	}

	public static function appointmentStatus( $status )
	{
        $statuses = Helper::getAppointmentStatuses();

        if (array_key_exists($status, $statuses))
            return $statuses[$status];

        return null;
	}

    public static function deFormatPrice ( $price )
    {
        $priceNumberFormat	= self::getOption('price_number_format', '1');

        switch( $priceNumberFormat )
        {
            case '1':
                $price =  str_replace(' ' , '' , $price);
                break;
            case '2':
                $price = str_replace(',' , '' , $price);
                break;
            case '3':
                $price = str_replace([' ' , '.', ','] , ['','x','.'] , $price);
                break;
            case '4':
                $price = str_replace(['.' , ','] , ['','.'] , $price);
                break;
            case '5':
                $price = str_replace('’', '', $price);
                break;
        }

        return preg_match("/^[+-]?[0-9]*(\.[0-9]*)?$/", $price  ) ? $price : null;
    }

	public static function price( $price, $currency = null )
	{
		$scale				= self::getOption('price_number_of_decimals', '2');
		$priceNumberFormat	= self::getOption('price_number_format', '1');

		switch( $priceNumberFormat )
		{
			case '2':
				$decimalPoint		= '.';
				$thousandsSeparator	= ',';
				break;
			case '3':
				$decimalPoint		= ',';
				$thousandsSeparator	= ' ';
				break;
			case '4':
				$decimalPoint		= ',';
				$thousandsSeparator	= '.';
				break;
            case '5':
                $decimalPoint       = '.';
                $thousandsSeparator = '’';
                break;
			default:
				$decimalPoint		= '.';
				$thousandsSeparator	= ' ';
				break;
		}

		$price = Math::floor( $price, $scale);

		$price = number_format($price, $scale, $decimalPoint, $thousandsSeparator);

		$currencyFormat	= self::getOption('currency_format', '1');
        if( $currency === false )
        {
            return $price;
        }
		$currency		= is_null( $currency ) ? self::currencySymbol() : $currency;

		switch ( $currencyFormat )
		{
			case '2':
				return $currency . ' ' . $price;
			case '3':
				return $price . $currency;
			case '4':
				return $price . ' ' . $currency;
			default:
				return $currency . $price;
		}
	}

	public static function currencySymbol( $currency = null )
	{
		$currency_symbol = Helper::getOption('currency_symbol', '');

		if( !empty( $currency_symbol ) )
		{
			return $currency_symbol;
		}

		$currency = is_null( $currency ) ? Helper::getOption('currency', 'USD') : $currency;

		$currencyInf = self::currencies( $currency );

		return isset($currencyInf['symbol']) ? $currencyInf['symbol'] : '$';
	}

	public static function currencies( $currency = null )
	{
		$currencies = [
			'USD' => [ 'name' => 'US Dollar', 'symbol' => '$'],
			'EUR' => [ 'name' => 'Euro', 'symbol' => '€'],
			'GBP' => [ 'name' => 'Pound Sterling', 'symbol' => '£'],
			'AFN' => [ 'name' => 'Afghani', 'symbol' => 'Af'],
			'DZD' => [ 'name' => 'Algerian Dinar', 'symbol' => 'د.ج'],
			'ARS' => [ 'name' => 'Argentine Peso', 'symbol' => '$'],
			'AMD' => [ 'name' => 'Armenian Dram', 'symbol' => 'Դ'],
			'AWG' => [ 'name' => 'Aruban Guilder/Florin', 'symbol' => 'ƒ'],
			'AUD' => [ 'name' => 'Australian Dollar', 'symbol' => '$'],
			'AZN' => [ 'name' => 'Azerbaijani Manat', 'symbol' => '₼'],
			'BSD' => [ 'name' => 'Bahamian Dollar', 'symbol' => '$'],
			'BHD' => [ 'name' => 'Bahraini Dinar', 'symbol' => 'ب.د'],
			'THB' => [ 'name' => 'Baht', 'symbol' => '฿'],
			'PAB' => [ 'name' => 'Balboa', 'symbol' => 'B/.'],
			'BBD' => [ 'name' => 'Barbados Dollar', 'symbol' => '$'],
			'BYN' => [ 'name' => 'Belarusian Ruble', 'symbol' => 'Br'],
			'BZD' => [ 'name' => 'Belize Dollar', 'symbol' => '$'],
			'BMD' => [ 'name' => 'Bermudian Dollar', 'symbol' => '$'],
			'VEF' => [ 'name' => 'Bolivar Fuerte', 'symbol' => 'Bs F'],
			'BOB' => [ 'name' => 'Boliviano', 'symbol' => 'Bs.'],
			'BRL' => [ 'name' => 'Brazilian Real', 'symbol' => 'R$'],
			'BND' => [ 'name' => 'Brunei Dollar', 'symbol' => '$'],
			'BGN' => [ 'name' => 'Bulgarian Lev', 'symbol' => 'лв'],
			'BIF' => [ 'name' => 'Burundi Franc', 'symbol' => '₣'],
			'CAD' => [ 'name' => 'Canadian Dollar', 'symbol' => '$'],
			'CVE' => [ 'name' => 'Cape Verde Escudo', 'symbol' => '$'],
			'KYD' => [ 'name' => 'Cayman Islands Dollar', 'symbol' => '$'],
			'GHS' => [ 'name' => 'Cedi', 'symbol' => '₵'],
			'XAF' => [ 'name' => 'CFA Franc BCEAO', 'symbol' => '₣'],
			'XPF' => [ 'name' => 'CFP Franc', 'symbol' => '₣'],
			'CLP' => [ 'name' => 'Chilean Peso', 'symbol' => '$'],
			'COP' => [ 'name' => 'Colombian Peso', 'symbol' => '$'],
			'CDF' => [ 'name' => 'Congolese Franc', 'symbol' => '₣'],
			'NIO' => [ 'name' => 'Cordoba Oro', 'symbol' => 'C$'],
			'CRC' => [ 'name' => 'Costa Rican Colon', 'symbol' => '₡'],
			'HRK' => [ 'name' => 'Croatian Kuna', 'symbol' => 'Kn'],
			'CUP' => [ 'name' => 'Cuban Peso', 'symbol' => '$'],
			'CZK' => [ 'name' => 'Czech Koruna', 'symbol' => 'Kč'],
			'GMD' => [ 'name' => 'Dalasi', 'symbol' => 'D'],
			'DKK' => [ 'name' => 'Danish Krone', 'symbol' => 'kr'],
			'MKD' => [ 'name' => 'Denar', 'symbol' => 'ден'],
			'DJF' => [ 'name' => 'Djibouti Franc', 'symbol' => '₣'],
			'STN' => [ 'name' => 'Dobra', 'symbol' => 'Db'],
			'DOP' => [ 'name' => 'Dominican Peso', 'symbol' => '$'],
			'VND' => [ 'name' => 'Dong', 'symbol' => '₫'],
			'XCD' => [ 'name' => 'East Caribbean Dollar', 'symbol' => '$'],
			'EGP' => [ 'name' => 'Egyptian Pound', 'symbol' => '£'],
			'ETB' => [ 'name' => 'Ethiopian Birr', 'symbol' => 'ETB'],
			'FKP' => [ 'name' => 'Falkland Islands Pound', 'symbol' => '£'],
			'FJD' => [ 'name' => 'Fiji Dollar', 'symbol' => '$'],
			'HUF' => [ 'name' => 'Hungarian Forint', 'symbol' => 'Ft'],
			'GIP' => [ 'name' => 'Gibraltar Pound', 'symbol' => '£'],
			'HTG' => [ 'name' => 'Gourde', 'symbol' => 'G'],
			'PYG' => [ 'name' => 'Guarani', 'symbol' => '₲'],
			'GNF' => [ 'name' => 'Guinea Franc', 'symbol' => '₣'],
			'GYD' => [ 'name' => 'Guyana Dollar', 'symbol' => '$'],
			'HKD' => [ 'name' => 'Hong Kong Dollar', 'symbol' => '$'],
			'UAH' => [ 'name' => 'Hryvnia', 'symbol' => '₴'],
			'ISK' => [ 'name' => 'Iceland Krona', 'symbol' => 'Kr'],
			'INR' => [ 'name' => 'Indian Rupee', 'symbol' => '₹'],
			'IRR' => [ 'name' => 'Iranian Rial', 'symbol' => '﷼'],
			'IQD' => [ 'name' => 'Iraqi Dinar', 'symbol' => 'ع.د'],
			'JMD' => [ 'name' => 'Jamaican Dollar', 'symbol' => '$'],
			'JOD' => [ 'name' => 'Jordanian Dinar', 'symbol' => 'د.ا'],
			'KES' => [ 'name' => 'Kenyan Shilling', 'symbol' => 'Sh'],
			'PGK' => [ 'name' => 'Kina', 'symbol' => 'K'],
			'LAK' => [ 'name' => 'Kip', 'symbol' => '₭'],
			'BAM' => [ 'name' => 'Konvertibilna Marka', 'symbol' => 'КМ'],
			'KWD' => [ 'name' => 'Kuwaiti Dinar', 'symbol' => 'د.ك'],
			'MWK' => [ 'name' => 'Kwacha', 'symbol' => 'MK'],
			'AOA' => [ 'name' => 'Kwanza', 'symbol' => 'Kz'],
			'MMK' => [ 'name' => 'Kyat', 'symbol' => 'K'],
			'GEL' => [ 'name' => 'Lari', 'symbol' => 'ლ'],
			'LBP' => [ 'name' => 'Lebanese Pound', 'symbol' => 'ل.ل'],
			'ALL' => [ 'name' => 'Lek', 'symbol' => 'L'],
			'HNL' => [ 'name' => 'Lempira', 'symbol' => 'L'],
			'SLL' => [ 'name' => 'Leone', 'symbol' => 'Le'],
			'RON' => [ 'name' => 'Leu', 'symbol' => 'L'],
			'LRD' => [ 'name' => 'Liberian Dollar', 'symbol' => '$'],
			'LYD' => [ 'name' => 'Libyan Dinar', 'symbol' => 'ل.د'],
			'SZL' => [ 'name' => 'Lilangeni', 'symbol' => 'L'],
			'LSL' => [ 'name' => 'Loti', 'symbol' => 'L'],
			'MGA' => [ 'name' => 'Malagasy Ariary', 'symbol' => 'MGA'],
			'MYR' => [ 'name' => 'Malaysian Ringgit', 'symbol' => 'RM'],
			'TMT' => [ 'name' => 'Manat', 'symbol' => 'm'],
			'MUR' => [ 'name' => 'Mauritius Rupee', 'symbol' => '₨'],
			'MZN' => [ 'name' => 'Metical', 'symbol' => 'MTn'],
			'MXN' => [ 'name' => 'Mexican Peso', 'symbol' => '$'],
			'MDL' => [ 'name' => 'Moldovan Leu', 'symbol' => 'L'],
			'MAD' => [ 'name' => 'Moroccan Dirham', 'symbol' => 'د.م.'],
			'NGN' => [ 'name' => 'Naira', 'symbol' => '₦'],
			'ERN' => [ 'name' => 'Nakfa', 'symbol' => 'Nfk'],
			'NAD' => [ 'name' => 'Namibia Dollar', 'symbol' => '$'],
			'NPR' => [ 'name' => 'Nepalese Rupee', 'symbol' => '₨'],
			'ILS' => [ 'name' => 'New Israeli Shekel', 'symbol' => '₪'],
			'NZD' => [ 'name' => 'New Zealand Dollar', 'symbol' => '$'],
			'BTN' => [ 'name' => 'Ngultrum', 'symbol' => 'BTN'],
			'KPW' => [ 'name' => 'North Korean Won', 'symbol' => '₩'],
			'NOK' => [ 'name' => 'Norwegian Krone', 'symbol' => 'kr'],
			'PEN' => [ 'name' => 'Nuevo Sol', 'symbol' => 'S/.'],
			'MRU' => [ 'name' => 'Ouguiya', 'symbol' => 'UM'],
			'TOP' => [ 'name' => 'Pa’anga', 'symbol' => 'T$'],
			'PKR' => [ 'name' => 'Pakistan Rupee', 'symbol' => '₨'],
			'MOP' => [ 'name' => 'Pataca', 'symbol' => 'P'],
			'UYU' => [ 'name' => 'Peso Uruguayo', 'symbol' => 'UYU'],
			'PHP' => [ 'name' => 'Philippine Peso', 'symbol' => '₱'],
			'BWP' => [ 'name' => 'Pula', 'symbol' => 'P'],
			'PLN' => [ 'name' => 'PZloty', 'symbol' => 'zł'],
			'QAR' => [ 'name' => 'Qatari Rial', 'symbol' => 'ر.ق'],
			'GTQ' => [ 'name' => 'Quetzal', 'symbol' => 'Q'],
			'ZAR' => [ 'name' => 'Rand', 'symbol' => 'R'],
			'OMR' => [ 'name' => 'Rial Omani', 'symbol' => 'ر.ع.'],
			'KHR' => [ 'name' => 'Riel', 'symbol' => '៛'],
			'MVR' => [ 'name' => 'Rufiyaa', 'symbol' => 'ރ.'],
			'IDR' => [ 'name' => 'Rupiah', 'symbol' => 'Rp'],
			'RUB' => [ 'name' => 'Russian Ruble', 'symbol' => '₽'],
			'RWF' => [ 'name' => 'Rwanda Franc', 'symbol' => '₣'],
			'SHP' => [ 'name' => 'Saint Helena Pound', 'symbol' => '£'],
			'SAR' => [ 'name' => 'Saudi Riyal', 'symbol' => 'ر.س'],
			'RSD' => [ 'name' => 'Serbian Dinar', 'symbol' => 'din'],
			'SCR' => [ 'name' => 'Seychelles Rupee', 'symbol' => '₨'],
			'SGD' => [ 'name' => 'Singapore Dollar', 'symbol' => '$'],
			'SBD' => [ 'name' => 'Solomon Islands Dollar', 'symbol' => '$'],
			'KGS' => [ 'name' => 'Som', 'symbol' => 'KGS'],
			'SOS' => [ 'name' => 'Somali Shilling', 'symbol' => 'Sh'],
			'TJS' => [ 'name' => 'Somoni', 'symbol' => 'ЅМ'],
			'KRW' => [ 'name' => 'South Korean Won', 'symbol' => '₩'],
			'LKR' => [ 'name' => 'Sri Lanka Rupee', 'symbol' => 'Rs'],
			'SDG' => [ 'name' => 'Sudanese Pound', 'symbol' => '£'],
			'SRD' => [ 'name' => 'Suriname Dollar', 'symbol' => '$'],
			'SEK' => [ 'name' => 'Swedish Krona', 'symbol' => 'kr'],
			'CHF' => [ 'name' => 'Swiss Franc', 'symbol' => 'CHF'],
			'SYP' => [ 'name' => 'Syrian Pound', 'symbol' => 'ل.س'],
			'TWD' => [ 'name' => 'Taiwan Dollar', 'symbol' => '$'],
			'BDT' => [ 'name' => 'Taka', 'symbol' => '৳'],
			'WST' => [ 'name' => 'Tala', 'symbol' => 'T'],
			'TZS' => [ 'name' => 'Tanzanian Shilling', 'symbol' => 'Sh'],
			'KZT' => [ 'name' => 'Tenge', 'symbol' => '〒'],
			'TTD' => [ 'name' => 'Trinidad and Tobago Dollar', 'symbol' => '$'],
			'MNT' => [ 'name' => 'Tugrik', 'symbol' => '₮'],
			'TND' => [ 'name' => 'Tunisian Dinar', 'symbol' => 'د.ت'],
			'TRY' => [ 'name' => 'Turkish Lira', 'symbol' => '₺'],
			'AED' => [ 'name' => 'UAE Dirham', 'symbol' => 'د.إ'],
			'UGX' => [ 'name' => 'Uganda Shilling', 'symbol' => 'Sh'],
			'UZS' => [ 'name' => 'Uzbekistan Sum', 'symbol' => 'UZS'],
			'VUV' => [ 'name' => 'Vatu', 'symbol' => 'Vt'],
			'YER' => [ 'name' => 'Yemeni Rial', 'symbol' => '﷼'],
			'JPY' => [ 'name' => 'Yen', 'symbol' => '¥'],
			'CNY' => [ 'name' => 'Yuan', 'symbol' => '¥'],
			'ZMW' => [ 'name' => 'Zambian Kwacha', 'symbol' => 'ZK'],
			'ZWL' => [ 'name' => 'Zimbabwe Dollar', 'symbol' => '$']
		];

		if( is_null( $currency ) )
		{
			return $currencies;
		}
		else
		{
			return isset( $currencies[ $currency ] ) ? $currencies[ $currency ] : false;
		}
	}

	public static function assocByKey( $array, $key, $multipleData = false )
	{
		$newArr = [];
		$array = is_array( $array ) ? $array : [];

		foreach ( $array AS $data )
		{
			$keyValue = isset( $data[ $key ] ) ? $data[ $key ] : '-';

			// filters...
			if( $key == 'date' )
			{
				$keyValue = $keyValue == '-' ? '-' : Date::dateSQL( $keyValue );
			}

			if( $multipleData )
			{
				if( !isset($newArr[ $keyValue ]) )
				{
					$newArr[ $keyValue ] = [];
				}

				$newArr[ $keyValue ][] = $data;
			}
			else
			{
				$newArr[ $keyValue ] = $data;
			}
		}

		return $newArr;
	}

	public static function pluginTables()
	{
		return [
			'appearance',
            'appointment_extras',
			'customers',
            'appointment_prices',
			'appointments',
			'holidays',
			'locations',
            'workflow_logs',
            'workflow_actions',
            'workflows',
			'service_categories',
			'service_extras',
			'service_staff',
			'special_days',
			'timesheet',
			'services',
			'staff',
            'data',
            'translations',
            'service_extra_categories',
            'cart'
		];
	}

	public static function uninstallPlugin()
	{
		$purchaseKey = self::getOption('purchase_code' , '', false);

		$checkPurchaseCodeURL = Backend::API_URL . "?act=delete&purchase_code=" . urlencode( $purchaseKey ) . "&domain=" . site_url();

		wp_remote_get( $checkPurchaseCodeURL );

		// drop tables...
		$deleteTables = self::pluginTables();

		foreach( $deleteTables AS $tableName )
		{
			DB::DB()->query("DROP TABLE IF EXISTS `" . DB::table( $tableName ) . "`");
		}

		// delete options...
		DB::DB()->query('DELETE FROM `'.DB::DB()->base_prefix.'options` WHERE `option_name` LIKE \'bkntc_%\'');
	}

	public static function redirect( $url )
	{
		header('Location: ' . $url);
		exit();
	}

	public static function getAllSubCategories( $category )
	{
		$arr = [ (int)$category ];

		$subCategories = ServiceCategory::where('parent_id', $category)->fetchAll();
		foreach ( $subCategories AS $subCategory )
		{
			$arr = array_merge( $arr, self::getAllSubCategories( $subCategory['id'] ) );
		}

		return $arr;
	}

	public static function secureFileFormats( $formats )
	{
		$newFormats = [];
		foreach ( $formats AS $format )
		{
			$format = strtolower($format);

			if( !preg_match( '/^(php[0-9]*)|(htaccess)|(htpasswd)|(ini)$/', $format ) )
			{
				$newFormats[] = $format;
			}
		}

		return $newFormats;
	}

    /***
     * @deprecated
     */
	public static function customerPanelURL()
	{
		$customerPanelPageID = Helper::getOption('customer_panel_page_id', '', false);

		if( empty( $customerPanelPageID ) )
			return '';

		return get_page_link( (int)$customerPanelPageID );
	}

    public static function getRedirectURL()
    {
        $currentPage          = Helper::isSaaSVersion() ? home_url( '/' . \BookneticSaaS\Providers\Helpers\Helper::getCurrentDomain() ) : get_permalink();
        $signPageForCustomers = Helper::getOption( 'regular_sing_in_page', '', false );

        if ( ! empty( $signPageForCustomers ) )
        {
            $redirectPage = urlencode( $currentPage );

            setcookie( 'SigninRedirectURL', $redirectPage, strtotime( '+1 day' ), '/' );

            return add_query_arg( 'redirect_to', $redirectPage, get_permalink( $signPageForCustomers ) );
        }

        return wp_login_url();
    }

    public static function getBookneticJSData()
    {
        $localization = [
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

            // messages
            'select_location'       => bkntc__('Please select location.'),
            'select_staff'          => bkntc__('Please select staff.'),
            'select_service'        => bkntc__('Please select service'),
            'select_week_days'      => bkntc__('Please select week day(s)'),
            'date_time_is_wrong'    => bkntc__('Please select week day(s) and time(s) correctly'),
            'select_start_date'     => bkntc__('Please select start date'),
            'select_end_date'       => bkntc__('Please select end date'),
            'select_date'           => bkntc__('Please select date.'),
            'select_time'           => bkntc__('Please select time.'),
            'select_available_time' => bkntc__('Please select an available time'),
            'select_available_date' => bkntc__('Please select an available date'),
            'fill_all_required'     => bkntc__('Please fill in all required fields correctly!'),
            'email_is_not_valid'    => bkntc__('Please enter a valid email address!'),
            'phone_is_not_valid'    => bkntc__('Please enter a valid phone number!'),
            'Select date'           => bkntc__('Select date'),
            'NEXT STEP'             => bkntc__('NEXT STEP'),
            'CONFIRM BOOKING'       => bkntc__('CONFIRM BOOKING'),
        ];

        return [
            'ajax_url'		            => admin_url( 'admin-ajax.php' ),
            'assets_url'	            => Helper::assets('/', 'front-end') ,
            'date_format'	            => Helper::getOption('date_format', 'Y-m-d'),
            'week_starts_on'            => Helper::getOption('week_starts_on', 'sunday') == 'monday' ? 'monday' : 'sunday',
            'client_time_zone'	        => htmlspecialchars(Helper::getOption('client_timezone_enable', 'off')),
            'skip_extras_step_if_need'  => htmlspecialchars(Helper::getOption('skip_extras_step_if_need', 'on')),
            'localization'              => apply_filters('bkntc_frontend_localization' , $localization ),
            'tenant_id'                 => Permission::tenantId(),
            'settings'                  => [
                'redirect_users_on_confirm' => Helper::getOption( 'redirect_users_on_confirm', 'off' ) === 'on',
                'redirect_users_on_confirm_url' => Helper::getOption( 'redirect_users_on_confirm_url', '' )
            ]
        ];
    }

	public static function isSaaSVersion()
	{
		return class_exists( '\BookneticSaaS\Providers\Core\Bootstrap' );
	}

	public static function renderView( $view_path, $parameters = [] )
	{
		$viewsPath = file_exists( $view_path ) ? $view_path : Backend::MODULES_DIR . str_replace( '.', DIRECTORY_SEPARATOR, $view_path ) . '.php';

		if( !file_exists( $viewsPath ) )
		{
			return bkntc__( 'View ( %s ) not found!', [ $view_path ] );
		}

		ob_start();
		require $viewsPath;
		$viewOutput = ob_get_clean();

		return $viewOutput;
	}

	public static function is_ajax()
	{
		return defined('DOING_AJAX') && DOING_AJAX;
	}

	public static function is_update_process()
	{
		$isUpdate = Helper::_post('action', '', 'string') == 'update-plugin';

		if( $isUpdate && Helper::_post('slug', '', 'string') == Backend::getSlugName() )
		{
			set_time_limit(150);
		}

		return $isUpdate;
	}

	public static function checkUserRole( $userInfo, $roles )
	{
		$roles = is_array( $roles ) ? $roles : (array)$roles;

		foreach( $roles AS $checkRole )
		{
			if( in_array( $checkRole, $userInfo->roles ) )
			{
				return true;
			}
		}

		return false;
	}

	// doit: bu argumentlerden birshey anlamadim men.
    public static function isRTLLanguage( $tenant_id = 0, $is_backend = false, $session_lang = '' )
    {
        $default_language = '';

        if($is_backend && $session_lang != '')
        {
            $default_language = $session_lang;
        }
        else if (self::isSaaSVersion() && $tenant_id > 0)
        {
            $default_language = self::getOption('default_language' , 'en', $tenant_id);
        }
        else if( !self::isSaaSVersion() && is_rtl() )
        {
            return true;
        }
         $rtl_languages = [
                'ar',
                'ary',
                'azb',
                'ckb',
                'fa_AF',
                'fa_IR',
                'haz',
                'ps',
                'ug_CN',
                'ur',
                'he_IL'
            ];

            if( in_array( $default_language, $rtl_languages ) )
            {
                return true;
            }

        return false;
    }

	/**
	 * Converts camelCase to kebab-case
	 *
	 * @param $camelString
	 *
	 * @return string
	 */
	public static function camelToKebab ( $camelString )
	{
		preg_match_all( '/([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)/', $camelString, $parts );

		foreach ( $parts[ 0 ] as &$part )
		{
			$part = ( $part === strtoupper( $part ) ? strtolower( $part ) : lcfirst( $part ) );
		}

		return implode( '-', $parts[0] );
	}

	public static function snakeCaseToCamel( $snakeCaseString )
	{
		return lcfirst( str_replace(' ', '', ucwords( str_replace('_', ' ', $snakeCaseString) ) ) );
	}

	/**
	 * Checks if user in admin panel
	 *
	 * @return bool
	 */
	public static function isAdmin()
	{
		if ( is_admin() )
		{
			return true;
		}

		global $current_screen;

		if ( isset( $current_screen ) && method_exists( $current_screen, 'is_block_editor' ) && $current_screen->is_block_editor() )
		{
			return true;
		}

		return false;
	}

	public static function getSlugName()
	{
		return Backend::getSlugName();
	}

    public static function getAppointmentStatuses(): array
    {
        $statuses = [
            [
                'slug'      => 'pending',
                'title'     => bkntc__( 'Pending' ),
                'color'     => '#fd9b78',
                'icon'      => 'far fa-clock',
                'busy'      => true,
            ],
            [
                'slug'      => 'approved',
                'title'     => bkntc__( 'Approved' ),
                'color'     => '#53d56c',
                'icon'      => 'fa fa-check',
                'busy'      => true,
            ],
            [
                'slug'      => 'canceled',
                'title'     => bkntc__( 'Canceled' ),
                'color'     => '#fb3e6e',
                'icon'      => 'fa fa-times',
                'busy'      => false,
            ],
            [
                'slug'      => 'rejected',
                'title'     => bkntc__( 'Rejected' ),
                'color'     => '#8f9ca7',
                'icon'      => 'fa fa-times',
                'busy'      => false,
            ],
        ];

        $statuses = apply_filters( 'bkntc_appointment_statuses', $statuses );

        return Helper::assocByKey( $statuses, 'slug' );
    }

    public static function getBusyAppointmentStatuses(): array
    {
        $busyStatuses = array_filter( Helper::getAppointmentStatuses(), function ( $item )
        {
            return $item['busy'];
        });

        $busyStatuses = array_keys( $busyStatuses );

        return empty( $busyStatuses ) ? ['-'] : $busyStatuses;
    }

    public static function getAllAppointmentStatuses(): array
    {
        $allStatuses = array_keys( Helper::getAppointmentStatuses() );

        return empty( $allStatuses ) ? ['-'] : $allStatuses;
    }

    public static function getDefaultAppointmentStatus()
    {
        $status = Helper::getOption( 'default_appointment_status' );

        if ( empty( $status ) || ! key_exists( $status, Helper::getAppointmentStatuses() ) )
        {
            $status = array_keys( Helper::getAppointmentStatuses() )[0];
        }

        return $status;
    }

    public static function showChangelogs ()
    {
        if (Helper::isSaaSVersion() || !Permission::isAdministrator()) return false;

        $changelogsURL = Helper::getOption( 'changelogs_url', false, false );

        if ( ! empty( $changelogsURL ) )
        {
            Helper::deleteOption( 'changelogs_url', false );
        }

        return $changelogsURL;
    }

    public static function getBackendSlug ()
    {
        return Helper::isSaaSVersion() && Permission::isSuperAdministrator() ? 'booknetic-saas' : self::getSlugName();
    }

    public static function getHostName( $url = null )
    {
        $url = is_null( $url ) ? site_url() : $url;
        return trim( $url, '/' );
    }

    public static function generateToken ( array $headers, array $payload, $secret) {

        $headers_encoded = rtrim( strtr( base64_encode( json_encode( $headers ) ), '+/', '-_' ), '=' );

        $payload_encoded = rtrim( strtr( base64_encode( json_encode( $payload ) ), '+/', '-_' ), '=' );

        $signature = hash_hmac('SHA256', "$headers_encoded.$payload_encoded", $secret, true);

        $signature_encoded = rtrim (strtr ( base64_encode ( $signature ) , '+/', '-_' ), '=' );

        $token = "$headers_encoded.$payload_encoded.$signature_encoded";

        return $token;

    }

    public static function validateToken ($token, $secret) {

        $tokenParts = explode('.', $token);

        $header = base64_decode($tokenParts[0]);
        $payload = base64_decode($tokenParts[1]);
        $signature_provided = $tokenParts[2];

        $base64_url_header = rtrim (strtr ( base64_encode ( $header ) , '+/', '-_' ), '=' );
        $base64_url_payload = rtrim (strtr ( base64_encode ( $payload ) , '+/', '-_' ), '=' );

        $signature = hash_hmac('SHA256', $base64_url_header . "." . $base64_url_payload, $secret, true);

        $base64_url_signature = rtrim (strtr ( base64_encode ( $signature ) , '+/', '-_' ), '=' );

        $is_signature_valid = hash_equals($base64_url_signature, $signature_provided);

        return $is_signature_valid;

    }

    public static function getURLOfUsersDashboard( $user = null )
    {
        if( is_null( $user ) )
        {
            $user = wp_get_current_user();
        }

        if( in_array( 'booknetic_saas_tenant', $user->roles ) || in_array( 'booknetic_staff', $user->roles ))
            return admin_url( '/admin.php?page=' . self::getSlugName() );

        if( ! in_array( 'booknetic_customer', $user->roles ) )
            return admin_url( '/' );

        $redirectUrl = Helper::_post( 'redirect','','string' );

        if( ! empty( $redirectUrl ) )
            return $redirectUrl;

        // todo:// bu deprecated method hook-a kecmelidi.
        return self::customerPanelURL() ?: site_url();
    }

    public static function timeslotsAsMinutes(): array
    {
        return [ 1,2,3,4,5,10,15,20,25,30,35,40,45,50,55,60,90,120,180,240,300,360,420,480,540,600,660,720,1440,2880,4320,5760,7200,8640,10080,11520,12960,14400,15840,17280,18720,20160,21600,23040,24480,25920,27360,28800,30240,31680,33120,34560,36000,37440,38880,40320,41760,43200 ];
    }

    public static function canShowTemplates(): bool
    {
        $notSelected = Helper::getOption( 'selected_a_template', '0' ) === '0';

        //if it's a regular version, return the option's value
        if ( ! Helper::isSaaSVersion() )
            return $notSelected;

        //check if saas has the relevant addon activated
        $haveAddon = apply_filters( 'bkntc_template_exists', false );

        //if no addon's found don't show the modal
        if ( ! $haveAddon )
            return false;

        //if addon's there, return the option's value
        return $notSelected;
    }

    public static function getMinTimeRequiredPriorBooking( ?int $sid = null, string $default = '0' ): string
    {
        $defaultMTRPB = self::getOption( 'min_time_req_prior_booking', $default );

        if ( is_null( $sid ) ) return $defaultMTRPB;

        $serviceSpecificMTRPB = Service::getData( $sid, 'minimum_time_required_prior_booking', -1 );

        return ( intval( $serviceSpecificMTRPB ) !== -1 )
            ? $serviceSpecificMTRPB
            : $defaultMTRPB;
    }

	public static function getPaymentStatuses(): array
	{
		return [
			[
				'slug'  => 'paid',
				'title' => bkntc__( 'Paid' )
			],
			[
				'slug'  => 'canceled',
				'title' => bkntc__( 'Canceled' )
			],
			[
				'slug'  => 'pending',
				'title' => bkntc__( 'Pending' )
			],
			[
				'slug'  => 'not_paid',
				'title' => bkntc__( 'Not Paid' )
			]
		];
	}

    /**
     * Prevents calendar to load from past month
     *
     * @param int $month
     * @param int $year
     * @return int
     */
    public static function getAdjustedYearByGivenDefaultStartMonth( int $month, int $year ) : int
    {
        $currentMonth = \date('n');
        $currentYear = \date("Y");

        if ( $month < $currentMonth && $year == $currentYear )
            return $year + 1;

        return $year;
    }

    public static function getLastBillingInfo( int $customerId ): ?array
    {
        foreach( Appointment::where( 'customer_id', $customerId )->orderBy( 'id DESC' )->fetchAll() as $a )
        {
            $billingData = Data::where( 'row_id', $a->id )
                ->where( 'table_name', Appointment::getTableName() )
                ->where( 'data_key', 'customer_billing_data' )->fetch();

            if ( is_null( $billingData ) )
                continue;
            else
                return json_decode( $billingData->data_value, true);
        }

        return null;
    }

    /**
     * @param array $info
     * @return string
     */
    public static function encodeInfo( array $info ): string
    {
        $infoSecret = 'dwye7tfg2ye9836djhqhd83287tr26r2e283e6328';

        return hash( 'sha256', json_encode( $info ) . $infoSecret ) . ':' . base64_encode( json_encode( $info ) );
    }

    /**
     * @param string $info
     * @return array|null
     */
    public static function decodeInfo( string $info ): ?array
    {
		if ( empty( $info ) )
		{
			return null;
		}

        $infoSecret = 'dwye7tfg2ye9836djhqhd83287tr26r2e283e6328';

        $infoDecoded = ( base64_decode( explode( ':', $info )[ 1 ] ) );

        if ( hash('sha256', $infoDecoded . $infoSecret ) === explode( ':', $info )[ 0 ] )
        {
            return json_decode( $infoDecoded, true );
        }
        else
        {
            return null;
        }
    }
}
