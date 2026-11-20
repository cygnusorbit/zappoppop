/* ZPP_REQUIRED_MOVE: Content tab. "Required" (+ its error message and the Autocomplete Attribute row) belong to the element's own settings block,
   not to the shared Animation & Behavior block. The three existing rows are re-parented into the visible element panel (#panel-elem-<type>) of the
   selected layer, so ids, bindings, the JS model, the flush, the AJAX whitelist and the renderer are untouched (only the DOM parent changes). */
(function (window, $) {
    'use strict';
    if (!$) { return; }

    var HOST = 'zappoppop-element-specific-settings';
    var ROWS = ['insp-row-gp-required', 'insp-row-gp-required-msg', 'insp-row-gp-autocomplete'];

    /* A closed element block must hide every row, including rows that Settings > Builder forces on with display:block !important (higher-specificity closed rule) */
    var D = 'html body #zappoppop-inspector-drawer#zappoppop-inspector-drawer';
    var CSS = [
        D + ' #zappoppop-element-specific-settings > .element-panel.zpp-ca-closed > *:not(:first-child)',
        D + ' #insp-section-gp-basic-Shared.zpp-ca-closed > *:not(:first-child)',
        D + ' #insp-section-conditional-logic.zpp-ca-closed > *:not(:first-child)',
        D + ' #insp-section-action-routing.zpp-ca-closed > *:not(:first-child)',
        D + ' .zappoppop-insp-section.zpp-tb-closed > *:not(:first-child)',
        D + ' .zappoppop-insp-section.zpp-acc-closed > *:not(:first-child)'
    ].join(',\n') + '{display:none !important}';
    /* Toggle rows in an element block (Required, Block Disposable Emails): label on the left, slide toggle on the right, one row */
    var ROWSEL = [D + ' #zappoppop-element-specific-settings > .element-panel > #insp-row-gp-required', D + ' #zappoppop-element-specific-settings > .element-panel #insp-row-email-disposable'].join(',');
    CSS += '\n' + ROWSEL + '{flex-direction:row !important;flex-wrap:nowrap !important;align-items:center !important;justify-content:space-between !important;gap:10px}';
    CSS += '\n' + ROWSEL.split(',').join(' > .zappoppop-prop-label,') + ' > .zappoppop-prop-label{width:auto !important;margin:0 !important;flex:1 1 auto}';
    CSS += '\n' + ROWSEL.split(',').join(' > .zappoppop-gp-switch,') + ' > .zappoppop-gp-switch{flex:0 0 auto;margin:0 !important}';
    function ensureCss() {
        if (document.getElementById('zpp-rm-css')) { return; }
        var st = document.createElement('style');
        st.id = 'zpp-rm-css';
        st.appendChild(document.createTextNode(CSS));
        (document.head || document.documentElement).appendChild(st);
    }

    function visiblePanel() {
        var host = document.getElementById(HOST);
        if (!host) { return null; }
        var list = host.children, i, p = null;
        for (i = 0; i < list.length; i++) {
            if (list[i].classList.contains('element-panel') && list[i].style.display !== 'none' && window.getComputedStyle(list[i]).display !== 'none') { p = list[i]; break; }
        }
        return p;
    }

    function sync() {
        var p = visiblePanel();
        if (!p) { return; }
        ROWS.forEach(function (id) {
            var r = document.getElementById(id);
            if (r && r.parentNode !== p) { p.appendChild(r); }
        });
        /* keep the order: Required, Error Message, Autocomplete (appendChild above only moves a row that is elsewhere) */
        var prev = null;
        ROWS.forEach(function (id) {
            var r = document.getElementById(id);
            if (!r || r.parentNode !== p) { return; }
            if (prev && prev.nextElementSibling !== r) { p.insertBefore(r, prev.nextSibling); }
            prev = r;
        });
    }

    function burst() { [0, 60, 300, 900].forEach(function (ms) { window.setTimeout(sync, ms); }); }

    $(function () {
        ensureCss();
        sync();
        var host = document.getElementById(HOST), t = null;
        if (host && window.MutationObserver) {
            new MutationObserver(function () { window.clearTimeout(t); t = window.setTimeout(sync, 30); }).observe(host, { attributes: true, attributeFilter: ['style', 'class'], subtree: true, childList: false });
        }
        $(document).on('click.zppReqMove', '.zappoppop-canvas-item, .zappoppop-layer-item, #zappoppop-inspector-drawer [data-tab]', burst);
        var n = 0, iv = window.setInterval(function () { sync(); if (++n > 20) { window.clearInterval(iv); } }, 500);
    });
})(window, window.jQuery);
