<?php


namespace BookneticApp\Providers\Core\Abstracts;
use BookneticApp\Providers\Core\CapabilitiesException;
use BookneticApp\Providers\Helpers\Helper;


abstract class AbstractRoute
{
    const DEFAULT_MODULE	= 'dashboard';
    const DEFAULT_ACTION	= 'index';

    private $route;
    private $controller;
    private $allowedActions;
    /**
     * @var array
     */
    private $middleware = [];
    private $requestMethod;

    public function __construct( $route, $controller, $allowedActions, $method )
    {
        $this->route            = $route;
        $this->controller       = $controller;
        $this->requestMethod    = $method;
        $this->allowedActions   = $allowedActions;
    }

    public function getController()
    {
        if( is_string( $this->controller ) )
        {
            $this->controller = new $this->controller();
        }

        $this->controller->modulesDir = (static::$backend)::MODULES_DIR;
        $this->controller->prefix = static::$prefix;
        return $this->controller;
    }

    public function getMiddleware()
    {
        return $this->middleware;
    }

    public function getRoute()
    {
        return $this->route;
    }

    public function getRequestMethod()
    {
        return $this->requestMethod;
    }

    public function checkActionIsAllowed( $action )
    {
        return empty( $this->allowedActions ) || in_array( $action, $this->allowedActions );
    }

    public function middleware( $middlewares, $options = false )
    {
        foreach( (array)$middlewares AS $middleware )
        {
            $this->middleware[] = [
                'class'     =>  $middleware,
                'options'   =>  $options
            ];
        }

        return $this;
    }

    public static function get( $route, $controller, $allowedActions = [] )
    {
        static::$routesGET[ $route ] = new static( $route, $controller, $allowedActions, 'GET' );

        return static::$routesGET[ $route ];
    }

    public static function post( $route, $controller, $allowedActions = [] )
    {
        static::$routesPOST[ $route ] = new static( $route, $controller, $allowedActions, 'POST' );

        return static::$routesPOST[ $route ];
    }

    public static function addGlobalMiddleware( $middlewares, $options = false )
    {
        foreach( (array)$middlewares AS $middleware )
        {
            static::$globalMiddlewares[] = [
                'class'     =>  $middleware,
                'options'   =>  $options
            ];
        }
    }

    public static function find ( $route )
    {
        $routesObj = isset( $_POST['action'] ) && $_POST['action'] !== 'datatable_get_select_options' ? static::$routesPOST : static::$routesGET;

        if ( array_key_exists( $route, $routesObj ) )
        {
            return $routesObj[ $route ];
        }
        else
        {
            throw new \Exception();
        }
    }

    public static function init()
    {
        $module         = static::getCurrentModule();
        $action         = static::getCurrentAction();

        $route          = static::find( $module );
        $controller     = $route->getController();

        if ( ! $route->checkActionIsAllowed( $action ) || ! is_callable( [ $controller, $action ] ) )
        {
            throw new \Exception();
        }

        do_action( static::$prefix . "before_request_{$module}_{$action}" );

        $checkMiddlewares = array_merge( static::$globalMiddlewares, $route->getMiddleware() );

        $recursiveActions = function () use ( $controller, $action )
        {
            return $controller->$action();
        };

        foreach ( array_reverse( $checkMiddlewares ) AS $middleware )
        {
            $middlewareClass = $middleware['class'];
            $middlewareOptions = $middleware['options'];

            if( isset( $middlewareOptions['only'] ) && ! in_array( $action, (array)$middlewareOptions['only'] ) )
            {
                $canBootMiddleware = false;
            }
            else if( isset( $middlewareOptions['except'] ) && in_array( $action, (array)$middlewareOptions['except'] ) )
            {
                $canBootMiddleware = false;
            }
            else
            {
                $canBootMiddleware = true;
            }

            if( ! $canBootMiddleware )
                continue;

            if( is_string( $middlewareClass ) )
                $middlewareClass = new $middlewareClass();

            if( is_callable( [ $middlewareClass, 'handle' ] ) )
            {
                $recursiveActions = function () use ( $recursiveActions, $middlewareClass )
                {
                    return $middlewareClass->handle( $recursiveActions );
                };
            }
        }

        try
        {
            $result = $recursiveActions();
        }
        catch ( CapabilitiesException $e )
        {
            $result = Helper::response( false, $e->getMessage(), true );
        }

		if( static::$prefix == 'bkntcsaas_' )
		{
			$result = apply_filters( "bkntcsaas_after_request_{$module}_{$action}", $result );
		}
		else
		{
			$result = apply_filters( "bkntc_after_request_{$module}_{$action}", $result );
		}

        if( is_array( $result ) )
        {
            echo json_encode( $result );
        }
        else
        {
            echo $result;
        }
    }

    public static function getCurrentModule()
    {
        $requestMethod = static::isAjax() ? '_post' : '_get';

        $module = Helper::$requestMethod( 'module', static::DEFAULT_MODULE, 'string' );
        $module = preg_replace( '/[^a-zA-Z0-9\-_]/', '', $module );

        return !empty( $module ) ? strtolower( $module ) : static::DEFAULT_MODULE;
    }

    public static function getCurrentAction()
    {
        $requestMethod = static::isAjax() ? '_post' : '_get';

        $action = Helper::$requestMethod( 'action', static::DEFAULT_ACTION, 'string' );
        $action = preg_replace( '/[^a-zA-Z0-9\-_]/', '', $action );

        return empty( $action ) || strpos( $action, '_' ) === 0 || in_array( $action, [ 'view', 'modalView', 'tabView' ] )  ? static::DEFAULT_ACTION : $action;
    }

    public static function isAjax()
    {
        return Helper::_get( 'ajax', 0, 'int', [ 1 ] ) == 1;
    }

    public static function getAllRoutes( $method = 'GET' )
    {
        return $method == 'POST' ? static::$routesPOST : static::$routesGET;
    }

    public static function getURL( $controller, $action = '' )
    {
        return admin_url( 'admin.php?page=' . (static::$backend)::getSlugName() . '&module=' . $controller . ( empty( $action ) ? '' : '&action=' . $action ) );
    }
}