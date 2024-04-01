<?php

namespace BookneticApp;

use BookneticApp\Backend\Appointments\Helpers\AppointmentChangeStatus;
use BookneticApp\Backend\Settings\Helpers\LocalizationService;
use BookneticApp\Backend\Workflow\Actions\SetBookingStatusAction;
use BookneticApp\Models\Appointment;
use BookneticApp\Models\Staff;
use BookneticApp\Providers\Common\Divi\includes\BookneticDivi;
use BookneticApp\Providers\Common\Elementor\BookneticElementor;
use BookneticApp\Providers\Common\LocalPayment;
use BookneticApp\Providers\Common\PaymentGatewayService;
use BookneticApp\Providers\Common\ShortCodeService;
use BookneticApp\Providers\Common\ShortCodeServiceImpl;
use BookneticApp\Providers\Common\WorkflowDriversManager;
use BookneticApp\Providers\Common\WorkflowEventsManager;
use BookneticApp\Providers\Core\CapabilitiesException;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Helpers\Session;
use BookneticApp\Providers\UI\MenuUI;
use BookneticApp\Providers\Core\Capabilities;
use BookneticApp\Providers\Core\Permission;
use BookneticApp\Providers\Core\Route;
use BookneticApp\Providers\UI\TabUI;
use function Sodium\add;

class Config
{
    /**
     * @var WorkflowDriversManager
     */
    private static $workflowDriversManager;

    /**
     * @var WorkflowEventsManager
     */
    private static $workflowEventsManager;

    /**
     * @var ShortCodeService
     */
    private static $shortCodeService;

    private static $capabilityCache;

    /**
     * @return WorkflowDriversManager
     */
    public static function getWorkflowDriversManager()
    {
        return self::$workflowDriversManager;
    }

    /**
     * @return WorkflowEventsManager
     */
    public static function getWorkflowEventsManager()
    {
        return self::$workflowEventsManager;
    }

    /**
     * @return ShortCodeService
     */
    public static function getShortCodeService()
    {
        return self::$shortCodeService;
    }

    public static function getCapabilityCache()
    {
        return self::$capabilityCache;
    }

    public static function setCapabilityCache($capabilityCache)
    {
        self::$capabilityCache = $capabilityCache;
    }

    public static function load()
    {
        self::$shortCodeService = new ShortCodeService();
        self::$workflowDriversManager = new WorkflowDriversManager();

        self::$workflowEventsManager = new WorkflowEventsManager();
        self::$workflowEventsManager->setDriverManager( self::$workflowDriversManager );
        self::$workflowEventsManager->setShortcodeService( self::$shortCodeService );

        self::registerTextDomain();

        add_action( 'bkntc_init', [ self::class, 'init' ] );


        add_action( 'elementor/widgets/register', [BookneticElementor::class, 'registerWidgets'] );
        add_action( 'activated_plugin', [self::class , 'detectPluginActivation'], 10, 2 );
        add_action( 'divi_extensions_init', function (){ new BookneticDivi; } );

        add_action( 'template_include', function ($template){
            if( isset($_GET['bkntc_preview']) && $_SERVER['REQUEST_METHOD'] === 'POST')
            {
                $shortcode = Helper::_post('shortcode','','str');
                echo do_shortcode( $shortcode );
                print_late_styles();
                print_footer_scripts();
                exit;
            }
            return $template;
        } );

        add_action( 'profile_update', [ self::class, 'detectUserUpdate' ], 10, 2 );
        add_action( 'profile_update', [ self::class, 'detectProfileUpdate' ], 10, 1 );

    }

    public static function init ()
    {

        self::registerCoreUserCapabilites();
        self::registerCoreTenantCapabilites();
        self::registerCoreShortCodes();
        self::registerCoreWorkflowEvents();
        self::registerCoreWorkflowActions();
        self::registerLocalPaymentGateway();
        self::registerCorePricesName();
        self::registerWPUserRoles();
        //self::registerSigningPage(); wp-admin redirect etməsin deyə kommentlənib
        self::registerHardCodedUserRules();

        add_action( 'bkntc_backend', [ self::class, 'registerCoreRoutes' ] );
        add_action( 'bkntc_backend', [ self::class, 'registerCoreMenus' ] );

        add_filter('woocommerce_prevent_admin_access', function ()
        {
            return false;
        });

    }

    public static function registerTextDomain()
    {
        add_action( 'plugins_loaded', function()
        {
            $path = 'booknetic/languages';

            if ( Helper::isSaaSVersion() && ! Permission::isSuperAdministrator() && Permission::tenantId() > 0 )
            {
                $tenantId = Permission::tenantId();

                if( file_exists( WP_PLUGIN_DIR . '/' . $path . '/' . $tenantId . '/booknetic-' . get_locale() . '.mo' ) )
                {
                    $path .= '/' . $tenantId;
                }
            }

            load_plugin_textdomain( 'booknetic', false, $path );

            if( Helper::isSaaSVersion() )
            {
                $language = Session::get('active_language');
                LocalizationService::setLanguage( $language );
            }
        });
    }

