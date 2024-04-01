<?php

namespace BookneticApp\Providers\Helpers;

use BookneticApp\Models\ServiceCategory;
use BookneticApp\Providers\Helpers\Helper;

class Math
{

	private static $scale;

	private static function getScale()
	{
		if( is_null( self::$scale ) )
		{
			self::$scale = Helper::getOption('price_number_of_decimals', '2');
		}

		return self::$scale;
	}

	public static function add( $num1, $num2, $scale = null )
	{
		if( is_null( $scale ) )
			$scale = self::getScale();

        $num1 = self::round( $num1, $scale );
        $num2 = self::round( $num2, $scale );

		if( function_exists('bcadd') )
			return bcadd( $num1, $num2, $scale );

		return $num1 + $num2;
	}

	public static function sub( $num1, $num2, $scale = null )
	{
		if( is_null( $scale ) )
			$scale = self::getScale();

        $num2 = self::round( $num2, $scale );
        $num1 = self::round( $num1, $scale );

		if( function_exists('bcsub') )
			return bcsub( $num1, $num2, $scale );

		return $num1 - $num2;
	}

	public static function mul( $num1, $num2, $scale = null )
	{
		if( is_null( $scale ) )
			$scale = self::getScale();

        $num1 = self::round( $num1, $scale );
        $num2 = self::round( $num2, $scale );

		if( function_exists('bcmul') )
			return bcmul( $num1, $num2, $scale );

		return $num1 * $num2;
	}

	public static function div( $num1, $num2, $scale = null )
	{
		if( is_null( $scale ) )
			$scale = self::getScale();

        $num1 = self::round( $num1, $scale );
        $num2 = self::round( $num2, $scale );

		if( function_exists('bcdiv') )
			return bcdiv( $num1, $num2, $scale );

		return self::floor( $num1 / $num2, $scale );
	}

	public static function floor( $num, $scale = null )
	{
		if( is_null( $scale ) )
			$scale = self::getScale();

        $num = self::round( $num, $scale );

		if( function_exists('bcadd') )
			return bcadd( $num, 0, $scale );

		if( !is_numeric( $num ) )
			$num = 0;

		$mult = pow(10, $scale);

		return floor( (string)( $num * $mult ) ) / $mult;
	}

	public static function abs( $num )
	{
		return abs( $num );
	}

    private static function round( $number, $scale ) {
        return is_numeric( $number ) ? round( (float) $number, $scale ) : $number;
    }
}