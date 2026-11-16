<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap zappoppop-dashboard-wrap zappoppop-submissions-wrap" style="width:100%;padding-right:30px;max-width:none;box-sizing:border-box;">
    <?php include ZAPPOPPOP_PATH . 'templates/submissions/context.php'; ?>
    <!-- Top Action & Search Header -->
    <?php include ZAPPOPPOP_PATH . 'templates/submissions/header.php'; ?>

    <!-- Submissions List Table -->
    <?php include ZAPPOPPOP_PATH . 'templates/submissions/table.php'; ?>

    <?php include ZAPPOPPOP_PATH . 'templates/submissions/modal-table-options.php'; ?>
    <?php include ZAPPOPPOP_PATH . 'templates/submissions/modal-export.php'; ?>
    <!-- Lead Submission Detail & Print Receipt Modal -->
    <?php include ZAPPOPPOP_PATH . 'templates/submissions/modal-editor.php'; ?>
</div>
