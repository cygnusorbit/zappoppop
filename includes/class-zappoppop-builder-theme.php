<?php
/**
 * ZapPopPop Builder Theme Controller (Phase 7H)
 * - Compact Side-by-Side Layout (Settings Left | Dynamic Live Preview Right)
 * - Compact 2-Row Item Layout (Title + Swatch on Row 1 | Bg + Txt + Bdr + Opacity on Row 2)
 * - Dedicated Text Colors & Border Colors for Header/Toolbar, Content/Sidebar,
 *   Accordion Tab, Form Field/Button, and Primary Save Button
 * - Removed "Solid" dotted placeholder box
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'ZapPopPop_Builder_Theme' ) ) {

    class ZapPopPop_Builder_Theme {

        const OPTION_KEY = 'zappoppop_builder_theme';
        const DAY_OPTION = 'zappoppop_builder_day_preset'; /* ZPP_DAY_PRESET: your own saved Day Mode */

        public static function init() {
            add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ), 999 );
            add_action( 'admin_enqueue_scripts', array( __CLASS__, 'output_dynamic_css_variables' ), 1000 );
            add_action( 'admin_footer', array( __CLASS__, 'output_late_cascade_override' ), 999999 );
            add_action( 'admin_footer', array( __CLASS__, 'output_theme_builder_mount_template' ), 20 );

            add_action( 'wp_ajax_zappoppop_save_builder_theme', array( __CLASS__, 'ajax_save_builder_theme' ) );
            add_action( 'wp_ajax_zappoppop_reset_builder_theme', array( __CLASS__, 'ajax_reset_builder_theme' ) );
            add_action( 'wp_ajax_zappoppop_save_day_preset', array( __CLASS__, 'ajax_save_day_preset' ) );
            add_action( 'admin_init', array( __CLASS__, 'maybe_handle_post_save' ), 5 );
            add_filter( 'admin_body_class', array( __CLASS__, 'filter_admin_body_class' ) );
        }

        /**
         * Adds .zpp-fx-on when the active theme uses Panel Glow (17) or Edge Border Rings (18).
         */
        public static function filter_admin_body_class( $classes ) {
            if ( ! in_array( self::get_current_page_slug(), array( 'zappoppop-theme', 'zappoppop-builder' ), true ) ) {
                return $classes;
            }
            $s = self::get_settings();
            if ( intval( $s['glow_opacity'] ) > 0 || intval( $s['edge_border_opacity'] ) > 0 ) {
                $classes .= ' zpp-fx-on';
            }
            if ( ! empty( $s['stage_image_url'] ) ) {
                $classes .= ' zpp-bg-img';
            }
            return $classes;
        }

        /**
         * Canonical defaults for all 13 Builder Theme rows (including dedicated Text & Border colors).
         */
        public static function get_defaults() {
            return array(
                // 01. Header & Toolbar (Bg + Opacity + Dedicated Text + Dedicated Border)
                'header_bg_color'        => '#0f172a',
                'header_bg_opacity'      => 100,
                'header_text_color'      => '#f8fafc',
                'header_border_color'    => '#1e293b',

                // 02. Content & Sidebar (Bg + Opacity + Dedicated Text + Dedicated Border)
                'content_bg_color'       => '#111827',
                'content_bg_opacity'     => 100,
                'content_text_color'     => '#f8fafc',
                'content_border_color'   => '#1e293b',

                // 03. Accordion Title (Bg + Opacity + Dedicated Text + Dedicated Border)
                'accordion_bg_color'     => '#1e293b',
                'accordion_bg_opacity'   => 100,
                'accordion_text_color'   => '#f8fafc',
                'accordion_border_color' => '#334155',

                // 03b. Accordion Setting Area (Bg + Opacity + Dedicated Text + Dedicated Border)
                'area_bg_color'          => '#1e293b',
                'area_bg_opacity'        => 100,
                'area_text_color'        => '#f8fafc',
                'area_border_color'      => '#334155',

                // 04. Form Field & Button (Bg + Opacity + Dedicated Text + Dedicated Border)
                'field_bg_color'         => '#090d16',
                'field_bg_opacity'       => 100,
                'field_text_color'       => '#f8fafc',
                'field_border_color'     => '#334155',

                // 05. General / Fallback Text Color
                'text_color'             => '#f8fafc',

                // 06. Muted & Unit Text Color
                'muted_text_color'       => '#94a3b8',

                // 07. Icon Color
                'icon_color'             => '#cbd5e1',

                // 08. Hover, Active & Toggle On Color
                'hover_active_color'     => '#3b82f6',

                // 09. Divider & Toggle Off Color + Opacity
                'border_color'           => '#334155',
                'border_opacity'         => 100,

                // 10. Workspace Stage Background + Opacity
                'stage_bg_color'         => '#090d16',
                'stage_bg_opacity'       => 100,

                // 11. Stage Checkerboard Pattern + Opacity
                'checker_color'          => '#1e293b',
                'checker_opacity'        => 65,

                // 12. Primary Save Button (Bg + Dedicated Text + Dedicated Border)
                'save_btn_bg_color'      => '#eab308',
                'save_btn_text_color'    => '#0f172a',
                'save_btn_border_color'  => '#ca8a04',

                // 13. Dedicated Canvas Guide Line Color + Opacity
                'canvas_guide_color'     => '#64748b',
                'canvas_guide_opacity'   => 85,

                // 14. Selected Layer Highlight (Layers Panel) / 15. Update Button / 16. Elements Ribbon
                'layer_sel_bg_color'       => '#3b82f6',
                'layer_sel_bg_opacity'     => 22,
                'layer_sel_text_color'     => '#f8fafc',
                'layer_sel_border_color'   => '#3b82f6',
                'update_btn_bg_color'      => '#f97316',
                'update_btn_text_color'    => '#ffffff',
                'update_btn_border_color'  => '#ea580c',
                'element_bg_color'         => '#0f172a',
                'element_text_color'       => '#e2e8f0',
                'element_border_color'     => '#334155',
                'element_icon_color'       => '#94a3b8',
                'element_hover_color'      => '#334155',

                // 17. Panel Glow / 18. Edge Border Rings (0% opacity = off, existing themes unchanged)
                'glow_color'               => '#22d3ee',
                'glow_opacity'             => 0,
                'edge_border_color'        => '#ff8a2a',
                'edge_border_opacity'      => 0,

                // 18b. Layers Panel Title (header bar of the floating Layers Panel)
                'layers_title_bg_color'    => '#1e293b',
                'layers_title_bg_opacity'  => 100,
                'layers_title_text_color'  => '#f8fafc',
                'layers_title_border_color' => '#334155',

                // 18c. Layers Area (list body of the floating Layers Panel)
                'layers_area_bg_color'     => '#111827',
                'layers_area_bg_opacity'   => 100,
                'layers_area_text_color'   => '#f8fafc',
                'layers_area_border_color' => '#1e293b',

                // 18d. Elements Ribbon Bar (the bar behind the element buttons: Bg + Opacity + Border). Defaults = Header colours.
                'ribbon_bg_color'          => '#0f172a',
                'ribbon_bg_opacity'        => 100,
                'ribbon_border_color'      => '#1e293b',

                // 19. Layers Panel Frame (outer border of the whole floating Layers Panel)
                'layers_border_color'      => '#2a78b8',

                // 20. Stage Background Image (URL from the Media Library; opacity = how strongly it shows)
                'stage_image_url'          => self::builtin_stage_image_url(),
                'stage_image_opacity'      => 100,
            );
        }

        /**
         * Built-in Space starfield (bundled SVG, no external request).
         */
        public static function builtin_stage_image_url() {
            $base = defined( 'ZAPPOPPOP_URL' ) ? ZAPPOPPOP_URL : plugin_dir_url( dirname( __FILE__ ) );
            return $base . 'admin/img/space-stage.jpg';
        }

        /**
         * Starter Presets (Night Mode, Day Mode, Midnight Glass).
         */
        public static function get_presets() {
            $presets = array(
                'slate_night' => array(
                    'label'                  => 'Night Mode',
                    'header_bg_color'        => '#0f172a',
                    'header_bg_opacity'      => 100,
                    'header_text_color'      => '#f8fafc',
                    'header_border_color'    => '#1e293b',
                    'content_bg_color'       => '#111827',
                    'content_bg_opacity'     => 100,
                    'content_text_color'     => '#f8fafc',
                    'content_border_color'   => '#1e293b',
                    'accordion_bg_color'     => '#1e293b',
                    'accordion_bg_opacity'   => 100,
                    'accordion_text_color'   => '#f8fafc',
                    'accordion_border_color' => '#334155',
                    'field_bg_color'         => '#090d16',
                    'field_bg_opacity'       => 100,
                    'field_text_color'       => '#f8fafc',
                    'field_border_color'     => '#334155',
                    'text_color'             => '#f8fafc',
                    'muted_text_color'       => '#94a3b8',
                    'icon_color'             => '#cbd5e1',
                    'hover_active_color'     => '#3b82f6',
                    'border_color'           => '#334155',
                    'border_opacity'         => 100,
                    'stage_bg_color'         => '#090d16',
                    'stage_bg_opacity'       => 100,
                    'checker_color'          => '#1e293b',
                    'checker_opacity'        => 65,
                    'save_btn_bg_color'      => '#eab308',
                    'save_btn_text_color'    => '#0f172a',
                    'save_btn_border_color'  => '#ca8a04',
                    'canvas_guide_color'     => '#64748b',
                    'canvas_guide_opacity'   => 85,
                    'layer_sel_bg_color'       => '#3b82f6',
                    'layer_sel_bg_opacity'     => 22,
                    'layer_sel_text_color'     => '#f8fafc',
                    'layer_sel_border_color'   => '#3b82f6',
                    'update_btn_bg_color'      => '#f97316',
                    'update_btn_text_color'    => '#ffffff',
                    'update_btn_border_color'  => '#ea580c',
                    'element_bg_color'         => '#0f172a',
                    'element_text_color'       => '#e2e8f0',
                    'element_border_color'     => '#334155',
                    'element_icon_color'       => '#94a3b8',
                    'element_hover_color'      => '#334155',
                ),
                'day_light' => array(
                    'label'                  => 'Day Mode',
                    'header_bg_color'        => '#ffffff',
                    'header_bg_opacity'      => 100,
                    'header_text_color'      => '#0f172a',
                    'header_border_color'    => '#e2e8f0',
                    'content_bg_color'       => '#ffffff',
                    'content_bg_opacity'     => 100,
                    'content_text_color'     => '#0f172a',
                    'content_border_color'   => '#e2e8f0',
                    'accordion_bg_color'     => '#f1f5f9',
                    'accordion_bg_opacity'   => 100,
                    'accordion_text_color'   => '#0f172a',
                    'accordion_border_color' => '#cbd5e1',
                    'field_bg_color'         => '#f8fafc',
                    'field_bg_opacity'       => 100,
                    'field_text_color'       => '#0f172a',
                    'field_border_color'     => '#cbd5e1',
                    'text_color'             => '#0f172a',
                    'muted_text_color'       => '#64748b',
                    'icon_color'             => '#475569',
                    'hover_active_color'     => '#6366f1',
                    'border_color'           => '#cbd5e1',
                    'border_opacity'         => 100,
                    'stage_bg_color'         => '#f1f5f9',
                    'stage_bg_opacity'       => 100,
                    'checker_color'          => '#cbd5e1',
                    'checker_opacity'        => 55,
                    'save_btn_bg_color'      => '#6366f1',
                    'save_btn_text_color'    => '#ffffff',
                    'save_btn_border_color'  => '#4f46e5',
                    'canvas_guide_color'     => '#6366f1',
                    'canvas_guide_opacity'   => 75,
                    'layer_sel_bg_color'       => '#6366f1',
                    'layer_sel_bg_opacity'     => 14,
                    'layer_sel_text_color'     => '#0f172a',
                    'layer_sel_border_color'   => '#6366f1',
                    'update_btn_bg_color'      => '#f97316',
                    'update_btn_text_color'    => '#ffffff',
                    'update_btn_border_color'  => '#ea580c',
                    'element_bg_color'         => '#ffffff',
                    'element_text_color'       => '#0f172a',
                    'element_border_color'     => '#cbd5e1',
                    'element_icon_color'       => '#475569',
                    'element_hover_color'      => '#e2e8f0',
                ),
                'midnight_glass' => array(
                    'label'                  => 'Midnight Glass',
                    'header_bg_color'        => '#0f172a',
                    'header_bg_opacity'      => 80,
                    'header_text_color'      => '#f8fafc',
                    'header_border_color'    => '#1e293b',
                    'content_bg_color'       => '#0f172a',
                    'content_bg_opacity'     => 75,
                    'content_text_color'     => '#f8fafc',
                    'content_border_color'   => '#1e293b',
                    'accordion_bg_color'     => '#1e293b',
                    'accordion_bg_opacity'   => 82,
                    'accordion_text_color'   => '#e0f2fe',
                    'accordion_border_color' => '#38bdf8',
                    'field_bg_color'         => '#090d16',
                    'field_bg_opacity'       => 78,
                    'field_text_color'       => '#f8fafc',
                    'field_border_color'     => '#334155',
                    'text_color'             => '#f8fafc',
                    'muted_text_color'       => '#94a3b8',
                    'icon_color'             => '#38bdf8',
                    'hover_active_color'     => '#38bdf8',
                    'border_color'           => '#334155',
                    'border_opacity'         => 60,
                    'stage_bg_color'         => '#090d16',
                    'stage_bg_opacity'       => 92,
                    'checker_color'          => '#1e293b',
                    'checker_opacity'        => 75,
                    'save_btn_bg_color'      => '#0284c7',
                    'save_btn_text_color'    => '#ffffff',
                    'save_btn_border_color'  => '#38bdf8',
                    'canvas_guide_color'     => '#38bdf8',
                    'canvas_guide_opacity'   => 85,
                    'layer_sel_bg_color'       => '#38bdf8',
                    'layer_sel_bg_opacity'     => 20,
                    'layer_sel_text_color'     => '#e0f2fe',
                    'layer_sel_border_color'   => '#38bdf8',
                    'update_btn_bg_color'      => '#f59e0b',
                    'update_btn_text_color'    => '#0f172a',
                    'update_btn_border_color'  => '#fbbf24',
                    'element_bg_color'         => '#090d16',
                    'element_text_color'       => '#e0f2fe',
                    'element_border_color'     => '#334155',
                    'element_icon_color'       => '#38bdf8',
                    'element_hover_color'      => '#1e293b',
                ),
            );

            // Space: deep-space cockpit HUD (navy glass panels, orange borders, cyan glow).
            $presets['space'] = array(
                'label'                  => 'Space',
                'header_bg_color'        => '#060d1f',
                'header_bg_opacity'      => 88,
                'header_text_color'      => '#e6f1ff',
                'header_border_color'    => '#1d4568',
                'content_bg_color'       => '#07122a',
                'content_bg_opacity'     => 72,
                'content_text_color'     => '#d7ecff',
                'content_border_color'   => '#1f6fa8',
                'accordion_bg_color'     => '#0b1a33',
                'accordion_bg_opacity'   => 85,
                'accordion_text_color'   => '#ff9a3c',
                'accordion_border_color' => '#ff8a2a',
                'field_bg_color'         => '#050b18',
                'field_bg_opacity'       => 80,
                'field_text_color'       => '#e6f1ff',
                'field_border_color'     => '#ff8a2a',
                'text_color'             => '#cfe6ff',
                'muted_text_color'       => '#7fa3c7',
                'icon_color'             => '#9fdcff',
                'hover_active_color'     => '#2f80ff',
                'border_color'           => '#27415f',
                'border_opacity'         => 85,
                'stage_bg_color'         => '#030814',
                'stage_bg_opacity'       => 100,
                'checker_color'          => '#0b1730',
                'checker_opacity'        => 0,
                'save_btn_bg_color'      => '#06263d',
                'save_btn_text_color'    => '#7df3ff',
                'save_btn_border_color'  => '#22d3ee',
                'canvas_guide_color'     => '#22d3ee',
                'canvas_guide_opacity'   => 90,
                'layer_sel_bg_color'     => '#2f80ff',
                'layer_sel_bg_opacity'   => 22,
                'layer_sel_text_color'   => '#ffffff',
                'layer_sel_border_color' => '#22d3ee',
                'update_btn_bg_color'    => '#ff8a2a',
                'update_btn_text_color'  => '#190b00',
                'update_btn_border_color'=> '#ffb15c',
                'element_bg_color'       => '#0a1426',
                'element_text_color'     => '#e6f1ff',
                'element_border_color'   => '#2a5f8f',
                'element_icon_color'     => '#9fdcff',
                'element_hover_color'    => '#13294d',
                'glow_color'             => '#22d3ee',
                'glow_opacity'           => 45,
                'edge_border_color'      => '#ff8a2a',
                'edge_border_opacity'    => 70,
                'layers_border_color'    => '#2a78b8',
            );
            $presets['space']['stage_image_url']     = self::builtin_stage_image_url();
            $presets['space']['stage_image_opacity'] = 100;

            // Back-fill any key a preset does not define, so switching presets never leaks values.
            $defaults = self::get_defaults();
            foreach ( $presets as $p_key => $p_data ) {
                if ( 'space' !== $p_key && ! isset( $p_data['stage_image_url'] ) ) {
                    $p_data['stage_image_url'] = ''; // Night / Day / Glass stay image-free (the default image is not back-filled)
                }
                $presets[ $p_key ] = array_merge( $defaults, $p_data );
            }

            foreach ( $presets as $zpp_pk => $zpp_pv ) {
                foreach ( array( 'bg_color', 'bg_opacity', 'text_color', 'border_color' ) as $zpp_sfx ) {
                    if ( ! isset( $presets[ $zpp_pk ][ 'area_' . $zpp_sfx ] ) && isset( $zpp_pv[ 'accordion_' . $zpp_sfx ] ) ) {
                        $presets[ $zpp_pk ][ 'area_' . $zpp_sfx ] = $zpp_pv[ 'accordion_' . $zpp_sfx ];
                    }
                }
            }
            foreach ( $presets as $zpp_pk => $zpp_pv ) {
                foreach ( array( 'layers_title' => 'accordion', 'layers_area' => 'content', 'ribbon' => 'header' ) as $zpp_new => $zpp_src ) {
                    foreach ( array( 'bg_color', 'bg_opacity', 'text_color', 'border_color' ) as $zpp_sfx ) {
                        if ( ! isset( $presets[ $zpp_pk ][ $zpp_new . '_' . $zpp_sfx ] ) && isset( $zpp_pv[ $zpp_src . '_' . $zpp_sfx ] ) ) {
                            $presets[ $zpp_pk ][ $zpp_new . '_' . $zpp_sfx ] = $zpp_pv[ $zpp_src . '_' . $zpp_sfx ];
                        }
                    }
                }
            }
            /* ZPP_DAY_PRESET: the Day Mode button applies the user's saved Day Mode when there is one */
            $zpp_day = get_option( self::DAY_OPTION, array() );
            if ( is_array( $zpp_day ) && ! empty( $zpp_day ) && isset( $presets['day_light'] ) ) {
                $zpp_label = $presets['day_light']['label'];
                $presets['day_light'] = array_merge( $presets['day_light'], $zpp_day );
                $presets['day_light']['label'] = $zpp_label;
            }
            return $presets;
        }

        public static function get_settings() {
            $defaults = self::get_defaults();
            $saved    = get_option( self::OPTION_KEY, array() );
            if ( ! is_array( $saved ) ) {
                $saved = array();
            }

            $parent_theme = get_option( 'zappoppop_theme', array() );
            if ( is_array( $parent_theme ) && isset( $parent_theme['builder'] ) && is_array( $parent_theme['builder'] ) ) {
                $saved = array_merge( $parent_theme['builder'], $saved );
            }

            foreach ( array( 'bg_color', 'bg_opacity', 'text_color', 'border_color' ) as $zpp_sfx ) {
                if ( ! isset( $saved[ 'area_' . $zpp_sfx ] ) && isset( $saved[ 'accordion_' . $zpp_sfx ] ) ) {
                    $saved[ 'area_' . $zpp_sfx ] = $saved[ 'accordion_' . $zpp_sfx ];
                }
            }

            foreach ( array( 'layers_title' => 'accordion', 'layers_area' => 'content', 'ribbon' => 'header' ) as $zpp_new => $zpp_src ) {
                foreach ( array( 'bg_color', 'bg_opacity', 'text_color', 'border_color' ) as $zpp_sfx ) {
                    if ( ! isset( $saved[ $zpp_new . '_' . $zpp_sfx ] ) && isset( $saved[ $zpp_src . '_' . $zpp_sfx ] ) ) {
                        $saved[ $zpp_new . '_' . $zpp_sfx ] = $saved[ $zpp_src . '_' . $zpp_sfx ];
                    }
                }
            }

            return self::sanitize_settings( array_merge( $defaults, $saved ) );
        }

        public static function sanitize_settings( $raw ) {
            $defaults = self::get_defaults();
            if ( ! is_array( $raw ) ) {
                return $defaults;
            }

            $clean = array();
            foreach ( $defaults as $key => $default_val ) {
                if ( ! isset( $raw[ $key ] ) ) {
                    $clean[ $key ] = $default_val;
                    continue;
                }

                if ( 'stage_image_url' === $key ) {
                    $url = is_scalar( $raw[ $key ] ) ? esc_url_raw( trim( (string) wp_unslash( $raw[ $key ] ) ), array( 'http', 'https' ) ) : '';
                    $clean[ $key ] = ( strlen( $url ) <= 2000 ) ? $url : '';
                    continue;
                }

                if ( strpos( $key, '_opacity' ) !== false ) {
                    $val = is_numeric( $raw[ $key ] ) ? intval( round( floatval( $raw[ $key ] ) ) ) : $default_val;
                    $clean[ $key ] = max( 0, min( 100, $val ) );
                } else {
                    $hex = sanitize_text_field( wp_unslash( $raw[ $key ] ) );
                    if ( preg_match( '/^#([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/', $hex ) ) {
                        if ( strlen( $hex ) === 4 ) {
                            $hex = '#' . $hex[1] . $hex[1] . $hex[2] . $hex[2] . $hex[3] . $hex[3];
                        }
                        $clean[ $key ] = strtolower( $hex );
                    } else {
                        $clean[ $key ] = $default_val;
                    }
                }
            }

            return $clean;
        }

        public static function hex_to_rgba( $hex, $opacity = 100 ) {
            $hex = ltrim( trim( (string) $hex ), '#' );
            if ( strlen( $hex ) === 3 ) {
                $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
            }
            if ( strlen( $hex ) !== 6 ) {
                $hex = '0f172a';
            }
            $r = hexdec( substr( $hex, 0, 2 ) );
            $g = hexdec( substr( $hex, 2, 2 ) );
            $b = hexdec( substr( $hex, 4, 2 ) );
            $a = max( 0, min( 100, intval( $opacity ) ) ) / 100;
            $a_str = rtrim( rtrim( number_format( $a, 2, '.', '' ), '0' ), '.' );
            if ( $a_str === '' ) {
                $a_str = '0';
            }
            return sprintf( 'rgba(%d, %d, %d, %s)', $r, $g, $b, $a_str );
        }

        public static function compile_css_vars( $s = null ) {
            if ( ! is_array( $s ) ) {
                $s = self::get_settings();
            }

            $header_rgba    = self::hex_to_rgba( $s['header_bg_color'], $s['header_bg_opacity'] );
            $content_rgba   = self::hex_to_rgba( $s['content_bg_color'], $s['content_bg_opacity'] );
            $accordion_rgba = self::hex_to_rgba( $s['accordion_bg_color'], $s['accordion_bg_opacity'] );
            $area_rgba      = self::hex_to_rgba( $s['area_bg_color'], $s['area_bg_opacity'] );
            $ltitle_rgba    = self::hex_to_rgba( $s['layers_title_bg_color'], $s['layers_title_bg_opacity'] );
            $larea_rgba     = self::hex_to_rgba( $s['layers_area_bg_color'], $s['layers_area_bg_opacity'] );
            $ribbon_rgba    = self::hex_to_rgba( $s['ribbon_bg_color'], $s['ribbon_bg_opacity'] );
            $field_rgba     = self::hex_to_rgba( $s['field_bg_color'], $s['field_bg_opacity'] );
            $border_rgba    = self::hex_to_rgba( $s['border_color'], $s['border_opacity'] );
            $stage_rgba     = self::hex_to_rgba( $s['stage_bg_color'], $s['stage_bg_opacity'] );
            $checker_rgba   = self::hex_to_rgba( $s['checker_color'], $s['checker_opacity'] );
            $guide_rgba     = self::hex_to_rgba( $s['canvas_guide_color'], $s['canvas_guide_opacity'] );

            $accent_soft    = self::hex_to_rgba( $s['hover_active_color'], 18 );
            $accent_ring    = self::hex_to_rgba( $s['hover_active_color'], 32 );
            $layer_sel_rgba = self::hex_to_rgba( $s['layer_sel_bg_color'], $s['layer_sel_bg_opacity'] );
            $glow_rgba      = self::hex_to_rgba( $s['glow_color'], $s['glow_opacity'] );
            $edge_rgba      = self::hex_to_rgba( $s['edge_border_color'], $s['edge_border_opacity'] );

            $stage_image = 'none';
            if ( ! empty( $s['stage_image_url'] ) ) {
                $safe_url    = str_replace( array( '"', '\\', '(', ')', ' ', "\n", "\r" ), array( '%22', '%5C', '%28', '%29', '%20', '', '' ), (string) $s['stage_image_url'] );
                $dim_rgba    = self::hex_to_rgba( $s['stage_bg_color'], 100 - intval( $s['stage_image_opacity'] ) );
                $stage_image = 'linear-gradient(' . $dim_rgba . ', ' . $dim_rgba . '), url("' . $safe_url . '")';
            }

            $icon_hex_clean = rawurlencode( $s['icon_color'] );
            $chevron_svg    = "url(\"data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='{$icon_hex_clean}' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E\")";
            $checker_img    = "linear-gradient(45deg, {$checker_rgba} 25%, transparent 25%), linear-gradient(-45deg, {$checker_rgba} 25%, transparent 25%), linear-gradient(45deg, transparent 75%, {$checker_rgba} 75%), linear-gradient(-45deg, transparent 75%, {$checker_rgba} 75%)";

            $vars = array(
                '--zpp-builder-header-bg'          => $header_rgba,
                '--zpp-builder-header-text'        => $s['header_text_color'],
                '--zpp-builder-header-border'      => $s['header_border_color'],
                '--zpp-builder-subtoolbar-bg'      => $header_rgba,
                '--zpp-builder-floating-bar-bg'    => $header_rgba,
                '--zpp-builder-content-bg'         => $content_rgba,
                '--zpp-builder-content-text'       => $s['content_text_color'],
                '--zpp-builder-content-border'     => $s['content_border_color'],
                '--zpp-builder-popup-drawer-bg'    => $content_rgba,
                '--zpp-builder-popup-settings-bg'  => $content_rgba,
                '--zpp-builder-section-card-bg'    => $content_rgba,
                '--zpp-builder-layers-body-bg'     => $content_rgba,
                '--zpp-builder-accordion-bg'       => $accordion_rgba,
                '--zpp-builder-accordion-text'     => $s['accordion_text_color'],
                '--zpp-builder-accordion-border'   => $s['accordion_border_color'],
                '--zpp-builder-area-bg'            => $area_rgba,
                '--zpp-builder-area-text'          => $s['area_text_color'],
                '--zpp-builder-area-border'        => $s['area_border_color'],
                '--zpp-builder-popup-acc-bg'       => $accordion_rgba,
                '--zpp-builder-popup-acc-active-bg'=> $accordion_rgba,
                '--zpp-builder-layers-header-bg'   => $ltitle_rgba,
                '--zpp-builder-field-bg'           => $field_rgba,
                '--zpp-builder-field-text'         => $s['field_text_color'],
                '--zpp-builder-field-border'       => $s['field_border_color'],
                '--zpp-builder-popup-field-bg'     => $field_rgba,
                '--zpp-builder-toolbar-btn-bg'     => $field_rgba,
                '--zpp-builder-border-color'       => $border_rgba,
                '--zpp-builder-popup-border-color' => $s['content_border_color'],
                '--zpp-builder-text-color'         => $s['text_color'],
                '--zpp-builder-popup-acc-text'     => $s['accordion_text_color'],
                '--zpp-builder-muted-text'         => $s['muted_text_color'],
                '--zpp-builder-icon-color'         => $s['icon_color'],
                '--zpp-builder-popup-acc-icon'     => $s['icon_color'],
                '--zpp-builder-accent-color'       => $s['hover_active_color'],
                '--zpp-builder-accent-soft'        => $accent_soft,
                '--zpp-builder-accent-ring'        => $accent_ring,
                '--zpp-builder-toggle-off-bg'      => $border_rgba,
                '--zpp-builder-stage-bg'           => $stage_rgba,
                '--zpp-builder-checker-color'      => $checker_rgba,
                '--zpp-builder-checker-image'      => $checker_img,
                '--zpp-builder-save-bg'            => $s['save_btn_bg_color'],
                '--zpp-builder-save-text'          => $s['save_btn_text_color'],
                '--zpp-builder-save-border'        => $s['save_btn_border_color'],
                '--zpp-builder-guide-color'        => $guide_rgba,
                '--zpp-builder-selection-color'    => $s['hover_active_color'],
                '--zpp-builder-select-chevron'     => $chevron_svg,
                '--zpp-builder-layer-sel-bg'       => $layer_sel_rgba,
                '--zpp-builder-layer-sel-text'     => $s['layer_sel_text_color'],
                '--zpp-builder-layer-sel-border'   => $s['layer_sel_border_color'],
                '--zpp-builder-update-bg'          => $s['update_btn_bg_color'],
                '--zpp-builder-update-text'        => $s['update_btn_text_color'],
                '--zpp-builder-update-border'      => $s['update_btn_border_color'],
                '--zpp-builder-element-bg'         => $s['element_bg_color'],
                '--zpp-builder-element-text'       => $s['element_text_color'],
                '--zpp-builder-element-border'     => $s['element_border_color'],
                '--zpp-builder-element-icon'       => $s['element_icon_color'],
                '--zpp-builder-element-hover'      => $s['element_hover_color'],
                '--zpp-builder-glow'               => $glow_rgba,
                '--zpp-builder-glow-shadow'        => '0 0 10px 1px ' . $glow_rgba,
                '--zpp-builder-edge-border'        => $edge_rgba,
                '--zpp-builder-edge-ring'          => 'inset 0 0 0 1px ' . $edge_rgba,
                '--zpp-builder-layers-border'      => $s['layers_area_border_color'],
                '--zpp-builder-layers-title-bg'    => $ltitle_rgba,
                '--zpp-builder-layers-title-text'  => $s['layers_title_text_color'],
                '--zpp-builder-layers-title-border' => $s['layers_title_border_color'],
                '--zpp-builder-layers-area-bg'     => $larea_rgba,
                '--zpp-builder-layers-area-text'   => $s['layers_area_text_color'],
                '--zpp-builder-layers-area-border' => $s['layers_area_border_color'],
                '--zpp-builder-ribbon-bg'          => $ribbon_rgba,
                '--zpp-builder-ribbon-border'      => $s['ribbon_border_color'],
                '--zpp-builder-stage-image'        => $stage_image,
            );

            $out = '';
            foreach ( $vars as $k => $v ) {
                $out .= $k . ': ' . $v . '; ';
            }
            return $out;
        }

        public static function get_current_page_slug() {
            return isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
        }

        public static function enqueue_assets() {
            $page = self::get_current_page_slug();
            if ( ! in_array( $page, array( 'zappoppop-theme', 'zappoppop-builder' ), true ) ) {
                return;
            }

            $plugin_url = plugin_dir_url( dirname( __FILE__ ) );
            $plugin_dir = dirname( dirname( __FILE__ ) );

            $css_file = $plugin_dir . '/admin/css/zappoppop-builder-theme.css';
            $js_file  = $plugin_dir . '/admin/js/zappoppop-builder-theme.js';

            $css_ver = file_exists( $css_file ) ? filemtime( $css_file ) : '1.0.8';
            $js_ver  = file_exists( $js_file ) ? filemtime( $js_file ) : '1.0.8';

            wp_enqueue_style(
                'zappoppop-builder-theme',
                $plugin_url . 'admin/css/zappoppop-builder-theme.css',
                array(),
                $css_ver
            );

            wp_enqueue_script(
                'zappoppop-builder-theme',
                $plugin_url . 'admin/js/zappoppop-builder-theme.js',
                array( 'jquery' ),
                $js_ver,
                true
            );

            wp_localize_script(
                'zappoppop-builder-theme',
                'zappoppopBuilderThemeData',
                array(
                    'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
                    'nonce'    => wp_create_nonce( 'zappoppop_nonce' ),
                    'page'     => $page,
                    'settings' => self::get_settings(),
                    'defaults' => self::get_defaults(),
                    'presets'  => self::get_presets(),
                    'builtinBg' => self::builtin_stage_image_url(),
                )
            );
        }

        public static function output_dynamic_css_variables() {
            $page = self::get_current_page_slug();
            if ( ! in_array( $page, array( 'zappoppop-theme', 'zappoppop-builder' ), true ) ) {
                return;
            }

            $vars_css = self::compile_css_vars();
            if ( wp_style_is( 'zappoppop-builder-theme', 'enqueued' ) ) {
                wp_add_inline_style( 'zappoppop-builder-theme', ":root, html, body, body.wp-admin, .zappoppop-builder-wrap, #zappoppop-builder, .zappoppop-theme-preview-shell { " . wp_strip_all_tags( $vars_css ) . " }" );
            }
        }

        /**
         * Late-cascade CSS rules in admin_footer for /admin.php?page=zappoppop-builder.
         */
        public static function output_late_cascade_override() {
            $page = self::get_current_page_slug();
            if ( $page !== 'zappoppop-builder' ) {
                return;
            }

            $vars_css = self::compile_css_vars();
            $late_file = dirname( dirname( __FILE__ ) ) . '/admin/css/zappoppop-builder-theme-late.css';
            wp_enqueue_style( 'zappoppop-builder-theme-late', plugin_dir_url( dirname( __FILE__ ) ) . 'admin/css/zappoppop-builder-theme-late.css', array( 'zappoppop-builder-theme' ), file_exists( $late_file ) ? (string) filemtime( $late_file ) : '1.0' );
            wp_add_inline_style( 'zappoppop-builder-theme-late', ':root, html, body, body.wp-admin { ' . wp_strip_all_tags( $vars_css ) . ' }' );
            /* ZPP_SETTINGS_SWITCHES: Popup Settings theme bridge + slide-toggles (needs the theme variables printed above) */
            $zpp_sw_file = dirname( dirname( __FILE__ ) ) . '/admin/css/builder/settings-switches.css';
            wp_enqueue_style( 'zappoppop-builder-settings-switches', plugin_dir_url( dirname( __FILE__ ) ) . 'admin/css/builder/settings-switches.css', array( 'zappoppop-builder-theme-late' ), file_exists( $zpp_sw_file ) ? (string) filemtime( $zpp_sw_file ) : ( defined( 'ZAPPOPPOP_VERSION' ) ? ZAPPOPPOP_VERSION : '1.0' ) );
            /* ZPP_INSPECTOR_THEME_BRIDGE: Inspector frames / labels / switches follow the Theme > Builder tokens */
            $zpp_br_file = dirname( dirname( __FILE__ ) ) . '/admin/css/builder/inspector-theme-bridge.css';
            wp_enqueue_style( 'zappoppop-builder-inspector-theme-bridge', plugin_dir_url( dirname( __FILE__ ) ) . 'admin/css/builder/inspector-theme-bridge.css', array( 'zappoppop-builder-settings-switches' ), file_exists( $zpp_br_file ) ? (string) filemtime( $zpp_br_file ) : '1.0' );
            /* ZPP_CONTENT_TAB: framed accordion blocks, compact fields, bottom Reset */
            $zpp_ct_file = dirname( dirname( __FILE__ ) ) . '/admin/css/builder/content-tab.css';
            wp_enqueue_style( 'zappoppop-builder-content-tab', plugin_dir_url( dirname( __FILE__ ) ) . 'admin/css/builder/content-tab.css', array( 'zappoppop-builder-inspector-theme-bridge' ), file_exists( $zpp_ct_file ) ? (string) filemtime( $zpp_ct_file ) : '1.0' );
            /* ZPP_LINK_GROUP */
            $zpp_lk_file = dirname( dirname( __FILE__ ) ) . '/admin/css/builder/link-group.css';
            wp_enqueue_style( 'zappoppop-builder-link-group', plugin_dir_url( dirname( __FILE__ ) ) . 'admin/css/builder/link-group.css', array( 'zappoppop-builder-content-tab' ), file_exists( $zpp_lk_file ) ? (string) filemtime( $zpp_lk_file ) : '1.0' );
            /* ZPP_TAB_BLOCKS */
            $zpp_tb_file = dirname( dirname( __FILE__ ) ) . '/admin/css/builder/tab-blocks.css';
            wp_enqueue_style( 'zappoppop-builder-tab-blocks', plugin_dir_url( dirname( __FILE__ ) ) . 'admin/css/builder/tab-blocks.css', array( 'zappoppop-builder-link-group' ), file_exists( $zpp_tb_file ) ? (string) filemtime( $zpp_tb_file ) : '1.0' );
            /* ZPP_LAYERS_THEME */
            $zpp_lt_file = dirname( dirname( __FILE__ ) ) . '/admin/css/builder/layers-panel-theme.css';
            wp_enqueue_style( 'zappoppop-builder-layers-panel-theme', plugin_dir_url( dirname( __FILE__ ) ) . 'admin/css/builder/layers-panel-theme.css', array( 'zappoppop-builder-tab-blocks' ), file_exists( $zpp_lt_file ) ? (string) filemtime( $zpp_lt_file ) : '1.0' );
        }

        private static function verify_request_security() {
            if ( ! current_user_can( 'manage_options' ) ) {
                if ( wp_doing_ajax() ) {
                    wp_send_json_error( array( 'message' => esc_html__( 'Unauthorized user capability.', 'zappoppop' ) ), 403 );
                }
                wp_die( esc_html__( 'Unauthorized user capability.', 'zappoppop' ), 403 );
            }

            $zpp_verified = check_ajax_referer( 'zappoppop_nonce', 'nonce', false )
                || check_ajax_referer( 'zappoppop_nonce', '_ajax_nonce', false )
                || check_ajax_referer( 'zappoppop_admin', 'nonce', false )
                || check_ajax_referer( 'zappoppop_nonce', 'security', false )
                || ( isset( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'zappoppop_nonce' ) )
                || ( isset( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'zappoppop_admin' ) )
                || ( isset( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'zappoppop_theme_nonce' ) );

            if ( ! $zpp_verified ) {
                if ( wp_doing_ajax() ) {
                    wp_send_json_error( array( 'message' => esc_html__( 'Invalid security token.', 'zappoppop' ) ), 403 );
                }
                wp_die( esc_html__( 'Invalid security token.', 'zappoppop' ), 403 );
            }
        }

        public static function persist_settings( $sanitized ) {
            update_option( self::OPTION_KEY, $sanitized );

            $parent_theme = get_option( 'zappoppop_theme', array() );
            if ( ! is_array( $parent_theme ) ) {
                $parent_theme = array();
            }
            $parent_theme['builder'] = $sanitized;
            update_option( 'zappoppop_theme', $parent_theme );
        }

        public static function ajax_save_builder_theme() {
            self::verify_request_security();

            if (!class_exists('ZapPopPop_Sanitizer')) { require_once ZAPPOPPOP_PATH . 'includes/class-zappoppop-sanitizer.php'; }
            $raw = isset( $_POST['settings'] ) && is_array( $_POST['settings'] )
                ? ZapPopPop_Sanitizer::post_array( 'settings' )
                : ZapPopPop_Sanitizer::post_all();

            $sanitized = self::sanitize_settings( $raw );
            self::persist_settings( $sanitized );

            wp_send_json_success( array(
                'message'  => esc_html__( 'Builder theme settings saved.', 'zappoppop' ),
                'settings' => $sanitized,
                'cssVars'  => self::compile_css_vars( $sanitized ),
            ) );
        }

        public static function ajax_save_day_preset() {
            self::verify_request_security();
            if ( ! class_exists( 'ZapPopPop_Sanitizer' ) ) { require_once ZAPPOPPOP_PATH . 'includes/class-zappoppop-sanitizer.php'; }
            $raw = isset( $_POST['settings'] ) && is_array( $_POST['settings'] )
                ? ZapPopPop_Sanitizer::post_array( 'settings' )
                : array();
            if ( empty( $raw ) ) {
                wp_send_json_error( array( 'message' => esc_html__( 'No settings received.', 'zappoppop' ) ), 400 );
            }
            update_option( self::DAY_OPTION, self::sanitize_settings( $raw ) );
            wp_send_json_success( array(
                'message' => esc_html__( 'Day Mode saved.', 'zappoppop' ),
                'preset'  => self::get_presets()['day_light'],
            ) );
        }

        public static function ajax_reset_builder_theme() {
            self::verify_request_security();

            $defaults = self::get_defaults();
            self::persist_settings( $defaults );

            wp_send_json_success( array(
                'message'  => esc_html__( 'Builder theme reset to defaults.', 'zappoppop' ),
                'settings' => $defaults,
                'cssVars'  => self::compile_css_vars( $defaults ),
            ) );
        }

        public static function maybe_handle_post_save() {
            if ( ! isset( $_POST['zpp_builder_theme'] ) || ! is_array( $_POST['zpp_builder_theme'] ) ) {
                return;
            }
            if ( ! current_user_can( 'manage_options' ) ) {
                return;
            }
            $verified = ( isset( $_POST['zappoppop_builder_theme_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['zappoppop_builder_theme_nonce'] ) ), 'zappoppop_nonce' ) )
                || ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wpnonce'] ) ), 'zappoppop_nonce' ) )
                || ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wpnonce'] ) ), 'zappoppop_theme_nonce' ) );

            if ( $verified ) {
                if (!class_exists('ZapPopPop_Sanitizer')) { require_once ZAPPOPPOP_PATH . 'includes/class-zappoppop-sanitizer.php'; }
                $raw = ZapPopPop_Sanitizer::post_array( 'zpp_builder_theme' );
                $sanitized = self::sanitize_settings( $raw );
                self::persist_settings( $sanitized );
            }
        }

        /**
         * 13 Compact Rows with Dedicated Bg + Txt + Bdr on 01, 02, 03, 04, 12 + 13. Canvas Guide Line Color.
         */
        public static function get_fields_definition() {
            return array(
                array(
                    'num'              => '01',
                    'title'            => 'Header & Toolbar Background',
                    'desc'             => 'Top bar, secondary element toolbar & bottom bar (Bg, Text, Border & Opacity).',
                    'color_key'        => 'header_bg_color',
                    'text_color_key'   => 'header_text_color',
                    'border_color_key' => 'header_border_color',
                    'opacity_key'      => 'header_bg_opacity',
                ),
                array(
                    'num'              => '02',
                    'title'            => 'Content & Sidebar Background',
                    'desc'             => 'Popup Settings drawer/body, Layer Settings & Layers Panel (Bg, Text, Border & Opacity).',
                    'color_key'        => 'content_bg_color',
                    'text_color_key'   => 'content_text_color',
                    'border_color_key' => 'content_border_color',
                    'opacity_key'      => 'content_bg_opacity',
                ),
                array(
                    'num'              => '03',
                    'title'            => 'Accordion Title',
                    'desc'             => 'Header bar of every accordion in Popup Settings, Layer Settings & Layers Header (Bg, Text, Border & Opacity).',
                    'color_key'        => 'accordion_bg_color',
                    'text_color_key'   => 'accordion_text_color',
                    'border_color_key' => 'accordion_border_color',
                    'opacity_key'      => 'accordion_bg_opacity',
                ),
                array(
                    'num'              => '04',
                    'title'            => 'Accordion Setting Area',
                    'desc'             => 'Body of the accordion blocks in Layer Settings (Bg, Text, Border & Opacity).',
                    'color_key'        => 'area_bg_color',
                    'text_color_key'   => 'area_text_color',
                    'border_color_key' => 'area_border_color',
                    'opacity_key'      => 'area_bg_opacity',
                ),
                array(
                    'num'              => '05',
                    'title'            => 'Form Field & Button Background',
                    'desc'             => 'All text inputs, coordinate boxes, dropdowns & icon buttons (Bg, Text, Border & Opacity).',
                    'color_key'        => 'field_bg_color',
                    'text_color_key'   => 'field_text_color',
                    'border_color_key' => 'field_border_color',
                    'opacity_key'      => 'field_bg_opacity',
                ),
                array(
                    'num'         => '06',
                    'title'       => 'General Text & Label Color',
                    'desc'        => 'Fallback typography color for general headings and labels.',
                    'color_key'   => 'text_color',
                    'opacity_key' => null,
                ),
                array(
                    'num'         => '07',
                    'title'       => 'Muted & Unit Text Color',
                    'desc'        => 'Helper captions (Appearance, Duration ms, px, %, 350 x 400 readout).',
                    'color_key'   => 'muted_text_color',
                    'opacity_key' => null,
                ),
                array(
                    'num'         => '08',
                    'title'       => 'Icon Color',
                    'desc'        => 'Toolbar icons, element glyphs, accordion arrows, layer icons, and dropdown chevrons.',
                    'color_key'   => 'icon_color',
                    'opacity_key' => null,
                ),
                array(
                    'num'         => '09',
                    'title'       => 'Hover, Active & Toggle On Color',
                    'desc'        => 'Active tabs, active Canvas 1 pill, selected layer row, focus ring, and active switches.',
                    'color_key'   => 'hover_active_color',
                    'opacity_key' => null,
                ),
                array(
                    'num'         => '10',
                    'title'       => 'Divider & Toggle Off Color',
                    'desc'        => 'Internal horizontal dividers, section lines, and unchecked toggle switches.',
                    'color_key'   => 'border_color',
                    'opacity_key' => 'border_opacity',
                ),
                array(
                    'num'         => '11',
                    'title'       => 'Workspace Stage Background',
                    'desc'        => 'Base background color and opacity of the center builder workspace stage.',
                    'color_key'   => 'stage_bg_color',
                    'opacity_key' => 'stage_bg_opacity',
                ),
                array(
                    'num'         => '12',
                    'title'       => 'Stage Checkerboard Pattern',
                    'desc'        => 'Checkerboard grid pattern on the workspace stage (set opacity to 0% to hide).',
                    'color_key'   => 'checker_color',
                    'opacity_key' => 'checker_opacity',
                ),
                array(
                    'num'              => '13',
                    'title'            => 'Primary Save Button',
                    'desc'             => 'Background, Text, and Border color for Save & Save As Template buttons.',
                    'color_key'        => 'save_btn_bg_color',
                    'text_color_key'   => 'save_btn_text_color',
                    'border_color_key' => 'save_btn_border_color',
                    'opacity_key'      => null,
                ),
                array(
                    'num'         => '14',
                    'title'       => 'Canvas Guide Line Color',
                    'desc'        => 'Dashed popup canvas boundary frame and alignment/snap guide lines on stage.',
                    'color_key'   => 'canvas_guide_color',
                    'opacity_key' => 'canvas_guide_opacity',
                ),
                array(
                    'num'              => '15',
                    'title'            => 'Selected Layer Highlight',
                    'desc'             => 'Active row in the floating Layers Panel (Bg + Opacity, Text & Border).',
                    'color_key'        => 'layer_sel_bg_color',
                    'text_color_key'   => 'layer_sel_text_color',
                    'border_color_key' => 'layer_sel_border_color',
                    'opacity_key'      => 'layer_sel_bg_opacity',
                ),
                array(
                    'num'              => '16',
                    'title'            => 'Update Button (Unsaved Changes)',
                    'desc'             => 'Color of the Save button once it turns into Update because the popup has unsaved changes.',
                    'color_key'        => 'update_btn_bg_color',
                    'text_color_key'   => 'update_btn_text_color',
                    'border_color_key' => 'update_btn_border_color',
                    'opacity_key'      => null,
                ),
                array(
                    'num'              => '17',
                    'title'            => 'Elements Ribbon Buttons',
                    'desc'             => 'Element tool buttons in the ribbon: Bg, Text, Border, Icon and Hover background.',
                    'color_key'        => 'element_bg_color',
                    'text_color_key'   => 'element_text_color',
                    'border_color_key' => 'element_border_color',
                    'extra_color_keys' => array( 'element_icon_color' => 'Ico', 'element_hover_color' => 'Hov' ),
                    'opacity_key'      => null,
                ),
                array(
                    'num'              => '23',
                    'title'            => 'Elements Ribbon Bar',
                    'desc'             => 'The bar behind the element buttons (Bg + Opacity & Border). Buttons keep their own colours in row 17.',
                    'color_key'        => 'ribbon_bg_color',
                    'border_color_key' => 'ribbon_border_color',
                    'opacity_key'      => 'ribbon_bg_opacity',
                ),
                array(
                    'num'         => '18',
                    'title'       => 'Panel Glow',
                    'desc'        => 'Soft neon glow around accordions, Layers Panel, side drawers, Save buttons & ribbon buttons. Opacity 0% = off.',
                    'color_key'   => 'glow_color',
                    'opacity_key' => 'glow_opacity',
                ),
                array(
                    'num'         => '19',
                    'title'       => 'Edge Border Rings',
                    'desc'        => 'Border for inactive canvas tabs and the frame around the elements ribbon bar (inset, no layout shift). Opacity 0% = off.',
                    'color_key'   => 'edge_border_color',
                    'opacity_key' => 'edge_border_opacity',
                ),
                array(
                    'num'              => '20',
                    'title'            => 'Layers Panel Title',
                    'desc'             => 'Header bar of the floating Layers Panel (Bg, Text, Border & Opacity).',
                    'color_key'        => 'layers_title_bg_color',
                    'text_color_key'   => 'layers_title_text_color',
                    'border_color_key' => 'layers_title_border_color',
                    'opacity_key'      => 'layers_title_bg_opacity',
                ),
                array(
                    'num'              => '21',
                    'title'            => 'Layers Area',
                    'desc'             => 'Layer list body of the floating Layers Panel (Bg, Text, Border & Opacity).',
                    'color_key'        => 'layers_area_bg_color',
                    'text_color_key'   => 'layers_area_text_color',
                    'border_color_key' => 'layers_area_border_color',
                    'opacity_key'      => 'layers_area_bg_opacity',
                ),
                array(
                    'num'         => '22',
                    'title'       => 'Stage Background Image',
                    'desc'        => 'Image behind the whole builder (the Space starfield or your own upload). Opacity = how strongly it shows; the rest is dimmed with the Stage color (row 10).',
                    'type'        => 'image',
                    'image_key'   => 'stage_image_url',
                    'opacity_key' => 'stage_image_opacity',
                ),
            );
        }

        /**
         * Row 20: Media Library picker + URL field + opacity (not a colour row).
         */
        public static function render_image_row( $f, $settings ) {
            $i_key = $f['image_key'];
            $o_key = $f['opacity_key'];
            $i_val = isset( $settings[ $i_key ] ) ? (string) $settings[ $i_key ] : '';
            $o_val = isset( $settings[ $o_key ] ) ? intval( $settings[ $o_key ] ) : 100;
            $thumb = $i_val ? "background-image:url('" . esc_url( $i_val ) . "')" : '';
            ?>
            <div class="zpp-bt-compact-row zpp-bt-image-row" data-setting-num="<?php echo esc_attr( $f['num'] ); ?>" title="<?php echo esc_attr( $f['desc'] ); ?>">
                <div class="zpp-bt-row-top">
                    <div class="zpp-bt-row-label">
                        <span class="zpp-bt-row-num"><?php echo esc_html( $f['num'] ); ?></span>
                        <label class="zpp-bt-row-title" for="zpp-bt-<?php echo esc_attr( $i_key ); ?>"><?php echo esc_html( $f['title'] ); ?></label>
                        <small class="zpp-bt-row-desc"><?php echo esc_html( $f['desc'] ); ?></small>
                    </div>
                </div>
                <div class="zpp-bt-row-controls">
                    <div class="zpp-bt-image-pill">
                        <span class="zpp-bt-image-thumb" style="<?php echo esc_attr( $thumb ); ?>"></span>
                        <input type="text" id="zpp-bt-<?php echo esc_attr( $i_key ); ?>" class="zpp-bt-image-url" name="zpp_builder_theme[<?php echo esc_attr( $i_key ); ?>]" data-image-key="<?php echo esc_attr( $i_key ); ?>" value="<?php echo esc_attr( $i_val ); ?>" placeholder="<?php echo esc_attr__( 'https://… or choose from the Media Library', 'zappoppop' ); ?>" spellcheck="false" />
                        <button type="button" class="button zpp-bt-image-choose"><?php echo esc_html__( 'Choose Image', 'zappoppop' ); ?></button>
                        <button type="button" class="button zpp-bt-image-builtin"><?php echo esc_html__( 'Space Starfield', 'zappoppop' ); ?></button>
                        <button type="button" class="button zpp-bt-image-remove"><?php echo esc_html__( 'Remove', 'zappoppop' ); ?></button>
                    </div>
                    <div class="zpp-bt-opacity-inline" title="<?php echo esc_attr__( 'Image visibility (0–100%)', 'zappoppop' ); ?>">
                        <span class="zpp-bt-op-lbl"><?php echo esc_html__( 'Opacity', 'zappoppop' ); ?></span>
                        <input type="range" class="zpp-bt-opacity-range" data-opacity-key="<?php echo esc_attr( $o_key ); ?>" data-paired-color="stage_bg_color" min="0" max="100" step="1" value="<?php echo esc_attr( $o_val ); ?>" />
                        <div class="zpp-bt-opacity-num-box">
                            <input type="number" name="zpp_builder_theme[<?php echo esc_attr( $o_key ); ?>]" class="zpp-bt-opacity-number" data-opacity-num-for="<?php echo esc_attr( $o_key ); ?>" data-paired-color="stage_bg_color" min="0" max="100" step="1" value="<?php echo esc_attr( $o_val ); ?>" />
                            <span>%</span>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }

        public static function render_settings_panel() {
            $settings = self::get_settings();
            $fields   = self::get_fields_definition();
            $presets  = self::get_presets();
            $nonce    = wp_create_nonce( 'zappoppop_nonce' );
            ?>
            <div id="zappoppop-builder-theme-panel" class="zpp-bt-panel" data-zpp-builder-theme-version="7H">
                <input type="hidden" name="zappoppop_builder_theme_submitted" value="1" />
                <input type="hidden" id="zappoppop_builder_theme_nonce" name="zappoppop_builder_theme_nonce" value="<?php echo esc_attr( $nonce ); ?>" />

                <!-- Compact Top Action Bar -->
                <div class="zpp-bt-top-bar">
                    <div class="zpp-bt-top-left">
                        <span class="zpp-bt-badge">Builder Theme</span>
                        <strong class="zpp-bt-heading">Workspace, Dedicated Text, Borders &amp; Guide Line Colors</strong>
                    </div>

                    <div class="zpp-bt-top-right">
                        <div class="zpp-bt-presets-inline">
                            <?php foreach ( $presets as $p_key => $p_data ) : ?>
                                <button type="button" class="zpp-bt-preset-btn" data-preset="<?php echo esc_attr( $p_key ); ?>">
                                    <span class="zpp-bt-preset-dot" style="background: <?php echo esc_attr( $p_data['accordion_bg_color'] ); ?>; border-color: <?php echo esc_attr( $p_data['hover_active_color'] ); ?>;"></span>
                                    <?php echo esc_html( $p_data['label'] ); ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" id="zpp-bt-day-save" class="button button-secondary zpp-bt-btn-reset" title="<?php echo esc_attr__( 'Store the current settings as the Day Mode preset', 'zappoppop' ); ?>"><?php echo esc_html__( 'Save as Day Mode', 'zappoppop' ); ?></button>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=zappoppop-builder' ) ); ?>" target="_blank" class="zpp-bt-link-btn">Open Builder &#8599;</a>
                        <button type="button" id="zpp-bt-reset-btn" class="button button-secondary zpp-bt-btn-reset">Reset</button>
                        <button type="button" id="zpp-bt-save-btn" class="button button-primary zpp-bt-btn-save">Save Theme</button>
                    </div>
                </div>

                <!-- Side-by-Side Split Layout: Left Compact Settings List | Right Dynamic Interactive Preview -->
                <div class="zpp-bt-side-by-side">
                    <!-- LEFT: Compact 13-Row Settings Table (2-Line Compact Row: Title on Line 1, Controls on Line 2) -->
                    <div class="zpp-bt-settings-pane">
                        <div class="zpp-bt-pane-header">
                            <span>Theme Setting (1–20)</span>
                            <span>Bg / Text / Border / Opacity</span>
                        </div>
                        <div class="zpp-bt-compact-list">
                            <?php foreach ( $fields as $f ) :
                                if ( ! empty( $f['type'] ) && 'image' === $f['type'] ) {
                                    self::render_image_row( $f, $settings );
                                    continue;
                                }
                                $c_key  = $f['color_key'];
                                $c_val  = isset( $settings[ $c_key ] ) ? $settings[ $c_key ] : '#0f172a';
                                $o_key  = $f['opacity_key'];
                                $o_val  = ( $o_key && isset( $settings[ $o_key ] ) ) ? intval( $settings[ $o_key ] ) : 100;
                                $rgba_preview = self::hex_to_rgba( $c_val, $o_val );
                                $has_multi_colors = ! empty( $f['text_color_key'] ) || ! empty( $f['border_color_key'] ) || ! empty( $f['extra_color_keys'] );
                            ?>
                                <div class="zpp-bt-compact-row" data-setting-num="<?php echo esc_attr( $f['num'] ); ?>" title="<?php echo esc_attr( $f['desc'] ); ?>">
                                    <!-- First Line: Number Badge + Title + Inline Description + Live Alpha Swatch -->
                                    <div class="zpp-bt-row-top">
                                        <div class="zpp-bt-row-label">
                                            <span class="zpp-bt-row-num"><?php echo esc_html( $f['num'] ); ?></span>
                                            <label class="zpp-bt-row-title" for="zpp-bt-<?php echo esc_attr( $c_key ); ?>"><?php echo esc_html( $f['title'] ); ?></label>
                                            <small class="zpp-bt-row-desc"><?php echo esc_html( $f['desc'] ); ?></small>
                                        </div>
                                        <span class="zpp-bt-swatch-checker" title="Live RGBA Swatch">
                                            <span class="zpp-bt-alpha-swatch" data-swatch-for="<?php echo esc_attr( $c_key ); ?>" data-opacity-source="<?php echo esc_attr( (string) $o_key ); ?>" style="background-color: <?php echo esc_attr( $rgba_preview ); ?>;"></span>
                                        </span>
                                    </div>

                                    <!-- Second Line: Compact Color Pills (Bg, Txt, Bdr) + Opacity Slider (No "Solid" box) -->
                                    <div class="zpp-bt-row-controls">
                                        <div class="zpp-bt-color-pill" title="Background / Primary Color">
                                            <span class="zpp-bt-mini-tag"><?php echo $has_multi_colors ? 'Bg' : 'Color'; ?></span>
                                            <input
                                                type="color"
                                                id="zpp-bt-<?php echo esc_attr( $c_key ); ?>"
                                                class="zpp-bt-color-picker"
                                                data-key="<?php echo esc_attr( $c_key ); ?>"
                                                value="<?php echo esc_attr( $c_val ); ?>"
                                            />
                                            <input
                                                type="text"
                                                name="zpp_builder_theme[<?php echo esc_attr( $c_key ); ?>]"
                                                class="zpp-bt-hex-text"
                                                data-hex-for="<?php echo esc_attr( $c_key ); ?>"
                                                value="<?php echo esc_attr( $c_val ); ?>"
                                                maxlength="7"
                                                spellcheck="false"
                                            />
                                        </div>

                                        <?php if ( ! empty( $f['text_color_key'] ) ) :
                                            $tc_key = $f['text_color_key'];
                                            $tc_val = isset( $settings[ $tc_key ] ) ? $settings[ $tc_key ] : '#f8fafc';
                                        ?>
                                            <!-- Dedicated Text Color Picker (01, 02, 03, 04, 12) -->
                                            <div class="zpp-bt-color-pill zpp-bt-text-pill" title="Dedicated Text Color">
                                                <span class="zpp-bt-mini-tag">Txt</span>
                                                <input
                                                    type="color"
                                                    id="zpp-bt-<?php echo esc_attr( $tc_key ); ?>"
                                                    class="zpp-bt-color-picker"
                                                    data-key="<?php echo esc_attr( $tc_key ); ?>"
                                                    value="<?php echo esc_attr( $tc_val ); ?>"
                                                />
                                                <input
                                                    type="text"
                                                    name="zpp_builder_theme[<?php echo esc_attr( $tc_key ); ?>]"
                                                    class="zpp-bt-hex-text"
                                                    data-hex-for="<?php echo esc_attr( $tc_key ); ?>"
                                                    value="<?php echo esc_attr( $tc_val ); ?>"
                                                    maxlength="7"
                                                    spellcheck="false"
                                                />
                                            </div>
                                        <?php endif; ?>

                                        <?php if ( ! empty( $f['border_color_key'] ) ) :
                                            $bc_key = $f['border_color_key'];
                                            $bc_val = isset( $settings[ $bc_key ] ) ? $settings[ $bc_key ] : '#334155';
                                        ?>
                                            <!-- Dedicated Border Color Picker (01, 02, 03, 04, 12) -->
                                            <div class="zpp-bt-color-pill zpp-bt-border-pill" title="Dedicated Border Color">
                                                <span class="zpp-bt-mini-tag">Bdr</span>
                                                <input
                                                    type="color"
                                                    id="zpp-bt-<?php echo esc_attr( $bc_key ); ?>"
                                                    class="zpp-bt-color-picker"
                                                    data-key="<?php echo esc_attr( $bc_key ); ?>"
                                                    value="<?php echo esc_attr( $bc_val ); ?>"
                                                />
                                                <input
                                                    type="text"
                                                    name="zpp_builder_theme[<?php echo esc_attr( $bc_key ); ?>]"
                                                    class="zpp-bt-hex-text"
                                                    data-hex-for="<?php echo esc_attr( $bc_key ); ?>"
                                                    value="<?php echo esc_attr( $bc_val ); ?>"
                                                    maxlength="7"
                                                    spellcheck="false"
                                                />
                                            </div>
                                        <?php endif; ?>

                                        <?php if ( ! empty( $f['extra_color_keys'] ) && is_array( $f['extra_color_keys'] ) ) :
                                            foreach ( $f['extra_color_keys'] as $x_key => $x_tag ) :
                                                $x_val = isset( $settings[ $x_key ] ) ? $settings[ $x_key ] : '#94a3b8';
                                        ?>
                                            <div class="zpp-bt-color-pill zpp-bt-extra-pill" title="<?php echo esc_attr( $x_tag ); ?>">
                                                <span class="zpp-bt-mini-tag"><?php echo esc_html( $x_tag ); ?></span>
                                                <input type="color" id="zpp-bt-<?php echo esc_attr( $x_key ); ?>" class="zpp-bt-color-picker" data-key="<?php echo esc_attr( $x_key ); ?>" value="<?php echo esc_attr( $x_val ); ?>" />
                                                <input type="text" name="zpp_builder_theme[<?php echo esc_attr( $x_key ); ?>]" class="zpp-bt-hex-text" data-hex-for="<?php echo esc_attr( $x_key ); ?>" value="<?php echo esc_attr( $x_val ); ?>" maxlength="7" spellcheck="false" />
                                            </div>
                                        <?php endforeach; endif; ?>

                                        <?php if ( $o_key ) : ?>
                                            <div class="zpp-bt-opacity-inline" title="Background / Pattern Opacity (0–100%)">
                                                <span class="zpp-bt-op-lbl">Opacity</span>
                                                <input
                                                    type="range"
                                                    class="zpp-bt-opacity-range"
                                                    data-opacity-key="<?php echo esc_attr( $o_key ); ?>"
                                                    data-paired-color="<?php echo esc_attr( $c_key ); ?>"
                                                    min="0"
                                                    max="100"
                                                    step="1"
                                                    value="<?php echo esc_attr( $o_val ); ?>"
                                                />
                                                <div class="zpp-bt-opacity-num-box">
                                                    <input
                                                        type="number"
                                                        name="zpp_builder_theme[<?php echo esc_attr( $o_key ); ?>]"
                                                        class="zpp-bt-opacity-number"
                                                        data-opacity-num-for="<?php echo esc_attr( $o_key ); ?>"
                                                        data-paired-color="<?php echo esc_attr( $c_key ); ?>"
                                                        min="0"
                                                        max="100"
                                                        step="1"
                                                        value="<?php echo esc_attr( $o_val ); ?>"
                                                    />
                                                    <span>%</span>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- RIGHT: Side-by-Side Dynamic Interactive Builder Preview -->
                    <div class="zpp-bt-preview-pane">
                        <div class="zpp-bt-preview-card">
                            <div class="zpp-bt-preview-topbar">
                                <span class="zpp-bt-live-dot"></span>
                                <strong>Interactive Live Builder Preview</strong>
                                <span class="zpp-bt-preview-hint">Click accordion titles &amp; switches to test</span>
                            </div>

                            <!-- Interactive Mini-Builder Shell -->
                            <div id="zpp-bt-live-preview-shell" class="zappoppop-theme-preview-shell">
                                <!-- 01. Top Header Bar -->
                                <div class="zpp-pv-header" data-pv-role="header">
                                    <div class="zpp-pv-hdr-left">
                                        <button type="button" class="zpp-pv-icon-btn is-active" data-pv-role="field" title="Toggle Popup Settings">&#9881;</button>
                                        <input type="text" class="zpp-pv-title-inp" data-pv-role="field" value="Untitled Popup" readonly />
                                        <span class="zpp-pv-canvas-tab is-active" data-pv-role="header-text">Canvas 1 &times;</span>
                                        <span class="zpp-pv-canvas-tab" data-pv-role="header-text">Confirmation</span>
                                    </div>
                                    <div class="zpp-pv-hdr-right">
                                        <button type="button" class="zpp-pv-icon-btn" data-pv-role="field">&#128065;</button>
                                        <button type="button" class="zpp-pv-save-btn" data-pv-role="save-btn">Save As Template</button>
                                        <button type="button" class="zpp-pv-save-btn" data-pv-role="save-btn">Save</button>
                                        <button type="button" class="zpp-pv-save-btn zpp-pv-update-btn" title="Update (unsaved changes)">Update</button>
                                    </div>
                                </div>

                                <!-- 01 / 04. Secondary Element Toolbar -->
                                <div class="zpp-pv-subtoolbar" data-pv-role="ribbon-bar">
                                    <span class="zpp-pv-tool-btn zpp-pv-element-btn">&#9638;</span>
                                    <span class="zpp-pv-tool-btn zpp-pv-element-btn">A</span>
                                    <span class="zpp-pv-tool-btn zpp-pv-element-btn">&#9733;</span>
                                    <span class="zpp-pv-tool-btn zpp-pv-element-btn">&#9993;</span>
                                    <span class="zpp-pv-tool-btn zpp-pv-element-btn">&#9745;</span>
                                    <div class="zpp-pv-snap-wrap">
                                        <span class="zpp-pv-toggle is-on" data-pv-toggle="1"><i></i></span>
                                        <span class="zpp-pv-muted" data-pv-role="header-text">Snap Object</span>
                                    </div>
                                </div>

                                <!-- Main Workspace Body: Left Popup Settings | Center Stage | Right Layer Settings -->
                                <div class="zpp-pv-workspace">
                                    <!-- 02 & 03. Left Popup Settings Drawer + Generic Accordions -->
                                    <div class="zpp-pv-sidebar zpp-pv-left" data-pv-role="content">
                                        <div class="zpp-pv-sb-hdr" data-pv-role="border">
                                            <strong data-pv-role="content-text">Popup Settings</strong>
                                            <span data-pv-role="icon">&times;</span>
                                        </div>

                                        <!-- Accordion 1 (Open) -->
                                        <div class="zpp-pv-acc-item is-open">
                                            <div class="zpp-pv-acc-hdr" data-pv-role="accordion">
                                                <span data-pv-role="accordion-text">1. Canvas Settings</span>
                                                <i data-pv-role="icon">&minus;</i>
                                            </div>
                                            <div class="zpp-pv-acc-body" data-pv-role="content">
                                                <div class="zpp-pv-pills-row">
                                                    <span class="zpp-pv-pill is-active" data-pv-role="accent-bg">&#9679; Canvas 1</span>
                                                    <span class="zpp-pv-pill" data-pv-role="field">Confirmation</span>
                                                </div>
                                                <div class="zpp-pv-subtabs" data-pv-role="border">
                                                    <span class="is-active" data-pv-role="accent-text">General &amp; Color</span>
                                                    <span data-pv-role="muted">Canvas Logic</span>
                                                </div>
                                                <div class="zpp-pv-inp-box" data-pv-role="field">Canvas 1</div>
                                                <div class="zpp-pv-grid2">
                                                    <div class="zpp-pv-inp-box" data-pv-role="field">350 <small data-pv-role="muted">W</small></div>
                                                    <div class="zpp-pv-inp-box" data-pv-role="field">400 <small data-pv-role="muted">H</small></div>
                                                </div>
                                                <div class="zpp-pv-toggle-row" data-pv-role="border">
                                                    <span data-pv-role="content-text">Show Close Button</span>
                                                    <span class="zpp-pv-toggle" data-pv-toggle="1"><i></i></span>
                                                </div>
                                                <div class="zpp-pv-toggle-row" data-pv-role="border">
                                                    <span data-pv-role="content-text">Close on Backdrop</span>
                                                    <span class="zpp-pv-toggle is-on" data-pv-toggle="1"><i></i></span>
                                                </div>
                                                <div class="zpp-pv-inp-box zpp-pv-select" data-pv-role="field">
                                                    <span>Solid Background Color</span>
                                                    <i data-pv-role="icon">&#9662;</i>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Accordion 2 -->
                                        <div class="zpp-pv-acc-item">
                                            <div class="zpp-pv-acc-hdr" data-pv-role="accordion">
                                                <span data-pv-role="accordion-text">2. Display Triggers</span>
                                                <i data-pv-role="icon">+</i>
                                            </div>
                                            <div class="zpp-pv-acc-body" data-pv-role="content" style="display:none;">
                                                <div class="zpp-pv-toggle-row" data-pv-role="border">
                                                    <span data-pv-role="content-text">On Page Load</span>
                                                    <span class="zpp-pv-toggle is-on" data-pv-toggle="1"><i></i></span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Accordion 3 -->
                                        <div class="zpp-pv-acc-item">
                                            <div class="zpp-pv-acc-hdr" data-pv-role="accordion">
                                                <span data-pv-role="accordion-text">3. Autoresponder</span>
                                                <i data-pv-role="icon">+</i>
                                            </div>
                                            <div class="zpp-pv-acc-body" data-pv-role="content" style="display:none;">
                                                <div class="zpp-pv-inp-box" data-pv-role="field">Subject Line</div>
                                            </div>
                                        </div>

                                        <!-- Accordion 4 -->
                                        <div class="zpp-pv-acc-item">
                                            <div class="zpp-pv-acc-hdr" data-pv-role="accordion">
                                                <span data-pv-role="accordion-text">4. Double Opt-In</span>
                                                <i data-pv-role="icon">+</i>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 10, 11 & 13. Center Checkerboard Stage + Floating Layers Panel + Dashed Canvas Guide Line -->
                                    <div class="zpp-pv-stage" data-pv-role="stage">
                                        <!-- Floating Layers Panel -->
                                        <div class="zpp-pv-layers-widget" data-pv-role="layers-frame">
                                            <div class="zpp-pv-layers-hdr" data-pv-role="layers-title">
                                                <span data-pv-role="layers-title-text">&#9776; Layers Panel</span>
                                                <i data-pv-role="icon">&#9652;</i>
                                            </div>
                                            <div class="zpp-pv-layers-body" data-pv-role="layers-area">
                                                <div class="zpp-pv-layer-item is-active zpp-pv-layer-selected">
                                                    <span data-pv-role="layers-area-text">&#9675; Shape 1</span>
                                                    <i data-pv-role="icon">&#128065; &#128465;</i>
                                                </div>
                                                <div class="zpp-pv-layer-item" data-pv-role="layers-row">
                                                    <span data-pv-role="layers-area-text">&#9633; Text 2</span>
                                                    <i data-pv-role="icon">&#128065; &#128465;</i>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Isolated Popup Canvas with Dedicated Dashed Guide Line (#13) -->
                                        <div class="zpp-pv-canvas-wrap">
                                            <div class="zpp-pv-canvas-box" data-pv-role="canvas-guide">
                                                <span class="zpp-pv-canvas-close">&times;</span>
                                                <div class="zpp-pv-shape-el" data-pv-role="selection"></div>
                                            </div>
                                            <div class="zpp-pv-dim-readout" data-pv-role="muted">350 x 400</div>
                                        </div>

                                        <!-- Floating Bottom Alignment Bar -->
                                        <div class="zpp-pv-float-bar" data-pv-role="header">
                                            <span data-pv-role="icon">&#8676;</span>
                                            <span data-pv-role="icon">&#8596;</span>
                                            <span data-pv-role="icon">&#8677;</span>
                                            <span data-pv-role="icon">&#9664;</span>
                                            <span data-pv-role="icon">&#9654;</span>
                                            <span data-pv-role="icon">&#9650;</span>
                                            <span data-pv-role="icon">&#9660;</span>
                                        </div>
                                    </div>

                                    <!-- 02, 03, 04. Right Layer Settings Sidebar -->
                                    <div class="zpp-pv-sidebar zpp-pv-right" data-pv-role="content">
                                        <div class="zpp-pv-sb-hdr" data-pv-role="border">
                                            <strong data-pv-role="content-text">&#9881; Layer Settings</strong>
                                            <span data-pv-role="icon">&times;</span>
                                        </div>
                                        <div class="zpp-pv-subtabs" data-pv-role="border">
                                            <span class="is-active" data-pv-role="accent-text">Content</span>
                                            <span data-pv-role="muted">Style</span>
                                            <span data-pv-role="muted">Logic</span>
                                        </div>
                                        <div class="zpp-pv-right-body">
                                                <label class="zpp-pv-lbl" data-pv-role="content-text">Layer Name</label>
                                                <div class="zpp-pv-inp-box" data-pv-role="field">Shape 1</div>
                                            <div class="zpp-pv-acc-item zpp-pv-blk is-open">
                                                <div class="zpp-pv-acc-hdr" data-pv-role="accordion"><span data-pv-role="accordion-text">Shape / Vector Geometry</span><i data-pv-role="accordion-text">&#8964;</i></div>
                                                <div class="zpp-pv-acc-body" data-pv-role="area">
                                                    <span class="zpp-pv-lbl" data-pv-role="area-text">SVG Shape Preset</span>
                                                    <div class="zpp-pv-inp-box zpp-pv-select" data-pv-role="field"><span>Square</span><i data-pv-role="icon">&#9662;</i></div>
                                                    <div class="zpp-pv-grid2"><div><span class="zpp-pv-lbl" data-pv-role="area-text">Width (px)</span><div class="zpp-pv-inp-box" data-pv-role="field">120</div></div><div><span class="zpp-pv-lbl" data-pv-role="area-text">Height (px)</span><div class="zpp-pv-inp-box" data-pv-role="field">120</div></div></div>
                                                    <span class="zpp-pv-lbl" data-pv-role="area-text">Fill Color</span>
                                                    <div class="zpp-pv-inp-box" data-pv-role="field"><span class="zpp-pv-chip"></span><span>#3b82f6</span></div>
                                                    <span class="zpp-pv-lbl" data-pv-role="area-text">Link URL</span>
                                                    <div class="zpp-pv-inp-box" data-pv-role="field"><span>https://example.com</span></div>
                                                    <div class="zpp-pv-sw-row"><span class="zpp-pv-toggle is-on" data-pv-toggle><i></i></span><span data-pv-role="area-text">Open in new tab</span></div>
                                                    <div class="zpp-pv-sw-row"><span class="zpp-pv-toggle" data-pv-toggle><i></i></span><span data-pv-role="area-text">rel="nofollow"</span></div>
                                                </div>
                                            </div>
                                            <div class="zpp-pv-acc-item zpp-pv-blk">
                                                <div class="zpp-pv-acc-hdr" data-pv-role="accordion"><span data-pv-role="accordion-text">Animation &amp; Behavior</span><i data-pv-role="accordion-text">&#8964;</i></div>
                                                <div class="zpp-pv-acc-body" data-pv-role="area" style="display:none">
                                                    <span class="zpp-pv-lbl" data-pv-role="area-text">Animation</span>
                                                    <div class="zpp-pv-inp-box zpp-pv-select" data-pv-role="field"><span>None</span><i data-pv-role="icon">&#9662;</i></div>
                                                    <div class="zpp-pv-grid2"><div><span class="zpp-pv-lbl" data-pv-role="area-text">Duration (ms)</span><div class="zpp-pv-inp-box" data-pv-role="field">1000</div></div><div><span class="zpp-pv-lbl" data-pv-role="area-text">Start Delay (ms)</span><div class="zpp-pv-inp-box" data-pv-role="field">0</div></div></div>
                                                </div>
                                            </div>
                                            <div class="zpp-pv-acc-item zpp-pv-blk">
                                                <div class="zpp-pv-acc-hdr" data-pv-role="accordion"><span data-pv-role="accordion-text">Coordinates &amp; Bounds</span><i data-pv-role="accordion-text">&#8964;</i></div>
                                                <div class="zpp-pv-acc-body" data-pv-role="area" style="display:none">
                                                    <div class="zpp-pv-grid2"><div class="zpp-pv-inp-box" data-pv-role="field">75</div><div class="zpp-pv-inp-box" data-pv-role="field">170</div></div>
                                                </div>
                                            </div>
                                                <div class="zpp-pv-reset" data-pv-role="field">&#8634; Reset Content Settings</div>
                                                                                    </div>
                                    </div>
                                </div>
                            </div>

                            <div id="zpp-bt-toast" class="zpp-bt-toast" aria-live="polite"></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }

        public static function output_theme_builder_mount_template() {
            $page = self::get_current_page_slug();
            if ( $page !== 'zappoppop-theme' ) {
                return;
            }
            echo '<template id="zappoppop-builder-theme-template">';
            self::render_settings_panel();
            echo '</template>';
        }
    }

    ZapPopPop_Builder_Theme::init();
}
