<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- template variables are local to the method that includes this file
if (!defined('ABSPATH')) {
    exit;
}
global $wpdb;
$bar_title   = isset($bar_title) ? $bar_title : __('Dashboard', 'zappoppop');
$bar_icon    = isset($bar_icon) ? $bar_icon : 'dashicons-screenoptions';
$bar_actions = isset($bar_actions) ? $bar_actions : '';
$bar_after_title = isset($bar_after_title) ? $bar_after_title : '';
$bar_items  = $wpdb->prefix . 'zappoppop_items';
$bar_subs   = $wpdb->prefix . 'zappoppop_submissions';
// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- table names are built from $wpdb->prefix and the query takes no user input
$bar_popups = $wpdb->get_results("SELECT i.uid, i.title,
        SUM(CASE WHEN s.id IS NOT NULL AND COALESCE(s.is_read, 0) = 0 THEN 1 ELSE 0 END) AS unread_count,
        SUM(CASE WHEN s.id IS NOT NULL AND COALESCE(s.is_read, 0) <> 0 THEN 1 ELSE 0 END) AS read_count
    FROM {$bar_items} i LEFT JOIN {$bar_subs} s ON s.popup_uid = i.uid
    WHERE i.status <> 'trash'
    GROUP BY i.id, i.uid, i.title ORDER BY i.id DESC LIMIT 30");
// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter

$bar_links = [
    ['zappoppop-dashboard',       __('Dashboard', 'zappoppop'),        'dashicons-screenoptions'],
    ['zappoppop',                 __('Popups', 'zappoppop'),           'dashicons-megaphone'],
    ['zappoppop-builder',         __('Create Popup', 'zappoppop'),     'dashicons-plus-alt'],
    ['zappoppop-submissions',     __('Submissions', 'zappoppop'),      'dashicons-email-alt'],
    ['zappoppop-log',             __('Activity Log', 'zappoppop'),     'dashicons-list-view'],
    ['zappoppop-field-analytics', __('Field Analytics', 'zappoppop'),  'dashicons-chart-pie'],
    ['zappoppop-payments',        __('Transactions', 'zappoppop'),     'dashicons-money-alt'],
    ['zappoppop-library',         __('Popups Library', 'zappoppop'),   'dashicons-category'],
    ['zappoppop-settings',        __('Settings', 'zappoppop'),         'dashicons-admin-generic'],
    ['zappoppop-theme',           __('Theme', 'zappoppop'),            'dashicons-art'],
    ['zappoppop-extend',          __('Extend', 'zappoppop'),           'dashicons-plugins-checked'],
    ['zappoppop-tools',           __('Tools & Export', 'zappoppop'),   'dashicons-admin-tools'],
];
$bar_links = apply_filters('zappoppop_bar_links', $bar_links);
/* ZAPPOPPOP_BAR_MENU_SYNC: Menu dropdown mirrors Settings > Pages (label, order, hidden), live from the registered submenu. */
$bar_icons = [];
foreach ($bar_links as $bl) { $bar_icons[$bl[0]] = $bl[2]; }
$bar_menu = [];
if (class_exists('ZapPopPop_Admin_Menu')) {
    $bar_orig = ZapPopPop_Admin_Menu::get_original_pages();
    if (!empty($bar_orig)) {
        foreach (ZapPopPop_Admin_Menu::resolve_pages($bar_orig, ZapPopPop_Admin_Menu::get_prefs()) as $br) {
            if (!empty($br['hidden'])) { continue; }
            $bar_menu[] = [$br['slug'], $br['label'], isset($bar_icons[$br['slug']]) ? $bar_icons[$br['slug']] : 'dashicons-admin-page'];
        }
    }
}
if (empty($bar_menu)) { $bar_menu = $bar_links; }
$bar_builder = admin_url('admin.php?page=zappoppop-builder');
$bar_logo_custom = class_exists('ZapPopPop_Admin_Theme') ? ZapPopPop_Admin_Theme::logo_url() : '';
$bar_logo        = ($bar_logo_custom !== '') ? $bar_logo_custom : ZAPPOPPOP_URL . 'admin/img/logo-temp.svg';
?>
<div class="zappoppop-dash-bar" role="navigation" aria-label="ZapPopPop">
    <div class="cell logo"><a class="bar-logo-link" href="<?php echo esc_url(admin_url('admin.php?page=zappoppop-dashboard')); ?>" title="<?php echo esc_attr__('Dashboard', 'zappoppop'); ?>" style="display:-webkit-inline-box;display:inline-flex;align-items:center;text-decoration:none;border:0;box-shadow:none;outline:0;"><img src="<?php echo esc_url($bar_logo); ?>" alt="ZapPopPop"<?php echo ($bar_logo_custom !== '') ? ' style="width:auto;max-width:140px;height:28px;object-fit:contain;"' : ''; ?>></a></div>

    <div class="cell">
        <button type="button" class="bar-btn" data-dd="zappoppop-dd-menu" aria-haspopup="true" aria-expanded="false" title="<?php echo esc_attr__('Menu', 'zappoppop'); ?>">
            <span class="dashicons dashicons-menu"></span>
        </button>
        <div class="zappoppop-dd" id="zappoppop-dd-menu">
            <?php foreach ($bar_menu as $ml) : ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=' . $ml[0])); ?>"><span class="dashicons <?php echo esc_attr($ml[2]); ?>"></span><?php echo esc_html($ml[1]); ?></a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="cell">
        <?php /* ZAPPOPPOP_BAR_UNREAD: per-popup submissions dropdown */ ?>
        <button type="button" class="bar-btn" data-dd="zappoppop-dd-popups" aria-haspopup="true" aria-expanded="false" title="<?php echo esc_attr__('Submissions', 'zappoppop'); ?>">
            <span class="dashicons dashicons-email-alt"></span>
        </button>
        <div class="zappoppop-dd" id="zappoppop-dd-popups">
            <?php if (empty($bar_popups)) : ?>
                <div class="dd-empty"><?php echo esc_html__('No popups yet.', 'zappoppop'); ?></div>
            <?php else : foreach ($bar_popups as $bp) :
                $bp_unread = (int) $bp->unread_count;
                $bp_read   = (int) $bp->read_count;
                $bp_name   = ((string) $bp->title !== '') ? $bp->title : $bp->uid;
                $bp_url    = admin_url('admin.php?page=zappoppop-submissions&popup_uid=' . rawurlencode($bp->uid));
                ?>
                <a href="<?php echo esc_url($bp_url); ?>">
                    <span class="dashicons dashicons-email-alt"></span>
                    <span style="flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;"><?php echo esc_html($bp_name); ?></span>
                    <?php if ($bp_unread > 0) : ?>
                        <span title="<?php echo esc_attr__('Unread', 'zappoppop'); ?>" style="background:var(--zpp-c-primary,#c1272d);color:#fff;border-radius:10px;padding:0 7px;font-size:11px;font-weight:700;line-height:18px;"><?php echo (int) $bp_unread; ?></span>
                    <?php endif; ?>
                    <span title="<?php echo esc_attr__('Read', 'zappoppop'); ?>" style="color:var(--zpp-c-muted,#787c82);font-size:11px;white-space:nowrap;">( <?php echo (int) $bp_read; ?> )</span>
                </a>
            <?php endforeach; endif; ?>
            <div class="dd-sep"></div>
            <a href="<?php echo esc_url(admin_url('admin.php?page=zappoppop')); ?>"><span class="dashicons dashicons-list-view"></span><?php echo esc_html__('View all popups', 'zappoppop'); ?></a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=zappoppop-submissions')); ?>"><span class="dashicons dashicons-email-alt"></span><?php echo esc_html__('All submissions', 'zappoppop'); ?></a>
        </div>
    </div>

    <div class="cell">
        <a class="bar-btn" href="<?php echo esc_url($bar_builder); ?>" title="<?php echo esc_attr__('Add New', 'zappoppop'); ?>"><span class="dashicons dashicons-plus-alt2"></span></a>
    </div>

    <div class="cell title">
        <span class="dashicons <?php echo esc_attr($bar_icon); ?>"></span>
        <span><?php echo esc_html($bar_title); ?></span>
    </div>

    <?php ZapPopPop_Promo::render('header_text'); ?>

    <?php if ($bar_after_title !== '' && file_exists($bar_after_title)) { include $bar_after_title; } ?>

    <?php if ($bar_actions !== '' && file_exists($bar_actions)) { include $bar_actions; } ?>
</div>
