<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('ZapPopPop_Sanitizer')) {
    require_once ZAPPOPPOP_PATH . 'includes/class-zappoppop-sanitizer.php';
}

class ZapPopPop_Ajax_Builder {
    public function __construct() {
        add_action('wp_ajax_zappoppop_builder_save', array($this, 'save_popup'));
        add_action('wp_ajax_zappoppop_save_popup', [$this, 'save_popup']);
        add_action('wp_ajax_zappoppop_load_popup', [$this, 'load_popup']);
        add_action('wp_ajax_zappoppop_duplicate_popup', [$this, 'duplicate_popup']);
        add_action('wp_ajax_zappoppop_delete_popup', [$this, 'delete_popup']);
        add_action('wp_ajax_zappoppop_bulk_delete', [$this, 'bulk_delete']);
        add_action('wp_ajax_zappoppop_export_popup', [$this, 'export_popup']);
        add_action('wp_ajax_zappoppop_import_popup', [$this, 'import_popup']);
        if (!class_exists('ZapPopPop_Ajax_Templates')) { require_once ZAPPOPPOP_PATH . 'includes/ajax/class-ajax-templates.php'; }
        new ZapPopPop_Ajax_Templates();
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

    public function save_popup() {
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

        $this->verify_security();

        global $wpdb;
        $table_name = $wpdb->prefix . 'zappoppop_items';

        $title = isset($_POST['title']) ? sanitize_text_field(wp_unslash($_POST['title'])) : __('Untitled Popup', 'zappoppop');
        $uid   = isset($_POST['uid']) && !empty($_POST['uid']) ? sanitize_key($_POST['uid']) : $this->generate_unique_uid($title);

        // Support both 'data' and 'config' payload parameters
        $data_raw = '{}';
        if (isset($_POST['data'])) {
            $data_raw = ZapPopPop_Sanitizer::post_json_string('data');
        } elseif (isset($_POST['config'])) {
            $data_raw = ZapPopPop_Sanitizer::post_json_string('config');
        }

        if (json_decode($data_raw) === null) {
            wp_send_json_error(['message' => __('Malformed JSON definition.', 'zappoppop')]);
        }

        $zappoppop_obj = json_decode($data_raw);
        if (is_object($zappoppop_obj) || is_array($zappoppop_obj)) {
            $zappoppop_obj = ZapPopPop_Sanitizer::popup_payload($zappoppop_obj); /* deep sanitiser: keys, URLs, CSS values, markup */
        }
        /* ZAPPOPPOP_EXT_SETTINGS: extension modules (Pro) allow-list their own keys; unhandled keys keep their stored value */
        if (is_object($zappoppop_obj) && isset($zappoppop_obj->settings) && is_object($zappoppop_obj->settings)) {
            $zappoppop_obj->settings = ZapPopPop_Module_Bridge::sanitize_settings($zappoppop_obj->settings, 'save', $uid);
        }
        /* ZAPPOPPOP_BG_CLOSE_WHITELIST: normalize Click Background to Close to a strict boolean */
        if (is_object($zappoppop_obj) && isset($zappoppop_obj->settings) && is_object($zappoppop_obj->settings)) {
            $zappoppop_bgc = isset($zappoppop_obj->settings->close_on_backdrop) ? $zappoppop_obj->settings->close_on_backdrop : false;
            $zappoppop_obj->settings->close_on_backdrop = in_array($zappoppop_bgc, array(true, 1, '1', 'true'), true);
        }
        /* Popup Settings module order is global (Settings > Module); never stored per popup */
        if (is_object($zappoppop_obj) && isset($zappoppop_obj->settings) && is_object($zappoppop_obj->settings)) {
            unset($zappoppop_obj->settings->module_order);
        }
        /* ZAPPOPPOP_NOTIFICATIONS_WHITELIST: allow-list the Notifications module */
        if (is_object($zappoppop_obj) && isset($zappoppop_obj->settings) && is_object($zappoppop_obj->settings)) {
            $zappoppop_nin = isset($zappoppop_obj->settings->notifications) ? $zappoppop_obj->settings->notifications : array();
            if (is_object($zappoppop_nin)) { $zappoppop_nin = array_values(get_object_vars($zappoppop_nin)); }
            if (!is_array($zappoppop_nin)) { $zappoppop_nin = array(); }
            $zappoppop_nout = array();
            foreach (array_slice($zappoppop_nin, 0, 20) as $zappoppop_n) {
                $zappoppop_n = is_object($zappoppop_n) ? get_object_vars($zappoppop_n) : (is_array($zappoppop_n) ? $zappoppop_n : array());
                if (empty($zappoppop_n)) { continue; }
                $zappoppop_t = function ($k, $d = '') use ($zappoppop_n) {
                    return (isset($zappoppop_n[$k]) && is_scalar($zappoppop_n[$k])) ? sanitize_text_field((string) $zappoppop_n[$k]) : $d;
                };
                $zappoppop_b = function ($k) use ($zappoppop_n) {
                    return isset($zappoppop_n[$k]) && in_array($zappoppop_n[$k], array(true, 1, '1', 'true'), true);
                };
                $zappoppop_att = array();
                $zappoppop_ain = isset($zappoppop_n['attachments']) ? $zappoppop_n['attachments'] : array();
                if (is_object($zappoppop_ain)) { $zappoppop_ain = array_values(get_object_vars($zappoppop_ain)); }
                foreach ((is_array($zappoppop_ain) ? array_slice($zappoppop_ain, 0, 10) : array()) as $zappoppop_a) {
                    $zappoppop_a = is_object($zappoppop_a) ? get_object_vars($zappoppop_a) : (is_array($zappoppop_a) ? $zappoppop_a : array());
                    $zappoppop_aid = isset($zappoppop_a['id']) ? absint($zappoppop_a['id']) : 0;
                    if ($zappoppop_aid) {
                        $zappoppop_att[] = array(
                            'id'   => $zappoppop_aid,
                            'name' => isset($zappoppop_a['name']) && is_scalar($zappoppop_a['name']) ? sanitize_text_field((string) $zappoppop_a['name']) : '',
                            'url'  => isset($zappoppop_a['url']) && is_scalar($zappoppop_a['url']) ? esc_url_raw((string) $zappoppop_a['url']) : ''
                        );
                    }
                }
                $zappoppop_rules = array();
                $zappoppop_rin = isset($zappoppop_n['rules']) ? $zappoppop_n['rules'] : array();
                if (is_object($zappoppop_rin)) { $zappoppop_rin = array_values(get_object_vars($zappoppop_rin)); }
                foreach ((is_array($zappoppop_rin) ? array_slice($zappoppop_rin, 0, 20) : array()) as $zappoppop_r) {
                    $zappoppop_r = is_object($zappoppop_r) ? get_object_vars($zappoppop_r) : (is_array($zappoppop_r) ? $zappoppop_r : array());
                    $zappoppop_op = isset($zappoppop_r['operator']) ? (string) $zappoppop_r['operator'] : 'is';
                    $zappoppop_rules[] = array(
                        'field'    => isset($zappoppop_r['field']) && is_scalar($zappoppop_r['field']) ? sanitize_text_field((string) $zappoppop_r['field']) : '',
                        'operator' => in_array($zappoppop_op, array('is', 'is_not', 'contains', 'not_contains', 'empty', 'not_empty'), true) ? $zappoppop_op : 'is',
                        'value'    => isset($zappoppop_r['value']) && is_scalar($zappoppop_r['value']) ? sanitize_text_field((string) $zappoppop_r['value']) : ''
                    );
                }
                $zappoppop_send = $zappoppop_t('send', 'after_submission');
                $zappoppop_nout[] = array(
                    'id'           => sanitize_key($zappoppop_t('id')),
                    'name'         => $zappoppop_t('name', 'Notification'),
                    'enable'       => $zappoppop_b('enable'),
                    'send'         => in_array($zappoppop_send, array('after_submission', 'after_confirmation'), true) ? $zappoppop_send : 'after_submission',
                    'recipient'    => $zappoppop_t('recipient'),
                    'subject'      => $zappoppop_t('subject'),
                    'message'      => (isset($zappoppop_n['message']) && is_string($zappoppop_n['message'])) ? wp_kses_post($zappoppop_n['message']) : '',
                    'attachments'  => $zappoppop_att,
                    'reply_to'     => $zappoppop_t('reply_to'),
                    'from_email'   => $zappoppop_t('from_email'),
                    'from_name'    => $zappoppop_t('from_name'),
                    'format'       => $zappoppop_t('format', 'html') === 'text' ? 'text' : 'html', /* ZAPPOPPOP_NTF_FORMAT */
                    'logic_enable' => $zappoppop_b('logic_enable'),
                    'logic_match'  => $zappoppop_t('logic_match', 'all') === 'any' ? 'any' : 'all',
                    'rules'        => $zappoppop_rules
                );
            }
            $zappoppop_obj->settings->notifications = json_decode(wp_json_encode($zappoppop_nout));
            if (!is_array($zappoppop_obj->settings->notifications)) { $zappoppop_obj->settings->notifications = array(); }
        }
        /* ZAPPOPPOP_MODULE_SAVE_GUARD: settings of not-installed modules are kept as stored, never overwritten */
        if (class_exists('ZapPopPop_Modules') && is_object($zappoppop_obj) && isset($zappoppop_obj->settings) && is_object($zappoppop_obj->settings)) {
            /* phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- table name is built from $wpdb->prefix and every value is prepared */ $zappoppop_prev_json = $wpdb->get_var($wpdb->prepare("SELECT data FROM {$table_name} WHERE uid = %s", $uid));
            if (class_exists('ZapPopPop_Modules_Engine')) { ZapPopPop_Modules_Engine::sanitize_save($zappoppop_obj->settings); }
            ZapPopPop_Modules::guard_save($zappoppop_obj->settings, $zappoppop_prev_json);
        }
        if (is_object($zappoppop_obj) || is_array($zappoppop_obj)) {
            $zappoppop_enc = wp_json_encode($this->sanitize_anim_payload($zappoppop_obj));
            if (is_string($zappoppop_enc) && $zappoppop_enc !== '') { $data_raw = $zappoppop_enc; }
        }

        /* phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- table name is built from $wpdb->prefix and every value is prepared */ $existing = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$table_name} WHERE uid = %s", $uid));

        /* ZAPPOPPOP_SAVE_STATUS: whitelist - only publish|draft; anything else keeps the stored status */
        $zappoppop_req_status = isset($_POST['save_status']) ? sanitize_key(wp_unslash($_POST['save_status'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce verified at the top of save_popup()
        if (!in_array($zappoppop_req_status, ['publish', 'draft'], true)) {
            $zappoppop_req_status = '';
        }

        if ($existing) {
            $wpdb->update($table_name, ['title' => $title, 'data' => $data_raw], ['uid' => $uid], ['%s', '%s'], ['%s']);
            $uid = $this->maybe_rename_uid($uid, $title); /* ZAPPOPPOP_UID_FOLLOWS_TITLE */
            if ($zappoppop_req_status !== '') {
                /* phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- table name is built from $wpdb->prefix and every value is prepared */ $zappoppop_cur_status = (string) $wpdb->get_var($wpdb->prepare("SELECT status FROM {$table_name} WHERE uid = %s", $uid));
                if ($zappoppop_cur_status !== 'trash' && $zappoppop_cur_status !== $zappoppop_req_status) {
                    $wpdb->update($table_name, ['status' => $zappoppop_req_status], ['uid' => $uid], ['%s'], ['%s']);
                }
            }
        } else {
            $wpdb->insert($table_name, ['uid' => $uid, 'title' => $title, 'data' => $data_raw, 'status' => ($zappoppop_req_status !== '' ? $zappoppop_req_status : 'publish')], ['%s', '%s', '%s', '%s']);
        }

        /* phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- table name is built from $wpdb->prefix and every value is prepared */ $zappoppop_saved_status = $wpdb->get_var($wpdb->prepare("SELECT status FROM {$table_name} WHERE uid = %s", $uid));

        wp_send_json_success([
            'uid'     => $uid,
            'status'  => $zappoppop_saved_status ? $zappoppop_saved_status : 'publish',
            'message' => __('Popup configuration saved successfully!', 'zappoppop')
        ]);
    }

    /**
     * Unique embed UID: <popup-name>-<6 lowercase alphanumerics>.
     * Max length 36 (same as a UUID) so any existing uid column fits.
     */
    private function generate_unique_uid($title = '') {
        global $wpdb;
        $table = $wpdb->prefix . 'zappoppop_items';
        $base  = sanitize_title((string) $title);
        $base  = preg_replace('/[^a-z0-9-]+/', '-', strtolower($base));
        $base  = trim(preg_replace('/-{2,}/', '-', $base), '-');
        $base  = substr($base, 0, 29);
        $base  = rtrim($base, '-');
        if ($base === '') {
            $base = 'popup';
        }
        $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
        for ($try = 0; $try < 30; $try++) {
            $suffix = '';
            for ($i = 0; $i < 6; $i++) {
                $suffix .= $chars[wp_rand(0, strlen($chars) - 1)];
            }
            $candidate = $base . '-' . $suffix;
            /* phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- table name is built from $wpdb->prefix and every value is prepared */ $taken = $wpdb->get_var($wpdb->prepare("SELECT COUNT(1) FROM {$table} WHERE uid = %s", $candidate));
            if (!$taken) {
                return $candidate;
            }
        }
        return substr($base, 0, 20) . '-' . strtolower(wp_generate_password(6, false, false));
    }

    /** Slug used for the <popup-name> part of a uid (max 29 chars). */
    private function uid_base($title) {
        $base = sanitize_title((string) $title);
        $base = preg_replace('/[^a-z0-9-]+/', '-', strtolower($base));
        $base = trim(preg_replace('/-{2,}/', '-', $base), '-');
        $base = rtrim(substr($base, 0, 29), '-');
        return $base === '' ? 'popup' : $base;
    }

    /** Rename an auto-format uid (name-xxxxxx) when the title slug changed. Returns final uid. */
    private function maybe_rename_uid($uid, $title) {
        global $wpdb;
        if (!preg_match('/^(.+)-([a-z0-9]{6})$/', $uid, $m)) {
            return $uid;
        }
        $base = $this->uid_base($title);
        if ($m[1] === $base) {
            return $uid;
        }
        $items = $wpdb->prefix . 'zappoppop_items';
        $new   = $base . '-' . $m[2];
        /* phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- table name is built from $wpdb->prefix and every value is prepared */ if ($wpdb->get_var($wpdb->prepare("SELECT COUNT(1) FROM {$items} WHERE uid = %s", $new))) {
            $new = $this->generate_unique_uid($title);
        }
        $wpdb->update($items, ['uid' => $new], ['uid' => $uid], ['%s'], ['%s']);
        $wpdb->update($wpdb->prefix . 'zappoppop_submissions', ['popup_uid' => $new], ['popup_uid' => $uid], ['%s'], ['%s']);
        $wpdb->update($wpdb->prefix . 'zappoppop_transactions', ['popup_uid' => $new], ['popup_uid' => $uid], ['%s'], ['%s']);
        do_action('zappoppop_popup_uid_renamed', $uid, $new);
        /* RULE: a renamed uid keeps working. Record old -> new so existing shortcodes still resolve. */
        $aliases = get_option('zappoppop_uid_aliases', []);
        if (!is_array($aliases)) { $aliases = []; }
        foreach ($aliases as $ak => $av) { if ($av === $uid) { $aliases[$ak] = $new; } }
        $aliases[$uid] = $new;
        unset($aliases[$new]);
        update_option('zappoppop_uid_aliases', array_slice($aliases, -500, null, true), false);
        return $new;
    }

    private function sanitize_anim_payload($node) {
        if (!class_exists('ZapPopPop_Sanitizer')) { require_once ZAPPOPPOP_PATH . 'includes/class-zappoppop-sanitizer.php'; }
        return ZapPopPop_Sanitizer::anim_payload($node);
        if (is_object($node)) {
            foreach (get_object_vars($node) as $k => $v) { $node->$k = $this->sanitize_anim_value($k, $v); }
            return $node;
        }
        if (is_array($node)) {
            foreach ($node as $k => $v) { $node[$k] = $this->sanitize_anim_value($k, $v); }
        }
        return $node;
    }

    private function sanitize_anim_value($k, $v) {
        static $txt = array('anim_appearance', 'anim_disappearance', 'anim_effect', 'animEffect', 'animation', 'animIn', 'animOut');
        static $int = array('anim_duration', 'anim_delay', 'animDuration', 'animDelay', 'canvas_radius', 'autoclose', 'autoclose_delay');
        if (is_scalar($v) || $v === null) {
            if (in_array($k, $txt, true)) { return preg_replace('/[^A-Za-z0-9_\-]/', '', (string) $v); }
            if (in_array($k, $int, true)) { return max(0, intval($v)); }
            return $v;
        }
        return $this->sanitize_anim_payload($v);
    }

    public function load_popup() {
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

        $this->verify_security();
        global $wpdb;

        $uid = isset($_REQUEST['uid']) ? sanitize_key($_REQUEST['uid']) : '';
        if (empty($uid)) {
            wp_send_json_error(['message' => __('Missing campaign UID.', 'zappoppop')]);
        }

        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}zappoppop_items WHERE uid = %s", $uid), ARRAY_A);
        if (!$row) {
            wp_send_json_error(['message' => __('Popup not found.', 'zappoppop')]);
        }

        // Return configuration under both 'data' and 'config' properties for maximum compatibility
        $row['config'] = $row['data'];
        wp_send_json_success($row);
    }

    public function duplicate_popup() {
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

        $this->verify_security();
        global $wpdb;
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}zappoppop_items WHERE uid = %s", isset($_POST['uid']) ? sanitize_key(wp_unslash($_POST['uid'])) : ''), ARRAY_A);
        if (!$row) {
            wp_send_json_error(['message' => __('Source popup not found.', 'zappoppop')]);
        }

        $new_uid = $this->generate_unique_uid($row['title']);
        $wpdb->insert(
            $wpdb->prefix . 'zappoppop_items',
            [
                'uid'           => $new_uid,
                'title'         => $row['title'] . ' (Copy)',
                'data'          => $row['data'],
                'status'        => 'draft',
                'impressions'   => 0,
                'submissions'   => 0,
                'confirmations' => 0
            ],
            ['%s', '%s', '%s', '%s', '%d', '%d', '%d']
        );

        wp_send_json_success([
            'uid'     => $new_uid,
            'title'   => $row['title'] . ' (Copy)',
            'message' => __('Popup duplicated successfully!', 'zappoppop')
        ]);
    }

    public function delete_popup() {
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

        $this->verify_security();
        global $wpdb;
        $del_uid = isset($_POST['uid']) ? sanitize_key(wp_unslash($_POST['uid'])) : '';
        if ($del_uid === '') {
            wp_send_json_error(['message' => __('Missing campaign UID.', 'zappoppop')]);
        }
        $wpdb->delete($wpdb->prefix . 'zappoppop_items', ['uid' => $del_uid], ['%s']);
        wp_send_json_success(['message' => __('Popup removed successfully.', 'zappoppop')]);
    }

    public function bulk_delete() {
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

        $this->verify_security();
        $uids = isset($_POST['uids']) ? array_filter((array) wp_unslash($_POST['uids']), 'is_scalar') : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- each uid is sanitize_key()d below
        if (empty($uids)) {
            wp_send_json_error(['message' => __('No popups were selected for deletion.', 'zappoppop')]);
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'zappoppop_items';
        $sanitized_uids = array_map('sanitize_key', $uids);
        $placeholders = implode(',', array_fill(0, count($sanitized_uids), '%s'));

        /* phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare, PluginCheck.Security.DirectDB.UnescapedDBParameter -- table name is built from $wpdb->prefix, the IN() placeholders are generated and the values are prepared */ $deleted = $wpdb->query($wpdb->prepare("DELETE FROM {$table_name} WHERE uid IN ($placeholders)", $sanitized_uids));

        wp_send_json_success([
            'message'       => sprintf(
                /* translators: %d: number of popup campaigns removed. */
                _n('Successfully removed %d popup campaign.', 'Successfully removed %d popup campaigns.', count($sanitized_uids), 'zappoppop'),
                count($sanitized_uids)
            ),
            'deleted_count' => $deleted
        ]);
    }

    public function export_popup() {
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

        $this->verify_security();
        global $wpdb;
        $row = $wpdb->get_row($wpdb->prepare("SELECT title, data FROM {$wpdb->prefix}zappoppop_items WHERE uid = %s", isset($_GET['uid']) ? sanitize_key(wp_unslash($_GET['uid'])) : ''), ARRAY_A);
        if (!$row) {
            wp_send_json_error(['message' => __('Popup not found.', 'zappoppop')]);
        }
        $zappoppop_payload = $row['data'];
        $zappoppop_obj_x   = json_decode($zappoppop_payload);
        if (is_object($zappoppop_obj_x)) {
            if (!isset($zappoppop_obj_x->meta) || !is_object($zappoppop_obj_x->meta)) {
                $zappoppop_obj_x->meta = new stdClass();
            }
            if (empty($zappoppop_obj_x->meta->title)) {
                $zappoppop_obj_x->meta->title = $row['title'];
            }
            $zappoppop_re = wp_json_encode($zappoppop_obj_x);
            if (is_string($zappoppop_re) && $zappoppop_re !== '') { $zappoppop_payload = $zappoppop_re; }
        }
        wp_send_json_success(['filename' => sanitize_title($row['title']) . '-zappoppop-export.json', 'payload' => $zappoppop_payload]);
    }

    public function import_popup() {
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

        $this->verify_security();
        $json_raw = ZapPopPop_Sanitizer::post_json_string('import_data');
        $data = json_decode($json_raw, true);
        if (is_array($data)) { $data = ZapPopPop_Legacy_Import::convert($data); } /* accept exports from the previous generation */
        if (is_array($data)) {
            $data = ZapPopPop_Sanitizer::popup_payload($data); /* deep sanitiser: keys, URLs, CSS values, markup */
        }
        
        if (isset($data['canvasMeta']) && is_array($data['canvasMeta'])) {
            foreach ($data['canvasMeta'] as $cKey => $cVal) {
                if (is_array($cVal)) {
                    if (isset($cVal['anim_appearance'])) $data['canvasMeta'][$cKey]['anim_appearance'] = sanitize_text_field($cVal['anim_appearance']);
                    if (isset($cVal['anim_duration'])) $data['canvasMeta'][$cKey]['anim_duration'] = intval($cVal['anim_duration']);
                    if (isset($cVal['anim_delay'])) $data['canvasMeta'][$cKey]['anim_delay'] = intval($cVal['anim_delay']);
                    if (isset($cVal['anim_disappearance'])) $data['canvasMeta'][$cKey]['anim_disappearance'] = sanitize_text_field($cVal['anim_disappearance']);
                    if (isset($cVal['canvas_radius'])) $data['canvasMeta'][$cKey]['canvas_radius'] = max(0, min(500, intval($cVal['canvas_radius'])));
                }
            }
        }
        if (isset($data['canvas_meta']) && is_array($data['canvas_meta'])) {
            foreach ($data['canvas_meta'] as $cKey => $cVal) {
                if (is_array($cVal)) {
                    if (isset($cVal['anim_appearance'])) $data['canvas_meta'][$cKey]['anim_appearance'] = sanitize_text_field($cVal['anim_appearance']);
                    if (isset($cVal['anim_duration'])) $data['canvas_meta'][$cKey]['anim_duration'] = intval($cVal['anim_duration']);
                    if (isset($cVal['anim_delay'])) $data['canvas_meta'][$cKey]['anim_delay'] = intval($cVal['anim_delay']);
                    if (isset($cVal['anim_disappearance'])) $data['canvas_meta'][$cKey]['anim_disappearance'] = sanitize_text_field($cVal['anim_disappearance']);
                    if (isset($cVal['canvas_radius'])) $data['canvas_meta'][$cKey]['canvas_radius'] = max(0, min(500, intval($cVal['canvas_radius'])));
                }
            }
        }
/* ZAPPOPPOP_IMPORT_V2: accept exported popups (no "meta" key), sanitise, return uid */
        if (!is_array($data) || empty($data)) {
            wp_send_json_error(['message' => __('Invalid JSON definition.', 'zappoppop')]);
        }

        $zappoppop_title = '';
        if (isset($data['meta']) && is_array($data['meta']) && !empty($data['meta']['title']) && is_scalar($data['meta']['title'])) {
            $zappoppop_title = (string) $data['meta']['title'];
        } elseif (!empty($data['title']) && is_scalar($data['title'])) {
            $zappoppop_title = (string) $data['title'];
        } elseif (isset($data['settings']) && is_array($data['settings']) && !empty($data['settings']['title']) && is_scalar($data['settings']['title'])) {
            $zappoppop_title = (string) $data['settings']['title'];
        } elseif (isset($_POST['title'])) {
            $zappoppop_title = sanitize_text_field((string) wp_unslash($_POST['title']));
        }
        $zappoppop_title = sanitize_text_field($zappoppop_title);
        if ($zappoppop_title === '') {
            $zappoppop_title = __('Imported Popup', 'zappoppop');
        }
        if (!isset($data['meta']) || !is_array($data['meta'])) {
            $data['meta'] = [];
        }
        $data['meta']['title'] = $zappoppop_title;

        $zappoppop_clean = $this->sanitize_anim_payload(json_decode(wp_json_encode($data)));
        if (is_object($zappoppop_clean) && isset($zappoppop_clean->settings) && is_object($zappoppop_clean->settings) && class_exists('ZapPopPop_Module_Bridge')) {
            $zappoppop_clean->settings = ZapPopPop_Module_Bridge::sanitize_settings($zappoppop_clean->settings, 'template', '');
        }
        $zappoppop_enc   = wp_json_encode($zappoppop_clean);
        if (!is_string($zappoppop_enc) || $zappoppop_enc === '') {
            wp_send_json_error(['message' => __('Invalid JSON definition.', 'zappoppop')]);
        }

        global $wpdb;
        $zappoppop_new_uid = $this->generate_unique_uid($zappoppop_title);
        $zappoppop_ok = $wpdb->insert($wpdb->prefix . 'zappoppop_items', [
            'uid'    => $zappoppop_new_uid,
            'title'  => $zappoppop_title . ' (Imported)',
            'data'   => $zappoppop_enc,
            'status' => 'publish'
        ], ['%s', '%s', '%s', '%s']);
        if ($zappoppop_ok === false) {
            wp_send_json_error(['message' => __('Database error: could not import popup. Try Tools > Repair Tables.', 'zappoppop')]);
        }
        wp_send_json_success(['message' => __('Popup imported successfully!', 'zappoppop'), 'uid' => $zappoppop_new_uid]);
    }

    /**
     * Phase 2 Backend Whitelist Parity: opacity, hoverBgColor, customClass
     */
    public function sanitize_phase2_whitelist_properties(array $el, array &$clean = array()) {
        if (isset($el['opacity'])) {
            $clean['opacity'] = floatval($el['opacity']);
        } else {
            $clean['opacity'] = 1.0;
        }

        if (isset($el['hoverBgColor'])) {
            $clean['hoverBgColor'] = sanitize_text_field($el['hoverBgColor']);
            $clean['hoverBg'] = $clean['hoverBgColor'];
        } elseif (isset($el['hoverBg'])) {
            $clean['hoverBg'] = sanitize_text_field($el['hoverBg']);
            $clean['hoverBgColor'] = $clean['hoverBg'];
        }

        /* ZAPPOPPOP_CLOSE_WHITELIST */
        if (isset($el['hoverTextColor'])) { $clean['hoverTextColor'] = sanitize_text_field($el['hoverTextColor']); $clean['hoverColor'] = $clean['hoverTextColor']; }
        elseif (isset($el['hoverColor'])) { $clean['hoverColor'] = sanitize_text_field($el['hoverColor']); $clean['hoverTextColor'] = $clean['hoverColor']; }
        if (isset($el['hoverBorderColor'])) { $clean['hoverBorderColor'] = sanitize_text_field($el['hoverBorderColor']); $clean['hoverBorder'] = $clean['hoverBorderColor']; }
        elseif (isset($el['hoverBorder'])) { $clean['hoverBorder'] = sanitize_text_field($el['hoverBorder']); $clean['hoverBorderColor'] = $clean['hoverBorder']; }
        if (isset($el['closeGlyph'])) { $clean['closeGlyph'] = sanitize_text_field($el['closeGlyph']); }
        if (isset($el['iconSize'])) { $clean['iconSize'] = intval($el['iconSize']); }
        if (isset($el['sizePresetApplied'])) { $clean['sizePresetApplied'] = !empty($el['sizePresetApplied']); }

        if (isset($el['customClass'])) {
            $clean['customClass'] = sanitize_html_class($el['customClass']);
            $clean['custom_css_class'] = $clean['customClass'];
        } elseif (isset($el['custom_css_class'])) {
            $clean['custom_css_class'] = sanitize_html_class($el['custom_css_class']);
            $clean['customClass'] = $clean['custom_css_class'];
        }

        
        // Form Family Data & Advanced Tab Lifecycle Persistence
        if (isset($el['defaultValue'])) $clean['defaultValue'] = sanitize_text_field($el['defaultValue']);
        if (isset($el['dynamicDefault'])) $clean['dynamicDefault'] = !empty($el['dynamicDefault']);
        if (isset($el['paramName'])) $clean['paramName'] = sanitize_key($el['paramName']);
        if (isset($el['readOnly'])) $clean['readOnly'] = !empty($el['readOnly']);
        if (isset($el['saveToDb'])) $clean['saveToDb'] = !empty($el['saveToDb']);
        if (isset($el['fieldId'])) $clean['fieldId'] = sanitize_html_class($el['fieldId']);
        if (isset($el['inputMask'])) $clean['inputMask'] = sanitize_text_field($el['inputMask']);
        if (isset($el['customAttrs'])) $clean['customAttrs'] = sanitize_text_field($el['customAttrs']);
        if (isset($el['filters']) && is_array($el['filters'])) {
            $clean['filters'] = array_values(array_map('sanitize_key', $el['filters']));
        }
        if (isset($el['validators']) && is_array($el['validators'])) {
            $clean['validators'] = array_values(array_map('sanitize_key', $el['validators']));
        }

        
        // Phase 2: Media Family (Elements 1-9 & 26 Submit) Decomposed Properties Whitelist
        if (isset($el['shapeType'])) $clean['shapeType'] = sanitize_key($el['shapeType']);
        if (isset($el['rotate'])) $clean['rotate'] = intval($el['rotate']);
        if (isset($el['transparentFill'])) $clean['transparentFill'] = !empty($el['transparentFill']);
        if (isset($el['objectFit'])) $clean['objectFit'] = sanitize_key($el['objectFit']);
        if (isset($el['alt'])) $clean['alt'] = sanitize_text_field($el['alt']);
        if (isset($el['linkUrl'])) $clean['linkUrl'] = esc_url_raw($el['linkUrl']);
        if (isset($el['linkTarget']) && !is_array($el['linkTarget'])) $clean['linkTarget'] = ((string) $el['linkTarget'] === '_blank') ? '_blank' : '_self';
        if (isset($el['linkBlank'])) $clean['linkBlank'] = !empty($el['linkBlank']);
        if (isset($el['videoProvider'])) $clean['videoProvider'] = sanitize_key($el['videoProvider']);
        if (isset($el['aspectRatio'])) $clean['aspectRatio'] = sanitize_text_field($el['aspectRatio']);
        if (isset($el['videoUrl'])) $clean['videoUrl'] = esc_url_raw($el['videoUrl']);
        if (isset($el['videoThumb']) && !is_array($el['videoThumb'])) $clean['videoThumb'] = esc_url_raw(trim(wp_unslash((string) $el['videoThumb'])));
        if (isset($el['videoAutoplay'])) $clean['videoAutoplay'] = !empty($el['videoAutoplay']);
        if (isset($el['videoMute'])) $clean['videoMute'] = !empty($el['videoMute']);
        if (isset($el['videoLoop'])) $clean['videoLoop'] = !empty($el['videoLoop']);
        if (isset($el['videoControls'])) $clean['videoControls'] = !empty($el['videoControls']);
        if (isset($el['tag'])) $clean['tag'] = sanitize_key($el['tag']);
        if (isset($el['textTransform'])) $clean['textTransform'] = sanitize_key($el['textTransform']);
        if (isset($el['overflow'])) $clean['overflow'] = sanitize_key($el['overflow']);
        if (isset($el['closeAction'])) $clean['closeAction'] = sanitize_key($el['closeAction']);
        if (isset($el['cookieDays'])) $clean['cookieDays'] = intval($el['cookieDays']);
        if (isset($el['iconClass'])) $clean['iconClass'] = sanitize_text_field($el['iconClass']);
        if (isset($el['iconSize'])) $clean['iconSize'] = intval($el['iconSize']);
        if (isset($el['progressMode'])) $clean['progressMode'] = in_array($el['progressMode'], array('manual','form','steps'), true) ? $el['progressMode'] : 'manual';
        if (isset($el['progressPercent'])) $clean['progressPercent'] = max(0, min(100, intval($el['progressPercent'])));
        if (isset($el['progressColor'])) $clean['progressColor'] = sanitize_hex_color($el['progressColor']);
        if (isset($el['progressStriped'])) $clean['progressStriped'] = !empty($el['progressStriped']);
        if (isset($el['progressAnimated'])) $clean['progressAnimated'] = !empty($el['progressAnimated']);
        if (isset($el['buttonAction'])) $clean['buttonAction'] = sanitize_key($el['buttonAction']);
        if (isset($el['submitAction'])) $clean['submitAction'] = sanitize_key($el['submitAction']);
        if (isset($el['loadingText'])) $clean['loadingText'] = sanitize_text_field($el['loadingText']);
        if (isset($el['redirectUrl'])) $clean['redirectUrl'] = esc_url_raw($el['redirectUrl']);

        
        // Phase 3: Form Family Core Inputs (Elements 10-17) Decomposed Properties Whitelist
        if (isset($el['autocomplete'])) $clean['autocomplete'] = sanitize_text_field($el['autocomplete']);
        if (isset($el['maxLength'])) $clean['maxLength'] = intval($el['maxLength']);
        if (isset($el['emailValidationMode'])) $clean['emailValidationMode'] = sanitize_key($el['emailValidationMode']);
        if (isset($el['min'])) $clean['min'] = floatval($el['min']);
        if (isset($el['max'])) $clean['max'] = floatval($el['max']);
        if (isset($el['step'])) $clean['step'] = floatval($el['step']);
        if (isset($el['phonePrefix'])) $clean['phonePrefix'] = sanitize_text_field($el['phonePrefix']);
        if (isset($el['rows'])) $clean['rows'] = max(1, min(30, intval($el['rows'])));
        if (isset($el['layout'])) $clean['layout'] = sanitize_key($el['layout']);
        if (isset($el['termsUrl'])) $clean['termsUrl'] = esc_url_raw($el['termsUrl']);
        if (isset($el['checkedDefault'])) $clean['checkedDefault'] = !empty($el['checkedDefault']);

        
        // Phase 4: Form Family Selectors & Pickers (Elements 18-25) Decomposed Properties Whitelist
        if (isset($el['maxSelect'])) $clean['maxSelect'] = max(1, intval($el['maxSelect']));
        if (isset($el['columns'])) $clean['columns'] = max(1, min(6, intval($el['columns'])));
        if (isset($el['multiSelect'])) $clean['multiSelect'] = !empty($el['multiSelect']);
        if (isset($el['activeColor'])) $clean['activeColor'] = sanitize_hex_color($el['activeColor']);
        if (isset($el['dateFormat'])) $clean['dateFormat'] = sanitize_text_field($el['dateFormat']);
        if (isset($el['minDate'])) $clean['minDate'] = sanitize_text_field($el['minDate']);
        if (isset($el['maxDate'])) $clean['maxDate'] = sanitize_text_field($el['maxDate']);
        if (isset($el['timeFormat'])) $clean['timeFormat'] = sanitize_key($el['timeFormat']);
        if (isset($el['timeStep'])) $clean['timeStep'] = max(1, intval($el['timeStep']));
        if (isset($el['allowedExtensions'])) $clean['allowedExtensions'] = sanitize_text_field($el['allowedExtensions']);
        if (isset($el['maxFileSize'])) $clean['maxFileSize'] = max(1, intval($el['maxFileSize']));
        if (isset($el['multiUpload'])) $clean['multiUpload'] = !empty($el['multiUpload']);
        if (isset($el['maxFiles'])) $clean['maxFiles'] = min(20, max(0, intval($el['maxFiles']))); /* ZAPPOPPOP_UPLOAD_LIMITS */
        if (isset($el['uploadErrorMessage']) && !is_array($el['uploadErrorMessage'])) $clean['uploadErrorMessage'] = sanitize_text_field(wp_unslash((string) $el['uploadErrorMessage']));
        if (isset($el['minLength'])) $clean['minLength'] = max(0, intval($el['minLength']));
        if (isset($el['showEyeToggle'])) $clean['showEyeToggle'] = !empty($el['showEyeToggle']);
        if (isset($el['ratingMax']))$clean['ratingMax'] = in_array(intval($el['ratingMax']), array(5, 10), true) ? intval($el['ratingMax']) : 5;
        if (isset($el['ratingShape'])) $clean['ratingShape'] = sanitize_key($el['ratingShape']);
        if (isset($el['ratingColor'])) $clean['ratingColor'] = sanitize_hex_color($el['ratingColor']);
        if (isset($el['ratingInactiveColor']) && !is_array($el['ratingInactiveColor'])) { $zappoppop_ric = sanitize_hex_color((string) $el['ratingInactiveColor']); if ($zappoppop_ric) { $clean['ratingInactiveColor'] = $zappoppop_ric; } } /* ZAPPOPPOP_RATING */
        foreach (array('iconLeftColor', 'iconRightColor', 'fgColor') as $zappoppop_ic) { if (isset($el[$zappoppop_ic]) && !is_array($el[$zappoppop_ic])) { $zappoppop_icv = sanitize_hex_color((string) $el[$zappoppop_ic]); if ($zappoppop_icv) { $clean[$zappoppop_ic] = $zappoppop_icv; } } } /* ZAPPOPPOP_ICON_COLOR */

        
        // === PHASE 4: SANITIZED 5-TAB & 3-TAB LAYER SETTINGS WHITELIST ===
        $gp_text_keys = array(
            'autocomplete', 'animIn', 'animOut', 'animStart', 'iconLeft', 'iconRight',
            'customClass', 'cssClass', 'customId', 'logicAction', 'logicMatch',
            'defaultValue', 'paramName', 'fieldId', 'fieldName', 'token',
            'shapePreset', 'alt', 'objectFit', 'videoProvider',
            'aspectRatio', 'videoUrl', 'tag', 'textTransform', 'overflow',
            'closeSymbol', 'closeAction', 'closeGlyph', 'hoverBgColor', 'hoverTextColor', 'hoverBorderColor', 'hoverBg', 'hoverColor', 'hoverBorder', 'iconClass', 'progressMode',
            'buttonAction', 'url', 'loadingText', 'submitAction',
            'confirmMessage', 'emptySubmitMessage', 'redirectUrl', 'countryCode', 'checkboxLabel',
            'radioLayout', 'dateFormat', 'minDate', 'maxDate', 'timeFormat',
            'allowedExtensions', 'activeColor', 'ratingShape', 'ratingColor', 'requiredMessage', 'errorMessage', 'validatorEqual', 'validatorList', 'optionsList', 'firstOption', 'minValue', 'maxValue', 'stepValue', 'unitSuffix', 'uploadErrorMessage'
        );
        foreach ( $gp_text_keys as$gk ) {
            if ( isset( $el[$gk ] ) && ! is_array( $el[$gk ] ) ) {
                $clean[$gk ] = sanitize_text_field( wp_unslash( (string) $el[$gk ] ) );
            }
        }

        $gp_int_keys = array(
            'animDuration', 'animDelay', 'anim_duration', 'anim_delay', 'iconLeftOffset', 'iconRightOffset',
            'rotate', 'iconSize', 'cookieDays', 'progressValue',
            'maxLength', 'minLength', 'min', 'max', 'step', 'rows',
            'maxSelect', 'maxSelections', 'columns', 'gridCols', 'timeStep', 'maxFileSize', 'ratingMax'
        );
        foreach ( $gp_int_keys as$ik ) {
            if ( isset( $el[$ik ] ) && $el[$ik ] !== '' ) {
                $clean[$ik ] = intval( $el[$ik ] );
            }
        }

        $gp_bool_keys = array(
            'required', 'blockEmptySubmit', 'transparentFill', 'autoplay', 'muted', 'loop',
            'controls', 'showPercentLabel', 'striped', 'targetBlank',
            'nofollow', 'checkedDefault', 'multiSelect', 'multiple',
            'showEyeToggle', 'bgCustom', 'logicEnabled', 'dynamicDefault',
            'readOnly', 'saveToDb', 'blockDisposable'
        );
        foreach ( $gp_bool_keys as$bk ) {
            if ( isset( $el[$bk ] ) ) {
                $clean[$bk ] = ! empty( $el[$bk ] ) && $el[$bk ] !== 'false';
            }
        }

        if ( isset( $el['options'] ) && ! is_array( $el['options'] ) ) {
            $clean['options'] = sanitize_textarea_field( wp_unslash( (string)$el['options'] ) );
        }

        if ( isset( $el['inlineStyles'] ) && is_array( $el['inlineStyles'] ) ) {$clean['inlineStyles'] = array();
            foreach ( $el['inlineStyles'] as$st ) {
                if ( is_array( $st ) ) {$clean['inlineStyles'][] = array(
                        'prop'  => isset( $st['prop'] ) ? sanitize_text_field( $st['prop'] ) : '',
                        'value' => isset( $st['value'] ) ? sanitize_text_field( $st['value'] ) : '',
                    );
                }
            }
        }

        if ( isset( $el['logicRules'] ) && is_array( $el['logicRules'] ) ) {$clean['logicRules'] = array();
            foreach ( $el['logicRules'] as$rl ) {
                if ( is_array( $rl ) ) {$clean['logicRules'][] = array(
                        'field'    => isset( $rl['field'] ) ? sanitize_text_field( $rl['field'] ) : '',
                        'operator' => isset( $rl['operator'] ) ? sanitize_text_field( $rl['operator'] ) : 'is',
                        'value'    => isset( $rl['value'] ) ? sanitize_text_field( $rl['value'] ) : '',
                    );
                }
            }
        }

        foreach ( array( 'filters', 'validators' ) as $arr_k ) {
            if ( isset( $el[$arr_k ] ) && is_array( $el[$arr_k ] ) ) {
                $clean[$arr_k ] = array_map( 'sanitize_text_field', $el[$arr_k ] );
            }
        }

        
        if ( class_exists( 'ZapPopPop_Ajax_Element_Registry' ) && method_exists( 'ZapPopPop_Ajax_Element_Registry', 'sanitize_decomposed_layer_props' ) ) {
            $clean = ZapPopPop_Ajax_Element_Registry::sanitize_decomposed_layer_props( $el,$clean );
        }

        return $clean;
    }


    public function builder_save() {
        if ( ! current_user_can( 'manage_options' ) ) {
            if ( wp_doing_ajax() ) {
                wp_send_json_error( array( 'message' => esc_html__( 'Unauthorized user capability.', 'zappoppop' ) ), 403 );
            }
            wp_die( esc_html__( 'Unauthorized user capability.', 'zappoppop' ), 403 );
        }
        $zpp_verified = check_ajax_referer( 'zappoppop_nonce', 'nonce', false )
            || check_ajax_referer( 'zappoppop_nonce', '_ajax_nonce', false )
            || check_ajax_referer( 'zappoppop_admin', 'nonce', false )
            || check_ajax_referer( 'zappoppop_nonce', 'security', false )
            || check_ajax_referer( 'zappoppop_builder_nonce', 'nonce', false )
            || check_ajax_referer( 'zappoppop_admin_nonce', 'nonce', false )
            || check_ajax_referer( 'zappoppop_settings_nonce', 'nonce', false )
            || ( isset( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'zappoppop_nonce' ) )
            || ( isset( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'zappoppop_admin' ) );
        if ( ! $zpp_verified ) {
            if ( wp_doing_ajax() ) {
                wp_send_json_error( array( 'message' => esc_html__( 'Invalid security token.', 'zappoppop' ) ), 403 );
            }
            wp_die( esc_html__( 'Invalid security token.', 'zappoppop' ), 403 );
        }

        return $this->save_popup();
    }

}
