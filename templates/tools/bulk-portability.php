<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="zappoppop-card-box zpp-tools-card">
    <div class="zpp-tools-card-head">
        <span class="dashicons dashicons-download"></span>
        <h3><?php esc_html_e('Bulk Backup & Template Portability', 'zappoppop'); ?></h3>
    </div>

    <div class="zpp-tools-grid">
        <div class="zpp-tools-panel">
            <h4><?php esc_html_e('Export All Campaigns (Bulk JSON)', 'zappoppop'); ?></h4>
            <p class="zpp-tools-help"><?php esc_html_e('Download a complete JSON archive containing all popup designs, A/B campaigns, plugin settings and the global theme.', 'zappoppop'); ?></p>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="zappoppop-bulk-export-form">
                <input type="hidden" name="action" value="zappoppop_bulk_export">
                <input type="hidden" name="zappoppop_tools_nonce" value="<?php echo esc_attr(wp_create_nonce('zappoppop_tools_action')); ?>"><?php wp_referer_field(); ?>
                <button type="submit" name="zappoppop_bulk_export" id="zappoppop-bulk-export-btn" class="button button-primary zpp-tools-btn"><?php esc_html_e('Export Complete Backup', 'zappoppop'); ?></button>
            </form>
        </div>

        <div class="zpp-tools-panel">
            <h4><?php esc_html_e('Restore / Import Bulk Archive', 'zappoppop'); ?></h4>
            <p class="zpp-tools-help"><?php esc_html_e('Select a previously exported ZapPopPop bulk JSON archive (10 MB maximum). Restore is all-or-nothing: if the database rejects any entry, nothing is kept.', 'zappoppop'); ?></p>
            <form method="post" enctype="multipart/form-data" id="zappoppop-bulk-import-form">
                <input type="hidden" name="zappoppop_tools_nonce" value="<?php echo esc_attr(wp_create_nonce('zappoppop_tools_action')); ?>"><?php wp_referer_field(); ?>
                <input type="file" name="zappoppop_bulk_import_file" id="zappoppop-bulk-import-file" class="zpp-tools-file" accept=".json,application/json" required>
                <label class="zpp-tools-check"><input type="checkbox" name="import_settings" value="1"> <?php esc_html_e('Also restore plugin settings and theme', 'zappoppop'); ?></label>
                <div class="zpp-tools-row">
                    <button type="submit" name="zappoppop_bulk_import" id="zappoppop-bulk-import-btn" class="button zpp-tools-btn"><?php esc_html_e('Restore Bulk Backup', 'zappoppop'); ?></button>
                    <span id="zappoppop-import-status-text" class="zpp-tools-status" hidden></span>
                </div>
            </form>
        </div>
    </div>
</div>
