<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Module lifecycle: install / uninstall / delete of bundled modules (core and companion plugins).
 * ZAPPOPPOP_UPLOADER_REMOVED: modules cannot be uploaded; only the one-time clean-up of the old uploads folder remains.
 *   Install   = state "installed" (module is active in the builder and on the frontend).
 *   Uninstall = state "available" (files and popup data are kept, one click to reinstall).
 *   Delete    = state "deleted" (hidden until restored from Extend).
 * Locked modules can never be uninstalled or deleted. Every method returns an array or a WP_Error.
 */
class ZapPopPop_Modules_Installer {

    const RENAMED      = ['acc-20-animation' => 'acc-animation', 'acc-16-popup-overlay' => 'acc-popup-overlay', 'acc-17-popup-overlay' => 'acc-popup-overlay', 'acc-21-popup-positions' => 'acc-popup-positions', 'acc-18-notifications' => 'acc-notifications', 'acc-17-double-optin' => 'acc-double-optin', 'acc-22-targeting-triggers' => 'acc-targeting-triggers', 'acc-19-success-celebration' => 'acc-success-celebration', 'acc-08-autoresponder' => 'acc-autoresponder', 'acc-04-side-tabs' => 'acc-side-tabs', 'acc-09-webhooks' => 'acc-webhooks', 'acc-13-woocommerce' => 'acc-woocommerce', 'acc-06-downloads' => 'acc-downloads', 'acc-03-logic-math' => 'logic-math', 'acc-05-payments' => 'logic-payments', 'acc-07-video' => 'acc-video', 'acc-10-sms' => 'acc-sms', 'acc-11-targeting' => 'acc-targeting', 'acc-15-quiz' => 'acc-quiz', 'acc-12-frequency' => 'acc-frequency', 'acc-14-custom-code' => 'acc-custom-code'];
    const LEGACY_OPT   = 'zappoppop_legacy_uploads_purged';
    const NOTICE_OPT   = 'zappoppop_legacy_uploads_notice';

    protected static function err($code, $message, $data = null) {
        return new WP_Error($code, $message, $data);
    }

    /* ------------------------------------------------------ capabilities */

    public static function file_mods_allowed() {
        return !(defined('DISALLOW_FILE_MODS') && DISALLOW_FILE_MODS);
    }

    public static function can_manage() {
        return current_user_can('manage_options');
    }

    /* ------------------------------------------------------ summaries */

    public static function summary($m) {
        $slug = $m['slug'];
        $by   = array_keys(ZapPopPop_Modules::required_by($slug));
        return [
            'slug'        => $slug,
            'name'        => ($m['source'] === 'core') ? /* phpcs:ignore WordPress.WP.I18n.LowLevelTranslationFunction, WordPress.WP.I18n.NonSingularStringLiteralText -- module manifest strings are translated by the module text domain */ translate($m['name'], 'zappoppop') : $m['name'],
            'version'     => $m['version'],
            'author'      => $m['author'],
            'description' => ($m['source'] === 'core') ? /* phpcs:ignore WordPress.WP.I18n.LowLevelTranslationFunction, WordPress.WP.I18n.NonSingularStringLiteralText -- module manifest strings are translated by the module text domain */ translate($m['description'], 'zappoppop') : $m['description'],
            'source'      => $m['source'],
            'state'       => ZapPopPop_Modules::state($slug),
            'locked'      => (bool) $m['locked'],
            'can_edit'    => !isset($m['allow_edit']) || !empty($m['allow_edit']),
            'can_delete'  => !isset($m['allow_delete']) || !empty($m['allow_delete']),
            'can_manage'  => !isset($m['allow_manage']) || !empty($m['allow_manage']),
            'can_uninstall' => !isset($m['allow_uninstall']) || !empty($m['allow_uninstall']),
            'show_source' => !isset($m['show_source_pill']) || !empty($m['show_source_pill']),
            'show_state'  => !isset($m['show_state_pill']) || !empty($m['show_state_pill']),
            'pills'       => isset($m['pills']) ? array_values((array) $m['pills']) : [],
            'box'         => self::box_out($m), /* ZAPPOPPOP_EXT_BOX */
            'badge'       => self::badge_out($m), /* ZAPPOPPOP_EXT_BADGE */
            'deprecated'  => (bool) $m['deprecated'],
            'compatible'  => (bool) $m['compatible'],
            'requires'    => array_values($m['requires']),
            'required_by' => $by,
            'category'    => ZapPopPop_Module_Categories::of($m),
            'buttons'     => self::actions_out(isset($m['buttons']) ? $m['buttons'] : [], $m),
            'links'       => self::actions_out(isset($m['links']) ? $m['links'] : [], $m),
            'image'       => ZapPopPop_Module_Categories::image_of($m),
            'image_raw'   => ZapPopPop_Module_Categories::image_raw($m),
            'image_base'  => ZapPopPop_Modules::module_url($m),
        ];
    }

