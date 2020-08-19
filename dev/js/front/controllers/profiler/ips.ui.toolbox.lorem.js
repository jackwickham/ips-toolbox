;( function( $, _, undefined ) {
    'use strict';
    ips.createModule( 'ips.ui.toolbox.lorem', function() {
        // Functions that become public methods
        var respond = function( elem, options, e ) {
            $( '.ipsButton', elem ).on( 'click', function( e ) {

                e.preventDefault();
                let ajax = ips.getAjax();
                ajax( {
                        type: 'POST',
                        data: 'type=' + $( '[name="toolbox_lorem_type"]' ).val() + '&amount=' +
                            $( '[name="toolbox_lorem_amount"]' ).val(),
                        url: ips.getSetting( 'baseURL' ) + '?app=toolbox&module=bt&controller=bt&do=loremValues',
                        dataType: 'json',
                        bypassRedirect: true,
                        success: function( data ) {
                            copy( data.text );

                        },
                    },
                );
            } );

        };
        var copy = function( text, content ) {

            try {
                let message = 'Lorem Ipsum text copied to clipboard!';
                var textArea = document.createElement( 'textarea' );
                textArea.style.position = 'fixed';
                textArea.style.top = 0;
                textArea.style.left = 0;
                textArea.style.width = '2em';
                textArea.style.height = '2em';
                textArea.style.padding = 0;
                // Clean up any borders.
                textArea.style.border = 'none';
                textArea.style.outline = 'none';
                textArea.style.boxShadow = 'none';
                // Avoid flash of white box if rendered for any reason.
                textArea.style.background = 'transparent';
                textArea.value = text;
                document.body.appendChild( textArea );

                textArea.select();
                var successful = document.execCommand( 'copy' );
                document.body.removeChild( textArea );
                console.log( successful );
                if ( successful ) {
                    ips.ui.flashMsg.show( message );
                }
            } catch ( err ) {
                window.location.replace( text );
            }
        };

        // Register this module as a widget to enable the data API and
        // jQuery plugin functionality
        ips.ui.registerWidget( 'toolboxlorem', ips.ui.toolbox.lorem );

        // Expose public methods
        return {
            respond: respond,
        };
    } );
}( jQuery, _ ) );
