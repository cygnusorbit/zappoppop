/**
 * ZapPopPop colour input throttle (shared, modular).
 * Native <input type="color"> (the "old" picker) and the alpha picker's hidden model input fire an `input`
 * event on every pointer move; each one re-renders the canvas and lags the builder. This coalesces them to at
 * most one event per GAP ms (leading edge fires immediately, the trailing value is always delivered), then
 * flushes before `change`, blur, click/keydown (so Save / Pre-save Flush never sees a stale model) and page hide.
 * Handlers are untouched: they still receive a normal bubbling `input` event. ES5; Chrome, Safari, Firefox, Edge.
 */
(function (window, document) {
    'use strict';
    if (window.ZapPopPopColorThrottle) { return; }

    var GAP = 70, pend = null, timer = 0, last = 0;

    function now() { return (window.Date && Date.now) ? Date.now() : +new Date(); }
    function isColor(t) {
        return !!t && t.nodeType === 1 && t.tagName === 'INPUT' && (t.type === 'color' || t.hasAttribute('data-zpp-alpha'));
    }
    function emit(el) {
        var e;
        if (typeof window.Event === 'function') { e = new window.Event('input', { bubbles: true }); }
        else { e = document.createEvent('Event'); e.initEvent('input', true, true); }
        e.zppThrottled = true;
        last = now();
        el.dispatchEvent(e);
    }
    function flush() {
        var el = pend;
        if (timer) { window.clearTimeout(timer); timer = 0; }
        pend = null;
        if (el && document.documentElement.contains(el)) { emit(el); }
    }

    window.addEventListener('input', function (e) {
        var t = e.target, wait;
        if (e.zppThrottled || !isColor(t)) { return; }
        e.stopImmediatePropagation();
        if (pend && pend !== t) { flush(); }
        pend = t;
        wait = GAP - (now() - last);
        if (wait <= 0) { flush(); return; }
        if (!timer) { timer = window.setTimeout(flush, wait); }
    }, true);

    /* flush before anything that could read the model */
    window.addEventListener('change', function () { if (pend) { flush(); } }, true);
    window.addEventListener('focusout', function () { if (pend) { flush(); } }, true);
    document.addEventListener('mousedown', function () { if (pend) { flush(); } }, true);
    document.addEventListener('keydown', function () { if (pend) { flush(); } }, true);
    window.addEventListener('pagehide', function () { if (pend) { flush(); } }, false);

    window.ZapPopPopColorThrottle = { flush: flush };
})(window, document);
