/**
 * ZapPopPop Module Category manager (Settings > Module > Module Category).
 * Add, rename, delete and reorder (drag or arrows) the categories used by the filter pills on the Extend page.
 */
(function ($) {
    'use strict';

    var M = window.ZapPopPopModules;
    if (!M) { return; }

    $(function () {
        var $open = $('#zpp-mod-cats-btn');
        var $modal = $('#zpp-cat-modal');
        if (!$open.length || !$modal.length) { return; }

        /* Lives under <body>, outside the settings form, so none of its fields can be saved or submitted with it. */
        $modal.appendTo(document.body);
        var $dlg = $modal.find('.zpp-cat-dlg');
        var $fixed = $('#zpp-cat-fixed');
        var $list = $('#zpp-cat-list');
        var $new = $('#zpp-cat-new');
        var $msg = $('#zpp-cat-msg');
        var $save = $('#zpp-cat-save');
        var MAX = Number(M.vars.cat_max) || 30;
        var NAME_MAX = Number(M.vars.cat_name_max) || 40;
        var $returnTo = null;
        var busy = false;

        function trim(v) { return String(v === null || v === undefined ? '' : v).replace(/^\s+|\s+$/g, ''); }

        function icon(name) { return $('<span class="dashicons" aria-hidden="true"></span>').addClass('dashicons-' + name); }

        function btn(cls, label, ico) {
            return $('<button type="button" class="zpp-cat-btn"></button>').addClass(cls).attr({ 'aria-label': label, title: label }).append(icon(ico));
        }

        function row(c) {
            var $r = $('<li class="zpp-cat-row"></li>').attr('data-slug', c.slug || '');
            $r.append($('<span class="zpp-cat-grip" aria-hidden="true"></span>').append(icon('menu')));
            $('<input type="text" class="zpp-cat-name" autocomplete="off">')
                .attr({ maxlength: NAME_MAX, 'aria-label': M.t('cat_name') }).val(c.name || '').appendTo($r);
            $r.append(btn('zpp-cat-up', M.t('cat_up'), 'arrow-up-alt2'));
            $r.append(btn('zpp-cat-down', M.t('cat_down'), 'arrow-down-alt2'));
            $r.append(btn('zpp-cat-del', M.t('cat_remove'), 'trash'));
            return $r;
        }

        function say(type, text) {
            if (!text) { $msg.prop('hidden', true).empty(); return; }
            $msg.removeClass('is-error is-success').addClass('is-' + type).text(text).prop('hidden', false);
        }

        function fill() {
            $fixed.empty();
            $list.empty();
            $.each(M.vars.categories || [], function (i, c) {
                if (c.locked) {
                    $('<li class="zpp-cat-row is-locked"></li>')
                        .append($('<span class="zpp-cat-grip" aria-hidden="true"></span>').append(icon('lock')))
                        .append($('<span class="zpp-cat-fixed-name"></span>').text(c.name))
                        .append($('<span class="zpp-cat-lock"></span>').text(M.t('cat_default')))
                        .appendTo($fixed);
                } else {
                    $list.append(row(c));
                }
            });
            if ($.fn.sortable) {
                if ($list.data('ui-sortable')) { $list.sortable('destroy'); }
                $list.sortable({
                    handle: '.zpp-cat-grip',
                    axis: 'y',
                    tolerance: 'pointer',
                    placeholder: 'zpp-cat-ph',
                    forcePlaceholderSize: true
                });
            }
        }

        function collect() {
            var out = [];
            $list.children('.zpp-cat-row').each(function () {
                out.push({ slug: String($(this).attr('data-slug') || ''), name: trim($(this).find('.zpp-cat-name').val()) });
            });
            return out;
        }

        function taken(name, except) {
            var low = name.toLowerCase();
            if (low === 'all modules') { return true; }
            var all = M.vars.categories || [];
            for (var i = 0; i < all.length; i++) {
                if (all[i].locked && String(all[i].name).toLowerCase() === low) { return true; }
            }
            var hit = false;
            $list.children('.zpp-cat-row').each(function () {
                var $n = $(this).find('.zpp-cat-name');
                if ($n[0] !== except && trim($n.val()).toLowerCase() === low) { hit = true; }
            });
            return hit;
        }

        function addNew() {
            var name = trim($new.val());
            if (!name) { say('error', M.t('cat_empty')); $new.trigger('focus'); return; }
            if (taken(name)) { say('error', M.fmt(M.t('cat_dup'), name)); $new.trigger('focus'); return; }
            if ($list.children().length >= MAX) { say('error', M.fmt(M.t('cat_max_msg'), MAX)); return; }
            var $r = row({ slug: '', name: name }).appendTo($list);
            $new.val('').trigger('focus');
            say('', '');
            var el = $list.closest('.zpp-cat-bd')[0];
            if (el) { el.scrollTop = el.scrollHeight; }
            $r.addClass('is-new');
        }

        function check() {
            var rows = collect();
            var seen = {};
            for (var i = 0; i < rows.length; i++) {
                var low = rows[i].name.toLowerCase();
                if (!low) { say('error', M.t('cat_empty')); $list.children().eq(i).find('.zpp-cat-name').trigger('focus'); return null; }
                if (low === 'all modules' || seen[low]) { say('error', M.fmt(M.t('cat_dup'), rows[i].name)); $list.children().eq(i).find('.zpp-cat-name').trigger('focus'); return null; }
                seen[low] = 1;
            }
            return rows;
        }

        function focusables() {
            return $dlg.find('button, input, a[href], [tabindex]:not([tabindex="-1"])').filter(':visible:not(:disabled)');
        }

        function close() {
            if (busy) { return; }
            $(document).off('keydown.zppcats');
            $modal.prop('hidden', true);
            say('', '');
            if ($returnTo && $returnTo.length) { $returnTo.trigger('focus'); }
        }

        function openModal() {
            $returnTo = $(document.activeElement);
            fill();
            say('', '');
            $new.val('');
            $modal.prop('hidden', false);
            $(document).off('keydown.zppcats').on('keydown.zppcats', function (e) {
                if (e.which === 27) { e.preventDefault(); close(); return; }
                if (e.which !== 9) { return; }
                var $f = focusables();
                if (!$f.length) { return; }
                var first = $f[0];
                var last = $f[$f.length - 1];
                if (e.shiftKey && (document.activeElement === first || !$dlg[0].contains(document.activeElement))) { e.preventDefault(); last.focus(); }
                else if (!e.shiftKey && (document.activeElement === last || !$dlg[0].contains(document.activeElement))) { e.preventDefault(); first.focus(); }
            });
            var $first = $list.find('.zpp-cat-name').first();
            ($first.length ? $first : $new).trigger('focus');
        }

        function save() {
            if (busy) { return; }
            var rows = check();
            if (!rows) { return; }
            busy = true;
            $save.prop('disabled', true).text(M.t('cat_saving'));
            M.post('cats_save', { cats: JSON.stringify(rows) }, function (r) {
                busy = false;
                $save.prop('disabled', false).text(M.t('cat_save'));
                if (!r.ok) { say('error', r.message); return; }
                M.vars.categories = r.data.categories || M.vars.categories;
                close();
                $(document).trigger('zappoppop:module-cats-saved', [M.vars.categories]); /* ZAPPOPPOP_CATS_ON_EXTEND */
                M.notice($('#zpp-ext-notice').length ? $('#zpp-ext-notice') : $('#zpp-mod-notice'), 'success', r.data.message || M.t('cat_saved'));
            });
        }

        $open.on('click', openModal);
        $('#zpp-cat-close, #zpp-cat-cancel').on('click', close);
        $modal.on('mousedown', function (e) { if (e.target === $modal[0]) { close(); } });
        $('#zpp-cat-add').on('click', addNew);
        $new.on('keydown', function (e) { if (e.which === 13) { e.preventDefault(); e.stopPropagation(); addNew(); } });
        $save.on('click', save);

        $list.on('keydown', '.zpp-cat-name', function (e) { if (e.which === 13) { e.preventDefault(); e.stopPropagation(); $new.trigger('focus'); } });
        $list.on('input', '.zpp-cat-name', function () { say('', ''); });
        $list.on('click', '.zpp-cat-del', function () {
            var $r = $(this).closest('.zpp-cat-row');
            var $next = $r.next().length ? $r.next() : $r.prev();
            $r.remove();
            ($next.length ? $next.find('.zpp-cat-name') : $new).trigger('focus');
        });
        $list.on('click', '.zpp-cat-up, .zpp-cat-down', function () {
            var $b = $(this);
            var $r = $b.closest('.zpp-cat-row');
            if ($b.hasClass('zpp-cat-up')) { var $p = $r.prev('.zpp-cat-row'); if ($p.length) { $r.insertBefore($p); } }
            else { var $n = $r.next('.zpp-cat-row'); if ($n.length) { $r.insertAfter($n); } }
            $b.trigger('focus');
        });
    });
})(jQuery);
