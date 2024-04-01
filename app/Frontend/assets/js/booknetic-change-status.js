var bookneticHooks = {
    hooks: {
        'ajax': [],
        'steps' : []
    },

    addFilter: function ( key, fn ) {
        key = key.toLowerCase();

        if ( ! this.hooks.hasOwnProperty( key ) )
        {
            this.hooks[ key ] = [];
        }

        this.hooks[ key ].push( fn );
    },

    doFilter: function ( key, params, ...extra ) {
        key = key.toLowerCase();

        if ( this.hooks.hasOwnProperty( key ) )
        {
            if ( key.indexOf( '_' ) > -1 )
            {
                let mainKey = key.split( '_' )[ 0 ];

                this.hooks[ mainKey ].forEach( function ( fn ) {
                    if ( typeof params === 'undefined' )
                    {
                        params = fn( ...extra );
                    }
                    else
                    {
                        params = fn( params, ...extra );
                    }
                } );
            }

            this.hooks[ key ].forEach( function ( fn ) {
                if ( typeof params === 'undefined' )
                {
                    params = fn( ...extra );
                }
                else
                {
                    params = fn( params, ...extra );
                }
            } );
        }

        return params;
    },

    addAction: function ( key, fn ) {
        this.addFilter( key, fn );
    },

    doAction: function ( key, ...params ) {
        this.doFilter( key, undefined, ...params );
    }
};

