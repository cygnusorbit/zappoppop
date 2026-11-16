/* ZAPPOPPOP_DATA_TAB_SYNC: Data tab helpers (dynamic default row visibility) */
(function (window, $) {
    'use strict';
    if (!$ || window.__zppDataTabSync) { return; }
    window.__zppDataTabSync = true;

    function sync() {
        $('#prop-dynamic-param-row').toggle($('#prop-dynamic-default').is(':checked'));
    }

    $(document)
        .off('change.zppDataTab click.zppDataTab', '#prop-dynamic-default')
        .on('change.zppDataTab click.zppDataTab', '#prop-dynamic-default', function () {
            setTimeout(sync, 0);
        });

    $(sync);
})(window, window.jQuery);
