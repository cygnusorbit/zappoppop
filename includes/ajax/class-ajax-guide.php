<?php
if (!defined('ABSPATH')) {
    exit;
}

class ZapPopPop_Ajax_Guide {
    public function __construct() {
        add_action('wp_ajax_zappoppop_guide_save', [$this, 'save']);
    }

    public function save() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized capability.', 'zappoppop')], 403);
        }
        check_ajax_referer(ZapPopPop_Admin_Guide::NONCE, 'nonce');

        $op   = isset($_POST['op']) ? sanitize_key(wp_unslash($_POST['op'])) : '';
        $step = isset($_POST['step']) ? min(999, absint(wp_unslash($_POST['step']))) : 0;
        $uid  = get_current_user_id();

        switch ($op) {
            case 'progress':
                $state = ZapPopPop_Admin_Guide::save_state($uid, ['last_step' => max(1, $step)]);
                break;
            case 'complete':
                $state = ZapPopPop_Admin_Guide::save_state($uid, ['completed' => 1, 'last_step' => max(1, $step)]);
                break;
            case 'dismiss':
                $state = ZapPopPop_Admin_Guide::save_state($uid, ['dismissed' => 1, 'last_step' => max(1, $step)]);
                break;
            case 'reset':
                $state = ZapPopPop_Admin_Guide::reset_state($uid);
                break;
            default:
                wp_send_json_error(['message' => __('Unknown guide action.', 'zappoppop')], 400);
        }

        wp_send_json_success(['state' => $state]);
    }
}
