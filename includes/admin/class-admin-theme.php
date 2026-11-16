<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('ZapPopPop_Sanitizer')) {
    require_once ZAPPOPPOP_PATH . 'includes/class-zappoppop-sanitizer.php';
}

/**
 * Global admin theme: schema -> whitelist sanitizer -> option -> CSS variables.
 */
class ZapPopPop_Admin_Theme {
    const OPTION       = 'zappoppop_theme';
    const PAGE         = 'zappoppop-theme';
    const NONCE_ACTION = 'zappoppop_theme_save';

    public function __construct() {
        add_action('admin_init', [$this, 'handle_post']);
        add_filter('admin_body_class', [$this, 'body_class']);
        add_action('admin_menu', [$this, 'reorder_menu'], 100);
        add_action('admin_menu', [$this, 'register_fallback_menu'], 99);
    }

    public function register_fallback_menu() {
        global $submenu;
        if (!empty($submenu['zappoppop-dashboard'])) {
            foreach ($submenu['zappoppop-dashboard'] as $item) {
                if (isset($item[2]) && $item[2] === self::PAGE) { return; }
            }
        }
        add_submenu_page('zappoppop-dashboard', __('Theme', 'zappoppop'), __('Theme', 'zappoppop'), 'manage_options', self::PAGE, [$this, 'render_page']);
    }