(function($)
{
    "use strict";

    function __( key )
    {
        return key in BookneticChangeStatusData.localization ? BookneticChangeStatusData.localization[ key ] : key;
    }

    var booknetic = {

        __,

        options: {
            'templates': {
                'loader': '<div class="booknetic_loading_layout"></div>',
                'toast': '<div id="booknetic-toastr"><div class="booknetic-toast-img"><img></div><div class="booknetic-toast-details"><span class="booknetic-toast-description"></span></div><div class="booknetic-toast-remove"><i class="fa fa-times"></i></div></div>'
            }
        },

        localization: {
            month_names: [ __('January'), __('February'), __('March'), __('April'), __('May'), __('June'), __('July'), __('August'), __('September'), __('October'), __('November'), __('December') ],
            day_of_week: [ __('Sun'), __('Mon'), __('Tue'), __('Wed'), __('Thu'), __('Fri'), __('Sat'), __('Sun') ] ,
        },


        parseHTML: function ( html )
        {
            var range = document.createRange();
            var documentFragment = range.createContextualFragment( html );
            return documentFragment;
        },

        loading: function ( onOff )
        {
            if( typeof onOff === 'undefined' || onOff )
            {
                $('#booknetic_progress').removeClass('booknetic_progress_done').show();
                $({property: 0}).animate({property: 100}, {
                    duration: 1000,
                    step: function()
                    {
                        var _percent = Math.round(this.property);
                        if( !$('#booknetic_progress').hasClass('booknetic_progress_done') )
                        {
                            $('#booknetic_progress').css('width',  _percent+"%");
                        }
                    }
                });

                $('body').append( this.options.templates.loader );
            }
            else if( ! $('#booknetic_progress').hasClass('booknetic_progress_done') )
            {
                $('#booknetic_progress').addClass('booknetic_progress_done').css('width', 0);

                // IOS bug...
                setTimeout(function ()
                {
                    $('.booknetic_loading_layout').remove();
                }, 0);
            }
        },

        htmlspecialchars_decode: function (string, quote_style)
        {
            var optTemp = 0,
                i = 0,
                noquotes = false;
            if(typeof quote_style==='undefined')
            {
                quote_style = 2;
            }
            string = string.toString().replace(/&lt;/g, '<').replace(/&gt;/g, '>');
            var OPTS ={
                'ENT_NOQUOTES': 0,
                'ENT_HTML_QUOTE_SINGLE': 1,
                'ENT_HTML_QUOTE_DOUBLE': 2,
                'ENT_COMPAT': 2,
                'ENT_QUOTES': 3,
                'ENT_IGNORE': 4
            };
            if(quote_style===0)
            {
                noquotes = true;
            }
            if(typeof quote_style !== 'number')
            {
                quote_style = [].concat(quote_style);
                for (i = 0; i < quote_style.length; i++){
                    if(OPTS[quote_style[i]]===0){
                        noquotes = true;
                    } else if(OPTS[quote_style[i]]){
                        optTemp = optTemp | OPTS[quote_style[i]];
                    }
                }
                quote_style = optTemp;
            }
            if(quote_style & OPTS.ENT_HTML_QUOTE_SINGLE)
            {
                string = string.replace(/&#0*39;/g, "'");
            }
            if(!noquotes){
                string = string.replace(/&quot;/g, '"');
            }
            string = string.replace(/&amp;/g, '&');
            return string;
        },

        htmlspecialchars: function ( string, quote_style, charset, double_encode )
        {
            var optTemp = 0,
                i = 0,
                noquotes = false;
            if(typeof quote_style==='undefined' || quote_style===null)
            {
                quote_style = 2;
            }
            string = typeof string != 'string' ? '' : string;

            string = string.toString();
            if(double_encode !== false){
                string = string.replace(/&/g, '&amp;');
            }
            string = string.replace(/</g, '&lt;').replace(/>/g, '&gt;');
            var OPTS = {
                'ENT_NOQUOTES': 0,
                'ENT_HTML_QUOTE_SINGLE': 1,
                'ENT_HTML_QUOTE_DOUBLE': 2,
                'ENT_COMPAT': 2,
                'ENT_QUOTES': 3,
                'ENT_IGNORE': 4
            };
            if(quote_style===0)
            {
                noquotes = true;
            }
            if(typeof quote_style !== 'number')
            {
                quote_style = [].concat(quote_style);
                for (i = 0; i < quote_style.length; i++)
                {
                    if(OPTS[quote_style[i]]===0)
                    {
                        noquotes = true;
                    }
                    else if(OPTS[quote_style[i]])
                    {
                        optTemp = optTemp | OPTS[quote_style[i]];
                    }
                }
                quote_style = optTemp;
            }
            if(quote_style & OPTS.ENT_HTML_QUOTE_SINGLE)
            {
                string = string.replace(/'/g, '&#039;');
            }
            if(!noquotes)
            {
                string = string.replace(/"/g, '&quot;');
            }
            return string;
        },

        ajaxResultCheck: function ( res )
        {

            if( typeof res != 'object' )
            {
                try
                {
                    res = JSON.parse(res);
                }
                catch(e)
                {
                    this.toast( 'Error!', 'unsuccess' );
                    return false;
                }
            }

            if( typeof res['status'] == 'undefined' )
            {
                this.toast( 'Error!', 'unsuccess' );
                return false;
            }

            if( res['status'] == 'error' )
            {
                this.toast( typeof res['error_msg'] == 'undefined' ? 'Error!' : res['error_msg'], 'unsuccess' );
                return false;
            }

            if( res['status'] == 'ok' )
                return true;

            // else

            this.toast( 'Error!', 'unsuccess' );
            return false;
        },

        ajax: function ( action , params , func , loading, fnOnError )
        {
            loading = loading === false ? false : true;

            if( loading )
            {
                booknetic.loading(true);
            }

            if( params instanceof FormData)
            {
                params.append('action', 'bkntc_' + action);
            }
            else
            {
                params['action'] = 'bkntc_' + action;
            }

            params = bookneticHooks.doFilter( 'ajax_' + action, params );

            var ajaxObject =
                {
                    url: BookneticChangeStatusData.ajax_url,
                    method: 'POST',
                    data: params,
                    success: function ( result )
                    {
                        if( loading )
                        {
                            booknetic.loading( 0 );
                        }

                        if( booknetic.ajaxResultCheck( result, fnOnError ) )
                        {
                            try
                            {
                                result = JSON.parse(result);
                            }
                            catch(e)
                            {

                            }
                            if( typeof func == 'function' )
                                func( result );
                        }
                        else if( typeof fnOnError == 'function' )
                        {
                            fnOnError();
                        }
                    },
                    error: function (jqXHR, exception)
                    {
                        if( loading )
                        {
                            booknetic.loading( 0 );
                        }

                        booknetic.toast( jqXHR.status + ' error!' );

                        if( typeof fnOnError == 'function' )
                        {
                            fnOnError();
                        }
                    }
                };

            if( params instanceof FormData)
            {
                ajaxObject['processData'] = false;
                ajaxObject['contentType'] = false;
            }

            $.ajax( ajaxObject );

        },

        toastTimer: 0,

        toast: function(title , type , duration )
        {
            $("#booknetic-toastr").remove();

            if( this.toastTimer )
                clearTimeout(this.toastTimer);

            $("body").append(this.options.templates.toast);

            $("#booknetic-toastr").hide().fadeIn(300);

            type = type === 'unsuccess' ? 'unsuccess' : 'success';

            $("#booknetic-toastr .booknetic-toast-img > img").attr('src', BookneticChangeStatusData.assets_url + 'icons/' + type + '.svg');

            $("#booknetic-toastr .booknetic-toast-description").text(title);

            duration = typeof duration != 'undefined' ? duration : 1000 * ( title.length > 48 ? parseInt(title.length / 12) : 4 );

            this.toastTimer = setTimeout(function()
            {
                $("#booknetic-toastr").fadeOut(200 , function()
                {
                    $(this).remove();
                });
            } , typeof duration != 'undefined' ? duration : 4000);
        },

        timeZoneOffset: function()
        {
            if( BookneticChangeStatusData.client_time_zone == 'off' )
                return  '-';

            if ( window.Intl && typeof window.Intl === 'object' )
            {
                return Intl.DateTimeFormat().resolvedOptions().timeZone;
            }
            else
            {
                return new Date().getTimezoneOffset();
            }
        },

        reformatTimeFromCustomFormat: function ( time )
        {
            let parts = time.match( /^([0-9]{1,2}):([0-9]{1,2})\s(am|pm)$/i );

            if ( parts )
            {
                let hours = parseInt( parts[ 1 ] );
                let minutes = parseInt( parts[ 2 ] );
                let ampm = parts[ 3 ].toLowerCase();

                if ( ampm === 'pm' && hours < 12 ) hours += 12;
                if ( ampm === 'am' && hours === 12 ) hours = 0;

                if ( hours < 10 ) hours = '0' + hours.toString();
                if ( minutes < 10 ) minutes = '0' + minutes.toString();

                return hours + ':' + minutes;
            }

            return time;
        }

    };

    $(document).ready( function()
    {

        let token = BookneticChangeStatusData.token.split('.');
        let status = JSON.parse(atob(token[1]))['title'];
        let label = $('#label');
        let labelText = label.text()
        let labelSuccess = label.data('success-message');

        if ( labelText.includes('{status}') )
        {
            label.text(labelText.replace( '{status}', status ))
        }
        if ( labelSuccess.includes('{status}') )
        {
            label.data('success-message', labelSuccess.replace('{status}', status));
        }

        $(document).one('click' , '#btnChangeStatus' , function() {

            if( !$('#btnChangeStatus').hasClass(('btn--changed')) ){
                $('#btnChangeStatus').removeClass('btn--change');
                $('#btnChangeStatus').addClass('btn--waiting');
            }

            let data = new FormData();
            data.append('bkntc_token', BookneticChangeStatusData.token)

            booknetic.ajax ('change_status', data, function(result) {
                let container = $('.label');

                container.text( container.data('success-message') );

                $('#btnChangeStatus').removeClass('btn--waiting');
                $('#btnChangeStatus').addClass('btn--changed');

            },false,function () {
                $('#btnChangeStatus').removeClass('btn--waiting');
                $('.btn__text').text('Error');
                $('#btnChangeStatus').addClass('btn--error');

            })
        })

    });
})(jQuery);

