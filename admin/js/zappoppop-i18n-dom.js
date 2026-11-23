/**
 * ZapPopPop admin DOM translator (loaded only when the site language is not English).
 * Swaps English UI text that JavaScript builds at runtime for the translated text in
 * window.zappoppopDomStrings (filled by zappoppop-i18n-catalog.js and the Pro catalog).
 * It changes ONLY exact text-node matches and the title / placeholder / aria-label attributes:
 * ids, classes, values and layer data are never touched, and the popup stage (the user's own content)
 * is skipped, so drag, push and save behaviour is unchanged.
 */
(function () {
    'use strict';
    if (!window.MutationObserver || !document.createTreeWalker) { return; }

    var ATTRS = ['title', 'placeholder', 'aria-label', 'data-tooltip', 'data-title'];
    var SKIP_TAG = { SCRIPT: 1, STYLE: 1, TEXTAREA: 1, CODE: 1, PRE: 1, NOSCRIPT: 1 };
    var SKIP_SEL = '.zappoppop-canvas-stage, .zappoppop-no-tr, [data-zpp-notr], [contenteditable=""], [contenteditable="true"], #adminmenumain, #adminmenuwrap, #wpadminbar, #wpfooter';
    var lookup = null, lookupSize = -1;

    function norm(s) { return s.replace(/\s+/g, ' ').replace(/^ | $/g, ''); }

    function dict() {
        var m = window.zappoppopDomStrings || {}, n = 0, k;
        for (k in m) { if (Object.prototype.hasOwnProperty.call(m, k)) { n++; } }
        if (lookup === null || n !== lookupSize) { lookup = m; lookupSize = n; }
        return lookup;
    }

    function skipped(el) {
        if (!el || el.nodeType !== 1) { return false; }
        if (SKIP_TAG[el.tagName]) { return true; }
        return !!(el.closest && el.closest(SKIP_SEL));
    }

    function text(node) {
        var v = node.nodeValue, d = dict(), k, t, lead, trail;
        if (!v || !/[A-Za-z]/.test(v)) { return; }
        k = norm(v);
        if (!Object.prototype.hasOwnProperty.call(d, k)) { return; }
        t = d[k];
        if (typeof t !== 'string' || t === k || skipped(node.parentNode)) { return; }
        lead = v.match(/^\s*/)[0];
        trail = v.match(/\s*$/)[0];
        node.nodeValue = lead + t + trail;
    }

    function attrs(el) {
        var d = dict(), i, a, v, k;
        if (el.nodeType !== 1 || skipped(el)) { return; }
        for (i = 0; i < ATTRS.length; i++) {
            a = ATTRS[i];
            v = el.getAttribute(a);
            if (!v) { continue; }
            k = norm(v);
            if (Object.prototype.hasOwnProperty.call(d, k) && typeof d[k] === 'string' && d[k] !== k) { el.setAttribute(a, d[k]); }
        }
    }

    function walk(root) {
        var tw, n;
        if (!root) { return; }
        if (root.nodeType === 3) { text(root); return; }
        if (root.nodeType !== 1 || skipped(root)) { return; }
        attrs(root);
        tw = document.createTreeWalker(root, 1 | 4, {
            acceptNode: function (x) { return (x.nodeType === 1 && (SKIP_TAG[x.tagName] || (x.matches && x.matches(SKIP_SEL)))) ? 2 : 1; }
        });
        while ((n = tw.nextNode())) {
            if (n.nodeType === 3) { text(n); } else { attrs(n); }
        }
    }

    var queue = [], scheduled = false;
    var raf = window.requestAnimationFrame || function (f) { return setTimeout(f, 16); };
    function flush() {
        var q = queue; queue = []; scheduled = false;
        for (var i = 0; i < q.length; i++) { if (q[i].isConnected !== false) { walk(q[i]); } }
    }
    function push(n) {
        queue.push(n);
        if (!scheduled) { scheduled = true; raf(flush); }
    }

    function start() {
        walk(document.body);
        new MutationObserver(function (list) {
            for (var i = 0; i < list.length; i++) {
                var m = list[i], j;
                if (m.type === 'childList') { for (j = 0; j < m.addedNodes.length; j++) { push(m.addedNodes[j]); } }
                else if (m.type === 'characterData') { push(m.target); }
                else if (m.type === 'attributes') { push(m.target); }
            }
        }).observe(document.body, { childList: true, subtree: true, characterData: true, attributes: true, attributeFilter: ATTRS });
    }

    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', start); } else { start(); }
    window.addEventListener('load', function () { walk(document.body); });
}());
