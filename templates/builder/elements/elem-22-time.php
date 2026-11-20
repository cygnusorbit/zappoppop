<?php
if (!defined('ABSPATH')) exit;
?>
<!-- DECOMPOSED MODULE 22: TIME PICKER (time) -->
<div class="element-panel zappoppop-panel-specific" id="panel-elem-time" data-elem-family="form" style="display:none;background:#0f172a;border:1px solid #1e293b;border-radius:8px;padding:12px;margin-bottom:14px;">
    <div style="font-size:10px;font-weight:800;color:#38bdf8;text-transform:uppercase;margin-bottom:10px;letter-spacing:0.5px;"><?php esc_html_e('Time Picker Settings', 'zappoppop'); ?></div>
    <label style="display:block;font-size:10px;font-weight:700;color:#94a3b8;margin-bottom:4px;text-transform:uppercase;"><?php esc_html_e('Time Placeholder', 'zappoppop'); ?></label>
    <input type="text" id="prop-time-placeholder" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="placeholder" placeholder="<?php esc_attr_e('09:00 AM', 'zappoppop'); ?>" style="margin-bottom:10px;font-size:12px;">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
        <div>
            <label style="display:block;font-size:10px;font-weight:700;color:#94a3b8;margin-bottom:4px;text-transform:uppercase;"><?php esc_html_e('Clock Format', 'zappoppop'); ?></label>
            <select id="prop-time-format" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="timeFormat" style="font-size:12px;">
                <option value="12h"><?php esc_html_e('12-Hour (AM / PM)', 'zappoppop'); ?></option>
                <option value="24h"><?php esc_html_e('24-Hour (00:00–23:59)', 'zappoppop'); ?></option>
            </select>
        </div>
        <div>
            <label style="display:block;font-size:10px;font-weight:700;color:#94a3b8;margin-bottom:4px;text-transform:uppercase;"><?php esc_html_e('Minute Interval', 'zappoppop'); ?></label>
            <select id="prop-time-step" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="timeStep" style="font-size:12px;">
                <option value="5"><?php esc_html_e('Every 5 Min', 'zappoppop'); ?></option>
                <option value="10"><?php esc_html_e('Every 10 Min', 'zappoppop'); ?></option>
                <option value="15"><?php esc_html_e('Every 15 Min', 'zappoppop'); ?></option>
                <option value="30"><?php esc_html_e('Every 30 Min', 'zappoppop'); ?></option>
                <option value="60"><?php esc_html_e('Every 60 Min', 'zappoppop'); ?></option>
            </select>
        </div>
    </div>
</div>
