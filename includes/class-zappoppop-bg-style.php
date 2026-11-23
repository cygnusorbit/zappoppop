<?php
/**
 * Layer background overlays (image / gradient / colour) and background clipping (full element / padding / content / text) (ZPP_BACKGROUND).
 * Structured values only: enums, clamped numbers, sanitised colours and raster image URLs. The CSS is rebuilt from those pieces,
 * a stored string is never printed as CSS.
 *
 * @package ZapPopPop
 */

if (!defined('ABSPATH')) {
    exit;
}

class ZapPopPop_Bg_Style {

    const MAX_OVERLAYS = 6;
    const TYPES        = array('color', 'image', 'gradient');
    const RESOLUTIONS  = array('thumbnail', 'medium', 'large', 'full');
    const POSITIONS    = array('center center', 'center left', 'center right', 'top left', 'top center', 'top right', 'bottom left', 'bottom center', 'bottom right');
    const REPEATS      = array('repeat', 'repeat-x', 'repeat-y', 'no-repeat');
    const SIZES        = array('auto', 'cover', 'contain', 'custom');
    const ATTACHMENTS  = array('scroll', 'fixed');

    /** Raster image URL on this site's media, http(s) or root-relative, no characters that could leave a CSS url(). */
    public static function clean_url($url): string {
        if (!is_scalar($url)) {
            return '';
        }
        $url = trim((string) $url);
        if ($url === '' || preg_match('/[\s"\'()\\\\&=<>`]/', $url)) {
            return '';
        }
        if (0 !== strpos($url, '/') || 0 === strpos($url, '//')) {
            $url = esc_url_raw($url, array('http', 'https'));
        }
        if ($url === '') {
            return '';
        }
        $path = (string) wp_parse_url($url, PHP_URL_PATH);
        return preg_match('/\.(?:jpe?g|png|gif|webp|avif|bmp)$/i', $path) ? $url : '';
    }

    private static function colour($v, string $fallback): string {
        $c = is_scalar($v) ? ZapPopPop_Color::sanitize((string) $v, '') : '';
        return $c ? (string) $c : $fallback;
    }

    private static function pick($v, array $allowed, string $fallback): string {
        $v = is_scalar($v) ? sanitize_text_field((string) $v) : '';
        return in_array($v, $allowed, true) ? $v : $fallback;
    }

    private static function num($v, int $lo, int $hi, int $fallback): int {
        return is_numeric($v) ? max($lo, min($hi, (int) round((float) $v))) : $fallback;
    }

    /** One overlay, cleaned. Returns null when it is not usable. */
    public static function clean_item($o): ?array {
        if (is_object($o)) {
            $o = get_object_vars($o);
        }
        if (!is_array($o)) {
            return null;
        }
        $type = self::pick(isset($o['type']) ? $o['type'] : '', self::TYPES, 'color');
        $out  = array('type' => $type);
        $out['color']      = self::colour(isset($o['color']) ? $o['color'] : '', '#00000033');
        $out['url']        = self::clean_url(isset($o['url']) ? $o['url'] : '');
        $out['imageId']    = self::num(isset($o['imageId']) ? $o['imageId'] : 0, 0, 999999999, 0);
        $out['res']        = self::pick(isset($o['res']) ? $o['res'] : '', self::RESOLUTIONS, 'full');
        $out['pos']        = self::pick(isset($o['pos']) ? $o['pos'] : '', self::POSITIONS, 'center center');
        $out['repeat']     = self::pick(isset($o['repeat']) ? $o['repeat'] : '', self::REPEATS, 'no-repeat');
        $out['size']       = self::pick(isset($o['size']) ? $o['size'] : '', self::SIZES, 'cover');
        $out['sizeCustom'] = self::num(isset($o['sizeCustom']) ? $o['sizeCustom'] : 100, 1, 300, 100);
        $out['attach']     = self::pick(isset($o['attach']) ? $o['attach'] : '', self::ATTACHMENTS, 'scroll');
        $out['c1']         = self::colour(isset($o['c1']) ? $o['c1'] : '', '#000000');
        $out['s1']         = self::num(isset($o['s1']) ? $o['s1'] : 0, 0, 100, 0);
        $out['c2']         = self::colour(isset($o['c2']) ? $o['c2'] : '', '#ffffff');
        $out['s2']         = self::num(isset($o['s2']) ? $o['s2'] : 100, 0, 100, 100);
        $out['gtype']      = self::pick(isset($o['gtype']) ? $o['gtype'] : '', array('linear', 'radial'), 'linear');
        $out['angle']      = self::num(isset($o['angle']) ? $o['angle'] : 180, 0, 360, 180);
        return $out;
    }

