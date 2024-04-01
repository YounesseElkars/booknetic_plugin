<?php

namespace BookneticApp\Providers\UI\Abstracts;

use BookneticApp\Providers\Helpers\Helper;

abstract class AbstractTabItemUI
{
    private $slug;
    private $title;
    private $priority;
    private $views = [];
    private $sections = [];
    private $tabSlugUI;

    /* setters */

    /**
     * @param string $slug
     */
    public function __construct ( $slug, $tabSlugUI )
    {
        $this->slug = $slug;
        $this->tabSlugUI = $tabSlugUI;
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
     * @param string $viewPath
     * @param array $data
     * @return $this
     */
    public function addView ( $viewPath, $data = [], $priority = 999 )
    {
        $this->views[] = [
            'path'      => $viewPath,
            'data'      => $data,
            'priority'  => $priority
        ];

        return $this;
    }

    /* getters */

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
     * @return int
     */
    public function getPriority ()
    {
        return ! empty( $this->priority ) ? $this->priority : ++static::$lastItemPriority;
    }

    /**
     * @return array
     */
    public function getViews ()
    {
        return $this->views;
    }

    public function sectionStart ( $section )
    {
        $this->sections[ $section ] = apply_filters( 'bkntc_tabitem_' . $this->tabSlugUI . '_' . $this->slug . '_' . $section . '_before', ob_get_clean() );

        ob_start();
    }

    public function sectionEnd ( $section )
    {
        if ( isset( $this->sections[ $section ] ) )
        {
            echo( $this->sections[ $section ] . apply_filters( 'bkntc_tabitem_' . $this->tabSlugUI . '_' . $this->slug . '_' . $section . '_end', ob_get_clean() ) );
        }
    }

    public function setAction ( $section )
    {
        do_action( 'bkntc_tabitem_' . $this->tabSlugUI . '_' . $this->slug . '_' . $section, $this, func_get_args() );
    }

    /**
     * @param array $sharedParameters
     * @return string
     */
    public function getContent ( $sharedParameters = [] )
    {
        ob_start();

        $views = $this->getViews();

        if ( ! empty( $views ) )
        {
            usort( $views, function ( $item1, $item2 ) {
                return ( $item1[ 'priority' ] == $item2[ 'priority' ] ? 0 : ( $item1[ 'priority' ] > $item2[ 'priority' ] ? 1 : -1 ) );
            } );

            foreach ( $views as $view )
            {
                $viewPath = $view[ 'path' ];

                if ( file_exists( $viewPath ) )
                {
                    if ( ! empty( $view[ 'data' ] ) && is_callable( $view[ 'data' ] ) )
                    {
                        $parameters = call_user_func_array( $view[ 'data' ], [ $sharedParameters ] );
                    }

                    $parameters = isset( $parameters ) ? $parameters : ( ! empty( $view[ 'data' ] ) ? $view[ 'data' ] : $sharedParameters );

                    require $viewPath;
                }
            }
        }

        return ( string ) ob_get_clean();
    }
}