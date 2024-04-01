(function ($)
{
	"use strict";

	$(document).ready(function()
	{

		$(document).on('click', '#addBtn', function()
		{
			booknetic.loadModal('add_new', {});
		});

		booknetic.dataTable.actionCallbacks['edit'] = function (ids)
		{
			booknetic.loadModal('add_new', {'id': ids[0]});
		};

		booknetic.dataTable.actionCallbacks['share'] = function (ids)
		{
			booknetic.loadModal('Base.direct_link', {'location_id': ids[0]} , { type:'center' });
		}

	});

})(jQuery);