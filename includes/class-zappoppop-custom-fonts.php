<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Custom web fonts: structured data only. One font per line, "Name: url(https://example.com/font.woff2)".
 * Names and URLs are validated here; CSS is always rebuilt from the validated pieces. ZAPPOPPOP_FONTS_CLEAN
 */
class ZapPopPop_Custom_Fonts {
    const MAX_LINES = 40;
    const MAX_URLS  = 4;

    protected static function formats() {
        return array('woff2' => 'woff2', 'woff' => 'woff', 'ttf' => 'truetype', 'otf' => 'opentype');
    }

    /** @return string[] clean URL, or '' when the URL is not acceptable */
    protected static function clean_url($url) {
        $url = trim((string) $url);
        if ($url === '' || strlen($url) > 500 || preg_match('/[\s\'"()\\\\<>]/', $url) || !preg_match('#^https?://#i', $url)) {
            return '';
        }
        $url = esc_url_raw($url, array('http', 'https'));
        if ($url === '') {
            return '';
        }
        $path = (string) wp_parse_url($url, PHP_URL_PATH);
        $ext  = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $fmt  = self::formats();
        return isset($fmt[$ext]) ? $url : '';
    }

    protected static function clean_name($name) {
        $name = trim(sanitize_text_field((string) $name));
        return preg_match('/^[\p{L}\p{N} _-]{1,60}$/u', $name) ? $name : '';
    }

    /**
     * Parse stored or submitted text.
     * @return array fonts => [ [name, urls[]], ... ], rejected => int (lines that were dropped)
     */
    public static function parse($raw) {
        $out   = array('fonts' => array(), 'rejected' => 0);
        $lines = preg_split('/\r\n|\r|\n/', (string) $raw);
        $seen  = 0;
        foreach ((array) $lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            if (++$seen > self::MAX_LINES) {
                $out['rejected']++;
                continue;
            }
            $pos  = strpos($line, ':');
            $name = $pos === false ? '' : self::clean_name(substr($line, 0, $pos));
            $src  = $pos === false ? '' : trim(substr($line, $pos + 1));
            $urls = array();
            if ($name !== '' && $src !== '') {
                if (preg_match_all('/url\(\s*[\'"]?([^\'")\s]+)[\'"]?\s*\)/i', $src, $mm)) {
                    $cands = $mm[1];
                } else {
                    $cands = array($src);
                }
                foreach ($cands as $c) {
                    $u = self::clean_url($c);
                    if ($u !== '' && !in_array($u, $urls, true) && count($urls) < self::MAX_URLS) {
                        $urls[] = $u;
                    }
                }
            }
            if ($name === '' || empty($urls)) {
                $out['rejected']++;
                continue;
            }
            $out['fonts'][] = array($name, $urls);
        }
        return $out;
    }

    /** Canonical text that is stored in the setting. @return array text, rejected */
    public static function clean($raw) {
        $p = self::parse($raw);
        $lines = array();
        foreach ($p['fonts'] as $f) {
            $parts = array();
            foreach ($f[1] as $u) {
                $parts[] = 'url(' . $u . ')';
            }
            $lines[] = $f[0] . ': ' . implode(', ', $parts);
        }
        return array('text' => implode("\n", $lines), 'rejected' => $p['rejected']);
    }

    public static function raw() {
        $raw = function_exists('zappoppop_get_setting') ? zappoppop_get_setting('custom_fonts', '') : '';
        if (!is_string($raw) || trim($raw) === '') {
            $settings = get_option('zappoppop_settings', array());
            $raw = (is_array($settings) && !empty($settings['custom_fonts']) && is_string($settings['custom_fonts'])) ? $settings['custom_fonts'] : '';
        }
        return $raw;
    }

    /** @font-face rules built only from validated names and URLs. */
    public static function css() {
        $p   = self::parse(self::raw());
        $fmt = self::formats();
        $css = '';
        foreach ($p['fonts'] as $f) {
            $src = array();
            foreach ($f[1] as $u) {
                $ext   = strtolower(pathinfo((string) wp_parse_url($u, PHP_URL_PATH), PATHINFO_EXTENSION));
                $src[] = "url('" . $u . "') format('" . $fmt[$ext] . "')";
            }
            $css .= "@font-face {\n  font-family: '" . $f[0] . "';\n  src: " . implode(', ', $src) . ";\n  font-display: swap;\n}\n";
        }
        return $css;
    }

    /** Unique family names for the builder font dropdown. */
    public static function families() {
        $names = array();
        $p = self::parse(self::raw());
        foreach ($p['fonts'] as $f) {
            $names[] = $f[0];
        }
        return array_values(array_unique($names));
    }
}
