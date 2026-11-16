(function(window, $) {
    'use strict';
    window.ZapPopPopLog = window.ZapPopPopLog || {};

    var Search = {
        init: function() {
            var $input = $('#zappoppop-log-search');
            if (!$input.length) return;

            $input.on('input', function() {
                var term = $(this).val().toLowerCase().trim();
                $('.zappoppop-log-row').each(function() {
                    var type = $(this).data('type') || '';
                    var msg  = $(this).data('msg') || '';
                    if (type.indexOf(term) !== -1 || msg.indexOf(term) !== -1) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });
        }
    };

    window.ZapPopPopLog.Search = Search;
})(window, jQuery);
