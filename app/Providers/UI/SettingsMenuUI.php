<?php

namespace BookneticApp\Providers\UI;

use BookneticApp\Providers\UI\Abstracts\AbstractSettingsMenuUI;

class SettingsMenuUI extends AbstractSettingsMenuUI
{
    protected static $items = [];
    protected static $lastItemPriority = 0;
}