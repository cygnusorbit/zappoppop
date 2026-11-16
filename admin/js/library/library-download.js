/**
 * ZapPopPop Library: Download template JSON. ES5; Blob + msSaveBlob fallback (Chrome, Safari, Firefox, Edge).
 */
(function (window, $) {
    'use strict';

    function vars() {
        return window.zappoppop_libact_vars || window.zappoppop_lib_vars || { ajax_url: (typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php'), nonce: '' };
    }

    function saveText(name, text) {
        var blob = new Blob([text], { type: 'application/json' });
        if (window.navigator && window.navigator.msSaveBlob) { window.navigator.msSaveBlob(blob, name); return; }
        var url = (window.URL || window.webkitURL).createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url; a.download = name; a.style.display = 'none';
        document.body.appendChild(a);
        a.click();
        setTimeout(function () { document.body.removeChild(a); (window.URL || window.webkitURL).revokeObjectURL(url); }, 1000);
    }

    function decorate() {
        $('.zappoppop-usertpl-use').each(function () {
            var $use = $(this);
            if ($use.closest('.zappoppop-tpl-card').attr('data-nodl') === '1') { return; }
            if ($use.siblings('.zappoppop-usertpl-dl').length) { return; }
            $('<button type="button" class="button button-small zappoppop-usertpl-dl"></button>')
                .attr({ 'data-id': $use.attr('data-id'), title: 'Download', 'aria-label': 'Download' })
                .insertAfter($use);
        });
    }

    $(function () {
        decorate();
        $(document).on('click.zppDl', '.zappoppop-usertpl-dl', function (e) {
            e.preventDefault();
            var $b = $(this), v = vars();
            if ($b.prop('disabled')) { return; }
            $b.prop('disabled', true);
            var id = $b.attr('data-id');
            var legacy = function () {
            $.ajax({
                url: v.ajax_url, type: 'POST', dataType: 'json',
                data: { action: 'zappoppop_template_download', nonce: v.nonce, id: $b.attr('data-id') },
                success: function (res) {
                    if (res && res.success && res.data && res.data.payload) { saveText(res.data.filename || 'template.json', res.data.payload); }
                    else { window.alert((res && res.data && res.data.message) || wp.i18n.__('Download failed.', 'zappoppop')); }
                },
                error: function () { window.alert(wp.i18n.__('Server error while preparing the download.', 'zappoppop')); },
                complete: function () { $b.prop('disabled', false); }
            });
            };
            if (window.ZapPopPopLibraryZip && window.ZapPopPopLibraryZip.canZip()) {
                window.ZapPopPopLibraryZip.download(id, function (st) {
                    if (st === 'fallback') { legacy(); return; }
                    $b.prop('disabled', false);
                });
            } else { legacy(); }
        });
    });
})(window, jQuery);
