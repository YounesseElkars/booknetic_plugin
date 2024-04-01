<?php

namespace
{

	/**
	 * @param $text
	 * @param $params
	 * @param $esc
	 * @param $textdomain
	 *
	 * @return mixed
	 */
	function bkntc__( $text, $params = [], $esc = true, $textdomain = 'booknetic' )
	{
		if( empty( $params ) )
		{
			$result = trim( __($text, $textdomain ) );
		}
		else
		{
			$args = array_merge( [ trim( __($text, $textdomain ) ) ] , (array)$params );
			$result =  call_user_func_array('sprintf', $args );
		}

        return $esc ? htmlspecialchars($result) : $result;
	}
}

namespace BookneticApp\Providers\Core
{

	use BookneticApp\Config;
	use BookneticApp\Providers\Core\Backend;
	use BookneticApp\Providers\Core\Frontend;
	use BookneticApp\Providers\Helpers\Helper;
	use BookneticApp\Providers\Helpers\Curl;

    /**
	 * Class Bootstrap
	 * @package BookneticApp
	 */
	class Bootstrap
	{

        /**
         * @var AddonLoader[]
         */
        public static $addons = [];

		public function __construct()
		{
            if ( Helper::getOption( 'is_updating', null, false ) !== null )
            {
                add_action( 'admin_notices', function () {
                    echo '<div class="notice notice-warning"><p>' . bkntc__( 'Booknetic is updating, please wait.' ) . '</p></div>';
                } );
                return;
            }

			Config::load();

			if( ! $this->isInstalled() )
			{
				add_action('init', [$this, 'initPluginInstallationPage']);
			}
			else
			{
				if ( $this->checkLicense() === false )
				{
					add_action('init', [$this, 'initPluginDisabledPage']);
				}
				else
				{
                    add_action('plugins_loaded', function ()
                    {
                        static::$addons = apply_filters( 'bkntc_addons_load', [] );
                    });

					add_action('init', [$this, 'initApp'], 10);
				}
			}
		}

		public function initApp()
		{
            Backend::updateAddonsDB();

			do_action( 'bkntc_init' );

			if ( !Helper::isAdmin() || ( Helper::is_ajax() && !Helper::is_update_process() ) )
			{
				Frontend::init();
			}
			else if( Helper::isAdmin() )
			{
				Backend::init();
			}

            CronJob::init();
		}

		public function initPluginInstallationPage()
		{
			if( Helper::isAdmin() )
			{
				Backend::initInstallation();
			}
		}

		public function initPluginDisabledPage()
		{
			if( Helper::isAdmin() )
			{
				Backend::initDisabledPage();
			}
		}

		private function isInstalled()
		{
			$purchase_code = Helper::getOption('purchase_code', '', false);
			$version = Helper::getOption('plugin_version', '', false);

			if( empty( $purchase_code ) && empty( $version ) )
				return false;

			return true;
		}

		private function fetchLicenseStatus ()
		{
			$lastTime = Helper::getOption( 'license_last_checked_time', 0, false );

			if ( time() - $lastTime < 10 * 60 * 60 )
			{
				return;
			}

			$purchaseCode = Helper::getOption( 'purchase_code', '', false );

			$checkPurchaseCodeURL = Backend::API_URL . "?act=get_notifications&purchase_code=" . $purchaseCode . "&domain=" . site_url();
			$result2              = Curl::getURL( $checkPurchaseCodeURL );
			$result               = json_decode( $result2, true );

			if ( ! isset( $result ) || empty( $result ) || ! isset( $result[ 'action' ] ) )
			{
				return;
			}

			if ( $result[ 'action' ] === 'empty' )
			{
				Helper::setOption( 'plugin_alert', '', false );
				Helper::setOption( 'plugin_disabled', '0', false );
			}
			else if ( $result[ 'action' ] === 'warning' && ! empty( $result[ 'message' ] ) )
			{
				Helper::setOption( 'plugin_alert', $result[ 'message' ], false );
				Helper::setOption( 'plugin_disabled', '0', false );
			}
			else if ( $result[ 'action' ] === 'disable' )
			{
				if ( ! empty( $result[ 'message' ] ) )
				{
					Helper::setOption( 'plugin_alert', $result[ 'message' ], false );
				}

				Helper::setOption( 'plugin_disabled', '1', false );
			}
			else if ( $result[ 'action' ] === 'error' )
			{
				if ( ! empty( $result[ 'message' ] ) )
				{
					Helper::setOption( 'plugin_alert', $result[ 'message' ], false );
				}

				Helper::setOption( 'plugin_disabled', '2', false );
			}

			if ( ! empty( $result[ 'remove_license' ] ) )
			{
				Helper::deleteOption( 'purchase_code', false );
			}

			Helper::setOption( 'license_last_checked_time', time(), false );
		}

		private function checkLicense()
		{
			$this->fetchLicenseStatus();

			$alert    = Helper::getOption( 'plugin_alert', '', false );
			$disabled = Helper::getOption( 'plugin_disabled', '0', false );

			if ( $disabled === '1' )
			{
				return false;
			}
			else if ( $disabled === '2' )
			{
				if ( ! empty( $alert ) )
				{
					echo $alert;
				}

				exit();
			}

			if ( ! empty( $alert ) )
			{
				add_action( 'admin_notices', function () use ( $alert )
				{
					echo '<div class="notice notice-error"><p>'.$alert.'</p></div>';
				});
			}

			return true;
		}

	}

}
