/**
 * ZapPopPop Visual Builder: Save split-button dropdown
 * Caret next to Save opens "Save Draft" / "Save as Template".
 * Save Draft re-uses the one save pipeline (builder-io.js): it fires the Save button with the 'draft' status,
 * so the pre-save flush, the AJAX whitelist and the dashboard status all stay on a single code path.
 */
(function(window, $) {
    'use strict';
    if (window.__zappoppopSaveMenuBound) { return; }
    window.__zappoppopSaveMenuBound = true;

    var MENU = '#zappoppop-save-menu';
    var CARET = '#zappoppop-btn-save-menu';
    var SAVE = '#zappoppop-btn-save';
    var DRAFT = '#zappoppop-menu-save-draft';
    var TPL = '#zappoppop-menu-save-template';

    function isOpen() { return $(MENU).hasClass('is-open'); }
    function locked() { return $('body').hasClass('zappoppop-preview-locked'); }

    function place() {
        var c = $(CARET)[0];
        if (!c) { return; }
        var r = c.getBoundingClientRect();
        var $m = $(MENU);
        var w = $m.outerWidth() || 210;
        var left = Math.max(8, Math.min(window.innerWidth - w - 8, r.right - w));
        $m.css({ top: Math.round(r.bottom + 6) + 'px', left: Math.round(left) + 'px' });
    }

    function open() {
        if ($(CARET).prop('disabled') || locked()) { return; }
        cleanMenu();
        paintState();
        $(MENU).addClass('is-open');
        $(CARET).attr('aria-expanded', 'true');
        place();
    }

    function close(focusCaret) {
        $(MENU).removeClass('is-open');
        $(CARET).attr('aria-expanded', 'false');
        if (focusCaret) { $(CARET).trigger('focus'); }
    }

    function paintState(status) {
        var s = (status === undefined) ? $('#zappoppop-builder-status').val() : status;
        s = $.trim(String(s || ''));
        var $st = $(DRAFT).find('.zappoppop-save-menu-state').removeClass('is-draft is-publish').text('');
        if (s === 'draft') { $st.addClass('is-draft').text('Draft'); }
        else if (s === 'publish' || s === 'published') { $st.addClass('is-publish').text('Published'); }
    }

    /* ZAPPOPPOP_MENU_CLEAN: while the menu still sat inside the header, the theme script gave its rows inline !important colors and
       data-zpp-role="toolbar-btn", whose :hover colors are pinned by the theme CSS. Strip both so the hover highlight can show.
       Every row, including "Save as Template", then shares the same look. */
    function cleanMenu() {
        $(MENU).find('.zappoppop-save-menu-item').each(function() {
            this.removeAttribute('style');
            this.removeAttribute('data-zpp-role');
            $(this).find('*').each(function() { this.removeAttribute('style'); this.removeAttribute('data-zpp-role'); });
        });
    }

    function items() { return $(MENU).find('.zappoppop-save-menu-item:not(:disabled)'); }

    function isSaveCall(settings) {
        return settings && typeof settings.data === 'string' && settings.data.indexOf('zappoppop_save_popup') !== -1;
    }

    $(function() {
        /* the header scrolls horizontally: render the menu at body level so it is never clipped */
        $(MENU).appendTo('body');
        cleanMenu();
        paintState();

        $(document)
            .on('click.zappoppopSaveMenu', CARET, function(e) {
                e.preventDefault();
                if (isOpen()) { close(false); } else { open(); }
            })
            .on('click.zappoppopSaveMenu', DRAFT, function(e) {
                e.preventDefault();
                close(false);
                if (locked() || $(SAVE).prop('disabled')) { return; }
                $(SAVE).trigger('click', ['draft']);
            })
            .on('click.zappoppopSaveMenu', TPL, function() {
                close(false); /* builder-template.js opens the modal from this same click */
            })
            .on('mousedown.zappoppopSaveMenu touchstart.zappoppopSaveMenu', function(e) {
                if (isOpen() && !$(e.target).closest(MENU + ',' + CARET).length) { close(false); }
            })
            .on('keydown.zappoppopSaveMenu', function(e) {
                var k = e.which || e.keyCode;
                var inMenu = $(e.target).closest(MENU).length > 0;
                var onCaret = $(e.target).closest(CARET).length > 0;
                if (k === 27 && isOpen()) { e.preventDefault(); close(true); return; }
                if (onCaret && (k === 40 || k === 38)) {
                    e.preventDefault();
                    if (!isOpen()) { open(); }
                    var $i = items();
                    if ($i.length) { $i.eq(k === 40 ? 0 : $i.length - 1).trigger('focus'); }
                    return;
                }
                if (inMenu) {
                    var $all = items();
                    var idx = $all.index($(e.target).closest('.zappoppop-save-menu-item'));
                    if (k === 40) { e.preventDefault(); $all.eq((idx + 1) % $all.length).trigger('focus'); }
                    else if (k === 38) { e.preventDefault(); $all.eq((idx - 1 + $all.length) % $all.length).trigger('focus'); }
                    else if (k === 36) { e.preventDefault(); $all.eq(0).trigger('focus'); }
                    else if (k === 35) { e.preventDefault(); $all.eq($all.length - 1).trigger('focus'); }
                    else if (k === 9) { close(false); }
                }
            })
            .on('zappoppop:popup-saved.zappoppopSaveMenu', function(e, data) {
                paintState(data && data.status ? data.status : undefined);
            })
            .on('ajaxSend.zappoppopSaveMenu', function(e, xhr, settings) {
                if (isSaveCall(settings)) { close(false); $(CARET).prop('disabled', true); }
            })
            .on('ajaxComplete.zappoppopSaveMenu', function(e, xhr, settings) {
                if (isSaveCall(settings)) { setTimeout(function() { $(CARET).prop('disabled', false); }, 1550); }
            });

        $(window).on('resize.zappoppopSaveMenu', function() { if (isOpen()) { close(false); } });
        $('.zappoppop-builder-header').on('scroll.zappoppopSaveMenu', function() { if (isOpen()) { place(); } });
    });
})(window, jQuery);
