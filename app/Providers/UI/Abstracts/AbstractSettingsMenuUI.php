<?php


namespace BookneticApp\Providers\UI\Abstracts;


abstract class AbstractSettingsMenuUI
{
    private $slug;
    private $title;
    private $description;
    private $icon;
    private $priority;
    private $subItems = [];
    private $requireSubItems = false;

    /**
     * @param string $slug
     */
    public function __construct ( $slug )
    {
        $this->slug = $slug;
    }

    /**
     * @param string $action
     * @param string $route
     * @return static
     */
    public function subItem ( $action, $route = 'settings' )
    {
        $slug = strtolower( $route . '.' . $action );

        if ( empty( $this->subItems[ $slug ] ) )
        {
            $this->subItems[ $slug ] = new static( $slug );
        }

        return $this->subItems[ $slug ];
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle ( $title )
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription ( $description )
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @param string $icon
     * @return $this
     */
    public function setIcon ( $icon )
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * @param int $priority
     */
    public function setPriority ( $priority )
    {
        $this->priority = ( int ) $priority;

        if ( $priority > static::$lastItemPriority )
        {
            static::$lastItemPriority = $priority;
        }

        return $this;
    }

    /**
     * This menu is parent and requires at least a sub item (otherwise menu will be hidden)
     *
     * @return $this
     */
    public function requireSubItems ()
    {
        $this->requireSubItems = true;

        return $this;
    }

    /**
     * @return static[]
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
     * @return string
     */
    public function getTitle ()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getDescription ()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getIcon ()
    {
        return $this->icon;
    }

    /**
     * @return int
     */
    public function getPriority ()
    {
        return ! empty( $this->priority ) ? $this->priority : ++static::$lastItemPriority;
    }

    /**
     * @return bool Returns if this item requires at least a sub item
     */
    public function isSubItemsRequired ()
    {
        return $this->requireSubItems;
    }

    /**
     * @param string $slug
     * @return static
     */
    public static function get ( $action, $route = 'settings'  )
    {
        $slug = strtolower( $route . '.' . $action );

        if ( empty( static::$items[ $slug ] ) )
        {
            static::$items[ $slug ] = new static( $slug );
        }

        return static::$items[ $slug ];
    }

    /**
     * @return static[]
     */
    public static function getItems ()
    {
        if ( ! empty( static::$items ) )
        {
            usort( static::$items, function ( $item1, $item2 ) {
                return ( $item1->getPriority() == $item2->getPriority() ? 0 : ( $item1->getPriority() > $item2->getPriority() ? 1 : -1 ) );
            } );
        }

        return static::$items;
    }
}