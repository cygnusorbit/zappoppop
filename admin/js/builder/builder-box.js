/**
 * ZapPopPop Builder: per-side Border width + per-corner Radius (ZPP_BORDER_SIDES).
 *
 * Data lifecycle: the existing "all sides" inputs (borderWidth / borderRadius) stay; each opens a panel of four real data-prop inputs
 * (borderWidthTop/Right/Bottom/Left, borderRadiusTl/Tr/Br/Bl) so the generic Inspector binding, Save / Clear / Reset as Default (keys are
 * collected from data-prop), the AJAX whitelist and the front renderer treat them like any other number.
 * Rule shared with ZapPopPop_Box_Style (PHP): any side above 0 -> the four values are used, otherwise the all-sides value applies.
 * Stage: ZapPopPopBoxStyle.apply() overrides the shorthand border-width / border-radius written by the style renderers.
 */
(function (window, $) {
    'use strict';

    var WK = ['Top', 'Right', 'Bottom', 'Left'], RK = ['Tl', 'Tr', 'Br', 'Bl'];

    function cl(v, hi) { var n = parseInt(v, 10); if (isNaN(n) || n < 0) { return 0; } return n > hi ? hi : n; }
    function sides(el, base, keys, hi) {
        var m = cl(el[base], hi), out = [], used = false, i, v;
        for (i = 0; i < keys.length; i++) { v = cl(el[base + keys[i]], hi); out.push(v); if (v > 0) { used = true; } }
        return { v: used ? out : [m, m, m, m], used: used };
    }
    function widths(el) { return sides(el, 'borderWidth', WK, 40); }
    function radii(el) { return sides(el, 'borderRadius', RK, 200); }
    function off(el) { return el.borderEnabled === false || el.borderEnabled === 'false' || el.borderEnabled === 0; }

    /* ---- stage ---- */
    function apply($node, el) {
        var s, w, r, mx;
        if (!$node || !$node.length || !el || off(el)) { return; }
        s = $node[0].style;
        w = widths(el);
        r = radii(el);
        if (w.used) {
            s.setProperty('border-top-width', w.v[0] + 'px');
            s.setProperty('border-right-width', w.v[1] + 'px');
            s.setProperty('border-bottom-width', w.v[2] + 'px');
            s.setProperty('border-left-width', w.v[3] + 'px');
            mx = Math.max.apply(null, w.v);
            if (mx > 0 && !el.borderStyle) { s.setProperty('border-style', 'solid'); }
        }
        if (r.used) {
            s.setProperty('border-top-left-radius', r.v[0] + 'px');
            s.setProperty('border-top-right-radius', r.v[1] + 'px');
            s.setProperty('border-bottom-right-radius', r.v[2] + 'px');
            s.setProperty('border-bottom-left-radius', r.v[3] + 'px');
        }
    }
    window.ZapPopPopBoxStyle = { apply: apply, widths: widths, radii: radii };

    if (!$) { return; }

    /* ---- Inspector UI ---- */
    var G = {
        'prop-border-width': { base: 'borderWidth', keys: WK, hi: 40, get: widths, panel: 'zpp-bx-panel-w' },
        'prop-border-radius': { base: 'borderRadius', keys: RK, hi: 200, get: radii, panel: 'zpp-bx-panel-r' }
    };

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
    function sideId(mid, key) { return mid + '-' + key.toLowerCase(); }
    function rerender() {
        if (window.ZapPopPopBuilderCanvas && typeof window.ZapPopPopBuilderCanvas.renderCanvas === 'function') { window.ZapPopPopBuilderCanvas.renderCanvas(); }
    }

    function paintToggle(mid, r) {
        var g = G[mid], mixed = r.used && !(r.v[0] === r.v[1] && r.v[1] === r.v[2] && r.v[2] === r.v[3]);
        $('[data-zpp-bx-toggle="' + g.panel + '"]').toggleClass('is-mixed', mixed);
    }

    /* the model's effective four values go into the panel inputs (the generic populate leaves an unset key at the previous layer's value) */
    function hydrate() {
        var el = activeEl(), mid, g, r, i, $i;
        if (!el) { return; }
        for (mid in G) {
            if (!Object.prototype.hasOwnProperty.call(G, mid)) { continue; }
            g = G[mid];
            r = g.get(el);
            for (i = 0; i < g.keys.length; i++) {
                $i = $('#' + sideId(mid, g.keys[i]));
                if ($i.length && String($i.val()) !== String(r.v[i]) && document.activeElement !== $i[0]) { $i.val(r.v[i]); }
            }
            paintToggle(mid, r);
        }
    }

    /* capture phase: runs before the generic jQuery binding, so the model is already consistent when it re-renders */
    function onEdit(e) {
        var t = e.target, id = t && t.id, el, g, v, i, m, r, mid;
        if (!id || id.indexOf('prop-border-') !== 0) { return; }
        el = activeEl();
        if (!el) { return; }
        if (G[id]) { /* all-sides field: copy to the four sides */
            g = G[id];
            v = cl(t.value, g.hi);
            for (i = 0; i < g.keys.length; i++) { el[g.base + g.keys[i]] = v; $('#' + sideId(id, g.keys[i])).val(v); }
            paintToggle(id, g.get(el));
            return;
        }
        m = /^(prop-border-(?:width|radius))-(top|right|bottom|left|tl|tr|br|bl)$/.exec(id);
        if (!m) { return; }
        mid = m[1];
        g = G[mid];
        r = g.get(el);
        for (i = 0; i < g.keys.length; i++) { el[g.base + g.keys[i]] = r.v[i]; } /* keep the other three at their current value */
        window.setTimeout(function () {
            var a = [], k;
            for (k = 0; k < g.keys.length; k++) { a.push(cl(el[g.base + g.keys[k]], g.hi)); }
            if (a[0] === a[1] && a[1] === a[2] && a[2] === a[3] && cl(el[g.base], g.hi) !== a[0]) { /* all equal again: the all-sides field follows */
                el[g.base] = a[0];
                $('#' + mid).val(a[0]);
                rerender();
            }
            paintToggle(mid, g.get(el));
        }, 0);
    }
    document.addEventListener('input', onEdit, true);
    document.addEventListener('change', onEdit, true);

    document.addEventListener('click', function (e) {
        var b = e.target && e.target.closest ? e.target.closest('.zpp-bx-toggle') : null, p, open;
        if (!b) { return; }
        e.preventDefault();
        e.stopPropagation();
        p = document.getElementById(b.getAttribute('data-zpp-bx-toggle'));
        if (!p) { return; }
        open = p.classList.contains('is-collapsed');
        p.classList.toggle('is-collapsed', !open);
        b.setAttribute('aria-expanded', open ? 'true' : 'false');
        b.classList.toggle('is-open', open);
        if (open) { hydrate(); }
    }, true);

    var lastSig = '';
    function tick() {
        var el = activeEl(), sig, mid, g, i;
        if (!el) { return; }
        sig = cid(el.id) + '|' + el.borderWidth + '|' + el.borderRadius;
        for (mid in G) {
            if (!Object.prototype.hasOwnProperty.call(G, mid)) { continue; }
            g = G[mid];
            for (i = 0; i < g.keys.length; i++) { sig += '|' + el[g.base + g.keys[i]]; }
        }
        if (sig !== lastSig) { lastSig = sig; hydrate(); }
    }
    $(function () { window.setInterval(tick, 150); });
})(window, window.jQuery);
