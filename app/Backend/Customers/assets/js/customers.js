(function ($)
{
	"use strict";

	$(document).ready(function()
	{

		$(document).on('click', '#addBtn', function ()
		{
			booknetic.loadModal('add_new', {});
		}).on('click', '#importBtn', function ()
		{
			booknetic.loadModal('import', {}, {'type': 'center', 'width': '650px'});
		});

		booknetic.dataTable.actionCallbacks['info'] = function (ids)
		{
			booknetic.loadModal('info', {'id': ids[0]});
		}

		booknetic.dataTable.actionCallbacks['edit'] = function (ids)
		{
			booknetic.loadModal('add_new', {'id': ids[0]});
		}

		booknetic.dataTable.actionCallbacks['delete'] = function (ids)
		{
			let d = booknetic.can_delete_associated_account ? '<div class="mt-3"> <input type="checkbox" id="input_delete_customer_wp_user" checked><label for="input_delete_customer_wp_user">'+booknetic.__('delete_associated_wordpress_account')+'</label> </div>' : '';
			d = (isSaaSVersion != undefined && isSaaSVersion===true) ? '' : d;
			booknetic.confirm([ booknetic.__('are_you_sure_want_to_delete'), d], 'danger', 'trash', function(modal)
			{
				let ajaxData = {
					'delete_wp_user': booknetic.can_delete_associated_account ? ( modal.find('#input_delete_customer_wp_user').is(':checked') ? 1 : ((isSaaSVersion != undefined && isSaaSVersion===true) ? 1 : 0) ) : 0
				};

				booknetic.dataTable.doAction('delete', ids, ajaxData, function ()
				{
					booknetic.toast(booknetic.__('Deleted'), 'success', 2000);
				});
			});
		}

	});

})(jQuery);