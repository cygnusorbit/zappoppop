<?php
/**
 * Reads data exported by the previous generation of this plugin and maps it to the current names.
 * Keys and string values are converted. URLs, /wp-content/ paths and image file names are left untouched
 * so media links and template bundle image maps keep working.
 *
 * @package ZapPopPop
 */

if (!defined('ABSPATH')) {
    exit;
}

class ZapPopPop_Legacy_Import {

    /** Previous spellings => current ones. */
    const BRAND = array(
        'wppoppop' => 'zappoppop',
        'WpPopPop' => 'ZapPopPop',
        'WPPOPPOP' => 'ZAPPOPPOP',
        'Wppoppop' => 'Zappoppop',
    );

    /** Parts of a string that are copied untouched: URLs, wp-content paths and image file names. */
    const KEEP = '~((?:https?:)?//[^\s"\'()<>]+|/wp-content/[^\s"\'()<>]+|[A-Za-z0-9_@%./-]*\.(?:png|jpe?g|gif|webp|svg|avif|ico)\b)~i';

    /**
     * Converts arrays, stdClass objects and strings (recursively). Anything else is returned as is.
     *
     * @param mixed $node Decoded JSON data.
     * @return mixed
     */
    public static function convert($node) {
        if (is_string($node)) {
            return self::text($node);
        }
        if (is_array($node)) {
            $out = array();
            foreach ($node as $k => $v) {
                $out[is_string($k) ? self::text($k) : $k] = self::convert($v);
            }
            return $out;
        }
        if (is_object($node) && get_class($node) === 'stdClass') {
            $out = new stdClass();
            foreach (get_object_vars($node) as $k => $v) {
                $key       = self::text((string) $k);
                $out->$key = self::convert($v);
            }
            return $out;
        }
        return $node;
    }

    /**
     * Converts one string, skipping the protected parts.
     *
     * @param string $s Input.
     * @return string
     */
    public static function text($s) {
        $s = (string) $s;
        if ($s === '' || (stripos($s, 'poppop') === false && stripos($s, 'wpp') === false)) {
            return $s;
        }
        $parts = preg_split(self::KEEP, $s, -1, PREG_SPLIT_DELIM_CAPTURE);
        if (!is_array($parts)) {
            return $s;
        }
        foreach ($parts as $i => $part) {
            $parts[$i] = ($i % 2 === 1) ? self::url($part) : self::plain($part);
        }
        return implode('', $parts);
    }

    /**
     * Protected segment: only the plugin folder name inside a plugins URL is mapped.
     *
     * @param string $u Segment.
     * @return string
     */
    protected static function url($u) {
        $r = preg_replace('~(/wp-content/plugins/)wppoppop((?:-pro)?/)~', '${1}zappoppop${2}', $u);
        return is_string($r) ? $r : $u;
    }

    /**
     * Plain text segment.
     *
     * @param string $t Segment.
     * @return string
     */
    protected static function plain($t) {
        $orig = $t;
        $t    = str_replace(array_keys(self::BRAND), array_values(self::BRAND), $t);
        $r    = preg_replace(
            array('~(?<![A-Za-z0-9])wpp(?=[_-])~', '~(?<![A-Za-z0-9])wpp(?=[A-Z])~', '~(?<![A-Za-z0-9])WPP(?=_)~'),
            array('zpp', 'zpp', 'ZPP'),
            $t
        );
        return is_string($r) ? $r : $orig;
    }
}
