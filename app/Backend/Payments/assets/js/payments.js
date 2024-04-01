(function ($)
{
	"use strict";

	$(document).ready(function()
	{
		booknetic.dataTable.actionCallbacks['info'] = function (ids)
		{
			booknetic.loadModal('info', {'id': ids[0]});
		}

			

	});

})(jQuery);