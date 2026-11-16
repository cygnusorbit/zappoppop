<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * ZapPopPop Settings: "Reset Defaults" (per tab or all).
 * Never touches popups, campaigns, submissions or the cookie epoch.
 */
class ZapPopPop_Settings_Reset {

    public static function boot() {
        add_action('wp_ajax_zappoppop_reset_settings', [__CLASS__, 'handle']);
    }

    /** Stored option keys owned by each settings tab (aliases listed so they reset together). */
    public static function tab_keys() {
        if (!class_exists('ZapPopPop_Tracking')) {
            require_once ZAPPOPPOP_PATH . 'includes/class-zappoppop-tracking.php';
        }
        return (array) apply_filters('zappoppop_settings_tab_keys', [
            'general'     => ['sender_name', 'from_name', 'sender_email', 'from_email', 'admin_email', 'csv_separator', 'user_uploads', 'powered_by_badge', 'test_mode'],
            'performance' => ['preload_popups', 'preload_events', 'minify_css', 'cache_busting', 'render_delay', 'disable_google_fonts', 'default_embed_type'],
            'libraries'   => ['default_font', 'default_font_weight', 'default_font_size', 'default_text_align', 'default_font_italic', 'default_font_underline', 'default_font_color', /* ZAPPOPPOP_DEFAULT_TYPO */ 'google_fonts', 'font_awesome', 'custom_fonts',
                              'air_datepicker', 'load_air_datepicker', 'jquery_mask', 'load_jquery_mask',
                              'range_slider', 'load_range_slider'],
            'module'      => ['modules', 'module_order'],
            'builder'     => ['builder_alignment_tool', 'builder_viewport_toggle', 'builder_zoom_select', 'builder_save_template', 'builder_extra_elements', 'builder_click_canvas_settings', 'builder_click_element_settings', 'builder_guide', 'builder_guide_steps_on', 'builder_guide_steps'],
            'pages'       => ['menu_pages'],
            'tracking'    => array_keys(ZapPopPop_Tracking::defaults()),
        ]);
    }

    protected static function aliases() {
        return [
            'from_name' => 'sender_name', 'from_email' => 'sender_email',
            'load_air_datepicker' => 'air_datepicker', 'load_jquery_mask' => 'jquery_mask',
            'load_signature_pad' => 'signature_pad', 'load_range_slider' => 'range_slider',
            'load_math_parser' => 'js_parser', 'geoip_api_service' => 'geoip_service',
        ];
    }

    protected static function verify() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized capability.', 'zappoppop')]);
        }
        $nonce = isset($_REQUEST['nonce']) ? sanitize_text_field(wp_unslash($_REQUEST['nonce'])) : '';
        if (!wp_verify_nonce($nonce, 'zappoppop_settings_nonce') && !wp_verify_nonce($nonce, 'zappoppop_admin_nonce')) {
            wp_send_json_error(['message' => __('Security verification failed. Please reload the page.', 'zappoppop')]);
        }
    }

    public static function handle() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Unauthorized user capability.', 'zappoppop' ) ), 403 );
            wp_die();
        }
        $zpp_verified = check_ajax_referer( 'zappoppop_nonce', 'nonce', false )
            || check_ajax_referer( 'zappoppop_nonce', '_ajax_nonce', false )
            || check_ajax_referer( 'zappoppop_admin', 'nonce', false )
            || check_ajax_referer( 'zappoppop_nonce', 'security', false )
            || check_ajax_referer( 'zappoppop_builder_nonce', 'nonce', false )
            || check_ajax_referer( 'zappoppop_admin_nonce', 'nonce', false )
            || check_ajax_referer( 'zappoppop_settings_nonce', 'nonce', false );
        if ( ! $zpp_verified ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Invalid security token.', 'zappoppop' ) ), 403 );
            wp_die();
        }

        self::verify();

        $tab = isset($_POST['tab']) ? sanitize_key(wp_unslash($_POST['tab'])) : '';
        $map = self::tab_keys();
        if ($tab !== 'all' && !isset($map[$tab])) {
            wp_send_json_error(['message' => __('This tab has no stored settings to reset.', 'zappoppop')]);
        }

        if (!class_exists('ZapPopPop_Admin_Settings')) {
            require_once ZAPPOPPOP_PATH . 'includes/admin/class-admin-settings.php';
        }

        $existing = get_option('zappoppop_settings', []);
        $raw      = is_array($existing) ? $existing : [];
        $defaults = ZapPopPop_Admin_Settings::get_defaults();
        $aliases  = self::aliases();
        $tabs     = ($tab === 'all') ? array_keys($map) : [$tab];

        foreach ($tabs as $t) {
            foreach ($map[$t] as $key) {
                if ($key === 'modules') {
                    $raw['modules'] = isset($defaults['modules']) ? $defaults['modules'] : ZapPopPop_Admin_Settings::get_module_defaults();
                } elseif ($key === 'menu_pages') {
                    $raw['menu_pages'] = isset($defaults['menu_pages']) ? $defaults['menu_pages'] : ZapPopPop_Admin_Settings::get_default_menu_pages();
                } else {
                    $src = isset($aliases[$key]) ? $aliases[$key] : $key;
                    if (array_key_exists($src, $defaults)) {
                        $raw[$key] = $defaults[$src];
                    } else {
                        unset($raw[$key]);
                    }
                }
            }
        }

        /* Cookie epoch is server-owned */
        $raw['cookie_epoch'] = isset($existing['cookie_epoch'])
            ? (int) $existing['cookie_epoch']
            : (int) get_option('zappoppop_cookie_epoch', 1);

        $clean = ZapPopPop_Admin_Settings::sanitize_settings($raw);
        update_option('zappoppop_settings', $clean);
        if (function_exists('zappoppop_flush_settings_cache')) {
            zappoppop_flush_settings_cache();
        }
        if (function_exists('zappoppop_log_event')) {
            zappoppop_log_event('settings_reset', 'Settings reset to defaults: ' . $tab, ['user' => get_current_user_id()]);
        }

        wp_send_json_success([
            'message' => ($tab === 'all')
                ? __('All settings were reset to defaults.', 'zappoppop')
                : __('This tab was reset to defaults.', 'zappoppop'),
            'tab' => $tab,
        ]);
    }
}

ZapPopPop_Settings_Reset::boot();
