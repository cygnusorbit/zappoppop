/* ZPP_LAYER_DEFAULTS: "Save <Tab> Settings as Default" on every Inspector tab (Content, Style, Logic, Data, Advanced).
   Storage : option zappoppop_saved_defaults -> scope "layers" -> { layerType: { tab: { prop: value } } } (site-wide, whitelisted server side).
   Used by : (1) every NEW layer of that type, (2) each tab's Reset button (resets to the saved default, else the built-in spawn default).
   The data lifecycle is untouched: defaults are only written into the JS model; the normal pre-save flush / AJAX whitelist / renderer handle them. */
(function (window, $) {
    'use strict';
    if (!$) { return; }

    var ORDER = ['basic', 'style', 'logic', 'data', 'advanced'];
    var TABS = { basic: 'Content', style: 'Style', logic: 'Logic', data: 'Data', advanced: 'Advanced' };
    /* identity, position, state and unique field ids are never part of a default (mirrors ZapPopPop_Saved_Defaults::layer_skip) */
    var SKIP = ['id', 'type', 'name', 'right', 'bottom', 'zIndex', 'locked', 'hidden', 'custom_settings', 'fieldId', 'fieldName', 'field_id', 'field_name']; /* ZPP_LD_COORDS: X / Y / Width / Height can now be defaults */
    /* Coordinates & Bounds inputs have no data-prop, so the Content tab lists their model keys here; x / y / w / h follow only when the layer already has them */
    var COORDS = ['left', 'top', 'width', 'height', 'x', 'y', 'w', 'h'];
    var COORD_ALIAS = [['left', 'x'], ['top', 'y'], ['width', 'w'], ['height', 'h']];
    var store = {};

    function own(o, k) { return Object.prototype.hasOwnProperty.call(o, k); }
    function clone(o) { return JSON.parse(JSON.stringify(o)); }
    function core() { return window.ZapPopPopBuilderCore || null; }
    function cid(v) { return String(v || '').replace(/^zappoppop-el-/, '').replace(/^el-/, '').trim(); }
    function drawer() { return document.getElementById('zappoppop-inspector-drawer'); }
    function activeEl() {
        var c = core();
        if (!c || !c.state) { return null; }
        var id = cid(c.state.activeId);
        if (!id) { return null; }
        var list = (c.state.canvases && c.state.canvases[c.state.currentCanvas || 1]) || [];
        for (var i = 0; i < list.length; i++) { if (list[i] && cid(list[i].id) === id) { return list[i]; } }
        return null;
    }

    function setStore(v) {
        store = (v && typeof v === 'object' && !Array.isArray(v)) ? clone(v) : {};
        if (window.zappoppop_vars) { window.zappoppop_vars.layer_defaults = clone(store); }
    }
    function loadStore() { setStore(window.zappoppop_vars && window.zappoppop_vars.layer_defaults); }

    function get(type, tab) { return (store[type] && store[type][tab] && typeof store[type][tab] === 'object') ? store[type][tab] : null; }
    function has(type, tab) { var g = get(type, tab); return !!(g && Object.keys(g).length); }

    /* same flush the Save Popup button performs, so the model holds what the Inspector shows */
    function flush() {
        try {
            var R = window.ZapPopPopElementsRegistry, I = window.ZapPopPopBuilderInspector, IO = window.ZapPopPopBuilderIO;
            if (R && typeof R.flushElementFromDOM === 'function') { R.flushElementFromDOM(); }
            if (I && typeof I.flushSanitizedLayerSettings === 'function') { I.flushSanitizedLayerSettings(); }
            if (IO && typeof IO.flushActiveElement === 'function') { IO.flushActiveElement(); }
        } catch (e) { /* best effort */ }
    }

    function keysFor(tab, el) {
        var fn = window.ZapPopTabKeys && window.ZapPopTabKeys[tab], keys = {};
        if (typeof fn === 'function') {
            try { keys = fn(el) || {}; } catch (e) { keys = {}; }
            return keys;
        }
        var pane = document.getElementById(tab === 'basic' ? 'insp-tab-basic' : 'insp-tab-' + tab);
        if (pane) {
            Array.prototype.forEach.call(pane.querySelectorAll('[data-prop]'), function (n) {
                var k = n.getAttribute('data-prop');
                if (k) { keys[k] = 1; }
            });
        }
        return keys;
    }

    function snapshot(tab) {
        var el = activeEl();
        if (!el) { return null; }
        flush();
        var keys = keysFor(tab, el), out = {}, n = 0;
        if (tab === 'basic') { COORDS.forEach(function (k) { keys[k] = 1; }); } /* ZPP_LD_COORDS */
        Object.keys(keys).forEach(function (k) {
            if (SKIP.indexOf(k) !== -1 || !own(el, k)) { return; }
            var v = el[k];
            if (v === undefined || typeof v === 'function') { return; }
            try { out[k] = clone(v); n++; } catch (e) { /* skip non-serialisable */ }
        });
        COORD_ALIAS.forEach(function (p) { if (own(out, p[0]) && own(out, p[1])) { out[p[1]] = out[p[0]]; } }); /* ZPP_LD_COORDS: keep the alias equal to the main value */
        return n ? out : null;
    }

    function applyTo(el, tab) {
        if (!el) { return 0; }
        var sv = get(String(el.type || ''), tab), n = 0;
        if (!sv) { return 0; }
        Object.keys(sv).forEach(function (k) {
            if (SKIP.indexOf(k) !== -1) { return; }
            el[k] = clone(sv[k]);
            n++;
        });
        return n;
    }

    /* the Animation / form fields have no data-prop: the pre-save flush reads them from the DOM, so mirror the model into them */
    function syncDom(el) {
        if (!el) { return; }
        var map = { animIn: '#prop-anim-in', animStart: '#prop-anim-start', animDuration: '#prop-anim-duration', animDelay: '#prop-anim-delay', requiredMessage: '#prop-gp-required-msg', autocomplete: '#prop-gp-autocomplete' };
        Object.keys(map).forEach(function (k) { if (own(el, k) && el[k] !== undefined && el[k] !== null) { $(map[k]).val(el[k]); } });
        if (own(el, 'required')) { $('#prop-gp-required').prop('checked', !!el.required); }
    }

    function post(action, tab, type, snap, done) {
        var cfg = window.zappoppopDefaults;
        if (!cfg || !cfg.ajax_url) { done(false); return; }
        $.post(cfg.ajax_url, { action: action, nonce: cfg.nonce, type: type, tab: tab, data: snap ? JSON.stringify(snap) : '' })
            .done(function (res) {
                if (res && res.success) { setStore(res.data && res.data.layers); done(true); } else { done(false); }
            })
            .fail(function () { done(false); });
    }

    /* ---- UI: one row above each tab's Reset button ---- */
    function wrapFor(tab) {
        if (tab === 'basic') { return document.getElementById('zpp-ct-reset-wrap'); }
        var pane = document.getElementById('insp-tab-' + tab);
        return pane ? pane.querySelector('.zpp-tr-wrap') : null;
    }

    /* ZPP_LD_ONE: ONE "Save Layer Settings as Default" button (+ Clear) in a footer of the Inspector saves / clears all 5 tabs at once */
    function anySaved(type) {
        for (var i = 0; i < ORDER.length; i++) { if (type && has(type, ORDER[i])) { return true; } }
        return false;
    }

    function refresh() {
        var el = activeEl(), type = el ? String(el.type || '') : '';
        var c = document.querySelector('#zpp-ld-footer .zpp-ld-clear');
        if (!c) { return; }
        var show = anySaved(type);
        if (c.hidden === show) { c.hidden = !show; }
    }

    function ensureUi() {
        var d = drawer();
        if (!d) { return; }
        ORDER.forEach(function (tab) { /* the old per-tab rows are gone */
            var w = wrapFor(tab), old = w && w.querySelector('.zpp-ld-row');
            if (old && old.parentNode) { old.parentNode.removeChild(old); }
        });
        if (!document.getElementById('zpp-ld-footer')) {
            var f = document.createElement('div');
            f.id = 'zpp-ld-footer';
            f.className = 'zpp-ld-row zpp-ld-footer';
            f.innerHTML =
                '<button type="button" class="zpp-ld-save" data-zpp-ld="save" title="Saves the Content, Style, Logic, Data and Advanced settings of this layer type as the default for new layers"><span>Save Layer Settings as Default</span></button>' +
                '<button type="button" class="zpp-ld-clear" data-zpp-ld="clear" hidden title="Remove the saved defaults (all tabs) for this layer type"><span>Clear</span></button>';
            d.appendChild(f);
        }
        refresh();
    }

    function setText(btn, text) {
        var s = btn.querySelector('span');
        if (s && s.textContent !== text) { s.textContent = text; }
    }
    function flash(btn, text, cls) {
        if (!btn.__orig) { btn.__orig = btn.querySelector('span').textContent; }
        setText(btn, text);
        btn.classList.remove('is-ok', 'is-err', 'is-busy');
        if (cls) { btn.classList.add(cls); }
        window.clearTimeout(btn.__t);
        if (cls !== 'is-busy') {
            btn.__t = window.setTimeout(function () { setText(btn, btn.__orig); btn.classList.remove('is-ok', 'is-err'); }, 1800);
        }
    }
    function disarm(btn) {
        window.clearTimeout(btn.__a);
        btn.classList.remove('is-armed');
        setText(btn, 'Clear');
    }

    /* posts one request per tab, one after the other (the option is read-modify-written server side, so no parallel requests) */
    function chain(action, type, jobs, done) {
        var okAll = true, i = 0;
        (function next() {
            if (i >= jobs.length) { done(okAll); return; }
            var j = jobs[i++];
            post(action, j.tab, type, j.snap, function (ok) { if (!ok) { okAll = false; } next(); });
        })();
    }

    document.addEventListener('click', function (e) {
        var b = e.target && e.target.closest ? e.target.closest('[data-zpp-ld]') : null, d = drawer();
        if (!b || !d || !d.contains(b)) { return; }
        e.preventDefault();
        var el = activeEl();
        if (!el) { return; }
        var type = String(el.type || '');

        if (b.getAttribute('data-zpp-ld') === 'save') {
            if (b.__busy) { return; }
            var jobs = [];
            ORDER.forEach(function (tab) { var snap = snapshot(tab); if (snap) { jobs.push({ tab: tab, snap: snap }); } });
            if (!jobs.length) { flash(b, 'Nothing To Save', 'is-err'); return; }
            b.__busy = true;
            flash(b, 'Saving...', 'is-busy');
            chain('zappoppop_layer_defaults_save', type, jobs, function (ok) {
                b.__busy = false;
                flash(b, ok ? 'Saved As Default ✓' : 'Could Not Save All Tabs', ok ? 'is-ok' : 'is-err');
                refresh();
            });
            return;
        }

        /* clear: two clicks, like every Reset button */
        if (!b.classList.contains('is-armed')) {
            b.classList.add('is-armed');
            setText(b, 'Click Again To Clear');
            b.__a = window.setTimeout(function () { disarm(b); }, 3000);
            return;
        }
        disarm(b);
        var cj = [];
        ORDER.forEach(function (tab) { if (has(type, tab)) { cj.push({ tab: tab, snap: null }); } });
        chain('zappoppop_layer_defaults_clear', type, cj, function () { refresh(); });
    });

    /* ---- new layers of a type start from its saved defaults (installed last so it wins over the built-in size presets) ---- */
    function finishSpawn(made, touchedSize, savedPos) {
        var cv = window.ZapPopPopBuilderCanvas, L = window.ZapPopPopBuilderLayers, I = window.ZapPopPopBuilderInspector, c = core(), SP = window.ZapPopPopSpawnPlace;
        if (touchedSize && !savedPos && SP && typeof SP.place === 'function' && cv && typeof cv.getActiveElements === 'function') { SP.place(made, cv.getActiveElements()); }
        if (cv && typeof cv.renderCanvas === 'function') { cv.renderCanvas(); }
        if (L && typeof L.renderLayers === 'function') { L.renderLayers(); }
        if (I && typeof I.open === 'function') { I.open(made.id); }
        if (c && typeof c.pushHistory === 'function') { c.pushHistory('Apply Saved Layer Defaults'); }
    }

    function wrapAdd() {
        var cv = window.ZapPopPopBuilderCanvas;
        if (!cv || typeof cv.addElement !== 'function') { return false; }
        if (cv.__zppLdWrapped) { return true; }
        cv.__zppLdWrapped = true;
        var orig = cv.addElement;
        cv.addElement = function (type) {
            var before = typeof this.getActiveElements === 'function' ? (this.getActiveElements() || []).length : 0;
            var res = orig.apply(this, arguments);
            if (window.__zppFactorySpawn) { return res; }
            var t = String(type || ''), list = typeof this.getActiveElements === 'function' ? (this.getActiveElements() || []) : [];
            if (!store[t] || list.length <= before) { return res; }
            var made = list[list.length - 1];
            if (!made || String(made.type) !== t) { return res; }
            var n = 0, size = false, pos = false;
            ORDER.forEach(function (tab) {
                var sv = get(t, tab);
                n += applyTo(made, tab);
                if (sv && (own(sv, 'width') || own(sv, 'height') || own(sv, 'w') || own(sv, 'h'))) { size = true; }
                if (sv && (own(sv, 'left') || own(sv, 'top'))) { pos = true; } /* ZPP_LD_COORDS: a saved position is kept exactly, no overlap nudge */
            });
            if (n) { finishSpawn(made, size, pos); }
            return res;
        };
        return true;
    }

    window.ZapPopPopLayerDefaults = { get: get, has: has, snapshot: snapshot, applyTo: applyTo, syncDom: syncDom, reload: loadStore };
    loadStore();

    $(function () {
        loadStore();
        ensureUi();
        var d = drawer(), timer = 0;
        if (d && window.MutationObserver) {
            new MutationObserver(function () {
                window.clearTimeout(timer);
                timer = window.setTimeout(ensureUi, 120);
            }).observe(d, { childList: true, subtree: true });
        }
        $(document).on('click.zppLd', '.zappoppop-canvas-item, .zappoppop-layer-item', function () {
            [150, 450, 950].forEach(function (ms) { window.setTimeout(ensureUi, ms); });
        });
        var tries = 0, iv = window.setInterval(function () { ensureUi(); if (++tries > 20) { window.clearInterval(iv); } }, 500);
        window.setTimeout(wrapAdd, 1200);
        window.setTimeout(wrapAdd, 3000);
    });
})(window, window.jQuery);
