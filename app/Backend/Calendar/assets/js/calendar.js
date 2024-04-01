var FSCalendar,
	FSCalendarResources = [],
	FSCalendarRange = {};

function reloadCalendarFn()
{
	var locations = $( '#calendar_location_filter' ).select2( 'val' ),
		services  = $( '#calendar_service_filter' ).select2( 'val' ),
		staff	  = $( '#calendar_staff_filter' ).select2( 'val' ),
		statuses  = $( '#calendar_status_filter' ).select2( 'val' ),
		payments  = $( '#calendar_payment_filter' ).select2( 'val' ),

		activeRange	= FSCalendar.state.dateProfile.activeRange,

		startDate = activeRange.start.getUTCFullYear() + '-' + booknetic.zeroPad(parseInt(activeRange.start.getUTCMonth())+1) + '-' + booknetic.zeroPad(activeRange.start.getUTCDate()),
		endDate	  = activeRange.end.getUTCFullYear() + '-' + booknetic.zeroPad(parseInt(activeRange.end.getUTCMonth())+1) + '-' + booknetic.zeroPad(activeRange.end.getUTCDate());

	booknetic.ajax( 'get_calendar',
		{
			locations: locations,
			services: services,
			staff: staff,
			statuses: statuses,
			payments: payments,
			start: startDate,
			end: endDate},
		function(result )
	{
		let weekDays = JSON.parse(result.businessHours.timesheet);

		weekDays.map( (day) => {
			if ( day.day_off == 1 ) {
				day.start = '00:00';
				day.end = '00:00';
			}
			return day;
		} );

		var eventSources = FSCalendar.getEventSources();
		for (var i = 0; i < eventSources.length; i++)
		{
			eventSources[i].remove();
		}

		let arr = [];
		FSCalendarResources = [];

		result[ 'data' ].forEach( ( item ) => {
			if( typeof item.title != 'undefined' )
			{
				item.title = booknetic.htmlspecialchars_decode( item.title );
			}

			let staffId = item.staff_id;

			if( arr.indexOf( staffId ) === -1 )
			{
				arr.push( staffId );

				FSCalendarResources.push( {
					id: staffId,
					title: item[ 'staff_name' ],
				} );
			}

			item.editable = item[ 'appointment_id' ] !== 0;
		});
		FSCalendar.refetchResources();

		FSCalendar.addEventSource( result['data'] );

		FSCalendarRange = {
			weekDays: weekDays,
			appointments: result[ 'data' ],
			start: new Date( startDate ),
			end: new Date( endDate )
		}

		reloadCalendarHours()
	});
}

