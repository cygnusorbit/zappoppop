<?php
if (!defined('ABSPATH')) exit;
?>
<!-- DATA & VALUE BINDING SUITE -->
<div class="zappoppop-insp-section" id="insp-section-form-data" style="background:#0f172a; border:1px solid #1e293b; border-radius:8px; padding:12px;">
    <div style="font-size:10px; font-weight:800; color:#38bdf8; text-transform:uppercase; margin-bottom:10px;"><?php esc_html_e('Data & Value Binding', 'zappoppop'); ?></div>
    <div id="insp-row-data-default" style="margin-bottom:10px;">
        <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px; text-transform:uppercase;"><?php esc_html_e('Default Value', 'zappoppop'); ?></label>
        <input type="text" id="prop-default-value" class="widefat zappoppop-input zappoppop-data-bind" data-data-prop="defaultValue" placeholder="<?php esc_attr_e('Initial field value...', 'zappoppop'); ?>" style="font-size:12px;" />
    </div>
    <div id="insp-row-data-dynamic" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <label for="prop-dynamic-default" style="font-size:11px; font-weight:700; color:#e2e8f0; margin:0; cursor:pointer;"><?php esc_html_e('Dynamic Default (URL / Cookie)', 'zappoppop'); ?></label>
        <label class="zappoppop-insp-switch" title="<?php esc_attr_e('Toggle', 'zappoppop'); ?>"><input type="checkbox" id="prop-dynamic-default" class="zappoppop-data-bind" data-data-prop="dynamicDefault" /><span class="zappoppop-insp-slider"></span></label>
    </div>
    <div id="prop-dynamic-param-row" style="margin-bottom:10px;">
        <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px; text-transform:uppercase;"><?php esc_html_e('URL Parameter / Cookie Name', 'zappoppop'); ?></label>
        <input type="text" id="prop-dynamic-param" class="widefat zappoppop-input zappoppop-data-bind" data-data-prop="paramName" placeholder="<?php esc_attr_e('e.g. utm_source or user_email', 'zappoppop'); ?>" style="font-size:12px;" />
    </div>
    <div id="insp-row-data-readonly" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <label for="prop-readonly" style="font-size:11px; font-weight:700; color:#e2e8f0; margin:0; cursor:pointer;"><?php esc_html_e('Read Only Field', 'zappoppop'); ?></label>
        <label class="zappoppop-insp-switch" title="<?php esc_attr_e('Toggle', 'zappoppop'); ?>"><input type="checkbox" id="prop-readonly" class="zappoppop-data-bind" data-data-prop="readOnly" /><span class="zappoppop-insp-slider"></span></label>
    </div>
    <div id="insp-row-data-save" style="display:flex; justify-content:space-between; align-items:center;">
        <label for="prop-save-to-db" style="font-size:11px; font-weight:700; color:#e2e8f0; margin:0; cursor:pointer;"><?php esc_html_e('Save to Database', 'zappoppop'); ?></label>
        <label class="zappoppop-insp-switch" title="<?php esc_attr_e('Toggle', 'zappoppop'); ?>"><input type="checkbox" id="prop-save-to-db" class="zappoppop-data-bind" data-data-prop="saveToDb" checked /><span class="zappoppop-insp-slider"></span></label>
    </div>
</div>
