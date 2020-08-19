;( function($, _, undefined){
    "use strict";
    ips.createModule('ips.dtprofiler.search', function(){
        // Functions that become public methods
        var respond = function (elem, options, e) {
            let search = JSON.parse(options.sdata),
                key = options.key;
            $('#search'+key, elem).on('keyup', function(e){
                var lookup = $(this).val(),
                id = 'elProfile'+key+'_list';
                $('#'+id).find('li').remove();

                $.each(search, function (index, data) {
                    let name = index;

                    if (name.indexOf(lookup) != -1) {
                        let lis= $('<li>'),
                            a = $('<a>'),
                            s = $('<span>'),
                            n = data.name;

                        if( data.hasOwnProperty('extra') ){
                            n = n+ '<span class="dtProfileExtra">'+data.extra+'</span>';
                        }
                        if( data.hasOwnProperty('url')) {
                            a.attr('href', data.url).html(n);
                            if( data.hasOwnProperty('dialog') ){
                                a.attr('data-ipsdialog', 1);
                            }
                            lis.html(a).addClass('ipsPad_half dtProfilerSearch');
                        }
                        else{
                            a = n;
                        }
                        lis.html(a).addClass('ipsPad_half dtProfilerSearch');
                        $(elem).next().append(lis);
                        if( data.hasOwnProperty('dialog') ) {
                            $(document).trigger('contentChange', [$(elem).next()]);
                        }
                    }
                });
            })
        };

        // Register this module as a widget to enable the data API and
        // jQuery plugin functionality
        ips.ui.registerWidget( 'dtpsearch', ips.dtprofiler.search, ['sdata','key'] );

        // Expose public methods
        return {
            respond: respond
        };
    });
}(jQuery, _));
