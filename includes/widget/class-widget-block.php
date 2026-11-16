<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * ZapPopPop Gutenberg block: "ZapPopPop Popup"
 * Same controls as the sidebar widget (Popup, Embed Type, Onload Delay),
 * rendered through the [zappoppop] shortcode so every path shares one code route.
 */
class ZapPopPop_Widget_Block {
    public function __construct() {
        add_action('init', [$this, 'register_block']);
    }

    public function register_block() {
        if (!function_exists('register_block_type')) {
            return;
        }

        $rel    = 'admin/js/widget/widget-block.js';
        $file   = ZAPPOPPOP_PATH . $rel;
        $ver    = file_exists($file) ? (string) filemtime($file) : ZAPPOPPOP_VERSION;

        wp_register_script(
            'zappoppop-widget-block',
            ZAPPOPPOP_URL . $rel,
            ['wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-i18n'],
            $ver,
            true
        );

        wp_localize_script('zappoppop-widget-block', 'zappoppopBlockData', [
            'popups' => $this->get_popups(),
        ]);

        register_block_type('zappoppop/popup', [
            'editor_script'   => 'zappoppop-widget-block',
            'render_callback' => [$this, 'render_block'],
            'attributes'      => [
                'uid'   => ['type' => 'string',  'default' => ''],
                'mode'  => ['type' => 'string',  'default' => 'inline'],
                'delay' => ['type' => 'integer', 'default' => 0],
            ],
        ]);
    }

    protected function get_popups() {
        global $wpdb;
        $table = $wpdb->prefix . 'zappoppop_items';
        if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($table))) !== $table) {
            return [];
        }
        /* phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- table name is built from $wpdb->prefix and the query takes no user input */ $rows = $wpdb->get_results("SELECT uid, title FROM {$table} WHERE status = 'publish' ORDER BY title ASC", ARRAY_A);
        $out  = [];
        foreach ((array) $rows as $r) {
            $out[] = ['uid' => $r['uid'], 'title' => $r['title']];
        }
        return $out;
    }

    /* Backend whitelist: only known attributes / values reach the shortcode */
    public function render_block($attrs) {
        $uid   = isset($attrs['uid']) ? sanitize_key($attrs['uid']) : '';
        $mode  = (isset($attrs['mode']) && in_array($attrs['mode'], ['inline', 'onload'], true)) ? $attrs['mode'] : 'inline';
        $delay = isset($attrs['delay']) ? max(0, min(3600, (int) $attrs['delay'])) : 0;
        if ($uid === '') {
            return '';
        }
        /*
         * Every attribute is whitelisted above (sanitize_key uid, fixed mode list, clamped integer delay).
         * ZapPopPop_Front_Renderer escapes each value as it builds the markup; the finished markup is then passed
         * through wp_kses() with the popup allowlist (ZapPopPop_Sanitizer::kses_render) as a second, output-side layer.
         */
        if (!class_exists('ZapPopPop_Sanitizer')) { require_once ZAPPOPPOP_PATH . 'includes/class-zappoppop-sanitizer.php'; }
        return ZapPopPop_Sanitizer::kses_render(do_shortcode('[zappoppop uid="' . esc_attr($uid) . '" mode="' . esc_attr($mode) . '" delay="' . absint($delay) . '"]'));
    }
}
