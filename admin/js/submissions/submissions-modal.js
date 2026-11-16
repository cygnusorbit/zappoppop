(function(window, $) {
    'use strict';
    window.ZapPopPopSubmissions = window.ZapPopPopSubmissions || {};

    function escHtml(v) { return $('<div>').text(v == null ? '' : String(v)).html(); }

    var Modal = {
        init: function() {
            this.bindInspector();
            this.bindDismissal();
            this.bindResend();
        },

        bindInspector: function() {
            $('.zappoppop-view-sub-btn').on('click', function() {
                var $btn = $(this);
                var id = $btn.data('id');
                var email = $btn.data('email');
                var popup = $btn.data('popup');
                var date = $btn.data('date');
                var country = $btn.data('country');
                var fields = $btn.data('fields') || {};

                $('#zappoppop-modal-id').text('#' + id);
                $('#zappoppop-submission-modal').attr('data-sub-id', id);
                $('#zappoppop-sub-resend-msg').text('').css('color', '#64748b');
                $('#zappoppop-sub-resend-target').val('all');
                (function() {
                    var v = window.zappoppop_vars || {};
                    var $readRow = $btn.closest('tr.zappoppop-sub-row');
                    if (v.ajax_url && id && $readRow.hasClass('is-unread')) {
                        $.post(v.ajax_url, { action: 'zappoppop_mark_submission_read', nonce: v.nonce, id: id }).done(function(res) {
                            if (res && res.success) {
                                $readRow.removeClass('is-unread');
                                $(document).trigger('zappoppop:subs-read-changed');
                            }
                        });
                    }
                })();
                $('#zappoppop-modal-email').text(email);
                $('#zappoppop-modal-meta').text(popup + ' • ' + date + ' • Country: ' + country);

                var $fieldsList = $('#zappoppop-modal-fields-list');
                $fieldsList.empty();

                var $utmList = $('#zappoppop-modal-utm-list');
                $utmList.empty();
                var hasUtm = false;
                var hasSig = false;

                $.each(fields, function(key, val) {
                    if (key === 'signature' && val && String(val).indexOf('data:image/') === 0) {
                        hasSig = true;
                        $('#zappoppop-modal-sig-img').attr('src', val);
                    } else if (key.indexOf('utm_') === 0) {
                        hasUtm = true;
                        $utmList.append('<span style="background:#e2e8f0;padding:2px 8px;border-radius:0px;"><strong>' + key + ':</strong> ' + val + '</span>');
                    } else {
                        var displayVal = Array.isArray(val) ? val.join(', ') : val;
                        if (key.charAt(0) === '_') { key = key.substring(1); }
                        $fieldsList.append('<div style="background:#f8fafc;padding:8px 12px;border-radius:0px;border:1px solid #e2e8f0;"><label style="display:block;font-size:11px;color:#64748b;font-weight:600;text-transform:uppercase;">' + escHtml(key) + '</label><div style="font-weight:600;font-size:13px;color:#1e293b;">' + escHtml(displayVal) + '</div></div>');
                    }
                });

                $('#zappoppop-modal-sig-wrap').toggle(hasSig);
                $('#zappoppop-modal-utm-wrap').toggle(hasUtm);

                $('#zappoppop-submission-modal').css('display', 'flex');
            });
        },

        bindDismissal: function() {
            $('#zappoppop-sub-modal-close, #zappoppop-sub-modal-done').on('click', function() {
                $('#zappoppop-submission-modal').hide();
            });

            $('#zappoppop-submission-modal').on('click', function(e) {
                if (e.target === this) {
                    $(this).hide();
                }
            });
        },

        bindResend: function() {
            $('#zappoppop-sub-modal-resend').on('click', function() {
                var v = window.zappoppop_vars || {};
                var $btn = $(this), $msg = $('#zappoppop-sub-resend-msg');
                var id = $('#zappoppop-submission-modal').attr('data-sub-id');
                if (!v.ajax_url || !id || $btn.prop('disabled')) { return; }
                $btn.prop('disabled', true);
                $msg.css('color', '#64748b').text('Sending...');
                $.post(v.ajax_url, {
                    action: 'zappoppop_resend_notification',
                    nonce: v.nonce,
                    id: id,
                    target: $('#zappoppop-sub-resend-target').val()
                }).done(function(res) {
                    var ok = !!(res && res.success);
                    $msg.css('color', ok ? '#15803d' : '#b91c1c').text((res && res.data && res.data.message) || (ok ? 'Sent.' : 'Failed.'));
                }).fail(function(xhr) {
                    var m = xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message;
                    $msg.css('color', '#b91c1c').text(m || 'Request failed.');
                }).always(function() {
                    $btn.prop('disabled', false);
                });
            });
        }
    };

    window.ZapPopPopSubmissions.Modal = Modal;
})(window, jQuery);
