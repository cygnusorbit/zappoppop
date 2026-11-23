<?php
if (!defined('ABSPATH')) exit;
?>
<!-- ZAPPOPPOP UNIFIED STYLE TAB SUITE -->
<?php ZapPopPop_Inline_Assets::style('builder-elements-tab-style-full'); ?>
<!-- ZAPPOPPOP_TL_NOBORDER --><!-- ZAPPOPPOP_TL_STYLE: Tile hides icons/opacity/background/padding/shadow/custom css; shows border + hover state -->

<!-- ZAPPOPPOP_SHAPE_STYLE: Shape hides Opacity & Visibility and Background Color (fill is set in Shape / Vector Geometry); CSS-only so selection never flashes hidden cards -->

<!-- ZAPPOPPOP_RD_STYLE: Radio hides everything except Alignment and Typography -->

<!-- ZAPPOPPOP_CB_STYLE: Checkbox hides all style cards except Typography -->

<!-- ZAPPOPPOP_TF_STYLE_LEAN: Text Field hides generic style cards -->

<!-- ZAPPOPPOP_DATE_STYLE: Date hides Opacity/Visibility, Background Color, Padding, Box Shadow, Custom CSS & Inline Styles -->

<!-- A. FORM INPUT STYLE MODULE (Form Family 10-25) -->
<div id="zappoppop-form-style-module" class="zappoppop-insp-section" style="display:none; margin-bottom:14px;">
    <!-- ZPP_NO_TEXT_ALIGN: Input Text Alignment card removed -->
    <div id="zappoppop-form-icons-card" class="zappoppop-insp-card" style="background:#0f172a; border:1px solid #1e293b; border-radius:8px; padding:12px; margin-bottom:12px;">
        <div style="font-size:10px; font-weight:800; color:#38bdf8; text-transform:uppercase; margin-bottom:8px;"><?php esc_html_e('Input Icons & Offsets', 'zappoppop'); ?></div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
            <div>
                <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('Left Icon', 'zappoppop'); ?></label>
                <div style="display:flex; gap:4px; align-items:center;">
                    <button type="button" class="button zappoppop-fa-pick-btn" data-target="#prop-form-icon-left" title="<?php esc_attr_e('Choose icon', 'zappoppop'); ?><?php esc_html_e('" aria-label="', 'zappoppop'); ?><?php esc_attr_e('Choose left icon', 'zappoppop'); ?>" style="min-width:36px; height:30px; padding:0; display:inline-flex; align-items:center; justify-content:center;"><i class="fas fa-icons" aria-hidden="true"></i></button>
                    <button type="button" class="button zappoppop-fa-clear-btn" data-target="#prop-form-icon-left" title="<?php esc_attr_e('Clear icon', 'zappoppop'); ?><?php esc_html_e('" aria-label="', 'zappoppop'); ?><?php esc_attr_e('Clear left icon', 'zappoppop'); ?>" style="min-width:30px; height:30px; padding:0;">&times;</button>
                    <input type="text" id="prop-form-icon-left" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="iconLeft" placeholder="fas fa-envelope" style="font-size:11px; min-width:0;" />
                </div>
                <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin:8px 0 4px;"><?php esc_html_e('Offset (px)', 'zappoppop'); ?></label>
                <input type="number" id="prop-form-icon-left-offset" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="iconLeftOffset" min="0" max="40" value="10" style="font-size:12px;" />
                <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin:8px 0 4px;"><?php esc_html_e('Icon Color', 'zappoppop'); ?></label>
                <div style="display:flex; gap:4px; align-items:center;">
                    <input type="color" data-zpp-alpha="compact" id="prop-form-icon-left-color" class="zappoppop-gp-bind" data-prop="iconLeftColor" value="#64748b" style="flex:1; height:30px; padding:2px; background:#090d16; border:1px solid #1e293b; border-radius:4px; cursor:pointer; min-width:0;" />
                    <button type="button" class="button zappoppop-icon-color-reset" data-target="#prop-form-icon-left-color" title="<?php esc_attr_e('Reset color', 'zappoppop'); ?><?php esc_html_e('" aria-label="', 'zappoppop'); ?><?php esc_attr_e('Reset left icon color', 'zappoppop'); ?>" style="height:30px; min-width:30px; padding:0; font-size:11px;">&#8635;</button>
                </div>
            </div>
            <div>
                <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('Right Icon', 'zappoppop'); ?></label>
                <div style="display:flex; gap:4px; align-items:center;">
                    <button type="button" class="button zappoppop-fa-pick-btn" data-target="#prop-form-icon-right" title="<?php esc_attr_e('Choose icon', 'zappoppop'); ?><?php esc_html_e('" aria-label="', 'zappoppop'); ?><?php esc_attr_e('Choose right icon', 'zappoppop'); ?>" style="min-width:36px; height:30px; padding:0; display:inline-flex; align-items:center; justify-content:center;"><i class="fas fa-icons" aria-hidden="true"></i></button>
                    <button type="button" class="button zappoppop-fa-clear-btn" data-target="#prop-form-icon-right" title="<?php esc_attr_e('Clear icon', 'zappoppop'); ?><?php esc_html_e('" aria-label="', 'zappoppop'); ?><?php esc_attr_e('Clear right icon', 'zappoppop'); ?>" style="min-width:30px; height:30px; padding:0;">&times;</button>
                    <input type="text" id="prop-form-icon-right" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="iconRight" placeholder="fas fa-check" style="font-size:11px; min-width:0;" />
                </div>
                <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin:8px 0 4px;"><?php esc_html_e('Offset (px)', 'zappoppop'); ?></label>
                <input type="number" id="prop-form-icon-right-offset" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="iconRightOffset" min="0" max="40" value="10" style="font-size:12px;" />
                <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin:8px 0 4px;"><?php esc_html_e('Icon Color', 'zappoppop'); ?></label>
                <div style="display:flex; gap:4px; align-items:center;">
                    <input type="color" data-zpp-alpha="compact" id="prop-form-icon-right-color" class="zappoppop-gp-bind" data-prop="iconRightColor" value="#64748b" style="flex:1; height:30px; padding:2px; background:#090d16; border:1px solid #1e293b; border-radius:4px; cursor:pointer; min-width:0;" />
                    <button type="button" class="button zappoppop-icon-color-reset" data-target="#prop-form-icon-right-color" title="<?php esc_attr_e('Reset color', 'zappoppop'); ?><?php esc_html_e('" aria-label="', 'zappoppop'); ?><?php esc_attr_e('Reset right icon color', 'zappoppop'); ?>" style="height:30px; min-width:30px; padding:0; font-size:11px;">&#8635;</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- B. EFFECT: blend mode, opacity, transform list, filter list (ZPP_EFFECTS) -->
