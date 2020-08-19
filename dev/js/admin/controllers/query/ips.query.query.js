;( function($, _, undefined){
    "use strict";
    ips.controller.register('ips.admin.dtdevplus.query', {
        initialize: function () {
            this.on('change', '[id="elSelect_js_dtdevplus_ext_table"]', this._getFields);
        },
        _getFields: function(e){
            console.debug('yes');
            var url = ips.getSetting('dtdevplus_table_url');
            var ajax = ips.getAjax();
            ajax( {
                url: url+"&do=dtgetFields&table="+$(e.target).val(),
                type: "GET",
                success:function(data){
                    if( data.error == 0 ) {
                        $('#elSelect_js_dtdevplus_ext_field').replaceWith(data.html);
                    }
                }
            } );
        }
    });
}(jQuery, _));