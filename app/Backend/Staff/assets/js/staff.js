(function ($)
{
	"use strict";

	$(document).ready(function()
	{

		booknetic.dataTable.actionCallbacks['edit'] = function (ids)
		{
			booknetic.loadModal('add_new', {'id': ids[0]});
		}

		booknetic.dataTable.actionCallbacks['delete'] = function (ids)
		{
			let d = booknetic.can_delete_associated_account ? '<div class="mt-3"> <input type="checkbox" id="input_delete_staff_wp_user" checked><label for="input_delete_staff_wp_user">'+booknetic.__('delete_associated_wordpress_account')+'</label> </div>' : '';

			booknetic.confirm([ booknetic.__('are_you_sure_want_to_delete'), d], 'danger', 'trash', function(modal)
			{
				let ajaxData = {
					'delete_wp_user': booknetic.can_delete_associated_account ? (modal.find('#input_delete_staff_wp_user').is(':checked') ? 1 : 0) : 0
				};

				booknetic.dataTable.doAction('delete', ids, ajaxData, function ()
				{
					booknetic.toast(booknetic.__('Deleted'), 'success', 2000);
				});
			});
		}

		booknetic.dataTable.actionCallbacks['share'] = function (ids)
		{
			booknetic.loadModal('Base.direct_link', {'staff_id': ids[0]} , { type:'center' });
		}

		$(document).on('click', '#addBtn', function ()
		{
			booknetic.loadModal('add_new', {});
		});

		var js_parameters = $('#staff-js12394610');

		if( js_parameters.data('edit') > 0 )
		{
			booknetic.loadModal('add_new', {'id': js_parameters.data('edit')});
		}

	});

})(jQuery);