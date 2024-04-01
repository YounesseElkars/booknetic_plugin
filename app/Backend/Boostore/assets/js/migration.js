( function ( $ )
{
    'use strict';

    window.onbeforeunload = function () {
        if ( $( '.btn-install' ).length > 0 )
        {
            return 1;
        }
    }

    $( document ).ready( function ()
    {
        $( '#migrationModal' ).modal( { backdrop: 'static', keyboard: false } );

        let stepsCount = $( '.btn-install' ).length;
        let currentStep = 0;

        function installNextAddon ()
        {
            if ( stepsCount === currentStep )
            {
                booknetic.ajax( 'boostore.install_finished', {}, function ( res )
                {
                    location.reload();
                } );

                return;
            }

            let nextAddon = $( '.btn-install' ).first();

            if ( nextAddon )
            {
                let addon = nextAddon.attr( 'data-addon' );

                if ( addon )
                {
                    booknetic.ajax( 'boostore.install', { addon_slug: addon }, function ( res )
                    {
                        currentStep++;

                        booknetic.boostore.onInstall( nextAddon, res );

                        $( '#migrationProgress' ).css( 'width', ( 100 / stepsCount * currentStep ) + '%' );

                        installNextAddon();
                    } );
                }
            }
        }

        installNextAddon();
    } );

} )( jQuery );