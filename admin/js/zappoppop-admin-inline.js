/* ZapPopPop admin: replaces inline event handlers. data-zappoppop-confirm / -nav / -noop. */
(function () {
    'use strict';
    function closest(el, sel) {
        while (el && el.nodeType === 1) {
            if (el.matches ? el.matches(sel) : (el.msMatchesSelector && el.msMatchesSelector(sel))) { return el; }
            el = el.parentNode;
        }
        return null;
    }
    document.addEventListener('click', function (e) {
        var noop = closest(e.target, '[data-zappoppop-noop]');
        if (noop) { e.preventDefault(); return; }
        var c = closest(e.target, 'a[data-zappoppop-confirm], button[data-zappoppop-confirm], input[data-zappoppop-confirm]');
        if (c && !window.confirm(c.getAttribute('data-zappoppop-confirm'))) { e.preventDefault(); }
    }, true);
    document.addEventListener('submit', function (e) {
        var f = e.target;
        if (f && f.getAttribute && f.getAttribute('data-zappoppop-confirm') && !window.confirm(f.getAttribute('data-zappoppop-confirm'))) {
            e.preventDefault();
        }
    }, true);
    document.addEventListener('change', function (e) {
        var s = e.target;
        if (s && s.getAttribute && s.getAttribute('data-zappoppop-nav') && s.value) {
            window.location.href = s.value;
        }
    }, true);
}());
