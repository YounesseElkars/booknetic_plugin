<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\UI\TabUI;
use BookneticApp\Providers\Helpers\Helper;

/**
 * @var int $_mn
 * @var array $parameters
 */
?>

<link rel="stylesheet" href="<?php echo Helper::assets('css/edit.css', 'Appointments')?>">
<script type="text/javascript" src="<?php echo Helper::assets('js/edit.js', 'Appointments')?>" id="add_new_JS" data-mn="<?php echo $_mn; ?>" data-max-capacity="<?php echo (int)$parameters['service_capacity']; ?>" data-appointment-id="<?php echo $parameters['id']; ?>"></script>
<script> var priceUpdated = <?= $parameters['priceUpdated']; ?> </script>

<div class="fs-modal-title">
	<div class="title-icon badge-lg badge-purple"><i class="fa fa-pencil-alt"></i></div>
	<div class="title-text"><?php echo bkntc__('Edit Appointment')?></div>
	<div class="close-btn" data-dismiss="modal"><i class="fa fa-times"></i></div>
</div>

<div class="fs-modal-body">
	<div class="fs-modal-body-inner">
		<form id="addAppointmentForm" class="position-relative">
            <ul class="nav nav-tabs nav-light" data-tab-group="appointments_edit">
                <?php foreach ( TabUI::get( 'appointments_edit' )->getSubItems() as $tab ): ?>
                    <li class="nav-item"><a class="nav-link" data-tab="<?php echo $tab->getSlug(); ?>" href="#"><?php echo $tab->getTitle(); ?></a></li>
                <?php endforeach; ?>
            </ul>

            <div class="tab-content mt-5">
                <?php foreach ( TabUI::get( 'appointments_edit' )->getSubItems() as $tab ): ?>
                    <div class="tab-pane" data-tab-content="appointments_edit_<?php echo $tab->getSlug(); ?>" id="tab_<?php echo $tab->getSlug(); ?>"><?php echo $tab->getContent( $parameters[ 'id' ] ); ?></div>
                <?php endforeach; ?>
            </div>
		</form>
	</div>
</div>

<div class="fs-modal-footer">
	<div class="footer_left_action">
		<input type="checkbox" id="input_run_workflows" checked>
		<label for="input_run_workflows" class="font-size-14 text-secondary"><?php echo bkntc__('Run workflows on save')?></label>
	</div>

	<button type="button" class="btn btn-lg btn-outline-secondary" data-dismiss="modal"><?php echo bkntc__('CANCEL')?></button>
	<button type="button" class="btn btn-lg btn-primary" id="addAppointmentSave"><?php echo bkntc__('SAVE')?></button>
</div>