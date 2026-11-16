<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<?php ZapPopPop_Inline_Assets::style('library-modal-categories'); ?><div class="zappoppop-modal-backdrop" id="zappoppop-lib-cats-modal" role="dialog" aria-modal="true" aria-labelledby="zappoppop-lc-title">
    <div class="lc-dlg">
        <div class="lc-hd">
            <h3 id="zappoppop-lc-title"><?php esc_html_e('Manage Categories', 'zappoppop'); ?></h3>
            <button type="button" class="lc-x" id="zappoppop-lc-close" aria-label="<?php esc_attr_e('Close', 'zappoppop'); ?>">&times;</button>
        </div>
        <div class="lc-bd">
            <p class="lc-note"><?php esc_html_e('Drag to reorder. These are the filter pills shown above the template grid.', 'zappoppop'); ?></p>
            <ul class="lc-list" id="zappoppop-lc-list"></ul>
            <div class="lc-add">
                <input type="text" id="zappoppop-lc-new" maxlength="40" placeholder="<?php esc_attr_e('New category name...', 'zappoppop'); ?>">
                <button type="button" class="button" id="zappoppop-lc-add"><?php esc_html_e('Add', 'zappoppop'); ?></button>
            </div>
            <div class="lc-msg" id="zappoppop-lc-msg"></div>
        </div>
        <div class="lc-ft">
            <button type="button" class="button" id="zappoppop-lc-reset"><?php esc_html_e('Reset to defaults', 'zappoppop'); ?></button>
            <span>
                <button type="button" class="button" id="zappoppop-lc-cancel"><?php esc_html_e('Cancel', 'zappoppop'); ?></button>
                <button type="button" class="button button-primary" id="zappoppop-lc-save"><?php esc_html_e('Save Categories', 'zappoppop'); ?></button>
            </span>
        </div>
    </div>
</div>
