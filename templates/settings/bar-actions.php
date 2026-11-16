<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="cell actions">
    <div class="zappoppop-settings-search" role="search">
        <label class="screen-reader-text" for="zappoppop-settings-search"><?php esc_html_e('Search settings', 'zappoppop'); ?></label>
        <span class="dashicons dashicons-search" aria-hidden="true"></span>
        <input type="text" id="zappoppop-settings-search" class="zappoppop-settings-search-input" placeholder="<?php echo esc_attr__('Search settings...  ( / )', 'zappoppop'); ?>" autocomplete="off" autocapitalize="off" spellcheck="false" aria-controls="zappoppop-settings-form">
        <span id="zappoppop-settings-search-count" class="zpp-s-count" aria-live="polite"></span>
        <button type="button" id="zappoppop-settings-search-clear" class="zpp-s-clear" aria-label="<?php echo esc_attr__('Clear search', 'zappoppop'); ?>" hidden>&times;</button>
    </div>
    <span id="zappoppop-bar-save-msg" class="zpp-bar-msg" aria-live="polite"></span>
    <button type="submit" class="zpp-bar-btn is-primary" form="zappoppop-settings-form" id="zappoppop-bar-save-btn"><span class="dashicons dashicons-yes"></span><?php esc_html_e('Save Settings', 'zappoppop'); ?></button>
</div>
