(function() {
    'use strict';

    var canonicalDefaults = window.zappoppopCanonicalDefaults || {};

    function applySettingsDefaultsToDOM() {
        // 1. Builder Tools: Uncheck all
        var builderCheckboxes = document.querySelectorAll(
            '[name^="builder_tools"], [name^="zappoppop_settings[builder_tools]"], [data-section="builder_tools"] input[type="checkbox"], .zappoppop-builder-tool-checkbox'
        );
        builderCheckboxes.forEach(function(cb) {
            cb.checked = false;
            cb.removeAttribute('checked');
            cb.dispatchEvent(new Event('change', { bubbles: true }));
        });

        // 2. Campaign Settings Modules: Uncheck all except 16, 17, 18, 19, 20
        var moduleCheckboxes = document.querySelectorAll(
            '[name*="campaign_modules"], [name*="campaign_settings_modules"], [name*="modules["], .zappoppop-module-checkbox'
        );
        moduleCheckboxes.forEach(function(cb) {
            var val = (cb.value || cb.name || cb.id || '').toLowerCase();
            var numMatches = val.match(/\d+/);
            var modNum = numMatches ? parseInt(numMatches[0], 10) : null;
            var shouldEnable = [16, 17, 18, 19, 20].indexOf(modNum) !== -1;

            cb.checked = shouldEnable;
            if (shouldEnable) {
                cb.setAttribute('checked', 'checked');
            } else {
                cb.removeAttribute('checked');
            }
            cb.dispatchEvent(new Event('change', { bubbles: true }));
        });

        // 3. Pages: Check zappoppop-log, zappoppop-field-analytics, zappoppop-payments
        var targetPages = ['zappoppop-log', 'zappoppop-field-analytics', 'zappoppop-payments'];
        targetPages.forEach(function(slug) {
            var pageCheckboxes = document.querySelectorAll(
                'input[type="checkbox"][value="' + slug + '"], input[name*="[' + slug + ']"], #' + slug
            );
            pageCheckboxes.forEach(function(cb) {
                cb.checked = true;
                cb.setAttribute('checked', 'checked');
                cb.dispatchEvent(new Event('change', { bubbles: true }));
            });
        });
    }

    // Task 4: Hook Reset Button
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('#zappoppop-reset-settings, .zappoppop-reset-button, [data-action="reset-settings"], [name="reset_settings"]');
        if (!btn) return;

        if (!confirm('Are you sure you want to reset all settings to defaults?')) {
            e.preventDefault();
            return;
        }

        // Immediately synchronize DOM inputs to new default values
        applySettingsDefaultsToDOM();
    }, true);

    // If query param is reset, ensure inputs match immediately
    if (window.location.search.indexOf('status=reset') !== -1) {
        document.addEventListener('DOMContentLoaded', applySettingsDefaultsToDOM);
    }
})();
