/* ZPP_LINK_GROUP: Link URL / new tab / nofollow in the Animation and Shape blocks, kept in sync with Logic > Action & Click Routing. */
(function (window, $) {
    'use strict';
    if (!$) { return; }

    /* ZPP_LK_SCOPE: which layer gets which Link group.
       Shape  -> only the one inside its own Shape block (never in Animation).
       Icon / Label / HTML -> the one in Animation & Behavior (they have no block of their own for it).
       Form fields (Text, Email ...), Button, Image and the rest -> none (a field is not a link; Button / Image have their own URL setting). */
    var SCOPE = { shape: ['shape'], anim: ['icon', 'label', 'html'] };
    var TYPES = ['shape', 'icon', 'label', 'html'];

    function core() { return window.ZapPopPopBuilderCore || null; }
    function cid(v) { return String(v || '').replace(/^zappoppop-el-/, '').replace(/^el-/, '').trim(); }
    function T(v) { return v === true || v === 'true' || v === 1 || v === '1' || v === 'on'; }
    function activeEl() {
        var c = core();
        if (!c || !c.state) { return null; }
        var id = cid(c.state.activeId);
        if (!id) { return null; }
        var list = (c.state.canvases && c.state.canvases[c.state.currentCanvas || 1]) || [];
        for (var i = 0; i < list.length; i++) { if (list[i] && cid(list[i].id) === id) { return list[i]; } }
        return null;
    }
    function history(msg) {
        var c = core();
        if (c && typeof c.pushHistory === 'function') { c.pushHistory(msg); }
    }
    function actionOf(el) {
        var a = String(el.buttonAction || 'link');
        return a === 'url' ? 'link' : a;
    }

    function hydrate() {
        var el = activeEl();
        var type = el ? String(el.type || '').toLowerCase() : '';
        var supported = !!el && TYPES.indexOf(type) !== -1;
        var isLink = supported && actionOf(el) === 'link';
        $('.zpp-lk').each(function () {
            var $g = $(this), where = $g.attr('data-zpp-lk');
            var show = supported && !!SCOPE[where] && SCOPE[where].indexOf(type) !== -1;
            $g.css('display', show && isLink ? '' : 'none');
            $('[data-zpp-lk-note="' + where + '"]').css('display', show && !isLink ? '' : 'none');
            if (show && isLink) {
                $g.find('.zpp-lk-url').val(el.linkUrl || el.url || '');
                $g.find('.zpp-lk-blank').prop('checked', T(el.linkBlank !== undefined ? el.linkBlank : el.targetBlank));
                $g.find('.zpp-lk-nofollow').prop('checked', T(el.nofollow));
            }
        });
    }
    function sched() { [0, 60, 200, 350, 700, 1200].forEach(function (ms) { setTimeout(hydrate, ms); }); }

    function mirror(el) {
        $('.zpp-lk .zpp-lk-url, #prop-logic-link-url').val(el.linkUrl || '');
        $('.zpp-lk .zpp-lk-blank, #prop-route-blank').prop('checked', T(el.linkBlank));
        $('.zpp-lk .zpp-lk-nofollow, #prop-route-nofollow').prop('checked', T(el.nofollow));
    }

    $(document).off('input.zppLk change.zppLk', '.zpp-lk-url').on('input.zppLk change.zppLk', '.zpp-lk-url', function (e) {
        var el = activeEl(); if (!el) { return; }
        el.linkUrl = $.trim($(this).val()); el.url = el.linkUrl;
        if (el.linkUrl && !el.buttonAction) { el.buttonAction = 'link'; }
        $('.zpp-lk .zpp-lk-url, #prop-logic-link-url').not(this).val(el.linkUrl);
        if (e.type === 'change') { history('Change Link URL'); }
    });
    $(document).off('change.zppLk', '.zpp-lk-blank').on('change.zppLk', '.zpp-lk-blank', function () {
        var el = activeEl(); if (!el) { return; }
        el.linkBlank = $(this).is(':checked'); el.targetBlank = el.linkBlank;
        $('.zpp-lk .zpp-lk-blank, #prop-route-blank').not(this).prop('checked', el.linkBlank);
        history('Change Link Target');
    });
    $(document).off('change.zppLk', '.zpp-lk-nofollow').on('change.zppLk', '.zpp-lk-nofollow', function () {
        var el = activeEl(); if (!el) { return; }
        el.nofollow = $(this).is(':checked');
        $('.zpp-lk .zpp-lk-nofollow, #prop-route-nofollow').not(this).prop('checked', el.nofollow);
        history('Change Link Nofollow');
    });
    /* Routing-side edits keep the two groups in step */
    $(document).off('input.zppLkR change.zppLkR', '#prop-logic-link-url, #prop-route-blank, #prop-route-nofollow, #prop-logic-click-action')
        .on('input.zppLkR change.zppLkR', '#prop-logic-link-url, #prop-route-blank, #prop-route-nofollow, #prop-logic-click-action', function () {
            window.setTimeout(function () { var el = activeEl(); if (el) { mirror(el); hydrate(); } }, 30);
        });

    $(document).off('click.zppLkHydrate', '.zappoppop-canvas-item, .zappoppop-layer-item, .zappoppop-screen-tab, .zappoppop-canvas-tab, #zappoppop-inspector-drawer [data-tab], .zappoppop-ribbon-tool')
        .on('click.zppLkHydrate', '.zappoppop-canvas-item, .zappoppop-layer-item, .zappoppop-screen-tab, .zappoppop-canvas-tab, #zappoppop-inspector-drawer [data-tab], .zappoppop-ribbon-tool', sched);
    $(function () {
        sched();
        /* follow the selection itself: picking a layer from the Layers Panel (or by undo / keyboard) does not always fire the click handler above */
        var last = '';
        window.setInterval(function () {
            var el = activeEl(), key = el ? (cid(el.id) + '|' + String(el.type || '') + '|' + actionOf(el)) : '';
            var type = el ? String(el.type || '').toLowerCase() : '', bad = false;
            $('.zpp-lk').each(function () {
                var w = $(this).attr('data-zpp-lk'), want = !!el && !!SCOPE[w] && SCOPE[w].indexOf(type) !== -1 && actionOf(el) === 'link';
                if ((this.style.display !== 'none') !== want) { bad = true; }
            });
            if (key !== last || bad) { last = key; hydrate(); }
        }, 150);
    });
})(window, window.jQuery);
