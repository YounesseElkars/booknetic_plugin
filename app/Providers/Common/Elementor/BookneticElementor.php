<?php

namespace BookneticApp\Providers\Common\Elementor;


use BookneticApp\Providers\Helpers\Helper;

if (!defined('ABSPATH')) {
    exit;
}

class BookneticElementor
{

    private static $widgets_dir;
    private static $widgets_namespace = '\\BookneticApp\\Providers\\Common\\Elementor\\Widgets\\';

    public static function registerWidgets( $widgets_manager )
    {
        self::$widgets_dir =  dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'Widgets';
        $widgets = glob(self::$widgets_dir . DIRECTORY_SEPARATOR . '*.php');

        foreach ( $widgets as $widget )
        {
            if ( file_exists($widget) )
            {
                $class = str_replace('.php', '', basename($widget));
                $class = is_array($class) ? '' : $class;
                $class = self::$widgets_namespace . $class;

                if ( !( Helper::isSaaSVersion() && !$class::$allowInSaaS ) )
                {
                    $widgets_manager->register( new $class );
                }
            }
        }
    }
}