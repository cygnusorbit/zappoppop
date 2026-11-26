<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Files that visitors upload through a popup form. They live in uploads/zappoppop-submissions/ (resolved with
 * wp_upload_dir() at run time). Only the server writes the *_path entries of a lead, and only files inside that folder
 * are ever removed when a lead is deleted. ZAPPOPPOP_SUBMISSION_FILES
 */
class ZapPopPop_Submission_Files {
    const SUBDIR = 'zappoppop-submissions';

    /** Real path of the submissions folder, or '' when it does not exist yet / uploads are unavailable. */
    public static function root() {
        $up = wp_get_upload_dir();
        if (!empty($up['error']) || empty($up['basedir'])) {
            return '';
        }
        $real = realpath(rtrim($up['basedir'], '/\\') . DIRECTORY_SEPARATOR . self::SUBDIR);
        return $real ? $real : '';
    }

    /** Stores one uploaded file (a $_FILES entry). @return array|null wp_handle_upload() result, or null on failure */
    public static function store(array $file) {
        if (!function_exists('wp_handle_upload')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        $file = array( /* ZAPPOPPOP_P27_STORE_CLEAN */
            'name'     => isset($file['name']) && is_string($file['name']) ? sanitize_file_name($file['name']) : '',
            'tmp_name' => isset($file['tmp_name']) && is_string($file['tmp_name']) ? $file['tmp_name'] : '',
            'error'    => isset($file['error']) ? (int) $file['error'] : UPLOAD_ERR_NO_FILE,
            'size'     => isset($file['size']) ? absint($file['size']) : 0,
            'type'     => isset($file['type']) && is_string($file['type']) ? sanitize_mime_type($file['type']) : '',
        );
        if ($file['name'] === '' || $file['tmp_name'] === '' || !is_uploaded_file($file['tmp_name'])) {
            return null;
        }
        $sub = '/' . self::SUBDIR . '/' . gmdate('Y/m');
        $cb  = function ($d) use ($sub) {
            $d['subdir'] = $sub;
            $d['path']   = $d['basedir'] . $sub;
            $d['url']    = $d['baseurl'] . $sub;
            return $d;
        };
        add_filter('upload_dir', $cb);
        $r = wp_handle_upload($file, array('test_form' => false));
        remove_filter('upload_dir', $cb);
        return (is_array($r) && empty($r['error']) && !empty($r['file']) && !empty($r['url'])) ? $r : null;
    }

    /** Visitors may not send *_path keys: those are written by the server only. */
    public static function is_path_key($key) {
        return substr(strtolower((string) $key), -5) === '_path';
    }

    /** Removes the files named by the *_path entries of a lead payload, only inside the submissions folder. @return int files removed */
    public static function delete_files($payload) {
        $root = self::root();
        if ($root === '' || !is_array($payload)) {
            return 0;
        }
        $root = rtrim($root, '/\\') . DIRECTORY_SEPARATOR;
        $n    = 0;
        foreach ($payload as $key => $val) {
            if (!is_string($val) || $val === '' || !self::is_path_key($key)) {
                continue;
            }
            $real = realpath($val);
            if ($real && is_file($real) && strpos($real, $root) === 0) {
                wp_delete_file($real);
                $n++;
            }
        }
        return $n;
    }
}
