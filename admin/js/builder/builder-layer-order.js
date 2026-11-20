/**
 * ZapPopPop Builder: Inspector section order + accordion.   (zpp-lo-dom)
 * Source: Settings > Builder > Element / Layer Settings (zappoppop_vars.layer_settings.order / .tree / .selectors).
 * Order: the Inspector forces `display:block !important` on its tab panes, so CSS `order` cannot work. Instead the existing section
 * nodes are re-inserted in the saved order inside their own container, level by level (pane -> wrapper -> card). The same elements
 * are moved (nothing is cloned or re-rendered), so every binding, id and value stays; the original order is restored before each
 * re-run (selection change / Inspector re-render).
 * Accordion: a section's title bar opens / closes the section through one class; its inputs stay in the DOM.
 * Presentation only: Inspector DOM ids, JS model, pre-save flush, AJAX whitelist and front renderer are untouched, so a reordered or
 * collapsed setting keeps and saves its value exactly as before. Layer drag / push mechanics are not involved.
 */
(function (window, $) {
    'use strict';

    var cfg = window.zappoppop_vars && window.zappoppop_vars.layer_settings;
    if (!cfg || !cfg.selectors || !cfg.tree) { return; }

    var order = cfg.order || {};
    var hasOrder = Object.keys(order).length > 0;
    var ATTR = 'data-zpp-ls-type';
    var DRAWER_ID = 'zappoppop-inspector-drawer';
    var TABS = ['basic', 'style', 'logic', 'data', 'advanced'];
    var HEAD = 'zpp-acc-head';
    var CLOSED = 'zpp-acc-closed';
    var READY = 'zpp-acc-ready';
    var LS_KEY = 'zppInspAccordion';
    var OBSERVE = { attributes: true, attributeFilter: [ATTR], childList: true, subtree: true };
    var moved = [];
    var obs = null;
    var raf = 0;

    function drawer() { return document.getElementById(DRAWER_ID); }

    function all(selector, type) {
        var s = String(selector || '').replace(/\{type\}/g, type || '');
        if (!s) { return []; }
        try { return Array.prototype.slice.call(document.querySelectorAll(s)); } catch (e) { return []; }
    }

    function meta(tab, key) { return cfg.selectors[tab] && cfg.selectors[tab][key]; }

    function skipTag(n) { return /^(SCRIPT|STYLE|TEMPLATE)$/.test(n.tagName); }

    function kidsOf(container) {
        return Array.prototype.filter.call(container.children, function (n) { return !skipTag(n); });
    }

    // The direct child of the container that holds (or is) the element.
    function childOf(container, el) {
        var n = el;
        while (n && n.parentNode !== container) { n = n.parentNode; }
        return (n && n.nodeType === 1) ? n : null;
    }

    function sequence(keys, saved) {
        var out = [], seen = {};
        (saved || []).forEach(function (k) { if (keys.indexOf(k) !== -1 && !seen[k]) { seen[k] = 1; out.push(k); } });
        keys.forEach(function (k) { if (!seen[k]) { seen[k] = 1; out.push(k); } });
        return out;
    }

    // Put the given children back-to-back, in the given order, where the container's last child of that set sits now.
    function place(container, list) {
        var live = list.filter(function (n) { return n.parentNode === container; });
        var cur = kidsOf(container).filter(function (n) { return live.indexOf(n) !== -1; });
        if (!cur.length) { return; }
        var ref = cur[cur.length - 1].nextSibling;
        live.forEach(function (n) { container.insertBefore(n, ref); });
    }

    function arrange(container, groups) {
        var kids = kidsOf(container), seq = [];
        groups.forEach(function (g) {
            g.slice().sort(function (a, b) { return kids.indexOf(a) - kids.indexOf(b); }).forEach(function (n) {
                if (kids.indexOf(n) !== -1 && seq.indexOf(n) === -1) { seq.push(n); }
            });
        });
        if (seq.length < 2) { return; }
        var p = 0;
        var next = kids.map(function (n) { return seq.indexOf(n) === -1 ? n : seq[p++]; });
        if (!next.some(function (n, i) { return n !== kids[i]; })) { return; }
        moved.push({ c: container, orig: kids });
        place(container, next);
    }

    function clearAll() {
        moved.reverse().forEach(function (m) { place(m.c, m.orig); });
        moved = [];
    }

    // Order the keys inside a container, then descend into each wrapper child that holds several of them.
    function orderWithin(container, tab, type, keys) {
        var map = {};
        keys.forEach(function (k) {
            var m = meta(tab, k);
            map[k] = m ? all(m.s, type).filter(function (el) { return el !== container && container.contains(el); }) : [];
        });
        var live = keys.filter(function (k) { return map[k].length; });
        arrange(container, live.map(function (k) {
            return map[k].map(function (el) { return childOf(container, el); }).filter(Boolean);
        }));
        kidsOf(container).forEach(function (c) {
            var sub = live.filter(function (k) {
                return map[k].some(function (el) { return el !== c && c.contains(el); }) && !map[k].some(function (el) { return el === c; });
            });
            if (sub.length > 1) { orderWithin(c, tab, type, sub); }
        });
    }

    function applyOrder(type) {
        var o = (hasOrder && type) ? order[type] : null;
        if (!o) { return; }
        TABS.forEach(function (tab) {
            var pane = document.getElementById('insp-tab-' + tab);
            var tree = cfg.tree[tab] || [];
            if (!pane) { return; }
            if (o[tab]) {
                orderWithin(pane, tab, type, sequence(tree.map(function (e) { return e.k; }), o[tab]));
            }
            tree.forEach(function (e) {
                var slot = o[tab + '|' + e.k], m = meta(tab, e.k);
                if (!slot || !m || !e.r.length) { return; }
                var hostEl = all(m.s, type).filter(function (el) { return pane.contains(el); })[0];
                if (hostEl) { orderWithin(hostEl, tab, type, sequence(e.r, slot)); }
            });
        });
    }

    /* ---- Accordion ---- */
    function accState() {
        try { return JSON.parse(window.localStorage.getItem(LS_KEY) || '{}') || {}; } catch (e) { return {}; }
    }
    function accSave(st) {
        try { window.localStorage.setItem(LS_KEY, JSON.stringify(st)); } catch (e) { /* storage unavailable: state is per page view */ }
    }

    function buildAccordion() {
        var st = accState();
        TABS.forEach(function (tab) {
            var pane = document.getElementById('insp-tab-' + tab);
            if (!pane) { return; }
            (cfg.tree[tab] || []).forEach(function (e) {
                var m = meta(tab, e.k);
                if (!m || !m.s || e.k === 'element_settings') { return; }
                all(m.s, '').forEach(function (sec) {
                    if (!pane.contains(sec) || sec === pane || sec.classList.contains(READY)) { return; }
                    var head = sec.firstElementChild;
                    if (!head || sec.children.length < 2) { return; }
                    if (head.matches('input,select,textarea,button,label') || head.querySelector('input,select,textarea,button')) { return; }
                    var txt = (head.textContent || '').replace(/\s+/g, ' ').replace(/^\s+|\s+$/g, '');
                    if (!txt || txt.length > 60) { return; }
                    var id = tab + ':' + e.k, closed = !!st[id];
                    sec.classList.add(READY);
                    sec.classList.toggle(CLOSED, closed);
                    head.classList.add(HEAD);
                    head.setAttribute('role', 'button');
                    head.setAttribute('tabindex', '0');
                    head.setAttribute('aria-expanded', closed ? 'false' : 'true');
                    head.setAttribute('data-zpp-acc', id);
                });
            });
        });
    }

    function toggle(head) {
        var sec = head.parentNode;
        var closed = !sec.classList.contains(CLOSED);
        var st = accState(), id = head.getAttribute('data-zpp-acc');
        sec.classList.toggle(CLOSED, closed);
        head.setAttribute('aria-expanded', closed ? 'false' : 'true');
        if (closed) { st[id] = 1; } else { delete st[id]; }
        accSave(st);
    }

    function inDrawer(el) {
        var d = drawer();
        return !!(d && el && d.contains(el));
    }

    document.addEventListener('click', function (e) {
        var h = e.target && e.target.closest ? e.target.closest('.' + HEAD) : null;
        if (h && inDrawer(h)) { toggle(h); }
    });
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter' && e.key !== ' ' && e.key !== 'Spacebar') { return; }
        var h = e.target && e.target.classList && e.target.classList.contains(HEAD) ? e.target : null;
        if (h && inDrawer(h)) { e.preventDefault(); toggle(h); }
    });

    /* ---- Run: on selection (type attribute) and whenever the Inspector re-renders its panes ---- */
    /* ZPP_FOCUS_GUARD: moving a focused field in the DOM blurs it (and fires a native change that clamps half-typed values),
       so while a form control inside the Inspector has focus the re-order waits until the user leaves it. */
    var pendingRun = false;
    /* ZPP_LO_SIG: skip the remove/re-insert churn when nothing structural changed (was repainting the Content tab on every layer click) */
    var lastSig = '';
    function sig(d) {
        var s = d.getAttribute(ATTR) || '', n = d.querySelectorAll('[id]'), i;
        for (i = 0; i < n.length; i++) { s += '|' + n[i].id; }
        return s;
    }
    function editing(d) {
        var a = document.activeElement;
        return !!(a && d.contains(a) && /^(INPUT|TEXTAREA|SELECT)$/.test(a.tagName));
    }

    function run() {
        raf = 0;
        var d = drawer();
        if (!d) { return; }
        if (editing(d)) { pendingRun = true; return; }
        pendingRun = false;
        if (obs) { obs.disconnect(); }
        try {
            if (sig(d) !== lastSig) {
                clearAll();
                applyOrder(d.getAttribute(ATTR) || '');
                lastSig = sig(d);
            }
            buildAccordion();
        } finally {
            if (obs) { obs.takeRecords(); obs.observe(d, OBSERVE); }
        }
    }

    function schedule() {
        if (!raf) { raf = window.requestAnimationFrame(run); }
    }

    function boot() {
        var d = drawer();
        if (!d) { return false; }
        if (d.__zppLoObserved) { return true; }
        d.__zppLoObserved = true;
        d.addEventListener('focusout', function () {
            if (pendingRun) { window.setTimeout(schedule, 0); }
        }, false);
        obs = new window.MutationObserver(function (muts) {
            for (var i = 0; i < muts.length; i++) {
                if (muts[i].type === 'attributes' || muts[i].addedNodes.length) { schedule(); return; }
            }
        });
        obs.observe(d, OBSERVE);
        run();
        return true;
    }

    $(function () {
        if (boot()) { return; }
        var tries = 0, t = window.setInterval(function () {
            if (boot() || ++tries > 40) { window.clearInterval(t); }
        }, 300);
    });
})(window, jQuery);
