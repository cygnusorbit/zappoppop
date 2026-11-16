<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Modular Frontend Coordinator
 * Boots targeting, markup rendering, and display dispatchers.
 */
class ZapPopPop_Front {
    protected $targeting;
    protected $renderer;
    protected $display;
    protected $preview;

    public function __construct() {
        require_once ZAPPOPPOP_PATH . 'includes/front/class-front-targeting.php';
        require_once ZAPPOPPOP_PATH . 'includes/front/class-front-renderer.php';
        require_once ZAPPOPPOP_PATH . 'includes/front/class-front-display.php';
        require_once ZAPPOPPOP_PATH . 'includes/front/class-front-preview.php';

        $this->targeting = new ZapPopPop_Front_Targeting();
        $this->renderer  = new ZapPopPop_Front_Renderer();
        $this->display   = new ZapPopPop_Front_Display($this->targeting, $this->renderer);
        $this->preview   = new ZapPopPop_Front_Preview($this->renderer);
    }

    /**
     * Backward-compatibility proxy for external callers (e.g., Remote Embed AJAX endpoint).
     */
    public function render_popup_markup($uid, array $config, $is_inline = false) {
        return $this->renderer->render_popup_markup($uid, $config, $is_inline);
    }

    public function detect_visitor_country() {
        return $this->targeting->detect_visitor_country();
    }
}
