<?php
/**
 * Colour value whitelist shared by the builder save path and the frontend renderer.
 * Accepts what the alpha colour picker can produce: #rgb / #rgba / #rrggbb / #rrggbbaa, rgb() / rgba(), "transparent" and plain colour keywords.
 */
if (!defined('ABSPATH')) {
    exit;
}

class ZapPopPop_Color {

    /**
     * Return a normalised, safe colour string, or $fallback when the value is not an accepted colour.
     *
     * @param mixed  $value    Raw value.
     * @param string $fallback Returned for anything not accepted.
     * @return string
     */
    public static function sanitize($value, $fallback = '') {
        if (!is_scalar($value)) {
            return $fallback;
        }
        $v = strtolower(trim((string) $value));
        if ($v === 'transparent') {
            return 'transparent';
        }
        if (preg_match('/^#(?:[0-9a-f]{3,4}|[0-9a-f]{6}|[0-9a-f]{8})$/', $v) === 1) {
            return $v;
        }
        if (preg_match('/^rgba?\(\s*(\d{1,3}(?:\.\d+)?)\s*,\s*(\d{1,3}(?:\.\d+)?)\s*,\s*(\d{1,3}(?:\.\d+)?)\s*(?:,\s*(\d{1,3}(?:\.\d+)?|\.\d+)(%?)\s*)?\)$/', $v, $m) === 1) {
            $r = max(0, min(255, (int) round((float) $m[1])));
            $g = max(0, min(255, (int) round((float) $m[2])));
            $b = max(0, min(255, (int) round((float) $m[3])));
            $a = (isset($m[4]) && $m[4] !== '') ? (float) $m[4] / ((isset($m[5]) && $m[5] === '%') ? 100 : 1) : 1.0;
            $a = max(0.0, min(1.0, $a));
            return 'rgba(' . $r . ',' . $g . ',' . $b . ',' . rtrim(rtrim(number_format($a, 3, '.', ''), '0'), '.') . ')';
        }
        if (preg_match('/^[a-z]{3,24}$/', $v) === 1) {
            return $v; /* CSS keyword / named colour: letters only, cannot carry extra declarations */
        }
        return $fallback;
    }
}
