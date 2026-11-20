<?php
if (!defined('ABSPATH')) exit;
?>
<!-- DECOMPOSED MODULE 04: LABEL / HEADLINE / TEXT (label / title / paragraph) -->
<div class="element-panel zappoppop-panel-specific" id="panel-elem-label" data-elem-family="media" style="display:none;background:#0f172a;border:1px solid #1e293b;border-radius:8px;padding:12px;margin-bottom:14px;">
    <div style="font-size:10px;font-weight:800;color:#38bdf8;text-transform:uppercase;margin-bottom:10px;letter-spacing:0.5px;"><?php esc_html_e('Label / Headline Content', 'zappoppop'); ?></div>
    <label style="display:block;font-size:10px;font-weight:700;color:#94a3b8;margin-bottom:4px;text-transform:uppercase;"><?php esc_html_e('Label Content / Inline HTML', 'zappoppop'); ?></label>
    <div style="position:relative;text-align:right;margin:-18px 0 6px 0;height:24px;">
        <button type="button" id="zappoppop-label-token-btn" title="<?php esc_attr_e('Insert WordPress value', 'zappoppop'); ?>" aria-haspopup="true" style="cursor:pointer;background:#1e293b;border:1px solid #334155;color:#e2e8f0;border-radius:4px;padding:2px 8px;font-size:12px;line-height:18px;">&lt;/&gt;</button>
        <div id="zappoppop-label-token-menu" role="menu" style="display:none;position:absolute;right:0;top:26px;z-index:100000;min-width:190px;max-height:240px;overflow-y:auto;background:#0f172a;border:1px solid #334155;border-radius:6px;box-shadow:0 10px 25px rgba(0,0,0,.45);text-align:left;"></div>
    </div>
    <textarea id="prop-label-content" rows="3" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="content" placeholder="Hello World" style="margin-bottom:10px;font-size:12px;"></textarea>
    <input type="hidden" id="prop-title-text" class="zappoppop-gp-alias" data-prop="content">
    <input type="hidden" id="prop-paragraph-text" class="zappoppop-gp-alias" data-prop="content">
    <input type="hidden" id="prop-text-content" class="zappoppop-gp-alias" data-prop="content">
    <div style="margin-bottom:10px;">
        <label style="display:block;font-size:10px;font-weight:700;color:#94a3b8;margin-bottom:4px;text-transform:uppercase;"><?php esc_html_e('URL', 'zappoppop'); ?></label>
        <input type="text" id="prop-label-url" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="url" placeholder="https://" style="font-size:12px;margin-bottom:8px;">
        <label style="display:flex;align-items:center;gap:8px;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;cursor:pointer;">
            <input type="checkbox" id="prop-label-blank" class="zappoppop-gp-bind" data-prop="targetBlank"> <?php esc_html_e('Open link in new tab', 'zappoppop'); ?>
        </label>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
        <div>
            <label style="display:block;font-size:10px;font-weight:700;color:#94a3b8;margin-bottom:4px;text-transform:uppercase;"><?php esc_html_e('Semantic Tag', 'zappoppop'); ?></label>
            <select id="prop-label-tag" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="tag" style="font-size:12px;">
                <option value="div"><?php esc_html_e('DIV Container', 'zappoppop'); ?></option>
                <option value="p"><?php esc_html_e('Paragraph (P)', 'zappoppop'); ?></option>
                <option value="span"><?php esc_html_e('Inline SPAN', 'zappoppop'); ?></option>
                <option value="h1"><?php esc_html_e('Heading H1', 'zappoppop'); ?></option>
                <option value="h2"><?php esc_html_e('Heading H2', 'zappoppop'); ?></option>
                <option value="h3"><?php esc_html_e('Heading H3', 'zappoppop'); ?></option>
                <option value="h4"><?php esc_html_e('Heading H4', 'zappoppop'); ?></option>
                <option value="h5"><?php esc_html_e('Heading H5', 'zappoppop'); ?></option>
                <option value="h6"><?php esc_html_e('Heading H6', 'zappoppop'); ?></option>
            </select>
            <input type="hidden" id="prop-title-tag" class="zappoppop-gp-alias" data-prop="tag">
            <input type="hidden" id="prop-paragraph-tag" class="zappoppop-gp-alias" data-prop="tag">
            <input type="hidden" id="prop-text-tag" class="zappoppop-gp-alias" data-prop="tag">
        </div>
        <div>
            <label style="display:block;font-size:10px;font-weight:700;color:#94a3b8;margin-bottom:4px;text-transform:uppercase;"><?php esc_html_e('Text Transform', 'zappoppop'); ?></label>
            <select id="prop-label-transform" class="widefat zappoppop-input zappoppop-gp-bind" data-prop="textTransform" style="font-size:12px;">
                <option value="none"><?php esc_html_e('None (As Typed)', 'zappoppop'); ?></option>
                <option value="uppercase"><?php esc_html_e('Uppercase', 'zappoppop'); ?></option>
                <option value="lowercase"><?php esc_html_e('lowercase', 'zappoppop'); ?></option>
                <option value="capitalize"><?php esc_html_e('Capitalize Words', 'zappoppop'); ?></option>
            </select>
            <input type="hidden" id="prop-title-transform" class="zappoppop-gp-alias" data-prop="textTransform">
        </div>
    </div>
</div>
