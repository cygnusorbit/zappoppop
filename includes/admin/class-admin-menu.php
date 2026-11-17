<?php
if (!defined('ABSPATH')) {
    exit;
}

class ZapPopPop_Admin_Menu {
    protected $pages;
    protected $dashboard_hook = '';
    protected static $original = [];
    protected static $hidden = [];

    public function __construct($pages = null) {
        if ($pages instanceof ZapPopPop_Admin_Pages) {
            $this->pages = $pages;
        } else {
            if (!class_exists('ZapPopPop_Admin_Pages') && file_exists(ZAPPOPPOP_PATH . 'includes/admin/class-admin-pages.php')) {
                require_once ZAPPOPPOP_PATH . 'includes/admin/class-admin-pages.php';
            }
            $this->pages = new ZapPopPop_Admin_Pages();
        }

        add_action('admin_menu', [$this, 'register_menus']);
        add_filter('set-screen-option', [$this, 'set_screen_option'], 10, 3);
        add_action('admin_menu', [$this, 'apply_menu_prefs'], 999);
        add_action('admin_menu', ['ZapPopPop_Promo', 'register_menu'], 1000);
        add_action('admin_enqueue_scripts', ['ZapPopPop_Promo', 'enqueue_menu']);
        ZapPopPop_Promo::init_plugin_link();
        add_action('admin_enqueue_scripts', [$this, 'print_hidden_css']);
        add_action('admin_enqueue_scripts', [$this, 'print_divider_css']);
        add_action('admin_enqueue_scripts', [$this, 'print_unread_pulse_css']);
        add_action('admin_enqueue_scripts', [$this, 'print_hidden_js']);
    }

    /* ZAPPOPPOP_MENU_ICON: SVG data URI (WordPress recolors it); falls back to a dashicon. */
    protected function menu_icon() {
        $file = ZAPPOPPOP_PATH . 'admin/img/zappoppop-menu-icon.svg';
        if (is_readable($file)) {
            /* phpcs:ignore WordPress.WP.AlternativeFunctions -- reads a local plugin or upload temp file, not a remote URL */ $svg = file_get_contents($file);
            if ($svg !== false && $svg !== '') {
                return 'data:image/svg+xml;base64,' . /* phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- inline SVG data URI, not obfuscation */ base64_encode($svg);
            }
        }
        return 'dashicons-external';
    }

    public function register_menus() {
        /* ZAPPOPPOP_MENU_V2: Dashboard is the parent page, so WordPress adds no duplicate first item. */
        $parent = 'zappoppop-dashboard';
        $overview_cb = method_exists($this->pages, 'render_overview') ? [$this->pages, 'render_overview'] : [$this->pages, 'render_dashboard'];

        add_menu_page(
            'ZapPopPop',
            'ZapPopPop',
            'manage_options',
            $parent,
            $overview_cb,
            $this->menu_icon(),
            30
        );

        add_submenu_page($parent, __('Dashboard', 'zappoppop'), __('Dashboard', 'zappoppop'), 'manage_options', $parent, $overview_cb);
        $this->dashboard_hook = add_submenu_page($parent, __('Popups', 'zappoppop'), __('Popups', 'zappoppop'), 'manage_options', 'zappoppop', [$this->pages, 'render_dashboard']);
        add_submenu_page($parent, __('Create Popup', 'zappoppop'), __('Create Popup', 'zappoppop'), 'manage_options', 'zappoppop-builder', [$this->pages, 'render_builder']);
        add_submenu_page($parent, __('Submissions', 'zappoppop'), __('Submissions', 'zappoppop'), 'manage_options', 'zappoppop-submissions', [$this->pages, 'render_submissions']);
        add_submenu_page($parent, __('Activity Log', 'zappoppop'), __('Activity Log', 'zappoppop'), 'manage_options', 'zappoppop-log', [$this->pages, 'render_log']);
        add_submenu_page($parent, __('Field Analytics', 'zappoppop'), __('Field Analytics', 'zappoppop'), 'manage_options', 'zappoppop-field-analytics', [$this->pages, 'render_field_analytics']);
        add_submenu_page($parent, __('Transactions', 'zappoppop'), __('Transactions', 'zappoppop'), 'manage_options', 'zappoppop-payments', [$this->pages, 'render_payments']);
        add_submenu_page($parent, __('Popups Library', 'zappoppop'), __('Popups Library', 'zappoppop'), 'manage_options', 'zappoppop-library', [$this->pages, 'render_library']);
        add_submenu_page($parent, __('Settings', 'zappoppop'), __('Settings', 'zappoppop'), 'manage_options', 'zappoppop-settings', [$this->pages, 'render_settings']);
        add_submenu_page($parent, __('Modules', 'zappoppop'), __('Modules', 'zappoppop'), 'manage_options', 'zappoppop-extend', [$this->pages, 'render_extend']);
        add_submenu_page($parent, __('Theme', 'zappoppop'), __('Theme', 'zappoppop'), 'manage_options', 'zappoppop-theme', [$this->pages, 'render_theme']);
        add_submenu_page($parent, __('Tools & Export', 'zappoppop'), __('Tools & Export', 'zappoppop'), 'manage_options', 'zappoppop-tools', [$this->pages, 'render_tools']);

        do_action('zappoppop_admin_menu', $parent);

        if (!empty($this->dashboard_hook)) {
            add_action("load-{$this->dashboard_hook}", [$this, 'load_dashboard_screen_options']);
        }
    }

