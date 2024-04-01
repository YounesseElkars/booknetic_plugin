<?php

defined( 'ABSPATH' ) or die();

/**
 * @var mixed $parameters
 */

?>
<div class="form-row">
    <div class="form-group col-md-12">
        <label for="input_location_name"><?php echo bkntc__('Location Name')?> <span class="required-star">*</span></label>
        <input type="text" data-multilang="true" data-multilang-fk="<?php echo (int)$parameters['location']['id']?>" class="form-control" id="input_location_name" value="<?php echo htmlspecialchars($parameters['location']['name'])?>">
    </div>
</div>

<div class="form-group">
    <label for="input_image"><?php echo bkntc__('Image')?></label>
    <input type="file" class="form-control" id="input_image">
    <div class="form-control" data-label="<?php echo bkntc__('BROWSE')?>"><?php echo bkntc__('(PNG, JPG, max 800x800 to 5mb)')?></div>
</div>

<div class="form-row">
    <div class="form-group col-md-12">
        <label for="input_address"><?php echo bkntc__('Address')?></label>
        <input type="text" class="form-control" data-multilang="true" data-multilang-fk="<?php echo (int)$parameters['location']['id']?>" id="input_address" value="<?php echo htmlspecialchars($parameters['location']['address'])?>">
        <div id="divmap"></div>
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-12">
        <label for="input_phone"><?php echo bkntc__('Phone')?></label>
        <input type="text" class="form-control" id="input_phone" value="<?php echo htmlspecialchars($parameters['location']['phone_number'])?>">
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-12">
        <label for="input_note"><?php echo bkntc__('Description')?></label>
        <textarea id="input_note" data-multilang="true" data-multilang-fk="<?php echo (int)$parameters['location']['id']?>" class="form-control"><?php echo htmlspecialchars($parameters['location']['notes'])?></textarea>
    </div>
</div>