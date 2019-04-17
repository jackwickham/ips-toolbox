;(function ($, _, undefined) {
    "use strict";
    ips.createModule('ips.dtprofiler.debug', function () {
        var respond = function (elem, options, e) {
            var el = $(elem);
            if (!el.data('_debugObj')) {
                var d = _debugObj(el);
                d.init(el.data('url'), el);
                el.data('_debugObj', d);
            }
            $('body').bind('beforeunload', function () {
                var obj = el.data('_debugObj');
                obj.abort();
            });
        };
        ips.ui.registerWidget('dtprofilerdebug', ips.dtprofiler.debug);
        return {
            respond: respond
        };
    });
    var _debugObj = function () {
        var ajax = null;
        var current = null;
        var aurl;
        var el;
        var init = function (url, elem) {
            aurl = url + '&do=debug';
            el = elem;
            ajax = ips.getAjax();
            _debug();
        };
        var abort = function () {
            current.abort();
        };
        var _debug = function () {
            current = ajax({
                type: "POST",
                data: 'last=' + $('#elProfiledebug', el).attr('data-last'),
                url: aurl,
                dataType: "json",
                bypassRedirect: true,
                success: function (data) {
                    var countEl = el.find('#elProfiledebug').find('.dtprofilerCount');

                    if (!data.hasOwnProperty('error')) {
                        $('#elProfiledebug_list', el).append(data.items);
                        var count = Number(countEl.attr('data-count'));
                        count = Number(data.count) + count;
                        countEl.html(count).attr('data-count', count);
                        countEl.parent().addClass('dtprofilerFlash');
                        $('#elProfiledebug', el).attr('data-last', data.last);
                        if ($('#elProfiledebug', el).hasClass('ipsHide')) {
                            $('#elProfiledebug', el).removeClass('ipsHide');
                        }
                        countEl.parent().addClass('dtprofilerFlash');
                    }
                },
                complete: function (data) {
                    _debug();
                },
                error: function (data) {
                }
            });
        };

        return {
            init: init,
            abort: abort
        }
    }
}(jQuery, _));