    public static function registerCoreUserCapabilites()
    {
        Capabilities::register( 'dashboard', bkntc__('Dashboard module') );

        Capabilities::register( 'appointments', bkntc__('Appointments module') );
        Capabilities::register( 'appointments_add', bkntc__('Add new'), 'appointments' );
        Capabilities::register( 'appointments_edit', bkntc__('Edit'), 'appointments' );
        Capabilities::register( 'appointments_delete', bkntc__('Delete'), 'appointments' );
        Capabilities::register( 'appointment_book_outside_working_hours', bkntc__('Book outside working hours'), 'appointments' );

        Capabilities::register( 'appearance', bkntc__('Appearance module') );
        Capabilities::register( 'appearance_add', bkntc__('Add new'), 'appearance' );
        Capabilities::register( 'appearance_edit', bkntc__('Edit'), 'appearance' );
        Capabilities::register( 'appearance_delete', bkntc__('Delete'), 'appearance' );
        Capabilities::register( 'appearance_select', bkntc__('Select'), 'appearance' );

        Capabilities::register( 'calendar', bkntc__('Calendar module') );

        Capabilities::register( 'customers', bkntc__('Customers module') );
        Capabilities::register( 'customers_add', bkntc__('Add new'), 'customers' );
        Capabilities::register( 'customers_edit', bkntc__('Edit'), 'customers' );
        Capabilities::register( 'customers_delete', bkntc__('Delete'), 'customers' );
        Capabilities::register( 'customers_import', bkntc__('Export & Import'), 'customers' );
        Capabilities::register( 'customers_allow_to_login', bkntc__( 'Allow to login' ), 'customers' );
        Capabilities::register( 'customers_delete_wordpress_account', bkntc__( 'Allow to delete associated WordPress account' ), 'customers' );

        Capabilities::register( 'locations', bkntc__('Locations module') );
        Capabilities::register( 'locations_add', bkntc__('Add new'), 'locations' );
        Capabilities::register( 'locations_edit', bkntc__('Edit'), 'locations' );
        Capabilities::register( 'locations_delete', bkntc__('Delete'), 'locations' );

        Capabilities::register( 'payments', bkntc__('Payments module') );
        Capabilities::register( 'payments_edit', bkntc__('Edit'), 'payments' );

        Capabilities::register( 'workflow', bkntc__('Workflow module') );
        Capabilities::register( 'workflow_add', bkntc__('Add new') , 'workflow');
        Capabilities::register( 'workflow_edit', bkntc__('Edit') , 'workflow');
        Capabilities::register( 'workflow_delete', bkntc__('Delete') , 'workflow');

        Capabilities::register( 'services', bkntc__('Services module') );
        Capabilities::register( 'services_add', bkntc__('Add new'), 'services' );
        Capabilities::register( 'services_edit', bkntc__('Edit'), 'services' );
        Capabilities::register( 'services_delete', bkntc__('Delete'), 'services' );
        Capabilities::register( 'services_add_category', bkntc__('Add new category'), 'services' );
        Capabilities::register( 'services_edit_category', bkntc__('Edit category'), 'services' );
        Capabilities::register( 'services_delete_category', bkntc__('Delete category'), 'services' );
        Capabilities::register( 'services_add_extra', bkntc__('Add new extra'), 'services' );
        Capabilities::register( 'services_edit_extra', bkntc__('Edit extra'), 'services' );
        Capabilities::register( 'services_delete_extra', bkntc__('Delete extra'), 'services' );

        Capabilities::register( 'staff', bkntc__('Staff module') );
        Capabilities::register( 'staff_add', bkntc__('Add new'), 'staff' );
        Capabilities::register( 'staff_edit', bkntc__('Edit'), 'staff' );
        Capabilities::register( 'staff_delete', bkntc__('Delete'), 'staff' );
        Capabilities::register( 'staff_allow_to_login', bkntc__( 'Allow to login' ), 'staff' );
        Capabilities::register( 'staff_delete_wordpress_account', bkntc__( 'Allow to delete associated WordPress account' ), 'staff' );


        Capabilities::register( 'roles', bkntc__('Roles module') );
        Capabilities::register( 'roles_add', bkntc__('Add new'), 'roles' );
        Capabilities::register( 'roles_edit', bkntc__('Edit'), 'roles' );
        Capabilities::register( 'roles_delete', bkntc__('Delete'), 'roles' );

        Capabilities::register( 'settings', bkntc__('Settings') );
        Capabilities::register( 'settings_general', bkntc__('General settings'), 'settings' );
        Capabilities::register( 'settings_booking_panel_steps', bkntc__('Booking Steps'), 'settings' );
        Capabilities::register( 'settings_booking_panel_labels', bkntc__('Labels'), 'settings' );
        Capabilities::register( 'page_settings', bkntc__( 'Pages' ), 'settings' );
        Capabilities::register( 'settings_payments', bkntc__('Payment settings'), 'settings' );
        Capabilities::register( 'settings_payment_gateways', bkntc__('Payment methods'), 'settings' );
        Capabilities::register( 'settings_company', bkntc__('Company details'), 'settings' );
        Capabilities::register( 'settings_business_hours', bkntc__('Business Hours'), 'settings' );
        Capabilities::register( 'settings_holidays', bkntc__('Holidays'), 'settings' );
        Capabilities::register( 'settings_integrations_facebook_api', bkntc__('Continue with Facebook'), 'settings' );
        Capabilities::register( 'settings_integrations_google_login', bkntc__('Continue with Google'), 'settings' );
        Capabilities::register( 'settings_backup', bkntc__('Export & Import data'), 'settings' );

        if ( ! Helper::isSaaSVersion() )
        {
            Capabilities::register( 'boostore', bkntc__('Boostore') );
            Capabilities::register( 'back_to_wordpress', bkntc__( 'Show Wordpress button' ) );
        }
    }

    public static function registerCoreTenantCapabilites()
    {
        Capabilities::registerLimit( 'locations_allowed_max_number', bkntc__('Allowed maximum Locations') );
        Capabilities::registerLimit( 'services_allowed_max_number', bkntc__('Allowed maximum Service') );
        Capabilities::registerLimit( 'staff_allowed_max_number', bkntc__('Allowed maximum Staff') );
        Capabilities::registerLimit( 'service_extras_allowed_max_number', bkntc__('Allowed maximum Service Extras') );

        Capabilities::registerTenantCapability( 'receive_appointments', bkntc__('Receive appointments') );
        Capabilities::registerTenantCapability( 'remove_branding', bkntc__('Remove branding') );
        Capabilities::registerTenantCapability( 'upload_logo_to_booking_panel', bkntc__('Upload a logo to the booking panel') );
        Capabilities::registerTenantCapability( 'dashboard', bkntc__('Dashboard module') );
        Capabilities::registerTenantCapability( 'appointments', bkntc__('Appointments module') );
        Capabilities::registerTenantCapability( 'appearance', bkntc__('Appearance module') );
        Capabilities::registerTenantCapability( 'calendar', bkntc__('Calendar module') );
        Capabilities::registerTenantCapability( 'customers', bkntc__('Customers module') );
        Capabilities::registerTenantCapability( 'locations', bkntc__('Locations module') );
        Capabilities::registerTenantCapability( 'payments', bkntc__('Payments module') );
        Capabilities::registerTenantCapability( 'workflow', bkntc__('Workflow module') );
        Capabilities::registerTenantCapability( 'services', bkntc__('Services module') );
        Capabilities::registerTenantCapability( 'staff', bkntc__('Staff module') );
        Capabilities::registerTenantCapability( 'dynamic_translations', bkntc__('Dynamic translations') );
        Capabilities::registerTenantCapability( 'settings', bkntc__('Settings') );
        Capabilities::registerTenantCapability( 'settings_general', bkntc__('General settings'), 'settings' );
        Capabilities::registerTenantCapability( 'settings_booking_panel_steps', bkntc__('Booking Steps'), 'settings' );
        Capabilities::registerTenantCapability( 'settings_booking_panel_labels', bkntc__('Labels'), 'settings' );
        Capabilities::registerTenantCapability( 'page_settings', bkntc__('Pages'), 'settings' );
        Capabilities::registerTenantCapability( 'settings_payments', bkntc__('Payment settings'), 'settings' );
        Capabilities::registerTenantCapability( 'settings_payment_gateways', bkntc__('Payment methods'), 'settings' );
        Capabilities::registerTenantCapability( 'settings_company', bkntc__('Company details'), 'settings' );
        Capabilities::registerTenantCapability( 'settings_business_hours', bkntc__('Business Hours'), 'settings' );
        Capabilities::registerTenantCapability( 'settings_holidays', bkntc__('Holidays'), 'settings' );
        Capabilities::registerTenantCapability( 'settings_integrations_facebook_api', bkntc__('Continue with Facebook'), 'settings' );
        Capabilities::registerTenantCapability( 'settings_integrations_google_login', bkntc__('Continue with Google'), 'settings' );
    }

