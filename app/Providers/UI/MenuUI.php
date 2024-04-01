<?php

namespace BookneticApp\Providers\UI;

use BookneticApp\Providers\Core\Backend;
use BookneticApp\Providers\UI\Abstracts\AbstractMenuUI;

class MenuUI extends AbstractMenuUI
{
    protected static $items = [
        self::MENU_TYPE_LEFT      => [],
        self::MENU_TYPE_TOP_RIGHT => [],
        self::MENU_TYPE_TOP_LEFT  => [],
    ];
    protected static $lastItemPriority = 0;
    protected static $backend = Backend::class;
}