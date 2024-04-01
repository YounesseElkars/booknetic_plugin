<?php

namespace BookneticApp\Providers\Helpers;

class Safe
{
    /**
     * Safe string to lower
     *
     * @ref mbstring.func_overload
     *
     * @param string $str
     * @return string
     */
    public static function strtolower( $str )
    {
        if( function_exists( 'mb_strtolower' ) )
            return mb_strtolower( $str );

        return strtolower( $str );
    }


    /**
     * Safe string to upper
     *
     * @ref mbstring.func_overload
     *
     * @param string $str
     * @return string
     */
    public static function strtoupper( $str )
    {
        if( function_exists( 'mb_strtoupper' ) )
            return mb_strtoupper( $str );

        return strtoupper( $str );
    }


    /**
     * Safe string length
     *
     * @ref mbstring.func_overload
     *
     * @param string $str
     * @return int
     */
    public static function strlen( $str )
    {
        if( function_exists( 'mb_strlen' ) )
        {
            /**  mb_strlen in PHP 7.x can return false.
             * @psalm-suppress RedundantCast
             */
            return ( int ) mb_strlen( $str, '8bit' );
        }

        return strlen( $str );
    }

    /**
     * Safe substring
     *
     * @ref mbstring.func_overload
     *
     * @staticvar boolean $exists
     * @param string $str
     * @param int $start
     * @param ?int $length
     * @return string
     */
    public static function substr( $str, $start = 0, $length = null )
    {
        if( $length === 0 )
            return '';

        if( function_exists( 'mb_substr' ) )
            return mb_substr( $str, $start, $length, '8bit' );

        // Unlike mb_substr(), substr() doesn't accept NULL for length
        if( $length !== null )
            return substr( $str, $start, $length );

        return substr( $str, $start );
    }

    /**
     * Safe strpos
     *
     * @ref mbstring.func_overload
     *
     * @staticvar boolean $exists
     * @param string $haystack
     * @param int $needle
     * @param int $offset
     * @param null|string $encoding
     * @return int|false
     */
    public static function strpos( $haystack, $needle, $offset = 0, $encoding = null )
    {
        if( function_exists( 'mb_strpos' ) )
            return mb_strpos( $haystack, $needle, $offset, $encoding );

        return strpos( $haystack, $needle, $offset );
    }
}