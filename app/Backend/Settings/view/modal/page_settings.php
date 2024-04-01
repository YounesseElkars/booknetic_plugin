<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Helpers\Helper;

?>
<div id="booknetic_settings_area">
	<link rel="stylesheet" href="<?php echo Helper::assets('css/general_settings.css', 'Settings')?>">
	<script type="application/javascript" src="<?php echo Helper::assets('js/page_settings.js', 'Settings')?>"></script>

	<div class="actions_panel clearfix">
		<button type="button" class="btn btn-lg btn-success settings-save-btn float-right"><i class="fa fa-check pr-2"></i> <?php echo bkntc__('SAVE CHANGES')?></button>
	</div>

	<div class="settings-light-portlet">
		<div class="ms-title">
			<?php echo bkntc__('Page Settings')?>
		</div>
		<div class="ms-content">

			<form class="position-relative">

                    <?php if( ! Helper::isSaaSVersion() ):?>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="input_change_status_page_id"><?php echo bkntc__('Change Appointment Status Page')?>:</label>
                            <select class="form-control" id="input_change_status_page_id">
                                <?php foreach ( get_pages() AS $page ) : ?>
                                    <option value="<?php echo htmlspecialchars($page->ID)?>"<?php echo Helper::getOption('change_status_page_id', '', false) == $page->ID ? ' selected' : ''?>><?php echo htmlspecialchars(empty($page->post_title) ? '-' : $page->post_title)?> (ID: <?php echo $page->ID?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif;?>

                        <div class="form-group col-md-3">
                            <label for="input_time_restriction_to_change_appointment_status"><?php echo bkntc__('Link expire')?>:</label>
                            <select class="form-control" id="input_time_restriction_to_change_appointment_status">
                                <option value="0"<?php echo Helper::getOption('time_restriction_to_change_status', '0')=='0' ? ' selected':''?>><?php echo bkntc__('Disabled')?></option>
                                <?php foreach ( Helper::timeslotsAsMinutes() as $minute ): ?>
                                    <option value="<?php echo $minute?>"<?php echo Helper::getOption('time_restriction_to_change_status', '0')==$minute ? ' selected':''?>><?php echo Helper::secFormat($minute*60)?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="input_restriction_type_to_change_appointment_status">&nbsp;</label>
                            <select class="form-control" id="input_restriction_type_to_change_appointment_status">
                                <option value="static"<?php echo Helper::getOption('restriction_type_to_change_status', 'static')=='static' ? ' selected':''?>><?php echo bkntc__('After link created') ?></option>
                                <option value="dynamic"<?php echo Helper::getOption('restriction_type_to_change_status', 'static')=='dynamic' ? ' selected':''?>><?php echo bkntc__( 'Before appointment starts' ) ?></option>
                            </select>
                        </div>

                    <?php if( ! Helper::isSaaSVersion() ):?>
                        <div class="form-group col-md-6">
                            <label for="input_booknetic_signin_page_id"><?php echo bkntc__('Booknetic Sign In Page')?>:</label>
                            <select class="form-control" id="input_booknetic_signin_page_id">
                                <?php foreach ( get_pages() AS $page ) : ?>
                                    <option value="<?php echo htmlspecialchars($page->ID)?>"<?php echo Helper::getOption('regular_sing_in_page', '', false) == $page->ID ? ' selected' : ''?>><?php echo htmlspecialchars(empty($page->post_title) ? '-' : $page->post_title)?> (ID: <?php echo $page->ID?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="input_booknetic_signup_page_id"><?php echo bkntc__('Booknetic Sign Up Page')?>:</label>
                            <select class="form-control" id="input_booknetic_signup_page_id">
                                <?php foreach ( get_pages() AS $page ) : ?>
                                    <option value="<?php echo htmlspecialchars($page->ID)?>"<?php echo Helper::getOption('regular_sign_up_page', '', false) == $page->ID ? ' selected' : ''?>><?php echo htmlspecialchars(empty($page->post_title) ? '-' : $page->post_title)?> (ID: <?php echo $page->ID?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="input_booknetic_forgot_password_page_id"><?php echo bkntc__('Booknetic Forgot Password Page')?>:</label>
                            <select class="form-control" id="input_booknetic_forgot_password_page_id">
                                <?php foreach ( get_pages() AS $page ) : ?>
                                    <option value="<?php echo htmlspecialchars($page->ID)?>"<?php echo Helper::getOption('regular_forgot_password_page', '', false) == $page->ID ? ' selected' : ''?>><?php echo htmlspecialchars(empty($page->post_title) ? '-' : $page->post_title)?> (ID: <?php echo $page->ID?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif;?>

                    </div>
			</form>

		</div>
	</div>
</div>