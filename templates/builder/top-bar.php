<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="zappoppop-builder-header">
    <div class="zappoppop-header-left">
        <button type="button" class="zappoppop-hdr-btn" id="zappoppop-btn-settings" title="<?php esc_attr_e('Popup Settings', 'zappoppop'); ?>"><?php esc_html_e('Settings', 'zappoppop'); ?></button>
        <span class="zappoppop-brand-title"><?php esc_html_e('ZapPopPop Builder', 'zappoppop'); ?></span>
        <div class="zappoppop-viewport-switch">
            <button type="button" class="zappoppop-hdr-btn active" id="zappoppop-viewport-desktop" data-viewport="desktop" title="<?php esc_attr_e('Desktop View (640px)', 'zappoppop'); ?>"><?php esc_html_e('Desktop', 'zappoppop'); ?></button>
            <button type="button" class="zappoppop-hdr-btn" id="zappoppop-viewport-mobile" data-viewport="mobile" title="<?php esc_attr_e('Mobile View (360px)', 'zappoppop'); ?>"><?php esc_html_e('Mobile', 'zappoppop'); ?></button>
        </div>
        <div class="zappoppop-screens-nav">
            <button type="button" class="zappoppop-screen-tab active" data-screen="1"><?php esc_html_e('Screen 1', 'zappoppop'); ?></button>
            <button type="button" class="zappoppop-screen-tab" data-screen="2"><?php esc_html_e('Screen 2', 'zappoppop'); ?></button>
            <button type="button" class="zappoppop-screen-tab" data-screen="3"><?php esc_html_e('Screen 3', 'zappoppop'); ?></button>
        </div>
    </div>

    <div class="zappoppop-header-center">
        <div class="zappoppop-align-tools">
            <button type="button" class="zappoppop-align-btn" data-align="left" title="<?php esc_attr_e('Align Left', 'zappoppop'); ?>">&#8676;</button>
            <button type="button" class="zappoppop-align-btn" data-align="center-h" title="<?php esc_attr_e('Center Horizontally', 'zappoppop'); ?>">&#8596;</button>
            <button type="button" class="zappoppop-align-btn" data-align="right" title="<?php esc_attr_e('Align Right', 'zappoppop'); ?>">&#8677;</button>
            <span class="zappoppop-v-sep"></span>
            <button type="button" class="zappoppop-align-btn" data-align="top" title="<?php esc_attr_e('Align Top', 'zappoppop'); ?>">&#8679;</button>
            <button type="button" class="zappoppop-align-btn" data-align="center-v" title="<?php esc_attr_e('Center Vertically', 'zappoppop'); ?>">&#8597;</button>
            <button type="button" class="zappoppop-align-btn" data-align="bottom" title="<?php esc_attr_e('Align Bottom', 'zappoppop'); ?>">&#8681;</button>
        </div>
        <div class="zappoppop-history-tools">
            <button type="button" class="zappoppop-hdr-btn" id="zappoppop-btn-undo" title="<?php esc_attr_e('Undo (Cmd+Z)', 'zappoppop'); ?>"><?php esc_html_e('Undo', 'zappoppop'); ?></button>
            <button type="button" class="zappoppop-hdr-btn" id="zappoppop-btn-redo" title="<?php esc_attr_e('Redo (Cmd+Y)', 'zappoppop'); ?>"><?php esc_html_e('Redo', 'zappoppop'); ?></button>
        </div>
    </div>

    <div class="zappoppop-header-right">
        <button type="button" class="zappoppop-hdr-btn" id="zappoppop-btn-embed" title="<?php esc_attr_e('Embed & Shortcode', 'zappoppop'); ?>"><?php esc_html_e('Embed', 'zappoppop'); ?></button>
        <button type="button" class="zappoppop-hdr-btn" id="zappoppop-btn-preview" title="<?php esc_attr_e('Live Preview', 'zappoppop'); ?>"><?php esc_html_e('Preview', 'zappoppop'); ?></button>
        <button type="button" class="zappoppop-hdr-btn zappoppop-btn-primary" id="zappoppop-btn-save"><span class="zappoppop-save-text"><?php esc_html_e('Save Popup', 'zappoppop'); ?></span></button>
    </div>
</div>
