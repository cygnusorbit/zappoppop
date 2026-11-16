<?php
if (!defined('ABSPATH')) exit;
/**
 * Decomposed Module: Media & Action Family Contextual Style Controls
 * Scoped per element via ZapPopPopBuilderInspector (Elements 1-9 & 26)
 */
?>
<div id="zappoppop-decomposed-style-media-Extras" class="zappoppop-decomposed-module" style="display:none; margin-top:10px;">
    <!-- Contextual Hover State Card (Strictly Elements 9 Button & 26 Submit) -->
    <div class="zappoppop-insp-card zappoppop-media-style-scope" data-style-elements="button,link,link_btn,step,step_btn,submit,submit_btn" style="display:none; background:#1e293b; border:1px solid #334155; border-radius:6px; padding:12px; margin-bottom:12px;">
        <div style="font-size:11px; font-weight:700; text-transform:uppercase; color:#38bdf8; margin-bottom:10px; letter-spacing:0.5px;">
            <span class="dashicons dashicons-admin-customizer" style="font-size:14px; width:14px; height:14px; margin-right:4px;"></span>
            <?php esc_html_e('Button Hover State', 'zappoppop'); ?>
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:8px;">
            <div>
                <label style="display:block; font-size:11px; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('Hover Bg', 'zappoppop'); ?></label>
                <input type="color" id="prop-elem-hover-bg" class="zappoppop-gp-bind" data-prop="hoverBgColor" data-elem-prop="hoverBgColor" value="#0284c7" style="width:100%; height:30px; border:1px solid #475569; border-radius:4px; background:#0f172a; cursor:pointer;">
            </div>
            <div>
                <label style="display:block; font-size:11px; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('Hover Text', 'zappoppop'); ?></label>
                <input type="color" id="prop-elem-hover-color" class="zappoppop-gp-bind" data-prop="hoverTextColor" data-elem-prop="hoverTextColor" value="#ffffff" style="width:100%; height:30px; border:1px solid #475569; border-radius:4px; background:#0f172a; cursor:pointer;">
            </div>
            <div>
                <label style="display:block; font-size:11px; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('Hover Border', 'zappoppop'); ?></label>
                <input type="color" id="prop-elem-hover-border" class="zappoppop-gp-bind" data-prop="hoverBorderColor" data-elem-prop="hoverBorderColor" value="#0284c7" style="width:100%; height:30px; border:1px solid #475569; border-radius:4px; background:#0f172a; cursor:pointer;">
            </div>
        </div>
    </div>

    <!-- Contextual Close / Icon Hover Color (Strictly Elements 6 Close & 7 Icon) -->
    <div class="zappoppop-insp-card zappoppop-media-style-scope" data-style-elements="close,close_icon,icon,fontawesome" style="display:none; background:#1e293b; border:1px solid #334155; border-radius:6px; padding:12px; margin-bottom:12px;">
        <div style="font-size:11px; font-weight:700; text-transform:uppercase; color:#38bdf8; margin-bottom:8px;"><?php esc_html_e('Glyph Hover & Badge Style', 'zappoppop'); ?></div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
            <div>
                <label style="display:block; font-size:11px; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('Hover Glyph Color', 'zappoppop'); ?></label>
                <input type="color" id="prop-glyph-hover-color" class="zappoppop-gp-bind" data-prop="hoverColor" data-elem-prop="hoverColor" value="#ef4444" style="width:100%; height:30px; border:1px solid #475569; border-radius:4px; background:#0f172a; cursor:pointer;">
            </div>
            <div>
                <label style="display:block; font-size:11px; color:#94a3b8; margin-bottom:4px;"><?php esc_html_e('Rotation (0°–360°)', 'zappoppop'); ?></label>
                <input type="number" id="prop-glyph-rotate" class="zappoppop-gp-bind" data-prop="rotate" data-elem-prop="shapeRotate" min="0" max="360" placeholder="0" style="width:100%; background:#0f172a; border:1px solid #475569; color:#f8fafc; border-radius:4px; padding:5px 8px; font-size:12px;">
            </div>
        </div>
    </div>
</div>
