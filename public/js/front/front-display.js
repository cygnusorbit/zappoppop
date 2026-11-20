/**
 * ZapPopPop Frontend Display Runtime Engine
 * Dual-Contract Normalization: Connects All Settings to All Popup Variants
 */
(function(window, $) {
    'use strict';

    var vars = window.zappoppop_front_vars || {
        ajax_url: '',
        nonce: '',
        cookie_epoch: 1,
        render_delay: 0,
        preload_events: false,
        libraries: {}
    };

    window.ZapPopPopFrontDisplay = {
        init: function() {
            var self = this;
            var startDelay = parseInt(vars.render_delay, 10) || 0;

            if (startDelay > 0) {
                setTimeout(function() {
                    self.bootstrap();
                }, startDelay);
            } else {
                self.bootstrap();
            }
        },

        bootstrap: function() {
            this.syncCookieEpoch();
            this.bindEvents();
            this.evaluateTriggers();
            this.initExtensionWidgets();
        },

        syncCookieEpoch: function() {
            try {
                var storedEpoch = localStorage.getItem('zappoppop_cookie_epoch');
                if (storedEpoch && parseInt(storedEpoch, 10) < parseInt(vars.cookie_epoch, 10)) {
                    Object.keys(localStorage).forEach(function(key) {
                        if (key.indexOf('zappoppop_dismissed_') === 0 || key.indexOf('zappoppop_submitted_') === 0) {
                            localStorage.removeItem(key);
                        }
                    });
                }
                localStorage.setItem('zappoppop_cookie_epoch', vars.cookie_epoch);
            } catch (e) {}
        },

        bindEvents: function() {
            var self = this;

            // Universal close triggers
            $(document).on('click', '.zappoppop-close-btn, .zappoppop-backdrop', function(e) {
                e.preventDefault();
                var $wrap = $(this).closest('.zappoppop-popup-wrap, .zappoppop-overlay');
                if ($wrap.attr('data-inline') === '1') { return; }
                /* ZAPPOPPOP_BG_CLOSE_JS: backdrop clicks close only when "Click Background to Close" is on */
                if ($(this).hasClass('zappoppop-backdrop') && !$(this).hasClass('zappoppop-close-btn') && $wrap.attr('data-close-backdrop') !== '1') { return; }
                var uid = $(this).data('uid') || $wrap.data('uid');
                self.close(uid);
            });

            // Sequence next-canvas step triggers
            $(document).on('click', '.zappoppop-next-canvas-btn, .zappoppop-next-screen-btn', function(e) {
                e.preventDefault();
                var targetIdx = $(this).data('goto-canvas') || $(this).data('goto-screen') || $(this).data('goto') || 2;
                var $popup = $(this).closest('.zappoppop-popup-wrap, .zappoppop-overlay');

                $popup.find('.zappoppop-canvas-container, .zappoppop-canvas-stage').removeClass('zappoppop-canvas-active zappoppop-screen-active active').hide();
                var $next = $popup.find('[data-canvas="' + targetIdx + '"], [data-screen="' + targetIdx + '"], [data-canvas-index="' + targetIdx + '"]');
                if ($next.length) {
                    $next.addClass('zappoppop-canvas-active zappoppop-screen-active active').fadeIn(200);
                    self.playCanvasIn($next);
                }
            });

            // Lead capture celebration trigger
            $(document).on('zappoppop:form_submitted', function(e, data) {
                self.triggerCelebration();
            });
        },

        getPopupWrap: function(uid) {
            return $('#zappoppop-popup-' + uid + ', #zappoppop-wrap-' + uid).first();
        },

        evaluateTriggers: function() {
            var self = this;
            $('.zappoppop-popup-wrap, .zappoppop-overlay[data-uid]').each(function() {
                var $wrap = $(this);
                var uid = $wrap.data('uid');
                if (!uid) return;

                try {
                    if (localStorage.getItem('zappoppop_dismissed_' + uid)) {
                        return;
                    }
                } catch(e) {}

                if ($wrap.attr('data-manual') === '1') return; /* opened only by button / link / JS API */
                var hasLoad = $wrap.attr('data-trigger-load') === '1';
                var hasOther = $wrap.is('[data-trigger-exit],[data-trigger-scroll],[data-trigger-idle],[data-trigger-video-end],[data-tt-event],[data-video-listen]');
                if (!hasLoad && hasOther) return;
                var loadDelay = hasLoad ? (parseFloat($wrap.attr('data-trigger-load-delay')) || 0) * 1000 : 0;
                if (loadDelay > 0) {
                    setTimeout(function() { self.show(uid); }, loadDelay);
                } else {
                    self.show(uid);
                }
            });
        },
        /* ZAPPOPPOP_IMPRESSION_BEACON: count one impression per popup per page view */
        trackImpression: function(uid) {
            this._impSent = this._impSent || {};
            if (!uid || this._impSent[uid]) return;
            this._impSent[uid] = true;
            var url = vars.rest_url || '';
            if (!url) {
                var l = document.querySelector('link[rel="https://api.w.org/"]');
                url = l && l.href ? l.href : '/wp-json/';
            }
            url = url.replace(/\/?$/, '/') + 'zappoppop/v1/impression';
            var body = JSON.stringify({ uid: uid, nonce: vars.nonce || '' });
            try {
                if (navigator.sendBeacon && navigator.sendBeacon(url, new Blob([body], { type: 'application/json' }))) return;
            } catch (e) {}
            try {
                $.ajax({ url: url, method: 'POST', contentType: 'application/json', data: body });
            } catch (e2) {}
        },

        show: function(uid) {
            var $wrap = this.getPopupWrap(uid);
            if (!$wrap.length) return;

            $wrap.data('zappoppopClosing', false);
            $wrap.children('.zappoppop-backdrop').css({ transition: '', '-webkit-transition': '', opacity: '' }); /* ZAPPOPPOP_CLOSE_FADE */
            if ($wrap.is('[data-zpp-noscroll]')) { $('html').addClass('zappoppop-no-scroll'); } /* ZAPPOPPOP_PROTECT_SCROLL */
            $wrap.stop(true, true).fadeIn(250).css('display', 'flex').attr('aria-hidden', 'false');
            this.playCanvasIn($wrap.find('.zappoppop-canvas-active').first(), $wrap.find('.zappoppop-box').first());
            try { this.initExtensionWidgets(); } catch (eW) {} /* ZAPPOPPOP_DATE_REINIT: library may load after bootstrap */

            this.trackImpression(uid);

            $(document).trigger('zappoppop:popup_shown', [uid]);
        },

        close: function(uid) {
            var $wrap = this.getPopupWrap(uid);
            if (!$wrap.length) return;

            if ($wrap.data('zappoppopClosing')) return;
            $wrap.data('zappoppopClosing', true);
            if (!$('[data-zpp-noscroll]').not($wrap).filter(':visible').length) { $('html').removeClass('zappoppop-no-scroll'); } /* ZAPPOPPOP_PROTECT_SCROLL */

            var self = this;
            var $c = $wrap.find('.zappoppop-canvas-active').first();
            var exitOn = $c.attr('data-anim-exit') !== '0';
            var outFx = exitOn ? this.fxName($c.attr('data-anim-disappearance'), 'out') : '';
            var outDur = parseInt($c.attr('data-anim-duration'), 10);
            if (isNaN(outDur) || outDur <= 0) outDur = 500;
            outDur = Math.min(outDur, 800);

            if (outFx && $c.length) {
                /* ZAPPOPPOP_CLOSE_FADE: the dim backdrop fades in step with the exit effect, then the wrapper hides with no extra pass */
                var $bd = $wrap.children('.zappoppop-backdrop').first();
                var bd = $bd.length ? $bd[0] : null;
                if (bd) {
                    var bdT = 'opacity ' + outDur + 'ms ease-out';
                    bd.style.webkitTransition = bdT;
                    bd.style.transition = bdT;
                    void bd.offsetWidth;
                    bd.style.opacity = '0';
                }
                this.runFx($wrap.find('.zappoppop-box').first(), outFx, outDur, 0, true);
                setTimeout(function() {
                    if (!$wrap.data('zappoppopClosing')) { return; }
                    $wrap.hide(0).attr('aria-hidden', 'true');
                    self.clearFx($wrap.find('.zappoppop-box').first());
                    if (bd) {
                        bd.style.webkitTransition = '';
                        bd.style.transition = '';
                        bd.style.opacity = '';
                    }
                }, outDur + 30);
            } else {
                $wrap.hide(0).attr('aria-hidden', 'true');
            }

            try {
                localStorage.setItem('zappoppop_dismissed_' + uid, Date.now());
            } catch(e) {}

            $(document).trigger('zappoppop:popup_closed', [uid]);
        },

        fxName: function(effect, dir) {
            if (!effect || effect === 'none') return '';
            var e = String(effect).replace(/^animate__/, '').replace(/^zappoppop-anim-/, '').replace(/^anim-/, '').replace(/^zappoppop-fx-/, '');
            var inMap = { fade: 'fadeIn', slideDown: 'slideInDown', slideUp: 'slideInUp', slideLeft: 'slideInRight', slideRight: 'slideInLeft', zoom: 'zoomIn', flip: 'flipInX' };
            var outMap = { fade: 'fadeOut', slideDown: 'slideOutDown', slideUp: 'slideOutUp', slideLeft: 'slideOutLeft', slideRight: 'slideOutRight', zoom: 'zoomOut' };
            var map = (dir === 'out') ? outMap : inMap;
            return 'zappoppop-fx-' + (map[e] || e);
        },

        clearFx: function($n) {
            $n.each(function() {
                this.className = this.className.replace(/(^|\s)zappoppop-fx-\S+/g, '');
                this.style.animationName = '';
                this.style.webkitAnimationName = '';
                this.style.animationDuration = '';
                this.style.webkitAnimationDuration = '';
                this.style.animationDelay = '';
                this.style.webkitAnimationDelay = '';
                this.style.animationFillMode = '';
                this.style.webkitAnimationFillMode = '';
            });
        },

        runFx: function($n, fxClass, dur, del, keep) {
            var self = this;
            if (!$n.length || !fxClass) return;
            self.clearFx($n);
            void $n[0].offsetWidth;
            $n.css({
                'animation-duration': dur + 'ms', '-webkit-animation-duration': dur + 'ms',
                'animation-delay': del + 'ms', '-webkit-animation-delay': del + 'ms',
                'animation-fill-mode': 'both', '-webkit-animation-fill-mode': 'both'
            }).addClass('zappoppop-fx-run ' + fxClass);
            clearTimeout($n.data('zappoppopFxTimer'));
            if (!keep) {
                $n.data('zappoppopFxTimer', setTimeout(function() { self.clearFx($n); }, dur + del + 100));
            }
        },

        restartFx: function(node) {
            node.style.webkitAnimationName = 'none';
            node.style.animationName = 'none';
            void node.offsetWidth;
            node.style.webkitAnimationName = '';
            node.style.animationName = '';
        },

        playCanvasIn: function($c, $target) {
            var self = this;
            if (!$c || !$c.length) return;
            var eff = this.fxName($c.attr('data-anim-appearance'), 'in');
            var dur = parseInt($c.attr('data-anim-duration'), 10);
            var del = parseInt($c.attr('data-anim-delay'), 10);
            if (isNaN(dur) || dur <= 0) dur = 1000;
            if (isNaN(del) || del < 0) del = 0;
            if (eff) this.runFx(($target && $target.length) ? $target : $c, eff, dur, del, false);
            var offset = eff ? (del + dur) : 0;
            $c.find('.zappoppop-layer-item.zappoppop-fx-run').each(function() {
                var own = this.getAttribute('data-fx-delay');
                if (own === null) {
                    own = parseInt(this.style.animationDelay || this.style.webkitAnimationDelay, 10) || 0;
                    this.setAttribute('data-fx-delay', own);
                }
                var d = (parseInt(own, 10) || 0) + offset;
                this.style.webkitAnimationDelay = d + 'ms';
                this.style.animationDelay = d + 'ms';
                self.restartFx(this);
            });
        },

        triggerCelebration: function() {
            /* ZAPPOPPOP_CELEBRATE: an explicit per-popup effect replaces the legacy global one */
            if (window.ZapPopPopFront && window.ZapPopPopFront.celebrationMode && window.ZapPopPopFront.celebrationMode !== 'default') { return; }
            if (typeof window.confetti === 'function') {
                try {
                    window.confetti({
                        particleCount: 90,
                        spread: 65,
                        origin: { y: 0.6 }
                    });
                } catch (e) {}
            }

            if (typeof window.Fireworks !== 'undefined') {
                try {
                    var container = document.body;
                    var fireworks = new window.Fireworks.default(container, {
                        autoresize: true,
                        opacity: 0.5,
                        acceleration: 1.05,
                        friction: 0.97,
                        gravity: 1.5,
                        particles: 50,
                        traceLength: 3,
                        traceSpeed: 10,
                        explosion: 5,
                        intensity: 30
                    });
                    fireworks.start();
                    setTimeout(function() { fireworks.stop(); }, 2500);
                } catch (e) {}
            }
        },

        initExtensionWidgets: function() {
            if (typeof $.fn.mask === 'function') {
                $('input[data-mask]').each(function() {
                    var maskPattern = $(this).data('mask');
                    if (maskPattern) $(this).mask(maskPattern);
                });
            }

            if (typeof window.AirDatepicker !== 'undefined') {
                $('.zappoppop-datepicker-input, .zappoppop-datepicker, .zappoppop-front-date').each(function() {
                    if (this._zppAirDp) return; /* ZAPPOPPOP_DATE_FIX: avoid double-init */
                    var $el = $(this);
                    var opts = { autoClose: true };
                    /* ZAPPOPPOP_DATE_LOCALE_INLINE: built-in English locale (library default is not English) */
                    opts.locale = {
                        days: ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'],
                        daysShort: ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'],
                        daysMin: ['Su','Mo','Tu','We','Th','Fr','Sa'],
                        months: ['January','February','March','April','May','June','July','August','September','October','November','December'],
                        monthsShort: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
                        today: 'Today',
                        clear: 'Clear',
                        dateFormat: 'MM/dd/yyyy',
                        timeFormat: 'hh:mm aa',
                        firstDay: 0
                    };
                    var fmt = $el.data('format') || $el.attr('data-format');
                    if (fmt) {
                        opts.dateFormat = String(fmt)
                            .replace('YYYY', 'yyyy').replace('MM', 'MM').replace('DD', 'dd');
                    }
                    var minD = $el.data('min') || $el.attr('data-min');
                    var maxD = $el.data('max') || $el.attr('data-max');
                    if (minD) { var md = new Date(minD); if (!isNaN(md)) opts.minDate = md; }
                    if (maxD) { var xd = new Date(maxD); if (!isNaN(xd)) opts.maxDate = xd; }
                    this._zppAirDp = new window.AirDatepicker(this, opts);
                });
            }

            if (typeof window.SignaturePad !== 'undefined') {
                $('.zappoppop-signature-canvas, .zappoppop-sig-canvas').each(function() {
                    if (!this._sigPad) {
                        this._sigPad = new window.SignaturePad(this, {
                            backgroundColor: 'rgba(255, 255, 255, 0)',
                            penColor: '#0f172a'
                        });
                    }
                });
            }
        }
    };

    window.ZapPopPopFront = window.ZapPopPopFront || {};
    if (!window.ZapPopPopFront.Modal) {
        window.ZapPopPopFront.Modal = {
            isSuppressed: function(uid) {
                try { return !!localStorage.getItem('zappoppop_dismissed_' + uid); } catch (e) { return false; }
            },
            open: function(uid) {
                if (!uid || this.isSuppressed(uid)) return;
                var $w = window.ZapPopPopFrontDisplay.getPopupWrap(uid);
                if (!$w.length || $w.is(':visible')) return;
                window.ZapPopPopFrontDisplay.show(uid);
            },
            close: function(uid) { window.ZapPopPopFrontDisplay.close(uid); }
        };
    }

    // ---- Public JavaScript API + universal click triggers ----
    (function() {
        var D = window.ZapPopPopFrontDisplay;

        function norm(uid) { return String(uid == null ? '' : uid).replace(/^\s+|\s+$/g, ''); }
        function wrap(uid) { return D.getPopupWrap(uid); }
        function clearDismissed(uid) { try { localStorage.removeItem('zappoppop_dismissed_' + uid); } catch (e) {} }

        window.ZapPopPop = {
            show: function(uid) {
                uid = norm(uid);
                if (!uid) return false;
                var $w = wrap(uid);
                if (!$w.length) {
                    if (window.console && console.warn) console.warn('[ZapPopPop] Popup not found on this page: ' + uid);
                    return false;
                }
                clearDismissed(uid);
                if ($w.is(':visible')) return true;
                D.show(uid);
                return true;
            },
            hide: function(uid) {
                uid = norm(uid);
                var $w = wrap(uid);
                if (!uid || !$w.length || !$w.is(':visible')) return false;
                D.close(uid);
                return true;
            },
            isOpen: function(uid) {
                var $w = wrap(norm(uid));
                return !!($w.length && $w.is(':visible'));
            },
            toggle: function(uid) {
                return this.isOpen(uid) ? this.hide(uid) : this.show(uid);
            },
            list: function() {
                var out = [];
                $('.zappoppop-popup-wrap[data-uid]').each(function() { out.push($(this).attr('data-uid')); });
                return out;
            }
        };

        $(document).off('click.zappoppopApi').on('click.zappoppopApi',
            '.zappoppop-trigger, .zappoppop-trigger-btn, .zappoppop-open-btn, [data-zappoppop-id], [data-target-uid], a[href^="#zappoppop-"]',
            function(e) {
                var $t = $(this);
                var uid = $t.attr('data-zappoppop-id') || $t.attr('data-target-uid') || '';
                if (!uid) {
                    var h = $t.attr('href') || '';
                    if (h.indexOf('#zappoppop-') === 0) uid = h.slice(11);
                }
                if (!uid) return;
                e.preventDefault();
                window.ZapPopPop.show(uid);
            });
    })();

    $(document).ready(function() {
        var T = window.ZapPopPopFront.Triggers;
        if (T && typeof T.init === 'function' && !T._zappoppopInit) {
            var origInit = T.init;
            T.init = function() {
                if (T._zappoppopInited) return;
                T._zappoppopInited = true;
                return origInit.apply(T, arguments);
            };
            T._zappoppopInit = true;
            T.init();
        }
        window.ZapPopPopFrontDisplay.init();
    });

})(window, jQuery);


