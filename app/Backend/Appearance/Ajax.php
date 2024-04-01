<?php

namespace BookneticApp\Backend\Appearance;

use BookneticApp\Backend\Appearance\Helpers\Theme;
use BookneticApp\Models\Appearance;
use BookneticApp\Providers\Core\Capabilities;
use BookneticApp\Providers\DB\DB;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Core\Permission;

class Ajax extends \BookneticApp\Providers\Core\Controller
{

	public function save()
	{
		$id = Helper::_post('id', 0, 'int');

		if( $id > 0 )
		{
			Capabilities::must( 'appearance_edit' );
		}
		else
		{
			Capabilities::must( 'appearance_add' );
		}

		$name		= Helper::_post('name', '', 'string');
		$custom_css	= Helper::_post('custom_css', '', 'string');
		$height		= Helper::_post('height', '', 'int');
		$fontfamily	= Helper::_post('fontfamily', '', 'string');
		$colors		= Helper::_post('colors', '', 'string');
        $hideSteps = Helper::_post('hide_steps', 0, 'int', [ 0, 1 ]);

		if( $id < 0 || empty( $name ) || empty($height) || empty($fontfamily) )
		{
			return $this->response(false, bkntc__('Please fill in all required fields correctly!'));
		}

		if( !preg_match('/^[a-zA-Z0-9\-\_\. \+]+$/', $fontfamily) )
		{
			return $this->response(false, 'Please enter the valid font-family name!');
		}

		if( $height < 400 || $height > 1500 )
		{
			return $this->response( false, 'Please enter the valid value for the Height field!' );
		}

		$colors = json_decode( $colors, true );

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

		foreach ( $default_colors AS $color_name => $color )
		{
			if( isset( $colors[ $color_name ] ) && is_string( $colors[ $color_name ] ) && preg_match('/\#[a-zA-Z0-9]{1,8}/', $colors[ $color_name ]) )
			{
				$default_colors[ $color_name ] = $colors[ $color_name ];
			}
		}

		$colors = json_encode( $default_colors );

		if( $id > 0 )
		{
			Appearance::where('id', $id)->update([
				'name'	        =>	$name,
				'custom_css'	=>	$custom_css,
				'colors'        =>	$colors,
				'height'        =>  $height,
				'fontfamily'    =>  $fontfamily,
                'hide_steps'    =>  $hideSteps
			]);

			Theme::createThemeCssFile( $id );
		}
		else
		{
			Appearance::insert([
				'name'		    =>	$name,
                'custom_css'	=>	$custom_css,
				'colors'	    =>	$colors,
				'height'        =>  $height,
				'fontfamily'    =>  $fontfamily,
                'hide_steps'    =>  $hideSteps
			]);
		}

		return $this->response( true );
	}

	public function delete()
	{
		Capabilities::must( 'appearance_delete' );

		$id = Helper::_post('id', 0, 'int');

		if( !($id > 0) )
		{
			return $this->response(false, bkntc__('Theme not found!'));
		}

		$getThemeInf = Appearance::get( $id );

		if( !$getThemeInf )
		{
			return $this->response(false, bkntc__('Theme not found!'));
		}

		if( $getThemeInf['is_default'] )
		{
			return $this->response(false, bkntc__('You can not delete default theme!'));
		}

		Appearance::where('id', $id)->delete();

		return $this->response( true );
	}

	public function select_default_appearance()
	{
		Capabilities::must( 'appearance_select' );

		$id = Helper::_post('id', 0, 'int');

		if( ! ( $id > 0 ) )
		{
			return $this->response(false, bkntc__('Theme not found!'));
		}

		$count = Appearance::whereId( $id )->count();

		if( $count === 0 )
		{
			return $this->response(false, bkntc__('Theme not found!'));
		}

		Appearance::where( 'is_default', 1 )->update( [ 'is_default' => 0 ] );
		Appearance::whereId( $id )->update( [ 'is_default' => 1 ] );

		return $this->response( true );
	}

}
