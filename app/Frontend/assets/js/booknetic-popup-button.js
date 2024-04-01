( function ( $ ) {
    $( document ).ready( function () {
        ( function () {
            if ( typeof BookneticData == "object" ) {
                return;
            }

            let theme        = $( this ).data( 'theme' );
            let addFileToDOM = ( file ) => {
                if ( file.type === 'js' ) {
                    if( document.querySelector( `script[id='${file.id}']` ) ) {
                        return;
                    }

                    let script = document.createElement( 'script' );

                    script.src = file.src;
                    script.id  = file.id;

                    document.body.appendChild( script );
                } else if ( file.type === 'css' ) {
                    if( document.querySelector( `link[id='${file.id}']` ) ) {
                        return;
                    }

                    let link = document.createElement( 'link' );

                    link.href = file.src;
                    link.id   = file.id;
                    link.type = 'text/css';
                    link.rel  = 'stylesheet';

                    document.getElementsByTagName( 'head' )[ 0 ].appendChild( link );
                }
            }

            $.ajax( {
                type: 'POST',
                url: ajaxurl,
                data: { action: "bkntc_get_booking_panel_necessary_files", theme },
                success: function( response ) {
                    response = JSON.parse( response );

                    if ( response.status !== 'ok' ) {
                        return;
                    }

                    let results = response.results;

                    if ( ! results ) {
                        return;
                    }

                    let scripts = results.scripts;
                    let files = results.files;

                    if ( !! scripts && scripts.length > 0 ) {
                        for ( let i = 0; i < scripts.length; i++ ) {
                            eval( scripts[ i ] );
                        }
                    }

                    for ( let key in files ) {
                        addFileToDOM( files[ key ] );
                    }
                }
            } );
        } )();
    } );
} )( jQuery );