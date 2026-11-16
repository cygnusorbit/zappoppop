/* ZAPPOPPOP_MARQUEE_SELECT: drag a rectangle on the canvas to select several layers, then drag/nudge them together */
(function (window, document, $) {
    'use strict';
    if (window.__zppMarqueeBound) { return; }
    window.__zppMarqueeBound = true;

    var THRESHOLD = 4;
    var EXCLUDE = '.zappoppop-canvas-item, .ui-resizable-handle, #zappoppop-canvas-corner-handle, #zappoppop-canvas-stage-bar, ' +
        '#zappoppop-inspector-drawer, #zappoppop-floating-layers-panel, #zappoppop-settings-drawer, #zappoppop-alignment-toolbar, ' +
        '.zappoppop-ribbon-bar, .zappoppop-builder-header, input, textarea, select, button, a, [contenteditable="true"]';

    var ids = [];            // multi-selection, only ever holds 2+ layer ids
    var pressOnMember = false;
    var swallowClick = false;
    var swallowTimer = null;
    var drag = null;         // marquee gesture
    var group = null;        // group-move gesture
    var nudgeTimer = null;

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
    function has(id) { return ids.indexOf(cid(id)) !== -1; }
    function isPreview() {
        var c = canvas();
        return !!((c && c.isPreview) || (window.ZapPopPopBuilderPreview && window.ZapPopPopBuilderPreview.isOpen));
    }
    function isTyping(t) {
        if (!t) { return false; }
        var tag = (t.tagName || '').toLowerCase();
        if (tag === 'input' || tag === 'textarea' || tag === 'select' || t.isContentEditable) { return true; }
        return !!(t.closest && t.closest('.note-editor, [contenteditable="true"], [role="slider"]'));
    }
    function activeIdNow() {
        var c = core();
        return (c && c.state && c.state.activeId) ? cid(c.state.activeId) : null;
    }
    function currentSelection() {
        if (ids.length >= 2) { return ids.slice(); }
        var a = activeIdNow();
        return a ? [a] : [];
    }

    // ------------------------------------------------------------- selection state
    function paint() {
        $('.zappoppop-canvas-item.zappoppop-multi-selected').removeClass('zappoppop-multi-selected');
        for (var i = 0; i < ids.length; i++) {
            var n = nodeOf(ids[i]);
            if (n) { n.classList.add('zappoppop-multi-selected'); }
        }
    }
    function clear() { if (ids.length) { ids = []; } paint(); }
    function reapply() {
        ids = ids.filter(function (id) { return !!findEl(id); });
        if (ids.length < 2) { ids = []; }
        paint();
    }
    function commit(newIds) {
        var c = canvas();
        if (!c) { return; }
        if (newIds.length >= 2) {
            c.deselect();               // closes the single-layer inspector, clears any previous multi-selection
            ids = newIds.slice();
            paint();
        } else if (newIds.length === 1) {
            c.selectElement(newIds[0], true);
        } else {
            c.deselect();
        }
    }
    function toggle(id) {
        var cur = currentSelection();
        var i = cur.indexOf(id);
        if (i === -1) { cur.push(id); } else { cur.splice(i, 1); }
        commit(cur);
    }

    // ------------------------------------------------------------- click suppression
    function armSwallow(ms) {
        swallowClick = true;
        clearTimeout(swallowTimer);
        if (ms) { swallowTimer = setTimeout(function () { swallowClick = false; }, ms); }
    }
    window.addEventListener('click', function (e) {
        if (!swallowClick) { return; }
        swallowClick = false;
        clearTimeout(swallowTimer);
        e.stopPropagation();
        e.preventDefault();
    }, true);

    // ------------------------------------------------------------- marquee
    function rectOf(d) {
        return {
            left: Math.min(d.x0, d.x1), top: Math.min(d.y0, d.y1),
            right: Math.max(d.x0, d.x1), bottom: Math.max(d.y0, d.y1)
        };
    }
    function hitTest(r) {
        var out = [];
        var nodes = document.querySelectorAll('#zappoppop-canvas-elements-root .zappoppop-canvas-item');
        for (var i = 0; i < nodes.length; i++) {
            var b = nodes[i].getBoundingClientRect();
            if (b.width === 0 && b.height === 0) { continue; }
            if (b.right >= r.left && b.left <= r.right && b.bottom >= r.top && b.top <= r.bottom) {
                var id = nodeId(nodes[i]);
                if (id && findEl(id)) { out.push(id); }
            }
        }
        return out;
    }
    function markHits(hits) {
        $('.zappoppop-canvas-item.zappoppop-marquee-hit').removeClass('zappoppop-marquee-hit');
        for (var i = 0; i < hits.length; i++) {
            var n = nodeOf(hits[i]);
            if (n) { n.classList.add('zappoppop-marquee-hit'); }
        }
    }
    function endMarquee() {
        if (drag && drag.box && drag.box.parentNode) { drag.box.parentNode.removeChild(drag.box); }
        document.body.classList.remove('zappoppop-marquee-active');
        markHits([]);
        drag = null;
    }
    function drawMarquee() {
        var r = rectOf(drag);
        var s = drag.box.style;
        s.left = r.left + 'px';
        s.top = r.top + 'px';
        s.width = (r.right - r.left) + 'px';
        s.height = (r.bottom - r.top) + 'px';
    }

    // ------------------------------------------------------------- group move helpers
    function posOf(el, node) {
        var l = parseFloat(node.style.left), t = parseFloat(node.style.top);
        return { left: isNaN(l) ? num(el.left !== undefined ? el.left : el.x) : l, top: isNaN(t) ? num(el.top !== undefined ? el.top : el.y) : t };
    }
    function collect(idList) {
        var out = [];
        for (var i = 0; i < idList.length; i++) {
            var el = findEl(idList[i]), node = nodeOf(idList[i]);
            if (!el || !node || el.locked || el.hidden) { continue; }
            var p = posOf(el, node);
            out.push({ id: cid(idList[i]), el: el, node: node, left: p.left, top: p.top, w: node.offsetWidth, h: node.offsetHeight });
        }
        return out;
    }
    function limitsFor(members) {
        var root = document.getElementById('zappoppop-canvas-elements-root');
        var c = canvas();
        if (!root || !members.length || (c && c.isOverflowAllowed && c.isOverflowAllowed())) { return null; }
        var minL = Infinity, minT = Infinity, maxR = -Infinity, maxB = -Infinity;
        members.forEach(function (m) {
            minL = Math.min(minL, m.left); minT = Math.min(minT, m.top);
            maxR = Math.max(maxR, m.left + m.w); maxB = Math.max(maxB, m.top + m.h);
        });
        return {
            loX: Math.min(0, -minL), hiX: Math.max(0, root.offsetWidth - maxR),
            loY: Math.min(0, -minT), hiY: Math.max(0, root.offsetHeight - maxB)
        };
    }
    function clampDelta(dx, dy, lim) {
        dx = Math.round(dx); dy = Math.round(dy);
        if (lim) {
            dx = Math.max(lim.loX, Math.min(lim.hiX, dx));
            dy = Math.max(lim.loY, Math.min(lim.hiY, dy));
        }
        return { dx: dx, dy: dy };
    }
    function applyMembers(members, d, skipNode, commitModel) {
        members.forEach(function (m) {
            var l = m.left + d.dx, t = m.top + d.dy;
            if (m.node !== skipNode) { m.node.style.left = l + 'px'; m.node.style.top = t + 'px'; }
            if (commitModel) {
                m.el.left = l; m.el.top = t;
                if (m.el.x !== undefined) { m.el.x = l; }
                if (m.el.y !== undefined) { m.el.y = t; }
            }
        });
    }
    function stopFollow() {
        if (group && group.raf) {
            (window.cancelAnimationFrame || window.clearTimeout)(group.raf);
            group.raf = 0;
        }
    }
    function follow() {
        if (!group) { return; }
        var l = parseFloat(group.pNode.style.left), t = parseFloat(group.pNode.style.top);
        if (!isNaN(l) && !isNaN(t)) {
            var d = clampDelta(l - group.p0.left, t - group.p0.top, group.lim);
            applyMembers(group.members, d, group.pNode, false);
            if (d.dx !== Math.round(l - group.p0.left) || d.dy !== Math.round(t - group.p0.top)) {
                group.pNode.style.left = (group.p0.left + d.dx) + 'px';
                group.pNode.style.top = (group.p0.top + d.dy) + 'px';
            }
        }
        group.raf = (window.requestAnimationFrame || function (f) { return setTimeout(f, 16); })(follow);
    }

    // ------------------------------------------------------------- listeners (capture phase, so we run before the legacy handlers)
    function markPress(e) {
        var t = e.target;
        if (!t || !t.closest) { return; }
        var item = t.closest('.zappoppop-canvas-item');
        if (!item) { return; }
        var id = nodeId(item);
        if (e.shiftKey) {
            // Shift+press builds the selection: keep the legacy single-select handler (and jQuery UI drag) out of it
            if (e.type === 'pointerdown' || e.type === 'touchstart') { e.stopPropagation(); }
            return;
        }
        if (ids.length >= 2 && has(id)) { pressOnMember = true; }
    }
    document.addEventListener('pointerdown', markPress, true);
    document.addEventListener('touchstart', markPress, true);

    document.addEventListener('mousedown', function (e) {
        markPress(e);
        if (e.button !== 0 || drag || group) { return; }
        if (!document.getElementById('zappoppop-canvas-box') || isPreview()) { return; }
        if (document.body.classList.contains('zappoppop-space-held')) { return; }
        var t = e.target;
        if (!t || !t.closest) { return; }

        var item = t.closest('.zappoppop-canvas-item');
        if (item) {
            if (e.shiftKey && !e.metaKey && !e.ctrlKey && !e.altKey) {
                e.preventDefault();
                e.stopPropagation();
                armSwallow(0);
                toggle(nodeId(item));
            }
            return;
        }
        if (!t.closest('.zappoppop-builder-workspace') || t.closest(EXCLUDE)) { return; }

        drag = { x0: e.clientX, y0: e.clientY, x1: e.clientX, y1: e.clientY, active: false, additive: e.shiftKey, base: e.shiftKey ? currentSelection() : [], box: null };
    }, true);

    document.addEventListener('mousemove', function (e) {
        if (!drag) { return; }
        drag.x1 = e.clientX; drag.y1 = e.clientY;
        if (!drag.active) {
            if (Math.abs(drag.x1 - drag.x0) < THRESHOLD && Math.abs(drag.y1 - drag.y0) < THRESHOLD) { return; }
            drag.active = true;
            drag.box = document.createElement('div');
            drag.box.className = 'zappoppop-marquee';
            document.body.appendChild(drag.box);
            document.body.classList.add('zappoppop-marquee-active');
            var ae = document.activeElement;
            if (ae && ae !== document.body && ae.blur) { try { ae.blur(); } catch (err) {} }
        }
        e.preventDefault();
        drawMarquee();
        markHits(hitTest(rectOf(drag)));
    }, true);

    document.addEventListener('mouseup', function () {
        pressOnMember = false;                       // a plain click on a member can collapse the selection again
        if (swallowClick && !drag) { armSwallow(120); }
        if (!drag) { return; }
        var wasActive = drag.active;
        var hits = wasActive ? hitTest(rectOf(drag)) : [];
        var base = drag.base;
        endMarquee();
        if (!wasActive) { return; }                  // plain click: legacy click handlers run unchanged
        armSwallow(120);                             // the box would otherwise treat the release as a click and deselect
        var out = base.slice();
        hits.forEach(function (id) { if (out.indexOf(id) === -1) { out.push(id); } });
        commit(out);
    }, true);

    window.addEventListener('blur', function () { pressOnMember = false; if (drag) { endMarquee(); } });

    // ------------------------------------------------------------- group drag (jQuery UI draggable events bubble to document)
    $(document).on('dragstart.zppMulti', '.zappoppop-canvas-item', function (e, ui) {
        if (!ui || !ui.position || ids.length < 2) { return; }   // native HTML5 dragstart has no ui
        var pid = nodeId(this);
        if (!has(pid)) { return; }
        var members = collect(ids);
        var p = null;
        members.forEach(function (m) { if (m.id === pid) { p = m; } });
        if (!p) { return; }
        group = { pNode: this, p0: { left: p.left, top: p.top }, members: members, lim: limitsFor(members), raf: 0 };
        follow();
    });

    // The event fires before the draggable's own stop callback, so adjusting ui.position here is what that callback stores
    $(document).on('dragstop.zppMulti', '.zappoppop-canvas-item', function (e, ui) {
        if (!group || group.pNode !== this || !ui || !ui.position) { return; }
        stopFollow();
        var d = clampDelta(ui.position.left - group.p0.left, ui.position.top - group.p0.top, group.lim);
        ui.position.left = group.p0.left + d.dx;
        ui.position.top = group.p0.top + d.dy;
        applyMembers(group.members, d, null, true);
        group = null;
        // the primary's own stop callback pushes the single undo step
    });

    // ------------------------------------------------------------- keyboard: arrows nudge the group, Esc clears it
    var KEYS = { ArrowLeft: [-1, 0], ArrowRight: [1, 0], ArrowUp: [0, -1], ArrowDown: [0, 1], Left: [-1, 0], Right: [1, 0], Up: [0, -1], Down: [0, 1] };
    var CODES = { 37: [-1, 0], 39: [1, 0], 38: [0, -1], 40: [0, 1] };

    document.addEventListener('keydown', function (e) {
        if (!document.getElementById('zappoppop-canvas-box') || isPreview() || isTyping(e.target)) { return; }
        if (e.key === 'Escape' || e.keyCode === 27) {
            if (drag) { endMarquee(); return; }
            if (ids.length >= 2 && canvas()) { canvas().deselect(); }
            return;
        }
        if (ids.length < 2 || e.metaKey || e.ctrlKey || e.altKey) { return; }
        var k = KEYS[e.key] || CODES[e.keyCode];
        if (!k) { return; }
        e.preventDefault();
        e.stopImmediatePropagation();
        var step = e.shiftKey ? 10 : 1;
        var members = collect(ids);
        if (!members.length) { return; }
        var d = clampDelta(k[0] * step, k[1] * step, limitsFor(members));
        if (!d.dx && !d.dy) { return; }
        applyMembers(members, d, null, true);
        clearTimeout(nudgeTimer);
        nudgeTimer = setTimeout(function () {
            var c = core();
            if (c && typeof c.pushHistory === 'function') { c.pushHistory('Nudge'); }
        }, 300);
    }, true);

    window.ZapPopPopMultiSelect = {
        ids: function () { return ids.slice(); },
        has: has,
        clear: clear,
        reapply: reapply,
        holdSelect: function (id) { return pressOnMember && has(id); },
        select: function (list) { commit(list.slice()); }
    };
})(window, document, jQuery);
