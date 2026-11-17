<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Module lifecycle: install / uninstall / delete / upload (module.zip).
 *   Install   = state "installed" (module is active in the builder and on the frontend).
 *   Uninstall = state "available" (files and popup data are kept, one click to reinstall).
 *   Delete    = uploaded module: files removed. Built-in module: state "deleted" (hidden until restored from Extend).
 * Locked modules can never be uninstalled or deleted. Every method returns an array or a WP_Error.
 */
class ZapPopPop_Modules_Installer {

    const MAX_ZIP      = 5242880;
    const MAX_FILES    = 200;
    const MAX_FILE     = 2097152;
    const MAX_UNPACKED = 10485760;
    const MAX_DEPTH    = 4;
    const RENAMED      = ['acc-20-animation' => 'acc-animation', 'acc-16-popup-overlay' => 'acc-popup-overlay', 'acc-17-popup-overlay' => 'acc-popup-overlay', 'acc-21-popup-positions' => 'acc-popup-positions', 'acc-18-notifications' => 'acc-notifications', 'acc-17-double-optin' => 'acc-double-optin', 'acc-22-targeting-triggers' => 'acc-targeting-triggers', 'acc-19-success-celebration' => 'acc-success-celebration', 'acc-08-autoresponder' => 'acc-autoresponder', 'acc-04-side-tabs' => 'acc-side-tabs', 'acc-09-webhooks' => 'acc-webhooks', 'acc-13-woocommerce' => 'acc-woocommerce', 'acc-06-downloads' => 'acc-downloads', 'acc-03-logic-math' => 'logic-math', 'acc-05-payments' => 'logic-payments', 'acc-07-video' => 'acc-video', 'acc-10-sms' => 'acc-sms', 'acc-11-targeting' => 'acc-targeting', 'acc-15-quiz' => 'acc-quiz', 'acc-12-frequency' => 'acc-frequency', 'acc-14-custom-code' => 'acc-custom-code'];
    const ALLOWED_EXT  = ['json', 'css', 'js', 'html', 'txt', 'md', 'png', 'jpg', 'jpeg', 'gif', 'webp', 'woff', 'woff2'];
    const BAD_SEGMENT  = '/^(php\d*|phtml|pht|phps|phar|cgi|pl|py|rb|sh|bash|asp|aspx|jsp|exe|dll|so|htaccess|htpasswd)$/i';

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

