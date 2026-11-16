<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- template variables are local to the method that includes this file
if (!defined('ABSPATH')) {
    exit;
}

$__gs_rows    = ZapPopPop_Admin_Guide::resolved_rows();
$__gs_targets = ZapPopPop_Admin_Guide::targets();
$__gs_before  = [
    ''                    => __('No extra action', 'zappoppop'),
    'open-inspector'      => __('Open the Inspector', 'zappoppop'),
    'inspector-tab:basic' => __('Inspector: Content tab', 'zappoppop'),
    'inspector-tab:style' => __('Inspector: Style tab', 'zappoppop'),
    'inspector-tab:logic' => __('Inspector: Logic tab', 'zappoppop'),
    'inspector-tab:data'  => __('Inspector: Data tab', 'zappoppop'),
    'inspector-tab:advanced' => __('Inspector: Advanced tab', 'zappoppop'),
    'open-settings'       => __('Open Popup Settings', 'zappoppop'),
];
$__gs_places  = [
    'auto'   => __('Automatic', 'zappoppop'),
    'bottom' => __('Below the target', 'zappoppop'),
    'top'    => __('Above the target', 'zappoppop'),
    'left'   => __('Left of the target', 'zappoppop'),
    'right'  => __('Right of the target', 'zappoppop'),
];

$__gs_row = function ($r, $i) use ($__gs_targets, $__gs_before, $__gs_places) {
    $name   = 'settings[builder_guide_steps][' . $i . ']';
    $custom = !empty($r['custom']);
    $is_cs  = ('custom' === $r['target']);
    $num    = is_int($i) ? $i + 1 : '';
    ?>
    <div class="zappoppop-gs-row<?php echo empty($r['enabled']) ? ' is-off' : ''; ?>" data-custom="<?php echo $custom ? 1 : 0; ?>">
        <input type="hidden" name="<?php echo esc_attr($name); ?>[id]" value="<?php echo esc_attr($r['id']); ?>">
        <div class="zappoppop-gs-head">
            <span class="zappoppop-gs-num" aria-hidden="true"><?php echo esc_html($num); ?></span>
            <label class="zappoppop-switch-bld" title="<?php esc_attr_e('Show this step', 'zappoppop'); ?>">
                <input type="hidden" name="<?php echo esc_attr($name); ?>[enabled]" value="0">
                <input type="checkbox" class="zappoppop-gs-enabled" name="<?php echo esc_attr($name); ?>[enabled]" value="1" <?php checked(!empty($r['enabled'])); ?> aria-label="<?php esc_attr_e('Show this step', 'zappoppop'); ?>">
                <span class="trk"></span>
            </label>
            <input type="text" class="zappoppop-gs-title" name="<?php echo esc_attr($name); ?>[title]" value="<?php echo esc_attr($r['title']); ?>" maxlength="120" placeholder="<?php esc_attr_e('Step title', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Step title', 'zappoppop'); ?>">
            <span class="zappoppop-gs-tag"><?php echo $custom ? esc_html__('Custom', 'zappoppop') : esc_html__('Built-in', 'zappoppop'); ?></span>
            <span class="zappoppop-gs-actions">
                <button type="button" class="button" data-gs-act="up" title="<?php esc_attr_e('Move up', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Move up', 'zappoppop'); ?>"><span class="dashicons dashicons-arrow-up-alt2" aria-hidden="true"></span></button>
                <button type="button" class="button" data-gs-act="down" title="<?php esc_attr_e('Move down', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Move down', 'zappoppop'); ?>"><span class="dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span></button>
                <button type="button" class="button" data-gs-act="edit" aria-expanded="false" title="<?php esc_attr_e('Edit step', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Edit step', 'zappoppop'); ?>"><span class="dashicons dashicons-edit" aria-hidden="true"></span></button>
                <?php if ($custom) : ?>
                <button type="button" class="button zappoppop-gs-del" data-gs-act="del" title="<?php esc_attr_e('Delete step', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Delete step', 'zappoppop'); ?>"><span class="dashicons dashicons-trash" aria-hidden="true"></span></button>
                <?php endif; ?>
            </span>
        </div>
        <div class="zappoppop-gs-body" hidden>
            <label class="zappoppop-gs-field zappoppop-gs-wide">
                <span><?php esc_html_e('Text (use **bold** for emphasis, 400 characters max)', 'zappoppop'); ?></span>
                <textarea name="<?php echo esc_attr($name); ?>[text]" rows="3" maxlength="400"><?php echo esc_textarea($r['text']); ?></textarea>
            </label>
            <label class="zappoppop-gs-field">
                <span><?php esc_html_e('Highlight', 'zappoppop'); ?></span>
                <select class="zappoppop-gs-target" name="<?php echo esc_attr($name); ?>[target]">
                    <?php foreach ($__gs_targets as $k => $t) : ?>
                        <option value="<?php echo esc_attr($k); ?>" <?php selected($r['target'], $k); ?>><?php echo esc_html($t['label']); ?></option>
                    <?php endforeach; ?>
                    <option value="custom" <?php selected($r['target'], 'custom'); ?>><?php esc_html_e('Custom CSS selector…', 'zappoppop'); ?></option>
                </select>
            </label>
            <label class="zappoppop-gs-field">
                <span><?php esc_html_e('Card position', 'zappoppop'); ?></span>
                <select name="<?php echo esc_attr($name); ?>[placement]">
                    <?php foreach ($__gs_places as $k => $label) : ?>
                        <option value="<?php echo esc_attr($k); ?>" <?php selected($r['placement'], $k); ?>><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="zappoppop-gs-field zappoppop-gs-if-custom"<?php echo $is_cs ? '' : ' hidden'; ?>>
                <span><?php esc_html_e('CSS selector', 'zappoppop'); ?></span>
                <input type="text" name="<?php echo esc_attr($name); ?>[selector]" value="<?php echo esc_attr($r['selector']); ?>" maxlength="200" placeholder="#zappoppop-btn-save" spellcheck="false" autocomplete="off">
            </label>
            <label class="zappoppop-gs-field zappoppop-gs-if-custom"<?php echo $is_cs ? '' : ' hidden'; ?>>
                <span><?php esc_html_e('Before showing the step', 'zappoppop'); ?></span>
                <select name="<?php echo esc_attr($name); ?>[before]">
                    <?php foreach ($__gs_before as $k => $label) : ?>
                        <option value="<?php echo esc_attr($k); ?>" <?php selected($r['before'], $k); ?>><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>
    </div>
    <?php
};

