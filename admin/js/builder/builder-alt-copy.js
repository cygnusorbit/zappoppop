/* ZAPPOPPOP_ALT_COPY: Alt/Option + drag leaves the original where it was and drops a copy where the mouse is released (shows the copy "+" cursor) */
(function (window, document, $) {
    'use strict';
    if (window.__zppAltCopyBound) { return; }
    window.__zppAltCopyBound = true;

    var altHeld = false;
    var run = null;          // current drag gesture

    function core() { return window.ZapPopPopBuilderCore; }
    function canvas() { return window.ZapPopPopBuilderCanvas; }
    function cid(r) { return String(r == null ? '' : r).replace(/^#/, '').replace(/^zappoppop-el-/, '').replace(/^el-/, '').replace(/^\s+|\s+$/g, ''); }
    function num(v) { var n = parseFloat(v); return isNaN(n) ? 0 : n; }
    function list() {
        var c = core();
        return (c && c.state && c.state.canvases && c.state.canvases[c.state.currentCanvas || 1]) || [];
    }
    function findEl(id) {
        var k = cid(id), l = list();
        for (var i = 0; i < l.length; i++) { if (l[i] && cid(l[i].id) === k) { return l[i]; } }
        return null;
    }
    function nodeOf(id) { return document.getElementById('el-' + cid(id)); }
    function nodeId(node) { return cid(node.getAttribute('data-id') || node.id); }
    function isAltKey(e) { return e.key === 'Alt' || e.keyCode === 18; }
    function setPos(el, left, top) {
        el.left = left; el.top = top;
        if (el.x !== undefined) { el.x = left; }
        if (el.y !== undefined) { el.y = top; }
    }

    // ------------------------------------------------------------- cursor + ghost feedback
    function sync() {
        if (!run) { return; }
        document.body.classList.toggle('zappoppop-copy-drag', altHeld);
        for (var i = 0; i < run.ghosts.length; i++) { run.ghosts[i].style.display = altHeld ? 'block' : 'none'; }
    }
    function cleanup() {
        if (run) {
            for (var i = 0; i < run.ghosts.length; i++) {
                if (run.ghosts[i].parentNode) { run.ghosts[i].parentNode.removeChild(run.ghosts[i]); }
            }
        }
        document.body.classList.remove('zappoppop-copy-drag');
        run = null;
    }

    document.addEventListener('keydown', function (e) {
        if (!isAltKey(e)) { return; }
        altHeld = true;
        if (run) { e.preventDefault(); sync(); }       // keeps Windows/Linux browsers from grabbing the menu bar mid-drag
    }, true);
    document.addEventListener('keyup', function (e) {
        if (!isAltKey(e)) { return; }
        altHeld = false;
        if (run) { e.preventDefault(); sync(); }
    }, true);
    document.addEventListener('mousedown', function (e) { altHeld = !!e.altKey; }, true);
    document.addEventListener('mousemove', function (e) {
        if (run && altHeld !== !!e.altKey) { altHeld = !!e.altKey; sync(); }   // heals a missed keyup (focus change, OS shortcut)
    }, true);
    window.addEventListener('blur', function () { altHeld = false; sync(); });

    // ------------------------------------------------------------- drag lifecycle (jQuery UI draggable events bubble to document)
    $(document).on('dragstart.zppAltCopy', '.zappoppop-canvas-item', function (e, ui) {
        if (!ui || !ui.position) { return; }              // native HTML5 dragstart has no ui
        var pid = nodeId(this);
        var M = window.ZapPopPopMultiSelect;
        var idList = (M && M.ids().length >= 2 && M.has(pid)) ? M.ids() : [pid];
        var members = [], p = null, root = document.getElementById('zappoppop-canvas-elements-root');
        idList.forEach(function (id) {
            var el = findEl(id), node = nodeOf(id);
            if (!el || !node || el.locked || el.hidden) { return; }
            var l = parseFloat(node.style.left), t = parseFloat(node.style.top);
            var m = { id: cid(id), el: el, node: node, left: isNaN(l) ? num(el.left) : l, top: isNaN(t) ? num(el.top) : t };
            members.push(m);
            if (m.id === pid) { p = m; }
        });
        if (!p) { return; }
        cleanup();
        run = { pid: pid, p0: { left: p.left, top: p.top }, members: members, ghosts: [] };
        if (e.altKey || (e.originalEvent && e.originalEvent.altKey)) { altHeld = true; }
        if (root) {
            members.forEach(function (m) {
                var g = document.createElement('div');
                g.className = 'zappoppop-copy-ghost';
                g.style.left = m.left + 'px';
                g.style.top = m.top + 'px';
                g.style.width = m.node.offsetWidth + 'px';
                g.style.height = m.node.offsetHeight + 'px';
                root.appendChild(g);
                run.ghosts.push(g);
            });
        }
        sync();
    });

    // Runs after the group-move handler and before the draggable's own stop callback (which stores ui.position and pushes ONE undo step)
    $(document).on('dragstop.zppAltCopy', '.zappoppop-canvas-item', function (e, ui) {
        if (!run || !ui || !ui.position || nodeId(this) !== run.pid) { return; }
        var r = run;
        var copy = altHeld || !!e.altKey || !!(e.originalEvent && e.originalEvent.altKey);
        cleanup();
        if (!copy) { return; }
        var dx = Math.round(ui.position.left - r.p0.left), dy = Math.round(ui.position.top - r.p0.top);
        if (!dx && !dy) { return; }                       // Alt+click without moving: nothing to copy

        var all = list();
        var maxZ = all.reduce(function (mx, it) { return Math.max(mx, parseInt(it.zIndex, 10) || 10); }, 10);
        var ordered = r.members.slice().sort(function (a, b) { return (parseInt(a.el.zIndex, 10) || 10) - (parseInt(b.el.zIndex, 10) || 10); });
        var stamp = Date.now().toString(36);
        var clones = ordered.map(function (m, i) {
            var c = JSON.parse(JSON.stringify(m.el));
            var base = (m.el.type === 'text' || m.el.type === 'paragraph') ? 'PARAGRAPH' : (m.el.name || String(m.el.type || 'LAYER').toUpperCase());
            c.id = 'layer_' + stamp + '_' + Math.random().toString(36).substr(2, 4) + i;
            c.name = base + ' (Copy)';
            c.zIndex = maxZ + 5 * (i + 1);
            setPos(c, m.left + dx, m.top + dy);
            return c;
        });

        // originals go back to where they started (the stop callback stores ui.position for the dragged one)
        r.members.forEach(function (m) { setPos(m.el, m.left, m.top); });
        ui.position.left = r.p0.left;
        ui.position.top = r.p0.top;
        clones.forEach(function (c) { all.push(c); });

        // re-render only after the draggable has finished with its node
        setTimeout(function () {
            var C = canvas(), L = window.ZapPopPopBuilderLayers, M = window.ZapPopPopMultiSelect;
            var ids = clones.map(function (c) { return c.id; });
            if (!C) { return; }
            C.renderCanvas();
            if (L && typeof L.renderLayers === 'function') { L.renderLayers(); }
            if (ids.length >= 2 && M && typeof M.select === 'function') { M.select(ids); }
            else { C.selectElement(ids[ids.length - 1], true); }
        }, 0);
    });
})(window, document, jQuery);