    /** Hex colour (#rgb, #rgba, #rrggbb, #rrggbbaa) or ''. */
    protected static function hex($v) {
        return (is_string($v) && preg_match('/^#(?:[0-9a-f]{3,4}|[0-9a-f]{6}|[0-9a-f]{8})$/i', trim($v)) === 1) ? strtolower(trim($v)) : '';
    }

    /**
     * ZAPPOPPOP_EXT_COLORS: default badge + card colours per kind (pro / core / custom) from defaults/extend-colors.json.
     * Pro or a theme can change them with the zappoppop_extend_colors filter. Empty colour = follow the Theme page colours.
     */
    protected static function extend_colors() {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }
        $base = [];
        foreach (['pro' => 'PRO', 'core' => 'CORE', 'custom' => ''] as $k => $label) {
            $base[$k] = [
                'badge' => ['enabled' => $k !== 'custom', 'text' => $label, 'bg' => '', 'color' => ''],
                'box'   => ['bg' => '', 'color' => '', 'border' => ''],
            ];
        }
        $raw  = [];
        $file = ZAPPOPPOP_PATH . 'defaults/extend-colors.json';
        if (is_file($file) && !is_link($file) && is_readable($file) && filesize($file) <= 20000) {
            /* phpcs:ignore WordPress.WP.AlternativeFunctions -- reads a local plugin file, not a remote URL */ $j = json_decode((string) file_get_contents($file), true);
            if (is_array($j)) {
                $raw = $j;
            }
        }
        $raw = apply_filters('zappoppop_extend_colors', $raw);
        if (!is_array($raw)) {
            $raw = [];
        }
        foreach ($base as $k => $unused) {
            if (!isset($raw[$k]) || !is_array($raw[$k])) {
                continue;
            }
            $b = (isset($raw[$k]['badge']) && is_array($raw[$k]['badge'])) ? $raw[$k]['badge'] : [];
            $x = (isset($raw[$k]['box']) && is_array($raw[$k]['box'])) ? $raw[$k]['box'] : [];
            if (array_key_exists('enabled', $b)) {
                $base[$k]['badge']['enabled'] = !in_array(is_string($b['enabled']) ? strtolower(trim($b['enabled'])) : $b['enabled'], [false, 0, null, '', '0', 'false', 'no', 'off'], true);
            }
            if (isset($b['text']) && is_scalar($b['text'])) {
                $base[$k]['badge']['text'] = mb_substr(trim(wp_strip_all_tags((string) $b['text'])), 0, 12);
            }
            foreach (['bg', 'color'] as $f) {
                if (isset($b[$f])) {
                    $base[$k]['badge'][$f] = self::hex($b[$f]);
                }
            }
            foreach (['bg', 'color', 'border'] as $f) {
                if (isset($x[$f])) {
                    $base[$k]['box'][$f] = self::hex($x[$f]);
                }
            }
        }
        $cache = $base;
        return $cache;
    }

    protected static function kind_of($m) {
        return ($m['source'] === 'pro') ? 'pro' : (($m['source'] === 'core') ? 'core' : 'custom');
    }

    /** Thumbnail corner badge: module.json "badge" > defaults/extend-colors.json > theme colours. null = hidden. */
    protected static function badge_out($m) {
        $kind = self::kind_of($m);
        $d    = self::extend_colors();
        $d    = $d[$kind]['badge'];
        $b    = (isset($m['badge']) && is_array($m['badge'])) ? $m['badge'] : [];
        $on   = (array_key_exists('enabled', $b) && $b['enabled'] !== null) ? !empty($b['enabled']) : !empty($d['enabled']);
        $text = (isset($b['text']) && $b['text'] !== '') ? $b['text'] : $d['text'];
        if (!$on || $text === '') {
            return null;
        }
        return [
            'kind'  => $kind,
            'text'  => $text,
            'bg'    => (isset($b['bg']) && $b['bg'] !== '') ? $b['bg'] : $d['bg'],
            'color' => (isset($b['color']) && $b['color'] !== '') ? $b['color'] : $d['color'],
        ];
    }

    /** Card colours: module.json "box" > defaults/extend-colors.json > CSS fallback (pro light orange, core light dark blue). */
    protected static function box_out($m) {
        $kind = self::kind_of($m);
        $d    = self::extend_colors();
        $d    = $d[$kind]['box'];
        $b    = (isset($m['box']) && is_array($m['box'])) ? $m['box'] : [];
        $out  = ['kind' => $kind];
        foreach (['bg', 'color', 'border'] as $f) {
            $out[$f] = (isset($b[$f]) && $b[$f] !== '') ? $b[$f] : $d[$f];
        }
        return $out;
    }

    /** Turn manifest buttons/links into final hrefs: absolute and /site paths as-is, page.php?x=1 -> admin URL, anything else -> module folder file. */
    protected static function actions_out($list, $m) {
        $out = [];
        foreach ((array) $list as $b) {
            $u = $b['url'];
            if (preg_match('#^(?:https?:|mailto:|tel:|/)#i', $u) !== 1) {
                if (preg_match('#^[a-z0-9_-]+\.php(?:\?|$)#i', $u) === 1) {
                    $u = admin_url($u);
                } else {
                    $base = ZapPopPop_Modules::module_url($m);
                    $u    = $base === '' ? '' : $base . $u;
                }
            }
            if ($u !== '') {
                $b['url'] = $u;
                $out[]    = $b;
            }
        }
        return $out;
    }

    protected static function names($slugs) {
        $out = [];
        foreach ($slugs as $s) {
            $m     = ZapPopPop_Modules::get($s);
            $out[] = $m ? $m['name'] : $s;
        }
        return implode(', ', $out);
    }

    /* ------------------------------------------------------ popup data */

    protected static function each_popup($cb) {
        global $wpdb;
        $table = $wpdb->prefix . 'zappoppop_items';
        $last  = 0;
        while (true) {
            /* phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- table name is built from $wpdb->prefix and every value is prepared */ $rows = $wpdb->get_results($wpdb->prepare("SELECT id, uid, title, data FROM {$table} WHERE id > %d ORDER BY id ASC LIMIT 200", $last), ARRAY_A);
            if (!$rows) {
                break;
            }
            foreach ($rows as $row) {
                $last = (int) $row['id'];
                $cb($row);
            }
        }
    }

    /** True when a scalar is truthy or an array holds at least one truthy leaf. */
    protected static function any_truthy($v) {
        if (is_array($v)) {
            foreach ($v as $x) {
                if (self::any_truthy($x)) {
                    return true;
                }
            }
            return false;
        }
        return ZapPopPop_Modules::truthy($v);
    }

    /** How many popups really use the module (in_use paths, else any stored data key). */
    public static function usage($slug) {
        $m = ZapPopPop_Modules::get($slug);
        if (!$m) {
            return ['count' => 0, 'titles' => []];
        }
        $paths = $m['in_use'] ? $m['in_use'] : $m['data_keys'];
        if (!$paths) {
            return ['count' => 0, 'titles' => []];
        }
        $count  = 0;
        $titles = [];
        self::each_popup(function ($row) use ($paths, &$count, &$titles) {
            $d = json_decode((string) $row['data'], true);
            $s = (is_array($d) && isset($d['settings']) && is_array($d['settings'])) ? $d['settings'] : [];
            foreach ($paths as $p) {
                if (self::any_truthy(ZapPopPop_Modules::path_value($s, $p))) {
                    $count++;
                    if (count($titles) < 5) {
                        $titles[] = (string) $row['title'];
                    }
                    break;
                }
            }
        });
        return ['count' => $count, 'titles' => $titles];
    }

    /** Settings keys owned only by this module. */
    protected static function owned_keys($m) {
        $others = [];
        foreach (ZapPopPop_Modules::all() as $slug => $o) {
            if ($slug !== $m['slug']) {
                foreach ($o['data_keys'] as $k) {
                    $others[$k] = true;
                }
            }
        }
        return array_values(array_filter($m['data_keys'], function ($k) use ($others) {
            return !isset($others[$k]);
        }));
    }

    /** "Remove data": strip the module's keys from every popup and drop its stored toggle / order slot. */
    public static function purge_data($slug) {
        $m = ZapPopPop_Modules::get($slug);
        if (!$m || $m['locked']) {
            return 0;
        }
        global $wpdb;
        $table   = $wpdb->prefix . 'zappoppop_items';
        $keys    = self::owned_keys($m);
        $changed = 0;
        if ($keys) {
            self::each_popup(function ($row) use ($keys, $wpdb, $table, &$changed) {
                $o = json_decode((string) $row['data']);
                if (!is_object($o) || !isset($o->settings) || !is_object($o->settings)) {
                    return;
                }
                $hit = false;
                foreach ($keys as $k) {
                    if (property_exists($o->settings, $k)) {
                        unset($o->settings->$k);
                        $hit = true;
                    }
                }
                if ($hit) {
                    $enc = wp_json_encode($o);
                    if (is_string($enc) && $enc !== '') {
                        $wpdb->update($table, ['data' => $enc], ['id' => (int) $row['id']], ['%s'], ['%d']);
                        $changed++;
                    }
                }
            });
        }
        $opt = get_option('zappoppop_settings', []);
        if (is_array($opt)) {
            $dirty = false;
            if (isset($opt['modules']) && is_array($opt['modules']) && array_key_exists($slug, $opt['modules'])) {
                unset($opt['modules'][$slug]);
                $dirty = true;
            }
            if (isset($opt['module_order']) && is_array($opt['module_order']) && in_array($slug, $opt['module_order'], true)) {
                $opt['module_order'] = array_values(array_diff($opt['module_order'], [$slug]));
                $dirty = true;
            }
            if ($dirty) {
                update_option('zappoppop_settings', $opt);
            }
        }
        return $changed;
    }

    /* ------------------------------------------------------ install / uninstall / delete */

    /**
     * Modules listed on the Extend page: every valid folder with a module.json, i.e. everything in /modules
     * (built-in or custom) and every upload, in any state.
     */
    public static function extend_items() {
        $rank = ['available' => 0, 'deleted' => 1, 'installed' => 2];
        $out  = [];
        foreach (ZapPopPop_Modules::all() as $m) {
            $out[] = self::summary($m);
        }
        usort($out, function ($a, $b) use ($rank) {
            $ra = $a['locked'] ? 3 : (isset($rank[$a['state']]) ? $rank[$a['state']] : 2);
            $rb = $b['locked'] ? 3 : (isset($rank[$b['state']]) ? $rank[$b['state']] : 2);
            return $ra === $rb ? strcasecmp($a['name'], $b['name']) : $ra - $rb;
        });
        return $out;
    }

    /** Data shared by the Module tab and the Extend page scripts. */
    public static function js_vars($extra = []) {
        $vars = [
            'ajax_url'   => admin_url('admin-ajax.php'),
            'nonce'      => wp_create_nonce('zappoppop_admin_nonce'),
            'extend_url' => admin_url('admin.php?page=zappoppop-extend'),
            'modules_url' => admin_url('admin.php?page=zappoppop-settings#module'),
            'categories'   => ZapPopPop_Module_Categories::all(),
            'cat_max'      => ZapPopPop_Module_Categories::MAX,
            'cat_name_max' => ZapPopPop_Module_Categories::NAME_MAX,
            'i18n'       => [
                'cancel'          => __('Cancel', 'zappoppop'),
                'install'         => __('Install', 'zappoppop'),
                'restore'         => __('Reinstall', 'zappoppop'),
                'uninstall'       => __('Uninstall', 'zappoppop'),
                'delete'          => __('Delete', 'zappoppop'),
                'manage'          => __('Manage', 'zappoppop'),
                'working'         => __('Working…', 'zappoppop'),
                'error_generic'   => __('Something went wrong. Please try again.', 'zappoppop'),
                'uninstall_title' => __('Uninstall module', 'zappoppop'),
                /* translators: %s: module name */
                'uninstall_msg'   => __('Uninstall "%s"? It stops working in the builder and on the frontend. Its files and saved popup data are kept, and you can reinstall it from Modules.', 'zappoppop'),
                'delete_title'    => __('Delete module', 'zappoppop'),
                /* translators: %s: module name */
                'delete_msg_core' => __('Delete "%s"? It is removed from your modules and can be restored from Modules.', 'zappoppop'),
                'remove_data'     => __('Remove data: also erase this module\'s saved data from all popups', 'zappoppop'),
                'in_use_title'    => __('Module is in use', 'zappoppop'),
                'in_use_msg'      => __('Popups that use it will lose this feature until it is installed again. Continue?', 'zappoppop'),
                'continue_anyway' => __('Continue', 'zappoppop'),
                'installed_ok'    => __('Module installed.', 'zappoppop'),
                'manage_link'     => __('Manage in Settings', 'zappoppop'),
                'extend_link'     => __('Open Modules', 'zappoppop'),
                'empty'           => __('No modules match.', 'zappoppop'),
                'builtin'         => __('Built-in', 'zappoppop'),
                'addon'            => __('Add-on', 'zappoppop'),
                'locked'          => __('Locked', 'zappoppop'),
                'deprecated'      => __('Deprecated', 'zappoppop'),
                'st_available'    => __('Available', 'zappoppop'),
                'st_installed'    => __('Installed', 'zappoppop'),
                'f_all'           => __('All', 'zappoppop'),
                'f_installed'     => __('Installed', 'zappoppop'),
                'f_uninstalled'   => __('Uninstalled', 'zappoppop'),
                'f_label'         => __('Filter modules', 'zappoppop'),
                'st_deleted'      => __('Deleted', 'zappoppop'),
                /* translators: %s: plugin version */
                'needs_version'   => __('Needs a newer ZapPopPop version.', 'zappoppop'),
                /* translators: %s: version number */
                'version'         => __('Version %s', 'zappoppop'),
                /* translators: %s: author name */
                'by'              => __('By %s', 'zappoppop'),
                /* translators: %s: module names */
                'requires'        => __('Requires: %s', 'zappoppop'),
                'no_match'        => __('No modules match your search.', 'zappoppop'),
                'cat_label'       => __('Category', 'zappoppop'),
                /* translators: %s: module name */
                'cat_for'         => __('Category for %s', 'zappoppop'),
                'cat_manage'      => __('Manage categories', 'zappoppop'),
                'cat_name'        => __('Category name', 'zappoppop'),
                'cat_default'     => __('Default', 'zappoppop'),
                'cat_up'          => __('Move up', 'zappoppop'),
                'cat_down'        => __('Move down', 'zappoppop'),
                'cat_remove'      => __('Delete category', 'zappoppop'),
                'cat_empty'       => __('Enter a category name.', 'zappoppop'),
                /* translators: %s: category name */
                'cat_dup'         => __('"%s" is already used by another category.', 'zappoppop'),
                /* translators: %s: maximum number of categories */
                'cat_max_msg'     => __('You can have up to %s categories.', 'zappoppop'),
                'cat_save'        => __('Save Categories', 'zappoppop'),
                'cat_saving'      => __('Saving…', 'zappoppop'),
                'cat_saved'       => __('Categories saved.', 'zappoppop'),
                'edit'            => __('Edit', 'zappoppop'),
                /* translators: %s: module name */
                'edit_title'      => __('Edit %s', 'zappoppop'),
                'edit_note'       => __('The category and image URL are saved in the database.', 'zappoppop'),
                'edit_image'      => __('Image URL', 'zappoppop'),
                'edit_img_bad'    => __('Enter a valid image URL or path.', 'zappoppop'),
                'edit_save'       => __('Save', 'zappoppop'),
                'edit_saving'     => __('Saving…', 'zappoppop'),
            ],
        ];
        return array_merge($vars, $extra);
    }

    public static function install($slug) {
        $m = ZapPopPop_Modules::get($slug);
        if (!$m) {
            return self::err('not_found', __('Module not found.', 'zappoppop'));
        }
        if (!$m['compatible']) {
            /* translators: %s: minimum plugin version */
            return self::err('incompatible', sprintf(__('This module needs ZapPopPop %s or newer.', 'zappoppop'), $m['min_version']));
        }
        if ($m['source'] === 'pro' && !ZapPopPop_Pro_API::is_licensed()) {
            return self::err('pro_locked', __('Activate your ZapPopPop Pro licence to install this module.', 'zappoppop'));
        }
        $missing = [];
        foreach ($m['requires'] as $r) {
            if (!ZapPopPop_Modules::is_installed($r)) {
                $missing[] = $r;
            }
        }
        if ($missing) {
            /* translators: %s: module names */
            return self::err('requires', sprintf(__('Install the required modules first: %s', 'zappoppop'), self::names($missing)), ['requires' => $missing]);
        }
        if (!$m['locked']) {
            ZapPopPop_Modules::set_state($slug, 'installed');
        }
        do_action('zappoppop_module_installed', $slug);
        return ['module' => self::summary(ZapPopPop_Modules::get($slug)), 'message' => __('Module installed.', 'zappoppop')];
    }

    /** Shared checks for uninstall and delete. Returns the manifest or a WP_Error. */
    protected static function guard_removal($slug, $force) {
        $m = ZapPopPop_Modules::get($slug);
        if (!$m) {
            return self::err('not_found', __('Module not found.', 'zappoppop'));
        }
        if ($m['locked']) {
            return self::err('locked', __('Locked modules cannot be uninstalled or deleted.', 'zappoppop'));
        }
        $by = array_keys(ZapPopPop_Modules::required_by($slug));
        if ($by) {
            /* translators: %s: module names */
            return self::err('required_by', sprintf(__('Required by: %s. Uninstall those first.', 'zappoppop'), self::names($by)), ['required_by' => $by]);
        }
        if (!$force) {
            $u = self::usage($slug);
            if ($u['count'] > 0) {
                /* translators: %d: number of popups */
                return self::err('in_use', sprintf(_n('%d popup uses this module.', '%d popups use this module.', $u['count'], 'zappoppop'), $u['count']), $u);
            }
        }
        return $m;
    }

    public static function uninstall($slug, $force = false, $remove_data = false) {
        $m = self::guard_removal($slug, $force);
        if (is_wp_error($m)) {
            return $m;
        }
        if (isset($m['allow_uninstall']) && !$m['allow_uninstall']) {
            return self::err('no_uninstall', __('Uninstalling is disabled for this module in its module.json.', 'zappoppop'));
        }
        if (ZapPopPop_Modules::state($slug) !== 'installed') {
            return self::err('not_installed', __('This module is not installed.', 'zappoppop'));
        }
        ZapPopPop_Modules::set_state($slug, 'available');
        if ($remove_data) {
            self::purge_data($slug);
        }
        do_action('zappoppop_module_uninstalled', $slug, (bool) $remove_data);
        return ['module' => self::summary(ZapPopPop_Modules::get($slug)), 'message' => __('Module uninstalled.', 'zappoppop')];
    }

    public static function delete($slug, $force = false, $remove_data = false) {
        $m = self::guard_removal($slug, $force);
        if (is_wp_error($m)) {
            return $m;
        }
        if (isset($m['allow_delete']) && !$m['allow_delete']) {
            return self::err('no_delete', __('Deleting is disabled for this module in its module.json.', 'zappoppop'));
        }
        if (ZapPopPop_Modules::state($slug) === 'deleted') {
            return self::err('deleted', __('This module is already deleted.', 'zappoppop'));
        }
        if ($remove_data) {
            self::purge_data($slug);
        }
        ZapPopPop_Modules::set_state($slug, 'deleted');
        do_action('zappoppop_module_deleted', $slug, (bool) $remove_data);
        return ['slug' => $slug, 'message' => __('Module deleted.', 'zappoppop')];
    }

    /** Installed/uninstalled/deleted state follows a renamed slug (runs before seeding, so nothing is reinstalled by mistake). */
    protected static function migrate_renamed_state() {
        $st    = ZapPopPop_Modules::states();
        $dirty = false;
        foreach (self::RENAMED as $old => $new) {
            if (!array_key_exists($old, $st)) {
                continue;
            }
            if (!array_key_exists($new, $st)) {
                $st[$new] = $st[$old];
            }
            unset($st[$old]);
            $dirty = true;
        }
        if ($dirty) {
            update_option(ZapPopPop_Modules::STATE_OPT, $st, false);
            ZapPopPop_Modules::flush();
        }
    }

    /** The on/off switch and the position in Popup Settings follow a renamed slug (runs once the new module is registered). */
    protected static function migrate_renamed_settings() {
        $s = get_option('zappoppop_settings', []);
        if (!is_array($s)) {
            return;
        }
        $dirty = false;
        foreach (self::RENAMED as $old => $new) {
            if (!ZapPopPop_Modules::get($new)) {
                continue;
            }
            if (isset($s['modules']) && is_array($s['modules']) && array_key_exists($old, $s['modules'])) {
                if (!array_key_exists($new, $s['modules'])) {
                    $s['modules'][$new] = $s['modules'][$old];
                }
                unset($s['modules'][$old]);
                $dirty = true;
            }
            if (isset($s['module_order']) && is_array($s['module_order'])) {
                $i = array_search($old, $s['module_order'], true);
                if ($i !== false) {
                    if (in_array($new, $s['module_order'], true)) {
                        unset($s['module_order'][$i]);
                    } else {
                        $s['module_order'][$i] = $new;
                    }
                    $s['module_order'] = array_values($s['module_order']);
                    $dirty = true;
                }
            }
        }
        if ($dirty) {
            update_option('zappoppop_settings', $s);
        }
    }

    /**
     * Carries state and settings of renamed modules over to their new slugs.
     * Bundled modules live in the plugin's modules/ folder, so nothing is copied into uploads any more.
     */
    public static function migrate_legacy() {
        if (!current_user_can('manage_options')) {
            return;
        }
        self::migrate_renamed_state();
        self::migrate_renamed_settings();
        self::purge_legacy_uploads();
        add_action('admin_notices', [__CLASS__, 'legacy_notice']);
    }

    /** Folder written by older versions that accepted module uploads. Nothing is read from it any more. */
    protected static function legacy_dir() {
        $u = wp_upload_dir(null, false);
        return empty($u['basedir']) ? '' : rtrim($u['basedir'], '/\\') . '/zappoppop-modules';
    }

    /**
     * One-time clean-up. Older versions let administrators upload module zips (JS, CSS, HTML) into uploads/zappoppop-modules.
     * That code is no longer loaded or accepted, so the folder is removed. Popup data saved by those modules is kept
     * (their data keys stay protected by the orphan list); only the files and the stored module state go.
     */
    public static function purge_legacy_uploads() {
        if (get_option(self::LEGACY_OPT) === 'done') {
            return;
        }
        $dir   = self::legacy_dir();
        $names = [];
        if ($dir !== '' && is_dir($dir)) {
            $base = realpath(dirname($dir));
            $real = realpath($dir);
            if (is_link($dir) || !$base || !$real || strpos($real . '/', rtrim($base, '/\\') . '/') !== 0 || $real === $base) {
                return;
            }
            $dirs = glob($dir . '/*', GLOB_ONLYDIR);
            foreach ((array) $dirs as $d) {
                $m = self::read_uploaded($d);
                if (!$m) {
                    continue;
                }
                $names[] = $m['name'];
                if (!ZapPopPop_Modules::get($m['slug'])) {
                    if (!empty($m['data_keys'])) {
                        ZapPopPop_Modules::set_orphan($m['slug'], $m['data_keys']);
                    }
                    ZapPopPop_Modules::forget_state($m['slug']);
                }
            }
            self::rrmdir($dir);
            ZapPopPop_Modules::flush();
            if (is_dir($dir)) {
                return;
            }
        }
        if ($names) {
            update_option(self::NOTICE_OPT, array_values(array_unique($names)), false);
        }
        update_option(self::LEGACY_OPT, 'done');
    }

    /** Shown once, right after the old uploads folder was removed. */
    public static function legacy_notice() {
        $names = get_option(self::NOTICE_OPT);
        if (!is_array($names) || !$names || !current_user_can('manage_options')) {
            return;
        }
        delete_option(self::NOTICE_OPT);
        $names = array_map('wp_strip_all_tags', array_map('strval', $names));
        /* translators: %s: comma separated module names */
        $text = sprintf(__('ZapPopPop no longer loads modules uploaded as .zip files. These uploaded modules were removed: %s. Your popup data was kept.', 'zappoppop'), implode(', ', $names));
        echo '<div class="notice notice-warning is-dismissible"><p>' . esc_html($text) . '</p></div>';
    }

    /* ------------------------------------------------------ clean-up of the old uploads folder */

    protected static function rrmdir($dir) {
        if (!is_dir($dir) || is_link($dir)) {
            return;
        }
        $items = scandir($dir);
        if (!is_array($items)) {
            return;
        }
        foreach ($items as $i) {
            if ($i === '.' || $i === '..') {
                continue;
            }
            $p = $dir . '/' . $i;
            if (is_dir($p) && !is_link($p)) {
                self::rrmdir($p);
            } else {
                wp_delete_file($p);
            }
        }
        /* phpcs:ignore WordPress.WP.AlternativeFunctions -- local file or stream handle owned by this plugin; WP_Filesystem would need FTP credentials */ @rmdir($dir);
    }

    /** Manifest (name, version) of an installed upload, or null when it is missing or unreadable. */
    protected static function read_uploaded($dir) {
        $f = $dir . '/module.json';
        if (is_link($dir) || !is_file($f) || is_link($f) || filesize($f) > ZapPopPop_Modules::MAX_JSON) {
            return null;
        }
        /* phpcs:ignore WordPress.WP.AlternativeFunctions -- reads a local plugin or upload temp file, not a remote URL */ $raw = json_decode((string) file_get_contents($f), true);
        if (!is_array($raw)) {
            return null;
        }
        $m = ZapPopPop_Modules::normalize($raw, 'uploaded', basename($dir));
        return $m ? $m : null;
    }

}
