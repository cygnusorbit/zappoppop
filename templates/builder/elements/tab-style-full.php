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
    <div style="background:rgba(56,189,248,0.12); border:1px solid rgba(56,189,248,0.35); border-radius:6px; padding:10px 12px; margin-bottom:12px; font-size:11px; color:#bae6fd; line-height:1.45;">
        <strong style="color:#38bdf8; display:block; margin-bottom:2px;"><?php esc_html_e('INPUT STYLING', 'zappoppop'); ?></strong>
        <?php esc_html_e('Base input styling is inherited from', 'zappoppop'); ?> <strong><?php esc_html_e('Popup Settings » Style » Inputs', 'zappoppop'); ?></strong><?php esc_html_e('. Configure per-layer alignment, left/right icons, and CSS overrides below.', 'zappoppop'); ?>
    </div>
    <div class="zappoppop-insp-card" style="background:#0f172a; border:1px solid #1e293b; border-radius:8px; padding:12px; margin-bottom:12px;">
        <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:6px; text-transform:uppercase;"><?php esc_html_e('TEXT ALIGNMENT', 'zappoppop'); ?></label>
        <div id="zappoppop-form-align-group" style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:6px;">
            <button type="button" class="button zappoppop-form-align-btn" data-align="left" style="font-size:11px; font-weight:700;"><?php esc_html_e('Left', 'zappoppop'); ?></button>
            <button type="button" class="button zappoppop-form-align-btn" data-align="center" style="font-size:11px; font-weight:700;"><?php esc_html_e('Center', 'zappoppop'); ?></button>
            <button type="button" class="button zappoppop-form-align-btn" data-align="right" style="font-size:11px; font-weight:700;"><?php esc_html_e('Right', 'zappoppop'); ?></button>
        </div>
    </div>
    <div id="zappoppop-form-icons-card" class="zappoppop-insp-card" style="background:#0f172a; border:1px solid #1e293b; border-radius:8px; padding:12px; margin-bottom:12px;">
        <div style="font-size:10px; font-weight:800; color:#38bdf8; text-transform:uppercase; margin-bottom:8px;"><?php esc_html_e('INPUT ICONS & OFFSETS', 'zappoppop'); ?></div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
            <div>
                <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('LEFT ICON', 'zappoppop'); ?></label>
                <div style="display:flex; gap:4px; align-items:center;">
                    <button type="button" class="button zappoppop-fa-pick-btn" data-target="#prop-form-icon-left" title="<?php esc_attr_e('Choose icon', 'zappoppop'); ?><?php esc_html_e('" aria-label="', 'zappoppop'); ?><?php esc_attr_e('Choose left icon', 'zappoppop'); ?>" style="min-width:36px; height:30px; padding:0; display:inline-flex; align-items:center; justify-content:center;"><i class="fas fa-icons" aria-hidden="true"></i></button>
                    <button type="button" class="button zappoppop-fa-clear-btn" data-target="#prop-form-icon-left" title="<?php esc_attr_e('Clear icon', 'zappoppop'); ?><?php esc_html_e('" aria-label="', 'zappoppop'); ?><?php esc_attr_e('Clear left icon', 'zappoppop'); ?>" style="min-width:30px; height:30px; padding:0;">&times;</button>
                    <input type="text" id="prop-form-icon-left" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="iconLeft" placeholder="fas fa-envelope" style="font-size:11px; min-width:0;" />
                </div>
                <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin:8px 0 4px;"><?php esc_html_e('OFFSET (PX)', 'zappoppop'); ?></label>
                <input type="number" id="prop-form-icon-left-offset" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="iconLeftOffset" min="0" max="40" value="10" style="font-size:12px;" />
                <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin:8px 0 4px;"><?php esc_html_e('ICON COLOR', 'zappoppop'); ?></label>
                <div style="display:flex; gap:4px; align-items:center;">
                    <input type="color" data-zpp-alpha="compact" id="prop-form-icon-left-color" class="zappoppop-gp-bind" data-prop="iconLeftColor" value="#64748b" style="flex:1; height:30px; padding:2px; background:#090d16; border:1px solid #1e293b; border-radius:4px; cursor:pointer; min-width:0;" />
                    <button type="button" class="button zappoppop-icon-color-reset" data-target="#prop-form-icon-left-color" title="<?php esc_attr_e('Reset color', 'zappoppop'); ?><?php esc_html_e('" aria-label="', 'zappoppop'); ?><?php esc_attr_e('Reset left icon color', 'zappoppop'); ?>" style="height:30px; min-width:30px; padding:0; font-size:11px;">&#8635;</button>
                </div>
            </div>
            <div>
                <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('RIGHT ICON', 'zappoppop'); ?></label>
                <div style="display:flex; gap:4px; align-items:center;">
                    <button type="button" class="button zappoppop-fa-pick-btn" data-target="#prop-form-icon-right" title="<?php esc_attr_e('Choose icon', 'zappoppop'); ?><?php esc_html_e('" aria-label="', 'zappoppop'); ?><?php esc_attr_e('Choose right icon', 'zappoppop'); ?>" style="min-width:36px; height:30px; padding:0; display:inline-flex; align-items:center; justify-content:center;"><i class="fas fa-icons" aria-hidden="true"></i></button>
                    <button type="button" class="button zappoppop-fa-clear-btn" data-target="#prop-form-icon-right" title="<?php esc_attr_e('Clear icon', 'zappoppop'); ?><?php esc_html_e('" aria-label="', 'zappoppop'); ?><?php esc_attr_e('Clear right icon', 'zappoppop'); ?>" style="min-width:30px; height:30px; padding:0;">&times;</button>
                    <input type="text" id="prop-form-icon-right" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="iconRight" placeholder="fas fa-check" style="font-size:11px; min-width:0;" />
                </div>
                <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin:8px 0 4px;"><?php esc_html_e('OFFSET (PX)', 'zappoppop'); ?></label>
                <input type="number" id="prop-form-icon-right-offset" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="iconRightOffset" min="0" max="40" value="10" style="font-size:12px;" />
                <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin:8px 0 4px;"><?php esc_html_e('ICON COLOR', 'zappoppop'); ?></label>
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
    <div style="font-size:10px; font-weight:800; color:#38bdf8; text-transform:uppercase; margin-bottom:8px;"><?php esc_html_e('OPACITY & VISIBILITY', 'zappoppop'); ?></div>
    <div style="display:flex; align-items:center; gap:10px;">
        <input type="range" id="prop-opacity" class="zappoppop-gp-bind" data-prop="opacity" min="0" max="100" value="100" style="flex:1;" />
        <span id="prop-opacity-val" style="font-size:12px; font-weight:700; color:#38bdf8; min-width:38px; text-align:right;">100%</span>
    </div>
