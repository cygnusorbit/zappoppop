<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- template variables are local to the method that includes this file
if (!defined('ABSPATH')) {
    exit;
}
if (!current_user_can('manage_options')) {
    wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'zappoppop'));
}
// ZAPPOPPOP_UPLOADER_REMOVED: modules come from plugins; there is no upload card.
?>
<div class="wrap zappoppop-dash zappoppop-extend-wrap">
    <?php include ZAPPOPPOP_PATH . 'templates/extend/header.php'; ?>

    <div id="zpp-ext-notice" class="zpp-mod-notice" role="status" aria-live="polite" hidden></div>
    <noscript><div class="notice notice-warning"><p><?php esc_html_e('JavaScript is required to manage modules.', 'zappoppop'); ?></p></div></noscript>

    <ul class="zpp-mod-filters zpp-ext-filters" id="zpp-ext-filters" aria-label="<?php echo esc_attr__('Filter modules', 'zappoppop'); ?>"></ul> <!-- ZAPPOPPOP_EXT_FILTERS -->

    <div class="zpp-ext-catbar"> <!-- ZAPPOPPOP_EXT_PAGER -->
        <div class="zpp-ext-cats-wrap">
            <div class="zpp-ext-cats" id="zpp-ext-cats" role="group" aria-label="<?php echo esc_attr__('Module categories', 'zappoppop'); ?>"></div>
            <button type="button" class="zpp-ext-cat-add" id="zpp-mod-cats-btn" aria-haspopup="dialog" title="<?php echo esc_attr__('Module Category', 'zappoppop'); ?>" aria-label="<?php echo esc_attr__('Module Category', 'zappoppop'); ?>"><span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span></button> <!-- ZAPPOPPOP_CATS_ON_EXTEND -->
        </div>
        <div class="zpp-mod-search">
            <label class="screen-reader-text" for="zpp-ext-search"><?php esc_html_e('Search modules', 'zappoppop'); ?></label>
            <input type="search" id="zpp-ext-search" placeholder="<?php echo esc_attr__('Search modules…', 'zappoppop'); ?>" autocomplete="off">
        </div>
    </div>

    <div class="zpp-ext-grid" id="zpp-ext-grid"></div>
    <div class="zpp-ext-pager" id="zpp-ext-pager" hidden
        data-l-items="<?php /* translators: %s: number of items. */ echo esc_attr__('%s items', 'zappoppop'); ?>"
        data-l-item="<?php echo esc_attr__('1 item', 'zappoppop'); ?>"
        data-l-per="<?php echo esc_attr__('Per page', 'zappoppop'); ?>"
        data-l-page="<?php /* translators: 1: current page number, 2: total number of pages. */ echo esc_attr__('Page %1$s of %2$s', 'zappoppop'); ?>"
        data-l-first="<?php echo esc_attr__('First page', 'zappoppop'); ?>"
        data-l-prev="<?php echo esc_attr__('Previous page', 'zappoppop'); ?>"
        data-l-next="<?php echo esc_attr__('Next page', 'zappoppop'); ?>"
        data-l-last="<?php echo esc_attr__('Last page', 'zappoppop'); ?>"></div>
    <?php include ZAPPOPPOP_PATH . 'templates/extend/addons.php'; ?>
    <?php ZapPopPop_Promo::render('pro_extensions'); /* ZAPPOPPOP_PRO_LOCK: static Pro cards, text from defaults/promo.json */ ?>
</div>
<?php include ZAPPOPPOP_PATH . 'templates/settings/modal-module-categories.php'; /* ZAPPOPPOP_CATS_ON_EXTEND */ ?>
