<?php
if (!defined('ABSPATH')) exit;
?>
<!-- TAB 4: DATA TAB (FORM FAMILY ELEMENTS 10-25) -->
<div id="insp-tab-data" class="zappoppop-insp-content zappoppop-insp-tab-content zappoppop-tab-pane" data-tab-content="data" style="display:none;padding:14px;">
    <div style="background:#0f172a;border:1px solid #1e293b;border-radius:8px;padding:14px;margin-bottom:14px;">
        <div style="font-size:11px;font-weight:800;color:#38bdf8;text-transform:uppercase;margin-bottom:12px;letter-spacing:0.6px;"><?php esc_html_e('Data & Field Binding', 'zappoppop'); ?></div>

        <div style="margin-bottom:12px;">
            <label for="prop-data-default" style="display:block;font-size:10px;font-weight:700;color:#94a3b8;margin-bottom:4px;text-transform:uppercase;"><?php esc_html_e('DEFAULT VALUE', 'zappoppop'); ?></label>
            <input type="text" id="prop-data-default" class="widefat zappoppop-input" placeholder="<?php esc_attr_e('Static initial value...', 'zappoppop'); ?>" style="width:100%;height:36px;background:#090d16;border:1px solid #1e293b;color:#f8fafc;border-radius:6px;padding:0 10px;font-size:12px;">
            <span style="display:block;font-size:10px;color:#64748b;margin-top:3px;"><?php esc_html_e('Enter default value of the field.', 'zappoppop'); ?></span>
        </div>

        <div style="margin-bottom:12px;padding-top:8px;border-top:1px solid #1e293b;">
            <label style="display:flex;align-items:center;justify-content:space-between;cursor:pointer;font-size:11px;font-weight:700;color:#cbd5e1;text-transform:uppercase;">
                <span><?php esc_html_e('DYNAMIC DEFAULT VALUE', 'zappoppop'); ?></span>
                <input type="checkbox" id="prop-data-dynamic-default" value="1">
            </label>
            <span style="display:block;font-size:10px;color:#64748b;margin-top:3px;"><?php esc_html_e('Set default value dynamically from URL query parameters.', 'zappoppop'); ?></span>
        </div>

        <div id="prop-data-param-wrap" style="display:none;margin-bottom:12px;">
            <label for="prop-data-param-name" style="display:block;font-size:10px;font-weight:700;color:#94a3b8;margin-bottom:4px;text-transform:uppercase;"><?php esc_html_e('PARAMETER NAME', 'zappoppop'); ?></label>
            <input type="text" id="prop-data-param-name" class="widefat zappoppop-input" placeholder="<?php esc_attr_e('e.g. email or user_email', 'zappoppop'); ?>" style="width:100%;height:36px;background:#090d16;border:1px solid #1e293b;color:#f8fafc;border-radius:6px;padding:0 10px;font-size:12px;">
            <span style="display:block;font-size:10px;color:#64748b;margin-top:3px;"><?php esc_html_e('Enter URL parameter name (e.g. <code>?email=user@domain.com</code>).', 'zappoppop'); ?></span>
        </div>

        <div style="margin-bottom:12px;padding-top:8px;border-top:1px solid #1e293b;">
            <label style="display:flex;align-items:center;justify-content:space-between;cursor:pointer;font-size:11px;font-weight:700;color:#cbd5e1;text-transform:uppercase;">
                <span><?php esc_html_e('READ ONLY', 'zappoppop'); ?></span>
                <input type="checkbox" id="prop-data-readonly" value="1">
            </label>
            <span style="display:block;font-size:10px;color:#64748b;margin-top:3px;"><?php esc_html_e('Lock input field so visitors cannot edit its value.', 'zappoppop'); ?></span>
        </div>

        <div style="padding-top:8px;border-top:1px solid #1e293b;">
            <label style="display:flex;align-items:center;justify-content:space-between;cursor:pointer;font-size:11px;font-weight:700;color:#cbd5e1;text-transform:uppercase;">
                <span><?php esc_html_e('SAVE TO DATABASE', 'zappoppop'); ?></span>
                <input type="checkbox" id="prop-data-save-db" value="1" checked>
            </label>
            <span style="display:block;font-size:10px;color:#64748b;margin-top:3px;"><?php esc_html_e('Store submitted field value in the database record.', 'zappoppop'); ?></span>
        </div>
    </div>
</div>

