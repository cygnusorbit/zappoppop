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
    <div class="zappoppop-insp-card" style="background:#0f172a; border:1px solid #1e293b; border-radius:8px; padding:12px; margin-bottom:12px;">
        <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:6px; text-transform:uppercase;"><?php esc_html_e('Text Alignment', 'zappoppop'); ?></label>
        <div id="zappoppop-form-align-group" style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:6px;">
            <button type="button" class="button zappoppop-form-align-btn" data-align="left" style="font-size:11px; font-weight:700;"><?php esc_html_e('Left', 'zappoppop'); ?></button>
            <button type="button" class="button zappoppop-form-align-btn" data-align="center" style="font-size:11px; font-weight:700;"><?php esc_html_e('Center', 'zappoppop'); ?></button>
            <button type="button" class="button zappoppop-form-align-btn" data-align="right" style="font-size:11px; font-weight:700;"><?php esc_html_e('Right', 'zappoppop'); ?></button>
        </div>
    </div>
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

<!-- B. OPACITY & VISIBILITY -->
<div class="zappoppop-insp-section" id="insp-section-opacity" style="background:#0f172a; border:1px solid #1e293b; border-radius:8px; padding:12px; margin-bottom:12px;">
    <div style="font-size:10px; font-weight:800; color:#38bdf8; text-transform:uppercase; margin-bottom:8px;"><?php esc_html_e('Opacity & Visibility', 'zappoppop'); ?></div>
    <div style="display:flex; align-items:center; gap:10px;">
        <input type="range" id="prop-opacity" class="zappoppop-gp-bind" data-prop="opacity" min="0" max="100" value="100" style="flex:1;" />
        <span id="prop-opacity-val" style="font-size:12px; font-weight:700; color:#38bdf8; min-width:38px; text-align:right;">100%</span>
    </div>
</div>

<!-- C. BACKGROUND COLOR (layer box) -->
<div class="zappoppop-insp-section" id="insp-section-bg-color" style="background:#0f172a; border:1px solid #1e293b; border-radius:8px; padding:12px; margin-bottom:12px;">
    <div style="font-size:10px; font-weight:800; color:#38bdf8; text-transform:uppercase; margin-bottom:8px;"><?php esc_html_e('Background Color', 'zappoppop'); ?></div>
    <div style="display:flex; align-items:center; gap:10px;">
        <input type="color" id="prop-bg-color" class="widefat zappoppop-gp-bind" data-zpp-alpha="1" data-prop="bgColor" value="#3b82f6" style="height:34px; padding:2px; background:#090d16; border:1px solid #1e293b; border-radius:6px; cursor:pointer; flex:1;" />
        <label style="display:inline-flex; align-items:center; gap:5px; font-size:11px; color:#cbd5e1; cursor:pointer; white-space:nowrap;">
            <input type="checkbox" id="prop-bg-transparent" class="zappoppop-gp-bind" data-prop="transparentFill" value="1" /> <?php esc_html_e('Transparent', 'zappoppop'); ?>
        </label>
    </div>
</div>

