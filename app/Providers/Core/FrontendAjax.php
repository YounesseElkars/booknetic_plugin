<?php

namespace BookneticApp\Providers\Core;

use BookneticApp\Providers\Core\Frontend;
use BookneticApp\Providers\Helpers\Helper;

class FrontendAjax
{

	final protected function view( $name, $parameters = [], $response_data = [] )
	{
		$viewsPath	= Frontend::VIEW_DIR . str_replace('.', DIRECTORY_SEPARATOR, basename( $name )) . '.php';

		// check if called view exists
		if( !file_exists( $viewsPath ) )
		{
			return $this->response( false, htmlspecialchars( $name ) . ' - view not exists!' );
		}

		ob_start();
		require $viewsPath;
		$viewOutput = ob_get_clean();

		$response_data['html'] = htmlspecialchars( $viewOutput );

		return $this->response( true, $response_data );
	}

	final protected function response( $status , $arr = [] )
	{
		return Helper::response( $status , $arr, true );
	}


}
