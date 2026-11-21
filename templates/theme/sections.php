<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- template variables are local to the method that includes this file
if (!defined('ABSPATH')) {
    exit;
}
?>
<?php $zappoppop_defs = ZapPopPop_Admin_Theme::defaults(); ?>

<nav class="zpp-theme-tabs" role="tablist" aria-label="<?php esc_attr_e('Theme sections', 'zappoppop'); ?>">
    <?php $first = true; foreach ($theme_schema as $gid => $group) : ?>
        <button type="button" role="tab" class="zpp-theme-tab<?php echo $first ? ' is-active' : ''; ?>" data-tab="<?php echo esc_attr($gid); ?>"><?php echo esc_html($group['label']); ?></button>
    <?php $first = false; endforeach; ?>
</nav>

<?php $first = true; foreach ($theme_schema as $gid => $group) : ?>
    <section class="zappoppop-box zpp-theme-panel<?php echo $first ? ' is-active' : ''; ?>" data-panel="<?php echo esc_attr($gid); ?>">
        <h2><?php echo esc_html($group['label']); ?></h2>
        <div class="zpp-theme-fields">
            <?php foreach ($group['fields'] as $key => $field) :
                $val   = $theme_values[$key];
                $name  = 'zappoppop_theme[' . $key . ']';
                $id    = 'zpp-f-' . $key;
                $var   = ZapPopPop_Admin_Theme::css_var($key);
                $is_color = ($field['type'] === 'color');
                $is_wide  = in_array($field['type'], ['font', 'textarea', 'shadow', 'media'], true);
                ?>
                <div class="zpp-theme-field<?php echo $is_wide ? ' is-wide' : ''; ?><?php echo $field['type'] === 'bool' ? ' is-bool' : ''; ?>" data-label="<?php echo esc_attr(strtolower($field['label'] . ' ' . $key)); ?>">
                    <label for="<?php echo esc_attr($id); ?>"><?php echo esc_html($field['label']); ?></label>
                    <?php if ($field['type'] === 'bool') : ?>
                        <input type="checkbox" id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($name); ?>" value="1" data-default="<?php echo (int) $zappoppop_defs[$key]; ?>" <?php checked(!empty($val)); ?>>
                    <?php elseif ($field['type'] === 'media') :
                        $zappoppop_logo_url = $val ? wp_get_attachment_image_url((int) $val, 'full') : '';
                        $zappoppop_def_logo = ZAPPOPPOP_URL . 'admin/img/logo-temp.svg';
                        ?>
                        <div class="zpp-theme-media" data-default="<?php echo esc_url($zappoppop_def_logo); ?>">
                            <input type="hidden" id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($name); ?>" value="<?php echo (int) $val; ?>" data-default="0">
                            <div class="zpp-theme-media-preview" style="display:inline-flex;align-items:center;justify-content:center;min-width:56px;min-height:44px;padding:6px 10px;margin:0 0 8px;border:1px solid #c3c4c7;border-radius:3px;background:#fff;">
                                <img src="<?php echo esc_url($zappoppop_logo_url ? $zappoppop_logo_url : $zappoppop_def_logo); ?>" alt="" style="display:block;max-width:140px;max-height:44px;object-fit:contain;">
                            </div>
                            <div>
                                <button type="button" class="button zpp-theme-media-pick"><?php echo $val ? esc_html__('Replace logo', 'zappoppop') : esc_html__('Upload logo', 'zappoppop'); ?></button>
                                <button type="button" class="button zpp-theme-media-remove"<?php echo $val ? '' : ' style="display:none;"'; ?>><?php esc_html_e('Remove', 'zappoppop'); ?></button>
                            </div>
                            <p class="description" style="margin:6px 0 0;"><?php esc_html_e('Shown in the top bar of every ZapPopPop page. Leave empty to use the default logo.', 'zappoppop'); ?></p>
                        </div>
                    <?php elseif ($field['type'] === 'enum') : ?>
                        <select id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($name); ?>" data-zpp-var="<?php echo esc_attr($var); ?>" data-default="<?php echo esc_attr($zappoppop_defs[$key]); ?>">
                            <?php foreach ($field['options'] as $ov => $ol) : ?>
                                <option value="<?php echo esc_attr($ov); ?>" <?php selected($val, (string) $ov); ?>><?php echo esc_html($ol); ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php elseif ($field['type'] === 'font') : ?>
                        <div class="zpp-theme-input-row">
                            <?php $zappoppop_font_groups = ZapPopPop_Admin_Theme::font_options(); $zappoppop_font_known = false; foreach ($zappoppop_font_groups as $zappoppop_fg_items) { if (isset($zappoppop_fg_items[$val])) { $zappoppop_font_known = true; break; } } ?>
                            <select id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($name); ?>" data-zpp-var="<?php echo esc_attr($var); ?>" data-default="<?php echo esc_attr($zappoppop_defs[$key]); ?>">
                                <?php if (!$zappoppop_font_known) : ?>
                                    <option value="<?php echo esc_attr($val); ?>" selected><?php echo esc_html($val); ?></option>
                                <?php endif; ?>
                                <?php foreach ($zappoppop_font_groups as $zappoppop_fg_label => $zappoppop_fg_items) : ?>
                                    <optgroup label="<?php echo esc_attr($zappoppop_fg_label); ?>">
                                        <?php foreach ($zappoppop_fg_items as $zappoppop_fv => $zappoppop_fl) : ?>
                                            <option value="<?php echo esc_attr($zappoppop_fv); ?>" <?php selected($val, (string) $zappoppop_fv); ?>><?php echo esc_html($zappoppop_fl); ?></option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endforeach; ?>
                            </select>
                            <button type="button" class="zpp-theme-field-reset" title="<?php esc_attr_e('Reset to default', 'zappoppop'); ?>">&#8634;</button>
                        </div>
                    <?php elseif ($field['type'] === 'textarea') : ?>
                        <textarea id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($name); ?>" rows="8" data-default=""><?php echo esc_textarea($val); ?></textarea>
                    <?php else : ?>
                        <div class="zpp-theme-input-row">
                            <?php if ($is_color) : ?>
                                <input type="color" class="zpp-theme-picker" data-zpp-alpha="compact" value="<?php echo esc_attr(preg_match('/^#(?:[0-9a-f]{6}|[0-9a-f]{8})$/i', $val) ? $val : '#000000'); ?>" aria-label="<?php echo esc_attr($field['label']); ?>">
                            <?php endif; ?>
                            <input type="text" id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($name); ?>"
                                   value="<?php echo esc_attr($val); ?>"
                                   data-zpp-var="<?php echo esc_attr($var); ?>"
                                   data-default="<?php echo esc_attr($zappoppop_defs[$key]); ?>"
                                   >
                            <button type="button" class="zpp-theme-field-reset" title="<?php esc_attr_e('Reset to default', 'zappoppop'); ?>">&#8634;</button>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
<?php $first = false; endforeach; ?>
