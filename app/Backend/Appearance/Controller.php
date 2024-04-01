<?php

namespace BookneticApp\Backend\Appearance;

use BookneticApp\Backend\Appearance\Helpers\Theme;
use BookneticApp\Models\Appearance;
use BookneticApp\Models\Location;
use BookneticApp\Providers\Core\Capabilities;
use BookneticApp\Providers\Helpers\Helper;

class Controller extends \BookneticApp\Providers\Core\Controller
{

	public function index()
	{
		Capabilities::must('appearance');

		$appearances = Appearance::fetchAll();

		$this->view( 'index', ['appearances' => $appearances] );
	}

	public function edit()
	{
		$id = Helper::_get( 'id', '0', 'int' );

		$default_colors = [
			'panel'					=>	'#292d32',
			'primary'				=>	'#6c70dc',
			'primary_txt'			=>	'#ffffff',
			'active_steps'			=>	'#4fbf65',
			'active_steps_txt'		=>	'#4fbf65',
			'compleated_steps'		=>	'#6c70dc',
			'compleated_steps_txt'	=>	'#ffffff',
			'other_steps'			=>	'#4d545a',
			'other_steps_txt'		=>	'#626c76',
			'title'					=>	'#292d32',
			'border'				=>	'#53d56c',
			'price'					=>	'#53d56c'
		];

		$appearanceInf = Appearance::get( $id );

		if( $appearanceInf )
		{
			Capabilities::must('appearance_edit');

			$colors2 = json_decode( $appearanceInf['colors'], true );

			foreach ( $default_colors AS $color_name => $color )
			{
				if( isset( $colors2[ $color_name ] ) && is_string( $colors2[ $color_name ] ) )
				{
					$default_colors[ $color_name ] = htmlspecialchars($colors2[ $color_name ]);
				}
			}
		}
		else
		{
			Capabilities::must('appearance_add');

			$appearanceInf = [
				'name'          =>  '',
				'fontfamily'    =>  'Poppins',
				'height'        =>  600,
                'hide_steps'    =>  '0'
			];
		}

		$locations = Location::fetchAll();

		$this->view( 'edit', [
			'id'		=> $id,
			'info'		=> $appearanceInf,
			'locations'	=> $locations,
			'colors'	=> $default_colors,
			'css_file'  => Theme::getThemeCss( $id )
		] );
	}

}
