<?php

namespace BookneticApp\Providers\Core;

use BookneticApp\Providers\Core\Abstracts\AbstractCapabilities;

class Capabilities extends AbstractCapabilities
{
    protected static $prefix = 'bkntc_';

    protected static $userCapabilities = [];

	private static $tenantCapabilities = [];

	private static $limits = [];

	public static function registerTenantCapability( $capability, $title, $parent = false )
	{
		self::$tenantCapabilities[ $capability ] = [
			'title'     =>  $title,
			'parent'    =>  $parent
		];
	}

	public static function registerLimit( $limit, $title )
	{
		self::$limits[ $limit ] = [
			'title'     =>  $title
		];
	}

	public static function getUserCapabilitiesList()
	{
		$capabilitiesArr = self::$userCapabilities;

		foreach ( $capabilitiesArr as $key => $capability )
		{
			if( is_string( $capability['parent'] ) && ! empty( $capability['parent'] ) && array_key_exists( $capability['parent'], $capabilitiesArr ) )
			{
				if( empty( $capabilitiesArr[ $capability['parent'] ]['children'] ) )
				{
					$capabilitiesArr[ $capability['parent'] ]['children'] = [];
				}

				$capabilitiesArr[ $capability['parent'] ]['children'][ $key ] = $capability;

				unset( $capabilitiesArr[ $key ] );
			}
		}

		return $capabilitiesArr;
	}

	public static function getTenantCapabilitiesList()
	{
		$capabilitiesArr = self::$tenantCapabilities;

		foreach ( $capabilitiesArr as $key => $capability )
		{
			if( is_string( $capability['parent'] ) && ! empty( $capability['parent'] ) && array_key_exists( $capability['parent'], $capabilitiesArr ) )
			{
				if( empty( $capabilitiesArr[ $capability['parent'] ]['children'] ) )
				{
					$capabilitiesArr[ $capability['parent'] ]['children'] = [];
				}

				$capabilitiesArr[ $capability['parent'] ]['children'][ $key ] = $capability;

				unset( $capabilitiesArr[ $key ] );
			}
		}

		return $capabilitiesArr;
	}

	public static function getLimitsList()
	{
		return self::$limits;
	}

	public static function getLimit( $limit )
	{
		if( ! isset( self::$limits[ $limit ] ) )
		{
            return -1;
            // doit: initSaaS'da limitler register olmayib
//			throw new \Exception( bkntc__('Limit %s not found', $limit ) );
		}

        if(\BookneticApp\Providers\Helpers\Helper::isSaaSVersion() && ! \BookneticApp\Providers\Core\Permission::tenantInf())
            return -1;

		return apply_filters( 'bkntc_capability_limit_filter', -1, $limit );
	}

	public static function getTenantCapability( $capability )
	{
		return isset( self::$tenantCapabilities[ $capability ] ) ? self::$tenantCapabilities[ $capability ] : false;
	}

	public static function mustTenant( $capability )
	{
		if( ! self::tenantCan( $capability ) )
		{
			throw new CapabilitiesException( bkntc__('Permission denied!') );
		}
	}

	public static function tenantCan( $capability )
	{
		$capabilityInf = self::getTenantCapability( $capability );

		if( $capabilityInf === false )
		{
            return true;
			//throw new \Exception( bkntc__('Capability %s not found', [ $capability ]) );
		}

		if( ! empty( $capabilityInf['parent'] ) && ! Capabilities::tenantCan( $capabilityInf['parent'] ) )
		{
			return false;
		}

		return apply_filters( 'bkntc_tenant_capability_filter', true, $capability );
	}
}