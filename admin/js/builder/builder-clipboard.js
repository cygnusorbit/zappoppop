/* ZAPPOPPOP_CLIPBOARD: Ctrl/Cmd+C copies the selected layer(s), Ctrl/Cmd+V pastes them (cascading offset, one undo step) */
(function (window, document, $) {
    'use strict';
    if (window.__zppClipBound) { return; }
    window.__zppClipBound = true;

    var KEY = 'zappoppop_layer_clipboard_v1';
    var STEP = 15;
    var mem = null;          // in-memory copy; localStorage mirrors it so it also works after a reload / in another builder tab
    var count = 0;           // pastes since the last copy (drives the cascade)
    var lastTs = 0;
    var pasteCanvas = 0;     // canvas of the last copy / paste: the cascade restarts on a different one

    function core() { return window.ZapPopPopBuilderCore; }
    function canvas() { return window.ZapPopPopBuilderCanvas; }
    function cid(r) { return String(r == null ? '' : r).replace(/^#/, '').replace(/^zappoppop-el-/, '').replace(/^el-/, '').replace(/^\s+|\s+$/g, ''); }
    function num(v) { var n = parseFloat(v); return isNaN(n) ? 0 : n; }
    function z(el) { return parseInt(el.zIndex, 10) || 10; }
    function list() {
        var C = canvas();
        if (C && typeof C.getActiveElements === 'function') { return C.getActiveElements(); }
        var c = core();
        return (c && c.state && c.state.canvases && c.state.canvases[c.state.currentCanvas || 1]) || [];
    }
    function findEl(id) {
        var k = cid(id), l = list();
        for (var i = 0; i < l.length; i++) { if (l[i] && cid(l[i].id) === k) { return l[i]; } }
        return null;
    }
    function isPreview() {
        var C = canvas();
        return !!((C && C.isPreview) || (window.ZapPopPopBuilderPreview && window.ZapPopPopBuilderPreview.isOpen));
    }
    function isTyping(t) {
        if (!t) { return false; }
        var tag = (t.tagName || '').toLowerCase();
        if (tag === 'input' || tag === 'textarea' || tag === 'select' || t.isContentEditable) { return true; }
        return !!(t.closest && t.closest('.note-editor, [contenteditable="true"], [role="slider"]'));
    }
    function setPos(el, left, top) {
        el.left = left; el.top = top;
        if (el.x !== undefined) { el.x = left; }
        if (el.y !== undefined) { el.y = top; }
    }
    function selectedIds() {
        var M = window.ZapPopPopMultiSelect, c = core();
        if (M && M.ids().length >= 2) { return M.ids(); }
        var a = c && c.state && c.state.activeId;
        return a ? [cid(a)] : [];
    }

    // ------------------------------------------------------------- storage
    function writeClip(o) {
        mem = o;
        try { window.localStorage.setItem(KEY, JSON.stringify(o)); } catch (e) {}
    }
    function readClip() {
        var o = null;
        try {
            var raw = window.localStorage.getItem(KEY);
            if (raw) { o = JSON.parse(raw); }
        } catch (e) { o = null; }
        if (o && o.items && o.items.length) { return o; }
        return mem;
    }

    // ------------------------------------------------------------- copy
    function copy() {
        var c = core(), ids = selectedIds(), items = [];
        if (!c || !ids.length) { return false; }
        ids.forEach(function (id) {
            var el = findEl(id);
            if (el) { items.push(JSON.parse(JSON.stringify(el))); }
        });
        if (!items.length) { return false; }
        items.sort(function (a, b) { return z(a) - z(b); });
        writeClip({ ts: Date.now(), canvas: c.state.currentCanvas || 1, items: items });
        count = 0;
        lastTs = mem.ts;
        pasteCanvas = mem.canvas;
        return true;
    }

    // ------------------------------------------------------------- paste
    function axisShift(min, max, off, limit) {
        if (min + off >= 0 && max + off <= limit) { return off; }       // fits as asked
        if (min - off >= 0 && max - off <= limit) { return -off; }      // no room that way: cascade the other way
        return Math.max(-min, Math.min(limit - max, off));
    }
    function paste() {
        var clip = readClip(), C = canvas(), c = core();
        if (!clip || !clip.items || !clip.items.length || !C || !c || !c.state) { return false; }
        var all = list();
        if (clip.ts !== lastTs) { lastTs = clip.ts; count = 0; }

        var cur = c.state.currentCanvas || 1;
        if (pasteCanvas !== cur) { count = 0; }
        var off = STEP * ((clip.canvas === cur ? 1 : 0) + count);
        var items = clip.items.map(function (it) { return JSON.parse(JSON.stringify(it)); });

        var minL = Infinity, minT = Infinity, maxR = -Infinity, maxB = -Infinity;
        items.forEach(function (it) {
            var l = num(it.left !== undefined ? it.left : it.x), t = num(it.top !== undefined ? it.top : it.y);
            minL = Math.min(minL, l); minT = Math.min(minT, t);
            maxR = Math.max(maxR, l + num(it.width || 180)); maxB = Math.max(maxB, t + num(it.height || 42));
        });
        var dx = off, dy = off;
        var root = document.getElementById('zappoppop-canvas-elements-root');
        if (root && !(C.isOverflowAllowed && C.isOverflowAllowed())) {
            dx = axisShift(minL, maxR, off, root.offsetWidth);
            dy = axisShift(minT, maxB, off, root.offsetHeight);
        }

        var maxZ = all.reduce(function (mx, it) { return Math.max(mx, z(it)); }, 10);
        var stamp = Date.now().toString(36);
        var ids = [];
        items.forEach(function (it, i) {
            var base = (it.type === 'text' || it.type === 'paragraph') ? 'Paragraph' : (it.name || (function(s){s=String(s||'');return s.charAt(0).toUpperCase()+s.slice(1);})(it.type || 'Layer'));
            it.id = 'layer_' + stamp + '_' + Math.random().toString(36).substr(2, 4) + i;
            it.name = /\(Copy\)$/.test(base) ? base : base + ' (Copy)';
            it.zIndex = maxZ + 5 * (i + 1);
            setPos(it, num(it.left !== undefined ? it.left : it.x) + dx, num(it.top !== undefined ? it.top : it.y) + dy);
            all.push(it);
            ids.push(it.id);
        });
        count++;
        pasteCanvas = cur;

        C.renderCanvas();
        var L = window.ZapPopPopBuilderLayers, M = window.ZapPopPopMultiSelect;
        if (L && typeof L.renderLayers === 'function') { L.renderLayers(); }
        if (ids.length >= 2 && M && typeof M.select === 'function') { M.select(ids); }
        else { C.selectElement(ids[ids.length - 1], true); }
        if (typeof c.pushHistory === 'function') { c.pushHistory(); }
        return true;
    }

    // ------------------------------------------------------------- keys (capture phase: ahead of the legacy handlers)
    document.addEventListener('keydown', function (e) {
        if (!document.getElementById('zappoppop-canvas-box') || isPreview() || isTyping(e.target)) { return; }
        if (!(e.metaKey || e.ctrlKey) || e.altKey || e.shiftKey) { return; }
        var k = String(e.key || '').toLowerCase();
        var letter = /^[a-z]$/.test(k) ? k : String(e.code || '').replace(/^Key/, '').toLowerCase();
        var done = false;
        if (letter === 'c') {
            var sel = window.getSelection ? String(window.getSelection()) : '';
            if (sel.length) { return; }                 // the user is copying highlighted text: leave the browser alone
            done = copy();
        } else if (letter === 'v') {
            done = paste();
        }
        if (done) { e.preventDefault(); e.stopImmediatePropagation(); }
    }, true);

    window.ZapPopPopClipboard = { copy: copy, paste: paste };
})(window, document, jQuery);
