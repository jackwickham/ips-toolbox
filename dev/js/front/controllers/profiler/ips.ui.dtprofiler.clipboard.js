;( function( $, _, undefined ) {
    'use strict';
    ips.createModule( 'ips.ui.dtprofiler.clipboard', function() {
        // Functions that become public methods
        var respond = function( elem, options, e ) {
            e.preventDefault();

            let target = $( e.currentTarget ),
                url = target.attr( 'href' );

            if ( url === undefined ) {
                copy( $( '#pnoteMessage' ).text(), true );
            } else {
                copy( url, false );
            }

        };
        var init = function() {
            $( document ).on( 'submitDialog', function( e, data ) {
                let response = data.response;
                if ( response.type === 'toolboxClipBoard' ) {
                    copy( response.text, false );
                }
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
                if ( successful ) {
                    ips.ui.flashMsg.show( message );
                } else {
                    window.location.replace( text );
                }
            } catch ( err ) {
                window.location.replace( text );
            }
        };
        // Register this module as a widget to enable the data API and
        // jQuery plugin functionality
        ips.ui.registerWidget( 'toolboxclipboard', ips.ui.dtprofiler.clipboard, [],
            { lazyLoad: true, lazyEvent: 'click' } );

        // Expose public methods
        return {
            init: init,
            respond: respond,
        };
    } );
}( jQuery, _ ) );
