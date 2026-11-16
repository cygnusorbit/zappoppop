<?php
/**
 * ZapPopPop Security & AJAX Permission Controller
 * Autonomous Decomposed Modular Architecture
 *
 * @package ZapPopPop
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ZapPopPop_Security {

    /**
     * Singleton instance.
     * @var ZapPopPop_Security|null
     */
    protected static $instance = null;

    /**
     * Get instance.
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_security_nonce' ], 1 );
    }

    /**
     * Localize administrative security nonce & intercept admin AJAX dispatches.
     */
    public function enqueue_admin_security_nonce() {
        $nonce = wp_create_nonce( 'zappoppop_nonce' );

        // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- handle without a file, it only carries an inline script
        wp_register_script( 'zappoppop-security-bridge', false, [ 'jquery' ], null, false );
        wp_enqueue_script( 'zappoppop-security-bridge' );

        $script = '
            window.zappoppop_nonce = ' . wp_json_encode( $nonce ) . ';
            window.zappoppopSecurity = { nonce: ' . wp_json_encode( $nonce ) . ' };
            if ( typeof jQuery !== "undefined" ) {
                jQuery( document ).ajaxSend( function( event, xhr, settings ) {
                    if ( settings.url && settings.url.indexOf( "admin-ajax.php" ) !== -1 ) {
                        if ( typeof settings.data === "string" && settings.data.indexOf( "action=zappoppop_" ) !== -1 ) {
                            if ( settings.data.indexOf( "nonce=" ) === -1 && settings.data.indexOf( "_ajax_nonce=" ) === -1 && settings.data.indexOf( "security=" ) === -1 ) {
                                var separator = settings.data.length ? "&" : "";
                                settings.data += separator + "nonce=" + encodeURIComponent( window.zappoppop_nonce );
                            }
                        }
                    }
                } );
            }
        ';
        wp_add_inline_script( 'zappoppop-security-bridge', $script, 'before' );
    }

    /**
     * Resolve the visitor IP. Proxy headers are opt-in via the zappoppop_client_ip_headers filter.
     */
    public static function get_client_ip() {
        $headers = (array) apply_filters( 'zappoppop_client_ip_headers', [ 'REMOTE_ADDR' ] );
        foreach ( $headers as $h ) {
            if ( empty( $_SERVER[ $h ] ) ) {
                continue;
            }
            $raw  = sanitize_text_field( wp_unslash( $_SERVER[ $h ] ) );
            $list = array_map( 'trim', explode( ',', $raw ) );
            foreach ( $list as $candidate ) {
                if ( filter_var( $candidate, FILTER_VALIDATE_IP ) ) {
                    return $candidate;
                }
            }
        }
        return '';
    }

    /**
     * Authoritative backend permission and nonce verifier for administrative actions.
     *
     * @param string $cap Required capability.
     * @return bool True on success, terminates on failure.
     */
    public static function verify_admin( $cap = 'manage_options' ) {
        if ( ! current_user_can( $cap ) ) {
            wp_send_json_error( [ 'message' => esc_html__( 'Unauthorized permission.', 'zappoppop' ) ], 403 );
            wp_die();
        }

        $verified = check_ajax_referer( 'zappoppop_nonce', 'nonce', false )
            || check_ajax_referer( 'zappoppop_nonce', '_ajax_nonce', false )
            || check_ajax_referer( 'zappoppop_admin', 'nonce', false )
            || check_ajax_referer( 'zappoppop_nonce', 'security', false )
            || check_ajax_referer( 'zappoppop_builder_nonce', 'nonce', false )
            || check_ajax_referer( 'zappoppop_admin_nonce', 'nonce', false )
            || check_ajax_referer( 'zappoppop_settings_nonce', 'nonce', false );

        if ( ! $verified ) {
            wp_send_json_error( [ 'message' => esc_html__( 'Invalid or expired security token.', 'zappoppop' ) ], 403 );
            wp_die();
        }

        return true;
    }
}

if ( ! function_exists( 'zappoppop_get_client_ip' ) ) {
    function zappoppop_get_client_ip() {
        return ZapPopPop_Security::get_client_ip();
    }
}

// Bootstrap
ZapPopPop_Security::get_instance();
