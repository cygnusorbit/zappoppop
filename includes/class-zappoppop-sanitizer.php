<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Shared payload sanitizers. Used by builder save and by backup restore,
 * so both paths apply the exact same animation whitelist.
 */
class ZapPopPop_Sanitizer {
    public static function anim_payload($node) {
        if (is_object($node)) {
            foreach (get_object_vars($node) as $k => $v) {
                $node->$k = self::anim_value($k, $v);
            }
            return $node;
        }
        if (is_array($node)) {
            foreach ($node as $k => $v) {
                $node[$k] = self::anim_value($k, $v);
            }
        }
        return $node;
    }

    protected static function anim_value($k, $v) {
        static $txt = ['anim_appearance', 'anim_disappearance', 'anim_effect', 'animEffect', 'animation', 'animIn', 'animOut'];
        static $int = ['anim_duration', 'anim_delay', 'animDuration', 'animDelay', 'canvas_radius', 'autoclose', 'autoclose_delay'];
        static $bool = ['close_btn_enabled', 'anim_exit_enabled', 'allow_overflow_edit']; /* ZAPPOPPOP_DEFAULT_CLOSE */
        if (is_scalar($v) || $v === null) {
            if (in_array($k, $bool, true)) {
                return in_array($v, [true, 1, '1', 'true', 'on', 'yes'], true);
            }
            if (in_array($k, $txt, true)) {
                return preg_replace('/[^A-Za-z0-9_\-]/', '', (string) $v);
            }
            if (in_array($k, $int, true)) {
                return max(0, intval($v));
            }
            return $v;
        }
        return self::anim_payload($v);
    }

    /* ------------------------------------------------------------------
     * Request input: read, validate and deep-sanitise posted JSON and uploads
     * ------------------------------------------------------------------ */

    const MAX_JSON_BYTES = 20971520;
    const MAX_DEPTH      = 64;
    const CSS_KEY        = '/(?:colou?r|background|(?:^|_)bg|border|shadow|gradient|font|margin|padding|width|height|size|radius|transform|filter|style|css|transition|cursor|outline|opacity|spacing|align|weight|decoration|display|position|overflow|index)/i';

    /** Raw string of one request field (unslashed). Sanitised by the callers below, never used as is. */
    protected static function request_text($key, $source) {
        $key   = (string) $key;
        $value = null;
        if ($source === 'REQUEST') {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- callers verify the nonce; the text is validated and deep-sanitised below
            $value = isset($_REQUEST[$key]) ? wp_unslash($_REQUEST[$key]) : null;
        } else {
            // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- callers verify the nonce; the text is validated and deep-sanitised below
            $value = isset($_POST[$key]) ? wp_unslash($_POST[$key]) : null;
        }
        return is_string($value) ? $value : '';
    }

