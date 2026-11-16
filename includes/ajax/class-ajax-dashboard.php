<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Dashboard (Popups list) AJAX: Quick Edit + Active toggle.
 * Whitelist: uid (sanitize_key), title (text), status (publish|draft only).
 */
class ZapPopPop_Ajax_Dashboard {
    public function __construct() {
        add_action('wp_ajax_zappoppop_quick_edit', [$this, 'quick_edit']);
        add_action('wp_ajax_zappoppop_toggle_status', [$this, 'toggle_status']);
        add_action('wp_ajax_zappoppop_export_selected', [$this, 'export_selected']);
    }

    private function verify_nonce() {
        $nonce = isset($_REQUEST['nonce']) ? sanitize_text_field(wp_unslash($_REQUEST['nonce'])) : '';
        if (!wp_verify_nonce($nonce, 'zappoppop_admin_nonce') && !wp_verify_nonce($nonce, 'zappoppop_builder_nonce')) {
            wp_send_json_error(['message' => __('Security check failed. Please refresh the page.', 'zappoppop')], 403);
        }
    }

    private function can($cap, $uid = '') {
        return current_user_can('manage_options') || ($uid !== '' ? current_user_can($cap, $uid) : current_user_can($cap));
    }

    private function counts() {
        global $wpdb;
        $rows = $wpdb->get_col("SELECT status FROM {$wpdb->prefix}zappoppop_items");
        $c = ['all' => 0, 'active' => 0, 'inactive' => 0, 'trash' => 0];
        foreach ((array) $rows as $st) {
            if ($st === 'trash') { $c['trash']++; continue; }
            $c['all']++;
            if ($st === 'publish' || $st === 'published') { $c['active']++; } else { $c['inactive']++; }
        }
        return $c;
    }

