(function(window, $) {
    'use strict';
    window.ZapPopPopPayments = window.ZapPopPopPayments || {};

    var Modal = {
        init: function() {
            this.bindInspector();
            this.bindDismissal();
            this.bindPrint();
        },

        bindInspector: function() {
            $('.zappoppop-view-receipt-btn').on('click', function() {
                var $btn = $(this);
                $('#zappoppop-rcpt-txid').text($btn.data('txid'));
                $('#zappoppop-rcpt-email').text($btn.data('email'));
                $('#zappoppop-rcpt-popup').text($btn.data('popup'));
                $('#zappoppop-rcpt-date').text($btn.data('date'));
                $('#zappoppop-rcpt-gateway').text($btn.data('gateway'));
                $('#zappoppop-rcpt-status').text($btn.data('status'));
                
                var formatted = '$' + $btn.data('amount') + ' ' + $btn.data('currency');
                $('#zappoppop-rcpt-amount').text(formatted);
                $('#zappoppop-rcpt-total').text(formatted);

                $('#zappoppop-receipt-modal').css('display', 'flex');
            });
        },

        bindDismissal: function() {
            $('#zappoppop-receipt-close, #zappoppop-rcpt-done').on('click', function() {
                $('#zappoppop-receipt-modal').hide();
            });

            $('#zappoppop-receipt-modal').on('click', function(e) {
                if (e.target === this) {
                    $(this).hide();
                }
            });
        },

        bindPrint: function() {
            $('#zappoppop-rcpt-print').on('click', function() {
                window.print();
            });
        }
    };

    window.ZapPopPopPayments.Modal = Modal;
})(window, jQuery);
