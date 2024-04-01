(function ($)
{
    "use strict";

    $(document).ready(function()
    {
        $('.fs-modal').on('click', '#eventSettingsSave', function ()
        {
            var locale			    = $("#input_locale").val();

            var data = new FormData();

            data.append('id', currentWorkflowID);
            data.append('locale', locale);

            booknetic.ajax( 'workflow_events.event_appointment_paid_save', data, function()
            {
                booknetic.modalHide($(".fs-modal"));
            });
        });

    });

})(jQuery);