/* === ZAPPOPPOP_CLOSE_DISMISS_START === */
(function(window, document) {
    'use strict';
    if (window.__zppCloseDismissBound) return;
    window.__zppCloseDismissBound = true;

    function setCookie(name, val, days) {
        var d = new Date(); d.setTime(d.getTime() + days * 86400000);
        document.cookie = name + '=' + encodeURIComponent(val) + ';expires=' + d.toUTCString() + ';path=/;SameSite=Lax';
    }
    document.addEventListener('click', function(e) {
        var t = e.target;
        if (!t || !t.closest) return;
        var btn = t.closest('.zappoppop-close-trigger[data-close-action]');
        if (!btn) return;
        var action = btn.getAttribute('data-close-action') || 'close';
        var wrap = btn.closest('.zappoppop-popup-wrap');
        var uid = wrap ? wrap.getAttribute('data-uid') : '';
        if (!uid) return;
        var vars = window.zappoppop_front_vars || {};
        try {
            if (action === 'close_period') {
                window.sessionStorage.setItem('zappoppop_session_' + uid, '1');
            } else if (action === 'close_forever') {
                var days = parseInt(btn.getAttribute('data-cookie-days'), 10);
                if (!days || days < 1) days = 30;
                setCookie('zappoppop_closed_' + uid, vars.cookie_epoch || 1, days);
            }
        } catch (err) {}
        /* existing handlers then perform the actual close animation */
    }, true);
})(window, document);
/* === ZAPPOPPOP_CLOSE_DISMISS_END === */


