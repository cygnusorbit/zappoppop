<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Multisite foundation. Every ZapPopPop table and setting lives per site, so setup has to run once for each site:
 * on network activation, and again whenever a new site is created. Companion plugins join in through the
 * zappoppop_site_setup action; on a single-site install the same action fires once on activation.
 */
class ZapPopPop_Multisite {

    const BATCH = 100;

    /** Table name suffixes (after the site prefix) that belong to one site. */
    const TABLES = array('zappoppop_items', 'zappoppop_submissions', 'zappoppop_logs', 'zappoppop_transactions');

    public static function hooks() {
        if (!is_multisite()) {
            return;
        }
        /* Late priority: WordPress creates the new site's own tables at priority 10. */
        add_action('wp_initialize_site', array(__CLASS__, 'on_new_site'), 900, 1);
        add_filter('wpmu_drop_tables', array(__CLASS__, 'drop_tables'), 10, 2);
    }

    /** Activation callback. $network_wide is true when a super admin used "Network Activate". */
    public static function activate($network_wide = false) {
        if (is_multisite() && $network_wide) {
            self::each_site(array(__CLASS__, 'setup_site'));
            return;
        }
        self::setup_site();
    }

    /** Creates tables and default settings for the current site, then lets companion plugins do the same. */
    public static function setup_site() {
        ZapPopPop_Installer::activate();
        do_action('zappoppop_site_setup', get_current_blog_id());
    }

    /** A site was created: set it up when ZapPopPop is network active. */
    public static function on_new_site($site) {
        if (!is_object($site) || empty($site->blog_id) || !self::network_active()) {
            return;
        }
        switch_to_blog((int) $site->blog_id);
        try {
            self::setup_site();
            ZapPopPop_Admin_Network::apply_defaults();
        } finally {
            restore_current_blog();
        }
    }

    /** Runs $callback(blog_id) inside every site, in batches. $args are passed to get_sites(). */
    public static function each_site($callback, array $args = array()) {
        if (!is_multisite()) {
            call_user_func($callback, get_current_blog_id());
            return;
        }
        $offset = 0;
        do {
            $ids = get_sites(array_merge(array('deleted' => 0), $args, array(
                'fields'  => 'ids',
                'number'  => self::BATCH,
                'offset'  => $offset,
                'orderby' => 'id',
                'order'   => 'ASC',
            )));
            foreach ($ids as $blog_id) {
                switch_to_blog((int) $blog_id);
                try {
                    call_user_func($callback, (int) $blog_id);
                } finally {
                    restore_current_blog();
                }
            }
            $offset += self::BATCH;
        } while (count($ids) === self::BATCH);
    }

    public static function network_active() {
        if (!function_exists('is_plugin_active_for_network')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        return is_multisite() && is_plugin_active_for_network(ZAPPOPPOP_BASENAME);
    }

    /** Deleting a site from the network also drops its ZapPopPop tables (companion plugins add theirs through the filter). */
    public static function drop_tables($tables, $blog_id) {
        global $wpdb;
        $prefix = $wpdb->get_blog_prefix((int) $blog_id);
        $names  = apply_filters('zappoppop_site_tables', self::TABLES);
        foreach ((array) $names as $name) {
            $tables[] = $prefix . $name;
        }
        return $tables;
    }
}
