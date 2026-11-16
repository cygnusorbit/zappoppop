<?php
if (!defined('ABSPATH')) {
    exit;
}

require_once ZAPPOPPOP_PATH . 'includes/widget/class-widget-form.php';
require_once ZAPPOPPOP_PATH . 'includes/widget/class-widget-render.php';

/**
 * Modular ZapPopPop Sidebar Widget
 * Delegates form controls and frontend output to isolated domain handlers.
 */
class ZapPopPop_Widget extends WP_Widget {
    protected $form_handler;
    protected $render_handler;

    public function __construct() {
        parent::__construct(
            'zappoppop_widget',
            'ZapPopPop Popup Widget',
            [
                'classname'   => 'widget_zappoppop',
                'description' => __('Display an embedded popup campaign or click trigger button in any sidebar or widget area.', 'zappoppop'),
            ]
        );

        $this->form_handler   = new ZapPopPop_Widget_Form();
        $this->render_handler = new ZapPopPop_Widget_Render();
    }

    public function widget($args, $instance) {
        $this->render_handler->render_widget($this, $args, $instance);
    }

    public function form($instance) {
        $this->form_handler->render_form($this, $instance);
    }

    public function update($new_instance, $old_instance) {
        return $this->form_handler->sanitize_settings($new_instance, $old_instance);
    }
}
