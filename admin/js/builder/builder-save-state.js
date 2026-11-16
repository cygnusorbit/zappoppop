/**
 * ZapPopPop Visual Builder: Save / Update button state
 * ZAPPOPPOP_UPDATE_STATE_V2
 * Clean saved popup -> "Save". Any real user change to a saved popup -> "Update" (colors come from zappoppop-theme row 15).
 * Dirty = (model snapshot differs from the baseline) OR (the user typed / changed a control in the Inspector, Settings or Title).
 * The baseline keeps re-syncing until the first genuine user gesture, so async loading / normalisation never fakes a change.
 */
(function(window, $) {
    'use strict';
    if (window.__zappoppopSaveStateBound) return;
    window.__zappoppopSaveStateBound = true;

    var BTN = '#zappoppop-btn-save';
    var UI = '#zappoppop-inspector-drawer, #zappoppop-settings-drawer, #zappoppop-builder-title';
    var baseline = null, pending = null, busy = false, touched = false, forced = false;

    function json(v) {
        try { return JSON.stringify(v); } catch (e) {}
        try {
            return JSON.stringify(v, function(k, x) { return (typeof x === 'function' || (x && x.nodeType)) ? undefined : x; });
        } catch (e2) { return null; }
    }

    function snap() {
        var c = window.ZapPopPopBuilderCore;
        if (!c || !c.state) return null;
        var s = null, S = window.ZapPopPopBuilderSettings;
        if (S && typeof S.getSettings === 'function') { try { s = S.getSettings(); } catch (e) {} }
        return json({ c: c.state.canvases, m: c.state.canvasMeta, t: $('#zappoppop-builder-title').val() || '', s: s });
    }

    function hasUid() {
        var io = window.ZapPopPopBuilderIO;
        var u = (io && typeof io.getUid === 'function') ? io.getUid() : $('#zappoppop-builder-uid').val();
        if (!u) { var c = window.ZapPopPopBuilderCore; u = c && c.state && c.state.id; }
        return !!u;
    }

    function paint(dirty) {
        var $b = $(BTN);
        if (!$b.length || busy || $b.prop('disabled')) return;
        $b.toggleClass('is-dirty', dirty);
        var want = dirty ? 'Update' : 'Save';
        if ($.trim($b.text()) !== want) { $b.text(want); }
    }

    function check() {
        if (busy) return;
        var now = snap();
        if (now === null) return;
        if (baseline === null || !touched) { baseline = now; forced = false; paint(false); return; }
        paint(hasUid() && (forced || now !== baseline));
    }

    function rebase() { touched = false; forced = false; baseline = snap(); check(); }

    function isAction(settings, name) {
        return settings && typeof settings.data === 'string' && settings.data.indexOf(name) !== -1;
    }

    /* first genuine gesture: freeze the baseline as it is right now (before the gesture changes anything) */
    function onGesture(e) {
        if (!e.isTrusted || touched) return;
        var s = snap();
        if (s !== null) { baseline = s; }
        touched = true;
    }

    /* typing / changing a control inside the Inspector, Settings drawer or Title always counts, even before the model is flushed */
    function onEdit(e) {
        if (!e.isTrusted) return;
        var t = e.target;
        if (t && t.closest && t.closest(UI)) { onGesture(e); forced = true; setTimeout(check, 30); }
    }

    document.addEventListener('mousedown', onGesture, true);
    document.addEventListener('touchstart', onGesture, true);
    document.addEventListener('keydown', onGesture, true);
    document.addEventListener('input', onEdit, true);
    document.addEventListener('change', onEdit, true);

    $(document).ajaxSend(function(e, xhr, settings) {
        if (isAction(settings, 'zappoppop_save_popup')) { busy = true; pending = snap(); }
    });

    $(document).ajaxSuccess(function(e, xhr, settings, data) {
        if (isAction(settings, 'zappoppop_save_popup')) {
            var ok = !!(data && data.success);
            if (ok) { baseline = pending; forced = false; touched = true; }
            // builder-io restores the button text 1500ms after a successful save
            setTimeout(function() { busy = false; $(BTN).removeClass('is-dirty'); check(); }, ok ? 1550 : 50);
        } else if (isAction(settings, 'zappoppop_load_popup')) {
            setTimeout(rebase, 900);
        }
    });

    $(document).ajaxError(function(e, xhr, settings) {
        if (isAction(settings, 'zappoppop_save_popup')) { setTimeout(function() { busy = false; check(); }, 50); }
    });

    window.ZapPopPopSaveState = { check: check, rebase: rebase };

    $(function() {
        setTimeout(check, 1200);
        setInterval(function() { if (!document.hidden) check(); }, 700);
    });
})(window, jQuery);

