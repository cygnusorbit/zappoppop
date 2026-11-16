<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Saved Defaults: "real" defaults for Settings, Theme and New Popups.
 *
 * Stored in the option `zappoppop_saved_defaults`. A read-only <plugin>/defaults/defaults.json shipped with
 * the plugin is used as the fallback. Secrets and site-specific values are never stored.
 */
class ZapPopPop_Saved_Defaults {
    const OPTION    = 'zappoppop_saved_defaults';
    const NONCE     = 'zappoppop_saved_defaults';
    const MAX_BYTES = 262144;

    protected static $cache     = null;
    protected static $localized = false;

    public static function scopes() {
        return ['settings', 'theme', 'popup'];
    }

    /** Settings keys that are site-specific, private or free-form code: never saved. */
    protected static function settings_skip() {
        return ['sender_email', 'from_email', 'admin_email', 'report_email', 'cors_origins', 'cookie_epoch'];
    }

    /** Popup-level module settings that hold credentials or site-specific targets. */
    protected static function popup_skip() {
        return ['webhooks', 'sms', 'autoresponder', 'payments', 'downloads', 'woocommerce'];
    }

    public static function is_secret_key($key) {
        return (bool) preg_match('/(secret|token|passw|api[_-]?key|private|licen[sc]e|credential|webhook|account_sid|site_key|bearer|(^|[_-])auth([_-]|$))/i', (string) $key);
    }

    public static function file_path() {
        return ZAPPOPPOP_PATH . 'defaults/defaults.json';
    }

    /** Defaults are stored in the database only; the shipped defaults file is read-only. */
    public static function file_writable() {
        return true;
    }

    /* ------------------------------------------------------------ storage */
    protected static function merge_store(array &$all, $store, $source) {
        if (!is_array($store)) {
            return;
        }
        foreach (self::scopes() as $scope) {
            if (isset($store[$scope]) && is_array($store[$scope]) && isset($store[$scope]['data']) && is_array($store[$scope]['data'])) {
                $all[$scope] = [
                    'data'   => $store[$scope]['data'],
                    'saved'  => isset($store[$scope]['saved']) ? (int) $store[$scope]['saved'] : 0,
                    'source' => $source,
                ];
            }
        }
    }

    protected static function load() {
        if (self::$cache !== null) {
            return self::$cache;
        }
        $all  = [];
        $file = self::file_path();
        if (is_readable($file)) {
            /* phpcs:ignore WordPress.WP.AlternativeFunctions -- reads a local plugin or upload temp file, not a remote URL */ $raw = file_get_contents($file);
            if ($raw !== false && strlen($raw) <= self::MAX_BYTES * 4) {
                self::merge_store($all, json_decode($raw, true), 'file');
            }
        }
        self::merge_store($all, get_option(self::OPTION, []), 'site');
        self::$cache = $all;
        return $all;
    }

    protected static function store_array() {
        $out = [];
        foreach (self::load() as $scope => $row) {
            $out[$scope] = ['data' => $row['data'], 'saved' => $row['saved']];
        }
        return $out;
    }

    protected static function persist(array $store) {
        if (empty($store)) {
            delete_option(self::OPTION);
        } else {
            update_option(self::OPTION, $store, false);
        }
        self::$cache = null;
        return ['file' => true];
    }

    public static function save($scope, array $data) {
        if (!in_array($scope, self::scopes(), true)) {
            return new WP_Error('zappoppop_defaults_scope', __('Unknown defaults scope.', 'zappoppop'));
        }
        if (strlen((string) wp_json_encode($data)) > self::MAX_BYTES) {
            return new WP_Error('zappoppop_defaults_size', __('These defaults are too large to save.', 'zappoppop'));
        }
        $store         = self::store_array();
        $store[$scope] = ['data' => $data, 'saved' => time()];
        return self::persist($store);
    }

    /** $scope: one of scopes() or 'all'. */
    public static function clear($scope) {
        $store = self::store_array();
        if ($scope === 'all') {
            $store = [];
        } elseif (in_array($scope, self::scopes(), true)) {
            unset($store[$scope]);
        } else {
            return new WP_Error('zappoppop_defaults_scope', __('Unknown defaults scope.', 'zappoppop'));
        }
        return self::persist($store);
    }

