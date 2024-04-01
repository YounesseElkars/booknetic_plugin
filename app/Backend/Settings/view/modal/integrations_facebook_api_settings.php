<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Integrations\LoginButtons\FacebookLogin;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Helpers\Date;

?>
<div id="booknetic_settings_area">
	<link rel="stylesheet" href="<?php echo Helper::assets('css/integrations_facebook_api_settings.css', 'Settings')?>">
	<script type="application/javascript" src="<?php echo Helper::assets('js/integrations_facebook_api_settings.js', 'Settings')?>"></script>

	<div class="actions_panel clearfix">
		<button type="button" class="btn btn-lg btn-success settings-save-btn float-right"><i class="fa fa-check pr-2"></i> <?php echo bkntc__('SAVE CHANGES')?></button>
	</div>

	<div class="settings-light-portlet">
		<div class="ms-title">
			<?php echo bkntc__('Facebook API / Continue with Facebook button')?>
		</div>
		<div class="ms-content">

			<form class="position-relative">

				<div class="form-row enable_disable_row">

					<div class="form-group col-md-2">
						<input id="input_facebook_login_enable" type="radio" name="input_facebook_login_enable" value="off"<?php echo Helper::getOption('facebook_login_enable', 'off')=='off'?' checked':''?>>
						<label for="input_facebook_login_enable"><?php echo bkntc__('Disabled')?></label>
					</div>
					<div class="form-group col-md-2">
						<input id="input_facebook_login_disable" type="radio" name="input_facebook_login_enable" value="on"<?php echo Helper::getOption('facebook_login_enable', 'off')=='on'?' checked':''?>>
						<label for="input_facebook_login_disable"><?php echo bkntc__('Enabled')?></label>
					</div>

				</div>

				<div id="integrations_facebook_api_settings_area">

					<div class="form-row">
						<div class="form-group col-md-12">
							<label for="input_google_calendar_redirect_uri"><?php echo bkntc__('Redirect URI')?>:</label>
							<input class="form-control" id="input_google_calendar_redirect_uri" value="<?php echo FacebookLogin::callbackURL() ?>" readonly>
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-md-6">
							<label for="input_facebook_app_id"><?php echo bkntc__('App ID')?>: <span class="required-star">*</span></label>
							<input class="form-control" id="input_facebook_app_id" value="<?php echo htmlspecialchars( Helper::getOption('facebook_app_id', '') )?>">
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-md-6">
							<label for="input_facebook_app_secret"><?php echo bkntc__('App Secret')?>: <span class="required-star">*</span></label>
							<input class="form-control" id="input_facebook_app_secret" value="<?php echo htmlspecialchars( Helper::getOption('facebook_app_secret', '') )?>">
						</div>
					</div>



				</div>

			</form>

		</div>
	</div>
</div>