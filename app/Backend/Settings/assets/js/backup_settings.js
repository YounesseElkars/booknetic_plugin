(function ($)
{
	"use strict";

	$(document).ready(function()
	{

		$('#booknetic_settings_area').on('click', '#export_data_btn', function ()
		{
			booknetic.ajax('export_data', {}, function ( result )
			{
				location.href = '?page=' + BACKEND_SLUG + '&module=settings&download=1';
			});
		}).on('click', '#import_data_btn', function ()
		{
			var file_to_restore = $('#file_to_restore')[0].files[0];

			if( !file_to_restore )
			{
				booknetic.toast(booknetic.__('fill_all_required'), 'unsuccess');
				return;
			}

			booknetic.confirm( booknetic.__('are_you_sure'), 'danger', 'trash', function ()
			{
				var data = new FormData();

				data.append('file', file_to_restore);

				booknetic.ajax('import_data', data, function ()
				{
					location.reload();
				})

			}, booknetic.__('YES'));

		});

	});

})(jQuery);