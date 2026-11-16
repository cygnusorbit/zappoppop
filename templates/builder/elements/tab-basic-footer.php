<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<!-- DECOMPOSED TAB MODULE: CONTENT SHARED FOOTER (AUTOCOMPLETE, ANIMATION, REQUIRED) -->
<div class="zappoppop-insp-card" id="insp-section-gp-basic-shared" style="background:#0f172a;border:1px solid #1e293b;border-radius:8px;padding:12px;margin-top:12px;">
    <div class="zappoppop-prop-row" id="insp-row-gp-autocomplete" style="display:none;margin-bottom:12px;">
        <label class="zappoppop-prop-label" for="prop-gp-autocomplete" style="display:block;font-size:10px;font-weight:700;color:#94a3b8;margin-bottom:4px;text-transform:uppercase;"><?php esc_html_e('AUTOCOMPLETE ATTRIBUTE', 'zappoppop'); ?></label>
        <select id="prop-gp-autocomplete" class="widefat zappoppop-select zappoppop-input" style="font-size:12px;">
            <option value="off"><?php esc_html_e('Disabled (off)', 'zappoppop'); ?></option>
            <option value="on"><?php esc_html_e('Enabled (on)', 'zappoppop'); ?></option>
            <option value="email"><?php esc_html_e('Email (email)', 'zappoppop'); ?></option>
            <option value="name"><?php esc_html_e('Full Name (name)', 'zappoppop'); ?></option>
            <option value="given-name"><?php esc_html_e('First Name (given-name)', 'zappoppop'); ?></option>
            <option value="family-name"><?php esc_html_e('Last Name (family-name)', 'zappoppop'); ?></option>
            <option value="tel"><?php esc_html_e('Telephone (tel)', 'zappoppop'); ?></option>
            <option value="organization"><?php esc_html_e('Organization (organization)', 'zappoppop'); ?></option>
            <option value="street-address"><?php esc_html_e('Street Address (street-address)', 'zappoppop'); ?></option>
            <option value="postal-code"><?php esc_html_e('Postal Code (postal-code)', 'zappoppop'); ?></option>
        </select>
    </div>

    <div class="zappoppop-prop-row" id="insp-row-gp-animation">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
            <label class="zappoppop-prop-label" style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;margin:0;"><?php esc_html_e('ANIMATION', 'zappoppop'); ?></label>
            <button type="button" id="zappoppop-btn-play-elem-anim" class="button button-small" style="background:#1e293b;border:1px solid #334155;color:#38bdf8;font-size:10px;line-height:1.8;padding:0 8px;height:22px;cursor:pointer;">&#9654; Play</button>
        </div>
        <div class="zappoppop-gp-2col-grid">
            <div class="zappoppop-gp-col" style="grid-column:1 / -1;">
                <select id="prop-anim-in" class="widefat zappoppop-select zappoppop-input" style="font-size:12px;">
                    <option value="none"><?php esc_html_e('None', 'zappoppop'); ?></option>
                    <option value="fadeIn">fadeIn</option>
                    <option value="fadeInUp">fadeInUp</option>
                    <option value="fadeInDown">fadeInDown</option>
                    <option value="fadeInLeft">fadeInLeft</option>
                    <option value="fadeInRight">fadeInRight</option>
                    <option value="zoomIn">zoomIn</option>
                    <option value="bounceIn">bounceIn</option>
                    <option value="slideInUp">slideInUp</option>
                    <option value="slideInDown">slideInDown</option>
                </select>
                <span class="zappoppop-gp-sublabel"><?php esc_html_e('Appearance', 'zappoppop'); ?></span>
            </div>
            <div class="zappoppop-gp-col">
                <input type="number" id="prop-anim-duration" class="widefat zappoppop-input" value="1000" min="0" step="50" style="font-size:12px;">
                <span class="zappoppop-gp-sublabel"><?php esc_html_e('Duration (ms)', 'zappoppop'); ?></span>
            </div>
            <div class="zappoppop-gp-col">
                <input type="number" id="prop-anim-delay" class="widefat zappoppop-input" value="0" min="0" step="50" style="font-size:12px;">
                <span class="zappoppop-gp-sublabel"><?php esc_html_e('Start delay (ms)', 'zappoppop'); ?></span>
            </div>
            <div class="zappoppop-gp-col" style="grid-column:1 / -1;">
                <select id="prop-anim-start" class="widefat zappoppop-select zappoppop-input" style="font-size:12px;">
                    <option value="after"><?php esc_html_e('Start animation after below layer (Default)', 'zappoppop'); ?></option>
                    <option value="with"><?php esc_html_e('Start same time when load', 'zappoppop'); ?></option>
                </select>
                <span class="zappoppop-gp-sublabel"><?php esc_html_e('Start Animation', 'zappoppop'); ?></span>
            </div>
        </div>
    </div>

    <div class="zappoppop-prop-row zappoppop-gp-switch-row" id="insp-row-gp-required" style="display:none;margin-top:12px;padding-top:10px;border-top:1px solid #1e293b;">
        <label class="zappoppop-prop-label" for="prop-gp-required" style="font-size:11px;font-weight:700;color:#e2e8f0;text-transform:uppercase;"><?php esc_html_e('REQUIRED FIELD', 'zappoppop'); ?></label>
        <label class="zappoppop-gp-switch">
            <input type="checkbox" id="prop-gp-required">
            <span class="zappoppop-gp-slider"></span>
        </label>
    </div>
</div>
