<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- template variables are local to the method that includes this file
if (!defined('ABSPATH')) {
    exit;
}
if (!class_exists('ZapPopPop_Tracking')) {
    require_once ZAPPOPPOP_PATH . 'includes/class-zappoppop-tracking.php';
}
$t = function ($k) { return ZapPopPop_Tracking::get($k); };

/* Checkbox row: hidden 0 + checkbox 1 so an unchecked box really saves as off */
$chk = function ($key, $title, $label, $desc = '') use ($t) {
    ?>
    <tr>
        <th scope="row"><?php echo esc_html($title); ?></th>
        <td>
            <input type="hidden" name="settings[<?php echo esc_attr($key); ?>]" value="0">
            <label><input type="checkbox" name="settings[<?php echo esc_attr($key); ?>]" value="1" <?php checked(!empty($t($key))); ?>> <?php echo esc_html($label); ?></label>
            <?php if ($desc !== '') : ?><p class="description"><?php echo esc_html($desc); ?></p><?php endif; ?>
        </td>
    </tr>
    <?php
};
$num = function ($key, $title, $min, $max, $unit = '', $desc = '') use ($t) {
    ?>
    <tr>
        <th scope="row"><label for="zpp-<?php echo esc_attr($key); ?>"><?php echo esc_html($title); ?></label></th>
        <td>
            <input type="number" id="zpp-<?php echo esc_attr($key); ?>" name="settings[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($t($key)); ?>" min="<?php echo (int) $min; ?>" max="<?php echo (int) $max; ?>" class="small-text"> <?php echo esc_html($unit); ?>
            <?php if ($desc !== '') : ?><p class="description"><?php echo esc_html($desc); ?></p><?php endif; ?>
        </td>
    </tr>
    <?php
};
?>
<div class="zappoppop-settings-section">
    <h3><?php esc_html_e('Impression Tracking', 'zappoppop'); ?></h3>
    <table class="form-table">
        <?php $chk('track_impressions', __('Count Impressions', 'zappoppop'), __('Record an impression each time a popup is shown', 'zappoppop')); ?>
        <?php $chk('track_once_session', __('Once Per Session', 'zappoppop'), __('Count each popup once per browser session', 'zappoppop')); ?>
        <?php $chk('track_exclude_admins', __('Exclude Administrators', 'zappoppop'), __('Do not count views from logged-in administrators', 'zappoppop')); ?>
        <?php $chk('track_exclude_bots', __('Exclude Bots', 'zappoppop'), __('Ignore crawlers and headless browsers (server-side)', 'zappoppop')); ?>
    </table>

    <?php do_action('zappoppop_tracking_tab_sections', $t, $chk, $num); ?>

    <h3><?php esc_html_e('Privacy', 'zappoppop'); ?></h3>
    <table class="form-table">
        <?php $chk('privacy_anonymize_ip', __('Anonymise IP Addresses', 'zappoppop'), __('Store lead IPs with the last part removed', 'zappoppop')); ?>
        <?php $chk('privacy_respect_dnt', __('Respect Do-Not-Track', 'zappoppop'), __('Do not count impressions when the browser sends DNT', 'zappoppop')); ?>
        <tr>
            <th scope="row"><label for="zpp-privacy_consent_cookie"><?php esc_html_e('Consent Cookie Name', 'zappoppop'); ?></label></th>
            <td>
                <input type="text" id="zpp-privacy_consent_cookie" name="settings[privacy_consent_cookie]" value="<?php echo esc_attr($t('privacy_consent_cookie')); ?>" class="regular-text" autocomplete="off">
                <p class="description"><?php esc_html_e('If set, impressions are only counted while this cookie exists (e.g. cookie_notice_accepted).', 'zappoppop'); ?></p>
            </td>
        </tr>
    </table>

    <h3><?php esc_html_e('Frequency Capping (global)', 'zappoppop'); ?></h3>
    <table class="form-table">
        <?php $num('freq_max_per_page', __('Max Popups per Page', 'zappoppop'), 0, 20, __('(0 = unlimited)', 'zappoppop')); ?>
        <?php $num('freq_min_gap', __('Minimum Gap Between Popups', 'zappoppop'), 0, 3600, __('seconds (0 = off)', 'zappoppop')); ?>
    </table>

    <h3><?php esc_html_e('Rate Limiting & CORS', 'zappoppop'); ?></h3>
    <table class="form-table">
        <?php $num('rate_limit_impr', __('Impression Requests per IP', 'zappoppop'), 1, 600, __('per minute', 'zappoppop')); ?>
        <?php $num('rate_limit_submit', __('Lead Submissions per IP', 'zappoppop'), 1, 120, __('per minute', 'zappoppop')); ?>
        <tr>
            <th scope="row"><label for="zpp-cors_origins"><?php esc_html_e('Allowed Origins', 'zappoppop'); ?></label></th>
            <td>
                <textarea id="zpp-cors_origins" name="settings[cors_origins]" rows="3" class="large-text code" placeholder="https://partner.example.com"><?php echo esc_textarea($t('cors_origins')); ?></textarea>
                <p class="description"><?php esc_html_e('One origin per line for cross-site embeds. Empty = allow any origin (current behaviour).', 'zappoppop'); ?></p>
            </td>
        </tr>
    </table>

    <h3><?php esc_html_e('Debug', 'zappoppop'); ?></h3>
    <table class="form-table">
        <?php $chk('debug_ab', __('Admin Debug Badge', 'zappoppop'), __('Show tracking and A/B variant status on the front end (administrators only)', 'zappoppop')); ?>
    </table>
</div>
