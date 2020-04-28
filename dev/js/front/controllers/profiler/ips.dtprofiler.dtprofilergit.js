;( function($, _, undefined){
    "use strict";
    ips.createModule('ips.dtprofiler.dtprofilergit', function(){
        // Functions that become public methods
        var respond = function (elem, options, e) {
            var ajax = ips.getAjax();
            var url = options.url;
            elem = $(elem);
            ajax({
                type: "GET",
                url: url,
                dataType: "json",
                bypassRedirect: true,
                success: function (data) {
                    if (data.hasOwnProperty('html')) {
                        elem.html( data.html );
                    }
                },
                complete: function (data) {
                }
            });
        };

        // Register this module as a widget to enable the data API and
        // jQuery plugin functionality
        ips.ui.registerWidget( 'dtprofilergit', ips.dtprofiler.dtprofilergit, ['url'] );

        // Expose public methods
        return {
            respond: respond
        };
    });
}(jQuery, _));
