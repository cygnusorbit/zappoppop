<?php
/**
 * Decomposed Universal Element Color & Opacity Lifecycle Module.
 *
 * Enables adjusting Color, Color Opacity, Background Color, Background Opacity,
 * and Element Opacity (0% - 100%) on every element in /admin.php?page=zappoppop-builder
 * with harmonized UI styling on /admin.php?page=zappoppop-theme > Builder.
 *
 * @package ZapPopPop
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'ZapPopPop_Element_Opacity' ) ) {

	class ZapPopPop_Element_Opacity {

		/**
		 * Initialize hooks across schema, admin inspector, theme builder page, AJAX save, and frontend render.
		 */
		public static function init() {
			add_filter( 'zappoppop_element_defaults', array( __CLASS__, 'register_element_defaults' ), 10, 2 );
			add_filter( 'zappoppop_layer_defaults', array( __CLASS__, 'register_element_defaults' ), 10, 2 );
			add_filter( 'zappoppop_allowed_layer_keys', array( __CLASS__, 'whitelist_layer_keys' ), 10, 1 );
			add_filter( 'zappoppop_sanitize_layer', array( __CLASS__, 'sanitize_layer_opacity' ), 10, 2 );
			add_filter( 'zappoppop_pre_save_popup_settings', array( __CLASS__, 'sanitize_popup_payload' ), 10, 1 );
			add_filter( 'zappoppop_render_layer_styles', array( __CLASS__, 'apply_frontend_layer_styles' ), 20, 2 );
			add_filter( 'zappoppop_render_element_html', array( __CLASS__, 'apply_frontend_element_html' ), 20, 2 );
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_assets' ), 20 );
			add_action( 'admin_footer', array( __CLASS__, 'print_fallback_builder_loader' ), 99 );
		}

		/**
		 * Return all element types supporting universal color & opacity controls.
		 *
		 * @return array
		 */
		public static function get_supported_element_types() {
			$core_types = array(
				'text',
				'heading',
				'button',
				'image',
				'shape',
				'input',
				'form',
				'icon',
				'html',
				'custom_html',
				'close',
				'close_button',
				'video',
				'countdown',
				'divider',
			);
			return apply_filters( 'zappoppop_element_opacity_supported_types', $core_types );
		}

		/**
		 * Clamp opacity percentage to integer 0..100.
		 *
		 * @param mixed $value    Raw opacity value.
		 * @param int   $fallback Default percentage if unset.
		 * @return int
		 */
		public static function clamp_opacity( $value, $fallback = 100 ) {
			if ( null === $value || '' === $value ) {
				return (int) $fallback;
			}
			$num = is_numeric( $value ) ? (float) $value : (float) $fallback;
			if ( $num > 0 && $num <= 1 && is_string( $value ) && false !== strpos( $value, '.' ) ) {
				$num = round( $num * 100 );
			}
			return max( 0, min( 100, (int) round( $num ) ) );
		}

		/**
		 * Extract alpha percentage (0..100) from rgba() or 8-digit hex string.
		 *
		 * @param string $color Color string.
		 * @return int|null
		 */
		public static function extract_alpha_percent( $color ) {
			if ( ! is_string( $color ) ) {
				return null;
			}
			$color = trim( $color );
			if ( preg_match( '/^rgba\s*\(\s*\d+\s*,\s*\d+\s*,\s*\d+\s*,\s*([0-9.]+)\s*\)$/i', $color, $m ) ) {
				$a = (float) $m[1];
				if ( $a <= 1.0 ) {
					return max( 0, min( 100, (int) round( $a * 100 ) ) );
				}
				return max( 0, min( 100, (int) round( $a ) ) );
			}
			if ( preg_match( '/^#([a-f0-9]{8})$/i', $color, $m ) ) {
				$alpha_byte = hexdec( substr( $m[1], 6, 2 ) );
				return max( 0, min( 100, (int) round( ( $alpha_byte / 255 ) * 100 ) ) );
			}
			return null;
		}

		/**
		 * Convert any valid CSS hex/rgb/rgba color + 0..100 opacity into cross-browser rgba().
		 *
		 * @param string $color   Base color string.
		 * @param int    $opacity Opacity percentage (0..100).
		 * @return string
		 */
		public static function color_with_opacity( $color, $opacity = 100 ) {
			$opacity = self::clamp_opacity( $opacity, 100 );
			$alpha   = round( $opacity / 100, 4 );

			if ( ! is_string( $color ) || '' === trim( $color ) ) {
				return '';
			}

			$raw = trim( $color );
			if ( 'transparent' === strtolower( $raw ) ) {
				return 'rgba(0, 0, 0, 0)';
			}

			if ( preg_match( '/^rgba?\s*\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})(?:\s*,\s*[0-9.]+)?\s*\)$/i', $raw, $m ) ) {
				$r = max( 0, min( 255, (int) $m[1] ) );
				$g = max( 0, min( 255, (int) $m[2] ) );
				$b = max( 0, min( 255, (int) $m[3] ) );
				return sprintf( 'rgba(%d, %d, %d, %s)', $r, $g, $b, rtrim( rtrim( sprintf( '%.4F', $alpha ), '0' ), '.' ) );
			}

			$hex = ltrim( $raw, '#' );
			if ( preg_match( '/^[a-f0-9]{3,4}$/i', $hex ) ) {
				$r = hexdec( str_repeat( $hex[0], 2 ) );
				$g = hexdec( str_repeat( $hex[1], 2 ) );
				$b = hexdec( str_repeat( $hex[2], 2 ) );
				return sprintf( 'rgba(%d, %d, %d, %s)', $r, $g, $b, rtrim( rtrim( sprintf( '%.4F', $alpha ), '0' ), '.' ) );
			}
			if ( preg_match( '/^[a-f0-9]{6}([a-f0-9]{2})?$/i', $hex ) ) {
				$r = hexdec( substr( $hex, 0, 2 ) );
				$g = hexdec( substr( $hex, 2, 2 ) );
				$b = hexdec( substr( $hex, 4, 2 ) );
				return sprintf( 'rgba(%d, %d, %d, %s)', $r, $g, $b, rtrim( rtrim( sprintf( '%.4F', $alpha ), '0' ), '.' ) );
			}

			return $raw;
		}

		/**
		 * Register default color & opacity fields in element schema models.
		 *
		 * @param array  $defaults Existing defaults.
		 * @param string $type     Element type.
		 * @return array
		 */
		public static function register_element_defaults( $defaults, $type = '' ) {
			if ( ! is_array( $defaults ) ) {
				$defaults = array();
			}
			if ( ! isset( $defaults['opacity'] ) ) {
				$defaults['opacity'] = 100;
			}
			if ( ! isset( $defaults['color_opacity'] ) ) {
				$defaults['color_opacity'] = 100;
			}
			if ( ! isset( $defaults['background_opacity'] ) ) {
				$defaults['background_opacity'] = 100;
			}
			return $defaults;
		}

		/**
		 * Whitelist color & opacity keys for AJAX persistence.
		 *
		 * @param array $keys Allowed layer property keys.
		 * @return array
		 */
		public static function whitelist_layer_keys( $keys ) {
			if ( ! is_array( $keys ) ) {
				$keys = array();
			}
			$extra_keys = array(
				'color',
				'text_color',
				'fill',
				'fill_color',
				'background_color',
				'bg_color',
				'opacity',
				'element_opacity',
				'color_opacity',
				'text_opacity',
				'background_opacity',
				'bg_opacity',
			);
			foreach ( $extra_keys as $k ) {
				if ( ! in_array( $k, $keys, true ) ) {
					$keys[] = $k;
				}
			}
			return $keys;
		}

		/**
		 * Sanitize a single layer's color, background color, and opacity properties.
		 *
		 * @param array  $layer Layer data.
		 * @param string $type  Optional element type.
		 * @return array
		 */
		public static function sanitize_layer_opacity( $layer, $type = '' ) {
			if ( ! is_array( $layer ) ) {
				return $layer;
			}

			$raw_el_op = isset( $layer['opacity'] ) ? $layer['opacity'] : ( isset( $layer['element_opacity'] ) ? $layer['element_opacity'] : 100 );
			$layer['opacity'] = self::clamp_opacity( $raw_el_op, 100 );

			$color_src = isset( $layer['color'] ) ? $layer['color'] : ( isset( $layer['text_color'] ) ? $layer['text_color'] : ( isset( $layer['fill'] ) ? $layer['fill'] : '' ) );
			if ( '' !== $color_src ) {
				$layer['color'] = sanitize_text_field( (string) $color_src );
			}

			$raw_color_op = isset( $layer['color_opacity'] ) ? $layer['color_opacity'] : ( isset( $layer['text_opacity'] ) ? $layer['text_opacity'] : null );
			if ( null === $raw_color_op && ! empty( $layer['color'] ) ) {
				$extracted    = self::extract_alpha_percent( $layer['color'] );
				$raw_color_op = ( null !== $extracted ) ? $extracted : 100;
			}
			$layer['color_opacity'] = self::clamp_opacity( $raw_color_op, 100 );

			$bg_src = isset( $layer['background_color'] ) ? $layer['background_color'] : ( isset( $layer['bg_color'] ) ? $layer['bg_color'] : '' );
			if ( '' !== $bg_src ) {
				$layer['background_color'] = sanitize_text_field( (string) $bg_src );
			}

			$raw_bg_op = isset( $layer['background_opacity'] ) ? $layer['background_opacity'] : ( isset( $layer['bg_opacity'] ) ? $layer['bg_opacity'] : null );
			if ( null === $raw_bg_op && ! empty( $bg_src ) ) {
				$extracted_bg = self::extract_alpha_percent( $bg_src );
				$raw_bg_op    = ( null !== $extracted_bg ) ? $extracted_bg : 100;
			}
			$layer['background_opacity'] = self::clamp_opacity( $raw_bg_op, 100 );

			if ( isset( $layer['style'] ) && is_array( $layer['style'] ) ) {
				$layer['style']['opacity']            = $layer['opacity'];
				$layer['style']['color_opacity']      = $layer['color_opacity'];
				$layer['style']['background_opacity'] = $layer['background_opacity'];
				if ( ! empty( $layer['color'] ) ) {
					$layer['style']['color'] = self::color_with_opacity( $layer['color'], $layer['color_opacity'] );
				}
				if ( ! empty( $layer['background_color'] ) ) {
					$layer['style']['background_color'] = self::color_with_opacity( $layer['background_color'], $layer['background_opacity'] );
				}
			}

			return $layer;
		}

		/**
		 * Sanitize all layers inside popup save payload.
		 *
		 * @param array $settings Popup data payload.
		 * @return array
		 */
		public static function sanitize_popup_payload( $settings ) {
			if ( ! is_array( $settings ) ) {
				return $settings;
			}
			foreach ( array( 'layers', 'elements', 'items' ) as $collection_key ) {
				if ( isset( $settings[ $collection_key ] ) && is_array( $settings[ $collection_key ] ) ) {
					foreach ( $settings[ $collection_key ] as $idx => $layer ) {
						$settings[ $collection_key ][ $idx ] = self::sanitize_layer_opacity( $layer );
					}
				}
			}
			return $settings;
		}

		/**
		 * Filter frontend CSS style declarations for a rendered layer.
		 *
		 * @param array|string $styles Existing styles.
		 * @param array        $layer  Layer configuration.
		 * @return array|string
		 */
		public static function apply_frontend_layer_styles( $styles, $layer ) {
			if ( ! is_array( $layer ) ) {
				return $styles;
			}

			$layer    = self::sanitize_layer_opacity( $layer );
			$color    = isset( $layer['color'] ) ? $layer['color'] : ( isset( $layer['text_color'] ) ? $layer['text_color'] : '' );
			$bg_color = isset( $layer['background_color'] ) ? $layer['background_color'] : ( isset( $layer['bg_color'] ) ? $layer['bg_color'] : '' );

			$rgba_color = '' !== $color ? self::color_with_opacity( $color, $layer['color_opacity'] ) : '';
			$rgba_bg    = '' !== $bg_color ? self::color_with_opacity( $bg_color, $layer['background_opacity'] ) : '';
			$el_alpha   = round( $layer['opacity'] / 100, 4 );

			if ( is_array( $styles ) ) {
				if ( '' !== $rgba_color ) {
					$styles['color'] = $rgba_color;
				}
				if ( '' !== $rgba_bg ) {
					$styles['background-color'] = $rgba_bg;
				}
				$styles['opacity']             = (string) $el_alpha;
				$styles['--zpp-el-opacity']    = (string) $el_alpha;
				$styles['--zpp-color-opacity'] = (string) round( $layer['color_opacity'] / 100, 4 );
				$styles['--zpp-bg-opacity']    = (string) round( $layer['background_opacity'] / 100, 4 );
				return $styles;
			}

			$extra = 'opacity:' . esc_attr( (string) $el_alpha ) . ';';
			if ( '' !== $rgba_color ) {
				$extra .= 'color:' . esc_attr( $rgba_color ) . ';';
			}
			if ( '' !== $rgba_bg ) {
				$extra .= 'background-color:' . esc_attr( $rgba_bg ) . ';';
			}
			$extra .= '--zpp-el-opacity:' . esc_attr( (string) $el_alpha ) . ';';
			$extra .= '--zpp-color-opacity:' . esc_attr( (string) round( $layer['color_opacity'] / 100, 4 ) ) . ';';
			$extra .= '--zpp-bg-opacity:' . esc_attr( (string) round( $layer['background_opacity'] / 100, 4 ) ) . ';';

			return rtrim( (string) $styles, '; ' ) . ( '' !== $styles ? ';' : '' ) . $extra;
		}

		/**
		 * Ensure rendered frontend element markup reflects color, background RGBA, and opacity values.
		 *
		 * @param string $html  Rendered HTML.
		 * @param array  $layer Layer configuration.
		 * @return string
		 */
		public static function apply_frontend_element_html( $html, $layer ) {
			if ( ! is_string( $html ) || '' === $html || ! is_array( $layer ) ) {
				return $html;
			}

			$layer    = self::sanitize_layer_opacity( $layer );
			$color    = isset( $layer['color'] ) ? $layer['color'] : ( isset( $layer['text_color'] ) ? $layer['text_color'] : '' );
			$bg_color = isset( $layer['background_color'] ) ? $layer['background_color'] : ( isset( $layer['bg_color'] ) ? $layer['bg_color'] : '' );
			$el_alpha = round( $layer['opacity'] / 100, 4 );

			$decls = array( 'opacity:' . esc_attr( (string) $el_alpha ) );
			if ( '' !== $color ) {
				$decls[] = 'color:' . esc_attr( self::color_with_opacity( $color, $layer['color_opacity'] ) );
			}
			if ( '' !== $bg_color ) {
				$decls[] = 'background-color:' . esc_attr( self::color_with_opacity( $bg_color, $layer['background_opacity'] ) );
			}

			if ( false !== stripos( $html, '<svg' ) && isset( $layer['color_opacity'] ) ) {
				$fill_alpha = round( $layer['color_opacity'] / 100, 4 );
				$svg_extra  = 'fill-opacity:' . esc_attr( (string) $fill_alpha ) . ';';
				if ( '' !== $color ) {
					$svg_extra .= 'fill:' . esc_attr( self::color_with_opacity( $color, $layer['color_opacity'] ) ) . ';';
				}
				$html = preg_replace(
					'/<svg\b([^>]*)>/i',
					'<svg$1 style="' . $svg_extra . '">',
					$html,
					1
				);
			}

			$inline_addition = implode( ';', $decls ) . ';';
			if ( preg_match( '/^(\s*<[a-z0-9-]+\b[^>]*?)\bstyle=(["\'])(.*?)\2/is', $html ) ) {
				$html = preg_replace(
					'/^(\s*<[a-z0-9-]+\b[^>]*?)\bstyle=(["\'])(.*?)\2/is',
					'$1style=$2$3;' . $inline_addition . '$2',
					$html,
					1
				);
			} else {
				$html = preg_replace(
					'/^(\s*<[a-z0-9-]+\b)/i',
					'$1 style="' . $inline_addition . '"',
					$html,
					1
				);
			}

			return $html;
		}

		/**
		 * Enqueue harmonized Builder & Theme Inspector opacity assets.
		 *
		 * @param string $hook Current admin screen hook.
		 */
		public static function enqueue_admin_assets( $hook = '' ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
			if ( false === strpos( (string) $hook, 'zappoppop' ) && false === strpos( $page, 'zappoppop' ) ) {
				return;
			}

			$base_url = plugin_dir_url( dirname( __FILE__ ) );
			$version  = defined( 'ZAPPOPPOP_VERSION' ) ? ZAPPOPPOP_VERSION : '1.0.00';

			wp_enqueue_style(
				'zappoppop-element-opacity',
				$base_url . 'admin/css/zappoppop-element-opacity.css',
				array(),
				$version . '.6c'
			);

			wp_enqueue_script(
				'zappoppop-element-opacity',
				$base_url . 'admin/js/zappoppop-element-opacity.js',
				array( 'jquery' ),
				$version . '.6c',
				true
			);

			wp_localize_script(
				'zappoppop-element-opacity',
				'zappoppopElementOpacityConfig',
				array(
					'supportedTypes' => self::get_supported_element_types(),
					'defaultOpacity' => 100,
					'labels'         => array(
						'sectionTitle'      => __( 'Color & Opacity (All Elements)', 'zappoppop' ),
						'elementOpacity'    => __( 'Element Opacity', 'zappoppop' ),
						'color'             => __( 'Element Color', 'zappoppop' ),
						'colorOpacity'      => __( 'Color Opacity', 'zappoppop' ),
						'backgroundColor'   => __( 'Background Color', 'zappoppop' ),
						'backgroundOpacity' => __( 'Background Opacity', 'zappoppop' ),
					),
				)
			);
		}

		/**
		 * Fallback loader in admin_footer in case custom builder views render outside standard script queues.
		 */
		public static function print_fallback_builder_loader() {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
			if ( false === strpos( $page, 'zappoppop' ) ) {
				return;
			}
			if ( ! wp_script_is( 'zappoppop-element-opacity', 'done' ) ) {
				self::enqueue_admin_assets( $page );
				wp_print_styles( array( 'zappoppop-element-opacity' ) );
				wp_print_scripts( array( 'zappoppop-element-opacity' ) );
			}
		}
	}

	ZapPopPop_Element_Opacity::init();
}
