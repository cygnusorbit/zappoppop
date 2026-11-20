<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- template variables are local to the method that includes this file
if (!defined('ABSPATH')) {
    exit;
}
$__bld = array_merge(ZapPopPop_Admin_Settings::get_builder_defaults(), (array) $settings);
$__align_on = !empty($__bld['builder_alignment_tool']);
$__vp_on = !empty($__bld['builder_viewport_toggle']);
$__zm_on = !empty($__bld['builder_zoom_select']);
$__st_on = !empty($__bld['builder_save_template']);
$__cc_on = !empty($__bld['builder_click_canvas_settings']);
$__ce_on = !empty($__bld['builder_click_element_settings']);
$__hl_on = !empty($settings['builder_hide_field_labels']); /* default OFF: new form elements show their label */
$__bg_on = !array_key_exists('builder_guide', (array) $settings) || !empty($settings['builder_guide']);
?>
<?php ZapPopPop_Inline_Assets::style('settings-tab-builder'); ?><nav class="zappoppop-bld-subtabs zpp-theme-tabs" aria-label="<?php esc_attr_e('Builder sections', 'zappoppop'); ?>">
    <button type="button" class="zpp-theme-tab zappoppop-bld-subtab is-active" data-bld-pane="tools" aria-selected="true"><?php esc_html_e('Builder Tools', 'zappoppop'); ?></button>
    <button type="button" class="zpp-theme-tab zappoppop-bld-subtab" data-bld-pane="layers" aria-selected="false"><?php esc_html_e('Element / Layer Settings', 'zappoppop'); ?></button>
    <button type="button" class="zpp-theme-tab zappoppop-bld-subtab" data-bld-pane="guide" aria-selected="false"><?php esc_html_e('Guide Steps', 'zappoppop'); ?></button>
