(function ($)
{
    "use strict";

    $(document).ready(function()
    {

        $('.fs-modal').on('click', '#addWorkflowSave', function ()
        {
            var workflow_name			= $('#input_name').val(),
                when					= $("#input_when").val(),
                doThis					= $("#input_do_this").val(),
                is_active               = $("#input_is_active").prop('checked') ? 1 : 0 ;

            var error = false;


            $('.required').each(function ()
            {
                var borderedElement =  $(this).is('input') ? $(this) : $(this).next('span');
                if( $(this).val() === "" )
                {
                    error = true;
                    borderedElement.css('border', '1px solid red');
                }
                else
                {
                    borderedElement.css('border', '');
                }
            });

            if( error )
            {
                booknetic.toast(booknetic.__('fill_all_required'), 'unsuccess');
                return;
            }

            var data = new FormData();

            data.append('workflow_name', workflow_name);
            data.append('when', when);
            data.append('do_this', doThis);
            data.append('is_active', is_active);

            booknetic.ajax( 'create_workflow', data, function( result )
            {
                if( result.hasOwnProperty('workflow_id'))
                {
                    location.href = 'admin.php?page=' + BACKEND_SLUG + '&module=workflow&action=edit&workflow_id=' + result.workflow_id;
                }
                booknetic.modalHide($(".fs-modal"));
                booknetic.dataTable.reload( $("#fs_data_table_div") );
            });

        });

        $("#input_do_this, #input_when").select2({
            theme:			'bootstrap',
            placeholder:	booknetic.__('select'),
            allowClear:		false
        });

    });

})(jQuery);