    public static function get($scope) {
        $all = self::load();
        if (!isset($all[$scope])) {
            return null;
        }
        $data = $all[$scope]['data'];
        return $scope === 'popup' ? self::sanitize_popup($data) : $data;
    }

    public static function status() {
        $all = self::load();
        $out = [];
        foreach (self::scopes() as $scope) {
            $out[$scope] = isset($all[$scope])
                ? ['saved' => true, 'time' => $all[$scope]['saved'], 'source' => $all[$scope]['source']]
                : ['saved' => false, 'time' => 0, 'source' => ''];
        }
        return $out;
    }

    public static function format_time($ts) {
        if (!$ts) {
            return '';
        }
        $fmt = get_option('date_format') . ' ' . get_option('time_format');
        return function_exists('wp_date') ? wp_date($fmt, $ts) : date_i18n($fmt, $ts);
    }

    /* ------------------------------------------------------------ settings */
    protected static function load_admin_settings() {
        if (!class_exists('ZapPopPop_Admin_Settings')) {
            require_once ZAPPOPPOP_PATH . 'includes/admin/class-admin-settings.php';
        }
    }

    protected static function load_admin_theme() {
        if (!class_exists('ZapPopPop_Admin_Theme')) {
            require_once ZAPPOPPOP_PATH . 'includes/admin/class-admin-theme.php';
        }
    }

    public static function capture_settings() {
        self::load_admin_settings();
        $cur = get_option('zappoppop_settings', []);
        $cur = is_array($cur) ? $cur : [];
        $out = [];
        foreach (ZapPopPop_Admin_Settings::get_factory_defaults() as $key => $unused) {
            if (in_array($key, self::settings_skip(), true) || self::is_secret_key($key)) {
                continue;
            }
            if (array_key_exists($key, $cur)) {
                $out[$key] = $cur[$key];
            }
        }
        return $out;
    }

    /** Overlay saved settings defaults on a full defaults array (type-safe, whitelisted by $base keys). */
    public static function apply_settings(array $base) {
        $saved = self::get('settings');
        if (!$saved) {
            return $base;
        }
        foreach ($saved as $key => $val) {
            if (!array_key_exists($key, $base) || in_array($key, self::settings_skip(), true) || self::is_secret_key($key)) {
                continue;
            }
            $cur = $base[$key];
            if ($key === 'modules' && is_array($cur) && is_array($val)) {
                foreach ($val as $sk => $sv) {
                    if (array_key_exists($sk, $cur) && is_scalar($sv)) {
                        $cur[$sk] = empty($sv) ? 0 : 1;
                    }
                }
                $base[$key] = $cur;
            } elseif ($key === 'menu_pages' && is_array($cur) && is_array($val)) {
                foreach ($val as $sk => $sv) {
                    $sk = sanitize_key($sk);
                    if ($sk !== '' && is_array($sv)) {
                        $cur[$sk] = ['hidden' => empty($sv['hidden']) ? 0 : 1];
                    }
                }
                $base[$key] = $cur;
            } elseif ($key === 'builder_guide_steps' && is_array($val)) {
                $base[$key] = ZapPopPop_Admin_Guide::sanitize_custom_steps($val);
            } elseif ($key === 'module_order' && is_array($cur) && is_array($val)) {
                self::load_admin_settings();
                $base[$key] = ZapPopPop_Admin_Settings::normalize_module_order($val);
            } elseif (!is_array($cur) && is_scalar($val)) {
                if (is_int($cur)) {
                    $val = (int) $val;
                } elseif (is_float($cur)) {
                    $val = (float) $val;
                } else {
                    $val = (string) $val;
                }
                $base[$key] = $val;
            }
        }
        return $base;
    }

    /** Fresh-install seed: saved settings defaults win over the minimal built-in seed. */
    public static function seed_settings(array $seed) {
        $saved = self::get('settings');
        if (!$saved) {
            return $seed;
        }
        self::load_admin_settings();
        $full = self::apply_settings(ZapPopPop_Admin_Settings::get_factory_defaults());
        foreach ($saved as $key => $unused) {
            if (array_key_exists($key, $full) && !in_array($key, self::settings_skip(), true) && !self::is_secret_key($key)) {
                $seed[$key] = $full[$key];
            }
        }
        return $seed;
    }

