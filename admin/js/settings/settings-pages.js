/**
 * ZapPopPop Settings: Pages tab (rename / reorder / hide admin menu pages)
 */
(function ($) {
    'use strict';

    var $list;

    function reindex() {
        var $items = $list.children('li');
        $items.each(function (i) {
            var $li = $(this);
            $li.find('.zpp-pg-order').val(i + 1);
            $li.find('.zpp-pg-up').prop('disabled', i === 0);
            $li.find('.zpp-pg-down').prop('disabled', i === $items.length - 1);
            $li.toggleClass('is-hidden-page', $li.find('.zpp-pg-hidden-cb').is(':checked'));
        });
    }

    function init() {
        $list = $('#zappoppop-pages-list');
        if (!$list.length) { return; }

        if ($.fn.sortable) {
            $list.sortable({
                handle: '.zpp-pg-handle',
                axis: 'y',
                cursor: 'move',
                update: reindex
            });
        }

        $list.on('click', '.zpp-pg-up', function (e) {
            e.preventDefault();
            var $li = $(this).closest('li');
            var $prev = $li.prev('li');
            if ($prev.length) { $prev.before($li); reindex(); }
        });

        $list.on('click', '.zpp-pg-down', function (e) {
            e.preventDefault();
            var $li = $(this).closest('li');
            var $next = $li.next('li');
            if ($next.length) { $next.after($li); reindex(); }
        });

        $list.on('change', '.zpp-pg-hidden-cb', reindex);

        $('#zappoppop-pages-reset').on('click', function (e) {
            e.preventDefault();
            var items = $list.children('li').get();
            items.sort(function (a, b) {
                return parseInt($(a).attr('data-idx'), 10) - parseInt($(b).attr('data-idx'), 10);
            });
            $.each(items, function (_, li) {
                var $li = $(li);
                $li.find('.zpp-pg-label').val('');
                $li.find('.zpp-pg-hidden-cb').prop('checked', [
                    'zappoppop-log',
                    'zappoppop-field-analytics',
                    'zappoppop-payments'
                ].indexOf($li.attr('data-slug')) !== -1);
                $list.append($li);
            });
            reindex();
        });

        // Safety net: flush order right before the AJAX save serialises the form.
        $('#zappoppop-settings-form').on('submit', reindex);

        reindex();
    }

    $(init);
})(jQuery);
