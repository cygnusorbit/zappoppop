(function(window, $) {
    'use strict';

    $(document).ready(function() {
        if (!window.ZapPopPopPayments) return;

        if (window.ZapPopPopPayments.Search) {
            window.ZapPopPopPayments.Search.init();
        }

        if (window.ZapPopPopPayments.Modal) {
            window.ZapPopPopPayments.Modal.init();
        }

        if (window.ZapPopPopPayments.Export) {
            window.ZapPopPopPayments.Export.init();
        }
    });
})(window, jQuery);