    /* --------------------------------------------------------------- theme */
    public static function capture_theme() {
        self::load_admin_theme();
        $vals = ZapPopPop_Admin_Theme::get_values();
        $out  = [];
        foreach (ZapPopPop_Admin_Theme::schema() as $group) {
            foreach ($group['fields'] as $key => $field) {
                if ($field['type'] !== 'media' && array_key_exists($key, $vals)) {
                    $out[$key] = $vals[$key];
                }
            }
        }
        return $out;
    }

    public static function apply_theme(array $values) {
        $saved = self::get('theme');
        if (!$saved) {
            return $values;
        }
        foreach (ZapPopPop_Admin_Theme::schema() as $group) {
            foreach ($group['fields'] as $key => $field) {
                if ($field['type'] === 'media' || !array_key_exists($key, $saved) || !array_key_exists($key, $values)) {
                    continue;
                }
                $values[$key] = ZapPopPop_Admin_Theme::sanitize_value($field, $saved[$key]);
            }
        }
        return $values;
    }

    /* --------------------------------------------------------------- popup */
    protected static function clean_tree($node, $depth) {
        if ($depth > 8) {
            return null;
        }
        if (is_array($node)) {
            $out = [];
            foreach ($node as $k => $v) {
                if (is_string($k)) {
                    $k = preg_replace('/[^A-Za-z0-9_:.\-]/', '', $k);
                    if ($k === '' || self::is_secret_key($k)) {
                        continue;
                    }
                }
                $c = self::clean_tree($v, $depth + 1);
                if ($c !== null) {
                    $out[$k] = $c;
                }
            }
            return $out;
        }
        if (is_bool($node) || is_int($node) || is_float($node)) {
            return $node;
        }
        if (is_string($node)) {
            $node = str_replace("\0", '', wp_check_invalid_utf8($node));
            return strlen($node) > 20000 ? substr($node, 0, 20000) : $node;
        }
        return null;
    }

    /** Whitelists the popup-level part of a builder export: canvas meta (no names) + popup settings (no credentials). */
    public static function sanitize_popup($raw) {
        $out = ['canvasMeta' => [], 'settings' => []];
        if (!is_array($raw)) {
            return $out;
        }
        if (isset($raw['canvasMeta']) && is_array($raw['canvasMeta'])) {
            foreach ([1, 2, 3] as $n) {
                if (isset($raw['canvasMeta'][$n]) && is_array($raw['canvasMeta'][$n])) {
                    $m = $raw['canvasMeta'][$n];
                    unset($m['name']);
                    $out['canvasMeta'][(string) $n] = self::clean_tree($m, 0);
                }
            }
        }
        if (isset($raw['settings']) && is_array($raw['settings'])) {
            $s = $raw['settings'];
            foreach (self::popup_skip() as $k) {
                unset($s[$k]);
            }
            $out['settings'] = self::clean_tree($s, 0);
        }
        return $out;
    }

    /* -------------------------------------------------------------- assets */
    public static function enqueue() {
        $v = defined('ZAPPOPPOP_VERSION') ? ZAPPOPPOP_VERSION : '1.0';
        wp_enqueue_script('zappoppop-saved-defaults-js', ZAPPOPPOP_URL . 'admin/js/saved-defaults.js', [], $v, true);
        if (self::$localized) {
            return;
        }
        self::$localized = true;
        wp_localize_script('zappoppop-saved-defaults-js', 'zappoppopDefaults', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce(self::NONCE),
            'i18n'     => [
                'saved'            => __('Saved!', 'zappoppop'),
                'cleared'          => __('Cleared', 'zappoppop'),
                'saving'           => __('Saving...', 'zappoppop'),
                'error'            => __('Could not save the defaults. Please reload the page and try again.', 'zappoppop'),
                'confirm_settings' => __('Save your last saved Settings as the defaults? Reset Defaults will then return to these values. (Unsaved form changes are not included: click Save Settings first.)', 'zappoppop'),
                'confirm_theme'    => __('Save your last saved Theme as the defaults? Revert and Reset will then return to these values. (Unsaved form changes are not included: click Save Theme first.)', 'zappoppop'),
                'confirm_popup'    => __('Use this popup\'s canvas and popup settings (not its layers) as the starting point for every new popup?', 'zappoppop'),
                'confirm_clear'    => __('Remove the saved defaults and go back to the built-in ones?', 'zappoppop'),
            ],
        ]);
    }
}
