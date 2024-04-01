<?php
use BookneticApp\Providers\Helpers\Helper;

defined( 'ABSPATH' ) or die();

/**
 * @var mixed $parameters
 */

?>
<script type="application/javascript" src="<?php echo Helper::assets('js/event_booking_starts.js', 'workflow')?>"></script>

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
                    <select class="form-control" id="input_offset_sign">
                        <option value="before" <?php echo $parameters['offset_sign'] === 'before' ? 'selected' : '' ?>><?php echo bkntc__('Before') ?></option>
                        <option value="after" <?php echo $parameters['offset_sign'] === 'after' ? 'selected' : '' ?>><?php echo bkntc__('After') ?></option>
                    </select>
                    <input type="number" min="0" class="form-control" value="<?php echo $parameters['offset_value'] ?>" id="input_offset_value">
                    <select class="form-control" id="input_offset_type">
                        <option value="minute" <?php echo $parameters['offset_type'] === 'minute' ? 'selected' : '' ?>><?php echo bkntc__('Minute') ?></option>
                        <option value="hour" <?php echo $parameters['offset_type'] === 'hour' ? 'selected' : '' ?>><?php echo bkntc__('Hour') ?></option>
                        <option value="day" <?php echo $parameters['offset_type'] === 'day' ? 'selected' : '' ?>><?php echo bkntc__('Day') ?></option>
                    </select>
                </div>
            </div>
        </div>


        <div class="form-row">
            <div class="form-group col-md-12">
                <label for="input_statuses"><?php echo bkntc__('Status filter')?></label>
                <select class="form-control" id="input_statuses" multiple>
                    <?php
                    foreach ( Helper::getAppointmentStatuses() AS $key => $value )
                    {
                        echo '<option value="' . $key . '"' . (in_array($key, $parameters['statuses']) ? ' selected' : '') . '>' . $value['title'] . '</option>';
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-12">
                <label for="input_locations"><?php echo bkntc__('Locations filter')?></label>
                <select class="form-control" id="input_locations" multiple>
                    <?php
                    foreach ( $parameters['locations'] AS $location )
                    {
                        echo '<option value="' . (int)$location[0] . '" selected>' . htmlspecialchars($location[1]) . '</option>';
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-12">
                <label for="input_services"><?php echo bkntc__('Service filter')?></label>
                <select class="form-control" id="input_services" multiple>
                    <?php
                    foreach ( $parameters['services'] AS $service )
                    {
                        echo '<option value="' . (int)$service[0] . '" selected>' . htmlspecialchars($service[1]) . '</option>';
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-12">
                <label for="input_staff"><?php echo bkntc__('Staff filter')?></label>
                <select class="form-control" id="input_staff" multiple>
                    <?php
                    foreach ( $parameters['staffs'] AS $staff )
                    {
                        echo '<option value="' . (int)$staff[0] . '" selected>' . htmlspecialchars($staff[1]) . '</option>';
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-12">
                <label><?php print bkntc__( 'Locale filter' ); ?></label>

                <select class="form-control" name="locale" id="input_locale">
                    <?php foreach ( $parameters[ 'locales' ] as $lang ): ?>
                        <option value="<?php echo htmlspecialchars( $lang[ 'language' ] ); ?>" lang="<?php echo htmlspecialchars( current( $lang[ 'iso' ] ) ); ?>" <?php echo $parameters[ 'locale' ] == $lang[ 'language' ] ? 'selected' : ''; ?>><?php echo htmlspecialchars( $lang[ 'native_name' ] ); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-12">
                <input type="checkbox" id="input_for_each_customer" <?php echo $parameters["for_each_customer"] ? "checked" : "" ?>>
                <label for="input_for_each_customer"><?php echo bkntc__('Trigger for each customers (in group booking)')?></label>
            </div>
        </div>

    </div>
</div>


<div class="fs-modal-footer">
    <button type="button" class="btn btn-lg btn-default" data-dismiss="modal"><?php echo bkntc__('CANCEL')?></button>
    <button type="button" class="btn btn-lg btn-primary" id="eventSettingsSave"><?php echo bkntc__('SAVE')?></button>
</div>