<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Helpers\Date;
use BookneticApp\Providers\UI\TabUI;

/**
 * @var mixed $parameters
 */

?>

<link rel="stylesheet" href="<?php echo Helper::assets('css/info.css', 'Customers')?>">

<div class="fs-modal-title">
	<div class="title-icon"><img src="<?php echo Helper::icon('info-purple.svg')?>"></div>
	<div class="title-text"><?php echo bkntc__('Customer info')?></div>
	<div class="close-btn" data-dismiss="modal"><i class="fa fa-times"></i></div>
</div>

<div class="fs-modal-body">
	<div class="fs-modal-body-inner">
        <ul class="nav nav-tabs nav-light" data-tab-group="customers_info">
            <?php foreach ( TabUI::get( 'customers_info' )->getSubItems() as $tab ): ?>
                <li class="nav-item"><a class="nav-link" data-tab="<?php echo $tab->getSlug(); ?>" href="#"><?php echo $tab->getTitle(); ?></a></li>
            <?php endforeach; ?>
        </ul>

        <div class="tab-content mt-5">
            <?php foreach ( TabUI::get( 'customers_info' )->getSubItems() as $tab ): ?>
                <div class="tab-pane" data-tab-content="customers_info_<?php echo $tab->getSlug(); ?>" id="tab_<?php echo $tab->getSlug(); ?>"><?php echo $tab->getContent( $parameters ); ?></div>
            <?php endforeach; ?>
        </div>

	</div>
</div>

<div class="fs-modal-footer">
	<button type="button" class="btn btn-lg btn-default" data-dismiss="modal"><?php echo bkntc__('CANCEL')?></button>
</div>
