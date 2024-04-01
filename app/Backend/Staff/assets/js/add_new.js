(function ($)
{
	"use strict";

	$(document).ready(function()
	{
		booknetic.select2Ajax( $('.fs-modal .break_line:not(:eq(-1)) .break_start, .fs-modal .break_line:not(:eq(-1)) .break_end, .fs-modal .special-day-row:not(:eq(-1)) .input_special_day_start, .fs-modal .special-day-row:not(:eq(-1)) .input_special_day_end'), 'get_available_times_all');

		booknetic.select2Ajax( $('#input_timesheet_1_start, #input_timesheet_2_start, #input_timesheet_3_start, #input_timesheet_4_start, #input_timesheet_5_start, #input_timesheet_6_start, #input_timesheet_7_start, #input_timesheet_1_end, #input_timesheet_2_end, #input_timesheet_3_end, #input_timesheet_4_end, #input_timesheet_5_end, #input_timesheet_6_end, #input_timesheet_7_end'), 'get_available_times_all');

		booknetic.initMultilangInput( $( '#input_name' ), 'staff', 'name' );
		booknetic.initMultilangInput( $( '#input_profession' ), 'staff', 'profession' );
		booknetic.initMultilangInput( $( '#input_note' ), 'staff', 'about' );

		$('.fs-modal').on('click', '.copy_time_to_all', function ()
		{
			let startEl   = $(".fs-modal #input_timesheet_1_start"),
				endEl     = $(".fs-modal #input_timesheet_1_end"),
				start	  = startEl.val(),
				startText = startEl.select2('data')[0]['text'],
				end		  = endEl.val(),
				endText	  = endEl.select2('data')[0]['text'],
				dayOff	  = $(".fs-modal #dayy_off_checkbox_1").is(':checked'),
				breaks	  = $(".fs-modal .breaks_area[data-day='1'] .break_line"),
				breakTpl  = $(".fs-modal .break_line:eq(-1)")[0].outerHTML;

			for(var i = 2; i <=7; i++)
			{
				let startOption = new Option(startText, start, false, true);
				let endOption = new Option(endText, end, false, true);

				$(".fs-modal #input_timesheet_"+i+"_start").append(startOption).trigger('change');
				$(".fs-modal #input_timesheet_"+i+"_end").append(endOption).trigger('change');
				$(".fs-modal .breaks_area[data-day='"+i+"']").html('');

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

				$(".fs-modal .breaks_area[data-day='"+i+"'] .break_line").removeClass('hidden');

				$(".fs-modal #dayy_off_checkbox_"+i).prop('checked', dayOff).trigger('change');
			}
		}).on('click', '#addStaffForm .delete-break-btn', function ()
		{
			$(this).closest('.break_line').slideUp(200, function()
			{
				$(this).remove();
			});
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
			var breakTpl = $(".fs-modal .break_line:eq(-1)")[0].outerHTML;

			area.append( breakTpl );
			area.find(' > .break_line:eq(-1)').hide().removeClass('hidden').slideDown(200);

			booknetic.select2Ajax( area.find(' > .break_line:eq(-1) .break_start'), 'get_available_times_all');
			booknetic.select2Ajax( area.find(' > .break_line:eq(-1) .break_end'), 'get_available_times_all');
		}).on('click', '.add-special-day-btn', function ()
		{
			var specialDayTpl = $(".fs-modal .special-day-row:eq(-1)")[0].outerHTML;

			$(".fs-modal .special-days-area").append( specialDayTpl );

			var lastRow = $(".fs-modal .special-days-area > .special-day-row:last");
			var date_format_js =$( '.input_special_day_date' ).data('date-format').replace('Y','yyyy').replace('m','mm').replace('d','dd');

			lastRow.find('.input_special_day_date').datepicker({
				autoclose: true,
				format: date_format_js,
				weekStart: weekStartsOn == 'sunday' ? 0 : 1
			});

			booknetic.select2Ajax( lastRow.find('.input_special_day_start'), 'get_available_times_all');
			booknetic.select2Ajax( lastRow.find('.input_special_day_end'), 'get_available_times_all');

			lastRow.removeClass('hidden').hide().slideDown(300);
		}).on('click', '.special-days-area .remove-special-day-btn', function ()
		{
			var spRow = $(this).closest('.special-day-row');
			booknetic.confirm( booknetic.__('delete_special_day'), 'danger', 'unsuccess', function()
			{
				spRow.slideUp(300, function()
				{
					spRow.remove();
				});
			});
		}).on('click', '.special-days-area .special-day-add-break-btn', function ()
		{
			var area = $(this).closest('.special-day-row').find('.special_day_breaks_area');
			var breakTpl = $(".fs-modal .break_line:eq(-1)")[0].outerHTML;

			area.append( breakTpl );
			area.find(' > .break_line:eq(-1)').hide().removeClass('hidden').slideDown(200);

			booknetic.select2Ajax( area.find(' > .break_line:eq(-1) .break_start'), 'get_available_times_all');
			booknetic.select2Ajax( area.find(' > .break_line:eq(-1) .break_end'), 'get_available_times_all');
		}).on('click', '#addStaffSave', function ()
		{
			var wp_user					= $('#input_wp_user').val(),
				name					= $("#input_name").val(),
				profession					= $("#input_profession").val(),
				phone					= $("#input_phone").val(),
				email					= $("#input_email").val(),
				allow_staff_to_login	= $("#input_allow_staff_to_login").is(':checked') ? 1 : 0,
				wp_user_use_existing	= $("#input_wp_user_use_existing").val(),
				wp_user_password		= $("#input_wp_user_password").val(),
				update_wp_user			= $("#input_update_wp_user").is(':checked') ? 1 : 0,
				note					= $("#input_note").val(),
				locations				= $("#input_locations").val(),
				services				= $("#input_services").val(),
				image					= $("#input_image")[0].files[0];

			if( name === '' ||  email === '' )
			{
				booknetic.toast(booknetic.__('fill_all_required'), 'unsuccess');
				return;
			}
			var weekly_schedule = [ ];

			if( $('#set_specific_timesheet_checkbox').is(':checked') )
			{
				for( var d=1; d <= 7; d++)
				{
					(function()
					{
						var dayOff	= $(".fs-modal #dayy_off_checkbox_"+d).is(':checked') ? 1 : 0,
							start	= dayOff ? '' : $(".fs-modal #input_timesheet_"+d+"_start").val(),
							end		= dayOff ? '' : $(".fs-modal #input_timesheet_"+d+"_end").val(),
							breaks	= [];

						if( !dayOff )
						{
							$(".fs-modal .breaks_area[data-day='" + d + "'] > .break_line").each(function()
							{
								var breakStart	= $(this).find('.break_start').val(),
									breakEnd	= $(this).find('.break_end').val();

								if( breakStart != '' && breakEnd != '' )
									breaks.push( [ breakStart, breakEnd ] );
							});
						}

						weekly_schedule.push( {
							'start'		: start,
							'end'		: end,
							'day_off'	: dayOff,
							'breaks'	: breaks
						} );
					})();
				}
			}

			var special_days = [];
			$(".fs-modal .special-days-area > .special-day-row").each(function ()
			{
				var spId = $(this).data('id'),
					spDate = $(this).find('.input_special_day_date').val(),
					spStartTime = $(this).find('.input_special_day_start').val(),
					spEndTime = $(this).find('.input_special_day_end').val(),
					spBreaks = [];

				$(this).find('.special_day_breaks_area > .break_line').each(function()
				{
					var breakStart = $(this).find('.break_start').val(),
						breakEnd = $(this).find('.break_end').val();

					spBreaks.push([ breakStart, breakEnd ]);
				});

				special_days.push({
					'id': spId > 0 ? spId : 0,
					'date': spDate,
					'start': spStartTime,
					'end': spEndTime,
					'breaks': spBreaks
				});
			});

			var holidays = [];

			var selectedHolidays = $(".fs-modal .yearly_calendar").data('calendar').getDataSource()
			for( var i in selectedHolidays )
			{
				var holidayId = selectedHolidays[i]['id'];
				var holidayDate = new Date( selectedHolidays[i]['startDate'] );

				holidayDate = holidayDate.getFullYear() + '-' + booknetic.zeroPad( holidayDate.getMonth() + 1 ) + '-' + booknetic.zeroPad( holidayDate.getDate() );

				holidays.push({
					id: holidayId,
					date: holidayDate
				});
			}

			var data = new FormData();

			data.append('id', $(".fs-modal #add_new_JS").data('staff-id'));
			data.append('wp_user', wp_user);
			data.append('name', name);
			data.append('profession', profession);
			data.append('phone', phone);
			data.append('email', email);
			data.append('allow_staff_to_login', allow_staff_to_login);
			data.append('wp_user_use_existing', wp_user_use_existing);
			data.append('wp_user_password', wp_user_password);
			data.append('update_wp_user', update_wp_user);
			data.append('note', note);
			data.append('locations', locations);
			data.append('services', services);
			data.append('image', image);
			data.append('weekly_schedule', JSON.stringify( weekly_schedule ));
			data.append('special_days', JSON.stringify( special_days ));
			data.append('holidays', JSON.stringify(holidays));
			data.append('translations', booknetic.getTranslationData( $('#tab_details')  ))

			booknetic.ajax( 'staff.save_staff', data, function()
			{
				booknetic.modalHide($(".fs-modal"));
				booknetic.dataTable.reload( $("#fs_data_table_div") );
			});
		}).on('change', '#set_specific_timesheet_checkbox', function ()
		{
			if( $(this).is(':checked') )
			{
				$('#set_specific_timesheet').slideDown(200);
			}
			else
			{
				$('#set_specific_timesheet').slideUp(200);
			} // suallar: asagidaki komentin ici silinecek
		}).on('change', '#input_wp_user', function ()
		{
			if( $(this).val() > 0 )
			{
				let email = $(this).children(':selected').data('email');
				let name = $(this).children(':selected').text();

				$('#input_name').val( name );
				$('#input_email').val( email );
			}
		}).on('click', '#hideStaffBtn', function ()
		{
			booknetic.ajax('hide_staff', { staff_id: $(".fs-modal #add_new_JS").data('staff-id') }, function ()
			{
				booknetic.modalHide($(".fs-modal"));

				booknetic.dataTable.reload( $("#fs_data_table_div") );
			});
		}).on('change', '#input_allow_staff_to_login', function ()
		{
			if( $(this).is(':checked') )
			{
				$('[data-hide="allow_staff_to_login"]').slideDown(200);
				$('#input_wp_user_use_existing').trigger('change');

			}
			else
			{
				$('[data-hide="allow_staff_to_login"]').slideUp(200);
				$('[data-hide="existing_user"]').slideUp(200);
				$('[data-hide="create_password"]').slideUp(200);
			}
		}).on('change', '#input_wp_user_use_existing', function ()
		{
			if( $(this).val() === 'yes' )
			{
				$('[data-hide="existing_user"]').show();
				$('[data-hide="create_password"]').hide();
			}
			else
			{
				$('[data-hide="existing_user"]').hide();
				$('[data-hide="create_password"]').show();
			}
		});

		$(".fs-modal #input_locations, .fs-modal #input_services").select2({
			theme:			'bootstrap',
			placeholder:	booknetic.__('select'),
			allowClear:		true
		});


		$(".fs-modal .dayy_off_checkbox").trigger('change');

		var dbHolidays = $(".fs-modal #add_new_JS").data('holidays');

		$(".fs-modal .yearly_calendar").calendar({
			dataSource: holidaysArr( dbHolidays ),
			clickDay: function(e)
			{
				var selectedDate = new Date( e.date ),
					selectedDate = selectedDate.getFullYear() + '-' + booknetic.zeroPad( selectedDate.getMonth() + 1 ) + '-' + booknetic.zeroPad( selectedDate.getDate() );

				if( selectedDate in dbHolidays )
				{
					delete dbHolidays[ selectedDate ];
				}
				else
				{
					dbHolidays[ selectedDate ] = 0;
				}

				$(".fs-modal .yearly_calendar").data('calendar').setDataSource( holidaysArr( dbHolidays ) );
			},
		});

		$('#set_specific_timesheet_checkbox').trigger('change');
		$('#input_wp_user_use_existing').trigger('change');
		$('#input_allow_staff_to_login').trigger('change');

		var date_format_js =$( '.input_special_day_date' ).data('date-format').replace('Y','yyyy').replace('m','mm').replace('d','dd');

		$('.fs-modal .input_special_day_date').datepicker({
			autoclose: true,
			format: date_format_js,
			weekStart: weekStartsOn == 'sunday' ? 0 : 1
		});

		$('#input_wp_user').select2({
			theme: 'bootstrap',
			placeholder: booknetic.__('select'),
			allowClear: true
		});

	});

	function holidaysArr( arr )
	{
		var newArr = [];

		for( var date in arr )
		{
			var id = arr[date];
			var parse_date = date.split('-');
			var date2 = new Date( parse_date[0], parse_date[1]-1, parse_date[2] );

			newArr.push({
				id: id,
				startDate: new Date( date2.getFullYear(), date2.getMonth(), date2.getDate() ),
				endDate: new Date( date2.getFullYear(), date2.getMonth(), date2.getDate() )
			});
		}

		return newArr;
	}

})(jQuery);