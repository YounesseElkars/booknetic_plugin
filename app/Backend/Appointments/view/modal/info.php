<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\UI\TabUI;
use BookneticApp\Providers\Helpers\Helper;

/**
 * @var int $_mn
 * @var array $parameters
 */
?>

<link rel="stylesheet" href="<?php echo Helper::assets('css/info.css', 'Appointments')?>">
<script type="text/javascript" src="<?php echo Helper::assets('js/info.js', 'Appointments')?>" id="add_new_JS_info1" data-mn="<?php echo $_mn; ?>" data-appointment-id="<?php echo $parameters['id']; ?>"></script>

<div class="fs-modal-title">
	<div class="title-icon"><img src="<?php echo Helper::icon('info-purple.svg')?>"></div>
	<div class="title-text"><?php echo bkntc__('Appointment info')?></div>
	<div class="close-btn" data-dismiss="modal"><i class="fa fa-times"></i></div>
</div>

<div class="fs-modal-body">
	<div class="fs-modal-body-inner">
        <ul class="nav nav-tabs nav-light" data-tab-group="appointments_info">
            <?php foreach ( TabUI::get( 'appointments_info' )->getSubItems() as $tab ): ?>
                <li class="nav-item"><a class="nav-link" data-tab="<?php echo $tab->getSlug(); ?>" href="#"><?php echo $tab->getTitle(); ?></a></li>
            <?php endforeach; ?>
        </ul>

        <div class="tab-content mt-5">
            <?php foreach ( TabUI::get( 'appointments_info' )->getSubItems() as $tab ): ?>
                <div class="tab-pane" data-tab-content="appointments_info_<?php echo $tab->getSlug(); ?>" id="tab_<?php echo $tab->getSlug(); ?>"><?php echo $tab->getContent( $parameters['id'] ); ?></div>
            <?php endforeach; ?>
        </div>
	</div>
</div>

<div class="fs-modal-footer">
	<button type="button" class="btn btn-lg btn-danger delete-btn"><?php echo bkntc__('DELETE')?></button>
	<button type="button" class="btn btn-lg btn-primary" data-load-modal="appointments.edit" data-parameter-id="<?php echo $parameters['id']?>" data-dismiss="modal"><?php echo bkntc__('EDIT')?></button>
	<button type="button" class="btn btn-lg btn-default" data-dismiss="modal"><?php echo bkntc__('CLOSE')?></button>
</div>
