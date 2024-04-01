(function ( $ )
{
    'use strict';

    $( document ).ready( function ()
    {
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