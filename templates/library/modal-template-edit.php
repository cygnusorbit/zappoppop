<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<?php ZapPopPop_Inline_Assets::style('library-modal-template-edit'); ?><div class="zappoppop-modal-backdrop" id="zappoppop-te-modal" role="dialog" aria-modal="true" aria-labelledby="zappoppop-te-title">
    <div class="te-dlg">
        <div class="te-hd">
            <h3 id="zappoppop-te-title"><?php esc_html_e('Edit Template', 'zappoppop'); ?></h3>
            <button type="button" class="te-x" id="zappoppop-te-close" aria-label="<?php esc_attr_e('Close', 'zappoppop'); ?>">&times;</button>
        </div>
        <div class="te-bd">
            <div class="te-grid">
                <div class="te-a-img">
                    <span class="te-l"><?php esc_html_e('Popup Image', 'zappoppop'); ?></span>
                    <div class="te-prev" id="zappoppop-te-prev" role="button" tabindex="0"><?php esc_html_e('Click to upload image', 'zappoppop'); ?></div>
                    <input type="hidden" id="zappoppop-te-image" value="">
                </div>
                <div class="te-a-btns">
                    <button type="button" class="te-btn te-blue" id="zappoppop-te-upload"><?php esc_html_e('Upload Image', 'zappoppop'); ?></button>
                    <button type="button" class="te-btn te-ghost" id="zappoppop-te-remove"><?php esc_html_e('Remove', 'zappoppop'); ?></button>
                </div>
                <div class="te-a-name">
                    <label class="te-l" for="zappoppop-te-name"><?php esc_html_e('Popup Name', 'zappoppop'); ?></label>
                    <input type="text" class="te-in" id="zappoppop-te-name" maxlength="120">
                </div>
                <div class="te-a-cat">
                    <label class="te-l" for="zappoppop-te-category"><?php esc_html_e('Category', 'zappoppop'); ?></label>
                    <select class="te-in" id="zappoppop-te-category"></select>
                </div>
                <div class="te-a-desc">
                    <label class="te-l" for="zappoppop-te-desc"><?php esc_html_e('Description', 'zappoppop'); ?></label>
                    <textarea class="te-in" id="zappoppop-te-desc" maxlength="400"></textarea>
                </div>
                <div class="te-a-size">
                    <span class="te-l"><?php esc_html_e('Canvas Size', 'zappoppop'); ?></span>
                    <div class="te-ro" id="zappoppop-te-size"></div>
                </div>
                <div class="te-a-used">
                    <span class="te-l"><?php esc_html_e('Used Element(s)', 'zappoppop'); ?> <span id="zappoppop-te-total"></span></span>
                    <div class="te-box" id="zappoppop-te-elements"></div>
                </div>
            </div>
            <div class="te-msg" id="zappoppop-te-msg"></div>
        </div>
        <div class="te-ft">
            <button type="button" class="te-btn te-ghost" id="zappoppop-te-cancel"><?php esc_html_e('Cancel', 'zappoppop'); ?></button>
            <button type="button" class="te-btn te-orange" id="zappoppop-te-update"><?php esc_html_e('Update', 'zappoppop'); ?></button>
        </div>
    </div>
</div>
