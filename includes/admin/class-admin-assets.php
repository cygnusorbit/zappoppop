<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('ZapPopPop_Libraries')) {
    require_once ZAPPOPPOP_PATH . 'includes/class-zappoppop-libraries.php';
}

class ZapPopPop_Admin_Assets {
    public function __construct() {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    /* ZPP_LAYER_FACTORY: built-in defaults for NEW layers (defaults/layer-defaults.json + Settings > Builder), localized to the builder as zappoppop_vars.layer_factory */
    protected static function layer_factory_for_builder() {
        if (!class_exists('ZapPopPop_Saved_Defaults') || !method_exists('ZapPopPop_Saved_Defaults', 'factory_layers')) { return array(); }
        $f = ZapPopPop_Saved_Defaults::factory_layers();
        return is_array($f) ? $f : array();
    }

    /* Cache-friendly asset version: plugin version; adds a cache-buster only when WP_DEBUG is on. */
    protected static function ver() {
        $v = defined('ZAPPOPPOP_VERSION') ? ZAPPOPPOP_VERSION : '1.0';
        return (defined('WP_DEBUG') && WP_DEBUG) ? $v . '.' . time() : $v;
    }

    /** Shared module-manager UI (Module tab + Extend page): stylesheet, API script and its localized data. */
    protected function enqueue_modules_ui($ver, array $css_deps = [], array $extra = []) {
        wp_enqueue_style('dashicons');
        wp_enqueue_style('zappoppop-modules-css', ZAPPOPPOP_URL . 'admin/css/modules.css', $css_deps, $ver);
        wp_enqueue_style('zappoppop-module-categories-css', ZAPPOPPOP_URL . 'admin/css/module-categories.css', ['zappoppop-modules-css'], $ver);
        wp_enqueue_script('zappoppop-modules-api-js', ZAPPOPPOP_URL . 'admin/js/modules/modules-api.js', ['jquery'], $ver, true);
        wp_localize_script('zappoppop-modules-api-js', 'zappoppop_modules_vars', ZapPopPop_Modules_Installer::js_vars($extra));
    }

    public function enqueue_assets($hook) {
        $zappoppop_page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';
        if (strpos($hook, 'zappoppop') === false && strpos($zappoppop_page, 'zappoppop') === false) {
            return;
        }

        /* ZAPPOPPOP_P18_INLINE_JS: confirm / navigate / no-op behaviours that used to be inline onclick/onchange/onsubmit */
        wp_enqueue_script('zappoppop-admin-inline-js', ZAPPOPPOP_URL . 'admin/js/zappoppop-admin-inline.js', [], self::ver(), true);

        $page = isset($_GET['page']) ? sanitize_key($_GET['page']) : '';
        $current_uid = isset($_GET['uid']) ? sanitize_text_field(wp_unslash($_GET['uid'])) : '';

        // 0. Global Theme (all ZapPopPop admin pages) + Theme page assets
        if (class_exists('ZapPopPop_Admin_Theme')) {
            ZapPopPop_Admin_Theme::enqueue_global();
            if ($page === ZapPopPop_Admin_Theme::PAGE) {
                ZapPopPop_Admin_Theme::enqueue_page();
            }
        }
        ZapPopPop_Promo::enqueue();

        // Shared general configuration
        $shared_payload = [
            'ajax_url'    => admin_url('admin-ajax.php'),
            'nonce'       => wp_create_nonce('zappoppop_admin_nonce'),
            'current_uid' => $current_uid
        ];

        // 1. Dashboard Assets
        if ($page === 'zappoppop' || $hook === 'toplevel_page_zappoppop') {
            wp_enqueue_style('zappoppop-dashboard-css', ZAPPOPPOP_URL . 'admin/css/dashboard.css', [], self::ver());
            wp_enqueue_script('zappoppop-dashboard-actions-js', ZAPPOPPOP_URL . 'admin/js/dashboard/dashboard-actions.js', ['jquery'], self::ver(), true);
            wp_enqueue_script('zappoppop-dashboard-import-js', ZAPPOPPOP_URL . 'admin/js/dashboard/dashboard-import.js', ['jquery'], self::ver(), true);
            wp_enqueue_script('zappoppop-dashboard-embed-js', ZAPPOPPOP_URL . 'admin/js/dashboard/dashboard-embed.js', ['jquery'], self::ver(), true);
            wp_enqueue_script('zappoppop-dashboard-table-js', ZAPPOPPOP_URL . 'admin/js/dashboard/dashboard-table.js', ['jquery'], self::ver(), true);
            wp_enqueue_script('zappoppop-dashboard-search-js', ZAPPOPPOP_URL . 'admin/js/dashboard/dashboard-search.js', ['jquery', 'zappoppop-dashboard-table-js'], self::ver(), true);
            wp_enqueue_script('zappoppop-dashboard-js', ZAPPOPPOP_URL . 'admin/js/dashboard.js', [
                'jquery',
                'zappoppop-dashboard-actions-js',
                'zappoppop-dashboard-import-js',
                'zappoppop-dashboard-embed-js',
                'zappoppop-dashboard-table-js',
                'zappoppop-dashboard-search-js'
            ], self::ver(), true);

            wp_localize_script('zappoppop-dashboard-js', 'zappoppop_vars', $shared_payload);
            wp_enqueue_script('zappoppop-dashboard-quickedit-js', ZAPPOPPOP_URL . 'admin/js/dashboard/dashboard-quickedit.js', ['jquery', 'zappoppop-dashboard-js'], self::ver(), true);
            wp_enqueue_script('zappoppop-dashboard-bulk-js', ZAPPOPPOP_URL . 'admin/js/dashboard/dashboard-bulk.js', ['jquery', 'zappoppop-dashboard-js'], self::ver(), true);
        }

        // 2. Visual Builder Assets (Equipped with Typography and Custom Fonts)
        if ($page === 'zappoppop-builder') {
            wp_enqueue_style('zappoppop-builder-css', ZAPPOPPOP_URL . 'admin/css/builder.css', [], self::ver());
            /* Builder boot: hides the page until styles are ready (head, render-blocking) and styles the loader */
            wp_enqueue_style('zappoppop-fouc-guard', ZAPPOPPOP_URL . 'admin/css/inline/builder-fouc-guard.css', [], self::ver());
            wp_enqueue_style('zappoppop-boot-style', ZAPPOPPOP_URL . 'admin/css/inline/builder-boot-style.css', [], self::ver());
            $zappoppop_st_file = ZAPPOPPOP_PATH . 'admin/css/builder/save-template.css';
            wp_enqueue_style('zappoppop-builder-save-template-css', ZAPPOPPOP_URL . 'admin/css/builder/save-template.css', ['zappoppop-builder-css'], file_exists($zappoppop_st_file) ? (string) filemtime($zappoppop_st_file) : self::ver());
            $zappoppop_sm_css = ZAPPOPPOP_PATH . 'admin/css/builder/save-menu.css';
            wp_enqueue_style('zappoppop-builder-save-menu-css', ZAPPOPPOP_URL . 'admin/css/builder/save-menu.css', ['zappoppop-builder-save-template-css'], file_exists($zappoppop_sm_css) ? (string) filemtime($zappoppop_sm_css) : self::ver());
            $zappoppop_ts_bld = ZAPPOPPOP_PATH . 'admin/css/library/theme-sync-modals.css';
            wp_enqueue_style('zappoppop-builder-theme-modals-css', ZAPPOPPOP_URL . 'admin/css/library/theme-sync-modals.css', ['zappoppop-builder-save-template-css'], file_exists($zappoppop_ts_bld) ? (string) filemtime($zappoppop_ts_bld) : self::ver());
            $zappoppop_ap_css = ZAPPOPPOP_PATH . 'admin/css/zappoppop-alpha-picker.css';
            $zappoppop_ap_js  = ZAPPOPPOP_PATH . 'admin/js/zappoppop-alpha-picker.js';
            wp_enqueue_style('zappoppop-alpha-picker-css', ZAPPOPPOP_URL . 'admin/css/zappoppop-alpha-picker.css', ['zappoppop-builder-css'], file_exists($zappoppop_ap_css) ? (string) filemtime($zappoppop_ap_css) : self::ver());
            wp_enqueue_script('zappoppop-color-throttle-js', ZAPPOPPOP_URL . 'admin/js/zappoppop-color-throttle.js', [], file_exists(ZAPPOPPOP_PATH . 'admin/js/zappoppop-color-throttle.js') ? (string) filemtime(ZAPPOPPOP_PATH . 'admin/js/zappoppop-color-throttle.js') : self::ver(), true); /* ZAPPOPPOP_COLOR_THROTTLE */
            wp_enqueue_script('zappoppop-alpha-picker-js', ZAPPOPPOP_URL . 'admin/js/zappoppop-alpha-picker.js', [], file_exists($zappoppop_ap_js) ? (string) filemtime($zappoppop_ap_js) : self::ver(), true);
            wp_localize_script('zappoppop-alpha-picker-js', 'zappoppop_alpha_vars', ['i18n' => [
                'pick'    => __('Pick colour', 'zappoppop'),
                'hex'     => __('Colour value', 'zappoppop'),
                'sv'      => __('Saturation and brightness', 'zappoppop'),
                'hue'     => __('Hue', 'zappoppop'),
                'opacity' => __('Opacity', 'zappoppop'),
                'clear'   => __('Transparent', 'zappoppop'),
                'revert'  => __('Revert', 'zappoppop'),
                'eyedrop' => __('Pick colour from screen', 'zappoppop'), /* ZPP_CP_EYEDROP */
            ]]);
            wp_enqueue_script('jquery-ui-draggable');
            wp_enqueue_script('jquery-ui-resizable');
            wp_enqueue_script('jquery-ui-sortable');

            // Enqueue active typography & Font Awesome for builder stage
            ZapPopPop_Libraries::enqueue_builder_libraries();
            wp_enqueue_style('zappoppop-animations-core-css', ZAPPOPPOP_URL . 'public/css/animations-core.css', [], (defined('ZAPPOPPOP_VERSION') ? ZAPPOPPOP_VERSION : '1.0'));
            /* ZPP_STAGE_DEFAULT_FONT: the stage inherits the same global default font/weight/size as the front wrapper, so Edit Mode matches Live Preview */
            if (function_exists('zappoppop_get_setting')) {
                $zappoppop_sf = '';
                $zappoppop_ff = (string) zappoppop_get_setting('default_font', 'inherit');
                if ($zappoppop_ff !== '' && $zappoppop_ff !== 'inherit' && preg_match('/^[A-Za-z0-9 ,\'"_-]+$/', $zappoppop_ff)) { $zappoppop_sf .= 'font-family:' . $zappoppop_ff . ';'; }
                /* ZPP_STAGE_THEME_FONT: with Default Font = inherit the popup takes the site theme's body font on the front (and in Live Preview); resolve it so the stage matches */
                if ($zappoppop_ff === '' || $zappoppop_ff === 'inherit') {
                    if (function_exists('wp_get_global_styles')) {
                        $zappoppop_tf = wp_get_global_styles(array('typography', 'fontFamily'));
                        if (is_string($zappoppop_tf) && preg_match('/var\(--wp--preset--font-family--([a-z0-9-]+)\)/i', $zappoppop_tf, $zappoppop_m) && function_exists('wp_get_global_settings')) {
                            $zappoppop_fams = wp_get_global_settings(array('typography', 'fontFamilies'));
                            if (is_array($zappoppop_fams)) {
                                foreach ($zappoppop_fams as $zappoppop_list) {
                                    if (!is_array($zappoppop_list)) { continue; }
                                    foreach ($zappoppop_list as $zappoppop_f) {
                                        if (is_array($zappoppop_f) && isset($zappoppop_f['slug'], $zappoppop_f['fontFamily']) && $zappoppop_f['slug'] === $zappoppop_m[1]) { $zappoppop_tf = (string) $zappoppop_f['fontFamily']; break 2; }
                                    }
                                }
                            }
                        }
                        if (is_string($zappoppop_tf) && $zappoppop_tf !== '' && preg_match('/^[A-Za-z0-9 ,\'"_-]+$/', $zappoppop_tf)) {
                            $zappoppop_sf .= 'font-family:' . $zappoppop_tf . ';';
                            if (function_exists('wp_print_font_faces')) { add_action('admin_head', 'wp_print_font_faces', 20); }
                        }
                    }
                }
                $zappoppop_fw = (string) zappoppop_get_setting('default_font_weight', 'inherit');
                if (in_array($zappoppop_fw, array('300', '400', '500', '600', '700'), true)) { $zappoppop_sf .= 'font-weight:' . $zappoppop_fw . ';'; }
                $zappoppop_fs = (int) zappoppop_get_setting('default_font_size', 0);
                if ($zappoppop_fs > 0 && $zappoppop_fs <= 200) { $zappoppop_sf .= 'font-size:' . $zappoppop_fs . 'px;'; }
                if (zappoppop_get_setting('default_font_italic', 0)) { $zappoppop_sf .= 'font-style:italic;'; }
                if ($zappoppop_sf !== '') { wp_add_inline_style('zappoppop-builder-css', '#zappoppop-canvas-box{' . $zappoppop_sf . '}'); }
            }
            wp_enqueue_script('zappoppop-builder-animation-js', ZAPPOPPOP_URL . 'admin/js/builder/builder-animation.js', ['jquery', 'zappoppop-builder-core-js'], self::ver(), true);

            if (function_exists('wp_enqueue_media')) { wp_enqueue_media(); }
        wp_enqueue_script('zappoppop-builder-element-ext-js', ZAPPOPPOP_URL . 'admin/js/builder/builder-element-ext.js', [], self::ver(), true);
        wp_enqueue_script('zappoppop-builder-core-js', ZAPPOPPOP_URL . 'admin/js/builder/builder-core.js', ['jquery', 'zappoppop-builder-element-ext-js'], self::ver(), true);
            wp_enqueue_script('zappoppop-builder-spawn-place-js', ZAPPOPPOP_URL . 'admin/js/builder/builder-spawn-place.js', [], self::ver(), true);
            wp_enqueue_script('zappoppop-builder-layer-factory-js', ZAPPOPPOP_URL . 'admin/js/builder/builder-layer-factory.js', ['zappoppop-builder-core-js'], self::ver(), true); /* ZPP_LAYER_FACTORY */
            wp_enqueue_script('zappoppop-builder-canvas-js', ZAPPOPPOP_URL . 'admin/js/builder/builder-canvas.js', ['jquery', 'jquery-ui-draggable', 'jquery-ui-resizable', 'zappoppop-builder-core-js', 'jquery-ui-droppable', 'zappoppop-builder-spawn-place-js', 'zappoppop-builder-layer-factory-js'], self::ver(), true);
            wp_enqueue_script('zappoppop-builder-layers-js', ZAPPOPPOP_URL . 'admin/js/builder/builder-layers.js', ['jquery', 'jquery-ui-sortable', 'zappoppop-builder-core-js'], self::ver(), true);
            wp_enqueue_script('zappoppop-style-toggles-js', ZAPPOPPOP_URL . 'admin/js/builder/registry/registry-style-toggles.js', ['jquery', 'zappoppop-builder-core-js', 'zappoppop-builder-canvas-js'], self::ver(), true);
            wp_enqueue_script('zappoppop-style-stable-js', ZAPPOPPOP_URL . 'admin/js/builder/registry/registry-style-stable.js', ['jquery', 'zappoppop-builder-core-js', 'zappoppop-builder-canvas-js'], self::ver(), true);
            wp_enqueue_script('zappoppop-builder-hotkeys-js', ZAPPOPPOP_URL . 'admin/js/builder/builder-hotkeys.js', ['jquery', 'zappoppop-builder-core-js', 'zappoppop-builder-canvas-js', 'zappoppop-builder-layers-js'], self::ver(), true);
            wp_enqueue_script('zappoppop-builder-inspector-js', ZAPPOPPOP_URL . 'admin/js/builder/builder-inspector.js', ['jquery', 'zappoppop-builder-core-js'], self::ver(), true);
            wp_enqueue_script('zappoppop-builder-data-tab-sync-js', ZAPPOPPOP_URL . 'admin/js/builder/builder-data-tab-sync.js', ['jquery', 'zappoppop-builder-inspector-js'], self::ver(), true);
        $zappoppop_reg_mods = array('registry-core', 'registry-style', 'registry-media', 'registry-label', 'registry-form', 'registry-lifecycle', 'registry-token', 'registry-compat', 'registry-inspector', 'registry-icons');
        $zappoppop_reg_prev = array('jquery');
        foreach ($zappoppop_reg_mods as $zappoppop_reg_i => $zappoppop_reg_m) {
            $zappoppop_reg_h = ($zappoppop_reg_i === count($zappoppop_reg_mods) - 1) ? 'zappoppop-builder-elements-registry' : 'zappoppop-' . $zappoppop_reg_m;
            wp_enqueue_script($zappoppop_reg_h, ZAPPOPPOP_URL . 'admin/js/builder/registry/' . $zappoppop_reg_m . '.js', $zappoppop_reg_prev, self::ver(), true);
            $zappoppop_reg_prev = array($zappoppop_reg_h);
        }
        wp_enqueue_script('zappoppop-builder-video-inspector-js', ZAPPOPPOP_URL . 'admin/js/builder/elements/video-inspector.js', ['jquery', 'zappoppop-builder-core-js', 'zappoppop-builder-elements-registry'], self::ver(), true);
        wp_enqueue_script('zappoppop-builder-image-inspector-js', ZAPPOPPOP_URL . 'admin/js/builder/elements/image-inspector.js', ['jquery', 'zappoppop-builder-core-js', 'zappoppop-builder-elements-registry'], self::ver(), true);
        wp_enqueue_script('zappoppop-builder-shape-inspector-js', ZAPPOPPOP_URL . 'admin/js/builder/elements/shape-inspector.js', ['jquery', 'zappoppop-builder-core-js', 'zappoppop-builder-elements-registry'], self::ver(), true);
            wp_enqueue_script('zappoppop-builder-settings-js', ZAPPOPPOP_URL . 'admin/js/builder/builder-settings.js', ['jquery', 'zappoppop-builder-core-js'], self::ver(), true);
            wp_enqueue_script('zappoppop-builder-settings-switches-js', ZAPPOPPOP_URL . 'admin/js/builder/builder-settings-switches.js', ['zappoppop-builder-settings-js'], self::ver(), true); /* ZPP_SETTINGS_SWITCHES */
            if (class_exists('ZapPopPop_Modules_Engine')) { ZapPopPop_Modules_Engine::enqueue_builder(); }
            wp_enqueue_script('zappoppop-builder-modals-js', ZAPPOPPOP_URL . 'admin/js/builder/builder-modals.js', ['jquery', 'zappoppop-builder-core-js'], self::ver(), true);
            wp_enqueue_script('zappoppop-builder-io-js', ZAPPOPPOP_URL . 'admin/js/builder/builder-io.js', ['jquery', 'zappoppop-builder-core-js'], self::ver(), true);

            wp_enqueue_script('zappoppop-builder-js', ZAPPOPPOP_URL . 'admin/js/builder.js', [
                'jquery',
                'zappoppop-builder-core-js',
                'zappoppop-builder-canvas-js',
                'zappoppop-builder-layers-js',
                'zappoppop-builder-inspector-js',
                'zappoppop-builder-settings-js',
                'zappoppop-builder-ext-js',
                'zappoppop-builder-modals-js',
                'zappoppop-builder-io-js', 'zappoppop-builder-elements-registry', 'zappoppop-builder-animation-js'
            ], self::ver(), true);

            wp_enqueue_script('zappoppop-builder-preview-js', ZAPPOPPOP_URL . 'admin/js/builder/builder-preview.js', ['jquery', 'zappoppop-builder-core-js', 'zappoppop-builder-canvas-js', 'zappoppop-builder-io-js'], self::ver(), true);
            wp_enqueue_media();
            wp_enqueue_script('zappoppop-builder-template-js', ZAPPOPPOP_URL . 'admin/js/builder/builder-template.js', ['jquery', 'zappoppop-builder-core-js', 'zappoppop-builder-io-js', 'zappoppop-builder-settings-js'], self::ver(), true);
            wp_enqueue_script('zappoppop-builder-save-menu-js', ZAPPOPPOP_URL . 'admin/js/builder/builder-save-menu.js', ['jquery', 'zappoppop-builder-core-js', 'zappoppop-builder-io-js'], self::ver(), true);
            wp_enqueue_script('zappoppop-builder-save-state-js', ZAPPOPPOP_URL . 'admin/js/builder/builder-save-state.js', ['jquery', 'zappoppop-builder-core-js', 'zappoppop-builder-io-js', 'zappoppop-builder-settings-js'], self::ver(), true);
            wp_enqueue_script('zappoppop-builder-mode-sync-js', ZAPPOPPOP_URL . 'admin/js/builder/builder-mode-sync.js', ['jquery', 'zappoppop-builder-core-js'], self::ver(), true);

            wp_enqueue_script('zappoppop-builder-button-canvas-select-js', ZAPPOPPOP_URL . 'admin/js/builder/builder-button-canvas-select.js', ['jquery', 'zappoppop-builder-core-js', 'zappoppop-builder-inspector-js', 'zappoppop-builder-elements-registry'], self::ver(), true);
            wp_enqueue_script('zappoppop-builder-ribbon-toggle-js', ZAPPOPPOP_URL . 'admin/js/builder/builder-ribbon-toggle.js', ['jquery', 'zappoppop-builder-core-js'], self::ver(), true);
            wp_enqueue_script('zappoppop-builder-align-dock-js', ZAPPOPPOP_URL . 'admin/js/builder/builder-align-dock.js', ['jquery', 'zappoppop-builder-core-js'], self::ver(), true);
            wp_enqueue_script('zappoppop-builder-history-fix-js', ZAPPOPPOP_URL . 'admin/js/builder/builder-history-fix.js', ['jquery', 'zappoppop-builder-core-js', 'zappoppop-builder-settings-js', 'zappoppop-builder-io-js'], self::ver(), true); /* ZAPPOPPOP_HISTORY_FIX */
            wp_enqueue_script('zappoppop-builder-nudge-js', ZAPPOPPOP_URL . 'admin/js/builder/builder-nudge.js', ['jquery', 'zappoppop-builder-core-js'], self::ver(), true);
            wp_enqueue_script('zappoppop-builder-marquee-js', ZAPPOPPOP_URL . 'admin/js/builder/builder-marquee.js', ['jquery', 'zappoppop-builder-core-js', 'zappoppop-builder-canvas-js'], self::ver(), true); /* ZAPPOPPOP_MARQUEE_SELECT */
            wp_enqueue_script('zappoppop-builder-alt-copy-js', ZAPPOPPOP_URL . 'admin/js/builder/builder-alt-copy.js', ['jquery', 'zappoppop-builder-core-js', 'zappoppop-builder-canvas-js'], self::ver(), true); /* ZAPPOPPOP_ALT_COPY */
            wp_enqueue_script('zappoppop-builder-clipboard-js', ZAPPOPPOP_URL . 'admin/js/builder/builder-layer-copy.js', ['jquery', 'zappoppop-builder-core-js', 'zappoppop-builder-canvas-js'], self::ver(), true); /* ZAPPOPPOP_CLIPBOARD */
            wp_enqueue_script('zappoppop-builder-click-open-js', ZAPPOPPOP_URL . 'admin/js/builder/builder-click-open.js', ['jquery', 'zappoppop-builder-core-js'], self::ver(), true);
            wp_enqueue_script('zappoppop-builder-panel-memory-js', ZAPPOPPOP_URL . 'admin/js/builder/builder-panel-memory.js', ['jquery', 'zappoppop-builder-core-js', 'zappoppop-builder-inspector-js', 'zappoppop-builder-settings-js', 'zappoppop-builder-io-js'], self::ver(), true); /* ZAPPOPPOP_PANEL_MEMORY */
            wp_enqueue_script('zappoppop-builder-ribbon-tooltip-js', ZAPPOPPOP_URL . 'admin/js/builder/builder-ribbon-tooltip.js', [], self::ver(), true);
            wp_enqueue_script('zappoppop-builder-ribbon-toggles-js', ZAPPOPPOP_URL . 'admin/js/builder/builder-ribbon-toggles.js', [], self::ver(), true); /* ZAPPOPPOP_RIBBON_ICON_TOGGLES */
            wp_enqueue_script('zappoppop-builder-zoom-js', ZAPPOPPOP_URL . 'admin/js/builder/builder-zoom.js', ['jquery', 'jquery-ui-draggable', 'jquery-ui-resizable', 'zappoppop-builder-core-js', 'zappoppop-builder-canvas-js'], self::ver(), true); /* ZAPPOPPOP_STAGE_ZOOM */

            $builder_payload = array_merge($shared_payload, [
                'nonce'        => wp_create_nonce('zappoppop_builder_nonce'),
                'save_template' => (function () {
                    $s = get_option('zappoppop_settings', []);
                    return (is_array($s) && !empty($s['builder_save_template'])) ? 1 : 0;
                })(),
                'alignment_tool' => (function () {
                    $s = get_option('zappoppop_settings', []);
                    return (is_array($s) && !empty($s['builder_alignment_tool'])) ? 1 : 0;
                })(),
                'click_canvas_open_settings' => (function () {
                    $s = get_option('zappoppop_settings', []);
                    return (is_array($s) && !empty($s['builder_click_canvas_settings'])) ? 1 : 0;
                })(),
                'click_element_open_settings' => (function () {
                    $s = get_option('zappoppop_settings', []);
                    return (is_array($s) && !empty($s['builder_click_element_settings'])) ? 1 : 0;
                })(),
                'custom_fonts' => ZapPopPop_Libraries::get_custom_font_families(),
                'fa_url' => ZapPopPop_Libraries::fa_url(),
                'layer_settings' => ZapPopPop_Layer_Settings::builder_payload(),
                'new_popup_defaults' => class_exists('ZapPopPop_Saved_Defaults') ? ZapPopPop_Saved_Defaults::get('popup') : null,
                'layer_defaults' => class_exists('ZapPopPop_Saved_Defaults') ? (ZapPopPop_Saved_Defaults::get('layers') ?: null) : null,
                'layer_color_presets' => (is_array($zappoppop_lc_saved = get_option('zappoppop_layer_color_presets', null)) ? array_values(array_filter($zappoppop_lc_saved, 'is_string')) : null), /* ZPP_LAYER_COLOR_PRESETS: null = never saved (JS shows the defaults); an empty array = the user removed them all */
                'layer_factory' => class_exists('ZapPopPop_Saved_Defaults') ? (self::layer_factory_for_builder() ?: null) : null /* ZPP_LAYER_FACTORY */
            ]);
            $builder_payload = apply_filters('zappoppop_builder_payload', $builder_payload);
            wp_localize_script('zappoppop-builder-core-js', 'zappoppop_vars', $builder_payload);
            wp_enqueue_script('zappoppop-builder-layer-settings-js', ZAPPOPPOP_URL . 'admin/js/builder/builder-layer-settings.js', ['jquery', 'zappoppop-builder-core-js', 'zappoppop-builder-elements-registry', 'zappoppop-style-stable-js'], self::ver(), true);
            wp_enqueue_style('zappoppop-builder-layer-order-css', ZAPPOPPOP_URL . 'admin/css/builder/layer-order.css', ['zappoppop-builder-css'], self::ver());
            wp_enqueue_script('zappoppop-builder-layer-order-js', ZAPPOPPOP_URL . 'admin/js/builder/builder-layer-order.js', ['jquery', 'zappoppop-builder-core-js', 'zappoppop-builder-layer-settings-js'], self::ver(), true);
            wp_enqueue_script('zappoppop-builder-content-tab-js', ZAPPOPPOP_URL . 'admin/js/builder/builder-content-tab.js', ['jquery', 'zappoppop-builder-core-js'], self::ver(), true);
            wp_enqueue_script('zappoppop-builder-link-group-js', ZAPPOPPOP_URL . 'admin/js/builder/builder-link-group.js', ['jquery', 'zappoppop-builder-core-js'], self::ver(), true);
            wp_enqueue_script('zappoppop-builder-tab-blocks-js', ZAPPOPPOP_URL . 'admin/js/builder/builder-tab-blocks.js', ['jquery', 'zappoppop-builder-core-js'], self::ver(), true);
            wp_enqueue_style('zappoppop-builder-layer-defaults-css', ZAPPOPPOP_URL . 'admin/css/builder/layer-defaults.css', ['zappoppop-builder-css'], self::ver());
            wp_enqueue_script('zappoppop-builder-layer-defaults-js', ZAPPOPPOP_URL . 'admin/js/builder/builder-layer-defaults.js', ['jquery', 'zappoppop-builder-core-js', 'zappoppop-builder-content-tab-js', 'zappoppop-builder-tab-blocks-js', 'zappoppop-saved-defaults-js'], self::ver(), true);
            wp_enqueue_script('zappoppop-builder-field-id-js', ZAPPOPPOP_URL . 'admin/js/builder/builder-field-id.js', ['jquery', 'zappoppop-builder-core-js'], self::ver(), true);
            wp_enqueue_script('zappoppop-builder-field-label-js', ZAPPOPPOP_URL . 'admin/js/builder/builder-field-label.js', ['jquery', 'zappoppop-builder-core-js'], self::ver(), true);
            wp_enqueue_script('zappoppop-builder-field-label-move-js', ZAPPOPPOP_URL . 'admin/js/builder/builder-field-label-move.js', ['jquery', 'zappoppop-builder-core-js', 'zappoppop-builder-field-label-js'], self::ver(), true); /* ZPP_FL_MOVE */
            wp_enqueue_script('zappoppop-builder-layer-color-js', ZAPPOPPOP_URL . 'admin/js/builder/builder-layer-color.js', ['jquery', 'zappoppop-builder-core-js', 'zappoppop-builder-field-label-js'], self::ver(), true); /* ZPP_LAYER_COLOR_PRESETS */
            wp_enqueue_script('zappoppop-builder-field-style-js', ZAPPOPPOP_URL . 'admin/js/builder/builder-field-style.js', ['jquery', 'zappoppop-builder-core-js', 'zappoppop-builder-field-label-js'], self::ver(), true); /* ZPP_FIELD_STYLE */
            wp_enqueue_script('zappoppop-builder-alt-copy-guard-js', ZAPPOPPOP_URL . 'admin/js/builder/builder-alt-copy-guard.js', ['jquery', 'zappoppop-builder-core-js'], self::ver(), true);
            wp_enqueue_script('zappoppop-builder-form-cards-acc-js', ZAPPOPPOP_URL . 'admin/js/builder/builder-form-cards-acc.js', ['jquery', 'zappoppop-builder-core-js'], self::ver(), true);
            wp_enqueue_script('zappoppop-builder-required-move-js', ZAPPOPPOP_URL . 'admin/js/builder/builder-required-move.js', ['jquery', 'zappoppop-builder-core-js'], self::ver(), true);
            wp_enqueue_script('zappoppop-builder-panel-defaults-js', ZAPPOPPOP_URL . 'admin/js/builder/builder-panel-defaults.js', ['jquery', 'zappoppop-builder-core-js'], self::ver(), true);
            wp_enqueue_script('zappoppop-builder-shortcuts-js', ZAPPOPPOP_URL . 'admin/js/builder/builder-shortcuts.js', ['zappoppop-builder-core-js'], self::ver(), true);
            wp_enqueue_style('zappoppop-builder-click-routing-css', ZAPPOPPOP_URL . 'admin/css/builder/click-routing.css', ['zappoppop-builder-css'], self::ver());
            wp_enqueue_script('zappoppop-builder-click-routing-js', ZAPPOPPOP_URL . 'admin/js/builder/builder-click-routing.js', ['jquery', 'zappoppop-builder-core-js', 'zappoppop-builder-layer-order-js'], self::ver(), true);
            wp_localize_script('zappoppop-builder-inspector-js', 'zappoppop_custom_fonts', ZapPopPop_Libraries::get_custom_font_families());
            wp_localize_script('zappoppop-builder-io-js', 'zappoppop_vars', $builder_payload);
            wp_localize_script('zappoppop-builder-js', 'zappoppop_vars', $builder_payload);
            wp_localize_script('zappoppop-builder-preview-js', 'zappoppop_vars', $builder_payload);
            wp_enqueue_script('zappoppop-builder-saved-defaults-js', ZAPPOPPOP_URL . 'admin/js/builder/builder-saved-defaults.js', ['jquery', 'zappoppop-builder-core-js', 'zappoppop-builder-settings-js', 'zappoppop-builder-io-js'], self::ver(), true);
            ZapPopPop_Saved_Defaults::enqueue();
            do_action('zappoppop_admin_builder_assets', $builder_payload);
        }

        // 3. Settings Assets
        if ($page === 'zappoppop-settings') {
            $set_ver = self::ver();
            wp_enqueue_style('dashicons');
            wp_enqueue_style('zappoppop-dashboard-css', ZAPPOPPOP_URL . 'admin/css/dashboard.css', [], $set_ver);
            wp_enqueue_style('zappoppop-overview-css', ZAPPOPPOP_URL . 'admin/css/overview.css', [], $set_ver);
            wp_enqueue_script('zappoppop-overview-js', ZAPPOPPOP_URL . 'admin/js/overview.js', ['jquery'], $set_ver, true);
            wp_enqueue_style('zappoppop-settings-css', ZAPPOPPOP_URL . 'admin/css/settings.css', ['zappoppop-dashboard-css', 'zappoppop-overview-css', 'zappoppop-theme-global-css'], $set_ver);
            wp_enqueue_style('zappoppop-settings-search-css', ZAPPOPPOP_URL . 'admin/css/settings/search.css', ['zappoppop-settings-css'], $set_ver);
            wp_enqueue_style('zappoppop-settings-reset-css', ZAPPOPPOP_URL . 'admin/css/settings/reset.css', ['zappoppop-settings-css'], $set_ver);

            wp_enqueue_style('zappoppop-settings-typography-css', ZAPPOPPOP_URL . 'admin/css/settings/typography.css', ['zappoppop-settings-css'], $set_ver); /* ZAPPOPPOP_DEFAULT_TYPO */
            wp_enqueue_script('zappoppop-settings-typography-js', ZAPPOPPOP_URL . 'admin/js/settings/settings-typography.js', ['jquery'], self::ver(), true);
            wp_enqueue_script('zappoppop-settings-tabs-js', ZAPPOPPOP_URL . 'admin/js/settings/settings-tabs.js', ['jquery'], self::ver(), true);
            wp_enqueue_script('zappoppop-settings-save-js', ZAPPOPPOP_URL . 'admin/js/settings/settings-save.js', ['jquery', 'zappoppop-settings-tabs-js'], self::ver(), true);
            wp_enqueue_script('zappoppop-settings-tools-js', ZAPPOPPOP_URL . 'admin/js/settings/settings-tools.js', ['jquery'], self::ver(), true);
            wp_enqueue_script('zappoppop-settings-pages-js', ZAPPOPPOP_URL . 'admin/js/settings/settings-pages.js', ['jquery', 'jquery-ui-sortable', 'zappoppop-settings-tabs-js'], self::ver(), true);
            $this->enqueue_modules_ui($set_ver, ['zappoppop-settings-css']);
            wp_enqueue_script('zappoppop-settings-modules-js', ZAPPOPPOP_URL . 'admin/js/settings/settings-modules.js', ['jquery', 'jquery-ui-sortable', 'zappoppop-settings-tabs-js', 'zappoppop-modules-api-js'], self::ver(), true);
            wp_enqueue_script('zappoppop-settings-search-js', ZAPPOPPOP_URL . 'admin/js/settings/settings-search.js', ['jquery', 'zappoppop-settings-tabs-js'], self::ver(), true);
            wp_enqueue_script('zappoppop-settings-reset-js', ZAPPOPPOP_URL . 'admin/js/settings/settings-reset.js', ['jquery', 'zappoppop-settings-tabs-js'], self::ver(), true);
            wp_enqueue_script('zappoppop-settings-guide-js', ZAPPOPPOP_URL . 'admin/js/settings/settings-guide.js', [], self::ver(), true);
            wp_localize_script('zappoppop-settings-guide-js', 'zappoppop_guide_settings', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => ZapPopPop_Admin_Guide::nonce(),
                'i18n'     => [
                    'done' => __('Tour reset. It will start the next time you open the builder.', 'zappoppop'),
                    'fail' => __('Could not reset the tour. Please reload the page and try again.', 'zappoppop'),
                ],
            ]);

            wp_enqueue_style('zappoppop-settings-guide-editor-css', ZAPPOPPOP_URL . 'admin/css/settings/guide-editor.css', ['zappoppop-settings-css'], $set_ver);
            wp_enqueue_script('zappoppop-settings-guide-steps-js', ZAPPOPPOP_URL . 'admin/js/settings/settings-guide-steps.js', [], self::ver(), true);
            wp_localize_script('zappoppop-settings-guide-steps-js', 'zappoppop_guide_steps_i18n', [
                'count'          => __('{n} of {max} steps', 'zappoppop'),
                'builtin'        => __('Built-in', 'zappoppop'),
                'confirm_delete' => __('Delete this step?', 'zappoppop'),
                'confirm_reset'  => __('Reset all guide steps to the built-in defaults? Save settings to apply.', 'zappoppop'),
            ]);

            wp_enqueue_style('zappoppop-settings-layer-settings-css', ZAPPOPPOP_URL . 'admin/css/settings/builder-layer-settings.css', ['zappoppop-settings-css'], $set_ver);
            wp_enqueue_script('zappoppop-settings-layer-settings-js', ZAPPOPPOP_URL . 'admin/js/settings/settings-layer-settings.js', ['jquery', 'zappoppop-settings-tabs-js'], self::ver(), true);
            wp_enqueue_script('zappoppop-settings-js', ZAPPOPPOP_URL . 'admin/js/settings.js', [
                'jquery',
                'zappoppop-settings-tabs-js',
                'zappoppop-settings-save-js',
                'zappoppop-settings-tools-js',
                'zappoppop-settings-search-js'
            ], self::ver(), true);

            $settings_payload = [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('zappoppop_settings_nonce')
            ];

            wp_localize_script('zappoppop-settings-js', 'zappoppop_settings_vars', $settings_payload);
            wp_localize_script('zappoppop-settings-js', 'zappoppop_vars', $shared_payload);
            ZapPopPop_Saved_Defaults::enqueue();
        }

        // 4. Library Assets
        if ($page === 'zappoppop-library') {
            $lib_ver = self::ver();
            wp_enqueue_style('dashicons');
            wp_enqueue_style('zappoppop-dashboard-css', ZAPPOPPOP_URL . 'admin/css/dashboard.css', [], $lib_ver);
            wp_enqueue_style('zappoppop-overview-css', ZAPPOPPOP_URL . 'admin/css/overview.css', [], $lib_ver);
            wp_enqueue_script('zappoppop-overview-js', ZAPPOPPOP_URL . 'admin/js/overview.js', ['jquery'], $lib_ver, true);
            wp_enqueue_style('zappoppop-library-css', ZAPPOPPOP_URL . 'admin/css/library.css', ['zappoppop-dashboard-css', 'zappoppop-overview-css', 'zappoppop-theme-global-css'], $lib_ver);
            wp_enqueue_script('zappoppop-library-js', ZAPPOPPOP_URL . 'admin/js/library.js', ['jquery'], $lib_ver, true);
            wp_localize_script('zappoppop-library-js', 'zappoppop_vars', $shared_payload);
            wp_enqueue_script('zappoppop-library-live-preview-js', ZAPPOPPOP_URL . 'admin/js/library/library-live-preview.js', ['jquery', 'zappoppop-library-js'], $lib_ver, true);
            $zappoppop_lib_payload = ['ajax_url' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('zappoppop_admin_nonce')];
            wp_localize_script('zappoppop-library-live-preview-js', 'zappoppop_lib_vars', $zappoppop_lib_payload);
            wp_enqueue_script('zappoppop-library-categories-js', ZAPPOPPOP_URL . 'admin/js/library/library-categories.js', ['jquery', 'jquery-ui-sortable', 'zappoppop-library-js'], $lib_ver, true);
            wp_localize_script('zappoppop-library-categories-js', 'zappoppop_libcat_vars', ['ajax_url' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('zappoppop_admin_nonce')]);
            wp_enqueue_style('zappoppop-fa-library', ZapPopPop_Libraries::fa_url(), [], '6.5.1');
            wp_enqueue_media();
            $zappoppop_la_css = ZAPPOPPOP_PATH . 'admin/css/library/actions.css';
            wp_enqueue_style('zappoppop-library-actions-css', ZAPPOPPOP_URL . 'admin/css/library/actions.css', ['zappoppop-library-css', 'zappoppop-fa-library'], file_exists($zappoppop_la_css) ? (string) filemtime($zappoppop_la_css) : $lib_ver);
            wp_enqueue_script('zappoppop-library-actions-js', ZAPPOPPOP_URL . 'admin/js/library/library-actions.js', ['jquery', 'zappoppop-library-js'], $lib_ver, true);
            wp_localize_script('zappoppop-library-actions-js', 'zappoppop_libact_vars', ['ajax_url' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('zappoppop_admin_nonce')]);
            $zappoppop_ts_lib = ZAPPOPPOP_PATH . 'admin/css/library/theme-sync-modals.css';
            wp_enqueue_style('zappoppop-library-theme-modals-css', ZAPPOPPOP_URL . 'admin/css/library/theme-sync-modals.css', ['zappoppop-library-css'], file_exists($zappoppop_ts_lib) ? (string) filemtime($zappoppop_ts_lib) : $lib_ver);
            $zappoppop_ub_css = ZAPPOPPOP_PATH . 'admin/css/library/uniform-buttons.css';
            wp_enqueue_style('zappoppop-library-uniform-css', ZAPPOPPOP_URL . 'admin/css/library/uniform-buttons.css', ['zappoppop-library-css', 'zappoppop-library-actions-css', 'zappoppop-library-theme-modals-css'], file_exists($zappoppop_ub_css) ? (string) filemtime($zappoppop_ub_css) : $lib_ver);
            $zappoppop_sb_css = ZAPPOPPOP_PATH . 'admin/css/library/select-button.css';
            wp_enqueue_style('zappoppop-library-select-css', ZAPPOPPOP_URL . 'admin/css/library/select-button.css', ['zappoppop-library-uniform-css'], file_exists($zappoppop_sb_css) ? (string) filemtime($zappoppop_sb_css) : $lib_ver);
            wp_enqueue_script('zappoppop-library-download-js', ZAPPOPPOP_URL . 'admin/js/library/library-download.js', ['jquery', 'zappoppop-library-actions-js'], $lib_ver, true);
            wp_enqueue_script('zappoppop-library-user-js', ZAPPOPPOP_URL . 'admin/js/library/library-user.js', ['jquery', 'zappoppop-library-js'], $lib_ver, true);
            wp_enqueue_script('zappoppop-library-tooltip-js', ZAPPOPPOP_URL . 'admin/js/library/library-tooltip.js', ['jquery', 'zappoppop-library-actions-js', 'zappoppop-library-download-js', 'zappoppop-library-user-js'], $lib_ver, true);
            wp_enqueue_style('zappoppop-library-pager-css', ZAPPOPPOP_URL . 'admin/css/library/pager.css', ['zappoppop-library-css'], $lib_ver);
            wp_enqueue_script('zappoppop-library-pager-js', ZAPPOPPOP_URL . 'admin/js/library/library-pager.js', ['jquery', 'zappoppop-library-js'], $lib_ver, true);
            wp_localize_script('zappoppop-library-pager-js', 'zappoppop_libpg_vars', ['i18n' => [
                'showing' => /* translators: 1: first item number, 2: last item number, 3: total number of items. */ __('Showing %1$s-%2$s of %3$s', 'zappoppop'),
                'prev'    => __('Previous page', 'zappoppop'),
                'next'    => __('Next page', 'zappoppop'),
            ]]);
            wp_enqueue_style('zappoppop-library-import-css', ZAPPOPPOP_URL . 'admin/css/library/import.css', ['zappoppop-library-css'], $lib_ver);
            $zappoppop_bd_css = ZAPPOPPOP_PATH . 'admin/css/library/badge.css';
            wp_enqueue_style('zappoppop-library-badge-css', ZAPPOPPOP_URL . 'admin/css/library/badge.css', ['zappoppop-library-css'], file_exists($zappoppop_bd_css) ? (string) filemtime($zappoppop_bd_css) : $lib_ver);
            wp_enqueue_style('zappoppop-library-import-preview-css', ZAPPOPPOP_URL . 'admin/css/library/import-preview.css', ['zappoppop-library-css'], $lib_ver);
            wp_enqueue_script('zappoppop-library-import-preview-js', ZAPPOPPOP_URL . 'admin/js/library/library-import-preview.js', ['jquery', 'zappoppop-library-js'], $lib_ver, true);
            wp_localize_script('zappoppop-library-import-preview-js', 'zappoppop_libprev_vars', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('zappoppop_admin_nonce'),
                'i18n'     => [
                    'title'    => __('Preview template', 'zappoppop'),
                    'note'     => __('Check the template below, then click Import to add it to My Templates.', 'zappoppop'),
                    'loading'  => __('Reading template...', 'zappoppop'),
                    'failed'   => __('This file could not be read.', 'zappoppop'),
                    'elements' => __('elements', 'zappoppop'),
                    'images'   => __('images', 'zappoppop'),
                    'remove'   => __('Remove', 'zappoppop'),
                    'cancel'   => __('Cancel', 'zappoppop'),
                    'import'   => __('Import', 'zappoppop'),
                    /* translators: %d: number of templates */
                    'import_n' => __('Import %d templates', 'zappoppop'),
                ],
            ]);
            wp_enqueue_script('zappoppop-library-bundle-js', ZAPPOPPOP_URL . 'admin/js/library/library-bundle.js', ['jquery', 'zappoppop-library-js'], $lib_ver, true);
            wp_localize_script('zappoppop-library-bundle-js', 'zappoppop_libzip_vars', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('zappoppop_admin_nonce'),
                'zip'      => class_exists('ZapPopPop_Template_Bundle') ? ZapPopPop_Template_Bundle::zip_available() : false,
                'max_mb'   => 60,
                'i18n'     => [
                    'missing'       => __('Some images could not be found and were left as links.', 'zappoppop'),
                    'export_failed' => __('Download failed.', 'zappoppop'),
                    'bad_type'      => __('Only .zip or .json files can be imported.', 'zappoppop'),
                    'too_big'       => __('File is too large.', 'zappoppop'),
                    'uploading'     => __('Importing...', 'zappoppop'),
                    'done'          => __('Imported', 'zappoppop'),
                    'failed'        => __('Import failed.', 'zappoppop'),
                ],
            ]);
        }

        // Dashboard overview page (separate from the Popups list assets)
        if (in_array($page, ['zappoppop-dashboard', 'zappoppop'], true)) {
            $ov_ver = self::ver();
            wp_enqueue_style('dashicons');
            wp_enqueue_style('zappoppop-overview-css', ZAPPOPPOP_URL . 'admin/css/overview.css', [], $ov_ver);
            wp_enqueue_script('zappoppop-overview-js', ZAPPOPPOP_URL . 'admin/js/overview.js', ['jquery'], $ov_ver, true);
            if ($page === 'zappoppop-dashboard') {
                $zappoppop_sync_deps = ['zappoppop-overview-css'];
                if (wp_style_is('zappoppop-theme-global-css', 'enqueued')) { $zappoppop_sync_deps[] = 'zappoppop-theme-global-css'; }
                wp_enqueue_style('zappoppop-overview-sync-css', ZAPPOPPOP_URL . 'admin/css/overview-sync.css', $zappoppop_sync_deps, $ov_ver);
                wp_enqueue_script('zappoppop-dash-layout-js', ZAPPOPPOP_URL . 'admin/js/dash-layout.js', [], $ov_ver, true);
                wp_localize_script('zappoppop-dash-layout-js', 'zappoppop_dash_layout', [
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce'    => wp_create_nonce('zappoppop_admin_nonce'),
                ]);
            }
        }

        // 6. Submissions Assets
        if ($page === 'zappoppop-submissions') {
            $sub_ver = self::ver();
            wp_enqueue_style('dashicons');
            wp_enqueue_style('zappoppop-dashboard-css', ZAPPOPPOP_URL . 'admin/css/dashboard.css', [], $sub_ver);
            wp_enqueue_style('zappoppop-overview-css', ZAPPOPPOP_URL . 'admin/css/overview.css', [], $sub_ver);
            wp_enqueue_style('zappoppop-submissions-css', ZAPPOPPOP_URL . 'admin/css/submissions.css', ['zappoppop-dashboard-css', 'zappoppop-overview-css'], $sub_ver);
            wp_enqueue_script('zappoppop-overview-js', ZAPPOPPOP_URL . 'admin/js/overview.js', ['jquery'], $sub_ver, true);
            wp_enqueue_script('zappoppop-submissions-search-js', ZAPPOPPOP_URL . 'admin/js/submissions/submissions-search.js', ['jquery'], $sub_ver, true);
            wp_enqueue_script('zappoppop-submissions-actions-js', ZAPPOPPOP_URL . 'admin/js/submissions/submissions-actions.js', ['jquery'], $sub_ver, true);
            wp_enqueue_script('zappoppop-submissions-modal-js', ZAPPOPPOP_URL . 'admin/js/submissions/submissions-modal.js', ['jquery'], $sub_ver, true);
            wp_enqueue_script('zappoppop-submissions-table-options-js', ZAPPOPPOP_URL . 'admin/js/submissions/submissions-table-options.js', ['jquery', 'jquery-ui-sortable'], $sub_ver, true);
            wp_enqueue_script('zappoppop-submissions-list-js', ZAPPOPPOP_URL . 'admin/js/submissions/submissions-list.js', ['jquery'], $sub_ver, true);
            wp_enqueue_script('zappoppop-submissions-labels-js', ZAPPOPPOP_URL . 'admin/js/submissions/submissions-labels.js', ['jquery'], $sub_ver, true);
            wp_enqueue_script('zappoppop-submissions-js', ZAPPOPPOP_URL . 'admin/js/submissions.js', [
                'jquery',
                'zappoppop-submissions-search-js',
                'zappoppop-submissions-actions-js',
                'zappoppop-submissions-modal-js',
                'zappoppop-submissions-list-js'
            ], $sub_ver, true);
            wp_localize_script('zappoppop-submissions-js', 'zappoppop_vars', array_merge($shared_payload, [
                'nonce' => wp_create_nonce('zappoppop_builder_nonce')
            ]));
        }

        // 5. Tools & Portability Assets
        if ($page === 'zappoppop-tools') {
            $tl_ver = self::ver();
            wp_enqueue_style('dashicons');
            wp_enqueue_style('zappoppop-dashboard-css', ZAPPOPPOP_URL . 'admin/css/dashboard.css', [], $tl_ver);
            wp_enqueue_style('zappoppop-overview-css', ZAPPOPPOP_URL . 'admin/css/overview.css', [], $tl_ver);
            wp_enqueue_style('zappoppop-bar-actions-css', ZAPPOPPOP_URL . 'admin/css/bar-actions.css', ['zappoppop-dashboard-css', 'zappoppop-overview-css'], $tl_ver);
            wp_enqueue_style('zappoppop-tools-css', ZAPPOPPOP_URL . 'admin/css/tools.css', ['zappoppop-bar-actions-css', 'zappoppop-theme-global-css'], $tl_ver);
            wp_enqueue_script('zappoppop-overview-js', ZAPPOPPOP_URL . 'admin/js/overview.js', ['jquery'], $tl_ver, true);
            wp_enqueue_script('zappoppop-tools-bar-js', ZAPPOPPOP_URL . 'admin/js/tools/tools-bar.js', ['jquery'], $tl_ver, true);
            wp_enqueue_script('zappoppop-tools-maintenance-js', ZAPPOPPOP_URL . 'admin/js/tools/tools-maintenance.js', ['jquery'], $tl_ver, true);
            wp_enqueue_script('zappoppop-tools-system-js', ZAPPOPPOP_URL . 'admin/js/tools/tools-system.js', ['jquery'], self::ver(), true);
            wp_enqueue_script('zappoppop-tools-database-js', ZAPPOPPOP_URL . 'admin/js/tools/tools-database.js', ['jquery'], self::ver(), true);
            wp_enqueue_script('zappoppop-tools-portability-js', ZAPPOPPOP_URL . 'admin/js/tools/tools-portability.js', ['jquery'], self::ver(), true);
            wp_enqueue_script('zappoppop-tools-js', ZAPPOPPOP_URL . 'admin/js/tools.js', ['jquery', 'zappoppop-tools-system-js', 'zappoppop-tools-database-js', 'zappoppop-tools-portability-js'], self::ver(), true);
            wp_localize_script('zappoppop-tools-js', 'zappoppop_vars', $shared_payload);
            ZapPopPop_Saved_Defaults::enqueue();
        }

        // 6. Extend (module manager) page
        if ($page === 'zappoppop-extend') {
            $ex_ver = self::ver();
            wp_enqueue_style('dashicons');
            wp_enqueue_style('zappoppop-dashboard-css', ZAPPOPPOP_URL . 'admin/css/dashboard.css', [], $ex_ver);
            wp_enqueue_style('zappoppop-overview-css', ZAPPOPPOP_URL . 'admin/css/overview.css', [], $ex_ver);
            wp_enqueue_style('zappoppop-bar-actions-css', ZAPPOPPOP_URL . 'admin/css/bar-actions.css', ['zappoppop-dashboard-css', 'zappoppop-overview-css'], $ex_ver);
            wp_enqueue_script('zappoppop-overview-js', ZAPPOPPOP_URL . 'admin/js/overview.js', ['jquery'], $ex_ver, true);
            $this->enqueue_modules_ui($ex_ver, ['zappoppop-bar-actions-css', 'zappoppop-theme-global-css'], ['modules' => ZapPopPop_Modules_Installer::extend_items()]);
            wp_enqueue_style('zappoppop-extend-css', ZAPPOPPOP_URL . 'admin/css/extend.css', ['zappoppop-modules-css'], $ex_ver);
            wp_enqueue_script('zappoppop-extend-js', ZAPPOPPOP_URL . 'admin/js/modules/extend.js', ['jquery', 'zappoppop-modules-api-js'], $ex_ver, true);
            wp_enqueue_script('zappoppop-module-categories-js', ZAPPOPPOP_URL . 'admin/js/modules/module-categories.js', ['jquery', 'jquery-ui-sortable', 'zappoppop-modules-api-js'], $ex_ver, true); /* ZAPPOPPOP_CATS_ON_EXTEND */
            /* ZAPPOPPOP_ADDONS_INPLACE: add-on Install / Activate buttons (WordPress's own installer + one catalog-only endpoint) */
            if (class_exists('ZapPopPop_Addon_Catalog') && ZapPopPop_Addon_Catalog::items()) {
                $ex_deps = ['jquery', 'zappoppop-modules-api-js'];
                if (ZapPopPop_Addon_Catalog::can_install()) {
                    wp_enqueue_script('updates');
                    $ex_deps[] = 'updates';
                    if (function_exists('wp_print_request_filesystem_credentials_modal')) {
                        add_action('admin_footer', 'wp_print_request_filesystem_credentials_modal');
                    }
                }
                wp_enqueue_script('zappoppop-extend-addons-js', ZAPPOPPOP_URL . 'admin/js/modules/extend-addons.js', $ex_deps, $ex_ver, true);
                wp_localize_script('zappoppop-extend-addons-js', 'zappoppop_addons_vars', [
                    'i18n' => [
                        'installing' => __('Installing…', 'zappoppop'),
                        'activating' => __('Activating…', 'zappoppop'),
                        'activate'   => __('Activate', 'zappoppop'),
                        'active'     => __('Active', 'zappoppop'),
                        'inactive'   => __('Inactive', 'zappoppop'),
                        'installed'  => __('Add-on installed. Click Activate to turn it on.', 'zappoppop'),
                        'activated'  => __('Add-on activated. Reloading to load its modules…', 'zappoppop'),
                        'failed'     => __('Something went wrong. Please try again.', 'zappoppop'),
                        'native'     => __('Use the WordPress Plugins screen', 'zappoppop'),
                    ],
                ]);
            }
        }
    }
}
