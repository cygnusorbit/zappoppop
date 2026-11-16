<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- template variables are local to the method that includes this file
if (!defined('ABSPATH')) {
    exit;
}

$clean_on_uninstall = !empty($settings['clean_on_uninstall']);
$cookie_epoch       = isset($settings['cookie_epoch']) ? (int) $settings['cookie_epoch'] : (int) get_option('zappoppop_cookie_epoch', 1);
$db_version         = get_option('zappoppop_db_version', defined('ZAPPOPPOP_VERSION') ? ZAPPOPPOP_VERSION : '1.0.0');
?>
<div class="zappoppop-settings-section">
    <h3><?php esc_html_e('System Diagnostics & Cookie Cache Management', 'zappoppop'); ?></h3>
    <table class="form-table">
        <tr>
            <th scope="row"><?php esc_html_e('Cookie Epoch & Reset', 'zappoppop'); ?></th>
            <td>
                <p style="margin-bottom:10px;">
                    <?php esc_html_e('Current Cookie Epoch Version:', 'zappoppop'); ?>
                    <code><span id="zappoppop-current-cookie-epoch"><?php echo esc_html($cookie_epoch); ?></span></code>
                </p>
                <button type="button" id="zappoppop-reset-cookies-btn" class="button button-secondary">
                    <?php esc_html_e('Reset All Visitor Cookies', 'zappoppop'); ?>
                </button>
                <span id="zappoppop-cookie-reset-status" style="margin-left:12px;font-weight:600;"></span>
                <p class="description" style="margin-top:8px;">
                    <?php esc_html_e('Increments the global cookie epoch. Forces frequency-capped popups to display again even if previously dismissed.', 'zappoppop'); ?>
                </p>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php esc_html_e('Database Schema Version', 'zappoppop'); ?></th>
            <td>
                <code><?php echo esc_html($db_version); ?></code>
                <p class="description"><?php esc_html_e('Current database migration schema version installed in the WordPress database.', 'zappoppop'); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php esc_html_e('Uninstall Data Removal', 'zappoppop'); ?></th>
            <td>
                <label>
                    <input type="checkbox" name="settings[clean_on_uninstall]" value="1" <?php checked($clean_on_uninstall); ?>>
                    <span style="color:#b91c1c;font-weight:600;"><?php esc_html_e('Purge all database tables and settings upon plugin deletion', 'zappoppop'); ?></span>
                </label>
                <p class="description"><?php esc_html_e('Warning: If enabled, deleting the plugin will permanently drop campaigns, submissions, and analytics tables.', 'zappoppop'); ?></p>
            </td>
        </tr>
    </table>
</div>
