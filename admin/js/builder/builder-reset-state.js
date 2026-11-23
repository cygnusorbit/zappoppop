/**
 * ZapPopPop Builder: reset button state (ZPP_RESET_STATE).
 * Like the colour-picker revert icon: dull while the field still holds its default, bright as soon as it changed.
 * Covers the number-field resets (.zpp-ty-reset, default in data-default) and the Spacing group resets (default 0).
 * Display only: it reads the inputs, never writes the model, so the data lifecycle is untouched.
 */
(function (window, $) {
    'use strict';
    if (!$) { return; }

    function dirtyField(btn) {
        var inp = btn.parentNode ? btn.parentNode.querySelector('input') : null, def = btn.getAttribute('data-default');
        if (!inp) { return false; }
        return String(inp.value) !== String(def === null ? '0' : def);
    }
    function dirtyGroup(btn) {
        var g = btn.closest ? btn.closest('.zpp-sp-group') : null, ins, i, v;
        if (!g) { return false; }
        ins = g.querySelectorAll('input.zpp-sp-in');
        for (i = 0; i < ins.length; i++) { v = String(ins[i].value); if (v !== '' && v !== '0') { return true; } }
        return false;
    }
    function refresh() {
        var list = document.querySelectorAll('#zappoppop-inspector-drawer .zpp-ty-reset, #zappoppop-inspector-drawer .zpp-sp-reset[data-zpp-sp-reset]'), i, b, dirty;
        for (i = 0; i < list.length; i++) {
            b = list[i];
            dirty = b.classList.contains('zpp-ty-reset') ? dirtyField(b) : dirtyGroup(b);
            if (b.disabled === dirty) { b.disabled = !dirty; }
        }
    }
    document.addEventListener('input', function () { window.setTimeout(refresh, 0); }, true);
    document.addEventListener('change', function () { window.setTimeout(refresh, 0); }, true);
    document.addEventListener('click', function () { window.setTimeout(refresh, 30); }, true);
    $(function () { window.setInterval(refresh, 250); });
})(window, window.jQuery);
