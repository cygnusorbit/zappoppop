<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- template variables are local to the method that includes this file
if (!defined('ABSPATH')) {
    exit;
}

if (!isset($settings) || !is_array($settings)) {
    $settings = get_option('zappoppop_settings', []);
}
?>
<div class="wrap zappoppop-dash zappoppop-settings-wrap">
    <?php include ZAPPOPPOP_PATH . 'templates/settings/header.php'; ?>

    <!-- Native Server-Side Notice Banner -->
    <?php if (isset($notice) && 'saved' === $notice) : ?>
        <div class="notice notice-success is-dismissible">
            <p><?php esc_html_e('Settings saved successfully!', 'zappoppop'); ?></p>
        </div>
    <?php endif; ?>

    <!-- Real-time AJAX Notification Container -->
    <div id="zappoppop-settings-alert" class="notice is-dismissible" style="display:none;">
        <p></p>
    </div>

    <!-- Navigation Tab Buttons (Clean Text - No Icons) -->
    <nav class="zappoppop-settings-tabs-nav zpp-theme-tabs" aria-label="<?php esc_attr_e('Settings Sections', 'zappoppop'); ?>">
        <button type="button" class="zappoppop-tab-btn zpp-theme-tab active" data-tab="general"><?php esc_html_e('General & Mailing', 'zappoppop'); ?></button>
        <button type="button" class="zappoppop-tab-btn zpp-theme-tab" data-tab="performance"><?php esc_html_e('Performance & Preload', 'zappoppop'); ?></button>
        <button type="button" class="zappoppop-tab-btn zpp-theme-tab" data-tab="libraries"><?php esc_html_e('Typography & Libraries', 'zappoppop'); ?></button>
        <?php do_action('zappoppop_settings_tab_buttons'); ?>
        <button type="button" class="zappoppop-tab-btn zpp-theme-tab" data-tab="tools"><?php esc_html_e('Cookie Cache & DB', 'zappoppop'); ?></button>
        <button type="button" class="zappoppop-tab-btn zpp-theme-tab" data-tab="module"><?php esc_html_e('Module', 'zappoppop'); ?></button>
        <button type="button" class="zappoppop-tab-btn zpp-theme-tab" data-tab="builder"><?php esc_html_e('Builder', 'zappoppop'); ?></button>
        <button type="button" class="zappoppop-tab-btn zpp-theme-tab" data-tab="pages"><?php esc_html_e('Pages', 'zappoppop'); ?></button>
        <button type="button" class="zappoppop-tab-btn zpp-theme-tab" data-tab="tracking"><?php esc_html_e('Tracking & A/B', 'zappoppop'); ?></button>
       
    </nav>

    <!-- Master Settings Form -->
    <form id="zappoppop-settings-form" method="post" action="">
        <?php wp_nonce_field('zappoppop_save_settings_action', 'zappoppop_settings_nonce'); ?>

        <!-- Tab 1 Container: General & Mailing -->
        <div id="tab-general" class="zappoppop-tab-content active" data-tab-content="general">
            <?php include ZAPPOPPOP_PATH . 'templates/settings/tab-general.php'; ?>
        </div>

        <!-- Tab 2 Container: Performance & Preload -->
        <div id="tab-performance" class="zappoppop-tab-content" data-tab-content="performance" style="display:none;">
            <?php include ZAPPOPPOP_PATH . 'templates/settings/tab-performance.php'; ?>
        </div>

        <!-- Tab 3 Container: Typography & Libraries -->
        <div id="tab-libraries" class="zappoppop-tab-content" data-tab-content="libraries" style="display:none;">
            <?php include ZAPPOPPOP_PATH . 'templates/settings/tab-libraries.php'; ?>
        </div>

        <?php do_action('zappoppop_settings_tab_panels', $settings); ?>

        <!-- Tab 6 Container: System Tools & Diagnostics -->
        <div id="tab-tools" class="zappoppop-tab-content" data-tab-content="tools" style="display:none;">
            <?php include ZAPPOPPOP_PATH . 'templates/settings/tab-tools.php'; ?>
        </div>

        <!-- Tab 7 Container: Module (Campaign Settings on/off) -->
        <div id="tab-module" class="zappoppop-tab-content" data-tab-content="module" style="display:none;">
            <?php include ZAPPOPPOP_PATH . 'templates/settings/tab-module.php'; ?>
        </div>

        <!-- Tab 8 Container: Builder tools -->
        <div id="tab-builder" class="zappoppop-tab-content" data-tab-content="builder" style="display:none;">
            <?php include ZAPPOPPOP_PATH . 'templates/settings/tab-builder.php'; ?>
        </div>

        <!-- Tab 9 Container: Pages (menu rename / order / hide) -->
        <div id="tab-pages" class="zappoppop-tab-content" data-tab-content="pages" style="display:none;">
            <?php include ZAPPOPPOP_PATH . 'templates/settings/tab-pages.php'; ?>
        </div>

        <!-- Tab 10 Container: Tracking & A/B -->
        <div id="tab-tracking" class="zappoppop-tab-content" data-tab-content="tracking" style="display:none;">
            <?php include ZAPPOPPOP_PATH . 'templates/settings/tab-tracking.php'; ?>
        </div>

        <!-- Bottom Action Persistence Bar (Clean Text Button - No Misaligned Icons) -->
        <div class="zappoppop-settings-submit-bar">
            <button type="submit" id="zappoppop-save-settings-btn" class="button button-primary">
                <?php esc_html_e('Save Settings', 'zappoppop'); ?>
            </button>
            <span id="zappoppop-save-settings-spinner" class="spinner" style="float:none;margin:0;"></span>
            <span id="zappoppop-save-settings-msg" style="font-weight:600;font-size:13px;"></span>
            <div class="zappoppop-settings-bottom-tools">
                <button type="button" class="button" data-zpp-defaults-save="settings" title="<?php echo esc_attr__('Use the last saved settings as the defaults', 'zappoppop'); ?>"><?php esc_html_e('Save as Default', 'zappoppop'); ?></button>
                <div class="zappoppop-reset-wrap">
                    <button type="button" class="button zpp-btn-danger" id="zappoppop-reset-btn" aria-haspopup="true" aria-expanded="false"><?php esc_html_e('Reset Defaults', 'zappoppop'); ?></button>
                    <div class="zappoppop-reset-menu" id="zappoppop-reset-menu" role="menu" hidden>
                        <button type="button" role="menuitem" data-scope="tab"><?php esc_html_e('Reset this tab', 'zappoppop'); ?></button>
                        <button type="button" role="menuitem" data-scope="all"><?php esc_html_e('Reset all settings', 'zappoppop'); ?></button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
