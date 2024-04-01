( function ( $ ) {
	"use strict";

	$( document ).ready( function () {

		booknetic.initMultilangInput( $( 'input#new_category_name' ), 'service_categories', 'name' );

		$( ".fs-modal #input_employees, .fs-modal #input_parent_category").select2({
			theme: 'bootstrap',
			placeholder: booknetic.__( 'select' ),
			allowClear: true
		} );

		$( '.fs-modal' ).on( 'click', '#save_new_category', function () {
			let name         = $( 'input#new_category_name' ).val();
			let parent_id    = $( 'select#input_parent_category' ).val();
			let translations = booknetic.getTranslationData( $( '.fs-modal' ).first() );

			booknetic.ajax( 'category_save', { 'id': 0, name, parent_id, translations }, function () {
				booknetic.modalHide( $( ".fs-modal" ) );
			} );
		} );
	} );
} )( jQuery );