</div>

<!-- C. BACKGROUND COLOR (layer box) -->
<div class="zappoppop-insp-section" id="insp-section-bg-color" style="background:#0f172a; border:1px solid #1e293b; border-radius:8px; padding:12px; margin-bottom:12px;">
    <div style="font-size:10px; font-weight:800; color:#38bdf8; text-transform:uppercase; margin-bottom:8px;"><?php esc_html_e('BACKGROUND COLOR', 'zappoppop'); ?></div>
    <div style="display:flex; align-items:center; gap:10px;">
        <input type="color" id="prop-bg-color" class="widefat zappoppop-gp-bind" data-zpp-alpha="1" data-prop="bgColor" value="#3b82f6" style="height:34px; padding:2px; background:#090d16; border:1px solid #1e293b; border-radius:6px; cursor:pointer; flex:1;" />
        <label style="display:inline-flex; align-items:center; gap:5px; font-size:11px; color:#cbd5e1; cursor:pointer; white-space:nowrap;">
            <input type="checkbox" id="prop-bg-transparent" class="zappoppop-gp-bind" data-prop="transparentFill" value="1" /> <?php esc_html_e('Transparent', 'zappoppop'); ?>
        </label>
    </div>
</div>

<!-- D. TYPOGRAPHY & TEXT SPACING -->
<div class="zappoppop-insp-section" id="insp-section-typography" style="background:#0f172a; border:1px solid #1e293b; border-radius:8px; padding:12px; margin-bottom:12px;">
    <div style="font-size:10px; font-weight:800; color:#38bdf8; text-transform:uppercase; margin-bottom:8px;"><?php esc_html_e('TYPOGRAPHY & SPACING', 'zappoppop'); ?></div>
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:8px;">
        <div>
            <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('FONT SIZE (PX)', 'zappoppop'); ?></label>
            <input type="number" id="prop-font-size" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="fontSize" min="8" max="120" value="16" style="font-size:12px;" />
        </div>
        <div>
            <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('FONT WEIGHT', 'zappoppop'); ?></label>
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
            <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('LINE HEIGHT', 'zappoppop'); ?></label>
            <input type="number" id="prop-line-height" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="lineHeight" step="0.1" min="0.8" max="3" value="1.4" style="font-size:12px;" />
        </div>
        <div>
            <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('LETTER SPACING (PX)', 'zappoppop'); ?></label>
            <input type="number" id="prop-letter-spacing" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="letterSpacing" step="0.5" min="-5" max="20" value="0" style="font-size:12px;" />
        </div>
    </div>
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
        <div>
            <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('TEXT COLOR', 'zappoppop'); ?></label>
            <input type="color" id="prop-color" class="widefat zappoppop-gp-bind" data-zpp-alpha="1" data-prop="color" value="#ffffff" style="height:34px; padding:2px; background:#090d16; border:1px solid #1e293b; border-radius:6px; cursor:pointer;" />
        </div>
        <div>
            <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('FOREGROUND COLOR', 'zappoppop'); ?></label>
            <div style="display:flex; gap:4px; align-items:center;">
                <input type="color" data-zpp-alpha="compact" id="prop-fg-color" class="widefat zappoppop-gp-bind" data-prop="fgColor" value="#64748b" title="<?php esc_attr_e('Icons & accents', 'zappoppop'); ?>" style="flex:1; height:34px; padding:2px; background:#090d16; border:1px solid #1e293b; border-radius:6px; cursor:pointer; min-width:0;" />
                <button type="button" id="prop-fg-color-reset" class="button" title="<?php esc_attr_e('Reset foreground color', 'zappoppop'); ?><?php esc_html_e('" aria-label="', 'zappoppop'); ?><?php esc_attr_e('Reset foreground color', 'zappoppop'); ?>" style="height:34px; min-width:30px; padding:0; font-size:11px;">&#8635;</button>
            </div>
        </div>
    </div>
