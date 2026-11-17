<?php
/* ZPP_MODULE_CATS: Module Category manager (opened from Settings > Module). Rows are built by module-categories.js. */
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="zpp-cat-backdrop" id="zpp-cat-modal" role="dialog" aria-modal="true" aria-labelledby="zpp-cat-title" hidden>
    <div class="zpp-cat-dlg" tabindex="-1">
        <div class="zpp-cat-hd">
            <h3 id="zpp-cat-title"><?php esc_html_e('Module Categories', 'zappoppop'); ?></h3>
            <button type="button" class="zpp-cat-x" id="zpp-cat-close" aria-label="<?php echo esc_attr__('Close', 'zappoppop'); ?>">&times;</button>
        </div>
        <div class="zpp-cat-bd">
            <p class="zpp-cat-note"><?php esc_html_e('Add, rename, delete and reorder categories. They appear as filter pills above the module boxes on the Modules page. Deleting a category moves its modules back to All Modules.', 'zappoppop'); ?></p>
            <ul class="zpp-cat-list zpp-cat-fixed" id="zpp-cat-fixed"></ul>
            <ul class="zpp-cat-list" id="zpp-cat-list"></ul>
            <div class="zpp-cat-add">
                <label class="screen-reader-text" for="zpp-cat-new"><?php esc_html_e('New category name', 'zappoppop'); ?></label>
                <input type="text" id="zpp-cat-new" maxlength="40" autocomplete="off" placeholder="<?php echo esc_attr__('New category name…', 'zappoppop'); ?>">
                <button type="button" class="button" id="zpp-cat-add"><?php esc_html_e('Add', 'zappoppop'); ?></button>
            </div>
            <div class="zpp-cat-msg" id="zpp-cat-msg" role="status" aria-live="polite" hidden></div>
        </div>
        <div class="zpp-cat-ft">
            <button type="button" class="button" id="zpp-cat-cancel"><?php esc_html_e('Cancel', 'zappoppop'); ?></button>
            <button type="button" class="button button-primary" id="zpp-cat-save"><?php esc_html_e('Save Categories', 'zappoppop'); ?></button>
        </div>
    </div>
</div>
