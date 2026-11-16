(function(window, $) {
    'use strict';

    $(document).ready(function() {
        if (!window.ZapPopPopFront) return;

        if (window.ZapPopPopFront.Modal) {
            window.ZapPopPopFront.Modal.init();
        }

        if (window.ZapPopPopFront.Elements) {
            window.ZapPopPopFront.Elements.init();
        }

        if (window.ZapPopPopFront.Form) {
            window.ZapPopPopFront.Form.init();
        }

        if (window.ZapPopPopFront.Triggers) {
            window.ZapPopPopFront.Triggers.init();
        }
    });
})(window, jQuery);
