/**
 * ZapPopPop Settings: Tabs Navigation Controller
 */
(function(window, $) {
    'use strict';
    window.ZapPopPopSettings = window.ZapPopPopSettings || {};

    var Tabs = {
        init: function() {
            this.bindEvents();
            this.restoreActiveTab();
        },

        bindEvents: function() {
            var self = this;
            $('.zappoppop-tab-btn').on('click', function(e) {
                e.preventDefault();
                var targetTab = $(this).data('tab');
                self.switchTab(targetTab);
            });
        },

        switchTab: function(tabKey) {
            if (!tabKey) return;
            tabKey = String(tabKey).replace(/^#/, '').replace(/^tab-/, '').replace(/^zappoppop-tab-/, '');

            // Deactivate all tab buttons and containers
            $('.zappoppop-tab-btn').removeClass('active');
            $('.zappoppop-tab-content').removeClass('active').hide();

            // Highlight active button
            $('.zappoppop-tab-btn[data-tab="' + tabKey + '"]').addClass('active');

            // Activate target container matching either #tab-* or data-tab-content
            var $target = $('#tab-' + tabKey + ', #zappoppop-tab-' + tabKey + ', [data-tab-content="' + tabKey + '"]');
            if ($target.length) {
                $target.addClass('active').show();
            }

            // Sync with URL Hash for direct linking
            /* ZPP_BUILDER_ANCHORS: keep #builder-element / #builder-guide while the Builder tab stays open */
            if (window.history && window.history.replaceState && !(tabKey === 'builder' && /^#builder-(tools|element|guide)$/.test(window.location.hash))) {
                window.history.replaceState(null, '', '#' + tabKey);
            }
        },

        restoreActiveTab: function() {
            window.ZapPopPopSettings.initialHash = window.location.hash || ''; /* ZPP_BUILDER_ANCHORS */
            if (window.location.hash) {
                var hashTab = window.location.hash.replace('#', '').replace(/^tab-/, '');
                if (/^builder-(tools|element|guide)$/.test(hashTab)) { hashTab = 'builder'; }
                if ($('.zappoppop-tab-btn[data-tab="' + hashTab + '"]').length) {
                    this.switchTab(hashTab);
                    return;
                }
            }
            // Default to general tab if none selected
            this.switchTab('general');
        }
    };

    window.ZapPopPopSettings.Tabs = Tabs;
    window.ZapPopPopSettingsTabs = Tabs;
})(window, jQuery);

/* ZPP_TAB_CLEAN: remove legacy is-active hook from settings tab buttons */
(function ($) {
    'use strict';
    $(function () {
        $('.zappoppop-settings-tabs-nav .zappoppop-tab-btn').removeClass('is-active');
    });
})(jQuery);
