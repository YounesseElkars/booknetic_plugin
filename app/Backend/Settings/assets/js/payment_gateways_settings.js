(function ($)
{
	"use strict";

	$(document).ready(function ()
	{
		var wocommerceOrderDetailsInput = $( '#input_woocommerce_order_details' );
		if ( wocommerceOrderDetailsInput )
		{
			wocommerceOrderDetailsInput.attr( 'data-multilang', true );
			booknetic.initMultilangInput( wocommerceOrderDetailsInput, 'options', 'woocommerce_order_details' );
		}

		if (  $( '.bkntc_enable_payment_gateway:checked' ).length <= 0 )
		{
			$( '#enable_gateway_local' ).prop( 'checked', true );
		}

		$('#booknetic_settings_area').on('click', '.settings-save-btn', function()
		{
			var gateway_enable_checkboxes = $('.bkntc_enable_payment_gateway');
			var	payment_gateways_order	        = [];

			$('.step_elements_list > .step_element').each(function()
			{
				payment_gateways_order.push( $(this).data('step-id') );
			});

			const data = new FormData()

			gateway_enable_checkboxes.each(function( i, checkbox )
			{
				const key = $(checkbox).attr('data-slug');

				data.append(`gateways_statuses[${key}]`, $(checkbox).is(':checked') ? 'on' : 'off')

				const label = $(`#input_${key}_label`).val()

				data.append(`labels[${key}]`, label)

				const resetIcon = $(`#${key}_reset_icon`).val()

				if ( resetIcon === '1' ) {
					data.append(`icon_resets[${key}]`, 1)
				} else {
					const icon = $(`[data-step="${key}"] .payment-method-settings-icon-input`)[0].files[0]

					data.append(`icons[${key}]`, icon)
				}
			});

			data.append('payment_gateways_order', JSON.stringify( payment_gateways_order ))
			data.append('translations', booknetic.getTranslationData( $( '#booking_panel_settings_per_step' ) ))

			booknetic.ajax('settings.save_payment_gateways_settings', data, function ()
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
		}).on( 'change', '.bkntc_enable_payment_gateway', function ()
		{
			if ( $( '.bkntc_enable_payment_gateway:checked' ).length <= 0 )
			{
				$( this ).prop('checked', true);
			}
		}).on( 'click', '.payment-method-settings-icon-upload', function ()
		{
			const step = $(this).closest('[data-step]').data('step')

			$(`[data-step="${step}"] .payment-method-settings-icon-input`).click();
		}).on( 'change', '.payment-method-settings-icon-input', function ()
		{
			const _t  = $(this)

			if( _t[0].files && _t[0].files[0] )
			{
				const reader = new FileReader();

				reader.onload = function(e)
				{
					const step = _t.closest('[data-step]').data('step')

					$(`[data-step="${step}"] .payment-method-settings-icon-image`).attr('src', e.target.result);

					$(`#${step}_reset_icon`).val(undefined)
					$(`#${step}_reset_button`).show();
				}

				reader.readAsDataURL( _t[0].files[0] );
			}
		}).on('click', '.reset-to-default', function ()
		{
			const step = $(this).closest('[data-step]').data('step')
			const icon = $(`[data-step="${step}"] .payment-method-settings-icon-image`)

			icon.attr('src', icon.data('default-icon') );
			$(`#${step}_reset_icon`).val('1')
			$(`#${step}_reset_button`).hide();
		});

		$( '.step_elements_list' ).sortable({
			placeholder: "step_element selected_step",
			axis: 'y',
			handle: ".drag_drop_helper"
		});

		$('.reset-to-default').each(function (i, elm) {
			const img = $(elm).parent().parent().find('img')

			if (img.attr('src') === img.attr('data-default-icon')) {
				$(elm).hide()
			} else {
				$(elm).show()
			}
		})

		$('.step_elements_list > .step_element:eq(0)').trigger('click');

		$('table.form-table').find('input, select, textarea').addClass('form-control');

		$( '#booknetic_settings_area' ).on( 'change', '.step_switch .fs_onoffswitch-checkbox', function () {
			$( '[data-step]' ).addClass( 'disable_editing' );

			$( '.fs_onoffswitch-checkbox' ).each( function () {
				if ( $( this ).is( ':checked' ) )
				{
					let slug = $( this ).attr( 'data-slug' );

					$( '[data-step="' + slug + '"]' ).removeClass( 'disable_editing' );
				}
			} );
		} );

		$('[data-step]').each( function (i, elm) {
			const step = $(elm).data('step')

			booknetic.initMultilangInput( $( `#input_${step}_label` ), 'options', `${step}_label` );
		});

		$( '.step_switch .fs_onoffswitch-checkbox' ).trigger( 'change' );
	});

})(jQuery);