/**
 * ZapPopPop Builder: Typography block (ZPP_TYPOGRAPHY).
 *
 * Data lifecycle: every row keeps one real data-prop control (visually hidden select / number / colour), so the generic Inspector binding,
 * pre-save flush, Save / Clear / Reset as Default and the model need nothing extra. The segmented buttons only drive that control.
 * New model keys: lineDecoration, textDirection, fontStyle, textStrokeWidth, textStrokeColor (textAlign / textTransform already existed).
 * Stage rendering: ZapPopPopTextStyle.apply() sets data-zpp-* attributes + CSS variables on the layer node; admin CSS turns them into the real rules.
 */
(function (window, $) {
    'use strict';

    var ENUM = {
        lineDecoration: { none: 1, underline: 1, overline: 1, 'line-through': 1 },
        textDirection: { ltr: 1, rtl: 1 },
        fontStyle: { normal: 1, italic: 1 }
    };
    var DEFAULTS = { textAlign: 'left', lineDecoration: 'none', textDirection: 'ltr', fontStyle: 'normal' };
    var PROXIES = { textAlign: '#prop-text-align', lineDecoration: '#prop-line-deco', textDirection: '#prop-text-dir', fontStyle: '#prop-font-style' };
    var TEXT_TYPES = /^label$/i; /* Header layers only: HTML layers keep the standalone Text Alignment section, Button / Submit have their own */
    var LS = 'zppTyMore';

    function num(v, lo, hi, d) { var n = parseFloat(v); if (isNaN(n)) { return d; } return Math.max(lo, Math.min(hi, n)); }

    /* ---- stage: used by the canvas renderers (no jQuery needed on the layer) ---- */
    function apply($node, el) {
        var n, w, c;
        if (!$node || !$node.length || !el) { return; }
        n = $node[0];
        $node.attr('data-zpp-fs', el.fontStyle === 'italic' ? 'italic' : null);
        $node.attr('data-zpp-ld', ENUM.lineDecoration[el.lineDecoration] && el.lineDecoration !== 'none' ? el.lineDecoration : null);
        $node.attr('data-zpp-dir', el.textDirection === 'rtl' ? 'rtl' : null);
        w = num(el.textStrokeWidth, 0, 20, 0);
        if (w > 0) {
            c = /^#[0-9a-fA-F]{3,8}$/.test(el.textStrokeColor || '') ? el.textStrokeColor : '#000000';
            n.style.setProperty('--zpp-sw', w + 'px'); n.style.setProperty('--zpp-sc', c); $node.attr('data-zpp-sw', '1');
        } else { n.style.removeProperty('--zpp-sw'); n.style.removeProperty('--zpp-sc'); $node.removeAttr('data-zpp-sw'); }
    }
    window.ZapPopPopTextStyle = { apply: apply };

    if (!$) { return; }

    /* ---- Inspector UI ---- */
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

    function paint() {
        $('.zpp-ty-seg').each(function () {
            var $p = $($(this).attr('data-zpp-seg')), v = $p.length ? ($p.val() || String($p.find('option').first().val() || '')) : '';
            $(this).find('.zpp-ty-b').each(function () {
                var on = this.getAttribute('data-v') === v;
                $(this).toggleClass('is-on', on).attr('aria-pressed', on ? 'true' : 'false');
            });
        });
    }

    /* put the model's value into the hidden controls (the generic populate skips hidden rows, and an unset key must read as its default) */
    function hydrate() {
        var el = activeEl(), key, $p, v;
        if (!el) { return; }
        $('#zpp-ty-align-row').prop('hidden', !TEXT_TYPES.test(String(el.type || '')));
        for (key in PROXIES) {
            if (!Object.prototype.hasOwnProperty.call(PROXIES, key)) { continue; }
            $p = $(PROXIES[key]);
            v = el[key];
            if (!v || (ENUM[key] && !ENUM[key][v]) || (key === 'textAlign' && !/^(left|center|right|justify)$/.test(v))) { v = DEFAULTS[key]; }
            if ($p.length && $p.val() !== v) { $p.val(v); }
        }
        paint();
    }

    var lastKey = '';
    function tick() {
        var el = activeEl(), k = el ? (cid(el.id) + '|' + String(el.type || '')) : '';
        if (k !== lastKey) { lastKey = k; window.setTimeout(hydrate, 40); window.setTimeout(hydrate, 220); return; }
        paint();
    }

    document.addEventListener('click', function (e) {
        var b = e.target && e.target.closest ? e.target.closest('.zpp-ty-b') : null, seg, $p, v;
        if (!b) { return; }
        seg = b.closest('.zpp-ty-seg');
        $p = seg ? $(seg.getAttribute('data-zpp-seg')) : $();
        if (!$p.length) { return; }
        e.preventDefault();
        e.stopPropagation();
        v = b.getAttribute('data-v');
        if ($p.val() !== v) { $p.val(v).trigger('input').trigger('change'); }
        paint();
    }, true);

    /* per-field Reset (number rows): back to the field's default through the normal input / change path */
    document.addEventListener('click', function (e) {
        var b = e.target && e.target.closest ? e.target.closest('.zpp-ty-reset') : null, inp;
        if (!b) { return; }
        e.preventDefault();
        e.stopPropagation();
        inp = b.parentNode ? b.parentNode.querySelector('input') : null;
        if (inp && inp.value !== b.getAttribute('data-default')) { inp.value = b.getAttribute('data-default'); $(inp).trigger('input').trigger('change'); }
    }, true);

    /* Show less / Show more: collapses with visibility (not display) so the hidden controls stay in the Inspector's populate / flush */
    function setMore(open, save) {
        var $m = $('#zpp-ty-more'), $b = $('#zpp-ty-more-btn');
        $m.toggleClass('is-collapsed', !open);
        $b.attr('aria-expanded', open ? 'true' : 'false').find('.zpp-ty-more-text').text(open ? ($b.attr('data-less') || 'Show less') : ($b.attr('data-more') || 'Show more'));
        if (save) { try { window.localStorage.setItem(LS, open ? '1' : '0'); } catch (err) { /* per page view only */ } }
    }
    document.addEventListener('click', function (e) {
        var b = e.target && e.target.closest ? e.target.closest('#zpp-ty-more-btn') : null;
        if (!b) { return; }
        e.preventDefault();
        e.stopPropagation();
        setMore($('#zpp-ty-more').hasClass('is-collapsed'), true);
    }, true);

    $(function () {
        var $b = $('#zpp-ty-more-btn'), open = true;
        try { open = window.localStorage.getItem(LS) !== '0'; } catch (err) { open = true; }
        window.setTimeout(function () { setMore(open, false); hydrate(); }, 300);
        window.setInterval(tick, 150);
    });
})(window, window.jQuery);
