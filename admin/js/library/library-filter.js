/**
 * ZapPopPop Popups Library: Filter & Search Engine
 * Cross-Browser Compatibility: Safari, Firefox, Chrome, Edge
 */
(function(window, $) {
    'use strict';
    window.ZapPopPopLibraryFilter = window.ZapPopPopLibraryFilter || {};

    var Filter = {
        activeCategory: 'all',
        activeSearch: '',

        init: function() {
            this.bindCategoryPills();
            this.bindSearchInput();
        },

        bindCategoryPills: function() {
            var self = this;
            $(document).on('click', '.zappoppop-cat-btn', function(e) {
                e.preventDefault();
                $('.zappoppop-cat-btn').removeClass('active');
                $(this).addClass('active');

                self.activeCategory = $(this).data('category') || 'all';
                self.applyFilters();
            });
        },

        bindSearchInput: function() {
            var self = this;
            // Cross-browser input event with keyup fallback
            $(document).on('input keyup', '#zappoppop-lib-search', function() {
                self.activeSearch = $(this).val().toLowerCase().trim();
                self.applyFilters();
            });
        },

        applyFilters: function() {
            var self = this;
            var visibleCount = 0;

            $('.zappoppop-template-card').each(function() {
                var $card = $(this);
                var category = ($card.data('category') || '').toString().toLowerCase();
                var title = ($card.find('.zappoppop-tpl-info h3').text() || '').toLowerCase();
                var desc = ($card.find('.zappoppop-tpl-info p').text() || '').toLowerCase();

                var categoryMatch = (self.activeCategory === 'all') || (category === self.activeCategory);
                var searchMatch = !self.activeSearch || (title.indexOf(self.activeSearch) !== -1) || (desc.indexOf(self.activeSearch) !== -1);

                if (categoryMatch && searchMatch) {
                    $card.show();
                    visibleCount++;
                } else {
                    $card.hide();
                }
            });

            // Toggle empty notice
            if (visibleCount === 0) {
                if ($('#zappoppop-lib-empty-notice').length === 0) {
                    $('.zappoppop-library-grid').append('<div id="zappoppop-lib-empty-notice" class="zappoppop-library-empty">No popup starter templates found matching your criteria.</div>');
                } else {
                    $('#zappoppop-lib-empty-notice').show();
                }
            } else {
                $('#zappoppop-lib-empty-notice').hide();
            }
        }
    };

    window.ZapPopPopLibraryFilter = Filter;
})(window, jQuery);
