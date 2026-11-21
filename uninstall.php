<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * ZapPopPop Uninstall Procedure
 * Executes automatically when the plugin is deleted via the WordPress Admin.
 * On a multisite network every site is cleaned in turn, and each site keeps its data
 * unless its own "Clean on uninstall" setting is on.
 */
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

if (!function_exists('zappoppop_uninstall_site')) {
    /**
     * Removes the ZapPopPop data of the current site when its "Clean on uninstall" setting is on.
     * Tables added by ZapPopPop Pro are removed by Pro.
     */
    function zappoppop_uninstall_site() {
        global $wpdb;

        $zappoppop_uninstall_settings = get_option('zappoppop_settings', []);
        if (!is_array($zappoppop_uninstall_settings) || empty($zappoppop_uninstall_settings['clean_on_uninstall'])) {
            return;
        }

        // 1. Drop the plugin's custom database tables.
        $zappoppop_tables = [
            $wpdb->prefix . 'zappoppop_items',
            $wpdb->prefix . 'zappoppop_submissions',
            $wpdb->prefix . 'zappoppop_logs',
            $wpdb->prefix . 'zappoppop_transactions',
        ];

        foreach ($zappoppop_tables as $zappoppop_table) {
            $wpdb->query('DROP TABLE IF EXISTS `' . esc_sql($zappoppop_table) . '`'); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        }

        // 2. Remove all options
        delete_option('zappoppop_settings');
        delete_option('zappoppop_saved_defaults');
        delete_option('zappoppop_db_version');
        delete_option('zappoppop_cookie_epoch');
        delete_option('zappoppop_activated');
        delete_option('zappoppop_schema_is_read');
        delete_option('zappoppop_modules_state');
        delete_option('zappoppop_modules_orphans');
        delete_option('zappoppop_modules_seeded');
        delete_option('zappoppop_module_categories');
        delete_option('zappoppop_module_category_map');
        delete_option('zappoppop_module_image_map');

        // 2b. Remove the per-user builder guide progress.
        delete_metadata('user', 0, 'zappoppop_guide_state', '', true);

        // 3. Purge cached transients
        $wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                $wpdb->esc_like('_transient_zappoppop_') . '%',
                $wpdb->esc_like('_transient_timeout_zappoppop_') . '%'
            )
        );

        // 4. Remove the uploaded data and module folders of this site (symlinks are unlinked, never followed).
        $zappoppop_upload_dir = wp_upload_dir();
        foreach (['zappoppop', 'zappoppop-modules', 'zappoppop-submissions'] as $zappoppop_sub) {
            $zappoppop_root = $zappoppop_upload_dir['basedir'] . '/' . $zappoppop_sub;
            if (!is_dir($zappoppop_root) || is_link($zappoppop_root)) {
                continue;
            }
            $zappoppop_items = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($zappoppop_root, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($zappoppop_items as $zappoppop_item) {
                if ($zappoppop_item->isLink() || !$zappoppop_item->isDir()) {
                    wp_delete_file($zappoppop_item->getPathname());
                } else {
                    rmdir($zappoppop_item->getPathname()); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir
                }
            }
            rmdir($zappoppop_root); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir
        }
    }
}

if (is_multisite()) {
    $zappoppop_batch  = 100;
    $zappoppop_offset = 0;
    do {
        $zappoppop_site_ids = get_sites([
            'fields'  => 'ids',
            'number'  => $zappoppop_batch,
            'offset'  => $zappoppop_offset,
            'orderby' => 'id',
            'order'   => 'ASC',
        ]);
        foreach ($zappoppop_site_ids as $zappoppop_site_id) {
            switch_to_blog((int) $zappoppop_site_id);
            zappoppop_uninstall_site();
            restore_current_blog();
        }
        $zappoppop_offset += $zappoppop_batch;
    } while (count($zappoppop_site_ids) === $zappoppop_batch);
    $zappoppop_network_settings = get_site_option('zappoppop_network_settings', []);
    if (is_array($zappoppop_network_settings) && !empty($zappoppop_network_settings['clean_on_uninstall'])) {
        delete_site_option('zappoppop_network_settings');
    }
} else {
    zappoppop_uninstall_site();
}