<?php
$zappoppop_fx_blend = array(
    'normal' => __('Normal', 'zappoppop'), 'multiply' => __('Multiply', 'zappoppop'), 'screen' => __('Screen', 'zappoppop'), 'overlay' => __('Overlay', 'zappoppop'),
    'darken' => __('Darken', 'zappoppop'), 'lighten' => __('Lighten', 'zappoppop'), 'color-dodge' => __('Color Dodge', 'zappoppop'), 'color-burn' => __('Color Burn', 'zappoppop'),
    'hard-light' => __('Hard Light', 'zappoppop'), 'soft-light' => __('Soft Light', 'zappoppop'), 'difference' => __('Difference', 'zappoppop'), 'exclusion' => __('Exclusion', 'zappoppop'),
    'hue' => __('Hue', 'zappoppop'), 'saturation' => __('Saturation', 'zappoppop'), 'color' => __('Color', 'zappoppop'), 'luminosity' => __('Luminosity', 'zappoppop'),
);
$zappoppop_fx_origin = array(
    array('prop-fx-ox', 'fxOriginX', __('Origin X', 'zappoppop'), '%', 0, 100, 50),
    array('prop-fx-oy', 'fxOriginY', __('Origin Y', 'zappoppop'), '%', 0, 100, 50),
    array('prop-fx-oz', 'fxOriginZ', __('Origin Z', 'zappoppop'), 'PX', -500, 500, 0),
);
?>
<div class="zappoppop-insp-section zpp-fx" id="insp-section-opacity" style="background:#0f172a; border:1px solid #1e293b; border-radius:8px; padding:12px; margin-bottom:12px;">
    <div style="font-size:10px; font-weight:800; color:#38bdf8; text-transform:uppercase; margin-bottom:8px;"><?php esc_html_e('Effect', 'zappoppop'); ?></div>
    <div id="zpp-fx-root"
        data-l-inc="<?php esc_attr_e('Increase', 'zappoppop'); ?>" data-l-dec="<?php esc_attr_e('Decrease', 'zappoppop'); ?>" data-l-reset="<?php esc_attr_e('Reset', 'zappoppop'); ?>"
        data-l-eye="<?php esc_attr_e('Show / hide', 'zappoppop'); ?>" data-l-remove="<?php esc_attr_e('Remove', 'zappoppop'); ?>" data-l-type="<?php esc_attr_e('Transform', 'zappoppop'); ?>"
        data-l-filter="<?php esc_attr_e('Filter', 'zappoppop'); ?>" data-l-color="<?php esc_attr_e('Color', 'zappoppop'); ?>" data-l-value="<?php esc_attr_e('Value', 'zappoppop'); ?>"
        data-l-t_move="<?php esc_attr_e('Move', 'zappoppop'); ?>" data-l-t_scale="<?php esc_attr_e('Scale', 'zappoppop'); ?>" data-l-t_rotate="<?php esc_attr_e('Rotate', 'zappoppop'); ?>" data-l-t_skew="<?php esc_attr_e('Skew', 'zappoppop'); ?>" data-l-t_perspective="<?php esc_attr_e('Perspective', 'zappoppop'); ?>"
        data-l-f_blur="<?php esc_attr_e('Blur', 'zappoppop'); ?>" data-l-f_brightness="<?php esc_attr_e('Brightness', 'zappoppop'); ?>" data-l-f_contrast="<?php esc_attr_e('Contrast', 'zappoppop'); ?>" data-l-f_saturate="<?php esc_attr_e('Saturate', 'zappoppop'); ?>" data-l-f_hue-rotate="<?php esc_attr_e('Hue Rotate', 'zappoppop'); ?>" data-l-f_grayscale="<?php esc_attr_e('Grayscale', 'zappoppop'); ?>" data-l-f_invert="<?php esc_attr_e('Invert', 'zappoppop'); ?>" data-l-f_opacity="<?php esc_attr_e('Opacity', 'zappoppop'); ?>" data-l-f_sepia="<?php esc_attr_e('Sepia', 'zappoppop'); ?>" data-l-f_drop-shadow="<?php esc_attr_e('Drop Shadow', 'zappoppop'); ?>">
        <div class="zpp-fx-f">
            <label for="prop-blend-mode"><?php esc_html_e('Blend mode', 'zappoppop'); ?></label>
            <select id="prop-blend-mode" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="blendMode" style="font-size:12px;">
                <?php foreach ($zappoppop_fx_blend as $zappoppop_fx_v => $zappoppop_fx_l) : ?>
                <option value="<?php echo esc_attr($zappoppop_fx_v); ?>"><?php echo esc_html($zappoppop_fx_l); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="zpp-fx-f zpp-fx-op">
            <label for="prop-opacity"><?php esc_html_e('Opacity', 'zappoppop'); ?></label>
            <span style="display:flex; align-items:center; gap:10px;">
                <input type="range" id="prop-opacity" class="zappoppop-gp-bind" data-prop="opacity" min="0" max="100" value="100" style="flex:1;" />
                <span id="prop-opacity-val" style="font-size:12px; font-weight:700; color:#38bdf8; min-width:38px; text-align:right;">100%</span>
            </span>
        </div>
        <div class="zpp-fx-head">
            <label class="zpp-fx-title"><?php esc_html_e('Transform', 'zappoppop'); ?></label>
            <span class="zpp-fx-tools">
                <button type="button" class="zpp-fx-tb" data-fx-origin="1" aria-expanded="false" aria-controls="zpp-fx-origin" title="<?php esc_attr_e('Transform settings', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Transform settings', 'zappoppop'); ?>"><span class="dashicons dashicons-admin-settings" aria-hidden="true"></span></button>
                <button type="button" class="zpp-fx-tb" data-fx-add="t" title="<?php esc_attr_e('Add transform', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Add transform', 'zappoppop'); ?>"><span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span></button>
            </span>
        </div>
        <div class="zpp-fx-origin is-collapsed" id="zpp-fx-origin">
            <?php foreach ($zappoppop_fx_origin as $zappoppop_fx_o) : ?>
            <div class="zpp-fx-f">
                <label for="<?php echo esc_attr($zappoppop_fx_o[0]); ?>"><?php echo esc_html($zappoppop_fx_o[2]); ?></label>
                <span class="zpp-ty-numwrap"><span class="zpp-ty-ctl"><input type="number" id="<?php echo esc_attr($zappoppop_fx_o[0]); ?>" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="<?php echo esc_attr($zappoppop_fx_o[1]); ?>" min="<?php echo esc_attr($zappoppop_fx_o[4]); ?>" max="<?php echo esc_attr($zappoppop_fx_o[5]); ?>" value="<?php echo esc_attr($zappoppop_fx_o[6]); ?>" /><span class="zpp-ty-unit"><?php echo esc_html($zappoppop_fx_o[3]); ?></span><span class="zpp-sp-step"><button type="button" class="zpp-sp-up" tabindex="-1" title="<?php esc_attr_e('Increase', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Increase', 'zappoppop'); ?>"></button><button type="button" class="zpp-sp-down" tabindex="-1" title="<?php esc_attr_e('Decrease', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Decrease', 'zappoppop'); ?>"></button></span></span><button type="button" class="zpp-sp-reset zpp-ty-reset" data-default="<?php echo esc_attr($zappoppop_fx_o[6]); ?>" title="<?php esc_attr_e('Reset', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Reset', 'zappoppop'); ?>"><span class="dashicons dashicons-image-rotate" aria-hidden="true"></span></button></span>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="zpp-fx-list" id="zpp-fx-tlist"></div>
        <div class="zpp-fx-head">
            <label class="zpp-fx-title"><?php esc_html_e('Filters', 'zappoppop'); ?></label>
            <span class="zpp-fx-tools"><button type="button" class="zpp-fx-tb" data-fx-add="f" title="<?php esc_attr_e('Add filter', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Add filter', 'zappoppop'); ?>"><span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span></button></span>
        </div>
        <div class="zpp-fx-list" id="zpp-fx-flist"></div>
    </div>
</div>

