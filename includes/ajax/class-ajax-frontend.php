<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('ZapPopPop_Security')) {
    require_once ZAPPOPPOP_PATH . 'includes/class-zappoppop-security.php';
}

class ZapPopPop_Ajax_Frontend {
    public function __construct() {
        add_action('wp_ajax_zappoppop_submit_form', [$this, 'submit_form']);
        add_action('wp_ajax_nopriv_zappoppop_submit_form', [$this, 'submit_form']);
        add_action('wp_ajax_zappoppop_bulk_delete_submissions', [$this, 'bulk_delete_submissions']);
    }

    public function submit_form() {
        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        if (!wp_verify_nonce($nonce, 'zappoppop_front_nonce') && !wp_verify_nonce($nonce, 'zappoppop_submit_nonce')) {
            wp_send_json_error(['message' => __('Session expired. Please reload the page and try again.', 'zappoppop')]);
        }

        $ip = ZapPopPop_Security::get_client_ip();

        // 1. Request-level gates (add-ons: rate limits, bot verification)
        $gate = (string) apply_filters('zappoppop_submission_gate', '', 'request', ['ip' => $ip]);
        if ($gate !== '') {
            wp_send_json_error(['message' => $gate]);
        }

        // 3. Extract & Sanitize Form Fields
        if (!class_exists('ZapPopPop_Sanitizer')) { require_once ZAPPOPPOP_PATH . 'includes/class-zappoppop-sanitizer.php'; }
        $raw_fields = isset($_POST['fields']) && is_array($_POST['fields']) ? ZapPopPop_Sanitizer::post_array('fields') : [];
        if (empty($raw_fields)) {
            foreach (ZapPopPop_Sanitizer::post_all() as $k => $v) {
                if (!in_array($k, ['action', 'nonce', 'popup_uid', 'uid', 'country', 'quiz_score', 'g-recaptcha-response', 'recaptcha_token'], true)) {$raw_fields[$k] = $v;
                }
            }
        }

        $clean_fields = [];$email = '';
        $name = '';$phone = '';

        foreach ($raw_fields as $key =>$val) {
            $clean_key = sanitize_key($key);
            if (is_array($val)) {$clean_fields[$clean_key] = array_map('sanitize_text_field',$val);
            } else {
                $clean_val = sanitize_text_field($val);$clean_fields[$clean_key] =$clean_val;

                if (empty($email) && (strpos($clean_key, 'email') !== false || filter_var($clean_val, FILTER_VALIDATE_EMAIL))) {
                    $email = sanitize_email($clean_val);
                }
                if (empty($name) && in_array($clean_key, ['name', 'first_name', 'full_name'], true)) {
                    $name =$clean_val;
                }
                if (empty($phone) && (strpos($clean_key, 'phone') !== false || strpos($clean_key, 'tel') !== false)) {
                    $phone =$clean_val;
                }
            }
        }

        // 4. Handle Multipart File Uploads
        if (!empty($_FILES)) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            foreach ($_FILES as $file_key =>$file_data) {
                $file_key = sanitize_key($file_key);
                if ($file_key === '' || !is_array($file_data)) { continue; }
                if (!empty($file_data['name']) &&$file_data['error'] === UPLOAD_ERR_OK) {
                    $upload_overrides = ['test_form' => false];$movefile = wp_handle_upload($file_data,$upload_overrides);
                    if ($movefile && !isset($movefile['error'])) {
                        $clean_fields[$file_key] = esc_url_raw($movefile['url']);$clean_fields[$file_key . '_path'] = sanitize_text_field($movefile['file']);
                    }
                }
            }
        }

        // 5. Field-level gates (add-ons: email policy)
        $gate = (string) apply_filters('zappoppop_submission_gate', '', 'fields', ['ip' => $ip, 'email' => $email]);
        if ($gate !== '') {
            wp_send_json_error(['message' => $gate]);
        }

        // 6. Database Persistence
        global $wpdb;
        $table_name = $wpdb->prefix . 'zappoppop_submissions';$popup_uid  = $this->resolve_popup_uid();

