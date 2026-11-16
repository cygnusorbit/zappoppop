<?php
if (!defined('ABSPATH')) {
    exit;
}

class ZapPopPop_Admin_Pages {
    public function render_dashboard() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'zappoppop_items';
        $popups = /* phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching */ $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}zappoppop_items ORDER BY id DESC" );
        include ZAPPOPPOP_PATH . 'templates/dashboard-view.php';
    }

    public function render_overview() {
        global $wpdb;
        $items = $wpdb->prefix . 'zappoppop_items';
        $dash_menu_popups   = /* phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching */ $wpdb->get_results( "SELECT uid, title FROM {$wpdb->prefix}zappoppop_items ORDER BY id DESC LIMIT 30" );
        $dash_recent_popups = /* phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching */ $wpdb->get_results( "SELECT uid, title, submissions FROM {$wpdb->prefix}zappoppop_items ORDER BY id DESC LIMIT 5" );
        $subs = $wpdb->prefix . 'zappoppop_submissions';
        $dash_recent_subs = $wpdb->get_results("SELECT s.id, s.popup_uid, s.email, s.status, s.is_read, s.fields_data, s.created_at, i.title AS popup_title FROM {$wpdb->prefix}zappoppop_submissions s LEFT JOIN {$wpdb->prefix}zappoppop_items i ON s.popup_uid = i.uid ORDER BY s.id DESC LIMIT 6");
        $dash_export_url  = add_query_arg([
            'action' => 'zappoppop_export_submissions',
            'nonce'  => wp_create_nonce('zappoppop_admin_nonce'),
        ], admin_url('admin-post.php'));
        include ZAPPOPPOP_PATH . 'templates/dashboard-overview.php';
    }

    public function render_builder() {
        global $wpdb;
        $uid = isset($_GET['uid']) ? sanitize_text_field(wp_unslash($_GET['uid'])) : '';
        $popup = null;
        if (!empty($uid)) {
            $table_name = $wpdb->prefix . 'zappoppop_items';
            $popup = /* phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching */ $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}zappoppop_items WHERE uid = %s", $uid ) );
        }
        include ZAPPOPPOP_PATH . 'templates/builder-view.php';
    }

    public function render_submissions() {
        global $wpdb;
        $filter_uid = isset($_GET['popup_uid']) ? sanitize_text_field(wp_unslash($_GET['popup_uid'])) : '';
        $where = $filter_uid !== '' ? $wpdb->prepare(' WHERE s.popup_uid = %s', $filter_uid) : '';
        $submissions = /* phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching */ $wpdb->get_results("SELECT s.*, i.title as popup_title FROM {$wpdb->prefix}zappoppop_submissions s LEFT JOIN {$wpdb->prefix}zappoppop_items i ON s.popup_uid = i.uid{$where} ORDER BY s.id DESC LIMIT 200");
        include ZAPPOPPOP_PATH . 'templates/submissions-view.php';
    }

    public function render_log() {
        include ZAPPOPPOP_PATH . 'templates/log-view.php';
    }

    public function render_field_analytics() {
        include ZAPPOPPOP_PATH . 'templates/field-analytics-view.php';
    }

    public function render_payments() {
        global $wpdb;
        $transactions = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}zappoppop_transactions ORDER BY id DESC LIMIT 100");
        include ZAPPOPPOP_PATH . 'templates/payments-view.php';
    }

    public function render_library() {
        include ZAPPOPPOP_PATH . 'templates/library-view.php';
    }

    public function render_settings() {
        if (!current_user_can('manage_options')) {
            wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', "zappoppop" ) );
        }

        $notice = '';
        if (isset($_SERVER['REQUEST_METHOD']) && 'POST' === $_SERVER['REQUEST_METHOD'] && isset($_POST['zappoppop_settings_nonce'])) {
            if (check_admin_referer('zappoppop_save_settings_action', 'zappoppop_settings_nonce')) {
                /* phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitised key by key by sanitize_settings() */ $raw = isset($_POST['settings']) && is_array($_POST['settings']) ? wp_unslash($_POST['settings']) : wp_unslash($_POST);
                $existing = get_option('zappoppop_settings', []);
                if (!is_array($existing)) {
                    $existing = [];
                }

                if (!class_exists('ZapPopPop_Admin_Settings')) {
                    require_once ZAPPOPPOP_PATH . 'includes/admin/class-admin-settings.php';
                }

                // Cookie epoch is server-owned: never trust the client value.
                $raw['cookie_epoch'] = isset($existing['cookie_epoch'])
                    ? (int) $existing['cookie_epoch']
                    : (int) get_option('zappoppop_cookie_epoch', 1);

                $clean = ZapPopPop_Admin_Settings::sanitize_settings($raw);

                update_option('zappoppop_settings', $clean);
                if (function_exists('zappoppop_flush_settings_cache')) {
                    zappoppop_flush_settings_cache();
                }
                $notice = 'saved';
            }
        }

        $settings = get_option('zappoppop_settings', []);
        include ZAPPOPPOP_PATH . 'templates/settings-view.php';
    }

    public function render_theme() {
        if (!current_user_can('manage_options')) {
            wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', "zappoppop" ) );
        }
        include ZAPPOPPOP_PATH . 'templates/theme-view.php';
    }

    public function render_extend() {
        if (!current_user_can('manage_options')) {
            wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', "zappoppop" ) );
        }
        include ZAPPOPPOP_PATH . 'templates/extend-view.php';
    }

    public function render_tools() {
        include ZAPPOPPOP_PATH . 'templates/tools-view.php';
    }
}