    /** Pages that can never be hidden (prevents lock-out). */
    public static function pinned() {
        return ['zappoppop-dashboard', 'zappoppop-settings'];
    }

    /** slug => original title, captured live from the WP submenu (dynamic sync). */
    public static function get_original_pages() {
        return self::$original;
    }

    public static function get_prefs() {
        $s = get_option('zappoppop_settings', []);
        $prefs = (is_array($s) && isset($s['menu_pages']) && is_array($s['menu_pages'])) ? $s['menu_pages'] : [];
        if (!class_exists('ZapPopPop_Admin_Settings')) {
            require_once ZAPPOPPOP_PATH . 'includes/admin/class-admin-settings.php';
        }
        foreach (ZapPopPop_Admin_Settings::get_default_menu_pages() as $slug => $defaults) {
            $prefs[$slug] = (isset($prefs[$slug]) && is_array($prefs[$slug]))
                ? array_merge($defaults, $prefs[$slug])
                : $defaults;
        }
        return $prefs;
    }

    /**
     * ZAPPOPPOP_MENU_FIXED_ORDER: the one fixed menu sequence. Pro pages are matched by slug only.
     * Pages listed here always sort in this order; any other page follows, in registration order.
     * Filter: zappoppop_menu_order.
     */
    public static function menu_order() {
        $order = [
            'zappoppop-dashboard',
            'zappoppop',
            'zappoppop-builder',
            'zappoppop-submissions',
            'zappoppop-log',
            'zappoppop-field-analytics',
            'zappoppop-payments',
            'zappoppop-ab',
            'zappoppop-stats',
            'zappoppop-library',
            'zappoppop-theme',
            'zappoppop-extend',
            'zappoppop-tools',
            'zappoppop-settings',
        ];
        $filtered = apply_filters('zappoppop_menu_order', $order);
        return is_array($filtered) ? array_values($filtered) : $order;
    }

    /** Divider group of a page: 1 = dashboard..submissions, 2 = A/B + statistics, 3 = library and the rest. */
    public static function menu_group($slug) {
        if ($slug === 'zappoppop-ab' || $slug === 'zappoppop-stats') { return 2; }
        if (in_array($slug, ['zappoppop-dashboard', 'zappoppop', 'zappoppop-builder', 'zappoppop-submissions', 'zappoppop-log', 'zappoppop-field-analytics', 'zappoppop-payments'], true)) { return 1; }
        return 3;
    }

