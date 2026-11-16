/**
 * ZapPopPop Dashboard: Quick Edit + Active toggle
 * Cross-browser: Chrome, Safari, Firefox, Edge (ES5 only)
 */
(function (window, $) {
    'use strict';

    var QE = {
        $row: null,

        vars: function () {
            var v = window.zappoppop_vars || {};
            return {
                ajax_url: v.ajax_url || (typeof window.ajaxurl !== 'undefined' ? window.ajaxurl : ''),
                nonce: v.nonce || $('#zappoppop_admin_nonce_field').val() || ''
            };
        },

        normStatus: function (s) {
            return (s === 'publish' || s === 'published') ? 'publish' : 'draft';
        },

        init: function () {
            var self = this;
            $(document).off('.zappoppopQE');

            $(document).on('click.zappoppopQE', '.zappoppop-quick-edit-btn', function (e) {
                e.preventDefault();
                self.open($(this).closest('tr'));
            });
            $(document).on('click.zappoppopQE', '.zappoppop-qe-btn-cancel', function (e) {
                e.preventDefault();
                self.close();
            });
            $(document).on('click.zappoppopQE', '.zappoppop-qe-btn-save', function (e) {
                e.preventDefault();
                self.save();
            });
            $(document).on('keydown.zappoppopQE', '.zappoppop-qe-active input, .zappoppop-qe-active select', function (e) {
                if (e.key === 'Enter' || e.keyCode === 13) { e.preventDefault(); self.save(); }
            });
            $(document).on('keydown.zappoppopQE', function (e) {
                if ((e.key === 'Escape' || e.keyCode === 27) && $('.zappoppop-qe-active').length) { self.close(); }
            });
            $(document).on('change.zappoppopQE', '.zappoppop-status-toggle-input', function () {
                self.toggle($(this));
            });
        },

        open: function ($row) {
            if (!$row || !$row.length) { return; }
            this.close();

            var uid = $row.attr('data-uid') || '';
            var title = $row.attr('data-raw-title') || $row.find('a.row-title').text();
            var status = this.normStatus($row.attr('data-status'));
            if (!uid) { return; }

            var $tpl = $('#zappoppop-inline-edit');
            if (!$tpl.length) { return; }

            var $edit = $tpl.clone().removeAttr('id').addClass('zappoppop-qe-active').attr('data-uid', uid);
            // Prevent these fields from being submitted with the GET list form.
            $edit.find('[name]').removeAttr('name');
            $edit.find('td.colspanchange').attr('colspan', $row.children('th, td').length);
            $edit.find('.zappoppop-qe-input-title').val(title);
            $edit.find('.zappoppop-qe-input-uid').val(uid);
            $edit.find('.zappoppop-qe-input-status').val(status);

            this.$row = $row;
            $row.hide().after($edit);
            $edit.find('.zappoppop-qe-input-title').trigger('focus');
        },

        close: function () {
            $('.zappoppop-qe-active').remove();
            if (this.$row) { this.$row.show(); this.$row = null; }
        },

        save: function () {
            var self = this;
            var $edit = $('.zappoppop-qe-active');
            if (!$edit.length || !this.$row) { return; }

            var uid = $edit.attr('data-uid');
            var title = $.trim($edit.find('.zappoppop-qe-input-title').val());
            var status = $edit.find('.zappoppop-qe-input-status').val();
            var $err = $edit.find('.zappoppop-qe-error-notice');
            var $btns = $edit.find('.zappoppop-qe-btn-save, .zappoppop-qe-btn-cancel');
            var $spin = $edit.find('.zappoppop-qe-spinner');

            $err.hide();
            if (!title) {
                $edit.find('.zappoppop-qe-error-message').text('Title cannot be empty.');
                $err.show();
                return;
            }

            var v = this.vars();
            $btns.prop('disabled', true);
            $spin.addClass('is-active');

            $.ajax({
                url: v.ajax_url, type: 'POST', dataType: 'json',
                data: { action: 'zappoppop_quick_edit', nonce: v.nonce, uid: uid, title: title, status: status }
            }).done(function (res) {
                if (res && res.success && res.data) {
                    var $row = self.$row;
                    self.$row = null;
                    $('.zappoppop-qe-active').remove();
                    self.applyRow($row, res.data);
                    self.applyCounts(res.data.counts);
                    $row.show();
                    if (res.data.old_uid && res.data.uid && res.data.uid !== res.data.old_uid) {
                        window.location.reload(); /* ZAPPOPPOP_QE_UID_RELOAD: refresh shortcode, links and nonces */
                    }
                } else {
                    $edit.find('.zappoppop-qe-error-message').text((res && res.data && res.data.message) || 'Update failed.');
                    $err.show();
                }
            }).fail(function () {
                $edit.find('.zappoppop-qe-error-message').text('Server error while saving.');
                $err.show();
            }).always(function () {
                $btns.prop('disabled', false);
                $spin.removeClass('is-active');
            });
        },

        applyRow: function ($row, d) {
            var status = this.normStatus(d.status);
            $row.attr('data-raw-title', d.title).attr('data-status', status);
            $row.find('a.row-title').text(d.title);
            $row.find('.zappoppop-quick-edit-btn').attr('data-title', d.title).attr('data-status', status);
            $row.find('.zappoppop-action-embed').attr('data-status', status);

            var $strong = $row.find('td.column-title > strong').first();
            $strong.find('.post-state, .zappoppop-inactive-badge, .zappoppop-active-badge').remove();
            if (status !== 'publish') {
                $strong.append(' <span class="zappoppop-inactive-badge">INACTIVE</span>');
            } else {
                $strong.append(' <span class="zappoppop-active-badge">ACTIVE</span>');
            }
            $row.find('.zappoppop-status-toggle-input').prop('checked', status === 'publish');
        },

        applyCounts: function (c) {
            if (!c) { return; }
            var keys = ['all', 'active', 'inactive', 'trash'];
            var $links = $('.zappoppop-table-wrap .zappoppop-view-btn');
            for (var i = 0; i < keys.length && i < $links.length; i++) {
                $links.eq(i).find('.count').text('(' + (c[keys[i]] || 0) + ')');
            }
        },

        toggle: function ($input) {
            var self = this;
            var uid = $input.attr('data-uid');
            var want = $input.is(':checked');
            var $sw = $input.closest('.zappoppop-switch');
            var $row = $input.closest('tr');
            var v = this.vars();

            $sw.addClass('is-busy');
            $input.prop('disabled', true);

            $.ajax({
                url: v.ajax_url, type: 'POST', dataType: 'json',
                data: { action: 'zappoppop_toggle_status', nonce: v.nonce, uid: uid, active: want ? 1 : 0 }
            }).done(function (res) {
                if (res && res.success && res.data) {
                    self.applyRow($row, { title: $row.attr('data-raw-title') || $row.find('a.row-title').text(), status: res.data.status });
                    self.applyCounts(res.data.counts);
                    // If a filtered view no longer matches, remove the row.
                    var idx = $('.zappoppop-table-wrap .zappoppop-view-btn').index($('.zappoppop-table-wrap .zappoppop-view-btn.current'));
                    var nowOn = self.normStatus(res.data.status) === 'publish';
                    if ((idx === 1 && !nowOn) || (idx === 2 && nowOn)) {
                        $row.fadeOut(250, function () { $(this).remove(); });
                    }
                } else {
                    $input.prop('checked', !want);
                    window.alert((res && res.data && res.data.message) || wp.i18n.__('Could not update status.', 'zappoppop'));
                }
            }).fail(function () {
                $input.prop('checked', !want);
                window.alert(wp.i18n.__('Server error while updating status.', 'zappoppop'));
            }).always(function () {
                $sw.removeClass('is-busy');
                $input.prop('disabled', false);
            });
        }
    };

    window.ZapPopPopDashboardQuickEdit = QE;
    $(function () { QE.init(); });
})(window, jQuery);
