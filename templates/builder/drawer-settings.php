<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<!-- Slide-Out Backdrop -->
<div id="zappoppop-settings-backdrop" class="zappoppop-settings-backdrop"></div>

<!-- Hardware-Accelerated Slide-In Popup Settings Drawer -->
<div id="zappoppop-settings-drawer" class="zappoppop-settings-drawer">
    <div class="zappoppop-settings-drawer-header" style="display:flex;align-items:center;justify-content:space-between;padding:14px 18px;background:#0f172a;color:#ffffff;flex-shrink:0;">
        <h3 class="zappoppop-settings-drawer-title" style="margin:0;font-size:15px;font-weight:700;color:#ffffff !important;line-height:1.3;"><?php esc_html_e('Popup Settings', 'zappoppop'); ?></h3>
        <button type="button" id="zappoppop-settings-drawer-close" style="background:transparent;border:none;color:#94a3b8;font-size:22px;cursor:pointer;line-height:1;">&times;</button>
    </div>

    <div class="zappoppop-settings-drawer-scroll" style="flex:1;overflow-y:auto;padding:16px;">
        <div class="zappoppop-accordion-group">
            
            <?php
            /* ZAPPOPPOP_MODULE_ENGINE_DRAWER: built-in accordions (acc-*.php) and declarative modules share one order */
            $zappoppop_acc_files = glob(__DIR__ . '/settings/acc-*.php');
            $zappoppop_acc_decl  = class_exists('ZapPopPop_Modules_Engine') ? ZapPopPop_Modules_Engine::installed_ui() : [];
            if (!empty($zappoppop_acc_files) || !empty($zappoppop_acc_decl)) {
                if (!class_exists('ZapPopPop_Admin_Settings')) {
                    require_once ZAPPOPPOP_PATH . 'includes/admin/class-admin-settings.php';
                }
                $zappoppop_acc_entries = [];
                foreach ((array) $zappoppop_acc_files as $zappoppop_acc_f) {
                    $zappoppop_acc_entries[basename($zappoppop_acc_f, '.php')] = $zappoppop_acc_f;
                }
                foreach ($zappoppop_acc_decl as $zappoppop_acc_slug => $zappoppop_acc_m) {
                    if (!isset($zappoppop_acc_entries[$zappoppop_acc_slug])) {
                        $zappoppop_acc_entries[$zappoppop_acc_slug] = null;
                    }
                }
                ksort($zappoppop_acc_entries);
                /* Order is controlled globally in Settings > Module */
                $zappoppop_acc_order = array_flip(ZapPopPop_Admin_Settings::get_module_order());
                uksort($zappoppop_acc_entries, function ($ka, $kb) use ($zappoppop_acc_order) {
                    $pa = isset($zappoppop_acc_order[$ka]) ? $zappoppop_acc_order[$ka] : PHP_INT_MAX;
                    $pb = isset($zappoppop_acc_order[$kb]) ? $zappoppop_acc_order[$kb] : PHP_INT_MAX;
                    return $pa === $pb ? strcmp($ka, $kb) : ($pa < $pb ? -1 : 1);
                });
                foreach ($zappoppop_acc_entries as $zappoppop_acc_key => $zappoppop_acc_file) {
                    $zappoppop_acc_on   = ZapPopPop_Admin_Settings::module_enabled($zappoppop_acc_key);
                    $zappoppop_acc_trusted = ($zappoppop_acc_file === null); /* engine markup is escaped at build time */
                    if ($zappoppop_acc_on && !$zappoppop_acc_trusted) {
                        include $zappoppop_acc_file;
                        continue;
                    }
                    ob_start();
                    if ($zappoppop_acc_trusted) {
                        echo ZapPopPop_Modules_Engine::render_accordion($zappoppop_acc_decl[$zappoppop_acc_key]); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    } else {
                        include $zappoppop_acc_file;
                    }
                    $zappoppop_acc_html = ob_get_clean();
                    if (!$zappoppop_acc_on) {
                        // Disabled: keep the markup (so the save flush never wipes stored values) but hide it.
                        $zappoppop_acc_html = preg_replace(
                            '/<div class="zappoppop-acc-item"/',
                            '<div class="zappoppop-acc-item zappoppop-module-off" data-module="' . esc_attr($zappoppop_acc_key) . '" style="display:none !important;"',
                            $zappoppop_acc_html, 1, $zappoppop_acc_hit
                        );
                        if (empty($zappoppop_acc_hit)) {
                            $zappoppop_acc_html = '<div class="zappoppop-module-off" style="display:none !important;">' . $zappoppop_acc_html . '</div>';
                        }
                    }
                    echo $zappoppop_acc_trusted ? $zappoppop_acc_html : wp_kses_post( $zappoppop_acc_html ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                }
            }
            ?>

        </div>
        <?php ZapPopPop_Promo::render('builder_pro_settings'); /* ZAPPOPPOP_PRO_LOCK: static locked rows, text from defaults/promo.json */ ?>
        <div class="zappoppop-defaults-box" style="margin-top:16px;padding:12px;border:1px dashed #cbd5e1;border-radius:6px;background:#f8fafc;">
            <p style="margin:0 0 8px;font-size:12px;line-height:1.4;color:#475569;"><?php esc_html_e('Use this popup\'s canvas and popup settings (not its layers) as the starting point for every new popup.', 'zappoppop'); ?></p>
            <button type="button" class="button" data-zpp-defaults-save="popup"><?php esc_html_e('Save as default for new popups', 'zappoppop'); ?></button>
        </div>
        <?php ZapPopPop_Promo::render('builder_box'); ?>
    </div>
</div>
<?php /* Decomposed Phase 4 Settings Tokens (templates/builder/settings/acc-*.php): set-exit-intent-sensitivity, set-trigger-scroll-depth, set-canvas-autoclose */ ?>
