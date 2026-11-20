<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Saved Defaults: "real" defaults for Settings, Theme and New Popups.
 *
 * Stored in the option `zappoppop_saved_defaults`. A read-only <plugin>/defaults/defaults.json shipped with
 * the plugin is used as the fallback. While developing (the plugin's `.dev/` folder exists) every save / clear is
 * also mirrored to that file. Secrets and site-specific values are never stored.
 */
class ZapPopPop_Saved_Defaults {
    const OPTION    = 'zappoppop_saved_defaults';
    const NONCE     = 'zappoppop_saved_defaults';
    const MAX_BYTES = 262144;

    protected static $cache     = null;
    protected static $localized = false;

    public static function scopes() {
        return ['settings', 'theme', 'popup', 'layers'];
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

    /** True when <plugin>/defaults/defaults.json can be written (the folder, or the file if it already exists). */
    public static function file_writable() {
        $file = self::file_path();
        $dir  = dirname($file);
        if (!is_dir($dir) || !is_writable($dir)) {
            return false;
        }
        return !file_exists($file) || is_writable($file);
    }

    /** Development mode: defaults are also written to defaults/defaults.json. True while the plugin's `.dev/` folder exists (never shipped), or when ZAPPOPPOP_DEV_DEFAULTS is true. */
    public static function dev_mode() {
        if (defined('ZAPPOPPOP_DEV_DEFAULTS')) {
            return (bool) ZAPPOPPOP_DEV_DEFAULTS;
        }
        return (bool) apply_filters('zappoppop_defaults_dev_mode', is_dir(ZAPPOPPOP_PATH . '.dev'));
    }

    /** Development only: writes the whole store to defaults/defaults.json (temp file + rename, so a half-written file is never read). */
    protected static function write_file(array $store) {
        if (!self::dev_mode() || !self::file_writable()) {
            return false;
        }
        $json = wp_json_encode((object) $store, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (!is_string($json) || $json === '') {
            return false;
        }
        $file = self::file_path();
        $tmp  = $file . '.tmp';
        /* phpcs:ignore WordPress.WP.AlternativeFunctions -- development only: writes the plugin's own defaults file, not a remote URL */ $ok = file_put_contents($tmp, $json . "\n", LOCK_EX);
        if ($ok === false) {
            return false;
        }
        /* phpcs:ignore WordPress.WP.AlternativeFunctions -- same-folder rename of the file written above */ if (!rename($tmp, $file)) {
            /* phpcs:ignore WordPress.WP.AlternativeFunctions -- fallback when rename is not possible */ $ok = file_put_contents($file, $json . "\n", LOCK_EX);
            /* phpcs:ignore WordPress.WP.AlternativeFunctions -- remove the leftover temp file */ @unlink($tmp);
            return $ok !== false;
        }
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

    /* ------------------------------------------------------------ layer merge (ZPP_LAYER_MERGE) */
    /** { type: [tab, ...] } of file tabs the site cleared on purpose (tombstones); unknown types / tabs are dropped. */
    protected static function clean_cleared($raw) {
        $out = [];
        if (!is_array($raw)) {
            return $out;
        }
        foreach ($raw as $type => $tabs) {
            $type = is_string($type) ? self::layer_type($type) : '';
            if ($type === '' || !is_array($tabs)) {
                continue;
            }
            foreach ($tabs as $tab) {
                if (is_string($tab) && in_array($tab, self::layer_tabs(), true)) {
                    $out[$type][$tab] = $tab;
                }
            }
            if (!empty($out[$type])) {
                $out[$type] = array_values($out[$type]);
            }
        }
        return $out;
    }

    /** File defaults first, minus the tabs the site cleared, then the site's own tabs on top (per type and tab). */
    protected static function merge_layers($file, $site, $cleared) {
        $out = is_array($file) ? $file : [];
        foreach ((array) $cleared as $type => $tabs) {
            foreach ((array) $tabs as $tab) {
                unset($out[$type][$tab]);
            }
            if (isset($out[$type]) && empty($out[$type])) {
                unset($out[$type]);
            }
        }
        foreach ((array) $site as $type => $tabs) {
            if (!is_array($tabs)) {
                continue;
            }
            foreach ($tabs as $tab => $props) {
                if (is_array($props) && !empty($props)) {
                    $out[$type][$tab] = $props;
                }
            }
        }
        return $out;
    }

    /** Replaces the plain "site scope wins" row of `layers` with the merged one and keeps the parts for saving. */
    protected static function merge_layer_rows(array &$all, $file_row, $site_opt) {
        $file     = (is_array($file_row) && isset($file_row['data'])) ? self::sanitize_layers($file_row['data']) : [];
        $site     = [];
        $cleared  = [];
        $has_site = false;
        $saved    = (is_array($file_row) && isset($file_row['saved'])) ? (int) $file_row['saved'] : 0;
        if (is_array($site_opt) && isset($site_opt['layers']) && is_array($site_opt['layers']) && isset($site_opt['layers']['data']) && is_array($site_opt['layers']['data'])) {
            $has_site = true;
            $site     = self::sanitize_layers($site_opt['layers']['data']);
            $cleared  = self::clean_cleared(isset($site_opt['layers']['cleared']) ? $site_opt['layers']['cleared'] : []);
            $saved    = max($saved, isset($site_opt['layers']['saved']) ? (int) $site_opt['layers']['saved'] : 0);
        }
        if ($file_row === null && !$has_site) {
            return;
        }
        $all['layers'] = [
            'data'      => self::merge_layers($file, $site, $cleared),
            'saved'     => $saved,
            'source'    => ($file_row === null || !empty($site) || !empty($cleared)) ? 'site' : 'file',
            'file_data' => $file,
            'site_data' => $site,
            'cleared'   => $cleared,
        ];
    }

    /** { file, site, cleared } parts of the layers scope: what defaults.json ships, what the site saved, what the site cleared. */
    protected static function layers_parts() {
        $all = self::load();
        $row = isset($all['layers']) ? $all['layers'] : [];
        return [
            'file'    => isset($row['file_data']) ? $row['file_data'] : [],
            'site'    => isset($row['site_data']) ? $row['site_data'] : [],
            'cleared' => isset($row['cleared']) ? $row['cleared'] : [],
        ];
    }

    /** Stores ONLY the site's own layer tabs (+ tombstones) in the option; the file keeps supplying the rest. */
    protected static function persist_layers(array $site, array $cleared) {
        if (strlen((string) wp_json_encode($site)) > self::MAX_BYTES) {
            return new WP_Error('zappoppop_defaults_size', __('These defaults are too large to save.', 'zappoppop'));
        }
        $store = self::store_array();
        unset($store['layers']);
        $cleared = self::clean_cleared($cleared);
        if (!empty($site) || !empty($cleared)) {
            $row = ['data' => $site, 'saved' => time()];
            if (!empty($cleared)) {
                $row['cleared'] = $cleared;
            }
            $store['layers'] = $row;
        }
        return self::persist($store);
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
        $file_layers = isset($all['layers']) ? $all['layers'] : null; /* ZPP_LAYER_MERGE */
        $site_opt    = get_option(self::OPTION, []);
        self::merge_store($all, $site_opt, 'site');
        self::merge_layer_rows($all, $file_layers, $site_opt);
        self::$cache = $all;
        return $all;
    }

    protected static function store_array() {
        $out = [];
        foreach (self::load() as $scope => $row) {
            if ($scope === 'layers') { /* ZPP_LAYER_MERGE: only the site's own tabs + tombstones, never the file's */
                $site = isset($row['site_data']) ? $row['site_data'] : [];
                $cl   = isset($row['cleared']) ? $row['cleared'] : [];
                if (!empty($site) || !empty($cl)) {
                    $out['layers'] = ['data' => $site, 'saved' => $row['saved']];
                    if (!empty($cl)) {
                        $out['layers']['cleared'] = $cl;
                    }
                }
                continue;
            }
            $out[$scope] = ['data' => $row['data'], 'saved' => $row['saved']];
        }
        return $out;
    }

    protected static function persist(array $store, $keep_file_layers = true) {
        $mirror = $store;
        if (self::dev_mode()) { /* ZPP_LAYER_MERGE: defaults.json always receives the merged layers (file + site - cleared) */
            $row    = (isset($store['layers']) && is_array($store['layers'])) ? $store['layers'] : [];
            $parts  = self::layers_parts();
            $merged = self::merge_layers(
                $keep_file_layers ? $parts['file'] : [],
                (isset($row['data']) && is_array($row['data'])) ? $row['data'] : [],
                (isset($row['cleared']) && is_array($row['cleared'])) ? $row['cleared'] : []
            );
            if (!empty($merged)) {
                $mirror['layers'] = ['data' => $merged, 'saved' => time()];
            } else {
                unset($mirror['layers']);
            }
        }
        if (empty($store)) {
            delete_option(self::OPTION);
        } else {
            update_option(self::OPTION, $store, false);
        }
        self::$cache = null;
        return ['file' => self::write_file($mirror)];
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
        return self::persist($store, !in_array($scope, ['all', 'layers'], true)); /* ZPP_LAYER_MERGE */
    }

    public static function get($scope) {
        $all = self::load();
        if (!isset($all[$scope])) {
            return null;
        }
        $data = $all[$scope]['data'];
        if ($scope === 'layers') {
            return self::sanitize_layers($data);
        }
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

    /* -------------------------------------------------------------- layers */
    /** Inspector tabs that can hold a saved layer default (same ids as the builder). */
    public static function layer_tabs() {
        return ['basic', 'style', 'logic', 'data', 'advanced'];
    }

    /** Model keys that are never part of a layer default: identity, position, state and unique field ids. */
    protected static function layer_skip() {
        return ['id', 'type', 'name', 'right', 'bottom', 'zIndex', 'locked', 'hidden', 'custom_settings', 'fieldId', 'fieldName', 'field_id', 'field_name']; /* ZPP_LD_COORDS: X / Y / Width / Height are allowed */
    }

    public static function layer_type($type) {
        $type = strtolower((string) preg_replace('/[^A-Za-z0-9_]/', '', (string) $type));
        return (strlen($type) > 40) ? '' : $type;
    }

    public static function sanitize_layer_props($raw) {
        $props = [];
        if (!is_array($raw)) {
            return $props;
        }
        $skip = self::layer_skip();
        foreach ($raw as $k => $v) {
            if (!is_string($k) || !preg_match('/^[A-Za-z0-9_]{1,64}$/', $k) || in_array($k, $skip, true) || self::is_secret_key($k)) {
                continue;
            }
            $c = self::clean_tree($v, 0);
            if ($c !== null) {
                $props[$k] = $c;
            }
            if (count($props) >= 300) {
                break;
            }
        }
        return $props;
    }

    /** Whitelists { layerType: { tab: { prop: value } } }: slug-like keys, known tabs, no identity / position / secret props. */
    public static function sanitize_layers($raw) {
        $out = [];
        if (!is_array($raw)) {
            return $out;
        }
        foreach ($raw as $type => $tabs) {
            $type = is_string($type) ? self::layer_type($type) : '';
            if ($type === '' || !is_array($tabs)) {
                continue;
            }
            foreach (self::layer_tabs() as $tab) {
                if (empty($tabs[$tab]) || !is_array($tabs[$tab])) {
                    continue;
                }
                $props = self::sanitize_layer_props($tabs[$tab]);
                if ($props) {
                    $out[$type][$tab] = $props;
                }
            }
        }
        return $out;
    }

    /* ------------------------------------------------------------ layer factory (ZPP_LAYER_FACTORY) */
    protected static $factory = null;

    public static function factory_file_path() {
        return ZAPPOPPOP_PATH . 'defaults/layer-defaults.json';
    }

    /**
     * Built-in defaults for NEW layers, read-only: <plugin>/defaults/layer-defaults.json
     * { "aliases": { type: otherType }, "layers": { type: { tab: { prop: value } } } }.
     * Same whitelist as saved layer defaults. Add-ons extend it with the `zappoppop_layer_factory_defaults` filter.
     */
    public static function factory_layers() {
        if (self::$factory !== null) {
            return self::$factory;
        }
        $layers  = [];
        $aliases = [];
        $file    = self::factory_file_path();
        if (is_readable($file)) {
            /* phpcs:ignore WordPress.WP.AlternativeFunctions -- reads a local plugin file, not a remote URL */ $raw = file_get_contents($file);
            if ($raw !== false && strlen($raw) <= self::MAX_BYTES * 4) {
                $json = json_decode($raw, true);
                if (is_array($json)) {
                    $layers  = (isset($json['layers']) && is_array($json['layers'])) ? $json['layers'] : [];
                    $aliases = (isset($json['aliases']) && is_array($json['aliases'])) ? $json['aliases'] : [];
                }
            }
        }
        $out = self::sanitize_layers($layers);
        foreach ($aliases as $type => $from) {
            $type = is_string($type) ? self::layer_type($type) : '';
            $from = is_string($from) ? self::layer_type($from) : '';
            if ($type !== '' && $from !== '' && !isset($out[$type]) && isset($out[$from])) {
                $out[$type] = $out[$from];
            }
        }
        /* ZPP_HIDE_LABEL_DEFAULT: Settings > Builder > "Hide Field Labels by Default" (off = labels shown). Only when on, new form layers start with showFieldLabel=false. */
        $zpp_set = get_option('zappoppop_settings', []);
        if (is_array($zpp_set) && !empty($zpp_set['builder_hide_field_labels'])) {
            foreach (array('text', 'email', 'number', 'phone', 'textarea', 'select', 'multiselect', 'radio', 'checkbox', 'checkboxgroup', 'date', 'time', 'upload', 'password', 'imageselect', 'tile', 'rating') as $zpp_t) {
                if (!isset($out[$zpp_t]) || !is_array($out[$zpp_t])) { $out[$zpp_t] = []; }
                if (!isset($out[$zpp_t]['basic']) || !is_array($out[$zpp_t]['basic'])) { $out[$zpp_t]['basic'] = []; }
                $out[$zpp_t]['basic']['showFieldLabel'] = false;
            }
        }
        $out = apply_filters('zappoppop_layer_factory_defaults', $out);
        self::$factory = is_array($out) ? self::sanitize_layers($out) : [];
        return self::$factory;
    }

    /** Saves one tab of one layer type (other types / tabs are kept). */
    public static function save_layer($type, $tab, array $props) {
        $type = self::layer_type($type);
        if ($type === '' || !in_array($tab, self::layer_tabs(), true)) {
            return new WP_Error('zappoppop_defaults_scope', __('Unknown layer type or tab.', 'zappoppop'));
        }
        $props = self::sanitize_layer_props($props);
        if (empty($props)) {
            return new WP_Error('zappoppop_defaults_empty', __('Nothing to save.', 'zappoppop'));
        }
        $parts = self::layers_parts(); /* ZPP_LAYER_MERGE: only this site's own tabs are stored; saving a tab lifts its tombstone */
        $site  = $parts['site'];
        $cl    = $parts['cleared'];
        $site[$type][$tab] = $props;
        if (isset($cl[$type])) {
            $cl[$type] = array_values(array_diff($cl[$type], [$tab]));
            if (empty($cl[$type])) {
                unset($cl[$type]);
            }
        }
        return self::persist_layers($site, $cl);
    }

    /** Removes one tab of one layer type; the whole scope goes when nothing is left. */
    public static function clear_layer($type, $tab) {
        $type = self::layer_type($type);
        if ($type === '' || !in_array($tab, self::layer_tabs(), true)) {
            return new WP_Error('zappoppop_defaults_scope', __('Unknown layer type or tab.', 'zappoppop'));
        }
        $parts = self::layers_parts(); /* ZPP_LAYER_MERGE: a tab the file ships is hidden with a tombstone, a site-only tab is simply removed */
        $site  = $parts['site'];
        $cl    = $parts['cleared'];
        unset($site[$type][$tab]);
        if (isset($site[$type]) && empty($site[$type])) {
            unset($site[$type]);
        }
        if (isset($parts['file'][$type][$tab])) {
            $cl[$type][] = $tab;
            $cl[$type]   = array_values(array_unique($cl[$type]));
        }
        return self::persist_layers($site, $cl);
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
