<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<?php ZapPopPop_Inline_Assets::style('submissions-modal-table-options'); ?><div id="zappoppop-table-options-modal" data-config="<?php echo esc_attr(wp_json_encode($tbl_config_payload)); ?>">
    <div class="wto-panel" role="dialog" aria-modal="true" aria-labelledby="wto-title">
        <div class="wto-head">
            <h2 id="wto-title"><span class="dashicons dashicons-list-view"></span> <?php esc_html_e('Table options', 'zappoppop'); ?>
                <small>(<?php echo $sel_uid === '' ? esc_html__('All popups / default', 'zappoppop') : esc_html($sel_uid); ?>)</small>
            </h2>
            <div class="wto-side">
                <button type="button" class="wto-close" id="wto-close" title="<?php esc_attr_e('Close', 'zappoppop'); ?>">&#10005;</button>
            </div>
        </div>
        <div class="wto-bodywrap">

        <div class="wto-row">
            <div class="wto-label"><?php esc_html_e('Labels', 'zappoppop'); ?></div>
            <div class="wto-body">
                <div id="wto-labels"></div>
                <button type="button" class="wto-add-btn" id="wto-add-label"><?php esc_html_e('+ Add New', 'zappoppop'); ?></button>
            </div>
        </div>

        <div class="wto-row">
            <div class="wto-label"><?php esc_html_e('Column layout', 'zappoppop'); ?></div>
            <div class="wto-body">
                <div class="wto-cols">
                    <div>
                        <h4><?php esc_html_e('Active columns', 'zappoppop'); ?></h4>
                        <ul id="zappoppop-cols-active" class="zappoppop-cols-list"></ul>
                    </div>
                    <div>
                        <h4><?php esc_html_e('Inactive columns', 'zappoppop'); ?></h4>
                        <ul id="zappoppop-cols-inactive" class="zappoppop-cols-list"></ul>
                    </div>
                </div>
                <div style="margin-top:10px;">
                    <span class="wto-custom-wrap" hidden style="display:none !important;"><input type="text" class="wto-custom" id="wto-custom-key" placeholder="<?php esc_attr_e('Custom form field name (e.g. mobile_number)', 'zappoppop'); ?>"><button type="button" class="button" id="wto-add-custom"><?php esc_html_e('Add field', 'zappoppop'); ?></button></span>
                    <?php if ($sel_uid !== '') : ?>
                        
                        <p class="wto-help"><?php esc_html_e('Form fields on this popup sync live — new fields appear under Inactive columns without refreshing.', 'zappoppop'); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="wto-row">
            <div class="wto-label"><?php esc_html_e('Entries per page', 'zappoppop'); ?></div>
            <div class="wto-body"><select id="wto-per-page"><option value="10">10</option><option value="20">20</option><option value="30">30</option></select></div>
        </div>
        </div>
        <div class="wto-foot">
            <button type="button" class="wto-save" id="wto-save"><?php esc_html_e('Save', 'zappoppop'); ?></button>
        </div>
    </div>
</div>
