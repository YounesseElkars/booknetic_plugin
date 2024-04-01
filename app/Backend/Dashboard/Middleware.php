<?php

namespace BookneticApp\Backend\Dashboard;

use BookneticApp\Providers\Core\Capabilities;

class Middleware
{

	public function handle( $next )
	{
		if( ! Capabilities::tenantCan( 'dashboard' ) )
		{
			return '';
		}

		return $next();
	}

}
