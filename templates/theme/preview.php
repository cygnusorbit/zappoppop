<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="zappoppop-box zpp-theme-box zpp-theme-preview">
    <div class="zpp-theme-search">
        <input type="search" id="zpp-theme-search" placeholder="<?php esc_attr_e('Search settings...', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Search settings', 'zappoppop'); ?>" autocomplete="off">
    </div>
    <h3><?php esc_html_e('Live Preview', 'zappoppop'); ?></h3>
    <p class="description"><?php esc_html_e('Changes apply instantly to this whole page before saving.', 'zappoppop'); ?></p>
    <div class="zappoppop-kpi-card"><span class="kpi-label"><?php esc_html_e('Views', 'zappoppop'); ?></span><span class="kpi-val">1,284</span></div>
    <p>
        <a href="#" data-zappoppop-noop="1"><?php esc_html_e('Sample link', 'zappoppop'); ?></a>
        <span class="zappoppop-status-badge status-publish"><?php esc_html_e('Publish', 'zappoppop'); ?></span>
        <span class="zappoppop-status-badge status-draft"><?php esc_html_e('Draft', 'zappoppop'); ?></span>
        <span class="zappoppop-shortcode-chip"><?php esc_html_e('[zappoppop id="abc"]', 'zappoppop'); ?></span>
    </p>
    <p>
        <button type="button" class="button button-primary"><?php esc_html_e('Primary', 'zappoppop'); ?></button>
        <button type="button" class="button"><?php esc_html_e('Secondary', 'zappoppop'); ?></button>
        <button type="button" class="button zpp-btn-danger"><?php esc_html_e('Danger', 'zappoppop'); ?></button>
        <button type="button" class="button is-active"><?php esc_html_e('Active', 'zappoppop'); ?></button>
    </p>
    <p><input type="text" placeholder="<?php esc_attr_e('Text input', 'zappoppop'); ?>"> <select><option><?php esc_html_e('Select', 'zappoppop'); ?></option></select></p>
    <table class="wp-list-table widefat striped">
        <thead><tr><th><?php esc_html_e('Name', 'zappoppop'); ?></th><th><?php esc_html_e('Status', 'zappoppop'); ?></th></tr></thead>
        <tbody><tr><td><?php esc_html_e('Row one', 'zappoppop'); ?></td><td><?php esc_html_e('Active', 'zappoppop'); ?></td></tr><tr><td><?php esc_html_e('Row two', 'zappoppop'); ?></td><td><?php esc_html_e('Draft', 'zappoppop'); ?></td></tr></tbody>
    </table>
</div>
