(function ($)
{
	"use strict";

	function isDateBasedService()
	{
		var serviceData = $(".fs-modal #input_service").select2('data')[0];

		if( !serviceData )
			return false;

		return serviceData['date_based'];
	}

	$(document).ready(function()
	{
		var globalDayOffs = {},
			globalTimesheet = {},
			formStep = 1;

		booknetic.select2Ajax( $(".fs-modal .customers_area > div:eq(-1) .input_customer"), 'appointments.get_customers'  );

		/* start select2 init */
		booknetic.select2Ajax( $(".fs-modal .wd_input_time, .fs-modal #input_daily_time, .fs-modal #input_monthly_time"), 'appointments.get_available_times_all', function( select )
		{
			var service	=	$(".fs-modal #input_service").val(),
				staff	=	$(".fs-modal #input_staff").val(),
				dayNum	=	( select.attr('id') == 'input_daily_time' || select.attr('id') == 'input_monthly_time' ) ? -1 : select.attr('id').replace('input_time_wd_', '');

			return {
				service: service,
				staff: staff,
				day_number: dayNum
			}
		});

		booknetic.select2Ajax( $(".fs-modal #input_service"), 'appointments.get_services', function()
		{
			return {
				category: $(".fs-modal .input_category:eq(-1)").val()
			}
		});

		booknetic.select2Ajax( $(".fs-modal .input_category"), 'appointments.get_service_categories');

		booknetic.select2Ajax( $(".fs-modal #input_location"), 'appointments.get_locations' );

		booknetic.select2Ajax( $(".fs-modal #input_staff"), 'appointments.get_staff', function()
		{
			var location	=	$(".fs-modal #input_location").val(),
				service		=	$(".fs-modal #input_service").val();

			return {
				location: location,
				service: service
			}
		});

		booknetic.select2Ajax( $(".fs-modal #input_time"), 'appointments.get_available_times', function()
		{
			var service		=	$(".fs-modal #input_service").val(),
				location	=	$(".fs-modal #input_location").val(),
				staff		=	$(".fs-modal #input_staff").val(),
				date		=	$(".fs-modal #input_date").val();

			return {
				location: location,
				service: service,
				service_extras: JSON.stringify(collectExtras()),
				staff: staff,
				date: date
			}
		});

		$("#input_monthly_recurring_day_of_month").select2({
			theme: 'bootstrap',
			placeholder: booknetic.__('select'),
			allowClear: true
		});


		$(".fs-modal #input_date, #input_recurring_start_date, #input_recurring_end_date").datepicker({
			autoclose: true,
			format: dateFormat.replace('Y', 'yyyy').replace('m', 'mm').replace('d', 'dd'),
			weekStart: weekStartsOn == 'sunday' ? 0 : 1
		});

		function calcRecurringTimes()
		{
			serviceFixPeriodEndDate();

			var serviceData = $(".fs-modal #input_service").select2('data')[0];

			if( !serviceData )
				return;


			var fullPeriod			=	serviceData['full_period_value'];
			var isFullPeriodFixed	=	fullPeriod > 0 ;
			var repeatType			=	serviceData['repeat_type'];
			var startDate			=	$('#input_recurring_start_date').val();
			var endDate				=	$('#input_recurring_end_date').val();

			if( startDate == '' || endDate == '' )
				return;

			startDate	= new Date( convertDateToStandart( startDate ) );
			endDate		= new Date( convertDateToStandart( endDate ) );

			var cursor	= startDate,
				numberOfAppointments = 0,
				frequency = (repeatType == 'daily') ? $('#input_daily_recurring_frequency').val() : 1;

			if( !( frequency >= 1 ) )
			{
				frequency = 1;
				if( repeatType == 'daily' )
				{
					$('#input_daily_recurring_frequency').val('1');
				}
			}

			var activeDays = {};
			if( repeatType == 'weekly' )
			{
				$(".times_days_of_week_area > .active_day").each(function()
				{
					activeDays[ $(this).data('day') ] = true;
				});

				if( $.isEmptyObject( activeDays ) )
				{
					return;
				}
			}
			else if( repeatType == 'monthly' )
			{
				var monthlyRecurringType = $("#input_monthly_recurring_type").val();
				var monthlyDayOfWeek = $("#input_monthly_recurring_day_of_week").val();

				var selectedDays = $("#input_monthly_recurring_day_of_month").select2('val');

				for( var i in selectedDays )
				{
					activeDays[ selectedDays[i] ] = true;
				}
			}

			while(cursor <= endDate)
			{
				var weekNum = cursor.getDay();
				var dayNumber = parseInt( cursor.getDate() );
				weekNum = weekNum > 0 ? weekNum : 7;
				var dateFormat = cursor.getFullYear() + '-' + booknetic.zeroPad( cursor.getMonth() + 1 ) + '-' + booknetic.zeroPad( cursor.getDate() );

				if( repeatType == 'monthly' )
				{
					if( ( monthlyRecurringType == 'specific_day' && typeof activeDays[ dayNumber ] != 'undefined' ) || getMonthWeekInfo(cursor, monthlyRecurringType, monthlyDayOfWeek) )
					{
						if(
							// if is not off day for staff or service
							!( typeof globalTimesheet[ weekNum-1 ] != 'undefined' && globalTimesheet[ weekNum-1 ]['day_off'] ) &&
							// if is not holiday for staff or service
							typeof globalDayOffs[ dateFormat ] == 'undefined'
						)
						{
							numberOfAppointments++;
						}
					}
				}
				else if(
					// if weekly repeat type then only selected days of week...
					( typeof activeDays[ weekNum ] != 'undefined' || repeatType == 'daily' ) &&
					// if is not off day for staff or service
					!( typeof globalTimesheet[ weekNum-1 ] != 'undefined' && globalTimesheet[ weekNum-1 ]['day_off'] ) &&
					// if is not holiday for staff or service
					typeof globalDayOffs[ dateFormat ] == 'undefined'
				)
				{
					numberOfAppointments++;
				}

				cursor = new Date( cursor.getTime() + 1000 * 24 * 3600 * frequency );
			}

			$('#input_recurring_times').val( numberOfAppointments );

		}

		function serviceFixPeriodEndDate()
		{
			var serviceData = $(".fs-modal #input_service").select2('data')[0];

			var startDate = $("#input_recurring_start_date").val();

			startDate = convertDateToStandart( startDate );

			if( serviceData && serviceData['full_period_value'] > 0 )
			{
				$("#input_recurring_end_date").attr('disabled', true);
				$("#input_recurring_times").attr('disabled', true);


				if ( serviceData['full_period_type'] === 'time' )
				{
					$("#input_recurring_times").val( serviceData['full_period_value'] ).trigger('keyup');
				}
				else
				{
					endDate = new Date( startDate + ' 00:00' );

					switch ( serviceData['full_period_type'] )
					{
						case 'month':
							endDate.setMonth( endDate.getMonth() + parseInt( serviceData['full_period_value'] ) );
							endDate.setDate( endDate.getDate() - 1 );
							break;
						case 'week':
							endDate.setDate( endDate.getDate() + parseInt( serviceData['full_period_value'] ) * 7 - 1 );
							break;
						case 'day':
							endDate.setDate( endDate.getDate() + parseInt( serviceData['full_period_value'] ) - 1 );
							break;
					}

					$("#input_recurring_end_date").val( booknetic.convertDate( endDate.getFullYear() + '-' + booknetic.zeroPad( endDate.getMonth() + 1 ) + '-' + booknetic.zeroPad( endDate.getDate() ), 'Y-m-d' ) );
				}
			}
			else
			{
				$("#input_recurring_end_date").attr('disabled', false);
				$("#input_recurring_times").attr('disabled', false);

				if( $("#input_recurring_end_date").val() == '' )
				{
					startDate = new Date( startDate + ' 00:00' );
					var endDate = new Date( startDate.setMonth( startDate.getMonth() + 1 ) );

					$("#input_recurring_end_date").val( booknetic.convertDate( endDate.getFullYear() + '-' + booknetic.zeroPad( endDate.getMonth() + 1 ) + '-' + booknetic.zeroPad( endDate.getDate() ), 'Y-m-d' ) );
				}
			}
		}

		function serviceFixFrequency()
		{
			var serviceData = $(".fs-modal #input_service").select2('data')[0];

			if( serviceData && serviceData['repeat_frequency'] > 0 && serviceData['repeat_type'] == 'daily' )
			{
				$("#input_daily_recurring_frequency").val( serviceData['repeat_frequency'] ).attr('disabled', true);
			}
			else
			{
				$("#input_daily_recurring_frequency").attr('disabled', false);
			}
		}

		function getMonthWeekInfo( date, type, dayOfWeek )
		{
			var jsDate = new Date( date ),
				weekd = jsDate.getDay();
			weekd = weekd == 0 ? 7 : weekd;

			if( weekd != dayOfWeek )
			{
				return false;
			}

			var month = jsDate.getMonth()+1,
				year = jsDate.getFullYear();

			if( type == 'last' )
			{
				var nextWeek = new Date(jsDate.getTime());
				nextWeek.setDate( nextWeek.getDate() + 7 );

				return nextWeek.getMonth()+1 != month ? true : false;
			}

			var firstDayOfMonth = new Date( year + '-' + booknetic.zeroPad( month ) + '-01' ),
				firstWeekDay = firstDayOfMonth.getDay();
			firstWeekDay = firstWeekDay == 0 ? 7 : firstWeekDay;

			var dif = ( dayOfWeek >= firstWeekDay ? dayOfWeek : parseInt(dayOfWeek)+7 ) - firstWeekDay;

			var days = jsDate.getDate() - dif,
				dNumber = parseInt(days / 7)+1;

			return type == dNumber ? true : false;
		}

		function loadServiceExtras ()
		{
			$( '#tab_extras' ).empty();

			let service_id 	= $( '.fs-modal #input_service' ).val();

			booknetic.ajax( 'appointments.get_service_extras', { service_id }, function ( result )
			{
				$( '#tab_extras' ).html( booknetic.htmlspecialchars_decode( result[ 'html' ] ) )
			} );
		}

		function constructNumbersOfGroup()
		{
			var t  = $(".customers_area .number_of_group_customers");
			t.find('.c_number').text('1');
			var serviceInf = $(".fs-modal #input_service").select2('data')[0];

			if( !serviceInf )
			{
				t.attr('disabled' ,true);
				return;
			}
			t.removeAttr('disabled');
			var sumOfSelectedNums = 0;

			$(".fs-modal .customers_area .number_of_group_customers .c_number").each(function ()
			{
				sumOfSelectedNums += parseInt( $(this).text() );
			});

			var maxCapacity	= serviceInf['max_capacity'] - sumOfSelectedNums + parseInt( t.find('.c_number').text().trim() ),
				rows		= '';

			for( var i = 1; i <= (maxCapacity > 0 ? maxCapacity : 1); i++ )
			{
				rows += '<a class="dropdown-item" href="#">' + i + '</a>';
			}

			t.next('.number_of_group_customers_panel').html( rows );
		}

		function collectExtras()
		{
			var extras = [];

			$('.fs-modal #tab_extras div[data-extra-id]').each(function ()
			{
				var extra_id	= $(this).data('extra-id'),
					quantity	= $(this).find('.extra_quantity').val();

				if( quantity > 0 )
				{
					extras.push( {
						extra: extra_id,
						quantity: quantity
					} );
				}
			});

			return extras;
		}


		function convertDateToStandart( date )
		{
			if( dateFormat == 'd-m-Y')
			{
				var startDateParts = date.split('-');
				date = `${ startDateParts[2] }-${ startDateParts[1] }-${ startDateParts[0] }`;
			}
			else if( dateFormat == 'd/m/Y')
			{
				var startDateParts = date.split('/');
				date = `${ startDateParts[2] }-${ startDateParts[1] }-${ startDateParts[0] }`;
			}
			else if( dateFormat == 'm/d/Y')
			{
				var startDateParts = date.split('/');
				date = `${ startDateParts[2] }-${ startDateParts[0] }-${ startDateParts[1] }`;
			}
			else if( dateFormat == 'd.m.Y')
			{
				var startDateParts = date.split('.');
				date = `${ startDateParts[2] }-${ startDateParts[1] }-${ startDateParts[0] }`;
			}

			return date;
		}

		$(".fs-modal").on('keyup', '#input_recurring_times', function()
		{
			var serviceData = $(".fs-modal #input_service").select2('data')[0];

			if( !serviceData )
				return;

			var repeatType	=	serviceData['repeat_type'],
				start		=	$('#input_recurring_start_date').val(),
				end			=	$('#input_recurring_end_date').val(),
				times		=	$(this).val();

			if( start == '' || times == '' || times <= 0 )
				return;

			var frequency = (repeatType == 'daily') ? $('#input_daily_recurring_frequency').val() : 1;

			if( !( frequency >= 1 ) )
			{
				frequency = 1;
				if( repeatType == 'daily' )
				{
					$('#booknetic_daily_recurring_frequency').val('1');
				}
			}

			var activeDays = {};
			if( repeatType == 'weekly' )
			{
				$(".times_days_of_week_area > .active_day").each(function()
				{
					activeDays[ $(this).data('day') ] = true;
				});

				if( $.isEmptyObject( activeDays ) )
				{
					return;
				}
			}
			else if( repeatType == 'monthly' )
			{
				var monthlyRecurringType = $("#input_monthly_recurring_type").val();
				var monthlyDayOfWeek = $("#input_monthly_recurring_day_of_week").val();

				var selectedDays = $("#input_monthly_recurring_day_of_month").select2('val');

				for( var i in selectedDays )
				{
					activeDays[ selectedDays[i] ] = true;
				}
			}

			start = convertDateToStandart( start );

			var cursor = new Date( start );

			var c_times = 0;
			while( (!$.isEmptyObject( activeDays ) || repeatType == 'daily') && c_times < times )
			{
				var weekNum = cursor.getDay();
				var dayNumber = parseInt( cursor.getDate() );
				weekNum = weekNum > 0 ? weekNum : 7;
				var dateFormat = cursor.getFullYear() + '-' + booknetic.zeroPad( cursor.getMonth() + 1 ) + '-' + booknetic.zeroPad( cursor.getDate() );

				if( repeatType == 'monthly' )
				{
					if( ( monthlyRecurringType == 'specific_day' && typeof activeDays[ dayNumber ] != 'undefined' ) || getMonthWeekInfo(cursor, monthlyRecurringType, monthlyDayOfWeek) )
					{
						if
						(
							// if is not off day for staff or service
							!( typeof globalTimesheet[ weekNum ] != 'undefined' && globalTimesheet[ weekNum ]['day_off'] ) &&
							// if is not holiday for staff or service
							typeof globalDayOffs[ dateFormat ] == 'undefined'
						)
						{
							c_times++;
						}
					}
				}
				else if
				(
					// if weekly repeat type then only selected days of week...
					( typeof activeDays[ weekNum ] != 'undefined' || repeatType == 'daily' ) &&
					// if is not off day for staff or service
					!( typeof globalTimesheet[ weekNum-1 ] != 'undefined' && globalTimesheet[ weekNum-1 ]['day_off'] ) &&
					// if is not holiday for staff or service
					typeof globalDayOffs[ dateFormat ] == 'undefined'
				)
				{
					c_times++;
				}

				cursor = new Date( cursor.getTime() + 1000 * 24 * 3600 * frequency );
			}

			cursor = new Date( cursor.getTime() - 1000 * 24 * 3600 * frequency );
			end = cursor.getFullYear() + '-' + booknetic.zeroPad( cursor.getMonth() + 1 ) + '-' + booknetic.zeroPad( cursor.getDate() );

			if( !isNaN( cursor.getFullYear() ) )
			{
				$('#input_recurring_end_date').val( booknetic.convertDate( end, 'Y-m-d' ) );
			}

		}).on('click', '.second-step .dates-table .date-edit-btn', function()
		{
			var tr		= $(this).closest('tr'),
				timeTd	= tr.children('td[data-time]'),
				time	= timeTd.data('time'),
				date1	= tr.children('td[data-date]').data('date');

			timeTd.children('.time-span').html('<select class="form-control time-select"></select>').css('float', 'right').parent('td').css({'padding-top': '7px', 'padding-bottom': '14px'});

			booknetic.select2Ajax( timeTd.find('.time-select'), 'appointments.get_available_times', function()
			{
				var service		=	$(".fs-modal #input_service").val(),
					location	=	$(".fs-modal #input_location").val(),
					staff		=	$(".fs-modal #input_staff").val();

				return {
					service: service,
					location: location,
					service_extras: JSON.stringify(collectExtras()),
					staff: staff,
					date: date1
				}
			});

			$(this).closest('td').children('.date-has-error').remove();
			$(this).remove();
		}).on('keyup', '#input_daily_recurring_frequency', calcRecurringTimes)
		.on('change', '#input_monthly_recurring_type, #input_monthly_recurring_day_of_week, #input_monthly_recurring_day_of_month', calcRecurringTimes)
		.on('change', '.input_category', function()
		{
		  var categId = $(this).val();

		  while( $(this).parent().next().children('select').length > 0 )
		  {
		      $(this).parent().next().remove();
		  }

		  if( categId > 0 && $(this).select2('data')[0].have_sub_categ > 0 )
		  {
		      var selectCount = $(".fs-modal .input_category").length;

		      $(this).parent().after( '<div class="mt-2"><select class="form-control input_category"></select></div>' );

		      booknetic.select2Ajax( $(this).parent().next().children('select'), 'appointments.get_service_categories', function(select)
		      {
		          return {
		              category: select.parent().prev().children('select').val()
		          }
		      });
		  }

		  $(".fs-modal #input_service").select2( 'val', false );
		}).on('change', '#input_location', function()
		{
			$(".fs-modal #input_staff").select2( 'val', false );
		}).on('change', '#input_service', function()
		{
			$(".fs-modal #input_staff").select2( 'val', false );
			constructNumbersOfGroup();
			loadServiceExtras();
		}).on('change', '#input_staff', function()
		{
			$(".fs-modal #input_date").attr('disabled', ( !$(this).val() ) )
			$(".fs-modal #input_time").attr('disabled', ( !$(this).val() ) )

			if( $(this).val() > 0 )
			{
				var serviceData = $(".fs-modal #input_service").select2('data')[0];
				var isRepeatable = serviceData ? serviceData['repeatable'] : false;

				if( isRepeatable )
				{
					var repeatType = serviceData['repeat_type'];

					$(".fs-modal [data-service-type]").hide();
					$(".fs-modal [data-service-type='repeatable']").slideDown(300);
					$(".fs-modal [data-service-type='repeatable_" + repeatType + "']").slideDown(300);

					serviceFixPeriodEndDate();
					serviceFixFrequency();

					$("#input_recurring_start_date").trigger('change');

					if( repeatType == 'monthly' || repeatType == 'daily' )
					{
						if( isDateBasedService() )
						{
							$('#input_'+repeatType+'_time').append('<option>00:00</option>').val('00:00');
							$('#input_'+repeatType+'_time').closest('.form-group').hide();
						}
						else
						{
							$('#input_'+repeatType+'_time').closest('.form-group').show();
							$('#input_'+repeatType+'_time').empty();
						}
					}
				}
				else
				{
					$(".fs-modal [data-service-type='non_repeatable']").slideDown(300);
					$(".fs-modal [data-service-type='repeatable']").slideUp(300);
					$(".fs-modal [data-service-type='repeatable_daily']").slideUp(300);
					$(".fs-modal [data-service-type='repeatable_monthly']").slideUp(300);
					$(".fs-modal [data-service-type='repeatable_weekly']").slideUp(300);

					if( isDateBasedService() )
					{
						$('#input_time').append('<option>00:00</option>').val('00:00');
						$('#input_time').closest('.form-group').hide();
					}
					else
					{
						$('#input_time').closest('.form-group').show();
						$('#input_time').empty();
					}
				}
			}
			else
			{
				$(".fs-modal [data-service-type='non_repeatable']").slideUp(300);
				$(".fs-modal [data-service-type='repeatable']").slideUp(300);
				$(".fs-modal [data-service-type='repeatable_daily']").slideUp(300);
				$(".fs-modal [data-service-type='repeatable_monthly']").slideUp(300);
				$(".fs-modal [data-service-type='repeatable_weekly']").slideUp(300);
			}

		}).on('click' , '.customers_area > div:eq(-1) .number_of_group_customers' , function () {
			constructNumbersOfGroup();
		}).on('click', '.copy_time_to_all', function()
		{
			var time		= $(this).closest('.active_day').find('.wd_input_time').select2('data')[0];

			if( time )
			{
				var	timeId		= time['id'],
					timeText	= time['text'];

				$(".fs-modal .active_day:not(:first)").each(function ()
				{
					let option = new Option(timeText, timeId, false, true);
					$(this).find(".wd_input_time").append(option).trigger('change');
				});
			}

		}).on('change', '#input_monthly_recurring_type', function()
		{

			if( $(this).val() == 'specific_day' )
			{
				$("#input_monthly_recurring_day_of_month").next('.select2').show();
				$("#input_monthly_recurring_day_of_week").hide();
			}
			else
			{
				$("#input_monthly_recurring_day_of_month").next('.select2').hide();
				$("#input_monthly_recurring_day_of_week").show();
			}

		}).on('click', '#addAppointmentSave', function ()
		{
			var location				=	$("#input_location").val(),
				service					=	$("#input_service").val(),
				staff					=	$("#input_staff").val(),
				date					=	$("#input_date").val(),
				time					=	$("#input_time").val(),
				note					=	$("#note").val(),
				customer_id				=	$('.customers_area .input_customer').val(),
				status					=	$('.customers_area .customer-status-btn > button').attr('data-status'),
				weight					=	$('.customers_area .c_number').text(),
				repeatType				=	service > 0 ? $("#input_service").select2('data')[0]['repeat_type'] : '',
				recurringTimes			=	{},
				recurring_start_date	=	$("#input_recurring_start_date").val(),
				run_workflows			=	$("#input_run_workflows").is(':checked') ? 1 : 0,
				recurring_end_date		=	$("#input_recurring_end_date").val(),
				extras					=	collectExtras();

			if( staff == '' || service == '' || customer_id == '' )
			{
				booknetic.toast(booknetic.__('fill_all_required'), 'unsuccess');
				return;
			}

			let appointmentValidation = booknetic.doFilter('appointments.validation', false);
			if( appointmentValidation !== false )
			{
				booknetic.toast(appointmentValidation, 'unsuccess');
				return;
			}

			if( repeatType == 'weekly' )
			{
				$(".times_days_of_week_area > .active_day").each(function()
				{
					var dayNum = $(this).data('day');
					var time = $(this).find('.wd_input_time').val();

					recurringTimes[ dayNum ] = time;
				});

				recurringTimes = JSON.stringify( recurringTimes );
			}
			else if( repeatType == 'daily' )
			{
				recurringTimes = $("#input_daily_recurring_frequency").val();
				time = $("#input_daily_time").val();
			}
			else if( repeatType == 'monthly' )
			{
				recurringTimes = $("#input_monthly_recurring_type").val();
				if( recurringTimes == 'specific_day' )
				{
					recurringTimes += ':' + ( $("#input_monthly_recurring_day_of_month").val() == null ? '' : $("#input_monthly_recurring_day_of_month").val().join(',') );
				}
				else
				{
					recurringTimes += ':' + $("#input_monthly_recurring_day_of_week").val();
				}

				time = $("#input_monthly_time").val();
			}

			var isFirstStep = $(".fs-modal .first-step").css('display') == 'none' ? false : true;

			var data = new FormData();
			var obj = {};

			if( !isFirstStep )
			{
				var recurringDates = [];
				var hasTimeError = false;

				$(".fs-modal .dates-table > tr").each(function()
				{
					var sDate = $(this).find('[data-date]').data('date');
					var sTime = $(this).find('[data-time]').data('time');
					// if tried to change the time
					if( $(this).find('.time-select').length )
					{
						sTime = $(this).find('.time-select').val();
						if( sTime == '' )
						{
							hasTimeError = true;
						}
					}
					recurringDates.push([ sDate, sTime ]);
				});

				if( hasTimeError )
				{
					booknetic.toast(booknetic.__('fill_all_required'), 'unsuccess');
					return;
				}
				obj['appointments'] = JSON.stringify(recurringDates);
			}
			obj['location'] =  location;
			obj['service'] =  service;
			obj['staff'] =  staff;
			obj['date'] =  date;
			obj['time'] =  time ? time : '';
			obj['note'] =  note;
			obj['recurring_start_date'] =  recurring_start_date;
			obj['recurring_end_date'] =  recurring_end_date;
			obj['recurring_times'] =  recurringTimes;
			obj['customer_id'] =  customer_id ;
			obj['status'] =  status ;
			obj['weight'] =  weight ;
			obj['service_extras'] =  extras;
			let currentIndex = 0;
			data.append('current' , currentIndex);
			obj = booknetic.doFilter('appointments.create_appointment.cart' , obj , data );
			data.append('cart' , JSON.stringify([obj]))

			data.append('run_workflows', run_workflows);

			booknetic.ajax( 'appointments.create_appointment', data, function(result)
			{
				if( 'dates' in result )
				{
					$(".fs-modal .first-step").hide();
					$(".fs-modal .second-step").removeClass('hidden').show();

					var tbody = '';

					for(var i in result['dates'])
					{
						var hasError = result['dates'][i]['is_bookable'] ? '' : '<span class="date-has-error" title="' + booknetic.__('timeslot_is_not_available') + '"><img src="' + assetsUrl +'icons/warning.svg"></span>';

						tbody += '<tr><td>' + ( parseInt(i) + 1 ) + '</td><td data-date="' + result['dates'][i].date + '">' + result['dates'][i].date_format + '</td><td data-time="' + result['dates'][i].time + '"><span class="time-span">' + result['dates'][i].time_format + '</span>' + hasError + ' <button type="button" class="btn btn-default date-edit-btn">EDIT</button></td></tr>';
					}

					$(".fs-modal .second-step .dates-table").html( tbody );

					if( isDateBasedService() )
					{
						$(".fs-modal .second-step .dates-table tr > td:last-child").hide();
						$(".fs-modal .second-step table thead tr > th:last-child").hide();
					}
					else
					{
						$(".fs-modal .second-step table thead tr > th:last-child").show();
					}
				}
				else
				{
					booknetic.modalHide($(".fs-modal"));

					if( currentModule == 'calendar' )
					{
						reloadCalendarFn();
					}
					else
					{
						booknetic.dataTable.reload( $("#fs_data_table_div") );
					}
				}

			});
		} ).on('click', '.number_of_group_customers_panel > a', function ()
		{
			var num = $(this).text().trim();

			$(this).closest('.number_of_group_customers_panel').prev().find('.c_number').text( num );
		}).on('click', '.customer-status-panel [data-status]', function ()
		{
			$(this).closest('.customer-status-panel').prev().attr('data-status', $(this).attr('data-status') );
			$(this).closest('.customer-status-panel').prev().children('i').attr('class', $(this).children('i').attr('class') );
			$(this).closest('.customer-status-panel').prev().children('i').attr('style', $(this).children('i').attr('style'));
			$(this).closest('.customer-status-panel').prev().children('.c_status').text($(this).text().trim() );
		}).on('change', '.day_of_week_checkbox', function ()
		{
			var activeFirstDay = $(".fs-modal .times_days_of_week_area .active_day").attr('data-day');

			var dayNum	= $(this).attr('id').replace('day_of_week_checkbox_', ''),
				dayDIv	= $(".fs-modal .times_days_of_week_area > [data-day='" + dayNum + "']");

			if( $(this).is(':checked') )
			{
				dayDIv.removeClass('hidden').slideDown(200).addClass('active_day');

				if( isDateBasedService() )
				{
					dayDIv.find('.wd_input_time').append('<option>00:00</option>').val('00:00');
				}
			}
			else
			{
				dayDIv.slideUp(200).removeClass('active_day');
			}

			$(".fs-modal .times_days_of_week_area .active_day .copy_time_to_all").fadeOut( activeFirstDay > dayNum ? 100 : 0 );
			$(".fs-modal .times_days_of_week_area .active_day .copy_time_to_all:first").fadeIn( activeFirstDay > dayNum ? 100 : 0 );

			if( $('.fs-modal .day_of_week_checkbox:checked').length > 0 && !isDateBasedService() )
			{
				$('.times_days_of_week_area').slideDown(200);
			}
			else
			{
				$('.times_days_of_week_area').slideUp(200);
			}

			calcRecurringTimes();
		}).on('change', '#input_recurring_start_date, #input_recurring_end_date', function()
		{
			calcRecurringTimes();

			var serviceId	= $("#input_service").val(),
				staffId		= $("#input_staff").val(),
				startDate	= $("#input_recurring_start_date").val(),
				endDate		= $("#input_recurring_end_date").val();

			if( startDate == '' || endDate == '' )
				return;

			let data = new FormData();
			let obj = {};

			obj[ 'service' ] =  serviceId;
			obj[ 'staff' ] =  staffId;
			obj[ 'recurring_start_date' ] =  convertDateToStandart( startDate );
			obj[ 'recurring_end_date' ] =  convertDateToStandart( endDate );

			data.append( 'current' , 0 );
			data.append( 'cart' , JSON.stringify( [ obj ] ) )

			booknetic.ajax('appointments.get_day_offs', data, function( result )
			{
				globalDayOffs = result['day_offs'];
				globalTimesheet = result['timesheet'];

				result['disabled_days_of_week'].forEach(function( value, key )
				{
					$('#day_of_week_checkbox_' + (parseInt(key)+1)).attr('disabled', value);
				});

				calcRecurringTimes();
			});
		}).on('change', '#input_date', function ()
		{
			$(".fs-modal #input_time").select2('val', false);
			$(".fs-modal #input_time").trigger('change');
		});

		$(".fs-modal #input_staff").trigger('change');

		$("#input_monthly_recurring_type").trigger('change');

	});

})(jQuery);