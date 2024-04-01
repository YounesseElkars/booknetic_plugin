(function ($)
{
	"use strict";

	function mapCenter( duration )
	{
		var zoom = parseInt( $(".zoom-select > .selected-option").text() ) / 100;

		var areaWidth		= $( "#services_map" ).outerWidth(),
			areaHeight		= $( "#services_map" ).outerHeight(),
			mapWidth		= $("#categories_tree > ul > li").outerWidth(),
			mapHeight		= $("#categories_tree > ul > li").outerHeight();

		var leftCenter	= parseInt((areaWidth - mapWidth) / 2);
		var topCenter	= parseInt((areaHeight - mapHeight) / 3);

		duration = typeof duration == 'undefied' ? 300 : duration;

		if($('body').hasClass('rtl'))
		{
			$( "#categories_tree" ).animate({top: (topCenter * zoom) + 'px', right: (leftCenter * zoom) + 'px'}, duration);
		}
		else
		{
			$( "#categories_tree" ).animate({top: (topCenter * zoom) + 'px', left: (leftCenter * zoom) + 'px'}, duration);
		}
	}

	$(document).ready(function()
	{

		$(".goto-center").click(mapCenter);

		var horizontalProp = booknetic.isRtl() ? 'right' : 'left'

		$(document).on('click', '.zoom-select > a', function ()
		{
			if( $(this).is('.selected-option') )
			{
				return;
			}

			var percent = parseInt( $(this).text() );

			$(".zoom-select > .selected-option").removeClass('selected-option').children('i').remove();

			$(this).addClass('selected-option').append('<i class="fa fa-check"></i>');

			$("#zoom-dropdown").text( percent + '%' );

			$( "#categories_tree" ).css( 'transform', 'scale(' + ( parseInt( percent / 10 ) / 10 ) + ')' );
		}).on('click', '#zoom-out', function ()
		{
			if( $(".zoom-select > .selected-option").prev('a').length )
			{
				$(".zoom-select > .selected-option").prev('a').click();
			}
		}).on('click', '#zoom-in', function ()
		{
			if( $(".zoom-select > .selected-option").next('a').length )
			{
				$(".zoom-select > .selected-option").next('a').click();
			}
		}).on('click', '.drag-right-arrow', function ()
		{
			var left = parseInt($( "#categories_tree" ).css(horizontalProp).replace('px', '')) - 70;

			$( "#categories_tree" ).stop().animate({[horizontalProp]: left + 'px'}, 200);
		}).on('click', '.drag-left-arrow', function ()
		{
			var left = parseInt($( "#categories_tree" ).css(horizontalProp).replace('px', '')) + 70;

			$( "#categories_tree" ).stop().animate({[horizontalProp]: left + 'px'}, 200);
		}).on('click', '.drag-top-arrow', function ()
		{
			var top = parseInt($( "#categories_tree" ).css('top').replace('px', '')) + 70;

			$( "#categories_tree" ).stop().animate({'top': top + 'px'}, 200);
		}).on('click', '.drag-bottom-arrow', function ()
		{
			var top = parseInt($( "#categories_tree" ).css('top').replace('px', '')) - 70;

			$( "#categories_tree" ).stop().animate({'top': top + 'px'}, 200);
		}).on('mouseup', '#categories_tree .remove_node', function(e)
		{
			if (e.which != 1) return false;
			var li		= $(this).closest('li'),
				id		= li.data('id'),
				ul		= li.parent('ul'),
				type	= li.data('type');

			if( type == 'service' )
			{
				var message = booknetic.__('delete_service');
				var ajaxParam = 'service_delete';
			}
			else
			{
				var message = booknetic.__('delete_category');
				var ajaxParam = 'category_delete';
			}

			booknetic.confirm(message, 'danger', 'trash', function()
			{
				booknetic.ajax(ajaxParam, {'id': id} , function()
				{
					li.remove();

					if( ul.children('li').length === 0 )
						ul.remove();

					if( type == 'service' )
					{
						$('#services_count_badge').text( parseInt( $('#services_count_badge').text().trim() ) - 1 )
					}
				});
			});

		}).on('mouseup', '#categories_tree .cancel_node', function(e)
		{
			if (e.which != 1) return false;
			var li	= $(this).closest('li'),
				id	= li.data('id'),
				ul	= li.parent('ul');

			li.remove();

			if( ul.children('li').length === 0 )
				ul.remove();

		}).on('mouseup', '#categories_tree .save_node', function(e)
		{
			if (e.which != 1) return false;
			var li		= $(this).closest('li'),
				id		= li.data('id') || 0,
				parent	= li.parent().closest('li').data('id'),
				name	= li.find(' > .node_details > .node_name > input').val().trim();

			booknetic.ajax('category_save', {'id': id, 'name': name, 'parent_id': parent}, function(result )
			{
				li.find(' > .node_details .node_name').text(name).attr('title', name);

				li.attr('data-id', result['id']);

				li.find(' > .node_details .save_node').remove();

				li.children('.node_details').append('<span class="edit_node"></i></span>');

				if( id == 0 )
				{
					li.children('.node_details').append('<span class="add_new_node"></span>');
					li.children('.node_details').append('<span class="remove_node"></span>');
					li.find(' > .node_details .cancel_node').remove();
				}
			});

		}).on('mouseup', '#categories_tree .edit_node', function(e)
		{
			if (e.which != 1) return false;
			var li		= $(this).closest('li'),
				type	= li.data('type'),
				id		= li.data('id') || 0;

			if( type == 'service' )
			{
				booknetic.loadModal('add_new', {'id': id});
			}
			else
			{
				var parent	= li.parent().closest('li').data('id'),
					name	= li.find(' > .node_details > .node_name').text().trim();

				name	= li.find(' > .node_details > .node_name').text().trim();

				li.find(' > .node_details > .node_name').html('<span class="edit_categ_name_span">&nbsp;</span><input class="form-control" type="text" placeholder="' + booknetic.__('category_name') + '">');
				li.find(' > .node_details > .node_name > input').val( name ).focus();

				$(this).remove();
				li.children('.node_details').append('<span class="save_node"></span>');
			}

		}).on( 'mouseup', '#categories_tree .node_translations', function ( e ) {
			if (e.which != 1) return false;
			var li		= $(this).closest('li'),
				type	= li.data('type'),
				id		= li.data('id') || 0;

			if ( type !== 'category' || id === 0 ) return false;

			booknetic.defaultTranslatingValue = li.find( '.node_details .node_name' ).first().text()

			booknetic.loadModal( 'Base.get_translations', {
				row_id: id,
				table: 'service_categories',
				column: 'name',
				node: 'input'
			} )

		} ).on('click', '#categories_tree .add_new_service_btn', function()
		{
			var li	= $(this).closest('li'),
				id	= li.data('id');

			booknetic.loadModal('add_new', {'type': 'simple', 'category_id': 0});
		}).on('mouseup', '#categories_tree .add_new_node', function( e )
		{
			if (e.which != 1) return false;
			var li			= $(this).closest('li'),
				id			= li.data('id'),
				childType	= li.find(' > ul > li[data-type]').length > 0 ? li.find(' > ul > li[data-type]').data('type') : '';

			if( childType == 'service' )
			{
				booknetic.loadModal('add_new', {'type': 'simple', 'category_id': id});
			}
			else if( childType == 'category' || li.data('type') == 'root' )
			{
				addChildCategory( li );
			}
			else
			{
				var x = e.pageX,
					y = e.pageY;

				$("#select_add_type").show().css({ top: y + 35, left: x - 26 }).data('categ_id', id);
			}

		}).on('click', '#select_add_type [data-type]', function ()
		{
			var type	= $(this).data('type'),
				id		= $("#select_add_type").data('categ_id');

			if( type == 'service' )
			{
				booknetic.loadModal('add_new', {'type': 'simple', 'category_id': id});
			}
			else
			{
				var li = $("#categories_tree li[data-id='" + id + "']");

				addChildCategory( li );
			}
		}).on('click', function(e)
		{
			if( $(e.target).not('.add_new_node') && !$(e.target).closest('.add_new_node').length )
			{
				$("#select_add_type").hide();
			}
		});

		mapCenter( 0 );

		$( "#categories_tree" ).draggable(
			{cancel:'.add_new_node, .edit_node, .save_node, .cancel_node, .remove_node'}
		).animate( {opacity: 1}, 200 );
		//$( ".add_new_node" ).draggable( { cancel: true }  );

		$('.tooltip-it').tooltip();

	});


	function addChildCategory( li )
	{
		if( li.children('ul').length )
		{
			li.children('ul').append('<li data-type="category"><div class="node_details"><span class="node_name"><span class="edit_categ_name_span">&nbsp;</span><input class="form-control" type="text" placeholder="' + booknetic.__('category_name') + '"></span><span class="save_node"></span><span class="cancel_node"></span></div></li>');
		}
		else
		{
			li.append('<ul data-type="vertical"><li data-type="category"><div class="node_details"><span class="node_name"><span class="edit_categ_name_span">&nbsp;</span><input class="form-control" type="text" placeholder="' + booknetic.__('category_name') + '"></span><span class="save_node"></span><span class="cancel_node"></span></div></li></ul>');
		}

		li.children('ul').children('li:eq(-1)').find(' > .node_details > .node_name > input').focus();
	}

})(jQuery);
