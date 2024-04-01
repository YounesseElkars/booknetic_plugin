(function ( $ )
{
    'use strict';

    $( document ).ready( function ()
    {

        // Handle displaying of rating items level
        $( '.rating_item' ).each( function ( i, el )
        {
            $( el ).find( '.level > span' ).width( `${ $( el ).data( 'level-percent' ) }%` );
        } );

        // Handle 5-star rating selection
        $( '#details_js-rating' ).children().each( function ( i, el )
        {
            $( el ).on( 'click', function ()
            {
                // Clear previous state
                $( '#details_js-rating' ).children().each( ( i, el ) => $( el ).removeClass( 'filled' ) );

                // Fill clicked star and stars before that
                $( el ).addClass( 'filled' );
                $( el ).nextAll().addClass( 'filled' );
            } );
        } );

        // Show comment replies
        $( '.btn-show-replies' ).each( function ( i, el )
        {
            $( el ).on( 'click', function ()
            {
                $( el ).closest( '.comment-container' ).addClass( 'show-replies' );
            } );
        } );

        // Reply to a comment
        $( '.btn-reply' ).each( function ( i, el )
        {
            $( el ).on( 'click', function ()
            {
                var $btnClose  = $( '.tab-pane.active' ).find( '.btn-close' ),
                    $replyTo   = $( '.tab-pane.active' ).find( '.reply-to' ),
                    $textarea  = $( '.tab-pane.active' ).find( '.comment-form textarea' ),
                    $input     = $( '.tab-pane.active' ).find( '.comment-form input' ),
                    $rating    = $( '.tab-pane.active' ).find( '.comment-form #details_js-rating' ),
                    authorName = $( el ).closest( '.comment' ).find( 'h5' ).text()

                // Clear inputs and rating
                function resetForm ()
                {
                    $( '.tab-pane.active' ).find( '.comment-form input, .comment-form textarea' ).each( function ( i, el )
                    {
                        el.value = '';
                    } );
                    $rating.children().each( function ( i, el )
                    {
                        $( el ).removeClass( 'filled' )
                    } );
                }

                // Make form ready
                resetForm();
                var placeholder = $textarea.attr( 'placeholder' );
                $textarea.attr( 'placeholder', 'Write a reply' );
                $replyTo.text( ` - Reply to ${ authorName }` );
                $rating.addClass( 'd-none' );
                $input.addClass( 'd-none' );

                // Handle close reply
                $btnClose.removeClass( 'd-none' );
                $btnClose.on( 'click', function ()
                {
                    // Set everything back
                    resetForm();
                    $btnClose.addClass( 'd-none' );
                    $textarea.attr( 'placeholder', placeholder );
                    $input.removeClass( 'd-none' );
                    $rating.removeClass( 'd-none' );
                    $replyTo.text( '' );
                } );

                // Consider header height and extra space (80 + 24)
                window.scroll(
                    { top: $( '.tab-pane.active' ).offset().top - 104, left: 0, behavior: 'smooth' }
                );
            } );
        } );

        booknetic.boostore.onInstall = function ( el, res )
        {
            el.removeClass( 'btn-success btn-install' );
            el.addClass( 'btn-outline-danger btn-uninstall' );
            el.text( 'UNINSTALL' );
        }

        booknetic.boostore.onUninstall = function ( el, res )
        {
            el.removeClass( 'btn-outline-danger btn-uninstall' );
            el.addClass( 'btn-success btn-install' );
            el.text( 'INSTALL' );
        };


    } );

})( jQuery );