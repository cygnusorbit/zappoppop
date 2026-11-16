<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('ZapPopPop_Security') && file_exists(ZAPPOPPOP_PATH . 'includes/class-zappoppop-security.php')) {
    require_once ZAPPOPPOP_PATH . 'includes/class-zappoppop-security.php';
}

/**
 * Retrieve a ZapPopPop setting with static in-memory caching.
 */
function zappoppop_settings_store($reset = false) {
    static $settings = null;
    if ($reset) {
        $settings = null;
        return [];
    }
    if ($settings === null) {
        $settings = get_option('zappoppop_settings', []);
        if (!is_array($settings)) {
            $settings = [];
        }
    }
    return $settings;
}

function zappoppop_get_setting($key, $default = '') {
    $settings = zappoppop_settings_store();
    return isset($settings[$key]) ? $settings[$key] : $default;
}

/**
 * Flush in-memory settings cache on update.
 */
function zappoppop_flush_settings_cache() {
    zappoppop_settings_store(true);
}

/**
 * Strip comments and whitespace from inline CSS strings.
 */
function zappoppop_minify_css($css) {
    if (empty($css) || !is_string($css)) {
        return '';
    }
    $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
    $css = str_replace([': ', ' {', '{ ', ' }', '} ', '; '], [':', '{', '{', '}', '}', ';'], $css);
    $css = preg_replace('/\s+/', ' ', $css);
    return trim($css);
}

/**
 * System event logger for auditing and telemetry.
 */
function zappoppop_log_event($event_type, $message, $payload = []) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'zappoppop_logs';
    if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($table_name))) !== $table_name) {
        return false;
    }
    return $wpdb->insert(
        $table_name,
        [
            'event_type' => sanitize_key($event_type),
            'message'    => sanitize_text_field($message),
            'payload'    => !empty($payload) ? wp_json_encode($payload) : null,
            'created_at' => current_time('mysql')
        ],
        ['%s', '%s', '%s', '%s']
    );
}

/* ZAPPOPPOP_TRACKING_LOADER */
if (!class_exists('ZapPopPop_Tracking') && file_exists(ZAPPOPPOP_PATH . 'includes/class-zappoppop-tracking.php')) {
    require_once ZAPPOPPOP_PATH . 'includes/class-zappoppop-tracking.php';
}

/* ZAPPOPPOP_SETTINGS_RESET_LOADER */
if (!class_exists('ZapPopPop_Settings_Reset') && file_exists(ZAPPOPPOP_PATH . 'includes/class-zappoppop-settings-reset.php')) {
    require_once ZAPPOPPOP_PATH . 'includes/class-zappoppop-settings-reset.php';
}
