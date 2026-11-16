<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- template variables are local to the method that includes this file
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

// Gather Server & Environment Diagnostics
$php_version        = phpversion();
$wp_version         = get_bloginfo('version');
$server_software    = isset($_SERVER['SERVER_SOFTWARE']) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_SOFTWARE'])) : 'Unknown';
$mysql_version      = $wpdb->db_version();
$php_memory_limit   = ini_get('memory_limit') ?: 'N/A';
$wp_memory_limit    = defined('WP_MEMORY_LIMIT') ? WP_MEMORY_LIMIT : '40M';
$max_exec_time      = ini_get('max_execution_time') ? ini_get('max_execution_time') . 's' : 'N/A';
$upload_max_filesize= ini_get('upload_max_filesize') ?: 'N/A';
$post_max_size      = ini_get('post_max_size') ?: 'N/A';
$curl_status        = function_exists('curl_version') ? 'Enabled (' . curl_version()['version'] . ')' : 'Disabled';
$openssl_status     = defined('OPENSSL_VERSION_TEXT') ? OPENSSL_VERSION_TEXT : 'Disabled';
$gd_status          = extension_loaded('gd') ? 'Enabled' : 'Disabled';
$json_status        = extension_loaded('json') ? 'Enabled' : 'Disabled';
$multisite_status   = is_multisite() ? 'Yes' : 'No';
$wp_debug_status    = defined('WP_DEBUG') && WP_DEBUG ? 'Enabled' : 'Disabled';
$plugin_version     = defined('ZAPPOPPOP_VERSION') ? ZAPPOPPOP_VERSION : '1.0.0';

// Database Entity Counts
$popups_count       = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}zappoppop_items");
$leads_count        = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}zappoppop_submissions");

// Health Validations
$php_is_optimal     = version_compare($php_version, '7.4', '>=');
$mem_val            = (int)preg_replace('/[^0-9]/', '', $php_memory_limit);
$mem_bytes         = ((string) $php_memory_limit === '-1') ? -1 : (int) wp_convert_hr_to_bytes($php_memory_limit);
$memory_is_optimal = ($mem_bytes === -1 || $mem_bytes >= 128 * MB_IN_BYTES);

// Build Formatted Markdown Diagnostic Text
$report_buffer  = "### ZapPopPop System & Diagnostics Audit\n";
$report_buffer .= "- Generated: " . current_time('mysql') . "\n";
$report_buffer .= "- Site URL: " . get_site_url() . "\n";
$report_buffer .= "- ZapPopPop Version: " . $plugin_version . "\n";
$report_buffer .= "- WordPress Version: " . $wp_version . "\n";
$report_buffer .= "- Multisite: " . $multisite_status . "\n";
$report_buffer .= "- WP Debug: " . $wp_debug_status . "\n";
$report_buffer .= "- Web Server: " . $server_software . "\n";
$report_buffer .= "- PHP Version: " . $php_version . "\n";
$report_buffer .= "- PHP Memory Limit: " . $php_memory_limit . "\n";
$report_buffer .= "- WP Memory Limit: " . $wp_memory_limit . "\n";
$report_buffer .= "- PHP Max Execution Time: " . $max_exec_time . "\n";
$report_buffer .= "- Upload Max Filesize: " . $upload_max_filesize . "\n";
$report_buffer .= "- Post Max Size: " . $post_max_size . "\n";
$report_buffer .= "- MySQL Server Version: " . $mysql_version . "\n";
$report_buffer .= "- cURL: " . $curl_status . "\n";
$report_buffer .= "- OpenSSL: " . $openssl_status . "\n";
$report_buffer .= "- GD Library: " . $gd_status . "\n";
$report_buffer .= "- JSON Extension: " . $json_status . "\n";
$report_buffer .= "- Total Popups: " . $popups_count . "\n";
$report_buffer .= "- Captured Submissions: " . $leads_count . "\n";
$report_buffer .= (string) apply_filters('zappoppop_system_report_lines', '');
?>
<?php
$sys_groups = [
    __('WordPress Core', 'zappoppop') => [
        [__('WordPress Version', 'zappoppop'), $wp_version, null],
        [__('ZapPopPop Version', 'zappoppop'), $plugin_version, null],
        [__('Multisite Network', 'zappoppop'), $multisite_status, null],
        [__('WP Debug Mode', 'zappoppop'), $wp_debug_status, null],
    ],
    __('PHP & Web Server', 'zappoppop') => [
        [__('PHP Version', 'zappoppop'), $php_version, $php_is_optimal ? ['ok', 'Optimal'] : ['warn', 'Attention']],
        [__('PHP Memory Limit', 'zappoppop'), $php_memory_limit, $memory_is_optimal ? ['ok', 'Optimal'] : ['warn', 'Low']],
        [__('WP Memory Limit', 'zappoppop'), $wp_memory_limit, null],
        [__('Max Execution Time', 'zappoppop'), $max_exec_time, null],
    ],
    __('Uploads & Extensions', 'zappoppop') => [
        [__('Upload Max Filesize', 'zappoppop'), $upload_max_filesize, null],
        [__('Post Max Size', 'zappoppop'), $post_max_size, null],
        [__('MySQL Version', 'zappoppop'), $mysql_version, null],
        [__('cURL Extension', 'zappoppop'), $curl_status, null],
        [__('GD Library', 'zappoppop'), $gd_status, null],
    ],
];
?>
<div class="zappoppop-card-box zpp-tools-card">
    <div class="zpp-tools-card-head">
        <span class="dashicons dashicons-dashboard"></span>
        <h3><?php esc_html_e('System Environment & Health Diagnostics', 'zappoppop'); ?></h3>
        <div class="zpp-tools-head-actions">
            <button type="button" id="zappoppop-copy-system-info-btn" class="button zpp-tools-btn"><?php esc_html_e('Copy Diagnostic Report', 'zappoppop'); ?></button>
            <button type="button" id="zappoppop-download-system-info-btn" class="button zpp-tools-btn" title="<?php esc_attr_e('Download diagnostic report as a .txt file', 'zappoppop'); ?>"><?php esc_html_e('Download .txt', 'zappoppop'); ?></button>
        </div>
    </div>

    <textarea id="zappoppop-system-report-text" class="zpp-tools-hidden" readonly aria-hidden="true" tabindex="-1"><?php echo esc_textarea($report_buffer); ?></textarea>

    <div class="zpp-tools-grid zpp-tools-grid-3">
        <?php foreach ($sys_groups as $group_title => $rows) : ?>
            <div class="zpp-tools-panel">
                <h4><?php echo esc_html($group_title); ?></h4>
                <div class="zpp-kv">
                    <?php foreach ($rows as $row) : ?>
                        <div class="zpp-kv-row">
                            <span class="zpp-kv-label"><?php echo esc_html($row[0]); ?>:</span>
                            <span class="zpp-kv-value">
                                <strong><?php echo esc_html($row[1]); ?></strong>
                                <?php if ($row[2]) : ?>
                                    <span class="zpp-pill is-<?php echo esc_attr($row[2][0]); ?>"><?php echo esc_html($row[2][1]); ?></span>
                                <?php endif; ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