</nav>
<div class="zappoppop-bld-pane" data-bld-pane="tools">
<div class="zappoppop-settings-section">
    <p class="description"><?php esc_html_e('Turn visual builder tools on or off. Save settings, then reload the builder.', 'zappoppop'); ?></p>
    <ul class="zappoppop-bld-list">
        <li>
            <span class="bld-name"><?php esc_html_e('Alignment Tool', 'zappoppop'); ?>
                <span class="bld-desc"><?php esc_html_e('Floating toolbar on the stage: align left / center / right, top / middle / bottom, and distribute.', 'zappoppop'); ?></span>
            </span>
            <label class="zappoppop-switch-bld" title="<?php esc_attr_e('Alignment Tool', 'zappoppop'); ?>">
                <input type="hidden" name="settings[builder_alignment_tool]" value="0">
                <input type="checkbox" name="settings[builder_alignment_tool]" value="1" <?php checked($__align_on); ?>>
                <span class="trk"></span>
            </label>
        </li>
        <li>
            <span class="bld-name"><?php esc_html_e('Viewport', 'zappoppop'); ?>
                <span class="bld-desc"><?php esc_html_e('Show the Desktop / Mobile viewport switch in the builder header.', 'zappoppop'); ?></span>
            </span>
            <label class="zappoppop-switch-bld" title="<?php esc_attr_e('Viewport', 'zappoppop'); ?>">
                <input type="hidden" name="settings[builder_viewport_toggle]" value="0">
                <input type="checkbox" name="settings[builder_viewport_toggle]" value="1" <?php checked($__vp_on); ?>>
                <span class="trk"></span>
            </label>
        </li>
        <li>
            <span class="bld-name"><?php esc_html_e('Zoom Select', 'zappoppop'); ?>
                <span class="bld-desc"><?php esc_html_e('Show the stage zoom dropdown (50% - Fit) in the builder header.', 'zappoppop'); ?></span>
            </span>
            <label class="zappoppop-switch-bld" title="<?php esc_attr_e('Zoom Select', 'zappoppop'); ?>">
                <input type="hidden" name="settings[builder_zoom_select]" value="0">
                <input type="checkbox" name="settings[builder_zoom_select]" value="1" <?php checked($__zm_on); ?>>
                <span class="trk"></span>
            </label>
        </li>
        <li>
            <span class="bld-name"><?php esc_html_e('Save As Template', 'zappoppop'); ?>
                <span class="bld-desc"><?php esc_html_e('Show the green Save As Template button in the builder header so popups can be saved to the Popups Library.', 'zappoppop'); ?></span>
            </span>
            <label class="zappoppop-switch-bld" title="<?php esc_attr_e('Save As Template', 'zappoppop'); ?>">
                <input type="hidden" name="settings[builder_save_template]" value="0">
                <input type="checkbox" name="settings[builder_save_template]" value="1" <?php checked($__st_on); ?>>
                <span class="trk"></span>
            </label>
        </li>
        <li>
            <span class="bld-name"><?php esc_html_e('Click Canvas - Open Popup Settings', 'zappoppop'); ?>
                <span class="bld-desc"><?php esc_html_e('Clicking an empty area of the canvas slides in Popup Settings. Off by default.', 'zappoppop'); ?></span>
            </span>
            <label class="zappoppop-switch-bld" title="<?php esc_attr_e('Click Canvas - Open Popup Settings', 'zappoppop'); ?>">
                <input type="hidden" name="settings[builder_click_canvas_settings]" value="0">
                <input type="checkbox" name="settings[builder_click_canvas_settings]" value="1" <?php checked($__cc_on); ?>>
                <span class="trk"></span>
            </label>
        </li>
        <li>
            <span class="bld-name"><?php esc_html_e('Click Element - Open Layer Settings', 'zappoppop'); ?>
                <span class="bld-desc"><?php esc_html_e('Clicking an element on the canvas slides in Layer Settings. Off by default.', 'zappoppop'); ?></span>
            </span>
            <label class="zappoppop-switch-bld" title="<?php esc_attr_e('Click Element - Open Layer Settings', 'zappoppop'); ?>">
                <input type="hidden" name="settings[builder_click_element_settings]" value="0">
                <input type="checkbox" name="settings[builder_click_element_settings]" value="1" <?php checked($__ce_on); ?>>
                <span class="trk"></span>
            </label>
        </li>
        <li>
            <span class="bld-name"><?php esc_html_e('Show guide', 'zappoppop'); ?>
                <span class="bld-desc"><?php esc_html_e('Show the guided tour the first time each admin opens the builder. A Help (?) button in the builder toolbar can always relaunch it.', 'zappoppop'); ?></span>
            </span>
            <label class="zappoppop-switch-bld" title="<?php esc_attr_e('Show guide', 'zappoppop'); ?>">
                <input type="hidden" name="settings[builder_guide]" value="0">
                <input type="checkbox" name="settings[builder_guide]" value="1" <?php checked($__bg_on); ?>>
                <span class="trk"></span>
            </label>
        </li>
        <li>
            <span class="bld-name"><?php esc_html_e('Restart tour', 'zappoppop'); ?>
                <span class="bld-desc"><?php esc_html_e('Reset your own tour progress so it starts again the next time you open the builder.', 'zappoppop'); ?></span>
                <span class="bld-desc" id="zappoppop-guide-restart-status" role="status" aria-live="polite"></span>
            </span>
            <button type="button" class="button" id="zappoppop-guide-restart" data-uid="<?php echo (int) get_current_user_id(); ?>" data-nonce="<?php echo esc_attr(ZapPopPop_Admin_Guide::nonce()); ?>"><?php esc_html_e('Restart tour', 'zappoppop'); ?></button>
        </li>
            <li>
            <span class="bld-name"><?php esc_html_e('Hide Field Labels by Default', 'zappoppop'); ?>
                <span class="bld-desc"><?php esc_html_e('New form elements (Text, Email, Date, Radio, etc.) start with their field label visible. Turn this on to start them hidden. Existing elements are not changed; each element can still show its label with the Hide Label switch. Saving a layer with Save Layer Settings as Default overrides this.', 'zappoppop'); ?></span>
            </span>
            <label class="zappoppop-switch-bld" title="<?php esc_attr_e('Hide Field Labels by Default', 'zappoppop'); ?>">
                <input type="hidden" name="settings[builder_hide_field_labels]" value="0">
                <input type="checkbox" name="settings[builder_hide_field_labels]" value="1" <?php checked($__hl_on); ?>>
                <span class="trk"></span>
            </label>
        </li>
    </ul>
</div>
</div>
<div class="zappoppop-bld-pane" data-bld-pane="layers" hidden>
<div class="zappoppop-settings-section">
    <?php include ZAPPOPPOP_PATH . 'templates/settings/builder-layer-settings.php'; ?>
</div>
</div>
<div class="zappoppop-bld-pane" data-bld-pane="guide" hidden>
<div class="zappoppop-settings-section">
    <?php include ZAPPOPPOP_PATH . 'templates/settings/guide-editor.php'; ?>
</div>
</div>