    public static function registerCoreRoutes()
    {
        Route::post( 'base', \BookneticApp\Backend\Base\Ajax::class );

        if( Capabilities::tenantCan( 'dashboard' ) )
        {
            Route::get( 'dashboard', \BookneticApp\Backend\Dashboard\Controller::class );
            Route::post( 'dashboard', \BookneticApp\Backend\Dashboard\Ajax::class );
        }

        if( Capabilities::tenantCan( 'appointments' ) )
        {
            Route::get( 'appointments', \BookneticApp\Backend\Appointments\Controller::class )->middleware( \BookneticApp\Backend\Appointments\Middleware::class );
            Route::post( 'appointments', \BookneticApp\Backend\Appointments\Ajax::class )->middleware( \BookneticApp\Backend\Appointments\Middleware::class );
        }

        if( Capabilities::tenantCan( 'appearance' ) )
        {
            Route::get( 'appearance', \BookneticApp\Backend\Appearance\Controller::class );
            Route::post( 'appearance', \BookneticApp\Backend\Appearance\Ajax::class );
        }

        if( Capabilities::tenantCan( 'calendar' ) )
        {
            Route::get( 'calendar', \BookneticApp\Backend\Calendar\Controller::class );
            Route::post( 'calendar', \BookneticApp\Backend\Calendar\Ajax::class );
        }

        if( Capabilities::tenantCan( 'customers' ) )
        {
            Route::get( 'customers', \BookneticApp\Backend\Customers\Controller::class );
            Route::post( 'customers', \BookneticApp\Backend\Customers\Ajax::class );
        }

        if( Capabilities::tenantCan( 'locations' ) )
        {
            Route::get( 'locations', \BookneticApp\Backend\Locations\Controller::class );
            Route::post( 'locations', \BookneticApp\Backend\Locations\Ajax::class );
        }

        if( Capabilities::tenantCan( 'payments' ) )
        {
            Route::get( 'payments', \BookneticApp\Backend\Payments\Controller::class );
            Route::post( 'payments', \BookneticApp\Backend\Payments\Ajax::class );
        }

        if( Capabilities::tenantCan( 'services' ) )
        {
            Route::get( 'services', \BookneticApp\Backend\Services\Controller::class );
            Route::post( 'services', \BookneticApp\Backend\Services\Ajax::class );
        }

        if( Capabilities::tenantCan( 'staff' ) )
        {
            Route::get( 'staff', \BookneticApp\Backend\Staff\Controller::class );
            Route::post( 'staff', \BookneticApp\Backend\Staff\Ajax::class );
        }

        if( Capabilities::tenantCan( 'workflow' ) )
        {
            Route::get( 'workflow', new \BookneticApp\Backend\Workflow\Controller(self::getWorkflowEventsManager()) );
            Route::post( 'workflow', new \BookneticApp\Backend\Workflow\Ajax(self::getWorkflowEventsManager()) );
            Route::post( 'workflow_events', new \BookneticApp\Backend\Workflow\EventsAjax(self::getWorkflowEventsManager()) );
            Route::post( 'workflow_actions', new \BookneticApp\Backend\Workflow\ActionsAjax(self::getWorkflowEventsManager()) );
        }

        if( Capabilities::tenantCan( 'settings' ) )
        {
            Route::get( 'settings', \BookneticApp\Backend\Settings\Controller::class )->middleware( \BookneticApp\Backend\Settings\Middleware::class );
            Route::post( 'settings', \BookneticApp\Backend\Settings\Ajax::class )->middleware( \BookneticApp\Backend\Settings\Middleware::class );
        }

        if ( ! Helper::isSaaSVersion() && Capabilities::userCan('boostore') )
        {
            Route::get( 'cart', \BookneticApp\Backend\Boostore\CartController::class );
            Route::post( 'cart', \BookneticApp\Backend\Boostore\CartAjax::class );
            Route::get( 'boostore', \BookneticApp\Backend\Boostore\Controller::class );
            Route::post( 'boostore', \BookneticApp\Backend\Boostore\Ajax::class );
        }
    }

    public static function registerCoreMenus()
    {
        if( Capabilities::tenantCan( 'dashboard' ) && Capabilities::userCan( 'dashboard' ) )
        {
            MenuUI::get( 'dashboard' )
                  ->setTitle( bkntc__( 'Dashboard' ) )
                  ->setIcon( 'fa fa-cube' )
                  ->setPriority( 100 );
        }

        if( Capabilities::tenantCan( 'appointments' ) && Capabilities::userCan( 'appointments' ) )
        {
            MenuUI::get( 'appointments' )
                  ->setTitle( bkntc__( 'Appointments' ) )
                  ->setIcon( 'fa fa-clock' )
                  ->setPriority( 200 );
        }

        if( Capabilities::tenantCan( 'calendar' ) && Capabilities::userCan( 'calendar' ) )
        {
            MenuUI::get( 'calendar' )
                  ->setTitle( bkntc__( 'Calendar' ) )
                  ->setIcon( 'fa fa-calendar-check' )
                  ->setPriority( 300 );
        }

        if( Capabilities::tenantCan( 'payments' ) && Capabilities::userCan( 'payments' ) )
        {
            MenuUI::get( 'payments' )
                  ->setTitle( bkntc__( 'Payments' ) )
                  ->setIcon( 'fa fa-wallet' )
                  ->setPriority( 400 );
        }

        if( Capabilities::tenantCan( 'customers' ) && Capabilities::userCan( 'customers' ) )
        {
            MenuUI::get( 'customers' )
                  ->setTitle( bkntc__( 'Customers' ) )
                  ->setIcon( 'fa fa-users' )
                  ->setPriority( 500 );
        }

        if( Capabilities::tenantCan( 'services' ) && Capabilities::userCan( 'services' ) )
        {
            MenuUI::get( 'services' )
                  ->setTitle( bkntc__( 'Services' ) )
                  ->setIcon( 'fa fa-align-left' )
                  ->setPriority( 600 );
        }

        if( Capabilities::tenantCan( 'staff' ) && Capabilities::userCan( 'staff' ) )
        {
            MenuUI::get( 'staff' )
                  ->setTitle( bkntc__( 'Staff' ) )
                  ->setIcon( 'fa fa-user' )
                  ->setPriority( 700 );
        }

        if( Capabilities::tenantCan( 'locations' ) && Capabilities::userCan( 'locations' ) )
        {
            MenuUI::get( 'locations' )
                  ->setTitle( bkntc__( 'Locations' ) )
                  ->setIcon( 'fa fa-location-arrow' )
                  ->setPriority( 800 );
        }

        if( Capabilities::tenantCan( 'workflow' ) && Capabilities::userCan( 'workflow' ) )
        {
            MenuUI::get( 'workflow' )
                  ->setTitle( bkntc__( 'Workflow' ) )
                  ->setIcon( 'fa fa-project-diagram' )
                  ->setPriority( 900 );
        }

        if( Capabilities::tenantCan( 'appearance' ) && Capabilities::userCan( 'appearance' ) )
        {
            MenuUI::get( 'appearance' )
                  ->setTitle( bkntc__( 'Appearance' ) )
                  ->setIcon( 'fa fa-paint-brush' )
                  ->setPriority( 1000 );
        }

        if( Capabilities::tenantCan( 'settings' ) && Capabilities::userCan( 'settings' ) )
        {
            MenuUI::get( 'settings' )
                  ->setTitle( bkntc__( 'Settings' ) )
                  ->setIcon( 'fa fa-cog' )
                  ->setPriority( 2000 );
        }

        if ( ! Helper::isSaaSVersion() )
        {
            if( Capabilities::userCan( 'back_to_wordpress' ) )
            {
                MenuUI::get( 'back_to_wordpress', Providers\UI\Abstracts\AbstractMenuUI::MENU_TYPE_TOP_LEFT )
                      ->setTitle( bkntc__( 'WORDPRESS' ) )
                      ->setIcon( 'fa fa-angle-left' )
                      ->setLink( admin_url() )
                      ->setPriority( 100 );
            }

            if ( Capabilities::userCan('boostore') )
            {
                MenuUI::get( 'boostore', Providers\UI\Abstracts\AbstractMenuUI::MENU_TYPE_TOP_LEFT )
                      ->setTitle( bkntc__( 'Boostore' ) )
                      ->setIcon( 'fa fa-puzzle-piece' )
                      ->setPriority( 200 );
            }
        }

    }

