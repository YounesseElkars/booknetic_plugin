(function ($)
{
	"use strict";

	$(document).ready(function()
	{

		$('#booknetic_settings_area').on('click' , '.settings-save-btn', function ()
		{
			var holidays = [];

			var selectedHolidays = $(".yearly_calendar").data('calendar').getDataSource()
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

			booknetic.ajax('save_holidays_settings', {
				holidays: JSON.stringify(holidays)
			}, function ()
			{
				booknetic.toast(booknetic.__('saved_successfully'), 'success');
			});
		});

		$(".dayy_off_checkbox").trigger('change');

		$.fn.calendar.dates['en'] = {
			days: [booknetic.__("Sun"), booknetic.__("Mon"), booknetic.__("Tue"), booknetic.__("Wed"), booknetic.__("Thu"), booknetic.__("Fri"), booknetic.__("Sat"), booknetic.__("Sun")],
			daysShort: [booknetic.__("Sun"), booknetic.__("Mon"), booknetic.__("Tue"), booknetic.__("Wed"), booknetic.__("Thu"), booknetic.__("Fri"), booknetic.__("Sat"), booknetic.__("Sun")],
			daysMin: [booknetic.__("Sun"), booknetic.__("Mon"), booknetic.__("Tue"), booknetic.__("Wed"), booknetic.__("Thu"), booknetic.__("Fri"), booknetic.__("Sat"), booknetic.__("Sun")],
			months: [booknetic.__("January"), booknetic.__("February"), booknetic.__("March"), booknetic.__("April"), booknetic.__("May"), booknetic.__("June"), booknetic.__("July"), booknetic.__("August"), booknetic.__("September"), booknetic.__("October"), booknetic.__("November"), booknetic.__("December")],
			monthsShort: [booknetic.__("January"), booknetic.__("February"), booknetic.__("March"), booknetic.__("April"), booknetic.__("May"), booknetic.__("June"), booknetic.__("July"), booknetic.__("August"), booknetic.__("September"), booknetic.__("October"), booknetic.__("November"), booknetic.__("December")],
			weekShort: 'W',
			weekStart: weekStartsOn	== 'sunday' ? 0 : 1
		};

		$(".yearly_calendar").calendar({
			colspan: 'col-lg-6 col-xl-4',
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

				$(".yearly_calendar").data('calendar').setDataSource( holidaysArr( dbHolidays ) );
			},
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

	});

})(jQuery);