<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Core\Capabilities;
use BookneticApp\Providers\Core\Permission;
use BookneticApp\Providers\Helpers\Date;
use BookneticApp\Providers\Helpers\Helper;

/**
 * @var mixed $parameters
 */

?>

<?php
$isFullNameEnabled = $parameters['show_only_name'];
$firstName = htmlspecialchars( $parameters[ 'customer' ][ 'first_name' ] );
$lastName = htmlspecialchars( $parameters[ 'customer' ][ 'last_name' ] );

$firstName = $firstName . ( $isFullNameEnabled ? ' ' . $lastName : '' );
$lastName =  $isFullNameEnabled ? '' : $lastName;
?>

<link rel="stylesheet" href="<?php echo Helper::assets( 'css/add_new.css', 'Customers' ) ?>">

<div class="form-row">
    <div class="form-group col-md-<?php echo $isFullNameEnabled ? '12' : '6'?>">
        <label for="input_first_name"><?php echo ! $isFullNameEnabled ? bkntc__( 'First Name' ) : bkntc__( 'Full Name' ) ?> <span class="required-star">*</span></label>
        <input type="text" class="form-control" id="input_first_name" value="<?php echo $firstName; ?>">
    </div>
    <div class="form-group col-md-6<?php echo $isFullNameEnabled ? ' hidden' : ''?>">
        <label for="input_last_name"><?php echo bkntc__('Last Name')?> <span class="required-star">*</span></label>
        <input type="text" class="form-control" id="input_last_name" value="<?php echo $lastName ?>">
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-6">
        <label for="input_email"><?php echo bkntc__('Email')?> <?php echo $parameters['email_is_required']=='on'?'<span class="required-star">*</span>':''?></label>
        <input type="text" class="form-control" id="input_email" placeholder="example@gmail.com" value="<?php echo htmlspecialchars($parameters['customer']['email'])?>">
        <input type="hidden" id="customer_original_email" value="<?php echo htmlspecialchars( $parameters[ 'customer' ][ 'email' ] ) ?>">
    </div>
    <div class="form-group col-md-6">
        <label for="input_phone"><?php echo bkntc__('Phone')?> <?php echo $parameters['phone_is_required']=='on'?'<span class="required-star">*</span>':''?></label>
        <input type="text" class="form-control" id="input_phone" value="<?php echo htmlspecialchars($parameters['customer']['phone_number'])?>" data-country-code="<?php echo Helper::getOption('default_phone_country_code', '')?>">
    </div>
</div>

<?php if( Permission::isAdministrator() || Capabilities::userCan( 'customers_allow_to_login' ) ) : ?>
    <div class="form-row">
        <div class="form-group col-md-6">
            <?php if( Helper::isSaaSVersion() ) : ?>
                <label>&nbsp;</label>
            <?php endif; ?>
            <div class="form-control-checkbox">
                <label for="input_allow_customer_to_login"><?php echo bkntc__('Allow to log in')?></label>
                <div class="fs_onoffswitch <?php if( ! $parameters[ 'canAffectToWPUser' ] ) echo 'disabled'?>">
                    <input type="checkbox" <?php if( ! $parameters[ 'canAffectToWPUser' ] ) echo 'disabled'?> class="fs_onoffswitch-checkbox" id="input_allow_customer_to_login" <?php echo ($parameters['customer']['user_id'] > 0 ? ' checked' : '')?>>
                    <label class="fs_onoffswitch-label" for="input_allow_customer_to_login"></label>
                </div>
            </div>
        </div>
        <?php if( !Helper::isSaaSVersion() ): ?>
            <div class="form-group col-md-6" data-hide="allow_customer_to_login">
                <select class="form-control" id="input_wp_user_use_existing">
                    <option value="yes" <?php echo ($parameters['customer']['user_id'] > 0 ? ' selected' : '')?>><?php echo bkntc__('Use existing WordPress user')?></option>
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
                        <option value="<?php echo (int)$user['ID']?>" <?php echo ($user['ID'] == $parameters['customer']['user_id'] ? ' selected' : '')?> data-email="<?php echo htmlspecialchars( $user['user_email'] )?>"><?php echo htmlspecialchars( $user['display_name'] )?></option>
                        <?php
                    }
                    ?>
                </select>
            </div>
        <?php endif; ?>


        <div class="form-group col-md-6" data-hide="create_password">
            <label for="input_wp_user_password"><?php echo bkntc__('User password')?></label>
            <input type="text" <?php if( ! $parameters[ 'canAffectToWPUser' ] ) echo 'disabled'?> class="form-control" id="input_wp_user_password" placeholder="*****">
        </div>
    </div>
<?php endif; ?>

<div class="form-row">
    <div class="form-group col-md-12">
        <label for="input_image"><?php echo bkntc__('Image')?></label>
        <input type="file" class="form-control" id="input_image">
        <div class="form-control" data-label="<?php echo bkntc__('BROWSE')?>"><?php echo bkntc__('PNG, JPG, max 800x800 to 5mb)')?></div>
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-6">
        <label for="input_gender"><?php echo bkntc__('Gender')?></label>
        <select id="input_gender" class="form-control" placeholder="<?php echo bkntc__('Gender')?>">
            <option value="male"<?php echo ($parameters['customer']['gender'] == 'male' ? ' selected' : '')?>><?php echo bkntc__('Male')?></option>
            <option value="female"<?php echo ($parameters['customer']['gender'] == 'female' ? ' selected' : '')?>><?php echo bkntc__('Female')?></option>
        </select>
    </div>
    <div class="form-group col-md-6">
        <label for="input_birthday"><?php echo bkntc__('Date of birth')?></label>
        <div class="inner-addon left-addon">
            <i><img src="<?php echo Helper::icon('calendar.svg')?>"/></i>
            <input data-date-format="<?php echo (htmlspecialchars(Helper::getOption('date_format', 'Y-m-d')))?>" type="text" class="form-control" id="input_birthday" value="<?php echo ( empty($parameters['customer']['birthdate']) ? '' : Date::convertDateFormat( $parameters['customer']['birthdate'] ) )?>">
        </div>
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-12">
        <label for="input_note"><?php echo bkntc__('Note')?></label>
        <textarea id="input_note" class="form-control"><?php echo htmlspecialchars($parameters['customer']['notes'])?></textarea>
    </div>
</div>