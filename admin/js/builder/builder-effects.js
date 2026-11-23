/**
 * ZapPopPop Builder: Effect section - blend mode, Transform list, Filter list (ZPP_EFFECTS).
 *
 * Model (mirrors ZapPopPop_Effects_Style): el.blendMode (normal data-prop select), el.fxOriginX / fxOriginY / fxOriginZ (normal data-prop numbers),
 * el.fxTransforms = [ { type: move|scale|rotate|skew|perspective, x, y, z, off } ], el.fxFilters = [ { type, v, x, y, b, c, off } ].
 * The two lists are array data without a data-prop, so Save / Clear / Reset as Default list them in builder-tab-blocks.js (FIXED.style).
 * Stage: ZapPopPopFxStyle.apply($node, el) sets transform / transform-origin / filter / mix-blend-mode on the layer node.
 */
(function (window, $) {
    'use strict';

    var MAX = 8;
    var BLEND = ['normal', 'multiply', 'screen', 'overlay', 'darken', 'lighten', 'color-dodge', 'color-burn', 'hard-light', 'soft-light', 'difference', 'exclusion', 'hue', 'saturation', 'color', 'luminosity'];
    /* field = [key, label, min, max, step, default] */
    var TT = {
        move: { label: 'Move', unit: 'px', f: [['x', 'X', -2000, 2000, 1, 0], ['y', 'Y', -2000, 2000, 1, 0], ['z', 'Z', -1000, 1000, 1, 0]] },
        scale: { label: 'Scale', unit: '', f: [['x', 'X', 0, 10, 0.1, 1], ['y', 'Y', 0, 10, 0.1, 1], ['z', 'Z', 0, 10, 0.1, 1]] },
        rotate: { label: 'Rotate', unit: 'deg', f: [['x', 'X', -360, 360, 1, 0], ['y', 'Y', -360, 360, 1, 0], ['z', 'Z', -360, 360, 1, 0]] },
        skew: { label: 'Skew', unit: 'deg', f: [['x', 'X', -89, 89, 1, 0], ['y', 'Y', -89, 89, 1, 0]] },
        perspective: { label: 'Perspective', unit: 'px', f: [['x', 'Value', 1, 5000, 10, 1000]] }
    };
    var FT = {
        'blur': { label: 'Blur', unit: 'px', min: 0, max: 100, def: 0 },
        'brightness': { label: 'Brightness', unit: '%', min: 0, max: 300, def: 100 },
        'contrast': { label: 'Contrast', unit: '%', min: 0, max: 300, def: 100 },
        'saturate': { label: 'Saturate', unit: '%', min: 0, max: 300, def: 100 },
        'hue-rotate': { label: 'Hue Rotate', unit: 'deg', min: 0, max: 360, def: 0 },
        'grayscale': { label: 'Grayscale', unit: '%', min: 0, max: 100, def: 0 },
        'invert': { label: 'Invert', unit: '%', min: 0, max: 100, def: 0 },
        'opacity': { label: 'Opacity', unit: '%', min: 0, max: 100, def: 100 },
        'sepia': { label: 'Sepia', unit: '%', min: 0, max: 100, def: 0 },
        'drop-shadow': { label: 'Drop Shadow', unit: 'px', min: 0, max: 0, def: 0 }
    };
    var FORDER = ['blur', 'brightness', 'contrast', 'saturate', 'hue-rotate', 'grayscale', 'invert', 'opacity', 'sepia', 'drop-shadow'];
    var TORDER = ['move', 'scale', 'rotate', 'skew', 'perspective'];
    var TABS = ['move', 'scale', 'rotate', 'skew'];
    var AX = { move: ['\u2192', '\u2193', '\u2199'], scale: ['\u2192', '\u2193', '\u2199'], rotate: ['\u21b6', '\u21b7', '\u21bb'], skew: ['\u2192', '\u2193'], perspective: [''] };
    var TI = { move: 'dashicons-move', scale: 'dashicons-editor-expand', rotate: 'dashicons-image-rotate', skew: 'dashicons-align-wide', perspective: 'dashicons-visibility' };
    var DS = [['x', 'Horizontal', -100, 100, 1, 0], ['y', 'Vertical', -100, 100, 1, 0], ['b', 'Blur', 0, 100, 1, 0]];

    function num(v, lo, hi, d) { var n = parseFloat(v); if (isNaN(n)) { return d; } return Math.round(Math.max(lo, Math.min(hi, n)) * 100) / 100; }
    function colour(c, d) { c = String(c === undefined || c === null ? '' : c).replace(/^\s+|\s+$/g, ''); return /^(#[0-9a-fA-F]{3,8}|rgba?\([\d\s.,%]+\)|transparent)$/.test(c) ? c : d; }
    function isArr(v) { return Object.prototype.toString.call(v) === '[object Array]'; }
    function has(o, k) { return Object.prototype.hasOwnProperty.call(o, k); }

    function normT(o) {
        var t, out, i, f;
        o = o || {};
        t = (typeof o.type === 'string' && has(TT, o.type)) ? o.type : 'move';
        out = { type: t };
        for (i = 0; i < TT[t].f.length; i++) { f = TT[t].f[i]; out[f[0]] = num(o[f[0]], f[2], f[3], f[5]); }
        out.off = !!o.off && o.off !== 'false';
        return out;
    }
    function normF(o) {
        var t, out, d;
        o = o || {};
        t = (typeof o.type === 'string' && has(FT, o.type)) ? o.type : 'blur';
        out = { type: t };
        if (t === 'drop-shadow') {
            out.x = num(o.x, -100, 100, 0); out.y = num(o.y, -100, 100, 0); out.b = num(o.b, 0, 100, 0); out.c = colour(o.c, '#00000080');
        } else { d = FT[t]; out.v = num(o.v, d.min, d.max, d.def); }
        out.off = !!o.off && o.off !== 'false';
        return out;
    }
    function fmt(n) { return String(Math.round(n * 100) / 100); }

    function transformValue(el) {
        var l = el && isArr(el.fxTransforms) ? el.fxTransforms : [], out = [], i, t;
        for (i = 0; i < l.length && i < MAX; i++) {
            t = normT(l[i]);
            if (t.off) { continue; }
            if (t.type === 'move') { out.push('translate3d(' + fmt(t.x) + 'px,' + fmt(t.y) + 'px,' + fmt(t.z) + 'px)'); }
            else if (t.type === 'scale') { out.push('scale3d(' + fmt(t.x) + ',' + fmt(t.y) + ',' + fmt(t.z) + ')'); }
            else if (t.type === 'rotate') { out.push('rotateX(' + fmt(t.x) + 'deg) rotateY(' + fmt(t.y) + 'deg) rotateZ(' + fmt(t.z) + 'deg)'); }
            else if (t.type === 'skew') { out.push('skew(' + fmt(t.x) + 'deg,' + fmt(t.y) + 'deg)'); }
            else { out.push('perspective(' + fmt(t.x) + 'px)'); }
        }
        return out.join(' ');
    }
    function filterValue(el) {
        var l = el && isArr(el.fxFilters) ? el.fxFilters : [], out = [], i, f;
        for (i = 0; i < l.length && i < MAX; i++) {
            f = normF(l[i]);
            if (f.off) { continue; }
            if (f.type === 'drop-shadow') { out.push('drop-shadow(' + fmt(f.x) + 'px ' + fmt(f.y) + 'px ' + fmt(f.b) + 'px ' + f.c + ')'); }
            else { out.push(f.type + '(' + fmt(f.v) + FT[f.type].unit + ')'); }
        }
        return out.join(' ');
    }

    /* ---- stage ---- */
    function apply($node, el) {
        var n, t, f, b, rot, k, ox, oy, oz, s;
        if (!$node || !$node.length || !el) { return; }
        n = $node[0]; s = n.style;
        t = transformValue(el); f = filterValue(el);
        b = (typeof el.blendMode === 'string' && el.blendMode !== 'normal' && BLEND.indexOf(el.blendMode) !== -1) ? el.blendMode : '';
        if (t) {
            rot = 0;
            for (k = 0; k < 3; k++) { if (!rot) { rot = parseInt(el[['rotate', 'shapeRotate', 'rotation'][k]], 10) || 0; } }
            s.setProperty('transform', (rot ? 'rotate(' + rot + 'deg) ' : '') + t);
            ox = num(el.fxOriginX, 0, 100, 50); oy = num(el.fxOriginY, 0, 100, 50); oz = num(el.fxOriginZ, -500, 500, 0);
            if (ox !== 50 || oy !== 50 || oz !== 0) { s.setProperty('transform-origin', ox + '% ' + oy + '% ' + oz + 'px'); } else { s.removeProperty('transform-origin'); }
            n.setAttribute('data-zpp-fxt', '1');
        } else if (n.getAttribute('data-zpp-fxt') === '1') { s.removeProperty('transform'); s.removeProperty('transform-origin'); n.removeAttribute('data-zpp-fxt'); }
        if (f) { s.setProperty('filter', f); n.setAttribute('data-zpp-fxf', '1'); }
        else if (n.getAttribute('data-zpp-fxf') === '1') { s.removeProperty('filter'); n.removeAttribute('data-zpp-fxf'); }
        if (b) { s.setProperty('mix-blend-mode', b); n.setAttribute('data-zpp-fxb', '1'); }
        else if (n.getAttribute('data-zpp-fxb') === '1') { s.removeProperty('mix-blend-mode'); n.removeAttribute('data-zpp-fxb'); }
    }
    window.ZapPopPopFxStyle = { apply: apply, transformValue: transformValue, filterValue: filterValue };

    /* ZPP_FX_SYNC: keeps every stage layer in step with the model on its own (a rebuilt layer node, or a browser still holding an older cached canvas script, gets the effects within a moment) */
    function stageSync() {
        var c = window.ZapPopPopBuilderCore, cv, l, i, el, n, sg;
        if (!c || !c.state) { return; }
        cv = c.state.canvases || {};
        l = cv[c.state.currentCanvas || 1];
        if (!isArr(l)) { return; }
        for (i = 0; i < l.length; i++) {
            el = l[i];
            if (!el || el.id === undefined) { continue; }
            n = document.getElementById('el-' + el.id);
            if (!n) { continue; }
            try { sg = JSON.stringify([el.fxTransforms, el.fxFilters, el.blendMode, el.fxOriginX, el.fxOriginY, el.fxOriginZ, el.rotate]); } catch (e) { sg = ''; }
            if (n.getAttribute('data-zpp-fxs') !== sg) { apply({ length: 1, 0: n }, el); n.setAttribute('data-zpp-fxs', sg); }
        }
    }
    window.setInterval(stageSync, 250);

    if (!$) { return; }

    /* ---- Inspector UI ---- */
    var openT = -1, openF = -1, lastSig = '', lastId = '';

    function core() { return window.ZapPopPopBuilderCore || null; }
    function cid(v) { return String(v || '').replace(/^zappoppop-el-/, '').replace(/^el-/, '').trim(); }
    function activeEl() {
        var c = core(), id, cv, k, lst, i;
        if (!c || !c.state) { return null; }
        id = cid(c.state.activeId);
        if (!id) { return null; }
        cv = c.state.canvases || {};
        for (k in cv) {
            if (!Object.prototype.hasOwnProperty.call(cv, k)) { continue; }
            lst = cv[k] || [];
            for (i = 0; i < lst.length; i++) { if (lst[i] && cid(lst[i].id) === id) { return lst[i]; } }
        }
        return null;
    }
    function key(k) { return k === 'f' ? 'fxFilters' : 'fxTransforms'; }
    function list(el, k) { if (!el) { return []; } if (!isArr(el[key(k)])) { el[key(k)] = []; } return el[key(k)]; }
    function sig(el) { try { return JSON.stringify([el && el.fxTransforms, el && el.fxFilters, el && el.blendMode, el && el.fxOriginX, el && el.fxOriginY, el && el.fxOriginZ]); } catch (e) { return ''; } }
    function history(label) { var c = core(); if (c && typeof c.pushHistory === 'function') { c.pushHistory(label); } }
    function redraw() { var cv = window.ZapPopPopBuilderCanvas; if (cv && typeof cv.renderCanvas === 'function') { cv.renderCanvas(); } }
    function commit(el, label) { if (label) { history(label); } lastSig = sig(el); redraw(); }
    function L(name, d) { var r = document.getElementById('zpp-fx-root'); return (r && r.getAttribute('data-l-' + name)) || d; }
    function esc(s) { return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/"/g, '&quot;'); }

    function summary(k, o) {
        var parts, d;
        if (k === 't') {
            d = TT[o.type];
            parts = d.f.map(function (f) { return fmt(o[f[0]]) + (d.unit === 'px' ? 'px' : (d.unit === 'deg' ? '°' : '')); });
            return L('t_' + o.type, d.label) + ': ' + parts.join(', ');
        }
        d = FT[o.type];
        if (o.type === 'drop-shadow') { return L('f_' + o.type, d.label) + ': ' + fmt(o.x) + 'px, ' + fmt(o.y) + 'px, ' + fmt(o.b) + 'px'; }
        return L('f_' + o.type, d.label) + ': ' + fmt(o.v) + (d.unit === 'deg' ? '°' : d.unit);
    }

    function stepper() {
        return '<span class="zpp-sp-step"><button type="button" class="zpp-sp-up" tabindex="-1" aria-label="' + esc(L('inc', 'Increase')) + '"></button><button type="button" class="zpp-sp-down" tabindex="-1" aria-label="' + esc(L('dec', 'Decrease')) + '"></button></span>';
    }
    function fieldRow(f, v, unit, lab, ico) { /* ZPP_FX_TABS */
        var u = unit === 'deg' ? 'DEG' : (unit === 'px' ? 'PX' : (unit === '%' ? '%' : ''));
        return '<div class="zpp-fx-f"><label>' + esc(lab || f[1]) + '</label><span class="zpp-ty-numwrap"><span class="zpp-ty-ctl">' + (ico ? '<span class="zpp-fx-ico" aria-hidden="true">' + ico + '</span>' : '') + '<input type="number" class="widefat zappoppop-input" data-f="' + f[0] + '" min="' + f[2] + '" max="' + f[3] + '" step="' + f[4] + '" value="' + v + '" />' +
            (u ? '<span class="zpp-ty-unit">' + u + '</span>' : '') + stepper() + '</span><button type="button" class="zpp-sp-reset zpp-ty-reset" data-default="' + f[5] + '" title="' + esc(L('reset', 'Reset')) + '" aria-label="' + esc(L('reset', 'Reset')) + '"><span class="dashicons dashicons-image-rotate" aria-hidden="true"></span></button></span></div>';
    }
    function typeSelect(k, cur) {
        var order = k === 't' ? TORDER : FORDER, src = k === 't' ? TT : FT, h = '<div class="zpp-fx-f"><label>' + esc(k === 't' ? L('type', 'Transform') : L('filter', 'Filter')) + '</label><select class="widefat zappoppop-input" data-f="type">';
        order.forEach(function (t) { h += '<option value="' + t + '"' + (t === cur ? ' selected' : '') + '>' + esc(L((k === 't' ? 't_' : 'f_') + t, src[t].label)) + '</option>'; });
        return h + '</select></div>';
    }
    function panelHtml(k, o) {
        var h = k === 't' ? '' : typeSelect(k, o.type), d, i;
        if (k === 't') {
            d = TT[o.type]; h = '<div class="zpp-fx-tabs" role="tablist">';
            TABS.forEach(function (t) { h += '<button type="button" role="tab" class="zpp-fx-tab' + (t === o.type ? ' is-on' : '') + '" data-act="tab" data-t="' + t + '" aria-selected="' + (t === o.type ? 'true' : 'false') + '">' + esc(L('t_' + t, TT[t].label)) + '</button>'; });
            h += '</div>';
            for (i = 0; i < d.f.length; i++) { h += fieldRow(d.f[i], o[d.f[i][0]], d.unit, L('t_' + o.type, d.label) + ' ' + d.f[i][1], AX[o.type][i]); }
            return h;
        }
        if (o.type === 'drop-shadow') {
            for (i = 0; i < DS.length; i++) { h += fieldRow(DS[i], o[DS[i][0]], 'px'); }
            return h + '<div class="zpp-fx-f"><label>' + esc(L('color', 'Color')) + '</label><input type="color" data-zpp-alpha="compact" class="widefat" data-f="c" value="' + esc(o.c) + '" /></div>';
        }
        d = FT[o.type];
        return h + fieldRow(['v', '', d.min, d.max, 1, d.def], o.v, d.unit, L('value', 'Value'));
    }

    function renderList(k) {
        var el = activeEl(), box = document.getElementById(k === 't' ? 'zpp-fx-tlist' : 'zpp-fx-flist'), l, i, idx = k === 't' ? openT : openF, add;
        if (!box) { return; }
        box.innerHTML = '';
        if (!el) { return; }
        l = isArr(el[key(k)]) ? el[key(k)] : []; /* read only: drawing must not add keys to the layer */
        if (idx >= l.length) { idx = -1; if (k === 't') { openT = -1; } else { openF = -1; } }
        for (i = 0; i < l.length; i++) {
            (function (i) {
                var o = k === 't' ? normT(l[i]) : normF(l[i]), item = document.createElement('div');
                item.className = 'zpp-fx-item' + (idx === i ? ' is-open' : '') + (o.off ? ' is-off' : '');
                item.setAttribute('data-k', k); item.setAttribute('data-i', String(i));
                item.innerHTML = '<div class="zpp-fx-irow" role="button" tabindex="0" data-act="toggle" aria-expanded="' + (idx === i ? 'true' : 'false') + '">' + (k === 't' ? '<span class="dashicons ' + TI[o.type] + ' zpp-fx-ti" aria-hidden="true"></span>' : '') + '<span class="zpp-fx-lab">' + esc(summary(k, o)) + '</span>' +
                    '<button type="button" class="zpp-fx-ib" data-act="eye" title="' + esc(L('eye', 'Show / hide')) + '" aria-label="' + esc(L('eye', 'Show / hide')) + '" aria-pressed="' + (o.off ? 'true' : 'false') + '"><span class="dashicons ' + (o.off ? 'dashicons-hidden' : 'dashicons-visibility') + '" aria-hidden="true"></span></button>' +
                    '<button type="button" class="zpp-fx-ib" data-act="remove" title="' + esc(L('remove', 'Remove')) + '" aria-label="' + esc(L('remove', 'Remove')) + '"><span class="dashicons dashicons-no-alt" aria-hidden="true"></span></button></div>' +
                    (idx === i ? '<div class="zpp-fx-panel">' + panelHtml(k, o) + '</div>' : '');
                box.appendChild(item);
            })(i);
        }
        add = document.querySelector('[data-fx-add="' + k + '"]');
        if (add) { add.disabled = l.length >= MAX; }
        lastSig = sig(el);
    }
    function renderAll() { renderList('t'); renderList('f'); }

    function ctxOf(node) {
        var it = node.closest ? node.closest('.zpp-fx-item') : null, el = activeEl(), k, i, l;
        if (!it || !el) { return null; }
        k = it.getAttribute('data-k'); i = parseInt(it.getAttribute('data-i'), 10); l = list(el, k);
        return l[i] ? { el: el, k: k, i: i, o: (l[i] = (k === 't' ? normT(l[i]) : normF(l[i]))), item: it } : null;
    }
    function setOpen(k, i) { if (k === 't') { openT = i; } else { openF = i; } }
    function label(k, what) { return what + (k === 't' ? ' Transform' : ' Filter'); }

    function hydrateOrigin() {
        var el = activeEl(), k, $i, OD = { fxOriginX: 50, fxOriginY: 50, fxOriginZ: 0 };
        if (!el) { return; }
        for (k in OD) {
            if (!Object.prototype.hasOwnProperty.call(OD, k)) { continue; }
            $i = $('[data-prop="' + k + '"]');
            if ($i.length && document.activeElement !== $i[0]) { $i.val(el[k] === undefined || el[k] === '' ? OD[k] : el[k]); }
        }
        $('#prop-blend-mode').val(typeof el.blendMode === 'string' && BLEND.indexOf(el.blendMode) !== -1 ? el.blendMode : 'normal');
    }

    document.addEventListener('click', function (e) {
        var t = e.target, node, el, l, k, ctx, act, p, on;
        if (!t || !t.closest || !t.closest('#zpp-fx-root')) { return; }
        if ((node = t.closest('[data-fx-add]'))) {
            e.preventDefault(); e.stopPropagation();
            k = node.getAttribute('data-fx-add'); el = activeEl(); if (!el) { return; } l = list(el, k);
            if (l.length >= MAX) { return; }
            l.push(k === 't' ? normT({ type: 'move' }) : normF({ type: 'blur' })); setOpen(k, l.length - 1);
            commit(el, label(k, 'Add')); renderList(k); return;
        }
        if ((node = t.closest('[data-fx-origin]'))) {
            e.preventDefault(); e.stopPropagation();
            p = document.getElementById('zpp-fx-origin'); on = p.classList.contains('is-collapsed');
            p.classList.toggle('is-collapsed', !on); node.classList.toggle('is-open', on); node.setAttribute('aria-expanded', on ? 'true' : 'false');
            if (on) { hydrateOrigin(); }
            return;
        }
        if ((node = t.closest('[data-act]'))) {
            ctx = ctxOf(node);
            if (!ctx) { return; }
            act = node.getAttribute('data-act');
            e.preventDefault(); e.stopPropagation();
            if (act === 'remove') { list(ctx.el, ctx.k).splice(ctx.i, 1); setOpen(ctx.k, -1); commit(ctx.el, label(ctx.k, 'Remove')); renderList(ctx.k); }
            else if (act === 'eye') { ctx.o.off = !ctx.o.off; commit(ctx.el, label(ctx.k, 'Change')); renderList(ctx.k); }
            else if (act === 'tab') { if (ctx.o.type !== node.getAttribute('data-t')) { list(ctx.el, ctx.k)[ctx.i] = normT({ type: node.getAttribute('data-t') }); commit(ctx.el, label(ctx.k, 'Change')); renderList(ctx.k); } }
            else if (act === 'toggle') { setOpen(ctx.k, (ctx.k === 't' ? openT : openF) === ctx.i ? -1 : ctx.i); renderList(ctx.k); }
        }
    }, true);
    document.addEventListener('keydown', function (e) {
        var t = e.target;
        if ((e.key === 'Enter' || e.key === ' ') && t && t.classList && t.classList.contains('zpp-fx-irow') && t.closest('#zpp-fx-root')) { e.preventDefault(); t.click(); }
    });

    /* field edits: live while typing (no history, no redraw of the panel), one history step on change */
    function onField(e) {
        var t = e.target, f, ctx, lab, d, i, fd;
        if (!t || !t.getAttribute || !t.closest || !t.closest('.zpp-fx-item')) { return; }
        f = t.getAttribute('data-f');
        if (!f) { return; }
        ctx = ctxOf(t);
        if (!ctx) { return; }
        if (f === 'type') {
            ctx.o = ctx.k === 't' ? normT({ type: t.value }) : normF({ type: t.value }); list(ctx.el, ctx.k)[ctx.i] = ctx.o;
            if (e.type === 'change') { commit(ctx.el, label(ctx.k, 'Change')); renderList(ctx.k); }
            return;
        }
        if (f === 'c') { ctx.o.c = colour(t.value, '#00000080'); }
        else {
            if (t.value === '' || isNaN(parseFloat(t.value))) { return; }
            if (ctx.k === 't') { d = TT[ctx.o.type].f; for (i = 0; i < d.length; i++) { if (d[i][0] === f) { ctx.o[f] = num(t.value, d[i][2], d[i][3], d[i][5]); } } }
            else if (ctx.o.type === 'drop-shadow') { for (i = 0; i < DS.length; i++) { if (DS[i][0] === f) { ctx.o[f] = num(t.value, DS[i][2], DS[i][3], 0); } } }
            else { fd = FT[ctx.o.type]; ctx.o.v = num(t.value, fd.min, fd.max, fd.def); }
        }
        list(ctx.el, ctx.k)[ctx.i] = ctx.o = (ctx.k === 't' ? normT(ctx.o) : normF(ctx.o));
        lastSig = sig(ctx.el);
        redraw();
        lab = ctx.item.querySelector('.zpp-fx-lab');
        if (lab) { lab.textContent = summary(ctx.k, ctx.o); }
        if (e.type === 'change') { history(label(ctx.k, 'Change')); }
    }
    document.addEventListener('input', onField, true);
    document.addEventListener('change', onField, true);

    function tick() {
        var el = activeEl(), id = el ? cid(el.id) : '';
        if (id !== lastId) { lastId = id; openT = -1; openF = -1; renderAll(); hydrateOrigin(); return; }
        if (el && sig(el) !== lastSig) {
            if (!(document.activeElement && document.activeElement.closest && document.activeElement.closest('#zpp-fx-root'))) { renderAll(); }
            hydrateOrigin();
        }
    }

    $(function () { window.setInterval(tick, 200); window.setTimeout(function () { renderAll(); hydrateOrigin(); }, 300); });
})(window, window.jQuery);
