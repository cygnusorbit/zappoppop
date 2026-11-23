<?php
/**
 * Layer effects: blend mode, transform list and filter list (ZPP_EFFECTS).
 * Structured values only: enums and clamped numbers (plus a sanitised colour for drop-shadow). The CSS is rebuilt from those pieces,
 * a stored string is never printed as CSS.
 *
 * Model: blendMode (enum), fxTransforms [ {type, x, y, z, off} ], fxFilters [ {type, v, x, y, b, c, off} ], fxOriginX / fxOriginY (%), fxOriginZ (px).
 *
 * @package ZapPopPop
 */

if (!defined('ABSPATH')) {
    exit;
}

class ZapPopPop_Effects_Style {

    const MAX_ITEMS = 8;
    const BLEND     = array('normal', 'multiply', 'screen', 'overlay', 'darken', 'lighten', 'color-dodge', 'color-burn', 'hard-light', 'soft-light', 'difference', 'exclusion', 'hue', 'saturation', 'color', 'luminosity');

    /** type => list of [field, min, max, default] */
    const TRANSFORMS = array(
        'move'        => array(array('x', -2000, 2000, 0), array('y', -2000, 2000, 0), array('z', -1000, 1000, 0)),
        'scale'       => array(array('x', 0, 10, 1), array('y', 0, 10, 1), array('z', 0, 10, 1)),
        'rotate'      => array(array('x', -360, 360, 0), array('y', -360, 360, 0), array('z', -360, 360, 0)),
        'skew'        => array(array('x', -89, 89, 0), array('y', -89, 89, 0)),
        'perspective' => array(array('x', 1, 5000, 1000)),
    );

    /** type => [min, max, default, unit] ('drop-shadow' is handled separately) */
    const FILTERS = array(
        'blur'        => array(0, 100, 0, 'px'),
        'brightness'  => array(0, 300, 100, '%'),
        'contrast'    => array(0, 300, 100, '%'),
        'saturate'    => array(0, 300, 100, '%'),
        'hue-rotate'  => array(0, 360, 0, 'deg'),
        'grayscale'   => array(0, 100, 0, '%'),
        'invert'      => array(0, 100, 0, '%'),
        'opacity'     => array(0, 100, 100, '%'),
        'sepia'       => array(0, 100, 0, '%'),
        'drop-shadow' => array(0, 0, 0, 'px'),
    );

    private static function num($v, $lo, $hi, $d) {
        if (is_array($v) || is_object($v) || !is_numeric($v)) {
            return (float) $d;
        }
        return round(max((float) $lo, min((float) $hi, (float) $v)), 2);
    }

    private static function fmt($n) {
        $s = number_format((float) $n, 2, '.', '');
        return rtrim(rtrim($s, '0'), '.') === '' ? '0' : rtrim(rtrim($s, '0'), '.');
    }

    private static function as_array($o) {
        return is_object($o) ? get_object_vars($o) : (is_array($o) ? $o : null);
    }

    public static function clean_transform($o) {
        $o = self::as_array($o);
        if (!$o) {
            return null;
        }
        $type = isset($o['type']) && is_scalar($o['type']) && isset(self::TRANSFORMS[(string) $o['type']]) ? (string) $o['type'] : 'move';
        $out  = array('type' => $type);
        foreach (self::TRANSFORMS[$type] as $f) {
            $out[$f[0]] = self::num(isset($o[$f[0]]) ? $o[$f[0]] : $f[3], $f[1], $f[2], $f[3]);
        }
        $out['off'] = !empty($o['off']) && 'false' !== $o['off'];
        return $out;
    }

    public static function clean_filter($o) {
        $o = self::as_array($o);
        if (!$o) {
            return null;
        }
        $type = isset($o['type']) && is_scalar($o['type']) && isset(self::FILTERS[(string) $o['type']]) ? (string) $o['type'] : 'blur';
        $out  = array('type' => $type);
        if ('drop-shadow' === $type) {
            $out['x'] = self::num(isset($o['x']) ? $o['x'] : 0, -100, 100, 0);
            $out['y'] = self::num(isset($o['y']) ? $o['y'] : 0, -100, 100, 0);
            $out['b'] = self::num(isset($o['b']) ? $o['b'] : 0, 0, 100, 0);
            $c        = isset($o['c']) && is_scalar($o['c']) ? ZapPopPop_Color::sanitize((string) $o['c'], '') : '';
            $out['c'] = $c ? (string) $c : '#00000080';
        } else {
            $f        = self::FILTERS[$type];
            $out['v'] = self::num(isset($o['v']) ? $o['v'] : $f[2], $f[0], $f[1], $f[2]);
        }
        $out['off'] = !empty($o['off']) && 'false' !== $o['off'];
        return $out;
    }

    private static function clean_list($list, $cb) {
        if (is_object($list)) {
            $list = array_values(get_object_vars($list));
        }
        $out = array();
        if (!is_array($list)) {
            return $out;
        }
        foreach ($list as $o) {
            $c = call_user_func($cb, $o);
            if ($c) {
                $out[] = $c;
            }
            if (count($out) >= self::MAX_ITEMS) {
                break;
            }
        }
        return $out;
    }