function reloadCalendarHours()
{
	if ( FSCalendarRange.settingOption )
	{
		FSCalendarRange.settingOption = false;
		return;
	}

	let appointmentsInRange = FSCalendarRange.appointments.filter( ( appointment ) =>
	{
		return 	( new Date( appointment.start ).getTime() > (new Date(FSCalendar.getDate().getTime()).setHours(0,0,0,0) ) ) &&
				( new Date( appointment.end ).getTime() < (new Date(FSCalendar.getDate().getTime()).setHours(24,0,0,0) ) );
	});

	let startTime, endTime

	if ( FSCalendar.view.type === 'resourceTimeGridDay' )
	{
		let day = FSCalendar.getDate().getDay() - 1;
		day = day === -1 ? 6 : day;

		startTime = FSCalendarRange.weekDays[ day ].start
		endTime = FSCalendarRange.weekDays[ day ].end
	}
	else if ( FSCalendar.view.type === 'timeGridWeek' )
	{
		const weekDaysWithoutDayOffs = FSCalendarRange.weekDays.filter( (day) => {
			return day.day_off == 0;
		} )

		startTime = weekDaysWithoutDayOffs.reduce( ( accumulator, current ) => {
			return current.start < accumulator.start ? current : accumulator
		} ).start
		endTime = weekDaysWithoutDayOffs.reduce( ( accumulator, current ) => {
			return current.end > accumulator.end ? current : accumulator
		} ).end
	}
	else
	{
		startTime = '00:00';
		endTime = '24:00'
	}


	if ( appointmentsInRange.length <= 0 )
	{
		FSCalendarRange.settingOption = true;

		FSCalendar.batchRendering( function()
		{
			FSCalendar.setOption('minTime', startTime);
			FSCalendar.setOption('maxTime', endTime);
		});

		return;
	}

	let appointmentMaxStartTime = booknetic.reformatTimeFromCustomFormat( appointmentsInRange.reduce( ( accumulator, current ) =>
	{
		return booknetic.reformatTimeFromCustomFormat( accumulator.start_time ) > booknetic.reformatTimeFromCustomFormat( current.start_time ) ? current : accumulator
	}).start_time );

	let appointmentMaxEndTime = booknetic.reformatTimeFromCustomFormat( appointmentsInRange.reduce( ( accumulator, current ) =>
	{
		return booknetic.reformatTimeFromCustomFormat( accumulator.end_time ) > booknetic.reformatTimeFromCustomFormat( current.end_time ) ? accumulator : current
	}).end_time );

	startTime = startTime > appointmentMaxStartTime ? appointmentMaxStartTime : startTime;
	endTime = endTime > appointmentMaxEndTime ? endTime : appointmentMaxEndTime;


	FSCalendarRange.settingOption = true;

	FSCalendar.batchRendering( function()
	{
		FSCalendar.setOption('minTime', startTime);
		FSCalendar.setOption('maxTime', endTime);
	});
}

function closeFiltersPopover()
{
	$(".advanced_filters_popover").hide();
	$('.advanced_filters select').each(function(){
		if($(this).select2("val").length > 0){
			$(".advanced_filters_btn>.filter_status").show();
			return false;
		}
		else{
			$('.advanced_filters_btn .filter_status').hide();
		}
	})
}

