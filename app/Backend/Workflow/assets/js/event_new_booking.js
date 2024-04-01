(function ($)
{
    "use strict";

    $(document).ready(function()
    {
        $('.fs-modal').on('click', '#eventSettingsSave', function ()
        {
            var locations			= $("#input_locations").val(),
                services			= $("#input_services").val(),
                staffs			    = $("#input_staff").val(),
                locale			    = $("#input_locale").val(),
                statuses			= $("#input_statuses").val(),
                called_from         = $("#input_called_from").val();


            var data = new FormData();

            data.append('id', currentWorkflowID);
            data.append('locations', JSON.stringify( locations ));
            data.append('services', JSON.stringify( services ));
            data.append('staffs', JSON.stringify( staffs ));
            data.append('statuses', JSON.stringify( statuses ));
            data.append('locale', locale);
            data.append('called_from', called_from);

            booknetic.ajax( 'workflow_events.event_new_booking_save', data, function()
            {
                booknetic.modalHide($(".fs-modal"));
            });
        });

        booknetic.select2Ajax( $(".fs-modal #input_locations"), 'workflow_events.get_locations');
        booknetic.select2Ajax( $(".fs-modal #input_services"), 'workflow_events.get_services');
        booknetic.select2Ajax( $(".fs-modal #input_staff"), 'workflow_events.get_staffs');
        booknetic.select2Ajax( $(".fs-modal #input_statuses"), 'workflow_events.get_statuses');

    });

})(jQuery);