    /** Uploading or physically deleting module files: single-site admins and network super admins only. */
    public static function can_upload() {
        return self::can_manage() && self::file_mods_allowed() && (!is_multisite() || is_super_admin());
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
            'can_upload' => self::can_upload() && class_exists('ZipArchive'),
            'max_zip'    => self::MAX_ZIP,
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
                'uploading'       => __('Uploading…', 'zappoppop'),
                'upload'          => __('Upload Module', 'zappoppop'),
                'error_generic'   => __('Something went wrong. Please try again.', 'zappoppop'),
                'uninstall_title' => __('Uninstall module', 'zappoppop'),
                /* translators: %s: module name */
                'uninstall_msg'   => __('Uninstall "%s"? It stops working in the builder and on the frontend. Its files and saved popup data are kept, and you can reinstall it from Modules.', 'zappoppop'),
                'delete_title'    => __('Delete module', 'zappoppop'),
                /* translators: %s: module name */
                'delete_msg_uploaded' => __('Delete "%s"? Its files are removed from the uploads folder. This cannot be undone.', 'zappoppop'),
                /* translators: %s: module name */
                'delete_msg_core' => __('Delete "%s"? It is removed from your modules and can be restored from Modules.', 'zappoppop'),
                'remove_data'     => __('Remove data: also erase this module\'s saved data from all popups', 'zappoppop'),
                'in_use_title'    => __('Module is in use', 'zappoppop'),
                'in_use_msg'      => __('Popups that use it will lose this feature until it is installed again. Continue?', 'zappoppop'),
                'continue_anyway' => __('Continue', 'zappoppop'),
                /* translators: 1: module name, 2: installed version, 3: new version */
                'replace_msg'     => /* translators: 1: module name, 2: installed version, 3: new version. */ __('"%1$s" is already installed (version %2$s). Replace it with version %3$s? Its installed state and popup data are kept.', 'zappoppop'),
                'replace'         => __('Replace', 'zappoppop'),
                'replace_title'   => __('Replace module', 'zappoppop'),
                'cmp_current'     => __('Current module', 'zappoppop'),
                'cmp_new'         => __('Uploaded module', 'zappoppop'),
                'cmp_msg'         => __('The uploaded module has the same slug and name as the module in the uploads folder. Replace it with the uploaded version? Its installed state and popup data are kept.', 'zappoppop'),
                'cmp_msg_core'    => __('The uploaded module has the same slug and name as a module in /modules. Replace it? The older copies in /modules and in the uploads folder are removed, the uploaded one takes its place, and its installed state and popup data are kept.', 'zappoppop'),
                'pick_zip'        => __('Choose a module .zip file first.', 'zappoppop'),
                'not_zip'         => __('Only a .zip file can be uploaded.', 'zappoppop'),
                /* translators: %s: maximum size */
                'too_big'         => __('The zip is larger than the %s limit.', 'zappoppop'),
                'installed_ok'    => __('Module installed.', 'zappoppop'),
                'manage_link'     => __('Manage in Settings', 'zappoppop'),
                'extend_link'     => __('Open Modules', 'zappoppop'),
                'empty'           => __('No modules match.', 'zappoppop'),
                'builtin'         => __('Built-in', 'zappoppop'),
                'uploaded'        => __('Uploaded', 'zappoppop'),
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
                'edit_note'       => __('The category and image URL are saved to this module\'s module.json.', 'zappoppop'),
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
        if ($m['source'] === 'uploaded' && !self::can_upload()) {
            return self::err('forbidden', __('You are not allowed to delete module files on this site.', 'zappoppop'));
        }
        if (ZapPopPop_Modules::state($slug) === 'deleted') {
            return self::err('deleted', __('This module is already deleted.', 'zappoppop'));
        }
        if ($remove_data) {
            self::purge_data($slug);
        }
        if ($m['source'] === 'uploaded') {
            $dir  = $m['dir'];
            $real = realpath($dir);
            $root = realpath(ZapPopPop_Modules::uploads_dir());
            if (!$real || !$root || is_link($dir) || $real === $root || strpos($real . '/', rtrim($root, '/') . '/') !== 0) {
                return self::err('unsafe_path', __('Module folder is outside the modules directory.', 'zappoppop'));
            }
            self::rrmdir($dir);
            if (is_dir($dir)) {
                return self::err('delete_failed', __('Could not remove the module files. Check folder permissions.', 'zappoppop'));
            }
            ZapPopPop_Modules::forget_state($slug);
            if ($remove_data || !$m['data_keys']) {
                ZapPopPop_Modules::clear_orphan($slug);
            } else {
                ZapPopPop_Modules::set_orphan($slug, $m['data_keys']);
            }
        } else {
            ZapPopPop_Modules::set_state($slug, 'deleted');
        }
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
    }

    /* ------------------------------------------------------ zip upload */

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

    /** Uploaded (not yet installed) module as shown in the replace confirmation. Only absolute image URLs can be previewed. */
    protected static function incoming($m) {
        $img = isset($m['image']) ? (string) $m['image'] : '';
        return [
            'slug'        => $m['slug'],
            'name'        => $m['name'],
            'version'     => $m['version'],
            'author'      => $m['author'],
            'description' => $m['description'],
            'source'      => 'uploaded',
            'image'       => preg_match('#^(?:https?:)?//|^/(?!/)#i', $img) === 1 ? $img : '',
        ];
    }

    protected static function rand_name($prefix) {
        return $prefix . bin2hex(function_exists('random_bytes') ? random_bytes(6) : md5(uniqid('', true)));
    }

