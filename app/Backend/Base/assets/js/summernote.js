(function ($) {
    booknetic.summernote =  function (node,toolbar = [],keywordList = {},height=350)
    {

        function keywordsDropdownKeydown(e) {

            let currentLi = $(this).find('ul li.focused');

            if( e.which === 40 ){
                e.preventDefault();
                let next = hasPrevOrNextElement(currentLi,'next');
                if(next){
                    currentLi.removeClass('focused');
                    currentLi.removeAttr('data-hidden');
                    next.addClass('focused');
                    next.find('a').focus();
                }
            }else if(e.which===38){
                e.preventDefault();
                let prev = hasPrevOrNextElement(currentLi,'prev');
                if(prev){
                    currentLi.removeClass('focused');
                    currentLi.removeAttr('data-hidden');
                    prev.addClass('focused');
                    prev.find('a').focus();
                }
            }else if(e.which===13){
                node.summernote('editor.restoreRange');
                node.summernote('editor.focus');
                node.summernote("editor.insertText", '{'+currentLi.data('code')+'}');
                $(this).parent().removeClass('open');
                $(this).removeClass('active');
            }
            else{
                $(this).find("input").focus();
            }



            function hasPrevOrNextElement(element,nav) {
                let result;
                if( nav === 'next' ){
                    result = element.nextAll().not('[data-hidden]');
                }else if( nav === 'prev' ){
                    result = element.prevAll().not('[data-hidden]');
                }
                if(result.length){
                    return result.first();
                }
                return false;
            }

        }

        let keywordButton = function (context) {
            let ui = $.summernote.ui;
            let button = ui.buttonGroup([
                ui.button({
                    className: 'dropdown-toggle',
                    contents: '<span class="fa fa-info"></span> ' + booknetic.__('keywords') + ' <span class="note-icon-caret"></span>',
                    data: {
                        toggle: 'dropdown'
                    },
                    click:  () => {
                        let input = $('.dropdown-toggle').next().find('.bkntc_keywords_search input');
                        input.val('');
                        input.trigger('input');

                        setTimeout( ()=> { input.focus() },50);

                        $('.bkntc_keywords_dropdown ul li:first-child').addClass('focused');

                    }
                }),
                ui.dropdown({
                    className: 'bkntc_keywords_dropdown',
                    contents: function () {
                        let str = "<div class='bkntc_keywords_search'><input autocomplete='off' id='search' type='text' placeholder='Search...'></div><ul>";

                        Object.keys(keywordList).forEach((key)=>{
                            str += '<li title="' + keywordList[key] +' - {' + key +'}" data-title="'+ keywordList[key] +'" data-code="'+ key +'"><a href="#"><span>' + keywordList[key] +'</span><span>{' + key +'}</span></a></li>';
                        });

                        str+="</ul>";

                        return str;
                    },
                    callback: function ($dropdown) {
                        $dropdown.find('#search').on('input',function (e) {
                            let val = $(this).val();
                            $dropdown.find("li").each(function () {
                                if ($(this).text().search(new RegExp(val, "i")) < 0) {
                                    $(this).hide();
                                    $(this).attr('data-hidden',true);
                                } else {
                                    $(this).show();
                                    $(this).removeAttr('data-hidden');
                                }

                                $(this).removeClass('focused');
                                $dropdown.find('ul li').not('[data-hidden]').eq(0).addClass('focused');
                            });
                        });
                        $dropdown.find('li').each(function () {
                            $(this).click(function () {
                                node.summernote('editor.restoreRange');
                                context.invoke("insertText", '{' + $(this).data('code') + '}');
                                $dropdown.parent().removeClass('open');
                                $dropdown.removeClass('active');
                            });
                        });
                    },
                    click: function (e) {
                        e.stopPropagation();
                    }
                })
            ]);
            return button.render();
        }

        let summerNoteObj = {
            dialogsInBody: true,
            placeholder: '',
            tabsize: 2,
            height: height,
            toolbar: toolbar,
            buttons: {
                keywords: keywordButton,
            },
            hint: {
                mentions: Object.keys(keywordList).map(   (key) =>  key.match( /[a-zA-Z0-9_]+/g ) ),
                match: /\B\{(\w*)$/,
                search: function (keyword, callback)
                {
                    callback($.grep(this.mentions, function (item)
                    {
                        return item[0].indexOf(keyword) == 0;
                    }));
                },
                content: function ( item )
                {
                    return '{' + item + '}';
                }
            }
        };

        if(Object.keys(keywordList).length>0)
        {
            summerNoteObj.toolbar.push(['keywords',['keywords']]);
        }

        node.summernote(summerNoteObj);

        if( node.val().trim() != '' )
        {
            node.summernote('code',node.val());
        }

        node.parent().find('.bkntc_keywords_dropdown').on('keydown',keywordsDropdownKeydown);
    }

    booknetic.checkShortCodes = function(node) {
        // some users add shortcodes which generates a link such as:
        // payment_link, change_status and forgets to uncheck protocol checkbox
        // thus resulting in bad url
        const regex = /(https?:\/\/)(\{\w+\})/g;

        if ( !!node.match(regex) ) {

            return node.replace(regex, function(protocolWithShortcode, protocol, shortcode) {

                return protocolWithShortcode;

            })

        }
        return node;

    }

    booknetic.summernoteReplace = function(node,isHTML=false)
    {
        node = booknetic.checkShortCodes(node.summernote('code'));

        if(isHTML)
        {
            return booknetic.htmlspecialchars_decode(node);
        }
        return node;
    }

})(jQuery)