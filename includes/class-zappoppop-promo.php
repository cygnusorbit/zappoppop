<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Promotional placements driven by defaults/promo.json.
 * Core only: every slot stays visible until the companion plugin reports a valid licence (ZapPopPop_Pro_API::is_licensed()).
 * Pro installed but not yet licensed still shows them; they all disappear once the licence key is activated.
 * Filters: zappoppop_promo_file (path), zappoppop_promo_config (array).
 */
class ZapPopPop_Promo {

    const MAX_SLIDES = 8;
    const MAX_LOCKED = 24; /* ZAPPOPPOP_PRO_LOCK */
    const PARENT     = 'zappoppop-dashboard';

    private static $config = null;

    public static function file() {
        $file = ZAPPOPPOP_PATH . 'defaults/promo.json';
        if (!is_readable($file) && is_readable(ZAPPOPPOP_PATH . 'promo/promo.json')) {
            $file = ZAPPOPPOP_PATH . 'promo/promo.json'; // older location
        }
        return (string) apply_filters('zappoppop_promo_file', $file);
    }

    public static function config() {
        if (self::$config !== null) {
            return self::$config;
        }
        $cfg  = array('enabled' => false, 'slots' => array());
        $file = self::file();
        if ($file !== '' && is_readable($file)) {
            $raw  = file_get_contents($file); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local plugin file
            $json = is_string($raw) ? json_decode($raw, true) : null;
            if (is_array($json)) {
                $cfg = array(
                    'enabled' => !empty($json['enabled']),
                    'remember_dismiss' => !array_key_exists('remember_dismiss', $json) || !empty($json['remember_dismiss']),
                    'show_close' => !array_key_exists('show_close', $json) || !empty($json['show_close']),
                    'slots'   => (isset($json['slots']) && is_array($json['slots'])) ? $json['slots'] : array(),
                );
            }
        }
        $cfg = apply_filters('zappoppop_promo_config', $cfg);
        self::$config = is_array($cfg) ? $cfg : array('enabled' => false, 'slots' => array());
        return self::$config;
    }

    /** False once Pro is licensed, or when the master switch is off. Pro active without a licence still shows the promotions. */
    public static function visible() {
        if (class_exists('ZapPopPop_Pro_API') && ZapPopPop_Pro_API::is_licensed()) {
            return false;
        }
        $cfg = self::config();
        return !empty($cfg['enabled']);
    }

    private static function ver() {
        $v = defined('ZAPPOPPOP_VERSION') ? ZAPPOPPOP_VERSION : '1.0';
        $t = 0;
        foreach (array('admin/css/promo.css', 'admin/css/pro-preview.css', 'admin/js/promo.js', 'admin/js/promo-lock.js') as $f) {
            $m = is_readable(ZAPPOPPOP_PATH . $f) ? (int) filemtime(ZAPPOPPOP_PATH . $f) : 0;
            $t = max($t, $m);
        }
        return $t ? $v . '.' . $t : $v;
    }

    /** Plugin pages only. The script loads in <head> so closed items are hidden before first paint (no flash). */
    public static function enqueue() {
        if (!self::visible()) {
            return;
        }
        wp_enqueue_style('dashicons');
        wp_enqueue_style('zappoppop-promo-css', ZAPPOPPOP_URL . 'admin/css/promo.css', array('zappoppop-theme-global-css'), self::ver());
        wp_enqueue_script('zappoppop-promo-js', ZAPPOPPOP_URL . 'admin/js/promo.js', array(), self::ver(), false);
        wp_enqueue_script('zappoppop-promo-lock-js', ZAPPOPPOP_URL . 'admin/js/promo-lock.js', array(), self::ver(), true); /* ZAPPOPPOP_PRO_LOCK */
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- page slug only, nothing is saved
        $zpp_page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
        if ($zpp_page === 'zappoppop-builder' && self::item('builder_pro_settings')) { /* ZAPPOPPOP_PRO_PREVIEW */
            wp_enqueue_style('zappoppop-pro-preview-css', ZAPPOPPOP_URL . 'admin/css/pro-preview.css', array('zappoppop-promo-css'), self::ver());
        }

        // Items that must come back after every refresh: any stored "closed" record for them is ignored and purged.
        $volatile = array();
        foreach (array('header_text', 'builder_box', 'dashboard_slider', 'pro_extensions', 'builder_pro_settings', 'upgrade_popup') as $slot) {
            $it = self::item($slot);
            if ($it && (empty($it['remember']) || empty($it['dismissible']))) { // no close button: ignore any old "closed" record
                $volatile[] = $it['id'];
            }
        }
        if ($volatile) {
            wp_add_inline_script('zappoppop-promo-js', 'window.zappoppopPromo=' . wp_json_encode(array('volatile' => $volatile)) . ';', 'before');
        }
    }

    /* ------------------------------------------------------------ admin menu link */