    /** Merge live pages with saved prefs; returns ordered rows. */
    public static function resolve_pages($orig, $prefs) {
        $rows = [];
        $i = 0;
        foreach ($orig as $slug => $title) {
            $p = (isset($prefs[$slug]) && is_array($prefs[$slug])) ? $prefs[$slug] : [];
            $rows[] = [
                'slug'    => $slug,
                'default' => $title,
                'label'   => (isset($p['label']) && $p['label'] !== '') ? $p['label'] : $title,
                'custom'  => (isset($p['label']) && $p['label'] !== '') ? 1 : 0,
                'hidden'  => (!empty($p['hidden']) && !in_array($slug, self::pinned(), true)) ? 1 : 0,
                'order'   => (isset($p['order']) && (int) $p['order'] > 0) ? (int) $p['order'] : null,
                'idx'     => $i++,
            ];
        }
        /* ZAPPOPPOP_MENU_FIXED_ORDER: the fixed sequence wins over any saved order; unknown pages follow in registration order */
        $fixed = array_flip(self::menu_order());
        usort($rows, function ($a, $b) use ($fixed) {
            $ra = isset($fixed[$a['slug']]) ? $fixed[$a['slug']] : PHP_INT_MAX;
            $rb = isset($fixed[$b['slug']]) ? $fixed[$b['slug']] : PHP_INT_MAX;
            return ($ra === $rb) ? ($a['idx'] - $b['idx']) : ($ra < $rb ? -1 : 1);
        });
        return $rows;
    }

    public function apply_menu_prefs() {
        global $submenu, $menu;
        $parent = 'zappoppop-dashboard';

        /* ZAPPOPPOP_MENU_UNREAD_PULSE: pulsing notice icon on the top-level menu while unread submissions exist */
        if (is_array($menu) && self::has_unread_submissions()) {
            foreach ($menu as $k => $m) {
                if (isset($m[2]) && $m[2] === $parent) {
                    $menu[$k][0] = $m[0] . '<span class="zappoppop-menu-pulse" title="' . esc_attr__('Unread submissions', 'zappoppop') . '" aria-hidden="true">!</span><span class="screen-reader-text">' . esc_html__('Unread submissions', 'zappoppop') . '</span>';
                    break;
                }
            }
        }
        if (empty($submenu[$parent]) || !is_array($submenu[$parent])) {
            return;
        }

        $items = [];
        self::$original = [];
        foreach ($submenu[$parent] as $it) {
            if (!isset($it[2])) { continue; }
            $items[$it[2]] = $it;
            self::$original[$it[2]] = trim(wp_strip_all_tags($it[0]));
        }

        $rows = self::resolve_pages(self::$original, self::get_prefs());
        $out = [];
        self::$hidden = [];
        $prev_group = null;
        foreach ($rows as $r) {
            $it = $items[$r['slug']];
            if (empty($r['hidden'])) {
                /* ZAPPOPPOP_MENU_DIVIDER: thin line before the first visible page of the next group */
                $g = self::menu_group($r['slug']);
                if ($prev_group !== null && $g !== $prev_group) {
                    $it[4] = trim((isset($it[4]) ? (string) $it[4] : '') . ' zpp-sep-before');
                }
                $prev_group = $g;
            }
            if (!empty($r['custom'])) {
                $it[0] = esc_html($r['label']);
                if (isset($it[3])) { $it[3] = $r['label']; }
            }
            if (!empty($r['hidden'])) {
                self::$hidden[] = $r['slug'];
            }
            $out[] = $it;
        }
        $submenu[$parent] = $out;
    }

    /** True when at least one submission is still unread (cheap existence check, no full count). */
    public static function has_unread_submissions() {
        global $wpdb;
        if (!current_user_can('manage_options')) { return false; }
        $table = $wpdb->prefix . 'zappoppop_submissions';
        $prev = $wpdb->suppress_errors(true);
        /* phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- table name is built from $wpdb->prefix and the query takes no user input */ $found = $wpdb->get_var("SELECT 1 FROM {$table} WHERE is_read = 0 LIMIT 1");
        $wpdb->suppress_errors($prev);
        return !empty($found);
    }

