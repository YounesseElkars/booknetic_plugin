(function ($)
{
	"use strict";

	$(document).ready(function()
	{

		$(document).on('click', '.settings-chart', function ()
		{
			$(".m_header").fadeOut(200);
			$(".settings-main-menu").fadeOut(200, function()
			{
				$(".settings-main-container").fadeIn(200);
				$( '.settings-floating-button' ).removeClass( 'hidden-important' );
				$(".settings-left-menu").removeClass('hidden-important').hide().fadeIn(200, function ()
				{
					$('.settings-left-menu').scrollTop( 0 );
					let top_pos = $(".settings-left-menu .settings_menu[data-view='" + view + "']").position().top - 10;

					//todo: deprecated, after removing niceScroll. Removed at 3.4.2
					// $('.settings-left-menu').scrollTop( top_pos ).niceScroll({cursorcolor: "#d5dce4"});
					$('.settings-left-menu').scrollTop( top_pos ).handleScrollBooknetic();
				});

				var firstFade = 250;
				$(".settings-left-menu .settings_menu").hide().each(function()
				{
					$(this).fadeIn( firstFade );
					firstFade += 50;
				});
			});

			var view = $(this).data('view');

			$(".settings-left-menu .settings_menu[data-view='" + view + "']").click();
		}).on('click', '.settings-left-menu .settings_menu', function (e)
		{
			$( '.settings-left-menu' ).removeClass( 'is-open' );
			$( '.settings-floating-button' ).removeClass( 'is-right' ).addClass( 'is-left' );

			if( $(this).hasClass('selected-menu') )
				return;

			$(".settings-left-menu .settings_menu.selected-menu").removeClass('selected-menu').removeClass('dashed-border');
			$(this).addClass('selected-menu').addClass('dashed-border');

			if( $(e.target).is('.settings_submenus > div') )
			{
				return;
			}
			$(".settings-left-menu .selected_sub_menu").removeClass('selected_sub_menu');

			if( $(this).find('.settings_submenus').length > 0 )
			{
				$(this).find('.settings_submenus > div:eq(0)').click();
				return;
			}

			let view = $( this ).data( 'view' );

			booknetic.ajax( view, {}, function( result ) {
				$( '.settings-main-container' ).html( booknetic.htmlspecialchars_decode( result['html'] ) );
			} );
		}).on( 'click', '.settings-floating-button.is-left', function ()
		{
			$( '.settings-left-menu' ).addClass( 'is-open' );
			$( this ).removeClass( 'is-left' ).addClass( 'is-right' );
		} ).on( 'click', '.settings-floating-button.is-right', function ()
		{
			$( '.settings-left-menu' ).removeClass( 'is-open' );
			$( this ).removeClass( 'is-right' ).addClass( 'is-left' );
		} ).on('click', '.settings-left-menu .settings_submenus > div', function ()
		{
			if( $(this).hasClass('selected_sub_menu') )
				return;

			$(".settings-left-menu .selected_sub_menu").removeClass('selected_sub_menu');
			$(this).addClass('selected_sub_menu');

			let view = $( this ).data( 'view' );

			booknetic.ajax( view, {}, function( result ) {
				$( '.settings-main-container' ).html( booknetic.htmlspecialchars_decode( result['html'] ) );
			} );
		});

		if( $('#settingsJS').data('goto') )
		{
			let goto = $('#settingsJS').data('goto');

			if( $('.settings-chart[data-view="settings.'+goto+'_settings"]').length )
			{
                $('.settings-chart[data-view="settings.'+goto+'_settings"]').click();
			}
			else if( $('.settings_submenus div[data-view="'+goto+'"]').length ) // is sub menu
			{
				$('.settings_submenus div[data-view="'+goto+'"]').click();

				let parent_view_name = $('.settings_submenus div[data-view="'+goto+'"]').closest('.settings_menu').data('view');

				$('.settings-chart[data-view="'+parent_view_name+'"]').click();
			}
		}

	});

})(jQuery);