    /**
     * Staff ve Administrator rule`lari var bizde. Hazirda hard code yazilib.
     * Administrator butun modul ve actionlara accessi var.
     * Staff ise yalniz Dashboard, Appointments, Calendar, Customers, Payments
     */
    public static function registerHardCodedUserRules()
    {
        /** if Staff */
        if( ! Permission::isAdministrator() )
        {
            add_filter( 'bkntc_user_capability_filter', [ self::class, 'userCapabilityFilter' ], 10, 2 );
        }
    }

    public static function userCapabilityFilter ( $can, $capability )
    {
        $capabilityInf = Capabilities::get( $capability );

        if( ! empty( $capabilityInf[ 'parent' ] ) )
        {
            $capability = $capabilityInf[ 'parent' ];
        }

        if( in_array( $capability, [ 'dashboard', 'appointments', 'calendar', 'customers', 'payments', 'staff' ] ) )
            return true;

        return false;
    }

    public static function registerCoreWorkflowEvents()
    {
        self::$workflowEventsManager->get('booking_new')
                                    ->setTitle(bkntc__('New booking'))
                                    ->setEditAction('workflow_events', 'event_new_booking')
                                    ->setAvailableParams(['appointment_id', 'location_id', 'service_id', 'staff_id', 'customer_id']);

        self::$workflowEventsManager->get('booking_rescheduled')
                                    ->setTitle(bkntc__('Booking rescheduled'))
                                    ->setEditAction('workflow_events', 'event_booking_rescheduled')
                                    ->setAvailableParams(['appointment_id', 'location_id', 'service_id', 'staff_id', 'customer_id']);

        self::$workflowEventsManager->get('booking_status_changed')
                                    ->setTitle(bkntc__('Booking status changed'))
                                    ->setEditAction('workflow_events', 'event_booking_status_changed')
                                    ->setAvailableParams(['appointment_id', 'location_id', 'service_id', 'staff_id', 'customer_id']);

        self::$workflowEventsManager->get('booking_starts')
                                    ->setTitle(bkntc__('Booking starts'))
                                    ->setEditAction('workflow_events', 'event_booking_starts')
                                    ->setAvailableParams(['appointment_id', 'location_id', 'service_id', 'staff_id', 'customer_id']);

        self::$workflowEventsManager->get('booking_ends')
                                    ->setTitle(bkntc__('Booking ends'))
                                    ->setEditAction('workflow_events', 'event_booking_ends')
                                    ->setAvailableParams(['appointment_id', 'location_id', 'service_id', 'staff_id', 'customer_id']);

        self::$workflowEventsManager->get('appointment_paid')
                                    ->setTitle(bkntc__('Appointment Paid'))
                                    ->setEditAction('workflow_events', 'event_appointment_paid_view')
                                    ->setAvailableParams(['appointment_id', 'location_id', 'service_id', 'staff_id', 'customer_id']);

        add_action( 'bkntc_customer_sign_up_confirm', function( $token, $customerId )
        {
            self::$shortCodeService->addReplacer( function( $text, $data ) use ( $token )
            {
                if( ! isset( $data['customer_id'] ) )
                    return $text;

                $page_link = get_page_link( Helper::getOption('regular_sign_up_page', '', false) );

                $confirm_url = add_query_arg( 'activation_token', $token, $page_link );

                return str_replace( '{url_to_complete_customer_signup}', $confirm_url, $text );
            });

            self::$workflowEventsManager->trigger( 'customer_signup', [
                'customer_id'   =>  $customerId
            ], function($event) {
                if (empty($event['data'])) return true;

                $data = json_decode($event['data'], true);

                if ( ! empty( $data[ 'locale' ] ) && $data['locale'] !== get_locale() ) {
                    return false;
                }

                return true;
            } );
        }, 10, 2);

        add_action( 'bkntc_customer_forgot_password', function( $token, $customerId )
        {
            self::$shortCodeService->addReplacer( function( $text, $data ) use ( $token )
            {
                if( ! isset( $data['customer_id'] ) )
                    return $text;

                $page_link = get_page_link( Helper::getOption('regular_forgot_password_page', '', false) );

                $confirm_url = add_query_arg( 'reset_token', $token, $page_link );

                return str_replace( '{url_to_reset_password}', $confirm_url, $text );
            });

            self::$workflowEventsManager->trigger( 'customer_forgot_password', [ 'customer_id' => $customerId ] );
        }, 10, 2);

        add_action('bkntc_customer_reset_password', function( $customerId )
        {
            self::$workflowEventsManager->trigger( 'customer_reset_password', [ 'customer_id' => $customerId ] );
        });

        add_action('bkntc_payment_confirmed', function ($appointmentId)
        {
            $appointment = Appointment::get($appointmentId);

            self::$workflowEventsManager->trigger('booking_new', [
                'appointment_id' => $appointmentId,
                'location_id' => $appointment->location_id,
                'service_id' => $appointment->service_id,
                'staff_id' => $appointment->staff_id,
                'customer_id' => $appointment->customer_id
            ], function ($event) use ($appointment) {
                if (empty($event['data'])) return true;

                $data = json_decode($event['data'], true);

                if ( ! empty( $data[ 'locale' ] ) && $data['locale'] !== $appointment->locale) {
                    return false;
                }

                if (count($data['locations']) > 0 && !in_array($appointment->location_id, $data['locations'])) {
                    return false;
                }

                if (count($data['services']) > 0 && !in_array($appointment->service_id, $data['services'])) {
                    return false;
                }

                if (count($data['staffs']) > 0 && !in_array($appointment->staff_id, $data['staffs'])) {
                    return false;
                }

                if(isset($data['statuses']) && is_countable($data['statuses']) && count($data['statuses']) > 0 && !in_array($appointment->status, $data['statuses']))
                    return false;

                if (
                    ! empty( $data['called_from'] ) &&
                    (
                        ( $data['called_from'] == 'backend' && !Permission::isBackEnd() ) ||
                        ( $data['called_from'] == 'frontend' && Permission::isBackEnd() )
                    )
                ) {
                    return false;
                }

                return true;
            });
            if ( $appointment->payment_method !== 'local' )
            {
                self::$workflowEventsManager->trigger( 'appointment_paid', [
                    'appointment_id' => $appointmentId,
                    'location_id' => $appointment->location_id,
                    'service_id' => $appointment->service_id,
                    'staff_id' => $appointment->staff_id,
                    'customer_id' => $appointment->customer_id
                ], function($event) {
                    if (empty($event['data'])) return true;

                    $data = json_decode($event['data'], true);

                    if ( ! empty( $data[ 'locale' ] ) && $data['locale'] !== get_locale() ) {
                        return false;
                    }

                    return true;
                } );
            }

        }, 1000, 1);

        $oldAppointmentInfObj = new \stdClass();
        $oldAppointmentInfObj->inf = null;

        add_action('bkntc_appointment_before_mutation', function ($id) use ($oldAppointmentInfObj)
        {
            $oldAppointmentInfObj->inf = is_null($id) ? null : Appointment::get($id);
        });

        add_action('bkntc_appointment_after_mutation', function ($id) use ($oldAppointmentInfObj)
        {
            $oldAppointmentInf = $oldAppointmentInfObj->inf;
            $newAppointmentInf = is_null($id) ? null : Appointment::get($id);

            if (empty($oldAppointmentInf) || empty($newAppointmentInf))
                return;

            // status change
            if ($newAppointmentInf->status != $oldAppointmentInf->status)
            {
                self::$workflowEventsManager->trigger('booking_status_changed', [
                    'appointment_id' => $newAppointmentInf->id,
                    'location_id' => $newAppointmentInf->location_id,
                    'service_id' => $newAppointmentInf->service_id,
                    'staff_id' => $newAppointmentInf->staff_id,
                    'customer_id' => $newAppointmentInf->customer_id
                ], function($event) use ($oldAppointmentInf, $newAppointmentInf) {
                    if (empty($event['data'])) return true;

                    $data = json_decode($event['data'], true);

                    if ( ! empty( $data[ 'locale' ] ) && $data['locale'] !== $newAppointmentInf->locale) {
                        return false;
                    }

                    if (is_countable($data['statuses']) && count($data['statuses']) > 0 && !in_array($newAppointmentInf->status, $data['statuses'])) {
                        return false;
                    }

                    if ( array_key_exists('prev_statuses',$data) && is_countable($data['prev_statuses']) && count($data['prev_statuses']) > 0 && !in_array($oldAppointmentInf->status, $data['prev_statuses'])) {
                        return false;
                    }

                    if (is_countable($data['locations']) && count($data['locations']) > 0 && !in_array($newAppointmentInf->location_id, $data['locations'])) {
                        return false;
                    }

                    if (is_countable($data['services']) && count($data['services']) > 0 && !in_array($newAppointmentInf->service_id, $data['services'])) {
                        return false;
                    }

                    if (is_countable($data['staffs']) && count($data['staffs']) > 0 && !in_array($newAppointmentInf->staff_id, $data['staffs'])) {
                        return false;
                    }

                    if (
                        ! empty( $data['called_from'] ) &&
                        (
                            ( $data['called_from'] == 'backend' && !Permission::isBackEnd() ) ||
                            ( $data['called_from'] == 'frontend' && Permission::isBackEnd() )
                        )
                    ) {
                        return false;
                    }

                    return true;
                });
            }

            // reschedule
            if ($newAppointmentInf->starts_at != $oldAppointmentInf->starts_at
                || $newAppointmentInf->location_id != $oldAppointmentInf->location_id
                || $newAppointmentInf->service_id != $oldAppointmentInf->service_id
                || $newAppointmentInf->staff_id != $oldAppointmentInf->staff_id
            )
            {
                self::$workflowEventsManager->trigger('booking_rescheduled', [
                    'appointment_id' => $newAppointmentInf->id,
                    'location_id' => $newAppointmentInf->location_id,
                    'service_id' => $newAppointmentInf->service_id,
                    'staff_id' => $newAppointmentInf->staff_id,
                    'customer_id' => $newAppointmentInf->customer_id
                ], function ($event) use ($newAppointmentInf) {
                    if (empty($event['data'])) return true;

                    $data = json_decode($event['data'], true);

                    if ( ! empty( $data[ 'locale' ] ) && $data['locale'] !== $newAppointmentInf->locale) {
                        return false;
                    }

                    if (count($data['locations']) > 0 && !in_array($newAppointmentInf->location_id, $data['locations'])) {
                        return false;
                    }

                    if (count($data['services']) > 0 && !in_array($newAppointmentInf->service_id, $data['services'])) {
                        return false;
                    }

                    if (count($data['staffs']) > 0 && !in_array($newAppointmentInf->staff_id, $data['staffs'])) {
                        return false;
                    }

                    return true;
                });
            }

        }, 1000, 1);

        add_action('bkntc_customer_created', function ( $id, $pass )
        {
            if( empty( $id ) || empty( $pass ) )
                return;

            self::$workflowEventsManager->trigger( 'new_wp_user_customer_created', [
                'customer_id'       => $id,
                'customer_password' => $pass
            ], function( $event ) {
                if ( empty( $event[ 'data' ] ) )
                    return true;

                $data = json_decode( $event[ 'data' ], true );

                if ( ! empty( $data[ 'locale' ] ) && $data['locale'] !== get_locale() )
                    return false;

                return true;
            } );
        }, 10, 2 );

        add_action('bkntc_appointment_after_mutation',function ( $id ) use( $oldAppointmentInfObj )
        {
            if( is_null( $id ) )
                return;

            $newAppointmentInf = Appointment::get($id);
            if( !empty($oldAppointmentInfObj->inf) && !empty( $newAppointmentInf ) && $oldAppointmentInfObj->inf->starts_at !== $newAppointmentInf->starts_at )
            {
                Appointment::deleteData( $id ,'triggered_cronjob_workflows');
            }
        });

    }