    /** Pulse icon styling in the plugin primary color (Theme page), printed on every admin screen. */
    public function print_unread_pulse_css() {
        if (!current_user_can('manage_options') || !self::has_unread_submissions()) { return; }
        $color = '#c1272d';
        if (class_exists('ZapPopPop_Admin_Theme')) {
            $v = ZapPopPop_Admin_Theme::get_values();
            $c = isset($v['c_primary']) ? sanitize_hex_color($v['c_primary']) : '';
            if ($c) { $color = $c; }
        }
        $hex = ltrim($color, '#');
        if (strlen($hex) === 3) { $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2]; }
        $rgb = hexdec(substr($hex, 0, 2)) . ',' . hexdec(substr($hex, 2, 2)) . ',' . hexdec(substr($hex, 4, 2));
        $css = '#adminmenu .zappoppop-menu-pulse{display:inline-block;box-sizing:border-box;width:16px;height:16px;margin:0 0 0 7px;border-radius:50%;background:{{COLOR}};color:#fff;font:700 11px/16px Arial,Helvetica,sans-serif;text-align:center;vertical-align:text-top;-webkit-animation:zappoppop-menu-pulse 1.6s ease-out infinite;animation:zappoppop-menu-pulse 1.6s ease-out infinite}'
            . '@-webkit-keyframes zappoppop-menu-pulse{0%{box-shadow:0 0 0 0 rgba({{RGB}},.65)}70%{box-shadow:0 0 0 8px rgba({{RGB}},0)}100%{box-shadow:0 0 0 0 rgba({{RGB}},0)}}'
            . '@keyframes zappoppop-menu-pulse{0%{box-shadow:0 0 0 0 rgba({{RGB}},.65)}70%{box-shadow:0 0 0 8px rgba({{RGB}},0)}100%{box-shadow:0 0 0 0 rgba({{RGB}},0)}}'
            . '@media (prefers-reduced-motion:reduce){#adminmenu .zappoppop-menu-pulse{-webkit-animation:none;animation:none}}';
        $css = str_replace(array('{{COLOR}}', '{{RGB}}'), array($color, $rgb), $css);
        wp_register_style('zappoppop-unread-pulse', false, array(), ZAPPOPPOP_VERSION);
        wp_enqueue_style('zappoppop-unread-pulse');
        wp_add_inline_style('zappoppop-unread-pulse', $css);
    }

    /** Thin divider line above the first page of each menu group (works on every WP admin colour scheme). */
    public function print_divider_css() {
        if (!current_user_can('manage_options')) { return; }
        $css = '#adminmenu .wp-submenu a.zpp-sep-before{border-top:1px solid rgba(127,127,127,.4);margin-top:4px;padding-top:9px}';
        wp_register_style('zappoppop-menu-divider', false, array(), ZAPPOPPOP_VERSION);
        wp_enqueue_style('zappoppop-menu-divider');
        wp_add_inline_style('zappoppop-menu-divider', $css);
    }

    /* Hidden pages stay registered (direct URLs keep working); only the menu entry is hidden. */
    public function print_hidden_css() {
        if (empty(self::$hidden)) { return; }
        $css = '';
        foreach (self::$hidden as $slug) {
            $css .= '#adminmenu .wp-submenu a[href$="page=' . esc_attr($slug) . '"]{display:none !important;}';
        }
        wp_register_style('zappoppop-hidden-pages', false, array(), ZAPPOPPOP_VERSION);
        wp_enqueue_style('zappoppop-hidden-pages');
        wp_add_inline_style('zappoppop-hidden-pages', $css);
    }

    public function print_hidden_js() {
        if (empty(self::$hidden)) { return; }
        $js = "(function () {\n"
            . "    var hidden = " . wp_json_encode(array_values(self::$hidden)) . ";\n"
            . "    var links = document.querySelectorAll('#adminmenu .wp-submenu a');\n"
            . "    for (var i = 0; i < links.length; i++) {\n"
            . "        var m = /[?&]page=([^&#]+)/.exec(links[i].getAttribute('href') || '');\n"
            . "        if (m && hidden.indexOf(decodeURIComponent(m[1])) > -1 && links[i].parentNode && links[i].parentNode.style) {\n"
            . "            links[i].parentNode.style.display = 'none';\n"
            . "        }\n"
            . "    }\n"
            . "})();";
        wp_register_script('zappoppop-hidden-pages', false, array(), ZAPPOPPOP_VERSION, true);
        wp_enqueue_script('zappoppop-hidden-pages');
        wp_add_inline_script('zappoppop-hidden-pages', $js);
    }

    public function load_dashboard_screen_options() {
        // Screen Options intentionally hidden on the Popups page.
        add_filter('screen_options_show_screen', '__return_false');
    }

    public function set_screen_option($status, $option, $value) {
        if ('zappoppop_campaigns_per_page' === $option) {
            return (int) $value;
        }
        return $status;
    }
}
