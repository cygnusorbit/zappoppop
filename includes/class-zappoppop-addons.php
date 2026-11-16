<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Modular Addons Coordinator
 * Boots the Double Opt-In confirmation link. Downloads, WooCommerce and SMS live in the Pro plugin.
 */
class ZapPopPop_Addons {
    protected $optin;

    public function __construct() {
        require_once ZAPPOPPOP_PATH . 'includes/addons/class-addon-optin.php';

        $this->optin = new ZapPopPop_Addon_Optin();
    }

    public function get_optin_handler() {
        return $this->optin;
    }
}
