<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- template variables are local to the method that includes this file
if (!defined('ABSPATH')) {
    exit;
}
$bar_title   = __('Settings', 'zappoppop');
$bar_icon    = 'dashicons-admin-generic';
$bar_actions = ZAPPOPPOP_PATH . 'templates/settings/bar-actions.php';
?>
<h1 class="screen-reader-text"><?php esc_html_e('ZapPopPop Settings', 'zappoppop'); ?></h1>
<?php include ZAPPOPPOP_PATH . 'templates/partials/dash-bar.php'; ?>
