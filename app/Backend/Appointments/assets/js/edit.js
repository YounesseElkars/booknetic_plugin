(function ($)
{
	"use strict";

	$(document).ready(function()
	{
		booknetic.select2Ajax( $(".fs-modal #input_service"), 'appointments.get_services', function()
		{
			return {
				category: $(".fs-modal .input_category:eq(-1)").val()
			}
		});

		booknetic.select2Ajax( $(".fs-modal .input_category"), 'appointments.get_service_categories', function(select)
		{
			return {
				category: select.parent().prev().children('select').val()
			}
		});

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
				id: $('#add_new_JS').data('appointment-id'),
				service: service,
				location: location,
				service_extras: JSON.stringify(collectExtras()),
				staff: staff,
				date: date
			}
		});

		booknetic.select2Ajax( $(".fs-modal .customers_area .input_customer"), 'appointments.get_customers'  );


		function constructNumbersOfGroup(t)
		{
			var serviceInf = $(".fs-modal #input_service").select2('data')[0];

			if( !serviceInf )
			{
				booknetic.toast('Please firstly choose a service!', 'unsuccess');
				return;
			}

			var sumOfSelectedNums = 0;

			$(".fs-modal .customers_area .c_number").each(function ()
			{
				sumOfSelectedNums += parseInt( $(this).text() );
			});

			var maxCapacity	= ('max_capacity' in serviceInf ? serviceInf['max_capacity'] : $('#add_new_JS').data('max-capacity')) - sumOfSelectedNums + parseInt( t.children('.c_number').text() ),
				rows		= '';

			for( var i = 1; i <= (maxCapacity > 0 ? maxCapacity : 1); i++ )
			{
				rows += '<a class="dropdown-item" href="#">' + i + '</a>';
			}

			t.next('.number_of_group_customers_panel').html( rows );
		}

		function loadServiceExtras ()
		{
			$( '#tab_extras' ).empty();

			let service_id 	= $( '.fs-modal #input_service' ).val();

			booknetic.ajax( 'appointments.get_service_extras', { appointment_id: $( '#add_new_JS' ).data( 'appointment-id' ), service_id }, function ( result )
			{
				$( '#tab_extras' ).html( booknetic.htmlspecialchars_decode( result[ 'html' ] ) )
			} );
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


		var staffFirstChange = true;

		$(".fs-modal").on('change', '.input_category', function()
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
		}).on('change', '#input_location', function ()
		{
			$(".fs-modal #input_staff").select2( 'val', false );
		}).on('change', '#input_service', function ()
		{
			$(".fs-modal #input_staff").select2( 'val', false );

			loadServiceExtras()

		}).on('change', '#input_staff', function ()
		{
			$(".fs-modal #input_date").attr('disabled', ( !$(this).val() ) )
			$(".fs-modal #input_time").attr('disabled', ( !$(this).val() ) )
			if( ! staffFirstChange )
			{
				$(".fs-modal #input_date").val('');
				$(".fs-modal #input_time").empty().trigger('change');
			}
			if( staffFirstChange )
			{
				staffFirstChange = false;
			}
		}).on('change', '#input_date', function ()
		{
			if( first_change )
			{
				first_change = false;
				return;
			}

			$(".fs-modal #input_time").select2('val', false);
			$(".fs-modal #input_time").trigger('change');
		}).on('click', '.number_of_group_customers_panel > a', function ()
		{
			var num = $(this).text().trim();

			$(this).closest('.number_of_group_customers_panel').prev().children('.c_number').text( num );
		}).on('click', '.customer-status-panel [data-status]', function ()
		{
			$(this).closest('.customer-status-panel').prev().attr('data-status', $(this).attr('data-status') );
			$(this).closest('.customer-status-panel').prev().children('i').attr('class', $(this).children('i').attr('class') );
			$(this).closest('.customer-status-panel').prev().children('i').attr('style', $(this).children('i').attr('style'));
			$(this).closest('.customer-status-panel').prev().children('.c_status').text($(this).text().trim() );
		}).on('click', '#addAppointmentSave', function()
		{

			if ( typeof priceUpdated === 'undefined' || priceUpdated == 0 )
			{
				addAppointmentSave();
			}
			else
			{
				booknetic.confirm( booknetic.__('update_appointment_prices'), 'info', 'starting_guide', () =>
				{
					booknetic.addFilter( 'appointments.save_edited_appointment.cart', ( obj ) =>
					{
						obj[ 'change_prices' ] = 1;
						return obj;
					})

					addAppointmentSave();
				}, booknetic.__( 'update' ), booknetic.__('dont'), true, () =>
				{
					booknetic.addFilter( 'appointments.save_edited_appointment.cart', ( obj ) =>
					{
						obj[ 'change_prices' ] = 0;
						return obj;
					})

					addAppointmentSave();
				} );
			}

			function addAppointmentSave()
			{
				var location			=	$("#input_location").val(),
					service				=	$("#input_service").val(),
					staff				=	$("#input_staff").val(),
					date				=	$("#input_date").val(),
					time				=	$("#input_time").val(),
					note				=	$("#note").val(),
					customer_id				=	$('.customers_area .input_customer').val(),
					status					=	$('.customers_area .customer-status-btn > button').attr('data-status'),
					weight					=	$('.customers_area .c_number').text(),
					run_workflows		=	$("#input_run_workflows").is(':checked') ? 1 : 0,
					extras				=	collectExtras();

				if( staff == '' || service == '' || customer_id == '' )
				{
					booknetic.toast('Please fill all required fields!', 'unsuccess');
					return;
				}

				let appointmentValidation = booknetic.doFilter('appointments.validation', false);
				if( appointmentValidation !== false )
				{
					booknetic.toast(appointmentValidation, 'unsuccess');
					return;
				}

				var data = new FormData();
				let obj = {};

				obj['id']=  $('#add_new_JS').data('appointment-id');
				obj['location']=  location;
				obj['service']=  service;
				obj['staff']=  staff;
				obj['date']=  date;
				obj['time']=  booknetic.reformatTimeFromCustomFormat( time ) ;
				obj['note']=  note;
				obj['customer_id']=  customer_id ;
				obj['status']=  status ;
				obj['weight']=  weight ;
				obj['service_extras']=  extras;

				data.append('run_workflows', run_workflows);
				data.append('current' , 0);

				obj = booknetic.doFilter('appointments.save_edited_appointment.cart' , obj, data );
				data.append('cart' , JSON.stringify([obj]));

				booknetic.ajax( 'appointments.save_edited_appointment', data, function(result)
				{
					if( currentModule == 'appointments' )
					{
						booknetic.modalHide($(".fs-modal"));
						booknetic.dataTable.reload( $("#fs_data_table_div") );
					}
					else if ( typeof reloadCalendarFn !== 'undefined' && currentModule == 'calendar' )
					{
						booknetic.modalHide($(".fs-modal"));
						reloadCalendarFn(); //so the visuals could be updated accordingly
					}
					else
					{
						booknetic.loading(1);
						window.location.reload();
					}
				});

			}
		});

		$(".fs-modal #input_staff").trigger('change');

		var first_change = true;
		$(".fs-modal #input_date").datepicker({
			autoclose: true,
			format: dateFormat.replace('YYYY','Y').replace('Y', 'yyyy')
				.replace('MM', 'm').replace('m', 'mm')
				.replace('DD','d').replace('d', 'dd'),
			weekStart: weekStartsOn == 'sunday' ? 0 : 1
		});

		$('.fs-modal .customers_area .number_of_group_customers').on('click', function ()
		{
			constructNumbersOfGroup( $(this) );
		});

		
		loadServiceExtras()

	});

})(jQuery);