<?php
/**
 * ZapPopPop network settings (multisite). Super admins set the defaults that every NEW site starts with.
 * Existing sites keep their own settings, which stay on the per-site Settings page.
 *
 * @package ZapPopPop
 */
if (!defined('ABSPATH')) {
    exit;
}

class ZapPopPop_Admin_Network {

    const OPTION = 'zappoppop_network_settings';
    const PAGE   = 'zappoppop-network';
    const ACTION = 'zappoppop_network_save';
    const NONCE  = 'zappoppop_network_save';
    const CAP    = 'manage_network_options';
    const LIST   = 50;

    public function __construct() {
        add_action('network_admin_menu', array($this, 'register_menu'));
        add_action('network_admin_edit_' . self::ACTION, array($this, 'handle_save'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue'));
    }

    /** Stored network defaults, always complete and sanitised. */
    public static function get() {
        $stored = get_site_option(self::OPTION, array());
        return self::sanitize(is_array($stored) ? $stored : array());
    }

    public static function sanitize($raw) {
        $raw   = is_array($raw) ? $raw : array();
        $email = isset($raw['sender_email']) ? sanitize_email((string) $raw['sender_email']) : '';
        return array(
            'sender_name'        => isset($raw['sender_name']) ? sanitize_text_field((string) $raw['sender_name']) : '',
            'sender_email'       => is_email($email) ? $email : '',
            'clean_on_uninstall' => empty($raw['clean_on_uninstall']) ? 0 : 1,
        );
    }

    /**
     * Copies the network defaults into the CURRENT site's settings. Called right after a new site is set up;
     * empty network values leave the site's own default alone.
     */
    public static function apply_defaults() {
        $net = self::get();
        $cfg = get_option('zappoppop_settings', array());
        if (!is_array($cfg)) {
            return;
        }
        foreach (array('sender_name', 'sender_email') as $key) {
            if ($net[$key] !== '') {
                $cfg[$key] = $net[$key];
            }
        }
        if (!empty($net['clean_on_uninstall'])) {
            $cfg['clean_on_uninstall'] = 1;
        }
        update_option('zappoppop_settings', $cfg);
    }

    public function register_menu() {
        if (!class_exists('ZapPopPop_Multisite') || !ZapPopPop_Multisite::network_active()) {
            return;
        }
        add_menu_page(
            __('ZapPopPop Network', 'zappoppop'),
            __('ZapPopPop', 'zappoppop'),
            self::CAP,
            self::PAGE,
            array($this, 'render_page'),
            'dashicons-megaphone',
            30
        );
    }

    public function render_page() {
        if (!current_user_can(self::CAP)) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'zappoppop'));
        }
        $net_values = self::get();
        $net_status = isset($_GET['zpp_status']) ? sanitize_key(wp_unslash($_GET['zpp_status'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only status flag after a redirect
        $net_sites  = $this->site_rows();
        $net_total  = (int) get_sites(array('count' => true, 'deleted' => 0));
        include ZAPPOPPOP_PATH . 'templates/network-view.php';
    }

    /** One row per site (first LIST sites): name, settings link and its own "clean on uninstall" state. */
    protected function site_rows() {
        $ids  = get_sites(array('fields' => 'ids', 'number' => self::LIST, 'deleted' => 0, 'orderby' => 'id', 'order' => 'ASC'));
        $rows = array();
        foreach ($ids as $blog_id) {
            $cfg    = get_blog_option((int) $blog_id, 'zappoppop_settings', array());
            $rows[] = array(
                'id'    => (int) $blog_id,
                'name'  => (string) get_blog_option((int) $blog_id, 'blogname'),
                'url'   => get_admin_url((int) $blog_id, 'admin.php?page=zappoppop-settings'),
                'clean' => is_array($cfg) && !empty($cfg['clean_on_uninstall']),
                'ready' => is_array($cfg),
            );
        }
        return $rows;
    }

    public function handle_save() {
        if (!current_user_can(self::CAP)) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'zappoppop'), '', array('response' => 403));
        }
        check_admin_referer(self::NONCE, 'zpp_network_nonce');
        $raw = isset($_POST['zpp_network']) && is_array($_POST['zpp_network']) ? wp_unslash($_POST['zpp_network']) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitised field by field in sanitize()
        update_site_option(self::OPTION, self::sanitize($raw));
        wp_safe_redirect(add_query_arg(array('page' => self::PAGE, 'zpp_status' => 'saved'), network_admin_url('admin.php')));
        exit;
    }

    public function enqueue($hook) {
        $page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only page routing
        if ($page !== self::PAGE || !is_network_admin()) {
            return;
        }
        $ver = defined('ZAPPOPPOP_VERSION') ? ZAPPOPPOP_VERSION : '1.0';
        wp_enqueue_style('dashicons');
        wp_enqueue_style('zappoppop-dashboard-css', ZAPPOPPOP_URL . 'admin/css/dashboard.css', array(), $ver);
        wp_enqueue_style('zappoppop-overview-css', ZAPPOPPOP_URL . 'admin/css/overview.css', array(), $ver);
        wp_enqueue_style('zappoppop-theme-css', ZAPPOPPOP_URL . 'admin/css/theme.css', array('zappoppop-dashboard-css', 'zappoppop-overview-css', 'zappoppop-theme-global-css'), $ver);
        wp_enqueue_style('zappoppop-network-css', ZAPPOPPOP_URL . 'admin/css/network.css', array('zappoppop-theme-css'), $ver);
    }
}
