<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<!-- Decomposed Module: Element Action Routing, Cookie Suppression & Merge Tokens -->
<div class="zappoppop-insp-section" id="zappoppop-logic-actions-module" style="margin-bottom:14px;">
    <div class="zappoppop-insp-section-header" style="margin-bottom:8px;">
        <h4 style="margin:0;font-size:11px;font-weight:700;color:#475569;text-transform:uppercase;"><?php esc_html_e('Element Actions & Routing', 'zappoppop'); ?></h4>
    </div>

    <!-- Contextual: Click Redirect URL (Image, Icon, Button, Shape) -->
    <div class="zappoppop-logic-action-group" data-logic-scope="click-url" style="margin-bottom:10px;">
        <label style="display:block;font-size:11px;font-weight:600;color:#475569;margin-bottom:4px;"><?php esc_html_e('On-Click Redirect URL', 'zappoppop'); ?></label>
        <input type="url" id="prop-action-url" class="widefat zappoppop-gp-bind" data-prop="click_url" placeholder="https://..." style="font-size:12px;margin-bottom:6px;">
        <label style="font-size:11.5px;color:#334155;cursor:pointer;display:inline-flex;align-items:center;gap:6px;">
            <input type="checkbox" id="prop-action-blank" class="zappoppop-gp-bind" data-prop="click_target" value="_blank">
            <?php esc_html_e('Open link in new tab (_blank)', 'zappoppop'); ?>
        </label>
    </div>

    <!-- Contextual: Close Icon Cookie Suppression (Close Element) -->
    <div class="zappoppop-logic-action-group" data-logic-scope="close-cookie" style="display:none;margin-bottom:10px;">
        <label style="display:block;font-size:11px;font-weight:600;color:#475569;margin-bottom:4px;"><?php esc_html_e('Close Suppression Cookie (Days)', 'zappoppop'); ?></label>
        <input type="number" id="prop-logic-cookie-days" class="widefat zappoppop-gp-bind" data-prop="cookie_days" min="0" max="365" value="0" style="font-size:12px;">
    </div>

    <!-- Contextual: Post-Submit Routing (Submit Button Element) -->
    <div class="zappoppop-logic-action-group" data-logic-scope="submit-routing" style="display:none;margin-bottom:10px;">
        <label style="display:block;font-size:11px;font-weight:600;color:#475569;margin-bottom:4px;"><?php esc_html_e('Post-Submit Routing', 'zappoppop'); ?></label>
        <select id="prop-logic-submit-action" class="widefat zappoppop-gp-bind" data-prop="submit_action" style="font-size:12px;margin-bottom:6px;">
            <option value="message"><?php esc_html_e('Show Confirmation / Close', 'zappoppop'); ?></option>
            <option value="next_canvas"><?php esc_html_e('Advance to Next Canvas Step', 'zappoppop'); ?></option>
            <option value="goto_canvas"><?php esc_html_e('Jump to Specific Canvas Step', 'zappoppop'); ?></option>
            <option value="redirect"><?php esc_html_e('Redirect to External URL', 'zappoppop'); ?></option>
        </select>
        <div style="display:grid;grid-template-columns:1fr 2fr;gap:6px;">
            <input type="number" id="prop-logic-goto-canvas" class="widefat zappoppop-gp-bind" data-prop="goto_canvas" min="1" max="10" placeholder="<?php esc_attr_e('Step #', 'zappoppop'); ?>" style="font-size:12px;">
            <input type="url" id="prop-logic-redirect-url" class="widefat zappoppop-gp-bind" data-prop="redirect_url" placeholder="https://redirect-url..." style="font-size:12px;">
        </div>
    </div>

    <!-- Universal: Custom Element CSS Selectors in Logic Tab -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:10px;">
        <div>
            <label style="display:block;font-size:10.5px;font-weight:600;color:#64748b;margin-bottom:3px;"><?php esc_html_e('Custom CSS Class', 'zappoppop'); ?></label>
            <input type="text" id="prop-logic-css-class" class="widefat zappoppop-gp-bind" data-prop="custom_css_class" placeholder="<?php esc_attr_e('my-layer-class', 'zappoppop'); ?>" style="font-size:12px;">
        </div>
        <div>
            <label style="display:block;font-size:10.5px;font-weight:600;color:#64748b;margin-bottom:3px;"><?php esc_html_e('Custom CSS ID', 'zappoppop'); ?></label>
            <input type="text" id="prop-logic-css-id" class="widefat zappoppop-gp-bind" data-prop="custom_css_id" placeholder="my-layer-id" style="font-size:12px;">
        </div>
    </div>

    <!-- Universal: Dynamic Personalization Merge Tokens -->
    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:8px 10px;">
        <label style="display:block;font-size:10.5px;font-weight:700;color:#475569;margin-bottom:4px;"><?php esc_html_e('Dynamic Personalization Merge Tokens', 'zappoppop'); ?></label>
        <div style="display:flex;flex-wrap:wrap;gap:5px;">
            <span class="zappoppop-token-pill zappoppop-tag-chip" data-token="{email}" style="background:#1e293b;color:#38bdf8;font-family:monospace;font-size:10.5px;padding:2px 6px;border-radius:4px;cursor:pointer;">{email}</span>
            <span class="zappoppop-token-pill zappoppop-tag-chip" data-token="{name}" style="background:#1e293b;color:#38bdf8;font-family:monospace;font-size:10.5px;padding:2px 6px;border-radius:4px;cursor:pointer;">{name}</span>
            <span class="zappoppop-token-pill zappoppop-tag-chip" data-token="{phone}" style="background:#1e293b;color:#38bdf8;font-family:monospace;font-size:10.5px;padding:2px 6px;border-radius:4px;cursor:pointer;">{phone}</span>
            <span class="zappoppop-token-pill zappoppop-tag-chip" data-token="{rating}" style="background:#1e293b;color:#38bdf8;font-family:monospace;font-size:10.5px;padding:2px 6px;border-radius:4px;cursor:pointer;">{rating}</span>
        </div>
    </div>
</div>
