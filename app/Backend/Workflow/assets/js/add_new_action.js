(function ($)
{
    "use strict";

    $(document).ready(function()
    {
        $("#input_do_this").select2({
            theme:			'bootstrap',
            placeholder:	booknetic.__('select'),
            allowClear:		false
        });

        $('.modal-footer').on('click', '#addActionNextBtn', function ()
        {
            var action_driver			    = $('#input_do_this').val(),
                workflow_id					= currentWorkflowID ;

            if( action_driver === "" )
            {
                $(this).next('span').css('border', '1px solid red');
                booknetic.toast(booknetic.__('fill_all_required'), 'unsuccess');
                return;
            }
            else
            {
                $(this).next('span').css('border', '');
            }

            var data = new FormData();

            data.append('action_driver', action_driver);
            data.append('workflow_id', workflow_id);

            booknetic.ajax( 'create_new_action', data, function( result )
            {
                    booknetic.reloadActionList();

                booknetic.modalHide($(".modal"));
                booknetic.loadModal(result.edit_action, { 'id' : result.action_id } )
            });
        });

    });

})(jQuery);