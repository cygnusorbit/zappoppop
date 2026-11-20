<?php
if (!defined('ABSPATH')) exit;
?>
<!-- DECOMPOSED MODULE 10: TEXT INPUT (text / textfield) -->
<div class="element-panel zappoppop-panel-specific" id="panel-elem-text" data-elem-family="form" style="display:none;background:#0f172a;border:1px solid #1e293b;border-radius:8px;padding:12px;margin-bottom:14px;">
    <div style="font-size:10px;font-weight:800;color:#38bdf8;text-transform:uppercase;margin-bottom:10px;letter-spacing:0.5px;"><?php esc_html_e('Text Input Settings', 'zappoppop'); ?></div>
    <label style="display:block;font-size:10px;font-weight:700;color:#94a3b8;margin-bottom:4px;text-transform:uppercase;"><?php esc_html_e('Placeholder Text', 'zappoppop'); ?></label>
    <input type="text" id="prop-text-placeholder" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="placeholder" placeholder="<?php esc_attr_e('Enter your name...', 'zappoppop'); ?>" style="margin-bottom:10px;font-size:12px;">
    <div style="display:grid;grid-template-columns:1fr;gap:10px;">
        <div>
            <label style="display:block;font-size:10px;font-weight:700;color:#94a3b8;margin-bottom:4px;text-transform:uppercase;"><?php esc_html_e('Max Length', 'zappoppop'); ?></label>
            <input type="number" id="prop-text-maxlength" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="maxLength" min="0" max="500" placeholder="<?php esc_attr_e('Unlimited', 'zappoppop'); ?>" style="font-size:12px;">
        </div>
        <div>
            <label style="display:block;font-size:10px;font-weight:700;color:#94a3b8;margin-bottom:4px;text-transform:uppercase;"><?php esc_html_e('Browser Autocomplete', 'zappoppop'); ?></label>
            <select id="prop-text-autocomplete" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="autocomplete" style="font-size:12px;">
                <option value="on"><?php esc_html_e('On (Default)', 'zappoppop'); ?></option>
                <option value="off"><?php esc_html_e('Off', 'zappoppop'); ?></option>
                <option value="name"><?php esc_html_e('Full Name (name)', 'zappoppop'); ?></option>
                <option value="given-name"><?php esc_html_e('First Name (given-name)', 'zappoppop'); ?></option>
                <option value="family-name"><?php esc_html_e('Last Name (family-name)', 'zappoppop'); ?></option>
                <option value="organization"><?php esc_html_e('Company (organization)', 'zappoppop'); ?></option>
                <option value="honorific-prefix"><?php esc_html_e('Title / Prefix (honorific-prefix)', 'zappoppop'); ?></option>
                <option value="additional-name"><?php esc_html_e('Middle Name (additional-name)', 'zappoppop'); ?></option>
                <option value="nickname"><?php esc_html_e('Nickname (nickname)', 'zappoppop'); ?></option>
                <option value="username"><?php esc_html_e('Username (username)', 'zappoppop'); ?></option>
                <option value="organization-title"><?php esc_html_e('Job Title (organization-title)', 'zappoppop'); ?></option>
                <option value="email"><?php esc_html_e('Email Address (email)', 'zappoppop'); ?></option>
                <option value="tel"><?php esc_html_e('Telephone (tel)', 'zappoppop'); ?></option>
                <option value="street-address"><?php esc_html_e('Street Address (street-address)', 'zappoppop'); ?></option>
                <option value="address-line1"><?php esc_html_e('Address Line 1 (address-line1)', 'zappoppop'); ?></option>
                <option value="address-line2"><?php esc_html_e('Address Line 2 (address-line2)', 'zappoppop'); ?></option>
                <option value="address-level2"><?php esc_html_e('City (address-level2)', 'zappoppop'); ?></option>
                <option value="address-level1"><?php esc_html_e('State / Province (address-level1)', 'zappoppop'); ?></option>
                <option value="postal-code"><?php esc_html_e('ZIP / Postal Code (postal-code)', 'zappoppop'); ?></option>
                <option value="country-name"><?php esc_html_e('Country (country-name)', 'zappoppop'); ?></option>
                <option value="bday"><?php esc_html_e('Birthday (bday)', 'zappoppop'); ?></option>
                <option value="url"><?php esc_html_e('Website URL (url)', 'zappoppop'); ?></option>
                <option value="one-time-code"><?php esc_html_e('One-Time Code (one-time-code)', 'zappoppop'); ?></option>
            </select>
        </div>
    </div>
</div>
