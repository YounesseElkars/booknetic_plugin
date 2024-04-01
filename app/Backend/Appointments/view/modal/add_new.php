<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\UI\TabUI;
use BookneticApp\Providers\Helpers\Helper;

/**
 * @var int $_mn
 * @var array $parameters
 */
?>

<link rel="stylesheet" href="<?php echo Helper::assets('css/add_new.css', 'Appointments')?>">
<script type="text/javascript" src="<?php echo Helper::assets('js/add_new.js', 'Appointments')?>" id="add_new_JS_add1" data-mn="<?php echo $_mn; ?>"></script>

<div class="fs-modal-title">
	<div class="title-icon"><img src="<?php echo Helper::icon('add-employee.svg')?>"></div>
	<div class="title-text"><?php echo bkntc__('New appointment')?></div>
	<div class="close-btn" data-dismiss="modal"><i class="fa fa-times"></i></div>
</div>

<div class="fs-modal-body">
	<div class="fs-modal-body-inner">
		<form id="addAppointmentForm" class="position-relative">

			<div class="first-step">
                <ul class="nav nav-tabs nav-light" data-tab-group="appointments_add_new">
                    <?php foreach ( TabUI::get( 'appointments_add_new' )->getSubItems() as $tab ): ?>
                        <li class="nav-item"><a class="nav-link" data-tab="<?php echo $tab->getSlug(); ?>" href="#"><?php echo $tab->getTitle(); ?></a></li>
                    <?php endforeach; ?>
                </ul>

                <div class="tab-content mt-5">
                    <?php foreach ( TabUI::get( 'appointments_add_new' )->getSubItems() as $tab ): ?>
                        <div class="tab-pane" data-tab-content="appointments_add_new_<?php echo $tab->getSlug(); ?>" id="tab_<?php echo $tab->getSlug(); ?>"><?php echo $tab->getContent( $parameters[ 'data' ] ); ?></div>
                    <?php endforeach; ?>
                </div>
			</div>

			<div class="second-step hidden">
				<div class="form-row">
					<div class="form-group col-md-12">
						<table class="table-gray dashed-border">
							<thead>
								<tr>
									<th><?php echo bkntc__('#')?></th>
									<th><?php echo bkntc__('DATE')?></th>
									<th><?php echo bkntc__('TIME')?></th>
								</tr>
							</thead>
							<tbody class="dates-table">

							</tbody>
						</table>
					</div>
				</div>
				<table></table>
			</div>

		</form>
	</div>
</div>

<div class="fs-modal-footer">
    <div class="footer_left_action">
        <input type="checkbox" id="input_run_workflows" checked>
        <label for="input_run_workflows" class="font-size-14 text-secondary"><?php echo bkntc__('Run workflows on save')?></label>
    </div>

	<button type="button" class="btn btn-lg btn-default" data-dismiss="modal"><?php echo bkntc__('CANCEL')?></button>
	<button type="button" class="btn btn-lg btn-primary" id="addAppointmentSave"><?php echo bkntc__('SAVE')?></button>
</div>


