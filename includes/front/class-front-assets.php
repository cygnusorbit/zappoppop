<?php
if (!defined('ABSPATH')) {
    exit;
}

class ZapPopPop_Front_Assets {
    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_front_assets']);
    }

    public function enqueue_front_assets() {
        // Enqueue Animate.css Library
        $zappoppop_root = trailingslashit(dirname(__DIR__, 2));
        $zappoppop_css_deps = [];
        if (file_exists($zappoppop_root . 'public/css/vendor/animate.min.css')) {
            wp_enqueue_style('animate-css', ZAPPOPPOP_URL . 'public/css/vendor/animate.min.css', [], '4.1.1');
            $zappoppop_css_deps[] = 'animate-css';
        }
        wp_enqueue_style('zappoppop-animations-core-css', ZAPPOPPOP_URL . 'public/css/animations-core.css', [], ZAPPOPPOP_VERSION);
        wp_enqueue_style('zappoppop-front-css', ZAPPOPPOP_URL . 'public/css/zappoppop-front.css', $zappoppop_css_deps, ZAPPOPPOP_VERSION);

        wp_enqueue_script('zappoppop-front-display-js', ZAPPOPPOP_URL . 'public/js/front/front-display.js', ['jquery'], ZAPPOPPOP_VERSION, true);
        wp_enqueue_script('zappoppop-front-gamification-js', ZAPPOPPOP_URL . 'public/js/front/front-gamification.js', ['jquery'], ZAPPOPPOP_VERSION, true);
        wp_enqueue_script('zappoppop-front-logic-js', ZAPPOPPOP_URL . 'public/js/front/front-logic.js', ['jquery'], ZAPPOPPOP_VERSION, true);
        wp_enqueue_script('zappoppop-front-form-js', ZAPPOPPOP_URL . 'public/js/front/front-form.js', ['jquery'], ZAPPOPPOP_VERSION, true);
        wp_enqueue_script('zappoppop-front-triggers-js', ZAPPOPPOP_URL . 'public/js/front/front-triggers.js', ['jquery'], ZAPPOPPOP_VERSION, true);
        wp_enqueue_script('zappoppop-front-tokens-js', ZAPPOPPOP_URL . 'public/js/front/front-tokens.js', ['jquery'], ZAPPOPPOP_VERSION, true);
        wp_enqueue_script('zappoppop-front-js', ZAPPOPPOP_URL . (file_exists($zappoppop_root . 'public/js/front/zappoppop-front.js') ? 'public/js/front/zappoppop-front.js' : 'public/js/zappoppop-front.js'), [
            'jquery',
            'zappoppop-front-display-js',
            'zappoppop-front-gamification-js',
            'zappoppop-front-logic-js',
            'zappoppop-front-form-js',
            'zappoppop-front-triggers-js',
            'zappoppop-front-tokens-js'
        ], ZAPPOPPOP_VERSION, true);

        wp_localize_script('zappoppop-front-js', 'zappoppop_front_vars', [
            'ajax_url'     => admin_url('admin-ajax.php'),
            'fa_url'       => ZapPopPop_Libraries::fa_url(),
            'rest_url'     => esc_url_raw(rest_url('zappoppop/v1/')),
            'nonce'        => wp_create_nonce('zappoppop_front_nonce'),
            'cookie_epoch' => get_option('zappoppop_cookie_epoch', 1),
            'date_format'  => get_option('date_format'),
            'time_format'  => get_option('time_format'),
            'current_user' => [
                'logged_in' => is_user_logged_in(),
                'email'     => is_user_logged_in() ? wp_get_current_user()->user_email : '',
                'name'      => is_user_logged_in() ? wp_get_current_user()->display_name : '',
                'login'     => is_user_logged_in() ? wp_get_current_user()->user_login : ''
            ]
        ]);
    }
}
