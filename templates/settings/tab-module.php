<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- template variables are local to the method that includes this file
/* ZPP_MODULES_UI: Module tab, WordPress-Plugins style list (version, description, search, filters, Add Module). */
if (!defined('ABSPATH')) {
    exit;
}
if (!class_exists('ZapPopPop_Admin_Settings')) {
    require_once ZAPPOPPOP_PATH . 'includes/admin/class-admin-settings.php';
}
$__mods = array_merge(
    ZapPopPop_Admin_Settings::get_module_defaults(),
    (isset($settings['modules']) && is_array($settings['modules'])) ? $settings['modules'] : []
);
$__locked = ZapPopPop_Admin_Settings::get_locked_modules();
$__order  = ZapPopPop_Admin_Settings::normalize_module_order(isset($settings['module_order']) ? $settings['module_order'] : ZapPopPop_Admin_Settings::get_module_order());
$__rows   = [];
foreach ($__order as $__k) {
    $__m = ZapPopPop_Modules::get($__k);
    if ($__m && empty($__m['hidden'])) { /* ZAPPOPPOP_MODULE_HIDDEN */
        $__rows[$__k] = ZapPopPop_Modules_Installer::summary($__m);
    }
}
$__active = 0;
foreach ($__rows as $__k => $__r) {
    if (in_array($__k, $__locked, true) || !empty($__mods[$__k])) {
        $__active++;
    }
}
$__total = count($__rows);
?>
<div class="zappoppop-settings-section zpp-mod-tab">
    <div class="zpp-mod-head">
        <h3><?php esc_html_e('Popup Settings Modules', 'zappoppop'); ?></h3>
        <div class="zpp-mod-head-actions">
            <!-- ZAPPOPPOP_CATS_ON_EXTEND: Module Category now lives on the Extend page -->
            <a class="button zpp-mod-add" href="<?php echo esc_url(admin_url('admin.php?page=zappoppop-extend')); ?>"><span class="dashicons dashicons-plus-alt2"></span><?php esc_html_e('Add Module', 'zappoppop'); ?></a>
        </div>
    </div>
    <p class="description"><?php esc_html_e('Drag a module to change its position; the number is its position. Switch a module off to hide it from Popup Settings in the builder. Saved values are kept, so switching it back on restores them. Uninstall and Delete apply immediately; the switches and order need Save Settings. Reload the builder afterwards.', 'zappoppop'); ?></p>

    <div id="zpp-mod-notice" class="zpp-mod-notice" role="status" aria-live="polite" hidden></div>

    <div class="zpp-mod-toolbar">
        <ul class="zpp-mod-filters" id="zpp-mod-filters">
            <li><button type="button" class="is-active" data-filter="all"><?php esc_html_e('All', 'zappoppop'); ?> <span class="count">(<?php echo (int) $__total; ?>)</span></button></li>
            <li><button type="button" data-filter="active"><?php esc_html_e('Active', 'zappoppop'); ?> <span class="count">(<?php echo (int) $__active; ?>)</span></button></li>
            <li><button type="button" data-filter="inactive"><?php esc_html_e('Inactive', 'zappoppop'); ?> <span class="count">(<?php echo (int) ($__total - $__active); ?>)</span></button></li>
        </ul>
        <div class="zpp-mod-search">
            <label class="screen-reader-text" for="zpp-mod-search"><?php esc_html_e('Search installed modules', 'zappoppop'); ?></label>
            <input type="search" id="zpp-mod-search" placeholder="<?php echo esc_attr__('Search installed modules…', 'zappoppop'); ?>" autocomplete="off">
        </div>
    </div>

    <ul class="zappoppop-mod-list" id="zappoppop-mod-list">
        <?php $__i = 0; foreach ($__rows as $__k => $__r) :
            $__i++;
            $__lk = in_array($__k, $__locked, true);
            $__on = $__lk || !empty($__mods[$__k]);
            $__search = $__r['name'] . ' ' . $__r['description'] . ' ' . $__r['author'] . ' ' . $__k;
            ?>
            <li class="zpp-mod-row" data-module="<?php echo esc_attr($__k); ?>" data-name="<?php echo esc_attr($__r['name']); ?>" data-source="<?php echo esc_attr($__r['source']); ?>" data-search="<?php echo esc_attr($__search); ?>">
                <div class="zpp-mod-main">
                    <div class="zpp-mod-title">
                        <span class="dashicons dashicons-menu zappoppop-mod-handle" title="<?php esc_attr_e('Drag to reorder', 'zappoppop'); ?>"></span>
                        <input type="hidden" name="settings[module_order][]" value="<?php echo esc_attr($__k); ?>">
                        <span class="mod-num"><?php echo (int) $__i; ?>.</span>
                        <span class="zpp-mod-name"><?php echo esc_html($__r['name']); ?></span>
                        <?php if ($__lk) : ?><span class="zpp-mod-pill is-lock"><?php esc_html_e('Always enabled', 'zappoppop'); ?></span><?php endif; ?>
                        <?php if ($__r['deprecated']) : ?><span class="zpp-mod-pill is-warn"><?php esc_html_e('Deprecated', 'zappoppop'); ?></span><?php endif; ?>
                    </div>
                    <?php if ($__r['description'] !== '') : ?><p class="zpp-mod-desc"><?php echo esc_html($__r['description']); ?></p><?php endif; ?>
                    <p class="zpp-mod-meta">
                        <?php if ($__r['version'] !== '') : ?><span><?php echo esc_html(sprintf(/* translators: %s: module version. */ __('Version %s', 'zappoppop'), $__r['version'])); ?></span><?php endif; ?>
                        <?php if ($__r['author'] !== '') : ?><span><?php echo esc_html(sprintf(/* translators: %s: module author. */ __('By %s', 'zappoppop'), $__r['author'])); ?></span><?php endif; ?>
                        <span><?php echo $__r['source'] === 'core' ? esc_html__('Built-in', 'zappoppop') : ($__r['source'] === 'pro' ? esc_html__('Pro', 'zappoppop') : esc_html__('Uploaded', 'zappoppop')); ?></span>
                    </p>
                    <?php if (!$__lk) : ?>
                        <p class="zpp-mod-links">
                            <button type="button" class="button-link" data-act="uninstall"><?php esc_html_e('Uninstall', 'zappoppop'); ?></button><span class="sep">|</span><button type="button" class="button-link button-link-delete" data-act="delete"><?php esc_html_e('Delete', 'zappoppop'); ?></button>
                        </p>
                    <?php endif; ?>
                </div>
                <label class="zappoppop-switch-mod<?php echo $__lk ? ' is-locked' : ''; ?>" title="<?php echo esc_attr($__r['name']); ?>">
                    <input type="hidden" name="settings[modules][<?php echo esc_attr($__k); ?>]" value="<?php echo $__lk ? '1' : '0'; ?>">
                    <input type="checkbox" name="settings[modules][<?php echo esc_attr($__k); ?>]" value="1" <?php checked($__on); ?><?php echo $__lk ? ' disabled="disabled" onclick="return false;"' : ''; ?>>
                    <span class="trk"></span>
                </label>
            </li>
        <?php endforeach; ?>
    </ul>
    <p class="zpp-mod-empty" id="zpp-mod-empty" hidden><?php esc_html_e('No modules found.', 'zappoppop'); ?></p>
</div>
