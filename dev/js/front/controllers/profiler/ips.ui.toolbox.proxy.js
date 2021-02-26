;( function($, _, undefined){
    "use strict";
    ips.createModule('ips.ui.toolbox.proxy', function(){
        /**
         * Respond to a dialog trigger
         *
         * @param   {element}   elem        The element this widget is being created on
         * @param   {object}    options     The options passed
         * @param   {event}     e           if lazyload, event that is fire
         * @returns {void}
         */
        var ajax = ips.getAjax(),
            respond = function (elem, options, e) {
             e.preventDefault();
                ajax({
                    type: "GET",
                    url: ips.getSetting('baseURL')+'index.php?app=toolbox&module=bt&controller=bt&do=proxy',
                    bypassRedirect: true,
                    showLoading: true,
                    beforeSend: function(){
                        ips.ui.flashMsg.show(ips.getString('toolbox_doing_proxies'));
                    },
                    complete: function (data) {
                        ips.ui.flashMsg.show(ips.getString('toolbox_done_proxies'));
                    }
                });
        };

        // Register this module as a widget to enable the data API and
        // jQuery plugin functionality
        ips.ui.registerWidget( 'toolboxproxy', ips.ui.toolbox.proxy, [],{ lazyLoad: true, lazyEvent: 'click'} );

        // Expose public methods
        return {
            respond: respond
        };
    });
}(jQuery, _));
