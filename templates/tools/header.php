<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- template variables are local to the method that includes this file
if (!defined('ABSPATH')) {
    exit;
}
$bar_title   = __('Tools & Export', 'zappoppop');
$bar_icon    = 'dashicons-admin-tools';
$bar_actions = ZAPPOPPOP_PATH . 'templates/tools/bar-actions.php';
?>
<h1 class="screen-reader-text"><?php esc_html_e('Tools, Diagnostics & Portability', 'zappoppop'); ?></h1>
<?php include ZAPPOPPOP_PATH . 'templates/partials/dash-bar.php'; ?>
