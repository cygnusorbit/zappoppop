<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('ZapPopPop_Security')) {
    require_once ZAPPOPPOP_PATH . 'includes/class-zappoppop-security.php';
}

class ZapPopPop_Front_Targeting {

    public function detect_visitor_country() {
        return ZapPopPop_Module_Bridge::visitor_country();
    }

    public function is_eligible($popup) {
        if (!$popup || empty($popup->status) || $popup->status !== 'publish') {
            return false;
        }

        $zappoppop_raw_cfg = !empty($popup->data) ? $popup->data : (!empty($popup->config) ? $popup->config : '');
        $config = $zappoppop_raw_cfg !== '' ? json_decode($zappoppop_raw_cfg, true) : [];
        if (!is_array($config)) {
            $config = [];
        }

        if (class_exists('ZapPopPop_Modules') && !ZapPopPop_Modules::active('acc-targeting')) {
            unset($config['targeting']); /* ZAPPOPPOP_MODULE_GATE */
        }

        // 1. Device Viewport Targeting
        $target_settings = isset($config['targeting']) && is_array($config['targeting']) ? $config['targeting'] : [];
        $devices = isset($target_settings['devices']) ? $target_settings['devices'] : 'all';
        $is_mobile = wp_is_mobile();

        if ($devices === 'desktop' && $is_mobile) {
            return false;
        }
        if ($devices === 'mobile' && !$is_mobile) {
            return false;
        }

        // 2. User Authentication Targeting
        $users = isset($target_settings['users']) ? $target_settings['users'] : 'all';
        if ($users === 'logged_in' && !is_user_logged_in()) {
            return false;
        }
        if ($users === 'logged_out' && is_user_logged_in()) {
            return false;
        }

        /* Extension modules (Pro) add their own targeting rules, e.g. geo country, visitor authentication, activity period, roles, site area. */
        return (bool) apply_filters('zappoppop_popup_eligible', true, $popup, $config);
    }
}