    public function render_page() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'zappoppop'));
        }
        include ZAPPOPPOP_PATH . 'templates/theme-view.php';
    }

    /* Place "Theme" immediately below "Settings" in the ZapPopPop submenu. */
    public function reorder_menu() {
        global $submenu;
        if (empty($submenu['zappoppop-dashboard']) || !is_array($submenu['zappoppop-dashboard'])) { return; }
        $items = array_values($submenu['zappoppop-dashboard']);
        $theme_item = null;
        $rest = [];
        foreach ($items as $item) {
            if (isset($item[2]) && $item[2] === self::PAGE) { $theme_item = $item; } else { $rest[] = $item; }
        }
        if ($theme_item === null) { return; }
        $out = [];
        $placed = false;
        foreach ($rest as $item) {
            $out[] = $item;
            if (isset($item[2]) && $item[2] === 'zappoppop-settings') { $out[] = $theme_item; $placed = true; }
        }
        if (!$placed) { $out[] = $theme_item; }
        $submenu['zappoppop-dashboard'] = $out;
    }

    /* ---------------------------------------------------------- schema */
    public static function schema() {
        $sans = '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif';
        $mono = 'ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace';
        $weights = ['300' => '300 ' . __('Light', 'zappoppop'), '400' => '400 ' . __('Regular', 'zappoppop'), '500' => '500 ' . __('Medium', 'zappoppop'), '600' => '600 ' . __('Semi-bold', 'zappoppop'), '700' => '700 ' . __('Bold', 'zappoppop'), '800' => '800 ' . __('Extra-bold', 'zappoppop'), '900' => '900 ' . __('Black', 'zappoppop')];
        $transforms = ['none' => __('None', 'zappoppop'), 'uppercase' => __('UPPERCASE', 'zappoppop'), 'lowercase' => __('lowercase', 'zappoppop'), 'capitalize' => __('Capitalize', 'zappoppop')];

        $f = function ($label, $type, $default, $opts = []) {
            return array_merge(['label' => $label, 'type' => $type, 'default' => $default], $opts);
        };

        return [
            'general' => ['label' => __('General', 'zappoppop'), 'fields' => [
                'enabled'       => $f(__('Enable global theme on all ZapPopPop pages', 'zappoppop'), 'bool', 1),
                'apply_builder' => $f(__('Also apply to the Builder page', 'zappoppop'), 'bool', 0),
                'logo_id'       => $f(__('Top bar logo', 'zappoppop'), 'media', 0),
                'c_bg_page'     => $f(__('Page background', 'zappoppop'), 'color', '#f0f0f1'),
                'c_bg_card'     => $f(__('Card / panel background', 'zappoppop'), 'color', '#ffffff'),
                'c_border'      => $f(__('Border color', 'zappoppop'), 'color', '#dcdcde'),
                'c_border_strong' => $f(__('Strong border color', 'zappoppop'), 'color', '#c3c4c7'),
                'bw'            => $f(__('Border width', 'zappoppop'), 'size', '1px'),
            ]],
            'typography' => ['label' => __('Typography', 'zappoppop'), 'fields' => [
                'font_base'    => $f(__('Body font family', 'zappoppop'), 'font', $sans),
                'font_heading' => $f(__('Heading font family', 'zappoppop'), 'font', $sans),
                'font_mono'    => $f(__('Monospace font family', 'zappoppop'), 'font', $mono),
                'fs_base'      => $f(__('Body font size', 'zappoppop'), 'size', '13px'),
                'fs_h1'        => $f(__('H1 size', 'zappoppop'), 'size', '22px'),
                'fs_h2'        => $f(__('H2 size', 'zappoppop'), 'size', '15px'),
                'fs_h3'        => $f(__('H3 size', 'zappoppop'), 'size', '15px'),
                'fs_small'     => $f(__('Small / helper text size', 'zappoppop'), 'size', '12px'),
                'fs_label'     => $f(__('Label size', 'zappoppop'), 'size', '11px'),
                'fs_button'    => $f(__('Button text size', 'zappoppop'), 'size', '13px'),
                'fs_input'     => $f(__('Input text size', 'zappoppop'), 'size', '12px'),
                'fs_table'     => $f(__('Table text size', 'zappoppop'), 'size', '13px'),
                'fs_badge'     => $f(__('Badge text size', 'zappoppop'), 'size', '11px'),
                'fs_kpi'       => $f(__('KPI value size', 'zappoppop'), 'size', '24px'),
                'fs_modal_title' => $f(__('Modal title size', 'zappoppop'), 'size', '14px'),
                'fw_body'      => $f(__('Body weight', 'zappoppop'), 'enum', '400', ['options' => $weights]),
                'fw_heading'   => $f(__('Heading weight', 'zappoppop'), 'enum', '600', ['options' => $weights]),
                'fw_label'     => $f(__('Label weight', 'zappoppop'), 'enum', '700', ['options' => $weights]),
                'fw_button'    => $f(__('Button weight', 'zappoppop'), 'enum', '600', ['options' => $weights]),
                'fw_kpi'       => $f(__('KPI value weight', 'zappoppop'), 'enum', '800', ['options' => $weights]),
                'lh_base'      => $f(__('Line height (unitless)', 'zappoppop'), 'number', '1.5'),
                'ls_label'     => $f(__('Label letter spacing', 'zappoppop'), 'size', '0.5px'),
                'tt_label'     => $f(__('Label text transform', 'zappoppop'), 'enum', 'uppercase', ['options' => $transforms]),
                'tt_badge'     => $f(__('Badge text transform', 'zappoppop'), 'enum', 'uppercase', ['options' => $transforms]),
            ]],
            'text' => ['label' => __('Text & Links', 'zappoppop'), 'fields' => [
                'c_text'       => $f(__('Body text', 'zappoppop'), 'color', '#1d2327'),
                'c_heading'    => $f(__('Headings', 'zappoppop'), 'color', '#1d2327'),
                'c_muted'      => $f(__('Muted / description text', 'zappoppop'), 'color', '#787c82'),
                'c_link'       => $f(__('Link', 'zappoppop'), 'color', '#2237c9'),
                'c_link_hover' => $f(__('Link hover', 'zappoppop'), 'color', '#c1272d'),
            ]],
            'buttons' => ['label' => __('Buttons', 'zappoppop'), 'fields' => [
                'c_primary'        => $f(__('Primary background', 'zappoppop'), 'color', '#c1272d'),
                'c_primary_hover'  => $f(__('Primary hover background', 'zappoppop'), 'color', '#a51f25'),
                'c_primary_text'   => $f(__('Primary text', 'zappoppop'), 'color', '#ffffff'),
                'c_secondary_bg'   => $f(__('Secondary background', 'zappoppop'), 'color', '#ffffff'),
                'c_secondary_hover' => $f(__('Secondary hover background', 'zappoppop'), 'color', '#f1f5f9'),
                'c_secondary_text' => $f(__('Secondary text', 'zappoppop'), 'color', '#1d2327'),
                'c_secondary_border' => $f(__('Secondary border', 'zappoppop'), 'color', '#c3c4c7'),
                'c_danger'         => $f(__('Danger background', 'zappoppop'), 'color', '#b32d2e'),
                'c_danger_text'    => $f(__('Danger text', 'zappoppop'), 'color', '#ffffff'),
                'c_active'         => $f(__('Active background', 'zappoppop'), 'color', '#c1272d'),
                'c_active_text'    => $f(__('Active text', 'zappoppop'), 'color', '#ffffff'),
                'r_button'         => $f(__('Button radius', 'zappoppop'), 'size', '3px'),
                'h_button'         => $f(__('Button height', 'zappoppop'), 'size', '34px'),
            ]],
            'forms' => ['label' => __('Form Fields', 'zappoppop'), 'fields' => [
                'c_input_bg'          => $f(__('Input background', 'zappoppop'), 'color', '#ffffff'),
                'c_input_text'        => $f(__('Input text', 'zappoppop'), 'color', '#1e293b'),
                'c_input_border'      => $f(__('Input border', 'zappoppop'), 'color', '#8c8f94'),
                'c_input_focus'       => $f(__('Input focus border', 'zappoppop'), 'color', '#c1272d'),
                'c_input_placeholder' => $f(__('Placeholder text', 'zappoppop'), 'color', '#94a3b8'),
                'r_input'             => $f(__('Input radius', 'zappoppop'), 'size', '3px'),
                'h_input'             => $f(__('Input height', 'zappoppop'), 'size', '32px'),
            ]],
            'tables' => ['label' => __('Tables', 'zappoppop'), 'fields' => [
                'c_th_bg'      => $f(__('Header background', 'zappoppop'), 'color', '#f6f7f7'),
                'c_th_text'    => $f(__('Header text', 'zappoppop'), 'color', '#1d2327'),
                'c_row_bg'     => $f(__('Row background', 'zappoppop'), 'color', '#ffffff'),
                'c_row_stripe' => $f(__('Striped row background', 'zappoppop'), 'color', '#f6f7f7'),
                'c_row_hover'  => $f(__('Row hover background', 'zappoppop'), 'color', '#f6f7f7'),
                'c_row_border' => $f(__('Row border', 'zappoppop'), 'color', '#f0f0f1'),
            ]],
            'cards' => ['label' => __('Cards & KPI', 'zappoppop'), 'fields' => [
                'c_kpi_label' => $f(__('KPI label color', 'zappoppop'), 'color', '#787c82'),
                'c_kpi_value' => $f(__('KPI value color', 'zappoppop'), 'color', '#1d2327'),
                'r_card'      => $f(__('Card radius', 'zappoppop'), 'size', '3px'),
                'p_card'      => $f(__('Card padding', 'zappoppop'), 'size', '16px'),
                'gap'         => $f(__('Grid gap', 'zappoppop'), 'size', '16px'),
                'shadow_card' => $f(__('Card shadow', 'zappoppop'), 'shadow', '0 1px 1px rgba(0,0,0,0.04)'),
            ]],
            'badges' => ['label' => __('Badges & Chips', 'zappoppop'), 'fields' => [
                'c_badge_pub_bg'    => $f(__('Published badge background', 'zappoppop'), 'color', '#dcfce7'),
                'c_badge_pub_text'  => $f(__('Published badge text', 'zappoppop'), 'color', '#15803d'),
                'c_badge_draft_bg'  => $f(__('Draft badge background', 'zappoppop'), 'color', '#f1f5f9'),
                'c_badge_draft_text' => $f(__('Draft badge text', 'zappoppop'), 'color', '#475569'),
                'c_chip_bg'         => $f(__('Shortcode chip background', 'zappoppop'), 'color', '#f1f5f9'),
                'c_chip_text'       => $f(__('Shortcode chip text', 'zappoppop'), 'color', '#334155'),
                'c_chip_border'     => $f(__('Shortcode chip border', 'zappoppop'), 'color', '#e2e8f0'),
                'r_badge'           => $f(__('Badge radius', 'zappoppop'), 'size', '12px'),
            ]],
            'status' => ['label' => __('Status & Notices', 'zappoppop'), 'fields' => [
                'c_success' => $f(__('Success', 'zappoppop'), 'color', '#16a34a'),
                'c_warning' => $f(__('Warning', 'zappoppop'), 'color', '#d97706'),
                'c_info'    => $f(__('Info', 'zappoppop'), 'color', '#0284c7'),
            ]],
            'modals' => ['label' => __('Modals', 'zappoppop'), 'fields' => [
                'c_modal_bg'        => $f(__('Modal background', 'zappoppop'), 'color', '#ffffff'),
                'c_modal_head_bg'   => $f(__('Modal header background', 'zappoppop'), 'color', '#0f172a'),
                'c_modal_head_text' => $f(__('Modal header text', 'zappoppop'), 'color', '#ffffff'),
                'c_modal_close'     => $f(__('Modal close icon', 'zappoppop'), 'color', '#94a3b8'),
                'c_modal_backdrop'  => $f(__('Backdrop (rgba)', 'zappoppop'), 'color', 'rgba(15,23,42,0.7)'),
                'shadow_modal'      => $f(__('Modal shadow', 'zappoppop'), 'shadow', '0 20px 25px -5px rgba(0,0,0,0.25)'),
            ]],
            'scrollbar' => ['label' => __('Scrollbars', 'zappoppop'), 'fields' => [
                'c_scroll_thumb' => $f(__('Scrollbar thumb', 'zappoppop'), 'color', '#cbd5e1'),
                'c_scroll_track' => $f(__('Scrollbar track', 'zappoppop'), 'color', '#f8fafc'),
            ]],
            /* ZAPPOPPOP_THEME_BUILDER_TAB */
            'builder' => ['label' => __('Builder', 'zappoppop'), 'fields' => [
                'c_guide_color' => $f(__('Selected element guide line color', 'zappoppop'), 'color', '#7dd3fc'),
                'bw_guide'      => $f(__('Selected element guide line size', 'zappoppop'), 'size', '1px'),
            ]],
        ];
    }

    public static function font_presets() {
        return [
            '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif',
            'Arial, Helvetica, sans-serif',
            'Verdana, Geneva, sans-serif',
            'Tahoma, Geneva, sans-serif',
            '"Trebuchet MS", Helvetica, sans-serif',
            'Georgia, "Times New Roman", serif',
            '"Times New Roman", Times, serif',
            '"Courier New", Courier, monospace',
            'ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace',
            'system-ui, sans-serif',
            'inherit',
        ];
    }

    public static function css_var($key) {
        return '--zpp-' . str_replace('_', '-', $key);
    }

    public static function defaults() {
        $out = [];
        foreach (self::schema() as $group) {
            foreach ($group['fields'] as $key => $field) {
                $out[$key] = $field['default'];
            }
        }
        return class_exists('ZapPopPop_Saved_Defaults') ? ZapPopPop_Saved_Defaults::apply_theme($out) : $out;
    }

    public static function field_def($key) {
        foreach (self::schema() as $group) {
            if (isset($group['fields'][$key])) {
                return $group['fields'][$key];
            }
        }
        return null;
    }

    /* ---------------------------------------------------- sanitization */
    public static function sanitize_value($field, $value) {
        if (is_array($value) || is_object($value)) {
            return $field['default'];
        }
        $value = trim((string) $value);
        switch ($field['type']) {
            case 'bool':
                return !empty($value) && $value !== '0' ? 1 : 0;
            case 'color':
                if (preg_match('/^#([0-9a-f]{3,4}|[0-9a-f]{6}|[0-9a-f]{8})$/i', $value)) {
                    return strtolower($value);
                }
                if (preg_match('/^rgba?\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}\s*(,\s*(0|1|0?\.\d+|1\.0+)\s*)?\)$/i', $value)) {
                    return preg_replace('/\s+/', '', $value);
                }
                if (strtolower($value) === 'transparent') {
                    return 'transparent';
                }
                return $field['default'];
            case 'size':
                return preg_match('/^-?\d{1,4}(\.\d{1,3})?(px|rem|em|%|pt)?$/', $value) ? $value : $field['default'];
            case 'number':
                return preg_match('/^\d{1,3}(\.\d{1,3})?$/', $value) ? $value : $field['default'];
            case 'enum':
                return isset($field['options'][$value]) ? $value : $field['default'];
            case 'font':
                return ($value !== '' && strlen($value) <= 250 && preg_match('/^[A-Za-z0-9 ,"\'\-_.]+$/', $value)) ? $value : $field['default'];
            case 'shadow':
                return ($value !== '' && strlen($value) <= 200 && preg_match('/^(none|[0-9a-zA-Z\s.,#()%\-]+)$/', $value)) ? $value : $field['default'];
            case 'media':
                $zpp_id = absint($value);
                return ($zpp_id && strpos((string) get_post_mime_type($zpp_id), 'image/') === 0) ? $zpp_id : 0;
            case 'textarea':
                $value = wp_strip_all_tags($value);
                return str_ireplace('</style', '', substr($value, 0, 20000));
        }
        return $field['default'];
    }

    public static function sanitize_all($raw) {
        $clean = [];
        $raw = is_array($raw) ? $raw : [];
        foreach (self::schema() as $group) {
            foreach ($group['fields'] as $key => $field) {
                if ($field['type'] === 'bool') {
                    $clean[$key] = !empty($raw[$key]) ? 1 : 0;
                } else {
                    $clean[$key] = isset($raw[$key]) ? self::sanitize_value($field, $raw[$key]) : $field['default'];
                }
            }
        }
        return $clean;
    }

    public static function get_values() {
        $saved = get_option(self::OPTION, []);
        if (!is_array($saved)) {
            $saved = [];
        }
        $values = self::defaults();
        foreach ($saved as $key => $val) {
            $field = self::field_def($key);
            if ($field) {
                $values[$key] = self::sanitize_value($field, $val);
            }
        }
        if (!array_key_exists('c_active', $saved) && isset($values['c_primary'])) {
            $values['c_active'] = $values['c_primary'];
        }
        if (!array_key_exists('c_active_text', $saved) && isset($values['c_primary_text'])) {
            $values['c_active_text'] = $values['c_primary_text'];
        }
        return $values;
    }

    /** Custom top-bar logo URL, or '' to use the bundled default logo. */
    public static function logo_url() {
        $v  = self::get_values();
        $id = isset($v['logo_id']) ? (int) $v['logo_id'] : 0;
        if ($id <= 0) { return ''; }
        $url = wp_get_attachment_image_url($id, 'full');
        if (!$url) { $url = wp_get_attachment_url($id); }
        return $url ? $url : '';
    }

    /* -------------------------------------------------------- output */
    public static function is_zappoppop_admin_page() {
        $page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
        return $page !== '' && strpos($page, 'zappoppop') === 0;
    }

    public function body_class($classes) {
        if (!self::is_zappoppop_admin_page()) {
            return $classes;
        }
        $v = self::get_values();
        $page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
        if (empty($v['enabled'])) {
            return $classes;
        }
        if ($page === 'zappoppop-builder' && empty($v['apply_builder'])) {
            return $classes;
        }
        return $classes . ' zappoppop-admin-themed';
    }

    public static function build_css() {
        $v = self::get_values();
        $css = 'body.zappoppop-admin-themed{';
        foreach (self::schema() as $group) {
            foreach ($group['fields'] as $key => $field) {
                if (in_array($field['type'], ['bool', 'textarea', 'media'], true)) {
                    continue;
                }
                $css .= self::css_var($key) . ':' . $v[$key] . ';';
            }
        }
        $css .= '}';
        /* ZAPPOPPOP_THEME_BUILDER_TAB: guide vars must reach the builder page even when the admin theme is not applied there */
        $g_w = isset($v['bw_guide']) ? trim((string) $v['bw_guide']) : '1px';
        if (preg_match('/^\d{1,2}(\.\d{1,2})?$/', $g_w)) { $g_w .= 'px'; }
        if (!preg_match('/^\d{1,2}(\.\d{1,2})?px$/', $g_w)) { $g_w = '1px'; }
        $g_c = isset($v['c_guide_color']) && preg_match('/^#[0-9a-fA-F]{3,8}$/', (string) $v['c_guide_color']) ? $v['c_guide_color'] : '#7dd3fc';
        $css .= ':root{--zpp-guide-color:' . $g_c . ';--zpp-guide-width:' . $g_w . ';}';
        return $css;
    }

    protected static function version() {
        return defined('ZAPPOPPOP_VERSION') ? ZAPPOPPOP_VERSION : '1.0';
    }

    public static function enqueue_global() {
        wp_enqueue_style('zappoppop-theme-global-css', ZAPPOPPOP_URL . 'admin/css/theme-global.css', [], self::version());
        wp_add_inline_style('zappoppop-theme-global-css', self::build_css());
    }

    public static function enqueue_page() {
        $v = self::version();
        wp_enqueue_style('dashicons');
        wp_enqueue_style('zappoppop-dashboard-css', ZAPPOPPOP_URL . 'admin/css/dashboard.css', [], $v);
        wp_enqueue_style('zappoppop-overview-css', ZAPPOPPOP_URL . 'admin/css/overview.css', [], $v);
        wp_enqueue_style('zappoppop-theme-css', ZAPPOPPOP_URL . 'admin/css/theme.css', ['zappoppop-dashboard-css', 'zappoppop-overview-css', 'zappoppop-theme-global-css'], $v);
        wp_enqueue_script('zappoppop-overview-js', ZAPPOPPOP_URL . 'admin/js/overview.js', ['jquery'], $v, true);
        wp_enqueue_script('zappoppop-theme-js', ZAPPOPPOP_URL . 'admin/js/theme.js', [], $v, true);
        wp_enqueue_media();
        wp_enqueue_script('zappoppop-theme-logo-js', ZAPPOPPOP_URL . 'admin/js/theme-logo.js', ['zappoppop-theme-js'], $v, true);
        if (class_exists('ZapPopPop_Saved_Defaults')) {
            ZapPopPop_Saved_Defaults::enqueue();
        }
    }

    /* ---------------------------------------------------------- save */
    public function handle_post() {
        if (!is_admin() || empty($_POST['zpp_theme_action'])) {
            return;
        }
        if (!isset($_GET['page']) || sanitize_key(wp_unslash($_GET['page'])) !== self::PAGE) {
            return;
        }
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'zappoppop'));
        }
        check_admin_referer(self::NONCE_ACTION, 'zpp_theme_nonce');

        $action = sanitize_key(wp_unslash($_POST['zpp_theme_action']));
        $status = 'saved';

        if ($action === 'reset') {
            delete_option(self::OPTION);
            $status = 'reset';
        } elseif ($action === 'import') {
            $data = ZapPopPop_Sanitizer::request_json('zpp_theme_import');
            if (!is_array($data)) {
                $status = 'import_error';
            } else {
                $merged = array_merge(self::get_values(), $data);
                update_option(self::OPTION, self::sanitize_all($merged));
                $status = 'imported';
            }
        } else {
            $raw = isset($_POST['zpp_theme']) && is_array($_POST['zpp_theme']) ? /* phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitised key by key by the theme sanitiser */ wp_unslash($_POST['zpp_theme']) : [];
            update_option(self::OPTION, self::sanitize_all($raw));
        }

        wp_safe_redirect(add_query_arg(['page' => self::PAGE, 'zpp_status' => $status], admin_url('admin.php')));
        exit;
    }
}
