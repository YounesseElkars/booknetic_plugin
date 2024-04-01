<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Helpers\Helper;

?>

<script type="text/javascript" src="<?php echo Helper::assets('js/action_set_booking_status.js', 'Workflow')?>"></script>

<div class="fs-modal-title">
    <div class="title-icon badge-lg badge-purple"><i class="fa fa-plus"></i></div>
    <div class="title-text"><?php echo bkntc__('Edit action')?></div>
    <div class="close-btn" data-dismiss="modal"><i class="fa fa-times"></i></div>
</div>

<div class="fs-modal-body">
    <div class="fs-modal-body-inner">
        <form id="editWorkflowActionForm">

            <div class="form-row">
                <div class="form-group col-md-12">
                    <label for="input_appointment_ids"><?php echo bkntc__('Appointment IDs')?></label>
                    <select id="input_appointment_ids" class="form-control" multiple="multiple">
                        <?php foreach ( $parameters[ 'appointment_ids' ] as $key => $shortcode ): ?>
                            <option value="<?php echo htmlspecialchars( $key ); ?>" <?php echo $shortcode['selected'] ? 'selected' : '';?> ><?php echo htmlspecialchars( $shortcode['name'] ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-12">
                    <label for="input_appointment_set_status"><?php echo bkntc__('Change status to')?></label>
                    <select id="input_appointment_set_status" class="form-control">
                        <?php foreach ( Helper::getAppointmentStatuses() as $key => $status ): ?>
                            <option value="<?php echo htmlspecialchars( $key ); ?>" <?php echo $parameters['status'] === $key ? 'selected' : ''; ?> ><?php echo htmlspecialchars( $status['title'] ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-12">
                    <input type="checkbox" id="input_run_workflows" <?php echo $parameters["run_workflows"] ? "checked" : "" ?>>
                    <label for="input_run_workflows"><?php echo bkntc__('Run workflows')?></label>
                </div>
            </div>

        </form>
    </div>
</div>

<div class="fs-modal-footer">

    <div class="footer_left_action">
        <input type="checkbox" id="input_is_active" <?php echo $parameters['action_info']->is_active ? 'checked' : '' ?>>
        <label for="input_is_active" class="font-size-14 text-secondary"><?php echo bkntc__('Enabled')?></label>
    </div>

    <button type="button" class="btn btn-lg btn-outline-secondary" data-dismiss="modal"><?php echo bkntc__('CANCEL')?></button>
    <button type="button" class="btn btn-lg btn-primary" id="saveWorkflowActionBtn"><?php echo bkntc__('SAVE')?></button>
</div>
