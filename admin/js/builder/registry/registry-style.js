/**
 * ZapPopPop Builder Registry Module: registry-style
 * Split from builder-elements-registry.js (load order matters; see class-admin-assets.php)
 */
/* === ZAPPOPPOP_PHASE1_CORE_TAB_VERIFICATION_START === */
(function(window, $) {
    'use strict';

    window.ZapPopPopElementsRegistry = window.ZapPopPopElementsRegistry || {};
    var Reg = window.ZapPopPopElementsRegistry;

    Reg.getStageNode = function(el) {
        if (!el || !el.id) return $();
        var cleanId = String(el.id).replace(/^zappoppop-el-/, '').replace(/^el-/, '');
        return $('.zappoppop-canvas-item').filter(function() {
            var dId = $(this).attr('data-id') || $(this).data('id') || $(this).attr('id') || '';
            dId = String(dId).replace(/^zappoppop-el-/, '').replace(/^el-/, '');
            return dId === cleanId;
        });
    };

    Reg.applyStyleToStageNode = function(el) {
        if (!el) return;
        var $node = Reg.getStageNode(el);
        if (!$node.length) return;

        // 1. Geometry (X, Y, W, H)
        if (el.x !== undefined) $node.css('left', parseInt(el.x, 10) + 'px');
        if (el.y !== undefined) $node.css('top', parseInt(el.y, 10) + 'px');
        if (el.width !== undefined) $node.css('width', parseInt(el.width, 10) + 'px');
        if (el.height !== undefined) $node.css('height', parseInt(el.height, 10) + 'px');

        // 2. Opacity (normalized 0..1 for CSS)
        var op = el.opacity !== undefined ? parseFloat(el.opacity) : 100;
        var cssOp = (op > 1) ? (op / 100) : op;
        $node.css('opacity', cssOp);

        // 3. Typography & Colors
        if (el.fontFamily) $node.css('font-family', el.fontFamily);
        if (el.fontSize) $node.css('font-size', parseInt(el.fontSize, 10) + 'px');
        if (el.fontWeight) $node.css('font-weight', el.fontWeight);
        if (el.lineHeight) $node.css('line-height', el.lineHeight);
        if (el.letterSpacing !== undefined) $node.css('letter-spacing', parseFloat(el.letterSpacing) + 'px');
        if (el.textAlign) $node.css('text-align', el.textAlign);
        if (el.color) $node.css('color', el.color);
        $node.css('text-shadow', Reg.textShadowCss ? Reg.textShadowCss(el) : ''); /* ZPP_TEXT_SHADOW */
        $node.attr('data-zpp-tt', (el.textTransform && el.textTransform !== 'none') ? el.textTransform : null); /* ZPP_TEXT_TRANSFORM */
        $node.css('word-spacing', (parseFloat(el.wordSpacing) || 0) ? (parseFloat(el.wordSpacing) + 'px') : ''); /* ZPP_WORD_SPACING */
        /* ZAPPOPPOP_FG_COLOR: foreground colour for icons & accents */
        if (/^#[0-9a-fA-F]{3,8}$/.test(el.fgColor || '')) { $node[0].style.setProperty('--zappoppop-fg', el.fgColor); $node.attr('data-zpp-fg', '1'); }
        else { $node[0].style.removeProperty('--zappoppop-fg'); $node.removeAttr('data-zpp-fg'); }
        if (el.bgColor && !el.transparentFill) {
            $node.css('background-color', el.bgColor);
        } else if (el.transparentFill) {
            $node.css('background-color', 'transparent');
        }

        // 4. 4-Way Padding & Borders
        var pk = function(a, b) { return (el[a] !== undefined && el[a] !== '' && el[a] !== null) ? el[a] : el[b]; };
        var pt = parseInt(pk('paddingTop', 'padTop'), 10) || 0;
        var pr = parseInt(pk('paddingRight', 'padRight'), 10) || 0;
        var pb = parseInt(pk('paddingBottom', 'padBottom'), 10) || 0;
        var pl = parseInt(pk('paddingLeft', 'padLeft'), 10) || 0;
        var padSet = ['paddingTop','paddingRight','paddingBottom','paddingLeft','padTop','padRight','padBottom','padLeft'].some(function(k) { return el[k] !== undefined && el[k] !== ''; });
        if (padSet) {
            $node.css('padding', pt + 'px ' + pr + 'px ' + pb + 'px ' + pl + 'px');
        }
        var bOff = (el.borderEnabled === false || el.borderEnabled === 'false' || el.borderEnabled === 0);
        var bw = bOff ? 0 : (parseInt(el.borderWidth, 10) || 0);
        var bs = bOff ? 'none' : (el.borderStyle || (bw > 0 ? 'solid' : 'none'));
        var bc = el.borderColor || '#cbd5e1';
        $node.css({
            'border-width': bw + 'px',
            'border-style': bs,
            'border-color': bc,
            'border-radius': (bOff ? 0 : (parseInt(el.borderRadius, 10) || 0)) + 'px'
        });
        /* ZAPPOPPOP_FORM_BORDER_FIX: inner box follows wrapper radius; drop its own border when wrapper has one */
        var fbTypes = ['text', 'textfield', 'email', 'number', 'phone', 'tel', 'textarea', 'select', 'dropdown', 'date', 'time', 'password', 'upload', 'file', 'multiselect'];
        if (fbTypes.indexOf(String(el.type || '').toLowerCase()) !== -1 && $node[0]) {
            var fbRad = bOff ? 0 : (parseInt(el.borderRadius, 10) || 0);
            $node[0].style.setProperty('--zappoppop-ibr', Math.max(0, fbRad - bw) + 'px');
            if (bw > 0 && bs !== 'none') { $node[0].style.setProperty('--zappoppop-wb', bw + 'px'); }
            else { $node[0].style.removeProperty('--zappoppop-wb'); }
            $node.children('div').first().attr('data-zpp-ibox', '1');
        }

        // 5. 5-Parameter Box Shadow
        var sx = parseInt(el.shadowX, 10) || 0;
        var sy = parseInt(el.shadowY, 10) || 0;
        var sBlur = parseInt(el.shadowBlur, 10) || 0;
        var sSpread = parseInt(el.shadowSpread, 10) || 0;
        var sCol = el.shadowColor || 'rgba(0,0,0,0.15)';
        var sOff = (el.shadowEnabled === false || el.shadowEnabled === 'false' || el.shadowEnabled === 0);
        if (!sOff && (sx || sy || sBlur || sSpread)) {
            $node.css('box-shadow', sx + 'px ' + sy + 'px ' + sBlur + 'px ' + sSpread + 'px ' + sCol);
        } else {
            $node.css('box-shadow', 'none');
        }

        // 6. Inline CSS Styles Repeater
        if (Array.isArray(el.inlineStyles)) {
            el.inlineStyles.forEach(function(pair) {
                if (pair && pair.prop && pair.value) {
                    $node.css(pair.prop, pair.value);
                }
            });
        }
    };

    Reg.hydrateCoreAndStyleFields = function(el) {
        if (!el) return;
        var rawType = String(el.type || 'label').toLowerCase().trim();
        var canonical = (Reg.ALIAS_MAP && Reg.ALIAS_MAP[rawType]) ? Reg.ALIAS_MAP[rawType] : rawType;
        var isForm = Reg.FORM_FAMILY && Reg.FORM_FAMILY.indexOf(canonical) !== -1;

        // Hydrate Layer Name & Geometry (X, Y, W, H)
        $('#prop-layer-name, #prop-name').val(el.name || canonical.toUpperCase());
        $('#prop-x, #prop-pos-left').val(parseInt(el.x !== undefined ? el.x : el.left, 10) || 0);
        $('#prop-y, #prop-pos-top').val(parseInt(el.y !== undefined ? el.y : el.top, 10) || 0);
        $('#prop-w, #prop-size-width').val(parseInt(el.width !== undefined ? el.width : el.w, 10) || 160);
        $('#prop-h, #prop-size-height').val(parseInt(el.height !== undefined ? el.height : el.h, 10) || 44);

        // Toggle Media Style Suite vs Form Style Suite
        $('#insp-section-media-style-suite').toggle(!isForm);
        $('#insp-section-form-input-style').show(); // Custom CSS Class & CSS Styles repeater available for all elements
        $('#gp-form-global-style-notice, #gp-form-text-align-row').toggle(isForm);
        var hasSideIcons = isForm && ['text', 'email', 'number', 'tel', 'date', 'time', 'password', 'upload', 'file'].indexOf(canonical) !== -1;
        $('#gp-form-input-icons-row').toggle(hasSideIcons);
        $('#zappoppop-form-icons-card').toggle(canonical !== 'textarea' && canonical !== 'select'); /* ZAPPOPPOP_TEXTAREA_STYLE */
        /* ZAPPOPPOP_TF_STYLE: Text Field hides Opacity, Background, Typography, Custom CSS */
        var tfHide = '#insp-section-opacity, #insp-section-bg-color, #insp-section-typography, #insp-section-padding, #insp-section-border, #insp-section-shadow, #insp-section-custom-css, ' +
            '#prop-form-custom-class, #prop-gp-style-css-class, #zappoppop-btn-add-inline-style, #zappoppop-gp-inline-styles-list';
        var $tfNodes = $(tfHide).map(function() {
            var $c = $(this).closest('.zappoppop-insp-card, .zappoppop-insp-section');
            return ($c.length && !$c.is('#zappoppop-form-icons-card')) ? $c[0] : this;
        });
        if (canonical === 'text' || canonical === 'textfield' || canonical === 'email' || canonical === 'number' || canonical === 'tel' || canonical === 'textarea' || canonical === 'select') {
            $tfNodes.attr('data-tf-hidden', '1').hide();
        } else {
            $tfNodes.filter('[data-tf-hidden]').removeAttr('data-tf-hidden').show();
        }

        if (!isForm) {
            var textMedia = ['label', 'html', 'button', 'submit', 'progress'];
            var hoverMedia = ['button', 'submit'];
            $('#insp-section-typography').toggle(textMedia.indexOf(canonical) !== -1);
            $('#zappoppop-hover-state-card').toggle(hoverMedia.indexOf(canonical) !== -1);
        }

        $('#insp-tab-style').toggleClass('zappoppop-shape-style', canonical === 'shape'); /* ZAPPOPPOP_SHAPE_STYLE */
        /* ZAPPOPPOP_DATE_STYLE: keep Date's hidden sections hidden through this secondary hydration pass */
        $('#insp-tab-style').toggleClass('zappoppop-date-style', canonical === 'date' || canonical === 'time' || rawType === 'time'); /* ZAPPOPPOP_TIME_STYLE */

        // Hydrate Media Style fields
        var opVal = el.opacity !== undefined ? parseFloat(el.opacity) : 100;
        if (opVal > 0 && opVal <= 1) opVal = Math.round(opVal * 100);
        $('#prop-opacity').val(opVal);
        $('#prop-opacity-val').text(opVal + '%');

        $('#prop-font-family').val(el.fontFamily || 'inherit');
        $('#prop-font-size').val(parseInt(el.fontSize, 10) || 16);
        $('#prop-font-weight').val(el.fontWeight || '400');
        if (window.zappoppopSyncFontWeights) { window.zappoppopSyncFontWeights(false); } /* ZPP_FONT_WEIGHTS */
        $('#prop-line-height').val(el.lineHeight !== undefined ? el.lineHeight : 1.4);
        $('#prop-letter-spacing').val(el.letterSpacing !== undefined ? el.letterSpacing : 0);
        $('#prop-text-align').val(el.textAlign || 'left');
        $('#prop-color').val(el.color || '#1e293b');
        $('#prop-bg-color').val(el.bgColor || '#ffffff');
        $('#prop-pad-top').val(parseInt(el.padTop, 10) || 0);
        $('#prop-pad-right').val(parseInt(el.padRight, 10) || 0);
        $('#prop-pad-bottom').val(parseInt(el.padBottom, 10) || 0);
        $('#prop-pad-left').val(parseInt(el.padLeft, 10) || 0);
        $('#prop-border-width').val(parseInt(el.borderWidth, 10) || 0);
        $('#prop-border-style').val(el.borderStyle || 'none');
        $('#prop-border-radius').val(parseInt(el.borderRadius, 10) || 0);
        $('#prop-border-color').val(el.borderColor || '#cbd5e1');
        $('#prop-shadow-x').val(parseInt(el.shadowX, 10) || 0);
        $('#prop-shadow-y').val(parseInt(el.shadowY, 10) || 0);
        $('#prop-shadow-blur').val(parseInt(el.shadowBlur, 10) || 0);
        $('#prop-shadow-spread').val(parseInt(el.shadowSpread, 10) || 0);
        $('#prop-shadow-color').val(el.shadowColor || 'rgba(0,0,0,0.15)');
        $('#prop-hover-bg').val(el.hoverBg || '#1d4ed8');
        $('#prop-hover-color').val(el.hoverColor || '#ffffff');
        $('#prop-hover-border').val(el.hoverBorder || '#1d4ed8');

        // Hydrate Data Tab dynamic param row visibility
        $('#insp-row-data-param').toggle(!!el.dynamicDefault);
    };

    function bindPhase1VerifiedEvents() {
        // Hook hydrateCoreAndStyleFields into syncInspectorForElement
        var origSync = Reg.syncInspectorForElement;
        if (typeof origSync === 'function' && !Reg._phase1SyncHooked) {
            Reg._phase1SyncHooked = true;
            Reg.syncInspectorForElement = function(idOrEl) {
                origSync.apply(this, arguments);
                var el = Reg.resolveElement(idOrEl);
                if (el) Reg.hydrateCoreAndStyleFields(el);
            };
        }

        // 1. Layer Name two-way sync with Floating Layers Panel
        $(document).off('input.p1LayerName change.p1LayerName', '#prop-layer-name, #prop-name')
                   .on('input.p1LayerName change.p1LayerName', '#prop-layer-name, #prop-name', function() {
            var el = Reg.resolveElement();
            if (!el) return;
            el.name = $(this).val();
            var cleanId = String(el.id).replace(/^zappoppop-el-/, '').replace(/^el-/, '');
            $('#layer-item-' + cleanId + ' .zappoppop-layer-title, .zappoppop-layer-item[data-id="' + cleanId + '"] .zappoppop-layer-title').text(el.name);
        });

        // 2. Coordinates & Bounds (X, Y, W, H) two-way sync with Stage Node
        $(document).off('input.p1Bounds change.p1Bounds', '#prop-x, #prop-pos-left, #prop-y, #prop-pos-top, #prop-w, #prop-size-width, #prop-h, #prop-size-height')
                   .on('input.p1Bounds change.p1Bounds', '#prop-x, #prop-pos-left, #prop-y, #prop-pos-top, #prop-w, #prop-size-width, #prop-h, #prop-size-height', function() {
            var el = Reg.resolveElement();
            if (!el) return;
            var id = this.id;
            var v = parseInt($(this).val(), 10) || 0;
            if (id === 'prop-x' || id === 'prop-pos-left') { el.x = v; el.left = v; }
            if (id === 'prop-y' || id === 'prop-pos-top') { el.y = v; el.top = v; }
            if (id === 'prop-w' || id === 'prop-size-width') { el.width = Math.max(16, v); el.w = el.width; }
            if (id === 'prop-h' || id === 'prop-size-height') { el.height = Math.max(16, v); el.h = el.height; }
            Reg.applyStyleToStageNode(el);
        });

        // 3. Animation Play Button on Content Tab
        $(document).off('click.p1PlayAnim', '#zappoppop-btn-play-elem-anim')
                   .on('click.p1PlayAnim', '#zappoppop-btn-play-elem-anim', function(e) {
            return; /* handled by ZapPopPopBuilderAnimation */
            e.preventDefault();
            var el = Reg.resolveElement();
            if (!el) return;
            var anim = $('#prop-anim-in').val() || el.animIn || 'fadeIn';
            var dur  = parseInt($('#prop-anim-duration').val(), 10) || 1000;
            var $node = Reg.getStageNode(el);
            if ($node.length) {
                $node.css({ 'animation': 'none', '-webkit-animation': 'none' });
                void $node[0].offsetWidth;
                $node.css({
                    'animation': anim + ' ' + dur + 'ms both',
                    '-webkit-animation': anim + ' ' + dur + 'ms both'
                });
            }
        });

        // 4. Media Style Suite two-way binding
        var styleSelectors = [
            '#prop-opacity', '#prop-font-family', '#prop-font-size', '#prop-font-weight',
            '#prop-line-height', '#prop-letter-spacing', '#prop-text-align',
            '#prop-color', '#prop-bg-color', '#prop-pad-top', '#prop-pad-right',
            '#prop-pad-bottom', '#prop-pad-left', '#prop-border-width', '#prop-border-style',
            '#prop-border-radius', '#prop-border-color', '#prop-shadow-x', '#prop-shadow-y',
            '#prop-shadow-blur', '#prop-shadow-spread', '#prop-shadow-color',
            '#prop-hover-bg', '#prop-hover-color', '#prop-hover-border'
        ].join(', ');

        $(document).off('input.p1MediaStyle change.p1MediaStyle', styleSelectors)
                   .on('input.p1MediaStyle change.p1MediaStyle', styleSelectors, function() {
            var el = Reg.resolveElement();
            if (!el) return;
            var tid = this.id;
            var MAP = {
                'prop-font-family': ['fontFamily', 'str'], 'prop-font-size': ['fontSize', 'int'],
                'prop-font-weight': ['fontWeight', 'str'], 'prop-line-height': ['lineHeight', 'float'],
                'prop-letter-spacing': ['letterSpacing', 'float'], 'prop-text-align': ['textAlign', 'str'],
                'prop-color': ['color', 'str'],
                'prop-border-width': ['borderWidth', 'int'], 'prop-border-style': ['borderStyle', 'str'],
                'prop-border-color': ['borderColor', 'str'], 'prop-border-radius': ['borderRadius', 'int'],
                'prop-shadow-x': ['shadowX', 'int'], 'prop-shadow-y': ['shadowY', 'int'],
                'prop-shadow-blur': ['shadowBlur', 'int'], 'prop-shadow-spread': ['shadowSpread', 'int'],
                'prop-shadow-color': ['shadowColor', 'str'],
                'prop-hover-bg': ['hoverBg', 'str'], 'prop-hover-color': ['hoverColor', 'str'], 'prop-hover-border': ['hoverBorder', 'str']
            };
            if (tid === 'prop-opacity') {
                var op = parseInt($('#prop-opacity').val(), 10);
                if (!isNaN(op)) { el.opacity = op; $('#prop-opacity-val').text(op + '%'); }
            } else if (tid === 'prop-bg-color') {
                el.bgColor = $('#prop-bg-color').val();
                el.transparentFill = false;
                $('#prop-bg-transparent').prop('checked', false);
            } else if (tid === 'prop-bg-transparent') {
                el.transparentFill = $('#prop-bg-transparent').is(':checked');
                if (!el.transparentFill && (!el.bgColor || el.bgColor === 'transparent')) { el.bgColor = $('#prop-bg-color').val(); }
            } else if (/^prop-pad-(top|right|bottom|left)$/.test(tid)) {
                var side = tid.replace('prop-pad-', '');
                var Side = side.charAt(0).toUpperCase() + side.slice(1);
                var pv = Math.max(0, parseInt($(this).val(), 10) || 0);
                el['padding' + Side] = pv; el['pad' + Side] = pv; el['padding_' + side] = pv;
            } else if (MAP[tid]) {
                var spec = MAP[tid], raw = $(this).val(), out = raw;
                if (spec[1] === 'int') { out = parseInt(raw, 10); if (isNaN(out)) out = 0; }
                else if (spec[1] === 'float') { out = parseFloat(raw); if (isNaN(out)) out = 0; }
                el[spec[0]] = out;
            }

            Reg.applyStyleToStageNode(el);
        });

        // 5. Data Tab Dynamic URL Parameter Toggle
        $(document).off('change.p1DataDyn', '#prop-data-dynamic')
                   .on('change.p1DataDyn', '#prop-data-dynamic', function() {
            var isDyn = $(this).is(':checked');
            $('#insp-row-data-param').toggle(isDyn);
        });

        // 6. Inline CSS Styles & Logic Rule Repeater live input sync
        $(document).off('input.p1Repeaters change.p1Repeaters', '#zappoppop-gp-inline-styles-list input, #zappoppop-gp-logic-rules-list input, #zappoppop-gp-logic-rules-list select')
                   .on('input.p1Repeaters change.p1Repeaters', '#zappoppop-gp-inline-styles-list input, #zappoppop-gp-logic-rules-list input, #zappoppop-gp-logic-rules-list select', function() {
            var el = Reg.resolveElement();
            if (!el) return;
            var stylesArr = [];
            $('#zappoppop-gp-inline-styles-list .zappoppop-gp-repeater-item').each(function() {
                var p = $.trim($(this).find('.gp-css-prop').val() || '');
                var v = $.trim($(this).find('.gp-css-val').val() || '');
                if (p || v) stylesArr.push({ prop: p, value: v });
            });
            el.inlineStyles = stylesArr;

            var rulesArr = [];
            $('#zappoppop-gp-logic-rules-list .zappoppop-gp-repeater-item').each(function() {
                var f  = $.trim($(this).find('.gp-rule-field').val() || '');
                var op = $(this).find('.gp-rule-op').val() || 'is';
                var v  = $.trim($(this).find('.gp-rule-val').val() || '');
                rulesArr.push({ field: f, operator: op, value: v });
            });
            el.logicRules = rulesArr;
            Reg.applyStyleToStageNode(el);
        });
    }

    $(function() {
        bindPhase1VerifiedEvents();
        setTimeout(bindPhase1VerifiedEvents, 120);
    });
    bindPhase1VerifiedEvents();
})(window, jQuery);
/* === ZAPPOPPOP_PHASE1_CORE_TAB_VERIFICATION_END === */

/* === ZAPPOPPOP_FG_COLOR_BIND_START === */
(function(window, $) {
    'use strict';
    var Reg = window.ZapPopPopElementsRegistry = window.ZapPopPopElementsRegistry || {};
    if (!document.getElementById('zappoppop-fg-color-css')) {
        var st = document.createElement('style');
        st.id = 'zappoppop-fg-color-css';
        st.appendChild(document.createTextNode(
            '.zappoppop-canvas-item[data-zpp-fg] .dashicons:not([style*="color"]),' +
            '.zappoppop-canvas-item[data-zpp-fg] i:not([style*="color"]){color:var(--zappoppop-fg);}' +
            '.zappoppop-canvas-item[data-zpp-fg] input[type="checkbox"],.zappoppop-canvas-item[data-zpp-fg] input[type="radio"]{accent-color:var(--zappoppop-fg);}'
        ));
        (document.head || document.documentElement).appendChild(st);
    }
    var ok = function(c) { return /^#[0-9a-fA-F]{3,8}$/.test(c || ''); };
    function rerender(el) {
        var raw = String(el.type || '').toLowerCase();
        var can = (Reg.ALIAS_MAP && Reg.ALIAS_MAP[raw]) ? Reg.ALIAS_MAP[raw] : raw;
        var isForm = Reg.FORM_FAMILY && Reg.FORM_FAMILY.indexOf(can) !== -1;
        if (isForm && typeof Reg.renderFormElementStage === 'function') { Reg.renderFormElementStage(el); }
        else if (typeof Reg.renderMediaElementStage === 'function') { Reg.renderMediaElementStage(el); }
        else if (typeof Reg.applyStyleToStageNode === 'function') { Reg.applyStyleToStageNode(el); }
    }
    $(document).off('input.zppFg change.zppFg', '#prop-fg-color').on('input.zppFg change.zppFg', '#prop-fg-color', function(e) {
        var el = (typeof Reg.resolveElement === 'function') ? Reg.resolveElement() : null;
        if (!el) return;
        el.fgColor = $(this).val();
        rerender(el);
        if (e.type === 'change' && window.ZapPopPopBuilderCore && typeof window.ZapPopPopBuilderCore.pushHistory === 'function') { window.ZapPopPopBuilderCore.pushHistory(); }
    });
    $(document).off('click.zppFgReset', '#prop-fg-color-reset').on('click.zppFgReset', '#prop-fg-color-reset', function(e) {
        e.preventDefault();
        var el = (typeof Reg.resolveElement === 'function') ? Reg.resolveElement() : null;
        if (!el) return;
        el.fgColor = '';
        $('#prop-fg-color').val('#64748b');
        rerender(el);
        if (window.ZapPopPopBuilderCore && typeof window.ZapPopPopBuilderCore.pushHistory === 'function') { window.ZapPopPopBuilderCore.pushHistory(); }
    });
    function hydrate(el) {
        if (!el) el = (typeof Reg.resolveElement === 'function') ? Reg.resolveElement() : null;
        if (el) { $('#prop-fg-color').val(ok(el.fgColor) ? el.fgColor : '#64748b'); }
    }
    if (typeof Reg.hydrateCoreAndStyleFields === 'function' && !Reg._fgHooked) {
        Reg._fgHooked = true;
        var orig = Reg.hydrateCoreAndStyleFields;
        Reg.hydrateCoreAndStyleFields = function(el) { var r = orig.apply(this, arguments); hydrate(el); return r; };
    }
    $(document).off('click.zppFgHydrate', '.zappoppop-canvas-item, .zappoppop-layer-item, #zappoppop-inspector-drawer [data-tab]')
        .on('click.zppFgHydrate', '.zappoppop-canvas-item, .zappoppop-layer-item, #zappoppop-inspector-drawer [data-tab]', function() {
            [60, 250, 600].forEach(function(ms) { setTimeout(function() { hydrate(); }, ms); });
        });
})(window, jQuery);
/* === ZAPPOPPOP_FG_COLOR_BIND_END === */

/* ZPP_FONT_WEIGHTS: Font Weight options follow the selected bundled family */
(function ($) {
    'use strict';
    if (!$) { return; }
    function fire(node, type) {
        var e;
        if (typeof Event === 'function') { e = new Event(type, { bubbles: true }); } else { e = document.createEvent('Event'); e.initEvent(type, true, true); }
        node.dispatchEvent(e);
    }
    window.zappoppopSyncFontWeights = function (commit) {
        var fam = document.getElementById('prop-font-family');
        var sel = document.getElementById('prop-font-weight');
        if (!fam || !sel) { return; }
        var opt = fam.options[fam.selectedIndex];
        var list = opt ? String(opt.getAttribute('data-weights') || '') : '';
        var ok = list ? list.split(',').map(function (n) { return parseInt(n, 10); }) : null;
        var i, o, w, best = null, cur = parseInt(sel.value, 10) || 400;
        for (i = 0; i < sel.options.length; i++) {
            o = sel.options[i];
            w = parseInt(o.value, 10);
            o.disabled = !!(ok && ok.indexOf(w) === -1);
        }
        if (ok && ok.indexOf(cur) === -1) {
            for (i = 0; i < ok.length; i++) { if (best === null || Math.abs(ok[i] - cur) < Math.abs(best - cur)) { best = ok[i]; } }
            if (best !== null) {
                sel.value = String(best);
                if (commit) { fire(sel, 'input'); fire(sel, 'change'); }
            }
        }
    };
    $(document).on('change', '#prop-font-family', function () { window.zappoppopSyncFontWeights(true); });
}(window.jQuery));

/* === ZPP_TEXT_SHADOW_BIND_START === */
/* Layer Settings > Style > Typography: Text Shadow (X / Y / blur / colour). Model keys textShadowX, textShadowY, textShadowBlur, textShadowColor. */
(function (window, $) {
    'use strict';
    if (!$) { return; }
    var Reg = window.ZapPopPopElementsRegistry = window.ZapPopPopElementsRegistry || {};
    var DEF_COLOR = '#00000080';
    function pos(v) { return v === 'inset' ? 'inset' : 'outset'; }
    function num(v, lo, hi) { var n = parseFloat(v); if (isNaN(n)) { n = 0; } return Math.max(lo, Math.min(hi, n)); }
    function okColor(c) { return /^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{4}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/.test(c || ''); }
    Reg.textShadowCss = function (el) {
        if (!el) { return ''; }
        var x = num(el.textShadowX, -50, 50), y = num(el.textShadowY, -50, 50), b = num(el.textShadowBlur, 0, 100);
        if (!x && !y && !b) { return ''; }
        if (el.textShadowPos === 'inset') { x = -x; y = -y; }
        return x + 'px ' + y + 'px ' + b + 'px ' + (okColor(el.textShadowColor) ? el.textShadowColor : DEF_COLOR);
    };
    function resolve() { return (typeof Reg.resolveElement === 'function') ? Reg.resolveElement() : null; }
    function paint(el) {
        if (typeof Reg.applyStyleToStageNode === 'function') { Reg.applyStyleToStageNode(el); }
        var cv = window.ZapPopPopBuilderCanvas;
        if (cv && typeof cv.renderCanvas === 'function' && !Reg.getStageNode) { cv.renderCanvas(); }
    }
    $(document).off('input.zppTsh change.zppTsh', '#prop-tsh-x, #prop-tsh-y, #prop-tsh-blur, #prop-tsh-color, #prop-tsh-pos')
        .on('input.zppTsh change.zppTsh', '#prop-tsh-x, #prop-tsh-y, #prop-tsh-blur, #prop-tsh-color, #prop-tsh-pos', function (e) {
            var el = resolve();
            if (!el) { return; }
            el.textShadowX = num($('#prop-tsh-x').val(), -50, 50);
            el.textShadowY = num($('#prop-tsh-y').val(), -50, 50);
            el.textShadowBlur = num($('#prop-tsh-blur').val(), 0, 100);
            el.textShadowPos = pos($('#prop-tsh-pos').val());
            var c = $('#prop-tsh-color').val();
            el.textShadowColor = okColor(c) ? c : DEF_COLOR;
            paint(el);
            if (e.type === 'change' && window.ZapPopPopBuilderCore && typeof window.ZapPopPopBuilderCore.pushHistory === 'function') { window.ZapPopPopBuilderCore.pushHistory(); }
        });
    function hydrate(el) {
        if (!el) { el = resolve(); }
        if (!el) { return; }
        $('#prop-tsh-x').val(num(el.textShadowX, -50, 50));
        $('#prop-tsh-y').val(num(el.textShadowY, -50, 50));
        $('#prop-tsh-blur').val(num(el.textShadowBlur, 0, 100));
        $('#prop-tsh-color').val(okColor(el.textShadowColor) ? el.textShadowColor : DEF_COLOR);
        $('#prop-tsh-pos').val(pos(el.textShadowPos));
    }
    if (typeof Reg.hydrateCoreAndStyleFields === 'function' && !Reg._tshHooked) {
        Reg._tshHooked = true;
        var orig = Reg.hydrateCoreAndStyleFields;
        Reg.hydrateCoreAndStyleFields = function (el) { var r = orig.apply(this, arguments); hydrate(el); return r; };
    }
    (Reg._zppActiveHydrators = Reg._zppActiveHydrators || []).push(hydrate);
    $(document).off('click.zppTshHydrate', '.zappoppop-canvas-item, .zappoppop-layer-item, #zappoppop-inspector-drawer [data-tab]')
        .on('click.zppTshHydrate', '.zappoppop-canvas-item, .zappoppop-layer-item, #zappoppop-inspector-drawer [data-tab]', function () {
            [60, 250, 600].forEach(function (ms) { setTimeout(function () { hydrate(); }, ms); });
        });
})(window, window.jQuery);
/* === ZPP_TEXT_SHADOW_BIND_END === */

/* === ZPP_WORD_SPACING_BIND_START === */
/* Layer Settings > Style > Typography: Word Spacing. Model key wordSpacing (px, -10..50). */
(function (window, $) {
    'use strict';
    if (!$) { return; }
    var Reg = window.ZapPopPopElementsRegistry = window.ZapPopPopElementsRegistry || {};
    function num(v) { var n = parseFloat(v); if (isNaN(n)) { n = 0; } return Math.max(-10, Math.min(50, n)); }
    function resolve() { return (typeof Reg.resolveElement === 'function') ? Reg.resolveElement() : null; }
    $(document).off('input.zppWsp change.zppWsp', '#prop-word-spacing')
        .on('input.zppWsp change.zppWsp', '#prop-word-spacing', function (e) {
            var el = resolve();
            if (!el) { return; }
            el.wordSpacing = num($(this).val());
            if (typeof Reg.applyStyleToStageNode === 'function') { Reg.applyStyleToStageNode(el); }
            if (e.type === 'change' && window.ZapPopPopBuilderCore && typeof window.ZapPopPopBuilderCore.pushHistory === 'function') { window.ZapPopPopBuilderCore.pushHistory(); }
        });
    function hydrate(el) {
        if (!el) { el = resolve(); }
        if (!el) { return; }
        $('#prop-word-spacing').val(num(el.wordSpacing));
    }
    if (typeof Reg.hydrateCoreAndStyleFields === 'function' && !Reg._wspHooked) {
        Reg._wspHooked = true;
        var orig = Reg.hydrateCoreAndStyleFields;
        Reg.hydrateCoreAndStyleFields = function (el) { var r = orig.apply(this, arguments); hydrate(el); return r; };
    }
    (Reg._zppActiveHydrators = Reg._zppActiveHydrators || []).push(hydrate);
    $(document).off('click.zppWspHydrate', '.zappoppop-canvas-item, .zappoppop-layer-item, #zappoppop-inspector-drawer [data-tab]')
        .on('click.zppWspHydrate', '.zappoppop-canvas-item, .zappoppop-layer-item, #zappoppop-inspector-drawer [data-tab]', function () {
            [60, 250, 600].forEach(function (ms) { setTimeout(function () { hydrate(); }, ms); });
        });
})(window, window.jQuery);
/* === ZPP_WORD_SPACING_BIND_END === */

/* === ZPP_ACTIVE_LAYER_WATCH_START === */
/* Layer rows stop click propagation, so document-level click hydration can miss a selection change: re-hydrate typography extras whenever the active layer id changes. */
(function (window) {
    'use strict';
    var Reg = window.ZapPopPopElementsRegistry = window.ZapPopPopElementsRegistry || {};
    if (Reg._zppActiveWatch) { return; }
    Reg._zppActiveWatch = true;
    var last = null;
    window.setInterval(function () {
        var core = window.ZapPopPopBuilderCore;
        var id = core && core.state ? core.state.activeId : null;
        if (id === last) { return; }
        last = id;
        [40, 200, 500].forEach(function (ms) {
            window.setTimeout(function () { (Reg._zppActiveHydrators || []).forEach(function (fn) { try { fn(); } catch (e) {} }); }, ms);
        });
    }, 150);
})(window);
/* === ZPP_ACTIVE_LAYER_WATCH_END === */

/* === ZPP_TEXT_TRANSFORM_BIND_START === */
/* Layer Settings > Style > Typography: Text Transform. Same model key as the Header content-tab control (textTransform). */
(function (window, $) {
    'use strict';
    if (!$) { return; }
    var Reg = window.ZapPopPopElementsRegistry = window.ZapPopPopElementsRegistry || {};
    var OK = { none: 1, uppercase: 1, lowercase: 1, capitalize: 1 };
    function val(v) { return OK[v] ? v : 'none'; }
    function resolve() { return (typeof Reg.resolveElement === 'function') ? Reg.resolveElement() : null; }
    $(document).off('change.zppTt', '#prop-text-transform').on('change.zppTt', '#prop-text-transform', function () {
        var el = resolve();
        if (!el) { return; }
        el.textTransform = val($(this).val());
        $('#prop-label-transform, #prop-title-transform').val(el.textTransform);
        if (typeof Reg.applyStyleToStageNode === 'function') { Reg.applyStyleToStageNode(el); }
        if (window.ZapPopPopBuilderCore && typeof window.ZapPopPopBuilderCore.pushHistory === 'function') { window.ZapPopPopBuilderCore.pushHistory(); }
    });
    function hydrate(el) {
        if (!el) { el = resolve(); }
        if (!el) { return; }
        $('#prop-text-transform').val(val(el.textTransform));
    }
    if (typeof Reg.hydrateCoreAndStyleFields === 'function' && !Reg._ttHooked) {
        Reg._ttHooked = true;
        var orig = Reg.hydrateCoreAndStyleFields;
        Reg.hydrateCoreAndStyleFields = function (el) { var r = orig.apply(this, arguments); hydrate(el); return r; };
    }
    (Reg._zppActiveHydrators = Reg._zppActiveHydrators || []).push(hydrate);
})(window, window.jQuery);
/* === ZPP_TEXT_TRANSFORM_BIND_END === */
