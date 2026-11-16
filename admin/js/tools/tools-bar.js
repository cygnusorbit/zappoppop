/**
 * ZapPopPop Tools: Top-bar triggers.
 * Bar buttons proxy to the real card buttons so there is a single code path.
 */
(function($) {
    'use strict';
    $(document).on('click', '[data-zpp-trigger]', function(e) {
        e.preventDefault();
        var $target = $($(this).attr('data-zpp-trigger')).first();
        if ($target.length) {
            $target.trigger('click');
        }
    });
})(jQuery);
