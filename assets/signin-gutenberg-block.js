(function ( element, blocks, editor, components )
{
    var el					= element.createElement,
        registerBlockType	= blocks.registerBlockType;

    var iconEl = el('svg', { width: 14, height: 18 },
        el(
            'g',
            {},
            el( 'path', { fill: '#FB3E6E', d: "M10,4.5 C10,6.98517258 8.1168347,9 5.7963921,9 L0.000671974239,9 L0.000671974239,1.99951386 C-0.0270736763,0.924525916 0.807830888,0.0298036698 1.86650604,0 L5.7963921,0 C6.92240656,0.00400258212 7.99707785,0.478775271 8.76750662,1.31259115 C9.56615869,2.17496304 10.0074388,3.31626599 10,4.5 Z" } ) ,
            el( 'path', { fill: '#6C70DC', d: "M12.4778547,8.65245263 C11.5268442,7.63485938 10.1979969,7.05491011 8.80519796,7.04959388 L1.94478295,7.04959388 C0.84260796,7.01900347 -0.0269497359,6.10225269 0.000699371406,5 L0.000699371406,7.04959388 L0.000699371406,7.04959388 L0.000699371406,15.9506232 C-0.0281989986,17.0533556 0.842062899,17.9708488 1.94478295,18.0002171 L8.80519796,18.0002171 C11.6741804,18.0002171 14,15.548786 14,12.5293953 C14.008613,11.0896923 13.4637038,9.70170097 12.4778547,8.65245263 Z" } )
        )
    );

    registerBlockType( 'booknetic/signin',
        {
            title: 'Booknetic Sign In',
            icon: iconEl,
            category: 'booknetic',
            attributes:
                {
                    shortCode: {
                        type: 'string',
                        default: '[booknetic-signin]'
                    }
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

