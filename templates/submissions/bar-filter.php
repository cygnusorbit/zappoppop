<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- template variables are local to the method that includes this file
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="cell subs-filter">
    <select id="zappoppop-subs-popup-filter" aria-label="<?php echo esc_attr__('Filter by popup', 'zappoppop'); ?>" data-base="<?php echo esc_url(admin_url('admin.php?page=zappoppop-submissions')); ?>">
        <option value=""><?php echo esc_html__('All popups', 'zappoppop'); ?></option>
        <?php foreach ((array) $all_popups as $__p) : ?>
            <option value="<?php echo esc_attr($__p->uid); ?>" <?php selected($sel_uid, $__p->uid); ?>><?php echo esc_html($__p->title ? $__p->title : $__p->uid); ?></option>
        <?php endforeach; ?>
    </select>
</div>