    /** Appends the link after the menu preferences are applied, so it never shows up in Settings > Pages. */
    public static function register_menu() {
        global $submenu;
        if (empty($submenu[self::PARENT]) || !is_array($submenu[self::PARENT])) {
            return;
        }
        $item = self::item('menu_item');
        if (!$item) {
            return;
        }
        $label = wp_strip_all_tags($item['text']);
        // WordPress prints a URL used as the menu slug as-is, so it is escaped here.
        $submenu[self::PARENT][] = array(esc_html($label), 'manage_options', esc_url($item['url']), $label, 'zpp-go-pro');
    }

    private static function menu_primary() {
        $color = '#c1272d';
        if (class_exists('ZapPopPop_Admin_Theme')) {
            $v = ZapPopPop_Admin_Theme::get_values();
            $c = isset($v['c_primary']) ? sanitize_hex_color($v['c_primary']) : '';
            if ($c) {
                $color = $c;
            }
        }
        return $color;
    }

    /** Colours and optional new-tab behaviour for the menu link (every admin screen shows the menu). */
    public static function enqueue_menu() {
        if (!current_user_can('manage_options')) {
            return;
        }
        $item = self::item('menu_item');
        if (!$item) {
            return;
        }
        $bg = $item['bg_color'] !== '' ? $item['bg_color'] : self::menu_primary();
        $fg = $item['text_color'] !== '' ? $item['text_color'] : '#ffffff';
        $css = '#adminmenu .wp-submenu a.zpp-go-pro{display:block;background:' . $bg . ';color:' . $fg . ';font-weight:600;border-radius:3px}'
            . '#adminmenu .wp-submenu a.zpp-go-pro:hover,#adminmenu .wp-submenu a.zpp-go-pro:focus{background:' . $bg . ';color:' . $fg . ';-webkit-filter:brightness(1.12);filter:brightness(1.12)}';
        wp_register_style('zappoppop-promo-menu', false, array(), self::ver());
        wp_enqueue_style('zappoppop-promo-menu');
        wp_add_inline_style('zappoppop-promo-menu', $css);

        if (!empty($item['new_tab'])) {
            $js = "(function(){var l=document.querySelectorAll('#adminmenu a.zpp-go-pro');for(var i=0;i<l.length;i++){l[i].setAttribute('target','_blank');l[i].setAttribute('rel','noopener noreferrer');}})();";
            wp_register_script('zappoppop-promo-menu', false, array(), self::ver(), true);
            wp_enqueue_script('zappoppop-promo-menu');
            wp_add_inline_script('zappoppop-promo-menu', $js);
        }
    }

    /* ------------------------------------------------------------ plugins screen link */

    public static function init_plugin_link() {
        if (!defined('ZAPPOPPOP_BASENAME')) {
            return;
        }
        add_filter('plugin_action_links_' . ZAPPOPPOP_BASENAME, array(__CLASS__, 'plugin_action_links'));
        add_filter('network_admin_plugin_action_links_' . ZAPPOPPOP_BASENAME, array(__CLASS__, 'plugin_action_links'));
    }

    /** Appends the link after "Deactivate". Hidden once Pro is licensed or when the slot is off. */
    public static function plugin_action_links($links) {
        $item = self::item('plugin_link');
        if (!$item) {
            return $links;
        }
        $color = $item['text_color'] !== '' ? $item['text_color'] : self::menu_primary();
        $attr  = !empty($item['new_tab']) ? ' target="_blank" rel="noopener noreferrer"' : '';
        $links = is_array($links) ? $links : array();
        $links['zappoppop_go_pro'] = '<a href="' . esc_url($item['url']) . '" class="zpp-go-pro-link" style="color:' . esc_attr($color) . ';font-weight:600"' . $attr . '>' . esc_html(wp_strip_all_tags($item['text'])) . '</a>';
        return $links;
    }

    /* ------------------------------------------------------------ sanitising */

    private static function allowed_html() {
        return array(
            'a'      => array('href' => true, 'target' => true, 'rel' => true, 'class' => true),
            'strong' => array(),
            'b'      => array(),
            'em'     => array(),
            'i'      => array(),
            'br'     => array(),
        );
    }

    private static function color_keys() {
        return array('text_color', 'bg_color', 'cta_text_color', 'cta_bg_color');
    }

    private static function str($arr, $key) {
        return (isset($arr[$key]) && is_scalar($arr[$key])) ? trim((string) $arr[$key]) : '';
    }

    private static function flag($arr, $key, $default) {
        return array_key_exists($key, $arr) ? !empty($arr[$key]) : $default;
    }

    private static function color($arr, $key) {
        $c = sanitize_hex_color(self::str($arr, $key));
        return $c ? $c : '';
    }

    private static function image($v) {
        if ($v === '' || strpos($v, '..') !== false) {
            return '';
        }
        if (preg_match('#^(https?:)?//#i', $v)) {
            return esc_url_raw($v, array('http', 'https'));
        }
        $v = ltrim(str_replace('\\', '/', $v), '/');
        $v = preg_replace('#^(defaults|promo)/#i', '', $v); // old values that include the folder name
        if ($v === '' || !preg_match('/\.(png|jpe?g|gif|webp|svg|avif)$/i', $v)) {
            return '';
        }
        $dir = (!is_readable(ZAPPOPPOP_PATH . 'defaults/' . $v) && is_readable(ZAPPOPPOP_PATH . 'promo/' . $v)) ? 'promo/' : 'defaults/'; // promo/ = older location
        return ZAPPOPPOP_URL . $dir . implode('/', array_map('rawurlencode', explode('/', $v)));
    }