        if (!empty($name)) { $clean_fields['_name'] = $name; }
        if (!empty($phone)) { $clean_fields['_phone'] = $phone; }
        /* ZAPPOPPOP_PROTECT_AJAX: extension modules (Pro) check and capture; core falls back to an anonymised IP */
        $zappoppop_cfg = array();
        if ($popup_uid !== '') {
            $zappoppop_raw = $wpdb->get_var($wpdb->prepare("SELECT data FROM {$wpdb->prefix}zappoppop_items WHERE uid = %s", $popup_uid));
            $zappoppop_cfg = json_decode((string) $zappoppop_raw, true);
            if (!is_array($zappoppop_cfg)) { $zappoppop_cfg = array(); }
        }
        $zappoppop_pre = ZapPopPop_Module_Bridge::precheck($popup_uid, $clean_fields, $zappoppop_cfg);
        if ($zappoppop_pre['deny'] !== '') {
            wp_send_json_error(['message' => $zappoppop_pre['deny']]);
        }
        $zappoppop_sp_mail_skip = $zappoppop_pre['mail_skip'];
        if (is_array($zappoppop_pre['tech'])) {
            $clean_fields = array_merge($clean_fields, $zappoppop_pre['tech']);
        } else {
            $clean_fields['_ip'] = ZapPopPop_Tracking::anonymize_ip($ip);
        }
        $visitor_country = isset($_POST['country']) ? sanitize_text_field(wp_unslash($_POST['country'])) : '';

        /* ZAPPOPPOP_DOI_AJAX: with Double Opt-In enabled (extension) the lead stays "pending" until the email link is opened */
        $zappoppop_doi     = ZapPopPop_Module_Bridge::double_optin($zappoppop_cfg);
        $zappoppop_doi_cfg = $zappoppop_doi['cfg'];
        $zappoppop_doi_on  = !empty($zappoppop_doi['active']); /* every submission stays pending until the link is confirmed */
        $zappoppop_doi_token = $zappoppop_doi_on ? wp_generate_password(32, false) : '';

