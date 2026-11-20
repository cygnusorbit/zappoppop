<?php
/**
 * Plugin Name: ZapPopPop
 * Plugin URI: https://zappoppop.com
 * Description: The ultimate, high-converting WordPress visual popup builder and lead capture platform.
 * Version: 1.0.06
 * Author: ZapPopPop
 * Author URI: 
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: zappoppop
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define Core Plugin Constants
define('ZAPPOPPOP_VERSION', '1.0.06');
define('ZAPPOPPOP_PATH', plugin_dir_path(__FILE__));
define('ZAPPOPPOP_URL', plugin_dir_url(__FILE__));
define('ZAPPOPPOP_BASENAME', plugin_basename(__FILE__));
define('ZAPPOPPOP_API_VERSION', 1);

// Load Dynamic Autoloader
require_once ZAPPOPPOP_PATH . 'includes/class-zappoppop-autoloader.php';
ZapPopPop_Autoloader::register();

// Load Global Helper Utilities
require_once ZAPPOPPOP_PATH . 'includes/helpers.php';

// Register Database Installer & Activation Routine
register_activation_hook(__FILE__, ['ZapPopPop_Multisite', 'activate']);

/**
 * Main ZapPopPop Application Singleton
 */
final class ZapPopPop {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init_components();
    }

    private function init_components() {
        new ZapPopPop_I18n();
        new ZapPopPop_Hooks();
        ZapPopPop_Multisite::hooks();
        new ZapPopPop_Capabilities();
        new ZapPopPop_Ajax();
        new ZapPopPop_Rest();
        new ZapPopPop_Front();

        if (is_admin()) {
            new ZapPopPop_Admin();
            add_action('admin_init', ['ZapPopPop_Modules_Installer', 'migrate_legacy']);
            add_action('admin_init', 'zappoppop_purge_legacy_code_fields');
        }
        if (class_exists('ZapPopPop_Addons')) {
            new ZapPopPop_Addons();
        }
        if (class_exists('ZapPopPop_Widget_Block')) {
            new ZapPopPop_Widget_Block();
        }
        if (class_exists('ZapPopPop_Widget')) {
            add_action('widgets_init', function() {
                register_widget('ZapPopPop_Widget');
            });
        }
        do_action('zappoppop_loaded', $this);
    }
}

// Initialize Application Core
function zappoppop() {
    return ZapPopPop::get_instance();
}

/**
 * Removes custom CSS / JS / head / footer code stored by earlier versions (arbitrary code entry is no longer supported).
 */
function zappoppop_purge_legacy_code_fields() {
    if (get_option('zappoppop_code_fields_purged')) {
        return;
    }
    $settings = get_option('zappoppop_settings', []);
    if (is_array($settings)) {
        foreach (['custom_css', 'custom_js', 'custom_header_html', 'custom_footer_html'] as $key) {
            unset($settings[$key]);
        }
        update_option('zappoppop_settings', $settings);
    }
    if (class_exists('ZapPopPop_Admin_Theme')) {
        $theme = get_option(ZapPopPop_Admin_Theme::OPTION, []);
        if (is_array($theme) && isset($theme['custom_css'])) {
            unset($theme['custom_css']);
            update_option(ZapPopPop_Admin_Theme::OPTION, $theme);
        }
    }
    update_option('zappoppop_code_fields_purged', 1, false);
}

add_action('plugins_loaded', 'zappoppop');
require_once plugin_dir_path( __FILE__ ) . 'includes/class-zappoppop-security.php';

require_once plugin_dir_path( __FILE__ ) . 'includes/class-zappoppop-builder-theme.php';
