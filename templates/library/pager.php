<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- template variables are local to the method that includes this file
if (!defined('ABSPATH')) {
    exit;
}
$zpp_pg_pos = isset($zpp_pg_pos) ? $zpp_pg_pos : 'top';
if ($zpp_pg_pos === 'top') : ?>
<div class="zpp-pg-bar" id="zpp-pg-bar">
    <span class="zpp-pg-info" id="zpp-pg-info" aria-live="polite"></span>
    <div class="zpp-pg-views" role="group" aria-label="<?php esc_attr_e('Cards per page', 'zappoppop'); ?>">
        <span class="zpp-pg-label"><?php esc_html_e('View', 'zappoppop'); ?></span>
        <button type="button" class="zpp-pg-view" data-cols="3" aria-pressed="true">3 &times; 3</button>
        <button type="button" class="zpp-pg-view" data-cols="4" aria-pressed="false">4 &times; 4</button>
        <button type="button" class="zpp-pg-view" data-cols="5" aria-pressed="false">5 &times; 5</button>
    </div>
</div>
<?php else : ?>
<nav class="zpp-pg-nav" id="zpp-pg-nav" aria-label="<?php esc_attr_e('Library pagination', 'zappoppop'); ?>"></nav>
<?php endif; ?>
