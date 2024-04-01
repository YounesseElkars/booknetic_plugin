( ( $ ) => {
    let doc = $( document );

    doc.ready( () => {
        booknetic.ajax( 'base.get_template_selection_modal', {}, ( result ) => {
            if ( typeof result[ 'html' ] === 'undefined' )
                return;

            booknetic.modal( booknetic.htmlspecialchars_decode( result[ 'html' ] ), {
                type: 'center',
                width: 80,
            } );
        }, () => {} )
    } );

} )( jQuery )
