<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('ZapPopPop_Sanitizer')) {
    require_once ZAPPOPPOP_PATH . 'includes/class-zappoppop-sanitizer.php';
}

/**
 * Module lifecycle endpoints: install, uninstall, delete, usage, categories. ZAPPOPPOP_UPLOADER_REMOVED: there is no upload endpoint.
 * Every action requires manage_options and a valid nonce; file operations are further limited by the installer.
 */
class ZapPopPop_Ajax_Modules {

    public function __construct() {
        foreach (['install', 'uninstall', 'delete', 'usage', 'cats_save', 'set_category'] as $a) {
            add_action('wp_ajax_zappoppop_module_' . $a, [$this, $a]);
        }
    }

    protected function verify() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized action.', 'zappoppop')], 403);
        }
        $nonce = isset($_REQUEST['nonce']) ? sanitize_text_field(wp_unslash($_REQUEST['nonce'])) : '';
        foreach (['zappoppop_admin_nonce', 'zappoppop_settings_nonce', 'zappoppop_builder_nonce'] as $action) {
            if (wp_verify_nonce($nonce, $action)) {
                return;
            }
        }
        wp_send_json_error(['message' => __('Security check failed. Please refresh the page.', 'zappoppop')], 403);
    }

    protected function slug() {
        $s = isset($_POST['slug']) ? sanitize_key(wp_unslash($_POST['slug'])) : '';
        return ZapPopPop_Modules::slug_valid($s) ? $s : '';
    }

    protected function flag($key) {
        return !empty($_POST[$key]) && $_POST[$key] !== 'false' && $_POST[$key] !== '0';
    }

    protected function respond($res) {
        if (is_wp_error($res)) {
            wp_send_json_error([
                'code'    => $res->get_error_code(),
                'message' => $res->get_error_message(),
                'data'    => $res->get_error_data(),
            ]);
        }
        wp_send_json_success($res);
    }

    public function install() {
        $this->verify();
        $this->respond(ZapPopPop_Modules_Installer::install($this->slug()));
    }

    public function uninstall() {
        $this->verify();
        $this->respond(ZapPopPop_Modules_Installer::uninstall($this->slug(), $this->flag('force'), $this->flag('remove_data')));
    }

    public function delete() {
        $this->verify();
        $this->respond(ZapPopPop_Modules_Installer::delete($this->slug(), $this->flag('force'), $this->flag('remove_data')));
    }

    public function usage() {
        $this->verify();
        $slug = $this->slug();
        if ($slug === '' || !ZapPopPop_Modules::get($slug)) {
            wp_send_json_error(['message' => __('Module not found.', 'zappoppop')]);
        }
        wp_send_json_success(ZapPopPop_Modules_Installer::usage($slug));
    }

    /** Module Category manager: replaces the user category list (name, order). */
    public function cats_save() {
        $this->verify();
        $raw = ZapPopPop_Sanitizer::request_json('cats');
        if (!is_array($raw)) {
            wp_send_json_error(['message' => __('Malformed category list.', 'zappoppop')]);
        }
        wp_send_json_success([
            'categories' => ZapPopPop_Module_Categories::save($raw),
            'message'    => __('Categories saved.', 'zappoppop'),
        ]);
    }

    /** Extend page "Edit": set a module's category (written to its module.json). */
    public function set_category() {
        $this->verify();
        $slug = $this->slug();
        $mod  = ZapPopPop_Modules::get($slug);
        if ($mod && isset($mod['allow_edit']) && !$mod['allow_edit']) {
            $this->respond(new WP_Error('no_edit', __('Editing is disabled for this module in its module.json.', 'zappoppop')));
        }
        $cat  = isset($_POST['category']) ? sanitize_text_field(wp_unslash($_POST['category'])) : '';
        $img  = isset($_POST['image']) ? sanitize_text_field(wp_unslash($_POST['image'])) : null;
        $res  = ZapPopPop_Module_Categories::assign($slug, $cat, $img);
        if (is_wp_error($res)) {
            $this->respond($res);
        }
        $m = ZapPopPop_Modules::get($slug);
        wp_send_json_success([
            'module'  => $m ? ZapPopPop_Modules_Installer::summary($m) : null,
            'json'    => false,
            'message' => __('Module updated.', 'zappoppop'),
        ]);
    }
}
