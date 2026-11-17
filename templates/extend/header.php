<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- template variables are local to the method that includes this file
if (!defined('ABSPATH')) {
    exit;
}
$bar_title   = __('Modules', 'zappoppop');
$bar_icon    = 'dashicons-plugins-checked';
$bar_actions = ZAPPOPPOP_PATH . 'templates/extend/bar-actions.php';
?>
<h1 class="screen-reader-text"><?php esc_html_e('Modules: add and manage modules', 'zappoppop'); ?></h1>
<?php include ZAPPOPPOP_PATH . 'templates/partials/dash-bar.php'; ?>
