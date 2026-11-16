<?php
if (!defined('ABSPATH')) {
    exit;
}

class ZapPopPop_Addon_Optin {
    public function __construct() {
        add_action('template_redirect', [$this, 'handle_confirm_token']);
    }

    public function handle_confirm_token() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- the visitor is an anonymous e-mail recipient: the single-use random token in the link is the credential
        if (!isset($_GET['zappoppop_confirm'])) {
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- see above; the token is looked up with a prepared query and cleared on first use
        $token = sanitize_text_field(wp_unslash($_GET['zappoppop_confirm']));
        if (empty($token)) {
            return;
        }
        if (!preg_match('/^[A-Za-z0-9]{32}$/', $token)) {
            wp_die(esc_html__('The confirmation link is invalid or has already been used.', 'zappoppop'), esc_html__('Verification Error', 'zappoppop'), ['response' => 400]);
        }

        global $wpdb;
        $subs_table  = $wpdb->prefix . 'zappoppop_submissions';
        $items_table = $wpdb->prefix . 'zappoppop_items';

        // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter -- table name is built from $wpdb->prefix and every value is prepared
        $submission = $wpdb->get_row($wpdb->prepare(
            /* phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- table name is built from $wpdb->prefix and every value is prepared */ "SELECT * FROM {$subs_table} WHERE confirm_token = %s LIMIT 1",
            $token
        ), ARRAY_A);

        if (!$submission) {
            wp_die(esc_html__('The confirmation link is invalid or has already been used.', 'zappoppop'), esc_html__('Verification Error', 'zappoppop'), ['response' => 400]);
        }

        if ($submission['status'] === 'confirmed') {
            wp_die(esc_html__('Your subscription has already been confirmed. Thank you!', 'zappoppop'), esc_html__('Already Confirmed', 'zappoppop'), ['response' => 200]);
        }

        // Transition status to confirmed and invalidate token
        $wpdb->update(
            $subs_table,
            ['status' => 'confirmed', 'confirm_token' => ''],
            ['id' => $submission['id']],
            ['%s', '%s'],
            ['%d']
        );

        // Increment confirmation counter on popup record
        if (!empty($submission['popup_uid'])) {
            // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter -- table name is built from $wpdb->prefix and every value is prepared
            $wpdb->query($wpdb->prepare(
                /* phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- table name is built from $wpdb->prefix and every value is prepared */ "UPDATE {$items_table} SET confirmations = confirmations + 1 WHERE uid = %s",
                $submission['popup_uid']
            ));
        }

        $zappoppop_row = !empty($submission['popup_uid'])
            /* phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- table name is built from $wpdb->prefix and every value is prepared */ ? $wpdb->get_row($wpdb->prepare("SELECT title, data FROM {$items_table} WHERE uid = %s", $submission['popup_uid']), ARRAY_A) : null;
        $zappoppop_cfg = $zappoppop_row ? json_decode((string) $zappoppop_row['data'], true) : array();
        if (!is_array($zappoppop_cfg)) { $zappoppop_cfg = array(); }
        $zappoppop_thank    = ZapPopPop_Module_Bridge::confirmation_thank($zappoppop_cfg, $submission['email']);
        $zappoppop_dopt_msg = $zappoppop_thank['message'];

        if ($zappoppop_row) {
            $zappoppop_fields  = (array) json_decode(isset($submission['fields_data']) ? (string) $submission['fields_data'] : '[]', true);
            $zappoppop_country = isset($submission['country_code']) ? $submission['country_code'] : '';
            // Notifications: "After double opt-in confirmation"
            if (!class_exists('ZapPopPop_Notifications')) { require_once ZAPPOPPOP_PATH . 'includes/class-zappoppop-notifications.php'; }
            ZapPopPop_Notifications::dispatch('after_confirmation', $submission['popup_uid'], $zappoppop_row['title'], $zappoppop_cfg, $submission['email'], $zappoppop_fields, (int) $submission['id'], $zappoppop_country);
            // Extension modules (Pro): MailChimp / GetResponse "After double opt-in confirmation"
            ZapPopPop_Module_Bridge::after_confirmation(array(
                'uid' => $submission['popup_uid'], 'title' => $zappoppop_row['title'], 'config' => $zappoppop_cfg, 'email' => $submission['email'],
                'fields' => $zappoppop_fields, 'submission_id' => (int) $submission['id'], 'country' => $zappoppop_country,
            ));
        }

        zappoppop_log_event('optin_confirmed', 'Double opt-in confirmed for ' . $submission['email'], [
            'popup_uid'     => $submission['popup_uid'],
            'submission_id' => $submission['id']
        ]);

        if ($zappoppop_thank['url'] !== '') {
            /* phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- the thank-you page is chosen by the administrator and may be on another domain */ wp_redirect(esc_url_raw($zappoppop_thank['url']));
            exit;
        }

        // Render clean confirmation screen
        ?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?>>
        <head>
            <meta charset="<?php bloginfo('charset'); ?>">
            <title><?php esc_html_e('Subscription Confirmed', 'zappoppop'); ?></title>
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <?php echo ZapPopPop_Libraries::style_tag('zappoppop-optin-confirm'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- markup printed by wp_print_styles() ?>
        </head>
        <body>
            <div class="card">
                <div class="icon">&#10004;</div>
                <?php if ($zappoppop_dopt_msg === '') : ?><h1><?php esc_html_e('Subscription Confirmed!', 'zappoppop'); ?></h1><?php endif; ?>
                <?php if ($zappoppop_dopt_msg !== '') : ?>
                <div class="zappoppop-dopt-thanks"><?php echo wp_kses_post(wpautop($zappoppop_dopt_msg)); ?></div>
                <?php else : ?>
                <p><?php esc_html_e('Thank you. Your email address', 'zappoppop'); ?> <strong><?php echo esc_html($submission['email']); ?></strong> <?php esc_html_e('has been verified successfully.', 'zappoppop'); ?></p>
                <?php endif; ?>
                <a href="<?php echo esc_url(home_url()); ?>" class="btn"><?php esc_html_e('Return to Site', 'zappoppop'); ?></a>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}
