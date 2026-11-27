<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add-on catalog for the Extend page.
 *
 * An add-on is an ordinary WordPress plugin that ships its own modules/ folder and registers it through the
 * zappoppop_module_roots filter. ZapPopPop never accepts uploaded code: this class only lists add-ons and builds the
 * standard WordPress links to install (update.php?action=install-plugin) or activate (plugins.php?action=activate) them.
 * The list comes from defaults/addons.json and the zappoppop_addon_catalog filter. Nothing is fetched from a remote server.
 *
 * ZAPPOPPOP_ADDONS_INPLACE: the Extend page installs through wp.updates and activates through ZapPopPop_Ajax_Addons.
 *
 * Entry fields: slug, file (plugin-folder/main-file.php), name, description, kind (wporg | link), url (https, kind link only),
 * icon (dashicons-*), badge (up to 12 characters).
 */
class ZapPopPop_Addon_Catalog {

    const MAX = 24;

    /** @return array[] sanitised entries, duplicates (same slug) dropped. */
    public static function items() {
        $raw = apply_filters('zappoppop_addon_catalog', self::load());
        $out  = [];
        $seen = [];
        if (!is_array($raw)) {
            return $out;
        }
        foreach ($raw as $r) {
            $it = self::clean($r);
            if (!$it || isset($seen[$it['slug']])) {
                continue;
            }
            $seen[$it['slug']] = true;
            $out[]             = $it;
            if (count($out) >= self::MAX) {
                break;
            }
        }
        return $out;
    }

    protected static function load() {
        $file = ZAPPOPPOP_PATH . 'defaults/addons.json';
        if (!is_file($file) || is_link($file) || !is_readable($file) || filesize($file) > 65536) {
            return [];
        }
        /* phpcs:ignore WordPress.WP.AlternativeFunctions -- reads a local plugin file, not a remote URL */ $d = json_decode((string) file_get_contents($file), true);
        return (is_array($d) && isset($d['addons']) && is_array($d['addons'])) ? $d['addons'] : [];
    }

    protected static function text($r, $key, $max) {
        if (!isset($r[$key]) || !is_scalar($r[$key])) {
            return '';
        }
        return mb_substr(trim(wp_strip_all_tags((string) $r[$key])), 0, $max);
    }

    protected static function clean($r) {
        if (!is_array($r)) {
            return null;
        }
        $slug = (isset($r['slug']) && is_string($r['slug'])) ? sanitize_key($r['slug']) : '';
        $file = (isset($r['file']) && is_string($r['file'])) ? $r['file'] : '';
        $name = self::text($r, 'name', 60);
        if ($slug === '' || $name === '' || !preg_match('#^[a-z0-9._-]+/[a-z0-9._-]+\.php$#i', $file) || strpos($file, '..') !== false) {
            return null;
        }
        $kind = (isset($r['kind']) && $r['kind'] === 'link') ? 'link' : 'wporg';
        $url  = '';
        if ($kind === 'link') {
            $url = (isset($r['url']) && is_string($r['url'])) ? esc_url_raw($r['url'], ['https']) : '';
            if ($url === '') {
                return null;
            }
        }
        $icon = (isset($r['icon']) && is_string($r['icon']) && preg_match('/^dashicons-[a-z0-9-]+$/', $r['icon'])) ? $r['icon'] : 'dashicons-admin-plugins';
        return [
            'slug'        => $slug,
            'file'        => $file,
            'name'        => $name,
            'description' => self::text($r, 'description', 200),
            'kind'        => $kind,
            'url'         => $url,
            'icon'        => $icon,
            'badge'       => self::text($r, 'badge', 12),
        ];
    }

    /** missing | inactive | active */
    public static function status($it) {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $all = get_plugins();
        if (!isset($all[$it['file']])) {
            return 'missing';
        }
        if (is_plugin_active($it['file']) || (is_multisite() && is_plugin_active_for_network($it['file']))) {
            return 'active';
        }
        return 'inactive';
    }

    /** Installed version of the add-on plugin, '' when it is not installed. */
    public static function version($it) {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $all = get_plugins();
        return isset($all[$it['file']]['Version']) ? (string) $all[$it['file']]['Version'] : '';
    }

    /** Plain WordPress link that activates or deactivates the add-on (used when JavaScript is off). */
    public static function toggle_url($it, $active) {
        $what = $active ? 'deactivate' : 'activate';
        return wp_nonce_url(self_admin_url('plugins.php?action=' . $what . '&plugin=' . rawurlencode($it['file'])), $what . '-plugin_' . $it['file']);
    }

    public static function can_install() {
        $mods = function_exists('wp_is_file_mod_allowed') ? wp_is_file_mod_allowed('can_install_plugins') : !(defined('DISALLOW_FILE_MODS') && DISALLOW_FILE_MODS);
        return $mods && current_user_can('install_plugins');
    }

    /**
     * The one button a card shows, or null (already active, or the user may not do anything with it).
     *
     * @return array|null ['label' => string, 'url' => string, 'external' => bool, 'primary' => bool]
     */
    public static function action($it, $status) {
        if ($status === 'active') {
            return null;
        }
        if ($status === 'inactive') {
            if (!current_user_can('activate_plugins')) {
                return null;
            }
            return [
                'act'      => 'activate',
                'label'    => __('Activate', 'zappoppop'),
                'url'      => wp_nonce_url(self_admin_url('plugins.php?action=activate&plugin=' . rawurlencode($it['file'])), 'activate-plugin_' . $it['file']),
                'external' => false,
                'primary'  => true,
            ];
        }
        if ($it['kind'] === 'link') {
            return ['act' => '', 'label' => __('Get add-on', 'zappoppop'), 'url' => $it['url'], 'external' => true, 'primary' => false];
        }
        if (self::can_install()) {
            return [
                'act'      => 'install',
                'label'    => __('Install', 'zappoppop'),
                'url'      => wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . $it['slug']), 'install-plugin_' . $it['slug']),
                'external' => false,
                'primary'  => true,
            ];
        }
        return ['act' => '', 'label' => __('View on WordPress.org', 'zappoppop'), 'url' => 'https://wordpress.org/plugins/' . rawurlencode($it['slug']) . '/', 'external' => true, 'primary' => false];
    }
}
