<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="cell actions">
    <button type="button" class="zpp-bar-btn is-primary" id="zappoppop-btn-export-csv">
        <span class="dashicons dashicons-download"></span><span class="zpp-bar-label"><?php echo esc_html__('Export to CSV', 'zappoppop'); ?></span>
    </button>
    <button type="button" class="zpp-bar-btn is-icon" id="zappoppop-table-options-btn" title="<?php echo esc_attr__('Table options', 'zappoppop'); ?>" aria-label="<?php echo esc_attr__('Table options', 'zappoppop'); ?>">
        <span class="dashicons dashicons-admin-generic"></span>
    </button>
</div>
