(function ($)
{
	"use strict";

	$(document).ready(function()
	{
		$('#booknetic_settings_area').on('click', '.settings-save-btn', function ()
		{
			var currency							= $("#input_currency").val(),
				currency_symbol						= $("#input_currency_symbol").val(),
				currency_format						= $("#input_currency_format").val(),
				price_number_format					= $("#input_price_number_format").val(),
				price_number_of_decimals			= $("#input_price_number_of_decimals").val(),
				deposit_can_pay_full_amount			= $("#input_deposit_can_pay_full_amount").is(':checked') ? 'on' : 'off',
				max_time_limit_for_payment			= $("#input_max_time_limit_for_payment").val(),
				successful_payment_status			= $("#input_successful_payment_status").val(),
				failed_payment_status				= $("#input_failed_payment_status").val();

			var ajaxData = {
				currency: currency,
				currency_symbol: currency_symbol,
				currency_format: currency_format,
				price_number_format: price_number_format,
				price_number_of_decimals: price_number_of_decimals,
				deposit_can_pay_full_amount: deposit_can_pay_full_amount,
				max_time_limit_for_payment: max_time_limit_for_payment,
				successful_payment_status,
				failed_payment_status
			};
			ajaxData = booknetic.doFilter('payment_settings.save_payments_settings', ajaxData);
			booknetic.ajax('save_payments_settings', ajaxData , function ()
			{
				booknetic.toast(booknetic.__('saved_successfully'), 'success');
			});
		}).on('change', '#input_currency', function ()
		{
			var symbol = $(this).children(':selected').data('symbol');
			$('#input_currency_symbol').val( symbol );
		});

		$("#input_currency, #input_currency_format, #input_price_number_format, #input_price_number_of_decimals, #input_max_time_limit_for_payment").select2({
			theme: 'bootstrap',
			placeholder: booknetic.__('select'),
			allowClear: true
		});

		$('#input_successful_payment_status, #input_failed_payment_status').select2({
			theme: 'bootstrap',
			placeholder: booknetic.__('select'),
			allowClear: true
		});
	});

})(jQuery);