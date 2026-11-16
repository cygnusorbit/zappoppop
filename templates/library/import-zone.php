<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- template variables are local to the method that includes this file
if (!defined('ABSPATH')) {
    exit;
}
$zpp_zip_ok = class_exists('ZapPopPop_Template_Bundle') && ZapPopPop_Template_Bundle::zip_available();
?>
<div class="zpp-imp-card" id="zappoppop-lib-import-card">
    <div class="zpp-imp-head">
        <span class="dashicons dashicons-upload"></span>
        <h3><?php esc_html_e('Import Template', 'zappoppop'); ?></h3>
    </div>
    <p class="zpp-imp-help">
        <?php
        echo $zpp_zip_ok
            ? esc_html__('Import a template .zip (template.json plus its images) or a .json file. Images are added to your Media Library so the links never break. Imported templates appear under My Templates.', 'zappoppop')
            : esc_html__('Import a template .json file. The PHP Zip extension is not available on this server, so .zip files cannot be imported. Imported templates appear under My Templates.', 'zappoppop');
        ?>
    </p>
    <div class="zpp-imp-row">
        <div class="zpp-imp-drop" id="zappoppop-lib-drop">
            <span class="dashicons dashicons-media-archive"></span>
            <span class="zpp-imp-drop-text">
                <?php echo $zpp_zip_ok ? esc_html__('Drag a template .zip here, or', 'zappoppop') : esc_html__('Drag a template .json here, or', 'zappoppop'); ?>
                <button type="button" class="zpp-imp-browse" id="zappoppop-lib-drop-pick"><?php esc_html_e('browse', 'zappoppop'); ?></button>
            </span>
            <input type="file" id="zappoppop-lib-file" class="zpp-imp-file" accept=".zip,.json,application/zip,application/json" multiple tabindex="-1" aria-hidden="true">
        </div>
        <button type="button" class="button button-primary zappoppop-lib-import-btn" id="zpp-imp-btn"><?php esc_html_e('Import Template', 'zappoppop'); ?></button>
    </div>
    <div id="zappoppop-lib-drop-log" class="zpp-drop-log" aria-live="polite" style="display:none;"></div>
</div>
