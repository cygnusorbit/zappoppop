/* ZAPPOPPOP_RIBBON_TOGGLE: hide / unhide the top elements ribbon (state persisted per browser) */
(function (window, $) {
    'use strict';

    var KEY = 'zappoppop_ribbon_collapsed';

    function read() {
        try { return window.localStorage.getItem(KEY) === '1'; } catch (e) { return false; }
    }

    function write(v) {
        try { window.localStorage.setItem(KEY, v ? '1' : '0'); } catch (e) {}
    }

    function apply(collapsed, persist) {
        var $wrap = $('#zappoppop-ribbon-wrap');
        var $btn = $('#zappoppop-ribbon-toggle');
        if (!$wrap.length) { return; }
        var label = collapsed ? 'Show Elements Ribbon' : 'Hide Elements Ribbon';
        $wrap.toggleClass('is-collapsed', collapsed);
        $btn.attr({ 'aria-expanded': collapsed ? 'false' : 'true', 'title': label, 'aria-label': label });
        $btn.find('.dashicons')
            .toggleClass('dashicons-arrow-down-alt2', collapsed)
            .toggleClass('dashicons-arrow-up-alt2', !collapsed);
        if (persist) { write(collapsed); }
        // Let the stage / drawers re-measure the new available height
        setTimeout(function () {
            $(window).trigger('resize');
            $(document).trigger('zappoppop:ribbon-toggled', [collapsed]);
        }, 0);
    }

    function bind() {
        $(document).off('click.zappoppopRibbonToggle', '#zappoppop-ribbon-toggle')
            .on('click.zappoppopRibbonToggle', '#zappoppop-ribbon-toggle', function (e) {
                e.preventDefault();
                e.stopPropagation();
                apply(!$('#zappoppop-ribbon-wrap').hasClass('is-collapsed'), true);
            });
        apply(read(), false);
    }

    window.ZapPopPopBuilderRibbonToggle = { apply: apply, isCollapsed: read };

    $(bind);
})(window, jQuery);
