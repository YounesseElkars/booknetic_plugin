<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Helpers\Date;
use BookneticApp\Providers\Core\Permission;
use BookneticApp\Providers\UI\TabUI;

/**
 * @var mixed $parameters
 */

?>

<link rel="stylesheet" href="<?php echo Helper::assets('css/intlTelInput.min.css', 'front-end')?>">

<script type="application/javascript" src="<?php echo Helper::assets('js/intlTelInput.min.js', 'front-end')?>"></script>
<script type="application/javascript" src="<?php echo Helper::assets('js/utilsIntlTelInput.js', 'front-end')?>"></script>
<script type="application/javascript" src="<?php echo Helper::assets('js/add_new.js', 'Customers')?>" id="add_new_JS" data-customer-id="<?php echo (int)$parameters['customer']['id']?>"></script>

<div class="fs-modal-title">
	<div class="title-icon badge-lg badge-purple"><i class="fa fa-plus"></i></div>
	<div class="title-text"><?php echo $parameters['customer'] ? bkntc__('Customer') : bkntc__('Add Customer')?></div>
	<div class="close-btn" data-dismiss="modal"><i class="fa fa-times"></i></div>
</div>

<div class="fs-modal-body">
	<div class="fs-modal-body-inner">
		<form id="addCustomerForm">

            <ul class="nav nav-tabs nav-light" data-tab-group="customers_add_new">
                <?php foreach ( TabUI::get( 'customers_add_new' )->getSubItems() as $tab ): ?>
                    <li class="nav-item"><a class="nav-link" data-tab="<?php echo $tab->getSlug(); ?>" href="#"><?php echo $tab->getTitle(); ?></a></li>
                <?php endforeach; ?>
            </ul>

            <div class="tab-content mt-5">
                <?php foreach ( TabUI::get( 'customers_add_new' )->getSubItems() as $tab ): ?>
                    <div class="tab-pane" data-tab-content="customers_add_new_<?php echo $tab->getSlug(); ?>" id="tab_<?php echo $tab->getSlug(); ?>"><?php echo $tab->getContent( $parameters ); ?></div>
                <?php endforeach; ?>
            </div>

		</form>
	</div>
</div>

<div class="fs-modal-footer">
	<button type="button" class="btn btn-lg btn-outline-secondary" data-dismiss="modal"><?php echo bkntc__('CANCEL')?></button>
	<button type="button" class="btn btn-lg btn-primary" id="addCustomerSave"><?php echo $parameters['customer'] ? bkntc__('SAVE') : bkntc__('ADD CUSTOMER')?></button>
</div>
