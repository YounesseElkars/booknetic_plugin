(function ($)
{
	"use strict";

	$(document).ready(function()
	{
		var fadeSpeed = 0;

		$('#booknetic_settings_area').on('click', '.settings-save-btn', function ()
		{
			var facebook_app_id		        = $("#input_facebook_app_id").val(),
				facebook_app_secret		    = $("#input_facebook_app_secret").val(),
				facebook_login_enable		= $('input[name="input_facebook_login_enable"]:checked').val();

			booknetic.ajax('save_integrations_facebook_api_settings', {
				facebook_app_id: facebook_app_id,
				facebook_app_secret: facebook_app_secret,
				facebook_login_enable: facebook_login_enable
			}, function ()
			{
				booknetic.toast(booknetic.__('saved_successfully'), 'success');
			});
		}).on('change', 'input[name="input_facebook_login_enable"]', function()
		{
			if( $('input[name="input_facebook_login_enable"]:checked').val() == 'on' )
			{
				$('#integrations_facebook_api_settings_area').slideDown(fadeSpeed);
			}
			else
			{
				$('#integrations_facebook_api_settings_area').slideUp(fadeSpeed);
			}
			fadeSpeed = 400;
		});

		$('input[name="input_facebook_login_enable"]').trigger('change');

	});

})(jQuery);