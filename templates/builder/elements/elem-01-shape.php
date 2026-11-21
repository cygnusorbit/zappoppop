<?php
if (!defined('ABSPATH')) exit;
?>
<!-- DECOMPOSED MODULE 01: RECTANGLE / SQUARE / VECTOR SHAPE (shape) -->
<div class="element-panel zappoppop-panel-specific zappoppop-shape-panel" id="panel-elem-shape" data-elem-family="media" style="display:none;background:#0f172a;border:1px solid #1e293b;border-radius:8px;padding:12px;margin-bottom:14px;">
    <div class="zappoppop-shape-head">
        <div style="font-size:10px;font-weight:800;color:#38bdf8;text-transform:uppercase;letter-spacing:0.5px;"><?php esc_html_e('Shape / Vector Geometry', 'zappoppop'); ?></div>
    </div>
    <div class="zappoppop-shape-field">
        <label class="zappoppop-shape-label" for="prop-shape-type"><?php esc_html_e('SVG Shape Preset', 'zappoppop'); ?></label>
        <select id="prop-shape-type" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="shapePreset" style="font-size:12px;">
            <option value="square" selected><?php esc_html_e('Square', 'zappoppop'); ?></option>
            <option value="rectangle"><?php esc_html_e('Rectangle', 'zappoppop'); ?></option>
            <option value="rounded"><?php esc_html_e('Rounded Box', 'zappoppop'); ?></option>
            <option value="circle"><?php esc_html_e('Circle / Oval', 'zappoppop'); ?></option>
            <option value="pill"><?php esc_html_e('Pill / Stadium', 'zappoppop'); ?></option>
            <option value="triangle"><?php esc_html_e('Triangle', 'zappoppop'); ?></option>
            <option value="diamond"><?php esc_html_e('Diamond / Rhombus', 'zappoppop'); ?></option>
            <option value="pentagon"><?php esc_html_e('Pentagon', 'zappoppop'); ?></option>
            <option value="hexagon"><?php esc_html_e('Hexagon', 'zappoppop'); ?></option>
            <option value="star"><?php esc_html_e('5-Point Star', 'zappoppop'); ?></option>
            <option value="heart"><?php esc_html_e('Heart', 'zappoppop'); ?></option>
            <option value="badge"><?php esc_html_e('Seal / Burst Badge', 'zappoppop'); ?></option>
        </select>
    </div>
    <div class="zappoppop-shape-row">
        <div>
            <label class="zappoppop-shape-label" for="prop-size-width"><?php esc_html_e('Width (px)', 'zappoppop'); ?></label>
            <input type="number" id="prop-size-width" class="widefat zappoppop-input" min="16" step="1" value="120" style="font-size:12px;">
        </div>
        <div>
            <label class="zappoppop-shape-label" for="prop-size-height"><?php esc_html_e('Height (px)', 'zappoppop'); ?></label>
            <input type="number" id="prop-size-height" class="widefat zappoppop-input" min="16" step="1" value="120" style="font-size:12px;">
        </div>
    </div>
    <div class="zappoppop-shape-field">
        <label class="zappoppop-shape-label" for="prop-shape-fill"><?php esc_html_e('Fill Color', 'zappoppop'); ?></label>
        <div class="zappoppop-shape-color" data-zpp-clear-for="prop-shape-transparent">
            <input type="color" data-zpp-alpha="1" id="prop-shape-fill" class="zappoppop-gp-bind" data-prop="shapeFill" value="#3b82f6">
            <label class="zappoppop-shape-clear" title="<?php esc_attr_e('Transparent (no fill)', 'zappoppop'); ?>">
                <input type="checkbox" id="prop-shape-transparent" class="zappoppop-gp-bind" data-prop="shapeNoFill" value="1" aria-label="<?php esc_attr_e('Transparent fill', 'zappoppop'); ?>">
                <span class="zappoppop-shape-clear-sw" aria-hidden="true"></span>
                <span class="zappoppop-shape-clear-tx"><?php esc_html_e('Transparent', 'zappoppop'); ?></span>
            </label>
        </div>
    </div>
    <div class="zappoppop-shape-row">
        <div>
            <label class="zappoppop-shape-label" for="prop-shape-stroke-color"><?php esc_html_e('Stroke Color', 'zappoppop'); ?></label>
            <div class="zappoppop-shape-color" data-zpp-clear-for="prop-shape-stroke-transparent">
                <input type="color" data-zpp-alpha="1" id="prop-shape-stroke-color" data-zpp-prop="strokeColor" value="#0284c7">
                <label class="zappoppop-shape-clear zappoppop-shape-clear-icon" title="<?php esc_attr_e('Transparent stroke', 'zappoppop'); ?>">
                    <input type="checkbox" id="prop-shape-stroke-transparent" aria-label="<?php esc_attr_e('Transparent stroke', 'zappoppop'); ?>">
                    <span class="zappoppop-shape-clear-sw" aria-hidden="true"></span>
                </label>
            </div>
        </div>
        <div>
            <label class="zappoppop-shape-label" for="prop-shape-stroke-width"><?php esc_html_e('Stroke Width (px)', 'zappoppop'); ?></label>
            <input type="number" id="prop-shape-stroke-width" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="strokeWidth" min="0" max="40" value="0" style="font-size:12px;" placeholder="0">
        </div>
    </div>
    <div class="zappoppop-shape-field" style="margin-bottom:0;">
        <div class="zappoppop-shape-rot-head">
            <label class="zappoppop-shape-label" for="prop-shape-rotate" style="margin:0;"><?php esc_html_e('Rotation (0°–360°)', 'zappoppop'); ?></label>
            <span id="prop-shape-rotate-val" class="zappoppop-shape-rot-val">0&deg;</span>
        </div>
        <input type="range" id="prop-shape-rotate" class="zappoppop-gp-bind zappoppop-shape-range" data-prop="rotate" min="0" max="360" step="1" value="0">
    </div>
    <?php $zappoppop_lk_id = 'shape'; include ZAPPOPPOP_PATH . 'templates/builder/elements/partial-link-group.php'; ?>
</div>
