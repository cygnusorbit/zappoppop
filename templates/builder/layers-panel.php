<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div id="zappoppop-floating-layers-panel" class="zappoppop-floating-layers-panel">

    
    
    <div id="zappoppop-layers-header">
        <span class="zappoppop-layers-title-wrap">
            <span class="dashicons dashicons-menu"></span> <?php esc_html_e('Layers Panel', 'zappoppop'); ?>
        </span>
        <div class="zappoppop-layers-ctrls-wrap">
            <span id="zappoppop-layers-count" title="<?php esc_attr_e('Active Layers Count', 'zappoppop'); ?>">0</span>
            <button type="button" id="zappoppop-layers-toggle-collapse" title="<?php esc_attr_e('Collapse / Expand Layers Panel', 'zappoppop'); ?><?php esc_html_e('" aria-label="', 'zappoppop'); ?><?php esc_attr_e('Toggle Layers Panel', 'zappoppop'); ?>">
                <span class="dashicons dashicons-arrow-up-alt2"></span>
            </button>
        </div>
    
    

    </div>
    
    <div id="zappoppop-layers-list">
        <!-- Dynamically rendered and sortable layer items -->
    </div>
</div>
