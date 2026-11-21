<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- template variables are local to the method that includes this file
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
require_once ZAPPOPPOP_PATH . 'includes/admin/class-zappoppop-list-table.php';

$list_table = new ZapPopPop_List_Table();
$list_table->prepare_items();
?>
<?php ZapPopPop_Inline_Assets::style('dashboard-table'); ?><div class="zappoppop-table-wrap" style="width:100%;margin-top:16px;">
    <?php if (!empty($_GET['trashed'])) : 
        $trashed_count = intval($_GET['trashed']);
        $trashed_uid   = !empty($_GET['trashed_uid']) ? sanitize_text_field(wp_unslash($_GET['trashed_uid'])) : '';
        $undo_url      = '';
        if ($trashed_count === 1 && $trashed_uid) {
            $undo_url = wp_nonce_url(
                admin_url('admin.php?page=zappoppop&action=untrash&uid=' . rawurlencode($trashed_uid)),
                'zappoppop_untrash_' . $trashed_uid
            );
        }
    ?>
        <div class="notice notice-success is-dismissible" style="margin: 12px 0 16px 0;">
            <p>
                <?php 
                echo esc_html(sprintf(
                    /* translators: number of campaigns. */ _n('%d campaign moved to the Trash.', '%d campaigns moved to the Trash.', $trashed_count, 'zappoppop'),
                    number_format_i18n($trashed_count)
                )); 
                if ($undo_url) : ?>
                    <a href="<?php echo esc_url($undo_url); ?>"><?php esc_html_e('Undo', 'zappoppop'); ?></a>
                <?php endif; ?>
            </p>
        </div>
    <?php elseif (!empty($_GET['untrashed'])) : 
        $untrashed_count = intval($_GET['untrashed']);
    ?>
        <div class="notice notice-success is-dismissible" style="margin: 12px 0 16px 0;">
            <p>
                <?php 
                echo esc_html(sprintf(
                    /* translators: number of campaigns. */ _n('%d campaign restored from the Trash.', '%d campaigns restored from the Trash.', $untrashed_count, 'zappoppop'),
                    number_format_i18n($untrashed_count)
                )); 
                ?>
            </p>
        </div>
    <?php elseif (!empty($_GET['deleted'])) : 
        $deleted_count = intval($_GET['deleted']);
    ?>
        <div class="notice notice-success is-dismissible" style="margin: 12px 0 16px 0;">
            <p>
                <?php 
                echo esc_html(sprintf(
                    /* translators: number of campaigns. */ _n('%d campaign permanently deleted.', '%d campaigns permanently deleted.', $deleted_count, 'zappoppop'),
                    number_format_i18n($deleted_count)
                )); 
                ?>
            </p>
        </div>
    <?php elseif (!empty($_GET['duplicated'])) :
        $duplicated_count = intval($_GET['duplicated']);
    ?>
        <div class="notice notice-success is-dismissible" style="margin: 12px 0 16px 0;">
            <p><?php echo esc_html(sprintf(/* translators: number of campaigns. */ _n('%d campaign duplicated (saved as Draft).', '%d campaigns duplicated (saved as Draft).', $duplicated_count, 'zappoppop'), number_format_i18n($duplicated_count))); ?></p>
        </div>
    <?php elseif (!empty($_GET['updated'])) : 
        $updated_count = intval($_GET['updated']);
    ?>
        <div class="notice notice-success is-dismissible" style="margin: 12px 0 16px 0;">
            <p>
                <?php 
                echo esc_html(sprintf(
                    /* translators: number of campaigns. */ _n('%d campaign status updated.', '%d campaigns status updated.', $updated_count, 'zappoppop'),
                    number_format_i18n($updated_count)
                )); 
                ?>
            </p>
        </div>
    <?php endif; ?>

    <?php wp_nonce_field('zappoppop_admin_nonce', 'zappoppop_admin_nonce_field'); ?>
    <?php
    if (wp_script_is('zappoppop-dashboard-js', 'registered')) {
        wp_add_inline_script(
            'zappoppop-dashboard-js',
            'window.zappoppop_vars = window.zappoppop_vars || {};'
            . 'window.zappoppop_vars.ajax_url = ' . wp_json_encode(admin_url('admin-ajax.php')) . ';'
            . 'window.zappoppop_vars.nonce = ' . wp_json_encode(wp_create_nonce('zappoppop_admin_nonce')) . ';',
            'before'
        );
    }
    ?>

    <form id="zappoppop-campaigns-table-form" method="get">
        <input type="hidden" name="page" value="zappoppop" />
        <?php if (!empty($_GET['status'])) : ?>
            <input type="hidden" name="status" value="<?php echo esc_attr(sanitize_key($_GET['status'])); ?>" />
        <?php endif; ?>
        <?php wp_nonce_field('bulk-popups'); ?>
        <?php
        /* ZAPPOPPOP_LIST_TOOLBAR: status filter + search on one row */
        /* ZAPPOPPOP_P19_TOOLBAR: WP_List_Table prints its own escaped markup; nothing is buffered or echoed from a variable */
        echo '<div class="zappoppop-list-toolbar">';
        $list_table->views();
        $list_table->search_box(__('Search Popups', 'zappoppop'), 'zappoppop');
        echo '</div>';
        $list_table->display();
        ?>
    </form>

    <!-- WordPress Standard Quick Edit Template -->
    <table style="display:none;">
        <tbody id="zappoppop-quick-edit-template-root">
            <tr id="zappoppop-inline-edit" class="inline-edit-row inline-edit-row-post quick-edit-row quick-edit-row-post">
                <td colspan="7" class="colspanchange">
                    <fieldset class="inline-edit-col-left">
                        <legend class="inline-edit-legend"><?php esc_html_e('Quick Edit', 'zappoppop'); ?></legend>
                        <div class="inline-edit-col">
                            <label>
                                <span class="title"><?php esc_html_e('Title', 'zappoppop'); ?></span>
                                <span class="input-text-wrap">
                                    <input type="text" name="popup_title" class="ptitle zappoppop-qe-input-title" value="">
                                </span>
                            </label>
                            <label>
                                <span class="title"><?php esc_html_e('UID', 'zappoppop'); ?></span>
                                <span class="input-text-wrap">
                                    <input type="text" name="popup_uid" class="puid zappoppop-qe-input-uid" value="" readonly style="background:#f0f0f1;color:#646970;font-family:monospace;">
                                </span>
                            </label>
                        </div>
                    </fieldset>

                    <fieldset class="inline-edit-col-right">
                        <legend class="inline-edit-legend"><?php esc_html_e('Status', 'zappoppop'); ?></legend>
                        <div class="inline-edit-col">
                            <label class="inline-edit-status">
                                <span class="title"><?php esc_html_e('Status', 'zappoppop'); ?></span>
                                <select name="popup_status" class="zappoppop-qe-input-status">
                                    <option value="publish"><?php esc_html_e('Published', 'zappoppop'); ?></option>
                                    <option value="draft"><?php esc_html_e('Draft', 'zappoppop'); ?></option>
                                </select>
                            </label>
                        </div>
                    </fieldset>

                    <div class="submit inline-edit-save">
                        <button type="button" class="button cancel alignleft zappoppop-qe-btn-cancel"><?php esc_html_e('Cancel', 'zappoppop'); ?></button>
                        <button type="button" class="button button-primary save alignright zappoppop-qe-btn-save"><?php esc_html_e('Update', 'zappoppop'); ?></button>
                        <span class="spinner zappoppop-qe-spinner alignright" style="float:right;margin-top:4px;"></span>
                        <div class="notice notice-error notice-alt zappoppop-qe-error-notice" style="display:none;clear:both;margin-top:8px;">
                            <p class="zappoppop-qe-error-message" style="margin:0;"></p>
                        </div>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>
