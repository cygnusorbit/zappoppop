/* === ZAPPOPPOP_COLOR_BG_OPACITY_JS_START === */
(function($) {
    'use strict';

    var cfg = window.zappoppopElementOpacityConfig || {};
    var labels = cfg.labels || {
        sectionTitle: 'Color & Opacity (All Elements)',
        elementOpacity: 'Element Opacity',
        color: 'Element Color',
        colorOpacity: 'Color Opacity',
        backgroundColor: 'Background Color',
        backgroundOpacity: 'Background Opacity'
    };

    function clampOpacity(val, fallback) {
        if (val === undefined || val === null || val === '') {
            return typeof fallback === 'number' ? fallback : 100;
        }
        var n = parseFloat(val);
        if (isNaN(n)) {
            return typeof fallback === 'number' ? fallback : 100;
        }
        if (typeof val === 'string' && val.indexOf('.') !== -1 && n > 0 && n <= 1) {
            n = Math.round(n * 100);
        }
        return Math.max(0, Math.min(100, Math.round(n)));
    }

    function parseRgbChannels(colorStr) {
        if (!colorStr || typeof colorStr !== 'string') return null;
        var s = colorStr.trim();
        if (!s || s.toLowerCase() === 'transparent' || s.toLowerCase() === 'none') {
            return { r: 0, g: 0, b: 0, isTransparent: true };
        }
        var rgbMatch = s.match(/^rgba?\s*\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})(?:\s*,\s*([0-9.]+))?\s*\)$/i);
        if (rgbMatch) {
            return {
                r: Math.max(0, Math.min(255, parseInt(rgbMatch[1], 10))),
                g: Math.max(0, Math.min(255, parseInt(rgbMatch[2], 10))),
                b: Math.max(0, Math.min(255, parseInt(rgbMatch[3], 10))),
                a: rgbMatch[4] !== undefined ? parseFloat(rgbMatch[4]) : 1
            };
        }
        var hex = s.replace(/^#/, '');
        if (/^[a-f0-9]{3,4}$/i.test(hex)) {
            return {
                r: parseInt(hex[0] + hex[0], 16),
                g: parseInt(hex[1] + hex[1], 16),
                b: parseInt(hex[2] + hex[2], 16),
                a: hex.length === 4 ? (parseInt(hex[3] + hex[3], 16) / 255) : 1
            };
        }
        if (/^[a-f0-9]{6}([a-f0-9]{2})?$/i.test(hex)) {
            return {
                r: parseInt(hex.slice(0, 2), 16),
                g: parseInt(hex.slice(2, 4), 16),
                b: parseInt(hex.slice(4, 6), 16),
                a: hex.length === 8 ? (parseInt(hex.slice(6, 8), 16) / 255) : 1
            };
        }
        return null;
    }

    function toHex6(colorStr, fallbackHex) {
        var rgb = parseRgbChannels(colorStr);
        if (!rgb || rgb.isTransparent) {
            return fallbackHex || '#0f172a';
        }
        var hr = ('0' + rgb.r.toString(16)).slice(-2);
        var hg = ('0' + rgb.g.toString(16)).slice(-2);
        var hb = ('0' + rgb.b.toString(16)).slice(-2);
        return '#' + hr + hg + hb;
    }

    function extractAlphaPercent(colorStr) {
        var rgb = parseRgbChannels(colorStr);
        if (!rgb) return null;
        if (rgb.isTransparent) return 0;
        if (typeof rgb.a === 'number' && !isNaN(rgb.a)) {
            return clampOpacity(Math.round(rgb.a * 100), 100);
        }
        return null;
    }

    function colorWithOpacity(colorStr, opacityPercent) {
        var op = clampOpacity(opacityPercent, 100);
        var alpha = Number((op / 100).toFixed(4));
        var rgb = parseRgbChannels(colorStr);
        if (!rgb) {
            return colorStr || '';
        }
        if (rgb.isTransparent && op === 0) {
            return 'rgba(0, 0, 0, 0)';
        }
        return 'rgba(' + rgb.r + ', ' + rgb.g + ', ' + rgb.b + ', ' + alpha + ')';
    }

    function unhideAllInspectorColorAndOpacityNodes(root) {
        if (!root) return;
        var allGroups = root.querySelectorAll('.zappoppop-control-group, .zappoppop-field, .zappoppop-section, .zappoppop-panel-section, [data-setting-group]');
        for (var i = 0; i < allGroups.length; i++) {
            var g = allGroups[i];
            var txt = (g.textContent || '').toUpperCase();
            if (txt.indexOf('OPACITY') !== -1 || txt.indexOf('BACKGROUND COLOR') !== -1 || txt.indexOf('COLOR') !== -1) {
                if (g.style && g.style.display === 'none') {
                    g.style.display = '';
                }
            }
        }
    }

    function getInspectorRoot() {
        var direct = document.querySelector(
            '#zappoppop-inspector, .zappoppop-inspector, .zappoppop-layer-settings, .zappoppop-element-settings, ' +
            '#zappoppop-layer-inspector, .zappoppop-inspector-panel, .zappoppop-properties-panel, ' +
            '.zappoppop-sidebar-right, #zappoppop-sidebar-right, .zappoppop-theme-builder-inspector'
        );
        if (direct) return direct;

        // Fallback: locate sidebar panel containing layer coordinate or style inputs
        var coordInput = document.querySelector(
            'input[data-setting="left"], input[data-prop="left"], input[name="left"], ' +
            'input[data-setting="width"], input[data-prop="width"], input[id*="layer-width"]'
        );
        if (coordInput) {
            return coordInput.closest('aside, .zappoppop-sidebar, .zappoppop-panel, form, .zappoppop-builder-sidebar') || coordInput.parentElement.parentElement;
        }

        // Theme page (> Builder section) fallback
        var themeBuilderSection = document.querySelector(
            '[data-tab-content="builder"], #zappoppop-theme-builder, .zappoppop-theme-builder'
        );
        return themeBuilderSection || null;
    }

    function getSelectedCanvasLayer() {
        var sel = document.querySelector(
            '#zappoppop-canvas .zappoppop-layer.is-selected, ' +
            '#zappoppop-canvas .zappoppop-layer.active, ' +
            '#zappoppop-canvas .zappoppop-layer.selected, ' +
            '.zappoppop-canvas .zappoppop-layer.is-selected, ' +
            '.zappoppop-canvas .zappoppop-layer.active, ' +
            '.zappoppop-canvas .zappoppop-layer.selected, ' +
            '[data-layer-selected="true"]'
        );
        if (sel) return sel;
        // Fallback to first layer on canvas if none explicitly marked selected yet
        return document.querySelector('#zappoppop-canvas .zappoppop-layer, .zappoppop-canvas .zappoppop-layer');
    }

    function getActiveLayerModel() {
        var selectedEl = getSelectedCanvasLayer();
        var layerId = selectedEl
            ? (selectedEl.getAttribute('data-layer-id') || selectedEl.getAttribute('data-id') || selectedEl.id || '').replace(/^zappoppop-layer-/, '')
            : '';

        var candidates = [
            window.zappoppopActiveLayer,
            window.ZapPopPop && window.ZapPopPop.activeLayer,
            window.ZapPopPopBuilder && window.ZapPopPopBuilder.activeLayer
        ];
        for (var i = 0; i < candidates.length; i++) {
            if (candidates[i] && typeof candidates[i] === 'object') {
                return candidates[i];
            }
        }

        var collections = [
            window.zappoppopLayers,
            window.ZapPopPop && window.ZapPopPop.layers,
            window.ZapPopPopBuilder && window.ZapPopPopBuilder.layers,
            window.zappoppopPopupData && window.zappoppopPopupData.layers
        ];
        for (var c = 0; c < collections.length; c++) {
            var arr = collections[c];
            if (Array.isArray(arr) && layerId) {
                for (var j = 0; j < arr.length; j++) {
                    if (String(arr[j].id) === String(layerId)) {
                        return arr[j];
                    }
                }
            }
        }
        return null;
    }

    function findNativeInspectorInputs(inspector) {
        if (!inspector) return { colorInput: null, bgInput: null, opacityInput: null };

        var colorInput = inspector.querySelector(
            'input[data-setting="color"]:not([data-universal-control]), input[data-prop="color"]:not([data-universal-control]), ' +
            'input[name="color"]:not([data-universal-control]), input[data-setting="text_color"], input[data-prop="text_color"], ' +
            'input[data-setting="fill"], input[data-prop="fill"], input[id*="layer-color"], input[id*="element-color"]'
        );
        var bgInput = inspector.querySelector(
            'input[data-setting="background_color"]:not([data-universal-control]), input[data-prop="background_color"]:not([data-universal-control]), ' +
            'input[name="background_color"]:not([data-universal-control]), input[data-setting="bg_color"], input[data-prop="bg_color"], ' +
            'input[id*="background-color"], input[id*="bg-color"]'
        );
        var opacityInput = inspector.querySelector(
            'input[data-setting="opacity"]:not([data-universal-control]), input[data-prop="opacity"]:not([data-universal-control]), ' +
            'input[name="opacity"]:not([data-universal-control]), input[id*="layer-opacity"]'
        );

        return {
            colorInput: colorInput,
            bgInput: bgInput,
            opacityInput: opacityInput
        };
    }

    function ensureUniversalSuiteMounted(inspector) {
        if (!inspector) return null;
        unhideAllInspectorColorAndOpacityNodes(inspector);

        var existing = inspector.querySelector('[data-zappoppop-universal-suite="true"]');
        if (existing) return existing;

        var suite = document.createElement('div');
        suite.className = 'zappoppop-universal-color-opacity-suite';
        suite.setAttribute('data-zappoppop-universal-suite', 'true');

        suite.innerHTML =
            '<div class="zappoppop-universal-suite-header">' +
                '<span class="zappoppop-universal-suite-title">' + labels.sectionTitle + '</span>' +
                '<span class="zappoppop-universal-suite-badge" data-universal-element-badge="true">Element</span>' +
            '</div>' +

            /* 1. Master Element Opacity (0% - 100%) */
            '<div class="zappoppop-opacity-control-card" data-zappoppop-opacity-card="opacity">' +
                '<div class="zappoppop-opacity-control-header">' +
                    '<span class="zappoppop-opacity-control-label">' + labels.elementOpacity + '</span>' +
                    '<span class="zappoppop-opacity-swatch-pill">' +
                        '<span data-opacity-readout="opacity">100%</span>' +
                    '</span>' +
                '</div>' +
                '<div class="zappoppop-opacity-control-row">' +
                    '<input type="range" class="zappoppop-opacity-slider" min="0" max="100" step="1" value="100" data-opacity-range="opacity" data-universal-control="true" aria-label="' + labels.elementOpacity + '" />' +
                    '<div class="zappoppop-opacity-number-wrap">' +
                        '<input type="number" class="zappoppop-opacity-number" min="0" max="100" step="1" value="100" data-opacity-input="opacity" data-universal-control="true" aria-label="' + labels.elementOpacity + ' Percentage" />' +
                        '<span class="zappoppop-opacity-unit">%</span>' +
                    '</div>' +
                '</div>' +
            '</div>' +

            /* 2. Element Color + Color Opacity (0% - 100%) */
            '<div class="zappoppop-opacity-control-card" data-zappoppop-opacity-card="color_opacity">' +
                '<div class="zappoppop-opacity-control-header">' +
                    '<span class="zappoppop-opacity-control-label">' + labels.color + ' &amp; ' + labels.colorOpacity + '</span>' +
                    '<span class="zappoppop-opacity-swatch-pill">' +
                        '<span class="zappoppop-opacity-swatch-checker">' +
                            '<span class="zappoppop-opacity-swatch-fill" data-opacity-swatch="color_opacity"></span>' +
                        '</span>' +
                        '<span data-opacity-readout="color_opacity">100%</span>' +
                    '</span>' +
                '</div>' +
                '<div class="zappoppop-color-picker-row">' +
                    '<input type="color" class="zappoppop-universal-color-well" value="#0f172a" data-universal-color-well="color" data-universal-control="true" aria-label="' + labels.color + ' Picker" />' +
                    '<input type="text" class="zappoppop-universal-color-hex" value="#0f172a" data-universal-color-hex="color" data-universal-control="true" placeholder="#0f172a or rgba(...)" aria-label="' + labels.color + ' Hex" />' +
                '</div>' +
                '<div class="zappoppop-opacity-control-row">' +
                    '<input type="range" class="zappoppop-opacity-slider" min="0" max="100" step="1" value="100" data-opacity-range="color_opacity" data-universal-control="true" aria-label="' + labels.colorOpacity + '" />' +
                    '<div class="zappoppop-opacity-number-wrap">' +
                        '<input type="number" class="zappoppop-opacity-number" min="0" max="100" step="1" value="100" data-opacity-input="color_opacity" data-universal-control="true" aria-label="' + labels.colorOpacity + ' Percentage" />' +
                        '<span class="zappoppop-opacity-unit">%</span>' +
                    '</div>' +
                '</div>' +
            '</div>' +

            /* 3. Background Color + Background Opacity (0% - 100%) */
            '<div class="zappoppop-opacity-control-card" data-zappoppop-opacity-card="background_opacity">' +
                '<div class="zappoppop-opacity-control-header">' +
                    '<span class="zappoppop-opacity-control-label">' + labels.backgroundColor + ' &amp; Opacity</span>' +
                    '<span class="zappoppop-opacity-swatch-pill">' +
                        '<span class="zappoppop-opacity-swatch-checker">' +
                            '<span class="zappoppop-opacity-swatch-fill" data-opacity-swatch="background_opacity"></span>' +
                        '</span>' +
                        '<span data-opacity-readout="background_opacity">100%</span>' +
                    '</span>' +
                '</div>' +
                '<div class="zappoppop-color-picker-row">' +
                    '<input type="color" class="zappoppop-universal-color-well" value="#ffffff" data-universal-color-well="background_color" data-universal-control="true" aria-label="' + labels.backgroundColor + ' Picker" />' +
                    '<input type="text" class="zappoppop-universal-color-hex" value="#ffffff" data-universal-color-hex="background_color" data-universal-control="true" placeholder="#ffffff or transparent" aria-label="' + labels.backgroundColor + ' Hex" />' +
                '</div>' +
                '<div class="zappoppop-opacity-control-row">' +
                    '<input type="range" class="zappoppop-opacity-slider" min="0" max="100" step="1" value="100" data-opacity-range="background_opacity" data-universal-control="true" aria-label="' + labels.backgroundOpacity + '" />' +
                    '<div class="zappoppop-opacity-number-wrap">' +
                        '<input type="number" class="zappoppop-opacity-number" min="0" max="100" step="1" value="100" data-opacity-input="background_opacity" data-universal-control="true" aria-label="' + labels.backgroundOpacity + ' Percentage" />' +
                        '<span class="zappoppop-opacity-unit">%</span>' +
                    '</div>' +
                '</div>' +
            '</div>';

        // Isolate pointer/mouse events inside the suite so dragging sliders never triggers canvas drag
        ['mousedown', 'pointerdown', 'touchstart'].forEach(function(evtName) {
            suite.addEventListener(evtName, function(ev) {
                ev.stopPropagation();
            });
        });

        var nativeInputs = findNativeInspectorInputs(inspector);
        var anchor = nativeInputs.colorInput || nativeInputs.bgInput || nativeInputs.opacityInput;
        if (anchor) {
            var group = anchor.closest('.zappoppop-control-group, .zappoppop-field, .zappoppop-section, .form-field') || anchor.parentElement;
            if (group && group.parentNode) {
                group.parentNode.insertBefore(suite, group.nextSibling);
                return suite;
            }
        }

        var targetContainer = inspector.querySelector(
            '.zappoppop-tab-content-style, [data-tab-panel="style"], .zappoppop-inspector-body, .zappoppop-layer-settings, .zappoppop-inspector-content'
        ) || inspector;
        targetContainer.appendChild(suite);
        return suite;
    }

    function readElementCurrentState(layerEl, model, nativeInputs) {
        var cs = layerEl ? window.getComputedStyle(layerEl) : null;

        // 1. Element Opacity (0 - 100)
        var elOp = 100;
        if (model && model.opacity !== undefined && model.opacity !== null) {
            elOp = clampOpacity(model.opacity, 100);
        } else if (layerEl && layerEl.hasAttribute('data-element-opacity')) {
            elOp = clampOpacity(layerEl.getAttribute('data-element-opacity'), 100);
        } else if (nativeInputs.opacityInput && nativeInputs.opacityInput.value !== '') {
            elOp = clampOpacity(nativeInputs.opacityInput.value, 100);
        } else if (cs && cs.opacity !== undefined && cs.opacity !== '') {
            elOp = clampOpacity(Math.round(parseFloat(cs.opacity) * 100), 100);
        }

        // 2. Foreground / Fill Color & Color Opacity
        var rawColor = (nativeInputs.colorInput && nativeInputs.colorInput.value) ||
                       (model && (model.color || model.text_color || model.fill)) ||
                       (layerEl && layerEl.getAttribute('data-raw-color')) ||
                       (cs && cs.color) ||
                       '#0f172a';
        var colorHex = toHex6(rawColor, '#0f172a');

        var colorOp = 100;
        if (model && model.color_opacity !== undefined && model.color_opacity !== null) {
            colorOp = clampOpacity(model.color_opacity, 100);
        } else if (layerEl && layerEl.hasAttribute('data-color-opacity')) {
            colorOp = clampOpacity(layerEl.getAttribute('data-color-opacity'), 100);
        } else {
            var extC = extractAlphaPercent(rawColor);
            if (extC !== null) colorOp = extC;
        }

        // 3. Background Color & Background Opacity
        var rawBg = (nativeInputs.bgInput && nativeInputs.bgInput.value) ||
                    (model && (model.background_color || model.bg_color)) ||
                    (layerEl && layerEl.getAttribute('data-raw-bg-color')) ||
                    (cs && cs.backgroundColor) ||
                    '#ffffff';
        var bgHex = toHex6(rawBg, '#ffffff');

        var bgOp = 100;
        if (model && model.background_opacity !== undefined && model.background_opacity !== null) {
            bgOp = clampOpacity(model.background_opacity, 100);
        } else if (layerEl && layerEl.hasAttribute('data-background-opacity')) {
            bgOp = clampOpacity(layerEl.getAttribute('data-background-opacity'), 100);
        } else {
            var extB = extractAlphaPercent(rawBg);
            if (extB !== null) bgOp = extB;
        }

        return {
            elOp: elOp,
            colorHex: colorHex,
            colorOp: colorOp,
            bgHex: bgHex,
            bgOp: bgOp
        };
    }

    function applyVisualsToCanvasAndModel(state) {
        var layerEl = getSelectedCanvasLayer();
        var model = getActiveLayerModel();
        var inspector = getInspectorRoot();
        var nativeInputs = findNativeInspectorInputs(inspector);

        var elOp = clampOpacity(state.elOp, 100);
        var colorOp = clampOpacity(state.colorOp, 100);
        var bgOp = clampOpacity(state.bgOp, 100);
        var colorHex = state.colorHex || '#0f172a';
        var bgHex = state.bgHex || '#ffffff';

        var rgbaColor = colorWithOpacity(colorHex, colorOp);
        var rgbaBg = colorWithOpacity(bgHex, bgOp);
        var elAlpha = Number((elOp / 100).toFixed(4));

        // Update JS Model
        if (model) {
            model.opacity = elOp;
            model.color = colorHex;
            model.color_opacity = colorOp;
            model.background_color = bgHex;
            model.background_opacity = bgOp;
            if (model.fill !== undefined) model.fill = colorHex;
            if (model.style && typeof model.style === 'object') {
                model.style.opacity = elOp;
                model.style.color = rgbaColor;
                model.style.color_opacity = colorOp;
                model.style.background_color = rgbaBg;
                model.style.background_opacity = bgOp;
            }
        }

        // Sync Native Inspector Inputs if present (without infinite loop)
        if (nativeInputs.opacityInput && document.activeElement !== nativeInputs.opacityInput) {
            nativeInputs.opacityInput.value = String(elOp);
        }
        if (nativeInputs.colorInput && document.activeElement !== nativeInputs.colorInput) {
            nativeInputs.colorInput.value = colorHex;
        }
        if (nativeInputs.bgInput && document.activeElement !== nativeInputs.bgInput) {
            nativeInputs.bgInput.value = bgHex;
        }

        if (!layerEl) return;

        // Store authoritative data attributes on the canvas layer DOM
        layerEl.setAttribute('data-element-opacity', String(elOp));
        layerEl.setAttribute('data-raw-color', colorHex);
        layerEl.setAttribute('data-color-opacity', String(colorOp));
        layerEl.setAttribute('data-raw-bg-color', bgHex);
        layerEl.setAttribute('data-background-opacity', String(bgOp));

        // 1. Apply Element Opacity
        layerEl.style.opacity = String(elAlpha);
        layerEl.style.setProperty('--zpp-el-opacity', String(elAlpha));
        layerEl.style.setProperty('--zpp-color-opacity', String(Number((colorOp / 100).toFixed(4))));
        layerEl.style.setProperty('--zpp-bg-opacity', String(Number((bgOp / 100).toFixed(4))));

        // 2. Apply Color + Color Opacity across all inner element types
        layerEl.style.color = rgbaColor;
        var textTargets = layerEl.querySelectorAll(
            '.zappoppop-layer-inner, .zappoppop-element-content, .zappoppop-layer-content, ' +
            'button, a, input, textarea, select, span, p, h1, h2, h3, h4, h5, h6, label, i'
        );
        textTargets.forEach(function(node) {
            node.style.color = rgbaColor;
        });

        // Vector Shapes & Icons (SVG fill + stroke opacity)
        var svgs = layerEl.querySelectorAll('svg, svg path, svg rect, svg circle, svg polygon, svg ellipse, svg line');
        svgs.forEach(function(svgNode) {
            if (svgNode.tagName.toLowerCase() === 'svg') {
                svgNode.style.color = rgbaColor;
                svgNode.style.fill = rgbaColor;
                svgNode.style.fillOpacity = String(Number((colorOp / 100).toFixed(4)));
            } else {
                var curFill = svgNode.getAttribute('fill');
                if (!curFill || curFill !== 'none') {
                    svgNode.style.fill = rgbaColor;
                    svgNode.style.fillOpacity = String(Number((colorOp / 100).toFixed(4)));
                }
            }
        });

        // Divider elements
        var dividers = layerEl.querySelectorAll('hr, .zappoppop-divider-line');
        dividers.forEach(function(div) {
            div.style.borderColor = rgbaColor;
            div.style.backgroundColor = rgbaColor;
        });

        // Pro elements (Progress Bar fill, Star Rating active stars)
        var proForegrounds = layerEl.querySelectorAll('.zappoppop-star-active, .zappoppop-progress-fill, .zappoppop-rating-stars');
        proForegrounds.forEach(function(pf) {
            pf.style.color = rgbaColor;
            if (pf.classList.contains('zappoppop-progress-fill')) {
                pf.style.backgroundColor = rgbaColor;
            }
        });

        // 3. Apply Background Color + Background Opacity
        layerEl.style.backgroundColor = rgbaBg;
        var bgTargets = layerEl.querySelectorAll(
            '.zappoppop-layer-inner, button, input[type="text"], input[type="email"], textarea, select, ' +
            '.zappoppop-image-select-item, .zappoppop-title-select-item'
        );
        bgTargets.forEach(function(bgNode) {
            bgNode.style.backgroundColor = rgbaBg;
        });
    }

    function updateSuiteUI(suite, state, layerEl) {
        if (!suite) return;

        var badge = suite.querySelector('[data-universal-element-badge]');
        if (badge) {
            var elType = (layerEl && (layerEl.getAttribute('data-type') || layerEl.getAttribute('data-element') || layerEl.getAttribute('data-layer-type'))) || 'Element';
            badge.textContent = elType.replace(/_/g, ' ').toUpperCase();
        }

        // Element Opacity
        var elRange = suite.querySelector('[data-opacity-range="opacity"]');
        var elNum = suite.querySelector('[data-opacity-input="opacity"]');
        var elReadout = suite.querySelector('[data-opacity-readout="opacity"]');
        if (elRange && document.activeElement !== elRange) elRange.value = String(state.elOp);
        if (elNum && document.activeElement !== elNum) elNum.value = String(state.elOp);
        if (elReadout) elReadout.textContent = state.elOp + '%';

        // Color & Color Opacity
        var cWell = suite.querySelector('[data-universal-color-well="color"]');
        var cHex = suite.querySelector('[data-universal-color-hex="color"]');
        var cRange = suite.querySelector('[data-opacity-range="color_opacity"]');
        var cNum = suite.querySelector('[data-opacity-input="color_opacity"]');
        var cSwatch = suite.querySelector('[data-opacity-swatch="color_opacity"]');
        var cReadout = suite.querySelector('[data-opacity-readout="color_opacity"]');

        if (cWell && document.activeElement !== cWell) cWell.value = toHex6(state.colorHex, '#0f172a');
        if (cHex && document.activeElement !== cHex) cHex.value = state.colorHex;
        if (cRange && document.activeElement !== cRange) cRange.value = String(state.colorOp);
        if (cNum && document.activeElement !== cNum) cNum.value = String(state.colorOp);
        if (cSwatch) cSwatch.style.backgroundColor = colorWithOpacity(state.colorHex, state.colorOp);
        if (cReadout) cReadout.textContent = state.colorOp + '%';

        // Background Color & Background Opacity
        var bWell = suite.querySelector('[data-universal-color-well="background_color"]');
        var bHex = suite.querySelector('[data-universal-color-hex="background_color"]');
        var bRange = suite.querySelector('[data-opacity-range="background_opacity"]');
        var bNum = suite.querySelector('[data-opacity-input="background_opacity"]');
        var bSwatch = suite.querySelector('[data-opacity-swatch="background_opacity"]');
        var bReadout = suite.querySelector('[data-opacity-readout="background_opacity"]');

        if (bWell && document.activeElement !== bWell) bWell.value = toHex6(state.bgHex, '#ffffff');
        if (bHex && document.activeElement !== bHex) bHex.value = state.bgHex;
        if (bRange && document.activeElement !== bRange) bRange.value = String(state.bgOp);
        if (bNum && document.activeElement !== bNum) bNum.value = String(state.bgOp);
        if (bSwatch) bSwatch.style.backgroundColor = colorWithOpacity(state.bgHex, state.bgOp);
        if (bReadout) bReadout.textContent = state.bgOp + '%';
    }

    function syncInspectorFromSelection() {
        var inspector = getInspectorRoot();
        if (!inspector) return;

        var suite = ensureUniversalSuiteMounted(inspector);
        if (!suite) return;

        var layerEl = getSelectedCanvasLayer();
        var model = getActiveLayerModel();
        var nativeInputs = findNativeInspectorInputs(inspector);

        var state = readElementCurrentState(layerEl, model, nativeInputs);
        updateSuiteUI(suite, state, layerEl);
    }

    function collectStateFromSuite(suite) {
        var cHexEl = suite.querySelector('[data-universal-color-hex="color"]');
        var bHexEl = suite.querySelector('[data-universal-color-hex="background_color"]');
        var elOpEl = suite.querySelector('[data-opacity-input="opacity"]');
        var cOpEl = suite.querySelector('[data-opacity-input="color_opacity"]');
        var bOpEl = suite.querySelector('[data-opacity-input="background_opacity"]');

        return {
            elOp: clampOpacity(elOpEl ? elOpEl.value : 100, 100),
            colorHex: (cHexEl && cHexEl.value) ? cHexEl.value.trim() : '#0f172a',
            colorOp: clampOpacity(cOpEl ? cOpEl.value : 100, 100),
            bgHex: (bHexEl && bHexEl.value) ? bHexEl.value.trim() : '#ffffff',
            bgOp: clampOpacity(bOpEl ? bOpEl.value : 100, 100)
        };
    }

    function handleSuiteEvent(target) {
        var inspector = getInspectorRoot();
        if (!inspector) return;
        var suite = ensureUniversalSuiteMounted(inspector);
        if (!suite) return;

        // Sync range <-> number pair if slider or percentage input changed
        var rangeKind = target.getAttribute('data-opacity-range');
        var numKind = target.getAttribute('data-opacity-input');
        var kind = rangeKind || numKind;
        if (kind) {
            var clamped = clampOpacity(target.value, 100);
            var rEl = suite.querySelector('[data-opacity-range="' + kind + '"]');
            var nEl = suite.querySelector('[data-opacity-input="' + kind + '"]');
            if (rEl && rEl !== target) rEl.value = String(clamped);
            if (nEl && nEl !== target) nEl.value = String(clamped);
            if (target.type === 'range') target.value = String(clamped);
        }

        // Sync color well <-> hex input
        var wellKind = target.getAttribute('data-universal-color-well');
        var hexKind = target.getAttribute('data-universal-color-hex');
        if (wellKind) {
            var partnerHex = suite.querySelector('[data-universal-color-hex="' + wellKind + '"]');
            if (partnerHex) partnerHex.value = target.value;
        } else if (hexKind) {
            var partnerWell = suite.querySelector('[data-universal-color-well="' + hexKind + '"]');
            var parsed = parseRgbChannels(target.value);
            if (partnerWell && parsed && !parsed.isTransparent) {
                partnerWell.value = toHex6(target.value, '#0f172a');
            }
            var extAlpha = extractAlphaPercent(target.value);
            if (extAlpha !== null) {
                var opKey = hexKind === 'color' ? 'color_opacity' : 'background_opacity';
                var opR = suite.querySelector('[data-opacity-range="' + opKey + '"]');
                var opN = suite.querySelector('[data-opacity-input="' + opKey + '"]');
                if (opR) opR.value = String(extAlpha);
                if (opN) opN.value = String(extAlpha);
            }
        }

        var state = collectStateFromSuite(suite);
        updateSuiteUI(suite, state, getSelectedCanvasLayer());
        applyVisualsToCanvasAndModel(state);
    }

    document.addEventListener('input', function(e) {
        var t = e.target;
        if (!t) return;
        if (t.hasAttribute('data-universal-control')) {
            handleSuiteEvent(t);
            return;
        }
        var inspector = getInspectorRoot();
        if (inspector && inspector.contains(t)) {
            var nativeInputs = findNativeInspectorInputs(inspector);
            if (t === nativeInputs.colorInput || t === nativeInputs.bgInput || t === nativeInputs.opacityInput) {
                var suite = ensureUniversalSuiteMounted(inspector);
                if (!suite) return;
                var state = collectStateFromSuite(suite);
                if (t === nativeInputs.colorInput) state.colorHex = t.value;
                if (t === nativeInputs.bgInput) state.bgHex = t.value;
                if (t === nativeInputs.opacityInput) state.elOp = clampOpacity(t.value, 100);
                updateSuiteUI(suite, state, getSelectedCanvasLayer());
                applyVisualsToCanvasAndModel(state);
            }
        }
    });

    document.addEventListener('change', function(e) {
        var t = e.target;
        if (!t) return;
        if (t.hasAttribute('data-universal-control')) {
            if (t.hasAttribute('data-opacity-input')) {
                t.value = String(clampOpacity(t.value, 100));
            }
            handleSuiteEvent(t);
        }
    });

    document.addEventListener('click', function(e) {
        if (e.target && e.target.closest('#zappoppop-canvas, .zappoppop-canvas, .zappoppop-layers-list, .zappoppop-layer, .zappoppop-element-item')) {
            setTimeout(syncInspectorFromSelection, 20);
        }
    }, true);

    function flushOpacityBeforeSave() {
        var inspector = getInspectorRoot();
        var suite = inspector ? inspector.querySelector('[data-zappoppop-universal-suite="true"]') : null;
        var model = getActiveLayerModel();

        if (suite && model) {
            var st = collectStateFromSuite(suite);
            model.opacity = st.elOp;
            model.color = st.colorHex;
            model.color_opacity = st.colorOp;
            model.background_color = st.bgHex;
            model.background_opacity = st.bgOp;
        }

        var layers = window.zappoppopLayers || (window.ZapPopPop && window.ZapPopPop.layers) || (window.ZapPopPopBuilder && window.ZapPopPopBuilder.layers);
        if (Array.isArray(layers)) {
            layers.forEach(function(layer) {
                if (!layer || typeof layer !== 'object') return;
                var domEl = document.querySelector('[data-layer-id="' + layer.id + '"], [data-id="' + layer.id + '"], #zappoppop-layer-' + layer.id);
                if (domEl) {
                    if (domEl.hasAttribute('data-element-opacity')) {
                        layer.opacity = clampOpacity(domEl.getAttribute('data-element-opacity'), 100);
                    }
                    if (domEl.hasAttribute('data-raw-color')) {
                        layer.color = domEl.getAttribute('data-raw-color');
                    }
                    if (domEl.hasAttribute('data-color-opacity')) {
                        layer.color_opacity = clampOpacity(domEl.getAttribute('data-color-opacity'), 100);
                    }
                    if (domEl.hasAttribute('data-raw-bg-color')) {
                        layer.background_color = domEl.getAttribute('data-raw-bg-color');
                    }
                    if (domEl.hasAttribute('data-background-opacity')) {
                        layer.background_opacity = clampOpacity(domEl.getAttribute('data-background-opacity'), 100);
                    }
                }
                if (layer.opacity === undefined) layer.opacity = 100;
                if (layer.color_opacity === undefined) layer.color_opacity = 100;
                if (layer.background_opacity === undefined) layer.background_opacity = 100;
            });
        }
    }

    if ($ && $.ajaxPrefilter) {
        $.ajaxPrefilter(function(options) {
            if (options && typeof options.data === 'string' &&
                (options.data.indexOf('zappoppop_save_popup') !== -1 || options.data.indexOf('zappoppop_builder_save') !== -1 || options.data.indexOf('zappoppop_template_save') !== -1)) {
                flushOpacityBeforeSave();
            }
        });
    }

    document.addEventListener('click', function(e) {
        if (e.target && e.target.closest('#zappoppop-save-popup, .zappoppop-save-btn, [data-action="save-popup"], [data-action="save-template"]')) {
            flushOpacityBeforeSave();
        }
    }, true);

    function initObserver() {
        syncInspectorFromSelection();
        var inspector = getInspectorRoot();
        if (inspector && typeof MutationObserver !== 'undefined') {
            var mo = new MutationObserver(function() {
                if (!inspector.querySelector('[data-zappoppop-universal-suite="true"]')) {
                    syncInspectorFromSelection();
                }
            });
            mo.observe(inspector, { childList: true, subtree: true });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initObserver);
    } else {
        setTimeout(initObserver, 30);
    }

    window.zappoppopSyncElementOpacity = syncInspectorFromSelection;
    window.zappoppopFlushElementOpacity = flushOpacityBeforeSave;
    window.zappoppopColorWithOpacity = colorWithOpacity;
})(window.jQuery);
/* === ZAPPOPPOP_COLOR_BG_OPACITY_JS_END === */
