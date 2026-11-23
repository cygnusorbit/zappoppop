<?php
if (!defined('ABSPATH')) exit;
?>
<!-- ADVANCED FIELD IDENTIFIER, FILTERS & VALIDATORS SUITE -->
<?php /* ZPP_ADV_TOGGLES */ ZapPopPop_Inline_Assets::style('builder-elements-tab-advanced-form'); ?>
<div class="zappoppop-insp-section" id="insp-section-form-advanced" style="background:#0f172a; border:1px solid #1e293b; border-radius:8px; padding:12px; margin-bottom:12px;">
    <div style="font-size:10px; font-weight:800; color:#38bdf8; text-transform:uppercase; margin-bottom:10px;"><?php esc_html_e('Field Identifier', 'zappoppop'); ?></div>
    <div id="insp-row-adv-field-name" style="margin-bottom:10px;">
        <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px; text-transform:uppercase;"><?php esc_html_e('ID / Merge Token Name', 'zappoppop'); ?></label>
        <input type="text" id="prop-field-name" class="widefat zappoppop-input zappoppop-adv-bind" data-adv-prop="fieldName" placeholder="<?php esc_attr_e('auto: layer-name-a1b2c3', 'zappoppop'); ?>" style="font-size:12px;" />
        <small style="font-size:10px; color:#64748b; display:block; margin-top:3px;"><?php esc_html_e('Use as <code>{field_name}</code> in email notifications & CRM webhooks.', 'zappoppop'); ?></small>
    </div>
    <div class="zappoppop-input-mask-row" id="insp-row-adv-input-mask" style="display:none;" aria-hidden="true">
        <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px; text-transform:uppercase;"><?php esc_html_e('Input Mask', 'zappoppop'); ?></label>
        <input type="text" id="prop-input-mask" class="widefat zappoppop-input zappoppop-adv-bind" data-adv-prop="inputMask" placeholder="e.g. (999) 999-9999" style="font-size:12px;" />
    </div>
</div>

<div class="zappoppop-insp-section" id="insp-section-form-filters" style="background:#0f172a; border:1px solid #1e293b; border-radius:8px; padding:12px; margin-bottom:12px;">
    <div style="font-size:10px; font-weight:800; color:#38bdf8; text-transform:uppercase; margin-bottom:8px;"><?php esc_html_e('Sanitization Filters', 'zappoppop'); ?></div>
    <div class="zpp-adv-switches" style="display:grid; grid-template-columns:minmax(0,1fr) minmax(0,1fr); gap:10px 8px;">
        <label class="zpp-sw"><input type="checkbox" class="prop-filter-chk" value="trim" /><span class="zpp-sw-track"></span><span><?php esc_html_e('Trim Whitespace', 'zappoppop'); ?></span></label>
        <label class="zpp-sw"><input type="checkbox" class="prop-filter-chk" value="striptags" /><span class="zpp-sw-track"></span><span><?php esc_html_e('Strip Tags', 'zappoppop'); ?></span></label>
        <label class="zpp-sw"><input type="checkbox" class="prop-filter-chk" value="alpha" /><span class="zpp-sw-track"></span><span><?php esc_html_e('Alpha Only', 'zappoppop'); ?></span></label>
        <label class="zpp-sw"><input type="checkbox" class="prop-filter-chk" value="digits" /><span class="zpp-sw-track"></span><span><?php esc_html_e('Digits Only', 'zappoppop'); ?></span></label>
        <label class="zpp-sw"><input type="checkbox" class="prop-filter-chk" value="lowercase" /><span class="zpp-sw-track"></span><span><?php esc_html_e('Lowercase', 'zappoppop'); ?></span></label>
        <label class="zpp-sw"><input type="checkbox" class="prop-filter-chk" value="uppercase" /><span class="zpp-sw-track"></span><span><?php esc_html_e('Uppercase', 'zappoppop'); ?></span></label>
    </div>
</div>

<div class="zappoppop-insp-section" id="insp-section-form-validators" style="background:#0f172a; border:1px solid #1e293b; border-radius:8px; padding:12px;">
    <div style="font-size:10px; font-weight:800; color:#38bdf8; text-transform:uppercase; margin-bottom:8px;"><?php esc_html_e('Validation Rules', 'zappoppop'); ?></div>
    <div id="insp-row-val-rules" class="zpp-adv-switches" style="display:grid; grid-template-columns:minmax(0,1fr) minmax(0,1fr); gap:10px 8px; margin-bottom:10px;">
        <label class="zpp-sw"><input type="checkbox" class="prop-validator-chk" value="required" /><span class="zpp-sw-track"></span><span><?php esc_html_e('Required *', 'zappoppop'); ?></span></label>
        <label class="zpp-sw"><input type="checkbox" class="prop-validator-chk" value="email" /><span class="zpp-sw-track"></span><span><?php esc_html_e('RFC Email', 'zappoppop'); ?></span></label>
        <label class="zpp-sw"><input type="checkbox" class="prop-validator-chk" value="equal" /><span class="zpp-sw-track"></span><span><?php esc_html_e('Equal Match', 'zappoppop'); ?></span></label>
        <label class="zpp-sw"><input type="checkbox" class="prop-validator-chk" value="inarray" /><span class="zpp-sw-track"></span><span><?php esc_html_e('In Array', 'zappoppop'); ?></span></label>
        <label class="zpp-sw"><input type="checkbox" class="prop-validator-chk" value="noduplicates" /><span class="zpp-sw-track"></span><span><?php esc_html_e('No Duplicates', 'zappoppop'); ?></span></label>
    </div>
    <div id="zappoppop-val-extra-equal" class="zappoppop-val-extra" style="display:none; margin-bottom:10px;">
        <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px; text-transform:uppercase;"><?php esc_html_e('Equal Match — Value Must Equal', 'zappoppop'); ?></label>
        <input type="text" id="prop-validator-equal" class="widefat zappoppop-input" placeholder="<?php esc_attr_e('Exact value, e.g. YES', 'zappoppop'); ?>" style="font-size:12px;" />
    </div>
    <div id="zappoppop-val-extra-inarray" class="zappoppop-val-extra" style="display:none; margin-bottom:10px;">
        <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px; text-transform:uppercase;"><?php esc_html_e('In Array — Allowed Values (comma separated)', 'zappoppop'); ?></label>
        <input type="text" id="prop-validator-list" class="widefat zappoppop-input" placeholder="<?php esc_attr_e('red, green, blue', 'zappoppop'); ?>" style="font-size:12px;" />
    </div>
    <div id="insp-row-val-error">
        <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px; text-transform:uppercase;"><?php esc_html_e('Custom Validation Error Message', 'zappoppop'); ?></label>
        <input type="text" id="prop-validator-error" class="widefat zappoppop-input zappoppop-adv-bind" data-adv-prop="errorMessage" placeholder="<?php esc_attr_e('Please enter a valid value...', 'zappoppop'); ?>" style="font-size:12px;" />
    </div>
</div>
