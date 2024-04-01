<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Core\Capabilities;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Core\Permission;

/**
 * @var array $parameters
 */
?>

<div class="form-row">
    <div class="form-group col-md-6">
        <label for="input_name"><?php echo bkntc__('Full Name')?> <span class="required-star">*</span></label>
        <input type="text" data-multilang="true" data-multilang-fk="<?php echo $parameters[ 'staff' ][ 'id' ] ?>" class="form-control" id="input_name" value="<?php echo htmlspecialchars($parameters['staff']['name'])?>">
    </div>

    <div class="form-group col-md-6">
        <label for="input_name"><?php echo bkntc__('Profession')?></label>
        <input type="text" data-multilang="true" data-multilang-fk="<?php echo $parameters[ 'staff' ][ 'id' ] ?>" class="form-control" id="input_profession" value="<?php echo htmlspecialchars($parameters['staff']['profession'])?>">
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-6">
        <label for="input_email"><?php echo bkntc__('Email')?> <span class="required-star">*</span></label>
        <input type="text" class="form-control" id="input_email" placeholder="example@gmail.com" value="<?php echo htmlspecialchars($parameters['staff']['email'])?>" <?php echo ($parameters['staff']['id'] > 0 && $parameters['staff']->user_id > 0 && !Permission::isAdministrator() ? ' disabled' : '')?>>
    </div>
    <div class="form-group col-md-6">
        <label for="input_phone"><?php echo bkntc__('Phone')?></label>
        <input type="text" class="form-control" id="input_phone" value="<?php echo htmlspecialchars($parameters['staff']['phone_number'])?>">
    </div>
</div>
<?php if( Permission::isAdministrator() || Capabilities::userCan( 'staff_allow_to_login' ) ) : ?>
    <div class="form-row">
        <div class="form-group col-md-6">
            <?php if( Helper::isSaaSVersion() ) : ?>
                <label>&nbsp;</label>
            <?php endif; ?>
            <div class="form-control-checkbox">
                <label for="input_allow_staff_to_login"><?php echo bkntc__('Allow to log in')?></label>
                <div class="fs_onoffswitch">
                    <input type="checkbox" class="fs_onoffswitch-checkbox" id="input_allow_staff_to_login" <?php echo ($parameters['staff']['user_id'] > 0 ? ' checked' : '')?>>
                    <label class="fs_onoffswitch-label" for="input_allow_staff_to_login"></label>
                </div>
            </div>
        </div>
        <?php if( !Helper::isSaaSVersion() ): ?>
            <div class="form-group col-md-6" data-hide="allow_staff_to_login">
                <select class="form-control" id="input_wp_user_use_existing">
                    <option value="yes" <?php echo ($parameters['staff']['user_id'] > 0 ? ' selected' : '')?>><?php echo bkntc__('Use existing WordPress user')?></option>
                    <option value="no"><?php echo bkntc__('Create new WordPress user')?></option>
                </select>
            </div>
        <?php else: ?>
            <input type="hidden" id="input_wp_user_use_existing" value="no">
        <?php endif; ?>
        <?php if( !Helper::isSaaSVersion() ): ?>
            <div class="form-group col-md-6" data-hide="existing_user">
                <label for="input_wp_user"><?php echo bkntc__('WordPress user')?></label>
                <select class="form-control" id="input_wp_user">
                    <?php
                    foreach ( $parameters['users'] AS $user )
                    {
                        ?>
                        <option value="<?php echo (int)$user['ID']?>" <?php echo ($user['ID'] == $parameters['staff']['user_id'] ? ' selected' : '')?> data-email="<?php echo htmlspecialchars( $user['user_email'] )?>"><?php echo htmlspecialchars( $user['display_name'] )?></option>
                        <?php
                    }
                    ?>
                </select>
            </div>
            <?php if ( $parameters[ 'staff' ][ 'id' ] > 0 ): ?>
                <div class="form-group col-md-6" data-hide="existing_user">
                    <label>&nbsp;</label>
                    <div class="form-control-checkbox">
                        <label for="input_update_wp_user"><?php echo bkntc__('Update Wordpress User')?></label>
                        <div class="fs_onoffswitch">
                            <input type="checkbox" class="fs_onoffswitch-checkbox" id="input_update_wp_user" <?php echo ''?>>
                            <label class="fs_onoffswitch-label" for="input_update_wp_user"></label>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        <div class="form-group col-md-6" data-hide="create_password">
            <label for="input_wp_user_password"><?php echo bkntc__('User password')?></label>
            <input type="text" class="form-control" id="input_wp_user_password" placeholder="*****">
        </div>
    </div>
<?php endif; ?>

<div class="form-row">
    <div class="form-group col-md-12">
        <label for="input_image"><?php echo bkntc__('Image')?></label>
        <input type="file" class="form-control" id="input_image">
        <div class="form-control" data-label="<?php echo bkntc__('BROWSE')?>"><?php echo bkntc__('(PNG, JPG, max 800x800 to 5mb)')?></div>
    </div>
</div>

<?php if (Capabilities::tenantCan('locations')) : ?>
<div class="form-row">
    <div class="form-group col-md-12">
        <label for="input_locations"><?php echo bkntc__('Locations')?> <span class="required-star">*</span></label>
        <select class="form-control" id="input_locations" multiple>
            <?php
            $selectedLocations = explode(',', $parameters['staff']['locations']);
            foreach( $parameters['locations'] AS $location )
            {
                echo '<option value="' . (int)$location['id'] . '"' . ( in_array($location['id'], $selectedLocations) ? ' selected' : '' ) .'>' . htmlspecialchars( $location['name'] ) . '</option>';
            }
            ?>
        </select>
    </div>
</div>
<?php endif; ?>

<div class="form-row">
    <div class="form-group col-md-12">
        <label for="input_services"><?php echo bkntc__('Services')?></label>
        <select class="form-control" id="input_services" multiple>
            <?php
            foreach( $parameters['services'] AS $serviceInf )
            {
                echo '<option value="' . (int)$serviceInf['id'] . '"' . ( in_array($serviceInf['id'], $parameters['selected_services']) ? ' selected' : '' ) .'>' . htmlspecialchars($serviceInf['name']) . '</option>';
            }
            ?>
        </select>
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-12">
        <label for="input_note"><?php echo bkntc__('Note')?></label>
        <textarea id="input_note" class="form-control"><?php echo htmlspecialchars($parameters['staff']['about'])?></textarea>
    </div>
</div>
