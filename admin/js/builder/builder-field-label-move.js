/* ZPP_FL_MOVE: the label controls (Hide Label, Label Position, Label Text, Field Background, Field Border) belong to the element's own settings block, not to the Layer Name section.
   The existing #zpp-fl-box is re-parented into the visible element panel (#panel-elem-<type>) of the selected layer, so ids, bindings, the JS model,
   the flush, the AJAX whitelist and the renderer are untouched (only the DOM parent changes). Types without an element panel keep it under Layer Name. */
(function (window, $) {
    'use strict';
    if (!$) { return; }
    var HOST = 'zappoppop-element-specific-settings', BOX = 'zpp-fl-box';

    function visiblePanel() {
        var host = document.getElementById(HOST);
        if (!host) { return null; }
        var list = host.children, i;
        for (i = 0; i < list.length; i++) {
            if (list[i].classList.contains('element-panel') && list[i].style.display !== 'none' && window.getComputedStyle(list[i]).display !== 'none') { return list[i]; }
        }
        return null;
    }

    function sync() {
        var b = document.getElementById(BOX);
        if (!b) { return; }
        var p = visiblePanel(), home = document.getElementById('insp-section-layer-name'), target = p || home;
        if (target && b.parentNode !== target) { target.appendChild(b); }
        else if (p && b.parentNode === p && p.lastElementChild !== b) { p.appendChild(b); } /* stay below Required / Error Message / Autocomplete */
    }

    function burst() { [0, 60, 300, 900].forEach(function (ms) { window.setTimeout(sync, ms); }); }

    $(function () {
        sync();
        var host = document.getElementById(HOST), t = null;
        if (host && window.MutationObserver) {
            new MutationObserver(function () { sync(); /* ZPP_FLICKER_FIX: before paint */ window.clearTimeout(t); t = window.setTimeout(sync, 30); }).observe(host, { attributes: true, attributeFilter: ['style', 'class'], subtree: true, childList: false });
        }
        $(document).on('click.zppFlMove', '.zappoppop-canvas-item, .zappoppop-layer-item, #zappoppop-inspector-drawer [data-tab]', burst);
        var n = 0, iv = window.setInterval(function () { sync(); if (++n > 40) { window.clearInterval(iv); } }, 500);
    });
})(window, window.jQuery);