    public static function registerCoreWorkflowActions()
    {
        $drivers = self::getWorkflowDriversManager();
        $drivers->register(new SetBookingStatusAction());
    }

    public static function registerCoreShortCodes()
    {
        $shortCodeService = self::$shortCodeService;

        $shortCodeService->addReplacer([ShortCodeServiceImpl::class, 'replace']);
        $shortCodeService->addReplacer([ShortCodeServiceImpl::class, 'replacePaymentLink']);
        $shortCodeService->addReplacer([AppointmentChangeStatus::class, 'replaceShortCode']);

        $shortCodeService->registerCategory( 'appointment_info', bkntc__('Appointment Info') );
        $shortCodeService->registerCategory( 'service_info', bkntc__('Service Info') );
        $shortCodeService->registerCategory( 'customer_info', bkntc__('Customer Info') );
        $shortCodeService->registerCategory( 'staff_info', bkntc__('Staff Info') );
        $shortCodeService->registerCategory( 'location_info', bkntc__('Location Info') );
        $shortCodeService->registerCategory( 'others', bkntc__('Others') );

        $shortCodeService->registerShortCode( 'appointment_id', [
            'name'      =>  bkntc__('Appointment ID'),
            'category'  =>  'appointment_info',
            'depends'   =>  'appointment_id',
            'kind'      =>  'appointment_id'
        ] );
        $shortCodeService->registerShortCode( 'appointment_date', [
            'name'      =>  bkntc__('Appointment date'),
            'category'  =>  'appointment_info',
            'depends'   =>  'appointment_id'
        ] );
        $shortCodeService->registerShortCode( 'appointment_start_date', [
            'name'      =>  bkntc__('Appointment start date'),
            'category'  =>  'appointment_info',
            'depends'   =>  'appointment_id'
        ] );
        $shortCodeService->registerShortCode( 'appointment_end_date', [
            'name'      =>  bkntc__('Appointment end date'),
            'category'  =>  'appointment_info',
            'depends'   =>  'appointment_id'
        ] );
        $shortCodeService->registerShortCode( 'appointment_date_time', [
            'name'      =>  bkntc__('Appointment date-time'),
            'category'  =>  'appointment_info',
            'depends'   =>  'appointment_id'
        ] );
        $shortCodeService->registerShortCode( 'appointment_start_date_time', [
            'name'      =>  bkntc__('Appointment start-date-time'),
            'category'  =>  'appointment_info',
            'depends'   =>  'appointment_id'
        ] );
        $shortCodeService->registerShortCode( 'appointment_end_date_time', [
            'name'      =>  bkntc__('Appointment end-date-time'),
            'category'  =>  'appointment_info',
            'depends'   =>  'appointment_id'
        ] );
        $shortCodeService->registerShortCode( 'appointment_start_time', [
            'name'      =>  bkntc__('Appointment start time'),
            'category'  =>  'appointment_info',
            'depends'   =>  'appointment_id'
        ] );
        $shortCodeService->registerShortCode( 'appointment_end_time', [
            'name'      =>  bkntc__('Appointment end time'),
            'category'  =>  'appointment_info',
            'depends'   =>  'appointment_id'
        ] );
        $shortCodeService->registerShortCode( 'appointment_date_client', [
            'name'      =>  bkntc__('Appointment date (customer timezone)'),
            'category'  =>  'appointment_info',
            'depends'   =>  'appointment_id'
        ] );
        $shortCodeService->registerShortCode( 'appointment_start_date_client', [
            'name'      =>  bkntc__('Appointment start date (customer timezone)'),
            'category'  =>  'appointment_info',
            'depends'   =>  'appointment_id'
        ] );
        $shortCodeService->registerShortCode( 'appointment_end_date_client', [
            'name'      =>  bkntc__('Appointment end date (customer timezone)'),
            'category'  =>  'appointment_info',
            'depends'   =>  'appointment_id'
        ] );
        $shortCodeService->registerShortCode( 'appointment_date_time_client', [
            'name'      =>  bkntc__('Appointment date-time (customer timezone)'),
            'category'  =>  'appointment_info',
            'depends'   =>  'appointment_id'
        ] );
        $shortCodeService->registerShortCode( 'appointment_start_date_time_client', [
            'name'      =>  bkntc__('Appointment start-date-time (customer timezone)'),
            'category'  =>  'appointment_info',
            'depends'   =>  'appointment_id'
        ] );
        $shortCodeService->registerShortCode( 'appointment_end_date_time_client', [
            'name'      =>  bkntc__('Appointment end-date-time (customer timezone)'),
            'category'  =>  'appointment_info',
            'depends'   =>  'appointment_id'
        ] );
        $shortCodeService->registerShortCode( 'appointment_start_time_client', [
            'name'      =>  bkntc__('Appointment start time (customer timezone)'),
            'category'  =>  'appointment_info',
            'depends'   =>  'appointment_id'
        ] );
        $shortCodeService->registerShortCode( 'appointment_end_time_client', [
            'name'      =>  bkntc__('Appointment end time (customer timezone)'),
            'category'  =>  'appointment_info',
            'depends'   =>  'appointment_id'
        ] );
        $shortCodeService->registerShortCode( 'appointment_duration', [
            'name'      =>  bkntc__('Appointment duration'),
            'category'  =>  'appointment_info',
            'depends'   =>  'appointment_id'
        ] );
        $shortCodeService->registerShortCode( 'appointment_buffer_before', [
            'name'      =>  bkntc__('Appointment buffer before time'),
            'category'  =>  'appointment_info',
            'depends'   =>  'appointment_id'
        ] );
        $shortCodeService->registerShortCode( 'appointment_buffer_after', [
            'name'      =>  bkntc__('Appointment buffer after time'),
            'category'  =>  'appointment_info',
            'depends'   =>  'appointment_id'
        ] );
        $shortCodeService->registerShortCode( 'appointment_status', [
            'name'      =>  bkntc__('Appointment status'),
            'category'  =>  'appointment_info',
            'depends'   =>  'appointment_id'
        ] );
        $shortCodeService->registerShortCode( 'appointment_service_price', [
            'name'      =>  bkntc__('Service price'),
            'category'  =>  'appointment_info',
            'depends'   =>  'appointment_id'
        ] );
        $shortCodeService->registerShortCode( 'appointment_extras_price', [
            'name'      =>  bkntc__('Price of extra services'),
            'category'  =>  'appointment_info',
            'depends'   =>  'appointment_id'
        ] );
        $shortCodeService->registerShortCode( 'appointment_extras_list', [
            'name'      =>  bkntc__('List of extra services'),
            'category'  =>  'appointment_info',
            'depends'   =>  'appointment_id'
        ] );
        $shortCodeService->registerShortCode( 'appointment_discount_price', [
            'name'      =>  bkntc__('Discount'),
            'category'  =>  'appointment_info',
            'depends'   =>  'appointment_id'
        ] );
        $shortCodeService->registerShortCode( 'appointment_sum_price', [
            'name'      =>  bkntc__('Sum price'),
            'category'  =>  'appointment_info',
            'depends'   =>  'appointment_id'
        ] );
        $shortCodeService->registerShortCode( 'appointments_total_price', [
            'name'      =>  bkntc__('Sum price for recurring appointments'),
            'category'  =>  'appointment_info',
            'depends'   =>  'appointment_id'
        ] );

        $shortCodeService->registerShortCode( 'recurring_appointments_date', [
            'name'      =>  bkntc__('Recurring appointments all dates'),
            'category'  =>  'appointment_info',
            'depends'   =>  'appointment_id'
        ] );
        $shortCodeService->registerShortCode( 'recurring_appointments_date_time', [
            'name'      =>  bkntc__('Recurring appointments all dates and times'),
            'category'  =>  'appointment_info',
            'depends'   =>  'appointment_id'
        ] );
        $shortCodeService->registerShortCode( 'recurring_appointments_date_client', [
            'name'      =>  bkntc__('Recurring appointments all dates and times (Client timezone)'),
            'category'  =>  'appointment_info',
            'depends'   =>  'appointment_id'
        ] );
        $shortCodeService->registerShortCode( 'recurring_appointments_date_time_client', [
            'name'      =>  bkntc__('Recurring appointments all dates and times (Client timezone)'),
            'category'  =>  'appointment_info',
            'depends'   =>  'appointment_id'
        ] );

        $shortCodeService->registerShortCode( 'appointment_paid_price', [
            'name'      =>  bkntc__('Paid price'),
            'category'  =>  'appointment_info',
            'depends'   =>  'appointment_id'
        ] );
        $shortCodeService->registerShortCode( 'appointment_payment_method', [
            'name'      =>  bkntc__('Payment method'),
            'category'  =>  'appointment_info',
            'depends'   =>  'appointment_id'
        ] );
        $shortCodeService->registerShortCode( 'appointment_created_date', [
            'name'      =>  bkntc__('Appointment created date'),
            'category'  =>  'appointment_info',
            'depends'   =>  'appointment_id'
        ] );
        $shortCodeService->registerShortCode( 'appointment_created_time', [
            'name'      =>  bkntc__('Appointment created time'),
            'category'  =>  'appointment_info',
            'depends'   =>  'appointment_id'
        ] );
        $shortCodeService->registerShortCode( 'appointment_brought_people', [
            'name' => bkntc__('People brought to the appointment'),
            'category' => 'appointment_info',
            'depends' => 'appointment_id'
        ] );
        $shortCodeService->registerShortCode( 'appointment_total_attendees', [
            'name' => bkntc__('Total attendees count for one appointment '),
            'category' => 'appointment_info',
            'depends' => 'appointment_id'
        ] );
        $shortCodeService->registerShortCode( 'add_to_google_calendar_link', [
            'name'      =>  bkntc__('Add to google calendar'),
            'category'  =>  'appointment_info',
            'depends'   =>  'appointment_id'
        ] );

        $shortCodeService->registerShortCode( 'appointment_notes', [
            'name'      =>  bkntc__('Appointment notes'),
            'category'  =>  'appointment_info',
            'depends'   =>  'appointment_id'
        ] );

        $shortCodeService->registerShortCode( 'service_name', [
            'name'      =>  bkntc__('Service name'),
            'category'  =>  'service_info',
            'depends'   =>  'service_id'
        ] );
        $shortCodeService->registerShortCode( 'service_price', [
            'name'      =>  bkntc__('Service price'),
            'category'  =>  'service_info',
            'depends'   =>  'service_id'
        ] );
        $shortCodeService->registerShortCode( 'service_duration', [
            'name'      =>  bkntc__('Service duration'),
            'category'  =>  'service_info',
            'depends'   =>  'service_id'
        ] );
        $shortCodeService->registerShortCode( 'service_notes', [
            'name'      =>  bkntc__('Service notes'),
            'category'  =>  'service_info',
            'depends'   =>  'service_id'
        ] );
        $shortCodeService->registerShortCode( 'service_color', [
            'name'      =>  bkntc__('Service color'),
            'category'  =>  'service_info',
            'depends'   =>  'service_id'
        ] );
        $shortCodeService->registerShortCode( 'service_image_url', [
            'name'      =>  bkntc__('Service image URL'),
            'category'  =>  'service_info',
            'depends'   =>  'service_id'
        ] );
        $shortCodeService->registerShortCode( 'service_category_name', [
            'name'      =>  bkntc__('Service category'),
            'category'  =>  'service_info',
            'depends'   =>  'service_id'
        ] );


        $shortCodeService->registerShortCode( 'customer_full_name', [
            'name'      =>  bkntc__('Customer full name'),
            'category'  =>  'customer_info',
            'depends'   =>  'customer_id'
        ] );
        $shortCodeService->registerShortCode( 'customer_first_name', [
            'name'      =>  bkntc__('Customer first name'),
            'category'  =>  'customer_info',
            'depends'   =>  'customer_id'
        ] );
        $shortCodeService->registerShortCode( 'customer_last_name', [
            'name'      =>  bkntc__('Customer last name'),
            'category'  =>  'customer_info',
            'depends'   =>  'customer_id'
        ] );
        $shortCodeService->registerShortCode( 'customer_phone', [
            'name'      =>  bkntc__('Customer phone number'),
            'category'  =>  'customer_info',
            'depends'   =>  'customer_id',
            'kind'      =>  'phone'
        ] );
        $shortCodeService->registerShortCode( 'customer_email', [
            'name'      =>  bkntc__('Customer email'),
            'category'  =>  'customer_info',
            'depends'   =>  'customer_id',
            'kind'      =>  'email'
        ] );
        $shortCodeService->registerShortCode( 'customer_birthday', [
            'name'      =>  bkntc__('Customer birthdate'),
            'category'  =>  'customer_info',
            'depends'   =>  'customer_id'
        ] );
        $shortCodeService->registerShortCode( 'customer_notes', [
            'name'      =>  bkntc__('Customer notes'),
            'category'  =>  'customer_info',
            'depends'   =>  'customer_id'
        ] );
        $shortCodeService->registerShortCode( 'customer_profile_image_url', [
            'name'      =>  bkntc__('Customer image URL'),
            'category'  =>  'customer_info',
            'depends'   =>  'customer_id'
        ] );

        $shortCodeService->registerShortCode( 'customer_password', [
            'name'      =>  bkntc__('Customer password'),
            'category'  =>  'customer_info',
            'depends'   =>  'customer_password'
        ] );

        if ( is_null( Permission::tenantId() ) )
        {
            $shortCodeService->registerShortCode('url_to_complete_customer_signup',[
                'name'      =>  bkntc__('URL to complete customer sign up'),
                'category'  =>  'customer_info',
                'depends'   =>  'customer_id',
            ]);
            $shortCodeService->registerShortCode('url_to_reset_password',[
                'name'      =>  bkntc__('URL to reset customer password'),
                'category'  =>  'customer_info',
                'depends'   =>  'customer_id'
            ]);
        }

        $shortCodeService->registerShortCode( 'staff_name', [
            'name'      =>  bkntc__('Staff name'),
            'category'  =>  'staff_info',
            'depends'   =>  'staff_id'
        ] );
        $shortCodeService->registerShortCode( 'staff_email', [
            'name'      =>  bkntc__('Staff email'),
            'category'  =>  'staff_info',
            'depends'   =>  'staff_id',
            'kind'      =>  'email'
        ] );
        $shortCodeService->registerShortCode( 'staff_phone', [
            'name'      =>  bkntc__('Staff phone number'),
            'category'  =>  'staff_info',
            'depends'   =>  'staff_id',
            'kind'      =>  'phone'
        ] );
        $shortCodeService->registerShortCode( 'staff_about', [
            'name'      =>  bkntc__('Staff about'),
            'category'  =>  'staff_info',
            'depends'   =>  'staff_id'
        ] );
        $shortCodeService->registerShortCode( 'staff_profile_image_url', [
            'name'      =>  bkntc__('Staff image URL'),
            'category'  =>  'staff_info',
            'depends'   =>  'staff_id'
        ] );

        $shortCodeService->registerShortCode( 'location_name', [
            'name'      =>  bkntc__('Location name'),
            'category'  =>  'location_info',
            'depends'   =>  'location_id'
        ] );
        $shortCodeService->registerShortCode( 'location_address', [
            'name'      =>  bkntc__('Location address'),
            'category'  =>  'location_info',
            'depends'   =>  'location_id'
        ] );
        $shortCodeService->registerShortCode( 'location_image_url', [
            'name'      =>  bkntc__('Location image URL'),
            'category'  =>  'location_info',
            'depends'   =>  'location_id'
        ] );
        $shortCodeService->registerShortCode( 'location_phone_number', [
            'name'      =>  bkntc__('Location phone'),
            'category'  =>  'location_info',
            'depends'   =>  'location_id',
            'kind'      =>  'phone'
        ] );
        $shortCodeService->registerShortCode( 'location_notes', [
            'name'      =>  bkntc__('Location notes'),
            'category'  =>  'location_info',
            'depends'   =>  'location_id'
        ] );
        $shortCodeService->registerShortCode( 'location_google_maps_url', [
            'name'      =>  bkntc__('Location Google Maps URL'),
            'category'  =>  'location_info',
            'depends'   =>  'location_id'
        ] );

        $shortCodeService->registerShortCode( 'company_name', [
            'name'      =>  bkntc__('Company name'),
            'category'  =>  'others'
        ] );
        $shortCodeService->registerShortCode( 'company_image_url', [
            'name'      =>  bkntc__('Company image URL'),
            'category'  =>  'others'
        ] );
        $shortCodeService->registerShortCode( 'company_website', [
            'name'      =>  bkntc__('Company website'),
            'category'  =>  'others'
        ] );
        $shortCodeService->registerShortCode( 'company_phone', [
            'name'      =>  bkntc__('Company phone number'),
            'category'  =>  'others',
            'kind'      =>  'phone'
        ] );
        $shortCodeService->registerShortCode( 'company_address', [
            'name'      =>  bkntc__('Company address'),
            'category'  =>  'others'
        ] );
        $shortCodeService->registerShortCode( 'sign_in_page', [
            'name'      =>  bkntc__('Sign In Page'),
            'category'  =>  'others'
        ] );
        $shortCodeService->registerShortCode( 'sign_up_page', [
            'name'      =>  bkntc__('Sign Up Page'),
            'category'  =>  'others'
        ] );
        $shortCodeService->registerShortCode('total_appointments_in_group',[
            'name'      =>  bkntc__('Total appointments in group'),
            'category'  =>  'others',
            'depends'   =>  'appointment_id',
        ]);


        foreach ( Helper::getAppointmentStatuses() as $key => $status ){
            $shortCodeService->registerShortCode( 'link_to_change_appointment_status_to_' . $key, [
                'name'      =>  bkntc__('Link to change appointment status to') . ' ' . $status['title'],
                'category'  =>  'others',
                'depends'   =>  'appointment_id',
            ] );
        }

    }

