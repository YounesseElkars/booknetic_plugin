<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Helpers\Date;

?>

<div id="booknetic_loading" class="hidden"><div><?php echo bkntc__('Loading...')?></div><div><?php echo bkntc__('( it can take some time, please wait... )')?></div></div>

<div id="booknetic_alert" class="hidden"></div>

<div class="booknetic-box-container">
	<div class="booknetic-box">
		<div class="booknetic-box-logo">
			<img src="<?php echo Helper::assets( 'images/logo-black.svg' ); ?>">
		</div>
		<div class="booknetic-box-info">
			<i class="fas fa-info-circle"></i><?php echo bkntc__( 'Your plugin is disabled. Please activate the plugin.' ); ?>
		</div>
		<div class="booknetic-reason">
			<label><?php echo bkntc__( 'Reason: %s', [ Helper::getOption( 'plugin_alert', '', false ) ], false ); ?></label>
		</div>
		<div>
			<input type="text" id="bookneticPurchaseKey" autocomplete="off" placeholder="<?php echo bkntc__( 'Enter the purchase key' ); ?>">
		</div>
		<div>
			<button type="button" id="bookneticReactivateBtn"><?php echo bkntc__( 'RE-ACTIVATE' ); ?></button>
		</div>
	</div>
</div>
