/**
 * ZapPopPop Settings Master Coordinator
 * Initializes Decomposed Sub-Controllers on DOM Ready
 */
jQuery(document).ready(function($) {
    'use strict';

    if (window.ZapPopPopSettingsTabs) {
        window.ZapPopPopSettingsTabs.init();
    }
    if (window.ZapPopPopSettingsSave) {
        window.ZapPopPopSettingsSave.init();
    }
    if (window.ZapPopPopSettingsTools) {
        window.ZapPopPopSettingsTools.init();
    }
    if (window.ZapPopPopSettingsSearch) {
        window.ZapPopPopSettingsSearch.init();
    }
});
