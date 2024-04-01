<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\UI\TabUI;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Helpers\Date;

function breakTpl( $start = '', $end = '', $display = false )
{
	?>
	<div class="form-row break_line<?php echo $display ? '' : ' hidden' ?>">
		<div class="form-group col-md-9">
			<label for="input_duration" class="breaks-label"><?php echo bkntc__('Breaks')?></label>
			<div class="input-group">
				<div class="col-md-6 p-0 m-0"><select class="form-control break_start" placeholder="<?php echo bkntc__('Break start')?>"><option selected><?php echo ! empty( $start ) ? Date::time( $start ) : ''; ?></option></select></div>
				<div class="col-md-6 p-0 m-0"><select class="form-control break_end" placeholder="<?php echo bkntc__('Break end')?>"><option selected><?php echo empty( $end ) ?  '' : ( $end == "24:00" ? "24:00" : Date::time( $end ) ); ?></option></select></div>
			</div>
		</div>

		<div class="form-group col-md-3">
			<img src="<?php echo Helper::assets('icons/unsuccess.svg')?>" class="delete-break-btn">
		</div>
	</div>
	<?php
}

/**
 * @var mixed $parameters
 * @var mixed $_mn
 */
?>

<link rel="stylesheet" href="<?php echo Helper::assets('css/add_new.css', 'Staff')?>">
<script type="application/javascript" src="<?php echo Helper::assets('js/add_new.js', 'Staff')?>" id="add_new_JS" data-mn="<?php echo $_mn?>" data-staff-id="<?php echo (int)$parameters['staff']['id']?>" data-holidays="<?php echo htmlspecialchars( $parameters['holidays'] )?>"></script>

<div class="fs-modal-title">
	<div class="title-icon badge-lg badge-purple"><i class="fa fa-plus"></i></div>
	<div class="title-text"><?php echo $parameters[ 'staff' ][ 'id' ] > 0 ? bkntc__( 'Edit Staff' ) : bkntc__( 'Add Staff' )?></div>
	<div class="close-btn" data-dismiss="modal"><i class="fa fa-times"></i></div>
</div>

<div class="fs-modal-body">
	<div class="fs-modal-body-inner">
		<form id="addStaffForm">

			<div class="nowrap overflow-auto">
				<ul class="nav nav-tabs nav-light" data-tab-group="staff_add">
                    <?php foreach ( TabUI::get( 'staff_add' )->getSubItems() as $tab ): ?>
                        <li class="nav-item"><a class="nav-link" data-tab="<?php echo $tab->getSlug(); ?>" href="#"><?php echo $tab->getTitle(); ?></a></li>
                    <?php endforeach; ?>
				</ul>
			</div>

			<div class="tab-content mt-5">
                <?php foreach ( TabUI::get( 'staff_add' )->getSubItems() as $tab ): ?>
                    <div class="tab-pane" data-tab-content="staff_add_<?php echo $tab->getSlug(); ?>" id="tab_<?php echo $tab->getSlug(); ?>"><?php echo $tab->getContent( $parameters ); ?></div>
                <?php endforeach; ?>
            </div>

		</form>
	</div>
</div>

<div class="fs-modal-footer">
	<?php
	if( $parameters['staff']['id'] > 0 )
	{
		?>
		<button type="button" class="btn btn-lg btn-outline-secondary" id="hideStaffBtn"><?php echo $parameters['staff']['is_active'] != 1 ? bkntc__('UNHIDE STAFF') : bkntc__('HIDE STAFF')?></button>
		<?php
	}
	?>
	<button type="button" class="btn btn-lg btn-outline-secondary" data-dismiss="modal"><?php echo bkntc__('CANCEL')?></button>
	<button type="button" class="btn btn-lg btn-primary" id="addStaffSave"><?php echo $parameters['id'] ? bkntc__('SAVE STAFF') : bkntc__('ADD STAFF')?></button>
</div>

<?php
echo breakTpl();
?>
