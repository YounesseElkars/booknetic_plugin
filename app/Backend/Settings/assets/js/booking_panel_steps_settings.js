(function ($)
{
	"use strict";

	$(document).ready(function ()
	{
		booknetic.initMultilangInput( $( "#input_redirect_url_after_booking" ), 'options', 'redirect_url_after_booking' );

		$('#booknetic_settings_area').on('click', '.settings-save-btn', function()
		{
			var hide_address_of_location			= $("#input_hide_address_of_location").is(':checked')?'on':'off',
				set_email_as_required				= $("#input_set_email_as_required").is(':checked')?'on':'off',
				set_phone_as_required				= $("#input_set_phone_as_required").is(':checked')?'on':'off',
				separate_first_and_last_name		= $("#input_separate_first_and_last_name").is(':checked')?'on':'off',
				footer_text_staff					= $("#input_footer_text_staff").val(),
				any_staff							= $("#input_any_staff").is(':checked') ? 'on' : 'off',
				any_staff_rule						= $("#input_any_staff_rule").val(),
				skip_extras_step_if_need			= $("#input_skip_extras_step_if_need").is(':checked') ? 'on' : 'off',
				hide_gift_discount_row				= $("#input_hide_gift_discount_row").is(':checked') ? 'on' : 'off',
				hide_add_to_google_calendar_btn		= $("#input_hide_add_to_google_calendar_btn").is(':checked') ? 'on' : 'off',
				hide_add_to_icalendar_btn			= $("#input_hide_add_to_icalendar_btn").is(':checked') ? 'on' : 'off',
				hide_start_new_booking_btn			= $("#input_hide_start_new_booking_btn").is(':checked') ? 'on' : 'off',
				redirect_url_after_booking			= $("#input_redirect_url_after_booking").val(),
				time_view_type_in_front				= $("#input_time_view_type_in_front").val(),
				booking_panel_default_start_month	= $("#input_booking_panel_default_start_month").val(),
				hide_available_slots				= $("#input_hide_available_slots").is(':checked')?'on':'off',
				hide_accordion_default				= $("#input_hide_accordion_default").is(':checked')?'on':'off',
				collapse_service_extras				= $("#input_collapse_service_extras").is(':checked')?'on':'off',
				show_all_service_extras				= $("#input_show_all_service_extras").is(':checked')?'on':'off',

				show_step_location					= $('#show_step_location').is(':checked')?'on':'off',
				show_step_staff						= $('#show_step_staff').is(':checked')?'on':'off',
				show_step_service					= $('#show_step_service').is(':checked')?'on':'off',
				show_step_service_extras			= $('#show_step_service_extras').is(':checked')?'on':'off',
				show_step_information				= $('#show_step_information').is(':checked')?'on':'off',
				show_step_cart						= $('#show_step_cart').is(':checked')?'on':'off',
				show_step_confirm_details			= $('#show_step_confirm_details').is(':checked')?'on':'off',
				hide_confirmation_number			= $('#input_hide_confirmation_number').is(':checked')?'on':'off',
				confirmation_number					= $('#input_confirmation_number').val(),
				redirect_users_on_confirm_url = $( '#redirect_users_on_confirm_url' ).val(),

				steps								= [],
				confirm_details_checkbox			= {};


			$('.step_elements_list > .step_element:not(.no_drag_drop)').each(function()
			{
				steps.push( $(this).data('step-id') );
			});

			$('.step_elements_options [data-step="confirm_details"] .bkntc_confirm_details_checkbox').each( function ( i , checkbox ) {
				let key = $(checkbox).attr('data-slug');
				confirm_details_checkbox[key] = $(checkbox).is(':checked') ? 'on' : 'off';
			});

			booknetic.ajax('save_booking_steps_settings', {
				hide_address_of_location: hide_address_of_location,
				set_email_as_required: set_email_as_required,
				set_phone_as_required: set_phone_as_required,
				footer_text_staff: footer_text_staff,
				any_staff: any_staff,
				any_staff_rule: any_staff_rule,
				skip_extras_step_if_need: skip_extras_step_if_need,
				time_view_type_in_front: time_view_type_in_front,
				booking_panel_default_start_month: booking_panel_default_start_month,
				hide_available_slots: hide_available_slots,
				hide_accordion_default: hide_accordion_default,
				collapse_service_extras: collapse_service_extras,
				show_all_service_extras: show_all_service_extras,
				redirect_url_after_booking: redirect_url_after_booking,
				separate_first_and_last_name: separate_first_and_last_name,
				confirm_details_checkbox: JSON.stringify(confirm_details_checkbox),
				hide_gift_discount_row: hide_gift_discount_row,
				hide_add_to_google_calendar_btn: hide_add_to_google_calendar_btn,
				hide_add_to_icalendar_btn: hide_add_to_icalendar_btn,
				hide_start_new_booking_btn: hide_start_new_booking_btn,
				default_phone_country_code: phone_input.data('iti').getSelectedCountryData().iso2,

				show_step_location: show_step_location,
				show_step_staff: show_step_staff,
				show_step_service: show_step_service,
				show_step_service_extras: show_step_service_extras,
				show_step_information: show_step_information,
				show_step_cart: show_step_cart,
				show_step_confirm_details: show_step_confirm_details,
				hide_confirmation_number: hide_confirmation_number,
				confirmation_number: confirmation_number,
				translations: booknetic.getTranslationData( $( '#booknetic_settings_area' ) ),
				redirect_users_on_confirm_url,

				steps: JSON.stringify(steps)
			}, function ()
			{
				booknetic.toast(booknetic.__('saved_successfully'), 'success');
			});

		}).on('click', '.step_element:not(.selected_step)', function ()
		{
			$('.step_elements_list > .selected_step .drag_drop_helper > img').attr('src', assetsUrl + 'icons/drag-default.svg');

			$('.step_elements_list > .selected_step').removeClass('selected_step');
			$(this).addClass('selected_step');

			$(this).find('.drag_drop_helper > img').attr('src', assetsUrl + 'icons/drag-color.svg')

			var step_id = $(this).data('step-id');

			$('#booking_panel_settings_per_step > [data-step]').hide();
			$('#booking_panel_settings_per_step > [data-step="'+step_id+'"]').removeClass('hidden').show();
		}).on('change', '#input_any_staff', function ()
		{
			if( !$(this).is(':checked') )
			{
				$('#any_staff_selecting_rule').slideUp(animationSpeed);
			}
			else
			{
				$('#any_staff_selecting_rule').slideDown(animationSpeed);
			}
		});

		$( '.step_elements_list' ).sortable({
			items: '.step_element:not(.no_drag_drop)',
			placeholder: "step_element selected_step",
			axis: 'y',
			handle: ".drag_drop_helper"
		});

		$('.step_elements_list > .step_element:not(.hidden):eq(0)').trigger('click');

		var phone_input = $('#input_default_phone_country_code');

		phone_input.data('iti', window.intlTelInput( phone_input[0], {
			separateDialCode: true,
			initialCountry: phone_input.data('country-code')
		}));

		var animationSpeed = 0;
		$('#input_any_staff').trigger('change');
		animationSpeed = 200;


	});

})(jQuery);