/* === ZAPPOPPOP_BUTTON_CLOSE_START === */
(function(window, document) {
    'use strict';
    if (window.__zppBtnCloseBound) return;
    window.__zppBtnCloseBound = true;
    document.addEventListener('click', function(e) {
        var t = e.target;
        if (!t || !t.closest) return;
        var btn = t.closest('.zappoppop-front-btn.zappoppop-close-trigger');
        if (!btn) return;
        e.preventDefault();
        var wrap = btn.closest('.zappoppop-popup-wrap');
        var uid = wrap ? wrap.getAttribute('data-uid') : '';
        if (window.ZapPopPopFrontDisplay && typeof window.ZapPopPopFrontDisplay.close === 'function' && uid) {
            window.ZapPopPopFrontDisplay.close(uid);
        } else if (wrap) {
            wrap.style.display = 'none';
        }
    }, false);
})(window, document);
/* === ZAPPOPPOP_BUTTON_CLOSE_END === */


/* === ZAPPOPPOP_TRIGGER_REPEAT_START: "always on page load" + "re-appear every X hours" === */
(function(window, $) {
    'use strict';
    var D = window.ZapPopPopFrontDisplay;
    if (!D || D._repeatWrapped) return;
    D._repeatWrapped = true;

    var timers = {};
    function key(uid) { return 'zappoppop_dismissed_' + uid; }
    function getTs(uid) { try { return parseInt(localStorage.getItem(key(uid)), 10) || 0; } catch (e) { return 0; } }
    function clr(uid) { try { localStorage.removeItem(key(uid)); } catch (e) {} }

    function waitUntil(uid, due) {
        clearTimeout(timers[uid]);
        var left = due - Date.now();
        if (left <= 0) { clr(uid); D.show(uid); return; }
        timers[uid] = setTimeout(function() { waitUntil(uid, due); }, Math.min(left, 2147000000)); /* setTimeout max ~24.8d */
    }

    var origEval = D.evaluateTriggers;
    D.evaluateTriggers = function() {
        $('.zappoppop-popup-wrap[data-uid]').each(function() {
            var $w = $(this), uid = $w.attr('data-uid');
            if (!uid || $w.attr('data-inline') === '1') { return; }
            if ($w.attr('data-trigger-always') === '1') { clr(uid); return; }
            var h = parseFloat($w.attr('data-repeat-hours')) || 0;
            if (h > 0) {
                var ts = getTs(uid);
                if (!ts) { return; }
                var due = ts + h * 3600000;
                if (Date.now() >= due) {
                    clr(uid);
                    if ($w.attr('data-trigger-load') !== '1') { D.show(uid); }
                } else {
                    waitUntil(uid, due);
                }
            }
        });
        return origEval.apply(this, arguments);
    };

    var origClose = D.close;
    D.close = function(uid) {
        var r = origClose.apply(this, arguments);
        var $w = D.getPopupWrap(uid);
        var h = parseFloat($w.attr('data-repeat-hours')) || 0;
        if (h > 0 && $w.attr('data-inline') !== '1') {
            waitUntil(uid, Date.now() + h * 3600000);
        }
        return r;
    };
})(window, jQuery);
/* === ZAPPOPPOP_TRIGGER_REPEAT_END === */


