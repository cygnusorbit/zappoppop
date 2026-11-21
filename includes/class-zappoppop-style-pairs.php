<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Allow-list for the layer "CSS Styles Repeater" (property + value rows saved in a layer's inlineStyles).
 *
 * A row is kept only when the property is one the renderer already knows (ZapPopPop_Sanitizer::render_safe_css) and the value is
 * a single plain declaration: no ; { } < > or backslash, at most MAX_VALUE characters, only CSS functions from FUNCS, and url()
 * only with https, a site-relative path or an image data URI. Everything else is dropped when the popup is saved.
 */
class ZapPopPop_Style_Pairs {

    const MAX_VALUE = 200;

    const FUNCS = array(
        'rgb', 'rgba', 'hsl', 'hsla', 'calc', 'var', 'min', 'max', 'clamp', 'url',
        'linear-gradient', 'radial-gradient', 'conic-gradient', 'repeating-linear-gradient', 'repeating-radial-gradient',
        'translate', 'translatex', 'translatey', 'translatez', 'translate3d', 'rotate', 'rotatex', 'rotatey', 'rotatez', 'rotate3d',
        'scale', 'scalex', 'scaley', 'scalez', 'scale3d', 'skew', 'skewx', 'skewy', 'matrix', 'matrix3d', 'perspective',
        'blur', 'brightness', 'contrast', 'grayscale', 'hue-rotate', 'invert', 'opacity', 'saturate', 'sepia', 'drop-shadow',
        'cubic-bezier', 'steps',
    );

    /** @return array|null array('prop' => string, 'value' => string), or null when the row is not allowed. */
    public static function clean($prop, $value) {
        $prop  = strtolower(trim(sanitize_text_field((string) $prop)));
        $value = trim(sanitize_text_field((string) $value));
        if ($prop === '' || $value === '' || strlen($value) > self::MAX_VALUE) {
            return null;
        }
        if (!preg_match('/^-{0,1}[a-z][a-z-]*$/', $prop) || !in_array($prop, ZapPopPop_Sanitizer::render_safe_css(array()), true)) {
            return null;
        }
        // An image data URI legitimately contains ";base64,": it is checked below, so it is masked for the character test.
        $probe = preg_replace('#data:image/(?:png|jpe?g|gif|webp|avif);base64,[A-Za-z0-9+/=]+#i', 'data', $value);
        if (preg_match('/[;{}<>\\\\]/', $probe) || preg_match('/expression|javascript:|vbscript:|@import|behavior\s*:|-moz-binding|\/\*/i', $probe)) {
            return null;
        }
        if (preg_match_all('/([a-z0-9-]+)\s*\(/i', $value, $fn)) {
            foreach ($fn[1] as $name) {
                if (!in_array(strtolower($name), self::FUNCS, true)) {
                    return null;
                }
            }
        }
        if (preg_match_all('/url\(\s*([\'"]?)(.*?)\1\s*\)/i', $value, $u)) {
            foreach ($u[2] as $target) {
                $ok = preg_match('#^https://#i', $target)
                    || preg_match('#^/(?!/)#', $target)
                    || preg_match('#^data:image/(?:png|jpe?g|gif|webp|avif);base64,[A-Za-z0-9+/=]+$#i', $target);
                if (!$ok) {
                    return null;
                }
            }
        }
        return array('prop' => $prop, 'value' => $value);
    }
}