</div>

<!-- E. 4-WAY DIRECTIONAL PADDING -->
<div class="zappoppop-insp-section" id="insp-section-padding" style="background:#0f172a; border:1px solid #1e293b; border-radius:8px; padding:12px; margin-bottom:12px;">
    <div style="font-size:10px; font-weight:800; color:#38bdf8; text-transform:uppercase; margin-bottom:8px;"><?php esc_html_e('PADDING (TOP / RIGHT / BOTTOM / LEFT PX)', 'zappoppop'); ?></div>
    <div style="display:grid; grid-template-columns:1fr 1fr 1fr 1fr; gap:6px;">
        <div>
            <label style="display:block; font-size:9px; color:#94a3b8; text-align:center;"><?php esc_html_e('TOP', 'zappoppop'); ?></label>
            <input type="number" id="prop-pad-top" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="paddingTop" min="0" max="200" value="0" style="font-size:11px; text-align:center;" />
        </div>
        <div>
            <label style="display:block; font-size:9px; color:#94a3b8; text-align:center;"><?php esc_html_e('RIGHT', 'zappoppop'); ?></label>
            <input type="number" id="prop-pad-right" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="paddingRight" min="0" max="200" value="0" style="font-size:11px; text-align:center;" />
        </div>
        <div>
            <label style="display:block; font-size:9px; color:#94a3b8; text-align:center;"><?php esc_html_e('BOTTOM', 'zappoppop'); ?></label>
            <input type="number" id="prop-pad-bottom" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="paddingBottom" min="0" max="200" value="0" style="font-size:11px; text-align:center;" />
        </div>
        <div>
            <label style="display:block; font-size:9px; color:#94a3b8; text-align:center;"><?php esc_html_e('LEFT', 'zappoppop'); ?></label>
            <input type="number" id="prop-pad-left" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="paddingLeft" min="0" max="200" value="0" style="font-size:11px; text-align:center;" />
        </div>
    </div>
</div>

<!-- F. BORDER & CORNER RADIUS -->
<div class="zappoppop-insp-section" id="insp-section-border" style="background:#0f172a; border:1px solid #1e293b; border-radius:8px; padding:12px; margin-bottom:12px;">
    <div class="zappoppop-sec-head"><span class="zappoppop-sec-title"><?php esc_html_e('BORDER & CORNER RADIUS', 'zappoppop'); ?></span><label class="zappoppop-insp-switch" title="<?php esc_attr_e('Enable / Disable', 'zappoppop'); ?>"><input type="checkbox" id="prop-border-enabled" data-toggle-prop="borderEnabled" checked><span class="zappoppop-insp-slider"></span></label></div><div class="zappoppop-sec-body">
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:8px;">
        <div>
            <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('WIDTH (PX)', 'zappoppop'); ?></label>
            <input type="number" id="prop-border-width" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="borderWidth" min="0" max="40" value="0" style="font-size:12px;" />
        </div>
        <div>
            <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('STYLE', 'zappoppop'); ?></label>
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
            <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('BORDER COLOR', 'zappoppop'); ?></label>
            <input type="color" data-zpp-alpha="compact" id="prop-border-color" class="widefat zappoppop-gp-bind" data-prop="borderColor" value="#334155" style="height:34px; padding:2px; background:#090d16; border:1px solid #1e293b; border-radius:6px; cursor:pointer;" />
        </div>
        <div>
            <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('RADIUS (PX)', 'zappoppop'); ?></label>
            <input type="number" id="prop-border-radius" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="borderRadius" min="0" max="200" value="0" style="font-size:12px;" />
        </div>
    </div>
