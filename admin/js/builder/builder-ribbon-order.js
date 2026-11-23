/* ZPP_ELEMENT_ORDER: the builder ribbon follows Settings > Builder > Element / Layer Settings (drag & drop order).
   Moves the existing tool buttons (never clones them), so every bound handler and draggable stays intact. Dividers keep their slots; Submit stays last. */
(function (window, $) {
    'use strict';
    function run() {
        var ls = window.zappoppop_vars && window.zappoppop_vars.layer_settings;
        var order = ls && $.isArray(ls.element_order) ? ls.element_order : [];
        var scroll = document.querySelector('#zappoppop-ribbon .zappoppop-ribbon-scroll');
        if (!order.length || !scroll || scroll.getAttribute('data-zpp-eo')) { return; }
        var btns = Array.prototype.filter.call(scroll.children, function (n) { return n.classList && n.classList.contains('zappoppop-ribbon-tool') && n.getAttribute('data-type'); });
        if (btns.length < 2) { return; }
        var byType = {}, seq = [], seen = {};
        btns.forEach(function (b) { byType[b.getAttribute('data-type')] = b; });
        order.forEach(function (t) { if (byType[t] && !seen[t]) { seq.push(byType[t]); seen[t] = 1; } });
        btns.forEach(function (b) { var t = b.getAttribute('data-type'); if (!seen[t]) { seq.push(b); seen[t] = 1; } });
        if (byType.submit) { seq = seq.filter(function (b) { return b !== byType.submit; }); seq.push(byType.submit); }
        var marks = btns.map(function (b) { var m = document.createComment('zpp-eo'); scroll.insertBefore(m, b); return m; });
        btns.forEach(function (b) { scroll.removeChild(b); });
        seq.forEach(function (b, i) { scroll.insertBefore(b, marks[i]); });
        marks.forEach(function (m) { scroll.removeChild(m); });
        seq.forEach(function (b, i) {
            ['title', 'data-zpp-title'].forEach(function (a) { /* the tooltip script moves title to data-zpp-title */
                var t = b.getAttribute(a) || '';
                if (/^\d+\.\s/.test(t)) { b.setAttribute(a, t.replace(/^\d+\.\s/, (i + 1) + '. ')); }
            });
        });
        scroll.setAttribute('data-zpp-eo', '1');
    }
    $(run);
})(window, window.jQuery);
