<?php
if (!defined('ABSPATH')) exit;
?>
<!-- DECOMPOSED MODULE 26: FORM SUBMIT BUTTON (submit / submit_btn) -->
<div class="element-panel zappoppop-panel-specific" id="panel-elem-submit" data-elem-family="media" style="display:none;background:#0f172a;border:1px solid #1e293b;border-radius:8px;padding:12px;margin-bottom:14px;">
    <div style="font-size:10px;font-weight:800;color:#10b981;text-transform:uppercase;margin-bottom:10px;letter-spacing:0.5px;"><?php esc_html_e('Submit Button & Post-Submit Routing', 'zappoppop'); ?></div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:10px;">
        <div>
            <label style="display:block;font-size:10px;font-weight:700;color:#94a3b8;margin-bottom:4px;text-transform:uppercase;"><?php esc_html_e('Submit Label', 'zappoppop'); ?></label>
            <input type="text" id="prop-submit-label" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="content" placeholder="<?php esc_attr_e('Subscribe Now', 'zappoppop'); ?>" style="font-size:12px;">
        </div>
        <div>
            <label style="display:block;font-size:10px;font-weight:700;color:#94a3b8;margin-bottom:4px;text-transform:uppercase;"><?php esc_html_e('Loading Text', 'zappoppop'); ?></label>
            <input type="text" id="prop-submit-loading" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="loadingText" placeholder="<?php esc_attr_e('Sending...', 'zappoppop'); ?>" style="font-size:12px;">
        </div>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:10px;">
        <div>
            <label style="display:block;font-size:10px;font-weight:700;color:#94a3b8;margin-bottom:4px;text-transform:uppercase;"><?php esc_html_e('POST-SUBMIT Action', 'zappoppop'); ?></label>
            <select id="prop-submit-action" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="submitAction" style="font-size:12px;">
                <option value="default"><?php esc_html_e('Show Confirmation & Close', 'zappoppop'); ?></option>
                <option value="next_canvas"><?php esc_html_e('Go to Canvas Step', 'zappoppop'); ?></option>
                <option value="redirect"><?php esc_html_e('Redirect to URL', 'zappoppop'); ?></option>
            </select>
        </div>
        <div id="prop-submit-canvas-wrap">
            <label style="display:block;font-size:10px;font-weight:700;color:#94a3b8;margin-bottom:4px;text-transform:uppercase;"><?php esc_html_e('Next Canvas #', 'zappoppop'); ?></label>
            <select id="prop-submit-canvas" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="goto_canvas" style="font-size:12px;"><option value=""><?php esc_html_e('Select a canvas...', 'zappoppop'); ?></option></select>
            <span style="display:block;font-size:9px;color:#64748b;margin-top:3px;"><?php esc_html_e('Canvas shown after a successful submit.', 'zappoppop'); ?></span>
        </div>
    </div>
    <div id="prop-submit-redirect-wrap">
        <label style="display:block;font-size:10px;font-weight:700;color:#94a3b8;margin-bottom:4px;text-transform:uppercase;"><?php esc_html_e('POST-SUBMIT Redirect URL', 'zappoppop'); ?></label>
        <input type="text" id="prop-submit-redirect" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="redirectUrl" placeholder="https://example.com/thank-you" style="font-size:12px;">
    </div>
    <div id="prop-submit-confirm-wrap" style="margin-top:10px;">
        <label style="display:block;font-size:10px;font-weight:700;color:#94a3b8;margin-bottom:4px;text-transform:uppercase;"><?php esc_html_e('Confirmation Message', 'zappoppop'); ?></label>
        <input type="text" id="prop-submit-confirm" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="confirmMessage" placeholder="<?php esc_attr_e('Thank you for your submission!', 'zappoppop'); ?>" style="font-size:12px;">
    </div>
    <!-- ZAPPOPPOP_BLOCK_EMPTY_SUBMIT -->
    <div class="zappoppop-prop-row zappoppop-gp-switch-row" style="margin-top:12px;padding-top:10px;border-top:1px solid #1e293b;">
        <label class="zappoppop-prop-label" for="prop-submit-block-empty" style="font-size:11px;font-weight:700;color:#e2e8f0;text-transform:uppercase;"><?php esc_html_e('Do Not Allow Submit on Empty Field', 'zappoppop'); ?></label>
        <label class="zappoppop-gp-switch">
            <input type="checkbox" id="prop-submit-block-empty" class="zappoppop-gp-bind" data-prop="blockEmptySubmit" data-zpp-default="1" checked>
            <span class="zappoppop-gp-slider"></span>
        </label>
    </div>
    <div id="prop-submit-empty-msg-wrap" style="margin-top:10px;">
        <label style="display:block;font-size:10px;font-weight:700;color:#94a3b8;margin-bottom:4px;text-transform:uppercase;"><?php esc_html_e('Custom Error Message', 'zappoppop'); ?></label>
        <input type="text" id="prop-submit-empty-msg" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="emptySubmitMessage" placeholder="<?php esc_attr_e('Please fill in all fields before submitting.', 'zappoppop'); ?>" style="font-size:12px;">
    </div>
</div>
