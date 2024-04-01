<?php

namespace BookneticApp\Providers\Core;

use BookneticApp\Providers\Core\Abstracts\AbstractRoute;

class Route extends AbstractRoute
{
	protected static $routesPOST = [];
    protected static $routesGET = [];
    protected static $globalMiddlewares = [];
    protected static $prefix = 'bkntc_';
    protected static $backend = Backend::class;
}