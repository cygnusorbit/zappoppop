<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('ZapPopPop_Admin_Pages') && file_exists(ZAPPOPPOP_PATH . 'includes/admin/class-admin-pages.php')) {
    require_once ZAPPOPPOP_PATH . 'includes/admin/class-admin-pages.php';
}
if (!class_exists('ZapPopPop_Admin_Menu') && file_exists(ZAPPOPPOP_PATH . 'includes/admin/class-admin-menu.php')) {
    require_once ZAPPOPPOP_PATH . 'includes/admin/class-admin-menu.php';
}
if (!class_exists('ZapPopPop_Inline_Assets') && file_exists(ZAPPOPPOP_PATH . 'includes/admin/class-admin-inline-assets.php')) {
    require_once ZAPPOPPOP_PATH . 'includes/admin/class-admin-inline-assets.php';
}
if (!class_exists('ZapPopPop_Admin_Assets') && file_exists(ZAPPOPPOP_PATH . 'includes/admin/class-admin-assets.php')) {
    require_once ZAPPOPPOP_PATH . 'includes/admin/class-admin-assets.php';
}
if (!class_exists('ZapPopPop_Admin_Export') && file_exists(ZAPPOPPOP_PATH . 'includes/admin/class-admin-export.php')) {
    require_once ZAPPOPPOP_PATH . 'includes/admin/class-admin-export.php';
}

if (!class_exists('ZapPopPop_Admin_Theme') && file_exists(ZAPPOPPOP_PATH . 'includes/admin/class-admin-theme.php')) {
    require_once ZAPPOPPOP_PATH . 'includes/admin/class-admin-theme.php';
}

class ZapPopPop_Admin {
    protected $pages;
    protected $menu;
    protected $assets;
    protected $theme;
    protected $export;

    public function __construct() {
        $this->pages  = new ZapPopPop_Admin_Pages();
        $this->menu   = new ZapPopPop_Admin_Menu($this->pages);
        $this->theme  = new ZapPopPop_Admin_Theme();
        if (is_multisite()) {
            new ZapPopPop_Admin_Network();
        }
        $this->assets = new ZapPopPop_Admin_Assets();
        new ZapPopPop_Admin_Guide();
        if (class_exists('ZapPopPop_Admin_Export')) {
            $this->export = new ZapPopPop_Admin_Export();
        }
    }
}