/* === ZAPPOPPOP_VIDEO_FACADE_START === */
(function (window, document) {
    'use strict';
    if (window.__zappoppopVideoFacade) { return; }
    window.__zappoppopVideoFacade = true;

    function play(node) {
        var src = node.getAttribute('data-zappoppop-embed');
        if (!src || node.getAttribute('data-zappoppop-played')) { return; }
        node.setAttribute('data-zappoppop-played', '1');
        var f = document.createElement('iframe');
        f.setAttribute('src', src);
        f.setAttribute('frameborder', '0');
        f.setAttribute('allow', node.getAttribute('data-zappoppop-allow') || 'autoplay; fullscreen; picture-in-picture');
        f.setAttribute('allowfullscreen', '');
        f.style.cssText = 'width:100%;height:100%;border:0;border-radius:inherit;display:block;background:#000;';
        while (node.firstChild) { node.removeChild(node.firstChild); }
        node.appendChild(f);
    }

    function facadeOf(t) {
        while (t && t !== document) {
            if (t.nodeType === 1 && t.className && String(t.className).indexOf('zappoppop-video-facade') !== -1) { return t; }
            t = t.parentNode;
        }
        return null;
    }

    document.addEventListener('click', function (e) {
        var n = facadeOf(e.target);
        if (n) { e.preventDefault(); play(n); }
    }, true);

    document.addEventListener('keydown', function (e) {
        var k = e.key || e.keyCode;
        if (k !== 'Enter' && k !== ' ' && k !== 13 && k !== 32) { return; }
        var n = facadeOf(e.target);
        if (n) { e.preventDefault(); play(n); }
    }, true);
})(window, document);
/* === ZAPPOPPOP_VIDEO_FACADE_END === */

