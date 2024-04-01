<?php

namespace BookneticApp\Providers\UI;

use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\UI\Abstracts\AbstractDataTableUI;

class DataTableUI extends AbstractDataTableUI
{
    protected static $helper = Helper::class;
}