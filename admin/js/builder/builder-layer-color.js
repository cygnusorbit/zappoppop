/* ZPP_LAYER_COLOR_PRESETS: Layer Color swatch (#prop-fl-color, model key fieldLabelColor) is a circle on every element; click opens a preset popover.
   Presets are per site (option zappoppop_layer_color_presets, AJAX zappoppop_layer_color_presets). + saves the picked colour, the x on a hovered circle removes it (undo for 5 s; any preset can be removed).
   The colour itself keeps the existing lifecycle: Inspector DOM -> model (builder-field-label.js) -> history -> AJAX whitelist (fieldLabelColor) -> Layers Panel icon. */
(function (window, $) {
    'use strict';
    if (!$) { return; }
    var DEFAULTS = ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#ec4899', '#0ea5e9', '#8b5cf6', '#14b8a6', '#f97316'], DEF_FIRST = '#64748b';
    var MAX = 24, HEX = /^#[0-9a-f]{6}$/i, saveTimer = null;
    var V = window.zappoppop_vars || {};
    var presets = $.isArray(V.layer_color_presets) ? V.layer_color_presets.filter(function (c) { return HEX.test(c); }) : DEFAULTS.slice();
    /* ZPP_LC_DEFAULT: the first swatch is always the default Layer Color #64748b (not stored, not removable); the old built-in #334155 / #64748b entries are dropped from the stored list so they are not shown twice */
    presets = presets.filter(function (c) { return c.toLowerCase() !== '#334155' && c.toLowerCase() !== DEF_FIRST; });
    function themeHex() { return DEF_FIRST; }
    var pop = null, undo = null, undoTimer = null;

    var CSS = ''
        + 'html body #zappoppop-inspector-drawer.zappoppop-inspector-drawer #prop-fl-color{-webkit-appearance:none;appearance:none;width:30px !important;height:30px !important;min-height:30px !important;flex:0 0 30px !important;border-radius:50% !important;padding:0 !important;overflow:hidden;border:1px solid var(--zpp-builder-field-border,#475569) !important;background:none !important;cursor:pointer;box-sizing:border-box !important}\n'
        + 'html body #zappoppop-inspector-drawer.zappoppop-inspector-drawer #prop-fl-color::-webkit-color-swatch-wrapper{padding:0}\n'
        + 'html body #zappoppop-inspector-drawer.zappoppop-inspector-drawer #prop-fl-color::-webkit-color-swatch{border:0;border-radius:50%}\n'
        + 'html body #zappoppop-inspector-drawer.zappoppop-inspector-drawer #prop-fl-color::-moz-color-swatch{border:0;border-radius:50%}\n'
        + '#zpp-lc-pop{position:absolute;z-index:100000;width:214px;box-sizing:border-box;padding:12px;border-radius:12px;background:var(--zpp-builder-content-bg,#1e293b);border:1px solid var(--zpp-builder-border-color,#475569);box-shadow:0 8px 24px rgba(0,0,0,.35);color:var(--zpp-builder-content-text,#e2e8f0);font-size:12px}\n'
        + '#zpp-lc-pop .zpp-lc-t{margin:0 0 8px;font-weight:500;color:var(--zpp-builder-muted-text,#94a3b8)}\n'
        + '#zpp-lc-pop .zpp-lc-grid{display:grid;grid-template-columns:repeat(6,1fr);gap:8px}\n'
        + '#zpp-lc-pop .zpp-lc-dot{position:relative;width:26px;height:26px;padding:0;border-radius:50%;border:1px solid var(--zpp-builder-border-color,#475569);cursor:pointer;box-sizing:border-box}\n'
        + '#zpp-lc-pop .zpp-lc-dot.on{outline:2px solid var(--zpp-builder-accent-color,#6366f1);outline-offset:1px}\n'
        + '#zpp-lc-pop .zpp-lc-add{width:26px;height:26px;padding:0;border-radius:50%;border:1px dashed var(--zpp-builder-muted-text,#94a3b8);background:transparent;color:var(--zpp-builder-muted-text,#94a3b8);cursor:pointer;line-height:1;font-size:16px;display:flex;align-items:center;justify-content:center;box-sizing:border-box}\n'
        + '#zpp-lc-pop .zpp-lc-foot{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-top:12px;padding-top:10px;border-top:1px solid var(--zpp-builder-border-color,#475569);color:var(--zpp-builder-muted-text,#94a3b8)}\n'
        + '#zpp-lc-pop #zpp-lc-pick{-webkit-appearance:none;appearance:none;width:26px;height:26px;padding:0;border:1px solid var(--zpp-builder-border-color,#475569);border-radius:50%;background:none;overflow:hidden;cursor:pointer;flex:0 0 26px;box-sizing:border-box}\n'
        + '#zpp-lc-pop #zpp-lc-pick::-webkit-color-swatch-wrapper{padding:0}\n'
        + '#zpp-lc-pop #zpp-lc-pick::-webkit-color-swatch{border:0;border-radius:50%}\n'
        + '#zpp-lc-pop #zpp-lc-pick::-moz-color-swatch{border:0;border-radius:50%}\n';
    CSS += '#zpp-lc-pop .zpp-lc-x{position:absolute;top:-6px;right:-6px;width:14px;height:14px;border-radius:50%;display:none;align-items:center;justify-content:center;background:#0f172a;color:#fff;font-size:11px;line-height:1;border:1.5px solid var(--zpp-builder-content-bg,#1e293b);box-sizing:content-box;cursor:pointer}\n'
        + '#zpp-lc-pop .zpp-lc-dot:hover .zpp-lc-x,#zpp-lc-pop .zpp-lc-dot:focus-visible .zpp-lc-x{display:flex}\n'
        + '@media (hover:none){#zpp-lc-pop .zpp-lc-x{display:flex}}\n'
        + '#zpp-lc-pop .zpp-lc-empty{grid-column:1/-1;margin:0;color:var(--zpp-builder-muted-text,#94a3b8)}\n'
        + '#zpp-lc-pop .zpp-lc-undo{display:none;align-items:center;justify-content:space-between;gap:10px;margin-top:10px;padding:7px 10px;border-radius:8px;background:var(--zpp-builder-accent-color,#6366f1);color:#fff}\n'
        + '#zpp-lc-pop .zpp-lc-undo button{background:none;border:0;padding:0;color:#fff;text-decoration:underline;cursor:pointer;font-size:12px}\n';
    function css() {
        if (document.getElementById('zpp-lc-css')) { return; }
        var s = document.createElement('style'); s.id = 'zpp-lc-css'; s.textContent = CSS; document.head.appendChild(s);
    }
    function persist() {
        window.clearTimeout(saveTimer);
        saveTimer = window.setTimeout(function () {
            $.post(V.ajax_url, { action: 'zappoppop_layer_color_presets', nonce: V.nonce, presets: presets });
        }, 400);
    }
    function input() { return document.getElementById('prop-fl-color'); }
    function setColor(c) {
        var i = input(); if (!i) { return; }
        i.value = c;
        $(i).trigger('input').trigger('change');
    }
    function close() { window.clearTimeout(undoTimer); undo = null; if (pop) { $(pop).remove(); pop = null; } $(document).off('.zppLcOut'); }
    function showUndo() {
        if (!pop) { return; }
        var u = pop.querySelector('.zpp-lc-undo'); u.style.display = 'flex';
        window.clearTimeout(undoTimer);
        undoTimer = window.setTimeout(function () { undo = null; if (pop) { pop.querySelector('.zpp-lc-undo').style.display = 'none'; } }, 5000);
    }
    function render() {
        if (!pop) { return; }
        var cur = (input() && input().value || '').toLowerCase(), g = pop.querySelector('.zpp-lc-grid');
        g.innerHTML = '';
        var th = themeHex(), tb = document.createElement('button');
        tb.type = 'button'; tb.className = 'zpp-lc-dot' + (th === cur ? ' on' : ''); tb.style.background = th; tb.setAttribute('aria-label', th + ' (default)'); tb.title = th + ' (default)';
        tb.addEventListener('click', function (e) { e.preventDefault(); setColor(th); render(); });
        g.appendChild(tb);
        presets.forEach(function (c, idx) {
            if (c.toLowerCase() === th) { return; }
            var b = document.createElement('button');
            b.type = 'button'; b.className = 'zpp-lc-dot' + (c.toLowerCase() === cur ? ' on' : ''); b.style.background = c; b.setAttribute('aria-label', c); b.title = c;
            function del() { undo = { c: c, idx: idx }; presets.splice(idx, 1); persist(); showUndo(); render(); } /* ZPP_LC_DELETE */
            var x = document.createElement('span');
            x.className = 'zpp-lc-x'; x.textContent = '\u00d7'; x.setAttribute('aria-hidden', 'true'); x.title = 'Remove preset';
            x.addEventListener('click', function (e) { e.preventDefault(); e.stopPropagation(); del(); });
            b.appendChild(x);
            b.addEventListener('click', function (e) { e.preventDefault(); setColor(c); render(); });
            b.addEventListener('keydown', function (e) { if (e.key === 'Delete' || e.key === 'Backspace') { e.preventDefault(); del(); } });
            g.appendChild(b);
        });
        if (presets.length < MAX - 1) {
            var a = document.createElement('button');
            a.type = 'button'; a.className = 'zpp-lc-add'; a.setAttribute('aria-label', 'Add preset color'); a.title = 'Save the picked color as a preset'; a.textContent = '+';
            a.addEventListener('click', function (e) {
                e.preventDefault();
                var v = String(pop.querySelector('#zpp-lc-pick').value || '').toLowerCase();
                if (HEX.test(v) && presets.map(function (x) { return x.toLowerCase(); }).indexOf(v) < 0) { presets.push(v); persist(); }
                pop.querySelector('.zpp-lc-h').textContent = 'Saved ' + v; render();
            });
            g.appendChild(a);
        }
    }
    function open() {
        var i = input(); if (!i) { return; }
        css(); close();
        pop = document.createElement('div'); pop.id = 'zpp-lc-pop'; pop.setAttribute('role', 'dialog'); pop.setAttribute('aria-label', 'Layer color presets');
        pop.innerHTML = '<p class="zpp-lc-t">Preset colors</p><div class="zpp-lc-grid"></div><div class="zpp-lc-foot"><span class="zpp-lc-h">Click + to save a preset</span><input type="color" id="zpp-lc-pick" aria-label="Pick a color"></div><div class="zpp-lc-undo" role="status"><span>Preset removed</span><button type="button">Undo</button></div>';
        pop.querySelector('.zpp-lc-undo button').addEventListener('click', function (e) {
            e.preventDefault();
            if (undo && presets.map(function (x) { return x.toLowerCase(); }).indexOf(undo.c.toLowerCase()) < 0) { presets.splice(Math.min(undo.idx, presets.length), 0, undo.c); persist(); }
            undo = null; window.clearTimeout(undoTimer); pop.querySelector('.zpp-lc-undo').style.display = 'none'; render();
        });
        document.body.appendChild(pop);
        pop.querySelector('#zpp-lc-pick').value = HEX.test(i.value) ? i.value : DEF_FIRST;
        var r = i.getBoundingClientRect(), w = 214, left = Math.max(8, Math.min(window.innerWidth - w - 8, r.right - w));
        pop.style.left = (left + window.pageXOffset) + 'px'; pop.style.top = (r.bottom + window.pageYOffset + 6) + 'px';
        render();
        window.setTimeout(function () {
            $(document).on('mousedown.zppLcOut touchstart.zppLcOut', function (e) { if (pop && !pop.contains(e.target) && e.target !== input()) { close(); } });
            $(document).on('keydown.zppLcOut', function (e) { if (e.key === 'Escape') { close(); } });
        }, 0);
    }
    /* capture phase: stop the native picker, open the popover instead */
    document.addEventListener('click', function (e) {
        var i = input();
        if (!i || e.target !== i) { return; }
        e.preventDefault(); e.stopImmediatePropagation();
        if (pop) { close(); } else { open(); }
    }, true);
    $(document).on('click', '#zappoppop-inspector-drawer [data-tab], .zappoppop-layer-item, .zappoppop-canvas-item', close);
    $(function () { css(); });
})(window, window.jQuery);
