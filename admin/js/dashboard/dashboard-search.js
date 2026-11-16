/**
 * ZapPopPop Dashboard: Search Filter
 * Synchronizes real-time text input with ZapPopPopDashboardTable.
 */
(function($) {
    'use strict';

    window.ZapPopPopDashboardSearch = {
        init: function() {
            this.bindSearch();
        },

        bindSearch: function() {
            var debounceTimer;
            $('#zappoppop-search-input').on('keyup input', function() {
                var query = $(this).val();
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(function() {
                    if (window.ZapPopPopDashboardTable) {
                        window.ZapPopPopDashboardTable.filter(query);
                    }
                }, 150);
            });
        }
    };
})(jQuery);
