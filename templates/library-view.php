<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- template variables are local to the method that includes this file
if (!defined('ABSPATH')) {
    exit;
}

// Built-in starter templates removed: only saved (My Templates) and premade (library-presets) cards are listed.
$templates = []; /* ZAPPOPPOP_NO_HARDCODED */

// User-saved templates (Builder > Save As Template)
$zappoppop_user_tpls = get_option('zappoppop_user_templates', []);
$zappoppop_user_cards = [];
if (is_array($zappoppop_user_tpls)) {
    foreach (array_reverse($zappoppop_user_tpls) as $ut) {
        if (!is_array($ut) || empty($ut['id'])) { continue; }
        $zappoppop_user_cards[] = [
            'id'          => $ut['id'],
            'category'    => (isset($ut['category']) && $ut['category'] !== '') ? $ut['category'] : 'custom',
            'cat_label'   => __('My Templates', 'zappoppop'),
            'title'       => isset($ut['title']) ? $ut['title'] : '',
            'description' => isset($ut['description']) ? $ut['description'] : '',
            'icon'        => 'dashicons-saved',
            'bg_preview'  => 'linear-gradient(135deg, #00a32a, #0f172a)',
            'image'       => isset($ut['image']) ? $ut['image'] : '',
            'user'        => true,
            'config'      => [
                'meta'     => ['width' => isset($ut['width']) ? (int) $ut['width'] : 640, 'height' => isset($ut['height']) ? (int) $ut['height'] : 400, 'bg_mode' => 'solid', 'bg_color' => '#ffffff'],
                'triggers' => new stdClass(),
                'elements' => isset($ut['elements']) && is_array($ut['elements']) ? $ut['elements'] : [],
            ],
        ];
    }
}

// Premade templates (library-presets/*.json)
if (!class_exists('ZapPopPop_Library_Presets')) { require_once ZAPPOPPOP_PATH . 'includes/class-zappoppop-library-presets.php'; }
$zappoppop_preset_cards = [];
foreach (ZapPopPop_Library_Presets::all() as $zappoppop_pt) {
    $zappoppop_preset_cards[] = [
        'id'          => $zappoppop_pt['id'],
        'category'    => $zappoppop_pt['category'],
        'cat_label'   => __('Premade', 'zappoppop'),
        'title'       => $zappoppop_pt['title'],
        'description' => $zappoppop_pt['description'],
        'icon'        => 'dashicons-layout',
        'bg_preview'  => 'linear-gradient(135deg, #2563eb, #0f172a)',
        'image'       => $zappoppop_pt['image'],
        'preset'      => true,
        'tier'        => isset($zappoppop_pt['tier']) ? $zappoppop_pt['tier'] : 'free',
        'badge'       => isset($zappoppop_pt['badge']) ? $zappoppop_pt['badge'] : null,
        'no_download' => !empty($zappoppop_pt['no_download']),
        'config'      => [
            'meta'     => ['width' => $zappoppop_pt['width'], 'height' => $zappoppop_pt['height'], 'bg_mode' => 'solid', 'bg_color' => '#ffffff'],
            'triggers' => new stdClass(),
            'elements' => $zappoppop_pt['elements'],
        ],
    ];
}
$templates = array_merge($zappoppop_user_cards, $zappoppop_preset_cards, $templates);

// Keep card badges in sync with renamed categories
if (!class_exists('ZapPopPop_Ajax_Templates')) { require_once ZAPPOPPOP_PATH . 'includes/ajax/class-ajax-templates.php'; }
$zappoppop_cat_map = [];
$zappoppop_cat_alias = ZapPopPop_Ajax_Templates::category_aliases(); /* ZPP_CAT_ALIAS */
foreach (ZapPopPop_Ajax_Templates::get_categories() as $zappoppop_c) { $zappoppop_cat_map[$zappoppop_c['slug']] = $zappoppop_c['label']; }
foreach ($templates as $zappoppop_i => $zappoppop_t) {
    if (isset($zappoppop_cat_alias[$zappoppop_t['category']]) && !isset($zappoppop_cat_map[$zappoppop_t['category']])) { $templates[$zappoppop_i]['category'] = $zappoppop_cat_alias[$zappoppop_t['category']]; $zappoppop_t['category'] = $zappoppop_cat_alias[$zappoppop_t['category']]; }
    /* ZAPPOPPOP_TPL_CAT_FALLBACK: deleted category -> My Templates */
    if (!empty($zappoppop_t['user']) && !isset($zappoppop_cat_map[$zappoppop_t['category']])) { $templates[$zappoppop_i]['category'] = 'custom'; $zappoppop_t['category'] = 'custom'; }
    if (isset($zappoppop_cat_map[$zappoppop_t['category']])) { $templates[$zappoppop_i]['cat_label'] = $zappoppop_cat_map[$zappoppop_t['category']]; }
}
?>
<div class="wrap zappoppop-dash zappoppop-library-wrap">
    <!-- Catalog Header Toolbar & Category Filters -->
    <?php include ZAPPOPPOP_PATH . 'templates/library/header.php'; ?>

    <!-- Responsive Template Cards Grid -->
    <?php $zpp_pg_pos = 'top'; include ZAPPOPPOP_PATH . 'templates/library/pager.php'; ?>
    <?php include ZAPPOPPOP_PATH . 'templates/library/grid.php'; ?>
    <?php $zpp_pg_pos = 'bottom'; include ZAPPOPPOP_PATH . 'templates/library/pager.php'; ?>

    <!-- Template Inspection & Preview Modal -->
    <?php include ZAPPOPPOP_PATH . 'templates/library/modal-preview.php'; ?>

    <!-- Category Manager (gear icon) -->
    <?php include ZAPPOPPOP_PATH . 'templates/library/modal-categories.php'; ?>

    <!-- Template info editor (Edit icon) -->
    <?php include ZAPPOPPOP_PATH . 'templates/library/modal-template-edit.php'; ?>
</div>
