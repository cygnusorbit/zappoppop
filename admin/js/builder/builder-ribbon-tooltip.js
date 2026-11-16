/* ZAPPOPPOP_RIBBON_TOOLTIP: small bubble below a ribbon element on hover/focus. Text = the (type) in the title, Title Case */
(function (window, document) {
    'use strict';
    if (window.__zppRibbonTip) { return; }
    window.__zppRibbonTip = true;

    /* ZAPPOPPOP_HDR_TIPS: top-header icon buttons + both canvas (+) buttons reuse this bubble; their title is shown verbatim */
    var HDR_SEL = '.zappoppop-builder-header .zappoppop-btn-icon, .zappoppop-builder-header .zappoppop-hdr-icon-btn, '
        + '.zappoppop-builder-header .zappoppop-btn-tool, .zappoppop-builder-header .zappoppop-viewport-btn, '
        + '.zappoppop-builder-header .zappoppop-save-caret, #zappoppop-add-canvas-btn, #zappoppop-settings-add-canvas-bullet';
    var SEL = '.zappoppop-ribbon-tool, #zappoppop-alignment-toolbar .zappoppop-align-btn, ' + HDR_SEL;

    function isHdr(btn) {
        try { return !!(btn.matches && btn.matches(HDR_SEL)); } catch (e) { return false; }
    }
    var tip = null, current = null;

    function titleCase(s) {
        return String(s).replace(/[_-]+/g, ' ').replace(/\s+/g, ' ').replace(/^\s+|\s+$/g, '')
            .toLowerCase().replace(/(^|\s)([a-z0-9])/g, function (m, a, b) { return a + b.toUpperCase(); });
    }

    function labelFor(btn) {
        /* ZAPPOPPOP_THEME_TIP: the visible icon is the sun in light mode and the moon in dark mode; the label names that icon */
        if (btn.id === 'zappoppop-btn-theme-toggle') {
            return document.body.classList.contains('theme-light')
                ? (btn.getAttribute('data-zpp-day') || 'Day Mode')
                : (btn.getAttribute('data-zpp-night') || 'Night Mode');
        }
        if (isHdr(btn)) {
            return String(btn.getAttribute('data-zpp-title') || btn.getAttribute('title') || btn.getAttribute('aria-label') || '').replace(/^\s+|\s+$/g, '');
        }
        var raw = btn.getAttribute('data-zpp-title') || btn.getAttribute('title') || '';
        var m = raw.match(/\(([^()]+)\)\s*$/);
        if (m) { return titleCase(m[1]); }
        return titleCase(btn.getAttribute('data-type') || raw);
    }

    function prime(btn) {
        var t = btn.getAttribute('title');
        if (t) {
            btn.setAttribute('data-zpp-title', t);
            if (isHdr(btn)) {
                if (!btn.hasAttribute('aria-label')) { btn.setAttribute('aria-label', t); }
            } else {
                btn.setAttribute('aria-label', labelFor(btn));
            }
            btn.removeAttribute('title');
        }
    }

    function ensureTip() {
        if (tip) { return tip; }
        tip = document.createElement('div');
        tip.className = 'zappoppop-ribbon-tip';
        tip.setAttribute('role', 'tooltip');
        document.body.appendChild(tip);
        return tip;
    }

    function show(btn) {
        prime(btn);
        current = btn;
        var t = ensureTip();
        var lbl = labelFor(btn);
        t.textContent = lbl;
        if (btn.id === 'zappoppop-btn-theme-toggle') { btn.setAttribute('aria-label', lbl); }
        t.className = 'zappoppop-ribbon-tip is-on';
        var r = btn.getBoundingClientRect();
        var w = t.offsetWidth, vw = document.documentElement.clientWidth;
        var left = r.left + r.width / 2 - w / 2;
        left = Math.max(6, Math.min(left, vw - w - 6));
        t.style.left = Math.round(left) + 'px';
        t.style.top = Math.round(r.bottom + 8) + 'px';
        t.style.setProperty('--zpp-tip-x', Math.round(r.left + r.width / 2 - left) + 'px');
    }

    function hide() {
        current = null;
        if (tip) { tip.className = 'zappoppop-ribbon-tip'; }
    }

    function target(e) {
        var n = e.target;
        return (n && n.closest) ? n.closest(SEL) : null;
    }

    document.addEventListener('mouseover', function (e) {
        var b = target(e);
        if (b && b !== current) { show(b); }
    }, true);
    document.addEventListener('mouseout', function (e) {
        var b = target(e);
        if (b && !(e.relatedTarget && b.contains(e.relatedTarget))) { hide(); }
    }, true);
    document.addEventListener('focusin', function (e) { var b = target(e); if (b) { show(b); } }, true);
    document.addEventListener('focusout', function (e) { if (target(e)) { hide(); } }, true);
    document.addEventListener('mousedown', hide, true);
    document.addEventListener('dragstart', hide, true);
    window.addEventListener('scroll', hide, true);
    window.addEventListener('resize', hide);

    function init() {
        var list = document.querySelectorAll(SEL);
        for (var i = 0; i < list.length; i++) { prime(list[i]); }
    }
    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', init); } else { init(); }
})(window, document);
