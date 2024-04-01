(function ($)
{
	"use strict";

	function __( key )
	{
		return key in localization ? localization[ key ] : key;
	}

	$('body').on('click' , '.booknetic-cart-item-more' ,function (){
		$(".booknetic-cart-item-btns").toggleClass("show");
	});

	function displayCalendar()
	{
		var localization = {
			month_names: [ __('January'), __('February'), __('March'), __('April'), __('May'), __('June'), __('July'), __('August'), __('September'), __('October'), __('November'), __('December') ],
			day_of_week: [ 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun' ] ,
		};

		var currentTime = new Date()
		var _year = currentTime.getFullYear();
		var _month = currentTime.getMonth() + 1;

		var htmlContent		= "",
			febNumberOfDays	= "",
			counter			= 1,
			dateNow			= new Date(_year , _month ),
			month			= dateNow.getMonth()+1,
			year			= dateNow.getFullYear(),
			currentDate		= new Date();

		if (month == 2)
		{
			febNumberOfDays = ( (year%100!=0) && (year%4==0) || (year%400==0)) ? '29' : '28';
		}

		var monthNames	= localization.month_names;
		var dayPerMonth	= [null, '31', febNumberOfDays ,'31','30','31','30','31','31','30','31','30','31']

		var nextDate	= new Date(month +'/01/'+year);
		var weekdays	= nextDate.getDay();
		if( weekStartsOn === 'monday' )
		{
			var weekdays2	= weekdays == 0 ? 7 : weekdays;
			var week_start_n = 1;
			var week_end_n = 7;
		}
		else
		{
			var weekdays2	= weekdays;
			var week_start_n = 0;
			var week_end_n = 6;
		}

		var numOfDays	= dayPerMonth[month];

		for( var w=week_start_n; w < weekdays2; w++ )
		{
			htmlContent += "<div class=\"booknetic_td booknetic_empty_day\"></div>";
		}

		while (counter <= numOfDays)
		{
			if (weekdays2 > week_end_n)
			{
				weekdays2 = week_start_n;
				htmlContent += "</div><div class=\"booknetic_calendar_rows\">";
			}
			var date_formatted = year + '-' + booknetic.zeroPad(month) + '-' + booknetic.zeroPad(counter);

			if( dateFormat === 'Y-m-d' )
			{
				var date_format_view = year + '-' + booknetic.zeroPad(month) + '-' + booknetic.zeroPad(counter);
			}
			else if( dateFormat === 'd-m-Y' )
			{
				var date_format_view = booknetic.zeroPad(counter) + '-' + booknetic.zeroPad(month) + '-' + year;
			}
			else if( dateFormat === 'm/d/Y' )
			{
				var date_format_view = booknetic.zeroPad(month) + '/' + booknetic.zeroPad(counter) + '/' + year;
			}
			else if( dateFormat === 'd/m/Y' )
			{
				var date_format_view = booknetic.zeroPad(counter) + '/' + booknetic.zeroPad(month) + '/' + year;
			}

			var addClass = '';
			htmlContent +="<div class=\"booknetic_td booknetic_calendar_days"+addClass+"\" data-date=\"" + date_formatted + "\" data-date-format=\"" + date_format_view + "\"><div>"+counter+"<span></span></div></div>";

			weekdays2++;
			counter++;
		}

		for( var w=weekdays2; w <= week_end_n; w++ )
		{
			htmlContent += "<div class=\"booknetic_td booknetic_empty_day\"></div>";
		}

		var calendarBody = "<div class=\"booknetic_calendar\">";

		calendarBody += "<div class=\"booknetic_calendar_rows booknetic_week_names\">";

		for( var w in localization.day_of_week )
		{
			if( w > week_end_n || w < week_start_n )
				continue;

			calendarBody += "<div class=\"booknetic_td\"><span data-translate=\""+localization.day_of_week[ w ]+"\">" + __(localization.day_of_week[ w ]) + "</span></div>";
		}

		calendarBody += "</div>";

		calendarBody += "<div class=\"booknetic_calendar_rows\">";
		calendarBody += htmlContent;
		calendarBody += "</div></div>";

		$("#booknetic_calendar_area").html( calendarBody );

		$("#booknetic_calendar_area .days[data-count]:first").trigger('click');

		$(".booknetic_month_name").text( monthNames[ _month ] + ' ' + _year );
	}

	$(document).ready(function ()
	{
		let is_in_edit_mode = false;

		$('#booknetic_settings_area').on('click', '.settings-save-btn', function()
		{
			var language = $('#language_to_translate').val();
			var translates = {};

			if( !language || language == '' )
			{
				$('#language_to_translate').addClass('input-error');
				return;
			}

			$('#booknetic_panel_area [data-translate]').each(function ()
			{
				translates[ $(this).data('translate') ] = $(this).text().trim();
			});

			$('#booknetic_panel_area input[data-translate-key]').each(function ()
			{
				translates[ $(this).data('translate-key') ] = $(this).val();
			});

			booknetic.ajax('save_booking_labels_settings', {
				language: language,
				translates: JSON.stringify(translates)
			}, function ()
			{
				booknetic.toast(booknetic.__('saved_successfully'), 'success');
			});

		}).on('mouseenter', '[data-translate]', function ( )
		{
			if( is_in_edit_mode )
				return;

			var left = $(this)[0].getBoundingClientRect().x + $(this).outerWidth();
			var top = $(this)[0].getBoundingClientRect().y + $(this).outerHeight() - 18;
			if ($(window).innerWidth() <= 1440)
			{
				$('#translate_edit_icon').attr("style","").css({position:'absolute'}).show().appendTo($(this));
			}
			else
			{
				$('#translate_edit_icon').attr("style","").css({top: top+'px', left: left+'px'}).show().appendTo($(this));
			}

		}).on('mouseleave', '[data-translate]', function ()
		{
			if( is_in_edit_mode )
				return;

			$('#translate_edit_icon').hide().appendTo($('.label_settings_container'));
		}).on('click', '#translate_edit_icon', function ()
		{
			is_in_edit_mode = true;

			var parent = $(this).parent();
			$(this).hide().appendTo($('.label_settings_container'));

			parent.addClass('in_edit_mode').attr('contenteditable', true).focus();
			document.execCommand('selectAll', false, null);

			var left = parent.offset().left + parent.outerWidth();
			var top = parent.offset().top + parent.outerHeight() - 19;

			$('#translate_save_icon').css({top: top+'px', left: (left+10)+'px'}).show();
			$('#translate_cancel_icon').data('old_text', parent.text().trim()).css({top: top+'px', left: (left+40)+'px'}).show();
		}).on('keyup', '.in_edit_mode', function ()
		{
			var left = $(this).offset().left + $(this).outerWidth();
			var top = $(this).offset().top + $(this).outerHeight() - 19;

			$('#translate_save_icon').css({top: top+'px', left: (left+10)+'px'});
			$('#translate_cancel_icon').css({top: top+'px', left: (left+40)+'px'});
		}).on('click', '#translate_cancel_icon', function ()
		{
			var old_text = $(this).data('old_text');

			$('.in_edit_mode').text( old_text ).removeClass('in_edit_mode').removeAttr('contenteditable');

			$('#translate_save_icon').hide();
			$('#translate_cancel_icon').hide();

			is_in_edit_mode = false;
		}).on('click', '#translate_save_icon', function ()
		{
			var translation_key = $('.in_edit_mode').data('translate');
			var new_string = $('.in_edit_mode').text().trim();

			$('#booknetic_panel_area [data-translate="' + (translation_key.replace(/([ #;&,.+*~\':"!^$[\]()=>|\/@])/g,'\\$1')) + '"]').text( new_string );
			$('#booknetic_panel_area [data-translate-key="' + (translation_key.replace(/([ #;&,.+*~\':"!^$[\]()=>|\/@])/g,'\\$1')) + '"]').val( new_string );

			$('.in_edit_mode').removeClass('in_edit_mode').removeAttr('contenteditable');

			$('#translate_save_icon').hide();
			$('#translate_cancel_icon').hide();

			is_in_edit_mode = false;
		}).on('click', '.booknetic_appointment_step_element', function ()
		{
			var step_id = $(this).data('step-id');
			$('.booknetic_appointment_steps_body > .booknetic_active_step').removeClass('booknetic_active_step');

			$(this).addClass('booknetic_active_step');

			$('.booknetic_appointment_container_header').hide();
			$('.booknetic_appointment_container_header[data-step-id="'+step_id+'"]').removeClass('hidden').show();

			$('.booknetic_appointment_container_body > div').hide();
			$('.booknetic_appointment_container_body > [data-step-id="'+step_id+'"]').removeClass('hidden').show();

			if( step_id == 'date_time' )
			{
				displayCalendar();
			}
		}).on('click', '#start_transaltion', function ()
		{
			var language = $('#language_to_translate').val();

			if( !language || language == '' )
			{
				$('#language_to_translate').addClass('input-error');
				return;
			}

			$('#language_to_translate').removeClass('input-error');

			var translates = [];

			$('#booknetic_panel_area [data-translate]').each(function ()
			{
				translates.push( $(this).data('translate') );
			});

			$('#booknetic_panel_area input[data-translate-key]').each(function ()
			{
				translates.push( $(this).data('translate-key') );
			});

			booknetic.ajax('get_translation', {language: language, transaltions: JSON.stringify(translates)}, function ( result )
			{
				for( var translate_key in result['translations'] )
				{
					$('#booknetic_panel_area [data-translate="' + (translate_key) + '"]').text( booknetic.htmlspecialchars_decode( result['translations'][ translate_key ] ) );
					$('#booknetic_panel_area [data-translate-key="' + (translate_key) + '"]').val( booknetic.htmlspecialchars_decode( result['translations'][ translate_key ] ) );
				}

				if( $('#booknetic_panel_area').hasClass('hidden') )
				{
					$('#booknetic_panel_area').removeClass('hidden').hide().fadeIn(200);
				}

				$('#start_transaltion').attr('disabled', true);
			});
		}).on('change', '#language_to_translate', function ()
		{
			$('#language_to_translate').removeClass('input-error');
			$('#start_transaltion').removeAttr('disabled');
		}).on('blur', 'input[data-translate-key]', function ()
		{
			var translation_key = $(this).data('translate-key');
			var new_string = $(this).val().trim();

			$('#booknetic_panel_area [data-translate="' + (translation_key.replace(/([ #;&,.+*~\':"!^$[\]()=>|\/@])/g,'\\$1')) + '"]').text( new_string );
			$('#booknetic_panel_area [data-translate-key="' + (translation_key.replace(/([ #;&,.+*~\':"!^$[\]()=>|\/@])/g,'\\$1')) + '"]').val( new_string );
		}).on('click', '#set_default_langugage', function ()
		{
			var language = $('#language_to_translate').val();

			booknetic.ajax('set_default_language', {lng: language}, function ()
			{
				booknetic.toast(booknetic.__('saved_successfully'));
			});
		});

		$('#start_transaltion').click();

		$('.booknetic_appointment_step_element:eq(0)').click();
		$('.booknetic_appointment_step_element').each(function( i )
		{
			$(this).find('.booknetic_badge').text( parseInt(i) + 1 );
		});

	});

})(jQuery);