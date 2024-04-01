(function ($)
{
	"use strict";

	$(document).ready(function()
	{
		const serviceId = $("#add_new_JS").data('service-id');

		booknetic.initMultilangInput( $( "#input_name" ), 'services', 'name' );
		booknetic.initMultilangInput( $( "#input_note" ), 'services', 'note' );

		booknetic.select2Ajax( $('.fs-modal .break_line:not(:eq(-1)) .break_start, .fs-modal .break_line:not(:eq(-1)) .break_end, .fs-modal .special-day-row:not(:eq(-1)) .input_special_day_start, .fs-modal .special-day-row:not(:eq(-1)) .input_special_day_end'), 'get_available_times_all');

		booknetic.select2Ajax( $('#input_duration'), 'get_times_with_format', function ()
		{
			return {
				exclude_zero: true
			};
		});

		booknetic.select2Ajax( $( '#input_time_slot_length' ), 'get_times_with_format', function ()
		{
			return {
				exclude_zero: true,
				include_defaults: true
			};
		} );

		booknetic.select2Ajax( $('#input_buffer_before, #input_buffer_after'), 'get_times_with_format');

		booknetic.select2Ajax( $('#input_timesheet_1_start, #input_timesheet_2_start, #input_timesheet_3_start, #input_timesheet_4_start, #input_timesheet_5_start, #input_timesheet_6_start, #input_timesheet_7_start, #input_timesheet_1_end, #input_timesheet_2_end, #input_timesheet_3_end, #input_timesheet_4_end, #input_timesheet_5_end, #input_timesheet_6_end, #input_timesheet_7_end'), 'get_available_times_all');

		var extraPictureImg;
		var extraCategories = [];
		booknetic.ajax('get_extra_for_create_modal', {}, function (result )
		{
			extraPictureImg = result['image'];
			extraCategories = result['extra_categories'];
		});

		const extraServiceModal = function (data) {
			booknetic.modal(
				`
					<div class="modal-body new_extra_modal_body">
						<div id="new_extra_panel" data-id="${data?.id ?? 0}">
					
						<div class="extra_picture_div">
							<div class="extra_picture">
								<input type="file" id="input_image2">
								<div class="img-circle1"><img src="${data?.image ?? extraPictureImg}" data-src="${extraPictureImg}"></div>
							</div>
						</div>
					
						<div class="form-row">
							<div class="form-group col-md-6">
								<label for="input_extra_name">${booknetic.__('service_name')}<span class="required-star">*</span></label>
								<input class="form-control required" value="${data?.name ?? ''}" data-multilang-fk="${data?.id ?? ''}" data-multilang="true" id="input_extra_name" maxlength="100">
							</div>
							<div class="form-group col-md-3">
								<label for="input_extra_min_quantity">${booknetic.__('min_quantity')}</label>
								<i class="fa fa-info-circle help-icon do_tooltip" data-content="${booknetic.__('default_zero_means_there_is_no_minimum_requirement')}" data-original-title="" title=""></i>
								<input type="number" value="${data?.min_quantity ?? 0}" class="form-control" id="input_extra_min_quantity" min="0">
							</div>
							<div class="form-group col-md-3">
								<label for="input_extra_max_quantity">${booknetic.__('max_quantity')}</label>
								<input type="number" value="${data?.max_quantity ?? 1}" class="form-control" id="input_extra_max_quantity" min="1">
							</div>
						</div>
					
						<div class="form-row">
							<div class="form-group col-md-6">
								<label for="input_extra_category">${booknetic.__('category')}</label>
								<i class="fa fa-info-circle help-icon do_tooltip" data-content="${booknetic.__('to_add_a_category_enter_name_and_press_enter')}" data-original-title="" title=""></i>
								<select class="form-control" id="input_extra_category">		
									<option></option>
									${
										extraCategories.map( ( extraCategory ) => {
											
											var extraCategorySelected = "";
											if ( data?.category_id == extraCategory?.id ) {
												extraCategorySelected = "selected";
											}
											
											return "<option value='" + extraCategory?.id + "' " + extraCategorySelected + ">" + extraCategory?.name + "</option>"
										} )
									}
								</select>
							</div>
						</div>
					
						<div class="form-row">
							<div class="form-group col-md-6">
								<label for="input_extra_price">${booknetic.__('price')} <span class="required-star">*</span></label>
								<input class="form-control required" id="input_extra_price" value="${data?.price ?? ''}">
							</div>
							<div class="form-group col-md-6">
								<label>&nbsp;</label>
								<div class="form-control-checkbox">
									<label for="input_extra_hide_price">${booknetic.__('hide_price_booking_panel')}</label>
									<div class="fs_onoffswitch">
										<input type="checkbox" class="fs_onoffswitch-checkbox" id="input_extra_hide_price" ${data?.hide_price == 1 ? 'checked' : ''}>
										<label class="fs_onoffswitch-label" for="input_extra_hide_price"></label>
									</div>
								</div>
							</div>
						</div>
					
						<div class="form-row">
							<div class="form-group col-md-6">
								<label for="input_extra_duration">${booknetic.__('duration')}</label>
								<select class="form-control" id="input_extra_duration">
									${
										data?.duration ?
											'<option value="' + data?.duration +'">'+ data?.duration_txt + '</option>'
											: ''
									}
								</select>
							</div>
							<div class="form-group col-md-6">
								<label>&nbsp;</label>
								<div class="form-control-checkbox">
									<label for="input_extra_hide_duration">${booknetic.__('hide_duration_booking_panel')}</label>
									<div class="fs_onoffswitch">
										<input type="checkbox" class="fs_onoffswitch-checkbox" id="input_extra_hide_duration" ${data?.hide_duration == 1 ? 'checked' : ''}>
										<label class="fs_onoffswitch-label" for="input_extra_hide_duration"></label>
									</div>
								</div>
							</div>
						</div>
					
						<div class="form-row">
							<div class="form-group col-md-12">
								<label for="input_extra_note">${booknetic.__('note')}</label>
								<textarea data-multilang-fk="${data?.id ?? ''}" data-multilang="true" maxlength="1000" id="input_extra_note" class="form-control">${data?.notes ?? ''}</textarea>
							</div>
						</div>
					
						<div class="form-row">
							<div class="form-group col-md-12">
								<button type="button" class="btn btn-default new_extra_panel_cancel_btn mr-2" data-dismiss="modal">${booknetic.__('cancel')}</button>
								<button type="button" class="btn btn-success new_extra_panel_save_btn">${booknetic.__('save_extra')}</button>
							</div>
						</div>
					</div>
					</div>
				`,
				{type: 'center', width: 60}
			);

			$( '.new_extra_modal_body #input_extra_category' ).select2( {
				theme: 'bootstrap',
				placeholder: booknetic.__( 'select' ),
				tags: true,
				allowClear: true,
				templateResult: formatOption
			} );

			function formatOption( option )
			{
				if ( !option.id )
				{
					return option.text;
				}

				return $( `<span>${option.text}<i class="far fa-trash-alt delete_extra_category" style="position: absolute; right: 5px; padding-top: 3px;" data-id="${option.id}"></i></span>` );
			}

			$.fn.modal.Constructor.prototype._enforceFocus = function(){}

			booknetic.initMultilangInput( $( "#input_extra_name" ), 'service_extras', 'name' );
			booknetic.initMultilangInput( $( "#input_extra_note" ), 'service_extras', 'notes' );

			booknetic.select2Ajax( $('.new_extra_modal_body #input_extra_duration'), 'get_times_with_format' );

			registerExtraModalEvents()
		};

		$('.fs-modal').on('click', '#new_extra_btn', function ()
		{
			extraServiceModal();
		}).on('click', '.new_extra_panel_cancel_btn', function ()
		{

			$("#new_extra_panel").fadeOut(200, function()
			{
				$("#extra_list_area").fadeIn(200);
				$("#new_extra_btn").fadeIn(200);
			});

		}).on('click', '.new_extra_panel_save_btn', function ()
		{
			var extra_id		= $(".fs-modal #new_extra_panel").data('id'),
				name			= $(".fs-modal #input_extra_name").val(),
				category_id     = $('.fs-modal #input_extra_category').val(),
				duration		= $(".fs-modal #input_extra_duration").val(),
				hide_duration	= $(".fs-modal #input_extra_hide_duration").is(':checked')?1:0,
				price			= $(".fs-modal #input_extra_price").val(),
				hide_price		= $(".fs-modal #input_extra_hide_price").is(':checked')?1:0,
				min_quantity	= $(".fs-modal #input_extra_min_quantity").val(),
				max_quantity	= $(".fs-modal #input_extra_max_quantity").val(),
				extra_notes	    = $(".fs-modal #input_extra_note").val(),
				image			= $(".fs-modal #input_image2")[0].files[0];

			var data = new FormData();

			data.append('id', extra_id);
			data.append('service_id',  serviceId );
			data.append('name', name);
			data.append('category_id', category_id);
			data.append('duration', duration);
			data.append('hide_duration', hide_duration);
			data.append('price', price);
			data.append('hide_price', hide_price);
			data.append('min_quantity', min_quantity);
			data.append('max_quantity', max_quantity);
			data.append('extra_notes', extra_notes);
			data.append('image', image);
			data.append('translations', booknetic.getTranslationData( $( '#new_extra_panel' ) ));

			booknetic.ajax( 'save_extra' , data, function(result )
			{
				let modal = $(this).closest( '.modal' );
				var newId = result['id'];

				if( extra_id > 0 )
				{
					var row_that_data_must_change = $(".fs-modal #extra_list_area").children('.extra_row[data-id="' + extra_id + '"]');
				}
				else
				{
					var extraTpl = $(".fs-modal .extra_row:eq(-1)")[0].outerHTML;

					if( $(".fs-modal #extra_list_area > .extra_row").length > 0 )
					{
						$(".fs-modal #extra_list_area > .extra_row:eq(-1)").after( extraTpl );
					}
					else
					{
						$(".fs-modal #extra_list_area").prepend( extraTpl );
					}

					var row_that_data_must_change = $(".fs-modal #extra_list_area").children('.extra_row:eq(-1)');
					row_that_data_must_change.attr('data-id', newId);
				}

				row_that_data_must_change.hide();
				row_that_data_must_change.find('[data-tag="name"]').text( name );
				row_that_data_must_change.find('[data-tag="duration"]').text( result['duration'] );
				row_that_data_must_change.find('[data-tag="price"]').text( result['price'] );
				row_that_data_must_change.find('[data-tag="max_quantity"]').text( max_quantity );
				row_that_data_must_change.find('[data-tag="min_quantity"]').text( min_quantity );

				$(".fs-modal #new_extra_panel").fadeOut(200, function()
				{
					row_that_data_must_change.fadeIn(300);
					$(".fs-modal #extra_list_area").fadeIn(200);
					$(".fs-modal #new_extra_btn").fadeIn(400);
				});

				booknetic.modalHide( modal );

				booknetic.toast(booknetic.__('saved_successfully'), 'success');
			});
		}).on('click', '.delete_extra', function()
		{
			var extraRow	= $(this).closest('.extra_row'),
				extraId		= extraRow.data('id');

			booknetic.confirm(booknetic.__('delete_service_extra'), 'danger', 'trash', function()
			{
				booknetic.ajax('delete_extra', {id: extraId}, function ()
				{
					extraRow.slideUp(200, function()
					{
						$(this).remove();
					});
				});
			});
		}).on('click', '.copy_to_parent_services', function()
		{
			var extraRow	= $(this).closest('.extra_row'),
				extraId		= extraRow.data('id');

			booknetic.ajax('copy_extras', {val: 1, extraId: extraId}, function(res)
			{
				booknetic.toast(res.msg);
			});
			
		}).on('click', '.copy_to_all_services', function()
		{
			var extraRow	= $(this).closest('.extra_row'),
				extraId		= extraRow.data('id');

			booknetic.ajax('copy_extras', {val: 0, extraId: extraId}, function(res)
			{
				booknetic.toast(res.msg);
			});
			
		}).on('click', '.hide_extra', function()
		{
			var extraRow	= $(this).closest('.extra_row'),
				status      = extraRow.attr('data-active') == 1 ? 0 : 1,
				extraId		= extraRow.data('id');

			booknetic.ajax('hide_extra', {id: extraId, status: status}, function ()
			{
				extraRow.attr('data-active', status);
				if( status == 1 )
				{
					extraRow.find('.hide_extra').attr('src', extraRow.find('.hide_extra').attr('src').replace(/view\.svg$/, 'hide.svg'));
				}
				else
				{
					extraRow.find('.hide_extra').attr('src', extraRow.find('.hide_extra').attr('src').replace(/hide\.svg$/, 'view.svg'));
				}
			});
		}).on('click', '.edit_extra', function()
		{
			var extraRow	= $(this).closest('.extra_row'),
				extraId		= extraRow.data('id');

			booknetic.ajax('get_extra_data', {id: extraId}, function (result )
			{
				extraServiceModal( result );
			});
		}).on('click', '.timesheet_tabs > div', function()
		{
			var type = $(this).data('type');

			if( $(this).hasClass('selected-tab') )
				return;

			$(".fs-modal .timesheet_tabs > .selected-tab").removeClass('selected-tab');

			$(this).addClass('selected-tab');

			$(".fs-modal #tab_timesheet [data-tstab]").hide();
			$(".fs-modal #tab_timesheet [data-tstab='" + type + "']").removeClass('hidden').hide().fadeIn(200);
		}).on('click', '.copy_time_to_all', function()
		{
			let startEl   = $(".fs-modal #input_timesheet_1_start"),
				endEl     = $(".fs-modal #input_timesheet_1_end"),
				start	  = startEl.val(),
				startText = startEl.select2('data')[0]['text'],
				end		  = endEl.val(),
				endText   = endEl.select2('data')[0]['text'],
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

					$(".breaks_area[data-day='"+i+"'] .break_start:eq("+ index +")").append(breakStartOption).trigger('change');
					$(".breaks_area[data-day='"+i+"'] .break_end:eq("+ index +")").append(breakEndOption).trigger('change');
				});

				$(".fs-modal .breaks_area[data-day='"+i+"'] .break_line").removeClass('hidden');
				$(".fs-modal #dayy_off_checkbox_"+i).prop('checked', dayOff).trigger('change');
			}
		}).on('click', '#addServiceForm .delete-break-btn', function ()
		{
			$(this).closest('.break_line').slideUp(200, function()
			{
				$(this).remove();
			});
		}).on('change', '#tab_staff .change_price_checkbox', function ()
		{
			if( $(this).is(':checked') )
			{
				$(this).closest('.form-group').next().removeClass('hidden').fadeIn(200);
			}
			else
			{
				$(this).closest('.form-group').next().fadeOut(200);
			}
		}).on('click', '#tab_staff .delete-employee-btn', function()
		{
			$(this).closest('.form-row').slideUp(200, function()
			{
				$(this).remove();
			});
		}).on('click', '#tab_staff .add-employee-btn', function()
		{
			var employeeCount = $(".fs-modal #tab_staff > .staff_list_area > .employee-tpl").length;

			if( employeeCount >= $("#add_new_JS").data('staff-count') )
			{
				booknetic.toast(booknetic.__('no_more_staff_exist'), 'unsuccess');
				return;
			}

			if( $('.fs-modal #tab_staff > .staff_list_area .before-employee-select-form').length > 0 )
			{
				booknetic.toast(booknetic.__('choose_staff_first'), 'unsuccess');
				return;
			}

			const clone = $('.fs-modal .before-employee-select-form').last().clone();

			$('.fs-modal .staff_list_area .selected-employee').map( ( _, option ) =>
			{
				clone.find('option[value="' + $(option).attr('data-staff-id') + '"]').remove()
			})

			clone.find('.before-employee-select').select2({
				theme:			'bootstrap',
				placeholder:	booknetic.__('select_staff'),
				allowClear:		true,
			});


			$(".fs-modal #tab_staff > .staff_list_area").append(clone).children().last().slideDown(200);

		}).on('change', '.before-employee-select', function()
		{
			let form 				= $(this).closest('.before-employee-select-form'),
				selectedOptionVal 	= form.find('.before-employee-select').val();

			if ( ! selectedOptionVal )
			{
				booknetic.toast(booknetic.__('staff_empty'), 'unsuccess');
				return;
			}

			let selectedOptionText = form.find(`option[value=${selectedOptionVal}]`).text();


			$(this).closest('.before-employee-select-form').slideUp(200, function()
			{
				$(this).remove();
			})

			let employeeTpl = $(".fs-modal .employee-tpl:eq(-1)")[0].outerHTML.replace(/change_price_checkbox_[0-9]/g, 'change_price_checkbox_' + (++startCount));
			let employee = $(".fs-modal #tab_staff > .staff_list_area").append( employeeTpl ).find(' > .employee-tpl:eq(-1)')

			employee.find('.employee_select').append( $(`<div class="selected-employee" data-staff-id="${selectedOptionVal}">${selectedOptionText}</div>`) )

			employee.removeClass('hidden').hide().slideDown(200);

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
			area.find(' > .break_line:eq(-1)').removeClass('hidden').hide().slideDown(200);

			booknetic.select2Ajax( area.find(' > .break_line:eq(-1) .break_start'), 'get_available_times_all');
			booknetic.select2Ajax( area.find(' > .break_line:eq(-1) .break_end'), 'get_available_times_all');
		}).on('click', '.add-special-day-btn', function ()
		{
			var specialDayTpl = $(".fs-modal .special-day-row:eq(-1)")[0].outerHTML;

			$(".fs-modal .special-days-area").append( specialDayTpl );

			var lastRow = $(".fs-modal .special-days-area > .special-day-row:last");

			var date_format_js = lastRow.find('.input_special_day_date').data('date-format').replace('Y','yyyy').replace('m','mm').replace('d','dd');


			lastRow.find('.input_special_day_date').datepicker({
				autoclose: true,
				format: date_format_js,
				weekStart: weekStartsOn == 'sunday' ? 0 : 1
			});

			booknetic.select2Ajax( lastRow.find('.input_special_day_start'), 'get_available_times_all');
			booknetic.select2Ajax( lastRow.find('.input_special_day_end'), 'get_available_times_all');

			lastRow.removeClass('hidden').hide().slideDown(300);
		}).on('click', '.remove-special-day-btn', function ()
		{
			var spRow = $(this).closest('.special-day-row');
			booknetic.confirm( booknetic.__('delete_special_day'), 'danger', 'unsuccess', function()
			{
				spRow.slideUp(300, function()
				{
					spRow.remove();
				});
			});
		}).on('click', '.special-day-add-break-btn', function()
		{
			var area = $(this).closest('.special-day-row').find('.special_day_breaks_area');
			var breakTpl = $(".fs-modal .break_line:eq(-1)")[0].outerHTML;

			area.append( breakTpl );
			area.find(' > .break_line:eq(-1)').removeClass('hidden').hide().slideDown(200);

			booknetic.select2Ajax( area.find(' > .break_line:eq(-1) .break_start'), 'get_available_times_all');
			booknetic.select2Ajax( area.find(' > .break_line:eq(-1) .break_end'), 'get_available_times_all');
		}).on('change', '#repeatable_checkbox', function ()
		{

			if( $(this).is(':checked') )
			{
				$(".fs-modal [data-for='repeat']").slideDown( $(this).data('slideSpeed') || 0 );
			}
			else
			{
				$(".fs-modal [data-for='repeat']").slideUp( $(this).data('slideSpeed') || 0 );
			}

			$(this).data('slideSpeed', 200);

		}).on('change', '#deposit_checkbox', function ()
		{

			if( $(this).is(':checked') )
			{
				$(".fs-modal [data-for='deposit']").slideDown( $(this).data('slideSpeed') || 0 );
			}
			else
			{
				$(".fs-modal [data-for='deposit']").slideUp( $(this).data('slideSpeed') || 0 );
			}

			$(this).data('slideSpeed', 200);

		}).on('change', '#group_booking_checkbox', function ()
		{

			if( $(this).is(':checked') )
			{
				$(".fs-modal #group_booking_area").slideDown(200);
			}
			else
			{
				$(".fs-modal #group_booking_area").slideUp(200);

			}

		}).on('change', '#recurring_fixed_full_period, #recurring_fixed_frequency', function ()
		{

			if( $(this).is(':checked') )
			{
				$(this).closest('.form-group').next().fadeIn(200);
			}
			else
			{
				$(this).closest('.form-group').next().fadeOut(200);
			}

		}).on('change', '#input_recurring_type', function ()
		{
			var selectedType = $(this).val();

			var text = '';
			switch( selectedType )
			{
				case 'monthly':
					text = booknetic.__('times_per_month');
					break;
				case 'weekly':
					text = booknetic.__('times_per_week');
					break;
				case 'daily':
					text = booknetic.__('every_n_day');
					break;
			}

			$(".fs-modal .repeat_frequency_txt").text( text );

		}).on('click', '#addServiceSave', function ()
		{
			var name					= $(".fs-modal #input_name").val(),
				category				= $(".fs-modal #input_category").val(),

				duration				= $(".fs-modal #input_duration").val(),
				timeslot_length			= $(".fs-modal #input_time_slot_length").val(),

				price					= $(".fs-modal #input_price").val(),
				deposit_enabled 		= $("#deposit_checkbox").is(':checked') ? 1 : 0,
				deposit					= $(".fs-modal #input_deposit").val(),
				deposit_type			= $(".fs-modal #input_deposit_type").val(),
				hide_price			    = $(".fs-modal #input_hide_price").is(':checked') ? 1 : 0,
				hide_duration			= $(".fs-modal #input_hide_duration").is(':checked') ? 1 : 0,

				buffer_before			= $(".fs-modal #input_buffer_before").val(),
				buffer_after			= $(".fs-modal #input_buffer_after").val(),

				repeatable				= $(".fs-modal #repeatable_checkbox").is(':checked') ? 1 : 0,

				fixed_full_period		= $(".fs-modal #recurring_fixed_full_period").is(':checked') ? 1 : 0,
				full_period				= !fixed_full_period ? '' : $(".fs-modal #input_full_period").val(),
				full_period_type		= !fixed_full_period ? '' : $(".fs-modal #input_full_period_type").val(),

				repeat_type				= $(".fs-modal #input_recurring_type").val( ),
				recurring_payment_type	= $(".fs-modal #input_recurring_payment_type").val( ),

				fixed_frequency			= $(".fs-modal #recurring_fixed_frequency").is(':checked') ? 1 : 0,
				repeat_frequency		= !fixed_frequency ? '' : $(".fs-modal #input_repeat_frequency").val(),

				capacity				= $(".fs-modal #select_capacity").val(),
				max_capacity			= capacity === '0' ? 1 : $(".fs-modal #input_max_capacity").val(),

				employees				= [],
				note					= $(".fs-modal #input_note").val(),
				image					= $(".fs-modal #input_image")[0].files[0],
				color					= $(".fs-modal .service_color").data('color'),
				only_visible_to_staff   = $("#service_settings_custom_only_visible_to_staff").is(':checked') ? 1 : 0,
				minimum_time_required_prior_booking = $( '#input_min_time_req_prior_booking' ).val(),
				available_days_for_booking = $( '#input_available_days_for_booking' ).val()
			;

			if( name === '' || category === '' || duration === '' || price === '' || deposit === '' )
			{
				booknetic.toast(booknetic.__('fill_all_required'), 'unsuccess');
				return;
			}

			$(".fs-modal .staff_list_area > .employee-tpl").each(function()
			{
				var employeeId			= $(this).find('.selected-employee').attr('data-staff-id'),
					priceIsStandart= $(this).find('.change_price_checkbox').is(':checked') ? 0 : 1,
					employeePrice		= priceIsStandart ? -1 : $(this).find('.except_price_input').val(),
					employeeDeposit		= priceIsStandart ? -1 : $(this).find('.except_deposit_input').val(),
					employeeDepositType	= $(this).find('.except_deposit_type_input').val();

				employees.push([ employeeId, employeePrice, employeeDeposit, employeeDepositType ]);
			});

			if( employees.length === 0 )
			{
				booknetic.toast(booknetic.__('choose_staff'), 'unsuccess');
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


			var data = new FormData();

			data.append('id', serviceId );
			data.append('name', name);
			data.append('category', category);
			data.append('duration', duration);
			data.append('timeslot_length', timeslot_length);

			data.append('price', price);
			data.append('deposit_enabled', deposit_enabled);
			data.append('deposit', deposit);
			data.append('deposit_type', deposit_type);
			data.append('hide_price', hide_price);
			data.append('hide_duration', hide_duration);

			data.append('buffer_before', buffer_before);
			data.append('buffer_after', buffer_after);

			data.append('repeatable', repeatable);

			data.append('fixed_full_period', fixed_full_period);
			data.append('full_period_value', full_period);
			data.append('full_period_type', full_period_type);

			data.append('repeat_type', repeat_type);
			data.append('recurring_payment_type', recurring_payment_type);

			data.append('fixed_frequency', fixed_frequency);
			data.append('repeat_frequency', repeat_frequency);

			data.append('max_capacity', max_capacity);

			data.append('employees', JSON.stringify( employees ));
			data.append('note', note);
			data.append('image', image);
			data.append('color', color);

			data.append('weekly_schedule', JSON.stringify( weekly_schedule ));
			data.append('special_days', JSON.stringify( special_days ));
			data.append('extras', getExtras() );

			data.append('only_visible_to_staff', only_visible_to_staff);

			data.append( 'custom_payment_methods_enabled', $( '#service_settings_custom_payment_methods_enabled' ).is(':checked') ? 1 : 0 );
			data.append( 'custom_payment_methods', $( '#service_settings_custom_payment_methods' ).val() );

			data.append( 'bring_people', $( "#bring_people" ).is(':checked') ? 1 : 0 );
			data.append( 'translations', booknetic.getTranslationData( $( "#tab_service_details" ) ) );
			data.append( 'minimum_time_required_prior_booking', minimum_time_required_prior_booking );
			data.append( 'available_days_for_booking', available_days_for_booking );

			booknetic.ajax('save_service', data, function()
			{
				booknetic.modalHide( $(".fs-modal") );

				location.reload();
			});

		}).on( 'click', '#addServiceClose', function () {
			if ( serviceId > 0 )
				return;

			booknetic.ajax( 'delete_extras', { 'extras': getExtras() } );
		} ).on('click', '.service_picture img', function ()
		{
			$('#input_image').click();
		}).on('change', '#input_image', function ()
		{
			if( $(this)[0].files && $(this)[0].files[0] )
			{
				var reader = new FileReader();

				reader.onload = function(e)
				{
					$('.fs-modal .service_picture img').attr('src', e.target.result);
				}

				reader.readAsDataURL( $(this)[0].files[0] );
			}
		}).on('click', '.service_picture > .service_color', function ()
		{
			var x = parseInt( $(".fs-modal .fs-modal-content").outerWidth() ) / 2 - $("#service_color_panel").outerWidth()/2,
				y = parseInt( $(this).offset().top ) + 60;

			$("#service_color_panel").css({top: y+'px', left: x+'px'}).fadeIn(200);
		}).on('click', '.extra_picture img', function ()
		{
			$('#input_image2').click();
		}).on('click', '#service_color_panel .color-rounded', function ()
		{
			$("#service_color_panel .color-rounded.selected-color").removeClass('selected-color');
			$(this).addClass('selected-color');

			var color = $(this).data('color');

			$("#input_color_hex").val( color );
		}).on('click', '#service_color_panel .close-btn1', function ()
		{
			$("#service_color_panel .close-popover-btn").click();
		}).on('click', '#service_color_panel .save-btn1', function ()
		{
			var color = $("#input_color_hex").val();

			$(".fs-modal .service_color").css('background-color', color).data('color', color);

			$("#service_color_panel .close-popover-btn").click();
		}).on('change', '#set_specific_timesheet_checkbox', function ()
		{
			if( $(this).is(':checked') )
			{
				$('#set_specific_timesheet').slideDown(200);
			}
			else
			{
				$('#set_specific_timesheet').slideUp(200);
			}
		}).on('change', '#select_capacity', function ()
		{
			if( $(this).val() === '0' )
			{
				$(this).parents('.form-row').find( "#max_capacity_form_group, #bring_people_form_group" ).fadeOut(200);
			}
			else
			{
				$(this).parents('.form-row').find( "#max_capacity_form_group, #bring_people_form_group" ).fadeIn(200);
			}
		}).on('click', '#hideServiceBtn', function ()
		{
			booknetic.ajax('hide_service', { service_id: serviceId }, function ()
			{
				booknetic.loading(1);
				location.reload();
			});
		}).on( 'change', '#service_settings_custom_payment_methods_enabled', function () {
			if ( $( this ).is( ':checked' ) )
			{
				$( '#serviceCustomPaymentMethodsContainer' ).slideDown( 200 );
			}
			else
			{
				$( '#serviceCustomPaymentMethodsContainer' ).slideUp( 200 );
			}
		} );

		const registerExtraModalEvents = function () {
			$( '.new_extra_modal_body' ).on( 'select2:selecting', '#input_extra_category', function ( e )
			{
				if ( e.params.args.originalEvent !== undefined && e.params.args.originalEvent.target.className.includes( 'delete_extra_category' ) )
				{
					e.preventDefault();

					let id = e.params.args.data.id;

					booknetic.confirm( booknetic.__( 'sure_to_delete_extra_category' ), 'danger', 'trash', function ()
					{
						booknetic.ajax( 'delete_extra_category', { id: id }, function ()
						{
							let inputElement = $( '#input_extra_category' );
							let inputOptionToRemove = inputElement.find( 'option[value="' + id + '"]' );

							if ( inputOptionToRemove.length > 0 )
							{
								inputOptionToRemove.remove();
								inputElement.trigger( 'change' );
							}

							extraCategories = extraCategories.filter( function ( category )
							{
								return category.id !== id;
							} );

							booknetic.toast( 'Extra category successfully deleted', 'success' );
						} );
					} );
				}
			} ).on( 'select2:select', '#input_extra_category', function ( e )
			{
				if ( ! e.params.data.hasOwnProperty( 'element' ) )
				{
					booknetic.ajax( 'add_new_extra_category', { name: e.params.data.text }, function ( result )
					{
						let newOption = new Option( e.params.data.text, result.id, true, true );

						$( '#input_extra_category' ).append( newOption ).trigger( 'change' );

						extraCategories.push( {
							'id': result.id,
							'name': e.params.data.text
						} );

						booknetic.toast( 'New extra category added', 'success' )
					} );
				}
			} ).on('click', '.new_extra_panel_save_btn', function ()
			{
				var extra_id		= $(".new_extra_modal_body #new_extra_panel").data('id'),
					name			= $(".new_extra_modal_body #input_extra_name").val(),
					category_id     = $('.new_extra_modal_body #input_extra_category').val(),
					duration		= $(".new_extra_modal_body #input_extra_duration").val(),
					hide_duration	= $(".new_extra_modal_body #input_extra_hide_duration").is(':checked')?1:0,
					price			= $(".new_extra_modal_body #input_extra_price").val(),
					hide_price		= $(".new_extra_modal_body #input_extra_hide_price").is(':checked')?1:0,
					min_quantity	= $(".new_extra_modal_body #input_extra_min_quantity").val(),
					max_quantity	= $(".new_extra_modal_body #input_extra_max_quantity").val(),
					extra_notes	    = $(".new_extra_modal_body #input_extra_note").val(),
					image			= $(".new_extra_modal_body #input_image2")[0].files[0];

				var data = new FormData();

				data.append('id', extra_id);
				data.append('service_id', serviceId);
				data.append('name', name);
				data.append('category_id', category_id);
				data.append('duration', duration);
				data.append('hide_duration', hide_duration);
				data.append('price', price);
				data.append('hide_price', hide_price);
				data.append('min_quantity', min_quantity);
				data.append('max_quantity', max_quantity);
				data.append('extra_notes', extra_notes);
				data.append('image', image);
				data.append('translations', booknetic.getTranslationData( $( '#new_extra_panel' ) ));

				booknetic.ajax( 'save_extra' , data, function(result )
				{
					var newId = result['id'];

					if( extra_id > 0 )
					{
						var row_that_data_must_change = $(".fs-modal #extra_list_area").children('.extra_row[data-id="' + extra_id + '"]');
					}
					else
					{
						var extraTpl = $(".fs-modal .extra_row:eq(-1)")[0].outerHTML;

						if( $(".fs-modal #extra_list_area > .extra_row").length > 0 )
						{
							$(".fs-modal #extra_list_area > .extra_row:eq(-1)").after( extraTpl );
						}
						else
						{
							$(".fs-modal #extra_list_area").prepend( extraTpl );
						}

						var row_that_data_must_change = $(".fs-modal #extra_list_area").children('.extra_row:eq(-1)');
						row_that_data_must_change.attr('data-id', newId);
					}

					row_that_data_must_change.hide();
					row_that_data_must_change.find('[data-tag="name"]').text( name );
					row_that_data_must_change.find('[data-tag="duration"]').text( result['duration'] );
					row_that_data_must_change.find('[data-tag="price"]').text( result['price'] );
					row_that_data_must_change.find('[data-tag="max_quantity"]').text( max_quantity );
					row_that_data_must_change.find('[data-tag="min_quantity"]').text( min_quantity );

					booknetic.modalHide($(".modal"));
					row_that_data_must_change.fadeIn(300);
					$(".fs-modal #extra_list_area").fadeIn(200);
					$(".fs-modal #new_extra_btn").fadeIn(400);

					booknetic.toast(booknetic.__('saved_successfully'), 'success');
				});
			}).on('click', '.extra_picture img', function ()
			{
				$('#input_image2').click();
			}).on('change', '#input_image2', function ()
			{
				if( $(this)[0].files && $(this)[0].files[0] )
				{
					var reader = new FileReader();

					reader.onload = function(e)
					{
						$('.new_extra_modal_body .extra_picture img').attr('src', e.target.result);
					}

					reader.readAsDataURL( $(this)[0].files[0] );
				}
			});
		}

		if( serviceId == 0 )
		{
			$('#tab_staff .add-employee-btn').click();
		}

		$( '.fs-modal #service_settings_custom_payment_methods_enabled' ).trigger( 'change' );

		$(".fs-modal #tab_staff .change_price_checkbox").trigger('change');

		$(".fs-modal #deposit_checkbox").trigger('change');

		$(".fs-modal #repeatable_checkbox").trigger('change');

		$(".fs-modal #input_employees, .fs-modal #input_category").select2({
			theme:			'bootstrap',
			placeholder:	booknetic.__('select'),
			allowClear:		true
		});

		$(".fs-modal #group_booking_checkbox").trigger('change');

		$('.fs-modal #recurring_fixed_full_period, .fs-modal #recurring_fixed_frequency').trigger('change');

		$(".fs-modal #input_recurring_type").trigger('change');

		var selectedCategory = $(".service_category.sc-selected").data('id');
		if( selectedCategory > 0 )
		{
			$(".fs-modal #input_category").val( selectedCategory ).trigger('change');
		}

		$(".fs-modal .dayy_off_checkbox").trigger('change');

		$("#input_color_hex").colorpicker({
			format: 'hex'
		});

		$('.fs-modal .service_picture .d-none').removeClass('d-none');

		$('#set_specific_timesheet_checkbox').trigger('change');

		$('#select_capacity').trigger('change');

		var date_format_js = $('.fs-modal .input_special_day_date').data('date-format').replace('Y','yyyy').replace('m','mm').replace('d','dd');

		$('.fs-modal .input_special_day_date').datepicker({
			autoclose: true,
			format: date_format_js,
			weekStart: weekStartsOn == 'sunday' ? 0 : 1
		});

		// settings tab
		$( '#service_settings_custom_payment_methods' ).select2( {
			theme:			'bootstrap',
			placeholder:	booknetic.__( 'select' ),
			allowClear:		true
		} );

		$("#input_min_time_req_prior_booking").select2({
			theme: 'bootstrap',
			placeholder: booknetic.__('select'),
			allowClear: true
		});

		function getExtras()
		{
			let extras = [];

			$( '#extra_list_area > .extra_row' ).each( function()
			{
				extras.push( $( this ).data( 'id' ) )
			} );

			return JSON.stringify( extras );
		}
	});

})(jQuery);