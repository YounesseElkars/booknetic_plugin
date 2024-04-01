<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Helpers\Date;

?>
<div id="booknetic_settings_area">
	<link rel="stylesheet" href="<?php echo Helper::assets('css/holidays.css', 'Settings')?>">
	<script type="application/javascript" src="<?php echo Helper::assets('js/holidays.js', 'Settings')?>"></script>

	<div class="actions_panel clearfix">
		<button type="button" class="btn btn-lg btn-success settings-save-btn float-right"><i class="fa fa-check pr-2"></i> <?php echo bkntc__('SAVE CHANGES')?></button>
	</div>

	<div class="settings-light-portlet">
		<div class="ms-title"><?php echo bkntc__('Holidays')?></div>
		<div class="ms-content pl-0 pr-0">

			<div class="yearly_calendar">

			</div>

		</div>
	</div>

	<script>
		var dbHolidays = <?php echo $parameters['holidays']?>;
	</script>
</div>