</div>
</div>

<!-- G. BOX SHADOW -->
<div class="zappoppop-insp-section" id="insp-section-shadow" style="background:#0f172a; border:1px solid #1e293b; border-radius:8px; padding:12px; margin-bottom:12px;">
    <div class="zappoppop-sec-head"><span class="zappoppop-sec-title"><?php esc_html_e('BOX SHADOW', 'zappoppop'); ?></span><label class="zappoppop-insp-switch" title="<?php esc_attr_e('Enable / Disable', 'zappoppop'); ?>"><input type="checkbox" id="prop-shadow-enabled" data-toggle-prop="shadowEnabled" checked><span class="zappoppop-insp-slider"></span></label></div><div class="zappoppop-sec-body">
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:8px;">
        <div>
            <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('OFFSET X (PX)', 'zappoppop'); ?></label>
            <input type="number" id="prop-shadow-x" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="shadowX" min="-50" max="50" value="0" style="font-size:12px;" />
        </div>
        <div>
            <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('OFFSET Y (PX)', 'zappoppop'); ?></label>
            <input type="number" id="prop-shadow-y" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="shadowY" min="-50" max="50" value="0" style="font-size:12px;" />
        </div>
    </div>
    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:6px;">
        <div>
            <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('BLUR', 'zappoppop'); ?></label>
            <input type="number" id="prop-shadow-blur" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="shadowBlur" min="0" max="100" value="0" style="font-size:12px;" />
        </div>
        <div>
            <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('SPREAD', 'zappoppop'); ?></label>
            <input type="number" id="prop-shadow-spread" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="shadowSpread" min="-50" max="50" value="0" style="font-size:12px;" />
        </div>
        <div>
            <label style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('COLOR', 'zappoppop'); ?></label>
            <input type="color" data-zpp-alpha="compact" id="prop-shadow-color" class="widefat zappoppop-gp-bind" data-prop="shadowColor" value="#000000" style="height:34px; padding:2px; background:#090d16; border:1px solid #1e293b; border-radius:6px; cursor:pointer;" />
        </div>
    </div>
</div>
</div>

<!-- H. BUTTON HOVER STATE (Buttons & Submit) -->
<div class="zappoppop-insp-section" id="prop-btn-hover-section" style="display:none; background:#0f172a; border:1px solid #1e293b; border-radius:8px; padding:12px; margin-bottom:12px;">
    <div style="font-size:10px; font-weight:800; color:#38bdf8; text-transform:uppercase; margin-bottom:8px;"><?php esc_html_e('BUTTON HOVER STATE', 'zappoppop'); ?></div>
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
    <div style="font-size:10px; font-weight:800; color:#38bdf8; text-transform:uppercase; margin-bottom:8px;"><?php esc_html_e('CUSTOM CSS & INLINE STYLES', 'zappoppop'); ?></div>
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:10px;">
        <div>
            <label for="prop-custom-class" style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('CUSTOM CSS CLASS', 'zappoppop'); ?></label>
            <input type="text" id="prop-custom-class" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="customClass" placeholder="<?php esc_attr_e('e.g. my-custom-layer', 'zappoppop'); ?>" style="font-size:12px;" />
            <input type="hidden" id="prop-form-custom-class" class="zappoppop-gp-bind" data-prop="customClass" />
        </div>
        <div>
            <label for="prop-custom-id" style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('CUSTOM HTML ID', 'zappoppop'); ?></label>
            <input type="text" id="prop-custom-id" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="customId" placeholder="<?php esc_attr_e('e.g. custom-layer-id', 'zappoppop'); ?>" style="font-size:12px;" />
        </div>
    </div>
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
        <span style="font-size:10px; font-weight:700; color:#94a3b8; text-transform:uppercase;"><?php esc_html_e('CSS STYLES REPEATER', 'zappoppop'); ?></span>
        <button type="button" id="zappoppop-btn-add-inline-style" class="button button-small" style="font-size:11px; font-weight:700;"><?php esc_html_e('+ Add a style', 'zappoppop'); ?></button>
    </div>
    <div id="zappoppop-gp-inline-styles-list" style="display:flex; flex-direction:column; gap:6px;"></div>
</div>

<?php
$zappoppop_fa_picker = __DIR__ . '/tab-style-icon-picker.php';
if ( file_exists( $zappoppop_fa_picker ) ) { include_once $zappoppop_fa_picker; }
?>
