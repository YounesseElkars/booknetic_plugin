( function ( $ ) {
    'use strict';

    $( document ).ready( function () {
        $( '.fs-modal' ).on( 'click', '#saveWorkflowActionBtn', function () {
            let data	= new FormData();
            let appointment_ids		= $( '#input_appointment_ids' ).val();
            let status = $('#input_appointment_set_status').val();
            let run_workflows = $('#input_run_workflows').is(':checked');
            let is_active = $('#input_is_active').is(':checked') ? 1 : 0;

            data.append('id',		workflow_action_id );
            data.append('is_active', is_active);
            data.append('appointment_ids',		appointment_ids );
            data.append('status', status);
            data.append('run_workflows', run_workflows ? 1 : 0);

            booknetic.ajax( 'workflow_actions.set_booking_status_save', data, function () {
                booknetic.modalHide( $( '.fs-modal' ) );
                booknetic.reloadActionList();
            } );
        });

        $( '#input_appointment_ids' ).select2( {
            tokenSeparators: [ ',' ],
            theme: 'bootstrap',
            tags: true,
        });

        $( '#input_appointment_set_status' ).select2( {
            theme: 'bootstrap',
            placeholder: booknetic.__('select'),
            allowClear: false,
            tags: false
        });


    } );
})(jQuery);