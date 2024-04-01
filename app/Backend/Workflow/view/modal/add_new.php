<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Core\Capabilities;
use BookneticApp\Providers\Helpers\Helper;

?>

<link rel="stylesheet" href="<?php echo Helper::assets('css/add_new.css', 'Workflow')?>">
<script type="text/javascript" src="<?php echo Helper::assets('js/add_new.js', 'Workflow')?>"></script>


<div class="fs-modal-title">
    <div class="title-icon badge-lg badge-purple"><i class="fa fa-plus"></i></div>
    <div class="title-text"><?php echo bkntc__('New Workflow')?></div>
    <div class="close-btn" data-dismiss="modal"><i class="fa fa-times"></i></div>
</div>

<div class="fs-modal-body">
    <div class="fs-modal-body-inner">
        <form id="addWorkflowForm" onsubmit="return false">

            <div class="form-row">
                <div class="form-group col-md-12">
                    <label for="input_name"><?php echo bkntc__('Workflow Name')?></label>
                    <input type="text" class="form-control" id="input_name" value="">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-12">
                    <label for="input_when"><?php echo bkntc__('When This Happens')?> <span class="required-star">*</span></label>
                    <select id="input_when" class="form-control required">
                        <option value=""></option>
                        <?php foreach ($parameters['events'] as $eventKey => $eventValue): ?>

                            <option value="<?php echo $eventKey?>"> <?php echo $eventValue->getTitle(); ?> </option>

                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-12">

                    <label for="input_do_this"><?php echo bkntc__('Do This')?> <span class="required-star">*</span></label>
                    <select id="input_do_this" class="form-control required">
                        <option value=""></option>
                        <?php foreach ($parameters['drivers'] as $driverKey => $driverValue): ?>

                            <option value="<?php echo $driverKey?>"> <?php echo $driverValue->getName(); ?> </option>

                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if( count($parameters['drivers']) < 2 && Capabilities::userCan('boostore') && ( \BookneticApp\Providers\Core\Permission::tenantId() ) == null ): ?>
                <div class="form-group col-md-12">
                    <a href="?page=<?php echo Helper::getBackendSlug() ?>&module=boostore&category=2"><?php echo bkntc__('Get more workflow actions from Boostore')?></a>
                </div>
                <?php endif; ?>
            </div>

        </form>
    </div>
</div>

<div class="fs-modal-footer">
    <div class="footer_left_action">
        <input type="checkbox" id="input_is_active" checked>
        <label for="input_is_active" class="font-size-14 text-secondary"><?php echo bkntc__('Enabled')?></label>
    </div>

    <button type="button" class="btn btn-lg btn-outline-secondary" data-dismiss="modal"><?php echo bkntc__('CANCEL')?></button>
    <button type="button" class="btn btn-lg btn-primary" id="addWorkflowSave"><?php echo bkntc__('CREATE')?></button>
</div>
