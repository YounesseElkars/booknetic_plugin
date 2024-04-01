( function ( $ )
{
	'use strict';

	$( document ).ready( function()
	{
		let modal = $( '.fs-modal' );

		modal.find( '#appointment_info_payment_gateway' ).select2( {
			theme: 'bootstrap',
			placeholder: booknetic.__( 'select' ),
			allowClear: true
		} );

 		modal
			.on( 'click', '.delete-btn', function ()
			{
				booknetic.confirm(
					booknetic.__(  'really_want_to_delete' ),
					'danger',
					'trash',
					() => {
						var ajaxData = {
							'fs-data-table-action': 'delete',
							'ids': [ $('#add_new_JS_info1').data('appointment-id') ]
						};

						$.post(
							location.href.replace( /module=\w+/g , 'module=appointments'),
							ajaxData,
							( _ ) => {
								if( $("#fs_data_table_div").length > 0 )
								{
									booknetic.dataTable.reload( $("#fs_data_table_div") );

									booknetic.toast('Deleted!', 'success', 5000);

									booknetic.modalHide(modal);
								}
								else if ( typeof reloadCalendarFn !== 'undefined' && currentModule === 'calendar' )
								{
									booknetic.modalHide(modal);
									reloadCalendarFn(); //so the visuals could be updated accordingly
								}
								else
								{
									location.reload();
								}
							} );
					} );
			} )
			.on( 'click' ,'#bkntc_create_payment_link' , function ()
			{
				let paymentGateway = $( '#appointment_info_payment_gateway' ).val();
				let appointmentId  = $( this ).attr( 'data-appointment-id' );

				let data = new FormData();

				data.append( 'payment_gateway' , paymentGateway );
				data.append( 'id' , appointmentId );

				booknetic.ajax( 'appointments.create_payment_link', data, function( result )
				{
					let pmLink = $('.bkntc_payment_link_container');

					pmLink.show();
					pmLink.find( '.payment_link' ).text( result[ 'url' ] );
				} );
			} )
			.on( 'click' , '.copy_url_payment_link',function () {
				let val = $( '.bkntc_payment_link_container' )
					.find(".payment_link")
					.text()
					.trim();

				navigator.clipboard
					.writeText( val )
					.then( r => booknetic.toast( booknetic.__( 'link_copied' ), 'success' ) );
			} );
	} );

} )( jQuery );