    private function load_row($uid) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT uid, title, status, data FROM {$wpdb->prefix}zappoppop_items WHERE uid = %s", $uid), ARRAY_A);
    }

    public function quick_edit() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Unauthorized user capability.', 'zappoppop' ) ), 403 );
            wp_die();
        }
        $zpp_verified = check_ajax_referer( 'zappoppop_nonce', 'nonce', false )
            || check_ajax_referer( 'zappoppop_nonce', '_ajax_nonce', false )
            || check_ajax_referer( 'zappoppop_admin', 'nonce', false )
            || check_ajax_referer( 'zappoppop_nonce', 'security', false )
            || check_ajax_referer( 'zappoppop_builder_nonce', 'nonce', false )
            || check_ajax_referer( 'zappoppop_admin_nonce', 'nonce', false )
            || check_ajax_referer( 'zappoppop_settings_nonce', 'nonce', false );
        if ( ! $zpp_verified ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Invalid security token.', 'zappoppop' ) ), 403 );
            wp_die();
        }

        $this->verify_nonce();
        global $wpdb;

        $uid    = isset($_POST['uid']) ? sanitize_key(wp_unslash($_POST['uid'])) : '';
        $title  = isset($_POST['title']) ? trim(sanitize_text_field(wp_unslash($_POST['title']))) : '';
        $status = isset($_POST['status']) ? sanitize_key(wp_unslash($_POST['status'])) : '';

        if ($uid === '' || !$this->can('edit_zappoppop_campaign', $uid)) {
            wp_send_json_error(['message' => __('You do not have permission to edit this campaign.', 'zappoppop')], 403);
        }
        if ($title === '') {
            wp_send_json_error(['message' => __('Title cannot be empty.', 'zappoppop')]);
        }
        if (!in_array($status, ['publish', 'draft'], true)) {
            wp_send_json_error(['message' => __('Invalid status.', 'zappoppop')]);
        }

        $row = $this->load_row($uid);
        if (!$row || $row['status'] === 'trash') {
            wp_send_json_error(['message' => __('Campaign not found.', 'zappoppop')]);
        }

        $current_on = in_array($row['status'], ['publish', 'published'], true);
        $new_on     = ($status === 'publish');
        if ($current_on !== $new_on && !$this->can('publish_zappoppop_campaigns')) {
            wp_send_json_error(['message' => __('You do not have permission to change campaign status.', 'zappoppop')], 403);
        }

        $update = ['title' => $title, 'status' => $status, 'updated_at' => current_time('mysql')];
        $fmt    = ['%s', '%s', '%s'];

        // Keep builder meta title in sync (if present).
        $decoded = json_decode((string) $row['data'], true);
        $zappoppop_sync = false;
        if (is_array($decoded)) {
            if (isset($decoded['meta']) && is_array($decoded['meta']) && array_key_exists('title', $decoded['meta'])) { $decoded['meta']['title'] = $title; $zappoppop_sync = true; }
            if (array_key_exists('title', $decoded)) { $decoded['title'] = $title; $zappoppop_sync = true; }
        }
        if ($zappoppop_sync) {
            $enc = wp_json_encode($decoded);
            if (is_string($enc) && $enc !== '') {
                $update['data'] = $enc;
                $fmt[] = '%s';
            }
        }

        $ok = $wpdb->update($wpdb->prefix . 'zappoppop_items', $update, ['uid' => $uid], $fmt, ['%s']);
        if ($ok === false) {
            wp_send_json_error(['message' => __('Database error while saving.', 'zappoppop')]);
        }

        require_once ZAPPOPPOP_PATH . 'includes/class-zappoppop-uid.php';
        $old_uid = $uid;
        $uid     = ZapPopPop_Uid::rename_for_title($uid, $title); /* ZAPPOPPOP_QE_UID_FOLLOWS_TITLE */

        wp_send_json_success([
            'uid'     => $uid,
            'old_uid' => $old_uid,
            'title'  => $title,
            'status' => $status,
            'counts' => $this->counts(),
        ]);
    }

    public function toggle_status() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Unauthorized user capability.', 'zappoppop' ) ), 403 );
            wp_die();
        }
        $zpp_verified = check_ajax_referer( 'zappoppop_nonce', 'nonce', false )
            || check_ajax_referer( 'zappoppop_nonce', '_ajax_nonce', false )
            || check_ajax_referer( 'zappoppop_admin', 'nonce', false )
            || check_ajax_referer( 'zappoppop_nonce', 'security', false )
            || check_ajax_referer( 'zappoppop_builder_nonce', 'nonce', false )
            || check_ajax_referer( 'zappoppop_admin_nonce', 'nonce', false )
            || check_ajax_referer( 'zappoppop_settings_nonce', 'nonce', false );
        if ( ! $zpp_verified ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Invalid security token.', 'zappoppop' ) ), 403 );
            wp_die();
        }

        $this->verify_nonce();
        global $wpdb;

        $uid    = isset($_POST['uid']) ? sanitize_key(wp_unslash($_POST['uid'])) : '';
        $active_raw = isset($_POST['active']) ? sanitize_text_field(wp_unslash($_POST['active'])) : '';
        $active     = !in_array($active_raw, ['', '0', 'false'], true);

        if ($uid === '' || !$this->can('publish_zappoppop_campaigns', $uid)) {
            wp_send_json_error(['message' => __('You do not have permission to change campaign status.', 'zappoppop')], 403);
        }
        $row = $this->load_row($uid);
        if (!$row || $row['status'] === 'trash') {
            wp_send_json_error(['message' => __('Campaign not found.', 'zappoppop')]);
        }

        $status = $active ? 'publish' : 'draft';
        $ok = $wpdb->update($wpdb->prefix . 'zappoppop_items', ['status' => $status, 'updated_at' => current_time('mysql')], ['uid' => $uid], ['%s', '%s'], ['%s']);
        if ($ok === false) {
            wp_send_json_error(['message' => __('Database error while saving.', 'zappoppop')]);
        }

        wp_send_json_success(['uid' => $uid, 'status' => $status, 'counts' => $this->counts()]);
    }

    public function export_selected() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Unauthorized user capability.', 'zappoppop' ) ), 403 );
            wp_die();
        }
        $zpp_verified = check_ajax_referer( 'zappoppop_nonce', 'nonce', false )
            || check_ajax_referer( 'zappoppop_nonce', '_ajax_nonce', false )
            || check_ajax_referer( 'zappoppop_admin', 'nonce', false )
            || check_ajax_referer( 'zappoppop_nonce', 'security', false )
            || check_ajax_referer( 'zappoppop_builder_nonce', 'nonce', false )
            || check_ajax_referer( 'zappoppop_admin_nonce', 'nonce', false )
            || check_ajax_referer( 'zappoppop_settings_nonce', 'nonce', false );
        if ( ! $zpp_verified ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Invalid security token.', 'zappoppop' ) ), 403 );
            wp_die();
        }

        $this->verify_nonce();
        if (!$this->can('edit_zappoppop_campaigns')) {
            wp_send_json_error(['message' => __('You do not have permission to export campaigns.', 'zappoppop')], 403);
        }
        global $wpdb;
        if (!class_exists('ZapPopPop_Sanitizer')) { require_once ZAPPOPPOP_PATH . 'includes/class-zappoppop-sanitizer.php'; }
        $raw  = ZapPopPop_Sanitizer::post_array('uids', 'POST', true);
        $uids = array_values(array_unique(array_filter(array_map('sanitize_key', $raw))));
        if (empty($uids)) {
            wp_send_json_error(['message' => __('No campaigns selected.', 'zappoppop')]);
        }
        $ph    = implode(',', array_fill(0, count($uids), '%s'));
        /* phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare, PluginCheck.Security.DirectDB.UnescapedDBParameter -- table name is built from $wpdb->prefix, the IN() placeholders are generated and the values are prepared */ $items = $wpdb->get_results($wpdb->prepare("SELECT uid, title, data, status FROM {$wpdb->prefix}zappoppop_items WHERE uid IN ($ph) AND status != 'trash'", $uids), ARRAY_A);
        if (empty($items)) {
            wp_send_json_error(['message' => __('Selected campaigns were not found.', 'zappoppop')]);
        }
        wp_send_json_success([
            'filename' => 'zappoppop-selected-' . gmdate('Y-m-d') . '.json',
            'payload'  => [
                'generator' => 'ZapPopPop ' . (defined('ZAPPOPPOP_VERSION') ? ZAPPOPPOP_VERSION : '1.0.0'),
                'exported'  => current_time('mysql'),
                'items'     => $items,
                'campaigns' => [],
            ],
        ]);
    }
}
