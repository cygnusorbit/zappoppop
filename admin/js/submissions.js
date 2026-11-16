(function(window, $) {
    'use strict';

    $(document).ready(function() {
        if (!window.ZapPopPopSubmissions) return;

        if (window.ZapPopPopSubmissions.Search) {
            window.ZapPopPopSubmissions.Search.init();
        }

        if (window.ZapPopPopSubmissions.Actions) {
            window.ZapPopPopSubmissions.Actions.init();
        }

        if (window.ZapPopPopSubmissions.Modal) {
            window.ZapPopPopSubmissions.Modal.init();
        }
    });
})(window, jQuery);
