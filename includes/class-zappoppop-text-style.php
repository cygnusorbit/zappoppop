<?php
/**
 * Typography extras for layers: line decoration, direction, font style, text stroke (ZPP_TYPOGRAPHY).
 * Structured values only (enums, clamped numbers, a sanitised colour), never free text, so nothing reaches CSS unchecked.
 *
 * @package ZapPopPop
 */

if (!defined('ABSPATH')) {
    exit;
}

class ZapPopPop_Text_Style {

    const DECORATIONS = array('none', 'underline', 'overline', 'line-through');
    const DIRECTIONS  = array('ltr', 'rtl');
    const FONT_STYLES = array('normal', 'italic');

    /**
     * Copy the typography extras from a raw layer into its clean copy (save path).
     *
     * @param array $el    Raw layer.
     * @param array $clean Clean layer so far.
     * @return array
     */
    public static function clean(array $el, array $clean): array {
        $enums = array(
            'lineDecoration' => self::DECORATIONS,
            'textDirection'  => self::DIRECTIONS,
            'fontStyle'      => self::FONT_STYLES,
        );
        foreach ($enums as $key => $allowed) {
            if (isset($el[$key]) && !is_array($el[$key])) {
                $val = sanitize_text_field(wp_unslash((string) $el[$key]));
                if (in_array($val, $allowed, true)) {
                    $clean[$key] = $val;
                }
            }
        }
        if (isset($el['textStrokeWidth']) && is_numeric($el['textStrokeWidth'])) {
            $clean['textStrokeWidth'] = max(0, min(20, (float) $el['textStrokeWidth']));
        }
        if (isset($el['textStrokeColor']) && !is_array($el['textStrokeColor'])) {
            $col = ZapPopPop_Color::sanitize((string) $el['textStrokeColor']);
            if ($col) {
                $clean['textStrokeColor'] = $col;
            }
        }
        return $clean;
    }

    /**
     * CSS declarations (no trailing semicolon) for the front end.
     *
     * @param array $el Layer.
     * @return string[]
     */
    public static function declarations(array $el): array {
        $out = array();
        if (isset($el['fontStyle']) && 'italic' === $el['fontStyle']) {
            $out[] = 'font-style:italic';
        }
        if (isset($el['lineDecoration']) && in_array($el['lineDecoration'], array('underline', 'overline', 'line-through'), true)) {
            $out[] = 'text-decoration:' . $el['lineDecoration'];
        }
        if (isset($el['textDirection']) && 'rtl' === $el['textDirection']) {
            $out[] = 'direction:rtl';
        }
        if (isset($el['textStrokeWidth']) && is_numeric($el['textStrokeWidth']) && (float) $el['textStrokeWidth'] > 0) {
            $col = isset($el['textStrokeColor']) && !is_array($el['textStrokeColor']) ? ZapPopPop_Color::sanitize((string) $el['textStrokeColor'], '#000000') : '#000000';
            $out[] = '-webkit-text-stroke:' . max(0, min(20, (float) $el['textStrokeWidth'])) . 'px ' . esc_attr($col ? $col : '#000000');
        }
        return $out;
    }
}
