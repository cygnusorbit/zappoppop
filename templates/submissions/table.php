<?php
if (!defined('ABSPATH')) {
    exit;
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound


$colspan = 1 + count($active_cols);

if (!function_exists('zappoppop_subs_bulk_bar')) {
    function zappoppop_subs_bulk_bar($pos, $labels) {
        ?>
        <div class="tablenav <?php echo esc_attr($pos); ?>">
            <div class="alignleft actions bulkactions">
                <select class="zappoppop-bulk-select">
                    <option value=""><?php esc_html_e('Bulk actions', 'zappoppop'); ?></option>
                    <option value="delete"><?php esc_html_e('Delete', 'zappoppop'); ?></option>
                    <option value="anonymize"><?php esc_html_e('Anonymize (GDPR)', 'zappoppop'); ?></option>
                    <?php foreach ((array) $labels as $l) : if (empty($l['id'])) { continue; } ?>
                        <option value="label:<?php echo esc_attr($l['id']); ?>"><?php /* translators: %s: label name */ echo esc_html( sprintf( __( 'Add label: %s', 'zappoppop' ), $l['name'] ) ); ?></option>
                    <?php endforeach; ?>
                    <option value="unlabel"><?php esc_html_e('Remove all labels', 'zappoppop'); ?></option>
                </select>
                <button type="button" class="button zappoppop-bulk-apply"><?php esc_html_e('Apply', 'zappoppop'); ?></button>
            </div>
            <div class="tablenav-pages zappoppop-pager">
                <span class="displaying-num"></span>
                <span class="pagination-links">
                    <button type="button" class="button zappoppop-pg-first">&laquo;</button>
                    <button type="button" class="button zappoppop-pg-prev">&lsaquo;</button>
                    <span class="paging-input"><span class="zappoppop-pg-cur">1</span> of <span class="zappoppop-pg-total">1</span></span>
                    <button type="button" class="button zappoppop-pg-next">&rsaquo;</button>
                    <button type="button" class="button zappoppop-pg-last">&raquo;</button>
                </span>
            </div>
        </div>
        <?php
    }
}
?>
<?php ZapPopPop_Inline_Assets::style('submissions-table'); ?><div class="zappoppop-subs-list">
<?php zappoppop_subs_bulk_bar('top', $tbl_opts['labels']); ?>
<table class="wp-list-table widefat fixed striped table-view-list" id="zappoppop-submissions-table" data-per-page="<?php echo esc_attr($tbl_opts['per_page']); ?>">
    <thead>
        <tr>
            <td class="manage-column column-cb check-column" style="width:30px;"><input type="checkbox" class="zappoppop-check-all"></td>
            <?php foreach ($active_cols as $ci => $c) : ?>
                <th class="zappoppop-sortable" data-sort="<?php echo $c === 'date' ? 'date' : 'text'; ?>" <?php echo $ci === 0 ? 'style="width:190px;"' : ''; ?>><?php echo esc_html($avail_cols[$c]); ?> &#8693;</th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody id="zappoppop-submissions-body">
    <?php if (empty($submissions)) : ?>
        <tr class="zappoppop-empty-row">
            <td colspan="<?php echo (int) $colspan; ?>" style="text-align:center;padding:30px 10px;color:#64748b;">
                <?php esc_html_e('No leads captured yet. Published popups with form elements will log submission records here.', 'zappoppop'); ?>
            </td>
        </tr>
    <?php else : foreach ($submissions as $s) :
        $fields = json_decode($s->fields_data, true);
        if (!is_array($fields)) {
            $fields = [];
        }
        $fields = ZapPopPop_Module_Bridge::record_fields($fields, $s->popup_uid); /* ZAPPOPPOP_PROTECT_RECORD */
        $is_confirmed = ($s->status === 'confirmed');
        $is_unread    = empty($s->is_read);
        $dot_col      = in_array('popup', $active_cols, true) ? 'popup' : reset($active_cols);
        $unread_dot   = '<span class="zappoppop-unread-dot" title="' . esc_attr__('Unread', 'zappoppop') . '" aria-label="' . esc_attr__('Unread', 'zappoppop') . '"></span>';
        $country      = !empty($s->country_code) ? $s->country_code : '—';
        $ts           = strtotime($s->created_at);
        $date_label   = $ts ? date_i18n('j/n/Y g:i A', $ts) : $s->created_at;
        $popup_title  = $s->popup_title ? $s->popup_title : __('Untitled Popup', 'zappoppop');
        $row_labels   = [];
        if (!empty($label_map[$s->id]) && is_array($label_map[$s->id])) {
            foreach ($label_map[$s->id] as $lid) {
                if (isset($labels_by_id[$lid])) {
                    $row_labels[] = $labels_by_id[$lid];
                }
            }
        }
        $view_attrs = 'data-id="' . esc_attr($s->id) . '" data-email="' . esc_attr($s->email) . '" data-popup="' . esc_attr($popup_title)
            . '" data-date="' . esc_attr($s->created_at) . '" data-country="' . esc_attr($country)
            . "\" data-fields='" . esc_attr(wp_json_encode($fields)) . "'";
        ?>
        <tr class="zappoppop-sub-row <?php echo $is_confirmed ? 'is-confirmed' : 'is-pending'; ?><?php echo $is_unread ? ' is-unread' : ''; ?>"
            data-id="<?php echo esc_attr($s->id); ?>"
            data-status="<?php echo $is_confirmed ? 'confirmed' : 'pending'; ?>"
            data-ts="<?php echo esc_attr((int) $ts); ?>"
            data-email="<?php echo esc_attr(strtolower($s->email)); ?>"
            data-title="<?php echo esc_attr(strtolower($popup_title)); ?>">
            <th scope="row" class="check-column"><input type="checkbox" class="zappoppop-row-check" value="<?php echo esc_attr($s->id); ?>"></th>
            <?php foreach ($active_cols as $ci => $c) :
                if ($c === 'date')         { $val = $date_label; }
                elseif ($c === 'popup')    { $val = $popup_title; }
                elseif ($c === 'email')    { $val = $s->email; }
                elseif ($c === 'country')  { $val = $country; }
                elseif ($c === 'status')   { $val = ucfirst($s->status); }
                else {
                    $fk  = substr($c, 6);
                    $val = '';
                    foreach ([$fk, strtolower($fk), 'fields' . strtolower($fk), 'fields_' . strtolower($fk)] as $__try) {
                        if (isset($fields[$__try])) {
                            $val = $fields[$__try];
                            break;
                        }
                    }
                    if ($val === '' || $val === null) {
                        $__want = preg_replace('/^fields/', '', preg_replace('/[^a-z0-9]/', '', strtolower($fk)));
                        foreach ($fields as $__fk2 => $__fv2) {
                            $__have = preg_replace('/^fields/', '', preg_replace('/[^a-z0-9]/', '', strtolower((string) $__fk2)));
                            if ($__want !== '' && $__have === $__want) { $val = $__fv2; break; }
                        }
                    }
                    // Cross-popup fallback: match by field label inside this row's own popup.
                    if (($val === '' || $val === null) && !empty($popup_field_maps[$s->popup_uid])) {
                        $__want_lbl = strtolower((string) $avail_cols[$c]);
                        $__want_key = sanitize_key($fk);
                        foreach ($popup_field_maps[$s->popup_uid] as $__mk => $__ml) {
                            if (strtolower($__ml) !== $__want_lbl && $__mk !== $__want_key) {
                                continue;
                            }
                            foreach ([$__mk, 'fields' . $__mk, 'fields_' . $__mk] as $__try2) {
                                if (isset($fields[$__try2]) && $fields[$__try2] !== '') {
                                    $val = $fields[$__try2];
                                    break 2;
                                }
                            }
                            $__nk2 = preg_replace('/[^a-z0-9]/', '', strtolower($__mk));
                            foreach ($fields as $__fk3 => $__fv3) {
                                if (preg_replace('/^fields/', '', preg_replace('/[^a-z0-9]/', '', strtolower((string) $__fk3))) === $__nk2 && $__fv3 !== '') {
                                    $val = $__fv3;
                                    break 2;
                                }
                            }
                        }
                    }
                    $val = is_array($val) ? implode(', ', $val) : $val;
                }
                $val = ($val === '' || $val === null) ? '—' : (string) $val;
                $cls = ($c === 'email' || stripos($c, 'mail') !== false) ? 'col-email' : '';
                if ($ci === 0) : ?>
                    <td class="col-first <?php echo esc_attr($cls); ?>" data-sortval="<?php echo esc_attr(strtolower($val)); ?>">
                        <?php
                        $row_label_ids = [];
                        foreach ($row_labels as $rl) { $row_label_ids[] = $rl['id']; }
                        ?>
                        <span class="zappoppop-label-slot<?php echo $row_label_ids ? ' has-labels' : ''; ?>" data-labels="<?php echo esc_attr(wp_json_encode($row_label_ids)); ?>">
                            <button type="button" class="zappoppop-label-btn" aria-haspopup="true" title="<?php esc_attr_e('Labels', 'zappoppop'); ?>" aria-label="<?php esc_attr_e('Labels', 'zappoppop'); ?>">
                                <span class="zappoppop-label-dots"><?php foreach ($row_labels as $rl) : ?><span class="zappoppop-label-dot" style="background:<?php echo esc_attr($rl['color']); ?>;" title="<?php echo esc_attr($rl['name']); ?>"></span><?php endforeach; ?></span>
                                <svg class="zappoppop-bookmark" viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false"><path d="M6 3h12v18l-6-4-6 4z" fill="currentColor"/></svg>
                            </button>
                        </span>
                        <span class="zappoppop-mail" aria-hidden="true">
                            <svg class="m-closed" viewBox="0 0 24 24" width="16" height="16" focusable="false"><rect x="3" y="5" width="18" height="14" rx="2" fill="none" stroke="currentColor" stroke-width="2"/><path d="M3 7l9 6 9-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg>
                            <svg class="m-open" viewBox="0 0 24 24" width="16" height="16" focusable="false"><path d="M3 10l9-6 9 6v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M3 10l9 6 9-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg>
                        </span>
                        <?php echo ($c === $dot_col) ? wp_kses_post($unread_dot) : ''; ?><a href="#" class="zappoppop-view-sub-btn" <?php echo wp_kses_post( $view_attrs ); ?>><?php echo esc_html($val); ?></a>
                        <div class="row-actions">
                            <span class="view"><a href="#" class="zappoppop-view-sub-btn" <?php echo wp_kses_post( $view_attrs ); ?>><?php esc_html_e('View', 'zappoppop'); ?></a> | </span>
                            <span class="mark-unread-wrap"><a href="#" class="zappoppop-mark-unread-btn" data-id="<?php echo esc_attr($s->id); ?>"><?php esc_html_e('Mark as unread', 'zappoppop'); ?></a> | </span>
                            <span class="mark-read-wrap"><a href="#" class="zappoppop-mark-read-btn" data-id="<?php echo esc_attr($s->id); ?>"><?php esc_html_e('Mark as read', 'zappoppop'); ?></a> | </span>
                            <span class="anon"><a href="#" class="zappoppop-anon-sub-btn" data-id="<?php echo esc_attr($s->id); ?>" title="<?php esc_attr_e('Anonymize PII (GDPR)', 'zappoppop'); ?>"><?php esc_html_e('Anonymize', 'zappoppop'); ?></a> | </span>
                            <span class="trash"><a href="#" class="zappoppop-del-sub-btn" data-id="<?php echo esc_attr($s->id); ?>"><?php esc_html_e('Delete', 'zappoppop'); ?></a></span>
                        </div>
                    </td>
                <?php else : ?>
                    <td class="<?php echo esc_attr($cls); ?>" data-sortval="<?php echo esc_attr(strtolower($val)); ?>"><?php echo ($c === $dot_col) ? wp_kses_post($unread_dot) : ''; /* ZAPPOPPOP_SUB_STATUS_BADGE */
                        if ($c === 'status') : ?><span class="zappoppop-sub-status <?php echo $is_confirmed ? 'is-confirmed' : 'is-pending'; ?>"><?php echo $is_confirmed ? esc_html__('Confirmed', 'zappoppop') : esc_html__('Pending', 'zappoppop'); ?></span><?php else : echo esc_html($val); endif; ?></td>
                <?php endif;
            endforeach; ?>
        </tr>
    <?php endforeach; endif; ?>
    </tbody>
    <tfoot>
        <tr>
            <td class="manage-column column-cb check-column"><input type="checkbox" class="zappoppop-check-all"></td>
            <?php foreach ($active_cols as $c) : ?>
                <th><?php echo esc_html($avail_cols[$c]); ?></th>
            <?php endforeach; ?>
        </tr>
    </tfoot>
</table>
<?php
$zappoppop_lbl_text = function ($hex) {
    $h = ltrim((string) $hex, '#');
    if (strlen($h) === 3) { $h = $h[0] . $h[0] . $h[1] . $h[1] . $h[2] . $h[2]; }
    if (strlen($h) !== 6 || !ctype_xdigit($h)) { return '#ffffff'; }
    $y = (hexdec(substr($h, 0, 2)) * 299 + hexdec(substr($h, 2, 2)) * 587 + hexdec(substr($h, 4, 2)) * 114) / 1000;
    return $y > 200 ? '#1d2327' : '#ffffff';
};
?>
<div id="zappoppop-label-menu" role="menu" aria-label="<?php esc_attr_e('Labels', 'zappoppop'); ?>">
    <?php $__has_lbl = false; foreach ((array) $tbl_opts['labels'] as $__l) : if (empty($__l['id'])) { continue; } $__has_lbl = true; $__col = !empty($__l['color']) ? $__l['color'] : '#f0c330'; ?>
        <button type="button" class="zappoppop-label-opt" role="menuitemcheckbox" aria-checked="false" data-label-id="<?php echo esc_attr($__l['id']); ?>" data-color="<?php echo esc_attr($__col); ?>" data-name="<?php echo esc_attr($__l['name']); ?>" style="background:<?php echo esc_attr($__col); ?>;color:<?php echo esc_attr($zappoppop_lbl_text($__col)); ?>;"><span class="chk">&#10003;</span><?php echo esc_html($__l['name']); ?></button>
    <?php endforeach; if (!$__has_lbl) : ?>
        <div class="zappoppop-label-empty"><?php esc_html_e('No labels yet. Add some in Table options.', 'zappoppop'); ?></div>
    <?php endif; ?>
</div>
<?php zappoppop_subs_bulk_bar('bottom', $tbl_opts['labels']); ?>
</div>
