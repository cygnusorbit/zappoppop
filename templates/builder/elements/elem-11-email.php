<?php
if (!defined('ABSPATH')) exit;
?>
<!-- DECOMPOSED MODULE 11: EMAIL ADDRESS (email) -->
<div class="element-panel zappoppop-panel-specific" id="panel-elem-email" data-elem-family="form" style="display:none;background:#0f172a;border:1px solid #1e293b;border-radius:8px;padding:12px;margin-bottom:14px;">
    <div style="font-size:10px;font-weight:800;color:#38bdf8;text-transform:uppercase;margin-bottom:10px;letter-spacing:0.5px;"><?php esc_html_e('Email Input Settings', 'zappoppop'); ?></div>
    <label style="display:block;font-size:10px;font-weight:700;color:#94a3b8;margin-bottom:4px;text-transform:uppercase;"><?php esc_html_e('Placeholder Email', 'zappoppop'); ?></label>
    <input type="text" id="prop-email-placeholder" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="placeholder" placeholder="<?php esc_attr_e('Enter your email...', 'zappoppop'); ?>" style="margin-bottom:10px;font-size:12px;">
    <div style="display:grid;grid-template-columns:1fr;gap:10px;">
        <div>
            <label style="display:block;font-size:10px;font-weight:700;color:#94a3b8;margin-bottom:4px;text-transform:uppercase;"><?php esc_html_e('Max Length', 'zappoppop'); ?></label>
            <input type="number" id="prop-email-maxlength" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="maxLength" min="0" max="500" placeholder="<?php esc_attr_e('Unlimited', 'zappoppop'); ?>" style="font-size:12px;">
        </div>
        <div>
            <label style="display:block;font-size:10px;font-weight:700;color:#94a3b8;margin-bottom:4px;text-transform:uppercase;"><?php esc_html_e('Browser Autocomplete', 'zappoppop'); ?></label>
            <select id="prop-email-autocomplete" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="autocomplete" style="font-size:12px;">
                <option value="on"><?php esc_html_e('On (Default) (on)', 'zappoppop'); ?></option>
                <option value="off"><?php esc_html_e('Off (off)', 'zappoppop'); ?></option>
                <option value="name"><?php esc_html_e('Full Name (name)', 'zappoppop'); ?></option>
                <option value="given-name"><?php esc_html_e('First Name (given-name)', 'zappoppop'); ?></option>
                <option value="family-name"><?php esc_html_e('Last Name (family-name)', 'zappoppop'); ?></option>
                <option value="organization"><?php esc_html_e('Company (organization)', 'zappoppop'); ?></option>
                <option value="honorific-prefix"><?php esc_html_e('Title / Prefix (honorific-prefix)', 'zappoppop'); ?></option>
                <option value="additional-name"><?php esc_html_e('Middle Name (additional-name)', 'zappoppop'); ?></option>
                <option value="nickname"><?php esc_html_e('Nickname (nickname)', 'zappoppop'); ?></option>
                <option value="username"><?php esc_html_e('Username (username)', 'zappoppop'); ?></option>
                <option value="organization-title"><?php esc_html_e('Job Title (organization-title)', 'zappoppop'); ?></option>
                <option value="email" selected><?php esc_html_e('Email Address (email)', 'zappoppop'); ?></option>
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
        <div class="zappoppop-prop-row zappoppop-gp-switch-row" id="insp-row-email-disposable">
            <label class="zappoppop-prop-label" for="prop-email-disposable"><?php esc_html_e('Block Disposable Emails', 'zappoppop'); ?></label>
            <label class="zappoppop-gp-switch">
                <input type="checkbox" id="prop-email-disposable" class="zappoppop-gp-bind" data-prop="blockDisposable" value="1">
                <span class="zappoppop-gp-slider"></span>
            </label>
        </div>
    </div>
</div>
