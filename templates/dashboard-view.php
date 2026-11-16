<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- template variables are local to the method that includes this file
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap zappoppop-dashboard-wrap" style="width: 100%; padding-right: 30px; max-width: none; box-sizing: border-box;">
    <!-- Top Bar (shared with Dashboard) -->
    <?php
    $bar_title   = __('Popups', 'zappoppop');
    $bar_icon    = 'dashicons-megaphone';
    $bar_actions = ZAPPOPPOP_PATH . 'templates/dashboard/bar-actions.php';
    ?>
    <div class="zappoppop-dash" style="margin:0 0 20px;max-width:none;">
        <?php include ZAPPOPPOP_PATH . 'templates/partials/dash-bar.php'; ?>
    </div>

    <!-- Dashboard Header & Top Actions -->
    <?php include ZAPPOPPOP_PATH . 'templates/dashboard/header.php'; ?>

    <?php /* ZPP_KPI_HIDDEN: KPI cards disabled on the Popups page. To restore, uncomment the next line. */ ?>
    <?php // include ZAPPOPPOP_PATH . 'templates/dashboard/kpi-cards.php'; ?>

    <!-- Campaigns List Table -->
    <?php include ZAPPOPPOP_PATH . 'templates/dashboard/table.php'; ?>

    <!-- Embed Code Snippets Modal Dialog -->
    <?php include ZAPPOPPOP_PATH . 'templates/dashboard/modal-embed.php'; ?>
</div>
