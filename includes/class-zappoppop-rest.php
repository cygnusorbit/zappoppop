<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Modular REST API Coordinator
 * Registers decomposed delivery, submissions, and diagnostic controllers.
 */
class ZapPopPop_Rest {
    protected $delivery;
    protected $submissions;
    protected $diagnostics;

    public function __construct() {
        require_once ZAPPOPPOP_PATH . 'includes/rest/class-rest-delivery.php';
        require_once ZAPPOPPOP_PATH . 'includes/rest/class-rest-submissions.php';
        require_once ZAPPOPPOP_PATH . 'includes/rest/class-rest-diagnostics.php';

        $this->delivery    = new ZapPopPop_Rest_Delivery();
        $this->submissions = new ZapPopPop_Rest_Submissions();
        $this->diagnostics = new ZapPopPop_Rest_Diagnostics();

        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes() {
        $this->delivery->register_routes();
        $this->submissions->register_routes();
        $this->diagnostics->register_routes();
    }
}