/* === ZAPPOPPOP_SHAPE_INTERACTION_ENGINE_START === */
(function() {
    'use strict';

    // 1. Toggle-Slide Click Handler & Sync
    document.addEventListener('click', function(e) {
        var toggle = e.target.closest('.zappoppop-toggle-switch, .zappoppop-switch, .zappoppop-toggle-slide');
        if (!toggle) return;

        var checkbox = toggle.querySelector('input[type="checkbox"]');
        if (!checkbox) return;

        // If click was directly on the input, allow natural browser state change
        if (e.target !== checkbox) {
            checkbox.checked = !checkbox.checked;
            var changeEvt = new Event('change', { bubbles: true });
            checkbox.dispatchEvent(changeEvt);
        }

        if (checkbox.checked) {
            toggle.classList.add('is-active', 'active');
        } else {
            toggle.classList.remove('is-active', 'active');
        }
    }, true);

    // 2. Shape Background Color Field Suppression on Layer Selection
    function updateShapeInspectorView() {
        var inspector = document.querySelector('.zappoppop-inspector, #zappoppop-inspector, .zappoppop-layer-settings');
        if (!inspector) return;

        var activeLayer = document.querySelector('.zappoppop-layer.is-selected, .zappoppop-layer.active, [data-layer-id].active');
        var layerType = activeLayer ? (activeLayer.getAttribute('data-type') || activeLayer.getAttribute('data-element-type') || '') : '';

        if (!layerType) {
            var typeInp = inspector.querySelector('input[name="type"], input[data-prop="type"]');
            if (typeInp) layerType = typeInp.value;
        }

        if (layerType === 'shape') {
            inspector.setAttribute('data-element', 'shape');
            inspector.setAttribute('data-element-type', 'shape');

            // Hide background color controls dynamically
            var bgControls = inspector.querySelectorAll(
                '.zappoppop-control-background, .zappoppop-control-bg-color, [data-prop="background"], [data-prop="background_color"], [data-prop="backgroundColor"]'
            );
            bgControls.forEach(function(el) {
                el.style.setProperty('display', 'none', 'important');
            });

            // Ensure Stroke default shows 0 if empty
            var strokeInp = inspector.querySelector('input[name="stroke_width"], input[data-prop="stroke_width"], input[data-prop="stroke"]');
            if (strokeInp && (strokeInp.value === '' || strokeInp.value === undefined)) {
                strokeInp.value = '0';
            }

            // Ensure Border & Corner Radius default show 0 if empty
            var borderInp = inspector.querySelector('input[name="border_width"], input[data-prop="border_width"], input[data-prop="borderRadius"]');
            if (borderInp && (borderInp.value === '' || borderInp.value === undefined)) {
                borderInp.value = '0';
            }
        } else {
            if (inspector.getAttribute('data-element') === 'shape') {
                inspector.removeAttribute('data-element');
                inspector.removeAttribute('data-element-type');
            }
        }
    }

    document.addEventListener('click', function() {
        setTimeout(updateShapeInspectorView, 50);
    });
    window.addEventListener('load', function() {
        setTimeout(updateShapeInspectorView, 100);
    });
})();
/* === ZAPPOPPOP_SHAPE_INTERACTION_ENGINE_END === */

