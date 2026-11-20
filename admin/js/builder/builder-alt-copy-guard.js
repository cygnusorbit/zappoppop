/* ZPP_ALT_COPY_GUARD: the Alt/Option+drag "+" copy cursor must never outlive the drag.
   builder-alt-copy.js removes it on dragstop, but a re-render or a lost event can skip that. This clears it once no drag is running. */
(function (window, document) {
    'use strict';
    var CLS = 'zappoppop-copy-drag';

    function dragging() { return !!document.querySelector('.ui-draggable-dragging'); }
    function clear() {
        if (dragging()) { return; }
        if (document.body.classList.contains(CLS)) { document.body.classList.remove(CLS); }
        var g = document.querySelectorAll('.zappoppop-copy-ghost');
        for (var i = 0; i < g.length; i++) { if (g[i].parentNode) { g[i].parentNode.removeChild(g[i]); } }
    }
    function soon() { window.setTimeout(clear, 0); window.setTimeout(clear, 150); }

    ['mouseup', 'pointerup', 'touchend', 'dragend', 'keyup', 'contextmenu'].forEach(function (ev) { document.addEventListener(ev, soon, true); });
    window.addEventListener('blur', soon);
    window.addEventListener('mouseup', soon, true);
    document.addEventListener('visibilitychange', soon);
    window.setInterval(function () { if (document.body && document.body.classList.contains(CLS)) { clear(); } }, 400);
})(window, document);
