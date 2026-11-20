<?php
if (!defined('ABSPATH')) {
    exit;
}

class ZapPopPop_Rest_Submissions {
    public function register_routes() {
        register_rest_route('zappoppop/v1', '/submit', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [$this, 'handle_submission'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('zappoppop/v1', '/impression', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [$this, 'handle_impression'],
            'permission_callback' => '__return_true',
        ]);
    }

    public function handle_impression(WP_REST_Request $request) {
        return ZapPopPop_Tracking::rest_impression($request);
    }

    public function handle_submission(WP_REST_Request $request) {
        if (!ZapPopPop_Tracking::verify_front_nonce($request)) {
            return new WP_Error('invalid_nonce', __('Session expired. Please reload the page and try again.', 'zappoppop'), ['status' => 403]);
        }
        $params = $request->get_json_params() ?: $request->get_body_params();

        if (!ZapPopPop_Tracking::throttle('submit', (int) ZapPopPop_Tracking::get('rate_limit_submit'))) {
            return new WP_Error('rate_limited', __('Too many requests. Please try again shortly.', 'zappoppop'), ['status' => 429]);
        }

        // 1. Honeypot Bot Trap Validation
        if (!empty($params['_zappoppop_hp_email'])) {
            return new WP_Error('spam_detected', __('Spam detected by security shield.', 'zappoppop'), ['status' => 400]);
        }

        // Request-level gates (add-ons: rate limits, bot verification), same hook as the Ajax route
        $zappoppop_gate = (string) apply_filters('zappoppop_submission_gate', '', 'request', ['ip' => ZapPopPop_Security::get_client_ip(), 'recaptcha_token' => isset($params['recaptcha_token']) ? (string) $params['recaptcha_token'] : (isset($params['g-recaptcha-response']) ? (string) $params['g-recaptcha-response'] : '')]);
        if ($zappoppop_gate !== '') {
            return new WP_Error('submission_blocked', $zappoppop_gate, ['status' => 429]);
        }

        $uid   = isset($params['uid']) ? sanitize_text_field($params['uid']) : '';
        $email = isset($params['email']) ? sanitize_email($params['email']) : '';
        $fields = isset($params['fields']) ? (array)$params['fields'] : [];
        $visitor_country = isset($params['country']) ? sanitize_text_field($params['country']) : '';
        $quiz_score = isset($params['quiz_score']) ? intval($params['quiz_score']) : null;

        if ($email === '' && is_array($fields)) {
            foreach ($fields as $fk => $fv) {
                if (is_string($fv) && is_email($fv)) { $email = sanitize_email($fv); break; }
            }
        }
        // Field-level gates (add-ons: e-mail policy), same hook as the Ajax route
        $zappoppop_gate = (string) apply_filters('zappoppop_submission_gate', '', 'fields', ['ip' => ZapPopPop_Security::get_client_ip(), 'email' => $email]);
        if ($zappoppop_gate !== '') {
            return new WP_Error('submission_rejected', $zappoppop_gate, ['status' => 422]);
        }
        $clean = [];
        foreach ((array) $fields as $fk => $fv) {
            $ck = sanitize_key($fk);
            if ($ck === '') { continue; }
            $clean[$ck] = is_array($fv) ? array_map('sanitize_text_field', $fv)
                : (($ck === 'signature' && is_string($fv) && strpos($fv, 'data:image/') === 0) ? $fv : sanitize_text_field($fv));
        }
        $fields = $clean;
        if ($email !== '' && !is_email($email)) {
            return new WP_Error('invalid_email', __('Please enter a valid email address.', 'zappoppop'), ['status' => 422]);
        }

        global $wpdb;
        $items_table = $wpdb->prefix . 'zappoppop_items';
        $subs_table  = $wpdb->prefix . 'zappoppop_submissions';

        /* phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- table name is built from $wpdb->prefix and every value is prepared */ $row = $wpdb->get_row($wpdb->prepare("SELECT title, data FROM {$items_table} WHERE uid = %s", $uid), ARRAY_A);
        if (!$row) {
            return new WP_Error('popup_not_found', __('Associated popup configuration was not found.', 'zappoppop'), ['status' => 404]);
        }

        $config = json_decode($row['data'], true) ?: [];

        // Disposable e-mail filter: runs when an Email layer of this popup has "Block Disposable Emails" on
        $zappoppop_dis = ZapPopPop_Disposable::check($config, $email);
        if ($zappoppop_dis !== '') {
            return new WP_Error('disposable_email', $zappoppop_dis, ['status' => 422]);
        }

        /* ZAPPOPPOP_PROTECT_REST: extension modules (Pro) check the lead before anything is stored */
        $zappoppop_pre = ZapPopPop_Module_Bridge::precheck($uid, $fields, $config);
        if ($zappoppop_pre['deny'] !== '') {
            return new WP_Error('repeated_submission', $zappoppop_pre['deny'], ['status' => 429]);
        }
        $zappoppop_tech   = is_array($zappoppop_pre['tech']) ? $zappoppop_pre['tech'] : [];
        $zappoppop_doi    = ZapPopPop_Module_Bridge::double_optin($config);
        $dopt_cfg        = $zappoppop_doi['cfg'];
        $is_double_optin = $zappoppop_doi['active'];

        $initial_status = $is_double_optin ? 'pending' : 'confirmed';
        $confirm_token  = $is_double_optin ? wp_generate_password(32, false) : '';

        // Increment submissions counter
        /* phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- table name is built from $wpdb->prefix and every value is prepared */ $wpdb->query($wpdb->prepare("UPDATE {$items_table} SET submissions = submissions + 1 WHERE uid = %s", $uid));
        if (!$is_double_optin) {
            /* phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- table name is built from $wpdb->prefix and every value is prepared */ $wpdb->query($wpdb->prepare("UPDATE {$items_table} SET confirmations = confirmations + 1 WHERE uid = %s", $uid));
        }

        if ($quiz_score !== null && $quiz_score > 0) {
            $fields['quiz_score'] = $quiz_score;
        }

        // Insert lead record
        $wpdb->insert(
            $subs_table,
            [
                'popup_uid'     => $uid,
                'email'         => $email,
                'fields_data'   => wp_json_encode(array_merge($fields, $zappoppop_tech)),
                'status'        => $initial_status,
                'confirm_token' => $confirm_token,
                'country_code'  => $visitor_country,
            ],
            ['%s', '%s', '%s', '%s', '%s', '%s']
        );
        $submission_id = $wpdb->insert_id;
        do_action('zappoppop_submission_saved', $submission_id);

        // Notifications: "After successful form submission"
        if (!class_exists('ZapPopPop_Notifications')) { require_once ZAPPOPPOP_PATH . 'includes/class-zappoppop-notifications.php'; }
        ZapPopPop_Notifications::dispatch('after_submission', $uid, $row['title'], $config, $email, array_merge($fields, $zappoppop_tech), $submission_id, $visitor_country);

        // Extension modules (Pro): confirmation email, autoresponder, webhook, MailChimp, GetResponse, SMS, coupon, download link
        $zappoppop_out = ZapPopPop_Module_Bridge::dispatch([
            'uid'              => $uid,
            'title'            => isset($row['title']) ? $row['title'] : '',
            'config'           => $config,
            'email'            => $email,
            'fields'           => $fields,
            'submission_id'    => $submission_id,
            'country'          => $visitor_country,
            'double_optin'     => $is_double_optin,
            'double_optin_cfg' => $dopt_cfg,
            'confirm_token'    => $confirm_token,
        ]);
        $dl_url  = $zappoppop_out['download_url'];
        $wc_code = $zappoppop_out['coupon_code'];

        $actions = $config['actions'] ?? [];
        $success_message = $is_double_optin
            ? __('Thank you! A verification link has been dispatched to your email address.', 'zappoppop')
            : (!empty($actions['success_message']) ? esc_html($actions['success_message']) : __('Thank you! Your information has been registered.', 'zappoppop'));

        $res = new WP_REST_Response([
            'success'      => true,
            'message'      => $success_message,
            'redirect_url' => (!empty($actions['redirect_url']) && !$is_double_optin) ? esc_url_raw($actions['redirect_url']) : '',
            'download_url' => $dl_url,
            'coupon_code'  => $wc_code,
        ], 200);

        ZapPopPop_Tracking::cors($res);
        return $res;
    }
}
