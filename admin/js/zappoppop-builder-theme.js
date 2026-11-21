/**
 * ZapPopPop Builder Theme Client Controller (Phase 7H)
 * - Compact 2-Row Controls inside Side-by-Side Layout
 * - 5 Dedicated Text Color + 5 Dedicated Border Color Bindings:
 *     01. Header & Toolbar (--zpp-builder-header-bg / -text / -border)
 *     02. Content & Sidebar (--zpp-builder-content-bg / -text / -border)
 *     03. Accordion Tab (--zpp-builder-accordion-bg / -text / -border)
 *     04. Form Field & Button (--zpp-builder-field-bg / -text / -border)
 *     12. Primary Save Button (--zpp-builder-save-bg / -text / -border)
 * - 13. Dedicated Canvas Guide Line Color (--zpp-builder-guide-color)
 */
(function() {
    'use strict';

    var DATA = window.zappoppopBuilderThemeData || {};
    var STORAGE_KEY = 'zappoppop_builder_theme_live';

    function clampOpacity(val) {
        var n = parseInt(val, 10);
        if (isNaN(n)) return 100;
        return Math.max(0, Math.min(100, n));
    }

    function normalizeHex(hex, fallback) {
        var s = String(hex || '').trim();
        if (/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/.test(s)) {
            if (s.length === 4) {
                return ('#' + s[1] + s[1] + s[2] + s[2] + s[3] + s[3]).toLowerCase();
            }
            return s.toLowerCase();
        }
        return fallback || '#0f172a';
    }

    function hexToRgba(hex, opacity) {
        var clean = normalizeHex(hex, '#0f172a').slice(1);
        var r = parseInt(clean.substring(0, 2), 16) || 0;
        var g = parseInt(clean.substring(2, 4), 16) || 0;
        var b = parseInt(clean.substring(4, 6), 16) || 0;
        var a = clampOpacity(opacity) / 100;
        return 'rgba(' + r + ', ' + g + ', ' + b + ', ' + Number(a.toFixed(2)) + ')';
    }

    function imgEscape(u) {
        return String(u).replace(/"/g, '%22').replace(/\\/g, '%5C').replace(/\(/g, '%28').replace(/\)/g, '%29').replace(/\s/g, '%20');
    }

    function setImageControls(panel, url) {
        var val = (typeof url === 'string') ? url.trim() : '';
        var inp = panel.querySelector('.zpp-bt-image-url');
        var thumb = panel.querySelector('.zpp-bt-image-thumb');
        if (inp && inp.value !== val) inp.value = val;
        if (thumb) thumb.style.backgroundImage = val ? 'url("' + imgEscape(val) + '")' : 'none';
    }

    function getActiveSettings() {
        // ZAPPOPPOP: the saved server settings are the single source of truth. The old localStorage copy
        // (written on every live-preview keystroke) could be stale, so colors painted by the server CSS
        // appeared first and were then replaced by the cached ones once this script ran.
        var base = Object.assign({}, DATA.defaults || {}, DATA.settings || {});

        var stageHex = normalizeHex(base.stage_bg_color, '#090d16');
        var checkHex = normalizeHex(base.checker_color, '#1e293b');
        if (stageHex === checkHex) {
            base.checker_color = (stageHex === '#ffffff' || stageHex === '#f1f5f9' || stageHex === '#f8fafc') ? '#cbd5e1' : '#1e293b';
        }
        if (base.checker_opacity === undefined || base.checker_opacity === null) {
            base.checker_opacity = 65;
        }
        return base;
    }

    function compileVariablesMap(settings) {
        var s = Object.assign({}, DATA.defaults || {}, settings || {});

        var headerRgba    = hexToRgba(s.header_bg_color, s.header_bg_opacity);
        var contentRgba   = hexToRgba(s.content_bg_color, s.content_bg_opacity);
        var accordionRgba = hexToRgba(s.accordion_bg_color, s.accordion_bg_opacity);
        var fieldRgba     = hexToRgba(s.field_bg_color, s.field_bg_opacity);
        var areaRgba      = hexToRgba(s.area_bg_color || s.accordion_bg_color, s.area_bg_opacity !== undefined ? s.area_bg_opacity : s.accordion_bg_opacity);
        var borderRgba    = hexToRgba(s.border_color, s.border_opacity);
        var stageRgba     = hexToRgba(s.stage_bg_color, s.stage_bg_opacity);
        var checkerRgba   = hexToRgba(s.checker_color, s.checker_opacity);
        var guideRgba     = hexToRgba(s.canvas_guide_color || '#64748b', s.canvas_guide_opacity !== undefined ? s.canvas_guide_opacity : 85);

        // 5 Dedicated Text Colors
        var fallbackTxtHex  = normalizeHex(s.text_color, '#f8fafc');
        var headerTxtHex    = normalizeHex(s.header_text_color, fallbackTxtHex);
        var contentTxtHex   = normalizeHex(s.content_text_color, fallbackTxtHex);
        var accordionTxtHex = normalizeHex(s.accordion_text_color, fallbackTxtHex);
        var fieldTxtHex     = normalizeHex(s.field_text_color, fallbackTxtHex);
        var saveTxtHex      = normalizeHex(s.save_btn_text_color, '#0f172a');

        // 5 Dedicated Border Colors
        var headerBdrHex    = normalizeHex(s.header_border_color, '#1e293b');
        var contentBdrHex   = normalizeHex(s.content_border_color, '#1e293b');
        var accordionBdrHex = normalizeHex(s.accordion_border_color, '#334155');
        var fieldBdrHex     = normalizeHex(s.field_border_color, '#334155');
        var saveBdrHex      = normalizeHex(s.save_btn_border_color, '#ca8a04');

        var mutedHex        = normalizeHex(s.muted_text_color, '#94a3b8');
        var iconHex         = normalizeHex(s.icon_color, '#cbd5e1');
        var accentHex       = normalizeHex(s.hover_active_color, '#3b82f6');
        var saveBgHex       = normalizeHex(s.save_btn_bg_color, '#eab308');

        var checkerImg =
            'linear-gradient(45deg, ' + checkerRgba + ' 25%, transparent 25%), ' +
            'linear-gradient(-45deg, ' + checkerRgba + ' 25%, transparent 25%), ' +
            'linear-gradient(45deg, transparent 75%, ' + checkerRgba + ' 75%), ' +
            'linear-gradient(-45deg, transparent 75%, ' + checkerRgba + ' 75%)';

        var iconEncoded = encodeURIComponent(iconHex);
        var chevronSvg  = "url(\"data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='" + iconEncoded + "' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E\")";

        var layerSelRgba = hexToRgba(s.layer_sel_bg_color || '#3b82f6', s.layer_sel_bg_opacity !== undefined ? s.layer_sel_bg_opacity : 22);
        var glowRgba = hexToRgba(s.glow_color || '#22d3ee', s.glow_opacity !== undefined ? s.glow_opacity : 0);
        var edgeRgba = hexToRgba(s.edge_border_color || '#ff8a2a', s.edge_border_opacity !== undefined ? s.edge_border_opacity : 0);
        var stageImgUrl = (typeof s.stage_image_url === 'string') ? s.stage_image_url.trim() : '';
        var stageDim = hexToRgba(s.stage_bg_color, 100 - clampOpacity(s.stage_image_opacity !== undefined ? s.stage_image_opacity : 100));
        var stageImage = stageImgUrl ? ('linear-gradient(' + stageDim + ', ' + stageDim + '), url("' + imgEscape(stageImgUrl) + '")') : 'none';

        return {
            '--zpp-builder-glow':                glowRgba,
            '--zpp-builder-glow-shadow':         '0 0 10px 1px ' + glowRgba,
            '--zpp-builder-edge-border':         edgeRgba,
            '--zpp-builder-edge-ring':           'inset 0 0 0 1px ' + edgeRgba,
            '--zpp-builder-stage-image':         stageImage,
            '--zpp-builder-layers-border':       normalizeHex(s.layers_area_border_color, contentBdrHex),
            '--zpp-builder-layers-title-bg':     hexToRgba(s.layers_title_bg_color || s.accordion_bg_color, s.layers_title_bg_opacity !== undefined ? s.layers_title_bg_opacity : s.accordion_bg_opacity),
            '--zpp-builder-layers-title-text':   normalizeHex(s.layers_title_text_color, accordionTxtHex),
            '--zpp-builder-layers-title-border': normalizeHex(s.layers_title_border_color, accordionBdrHex),
            '--zpp-builder-layers-area-bg':      hexToRgba(s.layers_area_bg_color || s.content_bg_color, s.layers_area_bg_opacity !== undefined ? s.layers_area_bg_opacity : s.content_bg_opacity),
            '--zpp-builder-layers-area-text':    normalizeHex(s.layers_area_text_color, contentTxtHex),
            '--zpp-builder-layers-area-border':  normalizeHex(s.layers_area_border_color, contentBdrHex),
            '--zpp-builder-ribbon-bg':           hexToRgba(s.ribbon_bg_color || s.header_bg_color, s.ribbon_bg_opacity !== undefined ? s.ribbon_bg_opacity : s.header_bg_opacity),
            '--zpp-builder-ribbon-border':       normalizeHex(s.ribbon_border_color, headerBdrHex),
            '--zpp-builder-layer-sel-bg':        layerSelRgba,
            '--zpp-builder-layer-sel-text':      normalizeHex(s.layer_sel_text_color, '#f8fafc'),
            '--zpp-builder-layer-sel-border':    normalizeHex(s.layer_sel_border_color, '#3b82f6'),
            '--zpp-builder-update-bg':           normalizeHex(s.update_btn_bg_color, '#f97316'),
            '--zpp-builder-update-text':         normalizeHex(s.update_btn_text_color, '#ffffff'),
            '--zpp-builder-update-border':       normalizeHex(s.update_btn_border_color, '#ea580c'),
            '--zpp-builder-element-bg':          normalizeHex(s.element_bg_color, '#0f172a'),
            '--zpp-builder-element-text':        normalizeHex(s.element_text_color, '#e2e8f0'),
            '--zpp-builder-element-border':      normalizeHex(s.element_border_color, '#334155'),
            '--zpp-builder-element-icon':        normalizeHex(s.element_icon_color, '#94a3b8'),
            '--zpp-builder-element-hover':       normalizeHex(s.element_hover_color, '#334155'),
            '--zpp-builder-header-bg':           headerRgba,
            '--zpp-builder-header-text':         headerTxtHex,
            '--zpp-builder-header-border':       headerBdrHex,
            '--zpp-builder-subtoolbar-bg':       headerRgba,
            '--zpp-builder-floating-bar-bg':     headerRgba,
            '--zpp-builder-content-bg':          contentRgba,
            '--zpp-builder-content-text':        contentTxtHex,
            '--zpp-builder-content-border':      contentBdrHex,
            '--zpp-builder-popup-drawer-bg':     contentRgba,
            '--zpp-builder-popup-settings-bg':   contentRgba,
            '--zpp-builder-section-card-bg':     contentRgba,
            '--zpp-builder-layers-body-bg':      contentRgba,
            '--zpp-builder-accordion-bg':        accordionRgba,
            '--zpp-builder-accordion-text':      accordionTxtHex,
            '--zpp-builder-accordion-border':    accordionBdrHex,
            '--zpp-builder-area-bg':             areaRgba,
            '--zpp-builder-area-text':           normalizeHex(s.area_text_color, accordionTxtHex),
            '--zpp-builder-area-border':         normalizeHex(s.area_border_color, accordionBdrHex),
            '--zpp-builder-popup-acc-bg':        accordionRgba,
            '--zpp-builder-popup-acc-active-bg': accordionRgba,
            '--zpp-builder-layers-header-bg':    accordionRgba,
            '--zpp-builder-field-bg':            fieldRgba,
            '--zpp-builder-field-text':          fieldTxtHex,
            '--zpp-builder-field-border':        fieldBdrHex,
            '--zpp-builder-popup-field-bg':      fieldRgba,
            '--zpp-builder-toolbar-btn-bg':      fieldRgba,
            '--zpp-builder-border-color':        borderRgba,
            '--zpp-builder-popup-border-color':  contentBdrHex,
            '--zpp-builder-toggle-off-bg':       borderRgba,
            '--zpp-builder-text-color':          fallbackTxtHex,
            '--zpp-builder-popup-acc-text':      accordionTxtHex,
            '--zpp-builder-muted-text':          mutedHex,
            '--zpp-builder-icon-color':          iconHex,
            '--zpp-builder-popup-acc-icon':      iconHex,
            '--zpp-builder-accent-color':        accentHex,
            '--zpp-builder-accent-soft':         hexToRgba(accentHex, 18),
            '--zpp-builder-accent-ring':         hexToRgba(accentHex, 32),
            '--zpp-builder-stage-bg':            stageRgba,
            '--zpp-builder-checker-color':       checkerRgba,
            '--zpp-builder-checker-image':       checkerImg,
            '--zpp-builder-save-bg':             saveBgHex,
            '--zpp-builder-save-text':           saveTxtHex,
            '--zpp-builder-save-border':         saveBdrHex,
            '--zpp-builder-guide-color':         guideRgba,
            '--zpp-builder-selection-color':     accentHex,
            '--zpp-builder-select-chevron':      chevronSvg
        };
    }

    function applyCssVariablesToRoot(settings, persistLocal) {
        if (!settings) return;
        var root = document.documentElement;
        var body = document.body;
        var map = compileVariablesMap(settings);

        if (body) {
            var fxOn = Number(settings.glow_opacity) > 0 || Number(settings.edge_border_opacity) > 0;
            if (fxOn) { body.classList.add('zpp-fx-on'); } else { body.classList.remove('zpp-fx-on'); }
            var bgOn = typeof settings.stage_image_url === 'string' && settings.stage_image_url.trim() !== '';
            if (bgOn) { body.classList.add('zpp-bg-img'); } else { body.classList.remove('zpp-bg-img'); }
        }

        Object.keys(map).forEach(function(prop) {
            if (map[prop] !== undefined) {
                root.style.setProperty(prop, map[prop]);
                if (body) body.style.setProperty(prop, map[prop]);
            }
        });

        var previewShell = document.getElementById('zpp-bt-live-preview-shell');
        if (previewShell) {
            Object.keys(map).forEach(function(prop) {
                previewShell.style.setProperty(prop, map[prop]);
            });
            updateDynamicPreviewElements(previewShell, map);
        }

        if (persistLocal !== false) {
            try {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(settings));
            } catch (e) {}
        }

        if (window.location.search.indexOf('page=zappoppop-builder') !== -1) {
            bindBuilderElementsToCssVars();
        }
    }

    function updateDynamicPreviewElements(shell, map) {
        if (!shell || !map) return;

        shell.style.backgroundColor = map['--zpp-builder-stage-bg'];
        shell.style.borderColor = map['--zpp-builder-content-border'];
        var hasStageImg = map['--zpp-builder-stage-image'] && map['--zpp-builder-stage-image'] !== 'none';
        shell.style.backgroundImage = hasStageImg ? map['--zpp-builder-stage-image'] : map['--zpp-builder-checker-image'];
        shell.style.backgroundSize = hasStageImg ? 'cover' : '';
        shell.style.backgroundPosition = hasStageImg ? 'center' : '';
        shell.style.backgroundRepeat = hasStageImg ? 'no-repeat' : '';

        // 01. Header & Toolbar (+ Dedicated Header Text & Border)
        shell.querySelectorAll('[data-pv-role="header"]').forEach(function(el) {
            el.style.backgroundColor = map['--zpp-builder-header-bg'];
            el.style.borderColor = map['--zpp-builder-header-border'];
            el.style.color = map['--zpp-builder-header-text'];
        });
        shell.querySelectorAll('[data-pv-role="header-text"]').forEach(function(el) {
            el.style.color = map['--zpp-builder-header-text'];
        });

        // 02. Content & Sidebar (+ Dedicated Content Text & Border)
        shell.querySelectorAll('[data-pv-role="content"]').forEach(function(el) {
            el.style.backgroundColor = map['--zpp-builder-content-bg'];
            el.style.borderColor = map['--zpp-builder-content-border'];
            el.style.color = map['--zpp-builder-content-text'];
        });
        shell.querySelectorAll('[data-pv-role="content-text"]').forEach(function(el) {
            el.style.color = map['--zpp-builder-content-text'];
        });

        // 03. Accordion Title (+ Dedicated Title Text & Border)
        shell.querySelectorAll('[data-pv-role="accordion"]').forEach(function(el) {
            el.style.backgroundColor = map['--zpp-builder-accordion-bg'];
            el.style.borderColor = map['--zpp-builder-accordion-border'];
            el.style.color = map['--zpp-builder-accordion-text'];
        });
        shell.querySelectorAll('[data-pv-role="accordion-text"]').forEach(function(el) {
            el.style.color = map['--zpp-builder-accordion-text'];
        });

        shell.querySelectorAll('[data-pv-role="ribbon-bar"]').forEach(function(el) {
            el.style.backgroundColor = map['--zpp-builder-ribbon-bg'];
            el.style.borderColor = map['--zpp-builder-ribbon-border'];
            el.style.color = map['--zpp-builder-header-text'];
        });

        // Layers Panel: frame / title / area
        // ZPP_PV_LAYERS_V3: no outer frame; title and area each own their border
        shell.querySelectorAll('[data-pv-role="layers-frame"]').forEach(function(el) {
            el.style.borderColor = '';
        });
        shell.querySelectorAll('[data-pv-role="layers-title"]').forEach(function(el) {
            el.style.backgroundColor = map['--zpp-builder-layers-title-bg'];
            el.style.borderColor = map['--zpp-builder-layers-title-border'];
            el.style.color = map['--zpp-builder-layers-title-text'];
        });
        shell.querySelectorAll('[data-pv-role="layers-title-text"]').forEach(function(el) {
            el.style.color = map['--zpp-builder-layers-title-text'];
        });
        shell.querySelectorAll('[data-pv-role="layers-area"]').forEach(function(el) {
            el.style.backgroundColor = map['--zpp-builder-layers-area-bg'];
            el.style.borderColor = map['--zpp-builder-layers-area-border']; /* layers-area-border-v3 */
            el.style.color = map['--zpp-builder-layers-area-text'];
        });
        shell.querySelectorAll('[data-pv-role="layers-row"]').forEach(function(el) {
            el.style.backgroundColor = 'transparent';
            el.style.borderColor = map['--zpp-builder-layers-area-border'];
            el.style.color = map['--zpp-builder-layers-area-text'];
        });
        shell.querySelectorAll('[data-pv-role="layers-area-text"]').forEach(function(el) {
            el.style.color = map['--zpp-builder-layers-area-text'];
        });

        // 03b. Accordion Setting Area (body of the Layer Settings blocks)
        shell.querySelectorAll('[data-pv-role="area"]').forEach(function(el) {
            el.style.backgroundColor = map['--zpp-builder-area-bg'];
            if (el.parentNode) { el.parentNode.style.backgroundColor = map['--zpp-builder-area-bg']; el.parentNode.style.borderColor = map['--zpp-builder-area-border']; }
            el.style.color = map['--zpp-builder-area-text'];
        });
        shell.querySelectorAll('[data-pv-role="area-text"]').forEach(function(el) {
            if (el.closest('[data-pv-role="accordion"]')) { return; }
            el.style.color = map['--zpp-builder-area-text'];
        });

        // 04. Form Field & Button (+ Dedicated Field Text & Border)
        shell.querySelectorAll('[data-pv-role="field"]').forEach(function(el) {
            el.style.backgroundColor = map['--zpp-builder-field-bg'];
            el.style.borderColor = map['--zpp-builder-field-border'];
            el.style.color = map['--zpp-builder-field-text'];
        });
        shell.querySelectorAll('[data-pv-role="field-text"]').forEach(function(el) {
            el.style.color = map['--zpp-builder-field-text'];
        });

        // 05, 06, 07, 09. Fallback Text, Muted, Icons, Dividers
        shell.querySelectorAll('[data-pv-role="text"]').forEach(function(el) {
            el.style.color = map['--zpp-builder-text-color'];
        });

        shell.querySelectorAll('[data-pv-role="muted"]').forEach(function(el) {
            el.style.color = map['--zpp-builder-muted-text'];
        });

        shell.querySelectorAll('[data-pv-role="icon"]').forEach(function(el) {
            el.style.color = map['--zpp-builder-icon-color'];
        });

        shell.querySelectorAll('[data-pv-role="border"]').forEach(function(el) {
            el.style.borderColor = map['--zpp-builder-border-color'];
        });

        // 12. Primary Save Button (+ Dedicated Save Button Text & Border)
        shell.querySelectorAll('[data-pv-role="save-btn"]').forEach(function(el) {
            el.style.backgroundColor = map['--zpp-builder-save-bg'];
            el.style.color = map['--zpp-builder-save-text'];
            el.style.borderColor = map['--zpp-builder-save-border'];
        });

        // 13. Dedicated Canvas Guide Line Color
        shell.querySelectorAll('[data-pv-role="canvas-guide"]').forEach(function(el) {
            el.style.borderColor = map['--zpp-builder-guide-color'];
        });

        shell.querySelectorAll('[data-pv-role="accent-bg"]').forEach(function(el) {
            el.style.backgroundColor = map['--zpp-builder-accent-color'];
            el.style.borderColor = map['--zpp-builder-accent-color'];
            el.style.color = '#ffffff';
        });

        shell.querySelectorAll('[data-pv-role="accent-text"]').forEach(function(el) {
            el.style.color = map['--zpp-builder-accent-color'];
            el.style.borderColor = map['--zpp-builder-accent-color'];
        });

        shell.querySelectorAll('[data-pv-toggle]').forEach(function(tgl) {
            tgl.style.backgroundColor = tgl.classList.contains('is-on')
                ? map['--zpp-builder-accent-color']
                : map['--zpp-builder-border-color'];
        });
    }

    // ==========================================================================
    // BUILDER DOM BINDER (/admin.php?page=zappoppop-builder)
    // ==========================================================================

    var activeCanvasIdentifier = '';
    var isBindingBuilderDOM = false;

    /**
     * Unwrap any legacy .zappoppop-canvas-group-outer / .zappoppop-canvas-group-scale
     * wrapper divs on the Builder page so the Canvas sits in its native DOM parent.
     */
    function unwrapLegacyCanvasGroupWrappers() {
        document.querySelectorAll('.zappoppop-canvas-group-outer').forEach(function(outer) {
            var scaleBox = outer.querySelector('.zappoppop-canvas-group-scale');
            var realCanvas = scaleBox ? scaleBox.firstElementChild : outer.firstElementChild;
            if (realCanvas && outer.parentNode) {
                outer.parentNode.insertBefore(realCanvas, outer);
                outer.parentNode.removeChild(outer);
            }
        });
    }

    /**
     * Accurately locate the Popup Canvas container on the Stage and isolate it from
     * all Builder Theme background/text/width/height rules.
     */
    function locateAndIsolateCanvasElement() {
        unwrapLegacyCanvasGroupWrappers();

        // 1. Check standard canvas selectors first
        var canvasEl = document.querySelector('#zappoppop-canvas, .zappoppop-canvas, .zappoppop-popup-canvas, #popup-canvas, #canvas-preview');

        // 2. Fallback: locate via the "350 x 400" dimension readout on the stage
        var dimReadoutEl = null;
        var all = canvasEl ? [] : document.querySelectorAll('div, span, small'); /* ZAPPOPPOP_THEME_SCAN_ONLY_IF_NEEDED */
        for (var i = 0; i < all.length; i++) {
            var el = all[i];
            if (el.children.length === 0 && /^[0-9]{2,4}\s*[x×]\s*[0-9]{2,4}$/i.test((el.textContent || '').trim())) {
                dimReadoutEl = el;
                break;
            }
        }

        if (!canvasEl && dimReadoutEl) {
            var par = dimReadoutEl.parentElement;
            var prev = dimReadoutEl.previousElementSibling;
            // Check if the "350 x 400" readout is an absolute child INSIDE the Canvas box
            // OR a sibling right after the Canvas box:
            if (prev && prev.getBoundingClientRect().width >= 100 && prev.getBoundingClientRect().height >= 100) {
                canvasEl = prev;
            } else if (par) {
                canvasEl = par;
            }
        }

        if (dimReadoutEl) {
            setZppRole(dimReadoutEl, 'muted');
            dimReadoutEl.style.setProperty('color', 'var(--zpp-builder-muted-text)', 'important');
            dimReadoutEl.style.setProperty('background', 'transparent', 'important');
        }

        if (canvasEl) {
            canvasEl.setAttribute('data-zpp-isolated-canvas', '1');
            canvasEl.removeAttribute('data-zpp-role');

            // Remove any Builder Theme inline variables or transparent overrides that were placed on the canvas
            ['background', 'background-color', 'background-image', 'color', 'width', 'height', 'min-width', 'min-height'].forEach(function(prop) {
                var val = canvasEl.style.getPropertyValue(prop) || '';
                var prio = canvasEl.style.getPropertyPriority(prop) || '';
                if (val.indexOf('--zpp-builder-') !== -1 || (prop === 'background' && val === 'transparent' && prio === 'important')) {
                    canvasEl.style.removeProperty(prop);
                }
            });
        }

        return canvasEl;
    }

    /**
     * Find the "1. Canvas Settings" accordion body container in the Left Sidebar ("Popup Settings")
     * so we NEVER confuse Canvas Settings inputs with Layer Settings inputs!
     */
    function findCanvasSettingsBodyContainer() {
        var headers = document.querySelectorAll('div, button, summary, h3, h4, span');
        for (var i = 0; i < headers.length; i++) {
            var h = headers[i];
            if (h.closest && h.closest('#zappoppop-tour, #zpp-sc-modal')) continue; /* ZPP_TOUR_THEME_SKIP */
            var txt = (h.textContent || '').replace(/\s+/g, ' ').trim();
            if (/^1\.\s*Canvas Settings/i.test(txt) && !hasFormControlsInside(h)) {
                var bar = h;
                while (bar.parentElement && bar.parentElement !== document.body && !hasFormControlsInside(bar.parentElement)) {
                    var pTxt = (bar.parentElement.textContent || '').replace(/\s+/g, ' ').trim();
                    if (pTxt.length > txt.length + 10) break;
                    bar = bar.parentElement;
                }
                if (bar.nextElementSibling && hasFormControlsInside(bar.nextElementSibling)) {
                    return bar.nextElementSibling;
                }
            }
        }
        return null;
    }

    /**
     * Synchronize the Canvas Element's Size (Width & Height), Background Color, and Border Radius
     * directly with the controls inside "1. Canvas Settings".
     */
    function syncCanvasWithCanvasSettings(canvasEl) {
        if (!canvasEl) return;
        // The builder core already applies per-canvas size / radius / background to #zappoppop-canvas-box.
        if (canvasEl.id === 'zappoppop-canvas-box') return;

        var csBody = findCanvasSettingsBodyContainer();
        if (!csBody) return;

        var targetW = 0;
        var targetH = 0;
        var targetRadius = null;
        var bgMode = '';
        var bgHexColor = '';

        // 1. Read WIDTH (PX), HEIGHT (PX), and RADIUS (PX) strictly inside 1. Canvas Settings
        csBody.querySelectorAll('label, span, div').forEach(function(lbl) {
            if (lbl.children.length > 0) return;
            var tx = (lbl.textContent || '').trim().toUpperCase();
            if (tx === 'WIDTH (PX)' || tx === 'HEIGHT (PX)' || tx === 'RADIUS (PX)') {
                var box = lbl.parentElement;
                var inp = box ? box.querySelector('input[type="number"], input[type="text"], input:not([type])') : null;
                if (!inp && lbl.nextElementSibling && lbl.nextElementSibling.tagName === 'INPUT') {
                    inp = lbl.nextElementSibling;
                }
                if (inp) {
                    var val = parseFloat(inp.value);
                    if (!isNaN(val)) {
                        if (tx === 'WIDTH (PX)' && val >= 50 && val <= 5000) targetW = val;
                        if (tx === 'HEIGHT (PX)' && val >= 50 && val <= 5000) targetH = val;
                        if (tx === 'RADIUS (PX)' && val >= 0 && val <= 1000) targetRadius = val;
                    }
                }
            }
        });

        // 2. Read CANVAS COLOR mode (<select>) and color picker (<input type="color">) inside 1. Canvas Settings
        var selects = csBody.querySelectorAll('select');
        selects.forEach(function(sel) {
            var optText = (sel.options && sel.selectedIndex >= 0) ? (sel.options[sel.selectedIndex].text || '') : '';
            var valText = (sel.value || '') + ' ' + optText;
            if (/solid|transparent|gradient|background|color|none/i.test(valText)) {
                bgMode = valText.toLowerCase();
            }
        });

        var colorInputs = csBody.querySelectorAll('input[type="color"], input[data-zpp-alpha]');
        if (colorInputs.length > 0) {
            bgHexColor = colorInputs[0].value;
        } else {
            // Fallback: look for hex text input near CANVAS COLOR
            csBody.querySelectorAll('input[type="text"]').forEach(function(inp) {
                var v = (inp.value || '').trim();
                if (/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/.test(v)) {
                    bgHexColor = v;
                }
            });
        }

        // 3. Apply Width & Height from 1. Canvas Settings to Canvas
        if (targetW >= 50 && targetH >= 50) {
            canvasEl.style.width = targetW + 'px';
            canvasEl.style.height = targetH + 'px';
            canvasEl.style.minWidth = targetW + 'px';
            canvasEl.style.minHeight = targetH + 'px';

            // Update the "350 x 400" dimension readout on the stage if present
            document.querySelectorAll('div, span, small').forEach(function(el) {
                if (el.children.length === 0 && /^[0-9]{2,4}\s*[x×]\s*[0-9]{2,4}$/i.test((el.textContent || '').trim())) {
                    var newReadout = Math.round(targetW) + ' x ' + Math.round(targetH);
                    if (el.textContent !== newReadout) {
                        el.textContent = newReadout;
                    }
                }
            });
        }

        // 4. Apply Border Radius from 1. Canvas Settings to Canvas
        if (targetRadius !== null) {
            canvasEl.style.borderRadius = targetRadius + 'px';
        }

        // 5. Apply Canvas Background Color from 1. Canvas Settings to Canvas
        if (bgMode.indexOf('transparent') !== -1 || bgMode.indexOf('none') !== -1) {
            canvasEl.style.backgroundColor = 'transparent';
            canvasEl.style.backgroundImage = 'none';
        } else if (bgHexColor && (bgMode.indexOf('solid') !== -1 || bgMode === '')) {
            canvasEl.style.backgroundColor = bgHexColor;
        }
    }

    function isInsideIsolatedCanvas(el) {
        if (!el || !el.closest) return false;
        /* ZPP_TOUR_THEME_SKIP: Builder Guide tour + Keyboard Shortcut modal own their colours (Theme > Builder Guide) */
        if (el.closest('#zappoppop-tour, #zpp-sc-modal')) return true;
        return !!(el.closest('[data-zpp-isolated-canvas="1"], #zappoppop-canvas-box, #zappoppop-canvas, .zappoppop-canvas, .zappoppop-popup-canvas, #popup-canvas, #canvas-preview'));
    }

    function isCheckerSwatchElement(el) {
        if (!el) return false;
        var cls = ((el.className || '') + ' ' + (el.id || '') + ' ' + (el.getAttribute('title') || '')).toLowerCase();
        if (/checker|transparent|swatch|alpha|color-preview|opacity-swatch/.test(cls)) {
            return true;
        }
        var inlineStyle = (el.getAttribute('style') || '').toLowerCase();
        if (inlineStyle.indexOf('linear-gradient(45deg') !== -1 || inlineStyle.indexOf('repeating-conic-gradient') !== -1) {
            return true;
        }
        var r = el.getBoundingClientRect();
        if (r.width > 0 && r.width <= 36 && r.height > 0 && r.height <= 36) {
            var bgImg = window.getComputedStyle(el).backgroundImage || '';
            if (bgImg.indexOf('gradient') !== -1) {
                return true;
            }
        }
        return false;
    }

    // Elements whose DOM is known and fixed are themed by class-driven CSS (see the PHP late cascade).
    // Inline !important would override that CSS, which is why the selected-layer / Update / canvas-tab colors vanished.
    var CSS_MANAGED_SEL = [
        '#zappoppop-btn-save', '#zappoppop-btn-save *', '#zappoppop-btn-save-template', '#zappoppop-btn-save-template *',
        '.zappoppop-canvas-tab-wrapper', '.zappoppop-canvas-tab-wrapper *', '.zappoppop-canvas-add-btn',
        '.zappoppop-canvas-bullets-bar', '.zappoppop-canvas-bullets-bar *',
        '.zappoppop-acc-item', '.zappoppop-acc-item > .zappoppop-acc-header', '.zappoppop-acc-item > .zappoppop-acc-header *',
        '.zappoppop-acc-item > .zappoppop-acc-body',
        '#zappoppop-floating-layers-panel', '#zappoppop-floating-layers-panel *',
        '.zappoppop-ribbon-tool', '.zappoppop-ribbon-tool *', '.zappoppop-ribbon-bar',
        '#zappoppop-inspector-drawer', '#zappoppop-inspector-drawer *'
    ].join(',');

    function isCssManaged(el) {
        try { return !!(el && el.matches && el.matches(CSS_MANAGED_SEL)); } catch (e) { return false; }
    }

    /* ZPP_FLICKER_FIX: write data-zpp-role only when it changes (the restyle sweeps re-stamped ~1,500 nodes per layer click) */
    function setZppRole(el, val) {
        if (el && el.getAttribute('data-zpp-role') !== val) { el.setAttribute('data-zpp-role', val); }
    }

    function setVarImportant(el, prop, cssVarExpr) {
        if (!el || !el.style || isInsideIsolatedCanvas(el) || isCssManaged(el)) return;
        /* ZAPPOPPOP_MODE_LOCK: builder-mode-sync.js owns the workspace background while a Day / Night override is active */
        if ((prop === 'background' || prop === 'background-color' || prop === 'background-image') && el.hasAttribute && el.hasAttribute('data-zpp-mode-lock')) return;
        if ((prop === 'background' || prop === 'background-color' || prop === 'background-image') && isCheckerSwatchElement(el)) {
            return;
        }
        if (el.style.getPropertyValue(prop) !== cssVarExpr || el.style.getPropertyPriority(prop) !== 'important') {
            el.style.setProperty(prop, cssVarExpr, 'important');
        }
    }

    function hasFormControlsInside(el) {
        if (!el || !el.querySelector) return false;
        return !!(el.querySelector('input:not([type="hidden"]):not([type="checkbox"]), select, textarea'));
    }

    /**
     * Extract clean canvas name from any tab or pill ("canvas 1", "confirmation", "canvas 2", etc.)
     */
    function extractCanvasNameFromPill(el) {
        if (!el) return '';
        var clone = el.cloneNode(true);
        // Remove close buttons (×) or badges from clone before reading text
        clone.querySelectorAll('button, span, i').forEach(function(ch) {
            var t = (ch.textContent || '').trim();
            if (t === '×' || t === 'x' || t === '✕' || t === '+') {
                ch.remove();
            }
        });
        var raw = (clone.textContent || '').replace(/[×x✕●•]/g, ' ').replace(/\s+/g, ' ').trim().toLowerCase();
        return raw;
    }

    function detectActiveCanvasName() {
        var csBody = findCanvasSettingsBodyContainer();
        if (csBody) {
            // Check CANVAS NAME input inside 1. Canvas Settings first (authoritative source of truth!)
            var labels = csBody.querySelectorAll('label, span, div');
            for (var i = 0; i < labels.length; i++) {
                var l = labels[i];
                if (l.children.length === 0 && (l.textContent || '').trim().toUpperCase() === 'CANVAS NAME') {
                    var p = l.parentElement;
                    var inp = p ? p.querySelector('input[type="text"], input:not([type])') : null;
                    if (!inp && l.nextElementSibling && l.nextElementSibling.tagName === 'INPUT') {
                        inp = l.nextElementSibling;
                    }
                    if (inp && inp.value && inp.value.trim()) {
                        activeCanvasIdentifier = inp.value.trim().toLowerCase();
                        return activeCanvasIdentifier;
                    }
                }
            }
        }
        return activeCanvasIdentifier || 'canvas 1';
    }

    /**
     * Style a Top Header Canvas Tab Pill ("Canvas 1 ×", "Confirmation ×", "Canvas 2 ×", etc.)
     * as a SINGLE outer pill and strip all borders/backgrounds from its inner children.
     */
    function styleTopHeaderCanvasTabPill(pillEl, activeCanvasName) {
        if (!pillEl || isInsideIsolatedCanvas(pillEl)) return;

        var pillCanvasName = extractCanvasNameFromPill(pillEl);
        if (!pillCanvasName || pillCanvasName === '+') return;

        var isActive = (activeCanvasName && pillCanvasName === activeCanvasName);

        pillEl.setAttribute('data-zpp-canvas-name', pillCanvasName);
        setZppRole(pillEl, isActive ? 'canvas-tab-pill-active' : 'canvas-tab-pill');
        setVarImportant(pillEl, 'background-image', 'none');
        setVarImportant(pillEl, 'background-color', isActive ? 'var(--zpp-builder-accent-color)' : 'var(--zpp-builder-field-bg)');
        setVarImportant(pillEl, 'border', '1px solid ' + (isActive ? 'var(--zpp-builder-accent-color)' : 'var(--zpp-builder-field-border)'));
        setVarImportant(pillEl, 'border-color', isActive ? 'var(--zpp-builder-accent-color)' : 'var(--zpp-builder-field-border)');
        setVarImportant(pillEl, 'color', isActive ? '#ffffff' : 'var(--zpp-builder-header-text)');
        setVarImportant(pillEl, 'border-radius', '6px');
        setVarImportant(pillEl, 'box-shadow', 'none');

        var innerTxtColor = isActive ? '#ffffff' : 'var(--zpp-builder-header-text)';
        pillEl.querySelectorAll('*').forEach(function(inner) {
            setZppRole(inner, 'canvas-tab-inner');
            setVarImportant(inner, 'background', 'transparent');
            setVarImportant(inner, 'background-color', 'transparent');
            setVarImportant(inner, 'background-image', 'none');
            setVarImportant(inner, 'border', 'none');
            setVarImportant(inner, 'border-width', '0px');
            setVarImportant(inner, 'box-shadow', 'none');
            setVarImportant(inner, 'outline', 'none');
            setVarImportant(inner, 'color', innerTxtColor);
        });
    }

    /**
     * Style ALL Canvas Switcher Pills & the (+) Button inside "1. Canvas Settings > SELECT CANVAS TO CONFIGURE"
     * and inside the Top Header Bar so clicking any Canvas button or (+) button NEVER shows black!
     */
    function styleAllCanvasSwitcherButtonsAndAddButtons() {
        // Top header tabs + Canvas Settings bullets use the builder's own .active class (class-driven CSS),
        // so they can never disagree with the selected canvas. Name matching below is only a fallback.
        if (document.getElementById('zappoppop-canvas-tabs-container')) return;
        var activeCanvasName = detectActiveCanvasName();

        // 1. Inside 1. Canvas Settings: Find the "SELECT CANVAS TO CONFIGURE" pill bar
        var csBody = findCanvasSettingsBodyContainer();
        if (csBody) {
            var pillBar = null;
            csBody.querySelectorAll('label, span, div').forEach(function(lbl) {
                if (lbl.children.length === 0 && (lbl.textContent || '').trim().toUpperCase().indexOf('SELECT CANVAS') !== -1) {
                    if (lbl.nextElementSibling) {
                        pillBar = lbl.nextElementSibling;
                    } else if (lbl.parentElement && lbl.parentElement.nextElementSibling) {
                        pillBar = lbl.parentElement.nextElementSibling;
                    }
                }
            });

            if (pillBar) {
                setZppRole(pillBar, 'canvas-pill-bar');
                setVarImportant(pillBar, 'background-image', 'none');
                setVarImportant(pillBar, 'background-color', 'var(--zpp-builder-field-bg)');
                setVarImportant(pillBar, 'border-color', 'var(--zpp-builder-field-border)');

                // Direct children of pillBar are the Canvas pills ("● Canvas 1", "● Confirmation", "● Canvas 2"...) and the "+" button!
                Array.prototype.slice.call(pillBar.children).forEach(function(item) {
                    var rawTxt = (item.textContent || '').trim();
                    if (rawTxt === '+' || item.getAttribute('title') === 'Add Canvas' || item.getAttribute('aria-label') === 'Add Canvas') {
                        setZppRole(item, 'add-canvas-btn');
                        setVarImportant(item, 'background-image', 'none');
                        setVarImportant(item, 'background-color', 'var(--zpp-builder-field-bg)');
                        setVarImportant(item, 'border', '1px solid var(--zpp-builder-field-border)');
                        setVarImportant(item, 'color', 'var(--zpp-builder-icon-color)');
                        item.querySelectorAll('*').forEach(function(ch) {
                            setVarImportant(ch, 'background', 'transparent');
                            setVarImportant(ch, 'color', 'var(--zpp-builder-icon-color)');
                        });
                        return;
                    }

                    var cName = extractCanvasNameFromPill(item);
                    if (!cName) return;
                    item.setAttribute('data-zpp-canvas-name', cName);
                    var isAct = (cName === activeCanvasName);

                    if (isAct) {
                        setZppRole(item, 'active-pill');
                        setVarImportant(item, 'background-image', 'none');
                        setVarImportant(item, 'background-color', 'var(--zpp-builder-accent-color)');
                        setVarImportant(item, 'border', '1px solid var(--zpp-builder-accent-color)');
                        setVarImportant(item, 'color', '#ffffff');
                        item.querySelectorAll('*').forEach(function(ch) {
                            setVarImportant(ch, 'background', 'transparent');
                            setVarImportant(ch, 'border', 'none');
                            setVarImportant(ch, 'color', '#ffffff');
                        });
                    } else {
                        setZppRole(item, 'toolbar-btn');
                        setVarImportant(item, 'background-image', 'none');
                        setVarImportant(item, 'background-color', 'var(--zpp-builder-field-bg)');
                        setVarImportant(item, 'border', '1px solid var(--zpp-builder-field-border)');
                        setVarImportant(item, 'color', 'var(--zpp-builder-field-text)');
                        item.querySelectorAll('*').forEach(function(ch) {
                            setVarImportant(ch, 'background', 'transparent');
                            setVarImportant(ch, 'border', 'none');
                            setVarImportant(ch, 'color', 'var(--zpp-builder-field-text)');
                        });
                    }
                });
            }
        }

        // 2. Inside Top Header Bar: Find the Canvas Tab Bar (contains tabs + the "+" button)
        var headerBar = document.querySelector('[data-zpp-role="header"], .zappoppop-builder-header, .zappoppop-header, .zappoppop-topbar, #zappoppop-builder-header');
        if (headerBar) {
            // Find the "+" add-canvas button in the Top Header
            var headerAddBtn = null;
            headerBar.querySelectorAll('button, a, div, span').forEach(function(el) {
                if (el.children.length <= 1 && (el.textContent || '').trim() === '+') {
                    // Climb if it's an inner span inside a button
                    var btn = (el.tagName === 'SPAN' && el.parentElement && el.parentElement.tagName === 'BUTTON') ? el.parentElement : el;
                    headerAddBtn = btn;
                }
            });

            if (headerAddBtn) {
                setZppRole(headerAddBtn, 'add-canvas-btn');
                setVarImportant(headerAddBtn, 'background-image', 'none');
                setVarImportant(headerAddBtn, 'background-color', 'var(--zpp-builder-field-bg)');
                setVarImportant(headerAddBtn, 'border', '1px solid var(--zpp-builder-field-border)');
                setVarImportant(headerAddBtn, 'color', 'var(--zpp-builder-icon-color)');
                headerAddBtn.querySelectorAll('*').forEach(function(ch) {
                    setVarImportant(ch, 'background', 'transparent');
                    setVarImportant(ch, 'color', 'var(--zpp-builder-icon-color)');
                });

                // The siblings of headerAddBtn (or children of its previous sibling tab list container) are the Top Header Canvas Tab Pills!
                var tabGroupParent = headerAddBtn.parentElement;
                if (tabGroupParent && tabGroupParent !== headerBar) {
                    setZppRole(tabGroupParent, 'header-group');
                    setVarImportant(tabGroupParent, 'background', 'transparent');
                    setVarImportant(tabGroupParent, 'background-color', 'transparent');
                    setVarImportant(tabGroupParent, 'border-color', 'transparent');

                    Array.prototype.slice.call(tabGroupParent.children).forEach(function(sib) {
                        if (sib === headerAddBtn) return;
                        if (sib.tagName === 'INPUT') return;
                        // If sib is a inner tab-list container holding multiple tab pills
                        if (sib.children.length >= 2 && (sib.textContent || '').indexOf('×') !== -1 && sib.querySelectorAll('button').length >= 3) {
                            setZppRole(sib, 'header-group');
                            setVarImportant(sib, 'background', 'transparent');
                            setVarImportant(sib, 'background-color', 'transparent');
                            setVarImportant(sib, 'border-color', 'transparent');
                            Array.prototype.slice.call(sib.children).forEach(function(tabPill) {
                                styleTopHeaderCanvasTabPill(tabPill, activeCanvasName);
                            });
                        } else {
                            styleTopHeaderCanvasTabPill(sib, activeCanvasName);
                        }
                    });
                }
            }
        }
    }

    function styleAccordionHeaderAndBody(headerBar) {
        if (!headerBar || isInsideIsolatedCanvas(headerBar)) return;
        if (headerBar.closest && headerBar.closest('#zappoppop-ribbon-wrap')) return; /* ZPP_RIBBON_SKIP2 */

        // Native .zappoppop-acc-item cards are themed by CSS (one border on the rounded card); only style inner wrappers here.
        if (headerBar.classList && headerBar.classList.contains('zappoppop-acc-header')) {
            var nativeBody = headerBar.nextElementSibling;
            if (nativeBody && !isInsideIsolatedCanvas(nativeBody)) { styleContentBodyContainer(nativeBody); }
            return;
        }

        setZppRole(headerBar, 'accordion-header');
        setVarImportant(headerBar, 'background-image', 'none');
        setVarImportant(headerBar, 'background-color', 'var(--zpp-builder-accordion-bg)');
        setVarImportant(headerBar, 'border-color', 'var(--zpp-builder-accordion-border)');
        setVarImportant(headerBar, 'color', 'var(--zpp-builder-accordion-text)');

        headerBar.querySelectorAll('*').forEach(function(ch) {
            if (isCheckerSwatchElement(ch)) return;
            if (ch.tagName === 'BUTTON' && ch !== headerBar) {
                setVarImportant(ch, 'background-color', 'var(--zpp-builder-field-bg)');
                setVarImportant(ch, 'border-color', 'var(--zpp-builder-field-border)');
                setVarImportant(ch, 'color', 'var(--zpp-builder-field-text)');
                return;
            }
            if (ch.closest('button') && ch.closest('button') !== headerBar) {
                return;
            }
            var txt = (ch.textContent || '').trim();
            var isCaret = (ch.tagName === 'SVG' || ch.tagName === 'PATH' || ch.tagName === 'POLYLINE' || ch.tagName === 'I' ||
                          (txt.length === 1 && !/[0-9A-Za-z]/.test(txt)));
            if (isCaret) {
                setZppRole(ch, 'accordion-caret');
                setVarImportant(ch, 'color', 'var(--zpp-builder-icon-color)');
            } else {
                setVarImportant(ch, 'background-color', 'transparent');
                setVarImportant(ch, 'color', 'var(--zpp-builder-accordion-text)');
            }
        });

        var cardWrapper = headerBar.parentElement;
        if (cardWrapper && cardWrapper !== document.body && !isInsideIsolatedCanvas(cardWrapper)) {
            var directHeaders = 0;
            for (var i = 0; i < cardWrapper.children.length; i++) {
                var cText = (cardWrapper.children[i].textContent || '').replace(/\s+/g, ' ').trim();
                if (/^[0-9]{1,2}\.\s+[A-Za-z]/.test(cText) && !hasFormControlsInside(cardWrapper.children[i])) {
                    directHeaders++;
                }
            }
            if (directHeaders <= 1) {
                setZppRole(cardWrapper, 'content-panel');
                setVarImportant(cardWrapper, 'background-image', 'none');
                setVarImportant(cardWrapper, 'background-color', 'var(--zpp-builder-content-bg)');
                setVarImportant(cardWrapper, 'border-color', 'var(--zpp-builder-content-border)');
                setVarImportant(cardWrapper, 'color', 'var(--zpp-builder-content-text)');
            }
        }

        var bodyPane = headerBar.nextElementSibling;
        if (bodyPane && !isInsideIsolatedCanvas(bodyPane)) {
            var bNorm = (bodyPane.textContent || '').replace(/\s+/g, ' ').trim();
            var isAnotherHeader = (/^[0-9]{1,2}\.\s+[A-Za-z]/.test(bNorm) && !hasFormControlsInside(bodyPane));
            if (!isAnotherHeader) {
                styleContentBodyContainer(bodyPane);
            }
        }
    }

    function styleContentBodyContainer(bodyPane) {
        if (!bodyPane || isInsideIsolatedCanvas(bodyPane)) return;
        if (bodyPane.closest && bodyPane.closest('#zappoppop-ribbon-wrap')) return; /* ZPP_RIBBON_SKIP2 */
        setZppRole(bodyPane, 'content-panel');
        setVarImportant(bodyPane, 'background-image', 'none');
        setVarImportant(bodyPane, 'background-color', 'var(--zpp-builder-content-bg)');
        setVarImportant(bodyPane, 'border-color', 'var(--zpp-builder-content-border)');
        setVarImportant(bodyPane, 'color', 'var(--zpp-builder-content-text)');

        bodyPane.querySelectorAll('div, section, fieldset').forEach(function(inner) {
            if (isCheckerSwatchElement(inner)) return;
            if (inner.hasAttribute('data-zpp-role') && inner.getAttribute('data-zpp-role').indexOf('toggle') !== -1) return;
            if (inner.getAttribute('data-zpp-role') === 'canvas-pill-bar') return;
            if (inner.closest && inner.closest('#zappoppop-ribbon-wrap')) return; /* ZPP_RIBBON_SKIP: the Elements Ribbon Bar owns its colours (Theme > Builder > row 23) */
            var r = inner.getBoundingClientRect();
            if (r.width > 0 && r.width <= 54 && r.height > 0 && r.height <= 30 && !inner.querySelector('input[type="text"], input[type="number"], select')) {
                return;
            }
            var cs = window.getComputedStyle(inner);
            var bg = cs.backgroundColor || '';
            if (bg === 'rgb(255, 255, 255)' || bg === 'rgb(248, 250, 252)' || bg === 'rgb(241, 245, 249)' || bg === 'rgb(226, 232, 240)' || hasFormControlsInside(inner)) {
                setZppRole(inner, 'content-panel');
                setVarImportant(inner, 'background-image', 'none');
                setVarImportant(inner, 'background-color', 'var(--zpp-builder-content-bg)');
                setVarImportant(inner, 'border-color', 'var(--zpp-builder-content-border)');
                setVarImportant(inner, 'color', 'var(--zpp-builder-content-text)');
            }
        });
    }

    function applyCheckerboardToStage(stageEl) {
        if (!stageEl || isInsideIsolatedCanvas(stageEl)) return;
        setZppRole(stageEl, 'stage');
        setVarImportant(stageEl, 'background-color', 'var(--zpp-builder-stage-bg)');
        setVarImportant(stageEl, 'background-image', 'var(--zpp-builder-checker-image)');
        setVarImportant(stageEl, 'background-size', '20px 20px');
        setVarImportant(stageEl, 'background-position', '0 0, 0 10px, 10px -10px, -10px 0');
        setVarImportant(stageEl, 'background-repeat', 'repeat');
    }

    function applyCanvasGuideLineColor(canvasEl, stageCandidates) {
        document.querySelectorAll('.zappoppop-guide-line, .zappoppop-snap-line, .zappoppop-alignment-guide, .zappoppop-canvas-guide, .zappoppop-canvas-boundary').forEach(function(gl) {
            setZppRole(gl, 'canvas-guide');
            gl.style.setProperty('border-color', 'var(--zpp-builder-guide-color)', 'important');
        });

        if (canvasEl) {
            var cs = window.getComputedStyle(canvasEl);
            var inlineSt = (canvasEl.getAttribute('style') || '').toLowerCase();
            if (cs.borderTopStyle === 'dashed' || cs.borderStyle === 'dashed' || inlineSt.indexOf('dashed') !== -1 || cs.outlineStyle === 'dashed') {
                canvasEl.style.setProperty('border-color', 'var(--zpp-builder-guide-color)', 'important');
                if (cs.outlineStyle === 'dashed') {
                    canvasEl.style.setProperty('outline-color', 'var(--zpp-builder-guide-color)', 'important');
                }
            }
        }
    }

    function bindBuilderElementsToCssVars() {
        if (isBindingBuilderDOM) return;
        isBindingBuilderDOM = true;

        try {
            // 1. Locate & Isolate the Popup Canvas FIRST so no Builder Theme rule ever overrides it
            var canvasEl = locateAndIsolateCanvasElement();

            var stageCandidates = Array.prototype.slice.call(
                document.querySelectorAll('.zappoppop-stage, #zappoppop-stage, .zappoppop-builder-stage, .zappoppop-canvas-viewport, .zappoppop-canvas-area')
            );

            if (canvasEl) {
                var p = canvasEl.parentElement;
                while (p && p !== document.body && p.id !== 'wpbody-content') {
                    var hasSiblingSidebars = p.parentElement && p.parentElement.children.length >= 2 &&
                                             (p.parentElement.textContent || '').indexOf('Popup Settings') !== -1;
                    if (hasSiblingSidebars || p.clientWidth > window.innerWidth * 0.35) {
                        if (stageCandidates.indexOf(p) === -1) stageCandidates.push(p);
                        if (p.parentElement && p.parentElement !== document.body && p.parentElement.id !== 'wpbody-content') {
                            setZppRole(p.parentElement, 'workspace-flex');
                            setVarImportant(p.parentElement, 'background-color', 'var(--zpp-builder-stage-bg)');
                            setVarImportant(p.parentElement, 'flex-wrap', 'nowrap');
                        }
                        break;
                    } else {
                        // Intermediate wrapper around canvas inside stage: keep transparent so stage checkerboard & canvas color show cleanly
                        p.style.setProperty('background', 'transparent', 'important');
                        p.style.setProperty('background-image', 'none', 'important');
                    }
                    p = p.parentElement;
                }
            }

            stageCandidates.forEach(function(st) {
                applyCheckerboardToStage(st);
                st.querySelectorAll('button, div, span').forEach(function(el) {
                    if (isInsideIsolatedCanvas(el)) return;
                    var r = el.getBoundingClientRect();
                    if (r.width >= 20 && r.width <= 56 && r.height >= 12 && r.height <= 32 && el.children.length <= 2) {
                        var txt = (el.textContent || '').trim();
                        if (txt === '^' || txt === '⌃' || txt === '▴' || txt === '▾' || (el.querySelector('svg') && txt === '')) {
                            setVarImportant(el, 'background-color', 'var(--zpp-builder-header-bg)');
                            setVarImportant(el, 'border-color', 'var(--zpp-builder-header-border)');
                            setVarImportant(el, 'color', 'var(--zpp-builder-icon-color)');
                        }
                    }
                });
            });

            applyCanvasGuideLineColor(canvasEl, stageCandidates);

            // 2. Find Left Sidebar ("Popup Settings") & Right Sidebar ("LAYER SETTINGS")
            var sidebars = [];
            document.querySelectorAll('h1, h2, h3, h4, span, div, strong').forEach(function(el) {
                if (isInsideIsolatedCanvas(el) || el.children.length > 3) return;
                var norm = (el.textContent || '').replace(/\s+/g, ' ').trim();
                if (/^(Popup Settings|LAYER SETTINGS|Layer Settings)(\s*[×x✕])?$/i.test(norm) || norm === 'Popup Settings' || norm === 'LAYER SETTINGS') {
                    var hdrRow = el;
                    while (hdrRow.parentElement && hdrRow.parentElement !== document.body &&
                           hdrRow.parentElement.getBoundingClientRect().height <= 72 &&
                           !hasFormControlsInside(hdrRow.parentElement)) {
                        hdrRow = hdrRow.parentElement;
                    }
                    setZppRole(hdrRow, 'sidebar-header');
                    setVarImportant(hdrRow, 'background-image', 'none');
                    setVarImportant(hdrRow, 'background-color', 'var(--zpp-builder-content-bg)');
                    setVarImportant(hdrRow, 'border-color', 'var(--zpp-builder-content-border)');
                    setVarImportant(hdrRow, 'color', 'var(--zpp-builder-content-text)');

                    var drawer = hdrRow.parentElement;
                    while (drawer && drawer !== document.body && drawer.id !== 'wpbody-content') {
                        if (hasFormControlsInside(drawer) || drawer.children.length >= 2) {
                            setZppRole(drawer, 'sidebar');
                            setVarImportant(drawer, 'background-image', 'none');
                            setVarImportant(drawer, 'background-color', 'var(--zpp-builder-content-bg)');
                            setVarImportant(drawer, 'border-color', 'var(--zpp-builder-content-border)');
                            setVarImportant(drawer, 'color', 'var(--zpp-builder-content-text)');
                            if (sidebars.indexOf(drawer) === -1) sidebars.push(drawer);
                            break;
                        }
                        drawer = drawer.parentElement;
                    }
                }
            });

            // 3. Element-level Accordion Scanner
            var accordionTitleRegex = /^([0-9]{1,2}\.\s*(Canvas Settings|Display Triggers|Autoresponder|Double Opt-In|Notifications|Integrations|Targeting|Scheduling|Sound|Particles|Exit Intent|Scroll|Inactivity|AdBlock|Geo|Cookie|Referrer|Device|User Role|Custom CSS|Analytics|A\/B|Webhook|CRM|Zapier|Mailchimp|WooCommerce|Countdown|Floating|Social|GDPR|Frequency|Overlay|SHAPE\s*\/\s*VECTOR\s*GEOMETRY|[A-Za-z][A-Za-z0-9\s\/&-]{2,35})|COORDINATES\s*&\s*BOUNDS|BORDER\s*&\s*CORNER\s*RADIUS|BOX\s*SHADOW|TYPOGRAPHY|1\.\s*SHAPE\s*\/\s*VECTOR\s*GEOMETRY)(\s*[\+\-−▲▾▼▴⌃⌄×])?$/i;

            document.querySelectorAll('div, button, summary, header, h3, h4, a, label').forEach(function(el) {
                if (isInsideIsolatedCanvas(el)) return;
                if (hasFormControlsInside(el)) return;

                var normText = (el.textContent || '').replace(/\s+/g, ' ').trim();
                if (!normText || normText.length > 75) return;

                if (accordionTitleRegex.test(normText)) {
                    var bar = el;
                    while (bar.parentElement && bar.parentElement !== document.body) {
                        var par = bar.parentElement;
                        if (hasFormControlsInside(par)) break;
                        var parNorm = (par.textContent || '').replace(/\s+/g, ' ').trim();
                        if (parNorm.length > normText.length + 15) break;
                        var pr = par.getBoundingClientRect();
                        if (pr.height > 68) break;
                        bar = par;
                    }
                    styleAccordionHeaderAndBody(bar);
                }
            });

            // 4. Deep Sweeper Inside Sidebars
            sidebars.forEach(function(sb) {
                sb.querySelectorAll('div, button, summary, header').forEach(function(cand) {
                    if (isInsideIsolatedCanvas(cand) || isCheckerSwatchElement(cand)) return;
                    if (!hasFormControlsInside(cand) && cand.nextElementSibling && hasFormControlsInside(cand.nextElementSibling)) {
                        var cr = cand.getBoundingClientRect();
                        var cTxt = (cand.textContent || '').replace(/\s+/g, ' ').trim();
                        if ((cr.height >= 24 && cr.height <= 64) || /^[0-9]{1,2}\./.test(cTxt)) {
                            if (cand.nextElementSibling.querySelectorAll('input, select, textarea').length >= 2 || /^[0-9]{1,2}\./.test(cTxt)) {
                                styleAccordionHeaderAndBody(cand);
                            }
                        }
                    }
                });

                sb.querySelectorAll('div, section, details, summary, button, hr, span, a').forEach(function(node) {
                    if (isInsideIsolatedCanvas(node) || isCheckerSwatchElement(node)) return;

                    if (node.tagName === 'HR') {
                        setZppRole(node, 'divider-line');
                        setVarImportant(node, 'border-color', 'var(--zpp-builder-border-color)');
                        setVarImportant(node, 'background-color', 'var(--zpp-builder-border-color)');
                        return;
                    }

                    var cs = window.getComputedStyle(node);
                    var r = node.getBoundingClientRect();
                    var nTxt = (node.textContent || '').replace(/\s+/g, ' ').trim();

                    if (node.tagName === 'DIV' && r.width > 150) {
                        var bBottom = parseFloat(cs.borderBottomWidth) || 0;
                        var bTop = parseFloat(cs.borderTopWidth) || 0;
                        if (bBottom >= 1 && bBottom <= 2) {
                            setZppRole(node, 'divider-row');
                            setVarImportant(node, 'border-bottom-color', 'var(--zpp-builder-border-color)');
                        }
                        if (bTop >= 1 && bTop <= 2 && !node.hasAttribute('data-zpp-role')) {
                            setVarImportant(node, 'border-top-color', 'var(--zpp-builder-border-color)');
                        }
                        if (r.height >= 1 && r.height <= 2 && node.children.length === 0) {
                            setZppRole(node, 'divider-line');
                            setVarImportant(node, 'background-color', 'var(--zpp-builder-border-color)');
                            return;
                        }
                    }

                    if (/^(General\s*&\s*Color|Canvas\s*Logic|CONTENT|STYLE|LOGIC)$/i.test(nTxt) && r.height >= 20 && r.height <= 48 && node.children.length <= 1) {
                        var bBotW = parseFloat(cs.borderBottomWidth) || 0;
                        var isSubActive = node.classList.contains('active') || node.classList.contains('is-active') ||
                                          node.getAttribute('aria-selected') === 'true' ||
                                          (bBotW >= 1.5 && cs.borderBottomColor !== 'rgba(0, 0, 0, 0)' && cs.borderBottomColor !== 'transparent');
                        if (isSubActive) {
                            setZppRole(node, 'subtab-active');
                            setVarImportant(node, 'color', 'var(--zpp-builder-accent-color)');
                            setVarImportant(node, 'border-bottom-color', 'var(--zpp-builder-accent-color)');
                            setVarImportant(node, 'background-color', 'transparent');
                        } else {
                            setZppRole(node, 'subtab-inactive');
                            setVarImportant(node, 'color', 'var(--zpp-builder-muted-text)');
                            setVarImportant(node, 'background-color', 'transparent');
                        }
                        if (node.parentElement && node.parentElement.getBoundingClientRect().height <= 54) {
                            setVarImportant(node.parentElement, 'background-color', 'var(--zpp-builder-content-bg)');
                            setVarImportant(node.parentElement, 'border-color', 'var(--zpp-builder-content-border)');
                        }
                        return;
                    }

                    if ( /^(▶\s*Play|↺\s*Reset|Play|Reset)$/i.test(nTxt) && r.height >= 20 && r.height <= 38) {
                        setZppRole(node, 'toolbar-btn');
                        setVarImportant(node, 'background-color', 'var(--zpp-builder-field-bg)');
                        setVarImportant(node, 'border-color', 'var(--zpp-builder-field-border)');
                        setVarImportant(node, 'color', 'var(--zpp-builder-field-text)');
                        return;
                    }
                });

                sb.querySelectorAll('input, select, textarea').forEach(function(inp) {
                    if (isInsideIsolatedCanvas(inp)) return;
                    var t = (inp.type || '').toLowerCase();
                    if (t === 'range') {
                        setVarImportant(inp, 'background', 'var(--zpp-builder-border-color)');
                        setVarImportant(inp, 'background-color', 'var(--zpp-builder-border-color)');
                        setVarImportant(inp, 'accent-color', 'var(--zpp-builder-accent-color)');
                        return;
                    }
                    if (t === 'checkbox' || t === 'radio' || t === 'color' || t === 'hidden' || t === 'button' || t === 'submit') return;
                    setZppRole(inp, 'field');
                    setVarImportant(inp, 'background-color', 'var(--zpp-builder-field-bg)');
                    setVarImportant(inp, 'border-color', 'var(--zpp-builder-field-border)');
                    setVarImportant(inp, 'color', 'var(--zpp-builder-field-text)');
                });

                sb.querySelectorAll('label, span, p, small, em, h2, h3, h4').forEach(function(lbl) {
                    if (lbl.children.length > 0 || isInsideIsolatedCanvas(lbl) || isCheckerSwatchElement(lbl)) return;
                    if (lbl.closest && lbl.closest('button, .button, a.button')) return; /* ZPP_BTN_TEXT_SKIP: button text/icons keep the button's own colour */
                    if (lbl.closest('[data-zpp-role="accordion-header"], [data-zpp-role="active-pill"], [data-zpp-role="subtab-active"], [data-zpp-role="subtab-inactive"], [data-zpp-role="toolbar-btn"], [data-zpp-role="add-canvas-btn"], [data-zpp-role="canvas-pill-bar"]')) return;
                    var tx = (lbl.textContent || '').trim();
                    if (/^(Appearance|Duration \(ms\)|Start delay \(ms\)|Start Animation|px|%|deg|ms|sec \(0 = disabled\)|Automatically dismisses.*)$/i.test(tx)) {
                        setZppRole(lbl, 'muted');
                        setVarImportant(lbl, 'color', 'var(--zpp-builder-muted-text)');
                    } else {
                        setVarImportant(lbl, 'color', 'var(--zpp-builder-content-text)');
                    }
                });

                sb.querySelectorAll('input[type="checkbox"]').forEach(function(chk) {
                    if (chk.classList && chk.classList.contains('zpp-switch')) return; /* ZPP_SWITCH_SKIP */
                    var track = chk.nextElementSibling || chk.parentElement;
                    if (!track) return;
                    var tr = track.getBoundingClientRect();
                    if (tr.width >= 24 && tr.width <= 64 && tr.height >= 12 && tr.height <= 34) {
                        setZppRole(track, chk.checked ? 'toggle-track-on' : 'toggle-track-off');
                        setVarImportant(track, 'background-color', chk.checked ? 'var(--zpp-builder-accent-color)' : 'var(--zpp-builder-border-color)');
                    }
                });
            });

            // 5. Floating LAYERS PANEL on Stage
            document.querySelectorAll('div, span, h3, h4').forEach(function(el) {
                if (isInsideIsolatedCanvas(el) || el.children.length > 4) return;
                var txt = (el.textContent || '').replace(/\s+/g, ' ').trim();
                if (/^LAYERS\s*PANEL/i.test(txt)) {
                    var lHdr = el;
                    while (lHdr.parentElement && lHdr.parentElement.getBoundingClientRect().height <= 52 && lHdr.parentElement.children.length <= 4) {
                        lHdr = lHdr.parentElement;
                    }
                    setZppRole(lHdr, 'layers-header');
                    setVarImportant(lHdr, 'background-image', 'none');
                    setVarImportant(lHdr, 'background-color', 'var(--zpp-builder-accordion-bg)');
                    setVarImportant(lHdr, 'border-color', 'var(--zpp-builder-accordion-border)');
                    setVarImportant(lHdr, 'color', 'var(--zpp-builder-accordion-text)');

                    var lPanel = lHdr.parentElement;
                    if (lPanel && lPanel !== document.body) {
                        setZppRole(lPanel, 'layers-panel');
                        setVarImportant(lPanel, 'background-color', 'var(--zpp-builder-content-bg)');
                        setVarImportant(lPanel, 'border-color', 'var(--zpp-builder-content-border)');
                    }
                    if (lHdr.nextElementSibling) {
                        var lBody = lHdr.nextElementSibling;
                        setZppRole(lBody, 'layers-body');
                        setVarImportant(lBody, 'background-color', 'var(--zpp-builder-content-bg)');
                        (document.getElementById('zappoppop-layers-list') ? [] : lBody.querySelectorAll('div, li')).forEach(function(row) {
                            var rr = row.getBoundingClientRect();
                            if (rr.height >= 22 && rr.height <= 48 && rr.width > 100) {
                                setZppRole(row, 'layer-row');
                                setVarImportant(row, 'background-color', 'var(--zpp-builder-field-bg)');
                                setVarImportant(row, 'border-color', 'var(--zpp-builder-field-border)');
                                setVarImportant(row, 'color', 'var(--zpp-builder-field-text)');
                            }
                        });
                    }
                }
            });

            // 6. Top Header Bar & Secondary Toolbar
            var headerBar = null;
            document.querySelectorAll('button, a').forEach(function(btn) {
                if (isInsideIsolatedCanvas(btn)) return;
                if (btn.closest && btn.closest('.zappoppop-save-menu')) return; /* ZAPPOPPOP_MENU_SKIP: dropdown rows are styled by save-menu.css */
                if (btn.hasAttribute('data-zpp-defaults-save') || btn.hasAttribute('data-zpp-defaults-clear') || (btn.closest && btn.closest('#zappoppop-settings-drawer'))) return; /* ZPP_DEFAULTS_SKIP: "Saved!" label on drawer buttons is not the header Save */
                var txt = (btn.textContent || '').trim();
                if (btn.id === 'zappoppop-btn-save' || btn.id === 'zappoppop-btn-save-template' ||
                    /^(Save|Update|Updated|Saving\.\.\.|Saved!?|Save As Template|Save Popup)$/i.test(txt)) {
                    setZppRole(btn, 'save-btn');
                    setVarImportant(btn, 'background-image', 'none');
                    setVarImportant(btn, 'background-color', 'var(--zpp-builder-save-bg)');
                    setVarImportant(btn, 'border-color', 'var(--zpp-builder-save-border)');
                    setVarImportant(btn, 'color', 'var(--zpp-builder-save-text)');
                    btn.querySelectorAll('span, svg, i').forEach(function(ch) {
                        setVarImportant(ch, 'color', 'var(--zpp-builder-save-text)');
                    });
                    var anc = btn.parentElement;
                    var zppCanvasBox = document.getElementById('zappoppop-canvas-box');
                    while (anc && anc !== document.body) {
                        if (/^(wpbody-content|wpbody|wpcontent|wpwrap)$/.test(anc.id || '') || (zppCanvasBox && anc.contains(zppCanvasBox))) { break; } /* ZPP_HEADER_GUARD: never tag a wrapper that holds the canvas */
                        var rect = anc.getBoundingClientRect();
                        if (rect.top < 80 && rect.height >= 34 && rect.height <= 80 && rect.width > window.innerWidth * 0.6) {
                            headerBar = anc;
                            break;
                        }
                        anc = anc.parentElement;
                    }
                }
            });

            if (!headerBar) { headerBar = document.querySelector('.zappoppop-builder-header'); }

            if (headerBar) {
                setZppRole(headerBar, 'header');
                setVarImportant(headerBar, 'background-image', 'none');
                setVarImportant(headerBar, 'background-color', 'var(--zpp-builder-header-bg)');
                setVarImportant(headerBar, 'border-color', 'var(--zpp-builder-header-border)');
                setVarImportant(headerBar, 'color', 'var(--zpp-builder-header-text)');

                if (headerBar.parentElement && headerBar.parentElement !== document.body) {
                    var topWrap = headerBar.parentElement;
                    setZppRole(topWrap, 'workspace-root');
                    setVarImportant(topWrap, 'background-color', 'var(--zpp-builder-stage-bg)');
                    setVarImportant(topWrap, 'border-color', 'var(--zpp-builder-header-border)');
                }

                headerBar.querySelectorAll('input, select').forEach(function(inp) {
                    var t = (inp.type || '').toLowerCase();
                    if (t === 'hidden' || t === 'checkbox' || t === 'radio') return;
                    setZppRole(inp, 'field');
                    setVarImportant(inp, 'background-color', 'var(--zpp-builder-field-bg)');
                    setVarImportant(inp, 'border-color', 'var(--zpp-builder-field-border)');
                    setVarImportant(inp, 'color', 'var(--zpp-builder-field-text)');
                });

                // Style all Canvas Switcher Pills & (+) Buttons in both Top Header and 1. Canvas Settings!
                styleAllCanvasSwitcherButtonsAndAddButtons();

                // Style remaining standalone Header Buttons (Moon, Gear, ?, Eye, Monitor, </>)
                headerBar.querySelectorAll('button, a, [role="button"]').forEach(function(btn) {
                    var role = btn.getAttribute('data-zpp-role') || '';
                    if (role === 'save-btn' || role === 'add-canvas-btn' || role === 'canvas-tab-inner' ||
                        role === 'canvas-tab-pill' || role === 'canvas-tab-pill-active' ||
                        btn.closest('[data-zpp-role="save-btn"], [data-zpp-role="canvas-tab-pill"], [data-zpp-role="canvas-tab-pill-active"]')) {
                        return;
                    }
                    var cs = window.getComputedStyle(btn);
                    var isActBtn = btn.classList.contains('active') || btn.classList.contains('is-active') ||
                                   cs.backgroundColor === 'rgb(37, 99, 235)' || cs.backgroundColor === 'rgb(59, 130, 246)';

                    setZppRole(btn, 'toolbar-btn');
                    setVarImportant(btn, 'background-image', 'none');
                    setVarImportant(btn, 'background-color', 'var(--zpp-builder-field-bg)');
                    setVarImportant(btn, 'border', '1px solid ' + (isActBtn ? 'var(--zpp-builder-accent-color)' : 'var(--zpp-builder-field-border)'));
                    setVarImportant(btn, 'color', isActBtn ? 'var(--zpp-builder-accent-color)' : 'var(--zpp-builder-icon-color)');
                    btn.querySelectorAll('svg, i, .dashicons, span').forEach(function(ic) {
                        setVarImportant(ic, 'color', isActBtn ? 'var(--zpp-builder-accent-color)' : 'var(--zpp-builder-icon-color)');
                    });
                });

                var nextBar = headerBar.nextElementSibling;
                if (nextBar && !isInsideIsolatedCanvas(nextBar)) {
                    var nr = nextBar.getBoundingClientRect();
                    if (nr.height >= 28 && nr.height <= 75 && nr.width > window.innerWidth * 0.5) {
                        setZppRole(nextBar, 'subtoolbar');
                        setVarImportant(nextBar, 'background-image', 'none');
                        setVarImportant(nextBar, 'background-color', 'var(--zpp-builder-header-bg)');
                        setVarImportant(nextBar, 'border-color', 'var(--zpp-builder-header-border)');
                        setVarImportant(nextBar, 'color', 'var(--zpp-builder-header-text)');
                        setVarImportant(nextBar, 'margin-bottom', '0px');

                        nextBar.querySelectorAll('button, a, [role="button"]').forEach(function(tBtn) {
                            setZppRole(tBtn, 'toolbar-btn');
                            setVarImportant(tBtn, 'background-color', 'var(--zpp-builder-field-bg)');
                            setVarImportant(tBtn, 'border-color', 'var(--zpp-builder-field-border)');
                            setVarImportant(tBtn, 'color', 'var(--zpp-builder-icon-color)');
                            tBtn.querySelectorAll('svg, i, span').forEach(function(ic) {
                                setVarImportant(ic, 'color', 'var(--zpp-builder-icon-color)');
                            });
                        });

                        nextBar.querySelectorAll('span, label').forEach(function(lbl) {
                            if (lbl.children.length === 0 && (lbl.textContent || '').trim().length > 1) {
                                setVarImportant(lbl, 'color', 'var(--zpp-builder-header-text)');
                            }
                        });

                        nextBar.querySelectorAll('input[type="checkbox"]').forEach(function(chk) {
                            var track = chk.nextElementSibling || chk.parentElement;
                            if (track) {
                                var tr = track.getBoundingClientRect();
                                if (tr.width >= 24 && tr.width <= 60 && tr.height >= 12 && tr.height <= 32) {
                                    setZppRole(track, chk.checked ? 'toggle-track-on' : 'toggle-track-off');
                                    setVarImportant(track, 'background-color', chk.checked ? 'var(--zpp-builder-accent-color)' : 'var(--zpp-builder-border-color)');
                                }
                            }
                        });
                    }
                }
            }

            // 7. Finally, sync Canvas Size (Width & Height), Color & Radius from 1. Canvas Settings!
            syncCanvasWithCanvasSettings(canvasEl);

        } finally {
            isBindingBuilderDOM = false;
        }
    }

    // ==========================================================================
    // THEME PAGE CONTROLLER (/admin.php?page=zappoppop-theme > Builder)
    // ==========================================================================
    function mountBuilderTabOnThemePage() {
        var existingPanel = document.getElementById('zappoppop-builder-theme-panel');
        var tmpl = document.getElementById('zappoppop-builder-theme-template');
        if (existingPanel && tmpl) {
            if (existingPanel.getAttribute('data-zpp-builder-theme-version') !== '7H') {
                var parent = existingPanel.parentElement;
                parent.innerHTML = tmpl.innerHTML;
                return document.getElementById('zappoppop-builder-theme-panel');
            }
            return existingPanel;
        }
        if (existingPanel) return existingPanel;
        if (!tmpl) return null;

        var html = tmpl.innerHTML;
        var existingPaneSelectors = [
            '#tab-builder',
            '#zappoppop-tab-builder',
            '#zappoppop-theme-builder',
            '#builder',
            '[data-tab-content="builder"]',
            '[data-tab-panel="builder"]',
            '[data-panel="builder"]',
            '.zappoppop-tab-content[data-tab="builder"]',
            '.zappoppop-theme-tab-builder'
        ];

        for (var i = 0; i < existingPaneSelectors.length; i++) {
            var pane = document.querySelector(existingPaneSelectors[i]);
            if (pane) {
                pane.innerHTML = html;
                return document.getElementById('zappoppop-builder-theme-panel');
            }
        }

        var wrap = document.querySelector('.zappoppop-theme-wrap, .zappoppop-admin-wrap, #wpbody-content .wrap, #wpbody-content');
        if (wrap) {
            var holder = document.createElement('div');
            holder.id = 'zappoppop-theme-builder-container';
            holder.innerHTML = html;
            wrap.appendChild(holder);
            return document.getElementById('zappoppop-builder-theme-panel');
        }

        return null;
    }

    function collectCurrentSettingsFromDOM(panel) {
        var out = Object.assign({}, DATA.defaults || {}, DATA.settings || {});

        panel.querySelectorAll('.zpp-bt-color-picker').forEach(function(picker) {
            var key = picker.getAttribute('data-key');
            if (key) {
                out[key] = normalizeHex(picker.value, out[key]);
            }
        });

        panel.querySelectorAll('.zpp-bt-image-url').forEach(function(imgInp) {
            var iKey = imgInp.getAttribute('data-image-key');
            if (iKey) {
                out[iKey] = imgInp.value.trim();
            }
        });

        panel.querySelectorAll('.zpp-bt-opacity-number').forEach(function(numInp) {
            var oKey = numInp.getAttribute('data-opacity-num-for');
            if (oKey) {
                out[oKey] = clampOpacity(numInp.value);
            }
        });

        return out;
    }

    function refreshAllSwatches(panel, settings) {
        panel.querySelectorAll('.zpp-bt-alpha-swatch').forEach(function(swatch) {
            var cKey = swatch.getAttribute('data-swatch-for');
            var oKey = swatch.getAttribute('data-opacity-source');
            if (!cKey) return;
            var hex = settings[cKey] || '#0f172a';
            var op = (oKey && settings[oKey] !== undefined) ? settings[oKey] : 100;
            swatch.style.backgroundColor = hexToRgba(hex, op);
        });
    }

    function populateSettingsIntoDOM(panel, settings) {
        if (!panel || !settings) return;

        Object.keys(settings).forEach(function(key) {
            var val = settings[key];
            if (key === 'stage_image_url') {
                setImageControls(panel, val);
                return;
            }
            if (key.indexOf('_opacity') !== -1) {
                var opVal = clampOpacity(val);
                var range = panel.querySelector('.zpp-bt-opacity-range[data-opacity-key="' + key + '"]');
                var num   = panel.querySelector('.zpp-bt-opacity-number[data-opacity-num-for="' + key + '"]');
                if (range) range.value = opVal;
                if (num) num.value = opVal;
            } else {
                var hexVal = normalizeHex(val, '#0f172a');
                var picker = panel.querySelector('.zpp-bt-color-picker[data-key="' + key + '"]');
                var txt    = panel.querySelector('.zpp-bt-hex-text[data-hex-for="' + key + '"]');
                if (picker) picker.value = hexVal;
                if (txt) txt.value = hexVal;
            }
        });

        refreshAllSwatches(panel, settings);
        applyCssVariablesToRoot(settings, true);
    }

    function showToast(msg, isError) {
        var toast = document.getElementById('zpp-bt-toast');
        if (!toast) return;
        toast.textContent = msg;
        toast.style.display = 'block';
        toast.style.background = isError ? '#fef2f2' : '#ecfdf5';
        toast.style.borderColor = isError ? '#fecaca' : '#a7f3d0';
        toast.style.color = isError ? '#991b1b' : '#065f46';
        clearTimeout(toast._timer);
        toast._timer = setTimeout(function() {
            toast.style.display = 'none';
        }, 3000);
    }

    function sendBuilderThemeAjaxSave(panel, btnElement, silent) {
        var settings = collectCurrentSettingsFromDOM(panel);
        applyCssVariablesToRoot(settings, true);

        var origLabel = btnElement ? btnElement.textContent : '';
        if (btnElement) {
            btnElement.disabled = true;
            btnElement.textContent = 'Saving...';
        }

        var fd = new FormData();
        fd.append('action', 'zappoppop_save_builder_theme');
        fd.append('nonce', DATA.nonce || '');
        Object.keys(settings).forEach(function(k) {
            fd.append('settings[' + k + ']', settings[k]);
        });

        return fetch(DATA.ajaxUrl || window.ajaxurl || '/wp-admin/admin-ajax.php', {
            method: 'POST',
            body: fd,
            credentials: 'same-origin'
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (btnElement) {
                btnElement.disabled = false;
                btnElement.textContent = origLabel;
            }
            if (res && res.success) {
                var saved = (res.data && res.data.settings) ? res.data.settings : settings;
                DATA.settings = saved;
                if (!silent) {
                    populateSettingsIntoDOM(panel, saved);
                    showToast('Builder Theme saved & synced to Builder!');
                }
            }
        })
        .catch(function() {
            if (btnElement) {
                btnElement.disabled = false;
                btnElement.textContent = origLabel;
            }
        });
    }

    function bindInteractivePreviewEvents(panel) {
        var shell = panel.querySelector('#zpp-bt-live-preview-shell');
        if (!shell) return;

        shell.querySelectorAll('.zpp-pv-acc-hdr').forEach(function(hdr) {
            hdr.addEventListener('click', function() {
                var item = hdr.closest('.zpp-pv-acc-item');
                if (!item) return;
                var body = item.querySelector('.zpp-pv-acc-body');
                var isOpen = item.classList.toggle('is-open');
                if (body) {
                    body.style.display = isOpen ? 'flex' : 'none';
                }
                var icon = hdr.querySelector('i');
                if (icon && (icon.textContent === '+' || icon.textContent === '−' || icon.textContent === '-')) {
                    icon.textContent = isOpen ? '−' : '+';
                }
                applyCssVariablesToRoot(collectCurrentSettingsFromDOM(panel), false);
            });
        });

        shell.querySelectorAll('[data-pv-toggle]').forEach(function(tgl) {
            tgl.addEventListener('click', function() {
                tgl.classList.toggle('is-on');
                applyCssVariablesToRoot(collectCurrentSettingsFromDOM(panel), false);
            });
        });

        bindPreviewFit(shell);
    }

    // ZAPPOPPOP_PREVIEW_FIT: the mini-builder is laid out at a fixed design width (3 columns: drawer | stage | layer
    // settings) and scaled down to the pane. Without this the shell is ~350px wide, the stage column collapses to
    // ~60px and the Layer Settings column is clipped by overflow:hidden, so the preview cannot be used.
    function bindPreviewFit(shell) {
        if (!shell || shell._zppFit) return;
        shell._zppFit = true;

        var DESIGN_W = 500;
        var fit = document.createElement('div');
        fit.className = 'zpp-pv-fit';
        shell.parentNode.insertBefore(fit, shell);
        fit.appendChild(shell);

        var raf = 0;
        function apply() {
            raf = 0;
            var avail = fit.clientWidth;
            if (!avail) return; // tab hidden; re-run when it becomes visible (ResizeObserver)
            var k = Math.min(1, avail / DESIGN_W);
            shell.style.width = (k < 1 ? DESIGN_W : avail) + 'px';
            shell.style.transformOrigin = '0 0';
            shell.style.transform = k < 1 ? 'scale(' + k + ')' : 'none';
            fit.style.height = Math.ceil(shell.offsetHeight * k) + 'px';
        }
        function queue() {
            if (!raf) raf = window.requestAnimationFrame(apply);
        }

        if (typeof window.ResizeObserver === 'function') {
            var ro = new window.ResizeObserver(queue);
            ro.observe(fit);
            ro.observe(shell); // accordion open/close changes the shell height
        }
        window.addEventListener('resize', queue);
        queue();
    }

    function bindThemePanelEvents(panel) {
        if (!panel || panel._zppBtBound) return;
        panel._zppBtBound = true;

        var autoSaveTimer = null;
        var syncLive = function() {
            var current = collectCurrentSettingsFromDOM(panel);
            DATA.settings = current;
            refreshAllSwatches(panel, current);
            applyCssVariablesToRoot(current, true);
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(function() {
                sendBuilderThemeAjaxSave(panel, null, true);
            }, 350);
        };

        panel.querySelectorAll('.zpp-bt-color-picker').forEach(function(picker) {
            picker.addEventListener('input', function() {
                var key = picker.getAttribute('data-key');
                var txt = panel.querySelector('.zpp-bt-hex-text[data-hex-for="' + key + '"]');
                if (txt) txt.value = picker.value.toLowerCase();
                syncLive();
            });
        });

        panel.querySelectorAll('.zpp-bt-hex-text').forEach(function(txt) {
            txt.addEventListener('input', function() {
                var val = txt.value.trim();
                if (/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/.test(val)) {
                    var key = txt.getAttribute('data-hex-for');
                    var picker = panel.querySelector('.zpp-bt-color-picker[data-key="' + key + '"]');
                    if (picker) picker.value = normalizeHex(val);
                    syncLive();
                }
            });
        });

        panel.querySelectorAll('.zpp-bt-opacity-range').forEach(function(range) {
            range.addEventListener('input', function() {
                var oKey = range.getAttribute('data-opacity-key');
                var num = panel.querySelector('.zpp-bt-opacity-number[data-opacity-num-for="' + oKey + '"]');
                if (num) num.value = range.value;
                syncLive();
            });
        });

        panel.querySelectorAll('.zpp-bt-opacity-number').forEach(function(num) {
            num.addEventListener('input', function() {
                var oKey = num.getAttribute('data-opacity-num-for');
                var clamped = clampOpacity(num.value);
                var range = panel.querySelector('.zpp-bt-opacity-range[data-opacity-key="' + oKey + '"]');
                if (range) range.value = clamped;
                syncLive();
            });
        });

        panel.querySelectorAll('.zpp-bt-preset-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                var pKey = btn.getAttribute('data-preset');
                if (DATA.presets && DATA.presets[pKey]) {
                    var presetData = Object.assign({}, DATA.presets[pKey]);
                    // keep the user's own uploaded image across presets; the bundled starfield follows the preset
                    var curImgEl = panel.querySelector('.zpp-bt-image-url');
                    var curImg = curImgEl ? curImgEl.value.trim() : '';
                    if (curImg && curImg !== (DATA.builtinBg || '')) {
                        var curOpEl = panel.querySelector('.zpp-bt-opacity-number[data-opacity-num-for="stage_image_opacity"]');
                        presetData.stage_image_url = curImg;
                        if (curOpEl) presetData.stage_image_opacity = clampOpacity(curOpEl.value);
                    }
                    populateSettingsIntoDOM(panel, presetData);
                    sendBuilderThemeAjaxSave(panel, null, false);
                }
            });
        });

        var imgInput = panel.querySelector('.zpp-bt-image-url');
        if (imgInput) {
            imgInput.addEventListener('input', function() {
                setImageControls(panel, imgInput.value);
                syncLive();
            });
            var imgChoose = panel.querySelector('.zpp-bt-image-choose');
            if (imgChoose) {
                imgChoose.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (!window.wp || !window.wp.media) { imgInput.focus(); return; }
                    if (!panel._zppMedia) {
                        panel._zppMedia = window.wp.media({
                            title: 'Choose builder background image',
                            button: { text: 'Use this image' },
                            library: { type: 'image' },
                            multiple: false
                        });
                        panel._zppMedia.on('select', function() {
                            var att = panel._zppMedia.state().get('selection').first();
                            var data = att ? att.toJSON() : null;
                            if (data && data.url) {
                                setImageControls(panel, data.url);
                                syncLive();
                            }
                        });
                    }
                    panel._zppMedia.open();
                });
            }
            var imgBuiltin = panel.querySelector('.zpp-bt-image-builtin');
            if (imgBuiltin) {
                imgBuiltin.addEventListener('click', function(e) {
                    e.preventDefault();
                    setImageControls(panel, DATA.builtinBg || '');
                    syncLive();
                });
            }
            var imgRemove = panel.querySelector('.zpp-bt-image-remove');
            if (imgRemove) {
                imgRemove.addEventListener('click', function(e) {
                    e.preventDefault();
                    setImageControls(panel, '');
                    syncLive();
                });
            }
        }

        var saveBtn = panel.querySelector('#zpp-bt-save-btn');
        if (saveBtn) {
            saveBtn.addEventListener('click', function(e) {
                e.preventDefault();
                sendBuilderThemeAjaxSave(panel, saveBtn, false);
            });
        }

        document.querySelectorAll('button[type="submit"], input[type="submit"], #submit, .zappoppop-save-theme').forEach(function(gBtn) {
            if (gBtn === saveBtn) return;
            gBtn.addEventListener('click', function() {
                sendBuilderThemeAjaxSave(panel, null, true);
            });
        });

        var resetBtn = panel.querySelector('#zpp-bt-reset-btn');
        if (resetBtn) {
            resetBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (!window.confirm('Reset Builder Theme settings to default values?')) {
                    return;
                }
                try { localStorage.removeItem(STORAGE_KEY); } catch (err) {}
                var fd = new FormData();
                fd.append('action', 'zappoppop_reset_builder_theme');
                fd.append('nonce', DATA.nonce || '');

                fetch(DATA.ajaxUrl || window.ajaxurl || '/wp-admin/admin-ajax.php', {
                    method: 'POST',
                    body: fd,
                    credentials: 'same-origin'
                })
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    var defs = (res && res.success && res.data && res.data.settings) ? res.data.settings : (DATA.defaults || {});
                    DATA.settings = defs;
                    populateSettingsIntoDOM(panel, defs);
                    showToast('Builder Theme reset to defaults.');
                });
            });
        }

        var daySave = panel.querySelector('#zpp-bt-day-save');
        if (daySave) {
            daySave.addEventListener('click', function(e) {
                e.preventDefault();
                var fd = new FormData();
                fd.append('action', 'zappoppop_save_day_preset');
                fd.append('nonce', DATA.nonce || '');
                var cur = collectCurrentSettingsFromDOM(panel);
                Object.keys(cur).forEach(function(k) { fd.append('settings[' + k + ']', cur[k]); });
                fetch(DATA.ajaxUrl || window.ajaxurl || '/wp-admin/admin-ajax.php', { method: 'POST', body: fd, credentials: 'same-origin' })
                    .then(function(r) { return r.json(); })
                    .then(function(res) {
                        if (res && res.success) {
                            if (res.data && res.data.preset && DATA.presets) { DATA.presets.day_light = res.data.preset; }
                            showToast('Current settings saved as Day Mode.');
                        } else { showToast('Could not save Day Mode.', true); }
                    })
                    .catch(function() { showToast('Could not save Day Mode.', true); });
            });
        }

        bindInteractivePreviewEvents(panel);
    }

    function initBuilderThemeEngine() {
        var activeSettings = getActiveSettings();
        applyCssVariablesToRoot(activeSettings, false);

        var isThemePage   = window.location.search.indexOf('page=zappoppop-theme') !== -1;
        var isBuilderPage = window.location.search.indexOf('page=zappoppop-builder') !== -1;

        if (isThemePage) {
            var panel = mountBuilderTabOnThemePage();
            if (panel) {
                populateSettingsIntoDOM(panel, activeSettings);
                bindThemePanelEvents(panel);
            }
        }

        if (isBuilderPage) {
            bindBuilderElementsToCssVars();

            // ZAPPOPPOP_THEME_COALESCED_RESTYLE: one debounced pass instead of full-DOM sweeps on every event
            var restyleTimer = null;
            var lastClickAt = 0;
            var fieldSyncQueued = false;

            function interactionBusy() {
                return !!document.querySelector('.ui-draggable-dragging, .ui-resizable-resizing, .ui-sortable-helper');
            }

            function scheduleRestyle(delay) {
                if (restyleTimer) return;
                restyleTimer = setTimeout(function() {
                    restyleTimer = null;
                    // Never compete with a drawer slide-in or an active drag / resize
                    if (interactionBusy() || (Date.now() - lastClickAt) < 350) {
                        scheduleRestyle(150);
                        return;
                    }
                    var run = function() {
                        styleAllCanvasSwitcherButtonsAndAddButtons();
                        bindBuilderElementsToCssVars();
                    };
                    if (typeof window.requestIdleCallback === 'function') {
                        window.requestIdleCallback(run, { timeout: 500 });
                    } else {
                        run();
                    }
                }, delay || 120);
            }

            if (typeof MutationObserver !== 'undefined') {
                var mo = new MutationObserver(function(mutations) {
                    if (isBindingBuilderDOM) return;
                    for (var i = 0; i < mutations.length; i++) {
                        var tg = mutations[i].target;
                        if (!tg || isInsideIsolatedCanvas(tg)) continue;
                        if (tg.closest && tg.closest('#zappoppop-canvas-box, .zappoppop-canvas-item')) continue;
                        scheduleRestyle(120);
                        break;
                    }
                });
                mo.observe(document.body, { childList: true, subtree: true });
            }

            // Header canvas pills keep a synchronous restyle (no black flash); everything else is deferred.
            ['mousedown', 'mouseup', 'click'].forEach(function(evtName) {
                document.addEventListener(evtName, function(e) {
                    var t = e.target;
                    if (evtName === 'click') lastClickAt = Date.now();
                    if (!t || !t.closest || isInsideIsolatedCanvas(t)) return;
                    var pillSel = '[data-zpp-canvas-name], [data-zpp-role="canvas-tab-pill"], [data-zpp-role="canvas-tab-pill-active"], [data-zpp-role="add-canvas-btn"], [data-zpp-role="canvas-pill-bar"], [data-zpp-role="header"]';
                    if (t.closest(pillSel)) {
                        var named = t.closest('[data-zpp-canvas-name], [data-zpp-role="canvas-tab-pill"], [data-zpp-role="canvas-tab-pill-active"]') || t;
                        var cName = named.getAttribute('data-zpp-canvas-name') || extractCanvasNameFromPill(named);
                        if (cName && cName !== '+' && (named.closest('[data-zpp-role="canvas-pill-bar"]') || named.closest('[data-zpp-role="header"]'))) {
                            activeCanvasIdentifier = cName.toLowerCase();
                        }
                        styleAllCanvasSwitcherButtonsAndAddButtons();
                        scheduleRestyle(60);
                    } else if (evtName === 'click' && t.closest('#zappoppop-settings-drawer')) {
                        scheduleRestyle(400);
                    }
                }, true);
            });

            // Live sync of Width / Height / Radius / Canvas Color (Canvas Settings lives in Popup Settings only)
            var queueFieldSync = function(e) {
                var t = e.target;
                if (!t || !t.closest || isInsideIsolatedCanvas(t)) return;
                if (!t.closest('#zappoppop-settings-drawer')) return;
                if (fieldSyncQueued) return;
                fieldSyncQueued = true;
                window.requestAnimationFrame(function() {
                    fieldSyncQueued = false;
                    syncCanvasWithCanvasSettings(locateAndIsolateCanvasElement());
                    styleAllCanvasSwitcherButtonsAndAddButtons();
                });
            };
            document.addEventListener('input', queueFieldSync, true);
            document.addEventListener('change', queueFieldSync, true);

            window.addEventListener('storage', function(e) {
                if (e.key === STORAGE_KEY && e.newValue) {
                    try {
                        var updated = JSON.parse(e.newValue);
                        DATA.settings = updated;
                        applyCssVariablesToRoot(updated, false);
                    } catch (err) {}
                }
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initBuilderThemeEngine);
    } else {
        initBuilderThemeEngine();
    }
})();

/* ZPP_RIBBON_SKIP2: the Elements Ribbon Bar owns its colours - release anything the generic scanners tagged inside it */
(function () {
    'use strict';
    function release() {
        var wrap = document.getElementById('zappoppop-ribbon-wrap');
        if (!wrap) { return; }
        var list = wrap.querySelectorAll('[data-zpp-role="content-panel"]');
        for (var i = 0; i < list.length; i++) {
            var el = list[i];
            el.removeAttribute('data-zpp-role');
            ['background-image', 'background-color', 'border-color', 'color'].forEach(function (p) { el.style.removeProperty(p); });
        }
    }
    function run() { release(); window.setTimeout(release, 600); window.setTimeout(release, 1800); }
    if (document.readyState === 'complete') { run(); } else { window.addEventListener('load', run); }
})();
