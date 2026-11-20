<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Disposable e-mail check shared by every submission route (REST and Ajax).
 * Core ships a short list; add-ons extend it through the `zappoppop_disposable_domains` filter.
 * The check only runs when an Email layer of the submitted popup has "Block Disposable Emails" switched on.
 */
class ZapPopPop_Disposable {

    /** @return string[] lower-case provider domains. */
    public static function domains() {
        $list = (array) apply_filters('zappoppop_disposable_domains', array(
            'mailinator.com', '10minutemail.com', 'guerrillamail.com', 'trashmail.com', 'tempmail.com', 'sharklasers.com',
        ));
        $out = array();
        foreach ($list as $d) {
            $d = strtolower(trim((string) $d));
            if ($d !== '') {
                $out[] = $d;
            }
        }
        return array_values(array_unique($out));
    }

    /** True when the address belongs to a listed provider (or one of its sub-domains). */
    public static function is_disposable($email) {
        $at = strrpos((string) $email, '@');
        if ($at === false) {
            return false;
        }
        $domain = strtolower(trim(substr((string) $email, $at + 1)));
        if ($domain === '') {
            return false;
        }
        foreach (self::domains() as $d) {
            if ($domain === $d || substr($domain, -strlen($d) - 1) === '.' . $d) {
                return true;
            }
        }
        return false;
    }

    /** True when any Email layer in the popup config has the switch on. */
    public static function config_blocks($node) {
        if (!is_array($node)) {
            return false;
        }
        if (isset($node['type']) && $node['type'] === 'email' && isset($node['blockDisposable'])) {
            $v = $node['blockDisposable'];
            if (!empty($v) && $v !== 'false' && $v !== '0') {
                return true;
            }
        }
        foreach ($node as $child) {
            if (is_array($child) && self::config_blocks($child)) {
                return true;
            }
        }
        return false;
    }

    /** @return string error message, or '' when the submission may go on. */
    public static function check($config, $email) {
        $email = (string) $email;
        if ($email === '' || !self::config_blocks($config)) {
            return '';
        }
        return self::is_disposable($email) ? __('Disposable and temporary email addresses are not allowed.', 'zappoppop') : '';
    }

    /** Same check for routes that only know the popup uid. */
    public static function check_uid($uid, $email) {
        global $wpdb;
        $uid = (string) $uid;
        if ($uid === '' || (string) $email === '') {
            return '';
        }
        $raw    = $wpdb->get_var($wpdb->prepare("SELECT data FROM {$wpdb->prefix}zappoppop_items WHERE uid = %s", $uid)); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- table name is built from $wpdb->prefix and the value is prepared
        $config = json_decode((string) $raw, true);
        return self::check(is_array($config) ? $config : array(), $email);
    }
}