<!-- D. TYPOGRAPHY & TEXT SPACING -->
<div class="zappoppop-insp-section" id="insp-section-typography" style="background:#0f172a; border:1px solid #1e293b; border-radius:8px; padding:12px; margin-bottom:12px;">
    <div style="font-size:10px; font-weight:800; color:#38bdf8; text-transform:uppercase; margin-bottom:8px;"><?php esc_html_e('Typography & Spacing', 'zappoppop'); ?></div>
    <div style="margin-bottom:8px;">
        <label for="prop-font-family" style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('Font Family', 'zappoppop'); ?></label>
        <select id="prop-font-family" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="fontFamily" style="font-size:12px;">
            <?php echo wp_kses(ZapPopPop_Font_Catalog::options_html(), array('optgroup' => array('label' => true), 'option' => array('value' => true, 'data-weights' => true))); ?>
        </select>
    </div>
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:8px;">
        <div>
            <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('Font Size (px)', 'zappoppop'); ?></label>
            <input type="number" id="prop-font-size" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="fontSize" min="8" max="120" value="16" style="font-size:12px;" />
        </div>
        <div>
            <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('Font Weight', 'zappoppop'); ?></label>
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
    </div>
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:8px;">
        <div>
            <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('Line Height', 'zappoppop'); ?></label>
            <input type="number" id="prop-line-height" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="lineHeight" step="0.1" min="0.8" max="3" value="1.4" style="font-size:12px;" />
        </div>
        <div>
            <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('Letter Spacing (px)', 'zappoppop'); ?></label>
            <input type="number" id="prop-letter-spacing" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="letterSpacing" step="0.5" min="-5" max="20" value="0" style="font-size:12px;" />
        </div>
    </div>
    <!-- ZPP_WORD_SPACING -->
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:8px;">
        <div>
            <label for="prop-word-spacing" style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('Word Spacing (px)', 'zappoppop'); ?></label>
            <input type="number" id="prop-word-spacing" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="wordSpacing" step="0.5" min="-10" max="50" value="0" style="font-size:12px;" />
        </div>
        <div><!-- ZPP_TEXT_TRANSFORM -->
            <label for="prop-text-transform" style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('Text Transform', 'zappoppop'); ?></label>
            <select id="prop-text-transform" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="textTransform" style="font-size:12px;">
                <option value="none"><?php esc_html_e('None', 'zappoppop'); ?></option>
                <option value="uppercase"><?php esc_html_e('UPPERCASE', 'zappoppop'); ?></option>
                <option value="lowercase"><?php esc_html_e('lowercase', 'zappoppop'); ?></option>
                <option value="capitalize"><?php esc_html_e('Capitalize', 'zappoppop'); ?></option>
            </select>
        </div>
    </div>
    <div style="display:grid; grid-template-columns:1fr; gap:8px;">
        <div>
            <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('Text Color', 'zappoppop'); ?></label>
            <input type="color" id="prop-color" class="widefat zappoppop-gp-bind" data-zpp-alpha="1" data-prop="color" value="#ffffff" style="height:34px; padding:2px; background:#090d16; border:1px solid #1e293b; border-radius:6px; cursor:pointer;" />
        </div>
    </div>
    <!-- ZPP_TEXT_SHADOW -->
    <div id="insp-row-text-shadow" style="margin-top:12px;">
        <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('Text Shadow', 'zappoppop'); ?></label>
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
                <input type="number" id="prop-tsh-x" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="textShadowX" min="-50" max="50" step="1" value="0" style="font-size:12px;" />
            </div>
            <div>
                <label for="prop-tsh-y" style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('Vertical (px)', 'zappoppop'); ?></label>
                <input type="number" id="prop-tsh-y" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="textShadowY" min="-50" max="50" step="1" value="0" style="font-size:12px;" />
            </div>
            <div>
                <label for="prop-tsh-blur" style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('Blur (px)', 'zappoppop'); ?></label>
                <input type="number" id="prop-tsh-blur" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="textShadowBlur" min="0" max="100" step="1" value="0" style="font-size:12px;" />
            </div>
        </div>
    </div>
</div>

<!-- E. 4-WAY DIRECTIONAL PADDING -->
<div class="zappoppop-insp-section" id="insp-section-padding" style="background:#0f172a; border:1px solid #1e293b; border-radius:8px; padding:12px; margin-bottom:12px;">
    <div style="font-size:10px; font-weight:800; color:#38bdf8; text-transform:uppercase; margin-bottom:8px;"><?php esc_html_e('Padding (Top / Right / Bottom / Left px)', 'zappoppop'); ?></div>
    <div style="display:grid; grid-template-columns:1fr 1fr 1fr 1fr; gap:6px;">
        <div>
            <label style="display:block; font-size:9px; color:#94a3b8; text-align:center;"><?php esc_html_e('Top', 'zappoppop'); ?></label>
            <input type="number" id="prop-pad-top" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="paddingTop" min="0" max="200" value="0" style="font-size:11px; text-align:center;" />
        </div>
        <div>
            <label style="display:block; font-size:9px; color:#94a3b8; text-align:center;"><?php esc_html_e('Right', 'zappoppop'); ?></label>
            <input type="number" id="prop-pad-right" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="paddingRight" min="0" max="200" value="0" style="font-size:11px; text-align:center;" />
        </div>
        <div>
            <label style="display:block; font-size:9px; color:#94a3b8; text-align:center;"><?php esc_html_e('Bottom', 'zappoppop'); ?></label>
            <input type="number" id="prop-pad-bottom" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="paddingBottom" min="0" max="200" value="0" style="font-size:11px; text-align:center;" />
        </div>
        <div>
            <label style="display:block; font-size:9px; color:#94a3b8; text-align:center;"><?php esc_html_e('Left', 'zappoppop'); ?></label>
            <input type="number" id="prop-pad-left" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="paddingLeft" min="0" max="200" value="0" style="font-size:11px; text-align:center;" />
        </div>
    </div>
