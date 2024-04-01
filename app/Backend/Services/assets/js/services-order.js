(function ($) {

    $( document ).ready( function () {

        const bkntc_categories_wrapper = $( "#booknetic_categories" );
        const bkntc_services_wrapper   = $( "#bkntc_services_order_list" );
        const bkntc_categories_items   = bkntc_categories_wrapper.find( ".bkntc_order_item" );

        bkntc_categories_items.on( "click", function (e) {
            e.stopPropagation()

            if ( $(this).hasClass( "selected" ) ) return;
            bkntc_categories_items.removeClass("selected");
            $( this ).addClass("selected");

            bkntc_services_wrapper.html("");

            booknetic.ajax( "get_services_order", {
                id: $(this).attr( "data-id" )
            }, function (response) {
                let newHtml = '';
                if ( response.status === "ok" && response.services.length > 0 ) {
                    response.services.forEach(el => {
                        newHtml += `<div data-id="${ el.id }" class="bkntc_order_item">
                        <span class="bkntc_order_item_sort_helper">
                            <i class="fas fa-bars"></i>
                        </span>

                        ${el.name}
                    </div>`
                    })
                } else {
                    newHtml = `<div class="text-center">${ booknetic.__( 'no_service_to_show' ) }</div>`
                }

                bkntc_services_wrapper.html(newHtml);
            } );
        } );

        bkntc_categories_items.first().click();

        $(".bkntc_order_items_wrapper").sortable({
            helper: function (e, ui) {
                ui.children().each(function () {
                    $(this).width($(this).width());
                });
                return ui;
            },
            handle: ".bkntc_order_item_sort_helper",
            tolerance: "pointer",
            containment: "parent",
            axis: "y"
        });

        $( "#saveChangesBtn" ).on( "click", function () {
            let bkntc_category_order  = [];
            let bkntc_service_order   = [];
            let bkntc_active_category = 1;

            bkntc_categories_wrapper.find( ".bkntc_order_item" ).each((index, item) => {
                if ( $( item ).hasClass( 'selected' ) ) bkntc_active_category = $( item ).attr( "data-id" )
                bkntc_category_order.push( $( item ).attr( "data-id" ) );
            });

            bkntc_services_wrapper.find( ".bkntc_order_item" ).each( (index, item) => {
                bkntc_service_order.push( $( item ).attr( "data-id" ) );
            });

            booknetic.ajax( "save_services_order", {
                category_order: JSON.stringify( bkntc_category_order ),
                active_category: bkntc_active_category,
                service_order: JSON.stringify( bkntc_service_order )
            }, function ( response ) {
                if ( response.status === "ok" ) booknetic.toast( booknetic.__( "saved_successfully" ), "success" );
            }, false );
        } );

        $( '#resetOrderBtn' ).on( 'click', function ()
        {
            booknetic.ajax( 'reset_order', {}, function ( res )
            {
                if ( res.status === 'ok' )
                {
                    booknetic.toast( res.message );
                    window.location.reload();
                }
            })
        })

    } );

})(jQuery)