    /**
     * Text of a posted JSON field: valid UTF-8, no control characters, size-capped and syntactically valid JSON.
     * Returns '' when the field is missing or invalid. Use request_json() when the decoded value is needed.
     */
    public static function post_json_string($key, $source = 'POST') {
        $text = self::request_text($key, $source);
        if ($text === '' || strlen($text) > self::MAX_JSON_BYTES) {
            return '';
        }
        $text = wp_check_invalid_utf8($text);
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $text);
        if (!is_string($text) || $text === '') {
            return '';
        }
        json_decode($text, true, self::MAX_DEPTH);
        return (json_last_error() === JSON_ERROR_NONE) ? $text : '';
    }

    /** Posted JSON field, decoded and deep-sanitised (null when missing or invalid). */
    public static function request_json($key, $source = 'POST') {
        $text = self::post_json_string($key, $source);
        if ($text === '') {
            return null;
        }
        $decoded = json_decode($text, true, self::MAX_DEPTH);
        return ($decoded === null) ? null : self::deep($decoded);
    }

    /** One uploaded file with every entry sanitised, or null. The temp path is still checked by the caller with is_uploaded_file(). */
    public static function uploaded_file($key) {
        $key = (string) $key;
        if (empty($_FILES[$key]) || !is_array($_FILES[$key])) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- callers verify the nonce
            return null;
        }
        $f = $_FILES[$key]; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- every entry is sanitised below
        if (!isset($f['error'], $f['tmp_name'], $f['name']) || is_array($f['error']) || !is_string($f['tmp_name']) || !is_string($f['name'])) {
            return null;
        }
        return array(
            'name'     => sanitize_file_name(wp_unslash($f['name'])),
            'tmp_name' => sanitize_text_field($f['tmp_name']),
            'error'    => (int) $f['error'],
            'size'     => isset($f['size']) ? absint($f['size']) : 0,
            'type'     => (isset($f['type']) && is_string($f['type'])) ? sanitize_mime_type($f['type']) : '',
        );
    }

    /**
     * Posted array field, unslashed and reduced to plain text at the point of read (tags stripped, control characters
     * removed, keys cleaned). For settings-style payloads whose fields are re-sanitised one by one by their owner.
     * Popup definitions (markup allowed) must keep using request_json() / popup_payload().
     * $wrap_scalar: a single posted value becomes a one-item list (same as the old (array) cast).
     */
    public static function post_array($key, $source = 'POST', $wrap_scalar = false) {
        $key = (string) $key;
        if ($source === 'REQUEST') {
            $value = isset($_REQUEST[$key]) ? map_deep(wp_unslash($_REQUEST[$key]), 'sanitize_textarea_field') : null; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- callers verify the nonce
        } else {
            $value = isset($_POST[$key]) ? map_deep(wp_unslash($_POST[$key]), 'sanitize_textarea_field') : null; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- callers verify the nonce
        }
        if (is_array($value)) {
            return self::clean_keys_deep($value, 0);
        }
        return ($wrap_scalar && is_string($value) && $value !== '') ? array($value) : array();
    }

    /** The whole $_POST, unslashed and reduced to plain text (flattened-form payloads). Callers verify the nonce. */
    public static function post_all() {
        $value = map_deep(wp_unslash($_POST), 'sanitize_textarea_field'); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- callers verify the nonce
        return is_array($value) ? self::clean_keys_deep($value, 0) : array();
    }

    protected static function clean_keys_deep($node, $depth) {
        if ($depth > self::MAX_DEPTH) {
            return array();
        }
        $out = array();
        foreach ($node as $k => $v) {
            if (!is_int($k)) {
                $k = self::clean_key($k);
                if ($k === '') {
                    continue;
                }
            }
            $out[$k] = is_array($v) ? self::clean_keys_deep($v, $depth + 1) : $v;
        }
        return $out;
    }

    /**
     * Allowlist for popup markup produced by ZapPopPop_Front_Renderer (block / shortcode output).
     * Built from what the renderer really emits: layout tags, form controls, iframes, SVG shapes, canvas, media,
     * data-* and the aria-* attributes it uses. Scripts and event handlers are never allowed.
     * Extend with the 'zappoppop_block_kses_allowed_html' filter (add-ons that render their own element markup).
     */
    public static function render_allowed_html() {
        static $allowed = null;
        if ($allowed !== null) {
            return $allowed;
        }
        $allowed = self::allowed_html();
        $aria    = array_fill_keys(array('aria-hidden', 'aria-label', 'aria-live', 'aria-pressed', 'aria-expanded', 'aria-controls',
            'aria-describedby', 'aria-labelledby', 'aria-modal', 'aria-checked', 'aria-selected', 'aria-disabled', 'aria-required',
            'aria-invalid', 'aria-busy', 'aria-atomic', 'aria-current', 'aria-haspopup', 'aria-valuenow', 'aria-valuemin',
            'aria-valuemax', 'aria-valuetext', 'aria-errormessage', 'aria-relevant', 'aria-roledescription'), true);
        $global  = array_merge($aria, array('class' => true, 'id' => true, 'style' => true, 'title' => true, 'role' => true,
            'tabindex' => true, 'lang' => true, 'dir' => true, 'hidden' => true, 'draggable' => true, 'spellcheck' => true, 'data-*' => true));
        $svg_attrs = array_fill_keys(array('transform', 'fill', 'fill-opacity', 'fill-rule', 'clip-rule', 'clip-path', 'stroke', 'stroke-width',
            'stroke-opacity', 'stroke-linecap', 'stroke-linejoin', 'stroke-miterlimit', 'stroke-dasharray', 'stroke-dashoffset',
            'vector-effect', 'opacity', 'mask', 'filter', 'focusable'), true);
        $svg = array_merge($global, $svg_attrs);
        $extra = array(
            'form'           => array('action' => true, 'method' => true, 'name' => true, 'novalidate' => true, 'autocomplete' => true, 'enctype' => true, 'target' => true),
            'label'          => array('for' => true),
            'input'          => array('accept' => true, 'multiple' => true, 'minlength' => true, 'autocapitalize' => true, 'inputmode' => true, 'size' => true, 'list' => true, 'form' => true),
            'textarea'       => array('minlength' => true, 'autocapitalize' => true, 'inputmode' => true, 'wrap' => true),
            'select'         => array('size' => true, 'autocomplete' => true),
            'iframe'         => array('sandbox' => true, 'srcdoc' => false, 'allow' => true, 'allowfullscreen' => true, 'loading' => true, 'referrerpolicy' => true, 'scrolling' => true),
            'canvas'         => array('width' => true, 'height' => true),
            'video'          => array('src' => true, 'poster' => true, 'controls' => true, 'autoplay' => true, 'loop' => true, 'muted' => true, 'playsinline' => true, 'preload' => true, 'width' => true, 'height' => true, 'crossorigin' => true),
            'audio'          => array('src' => true, 'controls' => true, 'autoplay' => true, 'loop' => true, 'muted' => true, 'preload' => true),
            'source'         => array('src' => true, 'type' => true, 'srcset' => true, 'media' => true, 'sizes' => true),
            'svg'            => array_merge($svg, array('xmlns' => true, 'viewbox' => true, 'preserveaspectratio' => true, 'width' => true, 'height' => true, 'x' => true, 'y' => true, 'version' => true)),
            'g'              => $svg,
            'path'           => array_merge($svg, array('d' => true, 'pathlength' => true)),
            'circle'         => array_merge($svg, array('cx' => true, 'cy' => true, 'r' => true)),
            'ellipse'        => array_merge($svg, array('cx' => true, 'cy' => true, 'rx' => true, 'ry' => true)),
            'rect'           => array_merge($svg, array('x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true, 'ry' => true)),
            'line'           => array_merge($svg, array('x1' => true, 'y1' => true, 'x2' => true, 'y2' => true)),
            'polyline'       => array_merge($svg, array('points' => true)),
            'polygon'        => array_merge($svg, array('points' => true)),
            'defs'           => $svg,
            'lineargradient' => array_merge($svg, array('x1' => true, 'y1' => true, 'x2' => true, 'y2' => true, 'gradientunits' => true, 'gradienttransform' => true, 'spreadmethod' => true)),
            'radialgradient' => array_merge($svg, array('cx' => true, 'cy' => true, 'r' => true, 'fx' => true, 'fy' => true, 'gradientunits' => true, 'gradienttransform' => true, 'spreadmethod' => true)),
            'stop'           => array_merge($svg, array('offset' => true, 'stop-color' => true, 'stop-opacity' => true)),
            'clippath'       => array_merge($svg, array('clippathunits' => true)),
            'text'           => array_merge($svg, array('x' => true, 'y' => true, 'dx' => true, 'dy' => true, 'text-anchor' => true, 'font-size' => true, 'font-family' => true, 'font-weight' => true)),
            'tspan'          => array_merge($svg, array('x' => true, 'y' => true, 'dx' => true, 'dy' => true)),
        );
        foreach ($extra as $tag => $attrs) {
            $attrs         = array_merge($global, $attrs);
            $allowed[$tag] = isset($allowed[$tag]) ? array_merge($allowed[$tag], $attrs) : $attrs;
        }
        // Every allowed tag may carry the global attributes (layout wrappers add data-* / aria-* / role freely).
        foreach ($allowed as $tag => $attrs) {
            $allowed[$tag] = array_merge(is_array($attrs) ? $attrs : array(), $global);
        }
        $allowed = (array) apply_filters('zappoppop_block_kses_allowed_html', $allowed);
        return $allowed;
    }

    /** Inline-style properties the renderer writes that wp_kses() would otherwise drop. */
    public static function render_safe_css($props = array()) {
        $more = array(
            'position', 'top', 'right', 'bottom', 'left', 'inset', 'z-index', 'display', 'flex', 'flex-direction', 'flex-wrap', 'flex-grow',
            'flex-shrink', 'flex-basis', 'align-items', 'align-content', 'align-self', 'justify-content', 'justify-items', 'justify-self',
            'order', 'gap', 'row-gap', 'column-gap', 'grid', 'grid-template-columns', 'grid-template-rows', 'grid-column', 'grid-row',
            'place-items', 'place-content', 'width', 'height', 'min-width', 'max-width', 'min-height', 'max-height', 'aspect-ratio',
            'box-sizing', 'margin', 'margin-top', 'margin-right', 'margin-bottom', 'margin-left', 'padding', 'padding-top', 'padding-right',
            'padding-bottom', 'padding-left', 'overflow', 'overflow-x', 'overflow-y', 'opacity', 'visibility', 'transform', 'transform-origin',
            'transform-style', 'perspective', 'transition', 'transition-property', 'transition-duration', 'transition-timing-function',
            'transition-delay', 'animation', 'animation-name', 'animation-duration', 'animation-delay', 'animation-timing-function',
            'animation-iteration-count', 'animation-direction', 'animation-fill-mode', 'animation-play-state', 'filter', 'backdrop-filter',
            'mix-blend-mode', 'clip-path', 'object-fit', 'object-position', 'pointer-events', 'cursor', 'user-select', 'resize', 'outline',
            'outline-color', 'outline-style', 'outline-width', 'outline-offset', 'box-shadow', 'text-shadow', 'border-radius',
            'border-top-left-radius', 'border-top-right-radius', 'border-bottom-left-radius', 'border-bottom-right-radius', 'border',
            'border-top', 'border-right', 'border-bottom', 'border-left', 'border-color', 'border-style', 'border-width', 'border-collapse',
            'background', 'background-color', 'background-image', 'background-position', 'background-size', 'background-repeat',
            'background-attachment', 'background-clip', 'background-origin', 'color', 'font', 'font-family', 'font-size', 'font-style',
            'font-weight', 'font-variant', 'line-height', 'letter-spacing', 'word-spacing', 'word-break', 'overflow-wrap', 'white-space',
            'text-align', 'text-decoration', 'text-transform', 'text-indent', 'text-overflow', 'vertical-align', 'list-style', 'content',
            'fill', 'stroke', 'stroke-width', 'vector-effect', '-webkit-text-stroke', '-webkit-text-fill-color', '-webkit-background-clip',
            '-webkit-box-shadow', '-webkit-transform', '-webkit-transition', '-webkit-appearance', 'appearance', '-webkit-backdrop-filter',
            '-webkit-mask', 'mask', 'mask-image', 'mask-size', 'scroll-behavior', 'touch-action', 'will-change',
        );
        $more = (array) apply_filters('zappoppop_block_kses_safe_css', $more);
        return array_values(array_unique(array_merge((array) $props, $more)));
    }

    /**
     * Output-side kses for popup markup (block render callback). Escaping already happens where the renderer builds
     * each value; this is the second layer. Return false from 'zappoppop_block_output_kses' to bypass it for a
     * site where an exotic custom element is being stripped (support escape hatch).
     */
    public static function kses_render($html) {
        $html = (string) $html;
        if ($html === '') {
            return '';
        }
        if (!apply_filters('zappoppop_block_output_kses', true)) {
            return $html;
        }
        $css = array(__CLASS__, 'render_safe_css');
        add_filter('safe_style_css', $css);
        $out = wp_kses($html, self::render_allowed_html(), array_merge(wp_allowed_protocols(), array('data')));
        remove_filter('safe_style_css', $css);
        return $out;
    }

    /** Popup / template / backup payload (array or object): keys cleaned, every string value sanitised by its content and key. */
    public static function popup_payload($node) {
        return self::deep($node);
    }

    /** Settings subtrees owned by extension modules; they sanitise their own markup (see ZapPopPop_Module_Bridge). */
    protected static function module_keys() {
        $keys = apply_filters('zappoppop_sanitizer_module_keys', array('overlay', 'custom_code'));
        return is_array($keys) ? array_filter($keys, 'is_string') : array('overlay', 'custom_code');
    }

    public static function deep($node, $key = '', $depth = 0, $raw = false) {
        if ($depth > self::MAX_DEPTH) {
            return null;
        }
        if (!$raw && $key !== '' && $depth > 0 && (is_array($node) || is_object($node)) && in_array($key, self::module_keys(), true)) {
            $raw = true; // keep markup intact; keys are still cleaned below
        }
        if (is_object($node)) {
            $out = new stdClass();
            foreach (get_object_vars($node) as $k => $v) {
                $ck = self::clean_key($k);
                if ($ck === '' || self::blocked_key($ck)) {
                    continue;
                }
                $out->$ck = self::deep($v, $ck, $depth + 1, $raw);
            }
            return $out;
        }
        if (is_array($node)) {
            $out = array();
            foreach ($node as $k => $v) {
                if (is_int($k)) {
                    $out[$k] = self::deep($v, $key, $depth + 1, $raw);
                    continue;
                }
                $ck = self::clean_key($k);
                if ($ck === '' || self::blocked_key($ck)) {
                    continue;
                }
                $out[$ck] = self::deep($v, $ck, $depth + 1, $raw);
            }
            return $out;
        }
        if (is_string($node)) {
            if ($raw) {
                $node = str_replace("\0", '', $node);
                return preg_match('/^\s*(?:javascript|vbscript|livescript)\s*:/i', $node) ? '' : $node;
            }
            return self::clean_string($node, (string) $key);
        }
        return $node; // bool, int, float, null
    }

    protected static function clean_key($k) {
        $k = preg_replace('/[^A-Za-z0-9_\-\.:@\[\]]/', '', (string) $k);
        return is_string($k) ? $k : '';
    }

    /** Free-form script fields are not part of a popup definition. */
    protected static function blocked_key($k) {
        return in_array($k, array('customJs', 'custom_js'), true);
    }

    protected static function clean_string($s, $key) {
        $s = str_replace("\0", '', $s);
        if ($s === '') {
            return '';
        }
        // Script and non-image data URLs are never valid values.
        if (preg_match('/^\s*(?:j\s*a\s*v\s*a\s*s\s*c\s*r\s*i\s*p\s*t|v\s*b\s*s\s*c\s*r\s*i\s*p\s*t|l\s*i\s*v\s*e\s*s\s*c\s*r\s*i\s*p\s*t)\s*:/i', $s)) {
            return '';
        }
        if (preg_match('/^\s*data\s*:\s*[a-z0-9.+\-]+\/[a-z0-9.+\-]+\s*[;,]/i', $s) && !preg_match('/^\s*data\s*:\s*image\/(?:png|jpe?g|gif|webp|avif|bmp)\s*[;,]/i', $s)) {
            return '';
        }
        if ($key !== '' && preg_match(self::CSS_KEY, $key)) {
            $s = self::scrub_css($s);
        }
        if (strpos($s, '<') !== false) {
            $s = wp_kses($s, self::allowed_html());
        }
        return $s;
    }

    protected static function scrub_css($s) {
        $s = wp_strip_all_tags($s);
        $s = preg_replace('/(?:expression\s*\(|javascript\s*:|vbscript\s*:|-moz-binding|behavior\s*:|@import)/i', '', $s);
        return is_string($s) ? $s : '';
    }

    /** Post-content tags plus the form and embed tags the HTML layer is used for. Scripts and event handlers are always removed. */
    protected static function allowed_html() {
        static $allowed = null;
        if ($allowed === null) {
            $allowed = wp_kses_allowed_html('post');
            $base    = array('class' => true, 'id' => true, 'style' => true, 'title' => true);
            $extra   = array(
                'iframe'   => array('src' => true, 'width' => true, 'height' => true, 'frameborder' => true, 'allow' => true, 'allowfullscreen' => true, 'loading' => true, 'name' => true, 'referrerpolicy' => true),
                'input'    => array('type' => true, 'name' => true, 'value' => true, 'placeholder' => true, 'required' => true, 'checked' => true, 'disabled' => true, 'readonly' => true, 'min' => true, 'max' => true, 'step' => true, 'maxlength' => true, 'pattern' => true, 'autocomplete' => true),
                'button'   => array('type' => true, 'name' => true, 'value' => true, 'disabled' => true),
                'select'   => array('name' => true, 'required' => true, 'disabled' => true, 'multiple' => true),
                'option'   => array('value' => true, 'selected' => true),
                'textarea' => array('name' => true, 'rows' => true, 'cols' => true, 'placeholder' => true, 'required' => true, 'disabled' => true, 'readonly' => true, 'maxlength' => true),
            );
            foreach ($extra as $tag => $attrs) {
                $attrs = array_merge($base, $attrs);
                $allowed[$tag] = isset($allowed[$tag]) ? array_merge($allowed[$tag], $attrs) : $attrs;
            }
            $allowed = (array) apply_filters('zappoppop_kses_allowed_html', $allowed);
        }
        return $allowed;
    }
}