/* === ZAPPOPPOP_CLICK_ROUTE_JS === */
(function (win, doc) {
    function go(n, e) {
        var href = n.getAttribute('data-zpp-href');
        if (!href) { return; }
        e.preventDefault();
        if (href === '#') { return; }
        var rel = n.getAttribute('data-zpp-rel') || '';
        if (n.getAttribute('data-zpp-blank') === '1') {
            var w = win.open(href, '_blank');
            if (w && rel.indexOf('noopener') > -1) { try { w.opener = null; } catch (x) {} }
        } else { win.location.href = href; }
    }
    doc.addEventListener('click', function (e) {
        var n = e.target.closest ? e.target.closest('.zappoppop-click-link[data-zpp-href]') : null;
        if (n) { go(n, e); }
    });
    doc.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter' && e.key !== ' ') { return; }
        var n = e.target.closest ? e.target.closest('.zappoppop-click-route') : null;
        if (!n) { return; }
        e.preventDefault();
        n.click();
    });
})(window, document);
/* === ZAPPOPPOP_CLICK_ROUTE_JS_END === */

/* === ZAPPOPPOP_CLICK_ROUTE_JS2: copy-to-clipboard + scroll-to-anchor === */
(function (win, doc) {
    function copyText(txt, done) {
        function legacy() {
            var ta = doc.createElement('textarea');
            ta.value = txt; ta.setAttribute('readonly', ''); ta.style.cssText = 'position:fixed;top:0;left:0;opacity:0';
            doc.body.appendChild(ta); ta.select();
            try { doc.execCommand('copy'); } catch (x) {}
            doc.body.removeChild(ta); done();
        }
        if (win.navigator && win.navigator.clipboard && win.isSecureContext) {
            win.navigator.clipboard.writeText(txt).then(done, legacy);
        } else { legacy(); }
    }
    doc.addEventListener('click', function (e) {
        var t = e.target;
        var c = t.closest ? t.closest('.zappoppop-click-route[data-zpp-copy]') : null;
        if (c) {
            e.preventDefault();
            copyText(c.getAttribute('data-zpp-copy'), function () {
                c.setAttribute('data-zpp-copied', '1');
                var old = c.style.opacity; c.style.opacity = '0.6';
                win.setTimeout(function () { c.removeAttribute('data-zpp-copied'); c.style.opacity = old; }, 900);
            });
            return;
        }
        var a = t.closest ? t.closest('.zappoppop-click-route[data-zpp-anchor]') : null;
        if (a) {
            e.preventDefault();
            var node = doc.getElementById(a.getAttribute('data-zpp-anchor'));
            if (node && node.scrollIntoView) {
                try { node.scrollIntoView({ behavior: 'smooth', block: 'start' }); } catch (x) { node.scrollIntoView(); }
            }
        }
    });
})(window, document);
/* === ZAPPOPPOP_CLICK_ROUTE_JS2_END === */
