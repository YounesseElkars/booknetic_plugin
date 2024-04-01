( function ( $ ) {
    'use strict';

    $( document ).ready( function () {
        setTimeout( function () {
            $( '#changelogsPopup' ).fadeIn();
        }, 2000 );

        $( '#changelogsPopupClose' ).on( 'click', function () {
            $( '#changelogsPopup' ).fadeOut( function () {
                $( this ).remove();
            } );
        } );
    } );
} )( jQuery );