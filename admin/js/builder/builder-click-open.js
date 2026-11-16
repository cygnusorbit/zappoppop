/* ZAPPOPPOP_CLICK_OPEN: gates for "click canvas -> Popup Settings" and "click element -> Layer Settings" (Settings > Builder) */
(function (window, $) {
    'use strict';

    function flag(key) {
        var v = window.zappoppop_vars && window.zappoppop_vars[key];
        return v === true || v === 1 || v === '1' || v === 'true';
    }

    function inspectorOpen() {
        var $d = $('#zappoppop-inspector-drawer');
        return $d.length > 0 && $d.hasClass('is-open');
    }

    window.ZapPopPopClickOpen = {
        // Clicking an empty area of the canvas may slide in Popup Settings
        canvas: function () {
            return flag('click_canvas_open_settings');
        },
        // Clicking an element on the canvas may slide in Layer Settings.
        // When Layer Settings is already open it keeps following the clicked element.
        element: function () {
            return flag('click_element_open_settings') || inspectorOpen();
        }
    };
})(window, jQuery);
