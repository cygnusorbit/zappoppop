/**
 * ZapPopPop Popups Library: Template Inspection Modal Engine
 * Cross-Browser Compatibility: Safari, Firefox, Chrome, Edge
 */
(function(window, $) {
    'use strict';
    window.ZapPopPopLibraryPreview = window.ZapPopPopLibraryPreview || {};

    var Preview = {
        init: function() {
            this.bindTriggers();
            this.bindClose();
        },

        bindTriggers: function() {
            $(document).on('click', '.zappoppop-tpl-preview-btn', function(e) {
                e.preventDefault();
                var $card = $(this).closest('.zappoppop-template-card');
                var title = $card.find('.zappoppop-tpl-info h3').text() || 'Starter Template';
                var desc = $card.find('.zappoppop-tpl-info p').text() || '';
                var category = $card.find('.zappoppop-tpl-badge').text() || 'Standard';
                var elementsCount = $card.find('.zappoppop-tpl-count').text() || '4 Elements';
                var templateKey = $card.data('template-key') || '';

                $('#zappoppop-modal-tpl-title').text(title);
                $('#zappoppop-modal-tpl-desc').text(desc);
                $('#zappoppop-modal-spec-category').text(category);
                $('#zappoppop-modal-spec-elements').text(elementsCount);
                $('#zappoppop-modal-spec-canvas').text('640 × 400 px');

                $('#zappoppop-lib-modal-import-btn').attr('data-template-key', templateKey);

                $('#zappoppop-lib-preview-modal').css('display', 'flex').fadeIn(150);
            });
        },

        bindClose: function() {
            $(document).on('click', '.zappoppop-lib-modal-close, #zappoppop-lib-preview-modal', function(e) {
                if (e.target === this || $(this).hasClass('zappoppop-lib-modal-close')) {
                    $('#zappoppop-lib-preview-modal').fadeOut(150);
                }
            });

            // Cross-browser ESC key listener
            $(document).on('keydown', function(e) {
                if ((e.key === 'Escape' || e.keyCode === 27) && $('#zappoppop-lib-preview-modal').is(':visible')) {
                    $('#zappoppop-lib-preview-modal').fadeOut(150);
                }
            });
        }
    };

    window.ZapPopPopLibraryPreview = Preview;
})(window, jQuery);
