<?php
if (!defined('ABSPATH')) exit;
?>
<!-- DECOMPOSED MODULE 23: FILE UPLOAD (upload / file) -->
<div class="element-panel zappoppop-panel-specific" id="panel-elem-upload" data-elem-family="form" style="display:none;background:#0f172a;border:1px solid #1e293b;border-radius:8px;padding:12px;margin-bottom:14px;">
    <div style="font-size:10px;font-weight:800;color:#38bdf8;text-transform:uppercase;margin-bottom:10px;letter-spacing:0.5px;"><?php esc_html_e('File Upload Dropzone', 'zappoppop'); ?></div>
    <label style="display:block;font-size:10px;font-weight:700;color:#94a3b8;margin-bottom:4px;text-transform:uppercase;"><?php esc_html_e('Dropzone / Button Label', 'zappoppop'); ?></label>
    <input type="text" id="prop-upload-label" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="placeholder" placeholder="<?php esc_attr_e('Choose file or drag here...', 'zappoppop'); ?>" style="margin-bottom:10px;font-size:12px;">
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:8px;margin-bottom:8px;">
        <div>
            <label style="display:block;font-size:10px;font-weight:700;color:#94a3b8;margin-bottom:4px;text-transform:uppercase;"><?php esc_html_e('Allowed Extensions', 'zappoppop'); ?></label>
            <input type="text" id="prop-upload-exts" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="allowedExtensions" placeholder="<?php esc_attr_e('pdf,jpg,png', 'zappoppop'); ?>" style="font-size:12px;">
        </div>
        <div>
            <label style="display:block;font-size:10px;font-weight:700;color:#94a3b8;margin-bottom:4px;text-transform:uppercase;"><?php esc_html_e('Max Size (MB)', 'zappoppop'); ?></label>
            <input type="number" id="prop-upload-maxsize" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="maxFileSize" min="1" max="100" style="font-size:12px;">
        </div>
    </div>
    <?php ZapPopPop_Inline_Assets::style('builder-elements-elem-23-upload'); ?>    <label style="display:flex;align-items:center;justify-content:space-between;gap:8px;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;cursor:pointer;margin-bottom:8px;">
        <span><?php esc_html_e('Allow Multiple Files', 'zappoppop'); ?></span>
        <span style="position:relative;display:inline-block;width:36px;height:20px;flex-shrink:0;">
            <input type="checkbox" id="prop-upload-multiple" class="zappoppop-gp-bind" data-prop="multiUpload" value="1" style="position:absolute;opacity:0;width:0;height:0;">
            <span class="zappoppop-up-slider"></span>
        </span>
    </label>
    <div id="prop-upload-maxfiles-row" style="display:none;margin-bottom:8px;">
        <label style="display:block;font-size:10px;font-weight:700;color:#94a3b8;margin-bottom:4px;text-transform:uppercase;"><?php esc_html_e('Max Number of Files', 'zappoppop'); ?></label>
        <input type="number" id="prop-upload-maxfiles" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="maxFiles" min="1" max="20" placeholder="e.g. 3" style="font-size:12px;">
    </div>
    <label style="display:block;font-size:10px;font-weight:700;color:#94a3b8;margin-bottom:4px;text-transform:uppercase;"><?php esc_html_e('Error Message (File Too Large)', 'zappoppop'); ?></label>
    <input type="text" id="prop-upload-errmsg" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="uploadErrorMessage" placeholder="<?php esc_attr_e('File too large. Maximum size is {max}MB.', 'zappoppop'); ?>" style="font-size:12px;">
    <div style="font-size:9px;color:#64748b;margin-top:3px;"><?php esc_html_e('Tokens: {max} = size limit (MB), {file} = file name', 'zappoppop'); ?></div>
</div>