<!-- C. BACKGROUND (layer box): Overlay stack (Image / Gradient / Color), base Color, Clipping (ZPP_BACKGROUND) -->
<?php
$zappoppop_bg_seg = static function ($f, $opts) {
    ?>
    <div class="zpp-ty-seg zpp-bgo-seg" data-f="<?php echo esc_attr($f); ?>" role="group">
        <?php foreach ($opts as $v => $o) : ?>
        <button type="button" class="zpp-ty-b" data-v="<?php echo esc_attr($v); ?>" aria-pressed="false" title="<?php echo esc_attr($o[1]); ?>" aria-label="<?php echo esc_attr($o[1]); ?>"><?php if ('' !== $o[0]) : ?><span class="dashicons <?php echo esc_attr($o[0]); ?>" aria-hidden="true"></span><?php else : ?><span class="zpp-ty-glyph"><?php echo esc_html($o[1]); ?></span><?php endif; ?></button>
        <?php endforeach; ?>
    </div>
    <?php
};
?>
<div class="zappoppop-insp-section zpp-bg" id="insp-section-bg-color" style="background:#0f172a; border:1px solid #1e293b; border-radius:8px; padding:12px; margin-bottom:12px;">
    <div class="zappoppop-sec-head"><span class="zappoppop-sec-title"><?php esc_html_e('Background', 'zappoppop'); ?></span></div>
    <div class="zpp-bgo-head">
        <label class="zpp-ty-lab"><?php esc_html_e('Overlay', 'zappoppop'); ?></label>
        <button type="button" class="zpp-bgo-add" id="zpp-bgo-add" title="<?php esc_attr_e('Add overlay', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Add overlay', 'zappoppop'); ?>"><span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span></button>
    </div>
    <div class="zpp-bgo-list" id="zpp-bgo-list"></div>
    <div class="zpp-bg-divider" role="separator"></div><!-- ZPP_BG_DIVIDER -->
    <div class="zpp-ty-row">
        <label class="zpp-ty-lab" for="prop-bg-color"><?php esc_html_e('Color', 'zappoppop'); ?></label>
        <input type="color" id="prop-bg-color" class="widefat zappoppop-gp-bind" data-zpp-alpha="1" data-prop="bgColor" value="#3b82f6" style="height:34px; padding:2px; background:#090d16; border:1px solid #1e293b; border-radius:6px; cursor:pointer;" />
    </div>
    <div class="zpp-ty-row">
        <label class="zpp-ty-lab" for="prop-bg-clip"><?php esc_html_e('Clipping', 'zappoppop'); ?></label>
        <select id="prop-bg-clip" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="bgClip" style="font-size:12px;">
            <option value=""><?php esc_html_e('Full element', 'zappoppop'); ?></option>
            <option value="padding"><?php esc_html_e('Padding edges', 'zappoppop'); ?></option>
            <option value="content"><?php esc_html_e('Content edges', 'zappoppop'); ?></option>
            <option value="text"><?php esc_html_e('Text', 'zappoppop'); ?></option>
        </select>
    </div>
    <label style="display:none;">
        <input type="checkbox" id="prop-bg-transparent" class="zappoppop-gp-bind" data-prop="transparentFill" value="1" /> <?php esc_html_e('Transparent', 'zappoppop'); ?>
    </label>
    <template id="zpp-bgo-tpl"
        data-l-image="<?php esc_attr_e('Image', 'zappoppop'); ?>"
        data-l-linear="<?php esc_attr_e('Linear Gradient', 'zappoppop'); ?>"
        data-l-radial="<?php esc_attr_e('Radial Gradient', 'zappoppop'); ?>"
        data-l-color="<?php esc_attr_e('Color', 'zappoppop'); ?>"
        data-l-remove="<?php esc_attr_e('Remove overlay', 'zappoppop'); ?>">
        <div class="zpp-bgo-panel">
            <div class="zpp-bgo-tabs" role="tablist">
                <button type="button" class="zpp-bgo-tab" data-t="image" role="tab"><?php esc_html_e('Image', 'zappoppop'); ?></button>
                <button type="button" class="zpp-bgo-tab" data-t="gradient" role="tab"><?php esc_html_e('Gradient', 'zappoppop'); ?></button>
                <button type="button" class="zpp-bgo-tab" data-t="color" role="tab"><?php esc_html_e('Color', 'zappoppop'); ?></button>
            </div>
            <div class="zpp-bgo-pane" data-pane="image">
                <div class="zpp-bgo-imgwrap">
                    <div class="zpp-bgo-img" data-act="pick" role="button" tabindex="0" title="<?php esc_attr_e('Choose image', 'zappoppop'); ?>"><span class="zpp-bgo-ph"><?php esc_html_e('Choose image', 'zappoppop'); ?></span></div>
                    <button type="button" class="zpp-bgo-imgx" data-act="clearimg" title="<?php esc_attr_e('Remove image', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Remove image', 'zappoppop'); ?>"><span class="dashicons dashicons-no-alt" aria-hidden="true"></span></button>
                </div>
                <div class="zpp-ty-row"><label class="zpp-ty-lab"><?php esc_html_e('Resolution', 'zappoppop'); ?></label>
                    <select class="zappoppop-input zpp-bgo-in" data-f="res">
                        <option value="thumbnail"><?php esc_html_e('Thumbnail', 'zappoppop'); ?></option>
                        <option value="medium"><?php esc_html_e('Medium', 'zappoppop'); ?></option>
                        <option value="large"><?php esc_html_e('Large', 'zappoppop'); ?></option>
                        <option value="full"><?php esc_html_e('Full size', 'zappoppop'); ?></option>
                    </select></div>
                <div class="zpp-ty-row"><label class="zpp-ty-lab"><?php esc_html_e('Position', 'zappoppop'); ?></label>
                    <select class="zappoppop-input zpp-bgo-in" data-f="pos">
                        <option value="center center"><?php esc_html_e('Center Center', 'zappoppop'); ?></option>
                        <option value="center left"><?php esc_html_e('Center Left', 'zappoppop'); ?></option>
                        <option value="center right"><?php esc_html_e('Center Right', 'zappoppop'); ?></option>
                        <option value="top left"><?php esc_html_e('Top Left', 'zappoppop'); ?></option>
                        <option value="top center"><?php esc_html_e('Top Center', 'zappoppop'); ?></option>
                        <option value="top right"><?php esc_html_e('Top Right', 'zappoppop'); ?></option>
                        <option value="bottom left"><?php esc_html_e('Bottom Left', 'zappoppop'); ?></option>
                        <option value="bottom center"><?php esc_html_e('Bottom Center', 'zappoppop'); ?></option>
                        <option value="bottom right"><?php esc_html_e('Bottom Right', 'zappoppop'); ?></option>
                    </select></div>
                <div class="zpp-ty-row"><label class="zpp-ty-lab"><?php esc_html_e('Repeat', 'zappoppop'); ?></label>
                    <?php $zappoppop_bg_seg('repeat', array(
                        'repeat'    => array('dashicons-grid-view', __('Repeat', 'zappoppop')),
                        'repeat-x'  => array('dashicons-leftright', __('Repeat horizontally', 'zappoppop')),
                        'repeat-y'  => array('dashicons-sort', __('Repeat vertically', 'zappoppop')),
                        'no-repeat' => array('dashicons-no-alt', __('No repeat', 'zappoppop')),
                    )); ?></div>
                <div class="zpp-ty-row"><label class="zpp-ty-lab"><?php esc_html_e('Size', 'zappoppop'); ?></label>
                    <?php $zappoppop_bg_seg('size', array(
                        'auto'    => array('', __('Auto', 'zappoppop')),
                        'cover'   => array('', __('Cover', 'zappoppop')),
                        'contain' => array('', __('Contain', 'zappoppop')),
                        'custom'  => array('dashicons-edit', __('Custom', 'zappoppop')),
                    )); ?></div>
                <div class="zpp-ty-row" data-row="sizeCustom" hidden><label class="zpp-ty-lab"><?php esc_html_e('Width', 'zappoppop'); ?></label>
                    <span class="zpp-ty-ctl"><input type="number" class="zappoppop-input zpp-bgo-in" data-f="sizeCustom" min="1" max="300" step="1" value="100" /><span class="zpp-ty-unit">%</span></span></div>
                <div class="zpp-ty-row"><label class="zpp-ty-lab"><?php esc_html_e('Attachment', 'zappoppop'); ?></label>
                    <?php $zappoppop_bg_seg('attach', array(
                        'scroll' => array('', __('Scroll', 'zappoppop')),
                        'fixed'  => array('', __('Fixed', 'zappoppop')),
                    )); ?></div>
            </div>
            <div class="zpp-bgo-pane" data-pane="gradient">
                <div class="zpp-bgo-bar" data-f="bar"><span class="zpp-bgo-barfill"></span>
                    <button type="button" class="zpp-bgo-handle" data-stop="1" aria-label="<?php esc_attr_e('First color stop', 'zappoppop'); ?>"></button>
                    <button type="button" class="zpp-bgo-handle" data-stop="2" aria-label="<?php esc_attr_e('Second color stop', 'zappoppop'); ?>"></button>
                </div>
                <div class="zpp-ty-row"><label class="zpp-ty-lab"><?php esc_html_e('Flip', 'zappoppop'); ?></label>
                    <button type="button" class="zpp-sp-reset zpp-bgo-flip" data-act="flip" title="<?php esc_attr_e('Flip colors', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Flip colors', 'zappoppop'); ?>"><span class="dashicons dashicons-leftright" aria-hidden="true"></span></button></div>
                <div class="zpp-bgo-two">
                    <div><label class="zpp-ty-lab"><?php esc_html_e('Color', 'zappoppop'); ?></label>
                        <input type="color" class="zappoppop-gp-nobind zpp-bgo-in" data-zpp-alpha="1" data-f="stopColor" value="#000000" /></div>
                    <div><label class="zpp-ty-lab"><?php esc_html_e('Stop', 'zappoppop'); ?></label>
                        <span class="zpp-ty-ctl"><input type="number" class="zappoppop-input zpp-bgo-in" data-f="stopPos" min="0" max="100" step="1" value="0" /><span class="zpp-ty-unit">%</span></span></div>
                    <div><label class="zpp-ty-lab"><?php esc_html_e('Type', 'zappoppop'); ?></label>
                        <select class="zappoppop-input zpp-bgo-in" data-f="gtype">
                            <option value="linear"><?php esc_html_e('Linear', 'zappoppop'); ?></option>
                            <option value="radial"><?php esc_html_e('Radial', 'zappoppop'); ?></option>
                        </select></div>
                    <div data-row="angle"><label class="zpp-ty-lab"><?php esc_html_e('Angle', 'zappoppop'); ?></label>
                        <span class="zpp-ty-ctl"><input type="number" class="zappoppop-input zpp-bgo-in" data-f="angle" min="0" max="360" step="1" value="180" /><span class="zpp-ty-unit">DEG</span></span></div>
                </div>
            </div>
            <div class="zpp-bgo-pane" data-pane="color">
                <input type="color" class="zappoppop-gp-nobind zpp-bgo-in" data-zpp-alpha="1" data-f="color" value="#00000033" />
            </div>
        </div>
    </template>