    public static function registerPaymentShortCode()
    {
        foreach ( PaymentGatewayService::getEnabledGatewayNames() as $slug ){
            $paymentGatewayService = PaymentGatewayService::find($slug);
            if( ! property_exists( $paymentGatewayService  ,'createPaymentLink')) continue;

            self::getShortCodeService()->registerShortCode( 'appointment_payment_link_' . $slug, [
                'name'      =>  bkntc__('Payment Link ') . ' ' . $paymentGatewayService->getTitle(),
                'category'  =>  'others',
                'depends'   =>  'appointment_id',
            ] );
        }
    }

    public static function registerLocalPaymentGateway( )
    {
        $local_payment = new LocalPayment();

        TabUI::get('payment_gateways_settings')
             ->item('local')
             ->setTitle(bkntc__('Local'));
    }

    public static function registerCorePricesName()
    {
        add_filter('bkntc_price_name', function ($key)
        {
            $names = [
                'service_price' => bkntc__('Service price'),
                'discount' => bkntc__('Discount'),
                'service_extra' => bkntc__('Extra Service price')
            ];

            if (array_key_exists($key, $names))
                return $names[$key];

            return $key;
        });
    }

    public static function registerWPUserRoles()
    {
        add_role( 'booknetic_customer', bkntc__('Booknetic Customers'), [
            'read'         => false,
            'edit_posts'   => false,
            'upload_files' => false,
        ]);

        add_role( 'booknetic_staff', bkntc__('Booknetic Staff'), [
            'read'         => true,
            'edit_posts'   => false,
            'upload_files' => false
        ]);
    }

