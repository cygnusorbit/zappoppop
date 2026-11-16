<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- template variables are local to the method that includes this file
if (!defined('ABSPATH')) {
    exit;
}
?>
<?php ZapPopPop_Libraries::print_style('zappoppop-font-awesome'); ?>
<?php
$__zpp_set   = get_option('zappoppop_settings', []);
$__zpp_set   = is_array($__zpp_set) ? $__zpp_set : [];
$__zpp_vp_on = !empty($__zpp_set['builder_viewport_toggle']);
$__zpp_zm_on = !empty($__zpp_set['builder_zoom_select']);
$__zpp_tpl_on = !empty($__zpp_set['builder_save_template']);
?>
<div class="zappoppop-builder-header">
    <!-- Left Section: Navigation, Settings, Title -->
    <div class="zappoppop-hdr-group zappoppop-hdr-left">
                        <!-- Day / Night Mode Toggle -->
        <button type="button" class="zappoppop-btn-icon zappoppop-btn-theme" id="zappoppop-btn-theme-toggle" title="<?php esc_attr_e('Toggle Day / Night Mode', 'zappoppop'); ?><?php esc_html_e('" aria-label="', 'zappoppop'); ?><?php esc_attr_e('Toggle Day / Night Mode', 'zappoppop'); ?>" style="cursor:pointer;pointer-events:auto;">
            <svg class="zappoppop-icon-sun" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;pointer-events:none;"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
            <svg class="zappoppop-icon-moon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;pointer-events:none;"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
        </button>
        <button type="button" class="zappoppop-btn-icon zappoppop-btn-settings" id="zappoppop-btn-settings" data-action="settings" title="<?php esc_attr_e('Popup Settings', 'zappoppop'); ?><?php esc_html_e('" aria-label="', 'zappoppop'); ?><?php esc_attr_e('Popup Settings', 'zappoppop'); ?>" style="cursor:pointer;pointer-events:auto;"><span class="dashicons dashicons-admin-generic" style="pointer-events:none;"></span></button>
        <input type="text" id="zappoppop-builder-title" value="<?php echo esc_attr($popup ? $popup->title : __('Untitled Popup', 'zappoppop')); ?>" placeholder="<?php esc_attr_e('Popup Title...', 'zappoppop'); ?><?php esc_html_e('" title="', 'zappoppop'); ?><?php esc_attr_e('Popup Title', 'zappoppop'); ?>">
        <div class="zappoppop-canvas-tabs zappoppop-screen-tabs" id="zappoppop-canvas-tabs-container">
            <div class="zappoppop-canvas-tab-wrapper active">
                <button type="button" class="zappoppop-canvas-tab zappoppop-screen-tab" data-canvas="1" data-screen="1"><?php esc_html_e('Canvas 1', 'zappoppop'); ?></button>
            </div>
        <button type="button" class="zappoppop-btn-tool" id="zappoppop-canvas-duplicate-btn" title="<?php esc_attr_e('Duplicate Active Canvas', 'zappoppop'); ?><?php esc_html_e('" aria-label="', 'zappoppop'); ?><?php esc_attr_e('Duplicate Active Canvas', 'zappoppop'); ?>">
            <span class="dashicons dashicons-admin-page"></span>
        </button>
            <div class="zappoppop-canvas-tab-wrapper">
                <button type="button" class="zappoppop-canvas-tab zappoppop-screen-tab" data-canvas="2" data-screen="2"><?php esc_html_e('Confirmation', 'zappoppop'); ?></button>
                <span class="zappoppop-tab-delete-canvas" data-canvas="2" title="<?php esc_attr_e('Delete Canvas', 'zappoppop'); ?>">&times;</span>
            </div>
            <button type="button" id="zappoppop-add-canvas-btn" class="zappoppop-canvas-add-btn" title="<?php esc_attr_e('Add Canvas', 'zappoppop'); ?>">+</button>
        </div>
    </div>

    <!-- Center Section: Canvas Label (Outside Workspace) & Sequence Tabs -->
    <div class="zappoppop-hdr-group zappoppop-hdr-center">

        <div class="zappoppop-viewport-toggles"<?php echo $__zpp_vp_on ? '' : ' style="display:none !important;" aria-hidden="true"'; ?>>
            <button type="button" class="zappoppop-viewport-btn active" data-mode="desktop" title="<?php esc_attr_e('Desktop Viewport (640px)', 'zappoppop'); ?>">
                <span class="dashicons dashicons-desktop"></span>
            </button>
            <button type="button" class="zappoppop-viewport-btn" data-mode="mobile" title="<?php esc_attr_e('Mobile Viewport (360px)', 'zappoppop'); ?>">
                <span class="dashicons dashicons-smartphone"></span>
            </button>
        </div>
    </div>

    <!-- Right Section: Preview, Embed, Save Actions (Clean Text Buttons) -->
    <div class="zappoppop-hdr-group zappoppop-hdr-right">
        <select id="zappoppop-zoom-select" class="zappoppop-zoom-select"<?php echo $__zpp_zm_on ? '' : ' style="display:none !important;" aria-hidden="true" tabindex="-1"'; ?> autocomplete="off" title="<?php esc_attr_e('Stage Zoom', 'zappoppop'); ?><?php esc_html_e('" aria-label="', 'zappoppop'); ?><?php esc_attr_e('Stage Zoom', 'zappoppop'); ?>">
            <option value="0.5">50%</option>
            <option value="0.75">75%</option>
            <option value="1" selected>100%</option>
            <option value="1.25">125%</option>
            <option value="fit"><?php esc_html_e('Fit', 'zappoppop'); ?></option>
        </select>
        <?php if (ZapPopPop_Admin_Guide::active()) : ?>
        <button type="button" class="zappoppop-btn zappoppop-btn-secondary zappoppop-hdr-icon-btn" id="zappoppop-btn-guide" title="<?php esc_attr_e('Builder guide', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Builder guide', 'zappoppop'); ?>"><span class="dashicons dashicons-editor-help" aria-hidden="true"></span></button>
        <?php endif; ?>
        <button type="button" class="zappoppop-btn zappoppop-btn-secondary zappoppop-hdr-icon-btn" id="zappoppop-btn-preview" title="<?php esc_attr_e('Preview', 'zappoppop'); ?><?php esc_html_e('" aria-label="', 'zappoppop'); ?><?php esc_attr_e('Preview', 'zappoppop'); ?>"><i class="fa-solid fa-eye" aria-hidden="true"></i></button>
        <button type="button" class="zappoppop-btn zappoppop-btn-secondary zappoppop-hdr-icon-btn" id="zappoppop-btn-preview-home" title="<?php esc_attr_e('Preview on Homepage', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Preview on Homepage', 'zappoppop'); ?>"><i class="fa-solid fa-desktop" aria-hidden="true"></i></button>
        <button type="button" id="zappoppop-btn-embed" class="button zappoppop-hdr-icon-btn" title="<?php esc_attr_e('Get Embed Codes', 'zappoppop'); ?><?php esc_html_e('" aria-label="', 'zappoppop'); ?><?php esc_attr_e('Embed', 'zappoppop'); ?>"><i class="fa-solid fa-code" aria-hidden="true"></i></button>
        <div class="zappoppop-save-split" id="zappoppop-save-split">
            <button type="button" class="zappoppop-btn zappoppop-btn-primary zappoppop-btn-save" id="zappoppop-btn-save"><?php esc_html_e('Save', 'zappoppop'); ?></button>
            <button type="button" id="zappoppop-btn-save-menu" class="zappoppop-save-caret" aria-haspopup="true" aria-expanded="false" aria-controls="zappoppop-save-menu" title="<?php esc_attr_e('More save options', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('More save options', 'zappoppop'); ?>"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false" style="pointer-events:none;"><polyline points="6 9 12 15 18 9"></polyline></svg></button>
            <div class="zappoppop-save-menu" id="zappoppop-save-menu" role="menu" aria-labelledby="zappoppop-btn-save-menu">
                <button type="button" role="menuitem" class="zappoppop-save-menu-item" id="zappoppop-menu-save-draft" data-status="<?php echo esc_attr(($popup && isset($popup->status)) ? (string) $popup->status : ''); ?>"><span class="dashicons dashicons-edit" aria-hidden="true"></span><span class="zappoppop-save-menu-label"><?php esc_html_e('Save Draft', 'zappoppop'); ?></span><span class="zappoppop-save-menu-state"></span></button>
                <?php if ($__zpp_tpl_on) : ?>
                <button type="button" role="menuitem" class="zappoppop-save-menu-item" id="zappoppop-menu-save-template"><span class="dashicons dashicons-layout" aria-hidden="true"></span><span class="zappoppop-save-menu-label"><?php esc_html_e('Save as Template', 'zappoppop'); ?></span></button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
