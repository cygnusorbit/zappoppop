<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<?php ZapPopPop_Inline_Assets::style('submissions-modal-export'); ?><div id="zappoppop-export-modal" role="dialog" aria-modal="true" aria-labelledby="wex-title">
    <div class="wex-box">
        <div class="wex-head">
            <h2 id="wex-title"><?php echo esc_html__('Export to CSV', 'zappoppop'); ?></h2>
            <p class="wex-sub" id="wex-sub"></p>
        </div>
        <div class="wex-tools">
            <button type="button" class="button button-small" id="wex-all"><?php echo esc_html__('Select all', 'zappoppop'); ?></button>
            <button type="button" class="button button-small" id="wex-none"><?php echo esc_html__('Select none', 'zappoppop'); ?></button>
        </div>
        <div class="wex-body" id="wex-body"></div>
        <div class="wex-foot">
            <button type="button" class="button" id="wex-cancel"><?php echo esc_html__('Cancel', 'zappoppop'); ?></button>
            <button type="button" class="button button-primary" id="wex-go"><?php echo esc_html__('Export', 'zappoppop'); ?></button>
        </div>
    </div>
</div>
