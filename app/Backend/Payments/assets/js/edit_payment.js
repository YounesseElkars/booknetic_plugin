(function ($)
{
	"use strict";

	$(document).ready(function()
	{
		$(".fs-modal").on('click', '#addPaymentButton', function()
		{
			var prices          =   {},
				paid_amount		=	$(".fs-modal #input_paid_amount").val(),
				status			=	$(".fs-modal #input_payment_status").val();

			if( paid_amount == '' )
			{
				booknetic.toast('Please fill all required fields!', 'unsuccess');
				return;
			}

			$('.fs-modal .prices-section [data-price-id]').each(function ()
			{
				prices[ $(this).data('price-id') ] = $(this).val();
			});

			var data = new FormData();

			data.append('id', $('#add_new_JS_payment1').data('payment-id'));
			data.append('prices', JSON.stringify( prices ));
			data.append('paid_amount', paid_amount);
			data.append('status', status);

			booknetic.ajax( 'payments.save_payment', data, function()
			{
				booknetic.modalHide( $('#add_new_JS_payment1').closest(".fs-modal") );
				booknetic.reloadModal( $('#add_new_JS_payment1').data('mn2') );
				booknetic.dataTable.reload( $("#fs_data_table_div") );
			});
		});

	});

})(jQuery);