<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Helpers\Date;

/**
 * @var mixed $parameters
 */

echo $parameters['table'];
?>
<link rel="stylesheet" type="text/css" href="<?php echo Helper::assets('css/bootstrap-colorpicker.min.css', 'Services')?>" />
<script type="application/javascript" src="<?php echo Helper::assets('js/bootstrap-colorpicker.min.js', 'Services')?>"></script>

<link rel="stylesheet" type="text/css" href="<?php echo Helper::assets('css/service-list.css', 'Services')?>" />
<script type="text/javascript" src="<?php echo Helper::assets('js/services-list.js', 'Services')?>"></script>
