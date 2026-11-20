<?php
if (!defined('ABSPATH')) exit;
?>
<!-- CONDITIONAL LOGIC & ACTION ROUTING SUITE -->
<div class="zappoppop-insp-section" id="insp-section-conditional-logic" style="background:#0f172a; border:1px solid #1e293b; border-radius:8px; padding:12px; margin-bottom:12px;">
    <div style="font-size:10px; font-weight:800; color:#38bdf8; text-transform:uppercase; margin-bottom:10px;"><?php esc_html_e('Conditional Logic', 'zappoppop'); ?></div>
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <label for="prop-logic-enabled" style="font-size:11px; font-weight:700; color:#e2e8f0; margin:0; cursor:pointer;"><?php esc_html_e('Enable Conditional Logic', 'zappoppop'); ?></label>
        <label class="zappoppop-insp-switch" title="<?php esc_attr_e('Enable / Disable', 'zappoppop'); ?>"><input type="checkbox" id="prop-logic-enabled" class="zappoppop-logic-bind" data-logic-prop="logicEnabled" /><span class="zappoppop-insp-slider"></span></label>
    </div>
    <div id="zappoppop-logic-body" style="display:none; margin-top:10px; border-top:1px solid #1e293b; padding-top:10px;">
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:10px;">
            <div>
                <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px; text-transform:uppercase;"><?php esc_html_e('Action', 'zappoppop'); ?></label>
                <select id="prop-logic-action" class="widefat zappoppop-input zappoppop-logic-bind" data-logic-prop="logicAction" style="font-size:12px;">
                    <option value="show"><?php esc_html_e('Show Layer', 'zappoppop'); ?></option>
                    <option value="hide"><?php esc_html_e('Hide Layer', 'zappoppop'); ?></option>
                </select>
            </div>
            <div>
                <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px; text-transform:uppercase;"><?php esc_html_e('Match Mode', 'zappoppop'); ?></label>
                <select id="prop-logic-match" class="widefat zappoppop-input zappoppop-logic-bind" data-logic-prop="logicMatch" style="font-size:12px;">
                    <option value="all"><?php esc_html_e('If ALL rules match', 'zappoppop'); ?></option>
                    <option value="any"><?php esc_html_e('If ANY rule matches', 'zappoppop'); ?></option>
                </select>
            </div>
        </div>
        <div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                <span style="font-size:10px; font-weight:700; color:#94a3b8; text-transform:uppercase;"><?php esc_html_e('Rules', 'zappoppop'); ?></span>
                <button type="button" id="btn-add-logic-rule" class="button button-small" style="font-size:11px; font-weight:700;"><?php esc_html_e('+ Add rule', 'zappoppop'); ?></button>
            </div>
            <div id="zappoppop-logic-rules-list" style="display:flex; flex-direction:column; gap:8px;"></div>
        </div>
    </div>
</div>

<div class="zappoppop-insp-section" id="insp-section-action-routing" style="background:#0f172a; border:1px solid #1e293b; border-radius:8px; padding:12px;">
    <div style="font-size:10px; font-weight:800; color:#38bdf8; text-transform:uppercase; margin-bottom:10px;"><?php esc_html_e('Action & Click Routing', 'zappoppop'); ?></div>
    <div id="insp-row-logic-click-action" style="margin-bottom:8px;">
        <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px; text-transform:uppercase;"><?php esc_html_e('Click Action', 'zappoppop'); ?></label>
        <select id="prop-logic-click-action" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="buttonAction" style="font-size:12px;">
            <option value="link"><?php esc_html_e('Open URL Link', 'zappoppop'); ?></option>
            <option value="mailto"><?php esc_html_e('Send Email', 'zappoppop'); ?></option>
            <option value="tel"><?php esc_html_e('Call Phone Number', 'zappoppop'); ?></option>
            <option value="step"><?php esc_html_e('Go to Canvas Step', 'zappoppop'); ?></option>
            <option value="close"><?php esc_html_e('Close Popup', 'zappoppop'); ?></option>
            <option value="copy"><?php esc_html_e('Copy Text to Clipboard', 'zappoppop'); ?></option>
            <option value="anchor"><?php esc_html_e('Scroll to Page Anchor', 'zappoppop'); ?></option>
        </select>
    </div>
    <div id="prop-logic-link-url-row" style="margin-bottom:8px;">
        <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px; text-transform:uppercase;"><?php esc_html_e('Destination URL', 'zappoppop'); ?></label>
        <input type="text" id="prop-logic-link-url" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="linkUrl" placeholder="https://example.com" style="font-size:12px;" />
    </div>
    <div id="prop-route-value-row" style="display:none; margin-bottom:8px;">
        <label id="prop-route-value-label" style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px; text-transform:uppercase;"><?php esc_html_e('Value', 'zappoppop'); ?></label>
        <input type="text" id="prop-route-value" class="widefat zappoppop-input" placeholder="" style="font-size:12px;" />
    </div>
    <div id="prop-logic-goto-canvas-row" style="display:none; margin-bottom:8px;">
        <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px; text-transform:uppercase;"><?php esc_html_e('Target Canvas', 'zappoppop'); ?></label>
        <select id="prop-logic-goto-canvas" class="widefat zappoppop-input" style="font-size:12px;"><option value="2">Canvas 2</option></select>
    </div>
    <div id="prop-route-link-opts" style="display:flex; flex-direction:column; gap:10px; margin-bottom:10px;">
        <label class="zpp-sw"><input type="checkbox" id="prop-route-blank" /><span class="zpp-sw-track"></span><span><?php esc_html_e('Open in new tab', 'zappoppop'); ?></span></label>
        <label class="zpp-sw"><input type="checkbox" id="prop-route-nofollow" /><span class="zpp-sw-track"></span><span><code>rel="nofollow"</code></span></label>
    </div>
    <div id="prop-route-label-row" style="margin-bottom:0;">
        <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px; text-transform:uppercase;"><?php esc_html_e('Accessible Label / Tooltip', 'zappoppop'); ?></label>
        <input type="text" id="prop-route-label" class="widefat zappoppop-input" placeholder="<?php esc_attr_e('e.g. Visit our pricing page', 'zappoppop'); ?>" style="font-size:12px;" />
    </div>
</div>