(function ($)
{
	"use strict";

	$(document).ready(function()
	{

		$(".advanced_filters_popover select").each(function(){
			let $select = $(this);
			let select2Options = {
				theme: 'bootstrap',
				width: '100%',
				placeholder: booknetic.__('select'),
				closeOnSelect: false,
				multiple: true,
			};

			// Add decorator adapter
			select2Options.dataAdapter = createCustomSelect2DataAdapter();

			$select.select2(select2Options);

			function createCustomSelect2DataAdapter()
			{
				// Build dependencies
				let ArrayAdapter = $.fn.select2.amd.require("select2/data/array");
				let Utils = $.fn.select2.amd.require("select2/utils");

				function CustomArrayAdapter($element, options)
				{
					CustomArrayAdapter.__super__.constructor.call(this, $element, options);
				}

				Utils.Extend(CustomArrayAdapter, ArrayAdapter);

				// Add sorting
				CustomArrayAdapter.prototype.current = function (callback)
				{
					let data = [];

					this.$element.find(":selected").each(
						$.proxy(function (i, element) {
							let $option = $(element);
							let option = this.item($option);

							data.push(option);
						}, this)
					);

					// Sort by addedOn timestamp
					data = data.sort(function (a, b) {
						return a._addedOn - b._addedOn;
					});

					callback(data);
				};

				// Add timestamp
				CustomArrayAdapter.prototype.select = function (data)
				{
					data._addedOn = new Date();

					return CustomArrayAdapter.__super__.select.call(this, data);
				};

				// Remove timestamp
				CustomArrayAdapter.prototype.unselect = function (data)
				{
					data._addedOn = undefined;

					return CustomArrayAdapter.__super__.unselect.call(this, data);
				};

				return CustomArrayAdapter;
			}
		})

		$('[data-toggle="tooltip"]').tooltip();

		$(document).on('click','.advanced_filters_btn', function(){
			let popup = $( '.advanced_filters_popover' );

			if ( popup.is( ':hidden' ) )
				popup.show();
			else
				popup.hide();
		}).on('click', function (e) {
			if ($('.advanced_filters_popover').css('display') !== 'block')
				return;
			if($(e.target).closest(".advanced_filters").length === 0
				&& $(e.target).closest(".select2-results").length === 0
				&& !$(e.target).hasClass('select2-selection__choice__remove')){
				$(".advanced_filters_popover select").select2("close");
				closeFiltersPopover();
			}
		}).on('click','.advanced_filters_popover_head .close_btn', function(){
			closeFiltersPopover();
		}).on('click','.clear_filters_btn', function (){
			$('.filter select').each(function(){
				$(this).val(null).trigger('change')
			});
		}).on('click','.save_filters_btn',function(){
			reloadCalendarFn();
			closeFiltersPopover();
		}).on('change', '.advanced_filters select', function(){
			if($(this).val().length == 0){
				$(this).parent().find('.clear_select').hide()
			}else{
				$(this).parent().find('.clear_select').show()
			}
		}).on('click','.filter .clear_select', function(){
			$(this).parent().find('.select2-hidden-accessible').val(null).trigger('change')
		}).on('click', '.create_new_appointment_btn', function ()
		{
			booknetic.loadModal('appointments.add_new', {});
		}).on('click', '.fc-body .fc-content-skeleton table thead td, .fc-body .fc-bg table td', function (){
			var date = $(this).closest('td').data('date');
			booknetic.loadModal('appointments.add_new', {date});
		}).on('click', '.fc-body .fc-content-skeleton table tbody td', function (){
			if( $( this ).hasClass( 'fc-more-cell' ) || FSCalendar.view.type !== 'dayGridMonth' )
				return;
			const index = $(this).index();
			var date = $(this).closest('table').find('thead').find('td:eq('+index+')').data('date');
			booknetic.loadModal('appointments.add_new', {date});
		}).on('mouseenter', '.fc-view-container td', function()
		{
			let index = $(this).index();
			let td;

			if( $( this ).hasClass( 'fc-more-cell' ) || $( this ).hasClass( 'fc-event-container' ) )
				return;

			if($(this).closest('.fc-week').find('.fc-content-skeleton').find('tbody').find('td:eq('+index+')').hasClass('fc-event-container'))
			{
				td = $(this).parents('table:eq(0)').find('thead').find('tr').find('td:eq('+index+')');
				if(typeof td.attr('data-date') == 'undefined')
				{
					td = $(this).parents('table:eq(0)').find('tbody').find('tr').find('td:eq('+index+')');
					if(typeof td.attr('data-date') == 'undefined')
						return false;
				}
				td.append('<div class="add-appointment-on-calendar"><a title="'+booknetic.__('new_appointment')+'"><svg xmlns="http://www.w3.org/2000/svg" width="17" height="16" viewBox="0 0 17 16" fill="none">\n' +
					'  <path d="M8.57145 3.33301V12.6663M3.90479 7.99967H13.2381" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>\n' +
					'</svg></a></div>');
			}
			else
			{
				td = $(this).closest('.fc-week').find('.fc-bg').find('td:eq('+index+')');
				td.append('<div class="add-appointment-on-calendar centered"><a class="p-0" title="'+booknetic.__('new_appointment')+'"><svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 25 25" fill="none">\n' +
						'  <path d="M12.1428 5.5V19.5M5.14282 12.5H19.1428" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>\n' +
						'</svg></a></div>');
			}
		}).on('mouseleave', '.fc-view-container td', function() {
			const index = $(this).index();
			let td;
			if($(this).closest('.fc-week').find('.fc-content-skeleton').find('tbody').find('td:eq('+index+')').hasClass('fc-event-container'))
			{
				td = $(this).parents('table:eq(0)').find('thead').find('tr').find('td:eq('+index+')');
				if(typeof td.attr('data-date') == 'undefined')
				{
					td = $(this).parents('table:eq(0)').find('tbody').find('tr').find('td:eq('+index+')');
					if(typeof td.attr('data-date') == 'undefined')
						return false;
				}
			}
			else
			{
				td = $(this).closest('.fc-week').find('.fc-bg').find('td:eq('+index+')');
			}
			td.find('.add-appointment-on-calendar').remove();
		});

		if( timeFormat === 'H:i' )
		{
			var timeFormatObj = {
				hour:   '2-digit',
				minute: '2-digit',
				hour12: false,
				meridiem: false
			};
		}
		else
		{
			timeFormatObj = {
				hour:   'numeric',
				minute: '2-digit',
				omitZeroMinute: true,
				meridiem: 'short'
			};
		}

		let month_names = [
			booknetic.__( 'January' ),
			booknetic.__( 'February' ),
			booknetic.__( 'March' ),
			booknetic.__( 'April' ),
			booknetic.__( 'May' ),
			booknetic.__( 'June' ),
			booknetic.__( 'July' ),
			booknetic.__( 'August' ),
			booknetic.__( 'September' ),
			booknetic.__( 'October' ),
			booknetic.__( 'November' ),
			booknetic.__( 'December' )
		];

		let short_month_names = [
			booknetic.__( 'Jan' ),
			booknetic.__( 'Feb' ),
			booknetic.__( 'Mar' ),
			booknetic.__( 'Apr' ),
			booknetic.__( 'May' ),
			booknetic.__( 'Jun' ),
			booknetic.__( 'Jul' ),
			booknetic.__( 'Aug' ),
			booknetic.__( 'Sep' ),
			booknetic.__( 'Oct' ),
			booknetic.__( 'Nov' ),
			booknetic.__( 'Dec' )
		];

		FSCalendar = new FullCalendar.Calendar( $("#fs-calendar")[0],
		{
			header: {
				left: 'prev,today,next',
				center: 'title',
				right: 'dayGridMonth,timeGridWeek,resourceTimeGridDay,listWeek'
			},
			schedulerLicenseKey: '0793382538-fcs-1637838415',
			defaultView: 'dayGridMonth',
			resources: function (info , success , error) {
				success(FSCalendarResources);
			},
			plugins: [ 'interaction', 'dayGrid', 'resourceTimeGrid', 'list' ],
			editable: false,
			eventDurationEditable: false,
			eventDragStart: function( appointment )
			{
				if ( appointment.event.extendedProps.service_name === 'gc_event' )
				{
					appointment.draggable = false;
					appointment.editable = false;
				}
			},
			eventDrop: function(appointment)
			{
				const text = [
					booknetic.__( 'reschedule_appointment_confirm' ),
					'<div class="pt-2"><input type="checkbox" id="input_run_workflows" checked=""> <label for="input_run_workflows" class="font-size-14 text-secondary">' + booknetic.__("run_workflow_reschedule") + '</label></div>'
				];

				booknetic.confirm( text, 'success', 'time', function()
				{
					booknetic.addAction( 'ajax_after_reschedule_appointment_unsuccess', () => appointment.revert() );

					const params = {
						appointment_id: appointment.event.extendedProps.appointment_id,
						new_date_time: appointment.event.start.toISOString(),
						trigger_workflows: $('#input_run_workflows').is(':checked') ? 1 : 0,
					}

					if ( appointment.oldResource && appointment.newResource )
					{
						params['staff_id'] = appointment.newResource.id;
					}

					booknetic.ajax( 'reschedule_appointment', params, function( response )
					{
						reloadCalendarFn();

						booknetic.toast( booknetic.__( 'rescheduled_successfully' ) );
					});

				}, booknetic.__( 'reschedule' ), booknetic.__( 'cancel' ), true, () => appointment.revert() );

			},
			dir: booknetic.isRtl() ? 'rtl' : 'ltr',
			eventLimit: 2,
			navLinks: true,
			firstDay: weekStartsOn === 'monday' ? 1 : 0,
			allDayText: booknetic.__( 'all-day' ),
			listDayFormat: function ( date )
			{
				let week_days = [
					booknetic.__( "Sunday" ),
					booknetic.__( "Monday" ),
					booknetic.__( "Tuesday" ),
					booknetic.__( "Wednesday" ),
					booknetic.__( "Thursday" ),
					booknetic.__( "Friday" ),
					booknetic.__( "Saturday" )
				];

				return week_days[ date.date.marker.getUTCDay() ]
			},
			listDayAltFormat: function ( date )
			{
				return month_names[ date.date.marker.getUTCMonth() ] + ' ' + date.date.marker.getUTCDate() + ', ' + date.date.marker.getUTCFullYear();
			},

			slotLabelFormat : timeFormatObj,
			slotDuration: '00:15:00',
			slotLabelInterval: 15,

			datesRender: function()
			{
				// if calendar new loads...
				if( typeof FSCalendarRange.start == 'undefined' )
				{
					reloadCalendarFn();
					return;
				}

				reloadCalendarHours();

				var activeRange	=	FSCalendar.state.dateProfile.activeRange,
					startDate	=	new Date( activeRange.start.getUTCFullYear() + '-' + booknetic.zeroPad(parseInt(activeRange.start.getUTCMonth())+1) + '-' + booknetic.zeroPad(activeRange.start.getUTCDate()) ),
					endDate		=	new Date( activeRange.end.getUTCFullYear() + '-' + booknetic.zeroPad(parseInt(activeRange.end.getUTCMonth())+1) + '-' + booknetic.zeroPad(activeRange.end.getUTCDate()) );

				// if old range, then break
				if( ( FSCalendarRange.start.getTime() <= startDate.getTime() && FSCalendarRange.end.getTime() >= startDate.getTime() ) && ( FSCalendarRange.start.getTime() <= endDate.getTime() && FSCalendarRange.end.getTime() >= endDate.getTime() ) )
					return;

				reloadCalendarFn();
			},

			eventRender: function(info)
			{
				var data = info.event.extendedProps;
				var html = '<div class="calendar_cart" style="color: '+data.text_color+';">';
				html += '<div class="calendar_event_line_1">' + data.start_time + ' - ' + data.end_time + '</div>';

				if( data.service_name === 'gc_event')
					html += '<div class="cart_staff_line calendar_event_line_2"><div class="circle_image"><img src="' + data.gc_icon + '"></div> ' + data.event_title + '</div>';
				else
					html += '<div class="calendar_event_line_2">' + data.service_name + '</div>';

				if( data.customers_count == 1 && data.status )
				{
					data.status.icon = data.status.icon.replace('times-circle', 'times');
					data.status.icon = data.status.icon.replace('fa fa-clock', 'far fa-clock');
					html += '<div class="calendar_event_line_3">' + data.customer + ' <span class="appointment-status-default" style="background-color: ' + data.status.color + '"><i class="' + data.status.icon + '"></i></span></div>';
				}
				else if ( data.service_name !== 'gc_event' )
				{
					html += '<div class="calendar_event_line_3">' + booknetic.__('group_appointment') + '</div>';
				}

				html += '<div class="cart_staff_line calendar_event_line_4"><div class="circle_image"><img src="' + data.staff_profile_image + '"></div> ' + data.staff_name + '</div>';
				html += '</div>';

				if( data.duration <= 59 * 60 && (info.view.type === 'timeGridWeek' || info.view.type === 'resourceTimeGridDay' ) )
				{
					html = $(html);

					if( data.duration <= 29 * 60 )
					{
						html.tooltip({
							html: true,
							title: '<div class="calendar_tooltip">' + html[0].outerHTML + '</div>',
							container: $(info.el)
						});

						html.find('.calendar_event_line_4').hide();
					}
					if( data.duration <= 19 * 60 )
					{
						html.find('.calendar_event_line_3').hide();
					}
					if( data.duration <= 14 * 60 )
					{
						html.find('.calendar_event_line_2').hide();
					}

					html.addClass('calendar_mini_event');
				}

				$(info.el).find('.fc-time').html('').hide();

				$(info.el).find('.fc-title').css('width', '100%').empty();
				$(html).appendTo( $(info.el).find('.fc-title') );
			},
			eventPositioned: function(info)
			{
				var data = info.event.extendedProps;

				//Waiting-List patch.
				//When the add-on ( waiting-list ) turned off, status obj of the given appointment becomes null, as the
				//hook written inside the add-on is not triggered ( does: inserts the data )
				//todo: refactor the waiting-list so the related data according to the appointment should be stored independently
				if ( data.status == null )
					return;

				if( data.customers_count == 1 )
				{
					var htmlCustomer = '<div>' + data.customer + ' <span class="appointment-status-'+data.status.color+'"><i class="' + data.status.icon + '"></i></span>' + '</div>';
				}
				else
				{
					htmlCustomer = '<div>' + booknetic.__( 'group_appointment' ) + '</div>';
				}

				$(info.el).find('.fc-list-item-title').after('<td>'+htmlCustomer+'</td>');
				$(info.el).find('.fc-list-item-title').after('<td class="fc-list-item-staff"><div><div class="circle_image"><img src="' + data.staff_profile_image + '"></div> ' + data.staff_name + '</div></td>');

				$(info.view.el).find('.fc-widget-header').attr('colspan', $(info.el).children('td').length);
			},
			eventClick: function (info)
			{
				if ( info.event.extendedProps[ 'non_clickable' ] )
					return

				var id = info.event.extendedProps['appointment_id'];

				if (id !== 0) {
					booknetic.loadModal('appointments.info', {id: id});
					info.jsEvent.stopPropagation();
				}
			},

			buttonText: {
				today:  booknetic.__('TODAY'),
				month:  booknetic.__('month'),
				week:   booknetic.__('week'),
				day:    booknetic.__('day'),
				list:   booknetic.__('list')
			},

			titleFormat: function( date )
			{
				let start       = date.date.marker;
				let end         = date.end.marker;
				let diff_days   = Math.round( ( end.getTime() - start.getTime() ) / ( 1000 * 60 * 60 * 24 ) );

				if( diff_days >= 28 ) // month view
				{
					return month_names[start.getUTCMonth()] + ' ' + start.getUTCFullYear();
				}
				else if( diff_days === 1 )
				{
					return month_names[ start.getUTCMonth() ] + ' ' + start.getUTCDate() + ', ' + start.getUTCFullYear();
				}

				return short_month_names[ start.getUTCMonth() ] + ' ' + start.getUTCDate() + ( start.getUTCFullYear() == end.getUTCFullYear() ? '' : ( ', ' + start.getUTCFullYear() ) ) + ' - ' + ( start.getUTCMonth() == end.getUTCMonth() ? '' : ( short_month_names[ end.getUTCMonth() ] + ' ' ) ) + end.getUTCDate() + ', ' + end.getUTCFullYear();
			},
			columnHeaderText: function ( date )
			{
				let week_days = [
					booknetic.__( 'Sun' ),
					booknetic.__( 'Mon' ),
					booknetic.__( 'Tue' ),
					booknetic.__( 'Wed' ),
					booknetic.__( 'Thu' ),
					booknetic.__( 'Fri' ),
					booknetic.__( 'Sat' )
				];

				if( FSCalendar.view.type === 'timeGridWeek' )
				{
					return week_days[ date.getDay() ] + ' ' + month_names[ date.getMonth() ] + ' ' + date.getDate();
				}

				return week_days[ date.getDay() ]
			},
			eventLimitText: booknetic.__( 'more' )
		});

		FSCalendar.setOption( 'locale', fcLocale );
		FSCalendar.render();

		if( $( '.starting_guide_icon' ).css( 'display' ) !== 'none' )
		{
			$('.create_new_appointment_btn').css({right: '125px'})
		}
	});

})(jQuery);