/* === ZAPPOPPOP_SHAPE_DEFAULTS_ENFORCER_START === */
(function() {
    'use strict';

    var ZERO_PROPS = [
        'stroke', 'stroke_width', 'strokeWidth', 'stroke-width',
        'border_width', 'borderWidth', 'border-width',
        'border_radius', 'borderRadius', 'border-radius', 'corner_radius', 'cornerRadius', 'radius',
        'shadow_x', 'shadow_y', 'shadow_blur', 'shadow_spread',
        'shadowX', 'shadowY', 'shadowBlur', 'shadowSpread',
        'box_shadow_x', 'box_shadow_y', 'box_shadow_blur', 'box_shadow_spread'
    ];

    function sanitizeShapeModel(element) {
        if (!element) return;
        var type = element.type || element.element_type || element.elementType || '';
        if (type !== 'shape') return;

        // Force stroke to 0 if undefined, null, empty string, or non-explicit
        if (element.stroke_width === undefined || element.stroke_width === null || element.stroke_width === '' || element.stroke_width === 1 || element.stroke_width === '1' || element.stroke_width === '1px') {
            element.stroke_width = 0;
        }
        if (element.stroke === undefined || element.stroke === null || element.stroke === '' || element.stroke === 1 || element.stroke === '1') {
            element.stroke = 0;
        }

        // Force border and corner radius to 0
        if (element.border_width === undefined || element.border_width === null || element.border_width === '') {
            element.border_width = 0;
        }
        if (element.border_radius === undefined || element.border_radius === null || element.border_radius === '') {
            element.border_radius = 0;
        }
        if (element.borderRadius === undefined || element.borderRadius === null || element.borderRadius === '') {
            element.borderRadius = 0;
        }

        // Force all box shadow fields to 0
        ['shadow_x', 'shadow_y', 'shadow_blur', 'shadow_spread'].forEach(function(sp) {
            if (element[sp] === undefined || element[sp] === null || element[sp] === '') {
                element[sp] = 0;
            }
        });
    }

    function syncInspectorInputs() {
        var inspector = document.querySelector('.zappoppop-inspector, #zappoppop-inspector, .zappoppop-sidebar-settings, .zappoppop-layer-settings');
        if (!inspector) return;

        var activeLayer = document.querySelector('.zappoppop-layer.is-selected, .zappoppop-layer.active, [data-layer-id].active, .zappoppop-layer-item.active');
        var layerType = activeLayer ? (activeLayer.getAttribute('data-type') || activeLayer.getAttribute('data-element-type') || activeLayer.getAttribute('data-element') || '') : '';

        if (!layerType) {
            var typeInp = inspector.querySelector('input[name="type"], input[data-prop="type"], [data-setting="type"]');
            if (typeInp) layerType = typeInp.value;
        }

        if (layerType === 'shape') {
            inspector.setAttribute('data-element', 'shape');
            inspector.setAttribute('data-element-type', 'shape');

            // Hide background color controls
            var bgControls = inspector.querySelectorAll(
                '.zappoppop-control-background, .zappoppop-control-bg-color, [data-prop="background"], [data-prop="background_color"], [data-prop="backgroundColor"], .control-background-color'
            );
            bgControls.forEach(function(el) {
                el.style.setProperty('display', 'none', 'important');
            });

            // Enforce "0" on empty or uninitialized target fields in inspector DOM
            var inputs = inspector.querySelectorAll('input, select');
            inputs.forEach(function(inp) {
                var prop = (inp.getAttribute('data-prop') || inp.getAttribute('data-setting') || inp.getAttribute('data-key') || inp.name || inp.id || '').toLowerCase();
                for (var i = 0; i < ZERO_PROPS.length; i++) {
                    var zp = ZERO_PROPS[i].toLowerCase();
                    if (prop === zp || prop.endsWith('_' + zp) || prop.endsWith('-' + zp)) {
                        if (inp.value === '' || inp.value === undefined || inp.value === null || inp.value === '1' && (prop.indexOf('stroke') !== -1 || prop.indexOf('border') !== -1)) {
                            // If it defaulted to 1 or empty on a brand new shape, reset to 0
                            if (!activeLayer.getAttribute('data-has-custom-style')) {
                                inp.value = '0';
                                var evt = new Event('input', { bubbles: true });
                                inp.dispatchEvent(evt);
                            }
                        }
                        break;
                    }
                }
            });
        }
    }

    // Intercept builder element addition
    if (typeof window.ZapPopPop !== 'undefined' && window.ZapPopPop.registerElement) {
        var origReg = window.ZapPopPop.registerElement;
        window.ZapPopPop.registerElement = function(type, config) {
            if (type === 'shape' && config && config.defaults) {
                config.defaults.stroke = 0;
                config.defaults.stroke_width = 0;
                config.defaults.strokeWidth = 0;
                config.defaults.border_width = 0;
                config.defaults.border_radius = 0;
                config.defaults.borderRadius = 0;
                config.defaults.shadow_x = 0;
                config.defaults.shadow_y = 0;
                config.defaults.shadow_blur = 0;
                config.defaults.shadow_spread = 0;
            }
            return origReg.apply(this, arguments);
        };
    }

    // Bind DOM events
    document.addEventListener('click', function() {
        setTimeout(syncInspectorInputs, 40);
    }, true);

    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(syncInspectorInputs, 80);
    });

    window.addEventListener('load', function() {
        setTimeout(syncInspectorInputs, 150);
    });
})();
/* === ZAPPOPPOP_SHAPE_DEFAULTS_ENFORCER_END === */

