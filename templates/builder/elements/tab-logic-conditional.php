<?php
if (!defined('ABSPATH')) exit;
?>
<!-- CONDITIONAL LOGIC & ACTION ROUTING SUITE -->
<div class="zappoppop-insp-section" id="insp-section-conditional-logic" style="background:#0f172a; border:1px solid #1e293b; border-radius:8px; padding:12px; margin-bottom:12px;">
    <div style="font-size:10px; font-weight:800; color:#38bdf8; text-transform:uppercase; margin-bottom:10px;"><?php esc_html_e('CONDITIONAL LOGIC', 'zappoppop'); ?></div>
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
    <div style="font-size:10px; font-weight:800; color:#38bdf8; text-transform:uppercase; margin-bottom:10px;"><?php esc_html_e('ACTION & CLICK ROUTING', 'zappoppop'); ?></div>
    <div id="insp-row-logic-click-action" style="margin-bottom:8px;">
        <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px; text-transform:uppercase;"><?php esc_html_e('Click Action', 'zappoppop'); ?></label>
        <select id="prop-logic-click-action" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="buttonAction" style="font-size:12px;">
            <option value="link"><?php esc_html_e('Open URL Link', 'zappoppop'); ?></option>
            <option value="step"><?php esc_html_e('Go to Canvas Step', 'zappoppop'); ?></option>
            <option value="close"><?php esc_html_e('Close Popup', 'zappoppop'); ?></option>
        </select>
    </div>
    <div id="prop-logic-link-url-row" style="margin-bottom:8px;">
        <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px; text-transform:uppercase;"><?php esc_html_e('Destination URL', 'zappoppop'); ?></label>
        <input type="text" id="prop-logic-link-url" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="linkUrl" placeholder="https://example.com" style="font-size:12px;" />
    </div>
    <div id="prop-logic-goto-canvas-row" style="display:none; margin-bottom:8px;">
        <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px; text-transform:uppercase;"><?php esc_html_e('Target Canvas #', 'zappoppop'); ?></label>
        <input type="number" id="prop-logic-goto-canvas" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="goto_canvas" min="1" max="10" value="2" style="font-size:12px;" />
    </div>
</div>
