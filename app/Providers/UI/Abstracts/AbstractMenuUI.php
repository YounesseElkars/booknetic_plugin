<?php


namespace BookneticApp\Providers\UI\Abstracts;


use BookneticApp\Providers\Core\Route;

abstract class AbstractMenuUI
{
    const MENU_TYPE_LEFT = 1;
    const MENU_TYPE_TOP_RIGHT = 2;
    const MENU_TYPE_TOP_LEFT = 3;

    private $slug;
    private $link;
    private $title;
    private $description;
    private $icon;
    private $priority;
    private $subItems = [];

    /**
     * @param string $slug
     */
    public function __construct ( $slug )
    {
        $this->slug = $slug;
        $this->link = 'admin.php?page=' . ( static::$backend )::getSlugName() . '&module=' . $this->slug;
    }

    /**
     * @param string $slug
     * @return static
     */
    public function subItem ( $slug )
    {
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
     * @param string $link
     * @return $this
     */
    public function setLink ( $link )
    {
        $this->link = $link;

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
    public function getLink ()
    {
        return $this->link;
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
     * @return bool
     */
    public function isActive ()
    {
        return $this->slug === Route::getCurrentModule();
    }

    /**
     * @param string $slug
     * @param int $type
     * @return static
     */
    public static function get ( $slug, $type = self::MENU_TYPE_LEFT )
    {
        if ( empty( static::$items[ $type ][ $slug ] ) )
        {
            static::$items[ $type ][ $slug ] = new static( $slug );
        }

        return static::$items[ $type ][ $slug ];
    }

    /**
     * @return static[]
     */
    public static function getItems ( $type )
    {
        if ( ! empty( static::$items[ $type ] ) )
        {
            usort( static::$items[ $type ], function ( $item1, $item2 ) {
                return ( $item1->getPriority() == $item2->getPriority() ? 0 : ( $item1->getPriority() > $item2->getPriority() ? 1 : -1 ) );
            } );
        }

        return static::$items[ $type ];
    }
}