</div>

<!-- F. BORDER & CORNER RADIUS -->
<div class="zappoppop-insp-section" id="insp-section-border" style="background:#0f172a; border:1px solid #1e293b; border-radius:8px; padding:12px; margin-bottom:12px;">
    <div class="zappoppop-sec-head"><span class="zappoppop-sec-title"><?php esc_html_e('Border & Corner Radius', 'zappoppop'); ?></span><label class="zappoppop-insp-switch" title="<?php esc_attr_e('Enable / Disable', 'zappoppop'); ?>"><input type="checkbox" id="prop-border-enabled" data-toggle-prop="borderEnabled" checked><span class="zappoppop-insp-slider"></span></label></div><div class="zappoppop-sec-body">
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:8px;">
        <div>
            <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('Width (px)', 'zappoppop'); ?></label>
            <input type="number" id="prop-border-width" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="borderWidth" min="0" max="40" value="0" style="font-size:12px;" />
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
    </div>
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
        <div>
            <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('Border Color', 'zappoppop'); ?></label>
            <input type="color" data-zpp-alpha="compact" id="prop-border-color" class="widefat zappoppop-gp-bind" data-prop="borderColor" value="#334155" style="height:34px; padding:2px; background:#090d16; border:1px solid #1e293b; border-radius:6px; cursor:pointer;" />
        </div>
        <div>
            <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('Radius (px)', 'zappoppop'); ?></label>
            <input type="number" id="prop-border-radius" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="borderRadius" min="0" max="200" value="0" style="font-size:12px;" />
        </div>
    </div>
</div>
</div>

<!-- G. BOX SHADOW -->
<div class="zappoppop-insp-section" id="insp-section-shadow" style="background:#0f172a; border:1px solid #1e293b; border-radius:8px; padding:12px; margin-bottom:12px;">
    <div class="zappoppop-sec-head"><span class="zappoppop-sec-title"><?php esc_html_e('Box Shadow', 'zappoppop'); ?></span><label class="zappoppop-insp-switch" title="<?php esc_attr_e('Enable / Disable', 'zappoppop'); ?>"><input type="checkbox" id="prop-shadow-enabled" data-toggle-prop="shadowEnabled" checked><span class="zappoppop-insp-slider"></span></label></div><div class="zappoppop-sec-body">
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:8px;">
        <div>
            <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('Offset X (px)', 'zappoppop'); ?></label>
            <input type="number" id="prop-shadow-x" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="shadowX" min="-50" max="50" value="0" style="font-size:12px;" />
        </div>
        <div>
            <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('Offset Y (px)', 'zappoppop'); ?></label>
            <input type="number" id="prop-shadow-y" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="shadowY" min="-50" max="50" value="0" style="font-size:12px;" />
        </div>
    </div>
    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:6px;">
        <div>
            <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('Blur', 'zappoppop'); ?></label>
            <input type="number" id="prop-shadow-blur" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="shadowBlur" min="0" max="100" value="0" style="font-size:12px;" />
        </div>
        <div>
            <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('Spread', 'zappoppop'); ?></label>
            <input type="number" id="prop-shadow-spread" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="shadowSpread" min="-50" max="50" value="0" style="font-size:12px;" />
        </div>
        <div>
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
