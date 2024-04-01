(function ($)
{
	"use strict";

	$(document).ready(function()
	{

		$(window).resize(function ()
		{
			var t = $('.booknetic_appointment:eq(0)');

			var width = t.innerWidth();
			var parentWidth = $('.appearance_box_preview').innerWidth();

			var scale = parseInt(parentWidth / width * 100) / 100;
			var height = t.innerHeight() * scale;

			$('.booknetic_appointment').css('transform', 'scale(' + scale + ')');
			$('.appearance_box_preview').animate({'height': height + 'px', opacity: 1}, 500);
			$(".appearance_add_new").animate({'height': height+94 + 'px'}, 500);
		}).trigger('resize');

		$(document).on('click', '.appearance_box_choose_btn', function ()
		{
			if( $(this).closest('.appearance_box').hasClass('appearance_box_active') )
				return;

			var box = $(this).closest('.appearance_box'),
				id = box.data('id');

			booknetic.ajax('select_default_appearance', {id: id}, function ()
			{
				$('.appearance_box_active').find('.appearance_box_choose_btn')
					.text( $('.appearance_box_active').find('.appearance_box_choose_btn').data('label-false') )
					.removeClass('btn-primary')
					.addClass('btn-outline-secondary');

				$('.appearance_box_active').removeClass('appearance_box_active');

				box.addClass('appearance_box_active');
				box.find('.appearance_box_choose_btn').text( box.find('.appearance_box_choose_btn').data('label-true') )
					.removeClass('btn-outline-secondary')
					.addClass('btn-primary');
			});
		});

	});

})(jQuery);

