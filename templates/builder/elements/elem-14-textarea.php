<?php
if (!defined('ABSPATH')) exit;
?>
<!-- DECOMPOSED MODULE 14: TEXTAREA (textarea) -->
<div class="element-panel zappoppop-panel-specific" id="panel-elem-textarea" data-elem-family="form" style="display:none;background:#0f172a;border:1px solid #1e293b;border-radius:8px;padding:12px;margin-bottom:14px;">
    <div style="font-size:10px;font-weight:800;color:#38bdf8;text-transform:uppercase;margin-bottom:10px;letter-spacing:0.5px;"><?php esc_html_e('Multiline Textarea Settings', 'zappoppop'); ?></div>
    <label style="display:block;font-size:10px;font-weight:700;color:#94a3b8;margin-bottom:4px;text-transform:uppercase;"><?php esc_html_e('Placeholder Prompt', 'zappoppop'); ?></label>
    <input type="text" id="prop-textarea-placeholder" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="placeholder" placeholder="<?php esc_attr_e('Write your message...', 'zappoppop'); ?>" style="margin-bottom:10px;font-size:12px;">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
        <div>
            <label style="display:block;font-size:10px;font-weight:700;color:#94a3b8;margin-bottom:4px;text-transform:uppercase;"><?php esc_html_e('Visible Rows (2–15)', 'zappoppop'); ?></label>
            <input type="number" id="prop-textarea-rows" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="rows" min="2" max="15" value="4" style="font-size:12px;">
        </div>
        <div>
            <label style="display:block;font-size:10px;font-weight:700;color:#94a3b8;margin-bottom:4px;text-transform:uppercase;"><?php esc_html_e('Character Limit', 'zappoppop'); ?></label>
            <input type="number" id="prop-textarea-maxlength" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="maxLength" min="0" max="5000" placeholder="<?php esc_attr_e('Unlimited', 'zappoppop'); ?>" style="font-size:12px;">
        </div>
    </div>
</div>
