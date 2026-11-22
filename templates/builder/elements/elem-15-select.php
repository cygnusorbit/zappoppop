<?php
if (!defined('ABSPATH')) exit;
?>
<!-- DECOMPOSED MODULE 15: SELECT DROPDOWN (select / dropdown) -->
<div class="element-panel zappoppop-panel-specific" id="panel-elem-select" data-elem-family="form" style="display:none;background:#0f172a;border:1px solid #1e293b;border-radius:8px;padding:12px;margin-bottom:14px;">
    <div style="font-size:10px;font-weight:800;color:#38bdf8;text-transform:uppercase;margin-bottom:10px;letter-spacing:0.5px;"><?php esc_html_e('Select Dropdown Options', 'zappoppop'); ?></div>
    <label style="display:block;font-size:10px;font-weight:700;color:#94a3b8;margin-bottom:4px;text-transform:uppercase;"><?php esc_html_e('First Option Placeholder', 'zappoppop'); ?></label>
    <input type="text" id="prop-select-first" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="firstOption" placeholder="<?php esc_attr_e('Select an option...', 'zappoppop'); ?>" style="margin-bottom:10px;font-size:12px;">
    <label style="display:block;font-size:10px;font-weight:700;color:#94a3b8;margin-bottom:4px;text-transform:uppercase;"><?php esc_html_e('Dropdown Choices (One per Line)', 'zappoppop'); ?></label>
    <textarea id="prop-select-options" rows="4" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="optionsList" placeholder="<?php echo esc_attr( implode( "\n", array( __( 'Option 1', 'zappoppop' ), __( 'Option 2', 'zappoppop' ), __( 'Option 3', 'zappoppop' ) ) ) ); ?>" style="font-size:12px;"></textarea>
</div>
