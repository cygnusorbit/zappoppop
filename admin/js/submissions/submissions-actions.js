(function(window, $) {
    'use strict';
    window.ZapPopPopSubmissions = window.ZapPopPopSubmissions || {};

    var Actions = {
        init: function() {
            this.bindExportCsv();
            this.bindAnonymize();
            this.bindDelete();
            this.bindReadToggle();
        },

        getVars: function() {
            return window.zappoppop_vars || {
                ajax_url: ajaxurl || '',
                nonce: ''
            };
        },

        bindExportCsv: function() {
            var self = this;
            $('#zappoppop-btn-export-csv').on('click', function() {
                var vars = self.getVars();
                var EM = window.ZapPopPopSubmissions && window.ZapPopPopSubmissions.ExportModal;
                if (EM && EM.open()) { return; }
                window.location.href = vars.ajax_url + '?action=zappoppop_export_submissions_csv&nonce=' + vars.nonce;
            });
        },

        bindAnonymize: function() {
            var self = this;
            $('.zappoppop-anon-sub-btn').on('click', function() {
                if (!confirm(wp.i18n.__('Anonymize this lead record for GDPR compliance?', 'zappoppop'))) {
                    return;
                }

                var id = $(this).data('id');
                var $row = $(this).closest('tr');
                var vars = self.getVars();

                $.post(vars.ajax_url, {
                    action: 'zappoppop_anonymize_submission',
                    nonce: vars.nonce,
                    id: id
                }).done(function(res) {
                    if (res.success) {
                        $row.find('td:nth-child(2) strong').text('anonymized_' + id + '@privacy.local');
                        alert(res.data.message || wp.i18n.__('Lead PII scrubbed successfully.', 'zappoppop'));
                    } else {
                        alert(wp.i18n.__('Anonymization Error:', 'zappoppop') + ' ' + (res.data ? res.data.message : wp.i18n.__('Action failed.', 'zappoppop')));
                    }
                }).fail(function() {
                    alert(wp.i18n.__('Network error while anonymizing lead.', 'zappoppop'));
                });
            });
        },

        bindReadToggle: function() {
            var self = this;
            $(document).off('click.zppSubRead').on('click.zppSubRead', '.zappoppop-mark-unread-btn, .zappoppop-mark-read-btn', function(e) {
                e.preventDefault();
                var $a = $(this);
                var makeRead = $a.hasClass('zappoppop-mark-read-btn');
                var id = $a.data('id');
                var $row = $a.closest('tr');
                var vars = self.getVars();
                if ($a.data('busy')) { return; }
                $a.data('busy', true);

                $.post(vars.ajax_url, {
                    action: makeRead ? 'zappoppop_mark_submission_read' : 'zappoppop_mark_submission_unread',
                    nonce: vars.nonce,
                    id: id
                }).done(function(res) {
                    if (res && res.success) {
                        $row.toggleClass('is-unread', !makeRead);
                        var L = window.ZapPopPopSubmissions && window.ZapPopPopSubmissions.List;
                        if (L && typeof L.render === 'function') { L.render(); }
                    } else {
                        alert((res && res.data && res.data.message) || wp.i18n.__('Could not update read state.', 'zappoppop'));
                    }
                }).fail(function() {
                    alert(wp.i18n.__('Network error while updating read state.', 'zappoppop'));
                }).always(function() { $a.data('busy', false); });
            });
        },

        bindDelete: function() {
            var self = this;
            $('.zappoppop-del-sub-btn').on('click', function() {
                if (!confirm(wp.i18n.__('Permanently delete this submission record?', 'zappoppop'))) {
                    return;
                }

                var id = $(this).data('id');
                var $row = $(this).closest('tr');
                var vars = self.getVars();

                $.post(vars.ajax_url, {
                    action: 'zappoppop_delete_submission',
                    nonce: vars.nonce,
                    id: id
                }).done(function(res) {
                    if (res.success) {
                        $row.fadeOut(200, function() { $(this).remove(); });
                    } else {
                        alert(wp.i18n.__('Deletion Error:', 'zappoppop') + ' ' + (res.data ? res.data.message : wp.i18n.__('Action failed.', 'zappoppop')));
                    }
                }).fail(function() {
                    alert(wp.i18n.__('Network error while deleting submission.', 'zappoppop'));
                });
            });
        }
    };

    window.ZapPopPopSubmissions.Actions = Actions;
})(window, jQuery);


