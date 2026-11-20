<?php
if (!defined('ABSPATH')) exit;
?>
<!-- DECOMPOSED MODULE 16B: CHECKBOX CHOICES GROUP (checkboxgroup) -->
<div class="element-panel zappoppop-panel-specific" id="panel-elem-checkboxgroup" data-elem-family="form" style="display:none;background:#0f172a;border:1px solid #1e293b;border-radius:8px;padding:12px;margin-bottom:14px;">
    <div style="font-size:10px;font-weight:800;color:#38bdf8;text-transform:uppercase;margin-bottom:10px;letter-spacing:0.5px;"><?php esc_html_e('Checkbox Choices (Label + Value)', 'zappoppop'); ?></div>
    <label style="display:block;font-size:10px;font-weight:700;color:#94a3b8;margin-bottom:4px;text-transform:uppercase;"><?php esc_html_e('Checkbox Choices (Label + Value)', 'zappoppop'); ?></label>
    <textarea id="prop-checkboxgroup-options" rows="4" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="optionsList" tabindex="-1" aria-hidden="true" style="display:none;"></textarea>
    <!-- ZAPPOPPOP_CHECKBOX_REPEATER -->
    <?php ZapPopPop_Inline_Assets::style('builder-elements-elem-16b-checkboxgroup'); ?>    <div style="display:flex;gap:3px;margin-bottom:3px;padding:0 3px;">
        <span style="flex:0 0 14px;"></span>
        <span style="flex:1 1 0;font-size:9px;font-weight:700;color:#64748b;text-transform:uppercase;"><?php esc_html_e('Label', 'zappoppop'); ?></span>
        <span style="flex:1 1 0;font-size:9px;font-weight:700;color:#64748b;text-transform:uppercase;"><?php esc_html_e('Value', 'zappoppop'); ?> <em style="font-weight:400;text-transform:none;"><?php esc_html_e('(optional)', 'zappoppop'); ?></em></span>
        <span style="flex:0 0 66px;"></span>
    </div>
    <div id="zappoppop-checkboxgroup-rows" style="margin-bottom:10px;"></div>
    <?php ZapPopPop_Inline_Assets::script('builder-elements-elem-16b-checkboxgroup'); ?>    <label style="display:block;font-size:10px;font-weight:700;color:#94a3b8;margin-bottom:4px;text-transform:uppercase;"><?php esc_html_e('Layout Orientation', 'zappoppop'); ?></label>
    <select id="prop-checkboxgroup-layout" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="layout" style="font-size:12px;">
        <option value="vertical"><?php esc_html_e('Vertical (Stacked)', 'zappoppop'); ?></option>
        <option value="horizontal"><?php esc_html_e('Horizontal (Inline Row)', 'zappoppop'); ?></option>
    </select>
</div>
