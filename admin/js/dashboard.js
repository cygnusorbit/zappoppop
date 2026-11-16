/**
 * ZapPopPop Dashboard: Master Coordinator
 */
(function($) {
    'use strict';

    window.ZapPopPopDashboard = window.ZapPopPopDashboard || {};

    window.ZapPopPopDashboard.init = function() {
        if (window.ZapPopPopDashboardTable && typeof window.ZapPopPopDashboardTable.init === 'function') {
            window.ZapPopPopDashboardTable.init();
        }

        // Support both direct module and nested namespace
        var actionsModule = window.ZapPopPopDashboardActions || (window.ZapPopPopDashboard && window.ZapPopPopDashboard.Actions);
        if (actionsModule && typeof actionsModule.init === 'function') {
            actionsModule.init();
        }

        if (window.ZapPopPopDashboardSearch && typeof window.ZapPopPopDashboardSearch.init === 'function') {
            window.ZapPopPopDashboardSearch.init();
        }
        if (window.ZapPopPopDashboardImport && typeof window.ZapPopPopDashboardImport.init === 'function') {
            window.ZapPopPopDashboardImport.init();
        }
        if (window.ZapPopPopDashboardEmbed && typeof window.ZapPopPopDashboardEmbed.init === 'function') {
            window.ZapPopPopDashboardEmbed.init();
        }
    };

    $(document).ready(function() {
        window.ZapPopPopDashboard.init();
    });
})(jQuery);
