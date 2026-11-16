/**
 * ZapPopPop Library: live template preview + category/search filter.
 * Saved templates -> real frontend renderer in an iframe.
 * Built-in starters -> scaled wireframe. ES5 for Chrome, Safari, Firefox, Edge.
 */
(function (window, $) {
    'use strict';

    function vars() {
        return window.zappoppop_lib_vars || window.zappoppop_vars || { ajax_url: (typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php'), nonce: '' };
    }
    function $m() { return $('#zappoppop-lib-preview-modal'); }

    var cur = { id: '', frame: '' };

    function setView(mode) {
        $m().find('[data-lp-view]').removeClass('is-on').filter('[data-lp-view="' + mode + '"]').addClass('is-on');
        $m().find('.lp-frame').toggleClass('is-mobile', mode === 'mobile');
    }

    function mock(cfg) {
        var meta = (cfg && cfg.meta) || {};
        var w = parseInt(meta.width, 10) || 640, h = parseInt(meta.height, 10) || 400;
        var maxW = Math.max(300, $('#zappoppop-lp-stage').width() - 48);
        var scale = Math.min(1, maxW / w);
        var $box = $('<div class="lp-mock"></div>').css({ width: w + 'px', height: h + 'px', background: meta.bg_color || '#fff', transform: 'scale(' + scale + ')', marginBottom: (-(h * (1 - scale))) + 'px' });
        var els = (cfg && cfg.elements) || [], i, e, $e, label;
        for (i = 0; i < els.length; i++) {
            e = els[i] || {};
            label = (typeof e.content === 'string' || typeof e.content === 'number') ? String(e.content) : (e.type || '');
            $e = $('<div></div>').css({ left: (e.left || 0) + 'px', top: (e.top || 0) + 'px', width: (e.width || 100) + 'px', height: (e.height || 40) + 'px', zIndex: e.z_index || 1 }).text(label);
            if (e.color) { $e.css('color', e.color); }
            if (e.bg_color) { $e.css({ background: e.bg_color, border: '0' }); }
            $box.append($e);
        }
        return $('<div class="lp-mock-wrap"></div>').append($box);
    }

    function loadFrame(id) {
        var v = vars(), $stage = $('#zappoppop-lp-stage');
        $stage.empty().append('<div class="lp-msg">Loading preview&hellip;</div>');
        $.ajax({
            url: v.ajax_url, type: 'POST', dataType: 'json',
            data: { action: 'zappoppop_template_preview', nonce: v.nonce, id: id },
            success: function (res) {
                if (res && res.success && res.data && res.data.frame_url) {
                    cur.frame = res.data.frame_url;
                    var $f = $('<iframe class="lp-frame" title="Template preview"></iframe>');
                    $stage.empty().append($f);
                    $f.attr('src', cur.frame);
                    setView($m().find('[data-lp-view].is-on').attr('data-lp-view') || 'desktop');
                } else {
                    $stage.html('<div class="lp-msg">' + $('<div>').text((res && res.data && res.data.message) || 'Preview unavailable.').html() + '</div>');
                }
            },
            error: function () { $stage.html('<div class="lp-msg">Server error while loading preview.</div>'); }
        });
    }

    function open($btn) {
        var id = String($btn.attr('data-id') || ''), cfg = {};
        try { cfg = JSON.parse($btn.attr('data-config') || '{}'); } catch (e) { cfg = {}; }
        cur.id = id; cur.frame = '';
        $('#zappoppop-lp-title').text($btn.attr('data-title') || 'Template Preview');
        $('#zappoppop-lp-meta').text(($btn.attr('data-width') || '') + ' \u00d7 ' + ($btn.attr('data-height') || '') + ' px \u2022 ' + ($btn.attr('data-elements') || '0') + ' elements');
        var isUser = (id.indexOf('user-') === 0 || id.indexOf('preset-') === 0);
        $('#zappoppop-lp-use').toggle(isUser);
        $('#zappoppop-lp-replay').toggle(isUser);
        setView('desktop');
        $m().css('display', 'flex');
        if (isUser) { loadFrame(id); }
        else { $('#zappoppop-lp-stage').empty().append(mock(cfg)); }
    }

    function close() {
        $m().hide();
        $('#zappoppop-lp-stage').empty(); /* stops animations / media in the frame */
    }

    function applyFilters() {
        var cat = $('.zappoppop-cat-btn.active').attr('data-cat') || 'all';
        var q = $.trim(String($('#zappoppop-lib-search').val() || '')).toLowerCase();
        var shown = 0;
        if (window.ZapPopPopLibPager) { window.ZapPopPopLibPager.reset(); }
        $('.zappoppop-tpl-card').each(function () {
            var $c = $(this);
            var okCat = cat === 'all' || String($c.attr('data-category')) === cat;
            var okQ = !q || String($c.attr('data-title') || '').indexOf(q) !== -1 || String($c.attr('data-desc') || '').indexOf(q) !== -1;
            $c.toggle(okCat && okQ);
            if (okCat && okQ) { shown++; }
        });
        if (window.ZapPopPopLibPager) { window.ZapPopPopLibPager.refresh(true); }
        $('#zappoppop-lib-empty-notice').remove();
        if (!shown) { $('.zappoppop-library-grid').append('<div id="zappoppop-lib-empty-notice" class="zappoppop-library-empty" style="grid-column:1/-1;padding:24px;text-align:center;color:#64748b;">No templates found.</div>'); }
    }

    $(function () {
        $(document)
            .on('click.zppLp', '.zappoppop-tpl-preview-btn', function (e) { e.preventDefault(); open($(this)); })
            .on('click.zppLp', '#zappoppop-lp-close, #zappoppop-lp-cancel', function (e) { e.preventDefault(); close(); })
            .on('click.zppLp', '#zappoppop-lib-preview-modal', function (e) { if (e.target === this) { close(); } })
            .on('click.zppLp', '[data-lp-view]', function (e) { e.preventDefault(); setView($(this).attr('data-lp-view')); })
            .on('click.zppLp', '#zappoppop-lp-replay', function (e) { e.preventDefault(); if (cur.id) { loadFrame(cur.id); } })
            .on('click.zppLp', '#zappoppop-lp-use', function (e) {
                e.preventDefault();
                $('.zappoppop-tpl-card[data-id="' + cur.id + '"] .zappoppop-usertpl-use').trigger('click');
            })
            .on('keydown.zppLp', function (e) { if ((e.key === 'Escape' || e.keyCode === 27) && $m().is(':visible')) { close(); } })
            .on('click.zppLp', '.zappoppop-cat-btn', function (e) {
                e.preventDefault();
                $('.zappoppop-cat-btn').removeClass('active');
                $(this).addClass('active');
                applyFilters();
            })
            .on('input.zppLp keyup.zppLp', '#zappoppop-lib-search', applyFilters);
    });
})(window, jQuery);