        $wpdb->insert($table_name,
            [
                'popup_uid'     => $popup_uid,
                'email'         => (string) $email,
                'fields_data'   => wp_json_encode($clean_fields),
                'status'        => $zappoppop_doi_on ? 'pending' : 'confirmed',
                'confirm_token' => $zappoppop_doi_token,
                'country_code'  => $visitor_country,
                'created_at'    => current_time('mysql')
            ],
            ['%s', '%s', '%s', '%s', '%s', '%s', '%s']
        );
        if ($popup_uid !== '') {
            if ($zappoppop_doi_on) {
                $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}zappoppop_items SET submissions = submissions + 1 WHERE uid = %s", $popup_uid));
            } else {
                $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}zappoppop_items SET submissions = submissions + 1, confirmations = confirmations + 1 WHERE uid = %s", $popup_uid));
            }
        }

        $submission_id = $wpdb->insert_id;
        do_action('zappoppop_submission_saved', $submission_id);

        /* ZAPPOPPOP_AJAX_DISPATCH: same pipeline as the REST route: Notifications module, then the extension steps (confirmation mail, autoresponder, webhook, ...) */
        $zappoppop_tech_keys  = is_array($zappoppop_pre['tech']) ? array_keys($zappoppop_pre['tech']) : array('_ip');
        $zappoppop_ctx_fields = array();
        foreach ($clean_fields as $zappoppop_fk => $zappoppop_fv) {
            if ($zappoppop_fk === '_name' || $zappoppop_fk === '_phone' || in_array($zappoppop_fk, $zappoppop_tech_keys, true) || substr((string) $zappoppop_fk, -5) === '_path') {
                continue;
            }
            $zappoppop_ctx_fields[$zappoppop_fk] = $zappoppop_fv;
        }
        /* phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- table name is built from $wpdb->prefix and every value is prepared */ $zappoppop_title = $popup_uid !== '' ? (string) $wpdb->get_var($wpdb->prepare("SELECT title FROM {$wpdb->prefix}zappoppop_items WHERE uid = %s", $popup_uid)) : '';
        if (!class_exists('ZapPopPop_Notifications')) { require_once ZAPPOPPOP_PATH . 'includes/class-zappoppop-notifications.php'; }
        ZapPopPop_Notifications::dispatch('after_submission', $popup_uid, $zappoppop_title, $zappoppop_cfg, (string) $email, $clean_fields, $submission_id, $visitor_country);
        $zappoppop_out = ZapPopPop_Module_Bridge::dispatch(array(
            'uid'              => $popup_uid,
            'title'            => $zappoppop_title,
            'config'           => $zappoppop_cfg,
            'email'            => (string) $email,
            'fields'           => $zappoppop_ctx_fields,
            'submission_id'    => $submission_id,
            'country'          => $visitor_country,
            'double_optin'     => $zappoppop_doi_on,
            'double_optin_cfg' => $zappoppop_doi_cfg,
            'confirm_token'    => $zappoppop_doi_token,
        ));

        // 7. Automated Email Notification
        $admin_notification_email = zappoppop_get_setting('admin_email', get_option('admin_email'));
        $from_name                = zappoppop_get_setting('sender_name', zappoppop_get_setting('from_name', get_bloginfo('name')));$from_email               = zappoppop_get_setting('sender_email', zappoppop_get_setting('from_email', get_option('admin_email')));

        if (!ZapPopPop_Module_Bridge::email_modules_active($zappoppop_cfg) && !empty($admin_notification_email) && is_email($admin_notification_email)) {$lead_identifier = !empty($name) ?$name : (!empty($email) ?$email : 'Anonymous');
            $subject = sprintf('[%s] New Lead Captured: %s', get_bloginfo('name'),$lead_identifier);

            $headers = [
                'Content-Type: text/html; charset=UTF-8',
                'From: ' . esc_attr($from_name) . ' <' . sanitize_email($from_email) . '>',
                'Reply-To: ' . (!empty($email) ? sanitize_email($email) : sanitize_email($from_email))
            ];

            $msg  = '<div style="font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,sans-serif;max-width:600px;margin:0 auto;padding:24px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;">';
            $msg .= '<h2 style="color:#0f172a;margin-top:0;">' . esc_html__('New Lead Captured', 'zappoppop') . '</h2>';
            $msg .= '<p style="color:#475569;">' . sprintf(/* translators: 1: site name or date, 2: campaign UID */
            esc_html__( 'A new submission was received on %1$s for Campaign UID: %2$s.', 'zappoppop' ), esc_html(current_time('mysql')), '<code>' . esc_html($popup_uid) . '</code>') . '</p>';$msg .= '<table style="width:100%;border-collapse:collapse;margin-top:16px;background:#ffffff;border-radius:6px;overflow:hidden;border:1px solid #e2e8f0;">';

            if (!class_exists('ZapPopPop_Notifications')) { require_once ZAPPOPPOP_PATH . 'includes/class-zappoppop-notifications.php'; }
            $zappoppop_lead_labels = ZapPopPop_Notifications::field_labels($zappoppop_cfg); /* ZAPPOPPOP_LEAD_LABELS */
            foreach (array_diff_key($clean_fields, $zappoppop_sp_mail_skip) as $field_label =>$field_val) {
                if (strpos($field_label, '_path') !== false) {
                    continue;
                }
                $msg .= '<tr>';$msg .= '<td style="padding:10px 14px;border-bottom:1px solid #f1f5f9;font-weight:600;color:#334155;width:35%;">' . esc_html(isset($zappoppop_lead_labels[$field_label]) ? $zappoppop_lead_labels[$field_label] : ucwords(str_replace(['_', '-'], ' ', $field_label))) . '</td>';$msg .= '<td style="padding:10px 14px;border-bottom:1px solid #f1f5f9;color:#64748b;">' . esc_html(is_array($field_val) ? implode(', ', $field_val) : $field_val) . '</td>';$msg .= '</tr>';
            }

            if (empty($zappoppop_sp_mail_skip)) { $msg .= '<tr><td style="padding:10px 14px;font-weight:600;color:#334155;">' . esc_html__('IP Address', 'zappoppop') . '</td><td style="padding:10px 14px;color:#64748b;">' . esc_html($ip) . '</td></tr>'; }$msg .= '</table></div>';

            wp_mail($admin_notification_email,$subject, $msg,$headers);
        }

        // Resolve optional redirect destination
        $redirect_url = '';
        if (!empty($_POST['redirect_url'])) {
            $redirect_url = esc_url_raw(wp_unslash($_POST['redirect_url']));
        }

        wp_send_json_success([
            'message'       => $zappoppop_doi_on
                ? __('Thank you! A verification link has been dispatched to your email address.', 'zappoppop')
                : __('Thank you! Your submission has been received.', 'zappoppop'),
            'submission_id' => $submission_id,
            'redirect_url'  => $redirect_url,
            'double_optin'  => $zappoppop_doi_on,
            'download_url'  => $zappoppop_out['download_url'],
            'coupon_code'   => $zappoppop_out['coupon_code']
        ]);
    }

    /**
     * Resolve the popup UID from any known request key; map numeric IDs to UIDs.
     */
    private function resolve_popup_uid() {
        global $wpdb;
        $uid = '';
        foreach (['popup_uid', 'uid', 'popup_id', 'id'] as $k) {
            if (!empty($_POST[$k]) && is_scalar($_POST[$k])) {
                $uid = sanitize_text_field(wp_unslash((string) $_POST[$k]));
                if ($uid !== '' && $uid !== 'undefined' && $uid !== 'null') {
                    break;
                }
                $uid = '';
            }
        }
        if ($uid !== '' && ctype_digit($uid)) {
            $mapped = $wpdb->get_var($wpdb->prepare("SELECT uid FROM {$wpdb->prefix}zappoppop_items WHERE id = %d", (int) $uid));
            if ($mapped) {
                $uid = (string) $mapped;
            }
        }
        return $uid;
    }

    public function delete_submission() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized capability.', 'zappoppop')]);
        }
        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        if (!(wp_verify_nonce($nonce, 'zappoppop_admin_nonce') || wp_verify_nonce($nonce, 'zappoppop_builder_nonce'))) {
            wp_send_json_error(['message' => __('Security verification failed.', 'zappoppop')]);
        }

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if (!$id) {
            wp_send_json_error(['message' => __('Invalid submission ID.', 'zappoppop')]);
        }

        global $wpdb;
        $table_name =$wpdb->prefix . 'zappoppop_submissions';

        // Purge files if user_uploads is set to 'delete'
        $user_uploads = zappoppop_get_setting('user_uploads', 'keep');
        if ($user_uploads === 'delete') {$row = /* phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching */ $wpdb->get_row( $wpdb->prepare( "SELECT fields_data AS payload FROM {$wpdb->prefix}zappoppop_submissions WHERE id = %d", $id ) );
            if ($row && !empty($row->payload)) {
                $payload = json_decode($row->payload, true);
                if (is_array($payload)) {$upload_dir = wp_upload_dir();
                    $base_dir = realpath($upload_dir['basedir']);
                    foreach ($payload as $key =>$val) {
                        if (is_string($val) && (strpos($key, '_path') !== false || file_exists($val))) {
                            $real_path = realpath($val);
                            if ($real_path && strpos($real_path,$base_dir) === 0) {
                                wp_delete_file( $real_path );
                            }
                        }
                    }
                }
            }
        }

        $deleted =$wpdb->delete($table_name, ['id' =>$id], ['%d']);
        if ($deleted) {
            wp_send_json_success(['message' => __('Submission deleted successfully.', 'zappoppop'), 'id' => $id]);
        } else {
            wp_send_json_error(['message' => __('Failed to delete submission.', 'zappoppop')]);
        }
    }

    public function bulk_delete_submissions() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized capability.', 'zappoppop')]);
        }
        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        if (!(wp_verify_nonce($nonce, 'zappoppop_admin_nonce') || wp_verify_nonce($nonce, 'zappoppop_builder_nonce'))) {
            wp_send_json_error(['message' => __('Security verification failed.', 'zappoppop')]);
        }

        $ids = isset($_POST['ids']) && is_array($_POST['ids']) ? array_map('intval', $_POST['ids']) : [];
        if (empty($ids)) {
            wp_send_json_error(['message' => __('No submissions selected.', 'zappoppop')]);
        }

        global $wpdb;
        $table_name =$wpdb->prefix . 'zappoppop_submissions';

        $user_uploads = zappoppop_get_setting('user_uploads', 'keep');$upload_dir = wp_upload_dir();
        $base_dir = realpath($upload_dir['basedir']);

        foreach ($ids as$id) {
            if ($user_uploads === 'delete') {$row = /* phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching */ $wpdb->get_row( $wpdb->prepare( "SELECT fields_data AS payload FROM {$wpdb->prefix}zappoppop_submissions WHERE id = %d", $id ) );
                if ($row && !empty($row->payload)) {
                    $payload = json_decode($row->payload, true);
                    if (is_array($payload)) {
                        foreach ($payload as $key =>$val) {
                            if (is_string($val) && (strpos($key, '_path') !== false || file_exists($val))) {
                                $real_path = realpath($val);
                                if ($real_path && strpos($real_path,$base_dir) === 0) {
                                    wp_delete_file( $real_path );
                                }
                            }
                        }
                    }
                }
            }
            $wpdb->delete($table_name, ['id' =>$id], ['%d']);
        }

        wp_send_json_success(['message' => __('Selected submissions deleted successfully.', 'zappoppop')]);
    }
}
