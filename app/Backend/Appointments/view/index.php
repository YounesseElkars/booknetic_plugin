<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Helpers\Date;

/**
 * @var mixed $parameters
 */

echo $parameters['table'];
?>

<link rel="stylesheet" type="text/css" href="<?php echo Helper::assets('css/appointments.css', 'Appointments')?>" />
<link rel="stylesheet" href="<?php echo Helper::assets('css/info.css', 'Customers')?>">
<script src='<?php echo Helper::assets('js/appointment.js', 'Appointments')?>'></script>

<div class="fs-popover fs-popover-customers" id="customers-list-popover">
	<div class="fs-popover-title">
		<span><?php echo bkntc__('Customers')?></span>
		<img src="<?php echo Helper::icon('cross.svg')?>" class="close-popover-btn">
	</div>
	<div class="fs-popover-content">

	</div>
</div>
