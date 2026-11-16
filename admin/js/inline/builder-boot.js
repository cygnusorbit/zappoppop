/* ZAPPOPPOP_FOUC_GUARD: reveal once stylesheets and icon fonts have loaded (hard cap 3s) */
    (function () {
        var done = false;
        function reveal() {
            if (done) { return; }
            done = true;
            var s = document.getElementById('zappoppop-fouc-guard-css');
            if (s && s.parentNode) { s.parentNode.removeChild(s); }
            var l = document.getElementById('zappoppop-boot-loader');
            if (l) {
                l.className += ' is-done';
                setTimeout(function () {
                    var st = document.getElementById('zappoppop-boot-style-css');
                    if (l.parentNode) { l.parentNode.removeChild(l); }
                    if (st && st.parentNode) { st.parentNode.removeChild(st); }
                }, 300);
            }
        }
        function go() {
            if (window.requestAnimationFrame) {
                window.requestAnimationFrame(function () { window.requestAnimationFrame(reveal); });
            } else {
                setTimeout(reveal, 50);
            }
        }
        function ready() {
            if (document.fonts && document.fonts.ready && typeof document.fonts.ready.then === 'function') {
                document.fonts.ready.then(go, go);
            } else {
                go();
            }
        }
        if (document.readyState === 'complete') { ready(); } else { window.addEventListener('load', ready); }
        setTimeout(reveal, 3000);
    })();
