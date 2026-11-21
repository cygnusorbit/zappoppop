<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Extension API for companion plugins.
 * Core contains no licence or paid-feature code; an add-on reports itself through these filters.
 */
class ZapPopPop_Pro_API {

    const API_VERSION = 1;
    const ROOT_KINDS  = array('module', 'preset');

    public static function api_version() {
        return self::API_VERSION;
    }

    /** True while a companion plugin announces itself (define ZAPPOPPOP_PRO_VERSION or use the filter). */
    public static function is_active() {
        return (bool) apply_filters('zappoppop_pro_active', defined('ZAPPOPPOP_PRO_VERSION'));
    }

    /** True only when the companion plugin reports a valid licence. */
    public static function is_licensed() {
        return self::is_active() && (bool) apply_filters('zappoppop_pro_licensed', false);
    }

    /** Per-feature switch; the companion plugin decides, core defaults to $default. */
    public static function feature($key, $default = false) {
        return (bool) apply_filters('zappoppop_feature_enabled', (bool) $default, sanitize_key((string) $key));
    }

    /**
     * Extra folders a companion plugin contributes. Filter: zappoppop_{kind}_roots
     * Each item: array('path' => absolute folder, 'url' => public URL of that folder).
     *
     * @return array[] list of array('path' => string, 'url' => string with trailing slash)
     */
    public static function roots($kind) {
        $kind = sanitize_key((string) $kind);
        if (!in_array($kind, self::ROOT_KINDS, true)) {
            return array();
        }
        $list = apply_filters('zappoppop_' . $kind . '_roots', array());
        $out  = array();
        if (!is_array($list)) {
            return $out;
        }
        foreach ($list as $r) {
            if (!is_array($r) || empty($r['path']) || empty($r['url']) || !is_string($r['path']) || !is_string($r['url'])) {
                continue;
            }
            $path = rtrim($r['path'], '/\\');
            $url  = esc_url_raw($r['url']);
            if ($path === '' || $url === '' || !is_dir($path)) {
                continue;
            }
            if ($kind === 'module' && !self::in_plugin($path)) {
                continue;
            }
            $out[] = array('path' => $path, 'url' => trailingslashit($url));
        }
        return $out;
    }

    /** Real folders of installed plugins (plugins dir, must-use dir, and symlinked active plugins). */
    protected static function plugin_roots() {
        static $roots = null;
        if ($roots !== null) {
            return $roots;
        }
        $roots = array();
        $bases = array(WP_PLUGIN_DIR);
        if (defined('WPMU_PLUGIN_DIR')) {
            $bases[] = WPMU_PLUGIN_DIR;
        }
        $active = (array) get_option('active_plugins', array());
        if (is_multisite()) {
            $active = array_merge($active, array_keys((array) get_site_option('active_sitewide_plugins', array())));
        }
        foreach ($active as $file) {
            if (is_string($file) && strpos($file, '/') !== false) {
                $bases[] = WP_PLUGIN_DIR . '/' . dirname($file);
            }
        }
        foreach ($bases as $b) {
            $real = realpath($b);
            if ($real) {
                $roots[] = trailingslashit(wp_normalize_path($real));
            }
        }
        return $roots;
    }

    /** A module folder must sit inside a plugin. Uploads and every other writable content folder are refused. */
    protected static function in_plugin($path) {
        $real = realpath($path);
        if (!$real) {
            return false;
        }
        $real = trailingslashit(wp_normalize_path($real));
        $up   = wp_upload_dir(null, false);
        if (!empty($up['basedir']) && realpath($up['basedir'])) {
            if (strpos($real, trailingslashit(wp_normalize_path(realpath($up['basedir'])))) === 0) {
                return false;
            }
        }
        foreach (self::plugin_roots() as $root) {
            if (strpos($real, $root) === 0) {
                return true;
            }
        }
        return false;
    }
}
