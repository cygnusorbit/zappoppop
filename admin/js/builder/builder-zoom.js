/* ZAPPOPPOP_STAGE_ZOOM: ribbon zoom control ([-] [dropdown] [+]), Alt+wheel zoom about the pointer, Ctrl/Cmd+0 = 100%.
 * Works on top of the core zoom engine (no core edits): lifts the "fit" cap once the user zooms, allows panning when the
 * stage is larger than the workspace, and makes jQuery UI drag / resize zoom-aware so layers keep following the pointer. */
(function (window, document, $) {
    'use strict';
    if (window.__zppStageZoom) { return; }
    window.__zppStageZoom = true;
    if (!$) { return; }

    var MIN = 0.1;
    var MAX = 3;
    var PRESETS = [0.25, 0.5, 0.75, 1, 1.25, 1.5, 2, 3];

    function core() { return window.ZapPopPopBuilderCore || null; }
    function zoomNow() {
        var c = core();
        var z = c && c.state ? (c.state.effectiveZoom || c.state.zoom) : 1;
        z = parseFloat(z);
        return (isNaN(z) || z <= 0) ? 1 : z;
    }
    function clamp(v, a, b) { return Math.max(a, Math.min(b, v)); }
    function inBox(el) { return !!(el && el.closest && el.closest('#zappoppop-canvas-box')); }
    function isOne(z) { return Math.abs(z - 1) < 0.0005; }

    /* ---------- core engine adjustments ---------- */
    function patchCore() {
        var c = core();
        if (!c || c.__zppZoomPatched) { return !!c; }
        c.__zppZoomPatched = true;

        var oBounds = c.getStageBounds;
        c.getStageBounds = function () {
            var b = oBounds.apply(this, arguments);
            b.fitMax = b.max;
            if (this.state && this.state.zppFree) { b.max = MAX; }
            return b;
        };

        var oClamp = c.clampStageAxis;
        c.clampStageAxis = function (pan, wsSize, groupSize) {
            var slack = (wsSize - groupSize) / 2 - this.STAGE_PAD;
            if (slack > 0 || !(this.state && this.state.zppFree)) { return oClamp.apply(this, arguments); }
            var over = -slack;
            return Math.max(-over, Math.min(over, pan));
        };

        var oUpdate = c.updateCanvasTransform;
        c.updateCanvasTransform = function () {
            var r = oUpdate.apply(this, arguments);
            syncUi();
            try { $(document).trigger('zappoppop:zoom', [zoomNow()]); } catch (e) {}
            return r;
        };
        try { if (c.state) { c.updateCanvasTransform(); } } catch (e) {}
        return true;
    }

    /* ---------- zoom actions ---------- */
    function applyZoom(z, anchor, resetPan) {
        var c = core();
        if (!c || !c.state) { return; }
        var st = c.state;
        var old = zoomNow();
        z = Math.round(clamp(z, MIN, MAX) * 1000) / 1000;
        var pan = st.pan || { x: 0, y: 0 };
        var nx = pan.x, ny = pan.y;
        if (resetPan) {
            nx = 0; ny = 0;
        } else if (anchor) {
            var box = document.getElementById('zappoppop-canvas-box');
            if (box) {
                var r = box.getBoundingClientRect();
                var cx = r.left + r.width / 2;
                var cy = r.top + r.height / 2;
                var k = 1 - z / old;
                nx = pan.x + (anchor.x - cx) * k;
                ny = pan.y + (anchor.y - cy) * k;
            }
        }
        st.zppFree = true;
        st.zoomMode = 'manual';
        st.zoom = z;
        st.pan = { x: Math.round(nx), y: Math.round(ny) };
        c.updateCanvasTransform();
    }

    function stepZoom(dir) {
        var z = zoomNow();
        var i, t = null;
        if (dir > 0) {
            for (i = 0; i < PRESETS.length; i++) { if (PRESETS[i] > z + 0.001) { t = PRESETS[i]; break; } }
            if (t === null) { t = MAX; }
        } else {
            for (i = PRESETS.length - 1; i >= 0; i--) { if (PRESETS[i] < z - 0.001) { t = PRESETS[i]; break; } }
            if (t === null) { t = MIN; }
        }
        applyZoom(t, null, false);
    }

    function fitZoom() {
        var c = core();
        if (!c) { return; }
        if (c.state) { c.state.zppFree = true; }
        c.setZoom('fit');
    }

    /* ---------- ribbon UI ---------- */
    function pct(z) { return Math.round(z * 100) + '%'; }

    function syncUi() {
        var sel = document.getElementById('zappoppop-ribbon-zoom');
        if (!sel) { return; }
        var c = core();
        var z = zoomNow();
        var fitLabel = sel.getAttribute('data-fit-label') || 'Fit';
        var isFit = !!(c && c.state && c.state.zoomMode === 'fit');
        var html = '';
        var matched = false;
        var i;
        for (i = 0; i < PRESETS.length; i++) {
            var on = !isFit && Math.abs(PRESETS[i] - z) < 0.005;
            if (on) { matched = true; }
            html += '<option value="' + PRESETS[i] + '"' + (on ? ' selected' : '') + '>' + pct(PRESETS[i]) + '</option>';
        }
        html += '<option value="fit"' + (isFit ? ' selected' : '') + '>' + fitLabel + (isFit ? ' (' + pct(z) + ')' : '') + '</option>';
        if (!isFit && !matched) {
            html += '<option value="custom" selected>' + pct(z) + '</option>';
        }
        if (sel.__zppHtml !== html) {
            sel.innerHTML = html;
            sel.__zppHtml = html;
        }
        var out = document.getElementById('zappoppop-zoom-out');
        var inn = document.getElementById('zappoppop-zoom-in');
        if (out) { out.disabled = z <= MIN + 0.001; }
        if (inn) { inn.disabled = z >= MAX - 0.001; }
    }

    function bindUi() {
        $(document).off('click.zppZoom').on('click.zppZoom', '#zappoppop-zoom-out', function (e) {
            e.preventDefault();
            stepZoom(-1);
        }).on('click.zppZoom', '#zappoppop-zoom-in', function (e) {
            e.preventDefault();
            stepZoom(1);
        }).on('change.zppZoom', '#zappoppop-ribbon-zoom', function () {
            var v = this.value;
            if (v === 'fit') { fitZoom(); }
            else if (v !== 'custom') { applyZoom(parseFloat(v), null, false); }
            try { this.blur(); } catch (e) {}
        });
    }

    /* ---------- Alt + wheel (zoom about the pointer) and Ctrl/Cmd + 0 ---------- */
    function bindInput() {
        document.addEventListener('wheel', function (e) {
            if (!e.altKey || e.ctrlKey || e.metaKey) { return; }
            var t = e.target;
            if (!t || !t.closest || !t.closest('.zappoppop-builder-workspace')) { return; }
            var d = e.deltaY || e.deltaX || 0;
            if (!d) { return; }
            if (e.deltaMode === 1) { d *= 16; } else if (e.deltaMode === 2) { d *= 100; }
            d = clamp(d, -240, 240);
            e.preventDefault();
            e.stopPropagation();
            applyZoom(zoomNow() * Math.exp(-d * 0.0022), { x: e.clientX, y: e.clientY }, false);
        }, { passive: false, capture: true });

        document.addEventListener('keydown', function (e) {
            if (!(e.ctrlKey || e.metaKey) || e.altKey || e.shiftKey) { return; }
            if (!(e.key === '0' || e.code === 'Digit0' || e.code === 'Numpad0')) { return; }
            if (!document.getElementById('zappoppop-canvas-box')) { return; }
            e.preventDefault();
            applyZoom(1, null, true);
        }, true);
    }

    /* ---------- jQuery UI drag / resize made zoom-aware ---------- */
    function patchUi() {
        var ui = $.ui;
        if (!ui || ui.__zppZoomUi) { return; }
        ui.__zppZoomUi = true;

        if (ui.draggable && ui.draggable.prototype._generatePosition) {
            var dp = ui.draggable.prototype;
            var oGen = dp._generatePosition;
            dp._generatePosition = function (event, constrain) {
                var z = zoomNow();
                var el = this.element && this.element[0];
                if (isOne(z) || !inBox(el)) { return oGen.apply(this, arguments); }
                var cont = this.containment;
                this.containment = null;
                var p;
                try { p = oGen.apply(this, arguments); } finally { this.containment = cont; }
                var L = p.left / z;
                var T = p.top / z;
                if (constrain && cont) {
                    var root = document.getElementById('zappoppop-canvas-elements-root');
                    if (root) {
                        L = clamp(L, 0, Math.max(0, root.clientWidth - el.offsetWidth));
                        T = clamp(T, 0, Math.max(0, root.clientHeight - el.offsetHeight));
                    }
                }
                p.left = L;
                p.top = T;
                return p;
            };
        }

        if (ui.resizable && ui.resizable.prototype._mouseDrag) {
            var rp = ui.resizable.prototype;
            var oDrag = rp._mouseDrag;
            var oResp = rp._respectSize;
            rp._mouseDrag = function (event) {
                var z = zoomNow();
                var el = this.element && this.element[0];
                var o = this.originalMousePosition;
                if (isOne(z) || !inBox(el) || !o) { return oDrag.apply(this, arguments); }
                var ev = $.Event(event, {
                    pageX: o.left + (event.pageX - o.left) / z,
                    pageY: o.top + (event.pageY - o.top) / z
                });
                var oc = this.options.containment;
                this.__zppClamp = !!oc;
                if (oc) { this.options.containment = false; }
                try { return oDrag.call(this, ev); }
                finally {
                    if (oc) { this.options.containment = oc; }
                    this.__zppClamp = false;
                }
            };
            if (oResp) {
                rp._respectSize = function (data) {
                    var r = oResp.apply(this, arguments);
                    if (this.__zppClamp && r) {
                        var root = document.getElementById('zappoppop-canvas-elements-root');
                        var el = this.element && this.element[0];
                        if (root && el) {
                            var left = parseFloat(el.style.left) || 0;
                            var top = parseFloat(el.style.top) || 0;
                            if (typeof r.width === 'number') { r.width = Math.min(r.width, Math.max(10, root.clientWidth - left)); }
                            if (typeof r.height === 'number') { r.height = Math.min(r.height, Math.max(10, root.clientHeight - top)); }
                        }
                    }
                    return r;
                };
            }
        }
    }

    function init() {
        patchUi();
        patchCore();
        bindUi();
        syncUi();
    }

    bindInput();
    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', init); } else { init(); }
    window.ZapPopPopStageZoom = {
        set: function (z) { applyZoom(parseFloat(z) || 1, null, false); },
        reset: function () { applyZoom(1, null, true); },
        fit: fitZoom,
        step: stepZoom,
        get: zoomNow
    };
})(window, document, window.jQuery);
