(function () {
    'use strict';
    function init() {
        var gf  = document.querySelector('input[name="settings[google_fonts]"]');
        var off = document.querySelector('input[name="settings[disable_google_fonts]"]');
        if (!gf || !off) { return; }
        function sync() {
            if (off.checked) { gf.checked = false; }
            gf.disabled = off.checked;
        }
        off.addEventListener('change', sync);
        sync();
    }
    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', init); } else { init(); }
})();
