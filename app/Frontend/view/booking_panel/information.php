<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Helpers\Date;

/**
 * @var mixed $parameters
 */

?>

<div>
	<?php
	if(
		Helper::getOption('facebook_login_enable', 'off', false) == 'on'
		&& !empty( Helper::getOption('facebook_app_id', '', false) )
		&& !empty( Helper::getOption('facebook_app_secret', '', false) )
	)
	{
		?>
		<button type="button" class="booknetic_social_login_facebook" data-href="<?php echo site_url() . '/?' . Helper::getSlugName() . '_action=facebook_login'?>"><?php echo bkntc__('CONTINUE WITH FACEBOOK')?></button>
		<?php
	}

	if(
		Helper::getOption('google_login_enable', 'off', false) == 'on'
		&& !empty( Helper::getOption('google_login_app_id', '', false) )
		&& !empty( Helper::getOption('google_login_app_secret', '', false) )
	)
	{
		?>
		<button type="button" class="booknetic_social_login_google" data-href="<?php echo site_url() . '/?' . Helper::getSlugName() . '_action=google_login'?>"><?php echo bkntc__('CONTINUE WITH GOOGLE')?></button>
		<?php
	}
	?>
</div>

<div class="form-row">
	<div class="form-group col-md-<?php echo $parameters['show_only_name'] ? '12' : '6'?>">
		<label for="bkntc_input_name" data-required="true"><?php echo $parameters[ 'show_only_name' ] ? bkntc__( 'Full Name' ) : bkntc__('Name') ?></label>
		<input type="text" id="bkntc_input_name" class="form-control" name="first_name" value="<?php echo htmlspecialchars($parameters['name'] . ( $parameters['show_only_name'] ? ($parameters['name'] ? ' ' : '') . $parameters['surname'] : '' ))?>">
	</div>
	<div class="form-group col-md-6<?php echo $parameters['show_only_name'] ? ' booknetic_hidden' : ''?>">
		<label for="bkntc_input_surname"<?php echo $parameters['show_only_name'] ? '' : ' data-required="true"'?>><?php echo bkntc__('Surname')?></label>
		<input type="text" id="bkntc_input_surname" class="form-control" name="last_name" value="<?php echo htmlspecialchars($parameters['show_only_name'] ? '' : $parameters['surname'])?>">
	</div>
</div>
<div class="form-row">
	<div class="form-group col-md-6">
		<label for="bkntc_input_email" <?php echo $parameters['email_is_required']=='on'?' data-required="true"':''?>><?php echo bkntc__('Email')?></label>
		<input type="text" id="bkntc_input_email" class="form-control" name="email" value="<?php echo htmlspecialchars($parameters['email'])?>" <?php echo (!empty($parameters['email']) && $parameters['email_disabled']) ? "disabled" : "" ?>>
    </div>
    <div class="form-group col-md-6">
        <label for="bkntc_input_phone" <?php echo $parameters['phone_is_required']=='on'?' data-required="true"':''?>><?php echo bkntc__('Phone')?></label>
		<input type="text" id="bkntc_input_phone" class="form-control" name="phone" value="<?php echo htmlspecialchars($parameters['phone'])?>" data-country-code="<?php echo Helper::getOption('default_phone_country_code', '')?>">
	</div>
</div>
<?php if( $parameters['how_many_people_can_bring'] > 0 ) : ?>
    <div id="booknetic_bring_someone_section">
        <div class="form-row">
            <div class="form-group col-md-6">
                <input type="checkbox" id="booknetic_bring_someone_checkbox">
                <label for="booknetic_bring_someone_checkbox"><?php echo bkntc__('Bring People with You')?></label>
            </div>

            <div class="form-group col-md-6 booknetic_number_of_brought_customers d-none">
                <label for=""><?php echo bkntc__('Number of people:') ?></label>
                <div class="booknetic_number_of_brought_customers_quantity">
                    <div class="booknetic_number_of_brought_customers_dec">-</div>
                    <input type="text" class="booknetic_number_of_brought_customers_quantity_input" value="0" data-max-quantity="<?php echo ($parameters['how_many_people_can_bring']);?>">
                    <div class="booknetic_number_of_brought_customers_inc">+</div>
                </div>
            </div>
        </div>
    </div>
<?php endif;?>

<?php do_action( 'bkntc_after_information_inputs', $parameters['service']); ?>
