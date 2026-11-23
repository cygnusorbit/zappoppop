<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Allow-list for a layer's HTML ID and its "Attributes" rows (name + value pairs saved in htmlAttrs).
 *
 * Structured data only: a row is kept when the name is on the allow-list (data-*, a fixed set of aria-*, role, title, tabindex,
 * lang, dir, draggable, spellcheck) and the value fits the format of that name. Event handlers, style, class and id are never
 * accepted here (id and class have their own controls). Rows are cleaned when a popup is saved and again when it is rendered.
 */
class ZapPopPop_Html_Attrs {

    const MAX_ROWS  = 20;
    const MAX_VALUE = 200;

    const ARIA = array(
        'aria-hidden', 'aria-label', 'aria-live', 'aria-pressed', 'aria-expanded', 'aria-controls', 'aria-describedby', 'aria-labelledby',
        'aria-modal', 'aria-checked', 'aria-selected', 'aria-disabled', 'aria-required', 'aria-invalid', 'aria-busy', 'aria-atomic',
        'aria-current', 'aria-haspopup', 'aria-valuenow', 'aria-valuemin', 'aria-valuemax', 'aria-valuetext', 'aria-errormessage',
        'aria-relevant', 'aria-roledescription',
    );

    /** A layer HTML ID: letters, digits, _ - : . only, at most 64 characters. */
    public static function clean_id($id) {
        if (!is_scalar($id)) {
            return '';
        }
        $id = preg_replace('/[^A-Za-z0-9_\-:.]/', '', (string) $id);
        return is_string($id) ? substr($id, 0, 64) : '';
    }

    /** @return array|null array('name' => string, 'value' => string), or null when the row is not allowed. */
    public static function clean_row($name, $value) {
        if (!is_scalar($name) || !is_scalar($value)) {
            return null;
        }
        $name = strtolower(trim((string) $name));
        if ($name === '' || strlen($name) > 64) {
            return null;
        }
        $value = trim(sanitize_text_field((string) $value));
        if (strlen($value) > self::MAX_VALUE || preg_match('/^\s*(?:javascript|vbscript|data)\s*:/i', $value)) {
            return null;
        }
        if (strpos($name, 'data-') === 0) {
            return preg_match('/^data-[a-z0-9_.\-]+$/', $name) ? array('name' => $name, 'value' => $value) : null;
        }
        if ($value === '') {
            return null;
        }
        if (strpos($name, 'aria-') === 0) {
            return in_array($name, self::ARIA, true) ? array('name' => $name, 'value' => $value) : null;
        }
        switch ($name) {
            case 'title':
                $ok = true;
                break;
            case 'role':
                $ok = (bool) preg_match('/^[a-z][a-z\-]{0,30}$/', $value);
                break;
            case 'tabindex':
                $ok = (bool) preg_match('/^-?[0-9]{1,4}$/', $value);
                break;
            case 'lang':
                $ok = (bool) preg_match('/^[A-Za-z]{2,3}(?:-[A-Za-z0-9]{2,8}){0,3}$/', $value);
                break;
            case 'dir':
                $ok = in_array($value, array('ltr', 'rtl', 'auto'), true);
                break;
            case 'draggable':
            case 'spellcheck':
                $ok = in_array($value, array('true', 'false'), true);
                break;
            default:
                $ok = false;
        }
        return $ok ? array('name' => $name, 'value' => $value) : null;
    }

    /** @return array list of array('name', 'value'); duplicate names keep the last row. */
    public static function clean_list($rows) {
        $out = array();
        if (!is_array($rows)) {
            return $out;
        }
        foreach ($rows as $row) {
            if (is_object($row)) {
                $row = get_object_vars($row);
            }
            if (!is_array($row)) {
                continue;
            }
            $pair = self::clean_row(isset($row['name']) ? $row['name'] : '', isset($row['value']) ? $row['value'] : '');
            if ($pair === null) {
                continue;
            }
            $out[$pair['name']] = $pair;
            if (count($out) >= self::MAX_ROWS) {
                break;
            }
        }
        return array_values($out);
    }

    /** Attribute string for an opening tag: ' name="value"' per allowed row, each value escaped. */
    public static function render($rows) {
        $html = '';
        foreach (self::clean_list($rows) as $pair) {
            $html .= ' ' . esc_attr($pair['name']) . '="' . esc_attr($pair['value']) . '"';
        }
        return $html;
    }
}
