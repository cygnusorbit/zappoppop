/* ZAPPOPPOP_CLICK_ROUTING_UI: Logic > Action & Click Routing (all element types). Presentation + model only. */
(function (window, $) {
    'use strict';
    if (!$) { return; }

    var BTN = ['button', 'link', 'link_btn', 'step_btn', 'step'];
    var VAL = {
        mailto: { label: 'Email address', ph: 'name@example.com' },
        tel:    { label: 'Phone number', ph: '+1 555 123 4567' },
        copy:   { label: 'Text to copy', ph: 'e.g. SAVE20' },
        anchor: { label: 'Anchor ID on the page', ph: 'pricing' }
    };

    function core() { return window.ZapPopPopBuilderCore || null; }
    function cid(v) { return String(v || '').replace(/^zappoppop-el-/, '').replace(/^el-/, '').trim(); }
    function T(v) { return v === true || v === 'true' || v === 1 || v === '1' || v === 'on'; }
    function activeEl() {
        var c = core();
        if (!c || !c.state) { return null; }
        var id = cid(c.state.activeId);
        if (!id) { return null; }
        var list = (c.state.canvases && c.state.canvases[c.state.currentCanvas || 1]) || [];
        for (var i = 0; i < list.length; i++) {
            if (list[i] && cid(list[i].id) === id) { return list[i]; }
        }
        return null;
    }
    function isBtn(el) { return !!el && BTN.indexOf(String(el.type || '').toLowerCase()) !== -1; }
    function norm(a) {
        a = String(a || 'link');
        if (a === 'url') { return 'link'; }
        if (a === 'goto_canvas') { return 'step'; }
        return a;
    }
    function history(msg) {
        var c = core();
        if (c && typeof c.pushHistory === 'function') { c.pushHistory(msg); }
    }

    function canvasList() {
        var c = core(), st = (c && c.state) || {}, seen = {}, list = [];
        [st.canvases, st.canvasMeta].forEach(function (o) {
            if (o && typeof o === 'object') {
                Object.keys(o).forEach(function (k) {
                    var n = parseInt(k, 10);
                    if (n > 0 && !seen[n]) { seen[n] = 1; list.push(n); }
                });
            }
        });
        if (!list.length) { list = [1]; }
        list.sort(function (a, b) { return a - b; });
        return { list: list, current: parseInt(st.currentCanvas, 10) || 1, meta: st.canvasMeta || {} };
    }

    function fillCanvas(want) {
        var $s = $('#prop-logic-goto-canvas');
        if (!$s.length || !$s.is('select')) { return; }
        var i = canvasList(), list = i.list.slice();
        if (want > 0 && list.indexOf(want) === -1) { list.push(want); list.sort(function (a, b) { return a - b; }); }
        var html = '';
        list.forEach(function (n) {
            var m = i.meta[n], nm = m && (m.name || m.title || m.label);
            html += '<option value="' + n + '">Canvas ' + n + (nm ? ' – ' + $('<i>').text(nm).html() : '') + (n === i.current ? ' (this canvas)' : '') + '</option>';
        });
        $s.html(html);
        if (!(want > 0)) {
            want = list.filter(function (n) { return n > i.current; })[0] || list.filter(function (n) { return n !== i.current; })[0] || list[0];
        }
        $s.val(String(want));
    }

    function rows(a) {
        $('#prop-logic-link-url-row').css('display', a === 'link' ? '' : 'none');
        $('#prop-route-link-opts').css('display', a === 'link' ? 'flex' : 'none');
        $('#prop-logic-goto-canvas-row').css('display', a === 'step' ? '' : 'none');
        var v = VAL[a];
        $('#prop-route-value-row').css('display', v ? '' : 'none');
        if (v) {
            $('#prop-route-value-label').text(v.label);
            $('#prop-route-value').attr('placeholder', v.ph);
        }
    }

    function hydrate() {
        var el = activeEl();
        if (!el || !$('#prop-logic-click-action').length) { return; }
        var a = norm(el.buttonAction);
        var btn = isBtn(el);
        $('#prop-logic-click-action option[value="copy"], #prop-logic-click-action option[value="anchor"]').prop('disabled', btn);
        if (btn && (a === 'copy' || a === 'anchor')) { a = 'link'; }
        $('#prop-logic-click-action').val(a);
        $('#prop-logic-link-url').val(el.linkUrl || el.url || '');
        $('#prop-route-value').val(el.routeValue || '');
        $('#prop-route-label').val(el.routeLabel || '');
        $('#prop-route-blank').prop('checked', T(el.linkBlank !== undefined ? el.linkBlank : el.targetBlank));
        $('#prop-route-nofollow').prop('checked', T(el.nofollow));
        fillCanvas(parseInt(el.goto_canvas || el.goto_screen || el.target_canvas, 10) || 0);
        rows(a);
    }
    function sched() { [0, 60, 200, 350, 700, 1200].forEach(function (ms) { setTimeout(hydrate, ms); }); }

    $(document).off('change.zppRoute', '#prop-logic-click-action').on('change.zppRoute', '#prop-logic-click-action', function () {
        var el = activeEl(); if (!el) { return; }
        var a = norm($(this).val());
        el.buttonAction = a;
        if (a === 'step') {
            fillCanvas(parseInt(el.goto_canvas || el.goto_screen, 10) || 0);
            var n = parseInt($('#prop-logic-goto-canvas').val(), 10) || 2;
            el.goto_canvas = n; el.goto_screen = n; el.target_canvas = n;
        }
        rows(a);
        history('Change Click Action');
    });
    $(document).off('input.zppRoute change.zppRoute', '#prop-logic-link-url').on('input.zppRoute change.zppRoute', '#prop-logic-link-url', function (e) {
        var el = activeEl(); if (!el) { return; }
        el.linkUrl = $.trim($(this).val()); el.url = el.linkUrl;
        if (e.type === 'change') { history('Change Link URL'); }
    });
    $(document).off('input.zppRoute change.zppRoute', '#prop-route-value').on('input.zppRoute change.zppRoute', '#prop-route-value', function (e) {
        var el = activeEl(); if (!el) { return; }
        el.routeValue = $.trim($(this).val());
        if (e.type === 'change') { history('Change Click Value'); }
    });
    $(document).off('input.zppRoute change.zppRoute', '#prop-route-label').on('input.zppRoute change.zppRoute', '#prop-route-label', function (e) {
        var el = activeEl(); if (!el) { return; }
        el.routeLabel = $.trim($(this).val());
        if (e.type === 'change') { history('Change Click Label'); }
    });
    $(document).off('change.zppRoute', '#prop-route-blank').on('change.zppRoute', '#prop-route-blank', function () {
        var el = activeEl(); if (!el) { return; }
        el.linkBlank = $(this).is(':checked'); el.targetBlank = el.linkBlank;
        history('Change Link Target');
    });
    $(document).off('change.zppRoute', '#prop-route-nofollow').on('change.zppRoute', '#prop-route-nofollow', function () {
        var el = activeEl(); if (!el) { return; }
        el.nofollow = $(this).is(':checked');
        history('Change Link Nofollow');
    });
    $(document).off('change.zppRoute', '#prop-logic-goto-canvas').on('change.zppRoute', '#prop-logic-goto-canvas', function () {
        var el = activeEl(); if (!el) { return; }
        var n = parseInt($(this).val(), 10) || 2;
        el.goto_canvas = n; el.goto_screen = n; el.target_canvas = n;
        history('Change Target Canvas');
    });

    $(document).off('click.zppRouteHydrate', '.zappoppop-canvas-item, .zappoppop-layer-item, .zappoppop-screen-tab, .zappoppop-canvas-tab, #zappoppop-inspector-drawer [data-tab], .zappoppop-ribbon-tool')
        .on('click.zppRouteHydrate', '.zappoppop-canvas-item, .zappoppop-layer-item, .zappoppop-screen-tab, .zappoppop-canvas-tab, #zappoppop-inspector-drawer [data-tab], .zappoppop-ribbon-tool', sched);
    $(function () { sched(); });
})(window, window.jQuery);
