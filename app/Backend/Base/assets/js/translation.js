(function ($) {

    'use strict';

    $( document ).ready( function ()
    {
        var translationRowID = $( '#bkntcEditMultilangForm' ).data( 'id' ),
            translationTableName = $( '#bkntcEditMultilangForm' ).data( 'table' ),
            translationColumn    = $( '#bkntcEditMultilangForm' ).data( 'column' );

        if ( booknetic.defaultTranslatingValue )
        {
            $( '#bkntc_default_value' ).val( booknetic.defaultTranslatingValue )
        } else
        {
            $( '#bkntc_default_value' ).val( $( '.bkntc_translating_input' ).val() )
        }

        $( ".bkntc_multilang_row_locale" ).select2({
            theme:			'bootstrap',
            placeholder:	booknetic.__('select'),
            allowClear:		true
        });

        $( '.fs-modal' ).on( 'click', '.bkntc_delete_multilang_btn', function (e) {

            var parentRow = $( e.target ).closest( '.bkntc_multilang_row' );

            booknetic.confirm( booknetic.__( 'are_you_sure_want_to_delete' ), 'danger', 'trash', function () {
                if ( parseInt( parentRow.data( 'id' ) ) > 0 ) {
                    booknetic.ajax( 'Base.delete_translation', {
                        id: parentRow.data( 'id' )
                    }, function ( response ) {
                        if ( response.status === 'ok' ) {
                            parentRow.remove();
                        }
                    } )
                } else {
                    parentRow.remove();
                }
            } )

        } ).on( 'click', '#bkntcSaveTranslationsBtn', function () {

            var translations = [],
                isMultiBehaviour = !! booknetic.translatingFieldSelector,
                addedLocales = [],
                _this = $( this );

            $( '#bkntcEditMultilangForm .bkntc_multilang_row' ).each( function ( _, item ) {
                var locale = $( item ).find( '.bkntc_multilang_row_locale' ).val();
                if ( addedLocales.indexOf( locale ) > -1 ) {
                    booknetic.toast( 'You can add only one translation for one language', 'unsuccess' );
                    return;
                }
                addedLocales.push( locale );
                translations.push({
                    id: $( item ).closest( '.bkntc_multilang_row' ).data( 'id' ),
                    locale: locale,
                    value: $( item ).find( '.bkntc_multilang_value' ).val()
                });
            } );

            // Eger her hansi bir fieldi edit edirikse onda translationunu save edirik
            if ( translationRowID > 0 || ( translationTableName === 'options' && translationColumn ) )
            {
                booknetic.ajax( 'Base.save_translations', {
                    translations: JSON.stringify( translations ),
                    table_name: translationTableName,
                    column_name: translationColumn,
                    row_id: translationRowID
                }, function ( response ) {
                    if ( response.status === 'ok' )
                    {
                        booknetic.toast( booknetic.__( 'saved_successfully' ) );
                        booknetic.modalHide( _this.closest( '.fs-modal' ) )
                    }
                } );
            } else
            {
                $( '.bkntc_translating_input' ).first().data( 'translations', translations );
                if ( isMultiBehaviour ) {
                    // Eger translate olunan hisse custom formsdursa, onda elave etdiyimiz her bir inputun translate datasini her defe deyisirik
                    $( booknetic.translatingFieldSelector ).data( 'translations', booknetic.getTranslationData( $( '#formbuilder_options' ) ) )
                }
                booknetic.toast( booknetic.__( 'saved_successfully' ), 'success' );
                booknetic.modalHide( _this.closest( '.fs-modal' ) )
            }

        } ).on( 'click', '#bkntcAddNewTranslationBtn', function () {
            var template =  $( '#bkntc_translations_template' ).html(),
                newRowSelector = $(this).before( template ).prev().find( 'select' ).addClass( 'bkntc_multilang_row_locale' );

            newRowSelector.select2({
                theme:			'bootstrap',
                placeholder:	booknetic.__('select'),
                allowClear:		true
            });
        } ).on( 'click', '#copyTranslatingValueBtn', function () {

            navigator.clipboard.writeText( $( '#bkntc_default_value' ).val() ).then(function() {
                booknetic.toast( booknetic.__( 'copied_to_clipboard' ) );
            }, function(err) {
                booknetic.toast( booknetic.__( 'something_went_wrong' ) );
            });

        } );

    })

})(jQuery)