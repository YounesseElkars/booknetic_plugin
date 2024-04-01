(function ($)
{
	"use strict";

	$(document).ready(function ()
	{

		$('#customer_import_modal').on('click', '#addCustomerSave', function()
		{
			var fields		= [],
				delimiter	= $("#input_delimiter").val(),
				input_csv	= $("#input_csv")[0].files[0];

			$("#customer_import_form input[name='fields[]']:checked").map(function()
			{
				fields.push($(this).val());
			});

			if( fields.length === 0 || !input_csv )
			{
				booknetic.toast(booknetic.__('fill_all_required'), 'unsuccess');
				return;
			}

			var data = new FormData();

			data.append('fields', fields.join(','));
			data.append('delimiter', delimiter);
			data.append('csv', input_csv);

			booknetic.ajax( 'import_customers', data, function()
			{
				booknetic.modalHide($("#customer_import_form").closest('.modal'));

				booknetic.dataTable.reload( $("#fs_data_table_div") );
			});
		});

	});

})(jQuery);