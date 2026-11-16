<?php
if (!defined('ABSPATH')) exit;
?>
<!-- DECOMPOSED MODULE 07: FONT AWESOME / VECTOR ICON (icon) -->
<div class="element-panel zappoppop-panel-specific" id="panel-elem-icon" data-elem-family="media" style="display:none;background:#0f172a;border:1px solid #1e293b;border-radius:8px;padding:12px;margin-bottom:14px;">
    <div style="font-size:10px;font-weight:800;color:#38bdf8;text-transform:uppercase;margin-bottom:10px;letter-spacing:0.5px;"><?php esc_html_e('7. Font Awesome / Vector Icon', 'zappoppop'); ?></div>
    <label style="display:block;font-size:10px;font-weight:700;color:#94a3b8;margin-bottom:4px;text-transform:uppercase;"><?php esc_html_e('ICON CLASS (FONT AWESOME OR DASHICONS)', 'zappoppop'); ?></label>
    <div style="display:flex;gap:6px;align-items:stretch;margin-bottom:8px;"><input type="text" id="prop-icon-class" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="iconClass" value="dashicons-star-filled" placeholder="e.g. fas fa-star or dashicons-star-filled" style="font-size:12px;flex:1;min-width:0;"><button type="button" class="button zappoppop-fa-pick-btn" data-target="#prop-icon-class" title="<?php esc_attr_e('Select icon', 'zappoppop'); ?><?php esc_html_e('" aria-label="', 'zappoppop'); ?><?php esc_attr_e('Select icon', 'zappoppop'); ?>" style="flex:0 0 auto;width:34px;padding:0;display:flex;align-items:center;justify-content:center;"><i class="fas fa-icons" style="pointer-events:none;"></i></button></div>
    <div style="display:flex;gap:5px;flex-wrap:wrap;margin-bottom:10px;">
        <button type="button" class="button button-small zappoppop-icon-preset" data-icon="dashicons-star-filled"><?php esc_html_e('★ Star', 'zappoppop'); ?></button>
        <button type="button" class="button button-small zappoppop-icon-preset" data-icon="dashicons-yes-alt"><?php esc_html_e('✔ Check', 'zappoppop'); ?></button>
        <button type="button" class="button button-small zappoppop-icon-preset" data-icon="dashicons-heart"><?php esc_html_e('♥ Heart', 'zappoppop'); ?></button>
        <button type="button" class="button button-small zappoppop-icon-preset" data-icon="dashicons-bell"><?php esc_html_e('🔔 Bell', 'zappoppop'); ?></button>
        <button type="button" class="button button-small zappoppop-icon-preset" data-icon="dashicons-awards"><?php esc_html_e('🏆 Award', 'zappoppop'); ?></button>
        <button type="button" class="button button-small zappoppop-icon-preset" data-icon="dashicons-megaphone"><?php esc_html_e('📣 Promo', 'zappoppop'); ?></button>
    </div>
    <div id="zappoppop-icon-picker-grid" style="display:grid;grid-template-columns:repeat(6,1fr);gap:6px;margin-bottom:10px;">
        <button type="button" class="zappoppop-icon-preset zappoppop-icon-grid-btn" data-icon="dashicons-star-filled" title="<?php esc_attr_e('Star', 'zappoppop'); ?>"><span class="dashicons dashicons-star-filled" style="pointer-events:none;"></span></button>
        <button type="button" class="zappoppop-icon-preset zappoppop-icon-grid-btn" data-icon="dashicons-yes-alt" title="<?php esc_attr_e('Check', 'zappoppop'); ?>"><span class="dashicons dashicons-yes-alt" style="pointer-events:none;"></span></button>
        <button type="button" class="zappoppop-icon-preset zappoppop-icon-grid-btn" data-icon="dashicons-heart" title="<?php esc_attr_e('Heart', 'zappoppop'); ?>"><span class="dashicons dashicons-heart" style="pointer-events:none;"></span></button>
        <button type="button" class="zappoppop-icon-preset zappoppop-icon-grid-btn" data-icon="dashicons-bell" title="<?php esc_attr_e('Bell', 'zappoppop'); ?>"><span class="dashicons dashicons-bell" style="pointer-events:none;"></span></button>
        <button type="button" class="zappoppop-icon-preset zappoppop-icon-grid-btn" data-icon="dashicons-awards" title="<?php esc_attr_e('Award', 'zappoppop'); ?>"><span class="dashicons dashicons-awards" style="pointer-events:none;"></span></button>
        <button type="button" class="zappoppop-icon-preset zappoppop-icon-grid-btn" data-icon="dashicons-megaphone" title="<?php esc_attr_e('Megaphone', 'zappoppop'); ?>"><span class="dashicons dashicons-megaphone" style="pointer-events:none;"></span></button>
        <button type="button" class="zappoppop-icon-preset zappoppop-icon-grid-btn" data-icon="dashicons-location" title="<?php esc_attr_e('Location', 'zappoppop'); ?>"><span class="dashicons dashicons-location" style="pointer-events:none;"></span></button>
        <button type="button" class="zappoppop-icon-preset zappoppop-icon-grid-btn" data-icon="dashicons-cart" title="<?php esc_attr_e('Cart', 'zappoppop'); ?>"><span class="dashicons dashicons-cart" style="pointer-events:none;"></span></button>
        <button type="button" class="zappoppop-icon-preset zappoppop-icon-grid-btn" data-icon="dashicons-tag" title="<?php esc_attr_e('Tag', 'zappoppop'); ?>"><span class="dashicons dashicons-tag" style="pointer-events:none;"></span></button>
        <button type="button" class="zappoppop-icon-preset zappoppop-icon-grid-btn" data-icon="dashicons-lock" title="<?php esc_attr_e('Lock', 'zappoppop'); ?>"><span class="dashicons dashicons-lock" style="pointer-events:none;"></span></button>
        <button type="button" class="zappoppop-icon-preset zappoppop-icon-grid-btn" data-icon="dashicons-thumbs-up" title="<?php esc_attr_e('Thumbs Up', 'zappoppop'); ?>"><span class="dashicons dashicons-thumbs-up" style="pointer-events:none;"></span></button>
        <button type="button" class="zappoppop-icon-preset zappoppop-icon-grid-btn" data-icon="dashicons-warning" title="<?php esc_attr_e('Warning', 'zappoppop'); ?>"><span class="dashicons dashicons-warning" style="pointer-events:none;"></span></button>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
        <div>
            <label style="display:block;font-size:10px;font-weight:700;color:#94a3b8;margin-bottom:4px;text-transform:uppercase;"><?php esc_html_e('ICON SIZE (PX)', 'zappoppop'); ?></label>
            <input type="number" id="prop-icon-size" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="iconSize" min="12" max="200" value="36" style="font-size:12px;">
        </div>
        <div>
            <label style="display:block;font-size:10px;font-weight:700;color:#94a3b8;margin-bottom:4px;text-transform:uppercase;"><?php esc_html_e('ROTATION (DEG)', 'zappoppop'); ?></label>
            <input type="number" id="prop-icon-rotate" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="rotate" min="0" max="360" value="0" style="font-size:12px;">
        </div>
    </div>
</div>

<?php
$zappoppop_fa_picker_07 = __DIR__ . '/tab-style-icon-picker.php';
if ( file_exists( $zappoppop_fa_picker_07 ) ) { include_once $zappoppop_fa_picker_07; }
?>
