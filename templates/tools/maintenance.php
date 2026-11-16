<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="zappoppop-card-box zpp-tools-card">
    <div class="zpp-tools-card-head">
        <span class="dashicons dashicons-admin-generic"></span>
        <h3><?php esc_html_e('Restore Options & Maintenance', 'zappoppop'); ?></h3>
    </div>
    <div id="zappoppop-maint-notice" class="zpp-tools-notice" hidden></div>

    <div class="zpp-tools-grid">
        <div class="zpp-tools-panel">
            <h4><?php esc_html_e('Restore options', 'zappoppop'); ?></h4>
            <p class="zpp-tools-help"><?php esc_html_e('Applied the next time you restore a backup above. Original embed UIDs are kept unless they already exist.', 'zappoppop'); ?></p>
            <label class="zpp-tools-check"><input type="checkbox" id="zappoppop-import-dryrun"> <?php esc_html_e('Preview only (dry run, nothing is written)', 'zappoppop'); ?></label>
            <label class="zpp-tools-check"><input type="checkbox" id="zappoppop-import-settings"> <?php esc_html_e('Also restore plugin settings and theme', 'zappoppop'); ?></label>
        </div>

        <div class="zpp-tools-panel">
            <h4><?php esc_html_e('Purge old logs', 'zappoppop'); ?></h4>
            <p class="zpp-tools-help"><?php esc_html_e('Delete activity-log entries older than the chosen number of days.', 'zappoppop'); ?></p>
            <div class="zpp-tools-row">
                <input type="number" id="zappoppop-purge-days" value="30" min="1" max="3650" class="small-text">
                <button type="button" class="button" data-zpp-maint="zappoppop_purge_logs"><?php esc_html_e('Purge logs', 'zappoppop'); ?></button>
            </div>
        </div>

        <div class="zpp-tools-panel">
            <h4><?php esc_html_e('Optimize tables', 'zappoppop'); ?></h4>
            <p class="zpp-tools-help"><?php esc_html_e('Runs OPTIMIZE TABLE on the plugin tables to reclaim space.', 'zappoppop'); ?></p>
            <button type="button" class="button" data-zpp-maint="zappoppop_optimize_tables"><?php esc_html_e('Optimize now', 'zappoppop'); ?></button>
        </div>

        <div class="zpp-tools-panel">
            <h4><?php esc_html_e('Orphan check', 'zappoppop'); ?></h4>
            <p class="zpp-tools-help"><?php esc_html_e('Counts submissions and transactions whose popup no longer exists. Read-only.', 'zappoppop'); ?></p>
            <button type="button" class="button" data-zpp-maint="zappoppop_orphan_report"><?php esc_html_e('Run report', 'zappoppop'); ?></button>
        </div>
    </div>
</div>
