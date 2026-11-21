<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('ZapPopPop_Sanitizer')) {
    require_once ZAPPOPPOP_PATH . 'includes/class-zappoppop-sanitizer.php';
}

/**
 * ZapPopPop Front Preview: stages unsaved builder state and renders it on the real frontend
 * (admins only). Used by the builder "Preview" iframe and "Preview on Homepage" button.
 */
class ZapPopPop_Front_Preview {
    protected $renderer;
    protected $uid = '';
    protected $config = null;

    public function __construct(ZapPopPop_Front_Renderer $renderer) {
        $this->renderer = $renderer;
        add_action('wp_ajax_zappoppop_preview_stage', [$this, 'ajax_stage']);
        add_action('wp', [$this, 'maybe_boot'], 1);
        add_action('template_redirect', [$this, 'maybe_bare_template'], 1); // ZAPPOPPOP_PREVIEW_BARE_V1
    }

    protected function transient_key($uid) {
        return 'zappoppop_prev_' . get_current_user_id() . '_' . md5($uid);
    }

    public function ajax_stage() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized action.', 'zappoppop')]);
        }
        $nonce = isset($_REQUEST['nonce']) ? sanitize_text_field(wp_unslash($_REQUEST['nonce'])) : '';
        if (!wp_verify_nonce($nonce, 'zappoppop_builder_nonce') && !wp_verify_nonce($nonce, 'zappoppop_admin_nonce')) {
            wp_send_json_error(['message' => __('Security check failed. Please refresh the page.', 'zappoppop')]);
        }

        $uid = isset($_POST['uid']) ? sanitize_key(wp_unslash($_POST['uid'])) : '';
        if ($uid === '') {
            $uid = 'draft';
        }
        $raw     = ZapPopPop_Sanitizer::post_json_string('data');
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $decoded = ZapPopPop_Sanitizer::popup_payload($decoded);
        }
        if (!is_array($decoded)) {
            wp_send_json_error(['message' => __('Malformed preview payload.', 'zappoppop')]);
        }

        set_transient($this->transient_key($uid), wp_json_encode($decoded), HOUR_IN_SECONDS);

        $url = add_query_arg([
            'zappoppop_preview' => $uid,
            'zappoppop_draft'   => 1,
            'zappoppop_t'       => time(),
        ], home_url('/'));

        wp_send_json_success([
            'uid'       => $uid,
            'url'       => $url,
            'frame_url' => add_query_arg('zappoppop_frame', 1, $url),
        ]);
    }

    public function maybe_boot() {
        if (is_admin() || empty($_GET['zappoppop_preview'])) {
            return;
        }
        if (!is_user_logged_in() || !current_user_can('manage_options')) {
            return;
        }

        $uid    = sanitize_key(wp_unslash($_GET['zappoppop_preview']));
        $config = null;

        if (!empty($_GET['zappoppop_draft'])) {
            $raw = get_transient($this->transient_key($uid));
            if (is_string($raw) && $raw !== '') {
                $config = json_decode($raw, true);
            }
        }
        if (!is_array($config)) {
            global $wpdb;
            $table = $wpdb->prefix . 'zappoppop_items';
            /* phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- table name is built from $wpdb->prefix and every value is prepared */ $row   = $wpdb->get_row($wpdb->prepare("SELECT data FROM {$table} WHERE uid = %s", $uid), ARRAY_A);
            if ($row && !empty($row['data'])) {
                $config = json_decode($row['data'], true);
            }
        }
        if (!is_array($config)) {
            return;
        }

        $this->uid    = $uid;
        $this->config = $config;

        if (!defined('DONOTCACHEPAGE')) {
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound
        define('DONOTCACHEPAGE', true);
        }
        nocache_headers();
        if (!empty($_GET['zappoppop_frame'])) {
            show_admin_bar(false);
            add_filter('show_admin_bar', '__return_false');
        }

        // Preview shows ONLY the previewed popup.
        add_filter('zappoppop_suppress_front_popups', '__return_true');
        add_action('wp_footer', [$this, 'render'], 60);
        /* ZAPPOPPOP_PREVIEW_ICON_CSS: admin bar is off in frame mode, so load icon fonts explicitly */
        add_action('wp_enqueue_scripts', function () {
            wp_enqueue_style('dashicons');
            wp_enqueue_style('zappoppop-font-awesome', ZapPopPop_Libraries::fa_url(), array(), '6.5.1');
            wp_enqueue_script('zappoppop-front-preview', ZAPPOPPOP_URL . 'public/js/front-preview.js', array(), defined('ZAPPOPPOP_VERSION') ? ZAPPOPPOP_VERSION : '1.0', true);
            wp_add_inline_script('zappoppop-front-preview', 'window.zappoppopPreview = ' . wp_json_encode(array('uid' => (string) $this->uid)) . ';', 'before');
        }, 20);
    }

    /**
     * Frame mode: replace the theme template with a bare document so only the popup loads.
     */
    public function maybe_bare_template() {
        if (!$this->uid || !is_array($this->config) || empty($_GET['zappoppop_frame'])) {
            return;
        }
        status_header(200);
        header('Content-Type: text/html; charset=' . get_option('blog_charset'));
        wp_register_style('zappoppop-preview-frame', false, array(), defined('ZAPPOPPOP_VERSION') ? ZAPPOPPOP_VERSION : '1.0');
        wp_enqueue_style('zappoppop-preview-frame');
        wp_add_inline_style('zappoppop-preview-frame', 'html,body{margin:0;padding:0;min-height:100%;background:' . (ZapPopPop_Front_Renderer::config_has_transparent_canvas($this->config) ? 'transparent' : '#f1f5f9') . ';}');
        ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title><?php esc_html_e('Popup Preview', 'zappoppop'); ?></title>
<?php wp_head(); ?>
</head>
<body class="zappoppop-preview-frame">
<?php wp_footer(); ?>
</body>
</html>
        <?php
        exit;
    }

    public function render() {
        if (!$this->uid || !is_array($this->config)) {
            return;
        }
        $uid = $this->uid;

        try {
            ob_start();
            $zappoppop_ret  = $this->renderer->render_popup_markup($uid, $this->config, false, false);
            $zappoppop_html = ob_get_clean() . (is_string($zappoppop_ret) ? $zappoppop_ret : '');
            echo ZapPopPop_Sanitizer::kses_render($zappoppop_html); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- popup markup goes through wp_kses() with the popup allowlist (ZapPopPop_Sanitizer::kses_render) ZAPPOPPOP_OUTPUT_KSES
        } catch (\Throwable $e) {
            if (ob_get_level() > 0) { ob_end_clean(); }
            echo '<div style="position:fixed;top:12px;left:50%;transform:translateX(-50%);z-index:1000002;background:#7f1d1d;color:#fff;padding:8px 14px;border-radius:8px;font:600 12px sans-serif;">Preview render error: ' . esc_html($e->getMessage()) . '</div>';
        }

        $back = admin_url('admin.php?page=zappoppop-builder' . ($uid !== 'draft' ? '&uid=' . rawurlencode($uid) : ''));
        ?>
        <div id="zappoppop-preview-banner" style="position:fixed;left:12px;bottom:12px;z-index:1000001;background:#0f172a;color:#f8fafc;border:1px solid #334155;border-radius:20px;padding:6px 14px;font:600 12px/1.4 -apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;display:flex;gap:10px;align-items:center;box-shadow:0 8px 20px rgba(0,0,0,.35);">
            <span style="color:#10b981;">&#9679; <?php esc_html_e('PREVIEW MODE', 'zappoppop'); ?></span>
            <a href="<?php echo esc_url($back); ?>" style="color:#38bdf8;text-decoration:none;"><?php esc_html_e('Back to Builder', 'zappoppop'); ?></a>
            <a href="#" id="zappoppop-preview-replay" style="color:#e2e8f0;text-decoration:none;"><?php esc_html_e('Replay', 'zappoppop'); ?></a>
        </div>
        <?php
    }
}
