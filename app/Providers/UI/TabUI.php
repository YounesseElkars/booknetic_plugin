<?php

namespace BookneticApp\Providers\UI;

use BookneticApp\Providers\UI\Abstracts\AbstractTabUI;

class TabUI extends AbstractTabUI
{
    protected static $items = [];
    protected static $tabItemUI = TabItemUI::class;
}
