/* ZapPopPop Builder: capture / apply "new popup" defaults (canvas meta + popup settings, never layers). */
(function ($) {
    'use strict';

    function saved() {
        var v = window.zappoppop_vars && window.zappoppop_vars.new_popup_defaults;
        return (v && typeof v === 'object') ? v : null;
    }

    function clone(o) { return JSON.parse(JSON.stringify(o)); }

    /* Same live-input sync the Save Popup button performs, for the active canvas. */
    function syncLiveMeta(core) {
        var cur = core.state.currentCanvas || 1;
        if (!core.state.canvasMeta) { core.state.canvasMeta = {}; }
        if (!core.state.canvasMeta[cur]) { core.state.canvasMeta[cur] = {}; }
        var m = core.state.canvasMeta[cur], v;

        v = $('#set-canvas-anim-appearance, #set-anim-appearance').val();
        if (v) { m.anim_appearance = v; }
        v = $('#set-canvas-anim-duration, #set-anim-duration').val();
        if (v !== undefined && v !== '' && !isNaN(v)) { m.anim_duration = parseInt(v, 10); }
        v = $('#set-canvas-anim-delay, #set-anim-delay').val();
        if (v !== undefined && v !== '' && !isNaN(v)) { m.anim_delay = parseInt(v, 10); }
        v = $('#set-canvas-anim-disappearance, #set-anim-disappearance').val();
        if (v) { m.anim_disappearance = v; }
        var $exit = $('#set-canvas-anim-exit-enable');
        if ($exit.length) { m.anim_exit_enabled = $exit.is(':checked'); }
        var $rad = $('#set-canvas-radius');
        if ($rad.length) { m.canvas_radius = Math.max(0, Math.min(500, parseInt($rad.val(), 10) || 0)); }
        var $cb = $('#set-canvas-close-btn');
        if ($cb.length) { m.close_btn_enabled = $cb.is(':checked'); }
        var $ac = $('#set-canvas-autoclose');
        if ($ac.length) {
            var ac = Math.max(0, Math.min(300, parseInt($ac.val(), 10) || 0));
            m.autoclose = ac;
            m.autoclose_delay = ac;
        }
    }

    window.ZapPopPopBuilderDefaults = {
        /* JSON string of the current popup-level data; the server whitelists and redacts it. */
        capture: function () {
            var core = window.ZapPopPopBuilderCore, S = window.ZapPopPopBuilderSettings, IO = window.ZapPopPopBuilderIO;
            if (!core || !core.state || !S) { return '{}'; }
            try {
                if (window.ZapPopPopElementsRegistry && typeof window.ZapPopPopElementsRegistry.flushElementFromDOM === 'function') { window.ZapPopPopElementsRegistry.flushElementFromDOM(); }
                if (window.ZapPopPopBuilderInspector && typeof window.ZapPopPopBuilderInspector.flushSanitizedLayerSettings === 'function') { window.ZapPopPopBuilderInspector.flushSanitizedLayerSettings(); }
                if (IO && typeof IO.flushActiveElement === 'function') { IO.flushActiveElement(); }
            } catch (e) {}
            syncLiveMeta(core);
            return JSON.stringify({ canvasMeta: core.state.canvasMeta || {}, settings: S.getSettings() });
        },

        applyMeta: function (meta) {
            var d = saved();
            if (!d || !d.canvasMeta || !meta) { return; }
            var k, p;
            for (k in d.canvasMeta) {
                if (!Object.prototype.hasOwnProperty.call(d.canvasMeta, k) || !meta[k] || typeof d.canvasMeta[k] !== 'object') { continue; }
                for (p in d.canvasMeta[k]) {
                    if (Object.prototype.hasOwnProperty.call(d.canvasMeta[k], p) && p !== 'name') { meta[k][p] = d.canvasMeta[k][p]; }
                }
            }
        },

        applySettings: function () {
            var d = saved(), S = window.ZapPopPopBuilderSettings;
            if (!d || !S || typeof S.setSettings !== 'function' || !d.settings || typeof d.settings !== 'object' || $.isEmptyObject(d.settings)) { return; }
            S.setSettings(clone(d.settings));
        }
    };
})(jQuery);
