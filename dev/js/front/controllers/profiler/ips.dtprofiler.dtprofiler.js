;( function( $, _, undefined ) {
    'use strict';
    ips.createModule( 'ips.dtprofiler.dtprofiler', function() {
        // Functions that become public methods
        let dialogId = null,
            respond = function( elements ) {

                let elem = $( elements );
                if ( !elem.data( '_respond' ) ) {
                    let h = elem.parent().outerHeight();
                    $( '.dtProfilerPlaceHolder' ).css( 'height', h );
                    $( window ).on( 'resize', function() {
                        let h = elem.parent().outerHeight();
                        $( '.dtProfilerPlaceHolder' ).css( 'height', h );
                    } );

                    // $(document).on('hideDialog', function () {
                    //     dialogId = null;
                    // });
                    //
                    // $(document).on('openDialog', function (e, data) {
                    //     if (dialogId !== null) {
                    //         $(document).trigger('closeDialog', {dialogID: dialogId});
                    //     }
                    //     dialogId = data.elemID + '_dialog';
                    //
                    // });

                    $( document ).on( 'click', function( e ) {
                        let el = $( e.target );
                        let parent = el.parents( 'div#dtProfilerBarContainer' );
                        if ( parent.length === 0 ) {
                            elem.find( 'ul.isOpen' ).
                                removeClass( 'isOpen' ).
                                slideUp().
                                parent().
                                find( 'i.dtprofilearrow' ).
                                removeClass( 'fa-rotate-180' );
                        }
                    } );

                    elem.find( '> li.isParent' ).on( 'click', function() {
                        closeDialog();
                        let el = $( this );
                        if ( el.is( 'i' ) ) {
                            el = el.parent( 'li' );
                        }

                        el.removeClass( 'dtprofilerFlash' );
                        let bottom = el.parents( 'div' ).outerHeight(),
                            id = el.attr( 'id' ) + '_list',
                            child = $( '#' + id ), left = el.position().left;

                        if ( !child.hasClass( 'isOpen' ) ) {
                            if ( child.hasClass( 'dtProfilerMaxWidth' ) ) {
                                left = 0;
                            } else {
                                child.show();
                                let cWidth = child.outerWidth();
                                let cPos = left + cWidth;
                                child.hide();
                                let windowWidth = $( window ).width();
                                if ( cPos > windowWidth ) {
                                    left = left - ( cPos - windowWidth );
                                }
                            }
                            elem.find( 'ul.isOpen' ).
                                removeClass( 'isOpen' ).
                                slideUp().
                                parent().
                                find( 'i.dtprofilearrow' ).
                                removeClass( 'fa-rotate-180' );
                            child.css( 'left', left ).css( 'bottom', bottom );
                            child.addClass( 'isOpen' ).slideDown();
                            el.find( 'i.dtprofilearrow' ).addClass( 'fa-rotate-180' );
                        } else {
                            child.removeClass( 'isOpen' );
                            child.slideUp();
                            el.find( 'i.dtprofilearrow' ).removeClass( 'fa-rotate-180' );
                        }
                    } );
                    elem.data( '_respond', 1 );
                }
            },
            closeDialog = function() {
                if ( dialogId !== null ) {
                    $( document ).trigger( 'closeDialog', { dialogID: dialogId } );
                }
            };

        // Register this module as a widget to enable the data API and
        // jQuery plugin functionality
        ips.ui.registerWidget( 'dtprofiler', ips.dtprofiler.dtprofiler );

        // Expose public methods
        return {
            respond: respond,
        };
    } );
}( jQuery, _ ) );
