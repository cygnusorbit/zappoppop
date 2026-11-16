<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- template variables are local to the method that includes this file
if (!defined('ABSPATH')) {
    exit;
}
if (!current_user_can('manage_options')) {
    wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'zappoppop'));
}
$ext_can_upload = ZapPopPop_Modules_Installer::can_upload() && class_exists('ZipArchive');
if (!class_exists('ZipArchive')) {
    $ext_block = __('The PHP zip extension is required to upload modules. Ask your host to enable it.', 'zappoppop');
} elseif (!ZapPopPop_Modules_Installer::file_mods_allowed()) {
    $ext_block = __('File changes are disabled on this site (DISALLOW_FILE_MODS), so modules cannot be uploaded.', 'zappoppop');
} elseif (is_multisite() && !is_super_admin()) {
    $ext_block = __('Only network administrators can upload modules.', 'zappoppop');
} else {
    $ext_block = '';
}
?>
<div class="wrap zappoppop-dash zappoppop-extend-wrap">
    <?php include ZAPPOPPOP_PATH . 'templates/extend/header.php'; ?>

    <div id="zpp-ext-notice" class="zpp-mod-notice" role="status" aria-live="polite" hidden></div>
    <noscript><div class="notice notice-warning"><p><?php esc_html_e('JavaScript is required to manage modules.', 'zappoppop'); ?></p></div></noscript>

    <div class="zpp-ext-card zpp-ext-upload<?php echo $ext_can_upload ? '' : ' is-single'; ?>" id="zpp-ext-upload-card">
        <div class="zpp-ext-upload-cols">
            <?php if ($ext_can_upload) : ?>
            <div class="zpp-ext-upload-left">
                <div class="zpp-ext-drop" id="zpp-ext-drop">
                    <span class="dashicons dashicons-media-archive"></span>
                    <span class="zpp-ext-drop-text"><?php esc_html_e('Drag a module .zip here, or', 'zappoppop'); ?>
                        <label for="zpp-ext-file" class="zpp-ext-browse"><?php esc_html_e('browse', 'zappoppop'); ?></label></span>
                    <input type="file" id="zpp-ext-file" class="zpp-ext-file" accept=".zip,application/zip,application/x-zip-compressed">
                    <span class="zpp-ext-file-name" id="zpp-ext-file-name"></span>
                </div>
            </div>
            <?php endif; ?>
            <div class="zpp-ext-upload-right">
                <div class="zpp-ext-card-head">
                    <span class="dashicons dashicons-upload"></span>
                    <h3><?php esc_html_e('Upload Module', 'zappoppop'); ?></h3>
                </div>
                <?php if ($ext_can_upload) : ?>
                    <p class="zpp-ext-help"><?php
                        /* translators: %s: maximum zip size */
                        echo esc_html(sprintf(__('Upload a module .zip (module.json plus HTML, CSS and JS files, up to %s). An uploaded module stays Available until you click Install.', 'zappoppop'), size_format(ZapPopPop_Modules_Installer::MAX_ZIP)));
                    ?></p>
                    <div class="zpp-ext-upload-actions">
                        <button type="button" class="button button-primary" id="zpp-ext-upload-btn" disabled><?php esc_html_e('Upload Module', 'zappoppop'); ?></button>
                    </div>
                <?php else : ?>
                    <p class="zpp-ext-help"><?php echo esc_html($ext_block); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

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
</div>
<?php include ZAPPOPPOP_PATH . 'templates/settings/modal-module-categories.php'; /* ZAPPOPPOP_CATS_ON_EXTEND */ ?>
