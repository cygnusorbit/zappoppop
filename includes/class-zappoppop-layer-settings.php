<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Layer Settings catalogue: every Inspector setting (Content / Style / Logic / Data / Advanced)
 * per element type, with its default visibility and an enable / disable override.
 *
 * Storage: zappoppop_settings['builder_layer_settings'][type][tab][key] = 0|1
 * Only values that differ from the element's default are stored.
 */
class ZapPopPop_Layer_Settings {

    const OPTION_KEY = 'builder_layer_settings';

    public static function tabs() {
        return array(
            'basic'    => __('Content', 'zappoppop'),
            'style'    => __('Style', 'zappoppop'),
            'logic'    => __('Logic', 'zappoppop'),
            'data'     => __('Data', 'zappoppop'),
            'advanced' => __('Advanced', 'zappoppop'),
        );
    }

    public static function elements() {
        return (array) apply_filters('zappoppop_layer_elements', array(
            'shape'       => __('Rectangle / Shape', 'zappoppop'),
            'image'       => __('Image', 'zappoppop'),
            'video'       => __('Video Embed', 'zappoppop'),
            'label'       => __('Header', 'zappoppop'),
            'html'        => __('HTML Content', 'zappoppop'),
            'close'       => __('Close Icon', 'zappoppop'),
            'icon'        => __('Font Awesome Icon', 'zappoppop'),
            'button'      => __('Button / Link', 'zappoppop'),
            'text'        => __('Text Input', 'zappoppop'),
            'email'       => __('Email Address', 'zappoppop'),
            'number'      => __('Number Input', 'zappoppop'),
            'phone'       => __('Phone / Tel', 'zappoppop'),
            'textarea'    => __('Textarea', 'zappoppop'),
            'select'      => __('Select Dropdown', 'zappoppop'),
            'checkbox'    => __('Agreement Checkbox', 'zappoppop'),
            'checkboxgroup' => __('Checkbox', 'zappoppop'),
            'radio'       => __('Radio Button', 'zappoppop'),
            'multiselect' => __('Multiselect', 'zappoppop'),
            'date'        => __('Date Picker', 'zappoppop'),
            'time'        => __('Time Picker', 'zappoppop'),
            'upload'      => __('File Upload', 'zappoppop'),
            'password'    => __('Password', 'zappoppop'),
            'submit'      => __('Submit Button', 'zappoppop'),
        ));
    }

    /* ZPP_RIBBON_SYNC: element types in the exact left-to-right order of the builder ribbon (core buttons + anything an add-on prints through the zappoppop_builder_ribbon_tools hook). The ribbon template is the single source of truth, so this list can never drift from it. */
    protected static $ribbon_types = null;

    public static function ribbon_types() {
        if (self::$ribbon_types !== null) {
            return self::$ribbon_types;
        }
        self::$ribbon_types = array();
        $file = ZAPPOPPOP_PATH . 'templates/builder/ribbon.php';
        if (!is_readable($file)) {
            return self::$ribbon_types;
        }
        ob_start();
        include $file;
        $html = (string) ob_get_clean();
        if (preg_match_all('/<button\b[^>]*\bzappoppop-ribbon-tool\b[^>]*\bdata-type="([A-Za-z0-9_-]+)"/', $html, $m)) {
            self::$ribbon_types = array_values(array_unique($m[1]));
        }
        return self::$ribbon_types;
    }

    /* ZPP_ELEMENT_ORDER: the order of the element list (Settings > Builder > Element / Layer Settings) IS the order of the builder ribbon.
       Stored as zappoppop_settings['builder_element_order'] = [type, ...]; empty = default (the ribbon as shipped). Submit stays last. */
    private static function pin_submit($list) {
        $list = array_values(array_diff($list, array('submit')));
        if (isset(self::elements()['submit'])) {
            $list[] = 'submit';
        }
        return $list;
    }

    /** Default order: the ribbon as shipped (core buttons + add-on tools), then any remaining types, Submit last. */
    public static function default_element_order() {
        $els = self::elements();
        $out = array();
        foreach (self::ribbon_types() as $type) {
            if (isset($els[$type]) && !in_array($type, $out, true)) {
                $out[] = $type;
            }
        }
        foreach (array_keys($els) as $type) {
            if (!in_array($type, $out, true)) {
                $out[] = $type;
            }
        }
        return self::pin_submit($out);
    }

