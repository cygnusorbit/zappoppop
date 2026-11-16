/* ZAPPOPPOP_RIBBON_ICON_TOGGLES: ribbon "Hide / Unhide Layer Panel" button (remembered per browser) + live tooltip text for the Snap Object toggle */
(function (window, document) {
    'use strict';
    if (window.__zppRibbonToggles) { return; }
    window.__zppRibbonToggles = true;

    var KEY = 'zappoppop_layers_panel_hidden';
    var CLS = 'zpp-layers-hidden';

    function btn() { return document.getElementById('zappoppop-layers-visibility'); }
    function isHidden() { return document.body.classList.contains(CLS); }

    function refreshTip(node) {
        // the bubble hides on mousedown; show it again with the new state text while the pointer / focus is still on the control
        if (window.ZapPopPopRibbonTip) { window.ZapPopPopRibbonTip.refresh(node); }
    }

    function paint() {
        var b = btn();
        if (b) { b.setAttribute('aria-pressed', isHidden() ? 'false' : 'true'); }
    }

    function setHidden(hide, remember) {
        document.body.classList.toggle(CLS, !!hide);
        if (remember !== false) {
            try { window.localStorage.setItem(KEY, hide ? '1' : '0'); } catch (e) {}
        }
        paint();
        refreshTip(btn());
    }

    function init() {
        var saved = null;
        try { saved = window.localStorage.getItem(KEY); } catch (e) { saved = null; }
        if (saved === '1') { document.body.classList.add(CLS); }
        paint();

        document.addEventListener('click', function (e) {
            var t = e.target && e.target.closest ? e.target.closest('#zappoppop-layers-visibility') : null;
            if (!t) { return; }
            e.preventDefault();
            setHidden(!isHidden());
        });
        document.addEventListener('change', function (e) {
            var t = e.target;
            if (t && t.id === 'zappoppop-snap-object') { refreshTip(t); }
        });
    }

    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', init); } else { init(); }
    window.ZapPopPopRibbonToggles = { setLayersHidden: function (h) { setHidden(!!h); }, layersHidden: isHidden };
})(window, document);
