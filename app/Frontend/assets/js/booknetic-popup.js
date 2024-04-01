(function($)
{
    "use strict";

    $(document).ready( function()
    {
        $('body').append('<div class="bkntc_booking_modal" style="display: none">\n' +
            '\n' +
            '    <div class="content">\n' +
            '        <div class="close_icon">\n' +
            '            <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px"\n' +
            '                 width="20" height="20"\n' +
            '                 viewBox="0 0 24 24"\n' +
            '                 style=" fill:#000000;"><path d="M 4.7070312 3.2929688 L 3.2929688 4.7070312 L 10.585938 12 L 3.2929688 19.292969 L 4.7070312 20.707031 L 12 13.414062 L 19.292969 20.707031 L 20.707031 19.292969 L 13.414062 12 L 20.707031 4.7070312 L 19.292969 3.2929688 L 12 10.585938 L 4.7070312 3.2929688 z"></path></svg>\n' +
            '        </div>\n' +
            '        <div class="body">\n' +
            '\n' +
            '        </div>\n' +
            '    </div>\n' +
            '</div>');

        let modal = $('.bkntc_booking_modal');
        let modalX = modal.find('.close_icon');
        let modalBody = modal.find('.body');

        let closeModal = function ()
        {
            modal.css('display' ,'none');
            modalBody.html('')
            $("html,body").css('overflow','visible');
        };


        $(".bnktc_booking_popup_btn").on('click', function (e) {
            e.preventDefault();
            let data = {};
            let btn = $(this);
            data['action'] = 'bkntc_get_booking_panel';
            data['location'] = $(this).attr('data-location');
            data['staff'] = $(this).attr('data-staff');
            data['service'] = $(this).attr('data-service');
            data['category'] = $(this).attr('data-category');
            data['theme'] = $(this).attr('data-theme');

            var ajaxObject =
                {
                    url: BookneticData.ajax_url,
                    method: 'POST',
                    data: data,
                    success: function ( result )
                    {
                        modalX.css('display', 'flex');
                        modalBody.html(result)
                        bookneticInitBookingPage( $(".bkntc_booking_modal .content .body .booknetic_appointment").get(0));

                    },
                    error: function (jqXHR, exception)
                    {
                        closeModal();
                    }
                };

            modal.css('display' ,'flex');
            modalX.css('display', 'none');
            modalBody.append('<div class="bkntc_loader_div"><img class="btn_preloader" src="'+ BookneticData.assets_url +'/icons/loading.svg"></div>');
            $("html,body").css('overflow','hidden');
            $.ajax( ajaxObject );

        });

        $(".bkntc_booking_modal .close_icon").on('click', closeModal);
    });

})(jQuery);