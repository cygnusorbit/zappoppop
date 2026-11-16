(function(window, $) {
    'use strict';
    window.ZapPopPopPayments = window.ZapPopPopPayments || {};

    var Search = {
        init: function() {
            var $input = $('#zappoppop-payments-search');
            if (!$input.length) return;

            $input.on('input', function() {
                var term = $(this).val().toLowerCase().trim();
                $('.zappoppop-tx-row').each(function() {
                    var email = $(this).data('email') || '';
                    var txid  = $(this).data('txid') || '';
                    if (email.indexOf(term) !== -1 || txid.indexOf(term) !== -1) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });
        }
    };

    window.ZapPopPopPayments.Search = Search;
})(window, jQuery);
