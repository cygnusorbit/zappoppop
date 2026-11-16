<?php
if (!defined('ABSPATH')) {
    exit;
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound


$builder_url = admin_url('admin.php?page=zappoppop-builder');
?>
<div class="wrap zappoppop-dash zappoppop-overview-wrap">
    <h1 class="screen-reader-text"><?php echo esc_html__('Dashboard', 'zappoppop'); ?></h1>

    <!-- Top Bar (shared partial) -->
    <?php
    $bar_title = __('Dashboard', 'zappoppop');
    $bar_icon  = 'dashicons-screenoptions';
    include ZAPPOPPOP_PATH . 'templates/partials/dash-bar.php';
    ?>

    <!-- Boxes -->
    <div class="zappoppop-dash-grid" id="zappoppop-dash-grid">
        <?php if (!class_exists('ZapPopPop_Ajax_Dash_Layout')) { require_once ZAPPOPPOP_PATH . 'includes/ajax/class-ajax-dash-layout.php'; } $zappoppop_boxes = []; ob_start(); ?>
        <div class="zappoppop-box" id="zappoppop-box-popups">
            <h2><span class="dashicons dashicons-megaphone"></span><?php echo esc_html__('Popups', 'zappoppop'); ?></h2>

            <?php if (empty($dash_recent_popups)) : ?>
                <div class="notice-info">
                    <?php echo esc_html__('No popups yet,', 'zappoppop'); ?>
                    <a href="<?php echo esc_url($builder_url); ?>"><?php echo esc_html__('click here to create one', 'zappoppop'); ?></a>.
                </div>
            <?php else : ?>
                <ul class="zappoppop-pop-list">
                    <?php foreach ($dash_recent_popups as $p) : ?>
                        <li>
                            <span class="t"><?php echo esc_html($p->title !== '' ? $p->title : $p->uid); ?></span>
                            <span class="m"><?php echo (int) $p->submissions; ?> <?php echo esc_html__('entries', 'zappoppop'); ?></span>
                            <span class="a">
                                <a href="<?php echo esc_url(admin_url('admin.php?page=zappoppop-builder&uid=' . rawurlencode($p->uid))); ?>"><?php echo esc_html__('Edit', 'zappoppop'); ?></a>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=zappoppop-submissions&popup_uid=' . rawurlencode($p->uid))); ?>"><?php echo esc_html__('Entries', 'zappoppop'); ?></a>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <a class="zappoppop-btn-red" href="<?php echo esc_url($builder_url); ?>"><?php echo esc_html__('Add New', 'zappoppop'); ?></a>
        </div>

        <?php $zappoppop_boxes['popups'] = ob_get_clean(); ob_start(); ?>
        <!-- Recent Submissions -->
        <div class="zappoppop-box" id="zappoppop-box-recent">
            <h2><span class="dashicons dashicons-email-alt"></span><?php echo esc_html__('Recent Submissions', 'zappoppop'); ?></h2>

            <?php if (empty($dash_recent_subs)) : ?>
                <div class="notice-info"><?php echo esc_html__('No recent entries yet.', 'zappoppop'); ?></div>
            <?php else : ?>
                <ul class="zappoppop-sub-list">
                    <?php foreach ($dash_recent_subs as $s) :
                        $label = (string) $s->email;
                        if ($label === '') {
                            $f = json_decode($s->fields_data, true);
                            if (is_array($f)) {
                                foreach ($f as $fk => $fv) {
                                    if (is_string($fv) && $fv !== '' && $fk[0] !== '_') {
                                        $label = $fv;
                                        break;
                                    }
                                }
                            }
                        }
                        if ($label === '') {
                            $label = '#' . (int) $s->id;
                        }
                        $ts   = strtotime($s->created_at);
                        $when = $ts ? date_i18n('j M Y, g:i A', $ts) : $s->created_at;
                        $href = admin_url('admin.php?page=zappoppop-submissions' . ($s->popup_uid !== '' ? '&popup_uid=' . rawurlencode($s->popup_uid) : ''));
                        $unread = empty($s->is_read);
                        ?>
                        <li class="<?php echo $unread ? 'is-unread' : ''; ?>">
                            <span class="dot" aria-hidden="true"></span>
                            <a class="who" href="<?php echo esc_url($href); ?>"><?php echo esc_html($label); ?></a>
                            <span class="pop"><?php echo esc_html($s->popup_title ? $s->popup_title : __('Untitled Popup', 'zappoppop')); ?></span>
                            <span class="when"><?php echo esc_html($when); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <a class="zappoppop-btn-red" href="<?php echo esc_url(admin_url('admin.php?page=zappoppop-submissions')); ?>"><?php echo esc_html__('View all submissions', 'zappoppop'); ?></a>
        </div>

        <?php $zappoppop_boxes['recent'] = ob_get_clean(); ?>
        <?php
        ob_start(); ZapPopPop_Promo::render('dashboard_slider'); $zappoppop_promo_html = ob_get_clean(); // empty when hidden
        $zappoppop_present = array_keys($zappoppop_boxes);
        if ($zappoppop_promo_html !== '') { $zappoppop_present[] = 'promo'; }
        foreach (ZapPopPop_Ajax_Dash_Layout::get_columns($zappoppop_present) as $zappoppop_ci => $zappoppop_col) : ?>
        <div class="zappoppop-dash-col" data-col="<?php echo (int) $zappoppop_ci; ?>"><?php foreach ($zappoppop_col as $zappoppop_bk) {
            if ($zappoppop_bk === 'promo') { echo $zappoppop_promo_html; continue; } // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside ZapPopPop_Promo::render()
            if (isset($zappoppop_boxes[$zappoppop_bk])) { echo wp_kses_post( $zappoppop_boxes[$zappoppop_bk] ); }
        } ?></div>
        <?php endforeach; ?>
    </div>
</div>
