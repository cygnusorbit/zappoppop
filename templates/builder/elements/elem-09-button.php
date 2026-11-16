<?php
if (!defined('ABSPATH')) exit;
?>
<!-- DECOMPOSED MODULE 09: BUTTON / LINK / STEP TRIGGER (button / link / link_btn / step_btn) -->
<div class="element-panel zappoppop-panel-specific" id="panel-elem-button" data-elem-family="media" style="display:none;background:#0f172a;border:1px solid #1e293b;border-radius:8px;padding:12px;margin-bottom:14px;">
    <div style="font-size:10px;font-weight:800;color:#38bdf8;text-transform:uppercase;margin-bottom:10px;letter-spacing:0.5px;"><?php esc_html_e('9. Button Label & Click Action', 'zappoppop'); ?></div>
    <label style="display:block;font-size:10px;font-weight:700;color:#94a3b8;margin-bottom:4px;text-transform:uppercase;"><?php esc_html_e('BUTTON LABEL TEXT', 'zappoppop'); ?></label>
    <input type="text" id="prop-button-label" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="content" value="Click Here" placeholder="<?php esc_attr_e('Click Here', 'zappoppop'); ?>" style="margin-bottom:10px;font-size:12px;">
    <input type="hidden" id="prop-link-text" class="zappoppop-gp-bind" data-prop="content">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:10px;">
        <div>
            <label style="display:block;font-size:10px;font-weight:700;color:#94a3b8;margin-bottom:4px;text-transform:uppercase;"><?php esc_html_e('CLICK ACTION', 'zappoppop'); ?></label>
            <select id="prop-button-action" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="buttonAction" style="font-size:12px;">
                <option value="link"><?php esc_html_e('Open URL Link', 'zappoppop'); ?></option>
                <option value="step"><?php esc_html_e('Go to Canvas Step', 'zappoppop'); ?></option>
                <option value="close"><?php esc_html_e('Close Popup', 'zappoppop'); ?></option>
            </select>
        </div>
        <div id="prop-button-step-wrap">
            <label style="display:block;font-size:10px;font-weight:700;color:#94a3b8;margin-bottom:4px;text-transform:uppercase;"><?php esc_html_e('TARGET CANVAS', 'zappoppop'); ?></label>
            <!-- ZAPPOPPOP_BTN_CANVAS_SELECT: options are built live from the available canvases (builder-button-canvas-select.js) -->
            <select id="prop-goto-canvas" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="goto_canvas" style="font-size:12px;"><option value="2"><?php esc_html_e('Canvas 2', 'zappoppop'); ?></option></select>
        </div>
    </div>
    <div id="prop-button-url-wrap">
<?php ZapPopPop_Inline_Assets::style('builder-elements-elem-09-button'); ?>
        <label style="display:block;font-size:10px;font-weight:700;color:#94a3b8;margin-bottom:4px;text-transform:uppercase;"><?php esc_html_e('DESTINATION URL', 'zappoppop'); ?></label>
        <input type="text" id="prop-link-url" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="linkUrl" placeholder="https://example.com" style="margin-bottom:10px;font-size:12px;">
        <div style="display:flex;flex-direction:column;gap:10px;">
            <label class="zpp-sw"><input type="checkbox" id="prop-link-blank" class="zappoppop-gp-bind" data-prop="linkBlank" value="1"><span class="zpp-sw-track"></span><span><?php esc_html_e('Open in new tab (<code>_blank</code>)', 'zappoppop'); ?></span></label>
            <label class="zpp-sw"><input type="checkbox" id="prop-link-nofollow" class="zappoppop-gp-bind" data-prop="nofollow" value="1"><span class="zpp-sw-track"></span><span><code>rel="nofollow"</code></span></label>
        </div>
    </div>
</div>
