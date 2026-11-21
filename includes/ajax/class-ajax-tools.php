<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('ZapPopPop_Sanitizer')) {
    require_once ZAPPOPPOP_PATH . 'includes/class-zappoppop-sanitizer.php';
}

/**
 * Tools domain: backup/restore service, schema repair, counters, maintenance.
 * One import/export implementation shared by the AJAX and the native-POST paths.
 */
class ZapPopPop_Ajax_Tools {
    const MAX_IMPORT_BYTES = 10485760; // 10 MB

    public function __construct() {
        add_action('wp_ajax_zappoppop_bulk_export', [$this, 'bulk_export']);
        add_action('admin_post_zappoppop_bulk_export', [$this, 'bulk_export_download']);
        add_action('wp_ajax_zappoppop_bulk_import', [$this, 'bulk_import']);
        add_action('wp_ajax_zappoppop_repair_tables', [$this, 'repair_tables']);
        add_action('wp_ajax_zappoppop_reset_counters', [$this, 'reset_counters']);
        add_action('wp_ajax_zappoppop_purge_logs', [$this, 'purge_logs']);
        add_action('wp_ajax_zappoppop_optimize_tables', [$this, 'optimize_tables']);
        add_action('wp_ajax_zappoppop_orphan_report', [$this, 'orphan_report']);
    }