    public static function clean_transforms($list) {
        return self::clean_list($list, array(__CLASS__, 'clean_transform'));
    }

    public static function clean_filters($list) {
        return self::clean_list($list, array(__CLASS__, 'clean_filter'));
    }

    /** Save path. */
    public static function clean(array $el, array $clean) {
        if (isset($el['blendMode']) && !is_array($el['blendMode'])) {
            $b = sanitize_text_field((string) $el['blendMode']);
            $clean['blendMode'] = in_array($b, self::BLEND, true) ? $b : 'normal';
        }
        if (isset($el['fxTransforms'])) {
            $clean['fxTransforms'] = self::clean_transforms($el['fxTransforms']);
        }
        if (isset($el['fxFilters'])) {
            $clean['fxFilters'] = self::clean_filters($el['fxFilters']);
        }
        foreach (array('fxOriginX' => array(0, 100, 50), 'fxOriginY' => array(0, 100, 50), 'fxOriginZ' => array(-500, 500, 0)) as $k => $r) {
            if (isset($el[$k]) && '' !== $el[$k] && !is_array($el[$k])) {
                $clean[$k] = self::num($el[$k], $r[0], $r[1], $r[2]);
            }
        }
        return $clean;
    }

    /** CSS value of the visible transform items (empty when none). */
    public static function transform_value(array $el) {
        $parts = array();
        foreach (self::clean_transforms(isset($el['fxTransforms']) ? $el['fxTransforms'] : array()) as $t) {
            if ($t['off']) {
                continue;
            }
            switch ($t['type']) {
                case 'move':
                    $parts[] = 'translate3d(' . self::fmt($t['x']) . 'px,' . self::fmt($t['y']) . 'px,' . self::fmt($t['z']) . 'px)';
                    break;
                case 'scale':
                    $parts[] = 'scale3d(' . self::fmt($t['x']) . ',' . self::fmt($t['y']) . ',' . self::fmt($t['z']) . ')';
                    break;
                case 'rotate':
                    $parts[] = 'rotateX(' . self::fmt($t['x']) . 'deg) rotateY(' . self::fmt($t['y']) . 'deg) rotateZ(' . self::fmt($t['z']) . 'deg)';
                    break;
                case 'skew':
                    $parts[] = 'skew(' . self::fmt($t['x']) . 'deg,' . self::fmt($t['y']) . 'deg)';
                    break;
                case 'perspective':
                    $parts[] = 'perspective(' . self::fmt($t['x']) . 'px)';
                    break;
            }
        }
        return implode(' ', $parts);
    }

    public static function filter_value(array $el) {
        $parts = array();
        foreach (self::clean_filters(isset($el['fxFilters']) ? $el['fxFilters'] : array()) as $f) {
            if ($f['off']) {
                continue;
            }
            if ('drop-shadow' === $f['type']) {
                $parts[] = 'drop-shadow(' . self::fmt($f['x']) . 'px ' . self::fmt($f['y']) . 'px ' . self::fmt($f['b']) . 'px ' . $f['c'] . ')';
            } else {
                $parts[] = $f['type'] . '(' . self::fmt($f['v']) . self::FILTERS[$f['type']][3] . ')';
            }
        }
        return implode(' ', $parts);
    }

    /**
     * CSS declarations (no trailing semicolon) for the front end.
     *
     * @param array $el  Layer.
     * @param int   $rot Existing layer rotation in degrees (kept in front of the transform list, because one `transform` property is used).
     * @return string[]
     */
    public static function declarations(array $el, $rot = 0) {
        $out = array();
        $t   = self::transform_value($el);
        if ('' !== $t) {
            $out[] = 'transform:' . ((int) $rot !== 0 ? 'rotate(' . (int) $rot . 'deg) ' : '') . $t;
            $ox = self::num(isset($el['fxOriginX']) ? $el['fxOriginX'] : 50, 0, 100, 50);
            $oy = self::num(isset($el['fxOriginY']) ? $el['fxOriginY'] : 50, 0, 100, 50);
            $oz = self::num(isset($el['fxOriginZ']) ? $el['fxOriginZ'] : 0, -500, 500, 0);
            if (50.0 !== $ox || 50.0 !== $oy || 0.0 !== $oz) {
                $out[] = 'transform-origin:' . self::fmt($ox) . '% ' . self::fmt($oy) . '% ' . self::fmt($oz) . 'px';
            }
        }
        $f = self::filter_value($el);
        if ('' !== $f) {
            $out[] = 'filter:' . $f;
        }
        if (isset($el['blendMode']) && !is_array($el['blendMode']) && 'normal' !== $el['blendMode'] && in_array((string) $el['blendMode'], self::BLEND, true)) {
            $out[] = 'mix-blend-mode:' . $el['blendMode'];
        }
        return $out;
    }
}
