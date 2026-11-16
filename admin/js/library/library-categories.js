/**
 * ZapPopPop Library: category manager (add / edit / remove / reposition).
 * jQuery UI Sortable + up/down buttons. ES5 for Chrome, Safari, Firefox, Edge.
 */
(function (window, $) {
    'use strict';

    var DEFAULTS = [
        { slug: 'lead-gen', label: 'Lead Generation' },
        { slug: 'ecommerce', label: 'E-Commerce & Coupons' },
        { slug: 'gamified', label: 'Gamified & Interactive' },
        { slug: 'feedback', label: 'Surveys & Feedback' },
        { slug: 'announcement', label: 'Announcements' },
        { slug: 'custom', label: 'My Templates' }
    ];

    function vars() {
        return window.zappoppop_libcat_vars || { ajax_url: (typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php'), nonce: '' };
    }
    function $m() { return $('#zappoppop-lib-cats-modal'); }
    function $list() { return $('#zappoppop-lc-list'); }

    function msg(text, ok) {
        $('#zappoppop-lc-msg').css('color', ok ? '#008a20' : '#b32d2e').text(text).show();
    }

    function row(c) {
        var locked = c.slug === 'custom';
        var $r = $('<li class="lc-row"></li>').attr('data-slug', c.slug);
        $r.append('<span class="lc-grip" title="Drag to reorder" aria-hidden="true">&#8801;</span>');
        $r.append($('<input type="text" maxlength="40" class="lc-label">').val(c.label));
        $r.append('<button type="button" class="lc-btn lc-up" title="Move up" aria-label="Move up">&#9650;</button>');
        $r.append('<button type="button" class="lc-btn lc-down" title="Move down" aria-label="Move down">&#9660;</button>');
        if (locked) {
            $r.append('<span class="lc-lock" title="Holds your saved templates">protected</span>');
        } else {
            $r.append('<button type="button" class="lc-btn lc-del" title="Remove" aria-label="Remove">&times;</button>');
        }
        return $r;
    }

    function render(cats) {
        var $l = $list().empty(), i;
        for (i = 0; i < cats.length; i++) { $l.append(row(cats[i])); }
        if ($l.data('ui-sortable') || $l.hasClass('ui-sortable')) { $l.sortable('destroy'); }
        if ($.fn.sortable) {
            $l.sortable({ handle: '.lc-grip', axis: 'y', tolerance: 'pointer', placeholder: 'lc-ph', forcePlaceholderSize: true });
        }
    }

    function collect() {
        var out = [];
        $list().children('.lc-row').each(function () {
            var label = $.trim($(this).find('.lc-label').val());
            if (label) { out.push({ slug: $(this).attr('data-slug') || '', label: label }); }
        });
        return out;
    }

    function currentFromPills() {
        var out = [];
        $('.zappoppop-library-categories .zappoppop-cat-btn').each(function () {
            var slug = $(this).attr('data-cat');
            if (slug && slug !== 'all') { out.push({ slug: slug, label: $.trim($(this).text()) }); }
        });
        return out;
    }

    function open() {
        $('#zappoppop-lc-msg').hide().text('');
        $('#zappoppop-lc-new').val('');
        render(currentFromPills());
        $m().css('display', 'flex');
        $('#zappoppop-lc-new').focus();
    }
    function close() { $m().hide(); }

    function addNew() {
        var label = $.trim($('#zappoppop-lc-new').val());
        if (!label) { return; }
        if ($list().children('.lc-row').length >= 30) { msg('Maximum 30 categories.', false); return; }
        var $r = row({ slug: '', label: label });
        $list().append($r);
        $('#zappoppop-lc-new').val('').focus();
        $('#zappoppop-lc-msg').hide();
    }

    function save() {
        var cats = collect(), v = vars(), $b = $('#zappoppop-lc-save');
        $b.prop('disabled', true);
        $.ajax({
            url: v.ajax_url, type: 'POST', dataType: 'json',
            data: { action: 'zappoppop_library_cats_save', nonce: v.nonce, cats: JSON.stringify(cats) },
            success: function (res) {
                if (res && res.success) {
                    msg('Saved. Reloading...', true);
                    window.location.reload();
                } else {
                    msg((res && res.data && res.data.message) || 'Save failed.', false);
                    $b.prop('disabled', false);
                }
            },
            error: function () { msg('Server error while saving.', false); $b.prop('disabled', false); }
        });
    }

    $(function () {
        $(document)
            .on('click.zppLc', '#zappoppop-lib-cats-btn', function (e) { e.preventDefault(); open(); })
            .on('click.zppLc', '#zappoppop-lc-close, #zappoppop-lc-cancel', function (e) { e.preventDefault(); close(); })
            .on('click.zppLc', '#zappoppop-lib-cats-modal', function (e) { if (e.target === this) { close(); } })
            .on('click.zppLc', '#zappoppop-lc-add', function (e) { e.preventDefault(); addNew(); })
            .on('keydown.zppLc', '#zappoppop-lc-new', function (e) { if (e.keyCode === 13) { e.preventDefault(); addNew(); } })
            .on('click.zppLc', '#zappoppop-lc-save', function (e) { e.preventDefault(); save(); })
            .on('click.zppLc', '#zappoppop-lc-reset', function (e) {
                e.preventDefault();
                if (window.confirm(wp.i18n.__('Reset categories to the defaults? Your changes in this dialog will be replaced.', 'zappoppop'))) { render(DEFAULTS); }
            })
            .on('click.zppLc', '#zappoppop-lib-cats-modal .lc-up', function (e) {
                e.preventDefault();
                var $r = $(this).closest('.lc-row'), $p = $r.prev('.lc-row');
                if ($p.length) { $r.insertBefore($p); }
            })
            .on('click.zppLc', '#zappoppop-lib-cats-modal .lc-down', function (e) {
                e.preventDefault();
                var $r = $(this).closest('.lc-row'), $n = $r.next('.lc-row');
                if ($n.length) { $r.insertAfter($n); }
            })
            .on('click.zppLc', '#zappoppop-lib-cats-modal .lc-del', function (e) {
                e.preventDefault();
                $(this).closest('.lc-row').remove();
            })
            .on('keydown.zppLc', function (e) { if (e.keyCode === 27 && $m().is(':visible')) { close(); } });
    });
})(window, jQuery);
