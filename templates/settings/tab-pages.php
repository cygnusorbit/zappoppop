<?php
if (!defined('ABSPATH')) {
    exit;
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

$__pg_rows = class_exists('ZapPopPop_Admin_Menu')
    ? ZapPopPop_Admin_Menu::resolve_pages(ZapPopPop_Admin_Menu::get_original_pages(), ZapPopPop_Admin_Menu::get_prefs())
    : [];
$__pg_pin = class_exists('ZapPopPop_Admin_Menu') ? ZapPopPop_Admin_Menu::pinned() : [];
?>
<?php ZapPopPop_Inline_Assets::style('settings-tab-pages'); ?><div class="zappoppop-settings-section">
    <h3><?php esc_html_e('Pages', 'zappoppop'); ?></h3>
    <p class="description"><?php esc_html_e('Rename or hide ZapPopPop pages in the admin menu (the order is fixed). The list syncs automatically with the menu. Hidden pages remain reachable by URL. Save settings, then reload to see the menu update.', 'zappoppop'); ?></p>

    <?php if (empty($__pg_rows)) : ?>
        <p><em><?php esc_html_e('No menu pages detected.', 'zappoppop'); ?></em></p>
    <?php else : ?>
    <ul id="zappoppop-pages-list" class="zappoppop-pg-list">
        <?php foreach ($__pg_rows as $__i => $__r) :
            $__is_pin = in_array($__r['slug'], $__pg_pin, true);
            $__n = 'settings[menu_pages][' . esc_attr($__r['slug']) . ']';
        ?>
        <li data-slug="<?php echo esc_attr($__r['slug']); ?>" data-idx="<?php echo (int) $__r['idx']; ?>" data-default="<?php echo esc_attr($__r['default']); ?>" class="<?php echo $__r['hidden'] ? 'is-hidden-page' : ''; ?>">
            <span class="dashicons dashicons-menu zpp-pg-handle" title="<?php esc_attr_e('Drag to reorder', 'zappoppop'); ?>"></span>
            <input type="text" class="regular-text zpp-pg-label" maxlength="60"
                   name="<?php echo esc_attr( $__n ); ?>[label]"
                   value="<?php echo esc_attr($__r['custom'] ? $__r['label'] : ''); ?>"
                   placeholder="<?php echo esc_attr($__r['default']); ?>">
            <code class="zpp-pg-slug"><?php echo esc_html($__r['slug']); ?></code>
            <span class="zpp-pg-move">
                <button type="button" class="button button-small zpp-pg-up" aria-label="<?php esc_attr_e('Move up', 'zappoppop'); ?>">&uarr;</button>
                <button type="button" class="button button-small zpp-pg-down" aria-label="<?php esc_attr_e('Move down', 'zappoppop'); ?>">&darr;</button>
            </span>
            <label class="zpp-pg-hide">
                <input type="hidden" name="<?php echo esc_attr( $__n ); ?>[hidden]" value="0">
                <input type="checkbox" class="zpp-pg-hidden-cb" name="<?php echo esc_attr( $__n ); ?>[hidden]" value="1" <?php checked(!empty($__r['hidden'])); ?> <?php disabled($__is_pin); ?>>
                <?php echo $__is_pin ? esc_html__('Always visible', 'zappoppop') : esc_html__('Hide', 'zappoppop'); ?>
            </label>
            <input type="hidden" class="zpp-pg-order" name="<?php echo esc_attr( $__n ); ?>[order]" value="<?php echo (int) ($__i + 1); ?>">
        </li>
        <?php endforeach; ?>
    </ul>
    <p><button type="button" class="button" id="zappoppop-pages-reset"><?php esc_html_e('Reset to default', 'zappoppop'); ?></button></p>
    <?php endif; ?>
</div>