/* === ZAPPOPPOP_SHAPE_STROKE_ZERO_ENGINE_START === */
(function() {
    'use strict';

    function enforceShapeStrokeZero() {
        var inspector = document.querySelector('.zappoppop-inspector, #zappoppop-inspector, .zappoppop-layer-settings');
        if (!inspector) return;

        var activeLayer = document.querySelector('.zappoppop-layer.is-selected, .zappoppop-layer.active, [data-layer-id].active, .zappoppop-layer-item.active');
        var layerType = activeLayer ? (activeLayer.getAttribute('data-type') || activeLayer.getAttribute('data-element-type') || activeLayer.getAttribute('data-element') || '') : '';

        if (!layerType) {
            var typeInp = inspector.querySelector('input[name="type"], input[data-prop="type"], [data-setting="type"]');
            if (typeInp) layerType = typeInp.value;
        }

        if (layerType === 'shape') {
            // Find STROKE WIDTH (PX) inputs in inspector
            var strokeInputs = inspector.querySelectorAll(
                'input[name="stroke_width"], input[name="strokeWidth"], input[name="stroke"], ' +
                'input[data-prop="stroke_width"], input[data-prop="strokeWidth"], input[data-prop="stroke"], ' +
                'input[data-setting="stroke_width"], input[data-key="stroke_width"], #stroke_width'
            );

            strokeInputs.forEach(function(inp) {
                // If input is empty, undefined, or defaulted to 1 on initial load, lock it to 0
                if (inp.value === '' || inp.value === undefined || inp.value === null || (inp.value === '1' && !activeLayer.hasAttribute('data-stroke-user-set'))) {
                    inp.value = '0';
                    inp.setAttribute('value', '0');
                    var evt = new Event('input', { bubbles: true });
                    inp.dispatchEvent(evt);
                }
            });

            // Mark when user explicitly changes stroke
            strokeInputs.forEach(function(inp) {
                if (!inp._strokeTrackerAttached) {
                    inp._strokeTrackerAttached = true;
                    inp.addEventListener('change', function() {
                        if (activeLayer) activeLayer.setAttribute('data-stroke-user-set', 'true');
                    });
                }
            });
        }
    }

    // Intercept builder element registration to force default 0
    if (typeof window.ZapPopPop !== 'undefined' && window.ZapPopPop.registerElement) {
        var origRegister = window.ZapPopPop.registerElement;
        window.ZapPopPop.registerElement = function(type, config) {
            if (type === 'shape' && config && config.defaults) {
                config.defaults.stroke_width = 0;
                config.defaults.strokeWidth = 0;
                config.defaults.stroke = 0;
            }
            return origRegister.apply(this, arguments);
        };
    }

    document.addEventListener('click', function() {
        setTimeout(enforceShapeStrokeZero, 40);
    }, true);

    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(enforceShapeStrokeZero, 80);
    });

    window.addEventListener('load', function() {
        setTimeout(enforceShapeStrokeZero, 120);
    });
})();
/* === ZAPPOPPOP_SHAPE_STROKE_ZERO_ENGINE_END === */
