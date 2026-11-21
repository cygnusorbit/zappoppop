<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('ZapPopPop_Libraries')) {
    require_once ZAPPOPPOP_PATH . 'includes/class-zappoppop-libraries.php';
}

if (!class_exists('ZapPopPop_Front_Shortcode')) {
    require_once ZAPPOPPOP_PATH . 'includes/front/class-front-shortcode.php';
}

class ZapPopPop_Front_Display {
    protected $targeting;
    protected $renderer;

    public function __construct(ZapPopPop_Front_Targeting $targeting, ZapPopPop_Front_Renderer $renderer) {
        $this->targeting = $targeting;
        $this->renderer  = $renderer;

        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_head', [$this, 'print_custom_head_assets'], 99);
        add_action('wp_footer', [$this, 'print_custom_footer_assets'], 99);
        add_action('wp_footer', [$this, 'render_popups'], 50);
        new ZapPopPop_Front_Shortcode($renderer);
    }

    protected function is_execution_allowed() {
        $test_mode = (bool) zappoppop_get_setting('test_mode', false);
        if ($test_mode && !current_user_can('manage_options')) {
            return false;
        }
        return true;
    }

    public function enqueue_assets() {
        if (!$this->is_execution_allowed()) {
            return;
        }

        $cache_busting = (bool) zappoppop_get_setting('cache_busting', false);
        $cookie_epoch  = (int) zappoppop_get_setting('cookie_epoch', 1);
        $asset_version = $cache_busting ? ZAPPOPPOP_VERSION . '.' . $cookie_epoch . '.' . time() : ZAPPOPPOP_VERSION;

        wp_enqueue_style('zappoppop-front-css', ZAPPOPPOP_URL . 'public/css/front/modal.css', [], $asset_version);
        wp_enqueue_style('zappoppop-front-anim-css', ZAPPOPPOP_URL . 'public/css/front/animations.css', ['zappoppop-front-css'], $asset_version);
        wp_enqueue_style('zappoppop-animations-core-css', ZAPPOPPOP_URL . 'public/css/animations-core.css', ['zappoppop-front-anim-css'], $asset_version);
        wp_enqueue_style('zappoppop-front-forms-css', ZAPPOPPOP_URL . 'public/css/front/form-elements.css', ['zappoppop-animations-core-css'], $asset_version); /* ZAPPOPPOP_FORM_HARMONY_V1 */
        wp_enqueue_script('zappoppop-front-triggers-js', ZAPPOPPOP_URL . 'public/js/front/front-triggers.js', ['jquery'], $asset_version, true);
        wp_enqueue_script('zappoppop-front-js', ZAPPOPPOP_URL . 'public/js/front/front-display.js', ['jquery', 'zappoppop-front-triggers-js'], $asset_version, true);
        wp_enqueue_script('zappoppop-front-autoclose-js', ZAPPOPPOP_URL . 'public/js/front/front-autoclose.js', ['jquery', 'zappoppop-front-js'], $asset_version, true); /* ZAPPOPPOP_AUTOCLOSE_JS */
        wp_enqueue_script('zappoppop-front-form-js', ZAPPOPPOP_URL . 'public/js/front/front-form.js', ['jquery', 'zappoppop-front-js'], $asset_version, true);
        wp_enqueue_script('zappoppop-front-conditional-logic-js', ZAPPOPPOP_URL . 'public/js/front/front-conditional-logic.js', ['jquery', 'zappoppop-front-form-js'], $asset_version, true);
        wp_enqueue_script('zappoppop-front-scale-js', ZAPPOPPOP_URL . 'public/js/front/front-scale.js', ['zappoppop-front-js'], $asset_version, true);
        wp_enqueue_script('zappoppop-front-pickers-js', ZAPPOPPOP_URL . 'public/js/front/front-pickers.js', ['zappoppop-front-js'], $asset_version, true); /* ZAPPOPPOP_FRONT_PICKERS_V1 */
        wp_enqueue_script('zappoppop-front-tracking-js', ZAPPOPPOP_URL . 'public/js/front/front-tracking.js', ['jquery', 'zappoppop-front-js'], $asset_version, true);

        ZapPopPop_Libraries::enqueue_frontend_libraries();
        do_action('zappoppop_front_enqueue');

        $runtime_payload = [
            'ajax_url'           => admin_url('admin-ajax.php'),
            'fa_url'             => ZapPopPop_Libraries::fa_url(),
            'nonce'              => wp_create_nonce('zappoppop_front_nonce'),
            'cookie_epoch'       => $cookie_epoch,
            'render_delay'       => max(0, (int) zappoppop_get_setting('render_delay', 0)),
            'preload_events'     => (bool) zappoppop_get_setting('preload_events', false),
            'test_mode'          => (bool) zappoppop_get_setting('test_mode', false),
            'default_font'       => esc_attr(zappoppop_get_setting('default_font', 'inherit')),
            'libraries'          => [
                'datepicker' => (bool) (zappoppop_get_setting('load_air_datepicker', false) || zappoppop_get_setting('air_datepicker', false)),
                'mask'       => (bool) (zappoppop_get_setting('load_jquery_mask', false) || zappoppop_get_setting('jquery_mask', false)),
                'slider'     => (bool) (zappoppop_get_setting('load_range_slider', false) || zappoppop_get_setting('range_slider', false))
            ]
        ];

        $runtime_payload = array_merge($runtime_payload, ZapPopPop_Tracking::front_vars());
        $runtime_payload = (array) apply_filters('zappoppop_front_vars', $runtime_payload);
        wp_localize_script('zappoppop-front-js', 'zappoppop_front_vars', $runtime_payload);
        wp_localize_script('zappoppop-front-form-js', 'zappoppop_front_vars', $runtime_payload);
    }