<!-- TAB 5: ADVANCED TAB (FORM FAMILY ELEMENTS 10-25) -->
<div id="insp-tab-advanced" class="zappoppop-insp-content zappoppop-insp-tab-content zappoppop-tab-pane" data-tab-content="advanced" style="display:none;padding:14px;">
    <div style="background:#0f172a;border:1px solid #1e293b;border-radius:8px;padding:14px;margin-bottom:14px;">
        <div style="font-size:11px;font-weight:800;color:#38bdf8;text-transform:uppercase;margin-bottom:12px;letter-spacing:0.6px;"><?php esc_html_e('Advanced, Filters & Validators', 'zappoppop'); ?></div>

        <div style="margin-bottom:12px;">
            <label for="prop-adv-field-id" style="display:block;font-size:10px;font-weight:700;color:#94a3b8;margin-bottom:4px;text-transform:uppercase;"><?php esc_html_e('ID (FIELD IDENTIFIER)', 'zappoppop'); ?></label>
            <input type="text" id="prop-adv-field-id" class="widefat zappoppop-input" placeholder="<?php esc_attr_e('e.g. 11 or email', 'zappoppop'); ?>" style="width:100%;height:36px;background:#090d16;border:1px solid #1e293b;color:#f8fafc;border-radius:6px;padding:0 10px;font-size:12px;">
        </div>

        <div style="margin-bottom:12px;">
            <label for="prop-adv-input-mask" style="display:block;font-size:10px;font-weight:700;color:#94a3b8;margin-bottom:4px;text-transform:uppercase;"><?php esc_html_e('INPUT MASK PATTERN', 'zappoppop'); ?></label>
            <input type="text" id="prop-adv-input-mask" class="widefat zappoppop-input" placeholder="e.g. (999) 999-9999" style="width:100%;height:36px;background:#090d16;border:1px solid #1e293b;color:#f8fafc;border-radius:6px;padding:0 10px;font-size:12px;">
        </div>

        <div style="margin-bottom:14px;padding-top:10px;border-top:1px solid #1e293b;">
            <label style="display:block;font-size:10px;font-weight:700;color:#94a3b8;margin-bottom:6px;text-transform:uppercase;"><?php esc_html_e('FILTERS', 'zappoppop'); ?></label>
            <div id="zappoppop-adv-filters-list" class="zappoppop-adv-badge-wrap" style="display:flex;flex-wrap:wrap;gap:6px;min-height:32px;padding:6px;background:#090d16;border:1px solid #1e293b;border-radius:6px;margin-bottom:8px;"></div>
            <div style="display:flex;gap:5px;flex-wrap:wrap;">
                <button type="button" class="zappoppop-adv-add-btn" data-kind="filter" data-val="alpha"><?php esc_html_e('+ Alpha', 'zappoppop'); ?></button>
                <button type="button" class="zappoppop-adv-add-btn" data-kind="filter" data-val="alphanumeric"><?php esc_html_e('+ Alphanumeric', 'zappoppop'); ?></button>
                <button type="button" class="zappoppop-adv-add-btn" data-kind="filter" data-val="digits"><?php esc_html_e('+ Digits', 'zappoppop'); ?></button>
                <button type="button" class="zappoppop-adv-add-btn" data-kind="filter" data-val="regex"><?php esc_html_e('+ Regex', 'zappoppop'); ?></button>
                <button type="button" class="zappoppop-adv-add-btn" data-kind="filter" data-val="strip_tags"><?php esc_html_e('+ Strip Tags', 'zappoppop'); ?></button>
                <button type="button" class="zappoppop-adv-add-btn" data-kind="filter" data-val="trim"><?php esc_html_e('+ Trim', 'zappoppop'); ?></button>
            </div>
        </div>

        <div style="padding-top:10px;border-top:1px solid #1e293b;">
            <label style="display:block;font-size:10px;font-weight:700;color:#94a3b8;margin-bottom:6px;text-transform:uppercase;"><?php esc_html_e('VALIDATORS', 'zappoppop'); ?></label>
            <div id="zappoppop-adv-validators-list" class="zappoppop-adv-badge-wrap" style="display:flex;flex-wrap:wrap;gap:6px;min-height:32px;padding:6px;background:#090d16;border:1px solid #1e293b;border-radius:6px;margin-bottom:8px;"></div>
            <div style="display:flex;gap:5px;flex-wrap:wrap;">
                <button type="button" class="zappoppop-adv-add-btn" data-kind="validator" data-val="required"><?php esc_html_e('+ Required', 'zappoppop'); ?></button>
                <button type="button" class="zappoppop-adv-add-btn" data-kind="validator" data-val="email"><?php esc_html_e('+ Email', 'zappoppop'); ?></button>
                <button type="button" class="zappoppop-adv-add-btn" data-kind="validator" data-val="equal"><?php esc_html_e('+ Equal', 'zappoppop'); ?></button>
                <button type="button" class="zappoppop-adv-add-btn" data-kind="validator" data-val="equal_field"><?php esc_html_e('+ Equal To Field', 'zappoppop'); ?></button>
                <button type="button" class="zappoppop-adv-add-btn" data-kind="validator" data-val="in_array"><?php esc_html_e('+ In Array', 'zappoppop'); ?></button>
                <button type="button" class="zappoppop-adv-add-btn" data-kind="validator" data-val="prevent_duplicates"><?php esc_html_e('+ Prevent Duplicates', 'zappoppop'); ?></button>
            </div>
        </div>
    </div>
</div>
