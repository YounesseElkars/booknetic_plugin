<?php

namespace BookneticApp\Providers\UI\Abstracts;


use BookneticApp\Providers\UI\TabUI;
use BookneticApp\Providers\UI\TabItemUI;

class AbstractTabUI
{
    private $slug;
    private $subItems = [];

    /**
     * @param string $slug
     */
    public function __construct ( $slug )
    {
        $this->slug = $slug;
    }

    /**
     * @param string $slug
     * @return AbstractTabItemUI
     */
    public function item ( $slug )
    {
        if ( empty( $this->subItems[ $slug ] ) )
        {
            $this->subItems[ $slug ] = new static::$tabItemUI( $slug, $this->slug );
        }

        return $this->subItems[ $slug ];
    }

    /**
     * @return AbstractTabItemUI[]
     */
    public function getSubItems ()
    {
        if ( ! empty( $this->subItems ) )
        {
            usort( $this->subItems, function ( $item1, $item2 ) {
                return ( $item1->getPriority() == $item2->getPriority() ? 0 : ( $item1->getPriority() > $item2->getPriority() ? 1 : -1 ) );
            } );
        }

        return $this->subItems;
    }

    /**
     * @return string
     */
    public function getSlug ()
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     * @return TabUI
     */
    public static function get ( $slug )
    {
        if ( empty( static::$items[ $slug ] ) )
        {
            static::$items[ $slug ] = new static( $slug );
        }

        return static::$items[ $slug ];
    }

    /**
     * @param string $slug
     * @return TabItemUI[]
     */
    public static function getItems ( $slug )
    {
        return array_key_exists( $slug, static::$items ) ? static::$items[ $slug ] : [];
    }
}