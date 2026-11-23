<?php
/**
 * Per-side border width and per-corner radius (ZPP_BORDER_SIDES).
 *
 * Model keys: borderWidthTop/Right/Bottom/Left and borderRadiusTl/Tr/Br/Bl (the existing borderWidth / borderRadius stay the "all sides" value).
 * Rule: when any side / corner key is above 0 the four values are used, otherwise the all-sides value applies, so older layers render unchanged.
 * The keys are whitelisted as numbers in ZapPopPop_Ajax_Element_Registry; every value here is re-clamped to an integer before it reaches CSS.
 *
 * @package ZapPopPop
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ZapPopPop_Box_Style {

	const WIDTH_KEYS  = array( 'Top', 'Right', 'Bottom', 'Left' );
	const RADIUS_KEYS = array( 'Tl', 'Tr', 'Br', 'Bl' );

	private static function sides( array $el, $base, array $keys, $max ) {
		$master = self::num( isset( $el[ $base ] ) ? $el[ $base ] : 0, $max );
		$out    = array();
		$used   = false;
		foreach ( $keys as $k ) {
			$v     = self::num( isset( $el[ $base . $k ] ) ? $el[ $base . $k ] : 0, $max );
			$out[] = $v;
			if ( $v > 0 ) {
				$used = true;
			}
		}
		return $used ? $out : array( $master, $master, $master, $master );
	}

	private static function num( $v, $max ) {
		if ( is_array( $v ) || ! is_numeric( $v ) ) {
			return 0;
		}
		return max( 0, min( (int) $max, (int) $v ) );
	}

	public static function widths( array $el ) {
		return self::sides( $el, 'borderWidth', self::WIDTH_KEYS, 40 );
	}

	public static function radii( array $el ) {
		return self::sides( $el, 'borderRadius', self::RADIUS_KEYS, 200 );
	}

	/** Largest border side: decides whether a border is drawn at all. */
	public static function max_width( array $el ) {
		return max( self::widths( $el ) );
	}

	/** `border-width:` declaration when the four sides differ, otherwise an empty string (the shorthand already covers it). */
	public static function width_css( array $el ) {
		$w = self::widths( $el );
		if ( count( array_unique( $w ) ) < 2 ) {
			return '';
		}
		return 'border-width:' . implode( 'px ', $w ) . 'px';
	}

	/** Value for `border-radius:` (one value when uniform, four otherwise). */
	public static function radius_css( array $el ) {
		$r = self::radii( $el );
		return ( count( array_unique( $r ) ) < 2 ) ? $r[0] . 'px' : implode( 'px ', $r ) . 'px';
	}

	/** Every per-side / per-corner key (cleared when the Border switch is off). */
	public static function keys() {
		$out = array();
		foreach ( self::WIDTH_KEYS as $k ) {
			$out[] = 'borderWidth' . $k;
		}
		foreach ( self::RADIUS_KEYS as $k ) {
			$out[] = 'borderRadius' . $k;
		}
		return $out;
	}
}
