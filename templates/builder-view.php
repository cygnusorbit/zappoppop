<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- template variables are local to the method that includes this file
if (!defined('ABSPATH')) {
    exit;
}

$current_uid = isset($_GET['uid']) ? sanitize_text_field(wp_unslash($_GET['uid'])) : ($popup ? $popup->uid : '');
?>
<div id="zappoppop-boot-loader" role="status" aria-live="polite" aria-label="<?php esc_attr_e('Loading builder', 'zappoppop'); ?>"><span class="zappoppop-boot-spin"></span></div>
<div class="zappoppop-builder-wrap">
    <!-- Hidden Security Nonce & Popup Identifier State -->
    <?php wp_nonce_field('zappoppop_builder_nonce', 'zappoppop-builder-nonce'); ?>
    <input type="hidden" id="zappoppop-builder-uid" value="<?php echo esc_attr($current_uid); ?>">
    <input type="hidden" id="zappoppop-builder-status" value="<?php echo esc_attr(($popup && isset($popup->status)) ? $popup->status : ''); ?>">

    <!-- 1. Top Application Toolbar (Canvas Sequence & Navigation) -->
    <?php include ZAPPOPPOP_PATH . 'templates/builder/header.php'; ?>

    <!-- 2. Horizontal Elements Ribbon Toolbar (19 Elements) -->
    <?php include ZAPPOPPOP_PATH . 'templates/builder/ribbon.php'; ?>

    <!-- Main Workspace Frame (Supports Pushing-Frame Inspector Docking) -->
    <div class="zappoppop-main-frame" style="display:flex;flex:1;overflow:hidden;position:relative;">
        <!-- 3. Visual Stage, Layers Panel & Canvas Workspace -->
        <?php include ZAPPOPPOP_PATH . 'templates/builder/canvas.php'; ?>

        <!-- 4. Pushing Frame Layer Inspector Drawer -->
        <?php include ZAPPOPPOP_PATH . 'templates/builder/drawer-inspector.php'; ?>

        <!-- 5. Popup Settings Drawer (slides in within the checker area, below top bar & ribbon) -->
        <?php include ZAPPOPPOP_PATH . 'templates/builder/drawer-settings.php'; ?>
    </div>

    <!-- 6. Embed Codes & Live Sandbox Preview Modals -->
    <?php include ZAPPOPPOP_PATH . 'templates/builder/modals.php'; ?>

    <?php ZapPopPop_Inline_Assets::script('builder-boot', array()); ?>
</div>
