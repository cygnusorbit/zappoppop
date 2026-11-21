/**
 * ZapPopPop alpha colour picker (shared component).
 * Progressive enhancement: <input type="color" data-zpp-alpha="1"> becomes [swatch][hex field] plus a popover
 * (saturation / brightness, hue, opacity). The ORIGINAL input stays in the DOM (same id, same handlers) as
 * type="hidden" and keeps the value: "#rrggbb" when opaque, "#rrggbbaa" when transparent. Native input / change
 * events are dispatched, so existing inspector bindings keep working. ES5 + Pointer Events: Chrome, Safari, Firefox, Edge.
 */
(function (window, document) {
    'use strict';

    /* ZPP_CP_INSP: every colour input of the Layer Settings drawer (all tabs) gets the same [swatch][hex][revert] row, 30px high. The Layer Color swatch beside the Layer Name (data-zpp-fl) keeps its own compact look. */
    var INSP = '#zappoppop-inspector-drawer';
    var SEL = 'input[data-zpp-alpha], ' + INSP + ' input[type="color"]:not([data-zpp-fl])';
    var proto = window.HTMLInputElement && window.HTMLInputElement.prototype;
    var desc = proto && Object.getOwnPropertyDescriptor(proto, 'value');
    var pop = null, cur = null, uid = 0, R = {};

    function t(k, d) {
        var v = window.zappoppop_alpha_vars;
        return (v && v.i18n && v.i18n[k]) || d;
    }
    function clamp(n, a, b) { return n < a ? a : (n > b ? b : n); }
    function hex2(n) { n = Math.round(clamp(n, 0, 255)); return (n < 16 ? '0' : '') + n.toString(16); }
    function el(tag, cls) { var e = document.createElement(tag); if (cls) { e.className = cls; } return e; }

    /* ---- colour maths ---- */
    function parse(str) {
        var s = String(str === null || str === undefined ? '' : str).replace(/^\s+|\s+$/g, '').toLowerCase(), m, c, a;
        if (s === 'transparent') { return { r: 0, g: 0, b: 0, a: 0 }; }
        m = /^#([0-9a-f]{3,4}|[0-9a-f]{6}|[0-9a-f]{8})$/.exec(s);
        if (m) {
            c = m[1];
            if (c.length <= 4) { c = c.replace(/(.)/g, '$1$1'); }
            return {
                r: parseInt(c.substr(0, 2), 16), g: parseInt(c.substr(2, 2), 16), b: parseInt(c.substr(4, 2), 16),
                a: c.length === 8 ? parseInt(c.substr(6, 2), 16) / 255 : 1
            };
        }
        m = /^rgba?\(\s*([\d.]+)\s*,\s*([\d.]+)\s*,\s*([\d.]+)\s*(?:,\s*([\d.]+)(%?)\s*)?\)$/.exec(s);
        if (m) {
            a = (m[4] === undefined || m[4] === '') ? 1 : parseFloat(m[4]) / (m[5] ? 100 : 1);
            return {
                r: clamp(Math.round(parseFloat(m[1])), 0, 255), g: clamp(Math.round(parseFloat(m[2])), 0, 255),
                b: clamp(Math.round(parseFloat(m[3])), 0, 255), a: clamp(isNaN(a) ? 1 : a, 0, 1)
            };
        }
        return null;
    }
    function toHex(c) {
        var a = Math.round(clamp(c.a === undefined ? 1 : c.a, 0, 1) * 255);
        return '#' + hex2(c.r) + hex2(c.g) + hex2(c.b) + (a < 255 ? hex2(a) : '');
    }
    function rgba(c) { return 'rgba(' + c.r + ',' + c.g + ',' + c.b + ',' + (Math.round(clamp(c.a, 0, 1) * 1000) / 1000) + ')'; }
    function rgb2hsv(r, g, b) {
        var mx, mn, d, h = 0;
        r /= 255; g /= 255; b /= 255;
        mx = Math.max(r, g, b); mn = Math.min(r, g, b); d = mx - mn;
        if (d) {
            if (mx === r) { h = ((g - b) / d) % 6; } else if (mx === g) { h = (b - r) / d + 2; } else { h = (r - g) / d + 4; }
            h *= 60; if (h < 0) { h += 360; }
        }
        return { h: h, s: mx ? d / mx : 0, v: mx };
    }
    function hsv2rgb(h, s, v) {
        var c = v * s, x = c * (1 - Math.abs((h / 60) % 2 - 1)), m = v - c, r = 0, g = 0, b = 0, i = Math.floor(h / 60) % 6;
        if (i === 0) { r = c; g = x; } else if (i === 1) { r = x; g = c; } else if (i === 2) { g = c; b = x; }
        else if (i === 3) { g = x; b = c; } else if (i === 4) { r = x; b = c; } else { r = c; b = x; }
        return { r: Math.round((r + m) * 255), g: Math.round((g + m) * 255), b: Math.round((b + m) * 255) };
    }
    function setFrom(st, c) {
        var q = rgb2hsv(c.r, c.g, c.b);
        if (q.v > 0) { st.s = q.s; }
        if (q.v > 0 && q.s > 0) { st.h = q.h; }
        st.v = q.v; st.a = c.a;
    }
    function colorOf(st) { var c = hsv2rgb(st.h, st.s, st.v); c.a = st.a; return c; }

    /* ---- model input access (bypasses the instance setter so the picker never re-parses its own output) ---- */
    function setRaw(input, v) { if (desc && desc.set) { desc.set.call(input, v); } else { input.value = v; } }
    function fire(input, type) {
        var e;
        if (typeof window.Event === 'function') { e = new window.Event(type, { bubbles: true }); }
        else { e = document.createEvent('Event'); e.initEvent(type, true, true); }
        input.dispatchEvent(e);
    }

    /* ---- per-input UI ---- */
    function paint(ui, v, c) {
        ui.fill.style.backgroundColor = c ? rgba(c) : '';
        if (document.activeElement !== ui.hex) { ui.hex.value = c ? toHex(c) : String(v || ''); }
        ui.btn.disabled = ui.hex.disabled = !!ui.input.disabled;
        if (ui.rev) { ui.rev.disabled = !!ui.input.disabled || !ui.base || !parse(ui.base) || !c || toHex(parse(ui.base)) === toHex(c); }
        if (cur === ui) { paintPop(); }
    }
    function refresh(ui) {
        var v = ui.input.value, c = parse(v), h;
        if (c) {
            h = toHex(c);
            if (h !== ui.last) { setFrom(ui.st, c); ui.last = h; }
        }
        paint(ui, v, c);
    }
    function commit(ui, final) {
        var v = toHex(colorOf(ui.st));
        ui.last = v;
        setRaw(ui.input, v);
        paint(ui, v, parse(v));
        fire(ui.input, 'input');
        if (final) { fire(ui.input, 'change'); }
    }

    function labelText(input, wrap) {
        var lab = null, p;
        try { if (input.id) { lab = document.querySelector('label[for="' + input.id + '"]'); } } catch (e) { lab = null; }
        if (!lab) { p = wrap.previousElementSibling; if (p && p.tagName === 'LABEL') { lab = p; } }
        return lab ? String(lab.textContent || '').replace(/\s+/g, ' ').replace(/^\s+|\s+$/g, '') : '';
    }

    function gridSpan(wrap) {
        var cell = wrap, p, i;
        for (i = 0; i < 4 && cell && cell.parentNode; i++) {
            p = cell.parentNode;
            if (p.nodeType === 1 && window.getComputedStyle(p).display === 'grid') { cell.style.gridColumn = '1 / -1'; return; }
            if (p.id === 'zappoppop-inspector-drawer') { return; }
            cell = p;
        }
    }

    function enhance(input) {
        var raw, attr, pa, pr, h, ui, c, name;
        if (input.__zppCp || !input.parentNode) { return; }
        var inInsp = !!(input.closest && input.closest(INSP));
        raw = input.value; attr = input.getAttribute('value');
        /* a native colour input silently drops the alpha of "#rrggbbaa" (or resets it to #000000): trust the attribute then */
        pa = attr ? parse(attr) : null; pr = parse(raw);
        if (pa && (!pr || raw === '#000000' || (pa.a < 1 && pr.r === pa.r && pr.g === pa.g && pr.b === pa.b))) { raw = attr; }
        h = inInsp ? 30 : (parseInt(input.style.height, 10) || 32);
        ui = { input: input, st: { h: 0, s: 0, v: 1, a: 1 }, last: '', id: ++uid };
        input.__zppCp = ui;

        ui.wrap = el('span', 'zpp-cp' + (!inInsp && input.getAttribute('data-zpp-alpha') === 'compact' ? ' is-compact' : ''));
        ui.btn = el('button', 'zpp-cp-btn');
        ui.btn.type = 'button';
        ui.btn.style.height = h + 'px';
        ui.btn.setAttribute('aria-haspopup', 'dialog');
        ui.btn.setAttribute('aria-expanded', 'false');
        ui.chk = el('span', 'zpp-cp-chk');
        ui.fill = el('span', 'zpp-cp-fill');
        ui.chk.appendChild(ui.fill);
        ui.btn.appendChild(ui.chk);
        ui.hex = el('input', 'zpp-cp-hex');
        ui.hex.type = 'text';
        ui.hex.style.height = h + 'px';
        ui.hex.setAttribute('spellcheck', 'false');
        ui.hex.setAttribute('autocomplete', 'off');
        ui.hex.maxLength = 40;

        input.parentNode.insertBefore(ui.wrap, input);
        if (inInsp) { /* ZPP_CP_BOX: swatch + hex share ONE bordered field, the revert button stands beside it */
            ui.box = el('span', 'zpp-cp-box');
            ui.box.appendChild(ui.btn);
            ui.box.appendChild(ui.hex);
            ui.wrap.appendChild(ui.box);
        } else {
            ui.wrap.appendChild(ui.btn);
            ui.wrap.appendChild(ui.hex);
        }
        if (inInsp) { /* ZPP_CP_INSP: revert icon on the right, back to the value the layer was loaded with */
            ui.rev = el('button', 'zpp-cp-revert');
            ui.rev.type = 'button';
            ui.rev.title = t('revert', 'Revert');
            ui.rev.setAttribute('aria-label', t('revert', 'Revert'));
            ui.rev.innerHTML = '<svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true" focusable="false"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M3 12a9 9 0 1 0 3-6.7M3 4v5h5"/></svg>';
            ui.wrap.appendChild(ui.rev);
        }
        ui.wrap.appendChild(input);
        name = labelText(input, ui.wrap);
        ui.btn.setAttribute('aria-label', (name ? name + ': ' : '') + t('pick', 'Pick colour'));
        ui.hex.setAttribute('aria-label', name || t('hex', 'Colour value'));

        c = parse(raw);
        ui.base = raw;
        input.type = 'hidden';
        if (c) { setRaw(input, raw); }
        if (desc && desc.get && desc.set) {
            try {
                Object.defineProperty(input, 'value', {
                    configurable: true,
                    get: function () { return desc.get.call(this); },
                    set: function (v) { desc.set.call(this, v); ui.base = String(v === null || v === undefined ? '' : v); refresh(ui); }
                });
            } catch (e) {}
        }

        ui.btn.addEventListener('click', function (e) { e.preventDefault(); if (cur === ui) { close(); } else { open(ui); } });
        ui.hex.addEventListener('change', function () {
            var p = parse(ui.hex.value);
            if (!p) { refresh(ui); return; }
            setFrom(ui.st, p);
            commit(ui, true);
        });
        ui.hex.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.keyCode === 13) { e.preventDefault(); e.stopPropagation(); ui.hex.blur(); }
        });
        if (ui.rev) {
            ui.rev.addEventListener('click', function (e) {
                var p = parse(ui.base);
                e.preventDefault();
                if (!p) { return; }
                if (document.activeElement === ui.hex) { ui.hex.blur(); } /* Safari does not focus a clicked button: release the field so it repaints */
                setFrom(ui.st, p);
                commit(ui, true);
            });
            gridSpan(ui.wrap); /* a narrow grid cell would squeeze the hex field: the colour field takes the whole row */
        }
        ui.wrap.addEventListener('pointerenter', function () { refresh(ui); });
        refresh(ui);
    }

    /* ---- popover ---- */
    function drag(node, fn) {
        var on = false;
        function pt(e) {
            var r = node.getBoundingClientRect();
            return { x: clamp((e.clientX - r.left) / (r.width || 1), 0, 1), y: clamp((e.clientY - r.top) / (r.height || 1), 0, 1) };
        }
        function end(e) { var p; if (!on) { return; } on = false; p = pt(e); fn(p.x, p.y, true); }
        node.addEventListener('pointerdown', function (e) {
            var p;
            if (e.button > 0 || !cur) { return; }
            e.preventDefault(); node.focus(); on = true;
            try { node.setPointerCapture(e.pointerId); } catch (x) {}
            p = pt(e); fn(p.x, p.y, false);
        });
        node.addEventListener('pointermove', function (e) { var p; if (!on) { return; } p = pt(e); fn(p.x, p.y, false); });
        node.addEventListener('pointerup', end);
        node.addEventListener('pointercancel', end);
    }
    function keys(node, fn) {
        node.addEventListener('keydown', function (e) {
            var k = e.key || e.keyCode, step = e.shiftKey ? 10 : 1, dx = 0, dy = 0;
            if (k === 'ArrowLeft' || k === 37) { dx = -step; } else if (k === 'ArrowRight' || k === 39) { dx = step; }
            else if (k === 'ArrowUp' || k === 38) { dy = step; } else if (k === 'ArrowDown' || k === 40) { dy = -step; }
            else { return; }
            e.preventDefault(); e.stopPropagation();
            if (cur) { fn(dx, dy); }
        });
    }

    /* ZPP_CP_EYEDROP: screen eyedropper via the native EyeDropper API (Chrome, Edge, Opera). Safari and Firefox do not ship it, so the button is only built where it works. */
    function hasEye() { return typeof window.EyeDropper === 'function'; }
    function eyeBtn() {
        if (!hasEye()) { return ''; }
        return '<button type="button" class="zpp-cp-eye" title="' + t('eyedrop', 'Pick colour from screen') + '" aria-label="' + t('eyedrop', 'Pick colour from screen') + '">' +
            '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M11 7l6 6"/><path d="M4 16L15.7 4.3a1 1 0 0 1 1.4 0l2.6 2.6a1 1 0 0 1 0 1.4L8 20H4v-4z"/></svg></button>';
    }
    function buildPop() {
        var p = el('div', 'zpp-cp-pop'), pct;
        if (hasEye()) { p.className += ' has-eye'; }
        p.setAttribute('role', 'dialog');
        p.setAttribute('aria-label', t('pick', 'Pick colour'));
        p.innerHTML =
            '<div class="zpp-cp-sv" tabindex="0" role="slider" aria-label="' + t('sv', 'Saturation and brightness') + '"><div class="zpp-cp-thumb zpp-cp-sv-t"></div></div>' +
            '<div class="zpp-cp-bar zpp-cp-hue" tabindex="0" role="slider" aria-valuemin="0" aria-valuemax="360" aria-label="' + t('hue', 'Hue') + '"><div class="zpp-cp-thumb zpp-cp-hue-t"></div></div>' +
            '<div class="zpp-cp-bar zpp-cp-alpha" tabindex="0" role="slider" aria-valuemin="0" aria-valuemax="100" aria-label="' + t('opacity', 'Opacity') + '"><div class="zpp-cp-alpha-fill"></div><div class="zpp-cp-thumb zpp-cp-alpha-t"></div></div>' +
            '<input type="text" class="zpp-cp-pop-hex" spellcheck="false" autocomplete="off" maxlength="40" aria-label="' + t('hex', 'Colour value') + '">' +
            '<div class="zpp-cp-foot">' + eyeBtn() + '<label class="zpp-cp-pct"><span>' + t('opacity', 'Opacity') + '</span><input type="number" class="zpp-cp-pct-in" min="0" max="100" step="1"><span>%</span></label>' +
            '<button type="button" class="zpp-cp-clear" title="' + t('clear', 'Transparent') + '" aria-label="' + t('clear', 'Transparent') + '">' + t('clear', 'Transparent') + '</button></div>';
        document.body.appendChild(p);
        pop = p;
        R = {
            sv: p.querySelector('.zpp-cp-sv'), svT: p.querySelector('.zpp-cp-sv-t'),
            hue: p.querySelector('.zpp-cp-hue'), hueT: p.querySelector('.zpp-cp-hue-t'),
            al: p.querySelector('.zpp-cp-alpha'), alF: p.querySelector('.zpp-cp-alpha-fill'), alT: p.querySelector('.zpp-cp-alpha-t'),
            pct: p.querySelector('.zpp-cp-pct-in'), eye: p.querySelector('.zpp-cp-eye'), clr: p.querySelector('.zpp-cp-clear'), hx: p.querySelector('.zpp-cp-pop-hex')
        };
        drag(R.sv, function (x, y, f) { cur.st.s = x; cur.st.v = 1 - y; commit(cur, f); });
        drag(R.hue, function (x, y, f) { cur.st.h = x * 360; commit(cur, f); });
        drag(R.al, function (x, y, f) { cur.st.a = Math.round(x * 100) / 100; commit(cur, f); });
        keys(R.sv, function (dx, dy) { cur.st.s = clamp(cur.st.s + dx / 100, 0, 1); cur.st.v = clamp(cur.st.v + dy / 100, 0, 1); commit(cur, true); });
        keys(R.hue, function (dx) { cur.st.h = clamp(cur.st.h + dx, 0, 360); commit(cur, true); });
        keys(R.al, function (dx) { cur.st.a = clamp(Math.round((cur.st.a + dx / 100) * 100) / 100, 0, 1); commit(cur, true); });
        pct = R.pct;
        pct.addEventListener('input', function () { if (!cur) { return; } cur.st.a = clamp(parseFloat(pct.value) || 0, 0, 100) / 100; commit(cur, false); });
        pct.addEventListener('change', function () { if (!cur) { return; } cur.st.a = clamp(parseFloat(pct.value) || 0, 0, 100) / 100; commit(cur, true); });
        R.clr.addEventListener('click', function () { if (!cur) { return; } cur.st.a = 0; commit(cur, true); });
        if (R.eye) {
            R.eye.addEventListener('click', function (e) {
                var target = cur;
                e.preventDefault();
                if (!target || !hasEye()) { return; }
                try {
                    new window.EyeDropper().open().then(function (res) {
                        var c = parse(res && res.sRGBHex);
                        if (!c || cur !== target) { return; }
                        c.a = target.st.a > 0 ? target.st.a : 1; /* keep the opacity; a fully transparent colour would hide the pick */
                        setFrom(target.st, c);
                        commit(target, true);
                    }, function () { /* Esc / cancelled */ });
                } catch (err) { /* blocked: ignore */ }
            });
        }
        R.hx.addEventListener('change', function () {
            var c;
            if (!cur) { return; }
            c = parse(R.hx.value);
            if (!c) { paintPop(); return; }
            setFrom(cur.st, c);
            commit(cur, true);
        });
        R.hx.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.keyCode === 13) { e.preventDefault(); e.stopPropagation(); R.hx.blur(); }
        });
        p.addEventListener('keydown', function (e) { if (e.key === 'Escape' || e.keyCode === 27) { e.stopPropagation(); close(); } });

        document.addEventListener('pointerdown', function (e) {
            if (cur && !pop.contains(e.target) && !cur.wrap.contains(e.target)) { close(true); }
        }, true);
        window.addEventListener('resize', place);
        window.addEventListener('scroll', function (e) { if (cur && !(pop.contains && pop.contains(e.target))) { place(); } }, true);
    }
    function paintPop() {
        var st, base, c, rgb;
        if (!cur || !pop) { return; }
        st = cur.st; base = hsv2rgb(st.h, 1, 1); c = hsv2rgb(st.h, st.s, st.v);
        rgb = c.r + ',' + c.g + ',' + c.b;
        R.sv.style.backgroundColor = 'rgb(' + base.r + ',' + base.g + ',' + base.b + ')';
        R.svT.style.left = (st.s * 100) + '%'; R.svT.style.top = ((1 - st.v) * 100) + '%';
        R.hueT.style.left = (st.h / 360 * 100) + '%';
        R.alT.style.left = (st.a * 100) + '%';
        R.alF.style.backgroundImage = 'linear-gradient(to right, rgba(' + rgb + ',0), rgb(' + rgb + '))';
        R.hue.setAttribute('aria-valuenow', String(Math.round(st.h)));
        R.al.setAttribute('aria-valuenow', String(Math.round(st.a * 100)));
        if (document.activeElement !== R.pct) { R.pct.value = String(Math.round(st.a * 100)); }
        if (document.activeElement !== R.hx) { R.hx.value = toHex(colorOf(st)); }
    }
    function place() {
        var r, w, h, vw, vh, left, top;
        if (!cur || !pop) { return; }
        if (!document.documentElement.contains(cur.input)) { close(true); return; }
        r = cur.btn.getBoundingClientRect(); w = pop.offsetWidth; h = pop.offsetHeight;
        vw = document.documentElement.clientWidth; vh = document.documentElement.clientHeight;
        left = clamp(r.left, 8, Math.max(8, vw - w - 8));
        top = r.bottom + 6;
        if (top + h > vh - 8 && r.top - h - 6 > 8) { top = r.top - h - 6; }
        pop.style.left = left + 'px'; pop.style.top = Math.max(8, top) + 'px';
    }
    function open(ui) {
        if (!pop) { buildPop(); }
        if (cur && cur !== ui) { close(true); }
        refresh(ui);
        cur = ui;
        pop.className = 'zpp-cp-pop is-open' + (R.eye ? ' has-eye' : '');
        ui.btn.setAttribute('aria-expanded', 'true');
        paintPop(); place();
        R.sv.focus();
    }
    function close(noFocus) {
        var u = cur;
        if (!u) { return; }
        cur = null;
        pop.className = 'zpp-cp-pop' + (R.eye ? ' has-eye' : '');
        u.btn.setAttribute('aria-expanded', 'false');
        if (!noFocus) { u.btn.focus(); }
    }

    /* ---- discovery: existing inputs now, inspector-built inputs later ---- */
    function scan(root) {
        var list, i;
        if (!root) { return; }
        if (root.nodeType === 1 && root.matches && root.matches(SEL)) { enhance(root); }
        if (root.querySelectorAll) {
            list = root.querySelectorAll(SEL);
            for (i = 0; i < list.length; i++) { enhance(list[i]); }
        }
    }
    window.ZapPopPopAlphaPicker = { parse: parse, toHex: toHex, scan: scan };

    if (document.documentElement) {
        scan(document);
        if (window.MutationObserver) {
            new window.MutationObserver(function (muts) {
                var i, j, n;
                for (i = 0; i < muts.length; i++) {
                    for (j = 0; j < muts[i].addedNodes.length; j++) {
                        n = muts[i].addedNodes[j];
                        if (n.nodeType === 1) { scan(n); }
                    }
                }
                if (cur && !document.documentElement.contains(cur.input)) { close(true); }
            }).observe(document.documentElement, { childList: true, subtree: true });
        }
    }
})(window, document);
