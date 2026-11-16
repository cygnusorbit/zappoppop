<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- template variables are local to the method that includes this file
if (!defined('ABSPATH')) {
    exit;
}
$zappoppop_total     = isset($submissions) ? count($submissions) : 0;
$zappoppop_confirmed = 0;
$zappoppop_unread    = 0;
if (!empty($submissions)) {
    foreach ($submissions as $__s) {
        if (empty($__s->is_read)) {
            $zappoppop_unread++;
        }
        if ($__s->status === 'confirmed') {
            $zappoppop_confirmed++;
        }
    }
}
$zappoppop_pending = $zappoppop_total - $zappoppop_confirmed;
?>
<?php ZapPopPop_Inline_Assets::style('submissions-header'); ?><?php
$bar_title   = __('Submissions', 'zappoppop');
$bar_icon    = 'dashicons-email-alt';
$bar_actions = ZAPPOPPOP_PATH . 'templates/submissions/bar-actions.php';
$bar_popup_page = 'zappoppop-submissions';
?>
<h1 class="screen-reader-text"><?php esc_html_e('Submissions', 'zappoppop'); ?></h1>
<div class="zappoppop-dash" style="margin:0 0 20px;max-width:none;">
    <?php include ZAPPOPPOP_PATH . 'templates/partials/dash-bar.php'; ?>
</div>
<div id="zappoppop-subs-notice" style="display:none;" class="notice is-dismissible"></div>
<?php
$zappoppop_tab_base   = admin_url('admin.php?page=zappoppop-submissions');
$zappoppop_tab_counts = [];
$zappoppop_tab_total  = 0;
if (!empty($all_popups) || true) {
    $__rows = $wpdb->get_results("SELECT popup_uid, COUNT(*) AS c FROM {$wpdb->prefix}zappoppop_submissions GROUP BY popup_uid");
    foreach ((array) $__rows as $__r) {
        $zappoppop_tab_counts[(string) $__r->popup_uid] = (int) $__r->c;
        $zappoppop_tab_total += (int) $__r->c;
    }
}
?>
<div class="zappoppop-subs-popup-tabs" id="zappoppop-subs-popup-tabs" role="tablist" aria-label="<?php echo esc_attr__('Select popup', 'zappoppop'); ?>">
    <a role="tab" class="zappoppop-ptab<?php echo $sel_uid === '' ? ' active' : ''; ?>" aria-selected="<?php echo $sel_uid === '' ? 'true' : 'false'; ?>" href="<?php echo esc_url($zappoppop_tab_base); ?>"><?php esc_html_e('All popups', 'zappoppop'); ?> <small>(<?php echo (int) $zappoppop_tab_total; ?>)</small></a>
    <?php foreach ((array) $all_popups as $__p) : $__on = ($sel_uid === $__p->uid); ?>
        <a role="tab" class="zappoppop-ptab<?php echo $__on ? ' active' : ''; ?>" aria-selected="<?php echo $__on ? 'true' : 'false'; ?>" href="<?php echo esc_url(add_query_arg('popup_uid', rawurlencode($__p->uid), $zappoppop_tab_base)); ?>"><?php echo esc_html($__p->title ? $__p->title : $__p->uid); ?> <small>(<?php echo isset($zappoppop_tab_counts[$__p->uid]) ? (int) $zappoppop_tab_counts[$__p->uid] : 0; ?>)</small></a>
    <?php endforeach; ?>
</div>
<div class="zappoppop-subs-toolbar">
    <div class="zappoppop-subs-tabs" id="zappoppop-subs-tabs">
        <button type="button" class="active" data-filter="all"><?php esc_html_e('All', 'zappoppop'); ?> <small>(<span data-count="all"><?php echo (int) $zappoppop_total; ?></span>)</small></button>
        <button type="button" data-filter="unread"><?php esc_html_e('Unread', 'zappoppop'); ?> <small>(<span data-count="unread"><?php echo (int) $zappoppop_unread; ?></span>)</small></button>
        <button type="button" data-filter="pending"><?php esc_html_e('Pending', 'zappoppop'); ?> <small>(<span data-count="pending"><?php echo (int) $zappoppop_pending; ?></span>)</small></button>
        <button type="button" data-filter="confirmed"><?php esc_html_e('Confirmed', 'zappoppop'); ?> <small>(<span data-count="confirmed"><?php echo (int) $zappoppop_confirmed; ?></span>)</small></button>
    </div>
    <div class="zappoppop-subs-searchbox">
        <span class="dashicons dashicons-tag" style="color:#50575e;"></span>
        <input type="search" id="zappoppop-subs-search" placeholder="">
        <button type="button" class="button button-primary" id="zappoppop-subs-search-btn"><?php esc_html_e('Search', 'zappoppop'); ?></button>
    </div>
</div>
