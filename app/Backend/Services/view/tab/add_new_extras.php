<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Helpers\Helper;

/**
 * @param int $id
 * @param string $name
 * @param int $duration
 * @param int $price
 * @param int $max_quantity
 * @param int $is_active
 * @var mixed $parameters

 */

function extrasTpl($id = 0, $name = '', $duration = 0, $price = 0, $min_quantity = 0, $max_quantity = 0, $is_active = 1 )
{
    ?>
    <div class="form-row extra_row dashed-border" data-id="<?php echo (int)$id?>" data-active="<?php echo (int)$is_active ?>">
        <div class="form-group col-md-4">
            <label class="text-primary"><?php echo bkntc__('Service name')?>:</label>
            <div class="form-control-plaintext" data-tag="name"><?php echo htmlspecialchars($name)?></div>
        </div>
        <div class="form-group col-sm-2">
            <label><?php echo bkntc__('Duration')?>:</label>
            <div class="form-control-plaintext" data-tag="duration"><?php echo !$duration ? '-' : Helper::secFormat( $duration * 60 )?></div>
        </div>
        <div class="form-group col-sm-2">
            <label><?php echo bkntc__('Price')?>:</label>
            <div class="form-control-plaintext" data-tag="price"><?php echo Helper::price( $price , false )?></div>
        </div>
        <div class="form-group col-sm-2">
            <label><?php echo bkntc__('Min. qty')?>:</label>
            <div class="form-control-plaintext" data-tag="min_quantity"><?php echo (int)$min_quantity?></div>
        </div>
        <div class="form-group col-sm-2">
            <label><?php echo bkntc__('Max. qty')?>:</label>
            <div class="form-control-plaintext" data-tag="max_quantity"><?php echo (int)$max_quantity?></div>
        </div>
        <div class="extra_actions">
            <img src="<?php echo Helper::icon('edit.svg', 'Services')?>" class="edit_extra">
            <img src="<?php echo Helper::icon('hide.svg', 'Services')?>" class="hide_extra">
            <img src="<?php echo Helper::icon('copy.svg', 'Services')?>" class="copy_extra" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">
            <div class="dropdown-menu dropdown-menu-right row-actions-area">
                <button class="dropdown-item copy_to_all_services" type="button"><?php echo bkntc__('Copy to all services')?></button>
                <button class="dropdown-item copy_to_parent_services" type="button"><?php echo bkntc__('Copy to the same category services')?></button>
            </div>
            <img src="<?php echo Helper::icon('remove.svg', 'Services')?>" class="delete_extra">
        </div>
    </div>
    <?php
}

?>

<div id="extra_list_area">

    <?php
    foreach ($parameters['extras'] AS $extraInf )
    {
        extrasTpl( $extraInf['id'], $extraInf['name'], $extraInf['duration'], $extraInf['price'], $extraInf['min_quantity'], $extraInf['max_quantity'], $extraInf['is_active'] );
    }
    ?>

</div>

<button type="button" class="btn btn-success" id="new_extra_btn"><?php echo bkntc__('NEW EXTRA')?></button>

<div class="hidden">
    <?php echo extrasTpl(); ?>
</div>