/* === ZAPPOPPOP_EXPORT_MODAL_START: choose CSV columns for the currently filtered popup === */
(function(window, $) {
    'use strict';
    window.ZapPopPopSubmissions = window.ZapPopPopSubmissions || {};

    var uid = '';

    function vars() { return window.zappoppop_vars || { ajax_url: window.ajaxurl || '', nonce: '' }; }
    function key() { return 'zappoppop_export_cols_' + (uid || '_all'); }
    function esc(t) { return $('<div>').text(t == null ? '' : String(t)).html(); }
    function saved() {
        try { var r = window.localStorage.getItem(key()); return r ? JSON.parse(r) : null; } catch (e) { return null; }
    }
    function store(ids) { try { window.localStorage.setItem(key(), JSON.stringify(ids)); } catch (e) {} }

    function render(cols) {
        var sel = saved();
        var html = '', group = '';
        $.each(cols, function(_, c) {
            if (c.group !== group) {
                group = c.group;
                html += '<div class="wex-group">' + (group === 'field' ? 'Form fields' : 'Standard columns') + '</div>';
            }
            var on = sel ? ($.inArray(c.id, sel) !== -1) : (c.group !== 'core'); /* ZAPPOPPOP_EXPORT_STD_OFF: Standard columns unticked by default */
            html += '<label class="wex-item"><input type="checkbox" class="wex-col" value="' + esc(c.id) + '"' + (on ? ' checked' : '') + '>' + esc(c.label) + '</label>';
        });
        $('#wex-body').html(html || '<div class="wex-msg">No columns available.</div>');
    }

    var Modal = {
        open: function() {
            var $m = $('#zappoppop-export-modal');
            if (!$m.length) { return false; }
            /* ZAPPOPPOP_EXPORT_URL_UID: the popup comes from ?popup_uid= in the page URL (dropdown is only a fallback) */
            var $f = $('#zappoppop-subs-popup-filter');
            var um = /[?&]popup_uid=([^&#]*)/.exec(window.location.search || '');
            uid = '';
            if (um && um[1]) {
                try { uid = decodeURIComponent(um[1].replace(/\+/g, ' ')); } catch (e) { uid = um[1]; }
            } else if ($f.length) {
                uid = $f.val() || '';
            }
            uid = $.trim(uid);
            var uname = '';
            if (uid) {
                $f.find('option').each(function() { if (this.value === uid) { uname = $.trim($(this).text()); return false; } });
            }
            $('#wex-sub').text(uid ? ('Popup: ' + (uname || uid) + (uname && uname !== uid ? '  (' + uid + ')' : '')) : 'All popups');
            $('#wex-body').html('<div class="wex-msg">Loading columns...</div>');
            $m.addClass('is-open');
            var v = vars();
            $.post(v.ajax_url, { action: 'zappoppop_export_columns', nonce: v.nonce, popup_uid: uid }).done(function(res) {
                if (res && res.success && res.data && res.data.columns) { render(res.data.columns); }
                else { $('#wex-body').html('<div class="wex-msg">Could not load columns.</div>'); }
            }).fail(function() {
                $('#wex-body').html('<div class="wex-msg">Network error while loading columns.</div>');
            });
            return true;
        },
        close: function() { $('#zappoppop-export-modal').removeClass('is-open'); },
        go: function() {
            var ids = [];
            $('#wex-body .wex-col:checked').each(function() { ids.push($(this).val()); });
            if (!ids.length) { window.alert(wp.i18n.__('Select at least one column.', 'zappoppop')); return; }
            store(ids);
            var v = vars();
            window.location.href = v.ajax_url + '?action=zappoppop_export_submissions&nonce=' + encodeURIComponent(v.nonce)
                + '&popup_uid=' + encodeURIComponent(uid) + '&columns=' + encodeURIComponent(JSON.stringify(ids));
            Modal.close();
        }
    };

    $(document)
        .off('click.zppExport')
        .on('click.zppExport', '#wex-cancel', function() { Modal.close(); })
        .on('click.zppExport', '#wex-go', function() { Modal.go(); })
        .on('click.zppExport', '#wex-all', function() { $('#wex-body .wex-col').prop('checked', true); })
        .on('click.zppExport', '#wex-none', function() { $('#wex-body .wex-col').prop('checked', false); })
        .on('mousedown.zppExport', '#zappoppop-export-modal', function(e) { if (e.target === this) { Modal.close(); } })
        .on('keydown.zppExport', function(e) { if (e.which === 27 && $('#zappoppop-export-modal').hasClass('is-open')) { Modal.close(); } });

    window.ZapPopPopSubmissions.ExportModal = Modal;
})(window, jQuery);
/* === ZAPPOPPOP_EXPORT_MODAL_END === */