    private function verify_security() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized action.', 'zappoppop')]);
        }
        $nonce = isset($_REQUEST['nonce']) ? sanitize_text_field(wp_unslash($_REQUEST['nonce'])) : '';
        if (!wp_verify_nonce($nonce, 'zappoppop_builder_nonce') && !wp_verify_nonce($nonce, 'zappoppop_admin_nonce')) {
            wp_send_json_error(['message' => __('Security check failed. Please refresh the page.', 'zappoppop')]);
        }
    }

    /* ------------------------------------------------------ service */
    /** Table key => [label, name]. Companion plugins add their own tables with the zappoppop_tools_tables filter. */
    public static function table_defs() {
        global $wpdb;
        $defs = [
            'items'        => ['label' => __('Popups & Designs', 'zappoppop'), 'name' => $wpdb->prefix . 'zappoppop_items'],
            'submissions'  => ['label' => __('Captured Leads', 'zappoppop'), 'name' => $wpdb->prefix . 'zappoppop_submissions'],
            'logs'         => ['label' => __('Audit & System Logs', 'zappoppop'), 'name' => $wpdb->prefix . 'zappoppop_logs'],
            'transactions' => ['label' => __('Payment Records', 'zappoppop'), 'name' => $wpdb->prefix . 'zappoppop_transactions'],
        ];
        $filtered = apply_filters('zappoppop_tools_tables', $defs);
        return is_array($filtered) ? $filtered : $defs;
    }

    public static function tables() {
        $out = [];
        foreach (self::table_defs() as $key => $def) {
            $out[$key] = $def['name'];
        }
        return $out;
    }

    public static function table_status() {
        global $wpdb;
        $out = [];
        foreach (self::tables() as $key => $name) {
            $exists = ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($name))) === $name);
            $rows = 0;
            $size = '0 KB';
            if ($exists) {
                $rows = (int) /* phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching */ $wpdb->get_var("SELECT COUNT(*) FROM `{$name}`");
                $st = $wpdb->get_row($wpdb->prepare('SHOW TABLE STATUS LIKE %s', $wpdb->esc_like($name)), ARRAY_A);
                if ($st && isset($st['Data_length'])) {
                    $size = size_format((int) $st['Data_length'] + (int) $st['Index_length'], 2);
                }
            }
            $out[$key] = [
                'table'  => $name,
                'exists' => $exists,
                'rows'   => number_format_i18n($rows),
                'size'   => $size,
                'status' => $exists ? 'Optimal' : 'Missing',
            ];
        }
        return $out;
    }

    public static function build_export() {
        global $wpdb;
        $items     = $wpdb->get_results("SELECT uid, title, data, status FROM {$wpdb->prefix}zappoppop_items ORDER BY id ASC", ARRAY_A);
        $settings  = get_option('zappoppop_settings', []);
        $theme     = get_option(class_exists('ZapPopPop_Admin_Theme') ? ZapPopPop_Admin_Theme::OPTION : 'zappoppop_theme', []);
        $out = [
            'generator' => 'ZapPopPop ' . (defined('ZAPPOPPOP_VERSION') ? ZAPPOPPOP_VERSION : '1.0.0'),
            'format'    => 2,
            'exported'  => current_time('mysql'),
            'items'     => $items ? $items : [],
            'settings'  => is_array($settings) ? $settings : [],
            'theme'     => is_array($theme) ? $theme : [],
        ];
        $filtered = apply_filters('zappoppop_export_data', $out);
        return is_array($filtered) ? $filtered : $out;
    }

    /** Keep the original uid (so existing embeds keep working) unless it is taken. */
    private static function free_uid($wanted, $title) {
        global $wpdb;
        $t = $wpdb->prefix . 'zappoppop_items';
        $wanted = sanitize_key($wanted);
        /* phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- table name is built from $wpdb->prefix and every value is prepared */ if ($wanted !== '' && !$wpdb->get_var($wpdb->prepare("SELECT COUNT(1) FROM {$t} WHERE uid = %s", $wanted))) {
            return $wanted;
        }
        $base = trim(preg_replace('/[^a-z0-9-]+/', '-', strtolower(sanitize_title((string) $title))), '-');
        $base = rtrim(substr($base, 0, 29), '-');
        if ($base === '') {
            $base = 'popup';
        }
        $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
        for ($try = 0; $try < 30; $try++) {
            $s = '';
            for ($i = 0; $i < 6; $i++) {
                $s .= $chars[wp_rand(0, strlen($chars) - 1)];
            }
            $cand = $base . '-' . $s;
            /* phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- table name is built from $wpdb->prefix and every value is prepared */ if (!$wpdb->get_var($wpdb->prepare("SELECT COUNT(1) FROM {$t} WHERE uid = %s", $cand))) {
                return $cand;
            }
        }
        return substr($base, 0, 20) . '-' . strtolower(wp_generate_password(6, false, false));
    }

    public static function import_payload($raw, $dry_run = false, $with_settings = false) {
        global $wpdb;
        if (!is_string($raw) || $raw === '') {
            return new WP_Error('empty', __('No backup data was provided for restoration.', 'zappoppop'));
        }
        if (strlen($raw) > self::MAX_IMPORT_BYTES) {
            return new WP_Error('size', __('Backup file is too large (10 MB maximum).', 'zappoppop'));
        }
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) { foreach (array('settings', 'theme') as $zappoppop_k) { if (isset($decoded[$zappoppop_k]) && is_array($decoded[$zappoppop_k])) { $decoded[$zappoppop_k] = ZapPopPop_Legacy_Import::convert($decoded[$zappoppop_k]); } } } /* accept exports from the previous generation */
        $has_data = is_array($decoded) && !empty($decoded['items']);
        $has_data = (bool) apply_filters('zappoppop_import_has_data', $has_data, $decoded);
        if (!is_array($decoded) || !$has_data) {
            return new WP_Error('format', __('Invalid or unrecognized backup format.', 'zappoppop'));
        }

        $res = ['items' => 0, 'campaigns' => 0, 'skipped' => 0, 'renamed' => 0, 'settings' => false, 'dry_run' => (bool) $dry_run];
        $uid_map = [];
        $items_table = $wpdb->prefix . 'zappoppop_items';
        if (!$dry_run) { $wpdb->query('START TRANSACTION'); }

        if (!empty($decoded['items']) && is_array($decoded['items'])) {
            foreach ($decoded['items'] as $item) {
                if (!is_array($item)) { $res['skipped']++; continue; }
                $title = isset($item['title']) ? sanitize_text_field($item['title']) : '';
                if ($title === '') { $title = __('Restored Popup', 'zappoppop'); }
                $data = isset($item['data']) ? $item['data'] : '{}';
                if (is_array($data) || is_object($data)) { $data = wp_json_encode($data); }
                $zappoppop_chk = is_string($data) ? json_decode($data) : null; /* ZAPPOPPOP_SUBMISSION_FILES */
                if (!is_object($zappoppop_chk) && !is_array($zappoppop_chk)) { $res['skipped']++; continue; }
                $obj = json_decode($data);
                if (is_object($obj) || is_array($obj)) { $obj = ZapPopPop_Legacy_Import::convert($obj); } /* accept exports from the previous generation */
                if (is_object($obj) || is_array($obj)) {
                    if (!class_exists('ZapPopPop_Sanitizer')) { require_once ZAPPOPPOP_PATH . 'includes/class-zappoppop-sanitizer.php'; }
                    $enc = wp_json_encode(ZapPopPop_Sanitizer::anim_payload(ZapPopPop_Sanitizer::popup_payload($obj)));
                    if (is_string($enc) && $enc !== '') { $data = $enc; }
                }
                $status = (isset($item['status']) && in_array($item['status'], ['publish', 'draft'], true)) ? $item['status'] : 'draft';
                $old = isset($item['uid']) ? sanitize_key($item['uid']) : '';
                $new = self::free_uid($old, $title);
                if ($old !== '' && $new !== $old) { $res['renamed']++; }
                if ($old !== '') { $uid_map[$old] = $new; }
                if (!$dry_run) {
                    $ok = $wpdb->insert($items_table, [
                        'uid' => $new, 'title' => $title, 'data' => $data,
                        'status' => $status, 'impressions' => 0, 'submissions' => 0, 'confirmations' => 0,
                    ], ['%s', '%s', '%s', '%s', '%d', '%d', '%d']);
                    if ($ok === false) { $wpdb->query('ROLLBACK'); return new WP_Error('db', __('Database error during restore. No changes were kept.', 'zappoppop')); }
                }
                $res['items']++;
            }
        }

        /* Companion plugins restore their own data here. A WP_Error rolls the whole import back. */
        $extra = apply_filters('zappoppop_import_extra', ['campaigns' => 0, 'skipped' => 0], $decoded, $uid_map, (bool) $dry_run);
        if (is_wp_error($extra)) {
            if (!$dry_run) { $wpdb->query('ROLLBACK'); }
            return $extra;
        }
        if (is_array($extra)) {
            $res['campaigns'] = (int) (isset($extra['campaigns']) ? $extra['campaigns'] : 0);
            $res['skipped']  += (int) (isset($extra['skipped']) ? $extra['skipped'] : 0);
        }

        if (!$dry_run) { $wpdb->query('COMMIT'); }

        if ($with_settings && !$dry_run) {
            if (!empty($decoded['settings']) && is_array($decoded['settings']) && class_exists('ZapPopPop_Admin_Settings')) {
                $existing = get_option('zappoppop_settings', []);
                $incoming = $decoded['settings'];
                $incoming['cookie_epoch'] = is_array($existing) && isset($existing['cookie_epoch']) ? (int) $existing['cookie_epoch'] : (int) get_option('zappoppop_cookie_epoch', 1);
                update_option('zappoppop_settings', ZapPopPop_Admin_Settings::sanitize_settings($incoming));
                if (function_exists('zappoppop_flush_settings_cache')) { zappoppop_flush_settings_cache(); }
                $res['settings'] = true;
            }
            if (!empty($decoded['theme']) && is_array($decoded['theme']) && class_exists('ZapPopPop_Admin_Theme')) {
                update_option(ZapPopPop_Admin_Theme::OPTION, ZapPopPop_Admin_Theme::sanitize_all($decoded['theme']));
                $res['settings'] = true;
            }
        }
        return $res;
    }

    private static function summary($r) {
        /* translators: 1: action label (e.g. Successfully restored), 2: number of popups. */
        $msg = sprintf(__('%1$s %2$d popups', 'zappoppop'), !empty($r['dry_run']) ? __('Preview: would restore', 'zappoppop') : __('Successfully restored', 'zappoppop'), $r['items']);
        if (!empty($r['campaigns'])) {
            /* translators: %d: number of A/B campaigns. */
            $msg .= sprintf(__(' and %d A/B campaigns', 'zappoppop'), (int) $r['campaigns']);
        }
        if (!empty($r['renamed'])) { $msg .= sprintf(' (%d uid(s) changed because they already exist)', $r['renamed']); }
        if (!empty($r['skipped'])) { $msg .= sprintf('; %d entr%s skipped as invalid', $r['skipped'], $r['skipped'] === 1 ? 'y' : 'ies'); }
        if (!empty($r['settings'])) { $msg .= '; settings/theme applied'; }
        return $msg . '.';
    }

    public static function repair() {
        require_once ZAPPOPPOP_PATH . 'includes/class-zappoppop-installer.php';
        ZapPopPop_Installer::create_tables();
        $status  = self::table_status();
        $missing = array_filter($status, function ($t) { return empty($t['exists']); });
        $msg = count($missing)
            ? __('Some tables are still missing after repair. Check that the database user has CREATE/ALTER privileges.', 'zappoppop')
            : __('All database tables successfully verified and schema confirmed healthy!', 'zappoppop');
        return [$msg, $status, count($missing) === 0];
    }

    public static function reset() {
        global $wpdb;
        $n = $wpdb->query("UPDATE `{$wpdb->prefix}zappoppop_items` SET impressions = 0, submissions = 0, confirmations = 0");
        return $n !== false ? (int) $n : 0;
    }

    /** Native POST path used by templates/tools-view.php. Returns [message, type]. */
    public static function handle_post() {
        if (!current_user_can('manage_options')) {
            return [__('Unauthorized.', 'zappoppop'), 'error'];
        }
        check_admin_referer('zappoppop_tools_action', 'zappoppop_tools_nonce');
        if (isset($_POST['zappoppop_repair_tables'])) {
            $r = self::repair();
            return [$r[0], $r[2] ? 'success' : 'error'];
        }
        if (isset($_POST['zappoppop_reset_counters'])) {
            self::reset();
            return [__('All popup impressions, leads, and confirmation counters have been reset to 0.', 'zappoppop'), 'success'];
        }
        if (isset($_POST['zappoppop_bulk_import'])) {
            $raw = '';
            $zappoppop_bulk_file = ZapPopPop_Sanitizer::uploaded_file('zappoppop_bulk_import_file');
            $zappoppop_tmp = $zappoppop_bulk_file ? $zappoppop_bulk_file['tmp_name'] : '';
            if (is_string($zappoppop_tmp) && $zappoppop_tmp !== '' && is_uploaded_file($zappoppop_tmp)) {
                if (filesize($zappoppop_tmp) > self::MAX_IMPORT_BYTES) {
                    return [__('Backup file is too large (10 MB maximum).', 'zappoppop'), 'error'];
                }
                /* phpcs:ignore WordPress.WP.AlternativeFunctions -- reads a local plugin or upload temp file, not a remote URL */ $raw = (string) file_get_contents($zappoppop_tmp);
            }
            $r = self::import_payload($raw, false, !empty($_POST['import_settings']));
            return is_wp_error($r) ? [$r->get_error_message(), 'error'] : [self::summary($r), 'success'];
        }
        return ['', 'success'];
    }

    /* -------------------------------------------------------- AJAX */
    public function bulk_export() {
        $this->verify_security();
        wp_send_json_success([
            'filename' => 'zappoppop-bulk-backup-' . gmdate('Y-m-d') . '.json',
            'payload'  => self::build_export(),
        ]);
    }

    public function bulk_export_download() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Unauthorized action.', 'zappoppop'), '', ['response' => 403]);
        }
        check_admin_referer('zappoppop_tools_action', 'zappoppop_tools_nonce');
        while (ob_get_level()) {
            ob_end_clean();
        }
        nocache_headers();
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="zappoppop-bulk-backup-' . gmdate('Y-m-d') . '.json"');
        echo wp_json_encode(self::build_export(), JSON_PRETTY_PRINT);
        exit;
    }

    public function bulk_import() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Unauthorized user capability.', 'zappoppop' ) ), 403 );
            wp_die();
        }
        $zappoppop_verified = check_ajax_referer( 'zappoppop_nonce', 'nonce', false )
            || check_ajax_referer( 'zappoppop_nonce', '_ajax_nonce', false )
            || check_ajax_referer( 'zappoppop_admin', 'nonce', false )
            || check_ajax_referer( 'zappoppop_nonce', 'security', false )
            || check_ajax_referer( 'zappoppop_builder_nonce', 'nonce', false )
            || check_ajax_referer( 'zappoppop_admin_nonce', 'nonce', false )
            || check_ajax_referer( 'zappoppop_settings_nonce', 'nonce', false );
        if ( ! $zappoppop_verified ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Invalid security token.', 'zappoppop' ) ), 403 );
            wp_die();
        }

        $this->verify_security();
        $raw = '';
        $zappoppop_bulk_file = ZapPopPop_Sanitizer::uploaded_file('zappoppop_bulk_import_file');
            $zappoppop_tmp = $zappoppop_bulk_file ? $zappoppop_bulk_file['tmp_name'] : '';
        if (is_string($zappoppop_tmp) && $zappoppop_tmp !== '') {
            if (!is_uploaded_file($zappoppop_tmp)) {
                wp_send_json_error(['message' => __('The uploaded file could not be read.', 'zappoppop')]);
            }
            if (filesize($zappoppop_tmp) > self::MAX_IMPORT_BYTES) {
                wp_send_json_error(['message' => __('Backup file is too large (10 MB maximum).', 'zappoppop')]);
            }
            /* phpcs:ignore WordPress.WP.AlternativeFunctions -- reads a local plugin or upload temp file, not a remote URL */ $raw = (string) file_get_contents($zappoppop_tmp);
        } elseif (!empty($_POST['import_data'])) {
            $raw = ZapPopPop_Sanitizer::post_json_string('import_data'); // validated JSON text; every popup is deep-sanitised in import_payload()
        }
        $r = self::import_payload($raw, !empty($_POST['dry_run']), !empty($_POST['import_settings']));
        if (is_wp_error($r)) {
            wp_send_json_error(['message' => $r->get_error_message()]);
        }
        wp_send_json_success([
            'message'            => self::summary($r),
            'imported_items'     => $r['items'],
            'imported_campaigns' => $r['campaigns'],
            'dry_run'            => $r['dry_run'],
        ]);
    }

    public function repair_tables() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Unauthorized user capability.', 'zappoppop' ) ), 403 );
            wp_die();
        }
        $zappoppop_verified = check_ajax_referer( 'zappoppop_nonce', 'nonce', false )
            || check_ajax_referer( 'zappoppop_nonce', '_ajax_nonce', false )
            || check_ajax_referer( 'zappoppop_admin', 'nonce', false )
            || check_ajax_referer( 'zappoppop_nonce', 'security', false )
            || check_ajax_referer( 'zappoppop_builder_nonce', 'nonce', false )
            || check_ajax_referer( 'zappoppop_admin_nonce', 'nonce', false )
            || check_ajax_referer( 'zappoppop_settings_nonce', 'nonce', false );
        if ( ! $zappoppop_verified ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Invalid security token.', 'zappoppop' ) ), 403 );
            wp_die();
        }

        $this->verify_security();
        $r = self::repair();
        if (!$r[2]) {
            wp_send_json_error(['message' => $r[0], 'tables' => $r[1]]);
        }
        wp_send_json_success(['message' => $r[0], 'tables' => $r[1]]);
    }

    public function reset_counters() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Unauthorized user capability.', 'zappoppop' ) ), 403 );
            wp_die();
        }
        $zappoppop_verified = check_ajax_referer( 'zappoppop_nonce', 'nonce', false )
            || check_ajax_referer( 'zappoppop_nonce', '_ajax_nonce', false )
            || check_ajax_referer( 'zappoppop_admin', 'nonce', false )
            || check_ajax_referer( 'zappoppop_nonce', 'security', false )
            || check_ajax_referer( 'zappoppop_builder_nonce', 'nonce', false )
            || check_ajax_referer( 'zappoppop_admin_nonce', 'nonce', false )
            || check_ajax_referer( 'zappoppop_settings_nonce', 'nonce', false );
        if ( ! $zappoppop_verified ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Invalid security token.', 'zappoppop' ) ), 403 );
            wp_die();
        }

        $this->verify_security();
        wp_send_json_success([
            'message'  => __('All popup impressions, leads captured, and confirmation counters have been reset to 0.', 'zappoppop'),
            'affected' => self::reset(),
        ]);
    }

    public function purge_logs() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Unauthorized user capability.', 'zappoppop' ) ), 403 );
            wp_die();
        }
        $zappoppop_verified = check_ajax_referer( 'zappoppop_nonce', 'nonce', false )
            || check_ajax_referer( 'zappoppop_nonce', '_ajax_nonce', false )
            || check_ajax_referer( 'zappoppop_admin', 'nonce', false )
            || check_ajax_referer( 'zappoppop_nonce', 'security', false )
            || check_ajax_referer( 'zappoppop_builder_nonce', 'nonce', false )
            || check_ajax_referer( 'zappoppop_admin_nonce', 'nonce', false )
            || check_ajax_referer( 'zappoppop_settings_nonce', 'nonce', false );
        if ( ! $zappoppop_verified ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Invalid security token.', 'zappoppop' ) ), 403 );
            wp_die();
        }

        $this->verify_security();
        global $wpdb;
        $days = isset($_POST['days']) ? max(1, min(3650, (int) $_POST['days'])) : 30;
        $n = $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}zappoppop_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)", $days));
        wp_send_json_success(['message' => sprintf(
            /* translators: 1: number of log entries removed, 2: number of days. */
            _n('Removed %1$d log entry older than %2$d days.', 'Removed %1$d log entries older than %2$d days.', (int) $n, 'zappoppop'),
            (int) $n,
            $days
        )]);
    }

    public function optimize_tables() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Unauthorized user capability.', 'zappoppop' ) ), 403 );
            wp_die();
        }
        $zappoppop_verified = check_ajax_referer( 'zappoppop_nonce', 'nonce', false )
            || check_ajax_referer( 'zappoppop_nonce', '_ajax_nonce', false )
            || check_ajax_referer( 'zappoppop_admin', 'nonce', false )
            || check_ajax_referer( 'zappoppop_nonce', 'security', false )
            || check_ajax_referer( 'zappoppop_builder_nonce', 'nonce', false )
            || check_ajax_referer( 'zappoppop_admin_nonce', 'nonce', false )
            || check_ajax_referer( 'zappoppop_settings_nonce', 'nonce', false );
        if ( ! $zappoppop_verified ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Invalid security token.', 'zappoppop' ) ), 403 );
            wp_die();
        }

        $this->verify_security();
        global $wpdb;
        $done = 0;
        foreach (self::table_status() as $t) {
            if (!empty($t['exists'])) {
                // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                $wpdb->query('OPTIMIZE TABLE `' . $t['table'] . '`');
                $done++;
            }
        }
        wp_send_json_success(['message' => sprintf(
            /* translators: %d: number of database tables optimized. */
            _n('Optimized %d table.', 'Optimized %d tables.', (int) $done, 'zappoppop'),
            (int) $done
        ), 'tables' => self::table_status()]);
    }

    /** Read-only: nothing is deleted. */
    public function orphan_report() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Unauthorized user capability.', 'zappoppop' ) ), 403 );
            wp_die();
        }
        $zappoppop_verified = check_ajax_referer( 'zappoppop_nonce', 'nonce', false )
            || check_ajax_referer( 'zappoppop_nonce', '_ajax_nonce', false )
            || check_ajax_referer( 'zappoppop_admin', 'nonce', false )
            || check_ajax_referer( 'zappoppop_nonce', 'security', false )
            || check_ajax_referer( 'zappoppop_builder_nonce', 'nonce', false )
            || check_ajax_referer( 'zappoppop_admin_nonce', 'nonce', false )
            || check_ajax_referer( 'zappoppop_settings_nonce', 'nonce', false );
        if ( ! $zappoppop_verified ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Invalid security token.', 'zappoppop' ) ), 403 );
            wp_die();
        }

        $this->verify_security();
        global $wpdb;
        $p = $wpdb->prefix;
        $subs  = (int) /* phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- table name is built from $wpdb->prefix and the query takes no user input */ $wpdb->get_var("SELECT COUNT(*) FROM {$p}zappoppop_submissions s LEFT JOIN {$p}zappoppop_items i ON s.popup_uid = i.uid WHERE i.uid IS NULL");
        $trans = (int) /* phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- table name is built from $wpdb->prefix and the query takes no user input */ $wpdb->get_var("SELECT COUNT(*) FROM {$p}zappoppop_transactions t LEFT JOIN {$p}zappoppop_items i ON t.popup_uid = i.uid WHERE i.uid IS NULL");
        wp_send_json_success(['message' => sprintf(
            /* translators: 1: number of orphaned submissions, 2: number of orphaned transactions. */
            __('Orphaned records (popup no longer exists): %1$d submissions, %2$d transactions. Nothing was deleted.', 'zappoppop'),
            $subs,
            $trans
        )]);
    }
}
