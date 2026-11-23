/**
 * ZapPopPop Builder: Background overlays (ZPP_BACKGROUND).
 *
 * Model: el.bgOverlays = [ { type: 'color' | 'image' | 'gradient', color, url, imageId, res, pos, repeat, size, sizeCustom, attach, c1, s1, c2, s2, gtype, angle } ] (top overlay first),
 *        el.bgClip = '' (full element) | 'padding' | 'content' | 'text' (a normal data-prop select). The overlay list is array data without a data-prop, so Save / Clear / Reset as Default
 *        list it in builder-tab-blocks.js (FIXED.style) and this module re-draws from the model whenever it changes.
 * Stage: ZapPopPopBgStyle.apply($node, el) mirrors ZapPopPop_Bg_Style::declarations() (front end).
 */
(function (window, $) {
    'use strict';

    var MAX = 6;
    var TYPES = { color: 1, image: 1, gradient: 1 };
    var RES = { thumbnail: 1, medium: 1, large: 1, full: 1 };
    var POS = { 'center center': 1, 'center left': 1, 'center right': 1, 'top left': 1, 'top center': 1, 'top right': 1, 'bottom left': 1, 'bottom center': 1, 'bottom right': 1 };
    var REP = { repeat: 1, 'repeat-x': 1, 'repeat-y': 1, 'no-repeat': 1 };
    var SIZE = { auto: 1, cover: 1, contain: 1, custom: 1 };

    function num(v, lo, hi, d) { var n = parseFloat(v); if (isNaN(n)) { return d; } return Math.max(lo, Math.min(hi, Math.round(n))); }
    function colour(c, d) { c = String(c === undefined || c === null ? '' : c).replace(/^\s+|\s+$/g, ''); return /^(#[0-9a-fA-F]{3,8}|rgba?\([\d\s.,%]+\)|transparent)$/.test(c) ? c : d; }
    function safeUrl(u) {
        u = String(u || '');
        if (!/^(https?:\/\/|\/(?!\/))[^\s"'()\\&=<>`]+$/.test(u)) { return ''; }
        return /\.(jpe?g|png|gif|webp|avif|bmp)$/i.test(u.split('?')[0]) ? u : '';
    }
    function pick(v, set, d) { return Object.prototype.hasOwnProperty.call(set, v) ? v : d; }

    function norm(o) {
        o = o || {};
        return {
            type: pick(o.type, TYPES, 'color'), color: colour(o.color, '#00000033'), url: safeUrl(o.url), imageId: num(o.imageId, 0, 999999999, 0),
            res: pick(o.res, RES, 'full'), pos: pick(o.pos, POS, 'center center'), repeat: pick(o.repeat, REP, 'no-repeat'), size: pick(o.size, SIZE, 'cover'),
            sizeCustom: num(o.sizeCustom, 1, 300, 100), attach: o.attach === 'fixed' ? 'fixed' : 'scroll',
            c1: colour(o.c1, '#000000'), s1: num(o.s1, 0, 100, 0), c2: colour(o.c2, '#ffffff'), s2: num(o.s2, 0, 100, 100),
            gtype: o.gtype === 'radial' ? 'radial' : 'linear', angle: num(o.angle, 0, 360, 180)
        };
    }

    /* ---- CSS pieces (same rules as ZapPopPop_Bg_Style::declarations) ---- */
    function layers(el) {
        var list = (el && Object.prototype.toString.call(el.bgOverlays) === '[object Array]') ? el.bgOverlays : [], out = [], i, o, L;
        for (i = 0; i < list.length && out.length < MAX; i++) {
            o = norm(list[i]); L = null;
            if (o.type === 'color') {
                if (o.color !== 'transparent') { L = { img: 'linear-gradient(' + o.color + ',' + o.color + ')', pos: 'center center', rep: 'no-repeat', size: 'auto', att: 'scroll' }; }
            } else if (o.type === 'gradient') {
                var st = o.c1 + ' ' + o.s1 + '%,' + o.c2 + ' ' + o.s2 + '%';
                L = { img: o.gtype === 'radial' ? 'radial-gradient(circle,' + st + ')' : 'linear-gradient(' + o.angle + 'deg,' + st + ')', pos: 'center center', rep: 'no-repeat', size: 'auto', att: 'scroll' };
            } else if (o.url) {
                L = { img: 'url(' + o.url + ')', pos: o.pos, rep: o.repeat, size: o.size === 'custom' ? o.sizeCustom + '%' : o.size, att: o.attach };
            }
            if (L) { out.push(L); }
        }
        return out;
    }
    function join(list, k) { return list.map(function (l) { return l[k]; }).join(','); }

    function apply($node, el) {
        var n, L, has, clip;
        if (!$node || !$node.length || !el) { return; }
        n = $node[0];
        L = layers(el);
        has = n.getAttribute('data-zpp-bgo') === '1';
        if (L.length) {
            n.style.setProperty('background-image', join(L, 'img'), 'important');
            n.style.setProperty('background-position', join(L, 'pos'), 'important');
            n.style.setProperty('background-repeat', join(L, 'rep'), 'important');
            n.style.setProperty('background-size', join(L, 'size'), 'important');
            n.style.setProperty('background-attachment', join(L, 'att'), 'important');
            n.setAttribute('data-zpp-bgo', '1');
        } else if (has) {
            ['background-image', 'background-position', 'background-repeat', 'background-size', 'background-attachment'].forEach(function (p) { n.style.removeProperty(p); });
            n.removeAttribute('data-zpp-bgo');
        }
        clip = el.bgClip === 'padding' ? 'padding-box' : (el.bgClip === 'content' ? 'content-box' : (el.bgClip === 'text' ? 'text' : ''));
        if (clip) {
            n.style.setProperty('-webkit-background-clip', clip, 'important'); n.style.setProperty('background-clip', clip, 'important');
            if (clip === 'text') { n.style.setProperty('-webkit-text-fill-color', 'transparent', 'important'); } else { n.style.removeProperty('-webkit-text-fill-color'); }
            n.setAttribute('data-zpp-bgc', '1');
        } else if (n.getAttribute('data-zpp-bgc') === '1') {
            n.style.removeProperty('-webkit-background-clip'); n.style.removeProperty('background-clip'); n.style.removeProperty('-webkit-text-fill-color');
            n.removeAttribute('data-zpp-bgc');
        }
    }
    window.ZapPopPopBgStyle = { apply: apply, layers: layers };

    if (!$) { return; }

    /* ---- Inspector UI ---- */
    var open = -1, selStop = 1, lastSig = '', lastId = '', tpl = null;

    function core() { return window.ZapPopPopBuilderCore || null; }
    function cid(v) { return String(v || '').replace(/^zappoppop-el-/, '').replace(/^el-/, '').trim(); }
    function activeEl() {
        var c = core(), id, cv, k, list, i;
        if (!c || !c.state) { return null; }
        id = cid(c.state.activeId);
        if (!id) { return null; }
        cv = c.state.canvases || {};
        for (k in cv) {
            if (!Object.prototype.hasOwnProperty.call(cv, k)) { continue; }
            list = cv[k] || [];
            for (i = 0; i < list.length; i++) { if (list[i] && cid(list[i].id) === id) { return list[i]; } }
        }
        return null;
    }
    function list(el) { if (!el) { return []; } if (Object.prototype.toString.call(el.bgOverlays) !== '[object Array]') { el.bgOverlays = []; } return el.bgOverlays; }
    function sig(el) { var l = el && Object.prototype.toString.call(el.bgOverlays) === '[object Array]' ? el.bgOverlays : []; try { return JSON.stringify(l); } catch (e) { return ''; } }

    function history(label) { var c = core(); if (c && typeof c.pushHistory === 'function') { c.pushHistory(label); } }
    function redraw() { var cv = window.ZapPopPopBuilderCanvas; if (cv && typeof cv.renderCanvas === 'function') { cv.renderCanvas(); } }
    function commit(el, label) {
        if (label) { history(label); }
        lastSig = sig(el);
        redraw();
    }

    function labelFor(o, T) {
        if (o.type === 'color') { return o.color; }
        if (o.type === 'gradient') { return o.gtype === 'radial' ? T.radial : T.linear; }
        if (o.url) { return decodeURIComponent(o.url.split('/').pop().split('?')[0]) || T.image; }
        return T.image;
    }
    function chipCss(o) {
        var L = layers({ bgOverlays: [o] });
        return L.length ? { image: L[0].img, size: o.type === 'image' ? 'cover' : 'auto', pos: 'center' } : null;
    }
    function labels() {
        var t = tpl;
        return { image: t.getAttribute('data-l-image'), linear: t.getAttribute('data-l-linear'), radial: t.getAttribute('data-l-radial'), color: t.getAttribute('data-l-color'), remove: t.getAttribute('data-l-remove') };
    }

    function fillPanel(panel, o) {
        var stopC = selStop === 2 ? o.c2 : o.c1, stopP = selStop === 2 ? o.s2 : o.s1, bar, img, fill;
        Array.prototype.forEach.call(panel.querySelectorAll('.zpp-bgo-tab'), function (b) {
            var on = b.getAttribute('data-t') === o.type; b.classList.toggle('is-on', on); b.setAttribute('aria-selected', on ? 'true' : 'false');
        });
        Array.prototype.forEach.call(panel.querySelectorAll('.zpp-bgo-pane'), function (p) { p.hidden = p.getAttribute('data-pane') !== o.type; });
        function setIn(f, v) { var n = panel.querySelector('[data-f="' + f + '"]'); if (n && n.tagName !== 'DIV') { n.setAttribute('value', String(v)); n.value = String(v); } }
        setIn('res', o.res); setIn('pos', o.pos); setIn('sizeCustom', o.sizeCustom); setIn('gtype', o.gtype); setIn('angle', o.angle);
        setIn('stopColor', stopC); setIn('stopPos', stopP); setIn('color', o.color);
        ['repeat', 'size', 'attach'].forEach(function (f) {
            var seg = panel.querySelector('.zpp-bgo-seg[data-f="' + f + '"]');
            if (!seg) { return; }
            Array.prototype.forEach.call(seg.querySelectorAll('.zpp-ty-b'), function (b) { var on = b.getAttribute('data-v') === o[f]; b.classList.toggle('is-on', on); b.setAttribute('aria-pressed', on ? 'true' : 'false'); });
        });
        panel.querySelector('[data-row="sizeCustom"]').hidden = o.size !== 'custom';
        panel.querySelector('[data-row="angle"]').style.display = o.gtype === 'radial' ? 'none' : '';
        img = panel.querySelector('.zpp-bgo-img');
        if (o.url) { img.style.backgroundImage = 'url(' + o.url + ')'; img.classList.add('has-img'); } else { img.style.backgroundImage = ''; img.classList.remove('has-img'); }
        bar = panel.querySelector('.zpp-bgo-bar'); fill = bar.querySelector('.zpp-bgo-barfill');
        fill.style.backgroundImage = 'linear-gradient(90deg,' + o.c1 + ' ' + o.s1 + '%,' + o.c2 + ' ' + o.s2 + '%)';
        Array.prototype.forEach.call(bar.querySelectorAll('.zpp-bgo-handle'), function (h) {
            var two = h.getAttribute('data-stop') === '2';
            h.style.left = (two ? o.s2 : o.s1) + '%'; h.style.backgroundColor = two ? o.c2 : o.c1; h.classList.toggle('is-on', (two ? 2 : 1) === selStop);
        });
    }

    function render() {
        var el = activeEl(), $l = $('#zpp-bgo-list'), T, l, i;
        tpl = document.getElementById('zpp-bgo-tpl');
        if (!$l.length || !tpl) { return; }
        $l.empty();
        if (!el) { return; }
        T = labels(); l = Object.prototype.toString.call(el.bgOverlays) === '[object Array]' ? el.bgOverlays : []; /* read only: drawing must not add keys to the layer */
        if (l.length > 0 && open >= l.length) { open = -1; }
        for (i = 0; i < l.length; i++) {
            (function (i) {
                var o = norm(l[i]), item = document.createElement('div'), row = document.createElement('div'), chip = document.createElement('span'), lab = document.createElement('span'), x = document.createElement('button'), c = chipCss(o);
                item.className = 'zpp-bgo-item' + (open === i ? ' is-open' : ''); item.setAttribute('data-i', String(i));
                row.className = 'zpp-bgo-row'; row.setAttribute('role', 'button'); row.setAttribute('tabindex', '0'); row.setAttribute('aria-expanded', open === i ? 'true' : 'false'); row.setAttribute('data-act', 'toggle');
                chip.className = 'zpp-bgo-chip'; if (c) { chip.style.backgroundImage = c.image; chip.style.backgroundSize = c.size; chip.style.backgroundPosition = c.pos; }
                lab.className = 'zpp-bgo-lab'; lab.textContent = labelFor(o, T);
                x.type = 'button'; x.className = 'zpp-bgo-x'; x.setAttribute('data-act', 'remove'); x.title = T.remove; x.setAttribute('aria-label', T.remove);
                x.innerHTML = '<span class="dashicons dashicons-no-alt" aria-hidden="true"></span>';
                row.appendChild(chip); row.appendChild(lab); row.appendChild(x); item.appendChild(row);
                if (open === i) {
                    var panel = tpl.content.firstElementChild.cloneNode(true);
                    fillPanel(panel, o);
                    item.appendChild(panel);
                }
                $l[0].appendChild(item);
            })(i);
        }
        $('#zpp-bgo-add').prop('disabled', l.length >= MAX);
        lastSig = sig(el);
    }

    /* light refresh: row chip + label + gradient bar, without rebuilding the panel (keeps focus and an open colour popover alive) */
    function paintBar(bar, o) {
        var fill = bar && bar.querySelector('.zpp-bgo-barfill');
        if (!fill) { return; }
        fill.style.backgroundImage = 'linear-gradient(90deg,' + o.c1 + ' ' + o.s1 + '%,' + o.c2 + ' ' + o.s2 + '%)';
        Array.prototype.forEach.call(bar.querySelectorAll('.zpp-bgo-handle'), function (h) {
            var two = h.getAttribute('data-stop') === '2';
            h.style.left = (two ? o.s2 : o.s1) + '%'; h.style.backgroundColor = two ? o.c2 : o.c1;
        });
    }
    function refreshRow(i, o) {
        var item = document.querySelector('#zpp-bgo-list .zpp-bgo-item[data-i="' + i + '"]'), chip, lab, c;
        if (!item || !tpl) { return; }
        chip = item.querySelector('.zpp-bgo-chip'); lab = item.querySelector('.zpp-bgo-lab'); c = chipCss(o);
        if (chip) { chip.style.backgroundImage = c ? c.image : ''; chip.style.backgroundSize = c ? c.size : ''; chip.style.backgroundPosition = c ? c.pos : ''; }
        if (lab) { lab.textContent = labelFor(o, labels()); }
        paintBar(item.querySelector('.zpp-bgo-bar'), o);
    }

    function itemOf(node) { var it = node.closest ? node.closest('.zpp-bgo-item') : null; return it ? parseInt(it.getAttribute('data-i'), 10) : -1; }
    function overlayFrom(node) { var el = activeEl(), i = itemOf(node), l = list(el); return (el && i >= 0 && l[i]) ? { el: el, i: i, o: (l[i] = norm(l[i])) } : null; }

    function pickImage(ctx) {
        var frame;
        if (!window.wp || !window.wp.media) { return; }
        frame = window.wp.media({ title: 'Background image', button: { text: 'Use image' }, library: { type: 'image' }, multiple: false });
        frame.on('select', function () {
            var a = frame.state().get('selection').first().toJSON(), u = safeUrl(a && a.url);
            if (!u) { return; }
            ctx.o.imageId = parseInt(a.id, 10) || 0; ctx.o.url = u; ctx.o.res = 'full'; ctx.o.type = 'image';
            commit(ctx.el, 'Change Background Overlay'); render();
        });
        frame.open();
    }
    function resolveRes(ctx, res) {
        var o = ctx.o, id = o.imageId, att, apply2;
        o.res = res;
        if (!id || !window.wp || !window.wp.media) { commit(ctx.el, 'Change Background Overlay'); render(); return; }
        att = window.wp.media.attachment(id);
        apply2 = function () {
            var s = att.get('sizes') || {}, u = safeUrl((s[res] && s[res].url) || (s.full && s.full.url) || att.get('url'));
            if (u) { o.url = u; }
            commit(ctx.el, 'Change Background Overlay'); render();
        };
        if (att.get('sizes') || att.get('url')) { apply2(); } else { att.fetch().always(apply2); }
    }

    /* clicks (capture: Inspector handlers that stop propagation cannot swallow them) */
    document.addEventListener('click', function (e) {
        var t = e.target, node, act, ctx, el, l, v;
        if (!t || !t.closest || !t.closest('#insp-section-bg-color')) { return; }
        if ((node = t.closest('#zpp-bgo-add'))) {
            e.preventDefault(); e.stopPropagation();
            el = activeEl(); if (!el) { return; } l = list(el);
            if (l.length >= MAX) { return; }
            l.push(norm({ type: 'color', color: '#00000033' })); open = l.length - 1; commit(el, 'Add Background Overlay'); render(); return;
        }
        if ((node = t.closest('.zpp-bgo-tab'))) {
            e.preventDefault(); e.stopPropagation();
            if ((ctx = overlayFrom(node))) { ctx.o.type = node.getAttribute('data-t'); commit(ctx.el, 'Change Background Overlay'); render(); }
            return;
        }
        if ((node = t.closest('.zpp-bgo-handle'))) { e.preventDefault(); e.stopPropagation(); selStop = node.getAttribute('data-stop') === '2' ? 2 : 1; render(); return; }
        if ((node = t.closest('.zpp-bgo-seg .zpp-ty-b'))) {
            e.preventDefault(); e.stopPropagation();
            if ((ctx = overlayFrom(node))) { ctx.o[node.closest('.zpp-bgo-seg').getAttribute('data-f')] = node.getAttribute('data-v'); ctx.o = norm(ctx.o); list(ctx.el)[ctx.i] = ctx.o; commit(ctx.el, 'Change Background Overlay'); render(); }
            return;
        }
        if ((node = t.closest('[data-act]'))) {
            act = node.getAttribute('data-act');
            ctx = overlayFrom(node);
            if (!ctx) { return; }
            e.preventDefault(); e.stopPropagation();
            if (act === 'remove') { list(ctx.el).splice(ctx.i, 1); open = -1; commit(ctx.el, 'Remove Background Overlay'); render(); }
            else if (act === 'toggle') { open = (open === ctx.i) ? -1 : ctx.i; selStop = 1; render(); }
            else if (act === 'pick') { pickImage(ctx); }
            else if (act === 'clearimg') { ctx.o.url = ''; ctx.o.imageId = 0; commit(ctx.el, 'Change Background Overlay'); render(); }
            else if (act === 'flip') { v = ctx.o.c1; ctx.o.c1 = ctx.o.c2; ctx.o.c2 = v; commit(ctx.el, 'Change Background Overlay'); render(); }
        }
    }, true);
    /* drag a colour stop along the bar (both stops, both directions); click on the track moves the nearest stop; arrow keys nudge the focused stop */
    var drag = null;
    function pctAt(bar, e) { var r = bar.getBoundingClientRect(); return r.width > 0 ? Math.max(0, Math.min(100, Math.round(((e.clientX - r.left) / r.width) * 100))) : 0; }
    function setStop(d, pct) {
        var key = d.stop === 2 ? 's2' : 's1', inp;
        d.ctx.o[key] = pct;
        list(d.ctx.el)[d.ctx.i] = d.ctx.o = norm(d.ctx.o);
        lastSig = sig(d.ctx.el);
        redraw();
        refreshRow(d.ctx.i, d.ctx.o);
        inp = d.bar.closest('.zpp-bgo-panel').querySelector('[data-f="stopPos"]');
        if (inp && d.stop === selStop) { inp.value = String(pct); }
    }
    document.addEventListener('pointerdown', function (e) {
        var t = e.target, h, bar, ctx, stop, pct;
        if (!t || !t.closest || (e.button !== undefined && e.button > 0)) { return; }
        bar = t.closest('.zpp-bgo-bar');
        if (!bar || !bar.closest('#insp-section-bg-color')) { return; }
        ctx = overlayFrom(bar);
        if (!ctx) { return; }
        h = t.closest('.zpp-bgo-handle');
        pct = pctAt(bar, e);
        stop = h ? (h.getAttribute('data-stop') === '2' ? 2 : 1) : (Math.abs(pct - ctx.o.s1) <= Math.abs(pct - ctx.o.s2) ? 1 : 2);
        e.preventDefault(); e.stopPropagation();
        drag = { ctx: ctx, stop: stop, bar: bar, moved: !h };
        selStop = stop;
        Array.prototype.forEach.call(bar.querySelectorAll('.zpp-bgo-handle'), function (x) { x.classList.toggle('is-on', (x.getAttribute('data-stop') === '2' ? 2 : 1) === stop); });
        if (!h) { setStop(drag, pct); }
    }, true);
    document.addEventListener('pointermove', function (e) {
        if (!drag) { return; }
        e.preventDefault();
        drag.moved = true;
        setStop(drag, pctAt(drag.bar, e));
    }, true);
    function endDrag() {
        var d = drag;
        if (!d) { return; }
        drag = null;
        if (d.moved) { history('Change Background Overlay'); }
        render();
    }
    document.addEventListener('pointerup', endDrag, true);
    document.addEventListener('pointercancel', endDrag, true);
    document.addEventListener('keydown', function (e) {
        var h = e.target && e.target.closest ? e.target.closest('.zpp-bgo-handle') : null, ctx, stop, key, step;
        if (!h || (e.key !== 'ArrowLeft' && e.key !== 'ArrowRight')) { return; }
        ctx = overlayFrom(h);
        if (!ctx) { return; }
        e.preventDefault();
        stop = h.getAttribute('data-stop') === '2' ? 2 : 1; key = stop === 2 ? 's2' : 's1'; step = (e.shiftKey ? 10 : 1) * (e.key === 'ArrowLeft' ? -1 : 1);
        selStop = stop;
        ctx.o[key] = Math.max(0, Math.min(100, ctx.o[key] + step));
        list(ctx.el)[ctx.i] = ctx.o = norm(ctx.o);
        commit(ctx.el, 'Change Background Overlay'); render();
        var again = document.querySelector('#zpp-bgo-list .zpp-bgo-item[data-i="' + ctx.i + '"] .zpp-bgo-handle[data-stop="' + stop + '"]');
        if (again) { again.focus(); }
    });
    document.addEventListener('keydown', function (e) {
        var t = e.target;
        if ((e.key === 'Enter' || e.key === ' ') && t && t.classList && (t.classList.contains('zpp-bgo-row') || t.classList.contains('zpp-bgo-img')) && t.closest('#insp-section-bg-color')) { e.preventDefault(); t.click(); }
    });

    /* field edits: live while typing (no history, no redraw of the panel), one history step on change */
    function onField(e) {
        var t = e.target, f, ctx, v;
        if (!t || !t.getAttribute || !t.closest || !t.closest('#zpp-bgo-list')) { return; }
        f = t.getAttribute('data-f');
        if (!f) { return; }
        ctx = overlayFrom(t);
        if (!ctx) { return; }
        v = t.value;
        if (f === 'res') { if (e.type === 'change') { resolveRes(ctx, v); } return; }
        if (f === 'stopColor') { ctx.o[selStop === 2 ? 'c2' : 'c1'] = colour(v, selStop === 2 ? '#ffffff' : '#000000'); }
        else if (f === 'stopPos') { ctx.o[selStop === 2 ? 's2' : 's1'] = num(v, 0, 100, selStop === 2 ? 100 : 0); }
        else { ctx.o[f] = v; }
        list(ctx.el)[ctx.i] = ctx.o = norm(ctx.o);
        lastSig = sig(ctx.el);
        redraw();
        refreshRow(ctx.i, ctx.o);
        if (e.type === 'change') {
            history('Change Background Overlay');
            if (f === 'gtype') { render(); } /* shows / hides Angle */
        }
    }
    document.addEventListener('input', onField, true);
    document.addEventListener('change', onField, true);

    function tick() {
        var el = activeEl(), id = el ? cid(el.id) : '';
        if (id !== lastId) { lastId = id; open = -1; selStop = 1; render(); return; }
        if (el && sig(el) !== lastSig && !(document.activeElement && document.activeElement.closest && document.activeElement.closest('#zpp-bgo-list'))) { render(); } /* undo / redo / Reset / Clear keep the list current */
    }

    $(function () { window.setInterval(tick, 200); window.setTimeout(render, 300); });
})(window, window.jQuery);
