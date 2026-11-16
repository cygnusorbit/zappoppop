/**
 * ZapPopPop Library: hover / focus bubble (shown BELOW the small card buttons). ES5, cross-browser.
 * Moves each button's title into data-zpp-tip so the native tooltip does not double up.
 */
(function (window, $) {
    'use strict';

    var SEL = '.zappoppop-tpl-card .zpp-tpl-foot .button';
    var $tip = null;

    function tipEl() {
        if (!$tip) { $tip = $('<div id="zappoppop-lib-tip" role="tooltip"></div>').appendTo(document.body); }
        return $tip;
    }

    function stash($b) {
        var t = $b.attr('title');
        if (t) { $b.attr('data-zpp-tip', t).removeAttr('title'); }
        return $b.attr('data-zpp-tip') || $b.attr('aria-label') || '';
    }

    function show($b) {
        var text = stash($b);
        if (!text) { return; }
        var $t = tipEl().text(text).css({ display: 'block', left: 0, top: 0 });
        var r = $b[0].getBoundingClientRect();
        var w = $t.outerWidth(), vw = $(window).width();
        var cx = r.left + r.width / 2;
        var left = Math.max(8, Math.min(cx - w / 2, vw - w - 8));
        $t[0].style.setProperty('--zpp-tip-arrow', Math.round(cx - left) + 'px');
        $t.css({ left: left + 'px', top: Math.round(r.bottom + 8) + 'px' });
    }

    function hide() { if ($tip) { $tip.css('display', 'none'); } }

    $(function () {
        $(SEL).each(function () { stash($(this)); });
        $(document)
            .on('mouseenter.zppTip focusin.zppTip', SEL, function () { show($(this)); })
            .on('mouseleave.zppTip focusout.zppTip click.zppTip', SEL, hide);
        $(window).on('scroll.zppTip resize.zppTip', hide);
    });
})(window, jQuery);
