(function($)
{
    "use strict";

    function __( key )
    {
        return key in BookneticDataFP.localization ? BookneticDataFP.localization[ key ] : key;
    }

    let booknetic = {

        options: {
            'templates': {
                'loader': '<div class="booknetic-loader"></div>',
                'toast': '<div id="booknetic-toastr"><div class="booknetic-toast-img"><img></div><div class="booknetic-toast-details"><span class="booknetic-toast-description"></span></div><div class="booknetic-toast-remove"><i class="fa fa-times"></i></div></div>'
            }
        },

        localization: {
            month_names: [ __('January'), __('February'), __('March'), __('April'), __('May'), __('June'), __('July'), __('August'), __('September'), __('October'), __('November'), __('December') ],
            day_of_week: [ __('Sun'), __('Mon'), __('Tue'), __('Wed'), __('Thu'), __('Fri'), __('Sat'), __('Sun') ] ,
        },

        toastTimer: 0,

        urlParams: function ( key )
        {
            let queryString = window.location.search;
            let urlParams = new URLSearchParams(queryString);
            return urlParams.get(key);
        },

        parseHTML: function ( html )
        {
            let range = document.createRange();
            return range.createContextualFragment( html );
        },

        loading: function ( onOff )
        {
            $('body .booknetic-loader').remove();

            if( typeof onOff === 'undefined' || onOff )
            {
                $('body').append(booknetic.options.templates.loader);
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

            var ajaxObject =
                {
                    url: BookneticDataFP.ajax_url,
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

        select2Ajax: function ( select, action, parameters )
        {
            var params = {};
            params['action'] = 'bkntc_' + action;

            select.select2({
                theme: 'bootstrap',
                placeholder: __('select'),
                allowClear: true,
                ajax: {
                    url: BookneticDataFP.ajax_url,
                    dataType: 'json',
                    type: "POST",
                    data: function ( q )
                    {
                        var sendParams = params;
                        sendParams['q'] = q['term'];

                        if( typeof parameters == 'function' )
                        {
                            var additionalParameters = parameters( $(this) );

                            for (var key in additionalParameters)
                            {
                                sendParams[key] = additionalParameters[key];
                            }
                        }
                        else if( typeof parameters == 'object' )
                        {
                            for (var key in parameters)
                            {
                                sendParams[key] = parameters[key];
                            }
                        }

                        return sendParams;
                    },
                    processResults: function ( result )
                    {
                        if( booknetic.ajaxResultCheck( result ) )
                        {
                            try
                            {
                                result = JSON.parse(result);
                            }
                            catch(e)
                            {

                            }

                            return result;
                        }
                    }
                }
            });
        },

        zeroPad: function(n, p)
        {
            p = p > 0 ? p : 2;
            n = String(n);
            return n.padStart(p, '0');
        },

        toast: function(title , type , duration )
        {
            $("#booknetic-toastr").remove();

            if( this.toastTimer )
                clearTimeout(this.toastTimer);

            $("body").append(this.options.templates.toast);

            $("#booknetic-toastr").hide().fadeIn(300);

            type = type === 'unsuccess' ? 'unsuccess' : 'success';

            $("#booknetic-toastr .booknetic-toast-img > img").attr('src', BookneticDataFP.assets_url + 'icons/' + type + '.svg');

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

    };

    $(document).ready( function()
    {
        $(document).on('click', '.booknetic_forgot_password_btn', function ()
        {
            let form        = $(this).closest('.booknetic_forgot_password'),
                email		= form.find('#booknetic_email').val();

            booknetic.ajax('forgot_password', { email: email }, function ( result )
            {
                form.find('.booknetic_step_1').hide();
                form.find('.booknetic_resend_activation').hide();
                form.find('.booknetic_step_2').fadeIn(200);
                setTimeout( function ()
                {
                    form.find('.booknetic_resend_activation').slideDown(200);
                }, 60000);
            });

            return false;
        }).on('click', '.booknetic_complete_forgot_password_btn', function ()
        {
            let form            = $(this).closest('.booknetic_forgot_password'),
                password1	    = form.find('#booknetic_password1').val(),
                password2		= form.find('#booknetic_password2').val();

            let data = new FormData();

            data.append('password1', password1);
            data.append('password2', password2);
            data.append('token', form.data('token'));

            booknetic.ajax('complete_forgot_password', data, function ( result )
            {
                form.find('.booknetic_step_1').fadeOut(200, function ()
                {
                    form.find('.booknetic_step_2').fadeIn(200);
                });
            });
        }).on('click', '.booknetic_resend_activation_link', function ()
        {
            let form        = $(this).closest('.booknetic_forgot_password'),
                email		= form.find('#booknetic_email').val();

            booknetic.ajax('resend_forgot_password_link', { email: email }, function ( result )
            {
                form.find('.booknetic_resend_activation').hide();

                setTimeout( function ()
                {
                    form.find('.booknetic_resend_activation').slideDown(200);
                }, 60000);
            });
        }).on('submit', '.booknetic_form', function ()
        {
            $(this).find('.booknetic_forgot_password_btn').click();
            return false;
        });

    });

})(jQuery);

