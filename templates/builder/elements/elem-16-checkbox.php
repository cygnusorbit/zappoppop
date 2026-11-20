<?php
if (!defined('ABSPATH')) exit;
?>
<!-- DECOMPOSED MODULE: AGREEMENT CHECKBOX / CONSENT (checkbox / checkboxes) -->
<div class="element-panel zappoppop-panel-specific" id="panel-elem-checkbox" data-elem-family="form" style="display:none;background:#0f172a;border:1px solid #1e293b;border-radius:8px;padding:12px;margin-bottom:14px;">
    <div style="font-size:10px;font-weight:800;color:#38bdf8;text-transform:uppercase;margin-bottom:10px;letter-spacing:0.5px;"><?php esc_html_e('Agreement Checkbox & GDPR Consent', 'zappoppop'); ?></div>
    <label style="display:block;font-size:10px;font-weight:700;color:#94a3b8;margin-bottom:4px;text-transform:uppercase;"><?php esc_html_e('Checkbox Options / Consent Label (One per Line)', 'zappoppop'); ?></label>
    <textarea id="prop-checkbox-options" rows="3" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="optionsList" placeholder="I agree to the Terms &amp; Privacy Policy" style="margin-bottom:10px;font-size:12px;"></textarea>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
        <div>
            <label style="display:block;font-size:10px;font-weight:700;color:#94a3b8;margin-bottom:4px;text-transform:uppercase;"><?php esc_html_e('Layout Direction', 'zappoppop'); ?></label>
            <select id="prop-checkbox-layout" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="layout" style="font-size:12px;">
                <option value="vertical"><?php esc_html_e('Vertical (Stacked)', 'zappoppop'); ?></option>
                <option value="horizontal"><?php esc_html_e('Horizontal (Inline)', 'zappoppop'); ?></option>
            </select>
        </div>
        <div>
            <label style="display:block;font-size:10px;font-weight:700;color:#94a3b8;margin-bottom:4px;text-transform:uppercase;"><?php esc_html_e('Terms / Privacy URL', 'zappoppop'); ?></label>
            <input type="text" id="prop-checkbox-terms" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="termsUrl" placeholder="https://example.com/privacy" style="font-size:12px;">
        </div>
    </div>
</div>
