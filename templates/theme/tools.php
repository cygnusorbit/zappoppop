<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="zappoppop-box zpp-theme-box">
    <h3><?php esc_html_e('Export / Import', 'zappoppop'); ?></h3>
    <textarea id="zpp-theme-json" name="zpp_theme_import" rows="6" spellcheck="false"></textarea>
    <p>
        <button type="button" class="button" id="zpp-theme-export"><?php esc_html_e('Export current', 'zappoppop'); ?></button>
        <button type="submit" class="button" id="zpp-theme-import" data-action="import"><?php esc_html_e('Import JSON', 'zappoppop'); ?></button>
    </p>
    <hr>
    <p class="zpp-theme-tools-foot">
        <button type="button" class="button" data-zpp-defaults-save="theme" title="<?php echo esc_attr__('Use the last saved theme as the default', 'zappoppop'); ?>"><?php esc_html_e('Save as Default', 'zappoppop'); ?></button>
        <button type="submit" class="button zpp-btn-danger" id="zpp-theme-reset-all" data-action="reset"><?php esc_html_e('Reset all to defaults', 'zappoppop'); ?></button>
    </p>
</div>
