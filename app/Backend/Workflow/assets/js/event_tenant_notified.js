(function ( $ )
{
    "use strict";

    $( document ).ready( function ()
    {
        $( '.fs-modal' ).on( 'click', '#eventSettingsSave', function ()
        {
            let offset_value = $( "#input_offset_value" ).val();
            let offset_type = $( "#input_offset_type" ).val();

            let data = new FormData();

            data.append( 'id', currentWorkflowID );
            data.append( 'offset_value', offset_value );
            data.append( 'offset_type', offset_type );

            booknetic.ajax( 'workflow_events.event_tenant_notified_save', data, function ()
            {
                booknetic.modalHide( $( ".fs-modal" ) );
            } );
        } );
    } );

})( jQuery );