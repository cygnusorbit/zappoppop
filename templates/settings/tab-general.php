<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- template variables are local to the method that includes this file
if (!defined('ABSPATH')) {
    exit;
}

$sender_name      = isset($settings['sender_name']) ? $settings['sender_name'] : (isset($settings['from_name']) ? $settings['from_name'] : get_bloginfo('name'));
$sender_email     = isset($settings['sender_email']) ? $settings['sender_email'] : (isset($settings['from_email']) ? $settings['from_email'] : get_option('admin_email'));
$admin_email      = isset($settings['admin_email']) ? $settings['admin_email'] : get_option('admin_email');
$csv_separator    = isset($settings['csv_separator']) ? $settings['csv_separator'] : ',';
$user_uploads     = isset($settings['user_uploads']) ? $settings['user_uploads'] : 'keep';
$powered_by_badge = !empty($settings['powered_by_badge']);
$test_mode        = !empty($settings['test_mode']);
?>
<div class="zappoppop-settings-section">
    <h3><?php esc_html_e('General & Notification Settings', 'zappoppop'); ?></h3>
    <table class="form-table">
        <tr>
            <th scope="row"><label for="setting-sender-name"><?php esc_html_e('Sender Name', 'zappoppop'); ?></label></th>
            <td>
                <input type="text" id="setting-sender-name" name="settings[sender_name]" value="<?php echo esc_attr($sender_name); ?>" class="regular-text">
                <p class="description"><?php esc_html_e('Default display name shown in autoresponder emails and automated subscriber notifications.', 'zappoppop'); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="setting-sender-email"><?php esc_html_e('Sender Email', 'zappoppop'); ?></label></th>
            <td>
                <input type="email" id="setting-sender-email" name="settings[sender_email]" value="<?php echo esc_attr($sender_email); ?>" class="regular-text">
                <p class="description"><?php esc_html_e('Email address from which confirmation links and autoresponders originate.', 'zappoppop'); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="setting-admin-email"><?php esc_html_e('Lead Notification Email', 'zappoppop'); ?></label></th>
            <td>
                <input type="email" id="setting-admin-email" name="settings[admin_email]" value="<?php echo esc_attr($admin_email); ?>" class="regular-text">
                <p class="description"><?php esc_html_e('Destination inbox where new lead capture alerts and submission notices are sent.', 'zappoppop'); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="setting-csv-separator"><?php esc_html_e('CSV Export Delimiter', 'zappoppop'); ?></label></th>
            <td>
                <select id="setting-csv-separator" name="settings[csv_separator]">
                    <option value="," <?php selected($csv_separator, ','); ?>><?php esc_html_e('Comma (,)', 'zappoppop'); ?></option>
                    <option value=";" <?php selected($csv_separator, ';'); ?>><?php esc_html_e('Semicolon (;)', 'zappoppop'); ?></option>
                    <option value="tab" <?php selected($csv_separator, 'tab'); ?>><?php esc_html_e('Tab (\t)', 'zappoppop'); ?></option>
                </select>
                <p class="description"><?php esc_html_e('Column delimiter used when exporting captured submissions to CSV spreadsheets.', 'zappoppop'); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="setting-user-uploads"><?php esc_html_e('File Uploads Lifecycle', 'zappoppop'); ?></label></th>
            <td>
                <select id="setting-user-uploads" name="settings[user_uploads]">
                    <option value="keep" <?php selected($user_uploads, 'keep'); ?>><?php esc_html_e('Retain Uploads Permanently', 'zappoppop'); ?></option>
                    <option value="delete" <?php selected($user_uploads, 'delete'); ?>><?php esc_html_e('Purge When Lead Record is Deleted', 'zappoppop'); ?></option>
                </select>
                <p class="description"><?php esc_html_e('Storage policy for visitor files submitted through form upload layers.', 'zappoppop'); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php esc_html_e('Watermark Branding', 'zappoppop'); ?></th>
            <td>
                <label>
                    <input type="checkbox" name="settings[powered_by_badge]" value="1" <?php checked($powered_by_badge); ?>>
                    <?php esc_html_e('Display small "Powered by ZapPopPop" badge on public popups', 'zappoppop'); ?>
                </label>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php esc_html_e('Administrator Test Mode', 'zappoppop'); ?></th>
            <td>
                <label>
                    <input type="checkbox" name="settings[test_mode]" value="1" <?php checked($test_mode); ?>>
                    <?php esc_html_e('Enable test mode (Popups only trigger for logged-in site administrators)', 'zappoppop'); ?>
                </label>
                <p class="description"><?php esc_html_e('Draft and QA new popups on live sites without displaying them to visitors.', 'zappoppop'); ?></p>
            </td>
        </tr>
    </table>
</div>