    /** Create the uploads/zappoppop-modules root with a silence file and a no-PHP rule. */
    protected static function prepare_root($base) {
        if (!is_dir($base) && !wp_mkdir_p($base) && !is_dir($base)) {
            return false;
        }
        if (!file_exists($base . '/index.php')) {
            /* phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions -- best-effort index.php guard; local file or stream handle owned by this plugin; WP_Filesystem would need FTP credentials */ @file_put_contents($base . '/index.php', "<?php\n// Silence is golden.\n");
        }
        if (!file_exists($base . '/.htaccess')) {
            $rule  = "# ZapPopPop modules: static assets only.\n";
            $rule .= "<IfModule mod_authz_core.c>\n<FilesMatch \"\\.(php[0-9]*|phtml|pht|phar)$\">\nRequire all denied\n</FilesMatch>\n</IfModule>\n";
            $rule .= "<IfModule !mod_authz_core.c>\n<FilesMatch \"\\.(php[0-9]*|phtml|pht|phar)$\">\nOrder allow,deny\nDeny from all\n</FilesMatch>\n</IfModule>\n";
            /* phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions -- best-effort access rules; local file or stream handle owned by this plugin; WP_Filesystem would need FTP credentials */ @file_put_contents($base . '/.htaccess', $rule);
        }
        return is_dir($base) && wp_is_writable($base);
    }

    protected static function junk($name) {
        $base = strtolower(basename(rtrim($name, '/')));
        return strpos($name, '__MACOSX/') === 0 || $base === '.ds_store' || $base === 'thumbs.db' || strpos($base, '._') === 0;
    }

