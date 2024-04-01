( ( $ ) => {
    let doc = $( document );

    doc.ready( () => {
        doc
            .on( 'click', '.applyTemplate', apply )
            .on( 'click', '#skipSelection', skipSelection );

        initDefault();

        /*-------------------FUNCTIONS-------------------*/

        function apply()
        {
            let id = $( this ).data( 'id' );

            booknetic.ajax( 'base.apply_template', { id }, response );
        }

        function skipSelection()
        {
            booknetic.ajax( 'base.skip_template_selection', {}, response );
        }

        function response()
        {
            booknetic.modalHide( $( '.modal' ) );

            window.location.reload();
        }

        function initDefault()
        {
            let dTemp = $( '#selection_JS' ).data( 'default' );

            if ( dTemp === 0 )
                return;

            let btn = $( `.applyTemplate[data-id="${dTemp}"]` );

            if ( btn.length === 0 )
                return;

            let card = btn[ 0 ].closest( '.template-card' );

            if ( card.length === 0 )
                return;

            $( card ).find( 'button' ).html( booknetic.__( 'use_default' ) );

            $( '.template-card-wrapper' ).prepend( $( card ) );
        }
    } );
} )( jQuery )