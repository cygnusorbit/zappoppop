<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- template variables are local to the method that includes this file
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

$custom_tables = class_exists('ZapPopPop_Ajax_Tools') ? ZapPopPop_Ajax_Tools::table_defs() : [];
?>
<?php
$tbl_status = class_exists('ZapPopPop_Ajax_Tools') ? ZapPopPop_Ajax_Tools::table_status() : [];
?>
<div class="zappoppop-card-box zpp-tools-card">
    <div class="zpp-tools-card-head">
        <span class="dashicons dashicons-database"></span>
        <h3><?php esc_html_e('Database Schema Table Health & Maintenance', 'zappoppop'); ?></h3>
        <div class="zpp-tools-head-actions">
            <button type="button" class="button zpp-tools-btn" data-zpp-trigger="#zappoppop-copy-system-info-btn"><?php esc_html_e('Copy Report', 'zappoppop'); ?></button>
            <button type="button" class="button zpp-tools-btn" data-zpp-trigger="#zappoppop-repair-tables-btn"><?php esc_html_e('Verify Schema', 'zappoppop'); ?></button>
        </div>
    </div>

    <div id="zappoppop-db-status-notice" class="zpp-tools-notice" hidden></div>

    <table class="widefat striped zpp-tools-table">
        <thead>
            <tr>
                <th><?php esc_html_e('Component Domain', 'zappoppop'); ?></th>
                <th><?php esc_html_e('MySQL Table Name', 'zappoppop'); ?></th>
                <th><?php esc_html_e('Status', 'zappoppop'); ?></th>
                <th class="num"><?php esc_html_e('Records', 'zappoppop'); ?></th>
                <th class="num"><?php esc_html_e('Table Size', 'zappoppop'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($custom_tables as $key => $tbl) :
                $st = isset($tbl_status[$key]) ? $tbl_status[$key] : ['exists' => false, 'rows' => '0', 'size' => '0 KB', 'status' => 'Missing'];
            ?>
            <tr id="zappoppop-table-row-<?php echo esc_attr($key); ?>" data-table-key="<?php echo esc_attr($key); ?>">
                <td class="zpp-strong"><?php echo esc_html($tbl['label']); ?></td>
                <td class="zpp-mono"><?php echo esc_html($tbl['name']); ?></td>
                <td><span class="zappoppop-tbl-status-pill zpp-pill <?php echo !empty($st['exists']) ? 'is-ok' : 'is-bad'; ?>"><?php echo esc_html($st['status']); ?></span></td>
                <td class="num zappoppop-tbl-rows-count"><?php echo esc_html($st['rows']); ?></td>
                <td class="num zappoppop-tbl-size-val"><?php echo esc_html($st['size']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="zpp-tools-grid">
        <div class="zpp-tools-panel">
            <h4><?php esc_html_e('Verify & Repair Schema Tables', 'zappoppop'); ?></h4>
            <p class="zpp-tools-help"><?php esc_html_e('Runs dbDelta routines to verify table structures, apply missing columns, and guarantee index alignment across all 5 custom tables without modifying existing data.', 'zappoppop'); ?></p>
            <form method="post" id="zappoppop-repair-tables-form">
                <input type="hidden" name="zappoppop_tools_nonce" value="<?php echo esc_attr(wp_create_nonce('zappoppop_tools_action')); ?>"><?php wp_referer_field(); ?>
                <button type="submit" name="zappoppop_repair_tables" id="zappoppop-repair-tables-btn" class="button zpp-tools-btn"><?php esc_html_e('Verify & Repair Schema', 'zappoppop'); ?></button>
            </form>
        </div>

        <div class="zpp-tools-panel is-danger">
            <h4><?php esc_html_e('Reset Campaign Metrics Counters', 'zappoppop'); ?></h4>
            <p class="zpp-tools-help"><?php esc_html_e('Wipes all recorded popup impressions, captured lead counters, and double opt-in confirmations back to 0. This does not delete any designs or lead submissions.', 'zappoppop'); ?></p>
            <form method="post" id="zappoppop-reset-counters-form">
                <input type="hidden" name="zappoppop_tools_nonce" value="<?php echo esc_attr(wp_create_nonce('zappoppop_tools_action')); ?>"><?php wp_referer_field(); ?>
                <button type="submit" name="zappoppop_reset_counters" id="zappoppop-reset-counters-btn" class="button zpp-tools-btn zpp-btn-danger"><?php esc_html_e('Reset All Counters', 'zappoppop'); ?></button>
            </form>
        </div>
    </div>
</div>
