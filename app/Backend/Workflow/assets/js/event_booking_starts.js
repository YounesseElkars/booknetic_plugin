(function ($)
{
    "use strict";

    $(document).ready(function()
    {
        $('.fs-modal').on('click', '#eventSettingsSave', function ()
        {
            var statuses			= $("#input_statuses").val(),
                locations			= $("#input_locations").val(),
                services			= $("#input_services").val(),
                staffs			    = $("#input_staff").val(),
                locale			    = $("#input_locale").val(),
                for_each_customer   = $('#input_for_each_customer').is(':checked') ? 1 : 0;

            var offset_sign = $("#input_offset_sign").val();
            var offset_value = $("#input_offset_value").val();
            var offset_type = $("#input_offset_type").val();

            var data = new FormData();

            data.append('id', currentWorkflowID);
            data.append('offset_sign', offset_sign);
            data.append('offset_value', offset_value);
            data.append('offset_type', offset_type);
            data.append('statuses', JSON.stringify( statuses ));
            data.append('locations', JSON.stringify( locations ));
            data.append('services', JSON.stringify( services ));
            data.append('staffs', JSON.stringify( staffs ));
            data.append('locale', locale);
            data.append('for_each_customer', for_each_customer);

            booknetic.ajax( 'workflow_events.event_booking_starts_save', data, function()
            {
                booknetic.modalHide($(".fs-modal"));
            });
        });

        $('#input_statuses').select2({
            theme: 'bootstrap'
        });
        booknetic.select2Ajax( $(".fs-modal #input_locations"), 'workflow_events.get_locations');
        booknetic.select2Ajax( $(".fs-modal #input_services"), 'workflow_events.get_services');
        booknetic.select2Ajax( $(".fs-modal #input_staff"), 'workflow_events.get_staffs');

    });

})(jQuery);