    public static function clean_list($list): array {
        if (is_object($list)) {
            $list = array_values(get_object_vars($list));
        }
        $out = array();
        if (!is_array($list)) {
            return $out;
        }
        foreach ($list as $o) {
            $c = self::clean_item($o);
            if ($c) {
                $out[] = $c;
            }
            if (count($out) >= self::MAX_OVERLAYS) {
                break;
            }
        }
        return $out;
    }

    /** Save path: bgOverlays + bgClip. */
    public static function clean(array $el, array $clean): array {
        if (isset($el['bgOverlays'])) {
            $clean['bgOverlays'] = self::clean_list($el['bgOverlays']);
        }
        if (isset($el['bgClip']) && !is_array($el['bgClip'])) {
            $clip = sanitize_text_field((string) $el['bgClip']);
            $clean['bgClip'] = in_array($clip, array('padding', 'content', 'text'), true) ? $clip : '';
        }
        return $clean;
    }

    private static function image_url(array $o): string {
        if (!empty($o['imageId'])) {
            $u = wp_get_attachment_image_url((int) $o['imageId'], $o['res']);
            $u = $u ? self::clean_url($u) : '';
            if ($u !== '') {
                return $u;
            }
        }
        return (string) $o['url'];
    }

    /**
     * CSS declarations (no trailing semicolon) for the front end, rebuilt from the cleaned overlays.
     *
     * @param array $el Layer.
     * @return string[]
     */
    public static function declarations(array $el): array {
        $out = array();
        $img = $pos = $rep = $size = $att = array();
        foreach (self::clean_list(isset($el['bgOverlays']) ? $el['bgOverlays'] : array()) as $o) {
            $layer = '';
            $p = 'center center'; $r = 'no-repeat'; $s = 'auto'; $a = 'scroll';
            if ('color' === $o['type']) {
                if ('transparent' !== $o['color']) {
                    $layer = 'linear-gradient(' . $o['color'] . ',' . $o['color'] . ')';
                }
            } elseif ('gradient' === $o['type']) {
                $stops = $o['c1'] . ' ' . $o['s1'] . '%,' . $o['c2'] . ' ' . $o['s2'] . '%';
                $layer = ('radial' === $o['gtype']) ? 'radial-gradient(circle,' . $stops . ')' : 'linear-gradient(' . $o['angle'] . 'deg,' . $stops . ')';
            } else {
                $u = self::image_url($o);
                if ($u !== '') {
                    $layer = 'url(' . $u . ')';
                    $p = $o['pos']; $r = $o['repeat']; $a = $o['attach'];
                    $s = ('custom' === $o['size']) ? $o['sizeCustom'] . '%' : $o['size'];
                }
            }
            if ($layer === '') {
                continue;
            }
            $img[] = $layer; $pos[] = $p; $rep[] = $r; $size[] = $s; $att[] = $a;
        }
        if ($img) {
            $out[] = 'background-image:' . implode(',', $img);
            $out[] = 'background-position:' . implode(',', $pos);
            $out[] = 'background-repeat:' . implode(',', $rep);
            $out[] = 'background-size:' . implode(',', $size);
            $out[] = 'background-attachment:' . implode(',', $att);
        }
        $clips = array('padding' => 'padding-box', 'content' => 'content-box', 'text' => 'text');
        if (isset($el['bgClip']) && !is_array($el['bgClip']) && isset($clips[$el['bgClip']])) {
            $out[] = '-webkit-background-clip:' . $clips[$el['bgClip']];
            $out[] = 'background-clip:' . $clips[$el['bgClip']];
            if ('text' === $el['bgClip']) {
                $out[] = '-webkit-text-fill-color:transparent';
            }
        }
        return $out;
    }
}
