<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueues the static CSS / JS files that used to be printed inline by the admin templates.
 * Files: admin/css/inline/{slug}.css and admin/js/inline/{slug}.js. Called from the template at the position the tag used to be;
 * WordPress prints late-enqueued styles and scripts in the footer.
 */
class ZapPopPop_Inline_Assets {
    protected static function ver($path) {
        $file = ZAPPOPPOP_PATH . $path;
        if (file_exists($file)) {
            return (string) filemtime($file);
        }
        return defined('ZAPPOPPOP_VERSION') ? ZAPPOPPOP_VERSION : '1.0';
    }

    public static function style($slug, array $deps = array()) {
        $slug = sanitize_key($slug);
        $path = 'admin/css/inline/' . $slug . '.css';
        if (!file_exists(ZAPPOPPOP_PATH . $path)) {
            return;
        }
        wp_enqueue_style('zappoppop-inline-' . $slug, ZAPPOPPOP_URL . $path, $deps, self::ver($path));
    }

    public static function script($slug, array $deps = array('jquery')) {
        $slug = sanitize_key($slug);
        $path = 'admin/js/inline/' . $slug . '.js';
        if (!file_exists(ZAPPOPPOP_PATH . $path)) {
            return;
        }
        wp_enqueue_script('zappoppop-inline-' . $slug, ZAPPOPPOP_URL . $path, $deps, self::ver($path), true);
    }
}
