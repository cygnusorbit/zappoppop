/**
 * ZapPopPop Builder: Spacing block (Margin + Padding) with a Link button per group (ZPP_SPACING).
 *
 * Data lifecycle: every field is a normal data-prop control (marginTop/Right/Bottom/Left, paddingTop/Right/Bottom/Left), so the generic Inspector
 * binding, Save / Clear / Reset as Default, the JS model and the renderers need nothing extra. Margin is shown for Header layers only
 * (it is the space around the text block inside the layer box, so drag / push / X Y Width Height are untouched).
 * The Link button is an editing aid only: while it is on, changing one field writes the same value to the other three. Not saved.
 */
(function (window, $) {
    'use strict';
    if (!$) { return; }

    var MARGIN_TYPES = /^(label|title|paragraph)$/i;
    var MAR = { top: '#prop-mar-top', right: '#prop-mar-right', bottom: '#prop-mar-bottom', left: '#prop-mar-left' };
    var MAR_KEY = { top: 'marginTop', right: 'marginRight', bottom: 'marginBottom', left: 'marginLeft' };
    var PAD = { top: '#prop-pad-top', right: '#prop-pad-right', bottom: '#prop-pad-bottom', left: '#prop-pad-left' };
    var PAD_KEY = { top: 'paddingTop', right: 'paddingRight', bottom: 'paddingBottom', left: 'paddingLeft' };
    var LINKED = {};   /* 'layerId|group' -> true / false, this session only */
    var syncing = false, lastKey = '';

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
    function num(v) { var n = parseInt(v, 10); return isNaN(n) ? 0 : n; }
    function sel(group) { return group === 'margin' ? MAR : PAD; }
    function key(el, group) { return (el ? cid(el.id) : '') + '|' + group; }
    function values(group) {
        var m = sel(group);
        return [num($(m.top).val()), num($(m.right).val()), num($(m.bottom).val()), num($(m.left).val())];
    }
    function allEqual(v) { return v[0] > 0 && v[0] === v[1] && v[1] === v[2] && v[2] === v[3]; }
    function setButton(group, on) {
        $('.zpp-sp-link[data-zpp-sp-link="' + group + '"]').toggleClass('is-linked', !!on).attr('aria-pressed', on ? 'true' : 'false');
    }
    function isLinked(group) {
        var k = key(activeEl(), group);
        return Object.prototype.hasOwnProperty.call(LINKED, k) ? LINKED[k] : false;
    }

    /* fill the Margin fields from the layer (Padding is filled by the existing Inspector code) */
    function fillMargin(el, force) {
        var side, $i;
        for (side in MAR) {
            if (!Object.prototype.hasOwnProperty.call(MAR, side)) { continue; }
            $i = $(MAR[side]);
            if (!$i.length || (!force && $i[0] === document.activeElement)) { continue; }
            var v = String(el && el[MAR_KEY[side]] !== undefined && el[MAR_KEY[side]] !== '' ? num(el[MAR_KEY[side]]) : 0);
            if ($i.val() !== v) { $i.val(v); }
        }
    }
    function hydrate() {
        var el = activeEl(), type = el ? String(el.type || '') : '', showMargin = !!el && MARGIN_TYPES.test(type);
        $('.zpp-sp-group[data-zpp-sp="margin"]').prop('hidden', !showMargin);
        if (el && showMargin) { fillMargin(el, true); }
        ['margin', 'padding'].forEach(function (g) {
            var k = key(el, g);
            if (el && !Object.prototype.hasOwnProperty.call(LINKED, k)) { LINKED[k] = allEqual(values(g)); } /* a layer whose four values already match opens linked */
            setButton(g, el ? LINKED[k] : false);
        });
    }

    /* ZPP_SP_LINK_CAPTURE: capture-phase listener so Inspector click handlers that stop propagation cannot swallow the toggle */
    function toggleLink(btn) {
        var g = btn.getAttribute('data-zpp-sp-link'), el = activeEl(), k = key(el, g);
        if (!el) { return; }
        LINKED[k] = !LINKED[k];
        setButton(g, LINKED[k]);
        if (LINKED[k]) { /* turning it on: match the other three to the first non-zero value so the group is really linked */
            var m = sel(g), v = values(g), base = v[0] || v[1] || v[2] || v[3];
            if (base) { $(m.top).val(base).trigger('input').trigger('change'); }
        }
    }
    document.addEventListener('click', function (e) {
        var b = e.target && e.target.closest ? e.target.closest('.zpp-sp-link') : null;
        if (!b) { return; }
        e.preventDefault();
        e.stopPropagation();
        toggleLink(b);
    }, true);

    /* ZPP_SP_RESET: sets the four fields of one group back to 0 through the normal data-prop change path (model, save, renderers follow) */
    function resetGroup(g) {
        var m = sel(g);
        syncing = true;
        try {
            ['top', 'right', 'bottom', 'left'].forEach(function (side) {
                var $i = $(m[side]);
                if ($i.length && $i.val() !== '0') { $i.val('0').trigger('input').trigger('change'); }
            });
        } finally { syncing = false; }
    }
    document.addEventListener('click', function (e) {
        var b = e.target && e.target.closest ? e.target.closest('.zpp-sp-reset') : null;
        if (!b) { return; }
        e.preventDefault();
        e.stopPropagation();
        resetGroup(b.getAttribute('data-zpp-sp-reset'));
    }, true);

    /* ZPP_SP_STEP: custom up / down arrows (the native spinner is hidden for a uniform look). Shift = 10. Goes through the normal input / change path, so Link, model and save follow. */
    document.addEventListener('click', function (e) {
        var b = e.target && e.target.closest ? e.target.closest('.zpp-sp-up, .zpp-sp-down') : null, box, inp, step, lo, hi, dec, v, d;
        if (!b) { return; }
        e.preventDefault();
        e.stopPropagation();
        box = b.closest('.zpp-sp-box, .zpp-ty-ctl');
        inp = box ? box.querySelector('input') : null;
        if (!inp) { return; }
        step = parseFloat(inp.getAttribute('step')) || 1;
        lo = inp.getAttribute('min') !== null && inp.getAttribute('min') !== '' ? parseFloat(inp.getAttribute('min')) : 0;
        hi = inp.getAttribute('max') !== null && inp.getAttribute('max') !== '' ? parseFloat(inp.getAttribute('max')) : 200;
        dec = (String(step).split('.')[1] || '').length;
        d = (b.classList.contains('zpp-sp-up') ? 1 : -1) * (e.shiftKey ? 10 : 1) * step;
        v = parseFloat(inp.value); if (isNaN(v)) { v = 0; }
        v = Math.max(lo, Math.min(hi, parseFloat((v + d).toFixed(dec))));
        if (String(v) === inp.value) { return; }
        inp.value = String(v);
        $(inp).trigger('input').trigger('change');
    }, true);

    function paintLinks() {
        var el = activeEl();
        ['margin', 'padding'].forEach(function (g) {
            var on = !!el && !!LINKED[key(el, g)], $b = $('.zpp-sp-link[data-zpp-sp-link="' + g + '"]');
            if ($b.length && $b.hasClass('is-linked') !== on) { setButton(g, on); } /* the Inspector may rebuild the panes, keep the button in step with the state */
        });
    }

    function tick() {
        var el = activeEl(), k = el ? (cid(el.id) + '|' + String(el.type || '')) : '';
        paintLinks();
        if (k !== lastKey) { lastKey = k; window.setTimeout(hydrate, 40); window.setTimeout(hydrate, 220); return; }
        if (el && MARGIN_TYPES.test(String(el.type || ''))) { fillMargin(el, false); } /* undo / redo / reset keep the fields current */
    }

    /* ZPP_SP_SYNC_CAPTURE: while a group is linked, any edit to one field writes the same value to the other three (capture phase, cannot be blocked) */
    function syncGroup(t, type) {
        var $grp, g, v, self = t;
        if (syncing || !t || !t.classList || !t.classList.contains('zpp-sp-in')) { return; }
        $grp = $(t).closest('.zpp-sp-group');
        g = $grp.attr('data-zpp-sp');
        if (!g || !isLinked(g)) { return; }
        v = t.value;
        syncing = true;
        try {
            $grp.find('.zpp-sp-in').each(function () {
                if (this === self || this.value === v) { return; }
                this.value = v;
                $(this).trigger('input').trigger('change');
            });
        } finally { syncing = false; }
    }
    document.addEventListener('input', function (e) { syncGroup(e.target, 'input'); }, true);
    document.addEventListener('change', function (e) { syncGroup(e.target, 'change'); }, true);

    $(function () { window.setInterval(tick, 150); window.setTimeout(hydrate, 300); });
})(window, window.jQuery);
