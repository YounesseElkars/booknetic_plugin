(function ($)
{
	"use strict";

	$(document).ready(function()
	{
		function RGBtoHex(r,g,b)
		{
			var hexR = Number(r).toString(16);
			if (hexR.length < 2)
				hexR = "0" + hexR;

			var hexG = Number(g).toString(16);
			if (hexG.length < 2)
				hexG = "0" + hexG;

			var hexB = Number(b).toString(16);
			if (hexB.length < 2)
				hexB = "0" + hexB;

			return '#' + hexR + hexG + hexB;
		}

		function hslToHex(h, s, l)
		{
			var r, g, b;

			if(s == 0)
			{
				r = g = b = l;
			}
			else
			{
				var hue2rgb = function hue2rgb(p, q, t)
				{
					if(t < 0) t += 1;
					if(t > 1) t -= 1;
					if(t < 1/6) return p + (q - p) * 6 * t;
					if(t < 1/2) return q;
					if(t < 2/3) return p + (q - p) * (2/3 - t) * 6;
					return p;
				}

				var q = l < 0.5 ? l * (1 + s) : l + s - l * s;
				var p = 2 * l - q;
				r = hue2rgb(p, q, h + 1/3);
				g = hue2rgb(p, q, h);
				b = hue2rgb(p, q, h - 1/3);
			}

			return RGBtoHex( Math.round(r * 255), Math.round(g * 255), Math.round(b * 255) );
		}

		function colorSelected( parent1, color )
		{
			$( parent1 ).css('background-color', hslToHex(color._hsla[0] , color._hsla[1] , color._hsla[2]));
			var _for = $(parent1).data('for'),
				toEl = $( '#booknetic_panel_area [data-change-for="'+_for+'"]' );

			toEl.each(function ()
			{
				if( color._hsla )
				{
					var _type = $(this).data('type');

					if( _type === 'after')
					{
						$(this).addClass('afterBG');
						document.querySelector('style:not(#operaUserStyle)').sheet.deleteRule(0);
						document.querySelector('style:not(#operaUserStyle)').sheet.insertRule('.afterBG::after{background-color:'+ hslToHex(color._hsla[0] , color._hsla[1] , color._hsla[2]) +' !important}',0);
					}
					else
					{
						$(this).attr( 'style', _type+': ' + hslToHex(color._hsla[0] , color._hsla[1] , color._hsla[2]) + ' !important;' );
					}
				}
			});
		}

		function show_hide_steps() {
			const hide_steps = $('#hide_steps').is(':checked') ? 1 : 0;

			const appointment_container = $( '.booknetic_appointment_container' );
			const appointment_steps = $( '.booknetic_appointment_steps' );
			const appointment = $( '.booknetic_appointment' );

			if ( hide_steps )
			{
				appointment_container.attr( 'style', 'width: 100% !important' );
				appointment_steps.removeClass( 'd-block' );
				appointment_steps.addClass( 'hidden-important' );
				appointment.css( 'min-width', '600px' );
				appointment.css( 'width', '765px' );
			}
			else
			{
				appointment_container.attr( 'style', '' );
				appointment_steps.removeClass( 'hidden-important' );
				appointment_steps.addClass( 'd-block' );
				appointment.css( 'min-width', '980px' );
				appointment.css( 'width', '980px' );
			}
		}

		function apply_custom_css() {
			const style = $( '#custom_css_style' );

			if ( style.length === 0 )
			{
				const style = $('<style>');
				style.attr( 'id', 'custom_css_style' );

				style.insertBefore( '#custom_css' );
			}

			style.text( $( '#custom_css' ).val() );
		}

		$('.colorpicker01').each(function ()
		{
			$(this).css('background-color', $(this).data('color'));

			(function ( parent1 )
			{

				var picker = new Picker( {
					popup: booknetic.isMobileVer() ? 'left' : 'right',
					parent: parent1,
					onChange: function ( color )
					{
						colorSelected( parent1, color );
					},
					onOpen: function()
					{
						document.body.style.overflow = 'hidden';
						//todo: deprecated, after removing niceScroll. Removed at 3.4.2
						// $(".fs_portlet_content").getNiceScroll().remove();
						$(".fs_portlet_content").handleScrollBooknetic();
						document.querySelector('.fs_portlet_content').style.overflow = 'hidden';

						var width = $(parent1).children('.picker_wrapper').innerWidth();
						var height = $(parent1).children('.picker_wrapper').innerHeight();
						var scrollX = window.scrollX;
						var scrollY = window.scrollY;
						var top = $(parent1).offset().top - scrollY;
						var left = booknetic.isMobileVer() ? ( $(parent1).offset().left - width - 50 ) : ( $(parent1).offset().left + 50 - scrollX );

						if( top + height + scrollY > $(document).outerHeight() )
						{
							$(parent1).children('.picker_wrapper').css({
								top: top - height/2,
								left: left,
								position: 'fixed'
							});

							if ( ! booknetic.isMobileVer() )
							{
								$(parent1).find('.picker_wrapper > .picker_arrow').css('top', height/2+'px');
							}
						}
						else
						{
							$(parent1).children('.picker_wrapper').css({
								top: top,
								left: left,
								position: 'fixed'
							});

							if ( ! booknetic.isMobileVer() )
							{
								$(parent1).find('.picker_wrapper > .picker_arrow').css('top', 0);
							}
						}
					},
					onClose: function()
					{
						document.body.style.removeProperty('overflow')
						//todo: deprecated, after removing niceScroll. Removed at 3.4.2
						// $(".fs_portlet_content").niceScroll({cursorcolor: "#e4ebf4"});
						$(".fs_portlet_content").handleScrollBooknetic();

					},
					alpha: false,
					color: $(parent1).css('background-color')
				});

			})( $(this)[0] );

		}).trigger('change');


		$(document).on('click', '#save_btn', function ()
		{
			var name        = $('#input_name').val();
			var height      = $('#input_height').val();
			var fontfamily  = $('#input_fontfamily').val();
			var custom_css  = $('#custom_css').val();
			var colors      = {};
			var hide_steps = $('#hide_steps').is(':checked') ? 1 : 0;

			$(".colorpicker01[data-for]").each(function ()
			{
				var rgb = $(this).css('background-color').replace(/[^0-9\,]/g, '').split(',');
				colors[ $(this).data('for') ] = RGBtoHex( rgb[0], rgb[1], rgb[2] );
			});

			booknetic.ajax('save', {
				id: $('#appearance-script').data('id'),
				name: name,
				custom_css: custom_css,
				colors: JSON.stringify(colors),
				height: height,
				fontfamily: fontfamily,
				hide_steps: hide_steps
			}, function ( result )
			{
				location.href = '?page=' + BACKEND_SLUG + '&module=appearance';
			});
		}).on('click', '#delete_btn', function ()
		{
			booknetic.confirm(booknetic.__('are_you_sure'), 'danger', 'trash', function ()
			{
				booknetic.ajax('delete', { id: $('#appearance-script').data('id') }, function ( result )
				{
					location.href = '?page=' + BACKEND_SLUG + '&module=appearance';
				});
			});
		}).on('click', '#go_back_btn', function ()
		{
			location.href = '?page=' + BACKEND_SLUG + '&module=appearance';
		}).on('change', '#input_height', function ()
		{
			var height = parseInt($(this).val());
			height = isNaN(height) ? 600 : height;

			$('#booknetic_panel_area .booknetic_appointment').css({'height': height + 'px'});
			$(window).trigger('resize')

		});

		$( '#hide_steps' ).change( show_hide_steps );

		$( document ).on( 'keyup', '#custom_css', function() {
			apply_custom_css();
		});

		$('#input_height').trigger('change');

		$(window).resize(function ()
		{
			var t = $('.booknetic_appointment');

			var width = t.innerWidth();
			var parentWidth = $('#booknetic_panel_area').innerWidth();

			var scale = parseInt(parentWidth / width * 100) / 100 - 0.14;
			scale = scale > 0.86 ? 0.86 : scale;
			var height = t.innerHeight() * scale + 100;

			$('.booknetic_appointment').css('transform', 'scale(' + scale + ')');
			$('#booknetic_panel_area').css({'height': height + 'px', opacity: 1});

			//todo: deprecated, after removing niceScroll. Removed at 3.4.2
			// $(".fs_portlet_content").getNiceScroll().resize();

		}).trigger('resize');

		//todo: deprecated, after removing niceScroll. Removed at 3.4.2
		// $(".fs_portlet_content").niceScroll({cursorcolor: "#e4ebf4"});
		$(".fs_portlet_content").handleScrollBooknetic();

		show_hide_steps();
		apply_custom_css();
	});

})(jQuery);
