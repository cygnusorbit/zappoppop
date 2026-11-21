/* ZPP_FIELD_STYLE: "Field Background" and "Field Border" colour of form fields (Element Settings, under Label Text).
   Model keys: fieldBgColor, fieldBorderColor (undefined = built-in field look, nothing is overridden).
   Lifecycle: Inspector DOM (#prop-fs-bg / #prop-fs-bd, markup lives in #zpp-fl-box) -> model -> history -> AJAX whitelist -> front renderer
   (zpp_add_field_style) and the builder stage (this file). Save / Clear / Reset as Default: LABEL_KEYS in builder-content-tab.js. */
(function (window, $) {
    'use strict';
    if (!$) { return; }
    var TYPES = ['text', 'textfield', 'email', 'number', 'phone', 'tel', 'textarea', 'select', 'dropdown', 'multiselect', 'date', 'time', 'password', 'upload', 'file'];
    var PH_TYPES = ['text', 'textfield', 'email', 'number', 'phone', 'tel', 'textarea', 'select', 'dropdown', 'password']; /* ZPP_FS_PH: types that show placeholder text */
    var DEF_BG = '#ffffff', DEF_BD = '#cbd5e1', DEF_PH = '#64748b', HEX = /^#[0-9a-f]{3,8}$/i;

    function core() { return window.ZapPopPopBuilderCore || null; }
    function reg() { return window.ZapPopPopElementsRegistry || {}; }
    function cid(v) { return String(v || '').replace(/^zappoppop-el-/, '').replace(/^el-/, '').trim(); }
    function activeEl() { var R = reg(); return R.resolveElement ? R.resolveElement() : null; }
    function ok(el) { return !!el && TYPES.indexOf(String(el.type || '').toLowerCase()) !== -1; }
    function okPh(el) { return !!el && PH_TYPES.indexOf(String(el.type || '').toLowerCase()) !== -1; }
    function norm(v, d) {
        v = String(v || '');
        if (!HEX.test(v)) { return d; }
        if (v.length === 4) { v = '#' + v[1] + v[1] + v[2] + v[2] + v[3] + v[3]; }
        return (v.length === 7 || v.length === 9) ? v.toLowerCase() : d;
    }
    function allEls() {
        var c = core(), list = (c && c.state && c.state.canvases) ? c.state.canvases[c.state.currentCanvas || 1] : null;
        return $.isArray(list) ? list : [];
    }
    function setDisplay(n, v) { if (n && n.style.display !== v) { n.style.display = v; } }

    /* ---- inspector: show the two rows only for field-like types, mirror the model into the swatches ---- */
    function hydrate() {
        var el = activeEl(), rows = [document.getElementById('zpp-fs-bg-row'), document.getElementById('zpp-fs-bd-row')], phRow = document.getElementById('zpp-fs-ph-row');
        if (!rows[0] || !rows[1]) { return; }
        setDisplay(phRow, okPh(el) ? 'flex' : 'none');
        var phIn = document.getElementById('prop-fs-ph');
        if (okPh(el) && phIn) { var vp = norm(el.fieldPlaceholderColor, DEF_PH); if (String(phIn.value).toLowerCase() !== vp) { phIn.value = vp; } }
        var on = ok(el);
        setDisplay(rows[0], on ? 'flex' : 'none');
        setDisplay(rows[1], on ? 'flex' : 'none');
        if (!on) { return; }
        var bg = document.getElementById('prop-fs-bg'), bd = document.getElementById('prop-fs-bd');
        var vb = norm(el.fieldBgColor, DEF_BG), vd = norm(el.fieldBorderColor, DEF_BD);
        if (bg && String(bg.value).toLowerCase() !== vb) { bg.value = vb; }
        if (bd && String(bd.value).toLowerCase() !== vd) { bd.value = vd; }
    }

    /* ---- builder stage (Edit Mode): the live preview is the real front end, PHP draws that one ---- */
    function innerBox(node) {
        var kids = node.children, i;
        for (i = 0; i < kids.length; i++) {
            var k = kids[i];
            if (k.tagName === 'DIV' && !k.classList.contains('ui-resizable-handle') && !k.classList.contains('zappoppop-resize-handle')) { return k; }
        }
        return null;
    }
    function paintOne(el) {
        var node = document.getElementById('el-' + cid(el.id)) || $('.zappoppop-canvas-item[data-id="' + cid(el.id) + '"]')[0];
        if (!node) { return; }
        var box = innerBox(node);
        if (!box) { return; }
        var bg = ok(el) ? norm(el.fieldBgColor, '') : '', bd = ok(el) ? norm(el.fieldBorderColor, '') : '';
        /* ZPP_FS_OUTER: the layer's own Style-tab background sits behind the field box and tints a translucent Background; while a field Background is set it is the only fill */
        if (bg) { node.style.setProperty('background-color', 'transparent', 'important'); node.setAttribute('data-zpp-fso', '1'); }
        else if (node.getAttribute('data-zpp-fso')) { node.style.removeProperty('background-color'); node.removeAttribute('data-zpp-fso'); if (el.bgColor && !el.transparentFill) { node.style.backgroundColor = el.bgColor; } }
        if (bg) { if (box.style.getPropertyValue('background-color') !== bg || box.style.getPropertyPriority('background-color') !== 'important') { box.style.setProperty('background-color', bg, 'important'); } box.setAttribute('data-zpp-fsb', '1'); }
        else if (box.getAttribute('data-zpp-fsb')) { box.style.removeProperty('background-color'); box.removeAttribute('data-zpp-fsb'); }
        /* ZPP_FS_WRAP: with a Style-tab border the wrapper owns the visible border (inner box has 0 width), so the field Border colour must drive it too */
        if (bd) { if (!node.hasAttribute('data-zpp-fsw')) { node.setAttribute('data-zpp-fsw', node.style.getPropertyValue('border-color') || ''); } if (node.style.getPropertyValue('border-color') !== bd) { node.style.setProperty('border-color', bd, 'important'); } }
        else if (node.hasAttribute('data-zpp-fsw')) { node.style.removeProperty('border-color'); if (node.getAttribute('data-zpp-fsw')) { node.style.borderColor = node.getAttribute('data-zpp-fsw'); } node.removeAttribute('data-zpp-fsw'); }
        if (bd) { if (box.style.getPropertyValue('border-color') !== bd || box.style.getPropertyPriority('border-color') !== 'important') { box.style.setProperty('border-color', bd, 'important'); } box.setAttribute('data-zpp-fsd', '1'); }
        else if (box.getAttribute('data-zpp-fsd')) { box.style.removeProperty('border-color'); box.removeAttribute('data-zpp-fsd'); }
        /* ZPP_FS_PH: the grey placeholder span(s) of the fake field */
        var ph = okPh(el) ? norm(el.fieldPlaceholderColor, '') : '';
        var sp = box.querySelectorAll('span:not([aria-hidden])'), i, s;
        for (i = 0; i < sp.length; i++) {
            s = sp[i];
            if (s.hasAttribute('data-zpp-fsp')) {
                if (ph) { if (s.style.getPropertyValue('color') !== ph) { s.style.setProperty('color', ph, 'important'); } }
                else { s.style.removeProperty('color'); s.style.color = s.getAttribute('data-zpp-fsp'); s.removeAttribute('data-zpp-fsp'); }
            } else if (ph && /#64748b/i.test(s.getAttribute('style') || '')) {
                s.setAttribute('data-zpp-fsp', s.style.color || '#64748b'); s.style.setProperty('color', ph, 'important');
            }
        }
    }
    function paintStage() { allEls().forEach(paintOne); }
    function refresh() { hydrate(); paintStage(); }
    function schedule() { [50, 300, 900].forEach(function (ms) { window.setTimeout(refresh, ms); }); }

    function commit(key, v) {
        var el = activeEl();
        if (!ok(el)) { return; }
        el[key] = v;
        var c = core();
        if (c && typeof c.pushHistory === 'function') { c.pushHistory(); }
        paintStage();
    }
    $(document).on('input.zppFs change.zppFs', '#prop-fs-bg', function () { commit('fieldBgColor', this.value); });
    $(document).on('input.zppFs change.zppFs', '#prop-fs-bd', function () { commit('fieldBorderColor', this.value); });
    $(document).on('input.zppFs change.zppFs', '#prop-fs-ph', function () { var el = activeEl(); if (okPh(el)) { el.fieldPlaceholderColor = this.value; var c = core(); if (c && typeof c.pushHistory === 'function') { c.pushHistory(); } paintStage(); } }); /* ZPP_FS_PH */
    $(document).on('click.zppFs', '.zappoppop-canvas-item, .zappoppop-layer-item, #zappoppop-inspector-drawer [data-tab]', schedule);

    $(function () {
        refresh();
        var stage = document.querySelector('.zappoppop-canvas, #zappoppop-canvas, #zappoppop-canvas-stage') || document.body;
        if (window.MutationObserver) {
            /* the stage re-draws a field's inner box on every change; paint again before the browser does (attribute changes are not observed, so no loop) */
            new MutationObserver(function () { paintStage(); }).observe(stage, { childList: true, subtree: true });
        }
        /* selection changes, Reset / Clear as Default and undo change the model without a click on the controls: keep both sides in step */
        window.setInterval(refresh, 400);
    });
})(window, window.jQuery);
