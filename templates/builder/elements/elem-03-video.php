<?php
if (!defined('ABSPATH')) exit;
?>
<!-- DECOMPOSED MODULE 03: VIDEO EMBED (video) -->
<?php ZapPopPop_Inline_Assets::style('builder-elements-elem-03-video'); ?>
<div class="element-panel zappoppop-panel-specific" id="panel-elem-video" data-elem-family="media" style="display:none;background:#0f172a;border:1px solid #1e293b;border-radius:8px;padding:12px;margin-bottom:14px;">
    <div style="font-size:10px;font-weight:800;color:#38bdf8;text-transform:uppercase;margin-bottom:10px;letter-spacing:0.5px;"><?php esc_html_e('Video Embed & Playback', 'zappoppop'); ?></div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:10px;">
        <!-- ZPP_VIDEO_TOGGLES: number fields carry stepper + Reset (default 200 x 150) -->
        <div>
            <label for="prop-video-width" style="display:block;font-size:10px;font-weight:700;color:#94a3b8;margin-bottom:4px;text-transform:uppercase;"><?php esc_html_e('Width (px)', 'zappoppop'); ?></label>
            <span class="zpp-ty-numwrap"><span class="zpp-ty-ctl"><input type="number" id="prop-video-width" class="widefat zappoppop-input" min="16" step="1" value="200" /><span class="zpp-ty-unit">PX</span><span class="zpp-sp-step"><button type="button" class="zpp-sp-up" tabindex="-1" title="<?php esc_attr_e('Increase', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Increase', 'zappoppop'); ?>"></button><button type="button" class="zpp-sp-down" tabindex="-1" title="<?php esc_attr_e('Decrease', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Decrease', 'zappoppop'); ?>"></button></span></span><button type="button" class="zpp-sp-reset zpp-ty-reset" data-default="200" title="<?php esc_attr_e('Reset', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Reset', 'zappoppop'); ?>"><span class="dashicons dashicons-image-rotate" aria-hidden="true"></span></button></span>
        </div>
        <div>
            <label for="prop-video-height" style="display:block;font-size:10px;font-weight:700;color:#94a3b8;margin-bottom:4px;text-transform:uppercase;"><?php esc_html_e('Height (px)', 'zappoppop'); ?></label>
            <span class="zpp-ty-numwrap"><span class="zpp-ty-ctl"><input type="number" id="prop-video-height" class="widefat zappoppop-input" min="16" step="1" value="150" /><span class="zpp-ty-unit">PX</span><span class="zpp-sp-step"><button type="button" class="zpp-sp-up" tabindex="-1" title="<?php esc_attr_e('Increase', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Increase', 'zappoppop'); ?>"></button><button type="button" class="zpp-sp-down" tabindex="-1" title="<?php esc_attr_e('Decrease', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Decrease', 'zappoppop'); ?>"></button></span></span><button type="button" class="zpp-sp-reset zpp-ty-reset" data-default="150" title="<?php esc_attr_e('Reset', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Reset', 'zappoppop'); ?>"><span class="dashicons dashicons-image-rotate" aria-hidden="true"></span></button></span>
        </div>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:10px;">
        <div>
            <label style="display:block;font-size:10px;font-weight:700;color:#94a3b8;margin-bottom:4px;text-transform:uppercase;"><?php esc_html_e('Video Provider', 'zappoppop'); ?></label>
            <select id="prop-video-provider" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="videoProvider" style="font-size:12px;">
                <option value="youtube"><?php esc_html_e('YouTube', 'zappoppop'); ?></option>
                <option value="vimeo"><?php esc_html_e('Vimeo', 'zappoppop'); ?></option>
                <option value="mp4"><?php esc_html_e('Direct MP4 URL', 'zappoppop'); ?></option>
            </select>
        </div>
        <div>
            <label style="display:block;font-size:10px;font-weight:700;color:#94a3b8;margin-bottom:4px;text-transform:uppercase;"><?php esc_html_e('Aspect Ratio', 'zappoppop'); ?></label>
            <select id="prop-video-aspect" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="aspectRatio" style="font-size:12px;">
                <option value="16:9"><?php esc_html_e('16:9 Widescreen', 'zappoppop'); ?></option>
                <option value="4:3"><?php esc_html_e('4:3 Standard', 'zappoppop'); ?></option>
                <option value="1:1"><?php esc_html_e('1:1 Square', 'zappoppop'); ?></option>
                <option value="9:16"><?php esc_html_e('9:16 Vertical', 'zappoppop'); ?></option>
            </select>
        </div>
    </div>
    <label style="display:block;font-size:10px;font-weight:700;color:#94a3b8;margin-bottom:4px;text-transform:uppercase;"><?php esc_html_e('Video URL or Embed ID', 'zappoppop'); ?></label>
    <input type="text" id="prop-video-url" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="videoUrl" placeholder="https://www.youtube.com/watch?v=..." style="margin-bottom:10px;font-size:12px;">
    <label style="display:block;font-size:10px;font-weight:700;color:#94a3b8;margin-bottom:4px;text-transform:uppercase;"><?php esc_html_e('Video Thumbnail', 'zappoppop'); ?></label>
    <div style="display:flex;gap:6px;margin-bottom:6px;">
        <input type="text" id="prop-video-thumb" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="videoThumb" placeholder="https://... (optional)" style="flex:1;min-width:0;font-size:12px;">
        <button type="button" id="prop-video-thumb-btn" class="button button-secondary" style="height:36px;padding:0 10px;font-size:11px;font-weight:700;white-space:nowrap;"><?php esc_html_e('WP Media', 'zappoppop'); ?></button>
    </div>
    <div id="prop-video-thumb-preview" role="button" tabindex="0" title="<?php esc_attr_e('Click to choose a thumbnail from the Media Library', 'zappoppop'); ?>" style="position:relative;height:90px;margin-bottom:10px;border:1px dashed #334155;border-radius:6px;background:#020617 center / cover no-repeat;cursor:pointer;display:flex;align-items:center;justify-content:center;overflow:hidden;">
        <span class="zappoppop-video-thumb-hint" style="font-size:11px;color:#64748b;pointer-events:none;"><?php esc_html_e('Click to add thumbnail', 'zappoppop'); ?></span>
        <button type="button" id="prop-video-thumb-clear" title="<?php esc_attr_e('Remove thumbnail', 'zappoppop'); ?>" style="display:none;position:absolute;top:4px;right:4px;width:22px;height:22px;line-height:20px;padding:0;border:0;border-radius:50%;background:rgba(15,23,42,.85);color:#fff;font-size:14px;cursor:pointer;">&times;</button>
    </div>
    <div class="zpp-video-switches" style="display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:10px 8px;"><!-- ZPP_VIDEO_2COL -->
        <label class="zpp-sw"><input type="checkbox" id="prop-video-autoplay" class="zappoppop-gp-bind" data-prop="videoAutoplay" value="1"><span class="zpp-sw-track"></span><span><?php esc_html_e('Autoplay', 'zappoppop'); ?></span></label>
        <label class="zpp-sw"><input type="checkbox" id="prop-video-mute" class="zappoppop-gp-bind" data-prop="videoMute" value="1"><span class="zpp-sw-track"></span><span><?php esc_html_e('Muted', 'zappoppop'); ?></span></label>
        <label class="zpp-sw"><input type="checkbox" id="prop-video-loop" class="zappoppop-gp-bind" data-prop="videoLoop" value="1"><span class="zpp-sw-track"></span><span><?php esc_html_e('Loop Video', 'zappoppop'); ?></span></label>
        <label class="zpp-sw"><input type="checkbox" id="prop-video-controls" class="zappoppop-gp-bind" data-prop="videoControls" value="1" checked><span class="zpp-sw-track"></span><span><?php esc_html_e('Player Controls', 'zappoppop'); ?></span></label>
    </div>
</div>
