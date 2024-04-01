(function ($)
{
    "use strict";

    $(document).ready(function()
    {
        $('.fs-modal').on('click', '#eventSettingsSave', function ()
        {
            var stasuses			= $("#input_statuses").val(),
                prev_statuses		= $("#input_prev_statuses").val(),
                locations			= $("#input_locations").val(),
                services			= $("#input_services").val(),
                staffs			    = $("#input_staff").val(),
                locale			    = $("#input_locale").val(),
                called_from         = $("#input_called_from").val();

            var data = new FormData();

            data.append('id', currentWorkflowID);
            data.append('statuses', JSON.stringify( stasuses ));
            data.append('prev_statuses', JSON.stringify( prev_statuses ));
            data.append('locations', JSON.stringify( locations ));
            data.append('services', JSON.stringify( services ));
            data.append('staffs', JSON.stringify( staffs ));
            data.append('locale', locale);
            data.append('called_from', called_from);

            booknetic.ajax( 'workflow_events.event_booking_status_changed_save', data, function()
            {
                booknetic.modalHide($(".fs-modal"));
            });
        });

        $('#input_statuses').select2({
            theme: 'bootstrap',
            placeholder: booknetic.__('select'),
        });
        $('#input_prev_statuses').select2({
            theme: 'bootstrap',
            placeholder: booknetic.__('select'),
        });
        booknetic.select2Ajax( $(".fs-modal #input_locations"), 'workflow_events.get_locations');
        booknetic.select2Ajax( $(".fs-modal #input_services"), 'workflow_events.get_services');
        booknetic.select2Ajax( $(".fs-modal #input_staff"), 'workflow_events.get_staffs');

    });

})(jQuery);