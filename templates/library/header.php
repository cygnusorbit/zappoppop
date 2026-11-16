<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- template variables are local to the method that includes this file
if (!defined('ABSPATH')) {
    exit;
}
$bar_title = __('Popups Library', 'zappoppop');
$bar_icon  = 'dashicons-category';
$bar_actions = ZAPPOPPOP_PATH . 'templates/library/bar-actions.php';
if (!class_exists('ZapPopPop_Ajax_Templates')) { require_once ZAPPOPPOP_PATH . 'includes/ajax/class-ajax-templates.php'; }
?>
<h1 class="screen-reader-text"><?php esc_html_e('Popups & Templates Library', 'zappoppop'); ?></h1>
<?php include ZAPPOPPOP_PATH . 'templates/partials/dash-bar.php'; ?>
<?php include ZAPPOPPOP_PATH . 'templates/library/import-zone.php'; ?>

<div class="zappoppop-library-header">
    <div class="zappoppop-library-toolbar">
        <div class="zappoppop-library-intro">
            <h2><?php esc_html_e('Popups & Templates Library', 'zappoppop'); ?></h2>
            <p><?php esc_html_e('Browse pre-configured, high-converting templates and import them directly into the visual builder.', 'zappoppop'); ?></p>
        </div>
        <div class="zappoppop-library-search-wrap">
            <span class="dashicons dashicons-search"></span>
            <input type="text" id="zappoppop-lib-search" placeholder="<?php echo esc_attr__('Search templates by keyword...', 'zappoppop'); ?>" autocomplete="off">
        </div>
    </div>

    <div class="zappoppop-lib-categories zappoppop-library-categories">
        <button type="button" class="zappoppop-cat-btn active" data-cat="all"><?php esc_html_e('All Templates', 'zappoppop'); ?></button>
        <?php foreach (ZapPopPop_Ajax_Templates::get_categories() as $zpp_cat) : ?>
        <button type="button" class="zappoppop-cat-btn" data-cat="<?php echo esc_attr($zpp_cat['slug']); ?>"><?php echo esc_html($zpp_cat['label']); ?></button>
        <?php endforeach; ?>
    </div>
</div>
