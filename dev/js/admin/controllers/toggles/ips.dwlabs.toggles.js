;(function ($, _, undefined) {
    "use strict";
    ips.createModule('ips.dtcode.toggles', function () {
        var respond = function (elem, options, e) {
            $(elem).click(
                function (e) {
                    e.preventDefault();
                    var el = $('#tab_' + options.id);
                    if (el.data('isOpen') == 1) {
                        $(this).find('i:first').removeClass('fa-rotate-180');
                        el.slideUp().removeData('isOpen');
                    }
                    else {
                        $(this).find('i:first').addClass('fa-rotate-180');
                        el.slideDown().data('isOpen', 1);
                    }

                });
        };
        ips
            .ui
            .registerWidget('dtcodetoggle', ips.dtcode.toggles, ['id']);
        return {
            respond: respond
        };
    });
}(jQuery, _));
