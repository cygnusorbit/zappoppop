<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- template variables are local to the method that includes this file
if (!defined('ABSPATH')) {
    exit;
}
$zappoppop_pg_pos = isset($zappoppop_pg_pos) ? $zappoppop_pg_pos : 'top';
if ($zappoppop_pg_pos === 'top') : ?>
<div class="zpp-pg-bar" id="zpp-pg-bar">
    <span class="zpp-pg-info" id="zpp-pg-info" aria-live="polite"></span>
    <div class="zpp-pg-views" role="group" aria-label="<?php esc_attr_e('Cards per page', 'zappoppop'); ?>">
        <span class="zpp-pg-label"><?php esc_html_e('View', 'zappoppop'); ?></span>
        <button type="button" class="zpp-pg-view" data-cols="3" aria-pressed="false" title="3 × 3" aria-label="3 × 3"><svg viewBox="0 0 16 16" width="16" height="16" aria-hidden="true" focusable="false"><path d="M0.00 0.00h4.53v4.53h-4.53zM5.73 0.00h4.53v4.53h-4.53zM11.47 0.00h4.53v4.53h-4.53zM0.00 5.73h4.53v4.53h-4.53zM5.73 5.73h4.53v4.53h-4.53zM11.47 5.73h4.53v4.53h-4.53zM0.00 11.47h4.53v4.53h-4.53zM5.73 11.47h4.53v4.53h-4.53zM11.47 11.47h4.53v4.53h-4.53z"/></svg></button>
        <button type="button" class="zpp-pg-view is-active" data-cols="4" aria-pressed="true" title="4 × 4" aria-label="4 × 4"><svg viewBox="0 0 16 16" width="16" height="16" aria-hidden="true" focusable="false"><path d="M0.00 0.00h3.10v3.10h-3.10zM4.30 0.00h3.10v3.10h-3.10zM8.60 0.00h3.10v3.10h-3.10zM12.90 0.00h3.10v3.10h-3.10zM0.00 4.30h3.10v3.10h-3.10zM4.30 4.30h3.10v3.10h-3.10zM8.60 4.30h3.10v3.10h-3.10zM12.90 4.30h3.10v3.10h-3.10zM0.00 8.60h3.10v3.10h-3.10zM4.30 8.60h3.10v3.10h-3.10zM8.60 8.60h3.10v3.10h-3.10zM12.90 8.60h3.10v3.10h-3.10zM0.00 12.90h3.10v3.10h-3.10zM4.30 12.90h3.10v3.10h-3.10zM8.60 12.90h3.10v3.10h-3.10zM12.90 12.90h3.10v3.10h-3.10z"/></svg></button>
    </div>
</div>
<?php else : ?>
<nav class="zpp-pg-nav" id="zpp-pg-nav" aria-label="<?php esc_attr_e('Library pagination', 'zappoppop'); ?>"></nav>
<?php endif; ?>
