(function ( $ )
{
    'use strict';

    // Get addons with given parameters and update view
    function updateData ( params, initPage = true )
    {
        if ( initPage )
        {
            delete params.page;
        }

        booknetic.ajax( 'get_addons', params, function ( res )
        {
            $( '.addons_content' ).html( booknetic.htmlspecialchars_decode( res.html ) );
        } );
    }

    function updateUrlParameter(param, value) {

        let newUrl;
        const regExp = new RegExp(param + "(.+?)(&|$)", "g");
        if( window.location.href.match( regExp ) === null )
        {
             newUrl = window.location.href + `&${param}=${value}`;
        }
        else
        {
             newUrl = window.location.href.replace(regExp, param + "=" + value + "$2");
        }
        window.history.pushState("", "", newUrl );
    }

    $( document ).ready( function ()
    {

        // Default select2 config
        var defaults = {
            theme: 'bootstrap',
            minimumResultsForSearch: -1
        };

        var categorySelector = '.addons_filter_panel select#category';
        var searchSelector   = '.addons_filter_panel .search_input';
        var sortSelector     = '.addons_filter_panel select#sort';

        // Store all request params for fetching addons
        var addonsReqParams = {};

        var queryString = window.location.search;
        var urlParams = new URLSearchParams(queryString);
        if( urlParams.get('category') )
        {
            addonsReqParams.category_ids = urlParams.get('category');
        }

        // Load data initially
        updateData( addonsReqParams );

        // Initialize category dropdown
        $( categorySelector ).select2( {
            ...defaults,
        } );

        if( urlParams.get('category') )
        {
            $( categorySelector ).val(urlParams.get('category')).trigger('change');
        }


        // Initialize sort dropdown
        $( sortSelector ).select2( {
            ...defaults,
            templateSelection: function ( data )
            {
                if ( data.selected )
                {
                    return 'Sort by: ' + data.text;
                }

                return data.text;
            }
        } );

        $( categorySelector ).on( 'change', function ( e )
        {
            var selectedCategory = $( e.target ).val();

            updateUrlParameter('category' , selectedCategory );

            setTimeout( function ()
            {
                if ( selectedCategory )
                {
                    addonsReqParams.category_ids = selectedCategory;
                }
                else
                {
                    delete addonsReqParams.category_ids;
                }

                updateData( addonsReqParams );

            } );

        } );

        // Handle search
        var searchKeyword = '';
        var timer;

        $( searchSelector ).on( 'keyup', function ( e )
        {
            searchKeyword = e.target.value.trim().replace( new RegExp( /([^\p{L}\p{N}\-\s]+)/ug ), '' );

            clearTimeout( timer );

            timer = setTimeout( function ()
            {
                if ( searchKeyword.length > 0 )
                {
                    addonsReqParams.search = searchKeyword;
                }
                else
                {
                    delete addonsReqParams.search;
                }

                updateData( addonsReqParams );
            }, 300 );
        } );

        // Handle sort
        $( sortSelector ).on( 'select2:select', function ( e )
        {
            var sortBy = e.params.data.id;

            switch ( sortBy )
            {
                case 'lowest-price':
                    addonsReqParams.order_by   = 'current_price';
                    addonsReqParams.order_type = 'ASC';
                    break;
                case 'highest-price':
                    addonsReqParams.order_by   = 'current_price';
                    addonsReqParams.order_type = 'DESC';
                    break;
                case 'most-installed':
                    addonsReqParams.order_by   = 'downloads';
                    addonsReqParams.order_type = 'DESC';
                    break;
                case 'newest':
                    addonsReqParams.order_by   = 'created_at';
                    addonsReqParams.order_type = 'DESC';
                    break;
                default:
                    delete addonsReqParams.order_by;
                    delete addonsReqParams.order_type;
            }

            updateData( addonsReqParams );
        } );

        // Handle pagination
        $( '.addons_content' ).on( 'click', '.pagination .page_class:not(.active_page)', function ( e )
        {
            let curPage     = parseInt( $( e.target ).text() );
            let totalPages  = parseInt( $( '.pagination_total' ).text() );
            let activeClass = 'active_page badge-default';

            if ( curPage <= totalPages )
            {
                // Update pagination bullets
                $( '.addons_content' ).find( '.page_class.active_page' ).removeClass( activeClass );
                $( e.target ).addClass( activeClass );

                // Update current page number
                $( '.addons_content' ).find( '.pagination_current' ).text( curPage );

                addonsReqParams.page = curPage;

                // Update addons
                updateData( addonsReqParams, false );
            }
        } );

        booknetic.boostore.onInstall = function ( el, res )
        {
            updateData( addonsReqParams, false );
        };

        booknetic.boostore.onUninstall = function ( el, res )
        {
            updateData( addonsReqParams, false );
        };
    } );

})( jQuery );