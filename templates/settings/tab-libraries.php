<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- template variables are local to the method that includes this file
if (!defined('ABSPATH')) {
    exit;
}

$default_font          = isset($settings['default_font']) ? $settings['default_font'] : 'inherit';
$df_weight             = isset($settings['default_font_weight']) ? (string) $settings['default_font_weight'] : 'inherit'; /* ZAPPOPPOP_DEFAULT_TYPO */
$df_size               = isset($settings['default_font_size']) ? (int) $settings['default_font_size'] : 0;
$df_align              = isset($settings['default_text_align']) ? (string) $settings['default_text_align'] : 'inherit';
$df_italic             = !empty($settings['default_font_italic']);
$df_underline          = !empty($settings['default_font_underline']);
$df_color              = isset($settings['default_font_color']) ? (string) $settings['default_font_color'] : '';
$df_weights            = ['inherit' => 'inherit - Inherit', '300' => '300 - Light', '400' => '400 - Regular', '500' => '500 - Medium', '600' => '600 - Semi Bold', '700' => '700 - Bold'];
$google_fonts          = !empty($settings['google_fonts']);
$font_awesome          = !empty($settings['font_awesome']);
$custom_fonts          = isset($settings['custom_fonts']) ? $settings['custom_fonts'] : '';
$air_datepicker        = (!empty($settings['air_datepicker']) || !empty($settings['load_air_datepicker']));
$jquery_mask           = (!empty($settings['jquery_mask']) || !empty($settings['load_jquery_mask']));
$range_slider          = (!empty($settings['range_slider']) || !empty($settings['load_range_slider']));
?>
<div class="zappoppop-settings-section">
    <h3><?php esc_html_e('Typography & Component Extensions', 'zappoppop'); ?></h3>
    <table class="form-table">
        <tr>
            <th scope="row"><label for="setting-default-font"><?php esc_html_e('Default Font Family', 'zappoppop'); ?></label></th>
            <td>
                <select id="setting-default-font" name="settings[default_font]">
                    <option value="inherit" <?php selected($default_font, 'inherit'); ?>><?php esc_html_e('Theme Inherited (Default)', 'zappoppop'); ?></option>
                    <option value="Inter, sans-serif" <?php selected($default_font, 'Inter, sans-serif'); ?>><?php esc_html_e('Inter', 'zappoppop'); ?></option>
                    <option value="Roboto, sans-serif" <?php selected($default_font, 'Roboto, sans-serif'); ?>><?php esc_html_e('Roboto', 'zappoppop'); ?></option>
                    <option value="Montserrat, sans-serif" <?php selected($default_font, 'Montserrat, sans-serif'); ?>><?php esc_html_e('Montserrat', 'zappoppop'); ?></option>
                    <option value="Poppins, sans-serif" <?php selected($default_font, 'Poppins, sans-serif'); ?>><?php esc_html_e('Poppins', 'zappoppop'); ?></option>
                    <option value="Open Sans, sans-serif" <?php selected($default_font, 'Open Sans, sans-serif'); ?>><?php esc_html_e('Open Sans', 'zappoppop'); ?></option>
                    <option value="Lato, sans-serif" <?php selected($default_font, 'Lato, sans-serif'); ?>><?php esc_html_e('Lato', 'zappoppop'); ?></option>
                </select>
                <p class="description"><?php esc_html_e('Fallback typography applied to campaign elements when no specific custom font is defined.', 'zappoppop'); ?></p>
            </td>
        </tr>
        <tr class="zappoppop-typo-row">
            <th scope="row"><?php esc_html_e('Default Font Style', 'zappoppop'); ?></th>
            <td>
                <div class="zappoppop-typo-grid">
                    <div class="zappoppop-typo-cell">
                        <select id="setting-default-font-weight" name="settings[default_font_weight]">
                            <?php foreach ($df_weights as $wv => $wl) : ?>
                                <option value="<?php echo esc_attr($wv); ?>" <?php selected($df_weight, (string) $wv); ?>><?php echo esc_html($wl); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span class="zappoppop-typo-cap"><?php esc_html_e('Font weight', 'zappoppop'); ?></span>
                    </div>
                    <div class="zappoppop-typo-cell">
                        <div class="zappoppop-typo-color">
                            <input type="color" id="setting-default-font-color-picker" value="<?php echo esc_attr($df_color !== '' ? $df_color : '#444444'); ?>" aria-label="<?php esc_attr_e('Pick color', 'zappoppop'); ?>">
                            <input type="text" id="setting-default-font-color" name="settings[default_font_color]" value="<?php echo esc_attr($df_color); ?>" placeholder="<?php esc_attr_e('inherit', 'zappoppop'); ?>" maxlength="7" autocomplete="off" spellcheck="false">
                        </div>
                        <span class="zappoppop-typo-cap"><?php esc_html_e('Color', 'zappoppop'); ?></span>
                    </div>
                    <div class="zappoppop-typo-cell">
                        <div class="zappoppop-typo-size">
                            <input type="number" id="setting-default-font-size" name="settings[default_font_size]" value="<?php echo $df_size > 0 ? esc_attr($df_size) : ''; ?>" min="0" max="200" step="1" placeholder="<?php esc_attr_e('auto', 'zappoppop'); ?>">
                            <span class="zappoppop-typo-unit">px</span>
                        </div>
                        <span class="zappoppop-typo-cap"><?php esc_html_e('Size', 'zappoppop'); ?></span>
                    </div>
                    <div class="zappoppop-typo-cell">
                        <div class="zappoppop-typo-seg" role="radiogroup" aria-label="<?php esc_attr_e('Alignment', 'zappoppop'); ?>">
                            <label title="<?php esc_attr_e('Inherit', 'zappoppop'); ?>"><input type="radio" name="settings[default_text_align]" value="inherit" <?php checked($df_align === 'left' || $df_align === 'center' || $df_align === 'right', false); ?>><span><?php esc_html_e('Auto', 'zappoppop'); ?></span></label>
                            <label title="<?php esc_attr_e('Left', 'zappoppop'); ?>"><input type="radio" name="settings[default_text_align]" value="left" <?php checked($df_align, 'left'); ?>><span class="dashicons dashicons-editor-alignleft"></span></label>
                            <label title="<?php esc_attr_e('Center', 'zappoppop'); ?>"><input type="radio" name="settings[default_text_align]" value="center" <?php checked($df_align, 'center'); ?>><span class="dashicons dashicons-editor-aligncenter"></span></label>
                            <label title="<?php esc_attr_e('Right', 'zappoppop'); ?>"><input type="radio" name="settings[default_text_align]" value="right" <?php checked($df_align, 'right'); ?>><span class="dashicons dashicons-editor-alignright"></span></label>
                        </div>
                        <span class="zappoppop-typo-cap"><?php esc_html_e('Alignment', 'zappoppop'); ?></span>
                    </div>
                    <div class="zappoppop-typo-cell">
                        <div class="zappoppop-typo-seg">
                            <label title="<?php esc_attr_e('Italic', 'zappoppop'); ?>"><input type="checkbox" name="settings[default_font_italic]" value="1" <?php checked($df_italic); ?>><span class="dashicons dashicons-editor-italic"></span></label>
                            <label title="<?php esc_attr_e('Underline', 'zappoppop'); ?>"><input type="checkbox" name="settings[default_font_underline]" value="1" <?php checked($df_underline); ?>><span class="dashicons dashicons-editor-underline"></span></label>
                        </div>
                        <span class="zappoppop-typo-cap"><?php esc_html_e('Style', 'zappoppop'); ?></span>
                    </div>
                </div>
                <p class="description"><?php esc_html_e('Default weight, size, color, alignment and style for popup text. Layers with their own typography override these. Leave on inherit / auto / blank to keep theme behavior.', 'zappoppop'); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php esc_html_e('Font Asset Delivery', 'zappoppop'); ?></th>
            <td>
                <label style="display:block;margin-bottom:6px;">
                    <input type="checkbox" name="settings[google_fonts]" value="1" <?php checked($google_fonts); ?>>
                    <?php esc_html_e('Enqueue Google Fonts stylesheets on public frontend pages', 'zappoppop'); ?>
                </label>
                <label style="display:block;">
                    <input type="checkbox" name="settings[font_awesome]" value="1" <?php checked($font_awesome); ?>>
                    <?php esc_html_e('Enqueue Font Awesome icon font library for popup icon layers', 'zappoppop'); ?>
                </label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="setting-custom-fonts"><?php esc_html_e('Custom Fonts Definitions', 'zappoppop'); ?></label></th>
            <td>
                <textarea id="setting-custom-fonts" name="settings[custom_fonts]" rows="3" class="large-text" placeholder="<?php esc_attr_e('FontName: url(https://.../font.woff2)', 'zappoppop'); ?>"><?php echo esc_textarea($custom_fonts); ?></textarea>
                <p class="description"><?php esc_html_e('Custom web font declarations (one declaration per line) for the visual builder dropdown.', 'zappoppop'); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php esc_html_e('Library Extensions', 'zappoppop'); ?></th>
            <td>
                <fieldset>
                    <legend class="screen-reader-text"><?php esc_html_e('Library Extensions', 'zappoppop'); ?></legend>
                    <label style="display:block;margin-bottom:8px;">
                        <input type="checkbox" name="settings[air_datepicker]" value="1" <?php checked($air_datepicker); ?>>
                        <strong><?php esc_html_e('Air Datepicker', 'zappoppop'); ?></strong> — <?php esc_html_e('Interactive calendar date and appointment picking controls.', 'zappoppop'); ?>
                    </label>
                    <label style="display:block;margin-bottom:8px;">
                        <input type="checkbox" name="settings[jquery_mask]" value="1" <?php checked($jquery_mask); ?>>
                        <strong><?php esc_html_e('jQuery Mask', 'zappoppop'); ?></strong> — <?php esc_html_e('Input format masks for phone numbers, postal codes, and currency.', 'zappoppop'); ?>
                    </label>
                    <label style="display:block;margin-bottom:8px;">
                        <input type="checkbox" name="settings[range_slider]" value="1" <?php checked($range_slider); ?>>
                        <strong><?php esc_html_e('Range Slider UI', 'zappoppop'); ?></strong> — <?php esc_html_e('Visual range value sliders for interactive surveys and cost estimators.', 'zappoppop'); ?>
                    </label>
                    <?php do_action('zappoppop_settings_library_extensions', $settings); ?>
                </fieldset>
            </td>
        </tr>
    </table>
</div>
<?php ZapPopPop_Inline_Assets::script('settings-tab-libraries'); ?>