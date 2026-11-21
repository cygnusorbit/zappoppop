<?php
/* ZPP_LINK_GROUP: compact Link URL + slide switches. Mirrors Logic > Action & Click Routing (same model keys: linkUrl, linkBlank, nofollow). */
if (!defined('ABSPATH')) exit;
$zappoppop_lk = isset($zappoppop_lk_id) ? preg_replace('/[^a-z0-9_-]/i', '', (string) $zappoppop_lk_id) : 'x';
?>
<div class="zpp-lk" data-zpp-lk="<?php echo esc_attr($zappoppop_lk); ?>" style="display:none;">
    <label class="zappoppop-prop-label" for="prop-lk-<?php echo esc_attr($zappoppop_lk); ?>-url"><?php esc_html_e('Link URL', 'zappoppop'); ?></label>
    <input type="text" id="prop-lk-<?php echo esc_attr($zappoppop_lk); ?>-url" class="widefat zappoppop-input zpp-lk-url" placeholder="https://example.com" autocomplete="off" />
    <div class="zpp-lk-sw">
        <label class="zpp-sw"><input type="checkbox" id="prop-lk-<?php echo esc_attr($zappoppop_lk); ?>-blank" class="zpp-lk-blank" /><span class="zpp-sw-track"></span><span><?php esc_html_e('Open in new tab', 'zappoppop'); ?></span></label>
        <label class="zpp-sw"><input type="checkbox" id="prop-lk-<?php echo esc_attr($zappoppop_lk); ?>-nofollow" class="zpp-lk-nofollow" /><span class="zpp-sw-track"></span><span><code>rel="nofollow"</code></span></label>
    </div>
</div>
<div class="zpp-lk-note" data-zpp-lk-note="<?php echo esc_attr($zappoppop_lk); ?>" style="display:none;"><?php esc_html_e('This layer uses a different click action. Change it in Logic > Action & Click Routing.', 'zappoppop'); ?></div>
