<?php
/**
 * Translatable names and descriptions of the built-in modules.
 * The module.json files stay English (they are data); this class gives the same text as literal
 * __() calls so it is extracted into the POT file. Read through ZapPopPop_Modules::display().
 */
if (!defined('ABSPATH')) {
    exit;
}

class ZapPopPop_Module_Strings {

    /** @return array<string, array{name: string, description: string}> slug => translated text */
    public static function map() {
        return array(
            'acc-01-canvas-styling' => array(
                'name'        => __('Canvas Settings', 'zappoppop'),
                'description' => __('Canvas size, colours, close behaviour and auto-close.', 'zappoppop'),
            ),
            'acc-display-triggers'  => array(
                'name'        => __('Display Triggers', 'zappoppop'),
                'description' => __('Page load, always-on, repeat, exit intent and scroll depth triggers.', 'zappoppop'),
            ),
            'acc-notifications'     => array(
                'name'        => __('Notifications', 'zappoppop'),
                'description' => __('Notification messages for popup events.', 'zappoppop'),
            ),
        );
    }
}