    public function print_custom_head_assets() {
        if (!$this->is_execution_allowed()) {
            return;
        }

        ZapPopPop_Libraries::print_font_head_tags();

    }

    public function print_custom_footer_assets() {
        if (!$this->is_execution_allowed()) {
            return;
        }

        do_action('zappoppop_front_footer');

    }

    /**
     * True when this popup's uid appears in the current post content (link / button / JS snippet)
     * or a theme/plugin forces it via the zappoppop_force_render_uids filter.
     */
    protected function is_manual_trigger_used($uid) {
        static $haystack = null;
        if ($haystack === null) {
            $post     = function_exists('get_post') ? get_post() : null;
            $haystack = ($post && !empty($post->post_content)) ? (string) $post->post_content : '';
            $haystack = (string) apply_filters('zappoppop_manual_trigger_haystack', $haystack);
        }
        $forced = (array) apply_filters('zappoppop_force_render_uids', []);
        if (in_array($uid, $forced, true)) {
            return true;
        }
        return ($haystack !== '' && $uid !== '' && strpos($haystack, $uid) !== false);
    }

    /* ZAPPOPPOP_ALWAYS_EMBED_ONLY: "Popup All the Time on Page Load" is a trigger that only runs where embed code (shortcode / widget) is present */
    protected function zappoppop_is_always($popup) {
        $raw = !empty($popup->data) ? $popup->data : (!empty($popup->config) ? $popup->config : '');
        if ($raw === '') { return false; }
        $cfg = json_decode($raw, true);
        if (!is_array($cfg) || empty($cfg['settings']['triggers']) || !is_array($cfg['settings']['triggers'])) { return false; }
        $v = isset($cfg['settings']['triggers']['always_embed']) ? $cfg['settings']['triggers']['always_embed'] : false;
        return !empty($v) && $v !== 'false' && $v !== '0';
    }

    public function render_popups() {
        if (apply_filters('zappoppop_suppress_front_popups', false)) {
            return;
        }
        if (!$this->is_execution_allowed()) {
            return;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'zappoppop_items';
        if (// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $wpdb->prefix . 'zappoppop_items' ) ) ) !== $table_name) {
            return;
        }

        $popups = /* phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching */ $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}zappoppop_items WHERE status = 'publish' ORDER BY id DESC" );
        if (empty($popups)) {
            return;
        }

        $preload_popups = (bool) zappoppop_get_setting('preload_popups', false);

        foreach ($popups as $popup) {
            if (ZapPopPop_Front_Shortcode::is_inline_rendered($popup->uid)) {
                continue;
            }
            if (class_exists('ZapPopPop_Front_Ab') && ZapPopPop_Front_Ab::is_ab_member($popup->uid)
                && !ZapPopPop_Front_Shortcode::is_button_requested($popup->uid)
                && !ZapPopPop_Front_Shortcode::is_onload_requested($popup->uid)) {
                continue; /* un-chosen A/B variant: never auto-render */
            }
            $zappoppop_forced   = ZapPopPop_Front_Shortcode::is_button_requested($popup->uid) || ZapPopPop_Front_Shortcode::is_onload_requested($popup->uid) || $this->is_manual_trigger_used($popup->uid);
            $zappoppop_eligible = $this->targeting->is_eligible($popup);
            $zappoppop_always = $this->zappoppop_is_always($popup);
            if ($zappoppop_always && !$zappoppop_forced) { continue; } /* embed code only */
            if ($zappoppop_forced || $zappoppop_eligible) {
                $zappoppop_raw_cfg = !empty($popup->data) ? $popup->data : (!empty($popup->config) ? $popup->config : '');
                $config = $zappoppop_raw_cfg !== '' ? json_decode($zappoppop_raw_cfg, true) : [];
                if (!is_array($config)) {
                    $config = [];
                }
                ob_start();
                $zappoppop_ret  = $this->renderer->render_popup_markup($popup->uid, $config, false, $preload_popups);
                $zappoppop_html = ob_get_clean() . (is_string($zappoppop_ret) ? $zappoppop_ret : '');
                if (ZapPopPop_Front_Shortcode::is_onload_requested($popup->uid)) {
                    /* ZAPPOPPOP_WIDGET_ONLOAD: widget "Onload Popup" opens on page load regardless of the popup's own triggers */
                    if (strpos($zappoppop_html, 'data-trigger-load="1"') === false) {
                        $zappoppop_html = preg_replace(
                            '/<div class="zappoppop-overlay/',
                            '<div data-trigger-load="1" data-trigger-load-delay="' . (int) ZapPopPop_Front_Shortcode::get_onload_delay($popup->uid) . '" class="zappoppop-overlay',
                            $zappoppop_html,
                            1
                        );
                    }
                } elseif ($zappoppop_forced && !$zappoppop_eligible && !$zappoppop_always) {
                    $zappoppop_html = preg_replace('/<div class="zappoppop-overlay/', '<div data-manual="1" class="zappoppop-overlay', $zappoppop_html, 1);
                }
                echo ZapPopPop_Sanitizer::kses_render($zappoppop_html); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- popup markup goes through wp_kses() with the popup allowlist (ZapPopPop_Sanitizer::kses_render) ZAPPOPPOP_OUTPUT_KSES
            }
        }
    }
}