    public static function registerSigningPage()
    {
        $sign_in_page = Helper::getOption( 'regular_sing_in_page' );

        if( !empty( $sign_in_page ) && ( $sign_in_page_link = get_permalink( $sign_in_page ) ) && !empty( $sign_in_page_link ) )
        {
            add_filter( 'login_url', function ( $login_url, $redirect ) use( $sign_in_page_link )
            {
                if ( ! empty( $redirect ) )
                {
                    $sign_in_page_link = add_query_arg( 'redirect_to', urlencode( $redirect ), $sign_in_page_link );
                }

                return $sign_in_page_link;
            }, 10, 2);
        }
    }

    public static function detectPluginActivation( $plugin, $network_activation )
    {
        Helper::deleteOption( 'transient_cache_booknetic' , false );
    }

    public static function detectUserUpdate( $user_id, $old_user_data )
    {
        self::updateStaff( $user_id );
    }

    public static function detectProfileUpdate( $user_id )
    {
        self::updateStaff( $user_id );
    }

    private static function updateStaff( $user_id )
    {
        $updated_user_data = get_userdata( $user_id );

        $sqlData = [ 'email' => $updated_user_data -> user_email ];

        $sqlData = apply_filters( 'staff_sql_data', $sqlData );

        Staff::where( 'user_id', $user_id ) -> update( $sqlData );
    }
}
