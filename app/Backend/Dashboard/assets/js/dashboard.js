(function ($)
{
	"use strict";

	$(document).ready(function()
	{

		$(document).on('click', '#date_buttons .date_button', function ()
		{
			if( $(this).hasClass('active_btn') )
				return;


			$("#date_buttons .date_button.active_btn").removeClass('active_btn');
			$(this).addClass('active_btn');

			var type = $(this).data('type');

			if( type == 'custom' )
			{
				$(".custom_date_range").parent().fadeIn(200);

				return;
			}
			else
			{
				$(".custom_date_range").parent().fadeOut(200);
			}

			loadStatisticData( type );
		});

		dateFormat = dateFormat.replace('Y', 'YYYY')
			.replace('m', 'MM')
			.replace('d', 'DD');

		$(".custom_date_range").daterangepicker({
			opens: 'left',
			locale: {
				format: dateFormat, // "YYYY-MM-DD",
				separator: " - ",
				applyLabel: booknetic.__('Apply'),
				cancelLabel: booknetic.__('Cancel'),
				fromLabel: booknetic.__('From'),
				toLabel: booknetic.__('To'),
				customRangeLabel: "Custom",
				daysOfWeek: [
					booknetic.__("Sun"),
					booknetic.__("Mon"),
					booknetic.__("Tue"),
					booknetic.__("Wed"),
					booknetic.__("Thu"),
					booknetic.__("Fri"),
					booknetic.__("Sat")
				],
				monthNames: [
					booknetic.__("January"),
					booknetic.__("February"),
					booknetic.__("March"),
					booknetic.__("April"),
					booknetic.__("May"),
					booknetic.__("June"),
					booknetic.__("July"),
					booknetic.__("August"),
					booknetic.__("September"),
					booknetic.__("October"),
					booknetic.__("November"),
					booknetic.__("December")
				],
				firstDay: 1
			},
			startDate: new Date(),
			endDate: new Date(),
			cancelClass: "btn-outline-secondary"
		}, function(start, end, label)
		{
			loadStatisticData( 'custom', start.format(dateFormat), end.format(dateFormat) );
		});

		function loadStatisticData( type, startDate, endDate )
		{

			booknetic.ajax('Dashboard.get_stat', {type: type, start: startDate, end: endDate}, function( result )
			{
				$("#statistic-boxes-area .box-number-div[data-stat='appointments']").text( result['appointments'] );
				$("#statistic-boxes-area .box-number-div[data-stat='duration']").text( result['duration'] );
				$("#statistic-boxes-area .box-number-div[data-stat='revenue']").text( result['revenue'] );
				$("#statistic-boxes-area .box-number-div[data-stat='customers']").text( result['customers'] );

				$('.dashboard-appointments').each(function () {
					let element = $(this).find('.appointment-stats');
					let status = element.attr('data-stat').replace('status-','');
					element.text( result['count_by_status'][status] !== undefined ? result['count_by_status'][status]['count'] : 0);
				})
			});

		}

		loadStatisticData('today');


		function loadGraphData( year )
		{

			booknetic.ajax('Dashboard.get_graph_data', { year }, function( result )
			{
				document.querySelector('#graph').innerHTML = booknetic.htmlspecialchars_decode(result.html);
			});

		}

		$('body').on('mouseover' , 'svg rect' ,function (e) {
			$(".graph_info_popup").html($(this).attr('data-count') + " " + booknetic.__("bookings_on") + " " + $(this).attr('data-date') );
			let jsNode = document.querySelector('.graph_info_popup');
			$(".graph_info_popup").show();
			$(".graph_info_popup").css({
				"left" : e.pageX - jsNode.offsetWidth / 2,
				"top"  : e.pageY - jsNode.offsetHeight - 10
			});
		});

		$('body').on('mouseout' , 'svg rect' ,function () {
			$(".graph_info_popup").hide();
		});

		$(".graph-btns .date_buttons_span .date_button").on("click" , function () {
			$(".graph-btns .date_buttons_span .date_button.active").removeClass('active');
			$(this).addClass('active');
			let year  = $(this).attr('data-type');
			loadGraphData( year );
		});

	});

})(jQuery);