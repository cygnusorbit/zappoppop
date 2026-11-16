/**
 * ZapPopPop Library: icon action buttons + template info editor. ES5 for Chrome, Safari, Firefox, Edge.
 */
(function (window, $) {
    'use strict';

    function vars() {
        return window.zappoppop_libact_vars || window.zappoppop_lib_vars || { ajax_url: (typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php'), nonce: '' };
    }
    function label($b, text) { $b.attr({ title: text, 'aria-label': text }); }

    function decorate() {
        $('.zappoppop-tpl-preview-btn').each(function () { label($(this), 'Preview'); });
        $('.zappoppop-tpl-import-btn').each(function () { label($(this), 'Import & Edit'); });
        $('.zappoppop-usertpl-use').each(function () {
            var $use = $(this), id = $use.attr('data-id');
            label($use, 'Import & Edit');
            if ($use.closest('.zappoppop-tpl-card').attr('data-preset') === '1') { return; }
            if (!$use.siblings('.zappoppop-usertpl-edit').length) {
                $('<button type="button" class="button button-small zappoppop-usertpl-edit"></button>').attr('data-id', id).insertAfter($use);
            }
            if (!$use.siblings('.zappoppop-usertpl-del').length) {
                $('<button type="button" class="button button-small zappoppop-usertpl-del"></button>').attr('data-id', id).insertAfter($use.siblings('.zappoppop-usertpl-edit').first());
            }
            label($use.siblings('.zappoppop-usertpl-edit'), 'Edit');
            label($use.siblings('.zappoppop-usertpl-del'), 'Delete');
        });
    }

    var frame = null, curId = '';
    function $m() { return $('#zappoppop-te-modal'); }
    function msg(t, ok) { $('#zappoppop-te-msg').css('color', ok ? '#008a20' : '#b32d2e').text(t).show(); }

    function setImage(url) {
        $('#zappoppop-te-image').val(url || '');
        $('#zappoppop-te-prev').toggleClass('has-img', !!url)
            .css('background-image', url ? 'url("' + String(url).replace(/"/g, '%22') + '")' : 'none');
    }

    function pickImage() {
        if (!window.wp || !window.wp.media) { var u = window.prompt(wp.i18n.__('Image URL', 'zappoppop')); if (u) { setImage(u); } return; }
        if (!frame) {
            frame = window.wp.media({ title: 'Popup Image', button: { text: 'Use image' }, library: { type: 'image' }, multiple: false });
            frame.on('select', function () { setImage(frame.state().get('selection').first().toJSON().url || ''); });
        }
        frame.open();
    }

    function fillCategories(sel) {
        var $s = $('#zappoppop-te-category').empty(), found = false;
        $('.zappoppop-library-categories .zappoppop-cat-btn').each(function () {
            var slug = $(this).attr('data-cat');
            if (!slug || slug === 'all') { return; }
            $s.append($('<option></option>').val(slug).text($.trim($(this).text())));
            if (slug === sel) { found = true; }
        });
        $s.val(found ? sel : 'custom');
    }

    function openEdit(id) {
        var v = vars();
        curId = id;
        $('#zappoppop-te-msg').hide();
        $.ajax({
            url: v.ajax_url, type: 'POST', dataType: 'json',
            data: { action: 'zappoppop_template_get', nonce: v.nonce, id: id },
            success: function (res) {
                if (!(res && res.success && res.data)) { window.alert((res && res.data && res.data.message) || wp.i18n.__('Could not load template.', 'zappoppop')); return; }
                var d = res.data, html = '', k;
                $('#zappoppop-te-name').val(d.title);
                $('#zappoppop-te-desc').val(d.description);
                setImage(d.image);
                fillCategories(d.category);
                $('#zappoppop-te-size').text(d.width + ' \u00d7 ' + d.height + ' px');
                $('#zappoppop-te-total').text('(' + d.total + ')');
                for (k in d.counts) {
                    if (Object.prototype.hasOwnProperty.call(d.counts, k)) { html += '<div>' + $('<div>').text(k).html() + ' x ' + parseInt(d.counts[k], 10) + '</div>'; }
                }
                $('#zappoppop-te-elements').html(html || '<em>No elements</em>');
                $('#zappoppop-te-update').prop('disabled', false);
                $m().css('display', 'flex');
                $('#zappoppop-te-name').focus();
            },
            error: function () { window.alert(wp.i18n.__('Server error.', 'zappoppop')); }
        });
    }

    function closeEdit() { $m().hide(); }

    function update() {
        var v = vars(), name = $.trim($('#zappoppop-te-name').val());
        if (!name) { msg('Please enter a popup name.', false); return; }
        var $b = $('#zappoppop-te-update').prop('disabled', true);
        $.ajax({
            url: v.ajax_url, type: 'POST', dataType: 'json',
            data: {
                action: 'zappoppop_template_update', nonce: v.nonce, id: curId, name: name,
                description: $('#zappoppop-te-desc').val(), image: $('#zappoppop-te-image').val(),
                category: $('#zappoppop-te-category').val() || 'custom'
            },
            success: function (res) {
                if (res && res.success) { msg('Updated.', true); window.location.reload(); }
                else { msg((res && res.data && res.data.message) || 'Update failed.', false); $b.prop('disabled', false); }
            },
            error: function () { msg('Server error while updating.', false); $b.prop('disabled', false); }
        });
    }

    $(function () {
        decorate();
        $(document)
            .on('click.zppAct', '.zappoppop-usertpl-edit', function (e) { e.preventDefault(); openEdit($(this).attr('data-id')); })
            .on('click.zppAct', '#zappoppop-te-close, #zappoppop-te-cancel', function (e) { e.preventDefault(); closeEdit(); })
            .on('click.zppAct', '#zappoppop-te-modal', function (e) { if (e.target === this) { closeEdit(); } })
            .on('click.zppAct', '#zappoppop-te-upload, #zappoppop-te-prev', function (e) { e.preventDefault(); pickImage(); })
            .on('click.zppAct', '#zappoppop-te-remove', function (e) { e.preventDefault(); setImage(''); })
            .on('click.zppAct', '#zappoppop-te-update', function (e) { e.preventDefault(); update(); })
            .on('keydown.zppAct', function (e) { if (e.keyCode === 27 && $m().is(':visible')) { closeEdit(); } });
    });
})(window, jQuery);
