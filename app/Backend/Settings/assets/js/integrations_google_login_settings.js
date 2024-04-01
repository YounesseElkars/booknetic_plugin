(function ($)
{
	"use strict";

	$(document).ready(function()
	{
		var fadeSpeed = 0;

		$('#booknetic_settings_area').on('click', '.settings-save-btn', function ()
		{
			var google_login_app_id		    = $("#input_google_login_app_id").val(),
				google_login_app_secret		= $("#input_google_login_app_secret").val(),
				google_login_enable		    = $('input[name="input_google_login_enable"]:checked').val();

			booknetic.ajax('save_integrations_google_login_settings', {
				google_login_app_id: google_login_app_id,
				google_login_app_secret: google_login_app_secret,
				google_login_enable: google_login_enable
			}, function ()
			{
				booknetic.toast(booknetic.__('saved_successfully'), 'success');
			});
		}).on('change', 'input[name="input_google_login_enable"]', function()
		{
			if( $('input[name="input_google_login_enable"]:checked').val() == 'on' )
			{
				$('#integrations_google_login_settings_area').slideDown(fadeSpeed);
			}
			else
			{
				$('#integrations_google_login_settings_area').slideUp(fadeSpeed);
			}
			fadeSpeed = 400;
		});

		$('input[name="input_google_login_enable"]').trigger('change');

	});

})(jQuery);