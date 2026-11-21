<?php
/**
 * Font catalog: bundled (self-hosted) families plus the site's custom fonts.
 *
 * @package ZapPopPop
 */

if (!defined('ABSPATH')) {
    exit;
}

class ZapPopPop_Font_Catalog {

    /**
     * Bundled families: key (lowercase) => [label, generic fallback, weights].
     *
     * @return array
     */
    public static function bundled() {
        $fonts = array(
            'inter'            => array('Inter', 'sans-serif', '300;400;500;600;700;800;900'),
            'roboto'           => array('Roboto', 'sans-serif', '300;400;500;700;900'),
            'montserrat'       => array('Montserrat', 'sans-serif', '300;400;500;600;700;800;900'),
            'poppins'          => array('Poppins', 'sans-serif', '300;400;500;600;700;800;900'),
            'open sans'        => array('Open Sans', 'sans-serif', '300;400;500;600;700;800'),
            'lato'             => array('Lato', 'sans-serif', '300;400;700;900'),
            'nunito'           => array('Nunito', 'sans-serif', '300;400;500;600;700;800;900'),
            'raleway'          => array('Raleway', 'sans-serif', '300;400;500;600;700;800;900'),
            'dm sans'          => array('DM Sans', 'sans-serif', '300;400;500;600;700;800;900'),
            'work sans'        => array('Work Sans', 'sans-serif', '300;400;500;600;700;800;900'),
            'source sans 3'    => array('Source Sans 3', 'sans-serif', '300;400;500;600;700;800;900'),
            'oswald'           => array('Oswald', 'sans-serif', '300;400;500;600;700'),
            'playfair display' => array('Playfair Display', 'serif', '400;500;600;700;800;900'),
            'merriweather'     => array('Merriweather', 'serif', '300;400;700;900'),
        );
        return apply_filters('zappoppop_bundled_fonts', $fonts);
    }

    /**
     * Custom fonts as value => label.
     *
     * @return array
     */
    public static function custom() {
        $out = array();
        if (class_exists('ZapPopPop_Custom_Fonts')) {
            foreach (ZapPopPop_Custom_Fonts::families() as $name) {
                $name = (string) $name;
                if ($name === '') {
                    continue;
                }
                $out["'" . $name . "', sans-serif"] = $name;
            }
        }
        return $out;
    }

    /**
     * Dropdown groups: system, bundled, custom. Each is value => label.
     *
     * @return array
     */
    public static function groups() {
        $system = array(
            'inherit'                                           => __('Inherit (theme font)', 'zappoppop'),
            'system-ui, sans-serif'                             => __('System UI', 'zappoppop'),
            'Georgia, serif'                                    => 'Georgia',
            'Arial, Helvetica, sans-serif'                      => 'Arial',
            "'Courier New', monospace"                          => 'Courier New',
        );
        $bundled = array();
        foreach (self::bundled() as $f) {
            $bundled[$f[0] . ', ' . $f[1]] = $f[0];
        }
        return array(
            'system'  => $system,
            'bundled' => $bundled,
            'custom'  => self::custom(),
        );
    }

    /**
     * Render the <option>/<optgroup> markup for the layer font select.
     *
     * @return string
     */
    public static function options_html() {
        $groups = self::groups();
        $titles = array(
            'system'  => __('System', 'zappoppop'),
            'bundled' => __('Bundled fonts', 'zappoppop'),
            'custom'  => __('Custom fonts', 'zappoppop'),
        );
        $html = '';
        foreach ($groups as $key => $items) {
            if (empty($items)) {
                continue;
            }
            $html .= '<optgroup label="' . esc_attr($titles[$key]) . '">';
            foreach ($items as $value => $label) {
                $weights = '';
                if ($key === 'bundled') {
                    foreach (self::bundled() as $f) {
                        if ($f[0] . ', ' . $f[1] === $value) {
                            $weights = ' data-weights="' . esc_attr(str_replace(';', ',', $f[2])) . '"';
                        }
                    }
                }
                $html .= '<option value="' . esc_attr($value) . '"' . $weights . '>' . esc_html($label) . '</option>';
            }
            $html .= '</optgroup>';
        }
        return $html;
    }

    /**
     * True when the value is 'inherit' or one of the catalog's dropdown values.
     *
     * @param string $value Font-family value.
     * @return bool
     */
    public static function is_valid($value) {
        if ($value === 'inherit') {
            return true;
        }
        foreach (self::groups() as $items) {
            if (isset($items[$value])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Google-style weight map used by the stylesheet loader: key => "Family:wght@..".
     *
     * @return array
     */
    public static function weight_map() {
        $map = array();
        foreach (self::bundled() as $key => $f) {
            $map[$key] = str_replace(' ', '+', $f[0]) . ':wght@' . $f[2];
        }
        return $map;
    }
}
