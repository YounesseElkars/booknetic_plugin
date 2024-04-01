(function ($)
{
    "use strict";

    $(document).ready(function()
    {

        booknetic.dataTable.onLoad( function (){
                $('#fs_data_table_div').find('table tr').each(function () {
                    if( $(this).children('td:last-child').children('button').length === 0 && $(this).children('td:last-child').find('.actions_btn').length !== 0)
                    {
                        $(this).children('td:last-child').prepend('<button type="button" class="edit_action_btn btn btn-light-success">'+ booknetic.__('Edit') +'</button>');
                    }
                });
            }
        )


        $(document).on('click', '#addBtn', function ()
        {
            booknetic.loadModal('add_new', {});
        }).on('click', '#fs_data_table_div .edit_action_btn', function()
        {
            location.href = 'admin.php?page=' + BACKEND_SLUG + '&module=workflow&action=edit&workflow_id=' + $(this).closest('tr').data('id');
        });

    });

})(jQuery);

