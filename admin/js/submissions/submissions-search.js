(function(window, $) {
    'use strict';
    window.ZapPopPopSubmissions = window.ZapPopPopSubmissions || {};

    var Search = {
        init: function() {
            var $input = $('#zappoppop-subs-search');
            if (!$input.length) return;

            var fire = function() {
                $(document).trigger('zappoppop:subs-search', [$input.val().toLowerCase().trim()]);
            };

            $input.on('input keyup', fire);
            $input.on('keydown', function(e) {
                if (e.which === 13) { e.preventDefault(); fire(); }
            });
            $('#zappoppop-subs-search-btn').on('click', function(e) {
                e.preventDefault();
                fire();
            });
        }
    };

    window.ZapPopPopSubmissions.Search = Search;
})(window, jQuery);
