(function ( $ )
{
	"use strict";

	var warningTimer;

	function warning( text, style )
	{
		style = typeof style === 'undefined' ? 'warning' : style;

		$('#booknetic_alert')
			.removeClass('booknetic_alert_warning')
			.removeClass('booknetic_alert_success')
			.addClass('booknetic_alert_' + style)
			.text( text )
			.removeClass('hidden')
			.hide()
			.fadeIn(200);

		if(warningTimer)
		{
			clearTimeout(warningTimer);
			warningTimer = null;
		}

		warningTimer = setTimeout(function ()
		{
			$('#booknetic_alert').fadeOut(200);
		}, 3000);
	}

	function loading( s )
	{
		if( s === false )
		{
			$('#booknetic_loading').fadeOut(200);
		}
		else
		{
			$('#booknetic_loading').removeClass('hidden').hide().fadeIn(200);
		}
	}

	$(document).ready(function ()
	{

		$(document).on('click', '#bookneticReactivateBtn', function ()
		{
			var purchase_code	= $('#bookneticPurchaseKey').val();

			if( purchase_code.trim() === '' )
			{
				$('#bookneticPurchaseKey').attr('style', 'border: 1px solid #ffa3a8 !important');
				return false;
			}

			$('#bookneticPurchaseKey').removeAttr('style');

			loading();

			$.post(ajaxurl, {
				action: 'booknetic_reactivate_plugin',
				code: purchase_code
			}, function ( result )
			{
				loading(false);

				result = JSON.parse(result);

				if( 'status' in result && result['status'] === 'ok' )
				{
					warning('Done!', 'success');
					location.reload();
				}
				else
				{
					warning( 'error_msg' in result ? result['error_msg'] : 'Error!' );
				}

			});

		}).on('click', '#booknetic_alert', function ()
		{
			if(warningTimer)
			{
				clearTimeout(warningTimer);
				warningTimer = null;
			}

			$('#booknetic_alert').fadeOut(200);
		});

	});

})( jQuery );