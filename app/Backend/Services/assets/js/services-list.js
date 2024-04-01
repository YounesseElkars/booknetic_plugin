(function ($)
{
	"use strict";

	$(document).ready(function()
	{

		$('.m_head_actions').prepend('<button type="button" class="btn btn-primary btn-lg" id="addCategoryBtn"><i class="fa fa-plus"></i> '+booknetic.__('add_category')+'</button>');
		$('.m_head_actions').prepend('<a href="?page=' + BACKEND_SLUG + '&module=services&action=edit_order" type="button" class="btn btn-primary btn-lg" id="editOrderBtn"><i class="fa fa-arrows-alt mr-2" aria-hidden="true"></i> '+ booknetic.__( "edit_order" ) +'</a>');
		$('.m_head_actions').prepend('<a href="?page=' + BACKEND_SLUG + '&module=services&view=org" type="button" class="btn btn-outline-secondary btn-lg">'+booknetic.__('graphic_view')+'</a>');

		$(document).on('click', '#addBtn', function()
		{
			booknetic.loadModal('add_new', {});
		}).on('click', '#addCategoryBtn', function()
		{
			booknetic.loadModal('add_new_category', {'id': 0});
		});

		booknetic.dataTable.actionCallbacks['edit'] = function (ids)
		{
			booknetic.loadModal('add_new', {'id': ids[0]});
		}

		booknetic.dataTable.actionCallbacks['share'] = function (ids)
		{
			booknetic.loadModal('Base.direct_link', {'service_id': ids[0]} , { type:'center' });
		}
	});

})(jQuery);