    /** Normalised entry name, or null when it is unsafe. */
    protected static function clean_name($name) {
        if ($name === '' || strpos($name, "\0") !== false || strpos($name, '\\') !== false || $name[0] === '/' || preg_match('/^[a-zA-Z]:/', $name)) {
            return null;
        }
        $dir = substr($name, -1) === '/';
        foreach (explode('/', rtrim($name, '/')) as $seg) {
            if ($seg === '' || strlen($seg) > 100 || !preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]*$/', $seg)) {
                return null;
            }
        }
        return rtrim($name, '/') . ($dir ? '/' : '');
    }

    protected static function extract($zip, $dest) {
        $n = (int) $zip->numFiles;
        if ($n < 1 || $n > self::MAX_FILES) {
            return self::err('bad_zip', __('The zip is empty or contains too many files.', 'zappoppop'));
        }
        $entries = [];
        for ($i = 0; $i < $n; $i++) {
            $orig = $zip->getNameIndex($i, ZipArchive::FL_UNCHANGED);
            if (!is_string($orig)) {
                return self::err('bad_zip', __('The zip could not be read.', 'zappoppop'));
            }
            if (self::junk($orig)) {
                continue;
            }
            $clean = self::clean_name($orig);
            if ($clean === null) {
                /* translators: %s: file name inside the zip */
                return self::err('unsafe_path', sprintf(__('Unsafe file name in zip: %s', 'zappoppop'), substr(preg_replace('/[^\x20-\x7e]/', '?', $orig), 0, 80)));
            }
            $op   = 0;
            $attr = 0;
            if ($zip->getExternalAttributesIndex($i, $op, $attr) && $op === ZipArchive::OPSYS_UNIX && ((($attr >> 16) & 0170000) === 0120000)) {
                return self::err('unsafe_path', __('Symbolic links are not allowed in a module zip.', 'zappoppop'));
            }
            $entries[] = ['orig' => $orig, 'name' => $clean, 'dir' => substr($clean, -1) === '/'];
        }
        $names  = array_column($entries, 'name');
        $prefix = '';
        if (!in_array('module.json', $names, true)) {
            $tops = [];
            foreach ($names as $nm) {
                $tops[explode('/', $nm)[0]] = true;
            }
            $tops = array_keys($tops);
            if (count($tops) !== 1 || !in_array($tops[0] . '/module.json', $names, true)) {
                return self::err('no_manifest', __('module.json was not found at the top of the zip.', 'zappoppop'));
            }
            $prefix = $tops[0] . '/';
        }
        $total = 0;
        $files = 0;
        foreach ($entries as $e) {
            $rel = substr($e['name'], strlen($prefix));
            if ($rel === '' || $rel === false || ($prefix !== '' && $e['name'] === rtrim($prefix, '/'))) {
                continue;
            }
            $segs = explode('/', rtrim($rel, '/'));
            if (count($segs) > self::MAX_DEPTH) {
                return self::err('too_deep', __('The zip has folders nested too deeply.', 'zappoppop'));
            }
            $target = $dest . '/' . rtrim($rel, '/');
            if ($e['dir']) {
                if (!is_dir($target) && !wp_mkdir_p($target)) {
                    return self::err('write_failed', __('Could not create a folder for the module.', 'zappoppop'));
                }
                continue;
            }
            $file = end($segs);
            foreach (explode('.', $file) as $part) {
                if (preg_match(self::BAD_SEGMENT, $part)) {
                    /* translators: %s: file name */
                    return self::err('bad_type', sprintf(__('File type not allowed in a module: %s', 'zappoppop'), $file));
                }
            }
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($ext, self::ALLOWED_EXT, true)) {
                /* translators: %s: file name */
                return self::err('bad_type', sprintf(__('File type not allowed in a module: %s', 'zappoppop'), $file));
            }
            $parent = dirname($target);
            if (!is_dir($parent) && !wp_mkdir_p($parent)) {
                return self::err('write_failed', __('Could not create a folder for the module.', 'zappoppop'));
            }
            $in = $zip->getStream($e['orig']);
            if (!$in) {
                return self::err('bad_zip', __('The zip could not be read.', 'zappoppop'));
            }
            /* phpcs:ignore WordPress.WP.AlternativeFunctions -- local file or stream handle owned by this plugin; WP_Filesystem would need FTP credentials */ $out = @fopen($target, 'wb');
            if (!$out) {
                /* phpcs:ignore WordPress.WP.AlternativeFunctions -- local file or stream handle owned by this plugin; WP_Filesystem would need FTP credentials */ fclose($in);
                return self::err('write_failed', __('Could not write a module file.', 'zappoppop'));
            }
            $size = 0;
            while (!feof($in)) {
                /* phpcs:ignore WordPress.WP.AlternativeFunctions -- local file or stream handle owned by this plugin; WP_Filesystem would need FTP credentials */ $buf = fread($in, 8192);
                if ($buf === false || $buf === '') {
                    break;
                }
                $size  += strlen($buf);
                $total += strlen($buf);
                if ($size > self::MAX_FILE || $total > self::MAX_UNPACKED) {
                    /* phpcs:ignore WordPress.WP.AlternativeFunctions -- local file or stream handle owned by this plugin; WP_Filesystem would need FTP credentials */ fclose($in);
                    /* phpcs:ignore WordPress.WP.AlternativeFunctions -- local file or stream handle owned by this plugin; WP_Filesystem would need FTP credentials */ fclose($out);
                    return self::err('too_big', __('The module is too large once unpacked.', 'zappoppop'));
                }
                /* phpcs:ignore WordPress.WP.AlternativeFunctions -- local file or stream handle owned by this plugin; WP_Filesystem would need FTP credentials */ fwrite($out, $buf);
            }
            /* phpcs:ignore WordPress.WP.AlternativeFunctions -- local file or stream handle owned by this plugin; WP_Filesystem would need FTP credentials */ fclose($in);
            /* phpcs:ignore WordPress.WP.AlternativeFunctions -- local file or stream handle owned by this plugin; WP_Filesystem would need FTP credentials */ fclose($out);
            /* phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions -- best-effort permission normalisation; local file or stream handle owned by this plugin; WP_Filesystem would need FTP credentials */ @chmod($target, 0644);
            $files++;
        }
        if ($files < 1 || !is_file($dest . '/module.json')) {
            return self::err('no_manifest', __('module.json was not found at the top of the zip.', 'zappoppop'));
        }
        return true;
    }

    /**
     * Install a module from an uploaded zip. The module lands as "Available"; it is activated only by Install.
     * $replace = true overwrites an existing uploaded module with the same slug (its installed state is kept).
     */
    public static function install_zip($file, $replace = false) {
        if (!self::can_upload()) {
            return self::err('forbidden', __('You are not allowed to upload modules on this site.', 'zappoppop'));
        }
        if (!class_exists('ZipArchive')) {
            return self::err('no_zip', __('The PHP zip extension is required to upload modules.', 'zappoppop'));
        }
        if (!is_string($file) || !is_file($file) || !is_readable($file)) {
            return self::err('bad_file', __('The uploaded file could not be read.', 'zappoppop'));
        }
        $size = filesize($file);
        if ($size < 1 || $size > self::MAX_ZIP) {
            /* translators: %s: size limit */
            return self::err('too_big', sprintf(__('The zip must be smaller than %s.', 'zappoppop'), size_format(self::MAX_ZIP)));
        }
        $base = ZapPopPop_Modules::uploads_dir();
        if ($base === '' || !self::prepare_root($base)) {
            return self::err('no_dir', __('The modules folder could not be created in uploads.', 'zappoppop'));
        }
        $tmp = $base . '/' . self::rand_name('.tmp-');
        /* phpcs:ignore WordPress.WP.AlternativeFunctions -- local file or stream handle owned by this plugin; WP_Filesystem would need FTP credentials */ if (!@mkdir($tmp, 0755)) {
            return self::err('no_dir', __('The modules folder is not writable.', 'zappoppop'));
        }
        $zip = new ZipArchive();
        if ($zip->open($file) !== true) {
            self::rrmdir($tmp);
            return self::err('bad_zip', __('This is not a valid zip file.', 'zappoppop'));
        }
        $res = self::extract($zip, $tmp);
        $zip->close();
        if (is_wp_error($res)) {
            self::rrmdir($tmp);
            return $res;
        }
        $mf = $tmp . '/module.json';
        if (is_link($mf) || filesize($mf) > ZapPopPop_Modules::MAX_JSON) {
            self::rrmdir($tmp);
            return self::err('bad_manifest', __('module.json is missing or too large.', 'zappoppop'));
        }
        /* phpcs:ignore WordPress.WP.AlternativeFunctions -- reads a local plugin or upload temp file, not a remote URL */ $raw  = json_decode((string) file_get_contents($mf), true);
        $slug = (is_array($raw) && isset($raw['slug']) && is_string($raw['slug'])) ? $raw['slug'] : '';
        if (!ZapPopPop_Modules::slug_valid($slug)) {
            self::rrmdir($tmp);
            return self::err('bad_slug', __('module.json needs a valid "slug" (lowercase letters, numbers and dashes).', 'zappoppop'));
        }
        if (!isset($raw['category']) || !is_string($raw['category']) || trim($raw['category']) === '') {
            $raw['category'] = ZapPopPop_Module_Categories::DEFAULT_SLUG;
            $json = wp_json_encode($raw, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            if (is_string($json) && $json !== '') {
                /* phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions -- best-effort manifest write; local file or stream handle owned by this plugin; WP_Filesystem would need FTP credentials */ @file_put_contents($mf, $json . "\n");
            }
        }
        $m = ZapPopPop_Modules::normalize($raw, 'uploaded', $slug);
        if (!$m) {
            self::rrmdir($tmp);
            return self::err('bad_manifest', __('module.json is not a valid ZapPopPop module manifest.', 'zappoppop'));
        }
        if (!empty($m['html']) && (!is_file($tmp . '/accordion.html') || is_link($tmp . '/accordion.html') || filesize($tmp . '/accordion.html') > ZapPopPop_Modules::MAX_HTML)) {
            self::rrmdir($tmp);
            return self::err('bad_manifest', __('accordion.html is missing or too large.', 'zappoppop'));
        }
        if (!empty($m['html']) && !empty($m['data_keys']) && (!is_file($tmp . '/admin.js') || is_link($tmp . '/admin.js'))) {
            self::rrmdir($tmp);
            return self::err('bad_manifest', __('This module saves settings, so it needs an admin.js.', 'zappoppop'));
        }
        $core_path = ZapPopPop_Modules::core_dir() . '/' . $slug;
        $old_core  = null; // built-in modules are never replaced
        if (is_dir($core_path)) {
            // Built-in modules live in the plugin folder, which must never be modified.
            self::rrmdir($tmp);
            return self::err('core_slug', __('This slug belongs to a built-in module and cannot be uploaded. Use a different slug.', 'zappoppop'));
        }
        $dest   = $base . '/' . $slug;
        $exists = is_dir($dest);
        $old_up = $exists ? self::read_uploaded($dest) : null;
        if ($old_up) {
            if (strcasecmp(trim($old_up['name']), trim($m['name'])) !== 0) {
                self::rrmdir($tmp);
                /* translators: 1: module name in the uploads folder, 2: uploaded module name */
                return self::err('name_mismatch', sprintf(__('The module with this slug in the uploads folder is named "%1$s", not "%2$s", so it cannot be replaced.', 'zappoppop'), $old_up['name'], $m['name']));
            }
            if (version_compare($m['version'], $old_up['version'], '<')) {
                self::rrmdir($tmp);
                /* translators: 1: module name, 2: version in the uploads folder, 3: uploaded version */
                return self::err('older_version', sprintf(__('"%1$s" in the uploads folder is version %2$s, which is newer than the uploaded version %3$s.', 'zappoppop'), $old_up['name'], $old_up['version'], $m['version']));
            }
        }
        if (($exists || $old_core) && !$replace) {
            self::rrmdir($tmp);
            $old = $old_core ? $old_core : ZapPopPop_Modules::get($slug);
            return self::err('exists', __('A module with this slug is already installed.', 'zappoppop'), [
                'slug'        => $slug,
                'name'        => $m['name'],
                'old_version' => $old ? $old['version'] : '',
                'new_version' => $m['version'],
                'old'         => $old ? self::summary($old) : null,
                'new'         => self::incoming($m),
            ]);
        }
        $prev_state = $old_core ? ZapPopPop_Modules::state($slug) : '';
        /* phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions -- best-effort index.php guard; local file or stream handle owned by this plugin; WP_Filesystem would need FTP credentials */ @file_put_contents($tmp . '/index.php', "<?php\n// Silence is golden.\n");
        if ($exists) {
            $old = $base . '/' . self::rand_name('.old-');
            /* phpcs:ignore WordPress.WP.AlternativeFunctions -- local file or stream handle owned by this plugin; WP_Filesystem would need FTP credentials */ if (is_link($dest) || !@rename($dest, $old)) {
                self::rrmdir($tmp);
                return self::err('replace_failed', __('The existing module could not be replaced.', 'zappoppop'));
            }
            /* phpcs:ignore WordPress.WP.AlternativeFunctions -- local file or stream handle owned by this plugin; WP_Filesystem would need FTP credentials */ if (!@rename($tmp, $dest)) {
                /* phpcs:ignore WordPress.WP.AlternativeFunctions -- local file or stream handle owned by this plugin; WP_Filesystem would need FTP credentials */ @rename($old, $dest);
                self::rrmdir($tmp);
                return self::err('replace_failed', __('The existing module could not be replaced.', 'zappoppop'));
            }
            self::rrmdir($old);
        /* phpcs:ignore WordPress.WP.AlternativeFunctions -- local file or stream handle owned by this plugin; WP_Filesystem would need FTP credentials */ } elseif (!@rename($tmp, $dest)) {
            self::rrmdir($tmp);
            return self::err('write_failed', __('The module could not be moved into place.', 'zappoppop'));
        }
        ZapPopPop_Modules::flush();
        ZapPopPop_Modules::clear_orphan($slug);
        if ($old_core) {
            ZapPopPop_Modules::set_state($slug, $prev_state);
        } elseif (!$exists) {
            ZapPopPop_Modules::set_state($slug, 'available');
        }
        if (!empty($m['category'])) {
            ZapPopPop_Module_Categories::ensure($m['category']);
        }
        $exists = $exists || $old_core !== null;
        do_action('zappoppop_module_uploaded', $slug, $exists);
        $mod = ZapPopPop_Modules::get($slug);
        return [
            'module'   => $mod ? self::summary($mod) : null,
            'replaced' => $exists,
            'message'  => $old_core
                ? __('Module updated. The older version was removed from /modules.', 'zappoppop')
                : ($exists ? __('Module updated.', 'zappoppop') : __('Module uploaded. Click Install to activate it.', 'zappoppop')),
        ];
    }
}
