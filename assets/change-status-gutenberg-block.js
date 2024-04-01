(function ( element, blocks, editor, components )
{
    var el					= element.createElement,
        Fragment			= element.Fragment,
        InspectorControls	= editor.InspectorControls,
        TextControl			= components.TextControl,
        registerBlockType	= blocks.registerBlockType;

    var iconEl = el('svg', { width: 14, height: 18 },
        el(
            'g',
            {},
            el( 'path', { fill: '#FB3E6E', d: "M10,4.5 C10,6.98517258 8.1168347,9 5.7963921,9 L0.000671974239,9 L0.000671974239,1.99951386 C-0.0270736763,0.924525916 0.807830888,0.0298036698 1.86650604,0 L5.7963921,0 C6.92240656,0.00400258212 7.99707785,0.478775271 8.76750662,1.31259115 C9.56615869,2.17496304 10.0074388,3.31626599 10,4.5 Z" } ) ,
            el( 'path', { fill: '#6C70DC', d: "M12.4778547,8.65245263 C11.5268442,7.63485938 10.1979969,7.05491011 8.80519796,7.04959388 L1.94478295,7.04959388 C0.84260796,7.01900347 -0.0269497359,6.10225269 0.000699371406,5 L0.000699371406,7.04959388 L0.000699371406,7.04959388 L0.000699371406,15.9506232 C-0.0281989986,17.0533556 0.842062899,17.9708488 1.94478295,18.0002171 L8.80519796,18.0002171 C11.6741804,18.0002171 14,15.548786 14,12.5293953 C14.008613,11.0896923 13.4637038,9.70170097 12.4778547,8.65245263 Z" } )
        )
    );

    registerBlockType( 'booknetic/changestatus',
        {
            title: 'Booknetic Change Status',
            icon: iconEl,
            category: 'booknetic',
            attributes:
                {
                    label: {
                        type: 'string',
                        default: '',
                    },
                    successLabel: {
                        type: 'string',
                        default: '',
                    },
                    button: {
                        type: 'string',
                        default: '',
                    },
                    successButton: {
                        type: 'string',
                        default: '',
                    },
                    shortCode: {
                        type: 'string',
                        default: '[booknetic-change-status]'
                    }
                },

            edit: function ( props ) {

                function onChangeLabel( name )
                {
                    props.setAttributes( { label: name } );
                }

                function onChangeSuccessLabel( name )
                {
                    props.setAttributes( { successLabel: name } );
                }

                function onChangeButton( name )
                {
                    props.setAttributes( { button: name } );
                }

                function onChangeSuccessButton( name )
                {
                    props.setAttributes( {successButton: name} );
                }

                function shortCode( props )
                {
                    var attrs = [];

                    if( props.attributes.label !=='' )
                    {
                        let label = props.attributes.label
                        label = label.replaceAll('"' , "'" , label);
                        attrs.push( 'label="' + label +'"' )
                    }
                    if( props.attributes.successLabel !=='' )
                    {
                        let successLabel = props.attributes.successLabel
                        successLabel = successLabel.replaceAll('"' , "'" , successLabel);
                        attrs.push( 'successLabel="' + successLabel +'"' )
                    }
                    if( props.attributes.button !=='' )
                    {
                        let button = props.attributes.button
                        button = button.replaceAll('"' , "'" , button);
                        attrs.push( 'button="' + button +'"' )
                    }
                    if( props.attributes.successButton !=='' )
                    {
                        let successButton = props.attributes.successButton
                        successButton = successButton.replaceAll('"', "'", successButton);
                        attrs.push( 'successButton="' + successButton +'"')
                    }

                    var shortCode = '[booknetic-change-status' + (attrs.length ? ' ' : '') + attrs.join(' ') + ']';

                    props.setAttributes( { shortCode: shortCode } );

                    return shortCode;
                }

                return (
                    el(
                        Fragment,
                        null,
                        el(
                            InspectorControls,
                            null,
                            el(
                                TextControl,
                                {
                                    label: 'Label',
                                    value: props.attributes.label,
                                    onChange: onChangeLabel
                                }
                            ),
                            el(
                                TextControl,
                                {
                                    label: 'Success Label',
                                    value: props.attributes.successLabel,
                                    onChange: onChangeSuccessLabel
                                }
                            ),
                            el(
                                TextControl,
                                {
                                    label: 'Change Button Text',
                                    value: props.attributes.button,
                                    onChange: onChangeButton
                                }
                            ),
                            el(
                                TextControl,
                                {
                                    label: 'Change Success Button Text',
                                    value: props.attributes.successButton,
                                    onChange: onChangeSuccessButton
                                }
                            ),
                        ),
                        el(
                            'div',
                            null,
                            shortCode( props )
                        )
                    )
                );

            },


            save: function( props ) {

                return el(
                    'div',
                    null,
                    props.attributes.shortCode
                );
            },

        } );

})(
    wp.element,
    wp.blocks,
    wp.blockEditor,
    wp.components
);

