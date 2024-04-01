(function ($)
{
    "use strict";

    $(document).ready(function()
    {

        $(".select2").select2({
            theme:'bootstrap',
            minimumResultsForSearch: -1
        });

        const hostName = $('.bkntc_direct_booking_url .bkntc_link_output').text();

        function link_generate()
        {
            let urlObj = {};
            let skip_steps = [];

            $('.bkntc_direct_booking_url .url_generate').each( function () {
                let param = $( this ).attr( 'data-key' );
                const type = $( this ).attr( 'type' );
                let val = type === 'checkbox' ? $( this ).is( ':checked' ) : $( this ).val().trim();

                if ( type === 'checkbox' && val === true )
                {
                    const step = $( this ).data( 'unhide-step' );

                    urlObj[ `show_${ step }` ] = 1;

                    skip_steps.push( step );
                }
                else if ( type !== 'checkbox' && val !== '' )
                    urlObj[ param ] = val ;
            });
            $('.bkntc_direct_booking_url .bkntc_link_output').text(hostName + "/?" + $.param( urlObj ));
        }

        $('.bkntc_direct_booking_url')
            .on('change' ,'.categories', function()
        {
            const category_id = $(this).val();

            $('.services.url_generate').select2('val', '');

            $('.services.url_generate').find('option').each(function (key, el) {
                if(category_id !== '' && $(el).attr('data-category-id') != category_id && $(el).val() !== '')
                {
                    $(el).wrap($(el).parent().hasClass('wrapped-option') ? '' : '<div class="wrapped-option"></div>');
                }
                else
                {
                    $(el).unwrap('.wrapped-option');
                }
            });
        }).on('change' ,'.url_generate' , link_generate )


        $('.bkntc_copy_clipboard').on('click' , function () {
            let val = $('.bkntc_direct_booking_url .bkntc_link_output').text().trim();
            navigator.clipboard.writeText( val );

            booknetic.toast( booknetic.__('link_copied'), 'success' );
        });

        link_generate();

    });

})(jQuery);
