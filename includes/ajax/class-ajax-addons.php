<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add-on activation for the Extend page. Installing is done by WordPress itself (wp.updates.installPlugin);
 * this endpoint only activates an add-on that is already installed AND listed in the add-on catalog, so it cannot be
 * used to activate any other plugin. Needs manage_options, activate_plugins and a valid nonce.
 */
class ZapPopPop_Ajax_Addons {

    public function __construct() {
        add_action('wp_ajax_zappoppop_addon_activate', [$this, 'activate']);
    }

    protected function verify() {
        if (!current_user_can('manage_options') || !current_user_can('activate_plugins')) {
            wp_send_json_error(['message' => __('Unauthorized action.', 'zappoppop')], 403);
        }
        $nonce = isset($_REQUEST['nonce']) ? sanitize_text_field(wp_unslash($_REQUEST['nonce'])) : '';
        if (!wp_verify_nonce($nonce, 'zappoppop_admin_nonce')) {
            wp_send_json_error(['message' => __('Security check failed. Please refresh the page.', 'zappoppop')], 403);
        }
    }

    public function activate() {
        $this->verify();
        $slug = isset($_POST['slug']) ? sanitize_key(wp_unslash($_POST['slug'])) : '';
        $item = null;
        foreach (ZapPopPop_Addon_Catalog::items() as $it) {
            if ($it['slug'] === $slug) {
                $item = $it;
                break;
            }
        }
        if (!$item) {
            wp_send_json_error(['message' => __('Add-on not found.', 'zappoppop')]);
        }
        $status = ZapPopPop_Addon_Catalog::status($item);
        if ($status === 'missing') {
            wp_send_json_error(['message' => __('Install the add-on first.', 'zappoppop')]);
        }
        if ($status === 'inactive') {
            if (!function_exists('activate_plugin')) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }
            $res = activate_plugin($item['file']);
            if (is_wp_error($res)) {
                wp_send_json_error(['message' => wp_strip_all_tags($res->get_error_message())]);
            }
        }
        if (class_exists('ZapPopPop_Modules')) {
            ZapPopPop_Modules::flush();
        }
        wp_send_json_success(['slug' => $slug, 'status' => 'active', 'message' => __('Add-on activated.', 'zappoppop')]);
    }
}
