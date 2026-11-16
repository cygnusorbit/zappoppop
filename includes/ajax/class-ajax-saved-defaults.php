<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('ZapPopPop_Sanitizer')) {
    require_once ZAPPOPPOP_PATH . 'includes/class-zappoppop-sanitizer.php';
}

/** AJAX endpoints for ZapPopPop_Saved_Defaults (admin only, nonce protected). */
class ZapPopPop_Ajax_Saved_Defaults {
    public function __construct() {
        add_action('wp_ajax_zappoppop_defaults_save', [$this, 'save']);
        add_action('wp_ajax_zappoppop_defaults_clear', [$this, 'clear']);
    }

    private function verify() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized action.', 'zappoppop')], 403);
        }
        $nonce = isset($_REQUEST['nonce']) ? sanitize_text_field(wp_unslash($_REQUEST['nonce'])) : '';
        if (!wp_verify_nonce($nonce, ZapPopPop_Saved_Defaults::NONCE)) {
            wp_send_json_error(['message' => __('Security check failed. Please refresh the page.', 'zappoppop')], 403);
        }
    }

    private function scope() {
        return isset($_POST['scope']) ? sanitize_key(wp_unslash($_POST['scope'])) : '';
    }

    private function respond($res, $message) {
        if (is_wp_error($res)) {
            wp_send_json_error(['message' => $res->get_error_message()]);
        }
        wp_send_json_success(['message' => $message, 'status' => ZapPopPop_Saved_Defaults::status()]);
    }

    public function save() {
        $this->verify();
        $scope = $this->scope();
        $data  = null;
        if ($scope === 'settings') {
            $data = ZapPopPop_Saved_Defaults::capture_settings();
        } elseif ($scope === 'theme') {
            $data = ZapPopPop_Saved_Defaults::capture_theme();
        } elseif ($scope === 'popup') {
            $raw = ZapPopPop_Sanitizer::request_json('data');
            if (is_array($raw)) {
                $data = ZapPopPop_Saved_Defaults::sanitize_popup($raw);
            }
        }
        if (!is_array($data)) {
            wp_send_json_error(['message' => __('Nothing to save.', 'zappoppop')]);
        }
        $this->respond(ZapPopPop_Saved_Defaults::save($scope, $data), __('Defaults saved.', 'zappoppop'));
    }

    public function clear() {
        $this->verify();
        $this->respond(ZapPopPop_Saved_Defaults::clear($this->scope()), __('Saved defaults removed.', 'zappoppop'));
    }
}
