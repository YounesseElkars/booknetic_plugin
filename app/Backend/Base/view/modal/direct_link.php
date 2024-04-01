<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Core\Permission;
use BookneticApp\Providers\Helpers\Helper;

/**
 * @var mixed $parameters
 */
$all_pages = get_pages();
?>

<link rel="stylesheet" href="<?php echo Helper::assets('css/direct_link.css' )?>">
<script type="application/javascript" src="<?php echo Helper::assets('js/direct_link.js' )?>" ></script>

<div class="bkntc_direct_booking_url">
    <div class="form-row">
        <?php if( ! Helper::isSaaSVersion() ): ?>

        <div class="form-group col-md-4">
            <label ><?php echo bkntc__('Booking page')?> </label>
            <select class="form-control select2 pages url_generate" data-key="page_id">
                <?php foreach ( $all_pages as $key => $page ) : ?>
                    <option value="<?php echo $page->ID; ?>">
                        <?php echo htmlspecialchars(empty($page->post_title) ? '-' : $page->post_title)?> (ID: <?php echo $page->ID?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>

        <div class="form-group col-md-4">
            <label ><?php echo bkntc__('Category')?> </label>
            <select class="form-control select2 categories url_generate" data-key="category">
                <option value=""><?php echo bkntc__('Select...')?></option>
                <?php foreach ($parameters['categories'] as $category): ?>
                    <option value="<?php echo $category->id  ?>"><?php echo htmlspecialchars($category->name) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group col-md-4">
            <label ><?php echo bkntc__('Service')?> </label>
            <select class="form-control select2 services url_generate" data-key="service">
                <option value=""><?php echo bkntc__('Select...')?></option>
                <?php foreach ($parameters['services'] as $service): ?>
                    <option <?php echo $service->id == $parameters['service_id'] ? 'selected' : '' ?> data-category-id="<?php echo $service->category_id ?>" value="<?php echo $service->id  ?>"><?php echo htmlspecialchars($service->name) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group col-md-6">
            <label ><?php echo bkntc__('Location')?> </label>
            <select class="form-control select2 locations url_generate"  data-key="location">
                <option value=""><?php echo bkntc__('Select...')?></option>
                <?php foreach ($parameters['locations'] as $location): ?>
                    <option <?php echo $location->id == $parameters['location_id'] ? 'selected' : '' ?> value="<?php echo $location->id  ?>"><?php echo htmlspecialchars($location->name) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group col-md-6">
            <label ><?php echo bkntc__('Staff')?> </label>
            <select class="form-control select2 staff url_generate"  data-key="staff">
                <option value=""><?php echo bkntc__('Select...')?></option>
                <?php foreach ($parameters['staff'] as $staff): ?>
                    <option <?php echo $staff->id == $parameters['staff_id'] ? 'selected' : '' ?> value="<?php echo $staff->id ?>"><?php echo htmlspecialchars($staff->name) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

    </div>

    <div class="form-group col-md-12">
        <div class="form-control-checkbox">
            <label for="show_service_step"><?php echo bkntc__('Show service step') ?></label>
            <div class="fs_onoffswitch">
                <input type="checkbox" class="fs_onoffswitch-checkbox url_generate" id="show_service_step" data-key="show_service_step" data-unhide-step="service">
                <label class="fs_onoffswitch-label" for="show_service_step"></label>
            </div>
        </div>
    </div>

    <div class="output_background">
        <span class="bkntc_link_output"><?php echo Helper::isSaaSVersion() ?   site_url() . '/' . htmlspecialchars(Permission::tenantInf()->domain) : Helper::getHostName()?></span>
    </div>

    <div class="modal_actions">
        <button class="btn btn-lg btn-outline-secondary" type="button" data-dismiss="modal"><?php echo bkntc__('CLOSE')?></button>
        <button class="btn btn-lg ml-3 btn-info bkntc_copy_clipboard"><?php echo bkntc__('COPY URL')?></button>
    </div>
</div>

<script>

</script>
