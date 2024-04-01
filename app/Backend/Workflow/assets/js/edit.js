(function ($)
{
    "use strict";

    $(document).ready(function()
    {
        $(document).on('click', '#workflow_save_btn', function ()
        {
            let form = new FormData();
            form.append('id', currentWorkflowID);
            form.append('name', $('#workflow_name').val());
            form.append('is_active', $('#workflow_activated').prop('checked') ? 1 : 0);
            booknetic.ajax('save_workflow', form, function ()
            {
                booknetic.toast(booknetic.__('saved_changes'), 'success', 2000);
            });
        }).on('click', '#addBtn', function ()
        {
            booknetic.loadModal('add_new_action', {}, {'type':'center'});
        }).on( 'click', '.delete_action', function ()
        {

            var rid = $(this).data('id');

            booknetic.confirm(booknetic.__('are_you_sure_want_to_delete'), 'danger', 'trash', function()
            {
                var ajaxData = {
                    'id': rid
                };

                booknetic.ajax( 'delete_action', ajaxData, function()
                {
                    booknetic.reloadActionList();
                    booknetic.toast(booknetic.__('Deleted'), 'success', 2000);
                });
            });

        }).on( 'click', '.remove-row', function ()
        {
            $( this ).parent().remove();
        } );

        $("#input_workflow_when").select2({
            theme:			'bootstrap',
            placeholder:	booknetic.__('select'),
            allowClear:		false
        });

        booknetic.reloadActionList =  function() {
            booknetic.ajax( 'get_action_list_view',{ 'workflow_id' : currentWorkflowID } , function( result )
            {
                if( result.hasOwnProperty('html'))
                {
                    $('.workflow_action_list').html( booknetic.htmlspecialchars_decode( result.html ) );
                }
            });
        }
    });

})(jQuery);