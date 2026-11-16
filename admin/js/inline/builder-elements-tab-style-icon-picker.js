(function($){
    'use strict';
    if (window.__zappoppopFaPickerBound) return;
    window.__zappoppopFaPickerBound = true;

    var ICONS = ('envelope user phone lock key at id-card address-book building briefcase globe link map-marker-alt location-dot home search eye eye-slash check check-circle circle-check times calendar calendar-alt clock credit-card dollar-sign euro-sign pound-sign tag tags gift shopping-cart shopping-bag heart star bell comment comments paper-plane pen edit file file-alt image camera video music headphones microphone wifi mobile-alt desktop laptop print download upload cloud cog wrench shield-alt user-shield user-circle user-tie user-plus users hashtag percent info-circle question-circle exclamation-circle exclamation-triangle thumbs-up flag bookmark car plane truck coffee leaf fire bolt sun moon graduation-cap hospital medkit paw anchor fingerprint qrcode barcode language signature sliders-h arrow-right arrow-left chevron-down chevron-right plus minus').split(' ');
    var BRANDS = ['facebook','twitter','instagram','linkedin','youtube','whatsapp','telegram','google','apple','paypal','stripe','github'];
    var $target = null;

    function build() {
        if ($('#zappoppop-fa-picker').length) return;
        var items = '';
        ICONS.forEach(function(n){ items += '<button type="button" class="fa-pk-item" data-cls="fas fa-' + n + '" data-name="' + n + '" title="' + n + '"><i class="fas fa-' + n + '"></i></button>'; });
        BRANDS.forEach(function(n){ items += '<button type="button" class="fa-pk-item" data-cls="fab fa-' + n + '" data-name="' + n + '" title="' + n + '"><i class="fab fa-' + n + '"></i></button>'; });
        $('body').append(
            '<div id="zappoppop-fa-picker-backdrop" class="zappoppop-modal"></div>' +
            '<div id="zappoppop-fa-picker" class="zappoppop-modal" role="dialog" aria-modal="true" aria-label="Select icon">' +
              '<div class="fa-pk-head"><input type="search" id="zappoppop-fa-pk-search" placeholder="Search icons..." autocomplete="off"><button type="button" class="fa-pk-x" aria-label="Close">&times;</button></div>' +
              '<div class="fa-pk-grid">' + items + '</div>' +
            '</div>'
        );
    }

    function refreshBtn($input) {
        var $btn = $('.zappoppop-fa-pick-btn[data-target="#' + $input.attr('id') + '"]');
        var v = $.trim($input.val() || '');
        var cls = /^(fas|far|fab|fa)\s/.test(v) ? v : 'fas fa-icons';
        $btn.find('i').attr('class', cls);
    }

    function openPicker($input) {
        build();
        $target = $input;
        var cur = $.trim($input.val() || '');
        $('#zappoppop-fa-picker .fa-pk-item').removeClass('active').filter(function(){ return $(this).attr('data-cls') === cur; }).addClass('active');
        $('#zappoppop-fa-pk-search').val('');
        $('#zappoppop-fa-picker .fa-pk-item').show();
        $('#zappoppop-fa-picker-backdrop, #zappoppop-fa-picker').addClass('open');
        setTimeout(function(){ $('#zappoppop-fa-pk-search').trigger('focus'); }, 20);
    }

    function closePicker() { $('#zappoppop-fa-picker-backdrop, #zappoppop-fa-picker').removeClass('open'); $target = null; }

    $(document).on('click', '.zappoppop-fa-pick-btn', function(e){
        e.preventDefault(); e.stopPropagation();
        openPicker($($(this).attr('data-target')));
    });
    $(document).on('click', '.zappoppop-fa-clear-btn', function(e){
        e.preventDefault();
        var $i = $($(this).attr('data-target'));
        $i.val('').trigger('input').trigger('change'); refreshBtn($i);
    });
    $(document).on('click', '#zappoppop-fa-picker .fa-pk-item', function(e){
        e.preventDefault(); e.stopPropagation();
        if (!$target) return;
        var $i = $target;
        $i.val($(this).attr('data-cls')).trigger('input').trigger('change');
        refreshBtn($i); closePicker();
    });
    $(document).on('input', '#zappoppop-fa-pk-search', function(){
        var q = $.trim($(this).val()).toLowerCase();
        $('#zappoppop-fa-picker .fa-pk-item').each(function(){
            $(this).toggle(!q || $(this).attr('data-name').indexOf(q) !== -1);
        });
    });
    $(document).on('click', '#zappoppop-fa-picker-backdrop, #zappoppop-fa-picker .fa-pk-x', closePicker);
    $(document).on('keydown', function(e){ if (e.key === 'Escape' && $target) closePicker(); });
    $(document).on('input change', '#prop-form-icon-left, #prop-form-icon-right, #prop-icon-class', function(){ refreshBtn($(this)); });
    $(document).on('click', '#zappoppop-inspector-drawer [data-tab], .zappoppop-canvas-item, .zappoppop-layer-item', function(){
        setTimeout(function(){ refreshBtn($('#prop-form-icon-left')); refreshBtn($('#prop-form-icon-right')); refreshBtn($('#prop-icon-class')); }, 80);
    });
    $(function(){ refreshBtn($('#prop-form-icon-left')); refreshBtn($('#prop-form-icon-right')); refreshBtn($('#prop-icon-class')); });
})(jQuery);