    /** Accepts an array or a JSON string; keeps only known element types; returns array() when it equals the default order. */
    public static function sanitize_element_order($raw) {
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $raw     = is_array($decoded) ? $decoded : array();
        }
        if (!is_array($raw)) {
            return array();
        }
        $known = array_keys(self::elements());
        $want  = array();
        foreach ($raw as $t) {
            if (is_string($t) && in_array($t, $known, true) && !in_array($t, $want, true)) {
                $want[] = $t;
            }
        }
        if (!$want) {
            return array();
        }
        $def  = self::default_element_order();
        $full = self::pin_submit(array_merge($want, array_values(array_diff($def, $want))));
        return $full === $def ? array() : $full;
    }

    public static function get_element_order() {
        $s   = get_option('zappoppop_settings', array());
        $raw = (is_array($s) && isset($s['builder_element_order'])) ? $s['builder_element_order'] : array();
        return self::sanitize_element_order($raw);
    }

    /** elements() arranged by the saved order (default: like the ribbon); types missing from it keep their default place after the listed ones. */
    public static function ordered_elements() {
        $els   = self::elements();
        $order = self::get_element_order();
        $order = $order ? $order : self::default_element_order();
        $out   = array();
        foreach ($order as $type) {
            if (isset($els[$type])) {
                $out[$type] = $els[$type];
            }
        }
        foreach ($els as $type => $label) {
            if (!isset($out[$type])) {
                $out[$type] = $label;
            }
        }
        return $out;
    }

    public static function form_types() {
        return (array) apply_filters('zappoppop_layer_form_types', array('text', 'email', 'number', 'phone', 'textarea', 'select', 'checkbox', 'checkboxgroup', 'radio', 'multiselect', 'date', 'time', 'upload', 'password'));
    }

    /** Element types that keep the stock Style layout of the Inspector (mirrors the builder's settled layout). */
    private static function style_layout() {
        $form_base   = 'formModule iconsCard';
        $form_choice = 'formModule';
        return (array) apply_filters('zappoppop_layer_style_layout', array(
            'shape'         => 'padding shadow',
            'image'         => 'opacity padding border shadow',
            'video'         => 'padding border shadow',
            'label'         => 'bg typo padding border shadow',
            'html'          => 'bg padding border shadow',
            'close'         => 'bg border hover',
            'icon'          => 'opacity bg border',
            'button'        => 'bg typo padding border shadow hover',
            'submit'        => 'formModule iconsCard bg typo shadow hover css',
            'text'          => $form_base,
            'email'         => $form_base,
            'number'        => $form_base,
            'phone'         => $form_base,
            'select'        => $form_base,
            'textarea'      => $form_base,
            'date'          => $form_base . ' typo border',
            'time'          => $form_base . ' typo border',
            'upload'        => $form_base . ' typo border',
            'password'      => $form_base . ' typo border',
            'checkbox'      => $form_choice . ' bg typo shadow',
            'radio'         => $form_choice . ' typo',
            'checkboxgroup' => $form_choice . ' typo',
            'multiselect'   => $form_choice . ' typo',
        ));
    }

    private static function style_native($key) {
        $out = array();
        foreach (self::style_layout() as $type => $list) {
            if (in_array($key, explode(' ', $list), true)) {
                $out[] = $type;
            }
        }
        return $out;
    }

    private static function entry($key, $label, $sel, $native, $display = 'block', $rows = array(), $per_type = false) {
        return array(
            'key'      => $key,
            'label'    => $label,
            'sel'      => $sel,
            'native'   => $native,
            'd'        => $display,
            'rows'     => $rows,
            'per_type' => $per_type,
        );
    }

    /** tab => list of entries (an entry may carry child rows). */
    public static function catalogue() {
        static $cat = null;
        if ($cat !== null) {
            return $cat;
        }
        $all  = array_keys(self::elements());
        $form = self::form_types();
        $form_no_rating = array_values(array_diff($form, array('rating')));

        $cat = array();

        $cat['basic'] = array(
            self::entry('layer_name', __('Layer Name', 'zappoppop'), '#insp-section-layer-name', $all),
            self::entry('element_settings', '', '#panel-elem-{type}', $all, 'block', array(), true),
            self::entry('animation', __('Animation (Appearance / Duration / Start Delay / Start Animation)', 'zappoppop'), '#insp-section-gp-basic-Shared, #insp-section-gp-basic-shared, #insp-row-gp-animation', $all), /* ZPP_ANIM_CARD */
            self::entry('required', __('Required Switch', 'zappoppop'), '#insp-row-gp-required', $form, 'flex', array(
                self::entry('required_msg', __('Required Error Message', 'zappoppop'), '#insp-row-gp-required-msg', $form),
            )),
            self::entry('coords', __('Coordinates & Bounds (X / Y / Width / Height)', 'zappoppop'), '#insp-section-coords, #insp-section-coords-grid', $all),
        );

        $cat['style'] = array(
            self::entry('opacity', __('Effect', 'zappoppop'), '#insp-section-opacity', self::style_native('opacity')),
            self::entry('bg', __('Background', 'zappoppop'), '#insp-section-bg-color', self::style_native('bg')),
            self::entry('typo', __('Typography', 'zappoppop'), '#insp-section-typography', self::style_native('typo')),
            self::entry('padding', __('Spacing (Margin / Padding PX)', 'zappoppop'), '#insp-section-padding', self::style_native('padding')),
            self::entry('border', __('Border & Radius', 'zappoppop'), '#insp-section-border', self::style_native('border')),
            self::entry('shadow', __('Box Shadow', 'zappoppop'), '#insp-section-shadow', self::style_native('shadow')),
            self::entry('hover', __('Hover State', 'zappoppop'), '#prop-btn-hover-section', self::style_native('hover')),
            self::entry('css', __('Custom CSS Class & Inline Styles', 'zappoppop'), '#insp-section-custom-css', self::style_native('css')),
            self::entry('formModule', __('Input Styling Module', 'zappoppop'), '#zappoppop-form-style-module', self::style_native('formModule'), 'block', array(
                self::entry('iconsCard', __('Input Icons & Offsets', 'zappoppop'), '#zappoppop-form-icons-card', self::style_native('iconsCard')),
            )),
        );

        $cat['logic'] = array(
            self::entry('conditional_logic', __('Conditional Logic', 'zappoppop'), '#insp-section-conditional-logic', $all),
            self::entry('action_routing', __('Action & Click Routing', 'zappoppop'), '#insp-section-action-routing', $all, 'block', array(
                self::entry('click_action', __('Click Action', 'zappoppop'), '#insp-row-logic-click-action', $all),
                self::entry('link_url', __('Destination URL', 'zappoppop'), '#prop-logic-link-url-row', $all),
                self::entry('goto_canvas', __('Target Canvas #', 'zappoppop'), '#prop-logic-goto-canvas-row', $all),
            )),
        );

        $cat['data'] = array(
            self::entry('data_binding', __('Data & Value Binding', 'zappoppop'), '#insp-section-form-data', $form_no_rating, 'block', array(
                self::entry('default_value', __('Default Value', 'zappoppop'), '#insp-row-data-default', $form_no_rating),
                self::entry('dynamic_default', __('Dynamic Default (URL / Cookie)', 'zappoppop'), '#insp-row-data-dynamic', $form_no_rating, 'flex'),
                self::entry('dynamic_param', __('URL Parameter / Cookie Name', 'zappoppop'), '#prop-dynamic-param-row', $form_no_rating),
                self::entry('read_only', __('Read Only Field', 'zappoppop'), '#insp-row-data-readonly', $form_no_rating, 'flex'),
                self::entry('save_to_db', __('Save to Database', 'zappoppop'), '#insp-row-data-save', $form_no_rating, 'flex'),
            )),
        );

        $cat['advanced'] = array(
            self::entry('field_identifier', __('Field Identifier', 'zappoppop'), '#insp-section-form-advanced', $form, 'block', array(
                self::entry('field_name', __('ID / Merge Token Name', 'zappoppop'), '#insp-row-adv-field-name', $form),
                self::entry('input_mask', __('Input Mask', 'zappoppop'), '#insp-row-adv-input-mask', array()),
            )),
            self::entry('filters', __('Sanitization Filters', 'zappoppop'), '#insp-section-form-filters', $form),
            self::entry('validators', __('Validation Rules', 'zappoppop'), '#insp-section-form-validators', $form, 'block', array(
                self::entry('validator_rules', __('Validator Checklist', 'zappoppop'), '#insp-row-val-rules', $form),
                self::entry('validator_equal', __('Equal Match Value', 'zappoppop'), '#zappoppop-val-extra-equal', $form),
                self::entry('validator_inarray', __('In Array Allowed Values', 'zappoppop'), '#zappoppop-val-extra-inarray', $form),
                self::entry('validator_error', __('Custom Validation Error Message', 'zappoppop'), '#insp-row-val-error', $form),
            )),
        );

        /* ZAPPOPPOP_3C_CATALOGUE */
        $cat = (array) apply_filters('zappoppop_layer_catalogue', $cat);

        return $cat;
    }

    /** Flatten entries and their child rows (children carry a parent key). */
    public static function flat($entries) {
        $out = array();
        foreach ($entries as $e) {
            $e['parent'] = '';
            $out[] = $e;
            foreach ($e['rows'] as $r) {
                $r['parent'] = $e['key'];
                $out[] = $r;
            }
        }
        return $out;
    }

    /** Label of an entry for one element type. */
    public static function entry_label($entry, $type) {
        if (!empty($entry['per_type'])) {
            $els = self::elements();
            return sprintf(
                /* translators: %s: element name, e.g. Text Input */
                __('%s Settings', 'zappoppop'),
                isset($els[$type]) ? $els[$type] : $type
            );
        }
        return $entry['label'];
    }

    public static function default_on($type, $entry) {
        return in_array($type, $entry['native'], true);
    }

    public static function get_overrides() {
        $s = get_option('zappoppop_settings', array());
        $raw = (is_array($s) && isset($s[self::OPTION_KEY])) ? $s[self::OPTION_KEY] : array();
        return self::sanitize($raw);
    }

    /* ---- Layer Settings order (drag & drop). Stored apart from the on/off overrides: zappoppop_settings['builder_layer_order'][type][slot] = [keys]. Slot = tab, or "tab|parentKey" for child rows. ---- */

    public static function order_slot($tab, $parent = '') {
        return $parent === '' ? $tab : $tab . '|' . $parent;
    }

    private static function clean_keys($list, $allowed) {
        $out = array();
        foreach ((array) $list as $k) {
            if (is_string($k) && $k !== '' && in_array($k, $allowed, true) && !in_array($k, $out, true)) {
                $out[] = $k;
            }
        }
        return $out;
    }

    private static function sort_by_keys($items, $keys) {
        $pos  = array_flip(array_values(array_unique(array_map('strval', (array) $keys))));
        $head = array();
        $tail = array();
        foreach ($items as $it) {
            if (isset($pos[$it['key']])) {
                $head[$pos[$it['key']]] = $it;
            } else {
                $tail[] = $it;
            }
        }
        ksort($head);
        return array_merge(array_values($head), $tail);
    }

    /** Entries (and their child rows) arranged by the saved order; anything unlisted keeps its default place after the listed ones. */
    public static function ordered_entries($entries, $type, $tab, $order) {
        $slots = (isset($order[$type]) && is_array($order[$type])) ? $order[$type] : array();
        if (!empty($slots[$tab])) {
            $entries = self::sort_by_keys($entries, $slots[$tab]);
        }
        foreach ($entries as $i => $e) {
            $s = self::order_slot($tab, $e['key']);
            if (!empty($e['rows']) && !empty($slots[$s])) {
                $entries[$i]['rows'] = self::sort_by_keys($e['rows'], $slots[$s]);
            }
        }
        return $entries;
    }

    /** Accepts an array or a JSON string; keeps only known keys and only orders that differ from the default. */
    public static function sanitize_order($raw) {
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $raw     = is_array($decoded) ? $decoded : array();
        }
        if (!is_array($raw)) {
            return array();
        }
        $out = array();
        $cat = self::catalogue();
        foreach (array_keys(self::elements()) as $type) {
            if (empty($raw[$type]) || !is_array($raw[$type])) {
                continue;
            }
            foreach ($cat as $tab => $entries) {
                $tops = array();
                foreach ($entries as $e) {
                    $tops[] = $e['key'];
                }
                $want = (isset($raw[$type][$tab]) && is_array($raw[$type][$tab])) ? self::clean_keys($raw[$type][$tab], $tops) : array();
                if ($want && array_merge($want, array_values(array_diff($tops, $want))) !== $tops) {
                    $out[$type][$tab] = $want;
                }
                foreach ($entries as $e) {
                    $s = self::order_slot($tab, $e['key']);
                    if (empty($e['rows']) || empty($raw[$type][$s]) || !is_array($raw[$type][$s])) {
                        continue;
                    }
                    $kids = array();
                    foreach ($e['rows'] as $r) {
                        $kids[] = $r['key'];
                    }
                    $want = self::clean_keys($raw[$type][$s], $kids);
                    if ($want && array_merge($want, array_values(array_diff($kids, $want))) !== $kids) {
                        $out[$type][$s] = $want;
                    }
                }
            }
        }
        return $out;
    }

    /** tab => list of {k: entry key, r: [child keys]} in default order (builder payload). */
    public static function tree() {
        $tree = array();
        foreach (self::catalogue() as $tab => $entries) {
            foreach ($entries as $e) {
                $kids = array();
                foreach ($e['rows'] as $r) {
                    $kids[] = $r['key'];
                }
                $tree[$tab][] = array('k' => $e['key'], 'r' => $kids);
            }
        }
        return $tree;
    }

    public static function get_order() {
        $s   = get_option('zappoppop_settings', array());
        $raw = (is_array($s) && isset($s['builder_layer_order'])) ? $s['builder_layer_order'] : array();
        return self::sanitize_order($raw);
    }

    /** Accepts an array or a JSON string; returns only values that differ from the defaults. */
    public static function sanitize($raw) {
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $raw = is_array($decoded) ? $decoded : array();
        }
        if (!is_array($raw)) {
            return array();
        }
        $out = array();
        $cat = self::catalogue();
        foreach (array_keys(self::elements()) as $type) {
            if (empty($raw[$type]) || !is_array($raw[$type])) {
                continue;
            }
            foreach ($cat as $tab => $entries) {
                if (empty($raw[$type][$tab]) || !is_array($raw[$type][$tab])) {
                    continue;
                }
                foreach (self::flat($entries) as $e) {
                    if (!array_key_exists($e['key'], $raw[$type][$tab])) {
                        continue;
                    }
                    $v   = filter_var($raw[$type][$tab][$e['key']], FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
                    $def = self::default_on($type, $e) ? 1 : 0;
                    if ($v !== $def) {
                        $out[$type][$tab][$e['key']] = $v;
                    }
                }
            }
        }
        return $out;
    }

    /** Effective state of a setting for an element type. */
    public static function is_on($type, $tab, $entry, $overrides) {
        if (isset($overrides[$type][$tab]) && array_key_exists($entry['key'], $overrides[$type][$tab])) {
            return !empty($overrides[$type][$tab][$entry['key']]);
        }
        return self::default_on($type, $entry);
    }

    /** Data passed to the builder (zappoppop_vars.layer_settings). */
    public static function builder_payload() {
        $ov   = self::get_overrides();
        $cat  = self::catalogue();
        $sel  = array();
        $tabs = array();
        foreach ($cat as $tab => $entries) {
            foreach (self::flat($entries) as $e) {
                $sel[$tab][$e['key']] = array('s' => $e['sel'], 'd' => $e['d']);
            }
        }
        foreach (array_keys(self::elements()) as $type) {
            foreach ($cat as $tab => $entries) {
                $on = false;
                foreach ($entries as $e) {
                    if (self::is_on($type, $tab, $e, $ov)) {
                        $on = true;
                        break;
                    }
                }
                $tabs[$type][$tab] = $on;
            }
        }
        return array(
            'overrides' => empty($ov) ? new stdClass() : $ov,
            'selectors' => $sel,
            'tabs'      => $tabs,
            'order'     => ($ord = self::get_order()) ? $ord : new stdClass(),
            'element_order' => self::get_element_order(), /* ZPP_ELEMENT_ORDER_PAYLOAD: builder ribbon follows this */
            'tree'      => self::tree(),
        );
    }
}
