<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="zappoppop-property-panel" id="zappoppop-property-panel">
    <div class="zappoppop-prop-header">
        <button type="button" class="zappoppop-prop-close-btn" id="zappoppop-property-close" title="<?php esc_attr_e('Close Panel', 'zappoppop'); ?>">
            <span class="dashicons dashicons-no-alt"></span>
        </button>
        <div class="zappoppop-prop-tabs">
            <button type="button" class="zappoppop-prop-tab active" data-tab="basic"><?php esc_html_e('Basic', 'zappoppop'); ?></button>
            <button type="button" class="zappoppop-prop-tab" data-tab="style"><?php esc_html_e('Style', 'zappoppop'); ?></button>
            <button type="button" class="zappoppop-prop-tab" data-tab="logic"><?php esc_html_e('Logic', 'zappoppop'); ?></button>
        </div>
    </div>

    <div class="zappoppop-prop-body">
        <!-- Basic Tab -->
        <div class="zappoppop-prop-section active" data-section="basic">
            <label><?php esc_html_e('NAME', 'zappoppop'); ?></label>
            <input type="text" id="zappoppop-prop-name" class="widefat">

            <div style="display:flex;gap:10px;margin-top:10px;">
                <div style="flex:1;"><label><?php esc_html_e('TOP (PX)', 'zappoppop'); ?></label><input type="number" id="zappoppop-prop-top"></div>
                <div style="flex:1;"><label><?php esc_html_e('LEFT (PX)', 'zappoppop'); ?></label><input type="number" id="zappoppop-prop-left"></div>
            </div>

            <div style="display:flex;gap:10px;margin-top:10px;">
                <div style="flex:1;"><label><?php esc_html_e('WIDTH (PX)', 'zappoppop'); ?></label><input type="number" id="zappoppop-prop-width"></div>
                <div style="flex:1;"><label><?php esc_html_e('HEIGHT (PX)', 'zappoppop'); ?></label><input type="number" id="zappoppop-prop-height"></div>
            </div>

            <label style="margin-top:12px;display:block;"><?php esc_html_e('CONTENT / VALUE', 'zappoppop'); ?></label>
            <textarea id="zappoppop-prop-content" class="widefat" rows="3"></textarea>

            <label style="margin-top:12px;display:block;"><?php esc_html_e('TARGET SCREEN (Next Step)', 'zappoppop'); ?></label>
            <input type="number" id="zappoppop-prop-goto" value="2">

            <label style="margin-top:12px;display:block;"><?php esc_html_e('CLICK ACTION', 'zappoppop'); ?></label>
            <select id="zappoppop-prop-click-act" class="widefat">
                <option value="none"><?php esc_html_e('None', 'zappoppop'); ?></option>
                <option value="close"><?php esc_html_e('Close Popup', 'zappoppop'); ?></option>
                <option value="submit"><?php esc_html_e('Submit Form', 'zappoppop'); ?></option>
            </select>
        </div>

        <!-- Style Tab -->
        <div class="zappoppop-prop-section" data-section="style" style="display:none;">
            <label><?php esc_html_e('FONT FAMILY', 'zappoppop'); ?></label>
            <select id="zappoppop-prop-font" class="widefat">
                <option value="inherit"><?php esc_html_e('Inherit', 'zappoppop'); ?></option>
                <option value="Inter"><?php esc_html_e('Inter', 'zappoppop'); ?></option>
                <option value="Roboto"><?php esc_html_e('Roboto', 'zappoppop'); ?></option>
                <option value="Poppins"><?php esc_html_e('Poppins', 'zappoppop'); ?></option>
                <option value="Montserrat"><?php esc_html_e('Montserrat', 'zappoppop'); ?></option>
            </select>

            <div style="display:flex;gap:10px;margin-top:10px;">
                <div style="flex:1;"><label><?php esc_html_e('FONT SIZE (PX)', 'zappoppop'); ?></label><input type="number" id="zappoppop-prop-fontsize" value="16"></div>
                <div style="flex:1;"><label><?php esc_html_e('TEXT COLOR', 'zappoppop'); ?></label><input type="color" id="zappoppop-prop-color" value="#1e293b"></div>
            </div>

            <div style="display:flex;gap:10px;margin-top:10px;">
                <div style="flex:1;"><label><?php esc_html_e('BACKGROUND', 'zappoppop'); ?></label><input type="color" id="zappoppop-prop-bgcolor" value="#2271b1"></div>
                <div style="flex:1;"><label><?php esc_html_e('BORDER RADIUS', 'zappoppop'); ?></label><input type="number" id="zappoppop-prop-radius" value="0"></div>
            </div>

            <label style="margin-top:12px;display:block;"><?php esc_html_e('ANIMATION ENTRANCE', 'zappoppop'); ?></label>
            <select id="zappoppop-prop-anim" class="widefat">
                <option value="none"><?php esc_html_e('None', 'zappoppop'); ?></option>
                <option value="fade"><?php esc_html_e('Fade In', 'zappoppop'); ?></option>
                <option value="slideDown"><?php esc_html_e('Slide Down', 'zappoppop'); ?></option>
                <option value="bounceIn"><?php esc_html_e('Bounce In', 'zappoppop'); ?></option>
                <option value="zoomIn"><?php esc_html_e('Zoom In', 'zappoppop'); ?></option>
            </select>
        </div>

        <!-- Logic Tab -->
        <div class="zappoppop-prop-section" data-section="logic" style="display:none;">
            <label><input type="checkbox" id="zappoppop-prop-req"> <?php esc_html_e('Mandatory Field (Required)', 'zappoppop'); ?></label>
            <label style="margin-top:10px;display:block;"><?php esc_html_e('FIELD NAME / PARAM KEY', 'zappoppop'); ?></label>
            <input type="text" id="zappoppop-prop-fieldname" class="widefat" placeholder="<?php esc_attr_e('e.g. email or qty', 'zappoppop'); ?>">
            <label style="margin-top:10px;display:block;"><?php esc_html_e('OPTIONS (For Select, Radio, Wheel, Scratch)', 'zappoppop'); ?></label>
            <textarea id="zappoppop-prop-options" class="widefat" rows="4" placeholder="Option 1:10&#10;Option 2:20"></textarea>
        </div>
    </div>
</div>
