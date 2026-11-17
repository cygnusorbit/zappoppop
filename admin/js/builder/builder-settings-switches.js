/* ZapPopPop Builder: Popup Settings drawer, every plain check-mark becomes a slide-toggle.
 * Class only (.zpp-switch): the input, its id, events and the JS model are untouched, so the data lifecycle is preserved.
 * Runs in a MutationObserver callback, i.e. before the first paint of lazily rendered modules (no check-mark flash).
 * ES5: Chrome, Safari, Firefox, Edge. */
(function (window, document) {
    'use strict';

    var TRACK = /(track|trk|slider|switch)/i;

    function isPlainCheckmark(cb) {
        var n = cb.nextElementSibling, cs;
        if (n && TRACK.test(String(n.className || ''))) { return false; }   /* module already draws its own track */
        if (cb.hasAttribute('data-zpp-noswitch')) { return false; }
        cs = window.getComputedStyle(cb);
        if (cs.opacity === '0') { return false; } /* visually hidden input of a custom toggle (visibility is inherited from the closed drawer, so it is not tested) */
        if (cs.position === 'absolute' && parseFloat(cs.width) < 3) { return false; }
        return true;
    }

    function scan(root) {
        var list = root.querySelectorAll('input[type="checkbox"]:not([data-zpp-sw])'), i, cb, plain;
        for (i = 0; i < list.length; i++) {
            cb = list[i];
            plain = isPlainCheckmark(cb);
            cb.setAttribute('data-zpp-sw', plain ? '1' : '0');
            if (plain) { cb.className += (cb.className ? ' ' : '') + 'zpp-switch'; }
        }
    }

    function init() {
        var drawer = document.getElementById('zappoppop-settings-drawer');
        if (!drawer) { return; }
        scan(drawer);
        if (typeof window.MutationObserver === 'function') {
            new window.MutationObserver(function (muts) {
                var i;
                for (i = 0; i < muts.length; i++) {
                    if (muts[i].addedNodes && muts[i].addedNodes.length) { scan(drawer); return; }
                }
            }).observe(drawer, { childList: true, subtree: true });
        }
    }

    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', init); } else { init(); }
})(window, document);
