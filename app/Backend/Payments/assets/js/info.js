(function ($)
{
	"use strict";

	$(document).ready(function()
	{

		$('.fs-modal').on('click', '.complete-payment', function ()
		{
			var payment_id = $('#info_modal_JS').data('payment-id');

			booknetic.ajax('payments.complete_payment', {id: payment_id}, function ()
			{
				booknetic.reloadModal( $('#info_modal_JS').data('mn') );
				booknetic.dataTable.reload( $("#fs_data_table_div") );
			});

		});

	});

})(jQuery);