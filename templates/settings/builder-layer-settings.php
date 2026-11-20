<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- template variables are local to the method that includes this file
if (!defined('ABSPATH')) {
    exit;
}
$__ls_overrides = ZapPopPop_Layer_Settings::get_overrides();
$__ls_elements  = ZapPopPop_Layer_Settings::ordered_elements(); /* ZPP_RIBBON_SYNC */
$__ls_tabs      = ZapPopPop_Layer_Settings::tabs();
$__ls_cat       = ZapPopPop_Layer_Settings::catalogue();
$__ls_form      = ZapPopPop_Layer_Settings::form_types();
$__ls_first     = key($__ls_elements);
$__ls_json      = empty($__ls_overrides) ? '{}' : wp_json_encode($__ls_overrides);
$__ls_order     = ZapPopPop_Layer_Settings::get_order();
$__lo_json      = empty($__ls_order) ? '{}' : wp_json_encode($__ls_order);
?>
<div class="zappoppop-ls" id="zappoppop-ls-root">
    <p class="description"><?php esc_html_e('Choose which Layer Settings each element shows in the builder. Use the slide button to enable or disable a setting. Save settings, then reload the builder.', 'zappoppop'); ?></p>
    <input type="hidden" id="zappoppop-ls-json" name="settings[builder_layer_settings]" value="<?php echo esc_attr($__ls_json); ?>">
    <input type="hidden" id="zappoppop-lo-json" name="settings[builder_layer_order]" value="<?php echo esc_attr($__lo_json); ?>">

    <div class="zappoppop-ls-layout">
        <ul class="zappoppop-ls-elements" role="tablist" aria-label="<?php esc_attr_e('Elements', 'zappoppop'); ?>">
            <?php $__ls_n = 0; foreach ($__ls_elements as $__t => $__tl) : $__ls_n++; ?>
                <li>
                    <button type="button" class="zappoppop-ls-el-btn<?php echo $__t === $__ls_first ? ' is-active' : ''; ?><?php echo !empty($__ls_overrides[$__t]) ? ' has-override' : ''; ?>" data-ls-el="<?php echo esc_attr($__t); ?>" role="tab">
                        <span class="zappoppop-ls-num"><?php echo (int) $__ls_n; ?></span>
                        <span class="zappoppop-ls-name"><?php echo esc_html($__tl); ?></span>
                        <span class="zappoppop-ls-dot" title="<?php esc_attr_e('Customised', 'zappoppop'); ?>"></span>
                    </button>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="zappoppop-ls-main">
            <?php foreach ($__ls_elements as $__t => $__tl) : $__is_form = in_array($__t, $__ls_form, true); ?>
                <section class="zappoppop-ls-el" data-ls-el="<?php echo esc_attr($__t); ?>"<?php echo $__t === $__ls_first ? '' : ' hidden'; ?>>
                    <header class="zappoppop-ls-head">
                        <h4><?php echo esc_html($__tl); ?></h4>
                        <button type="button" class="button zappoppop-ls-reset" data-ls-el="<?php echo esc_attr($__t); ?>"><?php esc_html_e('Reset to default', 'zappoppop'); ?></button>
                    </header>

                    <nav class="zappoppop-ls-tabs zpp-theme-tabs" aria-label="<?php esc_attr_e('Settings groups', 'zappoppop'); ?>">
                        <?php $__ls_t1 = true; foreach ($__ls_tabs as $__tab => $__tablabel) : ?>
                            <button type="button" class="zpp-theme-tab zappoppop-ls-tab-btn<?php echo $__ls_t1 ? ' is-active' : ''; ?>" data-ls-tab="<?php echo esc_attr($__tab); ?>"><?php echo esc_html($__tablabel); ?></button>
                        <?php $__ls_t1 = false; endforeach; ?>
                    </nav>

                    <?php $__ls_p1 = true; foreach ($__ls_tabs as $__tab => $__tablabel) : ?>
                        <div class="zappoppop-ls-pane" data-ls-tab="<?php echo esc_attr($__tab); ?>"<?php echo $__ls_p1 ? '' : ' hidden'; ?>>
                            <?php if (($__tab === 'data' || $__tab === 'advanced') && !$__is_form) : ?>
                                <p class="zappoppop-ls-note"><?php esc_html_e('This tab is built for form elements. Every setting below is off by default for this element; enable one to add the tab to it.', 'zappoppop'); ?></p>
                            <?php endif; ?>
                            <ul class="zappoppop-bld-list zappoppop-ls-sortable" data-type="<?php echo esc_attr($__t); ?>" data-tab="<?php echo esc_attr($__tab); ?>">
                                <?php
                                $__pos_map = array();
                                $__pn      = 0;
                                foreach (ZapPopPop_Layer_Settings::flat($__ls_cat[$__tab]) as $__f) {
                                    $__pos_map[$__f['key']] = $__pn++;
                                }
                                foreach (ZapPopPop_Layer_Settings::ordered_entries($__ls_cat[$__tab], $__t, $__tab, $__ls_order) as $__e) {
                                    $__render_row = function ($e, $is_child, $parent = '') use ($__t, $__tab, $__ls_overrides, $__pos_map) {
                                        $on  = ZapPopPop_Layer_Settings::is_on($__t, $__tab, $e, $__ls_overrides);
                                        $def = ZapPopPop_Layer_Settings::default_on($__t, $e);
                                        $lbl = ZapPopPop_Layer_Settings::entry_label($e, $__t);
                                        ?>
                                        <li class="zappoppop-ls-row<?php echo $is_child ? ' is-child' : ''; ?><?php echo $on ? '' : ' is-off'; ?>" data-ls-key="<?php echo esc_attr($e['key']); ?>" data-ls-parent="<?php echo esc_attr($parent); ?>" data-ls-pos="<?php echo (int) (isset($__pos_map[$e['key']]) ? $__pos_map[$e['key']] : 0); ?>">
                                            <button type="button" class="zappoppop-ls-grip" aria-label="<?php esc_attr_e('Drag to reorder (or use the Up / Down arrow keys)', 'zappoppop'); ?>" title="<?php esc_attr_e('Drag to reorder', 'zappoppop'); ?>"><span class="dashicons dashicons-menu" aria-hidden="true"></span></button>
                                            <?php if (!$is_child && !empty($e['rows'])) : ?>
                                                <button type="button" class="zappoppop-ls-chev" aria-expanded="true" aria-label="<?php esc_attr_e('Show or hide the sub-settings', 'zappoppop'); ?>"><span class="dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span></button>
                                            <?php else : ?>
                                                <span class="zappoppop-ls-chev-sp" aria-hidden="true"></span>
                                            <?php endif; ?>
                                            
                                            <span class="bld-name"><?php echo esc_html($lbl); ?>
                                                <span class="bld-desc"><?php echo $def ? esc_html__('Default: On', 'zappoppop') : esc_html__('Default: Off (hidden)', 'zappoppop'); ?></span>
                                            </span>
                                            <label class="zappoppop-switch-bld" title="<?php echo esc_attr($lbl); ?>">
                                                <input type="checkbox" class="zappoppop-ls-chk" data-type="<?php echo esc_attr($__t); ?>" data-tab="<?php echo esc_attr($__tab); ?>" data-key="<?php echo esc_attr($e['key']); ?>" data-default="<?php echo $def ? '1' : '0'; ?>" <?php checked($on); ?>>
                                                <span class="trk"></span>
                                            </label>
                                        </li>
                                        <?php
                                    };
                                    $__render_row($__e, false);
                                    foreach ($__e['rows'] as $__r) {
                                        $__render_row($__r, true, $__e['key']);
                                    }
                                }
                                ?>
                            </ul>
                        </div>
                    <?php $__ls_p1 = false; endforeach; ?>
                </section>
            <?php endforeach; ?>
        </div>
    </div>
</div>
