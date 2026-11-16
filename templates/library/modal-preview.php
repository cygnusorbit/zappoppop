<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<?php ZapPopPop_Inline_Assets::style('library-modal-preview'); ?><div class="zappoppop-modal-backdrop" id="zappoppop-lib-preview-modal" role="dialog" aria-modal="true" aria-labelledby="zappoppop-lp-title">
    <div class="lp-dlg">
        <div class="lp-hd">
            <h3 id="zappoppop-lp-title"><?php esc_html_e('Template Preview', 'zappoppop'); ?></h3>
            <button type="button" class="lp-x" id="zappoppop-lp-close" aria-label="<?php esc_attr_e('Close', 'zappoppop'); ?>">&times;</button>
        </div>
        <div class="lp-bar">
            <button type="button" class="button button-small is-on" data-lp-view="desktop"><?php esc_html_e('Desktop', 'zappoppop'); ?></button>
            <button type="button" class="button button-small" data-lp-view="mobile"><?php esc_html_e('Mobile', 'zappoppop'); ?></button>
            <span class="lp-sp"></span>
            <button type="button" class="button button-small" id="zappoppop-lp-replay"><?php esc_html_e('Replay', 'zappoppop'); ?></button>
        </div>
        <div class="lp-stage" id="zappoppop-lp-stage"></div>
        <div class="lp-ft">
            <span id="zappoppop-lp-meta"></span>
            <span>
                <button type="button" class="button" id="zappoppop-lp-cancel"><?php esc_html_e('Close', 'zappoppop'); ?></button>
                <button type="button" class="button button-primary" id="zappoppop-lp-use" style="display:none;"><?php esc_html_e('Use Template', 'zappoppop'); ?></button>
            </span>
        </div>
    </div>
</div>
