/**
 * ZapPopPop Tools: Maintenance sub-module (purge logs, optimize, orphan report).
 */
(function($) {
    'use strict';

    function vars() {
        return {
            url: (window.zappoppop_vars && window.zappoppop_vars.ajax_url) ? window.zappoppop_vars.ajax_url : (typeof ajaxurl !== 'undefined' ? ajaxurl : ''),
            nonce: (window.zappoppop_vars && window.zappoppop_vars.nonce) ? window.zappoppop_vars.nonce : ''
        };
    }

    function notice(type, msg) {
        $('#zappoppop-maint-notice').removeClass('is-success is-error').addClass(type === 'success' ? 'is-success' : 'is-error').text(msg).prop('hidden', false);
    }

    window.ZapPopPopToolsMaintenance = {
        init: function() {
            $(document).on('click', '[data-zpp-maint]', function(e) {
                e.preventDefault();
                var $btn = $(this);
                var action = $btn.attr('data-zpp-maint');
                var v = vars();
                var data = { action: action, nonce: v.nonce };
                if (action === 'zappoppop_purge_logs') {
                    data.days = parseInt($('#zappoppop-purge-days').val(), 10) || 30;
                    if (!window.confirm(wp.i18n.sprintf(
                    /* translators: %s: number of days. */
                    wp.i18n.__('Delete all log entries older than %s days? This cannot be undone.', 'zappoppop'), data.days))) { return; }
                }
                $btn.prop('disabled', true);
                $.ajax({ url: v.url, type: 'POST', dataType: 'json', data: data })
                    .done(function(res) {
                        if (res && res.success) {
                            notice('success', res.data.message || 'Done.');
                            if (res.data.tables) {
                                $.each(res.data.tables, function(key, t) {
                                    var $row = $('#zappoppop-table-row-' + key);
                                    $row.find('.zappoppop-tbl-rows-count').text(t.rows);
                                    $row.find('.zappoppop-tbl-size-val').text(t.size);
                                });
                            }
                        } else {
                            notice('error', (res && res.data && res.data.message) ? res.data.message : 'Action failed.');
                        }
                    })
                    .fail(function() { notice('error', 'Server communication error.'); })
                    .always(function() { $btn.prop('disabled', false); });
            });
        }
    };

    $(function() { window.ZapPopPopToolsMaintenance.init(); });
})(jQuery);