    private static function in_window($raw) {
        $today = wp_date('Y-m-d');
        $start = self::str($raw, 'start');
        $end   = self::str($raw, 'end');
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $start) && $today < $start) {
            return false;
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $end) && $today > $end) {
            return false;
        }
        return true;
    }

    private static function entry($raw) {
        $e = array(
            'title'   => wp_strip_all_tags(self::str($raw, 'title')),
            'text'    => wp_kses(self::str($raw, 'text'), self::allowed_html()),
            'cta'     => wp_strip_all_tags(self::str($raw, 'cta')),
            'url'     => esc_url_raw(self::str($raw, 'url'), array('http', 'https')),
            'new_tab' => self::flag($raw, 'new_tab', true),
            'image'   => self::image(self::str($raw, 'image')),
            'image_url' => esc_url_raw(self::str($raw, 'image_url'), array('http', 'https')),
        );
        $e['image_new_tab'] = self::flag($raw, 'image_new_tab', $e['new_tab']);
        $fit = strtolower(self::str($raw, 'image_fit'));
        $e['image_fit'] = in_array($fit, array('auto', 'cover'), true) ? $fit : ''; // '' = inherit from the slot, then "auto"
        foreach (self::color_keys() as $k) {
            $e[$k] = self::color($raw, $k);
        }
        return $e;
    }

    /** Normalised slot data, or null when the slot must not be shown. */
    public static function item($slot) {
        if (!self::visible()) {
            return null;
        }
        $cfg = self::config();
        if (empty($cfg['slots'][$slot]) || !is_array($cfg['slots'][$slot])) {
            return null;
        }
        $raw = $cfg['slots'][$slot];
        if (!self::flag($raw, 'enabled', true) || !self::in_window($raw)) {
            return null;
        }

        $item = self::entry($raw);
        if ($slot === 'menu_item' || $slot === 'plugin_link') {
            $item['dismissible'] = false;
        } else {
            // show_close: slot value, else the older "dismissible" key, else the top-level value
            $show = !isset($cfg['show_close']) || !empty($cfg['show_close']);
            if (!array_key_exists('show_close', $raw) && array_key_exists('dismissible', $raw)) {
                $show = !empty($raw['dismissible']);
            }
            $item['dismissible'] = self::flag($raw, 'show_close', $show);
        }
        $item['reshow_days'] = max(0, min(365, (int) (isset($raw['reshow_days']) && is_scalar($raw['reshow_days']) ? $raw['reshow_days'] : 0)));
        // false = closing only hides the item until the next refresh
        $item['remember']    = ($slot === 'menu_item' || $slot === 'plugin_link') ? true : self::flag($raw, 'remember_dismiss', !isset($cfg['remember_dismiss']) || !empty($cfg['remember_dismiss']));

        if ($slot === 'dashboard_slider') {
            $slides = array();
            if (isset($raw['slides']) && is_array($raw['slides'])) {
                foreach ($raw['slides'] as $s) {
                    if (!is_array($s) || count($slides) >= self::MAX_SLIDES) {
                        continue;
                    }
                    $e = self::entry($s);
                    if ($e['title'] === '' && $e['text'] === '' && $e['image'] === '') {
                        continue;
                    }
                    foreach (self::color_keys() as $k) {
                        if ($e[$k] === '') {
                            $e[$k] = $item[$k];
                        }
                    }
                    if ($e['image_fit'] === '') {
                        $e['image_fit'] = $item['image_fit'];
                    }
                    $slides[] = $e;
                }
            }
            if (!$slides) {
                return null;
            }
            $item['slides']   = $slides;
            $item['interval'] = max(2000, min(30000, (int) (isset($raw['interval']) ? $raw['interval'] : 6000)));
        } elseif ($slot === 'menu_item' || $slot === 'plugin_link') {
            if ($item['text'] === '' || $item['url'] === '') {
                return null;
            }
        } elseif ($slot === 'header_text') {
            if ($item['text'] === '') {
                return null;
            }
        } elseif ($slot === 'pro_extensions' || $slot === 'builder_pro_settings') {
            $item['entries'] = self::lock_entries($raw); /* ZAPPOPPOP_PRO_LOCK */
            if ($slot === 'builder_pro_settings') { /* ZAPPOPPOP_PRO_OVERLAY */
                $oa = strtolower(self::str($raw, 'overlay_action'));
                $item['overlay_text']    = mb_substr(wp_strip_all_tags(self::str($raw, 'overlay_text')), 0, 40);
                $item['overlay_url']     = esc_url_raw(self::str($raw, 'overlay_url'), array('http', 'https'));
                $item['overlay_new_tab'] = self::flag($raw, 'overlay_new_tab', $item['new_tab']);
                $item['overlay_action']  = ($oa === 'popup') ? 'popup' : 'link';
            }
            if (!$item['entries']) {
                return null;
            }
        } elseif ($slot === 'upgrade_popup') {
            $item['features']    = self::features($raw);
            $item['close_text']  = mb_substr(wp_strip_all_tags(self::str($raw, 'close_text')), 0, 40);
            $ac = strtolower(self::str($raw, 'after_close')); /* ZAPPOPPOP_PRO_DISMISS */
            $item['after_close'] = ($ac === 'link') ? 'link' : 'none';
            if ($item['title'] === '' && $item['text'] === '') {
                return null;
            }
        } elseif ($item['title'] === '' && $item['text'] === '') {
            return null;
        }

        $id = sanitize_key(self::str($raw, 'id'));
        if ($id === '') {
            $id = substr(md5((string) wp_json_encode($item)), 0, 12);
        }
        $item['id'] = $slot . '-' . $id;
        return $item;
    }

    /* ------------------------------------------------------------ ZAPPOPPOP_PRO_LOCK: static Pro teasers (no stored data) */

    private static $modal_done = false;
    private static $installed  = null;

    /** Slugs of modules this site already has, so a teaser never sits next to the real module. */
    private static function installed_slugs() {
        if (self::$installed !== null) {
            return self::$installed;
        }
        self::$installed = array();
        if (class_exists('ZapPopPop_Modules_Installer')) {
            foreach ((array) ZapPopPop_Modules_Installer::extend_items() as $m) {
                if (is_array($m) && !empty($m['slug']) && is_string($m['slug'])) {
                    self::$installed[sanitize_key($m['slug'])] = true;
                }
            }
        }
        return self::$installed;
    }

    /** Entries of a list slot: name, description, icon (dashicons), badge, slug (optional). */
    private static function lock_entries($raw) {
        $out = array();
        if (!isset($raw['items']) || !is_array($raw['items'])) {
            return $out;
        }
        $have = self::installed_slugs();
        foreach ($raw['items'] as $r) {
            if (!is_array($r) || count($out) >= self::MAX_LOCKED) {
                continue;
            }
            $name = mb_substr(wp_strip_all_tags(self::str($r, 'name')), 0, 60);
            if ($name === '') {
                continue;
            }
            $slug = sanitize_key(self::str($r, 'slug'));
            if ($slug !== '' && isset($have[$slug])) {
                continue;
            }
            $icon = self::str($r, 'icon');
            if (!preg_match('/^dashicons-[a-z0-9-]+$/', $icon)) {
                $icon = 'dashicons-lock';
            }
            $out[] = array(
                'slug'        => $slug,
                'name'        => $name,
                'description' => mb_substr(wp_strip_all_tags(self::str($r, 'description')), 0, 200),
                'icon'        => $icon,
                'badge'       => mb_substr(wp_strip_all_tags(self::str($r, 'badge')), 0, 12),
                'controls'    => self::lock_controls($r),
                'preview'     => sanitize_key(self::str($r, 'preview')),
            );
        }
        return $out;
    }

    private static function features($raw) {
        $out = array();
        if (isset($raw['features']) && is_array($raw['features'])) {
            foreach ($raw['features'] as $f) {
                if (is_scalar($f) && count($out) < 8) {
                    $t = mb_substr(trim(wp_strip_all_tags((string) $f)), 0, 120);
                    if ($t !== '') {
                        $out[] = $t;
                    }
                }
            }
        }
        return $out;
    }

    /** The upgrade dialog, printed once per page. JS moves it to <body> when it opens (the builder drawer is transformed). */
    private static function render_modal() {
        if (self::$modal_done) {
            return;
        }
        $p = self::item('upgrade_popup');
        if (!$p) {
            return;
        }
        self::$modal_done = true;
        $allowed = self::allowed_html();
        echo '<div class="zpp-pro-modal" id="zpp-pro-modal" hidden data-zpp-pro-id="' . esc_attr($p['id']) . '" data-zpp-pro-days="' . (int) $p['reshow_days'] . '" data-zpp-pro-after="' . esc_attr($p['after_close']) . '"'
            . ((!empty($p['dismissible']) && !empty($p['remember'])) ? '' : ' data-zpp-pro-remember="0"')
            . ($p['url'] !== '' ? ' data-zpp-pro-url="' . esc_url($p['url']) . '"' : '')
            . ($p['new_tab'] ? ' data-zpp-pro-newtab="1"' : '') . '>'; /* ZAPPOPPOP_PRO_DISMISS */
        echo '<div class="zpp-pro-modal-backdrop" data-zpp-pro-close="1"></div>';
        echo '<div class="zpp-pro-modal-box" role="dialog" aria-modal="true" aria-labelledby="zpp-pro-modal-title" tabindex="-1"';
        self::print_vars($p);
        echo '>';
        if (!empty($p['dismissible'])) {
            echo '<button type="button" class="zpp-promo-x zpp-pro-modal-x" data-zpp-pro-close="1" data-zpp-pro-dismiss="1" aria-label="' . esc_attr__('Close', 'zappoppop') . '"><span class="dashicons dashicons-no-alt" aria-hidden="true"></span></button>';
        }
        if ($p['image'] !== '') {
            echo '<img class="zpp-pro-modal-img" src="' . esc_url($p['image']) . '" alt="" loading="lazy">';
        }
        if ($p['title'] !== '') {
            echo '<h2 class="zpp-pro-modal-title" id="zpp-pro-modal-title" data-zpp-pro-title="' . esc_attr($p['title']) . '">' . esc_html(str_replace('{name}', '', $p['title'])) . '</h2>';
        }
        if ($p['text'] !== '') {
            echo '<p class="zpp-pro-modal-text">' . wp_kses($p['text'], $allowed) . '</p>';
        }
        if (!empty($p['features'])) {
            echo '<ul class="zpp-pro-modal-list">';
            foreach ($p['features'] as $f) {
                echo '<li><span class="dashicons dashicons-yes" aria-hidden="true"></span>' . esc_html($f) . '</li>';
            }
            echo '</ul>';
        }
        echo '<div class="zpp-pro-modal-actions">';
        echo wp_kses(self::link($p, 'button button-primary zpp-promo-btn'), $allowed);
        echo '<button type="button" class="button zpp-pro-modal-close" data-zpp-pro-close="1" data-zpp-pro-dismiss="1">' . esc_html($p['close_text'] !== '' ? $p['close_text'] : __('Not now', 'zappoppop')) . '</button>';
        echo '</div></div></div>';
    }

    /** Opening attributes for a locked card or row: opens the dialog when it exists. */
    private static function lock_attrs($entry, $popup_ok) {
        $a = ' data-zpp-pro-name="' . esc_attr($entry['name']) . '"';
        if ($popup_ok) {
            $a .= ' data-zpp-pro-lock="1" role="button" tabindex="0" aria-haspopup="dialog"';
        }
        return $a;
    }

    private static function render_extensions($item) {
        $popup_ok = (bool) self::item('upgrade_popup');
        $badge    = __('Pro', 'zappoppop');
        $title    = $item['title'] !== '' ? $item['title'] : __('Pro extensions', 'zappoppop');
        echo '<section class="zpp-ext-card zpp-pro-lock-section" aria-labelledby="zpp-pro-lock-title"';
        self::print_root_attrs($item);
        echo '>';
        self::dismiss_button($item);
        echo '<div class="zpp-ext-card-head"><span class="dashicons dashicons-lock" aria-hidden="true"></span><h3 id="zpp-pro-lock-title">' . esc_html($title) . '</h3></div>';
        if ($item['text'] !== '') {
            echo '<p class="zpp-ext-help">' . wp_kses($item['text'], self::allowed_html()) . '</p>';
        }
        echo '<div class="zpp-ext-grid zpp-pro-lock-grid">';
        foreach ($item['entries'] as $e) {
            echo '<div class="zpp-ext-item zpp-pro-lock-item"' . self::lock_attrs($e, $popup_ok) . '>';
            echo '<div class="zpp-ext-item-top"><span class="zpp-ext-item-img is-empty"><span class="dashicons ' . esc_attr($e['icon']) . '" aria-hidden="true"></span></span>';
            echo '<div class="zpp-ext-item-head"><h3 class="zpp-ext-item-name">' . esc_html($e['name']) . '</h3>';
            echo '<span class="zpp-mod-pill is-lock">' . esc_html($e['badge'] !== '' ? $e['badge'] : $badge) . '</span></div></div>';
            if ($e['description'] !== '') {
                echo '<p class="zpp-ext-item-desc">' . esc_html($e['description']) . '</p>';
            }
            echo '<div class="zpp-pro-lock-cta">';
            $zpp_cta_label = $item['cta'] !== '' ? $item['cta'] : __('Available in Pro', 'zappoppop');
            if ($item['url'] !== '') {
                $zpp_cta_attr = !empty($item['new_tab']) ? ' target="_blank" rel="noopener noreferrer"' : '';
                echo '<a class="zpp-pro-lock-btn" href="' . esc_url($item['url']) . '"' . $zpp_cta_attr . '><span class="dashicons dashicons-lock" aria-hidden="true"></span>' . esc_html($zpp_cta_label) . '</a>';
            } else {
                echo '<span class="zpp-pro-lock-btn"><span class="dashicons dashicons-lock" aria-hidden="true"></span>' . esc_html($zpp_cta_label) . '</span>';
            }
            echo '</div></div>';
        }
        echo '</div>';
        echo '</section>';
        self::render_modal();
    }

    /* ZAPPOPPOP_PRO_ACC: sample controls of a Pro feature (display only, nothing is saved) */
    private static function lock_controls($r) {
        $out = array();
        if (!isset($r['controls']) || !is_array($r['controls'])) {
            return $out;
        }
        foreach ($r['controls'] as $c) {
            if (!is_array($c) || count($out) >= 8) {
                continue;
            }
            $type = strtolower(self::str($c, 'type'));
            if (!in_array($type, array('toggle', 'select', 'number', 'text', 'textarea', 'color', 'range'), true)) {
                continue;
            }
            $label = mb_substr(wp_strip_all_tags(self::str($c, 'label')), 0, 60);
            if ($label === '') {
                continue;
            }
            $opts = array();
            if (isset($c['options']) && is_array($c['options'])) {
                foreach ($c['options'] as $o) {
                    if (is_scalar($o) && count($opts) < 8) {
                        $t = mb_substr(wp_strip_all_tags((string) $o), 0, 40);
                        if ($t !== '') {
                            $opts[] = $t;
                        }
                    }
                }
            }
            $val = mb_substr(wp_strip_all_tags(self::str($c, 'value')), 0, 80);
            if ($type === 'color') {
                $hex = sanitize_hex_color($val);
                $val = ($hex && strlen($hex) === 7) ? $hex : '#000000';
            }
            $out[] = array('type' => $type, 'label' => $label, 'value' => $val, 'options' => $opts, 'on' => self::flag($c, 'on', false));
        }
        return $out;
    }

    private static function render_lock_control($c) {
        $lab = esc_attr($c['label']);
        echo '<div class="zpp-pro-ctl zpp-pro-ctl--' . esc_attr($c['type']) . '">';
        echo '<span class="zpp-pro-ctl-label">' . esc_html($c['label']) . '</span>';
        $common = ' disabled tabindex="-1" aria-disabled="true" data-zpp-static="1" aria-label="' . $lab . '"';
        switch ($c['type']) {
            case 'toggle':
                echo '<span class="zappoppop-ovf-switch"><input type="checkbox" role="switch"' . $common . ($c['on'] ? ' checked' : '') . '><span class="zappoppop-ovf-track"></span></span>';
                break;
            case 'select':
                echo '<select class="widefat"' . $common . '>';
                foreach ($c['options'] as $o) {
                    echo '<option' . ($o === $c['value'] ? ' selected' : '') . '>' . esc_html($o) . '</option>';
                }
                echo '</select>';
                break;
            case 'textarea':
                echo '<textarea class="widefat" rows="3"' . $common . '>' . esc_textarea($c['value']) . '</textarea>';
                break;
            case 'color':
                echo '<input type="color" value="' . esc_attr($c['value']) . '"' . $common . '>';
                break;
            case 'range':
                echo '<input type="range" min="0" max="100" value="' . (int) $c['value'] . '"' . $common . '>';
                break;
            case 'number':
                echo '<input type="number" class="widefat" value="' . esc_attr($c['value']) . '"' . $common . '>';
                break;
            default:
                echo '<input type="text" class="widefat" value="' . esc_attr($c['value']) . '"' . $common . '>';
        }
        echo '</div>';
    }

    /* ZAPPOPPOP_PRO_PREVIEW: static copy of a Pro accordion (defaults/pro-preview/<slug>.html), shown greyed out */
    private static function preview_html($e) {
        $key = sanitize_key($e['preview'] !== '' ? $e['preview'] : $e['slug']);
        if ($key === '') {
            return '';
        }
        $file = ZAPPOPPOP_PATH . 'defaults/pro-preview/' . $key . '.html';
        if (!is_readable($file)) {
            return '';
        }
        $html = file_get_contents($file); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local plugin file
        return is_string($html) ? $html : '';
    }

    private static function preview_allowed_html() {
        $common = array('class' => true, 'id' => true, 'title' => true, 'role' => true, 'tabindex' => true, 'aria-hidden' => true);
        $form   = array('type' => true, 'value' => true, 'placeholder' => true, 'rows' => true, 'min' => true, 'max' => true, 'step' => true,
                        'maxlength' => true, 'disabled' => true, 'checked' => true, 'selected' => true);
        $out = array();
        foreach (array('div', 'span', 'p', 'label', 'strong', 'em', 'code', 'ul', 'li', 'i', 'small', 'fieldset', 'h4') as $t) {
            $out[$t] = $common;
        }
        foreach (array('input', 'select', 'option', 'optgroup', 'textarea', 'button') as $t) {
            $out[$t] = array_merge($common, $form);
        }
        $out['svg']  = array('class' => true, 'viewbox' => true, 'aria-hidden' => true, 'focusable' => true);
        $out['rect'] = array('class' => true, 'x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true, 'ry' => true);
        return $out;
    }

    private static function render_builder_rows($item) {
        $popup_ok = (bool) self::item('upgrade_popup');
        $badge    = __('Pro', 'zappoppop');
        $title    = $item['title'] !== '' ? $item['title'] : __('Available in ZapPopPop Pro', 'zappoppop');
        $allowed  = self::allowed_html();
        /* ZAPPOPPOP_PRO_OVERLAY: one button over the greyed controls; text and link come from promo.json */
        $o_text   = $item['overlay_text'] !== '' ? $item['overlay_text'] : __('Upgrade to PRO', 'zappoppop');
        $o_url    = $item['overlay_url'] !== '' ? $item['overlay_url'] : $item['url'];
        $as_popup = $popup_ok && $item['overlay_action'] !== 'link';
        $as_link  = !$as_popup && $o_url !== '';
        echo '<div class="zappoppop-accordion-group zappoppop-pro-lock-group"';
        self::print_root_attrs($item);
        echo '>';
        self::dismiss_button($item);
        echo '<div class="zappoppop-pro-lock-head">' . esc_html($title) . '</div>';
        if ($item['text'] !== '') {
            echo '<p class="zappoppop-pro-lock-note">' . wp_kses($item['text'], $allowed) . '</p>';
        }
        foreach ($item['entries'] as $e) {
            $pill = $e['badge'] !== '' ? $e['badge'] : $badge;
            // Same classes as the real accordions: the builder's own handler and theme open, close and style it.
            echo '<div class="zappoppop-acc-item zpp-pro-acc">';
            echo '<button type="button" class="zappoppop-acc-header"><span>' . esc_html($e['name']) . '</span><span class="zpp-pro-badge">' . esc_html($pill) . '</span></button>';
            echo '<div class="zappoppop-acc-body zpp-pro-acc-body" style="display:none;"><div class="zpp-pro-acc-wrap">';
            $pv = self::preview_html($e);
            if ($pv !== '') {
                // exact copy of the real settings, every control disabled; escaped late with a fixed tag list
                echo '<div class="zpp-pro-acc-ctls zpp-pro-acc-real" aria-hidden="true">' . wp_kses($pv, self::preview_allowed_html()) . '</div>';
            } else {
                if ($e['description'] !== '') {
                    echo '<p class="zpp-pro-acc-desc">' . esc_html($e['description']) . '</p>';
                }
                echo '<div class="zpp-pro-acc-ctls" aria-hidden="true">';
                foreach ($e['controls'] as $c) {
                    self::render_lock_control($c);
                }
                echo '</div>';
            }
            if ($as_popup) {
                echo '<div class="zpp-pro-acc-overlay"><button type="button" class="button zpp-pro-acc-cta" data-zpp-pro-lock="1" data-zpp-pro-name="' . esc_attr($e['name']) . '">' . esc_html($o_text) . '</button></div>';
            } elseif ($as_link) {
                $attr = $item['overlay_new_tab'] ? ' target="_blank" rel="noopener noreferrer"' : '';
                echo '<div class="zpp-pro-acc-overlay"><a class="button zpp-pro-acc-cta" href="' . esc_url($o_url) . '"' . $attr . '>' . esc_html($o_text) . '</a></div>';
            }
            echo '</div></div></div>';
        }
        echo '</div>';
        self::render_modal();
    }

    /* ------------------------------------------------------------ rendering */

    private static function vars_css($e) {
        $map = array(
            'text_color'     => '--zpp-promo-fg',
            'bg_color'       => '--zpp-promo-bg',
            'cta_text_color' => '--zpp-promo-cta-fg',
            'cta_bg_color'   => '--zpp-promo-cta-bg',
        );
        $css = '';
        foreach ($map as $k => $var) {
            if (!empty($e[$k])) {
                $css .= $var . ':' . $e[$k] . ';';
            }
        }
        return $css;
    }

    private static function print_vars($e) {
        $css = self::vars_css($e);
        if ($css !== '') {
            echo ' style="' . esc_attr($css) . '"';
        }
    }

    private static function link($e, $class) {
        if ($e['cta'] === '' || $e['url'] === '') {
            return '';
        }
        $attr = $e['new_tab'] ? ' target="_blank" rel="noopener noreferrer"' : '';
        return '<a class="' . esc_attr($class) . '" href="' . esc_url($e['url']) . '"' . $attr . '>' . esc_html($e['cta']) . '</a>';
    }

    /** Wraps the image markup in a link when the entry has an image_url. */
    private static function image_link($e, $inner, $class) {
        if ($e['image_url'] === '' || $e['image'] === '') {
            return $inner;
        }
        $attr  = !empty($e['image_new_tab']) ? ' target="_blank" rel="noopener noreferrer"' : '';
        $label = $e['title'] !== '' ? $e['title'] : ($e['cta'] !== '' ? $e['cta'] : __('Learn more', 'zappoppop'));
        return '<a class="' . esc_attr($class) . '" href="' . esc_url($e['image_url']) . '" aria-label="' . esc_attr($label) . '"' . $attr . '>' . $inner . '</a>';
    }

    private static function dismiss_button($item) {
        if (empty($item['dismissible'])) {
            return;
        }
        echo '<button type="button" class="zpp-promo-x" data-zpp-promo-dismiss="1" aria-label="' . esc_attr__('Dismiss', 'zappoppop') . '"><span class="dashicons dashicons-no-alt" aria-hidden="true"></span></button>';
    }

    private static function print_root_attrs($item) {
        echo ' data-zpp-promo-id="' . esc_attr($item['id']) . '" data-zpp-promo-days="' . (int) $item['reshow_days'] . '"';
        if (empty($item['remember'])) {
            echo ' data-zpp-promo-remember="0"';
        }
        self::print_vars($item);
    }

    public static function render($slot) {
        if ($slot === 'menu_item' || $slot === 'plugin_link') {
            return;
        }
        $item = self::item($slot);
        if (!$item) {
            return;
        }
        $allowed = self::allowed_html();

        if ($slot === 'header_text') {
            echo '<div class="cell promo"';
            self::print_root_attrs($item);
            echo '>';
            echo '<span class="zpp-promo-text">' . wp_kses($item['text'], $allowed) . '</span>';
            echo wp_kses(self::link($item, 'zpp-promo-cta'), $allowed);
            self::dismiss_button($item);
            echo '</div>';
            return;
        }

        if ($slot === 'pro_extensions') { /* ZAPPOPPOP_PRO_LOCK */
            self::render_extensions($item);
            return;
        }
        if ($slot === 'builder_pro_settings') {
            self::render_builder_rows($item);
            return;
        }
        if ($slot === 'upgrade_popup') {
            return; // only printed together with a teaser that opens it
        }

        if ($slot === 'builder_box') {
            echo '<div class="zappoppop-promo zappoppop-promo--box"';
            self::print_root_attrs($item);
            echo '>';
            self::dismiss_button($item);
            if ($item['image'] !== '') {
                echo wp_kses_post(self::image_link($item, '<img class="zpp-promo-img" src="' . esc_url($item['image']) . '" alt="" loading="lazy">', 'zpp-promo-img-link'));
            }
            if ($item['title'] !== '') {
                echo '<strong class="zpp-promo-title">' . esc_html($item['title']) . '</strong>';
            }
            if ($item['text'] !== '') {
                echo '<p class="zpp-promo-body">' . wp_kses($item['text'], $allowed) . '</p>';
            }
            echo wp_kses(self::link($item, 'button button-primary zpp-promo-btn'), $allowed);
            echo '</div>';
            return;
        }

        if ($slot === 'dashboard_slider') {
            $total = count($item['slides']);
            // Same box markup as the other dashboard boxes, so dash-layout.js can drag it.
            echo '<div class="zappoppop-box zappoppop-promo zpp-promo-slider" id="zappoppop-box-promo" data-box-title="' . esc_attr__('Promotions', 'zappoppop') . '"';
            self::print_root_attrs($item);
            echo ' data-interval="' . (int) $item['interval'] . '" role="region" aria-roledescription="carousel" aria-label="' . esc_attr__('Promotions', 'zappoppop') . '">';
            self::dismiss_button($item);
            foreach ($item['slides'] as $n => $s) {
                $has = ($s['image'] !== '');
                $linked = ($has && $s['image_url'] !== '');
                $fit    = ($s['image_fit'] === 'cover') ? ' fit-cover' : ' fit-auto';
                $nocopy = ($s['title'] === '' && $s['text'] === '' && ($s['cta'] === '' || $s['url'] === '')) ? ' no-copy' : '';
                $cls = 'zpp-promo-slide' . ($n === 0 ? ' is-active' : '') . ($has ? ' has-img' . $fit . $nocopy : '') . ($linked ? ' has-link' : '');
                echo '<div class="' . esc_attr($cls) . '"';
                self::print_vars($s);
                echo ' role="group" aria-roledescription="slide" aria-label="' . esc_attr(($n + 1) . ' / ' . $total) . '"' . ($n === 0 ? '' : ' aria-hidden="true"') . '>';
                if ($has) {
                    echo wp_kses_post(self::image_link($s, '<img class="zpp-promo-bg" src="' . esc_url($s['image']) . '" alt="" loading="lazy">', 'zpp-promo-bg-link'));
                }
                echo '<div class="zpp-promo-slide-body">';
                if ($s['title'] !== '') {
                    echo '<strong class="zpp-promo-title">' . esc_html($s['title']) . '</strong>';
                }
                if ($s['text'] !== '') {
                    echo '<p class="zpp-promo-body">' . wp_kses($s['text'], $allowed) . '</p>';
                }
                echo wp_kses(self::link($s, 'button button-primary zpp-promo-btn'), $allowed);
                echo '</div></div>';
            }
            if ($total > 1) {
                echo '<button type="button" class="zpp-promo-nav zpp-promo-prev" aria-label="' . esc_attr__('Previous', 'zappoppop') . '"><span class="dashicons dashicons-arrow-left-alt2" aria-hidden="true"></span></button>';
                echo '<button type="button" class="zpp-promo-nav zpp-promo-next" aria-label="' . esc_attr__('Next', 'zappoppop') . '"><span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span></button>';
                echo '<div class="zpp-promo-dots">';
                for ($d = 0; $d < $total; $d++) {
                    echo '<button type="button" class="zpp-promo-dot' . ($d === 0 ? ' is-active' : '') . '" aria-label="' . esc_attr(($d + 1) . ' / ' . $total) . '"></button>';
                }
                echo '</div>';
            }
            echo '</div>';
        }
    }
}
