<?php

namespace BookneticApp\Backend\Settings;

use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Core\Permission;
use BookneticApp\Backend\Settings\Helpers\BackupService;

class Middleware
{

	public function handle( $next )
	{
		if ( Helper::_get( 'download' ) == 1 )
		{
			BackupService::download();
		}

		return $next();
	}

}