</div>

<!-- D. TYPOGRAPHY & TEXT SPACING -->
<div class="zappoppop-insp-section zpp-ty" id="insp-section-typography" style="background:#0f172a; border:1px solid #1e293b; border-radius:8px; padding:12px; margin-bottom:12px;">
    <div class="zappoppop-sec-head"><span class="zappoppop-sec-title"><?php esc_html_e('Typography', 'zappoppop'); ?></span></div>
    <?php
    /* ZPP_TYPOGRAPHY: segmented rows. Every row keeps one real data-prop control (a visually hidden select) so the Inspector binding, Save / Clear / Reset as Default, the model and the renderers use the normal path; the buttons only drive that control. */
    $zappoppop_ty_segs = array(
        'align' => array('id' => 'prop-text-align', 'prop' => 'textAlign', 'label' => __('Text align', 'zappoppop'), 'row' => 'zpp-ty-align-row', 'opts' => array(
            'left'    => array('icon' => 'dashicons-editor-alignleft',   'title' => __('Left', 'zappoppop')),
            'center'  => array('icon' => 'dashicons-editor-aligncenter', 'title' => __('Center', 'zappoppop')),
            'right'   => array('icon' => 'dashicons-editor-alignright',  'title' => __('Right', 'zappoppop')),
            'justify' => array('icon' => 'dashicons-editor-justify',     'title' => __('Justify', 'zappoppop')),
        )),
        'deco' => array('id' => 'prop-line-deco', 'prop' => 'lineDecoration', 'label' => __('Line decoration', 'zappoppop'), 'opts' => array(
            'none'         => array('glyph' => '—', 'title' => __('None', 'zappoppop')),
            'underline'    => array('glyph' => 'T', 'class' => 'zpp-ty-g-under', 'title' => __('Underline', 'zappoppop')),
            'line-through' => array('glyph' => 'T', 'class' => 'zpp-ty-g-strike', 'title' => __('Line through', 'zappoppop')),
            'overline'     => array('glyph' => 'T', 'class' => 'zpp-ty-g-over', 'title' => __('Overline', 'zappoppop')),
        )),
        'tt' => array('id' => 'prop-text-transform', 'prop' => 'textTransform', 'label' => __('Text transform', 'zappoppop'), 'opts' => array(
            'none'       => array('glyph' => '—',  'title' => __('None', 'zappoppop')),
            'capitalize' => array('glyph' => 'Aa', 'title' => __('Capitalize', 'zappoppop')),
            'uppercase'  => array('glyph' => 'AA', 'title' => __('UPPERCASE', 'zappoppop')),
            'lowercase'  => array('glyph' => 'aa', 'title' => __('lowercase', 'zappoppop')),
        )),
        'dir' => array('id' => 'prop-text-dir', 'prop' => 'textDirection', 'label' => __('Direction', 'zappoppop'), 'opts' => array(
            'ltr' => array('icon' => 'dashicons-editor-ltr', 'title' => __('Left to right', 'zappoppop')),
            'rtl' => array('icon' => 'dashicons-editor-rtl', 'title' => __('Right to left', 'zappoppop')),
        )),
        'fs' => array('id' => 'prop-font-style', 'prop' => 'fontStyle', 'label' => __('Font style', 'zappoppop'), 'opts' => array(
            'normal' => array('glyph' => '—', 'title' => __('Normal', 'zappoppop')),
            'italic' => array('glyph' => 'I', 'class' => 'zpp-ty-g-ital', 'title' => __('Italic', 'zappoppop')),
        )),
    );
    $zappoppop_ty_seg = static function ($k) use ($zappoppop_ty_segs) {
        $s = $zappoppop_ty_segs[$k];
        ?>
        <div class="zpp-ty-row"<?php echo 'align' === $k ? ' id="zpp-ty-align-row" hidden' : ''; ?>>
            <label class="zpp-ty-lab"><?php echo esc_html($s['label']); ?></label>
            <div class="zpp-ty-seg" data-zpp-seg="#<?php echo esc_attr($s['id']); ?>" role="group" aria-label="<?php echo esc_attr($s['label']); ?>">
                <?php foreach ($s['opts'] as $v => $o) : ?>
                <button type="button" class="zpp-ty-b" data-v="<?php echo esc_attr($v); ?>" aria-pressed="false" title="<?php echo esc_attr($o['title']); ?>" aria-label="<?php echo esc_attr($o['title']); ?>"><?php if (!empty($o['icon'])) : ?><span class="dashicons <?php echo esc_attr($o['icon']); ?>" aria-hidden="true"></span><?php else : ?><span class="zpp-ty-glyph <?php echo esc_attr(isset($o['class']) ? $o['class'] : ''); ?>" aria-hidden="true"><?php echo esc_html($o['glyph']); ?></span><?php endif; ?></button>
                <?php endforeach; ?>
            </div>
            <select id="<?php echo esc_attr($s['id']); ?>" class="zappoppop-input zappoppop-gp-bind zpp-ty-proxy" data-prop="<?php echo esc_attr($s['prop']); ?>" tabindex="-1" aria-hidden="true">
                <?php foreach ($s['opts'] as $v => $o) : ?>
                <option value="<?php echo esc_attr($v); ?>"><?php echo esc_html($o['title']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php
    };
    ?>
    <div class="zpp-ty-row">
        <label class="zpp-ty-lab" for="prop-font-family"><?php esc_html_e('Font family', 'zappoppop'); ?></label>
        <select id="prop-font-family" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="fontFamily" style="font-size:12px;">
            <?php echo wp_kses(ZapPopPop_Font_Catalog::options_html(), array('optgroup' => array('label' => true), 'option' => array('value' => true, 'data-weights' => true))); ?>
        </select>
    </div>
    <div class="zpp-ty-row">
        <label class="zpp-ty-lab" for="prop-font-weight"><?php esc_html_e('Font weight', 'zappoppop'); ?></label>
        <select id="prop-font-weight" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="fontWeight" style="font-size:12px;">
            <option value="300"><?php esc_html_e('Light (300)', 'zappoppop'); ?></option>
            <option value="400"><?php esc_html_e('Regular (400)', 'zappoppop'); ?></option>
            <option value="500"><?php esc_html_e('Medium (500)', 'zappoppop'); ?></option>
            <option value="600"><?php esc_html_e('Semi-Bold (600)', 'zappoppop'); ?></option>
            <option value="700"><?php esc_html_e('Bold (700)', 'zappoppop'); ?></option>
            <option value="800"><?php esc_html_e('Extra-Bold (800)', 'zappoppop'); ?></option>
            <option value="900"><?php esc_html_e('Black (900)', 'zappoppop'); ?></option>
        </select>
    </div>
    <div class="zpp-ty-row">
        <label class="zpp-ty-lab" for="prop-font-size"><?php esc_html_e('Font size', 'zappoppop'); ?></label>
        <span class="zpp-ty-numwrap"><span class="zpp-ty-ctl"><input type="number" id="prop-font-size" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="fontSize" min="8" max="120" value="16" /><span class="zpp-ty-unit">PX</span><span class="zpp-sp-step"><button type="button" class="zpp-sp-up" tabindex="-1" title="<?php esc_attr_e('Increase', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Increase', 'zappoppop'); ?>"></button><button type="button" class="zpp-sp-down" tabindex="-1" title="<?php esc_attr_e('Decrease', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Decrease', 'zappoppop'); ?>"></button></span></span><button type="button" class="zpp-sp-reset zpp-ty-reset" data-default="16" title="<?php esc_attr_e('Reset', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Reset', 'zappoppop'); ?>"><span class="dashicons dashicons-image-rotate" aria-hidden="true"></span></button></span>
    </div>

    <div class="zpp-ty-divider" role="separator"></div>

    <?php $zappoppop_ty_seg('align'); ?>
    <div class="zpp-ty-row">
        <label class="zpp-ty-lab" for="prop-color"><?php esc_html_e('Text color', 'zappoppop'); ?></label>
        <input type="color" id="prop-color" class="widefat zappoppop-gp-bind" data-zpp-alpha="1" data-prop="color" value="#ffffff" style="height:34px; padding:2px; background:#090d16; border:1px solid #1e293b; border-radius:6px; cursor:pointer;" />
    </div>

    <button type="button" class="zpp-ty-more-btn" id="zpp-ty-more-btn" aria-expanded="true" aria-controls="zpp-ty-more" data-less="<?php esc_attr_e('Show less', 'zappoppop'); ?>" data-more="<?php esc_attr_e('Show more', 'zappoppop'); ?>"><span class="zpp-ty-more-text"><?php esc_html_e('Show less', 'zappoppop'); ?></span><span class="zpp-ty-chev" aria-hidden="true"></span></button>

    <div class="zpp-ty-more" id="zpp-ty-more">
        <div class="zpp-ty-row">
            <label class="zpp-ty-lab" for="prop-line-height"><?php esc_html_e('Line height', 'zappoppop'); ?></label>
            <span class="zpp-ty-numwrap"><span class="zpp-ty-ctl"><input type="number" id="prop-line-height" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="lineHeight" step="0.1" min="0.8" max="3" value="1.4" /><span class="zpp-ty-unit">×</span><span class="zpp-sp-step"><button type="button" class="zpp-sp-up" tabindex="-1" title="<?php esc_attr_e('Increase', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Increase', 'zappoppop'); ?>"></button><button type="button" class="zpp-sp-down" tabindex="-1" title="<?php esc_attr_e('Decrease', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Decrease', 'zappoppop'); ?>"></button></span></span><button type="button" class="zpp-sp-reset zpp-ty-reset" data-default="1.4" title="<?php esc_attr_e('Reset', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Reset', 'zappoppop'); ?>"><span class="dashicons dashicons-image-rotate" aria-hidden="true"></span></button></span>
        </div>
        <div class="zpp-ty-row">
            <label class="zpp-ty-lab" for="prop-letter-spacing"><?php esc_html_e('Letter spacing', 'zappoppop'); ?></label>
            <span class="zpp-ty-numwrap"><span class="zpp-ty-ctl"><input type="number" id="prop-letter-spacing" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="letterSpacing" step="0.5" min="-5" max="20" value="0" /><span class="zpp-ty-unit">PX</span><span class="zpp-sp-step"><button type="button" class="zpp-sp-up" tabindex="-1" title="<?php esc_attr_e('Increase', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Increase', 'zappoppop'); ?>"></button><button type="button" class="zpp-sp-down" tabindex="-1" title="<?php esc_attr_e('Decrease', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Decrease', 'zappoppop'); ?>"></button></span></span><button type="button" class="zpp-sp-reset zpp-ty-reset" data-default="0" title="<?php esc_attr_e('Reset', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Reset', 'zappoppop'); ?>"><span class="dashicons dashicons-image-rotate" aria-hidden="true"></span></button></span>
        </div>
        <!-- ZPP_WORD_SPACING -->
        <div class="zpp-ty-row">
            <label class="zpp-ty-lab" for="prop-word-spacing"><?php esc_html_e('Word spacing', 'zappoppop'); ?></label>
            <span class="zpp-ty-numwrap"><span class="zpp-ty-ctl"><input type="number" id="prop-word-spacing" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="wordSpacing" step="0.5" min="-10" max="50" value="0" /><span class="zpp-ty-unit">PX</span><span class="zpp-sp-step"><button type="button" class="zpp-sp-up" tabindex="-1" title="<?php esc_attr_e('Increase', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Increase', 'zappoppop'); ?>"></button><button type="button" class="zpp-sp-down" tabindex="-1" title="<?php esc_attr_e('Decrease', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Decrease', 'zappoppop'); ?>"></button></span></span><button type="button" class="zpp-sp-reset zpp-ty-reset" data-default="0" title="<?php esc_attr_e('Reset', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Reset', 'zappoppop'); ?>"><span class="dashicons dashicons-image-rotate" aria-hidden="true"></span></button></span>
        </div>

        <div class="zpp-ty-divider" role="separator"></div>

        <?php $zappoppop_ty_seg('deco'); ?>
        <!-- ZPP_TEXT_TRANSFORM -->
        <?php $zappoppop_ty_seg('tt'); ?>
        <?php $zappoppop_ty_seg('dir'); ?>
        <?php $zappoppop_ty_seg('fs'); ?>

        <div class="zpp-ty-row">
            <label class="zpp-ty-lab" for="prop-text-stroke-w"><?php esc_html_e('Text stroke', 'zappoppop'); ?></label>
            <span class="zpp-ty-numwrap"><span class="zpp-ty-ctl"><input type="number" id="prop-text-stroke-w" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="textStrokeWidth" min="0" max="20" step="0.5" value="0" /><span class="zpp-ty-unit">PX</span><span class="zpp-sp-step"><button type="button" class="zpp-sp-up" tabindex="-1" title="<?php esc_attr_e('Increase', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Increase', 'zappoppop'); ?>"></button><button type="button" class="zpp-sp-down" tabindex="-1" title="<?php esc_attr_e('Decrease', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Decrease', 'zappoppop'); ?>"></button></span></span><button type="button" class="zpp-sp-reset zpp-ty-reset" data-default="0" title="<?php esc_attr_e('Reset', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Reset', 'zappoppop'); ?>"><span class="dashicons dashicons-image-rotate" aria-hidden="true"></span></button></span>
        </div>
        <div class="zpp-ty-row">
            <label class="zpp-ty-lab" for="prop-text-stroke-c"><?php esc_html_e('Stroke color', 'zappoppop'); ?></label>
            <input type="color" id="prop-text-stroke-c" class="widefat zappoppop-gp-bind" data-zpp-alpha="1" data-prop="textStrokeColor" value="#000000" style="height:34px; padding:2px; background:#090d16; border:1px solid #1e293b; border-radius:6px; cursor:pointer;" />
        </div>

        <div class="zpp-ty-divider" role="separator"></div>

    <!-- ZPP_TEXT_SHADOW -->
    <div id="insp-row-text-shadow" style="margin-top:12px;">
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
            <div>
                <label for="prop-tsh-color" style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('Shadow Color', 'zappoppop'); ?></label>
                <input type="color" data-zpp-alpha="compact" id="prop-tsh-color" class="widefat zappoppop-gp-bind" data-prop="textShadowColor" value="#00000080" style="height:34px; padding:2px; background:#090d16; border:1px solid #1e293b; border-radius:6px; cursor:pointer;" />
            </div>
            <div>
                <label for="prop-tsh-pos" style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('Position', 'zappoppop'); ?></label>
                <select id="prop-tsh-pos" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="textShadowPos" style="font-size:12px;">
                    <option value="outset"><?php esc_html_e('Outset', 'zappoppop'); ?></option>
                    <option value="inset"><?php esc_html_e('Inset', 'zappoppop'); ?></option>
                </select>
            </div>
            <div>
                <label for="prop-tsh-x" style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('Horizontal (px)', 'zappoppop'); ?></label>
                <span class="zpp-ty-numwrap"><span class="zpp-ty-ctl"><input type="number" id="prop-tsh-x" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="textShadowX" min="-50" max="50" step="1" value="0" style="font-size:12px;" /><span class="zpp-ty-unit">PX</span><span class="zpp-sp-step"><button type="button" class="zpp-sp-up" tabindex="-1" title="<?php esc_attr_e('Increase', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Increase', 'zappoppop'); ?>"></button><button type="button" class="zpp-sp-down" tabindex="-1" title="<?php esc_attr_e('Decrease', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Decrease', 'zappoppop'); ?>"></button></span></span><button type="button" class="zpp-sp-reset zpp-ty-reset" data-default="0" title="<?php esc_attr_e('Reset', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Reset', 'zappoppop'); ?>"><span class="dashicons dashicons-image-rotate" aria-hidden="true"></span></button></span>
            </div>
            <div>
                <label for="prop-tsh-y" style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('Vertical (px)', 'zappoppop'); ?></label>
                <span class="zpp-ty-numwrap"><span class="zpp-ty-ctl"><input type="number" id="prop-tsh-y" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="textShadowY" min="-50" max="50" step="1" value="0" style="font-size:12px;" /><span class="zpp-ty-unit">PX</span><span class="zpp-sp-step"><button type="button" class="zpp-sp-up" tabindex="-1" title="<?php esc_attr_e('Increase', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Increase', 'zappoppop'); ?>"></button><button type="button" class="zpp-sp-down" tabindex="-1" title="<?php esc_attr_e('Decrease', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Decrease', 'zappoppop'); ?>"></button></span></span><button type="button" class="zpp-sp-reset zpp-ty-reset" data-default="0" title="<?php esc_attr_e('Reset', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Reset', 'zappoppop'); ?>"><span class="dashicons dashicons-image-rotate" aria-hidden="true"></span></button></span>
            </div>
            <div>
                <label for="prop-tsh-blur" style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('Blur (px)', 'zappoppop'); ?></label>
                <span class="zpp-ty-numwrap"><span class="zpp-ty-ctl"><input type="number" id="prop-tsh-blur" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="textShadowBlur" min="0" max="100" step="1" value="0" style="font-size:12px;" /><span class="zpp-ty-unit">PX</span><span class="zpp-sp-step"><button type="button" class="zpp-sp-up" tabindex="-1" title="<?php esc_attr_e('Increase', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Increase', 'zappoppop'); ?>"></button><button type="button" class="zpp-sp-down" tabindex="-1" title="<?php esc_attr_e('Decrease', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Decrease', 'zappoppop'); ?>"></button></span></span><button type="button" class="zpp-sp-reset zpp-ty-reset" data-default="0" title="<?php esc_attr_e('Reset', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Reset', 'zappoppop'); ?>"><span class="dashicons dashicons-image-rotate" aria-hidden="true"></span></button></span>
            </div>
        </div>
    </div>
    </div>
</div>

<!-- E. SPACING: MARGIN + PADDING, 4 sides each, with a Link button (ZPP_SPACING) -->
<?php
$zappoppop_sp_sides = array(
    'top'    => __('Top', 'zappoppop'),
    'right'  => __('Right', 'zappoppop'),
    'bottom' => __('Bottom', 'zappoppop'),
    'left'   => __('Left', 'zappoppop'),
);
$zappoppop_sp_lines = array(
    'top'    => array(1.5, 1.5, 12.5, 1.5),
    'right'  => array(12.5, 1.5, 12.5, 12.5),
    'bottom' => array(1.5, 12.5, 12.5, 12.5),
    'left'   => array(1.5, 1.5, 1.5, 12.5),
);
$zappoppop_sp_groups = array(
    'margin'  => array('label' => __('Margin', 'zappoppop'),  'id' => 'prop-mar-', 'prop' => 'margin',  'hidden' => true),
    'padding' => array('label' => __('Padding', 'zappoppop'), 'id' => 'prop-pad-', 'prop' => 'padding', 'hidden' => false),
);
?>
<div class="zappoppop-insp-section zpp-sp" id="insp-section-padding" style="background:#0f172a; border:1px solid #1e293b; border-radius:8px; padding:12px; margin-bottom:12px;">
    <div class="zappoppop-sec-head"><span class="zappoppop-sec-title"><?php esc_html_e('Spacing', 'zappoppop'); ?></span></div>
    <div class="zpp-sp-body" id="zpp-sp-body">
        <?php foreach ($zappoppop_sp_groups as $zappoppop_sp_key => $zappoppop_sp_g) : ?>
        <div class="zpp-sp-group" data-zpp-sp="<?php echo esc_attr($zappoppop_sp_key); ?>"<?php echo $zappoppop_sp_g['hidden'] ? ' hidden' : ''; ?>>
            <div class="zpp-sp-ghead">
                <span><?php echo esc_html($zappoppop_sp_g['label']); ?></span>
                <span class="zpp-sp-tools"><button type="button" class="zpp-sp-reset" data-zpp-sp-reset="<?php echo esc_attr($zappoppop_sp_key); ?>" title="<?php esc_attr_e('Reset to 0', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Reset to 0', 'zappoppop'); ?>"><span class="dashicons dashicons-image-rotate" aria-hidden="true"></span></button><button type="button" class="zpp-sp-link" data-zpp-sp-link="<?php echo esc_attr($zappoppop_sp_key); ?>" aria-pressed="false" title="<?php esc_attr_e('Link all four values', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Link all four values', 'zappoppop'); ?>"><span class="dashicons dashicons-admin-links" aria-hidden="true"></span></button></span>
            </div>
            <div class="zpp-sp-grid">
                <?php foreach ($zappoppop_sp_sides as $zappoppop_sp_side => $zappoppop_sp_name) : $zappoppop_sp_id = $zappoppop_sp_g['id'] . $zappoppop_sp_side; ?>
                <div class="zpp-sp-cell">
                    <label for="<?php echo esc_attr($zappoppop_sp_id); ?>"><?php echo esc_html($zappoppop_sp_name); ?></label>
                    <span class="zpp-sp-box">
                        <svg class="zpp-sp-ico" viewBox="0 0 14 14" width="14" height="14" aria-hidden="true" focusable="false"><rect x="1.5" y="1.5" width="11" height="11" rx="1" fill="none" stroke="currentColor" stroke-width="1" stroke-dasharray="1.5 1.5" opacity=".55"></rect><line x1="<?php echo esc_attr($zappoppop_sp_lines[$zappoppop_sp_side][0]); ?>" y1="<?php echo esc_attr($zappoppop_sp_lines[$zappoppop_sp_side][1]); ?>" x2="<?php echo esc_attr($zappoppop_sp_lines[$zappoppop_sp_side][2]); ?>" y2="<?php echo esc_attr($zappoppop_sp_lines[$zappoppop_sp_side][3]); ?>" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line></svg>
                        <input type="number" id="<?php echo esc_attr($zappoppop_sp_id); ?>" class="zappoppop-input zappoppop-gp-bind zpp-sp-in" data-prop="<?php echo esc_attr($zappoppop_sp_g['prop'] . ucfirst($zappoppop_sp_side)); ?>" min="0" max="200" value="0" inputmode="numeric" />
                        <span class="zpp-sp-unit">PX</span>
                        <span class="zpp-sp-step"><button type="button" class="zpp-sp-up" tabindex="-1" title="<?php esc_attr_e('Increase', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Increase', 'zappoppop'); ?>"></button><button type="button" class="zpp-sp-down" tabindex="-1" title="<?php esc_attr_e('Decrease', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Decrease', 'zappoppop'); ?>"></button></span>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- F. BORDER & RADIUS (ZPP_BORDER_SIDES) -->
<?php
$zappoppop_bx_groups = array(
    'w' => array(
        'master' => 'prop-border-width',
        'prop'   => 'borderWidth',
        'max'    => 40,
        'cells'  => array(
            'Top'    => array(__('Top', 'zappoppop'),    '', '1.5', '1.5', '12.5', '1.5'),
            'Right'  => array(__('Right', 'zappoppop'),  '', '12.5', '1.5', '12.5', '12.5'),
            'Bottom' => array(__('Bottom', 'zappoppop'), '', '1.5', '12.5', '12.5', '12.5'),
            'Left'   => array(__('Left', 'zappoppop'),   '', '1.5', '1.5', '1.5', '12.5'),
        ),
    ),
    'r' => array(
        'master' => 'prop-border-radius',
        'prop'   => 'borderRadius',
        'max'    => 200,
        'cells'  => array(
            'Tl' => array(__('Top left', 'zappoppop'),     'M2 8V4a2 2 0 0 1 2-2h4'),
            'Tr' => array(__('Top right', 'zappoppop'),    'M6 2h4a2 2 0 0 1 2 2v4'),
            'Bl' => array(__('Bottom left', 'zappoppop'),  'M8 12H4a2 2 0 0 1-2-2V6'),
            'Br' => array(__('Bottom right', 'zappoppop'), 'M12 6v4a2 2 0 0 1-2 2H6'),
        ),
    ),
);
$zappoppop_bx_panel = function ($zappoppop_bx_key) use ($zappoppop_bx_groups) {
    $zappoppop_bx_g = $zappoppop_bx_groups[$zappoppop_bx_key];
    ?>
    <div class="zpp-bx-panel is-collapsed" id="zpp-bx-panel-<?php echo esc_attr($zappoppop_bx_key); ?>">
        <div class="zpp-sp-grid">
            <?php foreach ($zappoppop_bx_g['cells'] as $zappoppop_bx_side => $zappoppop_bx_c) : $zappoppop_bx_id = $zappoppop_bx_g['master'] . '-' . strtolower($zappoppop_bx_side); ?>
            <div class="zpp-sp-cell">
                <label for="<?php echo esc_attr($zappoppop_bx_id); ?>"><?php echo esc_html($zappoppop_bx_c[0]); ?></label>
                <span class="zpp-sp-box">
                    <svg class="zpp-sp-ico" viewBox="0 0 14 14" width="14" height="14" aria-hidden="true" focusable="false"><rect x="1.5" y="1.5" width="11" height="11" rx="1" fill="none" stroke="currentColor" stroke-width="1" stroke-dasharray="1.5 1.5" opacity=".55"></rect><?php if ('w' === $zappoppop_bx_key) : ?><line x1="<?php echo esc_attr($zappoppop_bx_c[2]); ?>" y1="<?php echo esc_attr($zappoppop_bx_c[3]); ?>" x2="<?php echo esc_attr($zappoppop_bx_c[4]); ?>" y2="<?php echo esc_attr($zappoppop_bx_c[5]); ?>" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line><?php else : ?><path d="<?php echo esc_attr($zappoppop_bx_c[1]); ?>" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><?php endif; ?></svg>
                    <input type="number" id="<?php echo esc_attr($zappoppop_bx_id); ?>" class="zappoppop-input zappoppop-gp-bind zpp-sp-in" data-prop="<?php echo esc_attr($zappoppop_bx_g['prop'] . $zappoppop_bx_side); ?>" min="0" max="<?php echo esc_attr($zappoppop_bx_g['max']); ?>" value="0" inputmode="numeric" />
                    <span class="zpp-sp-unit">PX</span>
                    <span class="zpp-sp-step"><button type="button" class="zpp-sp-up" tabindex="-1" title="<?php esc_attr_e('Increase', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Increase', 'zappoppop'); ?>"></button><button type="button" class="zpp-sp-down" tabindex="-1" title="<?php esc_attr_e('Decrease', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Decrease', 'zappoppop'); ?>"></button></span>
                </span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
};
?>
<div class="zappoppop-insp-section" id="insp-section-border" style="background:#0f172a; border:1px solid #1e293b; border-radius:8px; padding:12px; margin-bottom:12px;">
    <div class="zappoppop-sec-head"><span class="zappoppop-sec-title"><?php esc_html_e('Border & Radius', 'zappoppop'); ?></span><label class="zappoppop-insp-switch" title="<?php esc_attr_e('Enable / Disable', 'zappoppop'); ?>"><input type="checkbox" id="prop-border-enabled" data-toggle-prop="borderEnabled" checked><span class="zappoppop-insp-slider"></span></label></div><div class="zappoppop-sec-body">
    <div class="zpp-bx-grid" style="display:grid; grid-template-columns:1fr 1fr; gap:0 8px;">
        <div>
            <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('Width (px)', 'zappoppop'); ?></label>
            <span class="zpp-bx-row">
                <span class="zpp-ty-ctl"><input type="number" id="prop-border-width" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="borderWidth" min="0" max="40" value="0" style="font-size:12px;" /><span class="zpp-ty-unit">PX</span><span class="zpp-sp-step"><button type="button" class="zpp-sp-up" tabindex="-1" title="<?php esc_attr_e('Increase', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Increase', 'zappoppop'); ?>"></button><button type="button" class="zpp-sp-down" tabindex="-1" title="<?php esc_attr_e('Decrease', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Decrease', 'zappoppop'); ?>"></button></span></span>
                <button type="button" class="zpp-bx-toggle" data-zpp-bx-toggle="zpp-bx-panel-w" aria-expanded="false" aria-controls="zpp-bx-panel-w" title="<?php esc_attr_e('Set each side separately', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Set each side separately', 'zappoppop'); ?>"><svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false"><rect x="5" y="5" width="14" height="14" rx="2" fill="none" stroke="currentColor" stroke-width="2"></rect></svg></button>
            </span>
        </div>
        <div>
            <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('Style', 'zappoppop'); ?></label>
            <select id="prop-border-style" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="borderStyle" style="font-size:12px;">
                <option value="solid"><?php esc_html_e('Solid', 'zappoppop'); ?></option>
                <option value="dashed"><?php esc_html_e('Dashed', 'zappoppop'); ?></option>
                <option value="dotted"><?php esc_html_e('Dotted', 'zappoppop'); ?></option>
                <option value="double"><?php esc_html_e('Double', 'zappoppop'); ?></option>
                <option value="none"><?php esc_html_e('None', 'zappoppop'); ?></option>
            </select>
        </div>
    <?php $zappoppop_bx_panel('w'); ?>
        <div>
            <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('Radius (px)', 'zappoppop'); ?></label>
            <span class="zpp-bx-row">
                <span class="zpp-ty-ctl"><input type="number" id="prop-border-radius" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="borderRadius" min="0" max="200" value="0" style="font-size:12px;" /><span class="zpp-ty-unit">PX</span><span class="zpp-sp-step"><button type="button" class="zpp-sp-up" tabindex="-1" title="<?php esc_attr_e('Increase', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Increase', 'zappoppop'); ?>"></button><button type="button" class="zpp-sp-down" tabindex="-1" title="<?php esc_attr_e('Decrease', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Decrease', 'zappoppop'); ?>"></button></span></span>
                <button type="button" class="zpp-bx-toggle" data-zpp-bx-toggle="zpp-bx-panel-r" aria-expanded="false" aria-controls="zpp-bx-panel-r" title="<?php esc_attr_e('Set each corner separately', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Set each corner separately', 'zappoppop'); ?>"><svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false"><path d="M4 9V6a2 2 0 0 1 2-2h3M15 4h3a2 2 0 0 1 2 2v3M20 15v3a2 2 0 0 1-2 2h-3M9 20H6a2 2 0 0 1-2-2v-3" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg></button>
            </span>
        </div>
        <div>
            <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('Border Color', 'zappoppop'); ?></label>
            <input type="color" data-zpp-alpha="compact" id="prop-border-color" class="widefat zappoppop-gp-bind" data-prop="borderColor" value="#334155" style="height:34px; padding:2px; background:#090d16; border:1px solid #1e293b; border-radius:6px; cursor:pointer;" />
        </div>
    <?php $zappoppop_bx_panel('r'); ?>
    </div>
</div>
</div>

<!-- G. BOX SHADOW -->
<div class="zappoppop-insp-section" id="insp-section-shadow" style="background:#0f172a; border:1px solid #1e293b; border-radius:8px; padding:12px; margin-bottom:12px;">
    <div class="zappoppop-sec-head"><span class="zappoppop-sec-title"><?php esc_html_e('Box Shadow', 'zappoppop'); ?></span><label class="zappoppop-insp-switch" title="<?php esc_attr_e('Enable / Disable', 'zappoppop'); ?>"><input type="checkbox" id="prop-shadow-enabled" data-toggle-prop="shadowEnabled" checked><span class="zappoppop-insp-slider"></span></label></div><div class="zappoppop-sec-body">
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:8px;">
        <div>
            <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('Offset X (px)', 'zappoppop'); ?></label>
            <span class="zpp-ty-numwrap"><span class="zpp-ty-ctl"><input type="number" id="prop-shadow-x" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="shadowX" min="-50" max="50" value="0" style="font-size:12px;" /><span class="zpp-ty-unit">PX</span><span class="zpp-sp-step"><button type="button" class="zpp-sp-up" tabindex="-1" title="<?php esc_attr_e('Increase', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Increase', 'zappoppop'); ?>"></button><button type="button" class="zpp-sp-down" tabindex="-1" title="<?php esc_attr_e('Decrease', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Decrease', 'zappoppop'); ?>"></button></span></span><button type="button" class="zpp-sp-reset zpp-ty-reset" data-default="0" title="<?php esc_attr_e('Reset', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Reset', 'zappoppop'); ?>"><span class="dashicons dashicons-image-rotate" aria-hidden="true"></span></button></span>
        </div>
        <div>
            <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('Offset Y (px)', 'zappoppop'); ?></label>
            <span class="zpp-ty-numwrap"><span class="zpp-ty-ctl"><input type="number" id="prop-shadow-y" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="shadowY" min="-50" max="50" value="0" style="font-size:12px;" /><span class="zpp-ty-unit">PX</span><span class="zpp-sp-step"><button type="button" class="zpp-sp-up" tabindex="-1" title="<?php esc_attr_e('Increase', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Increase', 'zappoppop'); ?>"></button><button type="button" class="zpp-sp-down" tabindex="-1" title="<?php esc_attr_e('Decrease', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Decrease', 'zappoppop'); ?>"></button></span></span><button type="button" class="zpp-sp-reset zpp-ty-reset" data-default="0" title="<?php esc_attr_e('Reset', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Reset', 'zappoppop'); ?>"><span class="dashicons dashicons-image-rotate" aria-hidden="true"></span></button></span>
        </div>
    </div>
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
        <div>
            <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('Blur', 'zappoppop'); ?></label>
            <span class="zpp-ty-numwrap"><span class="zpp-ty-ctl"><input type="number" id="prop-shadow-blur" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="shadowBlur" min="0" max="100" value="0" style="font-size:12px;" /><span class="zpp-ty-unit">PX</span><span class="zpp-sp-step"><button type="button" class="zpp-sp-up" tabindex="-1" title="<?php esc_attr_e('Increase', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Increase', 'zappoppop'); ?>"></button><button type="button" class="zpp-sp-down" tabindex="-1" title="<?php esc_attr_e('Decrease', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Decrease', 'zappoppop'); ?>"></button></span></span><button type="button" class="zpp-sp-reset zpp-ty-reset" data-default="0" title="<?php esc_attr_e('Reset', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Reset', 'zappoppop'); ?>"><span class="dashicons dashicons-image-rotate" aria-hidden="true"></span></button></span>
        </div>
        <div>
            <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('Spread', 'zappoppop'); ?></label>
            <span class="zpp-ty-numwrap"><span class="zpp-ty-ctl"><input type="number" id="prop-shadow-spread" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="shadowSpread" min="-50" max="50" value="0" style="font-size:12px;" /><span class="zpp-ty-unit">PX</span><span class="zpp-sp-step"><button type="button" class="zpp-sp-up" tabindex="-1" title="<?php esc_attr_e('Increase', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Increase', 'zappoppop'); ?>"></button><button type="button" class="zpp-sp-down" tabindex="-1" title="<?php esc_attr_e('Decrease', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Decrease', 'zappoppop'); ?>"></button></span></span><button type="button" class="zpp-sp-reset zpp-ty-reset" data-default="0" title="<?php esc_attr_e('Reset', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Reset', 'zappoppop'); ?>"><span class="dashicons dashicons-image-rotate" aria-hidden="true"></span></button></span>
        </div>
        <div style="grid-column:1 / -1;">
            <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('Color', 'zappoppop'); ?></label>
            <input type="color" data-zpp-alpha="compact" id="prop-shadow-color" class="widefat zappoppop-gp-bind" data-prop="shadowColor" value="#000000" style="height:34px; padding:2px; background:#090d16; border:1px solid #1e293b; border-radius:6px; cursor:pointer;" />
        </div>
    </div>
</div>
</div>

<!-- H. BUTTON HOVER STATE (Buttons & Submit) -->
<div class="zappoppop-insp-section" id="prop-btn-hover-section" style="display:none; background:#0f172a; border:1px solid #1e293b; border-radius:8px; padding:12px; margin-bottom:12px;">
    <div style="font-size:10px; font-weight:800; color:#38bdf8; text-transform:uppercase; margin-bottom:8px;"><?php esc_html_e('Button Hover State', 'zappoppop'); ?></div>
    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:8px;">
        <div>
            <label style="display:block; font-size:10px; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('Hover Bg', 'zappoppop'); ?></label>
            <input type="color" data-zpp-alpha="compact" id="prop-elem-hover-bg" class="zappoppop-gp-bind" data-prop="hoverBgColor" value="#0284c7" style="width:100%; height:32px; border:1px solid #334155; border-radius:4px; background:#090d16; cursor:pointer;" />
        </div>
        <div>
            <label style="display:block; font-size:10px; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('Hover Text', 'zappoppop'); ?></label>
            <input type="color" data-zpp-alpha="compact" id="prop-elem-hover-color" class="zappoppop-gp-bind" data-prop="hoverTextColor" value="#ffffff" style="width:100%; height:32px; border:1px solid #334155; border-radius:4px; background:#090d16; cursor:pointer;" />
        </div>
        <div>
            <label style="display:block; font-size:10px; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('Hover Border', 'zappoppop'); ?></label>
            <input type="color" data-zpp-alpha="compact" id="prop-elem-hover-border" class="zappoppop-gp-bind" data-prop="hoverBorderColor" value="#0284c7" style="width:100%; height:32px; border:1px solid #334155; border-radius:4px; background:#090d16; cursor:pointer;" />
        </div>
    </div>
</div>

<!-- I. CUSTOM CSS CLASS & INLINE CSS STYLES REPEATER (+ Add a style) -->
<div class="zappoppop-insp-section" id="insp-section-custom-css" style="background:#0f172a; border:1px solid #1e293b; border-radius:8px; padding:12px;">
    <div style="font-size:10px; font-weight:800; color:#38bdf8; text-transform:uppercase; margin-bottom:8px;"><?php esc_html_e('Custom CSS & Inline Styles', 'zappoppop'); ?></div>
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:10px;">
        <div>
            <label for="prop-custom-class" style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('Custom CSS Class', 'zappoppop'); ?></label>
            <input type="text" id="prop-custom-class" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="customClass" placeholder="<?php esc_attr_e('e.g. my-custom-layer', 'zappoppop'); ?>" style="font-size:12px;" />
            <input type="hidden" id="prop-form-custom-class" class="zappoppop-gp-bind" data-prop="customClass" />
        </div>
        <div>
            <label for="prop-custom-id" style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('Custom HTML ID', 'zappoppop'); ?></label>
            <input type="text" id="prop-custom-id" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="customId" placeholder="<?php esc_attr_e('e.g. custom-layer-id', 'zappoppop'); ?>" style="font-size:12px;" />
        </div>
    </div>
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
        <span style="font-size:10px; font-weight:700; color:#94a3b8; text-transform:uppercase;"><?php esc_html_e('CSS Styles Repeater', 'zappoppop'); ?></span>
        <button type="button" id="zappoppop-btn-add-inline-style" class="button button-small" style="font-size:11px; font-weight:700;"><?php esc_html_e('+ Add a style', 'zappoppop'); ?></button>
    </div>
    <div id="zappoppop-gp-inline-styles-list" style="display:flex; flex-direction:column; gap:6px;"></div>
</div>

<?php
$zappoppop_fa_picker = __DIR__ . '/tab-style-icon-picker.php';
if ( file_exists( $zappoppop_fa_picker ) ) { include_once $zappoppop_fa_picker; }
?>
