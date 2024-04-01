(function ($)
{
	"use strict";

	$(document).ready(function()
	{

		$('#booknetic_settings_area').on('click', '.settings-save-btn', function()
		{
			var hasError = [];
			var business_hours = [ ];

			for( var d=1; d <= 7; d++)
			{
				(function()
				{
					var dayOff	= $("#dayy_off_checkbox_"+d).is(':checked') ? 1 : 0,
						start	= dayOff ? '' : $("#input_timesheet_"+d+"_start").val(),
						end		= dayOff ? '' : $("#input_timesheet_"+d+"_end").val(),
						breaks	= [];

					if( !dayOff )
					{
						$(".breaks_area[data-day='" + d + "'] > .break_line").each(function()
						{
							var breakStart	= $(this).find('.break_start').val(),
								breakEnd	= $(this).find('.break_end').val();

							if( breakStart != '' && breakEnd != '' && ( breakStart < breakEnd ) ){
								breaks.push( [ breakStart, breakEnd ] );
							}else{
								hasError.push(booknetic.__('Please fill the breaks correctly!'))
							}
						});
					}

					business_hours.push( {
						'start'		: start,
						'end'		: end,
						'day_off'	: dayOff,
						'breaks'	: breaks
					} );
				})();
			}

			if(hasError.length){
				booknetic.toast(hasError[0],'unsuccess');
			}else{
				booknetic.ajax('save_business_hours_settings', {
					business_hours: JSON.stringify(business_hours)
				}, function ()
				{
					booknetic.toast(booknetic.__('saved_successfully'), 'success');
				});
			}

		}).on('click', '.timesheet_tabs > div', function ()
		{
			var type = $(this).data('type');

			if( $(this).hasClass('selected-tab') )
				return;

			$(".timesheet_tabs > .selected-tab").removeClass('selected-tab');

			$(this).addClass('selected-tab');

			$("#tab_timesheet [data-tstab]").hide();
			$("#tab_timesheet [data-tstab='" + type + "']").fadeIn(200);
		}).on('click', '.copy_time_to_all', function ()
		{
			let startEl = $("#input_timesheet_1_start"),
			    endEl   = $("#input_timesheet_1_end"),
				start	  = startEl.val(),
				startText = startEl.select2('data')[0]['text'],
				end		  = endEl.val(),
				endText   = endEl.select2('data')[0]['text'],
				dayOff	= $("#dayy_off_checkbox_1").is(':checked'),
				breaks	= $(".breaks_area[data-day='1'] .break_line"),
				breakTpl = $(".break_line:eq(-1)")[0].outerHTML;

			for(var i = 2; i <=7; i++)
			{
				let startOption = new Option(startText, start, false, true);
				let endOption = new Option(endText, end, false, true);

				$("#input_timesheet_"+i+"_start").append( startOption ).trigger('change');
				$("#input_timesheet_"+i+"_end").append( endOption ).trigger('change');
				$(".breaks_area[data-day='"+i+"']").html('');

				breaks.each( function ( index ) {
					let breakStartVal 	= $(this).find('.break_start').val();
					let breakStartText 	= $(this).find('.break_start').select2('data')[0]['text'];
					let breakEndVal 	= $(this).find('.break_end').val();
					let breakEndText 	= $(this).find('.break_end').select2('data')[0]['text'];

					$(".breaks_area[data-day='"+i+"']").append( breakTpl );
					booknetic.select2Ajax( $(".breaks_area[data-day='"+i+"']").find('.break_start') , 'get_available_times_all' );
					booknetic.select2Ajax( $(".breaks_area[data-day='"+i+"']").find('.break_end') , 'get_available_times_all' );

					let breakStartOption = new Option(breakStartText, breakStartVal, false, true);
					let breakEndOption = new Option(breakEndText, breakEndVal, false, true);

					$(".breaks_area[data-day='"+i+"'] .break_start:eq("+ index +")").append( breakStartOption ).trigger('change');
					$(".breaks_area[data-day='"+i+"'] .break_end:eq("+ index +")").append( breakEndOption ).trigger('change');
				});

				$(".breaks_area[data-day='"+i+"'] .break_line").removeClass('hidden');

				$("#dayy_off_checkbox_"+i).prop('checked', dayOff).trigger('change');
			}
		}).on('change', '.dayy_off_checkbox', function ()
		{
			$(this).closest('.form-group').prev().find('select').attr( 'disabled', $(this).is(':checked') );

			if( $(this).is(':checked') )
			{
				$(this).closest('.form-row').next('.breaks_area').slideUp( 200 ).next('.add-break-btn').slideUp(200);
			}
			else
			{
				$(this).closest('.form-row').next('.breaks_area').slideDown( 200 ).next('.add-break-btn').slideDown(200);
			}
		}).on('click', '.add-break-btn', function ()
		{
			var area = $(this).prev('.breaks_area');
			var breakTpl = $(".break_line:eq(-1)")[0].outerHTML;

			area.append( breakTpl );
			area.find(' > .break_line:eq(-1)').removeClass('hidden').hide().slideDown(200);

			booknetic.select2Ajax( area.find(' > .break_line:eq(-1) .break_start'), 'get_available_times_all');
			booknetic.select2Ajax( area.find(' > .break_line:eq(-1) .break_end'), 'get_available_times_all');
		}).on('click', '.delete-break-btn', function()
		{
			$(this).closest('.break_line').slideUp(200, function()
			{
				$(this).remove();
			});
		});

		booknetic.select2Ajax( $('.break_line:not(:eq(-1)) .break_start, .break_line:not(:eq(-1)) .break_end'), 'get_available_times_all');

		booknetic.select2Ajax( $('#input_timesheet_1_start, #input_timesheet_2_start, #input_timesheet_3_start, #input_timesheet_4_start, #input_timesheet_5_start, #input_timesheet_6_start, #input_timesheet_7_start, #input_timesheet_1_end, #input_timesheet_2_end, #input_timesheet_3_end, #input_timesheet_4_end, #input_timesheet_5_end, #input_timesheet_6_end, #input_timesheet_7_end'), 'get_available_times_all');

		$(".dayy_off_checkbox").trigger('change');

	});

})(jQuery);