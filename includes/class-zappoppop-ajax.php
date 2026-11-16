<?php
if (!defined('ABSPATH')) {
    exit;
}

class ZapPopPop_Ajax {
    public function __construct() {
        $modules = [
            'ZapPopPop_Ajax_Builder'     => 'class-ajax-builder.php',
            'ZapPopPop_Ajax_Settings'    => 'class-ajax-settings.php',
            'ZapPopPop_Ajax_Frontend'    => 'class-ajax-frontend.php',
            'ZapPopPop_Ajax_Submissions' => 'class-ajax-submissions.php',
            'ZapPopPop_Ajax_Dashboard'   => 'class-ajax-dashboard.php',
            'ZapPopPop_Ajax_Tools'       => 'class-ajax-tools.php',
            'ZapPopPop_Ajax_Dash_Layout' => 'class-ajax-dash-layout.php',
            'ZapPopPop_Ajax_Modules'     => 'class-ajax-modules.php',
            'ZapPopPop_Ajax_Template_Bundle' => 'class-ajax-template-bundle.php',
            'ZapPopPop_Ajax_Saved_Defaults' => 'class-ajax-saved-defaults.php',
            'ZapPopPop_Ajax_Guide' => 'class-ajax-guide.php',
        ];

        foreach ($modules as $class => $file) {
            $path = ZAPPOPPOP_PATH . 'includes/ajax/' . $file;
            if (file_exists($path)) {
                require_once $path;
            }
            if (!class_exists($class)) {
                continue;
            }
            // Avoid double registration if another loader already booted it.
            if ($class === 'ZapPopPop_Ajax_Frontend' && has_action('wp_ajax_nopriv_zappoppop_submit_form')) {
                continue;
            }
            if ($class === 'ZapPopPop_Ajax_Submissions' && has_action('wp_ajax_zappoppop_delete_submission')) {
                continue;
            }
            new $class();
        }
    }
}
