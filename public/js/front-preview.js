(function () {
    var uid = (window.zappoppopPreview && window.zappoppopPreview.uid) || '';
    try { if (window.self !== window.top) { var b = document.getElementById('zappoppop-preview-banner'); if (b) b.style.display = 'none'; } } catch (e) {}
    try { localStorage.removeItem('zappoppop_dismissed_' + uid); } catch (e) {}
    function show(force) {
        var D = window.ZapPopPopFrontDisplay, $ = window.jQuery;
        if (!D || !$) { return setTimeout(function () { show(force); }, 150); }
        var $w = $('#zappoppop-popup-' + uid);
        if (force || !$w.is(':visible')) {
            try { localStorage.removeItem('zappoppop_dismissed_' + uid); } catch (e) {}
            $w.data('zappoppopClosing', false);
            D.show(uid);
        }
    }
    if (document.readyState === 'complete') { setTimeout(function () { show(false); }, 350); }
    else { window.addEventListener('load', function () { setTimeout(function () { show(false); }, 350); }); }
    document.addEventListener('click', function (e) {
        var t = e.target && e.target.closest ? e.target.closest('#zappoppop-preview-replay') : null;
        if (t) { e.preventDefault(); show(true); }
    });
})();
