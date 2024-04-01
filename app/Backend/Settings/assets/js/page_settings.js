(function ($)
{
	"use strict";

	$(document).ready(function ()
	{
		$('#booknetic_settings_area').on('click', '.settings-save-btn', function()
		{
			var change_status_page_id								= $("#input_change_status_page_id").select2('val'),
				time_restriction_to_change_status					= $("#input_time_restriction_to_change_appointment_status").select2('val'),
				restriction_type_to_change_appointment_status		= $("#input_restriction_type_to_change_appointment_status").select2('val'),
				booknetic_signin_page_id							= $("#input_booknetic_signin_page_id").select2('val'),
				booknetic_signup_page_id							= $("#input_booknetic_signup_page_id").select2('val'),
				booknetic_forgot_password_page_id					= $("#input_booknetic_forgot_password_page_id").select2('val');


			booknetic.ajax('save_page_settings', {
				change_status_page_id: change_status_page_id,
				time_restriction_to_change_status: time_restriction_to_change_status,
				restriction_type_to_change_appointment_status: restriction_type_to_change_appointment_status,
				booknetic_signin_page_id: booknetic_signin_page_id,
				booknetic_signup_page_id: booknetic_signup_page_id,
				booknetic_forgot_password_page_id: booknetic_forgot_password_page_id,
			}, function ()
			{
				booknetic.toast(booknetic.__('saved_successfully'), 'success');
			});

		});

		$("#input_change_status_page_id, #input_time_restriction_to_change_appointment_status, #input_restriction_type_to_change_appointment_status, #input_booknetic_signin_page_id, #input_booknetic_signup_page_id, #input_booknetic_forgot_password_page_id").select2({
			theme: 'bootstrap',
			placeholder: booknetic.__('select')
		});

	});

})(jQuery);