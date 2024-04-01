<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Helpers\Helper;

?>

<script type="application/javascript" src="<?php echo Helper::assets('js/event_customer_created.js', 'workflow')?>"></script>

<div class="fs-modal-title">
    <div class="title-text"><?php echo bkntc__('Edit event settings')?></div>
    <div class="close-btn" data-dismiss="modal"><i class="fa fa-times"></i></div>
</div>

<div class="fs-modal-body">
    <div class="fs-modal-body-inner">

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

    </div>
</div>


<div class="fs-modal-footer">
    <button type="button" class="btn btn-lg btn-default" data-dismiss="modal"><?php echo bkntc__('CANCEL')?></button>
    <button type="button" class="btn btn-lg btn-primary" id="eventSettingsSave"><?php echo bkntc__('SAVE')?></button>
</div>