<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Helpers\Helper;

?>

<link rel="stylesheet" href="<?php echo Helper::assets( 'css/permission_denied.css' )?>">

<div class="permission_denied_screen">
	<div class="permission_denied_title"><?php echo bkntc__( 'Upgrade needed!' )?></div>
	<div class="permission_denied_subtitle"><?php echo $parameters['text']?></div>
	<div class="permission_denied_image"><img src="<?php echo Helper::assets('images/permission_denied.svg')?>"></div>
	<div class="permission_denied_footer">
		<?php if( !isset( $parameters['no_close_btn'] ) ):?>
			<button type="button" class="btn btn-outline-secondary btn-lg" data-dismiss="modal"><?php echo bkntc__('CLOSE')?></button>
		<?php endif;?>
		<a href="?page=<?php echo Helper::getSlugName() ?>&module=billing&upgrade=1" class="btn btn-primary btn-lg"><?php echo bkntc__('UPGRADE NOW')?></a>
	</div>
</div>