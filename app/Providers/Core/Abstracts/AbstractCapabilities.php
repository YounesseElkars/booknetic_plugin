<?php

namespace BookneticApp\Providers\Core\Abstracts;

use BookneticApp\Config;
use BookneticApp\Providers\Core\CapabilitiesException;

abstract class AbstractCapabilities
{
    public static function register( $capability, $title, $parent = false )
    {
        static::$userCapabilities[ $capability ] = [
            'title'     =>  $title,
            'parent'    =>  $parent
        ];
    }

    public static function get( $capability )
    {
        return isset( static::$userCapabilities[ $capability ] ) ? static::$userCapabilities[ $capability ] : false;
    }

    /**
     * @throws CapabilitiesException
     */
    public static function must( $capability )
    {
        $capabilityInf = static::get( $capability );

        if( $capabilityInf === false )
            throw new CapabilitiesException( bkntc__('Capability %s not found', [ $capability ]) );

        if( ! empty( $capabilityInf['parent'] ) )
        {
            static::must( $capabilityInf['parent'] );
        }

        $can = apply_filters( 'bkntc_user_capability_filter', true, $capability );

        if ( ! $can )
            throw new CapabilitiesException( bkntc__( 'Permission denied!' ) );
    }

    public static function userCan( $capability )
    {
        try {
            static::must( $capability );

            return true;
        } catch ( CapabilitiesException $e ) {
            return false;
        }
    }

    public static function getUserCapabilityValue($capability)
    {
        if( self::userCan($capability) )
        {
            return Config::getCapabilityCache()[$capability];
        }
        return 'off';
    }
}