$__gs_on = ZapPopPop_Admin_Guide::option_on('builder_guide_steps_on');
$__gs_blank = ['id' => '', 'enabled' => 1, 'title' => '', 'text' => '', 'target' => 'center', 'selector' => '', 'before' => '', 'placement' => 'auto', 'custom' => 1];
?>
<div class="zappoppop-gs-master">
    <span class="zappoppop-gs-master-name"><?php esc_html_e('Enable Guide Steps', 'zappoppop'); ?>
        <span class="zappoppop-gs-master-desc"><?php esc_html_e('Master switch. When off, the builder tour is disabled for every admin: it does not start and the Help (?) button is hidden. Your steps below are kept.', 'zappoppop'); ?></span>
    </span>
    <label class="zappoppop-switch-bld" title="<?php esc_attr_e('Enable Guide Steps', 'zappoppop'); ?>">
        <input type="hidden" name="settings[builder_guide_steps_on]" value="0">
        <input type="checkbox" id="zappoppop-gs-master" name="settings[builder_guide_steps_on]" value="1" <?php checked($__gs_on); ?> aria-label="<?php esc_attr_e('Enable Guide Steps', 'zappoppop'); ?>">
        <span class="trk"></span>
    </label>
</div>
<p class="description"><?php esc_html_e('Edit the builder tour shown to every admin. Turn steps on or off, reorder them, rewrite their text, point them at another part of the builder, or add your own. Save settings, then reload the builder. Pro and extensions can still add steps on top of these.', 'zappoppop'); ?></p>
<div id="zappoppop-gs-root" class="zappoppop-gs<?php echo $__gs_on ? '' : ' is-master-off'; ?>" data-max="<?php echo (int) ZapPopPop_Admin_Guide::MAX_STEPS; ?>" data-defaults="<?php echo esc_attr(wp_json_encode(ZapPopPop_Admin_Guide::editor_defaults())); ?>">
    <div class="zappoppop-gs-toolbar">
        <span class="zappoppop-gs-count" id="zappoppop-gs-count" aria-live="polite"></span>
        <span class="zappoppop-gs-toolbar-btns">
            <button type="button" class="button" id="zappoppop-gs-reset"><?php esc_html_e('Reset to defaults', 'zappoppop'); ?></button>
            <button type="button" class="button button-primary" id="zappoppop-gs-add"><?php esc_html_e('Add step', 'zappoppop'); ?></button>
        </span>
    </div>
    <div class="zappoppop-gs-list">
        <?php foreach ($__gs_rows as $__gs_i => $__gs_r) { $__gs_row($__gs_r, $__gs_i); } ?>
    </div>
    <template id="zappoppop-gs-template"><?php $__gs_row($__gs_blank, '__i__'); ?></template>
</div>
