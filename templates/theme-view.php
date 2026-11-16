<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- template variables are local to the method that includes this file
if (!defined('ABSPATH')) {
    exit;
}
$theme_schema = ZapPopPop_Admin_Theme::schema();
$theme_values = ZapPopPop_Admin_Theme::get_values();
$theme_status = isset($_GET['zpp_status']) ? sanitize_key(wp_unslash($_GET['zpp_status'])) : '';
?>
<div class="wrap zappoppop-dash zappoppop-theme-wrap">
    <?php include ZAPPOPPOP_PATH . 'templates/theme/header.php'; ?>

    <form method="post" id="zpp-theme-form" action="<?php echo esc_url(admin_url('admin.php?page=' . ZapPopPop_Admin_Theme::PAGE)); ?>">
        <?php wp_nonce_field(ZapPopPop_Admin_Theme::NONCE_ACTION, 'zpp_theme_nonce'); ?>
        <input type="hidden" name="zpp_theme_action" id="zpp-theme-action" value="save">

        <div class="zpp-theme-layout">
            <div class="zpp-theme-main">
                <?php include ZAPPOPPOP_PATH . 'templates/theme/sections.php'; ?>
            </div>
            <aside class="zpp-theme-side">
                <?php include ZAPPOPPOP_PATH . 'templates/theme/preview.php'; ?>
                <?php include ZAPPOPPOP_PATH . 'templates/theme/tools.php'; ?>
            </aside>
        </div>
    </form>
</div>
