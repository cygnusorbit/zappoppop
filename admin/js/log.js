(function(window, $) {
    'use strict';

    $(document).ready(function() {
        if (!window.ZapPopPopLog) return;

        if (window.ZapPopPopLog.Search) {
            window.ZapPopPopLog.Search.init();
        }

        if (window.ZapPopPopLog.Payload) {
            window.ZapPopPopLog.Payload.init();
        }
    });
})(window, jQuery);
