<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Helpers\Helper;

/**
 * @var mixed $parameters
 * @var mixed $_mn
 */

?>

<script type="application/javascript" src="<?php echo Helper::assets('js/add_new_action.js', 'workflow')?>"></script>

<div class="modal-header">
    <h5 class="modal-title"><?php echo bkntc__('Select Action')?></h5>
    <span data-dismiss="modal" class="p-1 cursor-pointer"><i class="fa fa-times"></i></span>
</div>

<div class="modal-body">
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
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-lg btn-outline-secondary" data-dismiss="modal"><?php echo bkntc__('CANCEL')?></button>
    <button type="button" class="btn btn-lg btn-primary" id="addActionNextBtn"><?php echo bkntc__('NEXT')?></button>
</div>
