<?php
use BookneticApp\Providers\Helpers\Helper;

defined( 'ABSPATH' ) or die();

/**
 * @var mixed $parameters
 */

?>
<script type="application/javascript" src="<?php echo Helper::assets('js/event_tenant_notified.js', 'workflow')?>"></script>

<div class="fs-modal-title">
    <div class="title-text"><?php echo bkntc__('Edit event settings')?></div>
    <div class="close-btn" data-dismiss="modal"><i class="fa fa-times"></i></div>
</div>

<div class="fs-modal-body">
    <div class="fs-modal-body-inner">

        <div class="form-row">
            <div class="form-group col-md-12">
                <label for="input_locations"><?php echo bkntc__('Offset')?></label>
                <div class="input-group">
                    <input type="text" disabled class="form-control" value="<?php echo bkntc__('Before') ?>">
                    <input type="number" min="0" class="form-control" value="<?php echo $parameters['offset_value'] ?>" id="input_offset_value">
                    <select class="form-control" id="input_offset_type">
                        <option value="minute" <?php echo $parameters['offset_type'] === 'minute' ? 'selected' : '' ?>><?php echo bkntc__('Minute') ?></option>
                        <option value="hour" <?php echo $parameters['offset_type'] === 'hour' ? 'selected' : '' ?>><?php echo bkntc__('Hour') ?></option>
                        <option value="day" <?php echo $parameters['offset_type'] === 'day' ? 'selected' : '' ?>><?php echo bkntc__('Day') ?></option>
                    </select>
                </div>
            </div>
        </div>

    </div>
</div>


<div class="fs-modal-footer">
    <button type="button" class="btn btn-lg btn-default" data-dismiss="modal"><?php echo bkntc__('CANCEL')?></button>
    <button type="button" class="btn btn-lg btn-primary" id="eventSettingsSave"><?php echo bkntc__('SAVE')?></button>
</div>