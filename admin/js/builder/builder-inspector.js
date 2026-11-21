
    function resolveActiveElement() {
        if (!window.ZapPopPopBuilderCore || !window.ZapPopPopBuilderCore.state) return null;
        var rawId = window.ZapPopPopBuilderCore.state.activeId;
        if (!rawId) return null;
        var cleanId = String(rawId).replace(/^el-/, '');
        var cur = window.ZapPopPopBuilderCore.state.currentCanvas || 1;
        var elements = (window.ZapPopPopBuilderCore.state.canvases && window.ZapPopPopBuilderCore.state.canvases[cur]) ? window.ZapPopPopBuilderCore.state.canvases[cur] : [];
        return elements.find(function(it) {
            return String(it.id).replace(/^el-/, '') === cleanId;
        });
    }

/**
 * ZapPopPop Visual Builder: Layer Settings Inspector Controller
 * Hydrates 26 Elements, Vector Shapes, Spacing/Padding, Typography & Swatches
 */
(function($) {
    'use strict';

    window.ZapPopPopBuilderInspector = {
/* --- Per-Layer Instance Settings Parity --- */
    getInstanceElement: function() {
        var core = window.ZapPopPopBuilderCore;
        if (!core || !core.state || !core.state.canvases) return null;
        var cur = core.state.currentCanvas || 1;
        var elements = core.state.canvases[cur] || [];
        var activeId = core.state.activeId;
        return elements.find(function(e) { return String(e.id) === String(activeId); });
    },

        bindAdvancedStylingInputs: function() {
        var self = this;

        // Opacity Live Slider
        $(document).off('input.zappoppop_op change.zappoppop_op', '#prop-opacity').on('input.zappoppop_op change.zappoppop_op', '#prop-opacity', function(e) {
            var val = parseInt($(this).val(), 10);
            $('#prop-opacity-val').text(val + '%');
            var el = (typeof self.getActiveEl === 'function' ? self.getActiveEl() : (typeof self.getActiveElement === 'function' ? self.getActiveElement() : null));
            if (el) {
                el.opacity = val;
                $('#zappoppop-el-' + el.id).css('opacity', val / 100);
                if (e.type === 'change' && window.ZapPopPopBuilderCore && typeof window.ZapPopPopBuilderCore.pushHistory === 'function') {
                    window.ZapPopPopBuilderCore.pushHistory();
                }
            }
        });

        // Custom CSS Class & ID
        $(document).off('input.zappoppop_css_id change.zappoppop_css_id', '#prop-custom-class, #prop-custom-id').on('input.zappoppop_css_id change.zappoppop_css_id', '#prop-custom-class, #prop-custom-id', function() {
            var el = (typeof self.getActiveEl === 'function' ? self.getActiveEl() : (typeof self.getActiveElement === 'function' ? self.getActiveElement() : null));
            if (el) {
                el.customClass = $('#prop-custom-class').val().trim();
                el.customId = $('#prop-custom-id').val().trim();
            }
        });

        // Button Hover State Colors
        $(document).off('input.zappoppop_btn_hvr change.zappoppop_btn_hvr', '#prop-btn-hover-bg, #prop-btn-hover-color, #prop-btn-hover-border').on('input.zappoppop_btn_hvr change.zappoppop_btn_hvr', '#prop-btn-hover-bg, #prop-btn-hover-color, #prop-btn-hover-border', function(e) {
            var el = (typeof self.getActiveEl === 'function' ? self.getActiveEl() : (typeof self.getActiveElement === 'function' ? self.getActiveElement() : null));
            if (el) {
                el.hoverBgColor = $('#prop-btn-hover-bg').val();
                el.hoverTextColor = $('#prop-btn-hover-color').val();
                el.hoverBorderColor = $('#prop-btn-hover-border').val();
                var $node = $('#zappoppop-el-' + el.id);
                $node.css({
                    '--zappoppop-hover-bg': el.hoverBgColor,
                    '--zappoppop-hover-color': el.hoverTextColor,
                    '--zappoppop-hover-border': el.hoverBorderColor
                });
                if (e.type === 'change' && window.ZapPopPopBuilderCore && typeof window.ZapPopPopBuilderCore.pushHistory === 'function') {
                    window.ZapPopPopBuilderCore.pushHistory();
                }
            }
        });
    },

    bindAnimationPlayControls: function() {
        var self = this;
        $(document).off('click.zappoppop_anim_play').on('click.zappoppop_anim_play', '#zappoppop-btn-play-elem-anim, #prop-anim-play-btn, #prop-anim-replay-btn, #prop-anim-test-btn, .zappoppop-anim-play-btn', function(e) {
            e.preventDefault();
            var core = window.ZapPopPopBuilderCore;
            var activeId = (core && core.state) ? core.state.activeId : null;
            if (!activeId && window.ZapPopPopBuilderCanvas) {
                activeId = window.ZapPopPopBuilderCanvas.activeId;
            }
            if (!activeId) {
                var $sel = $('.zappoppop-canvas-item.zappoppop-selected, .zappoppop-selected');
                if ($sel.length) {
                    activeId = $sel.data('id') || $sel.attr('data-id') || ($sel.attr('id') || '').replace(/^(zappoppop-el-|el-)/, '');
                }
            }
            if (!activeId) return;

            var activeEl = null;
            if (core && core.state && core.state.canvases) {
                var cur = core.state.currentCanvas || 1;
                var list = core.state.canvases[cur] || [];
                activeEl = list.find(function(el) { return el.id === activeId; });
            }

            var effect = $('#prop-anim-in').val() || $('#prop-anim-appearance').val() || $('#prop-anim-effect').val();
            if (!effect || effect === 'none') {
                if (activeEl) {
                    effect = activeEl.anim_appearance || activeEl.animEffect || activeEl.animation || activeEl.anim_effect;
                }
            }
            if (!effect || effect === 'none') {
                return;
            }

            var duration = parseInt($('#prop-anim-duration').val(), 10) || 1000;
            var delay = parseInt($('#prop-anim-delay').val(), 10) || 0;

            if (window.ZapPopPopBuilderCanvas && typeof window.ZapPopPopBuilderCanvas.playAnimation === 'function') {
                window.ZapPopPopBuilderCanvas.playAnimation(activeId, effect, duration, delay);
            }
        });

        $(document).off('change.zappoppop_anim_sel').on('change.zappoppop_anim_sel', '#prop-anim-in, #prop-anim-appearance, #prop-anim-effect', function() {
            var val = $(this).val();
            if (val && val !== 'none') {
                var core = window.ZapPopPopBuilderCore;
                var activeId = (core && core.state) ? core.state.activeId : null;
                if (activeId && window.ZapPopPopBuilderCanvas && typeof window.ZapPopPopBuilderCanvas.playAnimation === 'function') {
                    window.ZapPopPopBuilderCanvas.playAnimation(activeId, val);
                }
            }
        });
    },

    bindInspectorCloseEvents: function() {
        var self = this;
        $(document).off('click.zappoppop_insp_close').on('click.zappoppop_insp_close', '#zappoppop-inspector-close, .zappoppop-inspector-close, .zappoppop-drawer-close, #insp-close-btn', function(e) {
            e.preventDefault();
            self.close();
        });
        $(document).off('click.zappoppop_ws_deselect').on('click.zappoppop_ws_deselect', '#zappoppop-canvas-workspace, .zappoppop-builder-workspace', function(e) {
            if ($(e.target).is('#zappoppop-canvas-workspace, .zappoppop-builder-workspace, #zappoppop-canvas-scroll-container')) {
                self.close();
            }
        });
    },

    bindLayerNameInputEvents: function() {
        var self = this;
        $(document).off('input.zappoppop_lname change.zappoppop_lname', '#prop-layer-name, #prop-element-name')
                   .on('input.zappoppop_lname change.zappoppop_lname', '#prop-layer-name, #prop-element-name', function(e) {
            var newName = $(this).val();
            $('#prop-layer-name, #prop-element-name').not(this).val(newName);

            var core = window.ZapPopPopBuilderCore;
            var aId = self.activeId || (core && core.state ? core.state.activeId : null);
            if (!aId && window.ZapPopPopBuilderCanvas) aId = window.ZapPopPopBuilderCanvas.activeId;
            if (!aId) return;

            var cleanId = String(aId).replace(/^(zappoppop-el-|el-)/, '');
            var cur = (core && core.state) ? (core.state.currentCanvas || 1) : 1;
            var list = (core && core.state && core.state.canvases) ? (core.state.canvases[cur] || []) : [];

            var targetEl = null;
            for (var i = 0; i < list.length; i++) {
                if (String(list[i].id).replace(/^(zappoppop-el-|el-)/, '') === cleanId) {
                    targetEl = list[i];
                    break;
                }
            }

            if (targetEl) {
                targetEl.name = newName;
                targetEl.label = newName;

                // Live-update floating layers panel list item title
                var $panel = $('#zappoppop-floating-layers-panel, #zappoppop-layers-list');
                var $item = $panel.find('[data-id="' + cleanId + '"], [data-element-id="' + cleanId + '"], [data-id="' + aId + '"]');
                if ($item.length) {
                    $item.find('.zappoppop-layer-title').text(newName || targetEl.type);
                }
            }

            if (e.type === 'change' && core && typeof core.pushHistory === 'function') {
                core.pushHistory();
            }
        });
    },

    
        normalizeType: function(type) {
            if (!type) return 'text';
            var t = String(type).toLowerCase();
            var map = {
                'paragraph': 'text',
                'select': 'dropdown',
                'dropdown': 'dropdown',
                'radio': 'radios',
                'radios': 'radios',
                'checkbox': 'checkboxes',
                'checkboxes': 'checkboxes',
                'step': 'step_btn',
                'step_btn': 'step_btn',
                'link': 'link_btn',
                'link_btn': 'link_btn',
                'close_icon': 'close',
                'close': 'close',
                'lucky_wheel': 'wheel',
                'wheel': 'wheel',
                'scratch_card': 'scratch',
                'scratch': 'scratch',
                'custom_html': 'html',
                'html': 'html'
            };
            return map[t] || t;
        },
    
        
    // === LAYER SETTINGS SANITIZER & CONTEXTUAL SCOPER ===
    
    // === PHASE 4: PRE-SAVE DOM FLUSH FOR SANITIZED 5-TAB LAYER SETTINGS ===
    flushActiveElement: function() { if (typeof this.flushSanitizedLayerSettings === 'function') { this.flushSanitizedLayerSettings(); } },
    flushSanitizedLayerSettings: function() {
        var el = null;
        if (typeof this.getActiveEl === 'function') {
            el = this.getActiveEl();
        } else if (window.ZapPopPopBuilderCore && typeof window.ZapPopPopBuilderCore.getActiveElement === 'function') {
            el = window.ZapPopPopBuilderCore.getActiveElement();
        }
        if (!el) return;

        // 1. Flush visible .zappoppop-gp-bind inputs inside active element panel
        jQuery('.element-panel:visible .zappoppop-gp-bind, .zappoppop-panel-specific:visible .zappoppop-gp-bind').each(function() {
            var $inp = jQuery(this);
            var prop = $inp.data('prop');
            if (!prop) return;
            el[prop] = $inp.is(':checkbox') ? $inp.is(':checked') :$inp.val();
        });

        // 2. Flush Basic / Content shared controls
        if (jQuery('#prop-gp-autocomplete').length && jQuery('#insp-row-gp-autocomplete').is(':visible')) { /* ZAPPOPPOP_AC_GUARD */
            el.autocomplete = jQuery('#prop-gp-autocomplete').val() || 'off';
        }
        if (jQuery('#prop-anim-in').length) {
            el.animIn       = jQuery('#prop-anim-in').val() || 'none';
            el.animation    = el.animIn;
            el.animStart      = jQuery('#prop-anim-start').val() === 'with' ? 'with' : 'after';
            el.animDuration = parseInt(jQuery('#prop-anim-duration').val(), 10) || 1000;
            el.animDelay    = parseInt(jQuery('#prop-anim-delay').val(), 10) || 0;
            if (window.ZapPopPopBuilderAnimation) { window.ZapPopPopBuilderAnimation.syncAliases(el); }
        }
        if (jQuery('#prop-gp-required').length && jQuery('#insp-row-gp-required').is(':visible')) {
            el.required = jQuery('#prop-gp-required').is(':checked');
            if (jQuery('#prop-gp-required-msg').length) { el.requiredMessage = jQuery('#prop-gp-required-msg').val() || ''; } /* ZAPPOPPOP_REQ_MSG: flush */
        }

        // 3. Flush Form Input Style Module (Icons, Alignment, Custom CSS Class, Inline Styles Repeater)
        if (jQuery('#insp-section-form-input-style').length && jQuery('#insp-section-form-input-style').is(':visible')) {
            el.iconLeft        = jQuery('#prop-form-icon-left').val() || '';
            el.iconLeftOffset  = (isNaN(parseInt(jQuery('#prop-form-icon-left-offset').val(), 10)) ? 10 : parseInt(jQuery('#prop-form-icon-left-offset').val(), 10));
            el.iconRight       = jQuery('#prop-form-icon-right').val() || '';
            el.iconRightOffset = (isNaN(parseInt(jQuery('#prop-form-icon-right-offset').val(), 10)) ? 10 : parseInt(jQuery('#prop-form-icon-right-offset').val(), 10));
            el.customClass     = jQuery('#prop-gp-style-css-class').val() || '';
            el.cssClass        = el.customClass;

            var stylesArr = [];
            jQuery('#zappoppop-gp-inline-styles-list .zappoppop-gp-repeater-item').each(function() {
                var p = jQuery.trim(jQuery(this).find('.gp-css-prop').val() || '');
                var v = jQuery.trim(jQuery(this).find('.gp-css-val').val() || '');
                if (p !== '' || v !== '') {
                    stylesArr.push({ prop: p, value: v });
                }
            });
            el.inlineStyles = stylesArr;
        }

        // 4. Flush Conditional Logic Repeater
        if (jQuery('#insp-section-gp-conditional-logic').length) {
            el.logicEnabled = jQuery('#prop-logic-enabled').is(':checked');
            el.logicAction  = jQuery('#prop-logic-action').val() || 'show';
            el.logicMatch   = jQuery('#prop-logic-match').val() || 'all';
            var rulesArr = [];
            jQuery('#zappoppop-logic-rules-list .zappoppop-logic-rule-row').each(function() {
                var f  = jQuery.trim(jQuery(this).find('.rule-field').val() || '');
                var op = jQuery(this).find('.rule-operator').val() || 'equal';
                var v  = jQuery.trim(jQuery(this).find('.rule-value').val() || '');
                if (f !== '' || op === 'not_empty') {
                    rulesArr.push({ field: f, operator: op, value: v });
                }
            });
            el.logicRules = rulesArr;
        }

        // 5. Flush Data & Advanced Tabs (if present in DOM)
        if (jQuery('#prop-data-default').length) {
            el.defaultValue   = jQuery('#prop-data-default').val();
        }
        if (jQuery('#prop-data-dynamic').length) {
            el.dynamicDefault = jQuery('#prop-data-dynamic').is(':checked');
        }
        if (jQuery('#prop-data-param').length) {
            el.paramName      = jQuery('#prop-data-param').val();
        }
        if (jQuery('#prop-data-readonly').length) {
            el.readOnly       = jQuery('#prop-data-readonly').is(':checked');
        }
        if (jQuery('#prop-data-savedb').length) {
            el.saveToDb       = jQuery('#prop-data-savedb').is(':checked');
        }
        /* ZAPPOPPOP_ADV_TAB: flush */
        if (jQuery('#prop-field-name').length) {
            var advName = jQuery.trim(jQuery('#prop-field-name').val() || '').replace(/[^A-Za-z0-9_-]/g, '');
            el.fieldName = advName; el.fieldId = advName; el.token = advName;
        }
        if (jQuery('#prop-input-mask').length) { el.inputMask = jQuery.trim(jQuery('#prop-input-mask').val() || ''); }
        if (jQuery('.prop-filter-chk').length) {
            var fArr2 = [];
            jQuery('.prop-filter-chk:checked').each(function() { fArr2.push(jQuery(this).val()); });
            el.filters = fArr2;
        }
        if (jQuery('.prop-validator-chk').length) {
            var vArr2 = [];
            jQuery('.prop-validator-chk:checked').each(function() { vArr2.push(jQuery(this).val()); });
            el.validators = vArr2;
            el.errorMessage = jQuery('#prop-validator-error').val() || '';
            el.validatorEqual = jQuery('#prop-validator-equal').val() || '';
            el.validatorList = jQuery('#prop-validator-list').val() || '';
        }
        if (jQuery('#prop-adv-id').length && jQuery('#prop-adv-id').val()) {
            el.fieldId        = jQuery('#prop-adv-id').val();
        }
    },

    sanitizeLayerInspectorForElement: function(el) {
        if (!el) return;
        var rawType = String(el.type || 'label').toLowerCase();
        var aliasMap = {
            'title': 'label', 'paragraph': 'label', 'heading': 'label',
            'textfield': 'text', 'phone': 'tel', 'dropdown': 'select',
            'radios': 'radio', 'checkboxes': 'checkbox', 'file': 'upload',
            'close_icon': 'close', 'link': 'button', 'link_btn': 'button',
            'step': 'button', 'step_btn': 'button', 'spinner': 'number'
        };
        var canonical = aliasMap[rawType] || rawType;

        var mediaFamily = ['shape', 'image', 'video', 'label', 'html', 'close', 'icon', 'progress', 'button', 'submit'];
        var formFamily  = ['text', 'email', 'number', 'tel', 'textarea', 'select', 'checkbox', 'radio', 'multiselect', 'imageselect', 'tile', 'date', 'time', 'upload', 'password', 'rating'];
        var isForm = formFamily.indexOf(canonical) !== -1;

        // 1. Tab Bar Visibility: 5 Tabs for Form (10-25), 3 Tabs for Media (1-9) & Submit (26)
        var $dataTabBtn = jQuery('.zappoppop-inspector-tabs [data-tab="tab-data"], .zappoppop-insp-tab[data-tab="tab-data"], .zappoppop-insp-tab[data-tab="data"]');
        var $advTabBtn  = jQuery('.zappoppop-inspector-tabs [data-tab="tab-advanced"], .zappoppop-insp-tab[data-tab="tab-advanced"], .zappoppop-insp-tab[data-tab="advanced"]');
        if (isForm) {
            $dataTabBtn.show().css('display', 'inline-flex');
            $advTabBtn.show().css('display', 'inline-flex');
        } else {
            $dataTabBtn.hide();
            $advTabBtn.hide();
            var activeTab = jQuery('.zappoppop-inspector-tabs .active, .zappoppop-insp-tab.active').attr('data-tab') || '';
            if (activeTab.indexOf('data') !== -1 || activeTab.indexOf('advanced') !== -1) {
                jQuery('.zappoppop-inspector-tabs [data-tab="tab-basic"], .zappoppop-insp-tab[data-tab="tab-basic"], .zappoppop-insp-tab[data-tab="basic"]').first().trigger('click');
            }
        }

        // 2. Content/Basic Tab Sanitization:
        //    Hide generic #prop-content if dedicated modular panel handles content or if element is non-text form/media
        var usesGenericContent = (canonical === 'label');
        jQuery('#prop-content').closest('.zappoppop-prop-row, .zappoppop-insp-card').toggle(usesGenericContent);

        //    Show Autocomplete only on single-line text-like inputs
        var hasAutocomplete = false; /* ZAPPOPPOP_AC_OWN_PANELS: every type uses its own panel select */
        jQuery('#insp-row-gp-autocomplete').toggle(hasAutocomplete);

        //    Show canonical Required switch only for Form elements (10-25)
        jQuery('#insp-row-gp-required').toggle(isForm);

        //    Deduplicate any redundant Required or Token inputs inside #tab-basic or #tab-logic
        jQuery('#insp-section-token').hide();
        jQuery('#zappoppop-element-specific-settings .element-panel').find('input[id*="required"], input[id*="token"]').each(function() {
            if (this.id !== 'prop-gp-required') {
                jQuery(this).closest('.zappoppop-prop-row, .zappoppop-form-group').hide();
            }
        });

        // 3. Style Tab Sanitization:
        //    - Form Family (10-25): Show Form Input Style card; hide Media Typography/Hover/Box cards
        //      (except imageselect, tile, rating, upload which also allow custom color/box tweaks)
        var textBearingMedia = ['label', 'button', 'submit', 'progress'];
        var hoverCapableMedia = ['button', 'submit', 'close', 'icon'];
        var iconSlotFormTypes = ['text', 'email', 'number', 'tel', 'date', 'time', 'password'];

        if (isForm) {
            jQuery('#insp-section-form-input-style').show();
            jQuery('#gp-form-input-icons-row').toggle(iconSlotFormTypes.indexOf(canonical) !== -1);
            jQuery('#insp-section-typography, #zappoppop-hover-state-card').hide();
        } else {
            jQuery('#insp-section-form-input-style').hide();
            jQuery('#insp-section-typography').toggle(textBearingMedia.indexOf(canonical) !== -1);
            jQuery('#zappoppop-hover-state-card').toggle(hoverCapableMedia.indexOf(canonical) !== -1);
        }

        // 4. Logic Tab Sanitization:
        //    - Conditional Logic Builder is available for all 26 elements
        //    - Click/Action Redirect URL only for actionable Media elements (button, submit, image, icon)
        var actionableTypes = ['button', 'submit', 'image', 'icon'];
        jQuery('#insp-section-gp-conditional-logic').show();
        jQuery('#prop-action-url, #prop-action-target, #prop-onclick-action').closest('.zappoppop-prop-row, .zappoppop-insp-card').toggle(actionableTypes.indexOf(canonical) !== -1);

        // 5. Hydrate canonical controls from el model
        jQuery('#prop-gp-autocomplete').val(el.autocomplete || (canonical === 'email' ? 'email' : (canonical === 'tel' ? 'tel' : 'off')));
        jQuery('#prop-gp-required').prop('checked', (el.required === undefined && String(el.type || '').toLowerCase() === 'email' ? true : !!el.required) /* ZAPPOPPOP_EMAIL_REQ_DEFAULT */);
        jQuery('#prop-anim-in').val(el.animIn || el.anim_appearance || el.animation || 'none');
        jQuery('#prop-anim-start').val(el.animStart === 'with' ? 'with' : 'after');
        jQuery('#prop-anim-duration').val(el.animDuration !== undefined ? el.animDuration : 1000);
        jQuery('#prop-anim-delay').val(el.animDelay !== undefined ? el.animDelay : 0);

        jQuery('#prop-form-icon-left').val(el.iconLeft || (canonical === 'email' ? 'fas fa-envelope' : ''));
        jQuery('#prop-form-icon-left-offset').val(el.iconLeftOffset !== undefined ? el.iconLeftOffset : 10);
        jQuery('#prop-form-icon-right').val(el.iconRight || '');
        jQuery('#prop-form-icon-right-offset').val(el.iconRightOffset !== undefined ? el.iconRightOffset : 10);
        jQuery('#prop-gp-style-css-class').val(el.customClass || el.cssClass || '');

        var currentAlign = el.textAlign || 'left';
        jQuery('#gp-form-text-align-group .zappoppop-align-seg-btn').removeClass('active');
        jQuery('#gp-form-text-align-group .zappoppop-align-seg-btn[data-align="' + currentAlign + '"]').addClass('active');

        jQuery('#prop-logic-enabled').prop('checked', !!el.logicEnabled);
        jQuery('#zappoppop-gp-logic-rules-wrap').toggle(!!el.logicEnabled);
        jQuery('#prop-logic-action').val(el.logicAction || 'show');
        jQuery('#prop-logic-match').val(el.logicMatch || 'all');

        // Hydrate Inline CSS Styles Repeater
        var $stylesList = jQuery('#zappoppop-gp-inline-styles-list').empty();
        if (Array.isArray(el.inlineStyles)) {
            el.inlineStyles.forEach(function(item) {
                if (!item) return;
                var pVal = jQuery('<div>').text(item.prop || '').html();
                var vVal = jQuery('<div>').text(item.value || '').html();
                $stylesList.append(
                    '<div class="zappoppop-gp-repeater-item">' +
                    '<input type="text" class="zappoppop-input gp-css-prop" placeholder="property" value="' + pVal + '">' +
                    '<input type="text" class="zappoppop-input gp-css-val" placeholder="value" value="' + vVal + '">' +
                    '<button type="button" class="zappoppop-gp-btn-remove" title="Remove">&times;</button>' +
                    '</div>'
                );
            });
        }

        // Hydrate Conditional Logic Rules Repeater
        var $rulesList = jQuery('#zappoppop-gp-logic-rules-list').empty();
        if (Array.isArray(el.logicRules)) {
            el.logicRules.forEach(function(rule) {
                if (!rule) return;
                var fVal = jQuery('<div>').text(rule.field || '').html();
                var opVal = rule.operator || 'is';
                var rVal = jQuery('<div>').text(rule.value || '').html();
                $rulesList.append(
                    '<div class="zappoppop-gp-repeater-item">' +
                    '<input type="text" class="zappoppop-input gp-rule-field" placeholder="Field token" value="' + fVal + '">' +
                    '<select class="zappoppop-select gp-rule-op" style="width:90px;">' +
                        '<option value="is"' + (opVal === 'is' ? ' selected' : '') + '>is</option>' +
                        '<option value="is_not"' + (opVal === 'is_not' ? ' selected' : '') + '>is not</option>' +
                        '<option value="contains"' + (opVal === 'contains' ? ' selected' : '') + '>contains</option>' +
                        '<option value="not_empty"' + (opVal === 'not_empty' ? ' selected' : '') + '>not empty</option>' +
                    '</select>' +
                    '<input type="text" class="zappoppop-input gp-rule-val" placeholder="Value" value="' + rVal + '">' +
                    '<button type="button" class="zappoppop-gp-btn-remove" title="Remove rule">&times;</button>' +
                    '</div>'
                );
            });
        }

    },

    bindSanitizedzappoppopControls: function() {
        var self = this;
        if (self._gpSanitizedBound) return;
        self._gpSanitizedBound = true;

        var getEl = function() {
            if (typeof self.getActiveEl === 'function') return self.getActiveEl();
            if (window.ZapPopPopBuilderCore && typeof window.ZapPopPopBuilderCore.getActiveElement === 'function') {
                return window.ZapPopPopBuilderCore.getActiveElement();
            }
            return null;
        };

        var syncStage = function() {
            if (window.ZapPopPopBuilderCanvas && typeof window.ZapPopPopBuilderCanvas.renderCanvas === 'function') {
                window.ZapPopPopBuilderCanvas.renderCanvas();
            }
        };

        // Autocomplete, Animation & Required bindings
        jQuery(document).on('change input', '#prop-gp-autocomplete, #prop-anim-in, #prop-anim-start, #prop-anim-duration, #prop-anim-delay, #prop-gp-required', function() {
            var el = getEl();
            if (!el) return;
            if (jQuery('#insp-row-gp-autocomplete').is(':visible')) { el.autocomplete = jQuery('#prop-gp-autocomplete').val(); } /* ZAPPOPPOP_AC_GUARD */
            el.animIn       = jQuery('#prop-anim-in').val();
            el.animation    = el.animIn;
            el.animStart      = jQuery('#prop-anim-start').val() === 'with' ? 'with' : 'after';
            el.animDuration = parseInt(jQuery('#prop-anim-duration').val(), 10) || 1000;
            el.animDelay    = parseInt(jQuery('#prop-anim-delay').val(), 10) || 0;
            if (window.ZapPopPopBuilderAnimation) { window.ZapPopPopBuilderAnimation.syncAliases(el); }
            el.required     = jQuery('#prop-gp-required').is(':checked');
            jQuery('#prop-required').prop('checked', el.required);
            syncStage();
        });

        // Form Style Text Alignment Segmented Buttons
        jQuery(document).on('click', '#gp-form-text-align-group .zappoppop-align-seg-btn', function(e) {
            e.preventDefault();
            var align = jQuery(this).attr('data-align') || 'left';
            jQuery('#gp-form-text-align-group .zappoppop-align-seg-btn').removeClass('active');
            jQuery(this).addClass('active');
            var el = getEl();
            if (!el) return;
            el.textAlign = align;
            jQuery('#prop-text-align').val(align);
            syncStage();
        });

        // Input Icons & Custom CSS Class
        jQuery(document).on('change input', '#prop-form-icon-left, #prop-form-icon-left-offset, #prop-form-icon-right, #prop-form-icon-right-offset, #prop-gp-style-css-class', function() {
            var el = getEl();
            if (!el) return;
            el.iconLeft        = jQuery('#prop-form-icon-left').val();
            el.iconLeftOffset  = (isNaN(parseInt(jQuery('#prop-form-icon-left-offset').val(), 10)) ? 10 : parseInt(jQuery('#prop-form-icon-left-offset').val(), 10));
            el.iconRight       = jQuery('#prop-form-icon-right').val();
            el.iconRightOffset = (isNaN(parseInt(jQuery('#prop-form-icon-right-offset').val(), 10)) ? 10 : parseInt(jQuery('#prop-form-icon-right-offset').val(), 10));
            el.customClass     = jQuery('#prop-gp-style-css-class').val();
            el.cssClass        = el.customClass;
            jQuery('#prop-custom-class').val(el.customClass);
            syncStage();
        });

        // Add Inline CSS Style Repeater Row
        jQuery(document).on('click', '#zappoppop-btn-add-css-style', function(e) {
            e.preventDefault();
            var el = getEl();
            if (!el) return;
            el.inlineStyles = Array.isArray(el.inlineStyles) ? el.inlineStyles : [];
            el.inlineStyles.push({ prop: '', value: '' });
            var rowHtml = '<div class="zappoppop-gp-repeater-item">' +
                '<input type="text" class="zappoppop-input gp-css-prop" placeholder="property (e.g. border-bottom)">' +
                '<input type="text" class="zappoppop-input gp-css-val" placeholder="value (e.g. 2px solid #c2185b)">' +
                '<button type="button" class="zappoppop-gp-btn-remove" title="Remove">&times;</button>' +
                '</div>';
            jQuery('#zappoppop-gp-inline-styles-list').append(rowHtml);
        });

        // Conditional Logic Toggle & Add Rule Repeater
        jQuery(document).on('change', '#prop-logic-enabled, #prop-logic-action, #prop-logic-match', function() {
            var el = getEl();
            if (!el) return;
            el.logicEnabled = jQuery('#prop-logic-enabled').is(':checked');
            el.logicAction  = jQuery('#prop-logic-action').val();
            el.logicMatch   = jQuery('#prop-logic-match').val();
            jQuery('#zappoppop-gp-logic-rules-wrap').toggle(el.logicEnabled);
        });

        jQuery(document).on('click', '#zappoppop-btn-add-logic-rule', function(e) {
            e.preventDefault();
            var el = getEl();
            if (!el) return;
            el.logicRules = Array.isArray(el.logicRules) ? el.logicRules : [];
            el.logicRules.push({ field: '', operator: 'is', value: '' });
            var ruleHtml = '<div class="zappoppop-gp-repeater-item">' +
                '<input type="text" class="zappoppop-input gp-rule-field" placeholder="Field token">' +
                '<select class="zappoppop-select gp-rule-op" style="width:90px;"><option value="is">is</option><option value="is_not">is not</option><option value="contains">contains</option><option value="not_empty">not empty</option></select>' +
                '<input type="text" class="zappoppop-input gp-rule-val" placeholder="Value">' +
                '<button type="button" class="zappoppop-gp-btn-remove" title="Remove rule">&times;</button>' +
                '</div>';
            jQuery('#zappoppop-gp-logic-rules-list').append(ruleHtml);
        });

        jQuery(document).on('click', '.zappoppop-gp-btn-remove', function(e) {
            e.preventDefault();
            jQuery(this).closest('.zappoppop-gp-repeater-item').remove();
        });
    },

    init: function() {
        if (typeof this.bindSanitizedzappoppopControls === "function") { this.bindSanitizedzappoppopControls(); }
            this.bindTabs();
            this.bindClose();
            this.bindInputs();
            this.bindLayerNameInputEvents();
            this.bindInspectorCloseEvents();
            /* animation preview handled by ZapPopPopBuilderAnimation */
        },

        bindTabs: function() {
            var self = this;
            $(document).on('click', '.zappoppop-insp-tab', function(e) {
                e.preventDefault();
                var tab = $(this).data('tab');
                self.switchTab(tab);
            });
        },

        switchTab: function(tab) {
        $('.zappoppop-insp-tab').removeClass('active').css({
            'border-bottom': '2px solid transparent',
            'color': '#94a3b8'
        });
        $('.zappoppop-insp-tab[data-tab="' + tab + '"]').addClass('active').css({
            'border-bottom': '2px solid #38bdf8',
            'color': '#38bdf8'
        });

        $('.zappoppop-tab-pane, .zappoppop-insp-content').hide();
        $('#insp-tab-' + tab).show();
    },
    _orig_switchTab: function(tab) {
            $('.zappoppop-insp-tab').removeClass('active').css({
                borderBottomColor: 'transparent',
                color: '#94a3b8'
            });
            $('.zappoppop-insp-tab[data-tab="' + tab + '"]').addClass('active').css({
                borderBottomColor: '#38bdf8',
                color: '#38bdf8'
            });

            $('.zappoppop-tab-pane').hide();
            $('#insp-tab-' + tab).show();

                if (tab === 'style') {
                    var curEl = (typeof self.getActiveEl === 'function' ? self.getActiveEl() : (typeof self.getActiveElement === 'function' ? self.getActiveElement() : null));
                    if (!curEl && window.ZapPopPopBuilderCore && window.ZapPopPopBuilderCore.state) {
                        var aId = window.ZapPopPopBuilderCore.state.activeId;
                        var curC = window.ZapPopPopBuilderCore.state.currentCanvas || 1;
                        var cList = window.ZapPopPopBuilderCore.state.canvases ? window.ZapPopPopBuilderCore.state.canvases[curC] : [];
                        curEl = (cList || []).find(function(it) { return it.id === aId; });
                    }
                    if (curEl) {
                        var cType = (curEl.type || 'text').toLowerCase();
                        if (['shape', 'image', 'video', 'signature', 'html'].indexOf(cType) !== -1) {
                            $('#insp-typography-section').hide();
                        } else {
                            $('#insp-typography-section').show();
                        }
                    }
                }

        },

        bindClose: function() {
            var self = this;
            $('#zappoppop-inspector-close').on('click', function(e) {
                e.preventDefault();
                self.close();
            });
        },

        updateContextualVisibility: function(type) {
        var rawType = (type || '').toLowerCase();
        this.updateContextualVisibility(rawType);
        var textTypes = ['title', 'paragraph', 'text', 'textfield', 'email', 'number', 'select', 'dropdown', 'date', 'submit', 'submit_btn', 'step', 'step_btn', 'link', 'link_btn', 'pay', 'pay_btn', 'countdown'];
        var buttonTypes = ['submit', 'submit_btn', 'step', 'step_btn', 'link', 'link_btn', 'pay', 'pay_btn', 'close', 'close_icon'];
        var formTypes = ['textfield', 'email', 'number', 'select', 'dropdown', 'radios', 'radio', 'checkboxes', 'checkbox', 'date', 'file'];

        // Typography Section
        if (textTypes.indexOf(rawType) !== -1) {
            $('#insp-section-typography, .insp-section-typography').show();
        } else {
            $('#insp-section-typography, .insp-section-typography').hide();
        }

        // Button States Switcher Section
        if (buttonTypes.indexOf(rawType) !== -1) {
            $('#insp-section-button-states').show();
        } else {
            $('#insp-section-button-states').hide();
            $('#zappoppop-hover-props-panel').hide();
            $('#zappoppop-btn-state-toggle .zappoppop-segment-btn[data-state="normal"]').addClass('active');
            $('#zappoppop-btn-state-toggle .zappoppop-segment-btn[data-state="hover"]').removeClass('active');
        }

        // Form Tokens & Token pills
        if (formTypes.indexOf(rawType) !== -1 || rawType === 'paragraph' || rawType === 'title') {
            $('#insp-section-tokens, .zappoppop-token-pills').show();
        } else {
            $('#insp-section-tokens, .zappoppop-token-pills').hide();
        }
    },

    bindButtonStateTabs: function() {
        var self = this;
        $('#zappoppop-btn-state-toggle').off('click.zappoppop_btn_state', '.zappoppop-segment-btn').on('click.zappoppop_btn_state', '.zappoppop-segment-btn', function(e) {
            e.preventDefault();
            var state = $(this).data('state');
            $('#zappoppop-btn-state-toggle .zappoppop-segment-btn').removeClass('active');
            $(this).addClass('active');

            if (state === 'hover') {
                $('#zappoppop-hover-props-panel').slideDown(140);
                $('#insp-section-appearance').slideUp(140);
            } else {
                $('#zappoppop-hover-props-panel').slideUp(140);
                $('#insp-section-appearance').slideDown(140);
            }
        });
    },

    bindBoxShadowBuilder: function() {
        var self = this;
        var updateShadow = function() {
            var cur = (window.ZapPopPopBuilderCore && window.ZapPopPopBuilderCore.state) ? (window.ZapPopPopBuilderCore.state.currentCanvas || 1) : 1;
            var elements = window.ZapPopPopBuilderCore.state.canvases[cur] || [];
            var el = elements.find(function(item) { return item.id === self.activeId; });
            if (!el) return;

            var x = parseInt($('#prop-shadow-x').val(), 10) || 0;
            var y = parseInt($('#prop-shadow-y').val(), 10) || 0;
            var blur = parseInt($('#prop-shadow-blur').val(), 10) || 0;
            var spread = parseInt($('#prop-shadow-spread').val(), 10) || 0;
            var color = $('#prop-shadow-color').val() || 'transparent';

            el.shadowX = x;
            el.shadowY = y;
            el.shadowBlur = blur;
            el.shadowSpread = spread;
            el.shadowColor = color;

            var shadowStr = (x === 0 && y === 0 && blur === 0 && spread === 0) ? 'none' : (x + 'px ' + y + 'px ' + blur + 'px ' + spread + 'px ' + color);
            el.boxShadow = shadowStr;

            var $node = $('#' + el.id + ', #el-' + el.id);
            $node.css('box-shadow', shadowStr);
        };

        $('#prop-shadow-x, #prop-shadow-y, #prop-shadow-blur, #prop-shadow-spread, #prop-shadow-color')
            .off('input.zappoppop_shadow change.zappoppop_shadow')
            .on('input.zappoppop_shadow change.zappoppop_shadow', updateShadow);
    },

    hydrateStyleLogicFields: function(el) {
        if (!el) return;
        var op = (el.opacity !== undefined && el.opacity !== null) ? parseFloat(el.opacity) : 1;
        var opPercent = Math.round(op <= 1 ? op * 100 : op);
        $('#prop-opacity').val(opPercent);
        $('#prop-opacity-val').text(opPercent + '%');

        // Button hover visibility & values
        var btnTypes = ['submit', 'submit_btn', 'step', 'step_btn', 'link', 'link_btn', 'pay', 'pay_btn', 'close', 'close_icon'];
        if (btnTypes.indexOf((el.type || '').toLowerCase()) !== -1) {
            $('#prop-btn-hover-section').show();
            $('#prop-btn-hover-bg').val(el.hoverBg || el.btnHoverBg || '#2563eb');
            $('#prop-btn-hover-color').val(el.hoverColor || el.btnHoverColor || '#ffffff');
            $('#prop-btn-hover-border').val(el.hoverBorder || el.btnHoverBorder || '#1d4ed8');
        } else {
            $('#prop-btn-hover-section').hide();
        }

        // Custom CSS identifiers
        $('#prop-custom-class').val(el.custom_css_class || el.customClass || '');
        $('#prop-custom-id').val(el.custom_css_id || el.customId || '');

        // Navigation
        $('#prop-goto-canvas').val(el.goto_canvas || el.goto_screen || el.target_canvas || 2);

        // Display Token
        var tokenName = el.fieldName || el.field_name || el.name || el.type || 'field';
        $('#prop-display-token').val('{' + tokenName + '}');
    },

    open: function(id) {
            if (window.ZapPopPopElementsRegistry) {
                var _regEl = window.ZapPopPopElementsRegistry.resolveElement(id);
                if (_regEl) {
                    window.ZapPopPopElementsRegistry.hydrateElement(_regEl, this);
                }
            }
        var self = this;
        if (!id) return;
        self.activeId = id;

        // 1. Structural containment & Slide-in UI activation
        var $drawer = $('#zappoppop-inspector-drawer');
        var $ws = $('.zappoppop-builder-workspace, #zappoppop-builder-workspace').first();
        if ($drawer.length && $ws.length && $drawer.parent()[0] !== $ws[0]) {
            $drawer.appendTo($ws);
        }

        $drawer.css('display', 'flex').removeClass('is-closed').addClass('is-open');
        $('body, .zappoppop-builder-workspace, .zappoppop-main-frame, .zappoppop-builder-wrap').addClass('panel-open inspector-open');

        if (window.ZapPopPopBuilderLayers && typeof window.ZapPopPopBuilderLayers.pushForInspector === 'function') {
            window.ZapPopPopBuilderLayers.pushForInspector(true);
        }

        // 2. Safe defensive element resolution
        var elements = [];
        if (window.ZapPopPopBuilderCanvas && typeof window.ZapPopPopBuilderCanvas.getActiveElements === 'function') {
            elements = window.ZapPopPopBuilderCanvas.getActiveElements() || [];
        }
        if ((!elements || !elements.length) && window.ZapPopPopBuilderCore && window.ZapPopPopBuilderCore.state && window.ZapPopPopBuilderCore.state.canvases) {
            var cur = window.ZapPopPopBuilderCore.state.currentCanvas || 1;
            elements = window.ZapPopPopBuilderCore.state.canvases[cur] || [];
        }

        var el = null;
        if (elements && elements.length) {
            for (var i = 0; i < elements.length; i++) {
                if (String(elements[i].id) === String(id)) {
                    el = elements[i];
                    break;
                }
            }
        }
        if (!el && window.ZapPopPopBuilderCore && window.ZapPopPopBuilderCore.state && window.ZapPopPopBuilderCore.state.canvases) {
            var curC = window.ZapPopPopBuilderCore.state.currentCanvas || 1;
            var list = window.ZapPopPopBuilderCore.state.canvases[curC] || [];
            for (var j = 0; j < list.length; j++) {
                if (String(list[j].id) === String(id)) {
                    el = list[j];
                    break;
                }
            }
        }

        if (!el) return;

        /* Title Element Hydration */
        if ((el.type || '').toLowerCase() === 'title') {
            var rawTitle = el.titleText || el.text || el.content || 'Headline Title';
            var rawTag   = el.headingTag || el.titleTag || el.tag || 'h2';
            var rawTrans = el.textTransform || el.text_transform || 'none';
            $('#prop-title-text').val(rawTitle);
            $('#prop-title-tag').val(rawTag);
            $('#prop-title-transform').val(rawTrans);
        }


        /* Phase 2: Hydrate Opacity & Hover */
        var opVal = (typeof el.opacity !== 'undefined') ? parseFloat(el.opacity) : 1;
        if (opVal <= 1) opVal = Math.round(opVal * 100);
        $('#prop-opacity').val(opVal);
        $('#prop-opacity-val').text(opVal + '%');

        $('#prop-custom-class').val(el.customClass || el.custom_class || '');
        $('#prop-custom-id').val(el.customId || el.custom_id || '');

        var btnTypes = ['submit', 'link', 'step', 'pay', 'submit_btn', 'link_btn', 'step_btn', 'pay_btn', 'button'];
        var isButton = btnTypes.indexOf((el.type || '').toLowerCase()) >= 0;
        if (isButton) {
            $('#prop-btn-hover-section').show();
            $('#prop-btn-hover-bg').val(el.hoverBgColor || el.hover_bg_color || '');
            $('#prop-btn-hover-color').val(el.hoverTextColor || el.hover_text_color || '');
            $('#prop-btn-hover-border').val(el.hoverBorderColor || el.hover_border_color || '');
        } else {
            $('#prop-btn-hover-section').hide();
        }


        // 3. Mark element as selected on stage
        $('.zappoppop-element, .zappoppop-canvas-item').removeClass('zappoppop-selected ui-selected');
        $('#zappoppop-el-' + id + ', #el-' + id + ', [data-id="' + id + '"]').addClass('zappoppop-selected');
        if (window.ZapPopPopBuilderCore && window.ZapPopPopBuilderCore.state) {
            window.ZapPopPopBuilderCore.state.activeId = id;
        }

        // 4. Populate Layer Name
        $('#prop-layer-name, #prop-element-name').val(el.name || el.label || el.type || '');

        // 5. Canonical Type Normalization & Contextual Panels
        var canonicalType = (el.type || 'text').toLowerCase();
        if (canonicalType === 'select') canonicalType = 'dropdown';
        if (canonicalType === 'radios') canonicalType = 'radio';
        if (canonicalType === 'checkboxes') canonicalType = 'checkbox';
        if (canonicalType === 'step_btn') canonicalType = 'step';
        if (canonicalType === 'link_btn') canonicalType = 'link';
        if (canonicalType === 'close_icon') canonicalType = 'close';
        if (canonicalType === 'text') canonicalType = 'paragraph';

        // Contextual Typography Visibility (Hide for non-text types)
        var nonTextTypes = ['shape', 'image', 'video', 'signature', 'html'];
        if (nonTextTypes.indexOf(canonicalType) !== -1 || nonTextTypes.indexOf((el.type || '').toLowerCase()) !== -1) {
            $('#insp-typography-section').hide();
        } else {
            $('#insp-typography-section').show();
        }

        // Contextual Form Token Visibility
        var inputElements = ['textfield', 'email', 'number', 'dropdown', 'radio', 'checkbox', 'rating', 'date', 'slider', 'file', 'signature'];
        if (inputElements.indexOf(canonicalType) === -1) {
            $('#prop-display-token').closest('.zappoppop-inspector-section, div').hide();
        } else {
            $('#prop-display-token').closest('.zappoppop-inspector-section, div').show();
        }

        // Show matching contextual panel in Basic Tab
        var $activePanel = $('#panel-elem-' + canonicalType); /* ZAPPOPPOP_PANEL_NOHIDE */
        $('.element-panel').not($activePanel).hide();
        if (!$activePanel.length && canonicalType === 'paragraph') $activePanel = $('#panel-elem-text');
        if (!$activePanel.length && canonicalType === 'dropdown') $activePanel = $('#panel-elem-select');
        if (!$activePanel.length && canonicalType === 'radio') $activePanel = $('#panel-elem-radios');
        if (!$activePanel.length && canonicalType === 'checkbox') $activePanel = $('#panel-elem-checkboxes');
        if (!$activePanel.length && canonicalType === 'step') $activePanel = $('#panel-elem-step_btn');
        if (!$activePanel.length && canonicalType === 'link') $activePanel = $('#panel-elem-link_btn');
        if (!$activePanel.length && canonicalType === 'close') $activePanel = $('#panel-elem-close_icon');
        if ($activePanel.length) {
            $activePanel.show();
        }

        // 6. Element-specific values population
        if (canonicalType === 'title') {
            $('#prop-title-content, #prop-title-text').val(el.content || el.text || '');
            $('#prop-title-tag').val(el.htmlTag || el.tag || 'h2');
        } else if (canonicalType === 'paragraph') {
            $('#prop-paragraph-content, #prop-text-content').val(el.content || el.text || '');
        } else if (canonicalType === 'textfield') {
            $('#prop-textfield-placeholder').val(el.placeholder || el.content || '');
            $('#prop-textfield-fieldname').val(el.fieldName || el.field_name || 'text_field');
            $('#prop-textfield-required').prop('checked', !!el.required);
        } else if (canonicalType === 'dropdown') {
            $('#prop-dropdown-options, #prop-select-options').val(Array.isArray(el.options) ? el.options.join(', ') : (el.content || ''));
            $('#prop-dropdown-fieldname, #prop-select-fieldname').val(el.fieldName || el.field_name || 'select_field');
        } else if (canonicalType === 'step') {
            $('#prop-step-label').val(el.content || el.text || 'Next');
            $('#prop-step-canvas').val(el.goto_canvas || el.goto_screen || 2);
        } else if (canonicalType === 'link') {
            $('#prop-link-label').val(el.content || el.text || 'Learn More');
            $('#prop-link-url').val(el.url || el.href || '');
        } else if (canonicalType === 'close') {
            $('#prop-close-label').val(el.content || el.text || '×');
            $('#prop-close-action').val(el.action || el.close_action || 'close');
        } else if (canonicalType === 'image') {
            $('#prop-image-url').val(el.url || el.src || '');
            $('#prop-image-alt').val(el.alt || '');
        } else if (canonicalType === 'video') {
            $('#prop-video-url').val(el.videoUrl || el.content || el.url || el.src || '');
            $('#prop-video-autoplay').prop('checked', !!(el.videoAutoplay || el.autoplay));
            $('#prop-video-mute').prop('checked', !!(el.videoMute || el.mute));
            $('#prop-video-loop').prop('checked', !!(el.videoLoop || el.loop));
            $('#prop-video-provider').val(el.videoProvider || 'youtube');
            $('#prop-video-aspect').val(el.aspectRatio || '16:9');
        }

        // 7. General Appearance, Coordinates & Dimensions
        $('#prop-left').val(parseInt(el.left, 10) || 0);
        $('#prop-top').val(parseInt(el.top, 10) || 0);
        $('#prop-width, #prop-size-width').val(parseInt(el.width, 10) || 100);
        $('#prop-height, #prop-size-height').val(parseInt(el.height, 10) || 40);

        // Opacity
        var elOpacity = (el.opacity !== undefined && el.opacity !== null && el.opacity !== '') ? parseInt(el.opacity, 10) : 100;
        $('#prop-opacity').val(elOpacity);
        $('#prop-opacity-val').text(elOpacity + '%');

        // Custom CSS Class & ID
        $('#prop-custom-class').val(el.customClass || el.custom_class || '');
        $('#prop-custom-id').val(el.customId || el.custom_id || '');

        // Button Hover States
        var isButtonType = ['submit', 'link', 'step', 'pay'].indexOf(canonicalType) !== -1;
        if (isButtonType) {
            $('#prop-btn-hover-section').show();
            $('#prop-btn-hover-bg').val(el.hoverBgColor || el.hover_bg_color || '#2563eb');
            $('#prop-btn-hover-color').val(el.hoverTextColor || el.hover_text_color || '#ffffff');
            $('#prop-btn-hover-border').val(el.hoverBorderColor || el.hover_border_color || '#1d4ed8');
        } else {
            $('#prop-btn-hover-section').hide();
        }

        // Typography
        if ($('#prop-font-family').length && el.fontFamily) $('#prop-font-family').val(el.fontFamily);
        if ($('#prop-font-size').length && el.fontSize) $('#prop-font-size').val(parseInt(el.fontSize, 10) || ((type === 'checkbox' || type === 'checkboxes' || type === 'radio' || type === 'radios' || type === 'checkboxgroup' || type === 'tile') ? 12 : 16));
        if ($('#prop-font-weight').length && el.fontWeight) $('#prop-font-weight').val(el.fontWeight);
        if ($('#prop-text-align').length && el.textAlign) $('#prop-text-align').val(el.textAlign);
        if ($('#prop-line-height').length && el.lineHeight) $('#prop-line-height').val(el.lineHeight);
        if ($('#prop-letter-spacing').length && el.letterSpacing) $('#prop-letter-spacing').val(el.letterSpacing);

        // Colors
        if ($('#prop-color').length && el.color) $('#prop-color').val(el.color);
        if ($('#prop-bg-color').length && el.bgColor) $('#prop-bg-color').val(el.bgColor);

        // Animation (canonical keys: animIn / animStart / animDuration / animDelay)
        if ($('#prop-anim-in').length) {
            $('#prop-anim-in').val(el.animIn || el.anim_appearance || el.animation || 'none');
            $('#prop-anim-start').val(el.animStart === 'with' ? 'with' : 'after');
            $('#prop-anim-duration').val(el.animDuration !== undefined && el.animDuration !== '' ? el.animDuration : 1000);
            $('#prop-anim-delay').val(el.animDelay !== undefined && el.animDelay !== '' ? el.animDelay : 0);
        }
    },

    close: function(force) {
        var $drawer = $('#zappoppop-inspector-drawer');
        /* ZAPPOPPOP_INSPECTOR_CLOSE_ONLY_X: only the title-bar X (or force===true) may close */
        if (force !== true && !window.__zappoppopInspCloseAllowed) { return; }
        $drawer.removeClass('is-open').addClass('is-closed');
        $('body, .zappoppop-builder-workspace, .zappoppop-main-frame, .zappoppop-builder-wrap').removeClass('panel-open inspector-open');
        setTimeout(function() {
            if (!$drawer.hasClass('is-open')) {
                $drawer.hide();
            }
        }, 280);
        $('.zappoppop-element, .zappoppop-canvas-item').removeClass('zappoppop-selected ui-selected');
        if (window.ZapPopPopBuilderCore && window.ZapPopPopBuilderCore.state) {
            window.ZapPopPopBuilderCore.state.activeId = null;
        }
        if (window.ZapPopPopBuilderCanvas) {
            window.ZapPopPopBuilderCanvas.activeId = null;
        }
        if (window.ZapPopPopBuilderLayers && typeof window.ZapPopPopBuilderLayers.pushForInspector === 'function') {
            window.ZapPopPopBuilderLayers.pushForInspector(false);
        }
    },

        /* ZAPPOPPOP_ACTIVE_EL: resolves the live model object inside Core.state.canvases so inspector handlers mutate saved state (drives Save -> Update). */
        getActiveEl: function() {
            var core = window.ZapPopPopBuilderCore;
            if (!core || !core.state || !core.state.activeId) return null;
            var cur = core.state.currentCanvas || 1;
            var list = (core.state.canvases && core.state.canvases[cur]) || [];
            var id = String(core.state.activeId).replace(/^el-/, '');
            for (var i = 0; i < list.length; i++) {
                if (list[i] && String(list[i].id).replace(/^el-/, '') === id) { return list[i]; }
            }
            return null;
        },

        getActiveElement: function() {
            var core = window.ZapPopPopBuilderCore;
            if (!core || !core.state.activeId) return null;
            var cur = core.state.currentCanvas || 1;
            var elements = core.state.canvases[cur] || [];
            return elements.find(function(item) { return String(item.id) === String(core.state.activeId); });
        },

        syncToCanvas: function() {
            if (window.ZapPopPopBuilderCanvas) {
                window.ZapPopPopBuilderCanvas.renderCanvas();
            }
            if (window.ZapPopPopBuilderLayers) {
                window.ZapPopPopBuilderLayers.renderLayers();
            }
            if (window.ZapPopPopBuilderCore) {
                window.ZapPopPopBuilderCore.pushHistory();
            }
        },

        
        showElementSpecificSettings: function(el) {
            var rawType = (el && el.type) ? String(el.type).toLowerCase() : 'text';
            var normType = this.normalizeType ? this.normalizeType(rawType) : rawType;
            
            // Hide all specific panels
            var panels = document.querySelectorAll('.element-panel, .zappoppop-panel-specific');
            panels.forEach(function(p) { p.style.display = 'none'; });

            // Activate matching panel
            var panel = document.getElementById('panel-elem-' + normType) ||
                        document.getElementById('panel-elem-' + rawType) ||
                        document.getElementById('panel-elem-' + (normType === 'text' ? 'paragraph' : normType));
            if (panel) {
                panel.style.display = 'block';
            }

            // Contextual isolation: Show/Hide Typography in Style tab based on element type
            var textTypes = ['text', 'paragraph', 'title', 'email', 'textfield', 'number', 'dropdown', 'select', 'radios', 'checkboxes', 'step_btn', 'link_btn', 'submit', 'pay', 'countdown'];
            var isTextual = textTypes.indexOf(normType) !== -1 || textTypes.indexOf(rawType) !== -1;
            var typoSection = document.getElementById('zappoppop-insp-typography-group');
            if (typoSection) {
                typoSection.style.display = isTextual ? 'block' : 'none';
            }
        },
    
        syncCoordinates: function(el) {
            $('#prop-left').val(Math.round(el.left || 0));
            $('#prop-top').val(Math.round(el.top || 0));
            $('#prop-width').val(Math.round(el.width || 0));
            $('#prop-height').val(Math.round(el.height || 0));
        },

        bindStyleLogicSync: function() {
        var self = this;
        var getActiveEl = function() {
            var activeId = window.ZapPopPopBuilderCore ? window.ZapPopPopBuilderCore.state.activeId : null;
            if (!activeId) return null;
            var elements = window.ZapPopPopBuilderCanvas ? window.ZapPopPopBuilderCanvas.getActiveElements() : [];
            return elements.find(function(e) { return e.id === activeId; });
        };

        // Opacity live stage update
        $('#prop-opacity').off('input.zappoppop_op change.zappoppop_op').on('input.zappoppop_op change.zappoppop_op', function() {
            var el = getActiveEl();
            if (!el) return;
            var val = parseInt($(this).val(), 10);
            $('#prop-opacity-val').text(val + '%');
            var opFloat = (val / 100).toFixed(2);
            el.opacity = parseFloat(opFloat);
            $('#el-' + el.id + ', #' + el.id).css('opacity', opFloat);
        });

        // Hover live model update
        $('#prop-btn-hover-bg, #prop-btn-hover-color, #prop-btn-hover-border').off('input.zappoppop_hover change.zappoppop_hover')
            .on('input.zappoppop_hover change.zappoppop_hover', function() {
                var el = getActiveEl();
                if (!el) return;
                el.hoverBg = $('#prop-btn-hover-bg').val();
                el.btnHoverBg = el.hoverBg;
                el.hoverColor = $('#prop-btn-hover-color').val();
                el.btnHoverColor = el.hoverColor;
                el.hoverBorder = $('#prop-btn-hover-border').val();
                el.btnHoverBorder = el.hoverBorder;
            });

        // Custom CSS identifiers update
        $('#prop-custom-class, #prop-custom-id').off('input.zappoppop_css change.zappoppop_css')
            .on('input.zappoppop_css change.zappoppop_css', function() {
                var el = getActiveEl();
                if (!el) return;
                el.custom_css_class = $('#prop-custom-class').val().trim();
                el.customClass = el.custom_css_class;
                el.custom_css_id = $('#prop-custom-id').val().trim();
                el.customId = el.custom_css_id;
            });

        // Target Canvas update
        $('#prop-goto-canvas').off('change.zappoppop_canvas').on('change.zappoppop_canvas', function() {
            var el = getActiveEl();
            if (!el) return;
            var cVal = parseInt($(this).val(), 10) || 1;
            el.goto_canvas = cVal;
            el.goto_screen = cVal;
            el.target_canvas = cVal;
        });

        // Click to copy display token
        $('#prop-display-token').off('click.zappoppop_copy_token').on('click.zappoppop_copy_token', function() {
            $(this).select();
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText($(this).val());
            } else {
                document.execCommand('copy');
            }
        });
    },

    bindInputs: function() {
        var self = this;
        var getActiveEl = function() {
            var core = window.ZapPopPopBuilderCore;
            var activeId = core && core.state ? core.state.activeId : null;
            if (!activeId) return null;
            var elements = (window.ZapPopPopBuilderCanvas && typeof window.ZapPopPopBuilderCanvas.getActiveElements === 'function') ? window.ZapPopPopBuilderCanvas.getActiveElements() : [];
            return elements.find(function(e) { return e.id === activeId; }) || null;
        };

        // HTML / Embed Code Live Sync
        $(document).off('input.zappoppop_html change.zappoppop_html', '#prop-html-code')
            .on('input.zappoppop_html change.zappoppop_html', '#prop-html-code', function() {
                var el = getActiveEl();
                if (!el) return;
                var val = $(this).val();
                el.content = val;
                el.htmlCode = val;
                if (window.ZapPopPopBuilderCanvas) {
                    window.ZapPopPopBuilderCanvas.renderCanvas();
                }
            });

        // Divider Live Sync
        $(document).off('change.zappoppop_div input.zappoppop_div', '#prop-divider-style, #prop-divider-weight, #prop-divider-color, #prop-divider-width-pct, #prop-divider-align')
            .on('change.zappoppop_div input.zappoppop_div', '#prop-divider-style, #prop-divider-weight, #prop-divider-color, #prop-divider-width-pct, #prop-divider-align', function() {
                var el = getActiveEl();
                if (!el) return;
                el.dividerStyle = $('#prop-divider-style').val();
                el.dividerWeight = parseInt($('#prop-divider-weight').val(), 10) || 2;
                el.dividerColor = $('#prop-divider-color').val();
                el.dividerWidthPct = parseInt($('#prop-divider-width-pct').val(), 10) || 100;
                el.dividerAlign = $('#prop-divider-align').val();
                if (window.ZapPopPopBuilderCanvas) {
                    window.ZapPopPopBuilderCanvas.renderCanvas();
                }
            });

        // Icon Preset & Class Live Sync
        $(document).off('change.zappoppop_icon_preset', '#prop-icon-preset').on('change.zappoppop_icon_preset', '#prop-icon-preset', function() {
            var val = $(this).val();
            if (val !== 'custom') {
                $('#prop-icon-class').val(val).trigger('input');
            }
        });

        $(document).off('input.zappoppop_icon change.zappoppop_icon', '#prop-icon-class, #prop-icon-size, #prop-icon-color')
            .on('input.zappoppop_icon change.zappoppop_icon', '#prop-icon-class, #prop-icon-size, #prop-icon-color', function() {
                var el = getActiveEl();
                if (!el) return;
                var iClass = $('#prop-icon-class').val().trim() || 'fas fa-star';
                var iSize = parseInt($('#prop-icon-size').val(), 10) || 36;
                var iColor = $('#prop-icon-color').val() || '#38bdf8';

                el.iconClass = iClass;
                el.content = iClass;
                el.iconSize = iSize;
                el.iconColor = iColor;

                $('#prop-icon-live-preview').attr('class', iClass).css({
                    color: iColor,
                    fontSize: iSize + 'px'
                });

                if (window.ZapPopPopBuilderCanvas) {
                    window.ZapPopPopBuilderCanvas.renderCanvas();
                }
            });

        this.bindStyleLogicSync();
        this.bindButtonStateTabs();
        this.bindBoxShadowBuilder();

        /* Title Element Event Listeners */
        $('#prop-title-text').off('input.zappoppopTitle change.zappoppopTitle').on('input.zappoppopTitle change.zappoppopTitle', function() {
            var el = (typeof self.getActiveEl === 'function') ? self.getActiveEl() : (typeof self.getActiveElement === 'function' ? self.getActiveElement() : null);
            if (!el) return;
            var val = $(this).val();
            el.titleText = val;
            el.text = val;
            el.content = val;

            var $node = $('#el-' + el.id + ', #canvas-elem-' + el.id);
            if ($node.length) {
                var $inner = $node.find('.zappoppop-title-inner');
                if ($inner.length) {
                    $inner.text(val || 'Headline Title');
                } else {
                    $node.find('h1, h2, h3, h4, h5, h6, div').first().text(val || 'Headline Title');
                }
            }
        });

        $('#prop-title-tag').off('change.zappoppopTitleTag').on('change.zappoppopTitleTag', function() {
            var el = (typeof self.getActiveEl === 'function') ? self.getActiveEl() : (typeof self.getActiveElement === 'function' ? self.getActiveElement() : null);
            if (!el) return;
            var newTag = $(this).val();
            el.headingTag = newTag;
            el.titleTag = newTag;
            el.tag = newTag;

            if (window.ZapPopPopBuilderCanvas && typeof window.ZapPopPopBuilderCanvas.renderCanvas === 'function') {
                window.ZapPopPopBuilderCanvas.renderCanvas();
            }
            if (window.ZapPopPopBuilderCore && typeof window.ZapPopPopBuilderCore.pushHistory === 'function') {
                window.ZapPopPopBuilderCore.pushHistory('Change Heading Tag');
            }
        });

        $('#prop-title-transform').off('change.zappoppopTitleTrans').on('change.zappoppopTitleTrans', function() {
            var el = (typeof self.getActiveEl === 'function') ? self.getActiveEl() : (typeof self.getActiveElement === 'function' ? self.getActiveElement() : null);
            if (!el) return;
            var transVal = $(this).val();
            el.textTransform = transVal;
            el.text_transform = transVal;

            var $node = $('#el-' + el.id + ', #canvas-elem-' + el.id);
            if ($node.length) {
                $node.find('.zappoppop-title-inner, h1, h2, h3, h4, h5, h6, div').css('text-transform', transVal);
            }
            if (window.ZapPopPopBuilderCore && typeof window.ZapPopPopBuilderCore.pushHistory === 'function') {
                window.ZapPopPopBuilderCore.pushHistory('Change Text Transform');
            }
        });


        /* Phase 2: Bind Opacity & Hover Listeners */
        $('#prop-opacity').off('input.zappoppopOpacity').on('input.zappoppopOpacity', function() {
            var val = parseInt($(this).val(), 10);
            $('#prop-opacity-val').text(val + '%');
            var el = (typeof self.getActiveEl === 'function') ? self.getActiveEl() : (typeof self.getActiveElement === 'function' ? self.getActiveElement() : null);
            if (el) {
                el.opacity = val / 100;
                var $node = $('#canvas-elem-' + el.id);
                if ($node.length) $node.css('opacity', el.opacity);
            }
        });

        $('#prop-custom-class').off('input.zappoppopCustomClass change.zappoppopCustomClass').on('input.zappoppopCustomClass change.zappoppopCustomClass', function() {
            var val = $(this).val().trim();
            var el = (typeof self.getActiveEl === 'function') ? self.getActiveEl() : (typeof self.getActiveElement === 'function' ? self.getActiveElement() : null);
            if (el) {
                el.customClass = val;
                el.custom_class = val;
            }
        });

        $('#prop-custom-id').off('input.zappoppopCustomId change.zappoppopCustomId').on('input.zappoppopCustomId change.zappoppopCustomId', function() {
            var val = $(this).val().trim();
            var el = (typeof self.getActiveEl === 'function') ? self.getActiveEl() : (typeof self.getActiveElement === 'function' ? self.getActiveElement() : null);
            if (el) {
                el.customId = val;
                el.custom_id = val;
            }
        });

        $('#prop-btn-hover-bg, #prop-btn-hover-color, #prop-btn-hover-border').off('input.zappoppopHover change.zappoppopHover').on('input.zappoppopHover change.zappoppopHover', function() {
            var el = (typeof self.getActiveEl === 'function') ? self.getActiveEl() : (typeof self.getActiveElement === 'function' ? self.getActiveElement() : null);
            if (!el) return;

        /* Title Element Hydration */
        if ((el.type || '').toLowerCase() === 'title') {
            var rawTitle = el.titleText || el.text || el.content || 'Headline Title';
            var rawTag   = el.headingTag || el.titleTag || el.tag || 'h2';
            var rawTrans = el.textTransform || el.text_transform || 'none';
            $('#prop-title-text').val(rawTitle);
            $('#prop-title-tag').val(rawTag);
            $('#prop-title-transform').val(rawTrans);
        }

            el.hoverBgColor = $('#prop-btn-hover-bg').val().trim();
            el.hoverTextColor = $('#prop-btn-hover-color').val().trim();
            el.hoverBorderColor = $('#prop-btn-hover-border').val().trim();
            
            var $node = $('#canvas-elem-' + el.id);
            if ($node.length) {
                $node.attr('data-has-hover', 'true');
                if (el.hoverBgColor) $node[0].style.setProperty('--zappoppop-hover-bg', el.hoverBgColor);
                if (el.hoverTextColor) $node[0].style.setProperty('--zappoppop-hover-color', el.hoverTextColor);
                if (el.hoverBorderColor) $node[0].style.setProperty('--zappoppop-hover-border', el.hoverBorderColor);
            }
        });


            // --- 26-ELEMENT TWO-WAY LIVE EVENT BINDINGS ---
            $('#prop-title-text, #prop-paragraph-text, #prop-text-content').off('input.zappoppop_sync').on('input.zappoppop_sync', function() {
                var el = resolveEl();
                if (el) {
                    var val = $(this).val();
                    el.text = val;
                    el.content = val;
                    if (window.ZapPopPopBuilderCanvas) window.ZapPopPopBuilderCanvas.renderCanvas();
                }
            });

            $('#prop-tf-placeholder, #prop-tf-name, #prop-tf-required').off('input.zappoppop_sync change.zappoppop_sync').on('input.zappoppop_sync change.zappoppop_sync', function() {
                var el = resolveEl();
                if (el) {
                    el.placeholder = $('#prop-tf-placeholder').val();
                    el.fieldName = $('#prop-tf-name').val();
                    el.required = $('#prop-tf-required').is(':checked');
                }
            });

            $('#prop-email-placeholder, #prop-email-name, #prop-email-required').off('input.zappoppop_sync change.zappoppop_sync').on('input.zappoppop_sync change.zappoppop_sync', function() {
                var el = resolveEl();
                if (el) {
                    el.placeholder = $('#prop-email-placeholder').val();
                    el.fieldName = $('#prop-email-name').val();
                    el.required = $('#prop-email-required').is(':checked');
                }
            });

            $('#prop-num-placeholder, #prop-num-min, #prop-num-max, #prop-num-step, #prop-num-name, #prop-num-required').off('input.zappoppop_sync change.zappoppop_sync').on('input.zappoppop_sync change.zappoppop_sync', function() {
                var el = resolveEl();
                if (el) {
                    el.placeholder = $('#prop-num-placeholder').val();
                    el.min = parseFloat($('#prop-num-min').val()) || 0;
                    el.max = parseFloat($('#prop-num-max').val()) || 100;
                    el.step = parseFloat($('#prop-num-step').val()) || 1;
                    el.fieldName = $('#prop-num-name').val();
                    el.required = $('#prop-num-required').is(':checked');
                }
            });

            $('#prop-select-options, #prop-select-name, #prop-select-required').off('input.zappoppop_sync change.zappoppop_sync').on('input.zappoppop_sync change.zappoppop_sync', function() {
                var el = resolveEl();
                if (el) {
                    el.options = $('#prop-select-options').val().split('\n').filter(Boolean);
                    el.fieldName = $('#prop-select-name').val();
                    el.required = $('#prop-select-required').is(':checked');
                }
            });

            $('#prop-radio-options, #prop-radio-name').off('input.zappoppop_sync change.zappoppop_sync').on('input.zappoppop_sync change.zappoppop_sync', function() {
                var el = resolveEl();
                if (el) {
                    el.options = $('#prop-radio-options').val().split('\n').filter(Boolean);
                    el.fieldName = $('#prop-radio-name').val();
                }
            });

            $('#prop-cb-label, #prop-cb-name, #prop-cb-checked, #prop-cb-required').off('input.zappoppop_sync change.zappoppop_sync').on('input.zappoppop_sync change.zappoppop_sync', function() {
                var el = resolveEl();
                if (el) {
                    el.label = $('#prop-cb-label').val();
                    el.checkboxLabel = el.label;
                    el.fieldName = $('#prop-cb-name').val();
                    el.checked = $('#prop-cb-checked').is(':checked');
                    el.required = $('#prop-cb-required').is(':checked');
                }
            });

            $('#prop-date-placeholder, #prop-date-name, #prop-date-required').off('input.zappoppop_sync change.zappoppop_sync').on('input.zappoppop_sync change.zappoppop_sync', function() {
                var el = resolveEl();
                if (el) {
                    el.placeholder = $('#prop-date-placeholder').val();
                    el.fieldName = $('#prop-date-name').val();
                    el.required = $('#prop-date-required').is(':checked');
                }
            });

            $('#prop-file-extensions, #prop-file-max-size, #prop-file-name, #prop-file-required').off('input.zappoppop_sync change.zappoppop_sync').on('input.zappoppop_sync change.zappoppop_sync', function() {
                var el = resolveEl();
                if (el) {
                    el.allowedExtensions = $('#prop-file-extensions').val();
                    el.maxSize = parseInt($('#prop-file-max-size').val(), 10) || 5;
                    el.fieldName = $('#prop-file-name').val();
                    el.required = $('#prop-file-required').is(':checked');
                }
            });

            $('#prop-step-label, #prop-step-canvas').off('input.zappoppop_sync change.zappoppop_sync').on('input.zappoppop_sync change.zappoppop_sync', function() {
                var el = resolveEl();
                if (el) {
                    el.label = $('#prop-step-label').val();
                    el.targetCanvas = parseInt($('#prop-step-canvas').val(), 10) || 2;
                    el.goto_canvas = el.targetCanvas;
                    if (window.ZapPopPopBuilderCanvas) window.ZapPopPopBuilderCanvas.renderCanvas();
                }
            });

            $('#prop-pay-label, #prop-pay-amount, #prop-pay-currency, #prop-pay-gateway').off('input.zappoppop_sync change.zappoppop_sync').on('input.zappoppop_sync change.zappoppop_sync', function() {
                var el = resolveEl();
                if (el) {
                    el.label = $('#prop-pay-label').val();
                    el.amount = $('#prop-pay-amount').val();
                    el.currency = $('#prop-pay-currency').val();
                    el.gateway = $('#prop-pay-gateway').val();
                    if (window.ZapPopPopBuilderCanvas) window.ZapPopPopBuilderCanvas.renderCanvas();
                }
            });

            $('#prop-html-code').off('input.zappoppop_sync').on('input.zappoppop_sync', function() {
                var el = resolveEl();
                if (el) {
                    el.htmlCode = $(this).val();
                    el.code = el.htmlCode;
                    if (window.ZapPopPopBuilderCanvas) window.ZapPopPopBuilderCanvas.renderCanvas();
                }
            });

            var self = this;
            var resolveEl = function() {
                return (typeof self.getActiveEl === 'function') ? self.getActiveEl() : ((typeof self.getActiveElement === 'function') ? self.getActiveElement() : null);
            };

            // Dynamic Heading Tag Synchronization
            $('#prop-title-tag').off('change.zappoppop_stage').on('change.zappoppop_stage', function() {
                var el = resolveEl();
                if (el) {
                    el.titleTag = $(this).val();
                    el.htmlTag = $(this).val();
                    if (window.ZapPopPopBuilderCanvas) window.ZapPopPopBuilderCanvas.renderCanvas();
                    if (window.ZapPopPopBuilderCore) window.ZapPopPopBuilderCore.pushHistory();
                }
            });

            // Image Object-Fit & Alt Synchronization
            $('#prop-image-fit, #prop-image-alt').off('input.zappoppop_stage change.zappoppop_stage').on('input.zappoppop_stage change.zappoppop_stage', function() {
                var el = resolveEl();
                if (el) {
                    el.imageFit = $('#prop-image-fit').val();
                    el.objectFit = el.imageFit;
                    el.imageAlt = $('#prop-image-alt').val();
                    el.alt = el.imageAlt;
                    el.altText = el.imageAlt;
                    if (window.ZapPopPopBuilderCanvas) window.ZapPopPopBuilderCanvas.renderCanvas();
                }
            });

            // Video URL & Controls Synchronization
            $('#prop-video-url, #prop-video-autoplay, #prop-video-mute, #prop-video-controls').off('input.zappoppop_stage change.zappoppop_stage').on('input.zappoppop_stage change.zappoppop_stage', function() {
                var el = resolveEl();
                if (el) {
                    el.videoUrl = $('#prop-video-url').val();
                    el.videoAutoplay = $('#prop-video-autoplay').is(':checked');
                    el.videoMute = $('#prop-video-mute').is(':checked');
                    el.videoControls = $('#prop-video-controls').is(':checked');
                    if (window.ZapPopPopBuilderCanvas) window.ZapPopPopBuilderCanvas.renderCanvas();
                }
            });

            // Shape Rotation Matrix Synchronization
            $('#prop-shape-rotate').off('input.zappoppop_stage change.zappoppop_stage').on('input.zappoppop_stage change.zappoppop_stage', function() {
                var el = resolveEl();
                if (el) {
                    var deg = parseInt($(this).val(), 10) || 0;
                    el.shapeRotate = deg;
                    el.rotate = deg;
                    var $node = $('#el-' + el.id);
                    if ($node.length) {
                        $node.css('transform', 'rotate(' + deg + 'deg)');
                    }
                }
            });

            if (window.ZapPopPopElementExt) { window.ZapPopPopElementExt.all('bindInspector', [resolveEl]); } /* ZAPPOPPOP_EXT_BIND_INSPECTOR */

            // Slider Bounds Synchronization
            $('#prop-slider-min, #prop-slider-max, #prop-slider-step, #prop-slider-default').off('input.zappoppop_stage change.zappoppop_stage').on('input.zappoppop_stage change.zappoppop_stage', function() {
                var el = resolveEl();
                if (el) {
                    el.sliderMin = parseFloat($('#prop-slider-min').val()) || 0;
                    el.sliderMax = parseFloat($('#prop-slider-max').val()) || 100;
                    el.sliderStep = parseFloat($('#prop-slider-step').val()) || 1;
                    el.sliderDefault = parseFloat($('#prop-slider-default').val()) || 0;
                    if (window.ZapPopPopBuilderCanvas) window.ZapPopPopBuilderCanvas.renderCanvas();
                }
            });

        var self = this;
        // Close Pop Element Live Bindings
        $('#prop-close-action').off('change.zappoppopClose').on('change.zappoppopClose', function() {
            var val = $(this).val();
            var activeEl = self.getActiveElement();
        this.hydrateStyleLogicFields(activeEl);
            if (activeEl) {
                activeEl.closeAction = val;
                activeEl.close_action = val;
                if (val === 'redirect') {
                    $('#prop-close-redirect-wrap').slideDown(150);
                } else {
                    $('#prop-close-redirect-wrap').slideUp(150);
                }
            }
        });

        $('#prop-close-icon-style').off('change.zappoppopClose').on('change.zappoppopClose', function() {
            var val = $(this).val();
            var activeEl = self.getActiveElement();
            if (activeEl) {
                activeEl.closeIconStyle = val;
                activeEl.close_icon_style = val;
                if (val === 'custom' || val === 'text') {
                    $('#prop-close-text-wrap').slideDown(150);
                    if (!activeEl.closeText) {
                        activeEl.closeText = 'CLOSE';
                        $('#prop-close-text').val('CLOSE');
                    }
                    activeEl.content = activeEl.closeText;
                } else {
                    $('#prop-close-text-wrap').slideUp(150);
                    activeEl.content = (val === 'dashicon') ? 'dashicons-no-alt' : '×';
                }
                if (window.ZapPopPopBuilderCanvas) {
                    window.ZapPopPopBuilderCanvas.renderCanvas();
                }
            }
        });

        $('#prop-close-text').off('input.zappoppopClose change.zappoppopClose').on('input.zappoppopClose change.zappoppopClose', function() {
            var val = $(this).val();
            var activeEl = self.getActiveElement();
            if (activeEl) {
                activeEl.closeText = val;
                activeEl.close_text = val;
                activeEl.content = val;
                if (window.ZapPopPopBuilderCanvas) {
                    window.ZapPopPopBuilderCanvas.renderCanvas();
                }
            }
        });

        $('#prop-close-redirect-url').off('input.zappoppopClose change.zappoppopClose').on('input.zappoppopClose change.zappoppopClose', function() {
            var val = $(this).val();
            var activeEl = self.getActiveElement();
            if (activeEl) {
                activeEl.closeRedirectUrl = val;
                activeEl.close_redirect_url = val;
            }
        });
        var self = this;

        // Canonical Aliased Inputs Synchronization
        $('#prop-dropdown-options, #prop-select-options, #prop-select-fieldname, #prop-dropdown-fieldname').off('input change').on('input change', function() {
            var el = (typeof self.getActiveEl === 'function' ? self.getActiveEl() : (typeof self.getActiveElement === 'function' ? self.getActiveElement() : null));
            if (el && (el.type === 'dropdown' || el.type === 'select')) {
                var val = $('#prop-dropdown-options').length ? $('#prop-dropdown-options').val() : $('#prop-select-options').val();
                el.content = val;
                el.options = val ? val.split(',').map(function(s){ return s.trim(); }) : [];
                el.field_name = $('#prop-dropdown-fieldname').val() || $('#prop-select-fieldname').val() || 'dropdown_field';
                if (window.ZapPopPopBuilderCanvas) window.ZapPopPopBuilderCanvas.renderCanvas();
            }
        });

        $('#prop-step-label, #prop-step-canvas').off('input change').on('input change', function() {
            var el = (typeof self.getActiveEl === 'function' ? self.getActiveEl() : (typeof self.getActiveElement === 'function' ? self.getActiveElement() : null));
            if (el && (el.type === 'step' || el.type === 'step_btn')) {
                el.content = $('#prop-step-label').val();
                var cNum = parseInt($('#prop-step-canvas').val(), 10) || 2;
                el.goto_canvas = cNum;
                el.goto_screen = cNum;
                if (window.ZapPopPopBuilderCanvas) window.ZapPopPopBuilderCanvas.renderCanvas();
            }
        });

        this.bindAdvancedStylingInputs();
            var self = this;

            // Shape Presets & Controls
            $(document).on('change', '#prop-shape-preset', function() {
                var el = self.getActiveElement();
                if (!el) return;

        /* Title Element Hydration */
        if ((el.type || '').toLowerCase() === 'title') {
            var rawTitle = el.titleText || el.text || el.content || 'Headline Title';
            var rawTag   = el.headingTag || el.titleTag || el.tag || 'h2';
            var rawTrans = el.textTransform || el.text_transform || 'none';
            $('#prop-title-text').val(rawTitle);
            $('#prop-title-tag').val(rawTag);
            $('#prop-title-transform').val(rawTrans);
        }


        /* Phase 2: Hydrate Opacity & Hover */
        var opVal = (typeof el.opacity !== 'undefined') ? parseFloat(el.opacity) : 1;
        if (opVal <= 1) opVal = Math.round(opVal * 100);
        $('#prop-opacity').val(opVal);
        $('#prop-opacity-val').text(opVal + '%');

        $('#prop-custom-class').val(el.customClass || el.custom_class || '');
        $('#prop-custom-id').val(el.customId || el.custom_id || '');

        var btnTypes = ['submit', 'link', 'step', 'pay', 'submit_btn', 'link_btn', 'step_btn', 'pay_btn', 'button'];
        var isButton = btnTypes.indexOf((el.type || '').toLowerCase()) >= 0;
        if (isButton) {
            $('#prop-btn-hover-section').show();
            $('#prop-btn-hover-bg').val(el.hoverBgColor || el.hover_bg_color || '');
            $('#prop-btn-hover-color').val(el.hoverTextColor || el.hover_text_color || '');
            $('#prop-btn-hover-border').val(el.hoverBorderColor || el.hover_border_color || '');
        } else {
            $('#prop-btn-hover-section').hide();
        }

                el.shapePreset = $(this).val();
                self.syncToCanvas();
            });

            $(document).on('input change', '#prop-shape-fill', function() {
                var el = self.getActiveElement();
                if (!el) return;

        /* Title Element Hydration */
        if ((el.type || '').toLowerCase() === 'title') {
            var rawTitle = el.titleText || el.text || el.content || 'Headline Title';
            var rawTag   = el.headingTag || el.titleTag || el.tag || 'h2';
            var rawTrans = el.textTransform || el.text_transform || 'none';
            $('#prop-title-text').val(rawTitle);
            $('#prop-title-tag').val(rawTag);
            $('#prop-title-transform').val(rawTrans);
        }


        /* Phase 2: Hydrate Opacity & Hover */
        var opVal = (typeof el.opacity !== 'undefined') ? parseFloat(el.opacity) : 1;
        if (opVal <= 1) opVal = Math.round(opVal * 100);
        $('#prop-opacity').val(opVal);
        $('#prop-opacity-val').text(opVal + '%');

        $('#prop-custom-class').val(el.customClass || el.custom_class || '');
        $('#prop-custom-id').val(el.customId || el.custom_id || '');

        var btnTypes = ['submit', 'link', 'step', 'pay', 'submit_btn', 'link_btn', 'step_btn', 'pay_btn', 'button'];
        var isButton = btnTypes.indexOf((el.type || '').toLowerCase()) >= 0;
        if (isButton) {
            $('#prop-btn-hover-section').show();
            $('#prop-btn-hover-bg').val(el.hoverBgColor || el.hover_bg_color || '');
            $('#prop-btn-hover-color').val(el.hoverTextColor || el.hover_text_color || '');
            $('#prop-btn-hover-border').val(el.hoverBorderColor || el.hover_border_color || '');
        } else {
            $('#prop-btn-hover-section').hide();
        }

                el.shapeFill = $(this).val();
                $('#prop-shape-fill-transparent-btn').removeClass('active');
                self.syncToCanvas();
            });

            $(document).on('click', '#prop-shape-fill-transparent-btn', function(e) {
                e.preventDefault();
                var el = self.getActiveElement();
                if (!el) return;

        /* Title Element Hydration */
        if ((el.type || '').toLowerCase() === 'title') {
            var rawTitle = el.titleText || el.text || el.content || 'Headline Title';
            var rawTag   = el.headingTag || el.titleTag || el.tag || 'h2';
            var rawTrans = el.textTransform || el.text_transform || 'none';
            $('#prop-title-text').val(rawTitle);
            $('#prop-title-tag').val(rawTag);
            $('#prop-title-transform').val(rawTrans);
        }


        /* Phase 2: Hydrate Opacity & Hover */
        var opVal = (typeof el.opacity !== 'undefined') ? parseFloat(el.opacity) : 1;
        if (opVal <= 1) opVal = Math.round(opVal * 100);
        $('#prop-opacity').val(opVal);
        $('#prop-opacity-val').text(opVal + '%');

        $('#prop-custom-class').val(el.customClass || el.custom_class || '');
        $('#prop-custom-id').val(el.customId || el.custom_id || '');

        var btnTypes = ['submit', 'link', 'step', 'pay', 'submit_btn', 'link_btn', 'step_btn', 'pay_btn', 'button'];
        var isButton = btnTypes.indexOf((el.type || '').toLowerCase()) >= 0;
        if (isButton) {
            $('#prop-btn-hover-section').show();
            $('#prop-btn-hover-bg').val(el.hoverBgColor || el.hover_bg_color || '');
            $('#prop-btn-hover-color').val(el.hoverTextColor || el.hover_text_color || '');
            $('#prop-btn-hover-border').val(el.hoverBorderColor || el.hover_border_color || '');
        } else {
            $('#prop-btn-hover-section').hide();
        }

                el.shapeFill = 'transparent';
                $(this).addClass('active');
                self.syncToCanvas();
            });

            $(document).on('input change', '#prop-shape-stroke', function() {
                var el = self.getActiveElement();
                if (!el) return;

        /* Title Element Hydration */
        if ((el.type || '').toLowerCase() === 'title') {
            var rawTitle = el.titleText || el.text || el.content || 'Headline Title';
            var rawTag   = el.headingTag || el.titleTag || el.tag || 'h2';
            var rawTrans = el.textTransform || el.text_transform || 'none';
            $('#prop-title-text').val(rawTitle);
            $('#prop-title-tag').val(rawTag);
            $('#prop-title-transform').val(rawTrans);
        }


        /* Phase 2: Hydrate Opacity & Hover */
        var opVal = (typeof el.opacity !== 'undefined') ? parseFloat(el.opacity) : 1;
        if (opVal <= 1) opVal = Math.round(opVal * 100);
        $('#prop-opacity').val(opVal);
        $('#prop-opacity-val').text(opVal + '%');

        $('#prop-custom-class').val(el.customClass || el.custom_class || '');
        $('#prop-custom-id').val(el.customId || el.custom_id || '');

        var btnTypes = ['submit', 'link', 'step', 'pay', 'submit_btn', 'link_btn', 'step_btn', 'pay_btn', 'button'];
        var isButton = btnTypes.indexOf((el.type || '').toLowerCase()) >= 0;
        if (isButton) {
            $('#prop-btn-hover-section').show();
            $('#prop-btn-hover-bg').val(el.hoverBgColor || el.hover_bg_color || '');
            $('#prop-btn-hover-color').val(el.hoverTextColor || el.hover_text_color || '');
            $('#prop-btn-hover-border').val(el.hoverBorderColor || el.hover_border_color || '');
        } else {
            $('#prop-btn-hover-section').hide();
        }

                el.shapeStroke = $(this).val();
                $('#prop-shape-stroke-transparent-btn').removeClass('active');
                self.syncToCanvas();
            });

            $(document).on('click', '#prop-shape-stroke-transparent-btn', function(e) {
                e.preventDefault();
                var el = self.getActiveElement();
                if (!el) return;

        /* Title Element Hydration */
        if ((el.type || '').toLowerCase() === 'title') {
            var rawTitle = el.titleText || el.text || el.content || 'Headline Title';
            var rawTag   = el.headingTag || el.titleTag || el.tag || 'h2';
            var rawTrans = el.textTransform || el.text_transform || 'none';
            $('#prop-title-text').val(rawTitle);
            $('#prop-title-tag').val(rawTag);
            $('#prop-title-transform').val(rawTrans);
        }


        /* Phase 2: Hydrate Opacity & Hover */
        var opVal = (typeof el.opacity !== 'undefined') ? parseFloat(el.opacity) : 1;
        if (opVal <= 1) opVal = Math.round(opVal * 100);
        $('#prop-opacity').val(opVal);
        $('#prop-opacity-val').text(opVal + '%');

        $('#prop-custom-class').val(el.customClass || el.custom_class || '');
        $('#prop-custom-id').val(el.customId || el.custom_id || '');

        var btnTypes = ['submit', 'link', 'step', 'pay', 'submit_btn', 'link_btn', 'step_btn', 'pay_btn', 'button'];
        var isButton = btnTypes.indexOf((el.type || '').toLowerCase()) >= 0;
        if (isButton) {
            $('#prop-btn-hover-section').show();
            $('#prop-btn-hover-bg').val(el.hoverBgColor || el.hover_bg_color || '');
            $('#prop-btn-hover-color').val(el.hoverTextColor || el.hover_text_color || '');
            $('#prop-btn-hover-border').val(el.hoverBorderColor || el.hover_border_color || '');
        } else {
            $('#prop-btn-hover-section').hide();
        }

                el.shapeStroke = 'transparent';
                $(this).addClass('active');
                self.syncToCanvas();
            });

            $(document).on('input change', '#prop-shape-stroke-width', function() {
                var el = self.getActiveElement();
                if (!el) return;

        /* Title Element Hydration */
        if ((el.type || '').toLowerCase() === 'title') {
            var rawTitle = el.titleText || el.text || el.content || 'Headline Title';
            var rawTag   = el.headingTag || el.titleTag || el.tag || 'h2';
            var rawTrans = el.textTransform || el.text_transform || 'none';
            $('#prop-title-text').val(rawTitle);
            $('#prop-title-tag').val(rawTag);
            $('#prop-title-transform').val(rawTrans);
        }


        /* Phase 2: Hydrate Opacity & Hover */
        var opVal = (typeof el.opacity !== 'undefined') ? parseFloat(el.opacity) : 1;
        if (opVal <= 1) opVal = Math.round(opVal * 100);
        $('#prop-opacity').val(opVal);
        $('#prop-opacity-val').text(opVal + '%');

        $('#prop-custom-class').val(el.customClass || el.custom_class || '');
        $('#prop-custom-id').val(el.customId || el.custom_id || '');

        var btnTypes = ['submit', 'link', 'step', 'pay', 'submit_btn', 'link_btn', 'step_btn', 'pay_btn', 'button'];
        var isButton = btnTypes.indexOf((el.type || '').toLowerCase()) >= 0;
        if (isButton) {
            $('#prop-btn-hover-section').show();
            $('#prop-btn-hover-bg').val(el.hoverBgColor || el.hover_bg_color || '');
            $('#prop-btn-hover-color').val(el.hoverTextColor || el.hover_text_color || '');
            $('#prop-btn-hover-border').val(el.hoverBorderColor || el.hover_border_color || '');
        } else {
            $('#prop-btn-hover-section').hide();
        }

                el.shapeStrokeWidth = parseInt($(this).val(), 10) || 0;
                self.syncToCanvas();
            });

            $(document).on('input change', '#prop-shape-rotate', function() {
                var el = self.getActiveElement();
                if (!el) return;

        /* Title Element Hydration */
        if ((el.type || '').toLowerCase() === 'title') {
            var rawTitle = el.titleText || el.text || el.content || 'Headline Title';
            var rawTag   = el.headingTag || el.titleTag || el.tag || 'h2';
            var rawTrans = el.textTransform || el.text_transform || 'none';
            $('#prop-title-text').val(rawTitle);
            $('#prop-title-tag').val(rawTag);
            $('#prop-title-transform').val(rawTrans);
        }


        /* Phase 2: Hydrate Opacity & Hover */
        var opVal = (typeof el.opacity !== 'undefined') ? parseFloat(el.opacity) : 1;
        if (opVal <= 1) opVal = Math.round(opVal * 100);
        $('#prop-opacity').val(opVal);
        $('#prop-opacity-val').text(opVal + '%');

        $('#prop-custom-class').val(el.customClass || el.custom_class || '');
        $('#prop-custom-id').val(el.customId || el.custom_id || '');

        var btnTypes = ['submit', 'link', 'step', 'pay', 'submit_btn', 'link_btn', 'step_btn', 'pay_btn', 'button'];
        var isButton = btnTypes.indexOf((el.type || '').toLowerCase()) >= 0;
        if (isButton) {
            $('#prop-btn-hover-section').show();
            $('#prop-btn-hover-bg').val(el.hoverBgColor || el.hover_bg_color || '');
            $('#prop-btn-hover-color').val(el.hoverTextColor || el.hover_text_color || '');
            $('#prop-btn-hover-border').val(el.hoverBorderColor || el.hover_border_color || '');
        } else {
            $('#prop-btn-hover-section').hide();
        }

                el.shapeRotate = parseInt($(this).val(), 10) || 0;
                self.syncToCanvas();
            });

            // Content & Text
            $(document).on('input', '#prop-content, #prop-title-text, #prop-paragraph-text, #prop-textfield-placeholder', function() {
                var el = self.getActiveElement();
                if (!el) return;

        /* Title Element Hydration */
        if ((el.type || '').toLowerCase() === 'title') {
            var rawTitle = el.titleText || el.text || el.content || 'Headline Title';
            var rawTag   = el.headingTag || el.titleTag || el.tag || 'h2';
            var rawTrans = el.textTransform || el.text_transform || 'none';
            $('#prop-title-text').val(rawTitle);
            $('#prop-title-tag').val(rawTag);
            $('#prop-title-transform').val(rawTrans);
        }


        /* Phase 2: Hydrate Opacity & Hover */
        var opVal = (typeof el.opacity !== 'undefined') ? parseFloat(el.opacity) : 1;
        if (opVal <= 1) opVal = Math.round(opVal * 100);
        $('#prop-opacity').val(opVal);
        $('#prop-opacity-val').text(opVal + '%');

        $('#prop-custom-class').val(el.customClass || el.custom_class || '');
        $('#prop-custom-id').val(el.customId || el.custom_id || '');

        var btnTypes = ['submit', 'link', 'step', 'pay', 'submit_btn', 'link_btn', 'step_btn', 'pay_btn', 'button'];
        var isButton = btnTypes.indexOf((el.type || '').toLowerCase()) >= 0;
        if (isButton) {
            $('#prop-btn-hover-section').show();
            $('#prop-btn-hover-bg').val(el.hoverBgColor || el.hover_bg_color || '');
            $('#prop-btn-hover-color').val(el.hoverTextColor || el.hover_text_color || '');
            $('#prop-btn-hover-border').val(el.hoverBorderColor || el.hover_border_color || '');
        } else {
            $('#prop-btn-hover-section').hide();
        }

                el.content = $(this).val();
                self.syncToCanvas();
            });

            // Layer Name
            $(document).on('input', '#prop-name', function() {
                var el = self.getActiveElement();
                if (!el) return;

        /* Title Element Hydration */
        if ((el.type || '').toLowerCase() === 'title') {
            var rawTitle = el.titleText || el.text || el.content || 'Headline Title';
            var rawTag   = el.headingTag || el.titleTag || el.tag || 'h2';
            var rawTrans = el.textTransform || el.text_transform || 'none';
            $('#prop-title-text').val(rawTitle);
            $('#prop-title-tag').val(rawTag);
            $('#prop-title-transform').val(rawTrans);
        }


        /* Phase 2: Hydrate Opacity & Hover */
        var opVal = (typeof el.opacity !== 'undefined') ? parseFloat(el.opacity) : 1;
        if (opVal <= 1) opVal = Math.round(opVal * 100);
        $('#prop-opacity').val(opVal);
        $('#prop-opacity-val').text(opVal + '%');

        $('#prop-custom-class').val(el.customClass || el.custom_class || '');
        $('#prop-custom-id').val(el.customId || el.custom_id || '');

        var btnTypes = ['submit', 'link', 'step', 'pay', 'submit_btn', 'link_btn', 'step_btn', 'pay_btn', 'button'];
        var isButton = btnTypes.indexOf((el.type || '').toLowerCase()) >= 0;
        if (isButton) {
            $('#prop-btn-hover-section').show();
            $('#prop-btn-hover-bg').val(el.hoverBgColor || el.hover_bg_color || '');
            $('#prop-btn-hover-color').val(el.hoverTextColor || el.hover_text_color || '');
            $('#prop-btn-hover-border').val(el.hoverBorderColor || el.hover_border_color || '');
        } else {
            $('#prop-btn-hover-section').hide();
        }

                el.name = $(this).val();
                if (window.ZapPopPopBuilderLayers) window.ZapPopPopBuilderLayers.renderLayers();
            });

            // Dimensions and Coordinates
            $(document).on('input change', '#prop-left, #prop-top, #prop-width, #prop-height', function() {
                var el = self.getActiveElement();
                if (!el) return;

        /* Title Element Hydration */
        if ((el.type || '').toLowerCase() === 'title') {
            var rawTitle = el.titleText || el.text || el.content || 'Headline Title';
            var rawTag   = el.headingTag || el.titleTag || el.tag || 'h2';
            var rawTrans = el.textTransform || el.text_transform || 'none';
            $('#prop-title-text').val(rawTitle);
            $('#prop-title-tag').val(rawTag);
            $('#prop-title-transform').val(rawTrans);
        }


        /* Phase 2: Hydrate Opacity & Hover */
        var opVal = (typeof el.opacity !== 'undefined') ? parseFloat(el.opacity) : 1;
        if (opVal <= 1) opVal = Math.round(opVal * 100);
        $('#prop-opacity').val(opVal);
        $('#prop-opacity-val').text(opVal + '%');

        $('#prop-custom-class').val(el.customClass || el.custom_class || '');
        $('#prop-custom-id').val(el.customId || el.custom_id || '');

        var btnTypes = ['submit', 'link', 'step', 'pay', 'submit_btn', 'link_btn', 'step_btn', 'pay_btn', 'button'];
        var isButton = btnTypes.indexOf((el.type || '').toLowerCase()) >= 0;
        if (isButton) {
            $('#prop-btn-hover-section').show();
            $('#prop-btn-hover-bg').val(el.hoverBgColor || el.hover_bg_color || '');
            $('#prop-btn-hover-color').val(el.hoverTextColor || el.hover_text_color || '');
            $('#prop-btn-hover-border').val(el.hoverBorderColor || el.hover_border_color || '');
        } else {
            $('#prop-btn-hover-section').hide();
        }

                el.left = parseInt($('#prop-left').val(), 10) || 0;
                el.top = parseInt($('#prop-top').val(), 10) || 0;
                el.width = parseInt($('#prop-width').val(), 10) || 50;
                el.height = parseInt($('#prop-height').val(), 10) || 30;
                self.syncToCanvas();
            });

            // Typography & Spacing Inputs
            $(document).on('input change', '#prop-font-size', function() {
                var el = self.getActiveElement();
                if (!el) return;

        /* Title Element Hydration */
        if ((el.type || '').toLowerCase() === 'title') {
            var rawTitle = el.titleText || el.text || el.content || 'Headline Title';
            var rawTag   = el.headingTag || el.titleTag || el.tag || 'h2';
            var rawTrans = el.textTransform || el.text_transform || 'none';
            $('#prop-title-text').val(rawTitle);
            $('#prop-title-tag').val(rawTag);
            $('#prop-title-transform').val(rawTrans);
        }


        /* Phase 2: Hydrate Opacity & Hover */
        var opVal = (typeof el.opacity !== 'undefined') ? parseFloat(el.opacity) : 1;
        if (opVal <= 1) opVal = Math.round(opVal * 100);
        $('#prop-opacity').val(opVal);
        $('#prop-opacity-val').text(opVal + '%');

        $('#prop-custom-class').val(el.customClass || el.custom_class || '');
        $('#prop-custom-id').val(el.customId || el.custom_id || '');

        var btnTypes = ['submit', 'link', 'step', 'pay', 'submit_btn', 'link_btn', 'step_btn', 'pay_btn', 'button'];
        var isButton = btnTypes.indexOf((el.type || '').toLowerCase()) >= 0;
        if (isButton) {
            $('#prop-btn-hover-section').show();
            $('#prop-btn-hover-bg').val(el.hoverBgColor || el.hover_bg_color || '');
            $('#prop-btn-hover-color').val(el.hoverTextColor || el.hover_text_color || '');
            $('#prop-btn-hover-border').val(el.hoverBorderColor || el.hover_border_color || '');
        } else {
            $('#prop-btn-hover-section').hide();
        }

                el.fontSize = parseInt($(this).val(), 10) || 14;
                self.syncToCanvas();
            });

            $(document).on('change', '#prop-font-weight', function() {
                var el = self.getActiveElement();
                if (!el) return;

        /* Title Element Hydration */
        if ((el.type || '').toLowerCase() === 'title') {
            var rawTitle = el.titleText || el.text || el.content || 'Headline Title';
            var rawTag   = el.headingTag || el.titleTag || el.tag || 'h2';
            var rawTrans = el.textTransform || el.text_transform || 'none';
            $('#prop-title-text').val(rawTitle);
            $('#prop-title-tag').val(rawTag);
            $('#prop-title-transform').val(rawTrans);
        }


        /* Phase 2: Hydrate Opacity & Hover */
        var opVal = (typeof el.opacity !== 'undefined') ? parseFloat(el.opacity) : 1;
        if (opVal <= 1) opVal = Math.round(opVal * 100);
        $('#prop-opacity').val(opVal);
        $('#prop-opacity-val').text(opVal + '%');

        $('#prop-custom-class').val(el.customClass || el.custom_class || '');
        $('#prop-custom-id').val(el.customId || el.custom_id || '');

        var btnTypes = ['submit', 'link', 'step', 'pay', 'submit_btn', 'link_btn', 'step_btn', 'pay_btn', 'button'];
        var isButton = btnTypes.indexOf((el.type || '').toLowerCase()) >= 0;
        if (isButton) {
            $('#prop-btn-hover-section').show();
            $('#prop-btn-hover-bg').val(el.hoverBgColor || el.hover_bg_color || '');
            $('#prop-btn-hover-color').val(el.hoverTextColor || el.hover_text_color || '');
            $('#prop-btn-hover-border').val(el.hoverBorderColor || el.hover_border_color || '');
        } else {
            $('#prop-btn-hover-section').hide();
        }

                el.fontWeight = $(this).val();
                self.syncToCanvas();
            });

            $(document).on('input change', '#prop-line-height', function() {
                var el = self.getActiveElement();
                if (!el) return;

        /* Title Element Hydration */
        if ((el.type || '').toLowerCase() === 'title') {
            var rawTitle = el.titleText || el.text || el.content || 'Headline Title';
            var rawTag   = el.headingTag || el.titleTag || el.tag || 'h2';
            var rawTrans = el.textTransform || el.text_transform || 'none';
            $('#prop-title-text').val(rawTitle);
            $('#prop-title-tag').val(rawTag);
            $('#prop-title-transform').val(rawTrans);
        }


        /* Phase 2: Hydrate Opacity & Hover */
        var opVal = (typeof el.opacity !== 'undefined') ? parseFloat(el.opacity) : 1;
        if (opVal <= 1) opVal = Math.round(opVal * 100);
        $('#prop-opacity').val(opVal);
        $('#prop-opacity-val').text(opVal + '%');

        $('#prop-custom-class').val(el.customClass || el.custom_class || '');
        $('#prop-custom-id').val(el.customId || el.custom_id || '');

        var btnTypes = ['submit', 'link', 'step', 'pay', 'submit_btn', 'link_btn', 'step_btn', 'pay_btn', 'button'];
        var isButton = btnTypes.indexOf((el.type || '').toLowerCase()) >= 0;
        if (isButton) {
            $('#prop-btn-hover-section').show();
            $('#prop-btn-hover-bg').val(el.hoverBgColor || el.hover_bg_color || '');
            $('#prop-btn-hover-color').val(el.hoverTextColor || el.hover_text_color || '');
            $('#prop-btn-hover-border').val(el.hoverBorderColor || el.hover_border_color || '');
        } else {
            $('#prop-btn-hover-section').hide();
        }

                el.lineHeight = parseFloat($(this).val()) || 1.4;
                self.syncToCanvas();
            });

            $(document).on('input change', '#prop-letter-spacing', function() {
                var el = self.getActiveElement();
                if (!el) return;

        /* Title Element Hydration */
        if ((el.type || '').toLowerCase() === 'title') {
            var rawTitle = el.titleText || el.text || el.content || 'Headline Title';
            var rawTag   = el.headingTag || el.titleTag || el.tag || 'h2';
            var rawTrans = el.textTransform || el.text_transform || 'none';
            $('#prop-title-text').val(rawTitle);
            $('#prop-title-tag').val(rawTag);
            $('#prop-title-transform').val(rawTrans);
        }


        /* Phase 2: Hydrate Opacity & Hover */
        var opVal = (typeof el.opacity !== 'undefined') ? parseFloat(el.opacity) : 1;
        if (opVal <= 1) opVal = Math.round(opVal * 100);
        $('#prop-opacity').val(opVal);
        $('#prop-opacity-val').text(opVal + '%');

        $('#prop-custom-class').val(el.customClass || el.custom_class || '');
        $('#prop-custom-id').val(el.customId || el.custom_id || '');

        var btnTypes = ['submit', 'link', 'step', 'pay', 'submit_btn', 'link_btn', 'step_btn', 'pay_btn', 'button'];
        var isButton = btnTypes.indexOf((el.type || '').toLowerCase()) >= 0;
        if (isButton) {
            $('#prop-btn-hover-section').show();
            $('#prop-btn-hover-bg').val(el.hoverBgColor || el.hover_bg_color || '');
            $('#prop-btn-hover-color').val(el.hoverTextColor || el.hover_text_color || '');
            $('#prop-btn-hover-border').val(el.hoverBorderColor || el.hover_border_color || '');
        } else {
            $('#prop-btn-hover-section').hide();
        }

                el.letterSpacing = parseFloat($(this).val()) || 0;
                self.syncToCanvas();
            });

            $(document).on('change', '#prop-text-align', function() {
                var el = self.getActiveElement();
                if (!el) return;

        /* Title Element Hydration */
        if ((el.type || '').toLowerCase() === 'title') {
            var rawTitle = el.titleText || el.text || el.content || 'Headline Title';
            var rawTag   = el.headingTag || el.titleTag || el.tag || 'h2';
            var rawTrans = el.textTransform || el.text_transform || 'none';
            $('#prop-title-text').val(rawTitle);
            $('#prop-title-tag').val(rawTag);
            $('#prop-title-transform').val(rawTrans);
        }


        /* Phase 2: Hydrate Opacity & Hover */
        var opVal = (typeof el.opacity !== 'undefined') ? parseFloat(el.opacity) : 1;
        if (opVal <= 1) opVal = Math.round(opVal * 100);
        $('#prop-opacity').val(opVal);
        $('#prop-opacity-val').text(opVal + '%');

        $('#prop-custom-class').val(el.customClass || el.custom_class || '');
        $('#prop-custom-id').val(el.customId || el.custom_id || '');

        var btnTypes = ['submit', 'link', 'step', 'pay', 'submit_btn', 'link_btn', 'step_btn', 'pay_btn', 'button'];
        var isButton = btnTypes.indexOf((el.type || '').toLowerCase()) >= 0;
        if (isButton) {
            $('#prop-btn-hover-section').show();
            $('#prop-btn-hover-bg').val(el.hoverBgColor || el.hover_bg_color || '');
            $('#prop-btn-hover-color').val(el.hoverTextColor || el.hover_text_color || '');
            $('#prop-btn-hover-border').val(el.hoverBorderColor || el.hover_border_color || '');
        } else {
            $('#prop-btn-hover-section').hide();
        }

                el.textAlign = $(this).val();
                self.syncToCanvas();
            });

            // Element Padding Input
            $(document).on('input change', '#prop-padding', function() {
                var el = self.getActiveElement();
                if (!el) return;

        /* Title Element Hydration */
        if ((el.type || '').toLowerCase() === 'title') {
            var rawTitle = el.titleText || el.text || el.content || 'Headline Title';
            var rawTag   = el.headingTag || el.titleTag || el.tag || 'h2';
            var rawTrans = el.textTransform || el.text_transform || 'none';
            $('#prop-title-text').val(rawTitle);
            $('#prop-title-tag').val(rawTag);
            $('#prop-title-transform').val(rawTrans);
        }


        /* Phase 2: Hydrate Opacity & Hover */
        var opVal = (typeof el.opacity !== 'undefined') ? parseFloat(el.opacity) : 1;
        if (opVal <= 1) opVal = Math.round(opVal * 100);
        $('#prop-opacity').val(opVal);
        $('#prop-opacity-val').text(opVal + '%');

        $('#prop-custom-class').val(el.customClass || el.custom_class || '');
        $('#prop-custom-id').val(el.customId || el.custom_id || '');

        var btnTypes = ['submit', 'link', 'step', 'pay', 'submit_btn', 'link_btn', 'step_btn', 'pay_btn', 'button'];
        var isButton = btnTypes.indexOf((el.type || '').toLowerCase()) >= 0;
        if (isButton) {
            $('#prop-btn-hover-section').show();
            $('#prop-btn-hover-bg').val(el.hoverBgColor || el.hover_bg_color || '');
            $('#prop-btn-hover-color').val(el.hoverTextColor || el.hover_text_color || '');
            $('#prop-btn-hover-border').val(el.hoverBorderColor || el.hover_border_color || '');
        } else {
            $('#prop-btn-hover-section').hide();
        }

                el.padding = Math.max(0, parseInt($(this).val(), 10) || 0);
                self.syncToCanvas();
            });

            // Animation Selector & Play Button
            
            $('#prop-padding-top, #prop-padding-right, #prop-padding-bottom, #prop-padding-left').off('input.livePad change.livePad').on('input.livePad change.livePad', function() {
                var el = (typeof self.getActiveElement === 'function') ? self.getActiveElement() : ((typeof getActiveEl === 'function') ? getActiveEl() : null);
                if (!el && window.ZapPopPopBuilderCore && window.ZapPopPopBuilderCore.state.activeId) {
                    var cur = window.ZapPopPopBuilderCore.state.currentCanvas || 1;
                    var list = window.ZapPopPopBuilderCore.state.canvases[cur] || [];
                    el = list.find(function(item) { return String(item.id) === String(window.ZapPopPopBuilderCore.state.activeId); });
                }
                if (!el) return;

        /* Title Element Hydration */
        if ((el.type || '').toLowerCase() === 'title') {
            var rawTitle = el.titleText || el.text || el.content || 'Headline Title';
            var rawTag   = el.headingTag || el.titleTag || el.tag || 'h2';
            var rawTrans = el.textTransform || el.text_transform || 'none';
            $('#prop-title-text').val(rawTitle);
            $('#prop-title-tag').val(rawTag);
            $('#prop-title-transform').val(rawTrans);
        }


        /* Phase 2: Hydrate Opacity & Hover */
        var opVal = (typeof el.opacity !== 'undefined') ? parseFloat(el.opacity) : 1;
        if (opVal <= 1) opVal = Math.round(opVal * 100);
        $('#prop-opacity').val(opVal);
        $('#prop-opacity-val').text(opVal + '%');

        $('#prop-custom-class').val(el.customClass || el.custom_class || '');
        $('#prop-custom-id').val(el.customId || el.custom_id || '');

        var btnTypes = ['submit', 'link', 'step', 'pay', 'submit_btn', 'link_btn', 'step_btn', 'pay_btn', 'button'];
        var isButton = btnTypes.indexOf((el.type || '').toLowerCase()) >= 0;
        if (isButton) {
            $('#prop-btn-hover-section').show();
            $('#prop-btn-hover-bg').val(el.hoverBgColor || el.hover_bg_color || '');
            $('#prop-btn-hover-color').val(el.hoverTextColor || el.hover_text_color || '');
            $('#prop-btn-hover-border').val(el.hoverBorderColor || el.hover_border_color || '');
        } else {
            $('#prop-btn-hover-section').hide();
        }


                var dir = $(this).attr('data-dir') || $(this).attr('id').replace('prop-padding-', '');
                var val = Math.max(0, parseInt($(this).val(), 10) || 0);

                if (dir === 'top') { el.paddingTop = val; el.padding_top = val; }
                if (dir === 'right') { el.paddingRight = val; el.padding_right = val; }
                if (dir === 'bottom') { el.paddingBottom = val; el.padding_bottom = val; }
                if (dir === 'left') { el.paddingLeft = val; el.padding_left = val; }
                el.padding = el.paddingTop || 0;
                $('#prop-padding').val(el.padding);

                var $target = $('#el-' + el.id);
                if ($target.length) {
                    var isControl = ['step_btn', 'submit', 'pay', 'link_btn', 'textfield', 'email', 'number', 'date', 'select'].indexOf(el.type) !== -1;
                    var $inner = $target.find('input, button, select, textarea, a, .zappoppop-btn').first();
                    if (isControl && $inner.length) {
                        $target.css({ 'padding': '0px', 'box-sizing': 'border-box' });
                        $inner.css('padding-' + dir, val + 'px').css('box-sizing', 'border-box');
                    } else {
                        $target.css('padding-' + dir, val + 'px').css('box-sizing', 'border-box');
                    }
                }
                if (window.ZapPopPopBuilderCore && typeof window.ZapPopPopBuilderCore.pushHistory === 'function') {
                    window.ZapPopPopBuilderCore.pushHistory();
                }
            });

            $(document).on('change', '#prop-animation', function() {
                var el = self.getActiveElement();
                if (!el) return;

        /* Title Element Hydration */
        if ((el.type || '').toLowerCase() === 'title') {
            var rawTitle = el.titleText || el.text || el.content || 'Headline Title';
            var rawTag   = el.headingTag || el.titleTag || el.tag || 'h2';
            var rawTrans = el.textTransform || el.text_transform || 'none';
            $('#prop-title-text').val(rawTitle);
            $('#prop-title-tag').val(rawTag);
            $('#prop-title-transform').val(rawTrans);
        }


        /* Phase 2: Hydrate Opacity & Hover */
        var opVal = (typeof el.opacity !== 'undefined') ? parseFloat(el.opacity) : 1;
        if (opVal <= 1) opVal = Math.round(opVal * 100);
        $('#prop-opacity').val(opVal);
        $('#prop-opacity-val').text(opVal + '%');

        $('#prop-custom-class').val(el.customClass || el.custom_class || '');
        $('#prop-custom-id').val(el.customId || el.custom_id || '');

        var btnTypes = ['submit', 'link', 'step', 'pay', 'submit_btn', 'link_btn', 'step_btn', 'pay_btn', 'button'];
        var isButton = btnTypes.indexOf((el.type || '').toLowerCase()) >= 0;
        if (isButton) {
            $('#prop-btn-hover-section').show();
            $('#prop-btn-hover-bg').val(el.hoverBgColor || el.hover_bg_color || '');
            $('#prop-btn-hover-color').val(el.hoverTextColor || el.hover_text_color || '');
            $('#prop-btn-hover-border').val(el.hoverBorderColor || el.hover_border_color || '');
        } else {
            $('#prop-btn-hover-section').hide();
        }

                el.animation = $(this).val();
                if (window.ZapPopPopBuilderCanvas) {
                    window.ZapPopPopBuilderCanvas.playElementAnimation(el.id, el.animation);
                }
                self.syncToCanvas();
            });

            $(document).on('change', '#prop-anim-disappearance', function() {
                var el = self.getActiveElement();
                if (!el) return;

        /* Title Element Hydration */
        if ((el.type || '').toLowerCase() === 'title') {
            var rawTitle = el.titleText || el.text || el.content || 'Headline Title';
            var rawTag   = el.headingTag || el.titleTag || el.tag || 'h2';
            var rawTrans = el.textTransform || el.text_transform || 'none';
            $('#prop-title-text').val(rawTitle);
            $('#prop-title-tag').val(rawTag);
            $('#prop-title-transform').val(rawTrans);
        }


        /* Phase 2: Hydrate Opacity & Hover */
        var opVal = (typeof el.opacity !== 'undefined') ? parseFloat(el.opacity) : 1;
        if (opVal <= 1) opVal = Math.round(opVal * 100);
        $('#prop-opacity').val(opVal);
        $('#prop-opacity-val').text(opVal + '%');

        $('#prop-custom-class').val(el.customClass || el.custom_class || '');
        $('#prop-custom-id').val(el.customId || el.custom_id || '');

        var btnTypes = ['submit', 'link', 'step', 'pay', 'submit_btn', 'link_btn', 'step_btn', 'pay_btn', 'button'];
        var isButton = btnTypes.indexOf((el.type || '').toLowerCase()) >= 0;
        if (isButton) {
            $('#prop-btn-hover-section').show();
            $('#prop-btn-hover-bg').val(el.hoverBgColor || el.hover_bg_color || '');
            $('#prop-btn-hover-color').val(el.hoverTextColor || el.hover_text_color || '');
            $('#prop-btn-hover-border').val(el.hoverBorderColor || el.hover_border_color || '');
        } else {
            $('#prop-btn-hover-section').hide();
        }

                el.anim_disappearance = $(this).val();
                el.exitAnimation = el.anim_disappearance;
                if (window.ZapPopPopBuilderCore) window.ZapPopPopBuilderCore.pushHistory();
            });

            $(document).on('click', '#prop-anim-play-btn', function(e) {
                e.preventDefault();
                var el = self.getActiveElement();
                if (!el) return;

        /* Title Element Hydration */
        if ((el.type || '').toLowerCase() === 'title') {
            var rawTitle = el.titleText || el.text || el.content || 'Headline Title';
            var rawTag   = el.headingTag || el.titleTag || el.tag || 'h2';
            var rawTrans = el.textTransform || el.text_transform || 'none';
            $('#prop-title-text').val(rawTitle);
            $('#prop-title-tag').val(rawTag);
            $('#prop-title-transform').val(rawTrans);
        }


        /* Phase 2: Hydrate Opacity & Hover */
        var opVal = (typeof el.opacity !== 'undefined') ? parseFloat(el.opacity) : 1;
        if (opVal <= 1) opVal = Math.round(opVal * 100);
        $('#prop-opacity').val(opVal);
        $('#prop-opacity-val').text(opVal + '%');

        $('#prop-custom-class').val(el.customClass || el.custom_class || '');
        $('#prop-custom-id').val(el.customId || el.custom_id || '');

        var btnTypes = ['submit', 'link', 'step', 'pay', 'submit_btn', 'link_btn', 'step_btn', 'pay_btn', 'button'];
        var isButton = btnTypes.indexOf((el.type || '').toLowerCase()) >= 0;
        if (isButton) {
            $('#prop-btn-hover-section').show();
            $('#prop-btn-hover-bg').val(el.hoverBgColor || el.hover_bg_color || '');
            $('#prop-btn-hover-color').val(el.hoverTextColor || el.hover_text_color || '');
            $('#prop-btn-hover-border').val(el.hoverBorderColor || el.hover_border_color || '');
        } else {
            $('#prop-btn-hover-section').hide();
        }

                var anim = $('#prop-animation').val() || el.animation || 'fade';
                if (window.ZapPopPopBuilderCanvas) {
                    window.ZapPopPopBuilderCanvas.playElementAnimation(el.id, anim);
                }
            });

            // Colors
            $(document).on('input change', '#prop-color', function() {
                var el = self.getActiveElement();
                if (!el) return;

        /* Title Element Hydration */
        if ((el.type || '').toLowerCase() === 'title') {
            var rawTitle = el.titleText || el.text || el.content || 'Headline Title';
            var rawTag   = el.headingTag || el.titleTag || el.tag || 'h2';
            var rawTrans = el.textTransform || el.text_transform || 'none';
            $('#prop-title-text').val(rawTitle);
            $('#prop-title-tag').val(rawTag);
            $('#prop-title-transform').val(rawTrans);
        }


        /* Phase 2: Hydrate Opacity & Hover */
        var opVal = (typeof el.opacity !== 'undefined') ? parseFloat(el.opacity) : 1;
        if (opVal <= 1) opVal = Math.round(opVal * 100);
        $('#prop-opacity').val(opVal);
        $('#prop-opacity-val').text(opVal + '%');

        $('#prop-custom-class').val(el.customClass || el.custom_class || '');
        $('#prop-custom-id').val(el.customId || el.custom_id || '');

        var btnTypes = ['submit', 'link', 'step', 'pay', 'submit_btn', 'link_btn', 'step_btn', 'pay_btn', 'button'];
        var isButton = btnTypes.indexOf((el.type || '').toLowerCase()) >= 0;
        if (isButton) {
            $('#prop-btn-hover-section').show();
            $('#prop-btn-hover-bg').val(el.hoverBgColor || el.hover_bg_color || '');
            $('#prop-btn-hover-color').val(el.hoverTextColor || el.hover_text_color || '');
            $('#prop-btn-hover-border').val(el.hoverBorderColor || el.hover_border_color || '');
        } else {
            $('#prop-btn-hover-section').hide();
        }

                el.color = $(this).val();
                $('#prop-text-transparent-btn').removeClass('active');
                self.syncToCanvas();
            });

            $(document).on('click', '#prop-text-transparent-btn', function(e) {
                e.preventDefault();
                var el = self.getActiveElement();
                if (!el) return;

        /* Title Element Hydration */
        if ((el.type || '').toLowerCase() === 'title') {
            var rawTitle = el.titleText || el.text || el.content || 'Headline Title';
            var rawTag   = el.headingTag || el.titleTag || el.tag || 'h2';
            var rawTrans = el.textTransform || el.text_transform || 'none';
            $('#prop-title-text').val(rawTitle);
            $('#prop-title-tag').val(rawTag);
            $('#prop-title-transform').val(rawTrans);
        }


        /* Phase 2: Hydrate Opacity & Hover */
        var opVal = (typeof el.opacity !== 'undefined') ? parseFloat(el.opacity) : 1;
        if (opVal <= 1) opVal = Math.round(opVal * 100);
        $('#prop-opacity').val(opVal);
        $('#prop-opacity-val').text(opVal + '%');

        $('#prop-custom-class').val(el.customClass || el.custom_class || '');
        $('#prop-custom-id').val(el.customId || el.custom_id || '');

        var btnTypes = ['submit', 'link', 'step', 'pay', 'submit_btn', 'link_btn', 'step_btn', 'pay_btn', 'button'];
        var isButton = btnTypes.indexOf((el.type || '').toLowerCase()) >= 0;
        if (isButton) {
            $('#prop-btn-hover-section').show();
            $('#prop-btn-hover-bg').val(el.hoverBgColor || el.hover_bg_color || '');
            $('#prop-btn-hover-color').val(el.hoverTextColor || el.hover_text_color || '');
            $('#prop-btn-hover-border').val(el.hoverBorderColor || el.hover_border_color || '');
        } else {
            $('#prop-btn-hover-section').hide();
        }

                el.color = 'transparent';
                $(this).addClass('active');
                self.syncToCanvas();
            });

            $(document).on('input change', '#prop-bg-color', function() {
                var el = self.getActiveElement();
                if (!el) return;

        /* Title Element Hydration */
        if ((el.type || '').toLowerCase() === 'title') {
            var rawTitle = el.titleText || el.text || el.content || 'Headline Title';
            var rawTag   = el.headingTag || el.titleTag || el.tag || 'h2';
            var rawTrans = el.textTransform || el.text_transform || 'none';
            $('#prop-title-text').val(rawTitle);
            $('#prop-title-tag').val(rawTag);
            $('#prop-title-transform').val(rawTrans);
        }


        /* Phase 2: Hydrate Opacity & Hover */
        var opVal = (typeof el.opacity !== 'undefined') ? parseFloat(el.opacity) : 1;
        if (opVal <= 1) opVal = Math.round(opVal * 100);
        $('#prop-opacity').val(opVal);
        $('#prop-opacity-val').text(opVal + '%');

        $('#prop-custom-class').val(el.customClass || el.custom_class || '');
        $('#prop-custom-id').val(el.customId || el.custom_id || '');

        var btnTypes = ['submit', 'link', 'step', 'pay', 'submit_btn', 'link_btn', 'step_btn', 'pay_btn', 'button'];
        var isButton = btnTypes.indexOf((el.type || '').toLowerCase()) >= 0;
        if (isButton) {
            $('#prop-btn-hover-section').show();
            $('#prop-btn-hover-bg').val(el.hoverBgColor || el.hover_bg_color || '');
            $('#prop-btn-hover-color').val(el.hoverTextColor || el.hover_text_color || '');
            $('#prop-btn-hover-border').val(el.hoverBorderColor || el.hover_border_color || '');
        } else {
            $('#prop-btn-hover-section').hide();
        }

                el.bgColor = $(this).val();
                $('#prop-bg-transparent-btn').removeClass('active');
                self.syncToCanvas();
            });

            $(document).on('click', '#prop-bg-transparent-btn', function(e) {
                e.preventDefault();
                var el = self.getActiveElement();
                if (!el) return;

        /* Title Element Hydration */
        if ((el.type || '').toLowerCase() === 'title') {
            var rawTitle = el.titleText || el.text || el.content || 'Headline Title';
            var rawTag   = el.headingTag || el.titleTag || el.tag || 'h2';
            var rawTrans = el.textTransform || el.text_transform || 'none';
            $('#prop-title-text').val(rawTitle);
            $('#prop-title-tag').val(rawTag);
            $('#prop-title-transform').val(rawTrans);
        }


        /* Phase 2: Hydrate Opacity & Hover */
        var opVal = (typeof el.opacity !== 'undefined') ? parseFloat(el.opacity) : 1;
        if (opVal <= 1) opVal = Math.round(opVal * 100);
        $('#prop-opacity').val(opVal);
        $('#prop-opacity-val').text(opVal + '%');

        $('#prop-custom-class').val(el.customClass || el.custom_class || '');
        $('#prop-custom-id').val(el.customId || el.custom_id || '');

        var btnTypes = ['submit', 'link', 'step', 'pay', 'submit_btn', 'link_btn', 'step_btn', 'pay_btn', 'button'];
        var isButton = btnTypes.indexOf((el.type || '').toLowerCase()) >= 0;
        if (isButton) {
            $('#prop-btn-hover-section').show();
            $('#prop-btn-hover-bg').val(el.hoverBgColor || el.hover_bg_color || '');
            $('#prop-btn-hover-color').val(el.hoverTextColor || el.hover_text_color || '');
            $('#prop-btn-hover-border').val(el.hoverBorderColor || el.hover_border_color || '');
        } else {
            $('#prop-btn-hover-section').hide();
        }

                el.bgColor = 'transparent';
                $(this).addClass('active');
                self.syncToCanvas();
            });

            // Target Canvas Navigation
            $(document).on('change', '#prop-goto-canvas', function() {
                var el = self.getActiveElement();
                if (!el) return;

        /* Title Element Hydration */
        if ((el.type || '').toLowerCase() === 'title') {
            var rawTitle = el.titleText || el.text || el.content || 'Headline Title';
            var rawTag   = el.headingTag || el.titleTag || el.tag || 'h2';
            var rawTrans = el.textTransform || el.text_transform || 'none';
            $('#prop-title-text').val(rawTitle);
            $('#prop-title-tag').val(rawTag);
            $('#prop-title-transform').val(rawTrans);
        }


        /* Phase 2: Hydrate Opacity & Hover */
        var opVal = (typeof el.opacity !== 'undefined') ? parseFloat(el.opacity) : 1;
        if (opVal <= 1) opVal = Math.round(opVal * 100);
        $('#prop-opacity').val(opVal);
        $('#prop-opacity-val').text(opVal + '%');

        $('#prop-custom-class').val(el.customClass || el.custom_class || '');
        $('#prop-custom-id').val(el.customId || el.custom_id || '');

        var btnTypes = ['submit', 'link', 'step', 'pay', 'submit_btn', 'link_btn', 'step_btn', 'pay_btn', 'button'];
        var isButton = btnTypes.indexOf((el.type || '').toLowerCase()) >= 0;
        if (isButton) {
            $('#prop-btn-hover-section').show();
            $('#prop-btn-hover-bg').val(el.hoverBgColor || el.hover_bg_color || '');
            $('#prop-btn-hover-color').val(el.hoverTextColor || el.hover_text_color || '');
            $('#prop-btn-hover-border').val(el.hoverBorderColor || el.hover_border_color || '');
        } else {
            $('#prop-btn-hover-section').hide();
        }

                var target = parseInt($(this).val(), 10) || 2;
                el.goto_canvas = target;
                el.goto_screen = target;
                self.syncToCanvas();
            });
        }
    };
})(jQuery);

/* === ZAPPOPPOP_SELF_CONTAINED_5TAB_ENGINE_START === */
(function(window, $) {
    'use strict';

    var MEDIA_FAMILY_3_TABS = [
        'shape', 'rectangle', 'square',
        'image', 'video',
        'label', 'title', 'paragraph',
        'html', 'close', 'close_icon',
        'icon', 'fontawesome', 'progress',
        'button', 'link', 'link_btn', 'step_btn',
        'submit'
    ];

    var FORM_FAMILY_5_TABS = [
        'text', 'textfield', 'text_field',
        'email', 'number', 'phone', 'tel',
        'textarea', 'select', 'dropdown',
        'checkbox', 'checkboxes', 'radio', 'radios',
        'multiselect', 'imageselect', 'tile',
        'date', 'time', 'upload', 'file',
        'password', 'rating'
    ];

    function getCanonicalFamily(rawType) {
        var t = String(rawType || '').toLowerCase().trim();
        if (FORM_FAMILY_5_TABS.indexOf(t) !== -1) {
            return 'form';
        }
        return 'media';
    }

    function resolveActiveElement(idOrEl) {
        if (idOrEl && typeof idOrEl === 'object' && idOrEl.type) {
            return idOrEl;
        }
        var insp = window.ZapPopPopBuilderInspector;
        var rawId = (idOrEl !== undefined && idOrEl !== null && typeof idOrEl !== 'object')
            ? String(idOrEl)
            : (insp && insp.activeElementId ? String(insp.activeElementId) : (
                window.ZapPopPopBuilderCore && window.ZapPopPopBuilderCore.state ? String(window.ZapPopPopBuilderCore.state.activeId || '') : ''
            ));
        var cleanId = rawId.replace(/^zappoppop-el-/, '').replace(/^el-/, '');

        if (window.ZapPopPopBuilderCanvas && typeof window.ZapPopPopBuilderCanvas.getActiveElements === 'function') {
            var els = window.ZapPopPopBuilderCanvas.getActiveElements() || [];
            if (Array.isArray(els)) {
                var found = els.find(function(e) {
                    return e && (String(e.id) === cleanId || String(e.id) === rawId);
                });
                if (found) return found;
            }
        }
        var $sel =$('.zappoppop-canvas-item.selected, .zappoppop-canvas-item.zappoppop-selected, .zappoppop-element.selected').first();
        if ($sel.length) {
            return {
                id: $sel.attr('data-id') || cleanId,
                type: $sel.attr('data-type') ||$sel.attr('data-element-type') || ''
            };
        }
        return null;
    }

    function ensureFiveTabHeaderDOM() {
        var $logicBtn =$('#zappoppop-inspector-drawer [data-tab="logic"]').first();
        if (!$logicBtn.length) return;
        var $bar =$logicBtn.parent();
        var cls = ($logicBtn.attr('class') || 'zappoppop-insp-tab').replace(/\bactive\b/g, '').trim();

        if (!$bar.find('[data-tab="data"]').length) {
            $('<button type="button" id="insp-tab-nav-data" data-tab="data">DATA</button>')
                .attr('class', cls + ' zappoppop-form-family-tab')
                .insertAfter($logicBtn);
        }
        if (!$bar.find('[data-tab="advanced"]').length) {
            $('<button type="button" id="insp-tab-nav-advanced" data-tab="advanced">ADVANCED</button>')
                .attr('class', cls + ' zappoppop-form-family-tab')
                .insertAfter($bar.find('[data-tab="data"]'));
        }
    }

    function renderBadgePills(selector, list, kind) {
        var $box =$(selector);
        if (!$box.length) return;
        $box.empty();
        (Array.isArray(list) ? list : []).forEach(function(item) {
            $('<span class="zappoppop-adv-badge" />')
                .attr('data-kind', kind)
                .attr('data-val', item)
                .css({
                    display: 'inline-flex',
                    alignItems: 'center',
                    gap: '4px',
                    padding: '3px 9px',
                    fontSize: '11px',
                    fontWeight: '700',
                    color: '#fff',
                    background: '#c2185b',
                    borderRadius: '0px',
                    cursor: 'pointer'
                })
                .text(item + ' ×')
                .appendTo($box);
        });
    }

    function applyFamilyRouting(elOrType) {
        ensureFiveTabHeaderDOM();
        var el = (elOrType && typeof elOrType === 'object') ? elOrType : resolveActiveElement(elOrType);
        var type = (el && el.type) ? String(el.type).toLowerCase().trim() : (typeof elOrType === 'string' ? elOrType.toLowerCase().trim() : '');
        if (!type) {
            var layerTitle = String($('#prop-layer-name').val() || '').toLowerCase().trim();
            var m = layerTitle.match(/^([a-z_]+)/);
            if (m) type = m[1];
        }

        var family = getCanonicalFamily(type);
        var isForm = (family === 'form');

        // 1. Toggle 3-Tab (Media) vs 5-Tab (Form) in Header
        $('#zappoppop-inspector-drawer [data-tab="data"], #zappoppop-inspector-drawer [data-tab="advanced"]').each(function() {
            if (isForm) {
                this.style.setProperty('display', 'inline-flex', 'important');
                this.style.setProperty('align-items', 'center', 'important');
                this.style.setProperty('justify-content', 'center', 'important');
                this.style.setProperty('flex', '1 1 0', 'important');
                this.style.setProperty('min-width', '0', 'important');
            } else {
                this.style.setProperty('display', 'none', 'important');
            }
        });

        // 2. Ensure Element 10 (`text` / `textfield`) shows its decomposed Form Input panel
        if (type === 'text' || type === 'textfield' || type === 'text_field') {
            $('#panel-elem-paragraph-legacy, #panel-elem-paragraph, #panel-elem-title').hide();
            $('#panel-elem-text, #panel-elem-textfield').first().show();
        }

        // 3. Auto-fallback to Content tab if switching from Form (on Data/Advanced) to Media
        if (!isForm) {
            var $act =$('#zappoppop-inspector-drawer [data-tab].active');
            var curTab = $act.attr('data-tab') || '';
            if (curTab === 'data' || curTab === 'advanced') {
                $('#zappoppop-inspector-drawer [data-tab="content"], #zappoppop-inspector-drawer [data-tab="basic"]').first().trigger('click');
            }
        }

        // 4. Hydrate Form Family Data & Advanced tabs
        if (el && isForm) {
            if (!Array.isArray(el.filters)) el.filters = ['trim'];
            if (!Array.isArray(el.validators)) {
                el.validators = (type === 'email') ? ['email'] : [];
                if (el.required && el.validators.indexOf('required') === -1) el.validators.push('required');
            }
            $('#prop-data-default').val(el.defaultValue || '');
            $('#prop-data-dynamic-default').prop('checked', !!el.dynamicDefault);
            $('#prop-data-param-name').val(el.paramName || '');
            $('#prop-data-param-wrap').toggle(!!el.dynamicDefault);
            $('#prop-data-readonly').prop('checked', !!el.readOnly);
            $('#prop-data-save-db').prop('checked', el.saveToDb !== false);

            $('#prop-adv-field-id').val(el.fieldId || el.fieldName || el.id || '');
            $('#prop-adv-input-mask').val(el.inputMask || '');
            renderBadgePills('#zappoppop-adv-filters-list', el.filters, 'filter');
            renderBadgePills('#zappoppop-adv-validators-list', el.validators, 'validator');
        }
    }

    function hookInspectorLifecycle() {
        var insp = window.ZapPopPopBuilderInspector;
        if (!insp || insp._pureDecomposedHooked) return;
        insp._pureDecomposedHooked = true;

        var origOpen = insp.open;
        insp.open = function(id) {
            var res = typeof origOpen === 'function' ? origOpen.apply(this, arguments) : undefined;
            applyFamilyRouting(id);
            setTimeout(function() { if (window.__zppPanelSettled && window.__zppPanelSettled(id)) return; applyFamilyRouting(id); }, 20);
            return res;
        };

        var origShowPanel = insp.showElementPanel;
        if (typeof origShowPanel === 'function') {
            insp.showElementPanel = function(type) {
                var res = origShowPanel.apply(this, arguments);
                applyFamilyRouting(type);
                return res;
            };
        }

        var origSwitchTab = insp.switchTab;
        insp.switchTab = function(tab) {
            var t = String(tab || 'content').toLowerCase();
            if (typeof origSwitchTab === 'function') {
                origSwitchTab.apply(this, arguments);
            }
            if (t === 'data' || t === 'advanced') {
                $('#zappoppop-inspector-drawer [data-tab]').removeClass('active');
                $('#zappoppop-inspector-drawer [data-tab="' + t + '"]').addClass('active');
                $('#zappoppop-inspector-drawer .zappoppop-insp-content, #zappoppop-inspector-drawer .zappoppop-insp-tab-content, #zappoppop-inspector-drawer .zappoppop-tab-pane, #insp-tab-basic, #insp-tab-content, #insp-tab-settings, #insp-tab-style, #insp-tab-logic, #insp-tab-data, #insp-tab-advanced').removeClass('active').hide();
                $('#insp-tab-' + t).addClass('active').show();
            } else {
                $('#insp-tab-data, #insp-tab-advanced').removeClass('active').hide();
            }
        };

        $(document).off('click.zappoppopPureTab').on('click.zappoppopPureTab', '#zappoppop-inspector-drawer [data-tab]', function(e) {
            var tabKey = $(this).attr('data-tab');
            if (tabKey === 'data' || tabKey === 'advanced') {
                e.preventDefault();
                e.stopPropagation();
                insp.switchTab(tabKey);
            } else {
                $('#insp-tab-data, #insp-tab-advanced').removeClass('active').hide();
            }
        });

        $(document).off('click.zappoppopPureSelect').on('click.zappoppopPureSelect', '.zappoppop-ribbon-btn, .zappoppop-ribbon-tool, .zappoppop-canvas-item, .zappoppop-layer-item', function() {
            var t = $(this).attr('data-type') ||$(this).data('type') || '';
            applyFamilyRouting(t);
            setTimeout(function() { if (window.__zppPanelSettled && window.__zppPanelSettled(t)) return; applyFamilyRouting(t); }, 30);
        });
    }

    $(function() {
        hookInspectorLifecycle();
        setTimeout(hookInspectorLifecycle, 100);
    });
    hookInspectorLifecycle();
})(window, jQuery);
/* === ZAPPOPPOP_SELF_CONTAINED_5TAB_ENGINE_END === */



/* === ZAPPOPPOP_PHASE3_FORM_CORE_MODULES_START === */
(function(window, $) {
    'use strict';

        var FORM_CORE_PANEL_MAP = {
        'text': '#panel-elem-text',
        'textfield': '#panel-elem-text',
        'text_field': '#panel-elem-text',
        'email': '#panel-elem-email',
        'number': '#panel-elem-number',
        'phone': '#panel-elem-phone',
        'tel': '#panel-elem-phone',
        'textarea': '#panel-elem-textarea',
        'select': '#panel-elem-select',
        'dropdown': '#panel-elem-select',
        'checkbox': '#panel-elem-checkbox',
        'checkboxes': '#panel-elem-checkbox',
        'radio': '#panel-elem-radio',
        'radios': '#panel-elem-radio',
        'checkboxgroup': '#panel-elem-checkboxgroup',
        'multiselect': '#panel-elem-multiselect',
        'imageselect': '#panel-elem-imageselect',
        'tile': '#panel-elem-tile',
        'date': '#panel-elem-date',
        'time': '#panel-elem-time',
        'upload': '#panel-elem-upload',
        'file': '#panel-elem-upload',
        'password': '#panel-elem-password',
        'rating': '#panel-elem-rating'
    };

    function getActiveFormElement(idOrObj) {
        if (idOrObj && typeof idOrObj === 'object' && idOrObj.type) return idOrObj;
        var insp = window.ZapPopPopBuilderInspector;
        var rawId = (idOrObj !== undefined && idOrObj !== null && typeof idOrObj !== 'object')
            ? String(idOrObj)
            : (insp && insp.activeElementId ? String(insp.activeElementId) : (
                window.ZapPopPopBuilderCore && window.ZapPopPopBuilderCore.state ? String(window.ZapPopPopBuilderCore.state.activeId || '') : ''
            ));
        var cleanId = rawId.replace(/^zappoppop-el-/, '').replace(/^el-/, '');
        if (window.ZapPopPopBuilderCanvas && typeof window.ZapPopPopBuilderCanvas.getActiveElements === 'function') {
            var els = window.ZapPopPopBuilderCanvas.getActiveElements() || [];
            if (Array.isArray(els)) {
                var found = els.find(function(e) {
                    return e && (String(e.id) === cleanId || String(e.id) === rawId);
                });
                if (found) return found;
            }
        }
        return null;
    }

    function hydrateFormCoreModule(elOrType) {
        var el = (elOrType && typeof elOrType === 'object') ? elOrType : getActiveFormElement(elOrType);
        var type = (el && el.type) ? String(el.type).toLowerCase().trim() : (typeof elOrType === 'string' ? elOrType.toLowerCase().trim() : '');
        if (!type) return;

        var targetPanel = FORM_CORE_PANEL_MAP[type];
        if (!targetPanel) return;

        // Hide all legacy inline panels and display only the standalone decomposed module
        $('[id$="-legacy"]').hide();
        $('.element-panel, .zappoppop-panel-specific').hide();$(targetPanel).show();

        // Ensure Typography & Input Icons are visible in Style Tab for Form Inputs, while hiding Button Hover
        $('#prop-line-height, #prop-letter-spacing').closest('.zappoppop-insp-row, .zappoppop-field-row, div').show();
        $('#prop-btn-hover-section, #prop-btn-hover-bg').closest('.zappoppop-insp-card, div').hide();

        if (!el) return;

        // Hydrate `.zappoppop-gp-bind` inputs inside the active standalone Form Core panel
        $(targetPanel).find('.zappoppop-gp-bind').each(function() {
            var $inp =$(this);
            var prop = $inp.data('prop');
            if (!prop) return;
            var val = el[prop];
            if (val === undefined) {
                if (prop === 'placeholder') val = el.content || '';
                if (prop === 'fieldName') val = el.token || el.fieldId || type;
                if (prop === 'options') val = Array.isArray(el.options) ? el.options.join(', ') : (el.options || 'Option 1, Option 2');
                if (prop === 'min') val = el.minValue !== undefined ? el.minValue : 0;
                if (prop === 'max') val = el.maxValue !== undefined ? el.maxValue : 100;
                if (prop === 'step') val = el.stepValue !== undefined ? el.stepValue : 1;
            } else if (prop === 'options' && Array.isArray(val)) {
                val = val.join(', ');
            }
            if ($inp.is(':checkbox')) {$inp.prop('checked', (val === undefined && $inp.attr('data-zpp-default') === '1') ? true : !!val); /* ZAPPOPPOP_GP_DEFAULT */
            } else if (val !== undefined && val !== null) {
                $inp.val(val);
            }
        });
    }

    function bindFormCoreModuleEvents() {
        $(document).off('.zappoppopFormCoreModules').on(
            'input.zappoppopFormCoreModules change.zappoppopFormCoreModules',
            '[data-elem-family="form"] .zappoppop-gp-bind',
            function() {
                var el = getActiveFormElement();
                if (!el) return;
                var $inp =$(this);
                var prop = $inp.data('prop');
                if (!prop) return;
                var val = $inp.is(':checkbox') ? $inp.is(':checked') :$inp.val();
                el[prop] = val;

                // Keep aliases & 5-Tab Data/Advanced properties synchronized
                if (prop === 'placeholder') {
                    el.content = val;
                } else if (prop === 'fieldName') {
                    el.token = val;
                    el.fieldId = val;
                    $('#prop-adv-field-id, #prop-data-token').val(val);
                } else if (prop === 'inputMask') {
                    $('#prop-adv-input-mask').val(val);
                } else if (prop === 'min') {
                    el.minValue = val;
                } else if (prop === 'max') {
                    el.maxValue = val;
                } else if (prop === 'step') {
                    el.stepValue = val;
                } else if (prop === 'required') {
                    el.validators = Array.isArray(el.validators) ? el.validators : [];
                    if (val && el.validators.indexOf('required') === -1) {
                        el.validators.push('required');
                    } else if (!val) {
                        el.validators = el.validators.filter(function(x) { return x !== 'required'; });
                    }
                    if (window.ZapPopPopBuilderInspector && typeof window.ZapPopPopBuilderInspector.syncFamilyTabs === 'function') {
                        window.ZapPopPopBuilderInspector.syncFamilyTabs(el);
                    }
                }

                if (window.ZapPopPopBuilderCanvas && typeof window.ZapPopPopBuilderCanvas.renderCanvas === 'function') {
                    window.ZapPopPopBuilderCanvas.renderCanvas();
                }
            }
        );

        var insp = window.ZapPopPopBuilderInspector;
        if (insp && !insp._phase3FormCoreHooked) {
            insp._phase3FormCoreHooked = true;
            var origOpen = insp.open;
            insp.open = function(id) {
                var res = typeof origOpen === 'function' ? origOpen.apply(this, arguments) : undefined;
                hydrateFormCoreModule(id);
                setTimeout(function() { hydrateFormCoreModule(id); }, 25);
                return res;
            };
            var origShow = insp.showElementPanel;
            if (typeof origShow === 'function') {
                insp.showElementPanel = function(type) {
                    var res = origShow.apply(this, arguments);
                    hydrateFormCoreModule(type);
                    return res;
                };
            }
        }

        $(document).off('click.zappoppopFormCoreSelect').on(
            'click.zappoppopFormCoreSelect',
            '.zappoppop-ribbon-btn, .zappoppop-ribbon-tool, .zappoppop-canvas-item, .zappoppop-layer-item',
            function() {
                var t = $(this).attr('data-type') ||$(this).data('type') || '';
                setTimeout(function() { hydrateFormCoreModule(t); }, 30);
            }
        );
    }

    $(function() {
        bindFormCoreModuleEvents();
        setTimeout(bindFormCoreModuleEvents, 120);
    });
    bindFormCoreModuleEvents();
})(window, jQuery);
/* === ZAPPOPPOP_PHASE3_FORM_CORE_MODULES_END === */

/* === ZAPPOPPOP_PHASE3_FORM_DECOMPOSED_MODULES_START === */
(function(window, $) {
    'use strict';

    var FORM_PANEL_MAP = {
        'text': '#panel-elem-text',
        'textfield': '#panel-elem-text',
        'text_field': '#panel-elem-text',
        'email': '#panel-elem-email',
        'number': '#panel-elem-number',
        'spinner': '#panel-elem-number',
        'phone': '#panel-elem-phone',
        'tel': '#panel-elem-phone',
        'textarea': '#panel-elem-textarea',
        'select': '#panel-elem-select',
        'dropdown': '#panel-elem-select',
        'checkbox': '#panel-elem-checkbox',
        'checkboxes': '#panel-elem-checkbox',
        'radio': '#panel-elem-radio',
        'radios': '#panel-elem-radio',
        'checkboxgroup': '#panel-elem-checkboxgroup',
        'multiselect': '#panel-elem-multiselect',
        'imageselect': '#panel-elem-imageselect',
        'tile': '#panel-elem-tile',
        'date': '#panel-elem-date',
        'time': '#panel-elem-time',
        'upload': '#panel-elem-upload',
        'file': '#panel-elem-upload',
        'password': '#panel-elem-password',
        'rating': '#panel-elem-rating'
    };

    function getActiveFormStageElement(idOrObj) {
        if (idOrObj && typeof idOrObj === 'object' && idOrObj.type) return idOrObj;
        var insp = window.ZapPopPopBuilderInspector;
        var rawId = (idOrObj !== undefined && idOrObj !== null && typeof idOrObj !== 'object')
            ? String(idOrObj)
            : (insp && insp.activeElementId ? String(insp.activeElementId) : (
                window.ZapPopPopBuilderCore && window.ZapPopPopBuilderCore.state ? String(window.ZapPopPopBuilderCore.state.activeId || '') : ''
            ));
        var cleanId = rawId.replace(/^zappoppop-el-/, '').replace(/^el-/, '');
        if (window.ZapPopPopBuilderCanvas && typeof window.ZapPopPopBuilderCanvas.getActiveElements === 'function') {
            var els = window.ZapPopPopBuilderCanvas.getActiveElements() || [];
            if (Array.isArray(els)) {
                var found = els.find(function(e) {
                    return e && (String(e.id) === cleanId || String(e.id) === rawId);
                });
                if (found) return found;
            }
        }
        return null;
    }

    function hydrateFormModule(elOrType) {
        var el = (elOrType && typeof elOrType === 'object') ? elOrType : getActiveFormStageElement(elOrType);
        var type = (el && el.type) ? String(el.type).toLowerCase().trim() : (typeof elOrType === 'string' ? elOrType.toLowerCase().trim() : '');
        if (!type) return;

        var targetPanel = FORM_PANEL_MAP[type];
        if (!targetPanel) return;

        $('[id$="-legacy"]').hide();
        $('.element-panel, .zappoppop-panel-specific').hide();$(targetPanel).show();

        if (window.ZapPopPopBuilderInspector && typeof window.ZapPopPopBuilderInspector.sanitizeLayerInspectorForElement === 'function' && el) {
            window.ZapPopPopBuilderInspector.sanitizeLayerInspectorForElement(el);
        }

        if (!el) return;

        $(targetPanel).find('.zappoppop-gp-bind').each(function() {
            var $inp =$(this);
            var prop = $inp.data('prop');
            if (!prop) return;
            var val = el[prop];
            if ($inp.is(':checkbox')) {$inp.prop('checked', (val === undefined && $inp.attr('data-zpp-default') === '1') ? true : !!val); /* ZAPPOPPOP_GP_DEFAULT */
            } else if (val !== undefined && val !== null) {
                $inp.val(val);
            }
        });
    }

    function bindFormModuleEvents() {
        $(document).off('.zappoppopFormModules').on('input.zappoppopFormModules change.zappoppopFormModules', '[data-elem-family="form"] .zappoppop-gp-bind', function() {
            var el = getActiveFormStageElement();
            if (!el) return;
            var $inp =$(this);
            var prop = $inp.data('prop');
            if (!prop) return;
            var val = $inp.is(':checkbox') ? $inp.is(':checked') :$inp.val();
            el[prop] = val;

            if (prop === 'fieldName') {
                el.token = val;
                $('#prop-field-name, #prop-adv-id').val(val);
            }

            if (window.ZapPopPopBuilderCanvas && typeof window.ZapPopPopBuilderCanvas.renderCanvas === 'function') {
                window.ZapPopPopBuilderCanvas.renderCanvas();
            }
        });

        var insp = window.ZapPopPopBuilderInspector;
        if (insp && !insp._phase3FormHooked) {
            insp._phase3FormHooked = true;
            var origOpen = insp.open;
            insp.open = function(id) {
                var res = typeof origOpen === 'function' ? origOpen.apply(this, arguments) : undefined;
                hydrateFormModule(id);
                setTimeout(function() { hydrateFormModule(id); }, 25);
                return res;
            };
        }

        $(document).off('click.zappoppopFormSelect').on('click.zappoppopFormSelect', '.zappoppop-ribbon-btn, .zappoppop-ribbon-tool, .zappoppop-canvas-item, .zappoppop-layer-item', function() {
            var t = $(this).attr('data-type') ||$(this).data('type') || '';
            setTimeout(function() { hydrateFormModule(t); }, 30);
        });
    }

    $(function() {
        bindFormModuleEvents();
        setTimeout(bindFormModuleEvents, 120);
    });
    bindFormModuleEvents();
})(window, jQuery);
/* === ZAPPOPPOP_PHASE3_FORM_DECOMPOSED_MODULES_END === */




/* === ZAPPOPPOP_COORDS_ANIM_SANITY_PATCH_START === */
(function($){
    'use strict';
    var GUARD = '__zappoppop_coords_anim_sanity_v1';

    function enforceCoordsAnimVisibility(tab) {
        var $drawer = $('#zappoppop-inspector-drawer');
        if (!$drawer.length) return;
        var activeTab = (typeof tab === 'string') ? tab : ($drawer.find('.zappoppop-insp-tab.active').data('tab') || '');
        activeTab = String(activeTab || '').toLowerCase();

        var isBasic = (activeTab === 'basic' || activeTab === 'content');

        // Coordinates header -> grid detection (defensive)
        var $coordsHeader = $drawer.find('div').filter(function() {
            var t = $(this).text() || '';
            return (typeof t === 'string' && t.trim().toUpperCase().indexOf('COORDINATES') === 0);
        }).first();

        var $coordsGrid = $coordsHeader.length ? $coordsHeader.next('div').first() : $drawer.find('#prop-left').closest('div').parent();

        if ($coordsHeader.length) {
            if (isBasic) { $coordsHeader.show(); } else { $coordsHeader.hide(); }
        }
        if ($coordsGrid.length) {
            if (isBasic) { $coordsGrid.show(); } else { $coordsGrid.hide(); }
        }

        // Animation wrapper
        var $animWrap = $drawer.find('#zappoppop-element-anim-wrap');
        if ($animWrap.length) {
            if (isBasic) { $animWrap.show(); } else { $animWrap.hide(); }
        }

        // Also ensure input fields inside these sections cannot be accidentally visible
        var coordInputs = ['#prop-left','#prop-top','#prop-width','#prop-height'];
        coordInputs.forEach(function(sel){
            var $i = $drawer.find(sel);
            if ($i.length) {
                if (isBasic) { $i.closest('div').show(); } else { $i.closest('div').hide(); }
            }
        });
    }

    function attachSanityPatches() {
        if (!window.ZapPopPopBuilderInspector) return;
        var insp = window.ZapPopPopBuilderInspector;
        if (insp[GUARD]) return;
        insp[GUARD] = true;

        // Patch switchTab if present
        if (typeof insp.switchTab === 'function') {
            var origSwitch = insp.switchTab;
            insp.switchTab = function(tab) {
                var res = origSwitch.apply(this, arguments);
                try { enforceCoordsAnimVisibility(tab); } catch (e) { /* safe fail */ }
                return res;
            };
        }

        // Patch open to run enforcement after opening
        if (typeof insp.open === 'function') {
            var origOpen = insp.open;
            insp.open = function() {
                var res = origOpen.apply(this, arguments);
                try { setTimeout(function(){ enforceCoordsAnimVisibility($('.zappoppop-insp-tab.active').data('tab')); }, 20); } catch(e) {}
                return res;
            };
        }

        // Global tab-button click fallback
        $(document).on('click.zappoppop_coords_sanity', '.zappoppop-insp-tab', function(){
            var tab = $(this).data('tab') || '';
            try { setTimeout(function(){ enforceCoordsAnimVisibility(tab); }, 8); } catch(e) {}
        });

        // Run once immediately (in case inspector already open)
        $(function(){
            try { enforceCoordsAnimVisibility($('.zappoppop-insp-tab.active').data('tab')); } catch(e) {}
        });
    }

    // Defer attaching until DOM ready and inspector exists
    $(function(){
        attachSanityPatches();
        // try again shortly in case inspector is loaded after this script
        setTimeout(attachSanityPatches, 120);
    });

})(jQuery);
/* === ZAPPOPPOP_COORDS_ANIM_SANITY_PATCH_END === */



/* === ZAPPOPPOP_INSPECTOR_LAYOUT_FIX_START === */

(function($){
    'use strict';

    var GUARD = '__zappoppop_layout_fix_v1';

    function setTabRolesAndPanels() {
        var $drawer = $('#zappoppop-inspector-drawer');
        if (!$drawer.length) return;

        // Buttons -> role=tab, container -> role=tablist
        var $tabs = $drawer.find('.zappoppop-insp-tab');
        $tabs.attr('role', 'tab');
        $tabs.each(function(i){
            var $b = $(this);
            var tabKey = $b.data('tab') || ('tab-' + i);
            $b.attr('id', 'zappoppop-insp-tab-btn-' + tabKey);
            $b.attr('aria-controls', 'insp-tab-' + tabKey);
            $b.attr('aria-selected', $b.hasClass('active') ? 'true' : 'false');
            $b.attr('tabindex', $b.hasClass('active') ? '0' : '-1');
        });

        // Panes -> role=tabpanel and link them
        $drawer.find('.zappoppop-tab-pane').each(function(){
            var $p = $(this);
            var id = $p.attr('id') || '';
            if (!id) return;
            $p.attr('role', 'tabpanel');
            var tabKey = id.replace(/^insp-tab-/, '');
            $p.attr('id', id);
            var $btn = $drawer.find('.zappoppop-insp-tab[data-tab="' + tabKey + '"]').first();
            if ($btn.length) {
                $p.attr('aria-labelledby', $btn.attr('id'));
            }
            // Toggle active class and aria-hidden
            var isActive = $btn.length && $btn.hasClass('active');
            if (isActive) {
                $p.addClass('active').attr('aria-hidden', 'false');
            } else {
                $p.removeClass('active').attr('aria-hidden', 'true');
            }
        });
    }

    function enforceInspectorSizing() {
        var $drawer = $('#zappoppop-inspector-drawer');
        if (!$drawer.length) return;
        var headerHeight = $drawer.find('[style*="height:48px"], .zappoppop-insp-header').outerHeight() || 48;
        var topOffset = parseInt($drawer.css('top')) || 52;
        var avail = window.innerHeight - (topOffset + 8); // small gap
        $drawer.css('max-height', Math.max(200, avail) + 'px');
        // ensure panes have proper min-height logic to make flex's min-height:0 work cross-browser
        $drawer.find('.zappoppop-tab-pane, .zappoppop-insp-content').css({ 'min-height': 0, 'max-height': (avail - headerHeight) + 'px' });
    }

    function enforceCoordsAnimationVisibilityOnTab(tabKey) {
        // defensive: treat "basic" and "content" as same
        var key = (tabKey || $('.zappoppop-insp-tab.active').data('tab') || '').toString().toLowerCase();
        var isBasic = (key === 'basic' || key === 'content');
        var $drawer = $('#zappoppop-inspector-drawer');
        if (!$drawer.length) return;

        // animation wrapper
        var $anim = $drawer.find('#zappoppop-element-anim-wrap');
        if ($anim.length) {
            if (isBasic) $anim.show(); else $anim.hide();
        }

        // coordinates inputs - ensure visible only on basic
        var coords = ['#prop-left','#prop-top','#prop-width','#prop-height'];
        coords.forEach(function(sel){
            var $i = $drawer.find(sel);
            if ($i.length) {
                if (isBasic) {
                    $i.closest('div').show();
                } else {
                    $i.closest('div').hide();
                }
            }
        });

        // update aria-selected
        $drawer.find('.zappoppop-insp-tab').each(function(){
            var $b = $(this);
            var sel = ($b.data('tab') || '').toString().toLowerCase();
            $b.attr('aria-selected', (sel === key) ? 'true' : 'false');
            $b.attr('tabindex', (sel === key) ? '0' : '-1');
        });

        // update panes active/aria-hidden
        $drawer.find('.zappoppop-tab-pane').each(function(){
            var $p = $(this);
            var pid = $p.attr('id') || '';
            var pkey = pid.replace(/^insp-tab-/, '');
            if (pkey === key) {
                $p.addClass('active').attr('aria-hidden','false').show();
            } else {
                $p.removeClass('active').attr('aria-hidden','true').hide();
            }
        });
    }

    function attachLayoutHooks() {
        if (!window.ZapPopPopBuilderInspector) return;
        var insp = window.ZapPopPopBuilderInspector;
        if (insp && insp.__wpoppop_layout_fix_attached) return;
        if (insp) insp.__wpoppop_layout_fix_attached = true;

        // ensure roles/initial states
        setTabRolesAndPanels();
        enforceInspectorSizing();
        enforceCoordsAnimationVisibilityOnTab();

        // patch switchTab if exists
        if (typeof insp.switchTab === 'function') {
            var orig = insp.switchTab;
            insp.switchTab = function(tab) {
                var res = orig.apply(this, arguments);
                try { setTimeout(function(){ setTabRolesAndPanels(); enforceCoordsAnimationVisibilityOnTab(tab); enforceInspectorSizing(); }, 10); } catch(e){}
                return res;
            };
        }

        // patch open to recompute sizing & roles after open
        if (typeof insp.open === 'function') {
            var origOpen = insp.open;
            insp.open = function() {
                var res = origOpen.apply(this, arguments);
                try { setTimeout(function(){ setTabRolesAndPanels(); enforceCoordsAnimationVisibilityOnTab(); enforceInspectorSizing(); }, 20); } catch(e){}
                return res;
            };
        }

        // fallback binding for clicks on tabs
        $(document).off('click.zappoppop_inspector_layout').on('click.zappoppop_inspector_layout', '#zappoppop-inspector-drawer .zappoppop-insp-tab', function(e){
            var tab = $(this).data('tab') || '';
            setTimeout(function(){ setTabRolesAndPanels(); enforceCoordsAnimationVisibilityOnTab(tab); enforceInspectorSizing(); }, 8);
        });

        // window resize
        $(window).on('resize.zappoppop_inspector_layout', function(){ enforceInspectorSizing(); });

        // initial enforcement shortly after load (IDEMPOTENT)
        setTimeout(function(){ setTabRolesAndPanels(); enforceCoordsAnimationVisibilityOnTab(); enforceInspectorSizing(); }, 120);
    }

    // run when DOM ready; re-run shortly in case inspector loads late
    $(function(){
        attachLayoutHooks();
        setTimeout(attachLayoutHooks, 200);
    });

})(jQuery);
/* === ZAPPOPPOP_INSPECTOR_LAYOUT_FIX_END === */




/* === ZAPPOPPOP_LAYER_PANEL_SYNC_START === */

(function($){
    'use strict';

    // Helper: highlight layer item(s) in layer panels for a given element id
    function highlightLayerPanelItem(elementId) {
        if (!elementId) return;
        var selectors = [
            '#zappoppop-floating-layers-panel .zappoppop-layer-item',
            '#zappoppop-layers-list .zappoppop-layer-item',
            '.zappoppop-layer-item' // fallback
        ];
        selectors.forEach(function(sel){
            var $panel = $(sel).closest('div').first();
            if (!$panel.length) return;
        });

        // Remove active class from all known layer items, then add to matching
        $('.zappoppop-layer-item').removeClass('is-active zappoppop-layer-active inspector-layer-selected');
        $('[data-id="' + elementId + '"], [data-element-id="' + elementId + '"], [data-id="el-' + elementId + '"]').each(function(){
            $(this).addClass('is-active zappoppop-layer-active inspector-layer-selected');
            // ensure it is visible / scrolled into view inside layers pane(s)
            var $parent = $(this).closest('#zappoppop-layers-list, #zappoppop-floating-layers-panel, .zappoppop-layers-wrap');
            if ($parent.length) {
                try { $(this)[0].scrollIntoView({ block: 'nearest', behavior: 'auto' }); } catch(e){}
            }
        });
    }

    // Ensure inspector UI reflects the model values for the selected element
    function syncInspectorToElement(elementId) {
        if (!elementId || !window.ZapPopPopBuilderInspector) return;
        var insp = window.ZapPopPopBuilderInspector;
        // Resolve element object using existing helper if available
        var el = null;
        if (typeof insp.getActiveElement === 'function') {
            el = insp.getActiveElement();
            // getActiveElement may return null if state.activeId hasn't been updated yet, attempt fallback
            if (!el && window.ZapPopPopBuilderCore && window.ZapPopPopBuilderCore.state) {
                var cur = window.ZapPopPopBuilderCore.state.currentCanvas || 1;
                var list = window.ZapPopPopBuilderCore.state.canvases ? (window.ZapPopPopBuilderCore.state.canvases[cur] || []) : [];
                for (var i = 0; i < list.length; i++) {
                    if (String(list[i].id) === String(elementId) || String(list[i].id) === 'el-' + String(elementId)) {
                        el = list[i];
                        break;
                    }
                }
            }
        } else if (window.ZapPopPopBuilderCore && window.ZapPopPopBuilderCore.state) {
            var cur2 = window.ZapPopPopBuilderCore.state.currentCanvas || 1;
            var list2 = window.ZapPopPopBuilderCore.state.canvases ? (window.ZapPopPopBuilderCore.state.canvases[cur2] || []) : [];
            for (var j = 0; j < list2.length; j++) {
                if (String(list2[j].id) === String(elementId) || String(list2[j].id) === 'el-' + String(elementId)) {
                    el = list2[j]; break;
                }
            }
        }

        if (!el) return;

        // Use existing inspector methods if they exist to hydrate fields
        try {
            if (typeof insp.syncCoordinates === 'function') insp.syncCoordinates(el);
        } catch(e) {}

        try {
            if (typeof insp.hydrateStyleLogicFields === 'function') insp.hydrateStyleLogicFields(el);
        } catch(e) {}

        try {
            if (typeof insp.showElementSpecificSettings === 'function') insp.showElementSpecificSettings(el);
        } catch(e) {}

        // Additional hydration to ensure inputs mirrored (defensive)
        try {
            if ($('#prop-left').length) $('#prop-left').val(parseInt(el.left, 10) || 0);
            if ($('#prop-top').length) $('#prop-top').val(parseInt(el.top, 10) || 0);
            if ($('#prop-width').length) $('#prop-width').val(parseInt(el.width, 10) || 100);
            if ($('#prop-height').length) $('#prop-height').val(parseInt(el.height, 10) || 40);
            if ($('#prop-layer-name').length) $('#prop-layer-name').val(el.name || el.label || '');
            if ($('#prop-content').length) $('#prop-content').val(el.content || el.text || '');
        } catch(e) {}

        // ensure the inspector pushes history/state if needed
        try {
            if (window.ZapPopPopBuilderCore && typeof window.ZapPopPopBuilderCore.pushHistory === 'function') {
                // do not create a history entry here, just ensure inspector state is consistent (no-op)
            }
        } catch(e){}
    }

    // Monkey-patch Inspector.open to also sync layer-panel highlight and inspector values
    function patchInspectorOpen() {
        if (!window.ZapPopPopBuilderInspector) return;
        var insp = window.ZapPopPopBuilderInspector;
        if (insp.__zappoppop_layer_panel_patch_applied) return;
        insp.__zappoppop_layer_panel_patch_applied = true;

        if (typeof insp.open === 'function') {
            var origOpen = insp.open;
            insp.open = function(id) {
                var res = origOpen.apply(this, arguments);
                try {
                    // element id normalization
                    var cleanId = String(id || (this.activeId || '')).replace(/^el-/, '').replace(/^zappoppop-el-/, '');
                    // highlight layer item(s)
                    setTimeout(function(){
                        highlightLayerPanelItem(cleanId);
                        syncInspectorToElement(cleanId);
                    }, 10);
                } catch(e){}
                return res;
            };
        }
    }

    // Ensure clicking a layer item selects it, highlights panel, and hydrates inspector
    function bindLayerPanelClicks() {
        $(document).off('click.zappoppop_layer_panel').on('click.zappoppop_layer_panel', '.zappoppop-layer-item', function(e){
            e.preventDefault();
            var $el = $(this);
            var id = $el.attr('data-id') || $el.attr('data-element-id') || $el.data('id') || $el.data('element-id') || '';
            if (!id) return;
            // normalize
            id = String(id).replace(/^el-/, '').replace(/^zappoppop-el-/, '');
            // highlight in panels
            highlightLayerPanelItem(id);
            // open inspector for this element if inspector exists
            if (window.ZapPopPopBuilderInspector && typeof window.ZapPopPopBuilderInspector.open === 'function') {
                try { window.ZapPopPopBuilderInspector.open(id); } catch(e){}
            }
            // also ensure inspector input values sync after a short delay
            setTimeout(function(){ syncInspectorToElement(id); }, 20);
        });
    }

    $(function(){
        // attach patch and bindings on DOM ready and re-run shortly after to handle late loads
        try { patchInspectorOpen(); } catch(e){}
        try { bindLayerPanelClicks(); } catch(e){}
        setTimeout(function(){ try { patchInspectorOpen(); bindLayerPanelClicks(); } catch(e){} }, 120);
    });

})(jQuery);
/* === ZAPPOPPOP_LAYER_PANEL_SYNC_END === */

/* === ZAPPOPPOP_WORKSPACE_MEDIA_SELECT_AND_CHECKER_GUARD_START === */
(function(window, $) {
    'use strict';

    function extractCleanElementId($node) {
        if (!$node || !$node.length) return '';
        var raw = $node.attr('data-id') || $node.data('id') || $node.attr('data-element-id') || $node.attr('id') || '';
        return String(raw).replace(/^zappoppop-el-/, '').replace(/^el-/, '').replace(/^layer-/, '');
    }

    function selectAndInspectLayer(cleanId, fromStage) {
        if (!cleanId) return;

        // 1. Update core state activeId
        if (window.ZapPopPopBuilderCore && window.ZapPopPopBuilderCore.state) {
            window.ZapPopPopBuilderCore.state.activeId = cleanId;
        }

        // 2. Highlight on Workspace Canvas (.zappoppop-canvas-item)
        $('.zappoppop-canvas-item').removeClass('selected is-selected active');
        var $stageItem = $('.zappoppop-canvas-item').filter(function() {
            return extractCleanElementId($(this)) === cleanId;
        });
        $stageItem.addClass('selected is-selected active');

        // 3. Highlight in Floating Layers Panel (.zappoppop-layer-item)
        $('.zappoppop-layer-item').removeClass('active is-active selected');
        var $layerRow = $('.zappoppop-layer-item').filter(function() {
            return extractCleanElementId($(this)) === cleanId;
        });
        $layerRow.addClass('active is-active selected');

        // 4. Allow jQuery UI draggable on button/media elements on Workspace
        $stageItem.each(function() {
            var $el = $(this);
            if ($el.data('ui-draggable') || $el.data('draggable')) {
                try { $el.draggable('option', 'cancel', '.ui-resizable-handle'); } catch (err) {}
            }
        });

        /* ZAPPOPPOP_CLICK_OPEN: stage click selects only; Layer Settings slides in when enabled */
        if (fromStage === true && window.ZapPopPopClickOpen && !window.ZapPopPopClickOpen.element()) { return; }

        // 5. Close Popup Settings drawer if open so drawers don't collide
        if (window.ZapPopPopBuilderSettings && typeof window.ZapPopPopBuilderSettings.closeDrawer === 'function') {
            window.ZapPopPopBuilderSettings.closeDrawer();
        }
        $('#zappoppop-settings-drawer').removeClass('open is-open');
        $('#zappoppop-settings-backdrop').fadeOut(150);

        // 6. Slide in Layer Settings Inspector Drawer & Push Floating Layers Panel to right: 376px
        var $inspDrawer = $('#zappoppop-inspector-drawer');
        $inspDrawer.removeClass('is-closed').addClass('is-open open').css('display', 'flex');
        $('body, .zappoppop-builder-workspace, .zappoppop-main-frame').addClass('panel-open inspector-open');

        if (window.ZapPopPopBuilderInspector) {
            window.ZapPopPopBuilderInspector.activeElementId = cleanId;
            if (typeof window.ZapPopPopBuilderInspector.open === 'function') {
                window.ZapPopPopBuilderInspector.open(cleanId);
            }
        }
        if (window.ZapPopPopElementsRegistry && typeof window.ZapPopPopElementsRegistry.syncInspectorForElement === 'function') {
            window.ZapPopPopElementsRegistry.syncInspectorForElement(cleanId);
        }
        if (window.ZapPopPopBuilderLayers) {
            if (typeof window.ZapPopPopBuilderLayers.pushForInspector === 'function') {
                window.ZapPopPopBuilderLayers.pushForInspector(true);
            } else if (typeof window.ZapPopPopBuilderLayers.pushLayerPanel === 'function') {
                window.ZapPopPopBuilderLayers.pushLayerPanel(true);
            } else {
                $('#zappoppop-floating-layers-panel').css('right', '376px');
            }
        }
    }

    function bindWorkspaceMediaSelectionAndCheckerGuard() {
        // A. Direct Selection of ANY Media or Form Element on the Workspace Stage
        $(document).off('mousedown.gpStageSelect click.gpStageSelect', '.zappoppop-canvas-item')
                   .on('mousedown.gpStageSelect click.gpStageSelect', '.zappoppop-canvas-item', function(e) {
            if ($(e.target).hasClass('ui-resizable-handle') || $(e.target).closest('.ui-resizable-handle').length) {
                return;
            }
            e.stopPropagation();
            var cleanId = extractCleanElementId($(this));
            if (cleanId) {
                selectAndInspectLayer(cleanId, true);
            }
        });

        // B. Direct Selection from the Floating Layers Panel
        $(document).off('click.gpLayerRowSelect', '.zappoppop-layer-item')
                   .on('click.gpLayerRowSelect', '.zappoppop-layer-item', function(e) {
            if ($(e.target).closest('.zappoppop-layer-actions, .zappoppop-layer-btn, button, input').length) {
                return;
            }
            e.stopPropagation();
            var cleanId = extractCleanElementId($(this));
            if (cleanId) {
                selectAndInspectLayer(cleanId);
            }
        });

        // C. Stop clicks inside Popup Settings Drawer (#zappoppop-settings-drawer)
        //    or Inspector Drawer (#zappoppop-inspector-drawer) from bubbling to the checker area
        $(document).off('mousedown.gpDrawerStop click.gpDrawerStop', '#zappoppop-settings-drawer, #zappoppop-inspector-drawer, #zappoppop-floating-layers-panel')
                   .on('mousedown.gpDrawerStop click.gpDrawerStop', '#zappoppop-settings-drawer, #zappoppop-inspector-drawer, #zappoppop-floating-layers-panel', function(e) {
            e.stopPropagation();
        });

        // D. Clicking on the Checker Area (or empty canvas background) closes Popup Settings
        //    only if Popup Settings was explicitly dismissed via its close button OR if clicking
        //    on the checker area when no settings input is being edited
        $(document).off('click.gpCheckerAreaClick', '.zappoppop-builder-workspace, #zappoppop-builder-workspace, #zappoppop-canvas-scroll-container, #zappoppop-canvas-box')
                   .on('click.gpCheckerAreaClick', '.zappoppop-builder-workspace, #zappoppop-builder-workspace, #zappoppop-canvas-scroll-container, #zappoppop-canvas-box', function(e) {
            var $t = $(e.target);
            // Ignore clicks on stage elements, resize handles, layers panel, or drawers
            if ($t.closest('.zappoppop-canvas-item, .ui-resizable-handle, #zappoppop-canvas-corner-handle, #zappoppop-floating-layers-panel, #zappoppop-inspector-drawer, #zappoppop-settings-drawer').length) {
                return;
            }

            // Deselect active stage element and slide out Layer Settings Inspector
            $('.zappoppop-canvas-item').removeClass('selected is-selected active');
            $('.zappoppop-layer-item').removeClass('active is-active selected');
            /* ZAPPOPPOP_INSPECTOR_CLOSE_ONLY_X: checker click no longer closes Layer Settings */
            if (window.ZapPopPopBuilderCore && window.ZapPopPopBuilderCore.state) { window.ZapPopPopBuilderCore.state.activeId = null; }
        });
    }

    $(function() {
        bindWorkspaceMediaSelectionAndCheckerGuard();
        setTimeout(bindWorkspaceMediaSelectionAndCheckerGuard, 150);
    });
    bindWorkspaceMediaSelectionAndCheckerGuard();
})(window, jQuery);
/* === ZAPPOPPOP_WORKSPACE_MEDIA_SELECT_AND_CHECKER_GUARD_END === */

/* === ZAPPOPPOP_UNIFIED_SELECTION_AND_POPULATION_SUITE_START === */
(function(window, $) {
    'use strict';

    var FORM_TYPES = [
        'text', 'textfield', 'email', 'number', 'spinner', 'phone', 'tel',
        'textarea', 'select', 'dropdown', 'checkbox', 'checkboxes',
        'radio', 'radios', 'checkboxgroup', 'multiselect', 'imageselect', 'tile',
        'date', 'time', 'upload', 'file', 'password', 'rating'
    ];

    var TEXT_BEARING_TYPES = [
        'label', 'title', 'paragraph', 'button', 'link', 'link_btn',
        'step', 'step_btn', 'submit', 'submit_btn', 'progress',
        'text', 'textfield', 'email', 'number', 'spinner', 'phone', 'tel', 'textarea'
    ];

    function normalizeId(raw) {
        if (raw === undefined || raw === null) return '';
        return String(raw)
            .replace(/^zappoppop-layer-/, '')
            .replace(/^layer-/, '')
            .replace(/^zappoppop-el-/, '')
            .replace(/^el-/, '')
            .trim();
    }

    function resolveElement(idOrObj) {
        if (idOrObj && typeof idOrObj === 'object' && idOrObj.type) return idOrObj;
        var rawId = (idOrObj !== undefined && idOrObj !== null && typeof idOrObj !== 'object')
            ? String(idOrObj)
            : (window.ZapPopPopBuilderInspector && (window.ZapPopPopBuilderInspector.activeElementId || window.ZapPopPopBuilderInspector.activeId)
                ? String(window.ZapPopPopBuilderInspector.activeElementId || window.ZapPopPopBuilderInspector.activeId)
                : (window.ZapPopPopBuilderCore && window.ZapPopPopBuilderCore.state ? String(window.ZapPopPopBuilderCore.state.activeId || '') : ''));
        var cleanId = normalizeId(rawId);
        if (!cleanId) return null;

        // 1. Check canvas elements list
        if (window.ZapPopPopBuilderCanvas && typeof window.ZapPopPopBuilderCanvas.getActiveElements === 'function') {
            var list = window.ZapPopPopBuilderCanvas.getActiveElements() || [];
            for (var i = 0; i < list.length; i++) {
                if (list[i] && (String(list[i].id) === rawId || normalizeId(list[i].id) === cleanId)) {
                    return list[i];
                }
            }
        }

        // 2. Fallback to core state canvases
        var core = window.ZapPopPopBuilderCore;
        if (core && core.state && core.state.canvases) {
            var cur = core.state.currentCanvas || 1;
            var cData = core.state.canvases[cur];
            var arr = Array.isArray(cData) ? cData : (cData && Array.isArray(cData.elements) ? cData.elements : []);
            for (var j = 0; j < arr.length; j++) {
                if (arr[j] && (String(arr[j].id) === rawId || normalizeId(arr[j].id) === cleanId)) {
                    return arr[j];
                }
            }
        }
        return null;
    }

    function executeSwitchTab(tabName) {
        var target = String(tabName || 'basic').toLowerCase().trim();
        if (target === 'content') target = 'basic';

        var $tabsBar = $('#zappoppop-inspector-tabs-bar, .zappoppop-inspector-tabs');
        $tabsBar.find('.zappoppop-insp-tab, [data-tab]').removeClass('active');
        $tabsBar.find('[data-tab="' + target + '"]').addClass('active');

        var allTabs = ['basic', 'style', 'logic', 'data', 'advanced'];
        for (var i = 0; i < allTabs.length; i++) {
            var pane = document.getElementById('insp-tab-' + allTabs[i]);
            if (pane) {
                if (allTabs[i] === target) {
                    $(pane).addClass('active');
                    if (pane.style.getPropertyValue('display') !== 'block' || pane.style.getPropertyPriority('display') !== 'important') { pane.style.setProperty('display', 'block', 'important'); } /* ZPP_FLICKER_FIX */
                } else {
                    $(pane).removeClass('active');
                    if (pane.style.getPropertyValue('display') !== 'none' || pane.style.getPropertyPriority('display') !== 'important') { pane.style.setProperty('display', 'none', 'important'); } /* ZPP_FLICKER_FIX */
                }
            }
        }
    }

    function populateInspectorTabs(el) {
        if (!el) return;
        var type = String(el.type || '').toLowerCase().trim();
        var isForm = FORM_TYPES.indexOf(type) !== -1;
        var isText = TEXT_BEARING_TYPES.indexOf(type) !== -1;
        var isButton = ['button', 'link', 'link_btn', 'step', 'step_btn', 'submit', 'submit_btn'].indexOf(type) !== -1;

        // Toggle Data & Advanced tabs visibility
        var $tabsBar = $('#zappoppop-inspector-tabs-bar, .zappoppop-inspector-tabs');
        $tabsBar.find('[data-tab="data"], [data-tab="advanced"]').each(function() {
            this.style.setProperty('display', isForm ? 'inline-block' : 'none', 'important');
        });

        // Current active tab check
        var curTab = $tabsBar.find('.zappoppop-insp-tab.active').attr('data-tab') || 'basic';
        if (!isForm && (curTab === 'data' || curTab === 'advanced')) {
            executeSwitchTab('basic');
        } else {
            executeSwitchTab(curTab);
        }

        // ==========================================
        // 1. POPULATE STYLE TAB
        // ==========================================
        var op = (el.opacity !== undefined && el.opacity !== null && el.opacity !== '') ? parseFloat(el.opacity) : 100;
        if (op <= 1 && op > 0) op = Math.round(op * 100);
        $('#prop-opacity').val(op);
        $('#prop-opacity-val').text(op + '%');

        $('#prop-bg-color').val(el.bgColor || '#3b82f6');
        $('#prop-bg-transparent').prop('checked', !!el.transparentFill || el.bgColor === 'transparent' || (type === 'shape' && !el.bgColor));

        $('#insp-section-typography').toggle(isText);
        if (isText) {
            $('#prop-font-size').val(el.fontSize || 16);
            $('#prop-font-weight').val(el.fontWeight || 400);
            $('#prop-line-height').val(el.lineHeight || 1.4);
            $('#prop-letter-spacing').val(el.letterSpacing || 0);
            $('#prop-color').val(el.color || '#ffffff');
        }

        $('#prop-pad-top').val(el.paddingTop || 0);
        $('#prop-pad-right').val(el.paddingRight || 0);
        $('#prop-pad-bottom').val(el.paddingBottom || 0);
        $('#prop-pad-left').val(el.paddingLeft || 0);

        $('#prop-border-width').val(el.borderWidth || 0);
        $('#prop-border-style').val(el.borderStyle || 'solid');
        $('#prop-border-color').val(el.borderColor || '#334155');
        $('#prop-border-radius').val(el.borderRadius || 0);

        $('#prop-shadow-x').val(el.shadowX || 0);
        $('#prop-shadow-y').val(el.shadowY || 0);
        $('#prop-shadow-blur').val(el.shadowBlur || 0);
        $('#prop-shadow-spread').val(el.shadowSpread || 0);
        $('#prop-shadow-color').val(el.shadowColor || '#000000');

        $('#prop-btn-hover-section').toggle(isButton);
        if (isButton) {
            $('#prop-elem-hover-bg').val(el.hoverBgColor || '#0284c7');
            $('#prop-elem-hover-color').val(el.hoverTextColor || '#ffffff');
            $('#prop-elem-hover-border').val(el.hoverBorderColor || '#0284c7');
        }

        $('#zappoppop-form-style-module').toggle(isForm);
        if (isForm) {
            var align = el.textAlign || 'left';
            $('#zappoppop-form-align-group .zappoppop-form-align-btn')
                .removeClass('active button-primary')
                .filter('[data-align="' + align + '"]')
                .addClass('active button-primary');
            $('#prop-form-icon-left').val(el.iconLeft || '');
            $('#prop-form-icon-left-offset').val(el.iconLeftOffset || 10);
            $('#prop-form-icon-right').val(el.iconRight || '');
            $('#prop-form-icon-right-offset').val(el.iconRightOffset || 10);
            $('#prop-form-custom-class').val(el.customClass || '');
        }

        // ==========================================
        // 2. POPULATE DATA TAB
        // ==========================================
        $('#prop-default-value').val(el.defaultValue || '');
        $('#prop-dynamic-default').prop('checked', !!el.dynamicDefault);
        $('#prop-dynamic-param-row').toggle(!!el.dynamicDefault);
        $('#prop-dynamic-param').val(el.paramName || el.dynamicParam || ''); $('#prop-dynamic-param-row').toggle(!!el.dynamicDefault); /* ZAPPOPPOP_DYN_ROW */
        $('#prop-readonly').prop('checked', !!el.readOnly);
        $('#prop-save-to-db').prop('checked', el.saveToDb !== false);

        // ==========================================
        // 3. POPULATE ADVANCED TAB
        // ==========================================
        $('#prop-field-name').val(el.fieldName || el.fieldId || '');
        $('#prop-input-mask').val(el.inputMask || '');
        $('#prop-validator-error').val(el.errorMessage || '');
        /* ZAPPOPPOP_ADV_TAB: hydrate */
        $('#prop-validator-equal').val(el.validatorEqual || '');
        $('#prop-validator-list').val(el.validatorList || '');
        var advV = Array.isArray(el.validators) ? el.validators : [];
        $('#zappoppop-val-extra-equal').toggle(advV.indexOf('equal') !== -1);
        $('#zappoppop-val-extra-inarray').toggle(advV.indexOf('inarray') !== -1);
        /* ZAPPOPPOP_REQ_MSG: hydrate */
        $('#insp-row-gp-required').toggle(isForm);
        $('#prop-gp-required').prop('checked', (el.required === undefined && String(el.type || '').toLowerCase() === 'email' ? true : !!el.required) /* ZAPPOPPOP_EMAIL_REQ_DEFAULT */);
        $('#prop-gp-required-msg').val(el.requiredMessage || '');
        $('#insp-row-gp-required-msg').toggle(isForm && (el.required === undefined && String(el.type || '').toLowerCase() === 'email' ? true : !!el.required) /* ZAPPOPPOP_EMAIL_REQ_DEFAULT */);

        var filters = Array.isArray(el.filters) ? el.filters : [];
        $('.prop-filter-chk').each(function() {
            $(this).prop('checked', filters.indexOf($(this).val()) !== -1);
        });

        var validators = Array.isArray(el.validators) ? el.validators : [];
        if (el.required && validators.indexOf('required') === -1) {
            validators.push('required');
        }
        $('.prop-validator-chk').each(function() {
            $(this).prop('checked', validators.indexOf($(this).val()) !== -1);
        });
    }

    // ==========================================
    // GLOBAL AUTHORITATIVE SELECTION DISPATCHER
    // ==========================================
    window.selectLayerEverywhere = function(idOrObj) {
        var el = resolveElement(idOrObj);
        if (!el) return;
        var actualId = el.id;

        // 1. Highlight layer row in Layers Panel
        $('#zappoppop-layers-list .zappoppop-layer-item, #zappoppop-layers-list > li')
            .removeClass('active selected is-selected');
        $('#zappoppop-layers-list .zappoppop-layer-item[data-id="' + actualId + '"], #zappoppop-layers-list > [data-id="' + actualId + '"]')
            .addClass('active selected is-selected');

        // 2. Select on Canvas stage
        if (window.ZapPopPopBuilderCore && window.ZapPopPopBuilderCore.state) {
            window.ZapPopPopBuilderCore.state.activeId = actualId;
        }
        if (window.ZapPopPopBuilderCanvas) {
            window.ZapPopPopBuilderCanvas.selectedId = actualId;
            if (typeof window.ZapPopPopBuilderCanvas.selectElement === 'function') {
                try { window.ZapPopPopBuilderCanvas.selectElement(actualId); } catch(e) {}
            }
        }
        $('.zappoppop-canvas-item, .zappoppop-element').removeClass('zappoppop-selected selected is-selected');
        $('#zappoppop-el-' + actualId + ', #el-' + actualId + ', [data-id="' + actualId + '"]')
            .addClass('zappoppop-selected selected is-selected');

        // 3. Open Layer Settings Inspector
        if (window.ZapPopPopBuilderInspector) {
            window.ZapPopPopBuilderInspector.activeElementId = actualId;
            window.ZapPopPopBuilderInspector.activeId = actualId;
            if (typeof window.ZapPopPopBuilderInspector.open === 'function') {
                try { window.ZapPopPopBuilderInspector.open(actualId); } catch(e) {}
            }
        }

        // 4. Force slide in and push layers panel
        var $drawer = $('#zappoppop-inspector-drawer');
        $drawer.css('display', 'flex').removeClass('is-closed').addClass('is-open open');
        $('body, .zappoppop-builder-workspace, .zappoppop-main-frame').addClass('panel-open inspector-open');

        if (window.ZapPopPopBuilderLayers && typeof window.ZapPopPopBuilderLayers.pushForInspector === 'function') {
            window.ZapPopPopBuilderLayers.pushForInspector(true);
        }

        // 5. Populate and hydrate all tabs
        populateInspectorTabs(el);
    };

    function bindSuiteEvents() {
        // Direct Tab Clicking
        $(document)
            .off('click.zappoppopSuiteTabs')
            .on('click.zappoppopSuiteTabs', '#zappoppop-inspector-drawer .zappoppop-insp-tab, #zappoppop-inspector-tabs-bar [data-tab], .zappoppop-inspector-tabs [data-tab]', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var tab = $(this).attr('data-tab') || $(this).data('tab') || 'basic';
                executeSwitchTab(tab);
            });

        // Layer Panel 1-Click Selection
        $(document)
            .off('click.zappoppopSuiteLayerSelect')
            .on('click.zappoppopSuiteLayerSelect', '#zappoppop-floating-layers-panel .zappoppop-layer-item, #zappoppop-layers-list .zappoppop-layer-item, #zappoppop-layers-list > li', function(e) {
                if ($(e.target).closest('.zappoppop-layer-action-btn, .zappoppop-layer-actions, .zappoppop-layer-vis, .zappoppop-layer-lock, .zappoppop-layer-del, .zappoppop-layer-dup, button, input').length) {
                    return;
                }
                e.preventDefault();
                e.stopPropagation();
                var rawId = $(this).attr('data-id') || $(this).data('id') || $(this).attr('id') || '';
                window.selectLayerEverywhere(rawId);
                        try {
                            if (window.ZapPopPopBuilderLayers && typeof window.ZapPopPopBuilderLayers.openLayerInspector === 'function') {
                                window.ZapPopPopBuilderLayers.openLayerInspector(rawId);
                            }
                        } catch (errOpen) {}
            });

        // Two-way synchronization on Style, Data & Advanced inputs
        $(document)
            .off('input.zappoppopSuiteInputs change.zappoppopSuiteInputs')
            .on('input.zappoppopSuiteInputs change.zappoppopSuiteInputs', '#insp-tab-style input, #insp-tab-style select, #insp-tab-data input, #insp-tab-advanced input', function() {
                var el = resolveElement();
                if (!el) return;
                var $inp = $(this);
                var prop = $inp.data('prop') || $inp.data('data-prop') || $inp.data('adv-prop');
                var val = $inp.is(':checkbox') ? $inp.is(':checked') : $inp.val();

                if ($inp.attr('id') === 'prop-opacity') {
                    $('#prop-opacity-val').text(val + '%');
                    el.opacity = val;
                } else if ($inp.attr('id') === 'prop-bg-transparent') {
                    el.transparentFill = val;
                    if (val) el.bgColor = 'transparent';
                } else if (prop) {
                    el[prop] = val;
                }

                if (window.ZapPopPopBuilderCanvas && typeof window.ZapPopPopBuilderCanvas.renderCanvas === 'function') {
                    window.ZapPopPopBuilderCanvas.renderCanvas();
                }
            });

        // Wrap Inspector controller methods
        var insp = window.ZapPopPopBuilderInspector;
        if (insp) {
            insp.switchTab = executeSwitchTab;
            var origOpen = insp.open;
            insp.open = function(id) {
                var res = typeof origOpen === 'function' ? origOpen.apply(this, arguments) : undefined;
                var el = resolveElement(id);
                if (el) populateInspectorTabs(el);
                return res;
            };
        }
    }

    $(function() {
        bindSuiteEvents();
        setTimeout(bindSuiteEvents, 150);
    });
    bindSuiteEvents();

})(window, jQuery);
/* === ZAPPOPPOP_UNIFIED_SELECTION_AND_POPULATION_SUITE_END === */

/* === ZAPPOPPOP_MASTER_LAYER_INSPECTOR_AND_LAYER_ENGINE_START === */
(function(window, $) {
    'use strict';

    var FALLBACK_STYLE_HTML = "";
    var FALLBACK_LOGIC_HTML = "";
    var FALLBACK_DATA_HTML = "";
    var FALLBACK_ADV_HTML = "";

    var FORM_TYPES = [
        'text', 'textfield', 'email', 'number', 'spinner', 'phone', 'tel',
        'textarea', 'select', 'dropdown', 'checkbox', 'checkboxes',
        'radio', 'radios', 'checkboxgroup', 'multiselect', 'imageselect', 'tile',
        'date', 'time', 'upload', 'file', 'password', 'rating'
    ];

    var MEDIA_PANEL_MAP = {
        'shape': '#panel-elem-shape',
        'rect': '#panel-elem-shape',
        'rectangle': '#panel-elem-shape',
        'square': '#panel-elem-shape',
        'image': '#panel-elem-image',
        'video': '#panel-elem-video',
        'label': '#panel-elem-label',
        'title': '#panel-elem-label',
        'paragraph': '#panel-elem-label',
        'html': '#panel-elem-html',
        'close': '#panel-elem-close',
        'close_icon': '#panel-elem-close',
        'icon': '#panel-elem-icon',
        'fontawesome': '#panel-elem-icon',
        'progress': '#panel-elem-progress',
        'button': '#panel-elem-button',
        'link': '#panel-elem-button',
        'link_btn': '#panel-elem-button',
        'step': '#panel-elem-button',
        'step_btn': '#panel-elem-button',
        'submit': '#panel-elem-submit',
        'submit_btn': '#panel-elem-submit',
        'text': '#panel-elem-text',
        'textfield': '#panel-elem-text',
        'email': '#panel-elem-email',
        'number': '#panel-elem-number',
        'spinner': '#panel-elem-number',
        'phone': '#panel-elem-phone',
        'tel': '#panel-elem-phone',
        'textarea': '#panel-elem-textarea',
        'select': '#panel-elem-select',
        'dropdown': '#panel-elem-select',
        'checkbox': '#panel-elem-checkbox',
        'checkboxes': '#panel-elem-checkbox',
        'radio': '#panel-elem-radio',
        'radios': '#panel-elem-radio',
        'checkboxgroup': '#panel-elem-checkboxgroup',
        'multiselect': '#panel-elem-multiselect',
        'imageselect': '#panel-elem-imageselect',
        'tile': '#panel-elem-tile',
        'date': '#panel-elem-date',
        'time': '#panel-elem-time',
        'upload': '#panel-elem-upload',
        'file': '#panel-elem-upload',
        'password': '#panel-elem-password',
        'rating': '#panel-elem-rating'
    };

    function normalizeId(raw) {
        if (raw === undefined || raw === null) return '';
        return String(raw)
            .replace(/^zappoppop-layer-item-/, '')
            .replace(/^zappoppop-layer-/, '')
            .replace(/^layer-/, '')
            .replace(/^zappoppop-el-/, '')
            .replace(/^el-/, '')
            .trim();
    }

    function extractAllCanvasElements() {
        var results = [];
        // 1. Try ZapPopPopBuilderCanvas.getActiveElements()
        if (window.ZapPopPopBuilderCanvas && typeof window.ZapPopPopBuilderCanvas.getActiveElements === 'function') {
            try {
                var activeList = window.ZapPopPopBuilderCanvas.getActiveElements();
                if (Array.isArray(activeList)) {
                    results = results.concat(activeList);
                } else if (activeList && typeof activeList === 'object') {
                    Object.keys(activeList).forEach(function(k) {
                        if (activeList[k] && typeof activeList[k] === 'object') results.push(activeList[k]);
                    });
                }
            } catch (e) {}
        }

        // 2. Try ZapPopPopBuilderCore.state.canvases across current and all canvas keys
        var core = window.ZapPopPopBuilderCore;
        if (core && core.state && core.state.canvases) {
            var canvases = core.state.canvases;
            var curKey = core.state.currentCanvas || 1;
            var keysToCheck = [curKey].concat(Object.keys(canvases));
            keysToCheck.forEach(function(ck) {
                var cData = canvases[ck];
                if (!cData) return;
                var pool = Array.isArray(cData) ? cData : (cData.elements ? cData.elements : cData);
                if (Array.isArray(pool)) {
                    results = results.concat(pool);
                } else if (pool && typeof pool === 'object') {
                    Object.keys(pool).forEach(function(ek) {
                        if (pool[ek] && typeof pool[ek] === 'object' && (pool[ek].id || pool[ek].type)) {
                            if (!pool[ek].id) pool[ek].id = ek;
                            results.push(pool[ek]);
                        }
                    });
                }
            });
        }
        return results;
    }

    function resolveElement(idOrObj) {
        if (idOrObj && typeof idOrObj === 'object' && (idOrObj.id || idOrObj.type)) return idOrObj;
        var insp = window.ZapPopPopBuilderInspector;
        var rawId = (idOrObj !== undefined && idOrObj !== null && typeof idOrObj !== 'object')
            ? String(idOrObj)
            : (insp && (insp.activeElementId || insp.activeId)
                ? String(insp.activeElementId || insp.activeId)
                : (window.ZapPopPopBuilderCore && window.ZapPopPopBuilderCore.state ? String(window.ZapPopPopBuilderCore.state.activeId || '') : ''));
        var cleanId = normalizeId(rawId);
        if (!cleanId) return null;

        var all = extractAllCanvasElements();
        for (var i = 0; i < all.length; i++) {
            var item = all[i];
            if (!item) continue;
            if (String(item.id) === rawId || normalizeId(item.id) === cleanId) {
                return item;
            }
        }

        // DOM Fallback so resolveElement NEVER returns null when a layer or stage node exists
        var $stageNode = $('#zappoppop-el-' + cleanId + ', #el-' + cleanId + ', .zappoppop-canvas-item[data-id="' + rawId + '"], .zappoppop-canvas-item[data-id="' + cleanId + '"]').first();
        var $layerNode = $('.zappoppop-layer-item[data-id="' + rawId + '"], .zappoppop-layer-item[data-id="' + cleanId + '"]').first();
        if ($stageNode.length || $layerNode.length) {
            var inferredType = ($stageNode.attr('data-type') || $layerNode.attr('data-type') || 'label').toLowerCase();
            var fallbackObj = {
                id: $stageNode.attr('data-id') || $layerNode.attr('data-id') || cleanId,
                type: inferredType,
                name: $layerNode.find('.zappoppop-layer-title, .zappoppop-layer-name').text().trim() || inferredType.toUpperCase(),
                left: parseInt($stageNode.css('left'), 10) || 20,
                top: parseInt($stageNode.css('top'), 10) || 20,
                width: $stageNode.outerWidth() || 180,
                height: $stageNode.outerHeight() || 44
            };
            return fallbackObj;
        }
        return null;
    }

    // Guarantee all 5 tab panes exist inside the scroll body and are NEVER empty
    function ensureFiveTabsDom() {
        var $drawer = $('#zappoppop-inspector-drawer');
        if (!$drawer.length) return;

        // Ensure #zappoppop-inspector-drawer is strictly inside .zappoppop-builder-workspace and 360px wide
        var $ws = $('.zappoppop-builder-workspace, #zappoppop-builder-workspace').first();
        if ($ws.length && $drawer.parent()[0] !== $ws[0]) {
            $ws.append($drawer);
        }
        $drawer.css({
            position: 'absolute',
            top: '0px',
            right: '0px',
            bottom: '0px',
            left: 'auto',
            width: '300px',
            maxWidth: '360px',
            height: '100%'
        });

        var $body = $drawer.find('#zappoppop-inspector-body, .zappoppop-inspector-body').first();
        if (!$body.length) {
            $body = $('<div id="zappoppop-inspector-body" class="zappoppop-inspector-body" style="flex:1 1 auto;overflow-y:auto;overflow-x:hidden;padding:16px;box-sizing:border-box;width:100%;"></div>');
            $drawer.append($body);
        } else {
            $body.attr('id', 'zappoppop-inspector-body').addClass('zappoppop-inspector-body');
        }

        // Deduplicate any duplicate tab panes and move canonical panes inside #zappoppop-inspector-body
        var paneSpecs = [
            { id: 'insp-tab-basic', fallback: null },
            { id: 'insp-tab-style', fallback: FALLBACK_STYLE_HTML },
            { id: 'insp-tab-logic', fallback: FALLBACK_LOGIC_HTML },
            { id: 'insp-tab-data', fallback: FALLBACK_DATA_HTML },
            { id: 'insp-tab-advanced', fallback: FALLBACK_ADV_HTML }
        ];

        for (var i = 0; i < paneSpecs.length; i++) {
            var spec = paneSpecs[i];
            var $panes = $('[id="' + spec.id + '"]');
            if ($panes.length > 1) {
                $panes.slice(1).remove();
            }
            var $pane = $('#' + spec.id);
            if (!$pane.length) {
                $pane = $('<div id="' + spec.id + '" class="zappoppop-tab-pane zappoppop-insp-content" style="display:none;"></div>');
                $body.append($pane);
            } else if ($pane.parent()[0] !== $body[0]) {
                $body.append($pane);
            }

            // If Style, Logic, Data, or Advanced pane is empty, inject authoritative markup
            if (spec.fallback && $pane.children().length === 0) {
                $pane.html(spec.fallback);
            }
        }

        // Keep Coordinates & Bounds and Animation strictly inside #insp-tab-basic
        var $basic = $('#insp-tab-basic');
        $('#insp-section-coords, #insp-section-animation, #insp-section-gp-basic-shared').each(function() {
            if ($basic.length && !$.contains($basic[0], this)) {
                $basic.append(this);
            }
        });

        // Ensure all 5 tab buttons exist in the tab bar
        var $tabsBar = $('#zappoppop-inspector-tabs-bar, .zappoppop-inspector-tabs').first();
        if ($tabsBar.length) {
            if (!$tabsBar.find('[data-tab="data"]').length) {
                $tabsBar.append('<button type="button" class="zappoppop-insp-tab zappoppop-form-only-tab" data-tab="data" style="display:none;">Data</button>');
            }
            if (!$tabsBar.find('[data-tab="advanced"]').length) {
                $tabsBar.append('<button type="button" class="zappoppop-insp-tab zappoppop-form-only-tab" data-tab="advanced" style="display:none;">Advanced</button>');
            }
        }
    }

    function executeSwitchTab(tabName) {
        ensureFiveTabsDom();
        var target = String(tabName || 'basic').toLowerCase().trim();
        if (target === 'content') target = 'basic';

        var $tabsBar = $('#zappoppop-inspector-tabs-bar, .zappoppop-inspector-tabs');
        $tabsBar.find('.zappoppop-insp-tab, [data-tab]').removeClass('active');
        $tabsBar.find('[data-tab="' + target + '"]').addClass('active');

        var allTabs = ['basic', 'style', 'logic', 'data', 'advanced'];
        for (var i = 0; i < allTabs.length; i++) {
            var pane = document.getElementById('insp-tab-' + allTabs[i]);
            if (pane) {
                if (allTabs[i] === target) {
                    $(pane).addClass('active');
                    if (pane.style.getPropertyValue('display') !== 'block' || pane.style.getPropertyPriority('display') !== 'important') { pane.style.setProperty('display', 'block', 'important'); } /* ZPP_FLICKER_FIX */
                    // Ensure direct section cards inside active tab are visible unless explicitly scoped off
                    $(pane).children('.zappoppop-insp-section').not('#zappoppop-form-style-module, #prop-btn-hover-section').show();
                } else {
                    $(pane).removeClass('active');
                    if (pane.style.getPropertyValue('display') !== 'none' || pane.style.getPropertyPriority('display') !== 'important') { pane.style.setProperty('display', 'none', 'important'); } /* ZPP_FLICKER_FIX */
                }
            }
        }
    }

    function renderInlineStylesRepeater(el) {
        var $list = $('#zappoppop-gp-inline-styles-list');
        if (!$list.length) return;
        $list.empty();
        var styles = Array.isArray(el.inlineStyles) ? el.inlineStyles : [];
        for (var i = 0; i < styles.length; i++) {
            var r = styles[i] || {};
            var $row = $(
                '<div class="zappoppop-inline-style-row" style="display:grid;grid-template-columns:1fr 1fr auto;gap:6px;align-items:center;">' +
                    '<input type="text" class="zappoppop-input zappoppop-style-prop" placeholder="property (e.g. border-color)" style="font-size:11px;" />' +
                    '<input type="text" class="zappoppop-input zappoppop-style-val" placeholder="value (e.g. #38bdf8)" style="font-size:11px;" />' +
                    '<button type="button" class="button button-small zappoppop-remove-inline-style" style="color:#f87171;">&times;</button>' +
                '</div>'
            );
            $row.find('.zappoppop-style-prop').val(r.prop || '');
            $row.find('.zappoppop-style-val').val(r.val || '');
            $list.append($row);
        }
    }

    /* ZAPPOPPOP_LOGIC_SCOPE: current-stage, form-field-only, de-duplicated candidates */
    function getLogicFieldCandidates(selfEl) {
        var LOGIC_TYPES = ['text','textfield','email','number','spinner','phone','tel','textarea','select','dropdown','checkbox','checkboxes','radio','radios','multiselect','imageselect','tile','date','time','upload','file','password','rating'];
        var pool = [];
        var core = window.ZapPopPopBuilderCore;
        if (core && core.state && core.state.canvases) {
            var cur = core.state.currentCanvas || 1;
            var cData = core.state.canvases[cur];
            if (cData) pool = Array.isArray(cData) ? cData : (Array.isArray(cData.elements) ? cData.elements : []);
        }
        if (!pool.length && window.ZapPopPopBuilderCanvas && typeof window.ZapPopPopBuilderCanvas.getActiveElements === 'function') {
            try { var a = window.ZapPopPopBuilderCanvas.getActiveElements(); if (Array.isArray(a)) pool = a; } catch (e) {}
        }
        var selfId = normalizeId(selfEl && selfEl.id);
        var seen = {}, out = [];
        for (var i = 0; i < pool.length; i++) {
            var f = pool[i];
            if (!f || !f.id) continue;
            var fid = normalizeId(f.id);
            if (!fid || fid === selfId || seen[fid]) continue;
            if (LOGIC_TYPES.indexOf(String(f.type || '').toLowerCase()) === -1) continue;
            seen[fid] = true;
            out.push(f);
        }
        return out;
    }


    function renderLogicRulesList(el) {
        var $list = $('#zappoppop-logic-rules-list');
        if (!$list.length) return;
        $list.empty();
        var rules = Array.isArray(el.logicRules) ? el.logicRules : [];
        var allEls = getLogicFieldCandidates(el);
        var cleanSelf = normalizeId(el.id);

        if (!rules.length) {
            $list.append('<div style="font-size:11px; color:#64748b; font-style:italic;">No rules yet. Click "+ Add rule" to add a condition.</div>');
            return;
        }

        for (var i = 0; i < rules.length; i++) {
            var r = rules[i] || {};
            var fieldOpts = '';
            for (var j = 0; j < allEls.length; j++) {
                var f = allEls[j];
                if (!f || normalizeId(f.id) === cleanSelf) continue;
                var fId = normalizeId(f.id);
                var usedElsewhere = false;
                for (var u = 0; u < rules.length; u++) { if (u !== i && rules[u] && normalizeId(rules[u].field) === fId) { usedElsewhere = true; break; } }
                if (usedElsewhere) continue;
                var fName = f.fieldName || f.name || f.label || f.placeholder || (String(f.type).toUpperCase() + ' #' + fId);
                var sel = (fId === normalizeId(r.field)) ? ' selected' : '';
                fieldOpts += '<option value="' + fId + '"' + sel + '>' + $('<div>').text(fName).html() + '</option>';
            }

            var ops = [
                { k: 'equal', l: 'Equals (==)' },
                { k: 'not_equal', l: 'Not Equal (!=)' },
                { k: 'contains', l: 'Contains' },
                { k: 'not_contains', l: 'Does Not Contain' },
                { k: 'greater', l: 'Greater (>)' },
                { k: 'less', l: 'Less (<)' },
                { k: 'empty', l: 'Is Empty' },
                { k: 'not_empty', l: 'Is Not Empty' }
            ];
            var opHtml = ops.map(function(o) {
                return '<option value="' + o.k + '"' + (o.k === (r.operator || 'equal') ? ' selected' : '') + '>' + o.l + '</option>';
            }).join('');

            var $row = $(
                '<div class="zappoppop-logic-rule-row" style="background:#090d16; border:1px solid #1e293b; border-radius:0px; padding:8px; display:grid; grid-template-columns:1fr 1fr; gap:6px;">' +
                    '<select class="widefat zappoppop-input rule-field" style="font-size:11px; grid-column:span 2;"><option value="">-- Select Field --</option>' + fieldOpts + '</select>' +
                    '<select class="widefat zappoppop-input rule-operator" style="font-size:11px;">' + opHtml + '</select>' +
                    '<div style="display:flex; gap:4px;">' +
                        '<input type="text" class="widefat zappoppop-input rule-value" placeholder="Value..." style="flex:1; font-size:11px;" />' +
                        '<button type="button" class="button button-small btn-remove-logic-rule" style="color:#ef4444; padding:0 6px;">&times;</button>' +
                    '</div>' +
                '</div>'
            );
            $row.find('.rule-value').val(r.value || '');
            $list.append($row);
        }
    }

    /* ZAPPOPPOP_ANIM_HYDRATE: load the Layer Settings > Animation controls from the selected layer's own model, so each layer keeps its own values when switching layers */
    function hydrateLayerAnimation(el) {
        if (!el || !$('#prop-anim-in').length) return;
        var hasVal = function(v) { return v !== undefined && v !== null && v !== ''; };
        $('#prop-anim-in').val(el.animIn || el.anim_appearance || el.animation || 'none');
        $('#prop-anim-duration').val(hasVal(el.animDuration) ? el.animDuration : (hasVal(el.anim_duration) ? el.anim_duration : 1000));
        $('#prop-anim-delay').val(hasVal(el.animDelay) ? el.animDelay : (hasVal(el.anim_delay) ? el.anim_delay : 0));
        $('#prop-anim-start').val(el.animStart === 'with' ? 'with' : 'after');
    }

    function hydrateAllFiveTabs(el) {
        ensureFiveTabsDom();
        if (!el) return;
        hydrateLayerAnimation(el);

        var type = String(el.type || 'label').toLowerCase().trim();
        var isForm = FORM_TYPES.indexOf(type) !== -1;
        var isButton = ['button', 'link', 'link_btn', 'step', 'step_btn', 'submit', 'submit_btn'].indexOf(type) !== -1;

        // 1. 3-Tab (Media/Action) vs 5-Tab (Form) Routing
        var $tabsBar = $('#zappoppop-inspector-tabs-bar, .zappoppop-inspector-tabs');
        $tabsBar.find('[data-tab="data"], [data-tab="advanced"]').each(function() {
            this.style.setProperty('display', isForm ? 'inline-block' : 'none', 'important');
        });

        var curTab = $tabsBar.find('.zappoppop-insp-tab.active').attr('data-tab') || 'basic';
        if (!isForm && (curTab === 'data' || curTab === 'advanced')) {
            curTab = 'basic';
        }
        executeSwitchTab(curTab);

        // 2. CONTENT TAB (#insp-tab-basic)
        $('#prop-layer-name, #prop-element-name').val(el.name || el.label || type.toUpperCase());
        $('#prop-pos-top, #prop-top').val(parseInt(el.top, 10) || 0);
        $('#prop-pos-left, #prop-left').val(parseInt(el.left, 10) || 0);
        $('#prop-size-width, #prop-width').val(parseInt(el.width, 10) || 160);
        $('#prop-size-height, #prop-height').val(parseInt(el.height, 10) || 44);
        $('#prop-required').prop('checked', !!el.required);

        var targetPanel = MEDIA_PANEL_MAP[type];
        if (targetPanel && $(targetPanel).length) {
            $('[id$="-legacy"]').hide();
            $('.element-panel, .zappoppop-panel-specific').hide();
            $(targetPanel).show();
            $(targetPanel).find('.zappoppop-gp-bind').each(function() {
                var $inp = $(this);
                var p = $inp.data('prop');
                if (!p) return;
                var v = el[p];
                if (v === undefined) {
                    if (p === 'content') v = el.label || el.titleText || el.text || '';
                    if (p === 'placeholder') v = el.label || '';
                    if (p === 'optionsList') v = el.options || el.selectOptions || '';
                }
                if ($inp.is(':checkbox')) $inp.prop('checked', (v === undefined && $inp.attr('data-zpp-default') === '1') ? true : !!v); /* ZAPPOPPOP_GP_DEFAULT */
                else if (v !== undefined && v !== null) $inp.val(v);
            });
        }

        if ((type === 'phone' || type === 'tel') && !el.autocomplete) { /* ZAPPOPPOP_PHONE_AC_DEFAULT */
            el.autocomplete = 'tel';
            $('#prop-phone-autocomplete').val('tel');
        }
        if (type === 'checkbox' || type === 'checkboxes') { $('#prop-checkbox-layout').val(el.layout || 'vertical'); } /* ZAPPOPPOP_CHECKBOX_LAYOUT */
        if (type === 'radio' || type === 'radios') { $('#prop-radio-layout').val(el.layout || 'vertical'); } /* ZAPPOPPOP_RADIO_LAYOUT */ if ((type === 'radio' || type === 'radios') && window.__zppRadioRebuild) { window.__zppRadioRebuild(true); } /* ZAPPOPPOP_RADIO_REBUILD */
        if (type === 'checkboxgroup') { /* ZAPPOPPOP_CBGROUP_REBUILD */
            $('#prop-checkboxgroup-layout').val(el.layout || 'vertical');
            if (window.__zppCheckboxRebuild) { window.__zppCheckboxRebuild(true); }
            if (!el.bgColor) { $('#prop-bg-color').val('#ffffff'); $('#prop-bg-transparent').prop('checked', true); }
        }
        if ((type === 'checkbox' || type === 'checkboxes') && !el.bgColor) { /* ZAPPOPPOP_CB_BG */
            $('#prop-bg-color').val('#ffffff');
            $('#prop-bg-transparent').prop('checked', true);
        }
        if ((type === 'radio' || type === 'radios') && !el.bgColor) { /* ZAPPOPPOP_RADIO_BG */
            $('#prop-bg-color').val('#ffffff');
            $('#prop-bg-transparent').prop('checked', true);
        }
        if (window.ZapPopPopElementExt) { window.ZapPopPopElementExt.call(type, 'hydrate', [el, type]); } /* ZAPPOPPOP_EXT_HYDRATE */
        if (type === 'shape') {
            $('#prop-shape-fill').val(el.shapeFill || '#3b82f6');
            $('#prop-shape-transparent').prop('checked', !!el.shapeNoFill);
        }

        // 3. STYLE TAB (#insp-tab-style)
        $('#zappoppop-form-style-module').toggle(isForm);
        $('#prop-btn-hover-section').toggle(isButton);
        $('#insp-section-opacity, #insp-section-bg-color, #insp-section-typography, #insp-section-padding, #insp-section-border, #insp-section-shadow, #insp-section-custom-css').show();
        $('#insp-section-typography').toggle(['shape', 'rect', 'rectangle', 'square'].indexOf(type) === -1);
        $('#insp-tab-style').toggleClass('zappoppop-shape-style', String(type).toLowerCase() === 'shape'); /* ZAPPOPPOP_SHAPE_STYLE */
        $('#insp-tab-style').toggleClass('zappoppop-rd-style', ['radio', 'radios', 'checkboxgroup', 'multiselect', 'imageselect'].indexOf(String(type).toLowerCase()) !== -1); /* ZAPPOPPOP_RD_STYLE */
        $('#insp-tab-style').toggleClass('zappoppop-tl-style', String(type).toLowerCase() === 'tile'); /* ZAPPOPPOP_TL_STYLE */
        $('#insp-tab-style').toggleClass('zappoppop-cb-style', ['checkbox', 'checkboxes'].indexOf(String(type).toLowerCase()) !== -1); /* ZAPPOPPOP_CB_STYLE */
        $('#insp-tab-style').toggleClass('zappoppop-tf-style-lean', ['text', 'textfield', 'email', 'number', 'phone', 'tel', 'textarea', 'select', 'dropdown'].indexOf(String(type).toLowerCase()) !== -1); /* ZAPPOPPOP_TF_STYLE_LEAN */

        var op = (el.opacity !== undefined && el.opacity !== null && el.opacity !== '') ? parseFloat(el.opacity) : 100;
        if (op <= 1 && op > 0) op = Math.round(op * 100);
        $('#prop-opacity').val(op);
        $('#prop-opacity-val').text(op + '%');

        $('#prop-bg-color').val((el.bgColor && el.bgColor !== 'transparent') ? el.bgColor : '#3b82f6');
        $('#prop-bg-transparent').prop('checked', !!el.transparentFill || el.bgColor === 'transparent' || (type === 'shape' && !el.bgColor));
        $('#prop-font-size').val(parseInt(el.fontSize, 10) || 16);
        $('#prop-font-weight').val(el.fontWeight || '400');
        $('#prop-line-height').val(el.lineHeight || 1.4);
        $('#prop-letter-spacing').val(el.letterSpacing || 0);
        $('#prop-color').val(el.color || '#ffffff');

        $('#prop-pad-top').val(parseInt(el.paddingTop, 10) || 0);
        $('#prop-pad-right').val(parseInt(el.paddingRight, 10) || 0);
        $('#prop-pad-bottom').val(parseInt(el.paddingBottom, 10) || 0);
        $('#prop-pad-left').val(parseInt(el.paddingLeft, 10) || 0);

        $('#prop-border-width').val(parseInt(el.borderWidth, 10) || 0);
        $('#prop-border-style').val(el.borderStyle || 'solid');
        $('#prop-border-color').val(el.borderColor || '#334155');
        $('#prop-border-radius').val(parseInt(el.borderRadius, 10) || 4);

        $('#prop-shadow-x').val(parseInt(el.shadowX, 10) || 0);
        $('#prop-shadow-y').val(parseInt(el.shadowY, 10) || 0);
        $('#prop-shadow-blur').val(parseInt(el.shadowBlur, 10) || 0);
        $('#prop-shadow-spread').val(parseInt(el.shadowSpread, 10) || 0);
        $('#prop-shadow-color').val(el.shadowColor || '#000000');

        $('#prop-elem-hover-bg').val(el.hoverBgColor || '#0284c7');
        $('#prop-elem-hover-color').val(el.hoverTextColor || '#ffffff');
        $('#prop-elem-hover-border').val(el.hoverBorderColor || '#0284c7');

        var align = el.textAlign || 'left';
        $('#zappoppop-form-align-group .zappoppop-form-align-btn')
            .removeClass('active button-primary')
            .removeClass('active is-active button-primary');
        $('#prop-form-icon-left').val(el.iconLeft || '');
        $('#prop-form-icon-left-offset').val(el.iconLeftOffset !== undefined ? el.iconLeftOffset : 10);
        $('#prop-form-icon-right').val(el.iconRight || '');
        $('#prop-form-icon-right-offset').val(el.iconRightOffset !== undefined ? el.iconRightOffset : 10);
        $('#prop-form-custom-class').val(el.customClass || '');
        renderInlineStylesRepeater(el);

        // 4. LOGIC TAB (#insp-tab-logic)
        $('#prop-logic-enabled').prop('checked', !!el.logicEnabled);
        $('#zappoppop-logic-body').toggle(!!el.logicEnabled);
        $('#prop-logic-action').val(el.logicAction || 'show');
        $('#prop-logic-match').val(el.logicMatch || 'all');
        renderLogicRulesList(el);
        $('#prop-logic-click-action').val(el.buttonAction || 'link');
        $('#prop-logic-link-url').val(el.linkUrl || el.url || '');
        $('#prop-logic-goto-canvas').val(el.goto_canvas || 2);

        // 5. DATA TAB (#insp-tab-data)
        $('#prop-default-value').val(el.defaultValue || '');
        $('#prop-dynamic-default').prop('checked', !!el.dynamicDefault);
        $('#prop-dynamic-param').val(el.paramName || el.dynamicParam || ''); $('#prop-dynamic-param-row').toggle(!!el.dynamicDefault); /* ZAPPOPPOP_DYN_ROW */
        $('#prop-readonly').prop('checked', !!el.readOnly);
        $('#prop-save-to-db').prop('checked', el.saveToDb !== false);

        // 6. ADVANCED TAB (#insp-tab-advanced)
        $('#prop-field-name').val(el.fieldName || el.fieldId || '');
        $('#prop-input-mask').val(el.inputMask || '');
        $('#prop-validator-error').val(el.errorMessage || '');
        /* ZAPPOPPOP_ADV_TAB: hydrate */
        $('#prop-validator-equal').val(el.validatorEqual || '');
        $('#prop-validator-list').val(el.validatorList || '');
        var advV = Array.isArray(el.validators) ? el.validators : [];
        $('#zappoppop-val-extra-equal').toggle(advV.indexOf('equal') !== -1);
        $('#zappoppop-val-extra-inarray').toggle(advV.indexOf('inarray') !== -1);
        /* ZAPPOPPOP_REQ_MSG: hydrate */
        $('#insp-row-gp-required').toggle(isForm);
        $('#prop-gp-required').prop('checked', (el.required === undefined && String(el.type || '').toLowerCase() === 'email' ? true : !!el.required) /* ZAPPOPPOP_EMAIL_REQ_DEFAULT */);
        $('#prop-gp-required-msg').val(el.requiredMessage || '');
        $('#insp-row-gp-required-msg').toggle(isForm && (el.required === undefined && String(el.type || '').toLowerCase() === 'email' ? true : !!el.required) /* ZAPPOPPOP_EMAIL_REQ_DEFAULT */);

        var filters = Array.isArray(el.filters) ? el.filters : [];
        $('.prop-filter-chk').each(function() {
            $(this).prop('checked', filters.indexOf($(this).val()) !== -1);
        });

        var validators = Array.isArray(el.validators) ? el.validators : [];
        if (el.required && validators.indexOf('required') === -1) validators.push('required');
        $('.prop-validator-chk').each(function() {
            $(this).prop('checked', validators.indexOf($(this).val()) !== -1);
        });
    }

    // =========================================================================
    // AUTHORITATIVE LAYER SELECTION DISPATCHER (CALLED BY CAPTURE & BUBBLE)
    // =========================================================================
    var _selectingGuard = false;
    window.selectLayerEverywhere = function(idOrObj) {
        if (_selectingGuard) return;
        var el = resolveElement(idOrObj);
        if (!el) return;

        _selectingGuard = true;
        try {
            var actualId = String(el.id);
            var cleanId = normalizeId(actualId);

            // 1. Update Core & Canvas state
            if (window.ZapPopPopBuilderCore && window.ZapPopPopBuilderCore.state) {
                window.ZapPopPopBuilderCore.state.activeId = actualId;
            }
            if (window.ZapPopPopBuilderCanvas) {
                window.ZapPopPopBuilderCanvas.selectedId = actualId;
            }

            // 2. Highlight in Floating Layers Panel
            $('#zappoppop-layers-list .zappoppop-layer-item, #zappoppop-layers-list > *').removeClass('active selected is-selected');
            $('#zappoppop-layers-list [data-id="' + actualId + '"], #zappoppop-layers-list [data-id="' + cleanId + '"], #zappoppop-layers-list [data-id="el-' + cleanId + '"], #zappoppop-layers-list [data-id="zappoppop-el-' + cleanId + '"]')
                .addClass('active selected is-selected');

            // 3. Highlight on Canvas Stage
            $('.zappoppop-canvas-item, .zappoppop-element').removeClass('zappoppop-selected selected is-selected');
            $('#zappoppop-el-' + cleanId + ', #el-' + cleanId + ', .zappoppop-canvas-item[data-id="' + actualId + '"], .zappoppop-canvas-item[data-id="' + cleanId + '"]')
                .addClass('zappoppop-selected selected is-selected');

            // 4. Open Inspector Drawer & Push Layers Panel to right: 376px
            if (window.ZapPopPopBuilderInspector) {
                window.ZapPopPopBuilderInspector.activeElementId = actualId;
                window.ZapPopPopBuilderInspector.activeId = actualId;
            }
            var $drawer = $('#zappoppop-inspector-drawer');
            $drawer.css('display', 'flex').removeClass('is-closed').addClass('is-open open');
            $('body, .zappoppop-builder-workspace, .zappoppop-main-frame').addClass('panel-open inspector-open');

            if (window.ZapPopPopBuilderLayers && typeof window.ZapPopPopBuilderLayers.pushForInspector === 'function') {
                window.ZapPopPopBuilderLayers.pushForInspector(true);
            } else {
                $('#zappoppop-floating-layers-panel').css('right', '376px');
            }

            // 5. Hydrate all 5 Tabs
            hydrateAllFiveTabs(el);
        } finally {
            _selectingGuard = false;
        }
    };

    function bindMasterHooks() {
        ensureFiveTabsDom();

        // Hook ZapPopPopBuilderInspector methods
        var insp = window.ZapPopPopBuilderInspector;
        if (insp) {
            insp.switchTab = executeSwitchTab;
            insp.sanitizeLayerInspectorForElement = hydrateAllFiveTabs;
            if (!insp._masterOpenWrapped) {
                insp._masterOpenWrapped = true;
                var origOpen = insp.open;
                insp.open = function(id) {
                    var res;
                    try {
                        if (typeof origOpen === 'function') res = origOpen.apply(this, arguments);
                    } catch (e) {}
                    var el = resolveElement(id);
                    if (el) hydrateAllFiveTabs(el);
                    return res;
                };
            }
        }

        // 1. CAPTURE-PHASE CLICK LISTENER ON WINDOW (Cannot be blocked by e.stopPropagation()!)
        if (!window._zappoppopMasterCaptureBound) {
            window._zappoppopMasterCaptureBound = true;
            window.addEventListener('click', function(e) {
                var target = e.target;
                if (!target || !target.closest) return;

                // A. Click on Layer Settings Tab Button (Content | Style | Logic | Data | Advanced)
                var tabBtn = target.closest('#zappoppop-inspector-drawer .zappoppop-insp-tab, #zappoppop-inspector-tabs-bar [data-tab], .zappoppop-inspector-tabs [data-tab]');
                if (tabBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    var tabName = tabBtn.getAttribute('data-tab') || 'basic';
                    executeSwitchTab(tabName);
                    return;
                }

                // B. Click on Layer Item inside Floating Layers Panel
                var layerRow = target.closest('#zappoppop-floating-layers-panel .zappoppop-layer-item, #zappoppop-layers-list .zappoppop-layer-item, #zappoppop-layers-list > li, #zappoppop-layers-list > div');
                if (layerRow) {
                    // Allow action buttons (lock, hide, duplicate, delete) or rename input to work normally
                    if (target.closest('.zappoppop-layer-action-btn, .zappoppop-layer-actions, .zappoppop-layer-btn-lock, .zappoppop-layer-btn-vis, .zappoppop-layer-btn-dup, .zappoppop-layer-btn-del, button, input')) {
                        return;
                    }
                    e.preventDefault();
                    e.stopPropagation();
                    var rawId = layerRow.getAttribute('data-id') || layerRow.getAttribute('data-layer-id') || layerRow.getAttribute('data-el-id') || layerRow.id || '';
                    if (rawId) {
                        window.selectLayerEverywhere(rawId);
                        try {
                            if (window.ZapPopPopBuilderLayers && typeof window.ZapPopPopBuilderLayers.openLayerInspector === 'function') {
                                window.ZapPopPopBuilderLayers.openLayerInspector(rawId);
                            }
                        } catch (errOpen) {}
                    }
                    return;
                }
            }, true); // <-- TRUE = Capture Phase!
        }

        // 2. Two-way live binding for Style, Logic, Data & Advanced inputs
        $(document)
            .off('input.zappoppopMasterSync change.zappoppopMasterSync')
            .on('input.zappoppopMasterSync change.zappoppopMasterSync', '#insp-tab-style input, #insp-tab-style select, #insp-tab-logic input, #insp-tab-logic select, #insp-tab-data input, #insp-tab-advanced input', function() {
                var el = resolveElement();
                if (!el) return;
                var $inp = $(this);
                var prop = $inp.data('prop') || $inp.data('data-prop') || $inp.data('adv-prop') || $inp.data('logic-prop');
                var val = $inp.is(':checkbox') ? $inp.is(':checked') : $inp.val();

                if (this.id === 'prop-opacity') {
                    $('#prop-opacity-val').text(val + '%');
                    el.opacity = val;
                } else if (this.id === 'prop-bg-transparent') {
                    el.transparentFill = val;
                    if (val) el.bgColor = 'transparent';
                } else if (this.id === 'prop-logic-enabled') {
                    el.logicEnabled = val;
                    $('#zappoppop-logic-body').toggle(!!val);
                } else if (prop) {
                    el[prop] = val;
                }

                // Live apply CSS to stage node
                var cleanId = normalizeId(el.id);
                var $node = $('#zappoppop-el-' + cleanId + ', #el-' + cleanId + ', .zappoppop-canvas-item[data-id="' + el.id + '"]').first();
                if ($node.length) {
                    if (el.opacity !== undefined) $node.css('opacity', (parseFloat(el.opacity) > 1 ? parseFloat(el.opacity) / 100 : el.opacity));
                    if (el.bgColor) $node.css('background-color', el.transparentFill ? 'transparent' : el.bgColor);
                    if (el.color) $node.css('color', el.color);
                    if (el.fontSize) $node.css('font-size', el.fontSize + 'px');
                    if (el.fontWeight) $node.css('font-weight', el.fontWeight);
                    if (el.lineHeight) $node.css('line-height', el.lineHeight);
                    if (el.letterSpacing !== undefined) $node.css('letter-spacing', el.letterSpacing + 'px');
                    if (el.borderRadius !== undefined) $node.css('border-radius', el.borderRadius + 'px');
                    if (el.borderWidth !== undefined) $node.css('border', el.borderWidth + 'px ' + (el.borderStyle || 'solid') + ' ' + (el.borderColor || '#334155'));
                }
            });

        // 3. Repeater buttons (+ Add a style, + Add rule, Form Align)
        $(document)
            .off('click.zappoppopMasterRepeaters')
            .on('click.zappoppopMasterRepeaters', '#zappoppop-btn-add-inline-style, #btn-add-logic-rule, #zappoppop-form-align-group .zappoppop-form-align-btn', function(e) {
                e.preventDefault();
                var el = resolveElement();
                if (!el) return;
                if (this.id === 'zappoppop-btn-add-inline-style') {
                    if (!Array.isArray(el.inlineStyles)) el.inlineStyles = [];
                    el.inlineStyles.push({ prop: '', val: '' });
                    renderInlineStylesRepeater(el);
                } else if (this.id === 'btn-add-logic-rule') {
                    if (!Array.isArray(el.logicRules)) el.logicRules = [];
                    el.logicRules.push({ field: '', operator: 'equal', value: '' });
                    renderLogicRulesList(el);
                } else if ($(this).hasClass('zappoppop-form-align-btn')) {
                    el.textAlign = $(this).attr('data-align') || 'left';
                    $('#zappoppop-form-align-group .zappoppop-form-align-btn').removeClass('active button-primary');
                    $(this).addClass('active button-primary');
                }
            });
    }

    $(function() {
        bindMasterHooks();
        setTimeout(bindMasterHooks, 150);
        setTimeout(bindMasterHooks, 450);
    });
    bindMasterHooks();

})(window, jQuery);
/* === ZAPPOPPOP_MASTER_LAYER_INSPECTOR_AND_LAYER_ENGINE_END === */

/* === ZAPPOPPOP_INSPECTOR_ICON_ONLY_ACTIVE_ON_CLICK_START === */
(function(window, $) {
    'use strict';

    // Reset all inspector icons/presets to neutral (unclicked) when switching layers
    function resetInspectorIconsOnOpen() {
        $('#zappoppop-inspector-drawer')
            .find('.zappoppop-icon-preset, .zappoppop-form-icon-preset, .zappoppop-icon-grid-btn, .zappoppop-form-align-btn, .zappoppop-insp-icon-btn')
            .removeClass('active is-active button-primary');
        $('#zappoppop-inspector-header-icon').css('color', '#94a3b8');
    }

    function bindIconActiveOnlyOnClick() {
        // 1. Capture-phase listener so clicking any icon inside #zappoppop-inspector-drawer
        //    exclusively activates ONLY the clicked icon.
        if (!window._zappoppopIconClickOnlyBound) {
            window._zappoppopIconClickOnlyBound = true;

            window.addEventListener('click', function(e) {
                var t = e.target;
                if (!t || !t.closest) return;

                var drawer = t.closest('#zappoppop-inspector-drawer');
                if (!drawer) return;

                // A. Element 7 Icon Presets & Icon Picker Grid (#panel-elem-icon)
                var iconPresetBtn = t.closest('.zappoppop-icon-preset, .zappoppop-icon-grid-btn');
                if (iconPresetBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    var iconCls = iconPresetBtn.getAttribute('data-icon') || '';
                    var $allIconBtns =$(drawer).find('.zappoppop-icon-preset, .zappoppop-icon-grid-btn');
                    $allIconBtns.removeClass('active is-active');$(drawer).find('[data-icon="' + iconCls + '"]').addClass('active is-active');
                    $('#prop-icon-class').val(iconCls).trigger('input').trigger('change');
                    return;
                }

                // B. Form Input Icon Presets (Left / Right Input Icons in Style Tab)
                var formIconBtn = t.closest('.zappoppop-form-icon-preset');
                if (formIconBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    var targetSel = formIconBtn.getAttribute('data-target') || '#prop-form-icon-left';
                    var glyph = formIconBtn.getAttribute('data-glyph') || '';

                    // Scope active state strictly to buttons sharing the same data-target (left vs right)
                    var $groupBtns =$(drawer).find('.zappoppop-form-icon-preset[data-target="' + targetSel + '"]');
                    $groupBtns.removeClass('active is-active');

                    // Only mark active if a non-empty icon glyph was clicked (Clear resets all to inactive)
                    if (glyph !== '') {
                        $(formIconBtn).addClass('active is-active');
                    }
                    $(targetSel).val(glyph).trigger('input').trigger('change');
                    return;
                }

                // C. Form Text Alignment Buttons (Left | Center | Right)
                var alignBtn = t.closest('.zappoppop-form-align-btn');
                if (alignBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    var alignVal = alignBtn.getAttribute('data-align') || 'left';
                    $('#zappoppop-form-align-group .zappoppop-form-align-btn').removeClass('active is-active button-primary');
                    $(alignBtn).addClass('active is-active');
                    if (window.ZapPopPopBuilderInspector) {
                        var el = window.ZapPopPopBuilderCanvas && typeof window.ZapPopPopBuilderCanvas.getActiveElements === 'function'
                            ? (window.ZapPopPopBuilderCanvas.getActiveElements() || []).find(function(x) {
                                return x && String(x.id) === String(window.ZapPopPopBuilderInspector.activeElementId);
                            })
                            : null;
                        if (el) {
                            el.textAlign = alignVal;
                            if (window.ZapPopPopBuilderCanvas && typeof window.ZapPopPopBuilderCanvas.renderCanvas === 'function') {
                                window.ZapPopPopBuilderCanvas.renderCanvas();
                            }
                        }
                    }
                    return;
                }

                // D. Titlebar Gear Icon (#zappoppop-inspector-header-icon)
                var headerIcon = t.closest('#zappoppop-inspector-header-icon, .zappoppop-insp-icon-btn');
                if (headerIcon) {
                    var $hi = $(headerIcon);$hi.toggleClass('active is-active');
                    $hi.css('color',$hi.hasClass('active') ? '#38bdf8' : '#94a3b8');
                    return;
                }
            }, true);
        }

        // 2. When the user types manually into #prop-icon-class, #prop-form-icon-left, or #prop-form-icon-right,
        //    clear preset button active states unless an exact preset was clicked
        $(document)
            .off('keydown.zappoppopManualIconInput')
            .on('keydown.zappoppopManualIconInput', '#prop-icon-class, #prop-form-icon-left, #prop-form-icon-right', function() {
                if (this.id === 'prop-icon-class') {
                    $('#panel-elem-icon .zappoppop-icon-preset').removeClass('active is-active');
                } else {
                    $('.zappoppop-form-icon-preset[data-target="#' + this.id + '"]').removeClass('active is-active');
                }
            });
    }

    // Hook into selectLayerEverywhere so opening a layer resets unclicked icons to neutral
    var origSelectEverywhere = window.selectLayerEverywhere;
    if (typeof origSelectEverywhere === 'function' && !origSelectEverywhere._iconResetWrapped) {
        var wrappedSelect = function(idOrObj) {
            var res = origSelectEverywhere.apply(this, arguments);
            resetInspectorIconsOnOpen();
            return res;
        };
        wrappedSelect._iconResetWrapped = true;
        window.selectLayerEverywhere = wrappedSelect;
    }

    $(function() {
        resetInspectorIconsOnOpen();
        bindIconActiveOnlyOnClick();
        setTimeout(bindIconActiveOnlyOnClick, 150);
    });
    bindIconActiveOnlyOnClick();

})(window, jQuery);

/* === ZAPPOPPOP_VIDEO_SIZE_PRESET_START === */
/* Video layers: default COORDINATES & BOUNDS preset WIDTH 200px x HEIGHT 150px.
   Applied on creation, plus an "Apply preset" button in the Content tab. */
(function(window, $) {
    'use strict';

    var PRESET_W = 200;
    var PRESET_H = 150;
    var TYPE = 'video';

    function cleanId(raw) {
        return String(raw || '').replace(/^zappoppop-el-/, '').replace(/^el-/, '').trim();
    }

    function getList() {
        var core = window.ZapPopPopBuilderCore;
        if (!core || !core.state || !core.state.canvases) return null;
        var cur = core.state.currentCanvas || 1;
        return core.state.canvases[cur] || null;
    }

    function getActiveEl() {
        var core = window.ZapPopPopBuilderCore;
        var list = getList();
        if (!core || !list || !core.state.activeId) return null;
        var id = cleanId(core.state.activeId);
        for (var i = 0; i < list.length; i++) {
            if (list[i] && cleanId(list[i].id) === id) return list[i];
        }
        return null;
    }

    function isVideo(el) {
        return !!el && String(el.type || '').toLowerCase().trim() === TYPE;
    }

    function applyPreset(el) {
        el.width = PRESET_W;
        el.height = PRESET_H;
        $('#prop-width, #prop-size-width').val(PRESET_W);
        $('#prop-height, #prop-size-height').val(PRESET_H);
        var id = cleanId(el.id);
        var $node = $('#canvas-elem-' + id + ', #zappoppop-el-' + id + ', #el-' + id + ', .zappoppop-canvas-item[data-id="' + id + '"]').first();
        if ($node.length) {
            $node.css({ width: PRESET_W + 'px', height: PRESET_H + 'px' });
        }
    }

    function renderAndRecord(label) {
        if (window.ZapPopPopBuilderCanvas && typeof window.ZapPopPopBuilderCanvas.renderCanvas === 'function') {
            window.ZapPopPopBuilderCanvas.renderCanvas();
        }
        if (window.ZapPopPopBuilderCore && typeof window.ZapPopPopBuilderCore.pushHistory === 'function') {
            window.ZapPopPopBuilderCore.pushHistory(label);
        }
    }

    // Wrap addElement so every new Video layer starts at the preset size
    function wrapAddElement() {
        var canvas = window.ZapPopPopBuilderCanvas;
        if (!canvas || typeof canvas.addElement !== 'function' || canvas.__zappoppopVideoPresetWrapped) return;
        canvas.__zappoppopVideoPresetWrapped = true;
        var orig = canvas.addElement;
        canvas.addElement = function(type) {
            var before = getList() ? getList().length : 0;
            var res = orig.apply(this, arguments);
            if (String(type || '').toLowerCase().trim() === TYPE) {
                var list = getList() || [];
                var created = (res && typeof res === 'object' && res.type) ? res : null;
                if (!created && list.length > before) created = list[list.length - 1];
                if (!created) {
                    for (var i = list.length - 1; i >= 0; i--) {
                        if (isVideo(list[i])) { created = list[i]; break; }
                    }
                }
                if (isVideo(created)) {
                    applyPreset(created);
                    renderAndRecord('Video Size Preset');
                }
            }
            return res;
        };
    }

    // Content tab button inside COORDINATES & BOUNDS
    function ensurePresetButton() {
        var $coords = $('#insp-section-coords');
        if (!$coords.length || $('#zappoppop-video-preset-btn').length) return;
        $coords.append(
            '<div id="zappoppop-video-preset-wrap" style="display:none; margin-top:8px;">' +
                '<button type="button" id="zappoppop-video-preset-btn" class="button button-small" ' +
                'style="width:100%; font-size:11px; font-weight:700;">Apply Video Preset (200 x 150)</button>' +
            '</div>'
        );
    }

    function refreshPresetButton() {
        ensurePresetButton();
        $('#zappoppop-video-preset-wrap').toggle(isVideo(getActiveEl()));
    }

    function bindPresetButton() {
        $(document).off('click.zappoppopVideoPreset').on('click.zappoppopVideoPreset', '#zappoppop-video-preset-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var el = getActiveEl();
            if (!isVideo(el)) return;
            applyPreset(el);
            renderAndRecord('Apply Video Size Preset');
        });
    }

    function hookInspector() {
        var insp = window.ZapPopPopBuilderInspector;
        if (!insp || insp.__zappoppopVideoPresetHooked) return;
        insp.__zappoppopVideoPresetHooked = true;
        if (typeof insp.open === 'function') {
            var origOpen = insp.open;
            insp.open = function() {
                var res = origOpen.apply(this, arguments);
                setTimeout(refreshPresetButton, 20);
                return res;
            };
        }
    }

    $(function() {
        wrapAddElement();
        bindPresetButton();
        hookInspector();
        refreshPresetButton();
        setTimeout(wrapAddElement, 150);
        setTimeout(hookInspector, 150);
        setInterval(refreshPresetButton, 500);
    });
})(window, jQuery);
/* === ZAPPOPPOP_VIDEO_SIZE_PRESET_END === */

/* === ZAPPOPPOP_FA_ICON_CONTROLS_START === */
/* Font Awesome / Vector Icon (types: icon, fontawesome).
   Content tab: colour picker (el.iconColor) + COORDINATES preset 40 x 40. */
(function(window, $) {
    'use strict';

    var TYPES = ['icon', 'fontawesome'];
    var PRESET = 40;
    var DEFAULT_COLOR = '#38bdf8';

    function cleanId(raw) {
        return String(raw || '').replace(/^zappoppop-el-/, '').replace(/^el-/, '').trim();
    }

    function getList() {
        var core = window.ZapPopPopBuilderCore;
        if (!core || !core.state || !core.state.canvases) return null;
        return core.state.canvases[core.state.currentCanvas || 1] || null;
    }

    function getActiveEl() {
        var core = window.ZapPopPopBuilderCore;
        var list = getList();
        if (!core || !list || !core.state.activeId) return null;
        var id = cleanId(core.state.activeId);
        for (var i = 0; i < list.length; i++) {
            if (list[i] && cleanId(list[i].id) === id) return list[i];
        }
        return null;
    }

    function isIcon(el) {
        return !!el && TYPES.indexOf(String(el.type || '').toLowerCase().trim()) !== -1;
    }

    function stageNode(el) {
        var id = cleanId(el.id);
        return $('#canvas-elem-' + id + ', #zappoppop-el-' + id + ', #el-' + id + ', .zappoppop-canvas-item[data-id="' + id + '"]').first();
    }

    function applyColorToStage(el) {
        var $node = stageNode(el);
        if (!$node.length) return;
        var $glyph = $node.find('i, .zappoppop-icon, .fa, [class*="fa-"]').first();
        ($glyph.length ? $glyph : $node).css('color', el.iconColor);
    }

    function applySizeToStage(el) {
        var $node = stageNode(el);
        if ($node.length) $node.css({ width: PRESET + 'px', height: PRESET + 'px' });
    }

    function render(label) {
        if (window.ZapPopPopBuilderCanvas && typeof window.ZapPopPopBuilderCanvas.renderCanvas === 'function') {
            window.ZapPopPopBuilderCanvas.renderCanvas();
        }
        if (window.ZapPopPopBuilderCore && typeof window.ZapPopPopBuilderCore.pushHistory === 'function') {
            window.ZapPopPopBuilderCore.pushHistory(label);
        }
    }

    // Colour picker row inside the Icon panel (reuses #prop-icon-color if the drawer already has it)
    function ensureColorPicker() {
        if ($('#prop-icon-color').length) return;
        var $panel = $('#panel-elem-icon');
        if (!$panel.length || $('#zappoppop-fa-color-row').length) return;
        $panel.append(
            '<div id="zappoppop-fa-color-row" style="display:none; margin-top:8px;">' +
                '<label for="zappoppop-fa-color" style="display:block; font-size:10px; font-weight:700; color:#94a3b8; margin-bottom:4px; text-transform:uppercase;">ICON COLOR</label>' +
                '<input type="color" id="zappoppop-fa-color" value="' + DEFAULT_COLOR + '" style="width:100%; height:32px; padding:2px; background:#090d16; border:1px solid #1e293b; border-radius:0px; cursor:pointer;" />' +
            '</div>'
        );
    }

    function colorInput() {
        return $('#prop-icon-color').length ? $('#prop-icon-color') : $('#zappoppop-fa-color');
    }

    function refreshUi() {
        ensureColorPicker();
        var el = getActiveEl();
        var show = isIcon(el);
        $('#zappoppop-fa-color-row').toggle(show && !$('#prop-icon-color').length);
        $('#zappoppop-fa-size-wrap').toggle(show);
        if (show) {
            colorInput().val(el.iconColor || DEFAULT_COLOR);
        }
    }

    function ensureSizeButton() {
        var $coords = $('#insp-section-coords');
        if (!$coords.length || $('#zappoppop-fa-size-wrap').length) return;
        $coords.append(
            '<div id="zappoppop-fa-size-wrap" style="display:none; margin-top:8px;">' +
                '<button type="button" id="zappoppop-fa-size-btn" class="button button-small" style="width:100%; font-size:11px; font-weight:700;">Apply Icon Preset (40 x 40)</button>' +
            '</div>'
        );
    }

    function bindControls() {
        $(document)
            .off('input.zappoppopFaColor change.zappoppopFaColor', '#prop-icon-color, #zappoppop-fa-color')
            .on('input.zappoppopFaColor change.zappoppopFaColor', '#prop-icon-color, #zappoppop-fa-color', function(e) {
                var el = getActiveEl();
                if (!isIcon(el)) return;
                el.iconColor = $(this).val();
                el.color = el.iconColor;
                applyColorToStage(el);
                if (e.type === 'change') render('Change Icon Color');
            });

        $(document)
            .off('click.zappoppopFaSize')
            .on('click.zappoppopFaSize', '#zappoppop-fa-size-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var el = getActiveEl();
                if (!isIcon(el)) return;
                el.width = PRESET;
                el.height = PRESET;
                $('#prop-width, #prop-size-width').val(PRESET);
                $('#prop-height, #prop-size-height').val(PRESET);
                applySizeToStage(el);
                render('Apply Icon Size Preset');
            });
    }

    function wrapAddElement() {
        var canvas = window.ZapPopPopBuilderCanvas;
        if (!canvas || typeof canvas.addElement !== 'function' || canvas.__zappoppopIconPresetWrapped) return;
        canvas.__zappoppopIconPresetWrapped = true;
        var orig = canvas.addElement;
        canvas.addElement = function(type) {
            var before = getList() ? getList().length : 0;
            var res = orig.apply(this, arguments);
            if (TYPES.indexOf(String(type || '').toLowerCase().trim()) !== -1) {
                var list = getList() || [];
                var created = (res && typeof res === 'object' && res.type) ? res : null;
                if (!created && list.length > before) created = list[list.length - 1];
                if (isIcon(created)) {
                    created.width = PRESET;
                    created.height = PRESET;
                    if (!created.iconColor) created.iconColor = DEFAULT_COLOR;
                    applySizeToStage(created);
                    render('New Icon Preset');
                }
            }
            return res;
        };
    }

    function hookInspector() {
        var insp = window.ZapPopPopBuilderInspector;
        if (!insp || insp.__zappoppopFaHooked) return;
        insp.__zappoppopFaHooked = true;
        if (typeof insp.open === 'function') {
            var origOpen = insp.open;
            insp.open = function() {
                var res = origOpen.apply(this, arguments);
                setTimeout(refreshUi, 20);
                return res;
            };
        }
    }

    $(function() {
        ensureColorPicker();
        ensureSizeButton();
        bindControls();
        wrapAddElement();
        hookInspector();
        refreshUi();
        setTimeout(wrapAddElement, 150);
        setTimeout(hookInspector, 150);
        setInterval(function() { ensureSizeButton(); refreshUi(); }, 500);
    });
})(window, jQuery);
/* === ZAPPOPPOP_FA_ICON_CONTROLS_END === */

/* === ZAPPOPPOP_FA_PREVIEW_RENDER_START === */
/* Font Awesome / Vector Icon stage preview.
   1. Loads Font Awesome CSS in the builder when the page does not already have it.
   2. Uses one icon renderer for icon/fontawesome layers (Dashicons or Font Awesome classes).
   3. Re-applies itself so later registry wrappers cannot replace it. */
(function(window, $) {
    'use strict';

    var TYPES = ['icon', 'fontawesome'];
    var FA_HREF = (window.zappoppop_vars && window.zappoppop_vars.fa_url) || '';
    var FA_LINK_ID = 'zappoppop-fa-css';
    var DEFAULT_CLASS = 'fas fa-star';

    function hasFontAwesomeCss() {
        if (window.FontAwesome) return true;
        var links = document.querySelectorAll('link[rel="stylesheet"]');
        for (var i = 0; i < links.length; i++) {
            if (/font-?awesome|fontawesome/i.test(links[i].href || '')) return true;
        }
        return false;
    }

    function ensureFontAwesome(onReady) {
        if (hasFontAwesomeCss()) { if (onReady) onReady(); return; }
        if (!FA_HREF || document.getElementById(FA_LINK_ID)) return;
        var link = document.createElement('link');
        link.id = FA_LINK_ID;
        link.rel = 'stylesheet';
        link.href = FA_HREF;
        link.crossOrigin = 'anonymous';
        link.onload = function() { if (onReady) onReady(); };
        (document.head || document.documentElement).appendChild(link);
    }

    function normalizeClass(raw) {
        var c = $.trim(String(raw || ''));
        if (!c) return DEFAULT_CLASS;
        if (c.indexOf('dashicons-') === 0) return 'dashicons ' + c;
        if (c.indexOf('dashicons ') === 0) return c;
        if (/^(fa|fas|far|fab|fal|fad|fa-solid|fa-regular|fa-brands)\b/.test(c)) {
            // "fa-star" alone -> "fas fa-star"
            if (c.indexOf(' ') === -1 && c.indexOf('fa-') === 0) return 'fas ' + c;
            return c;
        }
        // Bare glyph text or emoji: render as text, not an icon class
        return null;
    }

    function iconMarkup(el) {
        var raw = el.iconClass || el.content || '';
        var cls = normalizeClass(raw);
        var color = el.iconColor || el.color || '#38bdf8';
        var size = parseInt(el.iconSize, 10) || 36;
        var rot = parseInt(el.rotate, 10) || 0;
        var inner;
        if (cls === null) {
            inner = '<span style="font-size:' + size + 'px;line-height:1;">' + $('<div>').text(raw).html() + '</span>';
        } else {
            inner = '<i class="' + cls.replace(/"/g, '') + '" style="font-size:' + size + 'px;line-height:1;color:' + color + ';"></i>';
        }
        return '<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:' + color +
               ';transform:rotate(' + rot + 'deg);pointer-events:none;">' + inner + '</div>';
    }

    function wrapCanvasMarkup() {
        var canvas = window.ZapPopPopBuilderCanvas;
        if (!canvas || typeof canvas.getInnerMarkup !== 'function') return false;
        if (canvas.getInnerMarkup.__zappoppopFaWrapped) return true;
        var orig = canvas.getInnerMarkup;
        var wrapped = function(el) {
            if (el && TYPES.indexOf(String(el.type || '').toLowerCase().trim()) !== -1) {
                return iconMarkup(el);
            }
            return orig.apply(this, arguments);
        };
        wrapped.__zappoppopFaWrapped = true;
        canvas.getInnerMarkup = wrapped;
        return true;
    }

    function rerender() {
        if (window.ZapPopPopBuilderCanvas && typeof window.ZapPopPopBuilderCanvas.renderCanvas === 'function') {
            window.ZapPopPopBuilderCanvas.renderCanvas();
        }
    }

    $(function() {
        ensureFontAwesome(function() { rerender(); });
        var tries = 0;
        var timer = setInterval(function() {
            tries++;
            var ok = wrapCanvasMarkup();
            if (ok) rerender();
            if (ok && tries > 2) clearInterval(timer);
            if (tries > 60) clearInterval(timer);
        }, 250);
        // Keep the wrapper in place if the registry re-wraps the canvas later
        setInterval(function() {
            var canvas = window.ZapPopPopBuilderCanvas;
            if (canvas && typeof canvas.getInnerMarkup === 'function' && !canvas.getInnerMarkup.__zappoppopFaWrapped) {
                if (wrapCanvasMarkup()) rerender();
            }
        }, 1000);
    });
})(window, jQuery);
/* === ZAPPOPPOP_FA_PREVIEW_RENDER_END === */

/* === ZAPPOPPOP_MEDIA_STYLE_TRIM_START === */
/* ZAPPOPPOP_MEDIA_STYLE_TRIM_V2: per-type Style-tab section trim.
   Hides via a stylesheet keyed on <html data-zappoppop-trim-type="...">, so jQuery .show()
   calls elsewhere cannot re-display hidden sections. Visibility only; DOM and data untouched. */
(function(window, $) {
    'use strict';

    var HIDE_MAP = {
        'image':      ['insp-section-typography', 'insp-section-custom-css', 'insp-section-bg-color'],
        'shape':      ['insp-section-typography', 'insp-section-custom-css'],
        'rect':       ['insp-section-typography', 'insp-section-custom-css'],
        'rectangle':  ['insp-section-typography', 'insp-section-custom-css'],
        'square':     ['insp-section-typography', 'insp-section-custom-css'],
        'video':      ['insp-section-opacity', 'insp-section-bg-color', 'insp-section-custom-css'],
        'label':      ['insp-section-opacity', 'insp-section-custom-css'],
        'html':       ['insp-section-opacity', 'insp-section-custom-css'],
        'close':      ['insp-section-opacity', 'insp-section-typography', 'insp-section-padding', 'insp-section-shadow', 'insp-section-custom-css'],
        'close_icon': ['insp-section-opacity', 'insp-section-typography', 'insp-section-padding', 'insp-section-shadow', 'insp-section-custom-css'],
        'icon':       ['insp-section-typography', 'insp-section-padding', 'insp-section-shadow', 'insp-section-custom-css'],
        'fontawesome':['insp-section-typography', 'insp-section-padding', 'insp-section-shadow', 'insp-section-custom-css'],
        'button':     ['insp-section-opacity', 'insp-section-custom-css'],
        'link':       ['insp-section-opacity', 'insp-section-custom-css'],
        'link_btn':   ['insp-section-opacity', 'insp-section-custom-css'],
        'step_btn':   ['insp-section-opacity', 'insp-section-custom-css']
    };

    var STYLE_ID = 'zappoppop-style-trim-v2';
    var ATTR = 'data-zappoppop-trim-type';

    function buildCss() {
        var rules = [];
        Object.keys(HIDE_MAP).forEach(function(type) {
            HIDE_MAP[type].forEach(function(id) {
                rules.push('html[' + ATTR + '="' + type + '"] #' + id + ' { display: none !important; }');
            });
        });
        return rules.join('\n');
    }

    function ensureStyle() {
        var s = document.getElementById(STYLE_ID);
        if (!s) {
            s = document.createElement('style');
            s.id = STYLE_ID;
            (document.head || document.documentElement).appendChild(s);
        }
        var css = buildCss();
        if (s.textContent !== css) s.textContent = css;
    }

    function cleanId(raw) {
        return String(raw || '').replace(/^zappoppop-el-/, '').replace(/^el-/, '').trim();
    }

    function getActiveType() {
        var core = window.ZapPopPopBuilderCore;
        if (core && core.state && core.state.activeId) {
            var activeId = cleanId(core.state.activeId);
            var cur = core.state.currentCanvas || 1;
            var list = (core.state.canvases && core.state.canvases[cur]) || [];
            for (var i = 0; i < list.length; i++) {
                if (list[i] && cleanId(list[i].id) === activeId) {
                    return String(list[i].type || '').toLowerCase().trim();
                }
            }
        }
        var $sel = $('.zappoppop-canvas-item.zappoppop-selected, .zappoppop-canvas-item.selected').first();
        return String($sel.attr('data-type') || '').toLowerCase().trim();
    }

    function applyTrim() {
        ensureStyle();
        var type = getActiveType();
        var root = document.documentElement;
        if (HIDE_MAP.hasOwnProperty(type)) {
            if (root.getAttribute(ATTR) !== type) root.setAttribute(ATTR, type);
        } else if (root.hasAttribute(ATTR)) {
            root.removeAttribute(ATTR);
        }
    }

    function scheduleTrim() {
        applyTrim();
        setTimeout(applyTrim, 0);
        setTimeout(applyTrim, 60);
        setTimeout(applyTrim, 200);
    }

    function hookInspector() {
        var insp = window.ZapPopPopBuilderInspector;
        if (!insp || insp.__zappoppopMediaTrimV2Hooked) return;
        insp.__zappoppopMediaTrimV2Hooked = true;

        if (typeof insp.open === 'function') {
            var origOpen = insp.open;
            insp.open = function() {
                var res = origOpen.apply(this, arguments);
                scheduleTrim();
                return res;
            };
        }
        if (typeof insp.switchTab === 'function') {
            var origSwitch = insp.switchTab;
            insp.switchTab = function() {
                var res = origSwitch.apply(this, arguments);
                scheduleTrim();
                return res;
            };
        }
    }

    // Capture phase: runs even when other handlers call stopPropagation()
    window.addEventListener('click', function(e) {
        var t = e.target;
        if (!t || !t.closest) return;
        if (t.closest('.zappoppop-canvas-item, .zappoppop-layer-item, .zappoppop-insp-tab, #zappoppop-inspector-drawer')) {
            scheduleTrim();
        }
    }, true);

    $(function() {
        ensureStyle();
        hookInspector();
        scheduleTrim();
        setTimeout(hookInspector, 150);
        setTimeout(hookInspector, 450);
        setInterval(applyTrim, 400); // covers selection changes made without a click event
    });
})(window, jQuery);
/* === ZAPPOPPOP_MEDIA_STYLE_TRIM_END === */

/* === ZAPPOPPOP_LABEL_TEXT_ALIGN_START === */
/* Label & Html layers: TEXT ALIGNMENT section in Style tab (left / center / right).
   Writes to the element model (el.textAlign) and the stage node. */
(function(window, $) {
    'use strict';

    var SECTION_ID = 'insp-section-text-align';
    var ALIGNS = ['left', 'center', 'right'];
    var TARGET_TYPES = ['label', 'html'];
    function isTargetEl(el) {
        return !!el && TARGET_TYPES.indexOf(String(el.type || '').toLowerCase().trim()) !== -1;
    }

    function cleanId(raw) {
        return String(raw || '').replace(/^zappoppop-el-/, '').replace(/^el-/, '').trim();
    }

    function getActiveEl() {
        var core = window.ZapPopPopBuilderCore;
        if (!core || !core.state) return null;
        var activeId = cleanId(core.state.activeId);
        if (!activeId) return null;
        var cur = core.state.currentCanvas || 1;
        var list = (core.state.canvases && core.state.canvases[cur]) || [];
        for (var i = 0; i < list.length; i++) {
            if (list[i] && cleanId(list[i].id) === activeId) return list[i];
        }
        return null;
    }

    function ensureSection() {
        var $existing = $('#' + SECTION_ID);
        if ($existing.length) return $existing;
        var pane = document.getElementById('insp-tab-style');
        if (!pane) return $();

        var btns = ALIGNS.map(function(a) {
            var label = a.charAt(0).toUpperCase() + a.slice(1);
            return '<button type="button" class="button zappoppop-label-align-btn" data-align="' + a +
                   '" style="font-size:11px; font-weight:700;">' + label + '</button>';
        }).join('');

        var $s = $(
            '<div class="zappoppop-insp-section" id="' + SECTION_ID + '" style="display:none; background:#0f172a; border:1px solid #1e293b; border-radius:0px; padding:12px; margin-bottom:12px;">' +
                '<div style="font-size:10px; font-weight:800; color:#38bdf8; text-transform:uppercase; margin-bottom:8px;">TEXT ALIGNMENT</div>' +
                '<div id="zappoppop-label-align-group" style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:6px;">' + btns + '</div>' +
            '</div>'
        );

        var anchor = document.getElementById('insp-section-opacity');
        if (anchor) { $(anchor).before($s); } else { $(pane).prepend($s); }
        return $s;
    }

    function paintButtons(align) {
        $('#zappoppop-label-align-group .zappoppop-label-align-btn').each(function() {
            var on = $(this).attr('data-align') === align;
            $(this).toggleClass('active button-primary', on);
        });
    }

    function applyAlignToStage(el) {
        var id = cleanId(el.id);
        var $node = $('#zappoppop-el-' + id + ', #el-' + id + ', #canvas-elem-' + id + ', .zappoppop-canvas-item[data-id="' + id + '"]').first();
        if ($node.length) {
            $node.css('text-align', el.textAlign);
            $node.find('.zappoppop-label-inner, .zappoppop-title-inner, p, h1, h2, h3, h4, h5, h6, div').first().css('text-align', el.textAlign);
        }
    }

    function hydrate() {
        var el = getActiveEl();
        var $s = ensureSection();
        if (!$s.length) return;
        var isLabel = isTargetEl(el);
        if (!isLabel) {
            $s.hide();
            return;
        }
        var align = ALIGNS.indexOf(el.textAlign) !== -1 ? el.textAlign : 'left';
        paintButtons(align);
        $s.show();
    }

    function scheduleHydrate() {
        hydrate();
        setTimeout(hydrate, 0);
        setTimeout(hydrate, 60);
        setTimeout(hydrate, 200);
    }

    function bindAlignButtons() {
        $(document)
            .off('click.zappoppopLabelAlign')
            .on('click.zappoppopLabelAlign', '#zappoppop-label-align-group .zappoppop-label-align-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var el = getActiveEl();
                if (!isTargetEl(el)) return;
                var align = $(this).attr('data-align');
                if (ALIGNS.indexOf(align) === -1) return;

                el.textAlign = align;
                el.text_align = align;
                paintButtons(align);
                applyAlignToStage(el);

                if (window.ZapPopPopBuilderCanvas && typeof window.ZapPopPopBuilderCanvas.renderCanvas === 'function') {
                    window.ZapPopPopBuilderCanvas.renderCanvas();
                }
                if (window.ZapPopPopBuilderCore && typeof window.ZapPopPopBuilderCore.pushHistory === 'function') {
                    window.ZapPopPopBuilderCore.pushHistory('Change Text Alignment');
                }
            });
    }

    function hookInspector() {
        var insp = window.ZapPopPopBuilderInspector;
        if (!insp || insp.__zappoppopLabelAlignHooked) return;
        insp.__zappoppopLabelAlignHooked = true;

        if (typeof insp.open === 'function') {
            var origOpen = insp.open;
            insp.open = function() {
                var res = origOpen.apply(this, arguments);
                scheduleHydrate();
                return res;
            };
        }
        if (typeof insp.switchTab === 'function') {
            var origSwitch = insp.switchTab;
            insp.switchTab = function() {
                var res = origSwitch.apply(this, arguments);
                scheduleHydrate();
                return res;
            };
        }
    }

    // Capture phase: runs even when other handlers call stopPropagation()
    window.addEventListener('click', function(e) {
        var t = e.target;
        if (!t || !t.closest) return;
        if (t.closest('.zappoppop-canvas-item, .zappoppop-layer-item, .zappoppop-insp-tab, #zappoppop-inspector-drawer')) {
            scheduleHydrate();
        }
    }, true);

    $(function() {
        ensureSection();
        bindAlignButtons();
        hookInspector();
        scheduleHydrate();
        setTimeout(hookInspector, 150);
        setTimeout(hookInspector, 450);
    });
})(window, jQuery);
/* === ZAPPOPPOP_LABEL_TEXT_ALIGN_END === */

/* === ZAPPOPPOP_CLOSE_GLYPH_HOVER_START === */
/* Close layers: ICON GLYPH hydration/save + BUTTON HOVER STATE visibility & binding.
   Writes el.content / el.closeGlyph and el.hoverBgColor / hoverTextColor / hoverBorderColor. */
(function(window, $) {
    'use strict';

    var CLOSE_TYPES = ['close', 'close_icon'];
    var GLYPHS = ['×', '✕', '✖', '⊗', 'CLOSE'];
    var DEFAULT_GLYPH = '×';

    function cleanId(raw) {
        return String(raw || '').replace(/^zappoppop-el-/, '').replace(/^el-/, '').trim();
    }

    function getActiveEl() {
        var core = window.ZapPopPopBuilderCore;
        if (!core || !core.state) return null;
        var activeId = cleanId(core.state.activeId);
        if (!activeId) return null;
        var cur = core.state.currentCanvas || 1;
        var list = (core.state.canvases && core.state.canvases[cur]) || [];
        for (var i = 0; i < list.length; i++) {
            if (list[i] && cleanId(list[i].id) === activeId) return list[i];
        }
        return null;
    }

    function isClose(el) {
        return !!el && CLOSE_TYPES.indexOf(String(el.type || '').toLowerCase().trim()) !== -1;
    }

    function stageNode(el) {
        var id = cleanId(el.id);
        return $('#canvas-elem-' + id + ', #zappoppop-el-' + id + ', #el-' + id + ', .zappoppop-canvas-item[data-id="' + id + '"]').first();
    }

    function resolveGlyph(el) {
        if (GLYPHS.indexOf(el.content) !== -1) return el.content;
        if (GLYPHS.indexOf(el.closeGlyph) !== -1) return el.closeGlyph;
        return DEFAULT_GLYPH;
    }

    function hydrateGlyph(el) {
        var $sel = $('#prop-close-glyph');
        if (!$sel.length) return;
        $sel.val(resolveGlyph(el));
    }

    function hydrateHover(el) {
        $('#prop-btn-hover-section').show();
        $('#prop-elem-hover-bg').val(el.hoverBgColor || el.hoverBg || '#0284c7');
        $('#prop-elem-hover-color').val(el.hoverTextColor || el.hoverColor || '#ffffff');
        $('#prop-elem-hover-border').val(el.hoverBorderColor || '#0284c7');
    }

    function applyHoverToStage(el) {
        var $node = stageNode(el);
        if (!$node.length) return;
        $node.attr('data-has-hover', 'true');
        var node = $node[0];
        if (el.hoverBgColor) node.style.setProperty('--zappoppop-hover-bg', el.hoverBgColor);
        if (el.hoverTextColor) node.style.setProperty('--zappoppop-hover-color', el.hoverTextColor);
        if (el.hoverBorderColor) node.style.setProperty('--zappoppop-hover-border', el.hoverBorderColor);
    }

    function render() {
        if (window.ZapPopPopBuilderCanvas && typeof window.ZapPopPopBuilderCanvas.renderCanvas === 'function') {
            window.ZapPopPopBuilderCanvas.renderCanvas();
        }
    }

    function pushHistory(label) {
        if (window.ZapPopPopBuilderCore && typeof window.ZapPopPopBuilderCore.pushHistory === 'function') {
            window.ZapPopPopBuilderCore.pushHistory(label);
        }
    }

    function hydrate() {
        var el = getActiveEl();
        if (!isClose(el)) return;
        hydrateGlyph(el);
        hydrateHover(el);
    }

    function scheduleHydrate() {
        hydrate();
        setTimeout(hydrate, 0);
        setTimeout(hydrate, 60);
        setTimeout(hydrate, 200);
    }

    function bindControls() {
        $(document)
            .off('change.zappoppopCloseGlyph input.zappoppopCloseGlyph')
            .on('change.zappoppopCloseGlyph input.zappoppopCloseGlyph', '#prop-close-glyph', function() {
                var el = getActiveEl();
                if (!isClose(el)) return;
                var val = $(this).val();
                if (GLYPHS.indexOf(val) === -1) return;
                el.content = val;
                el.closeGlyph = val;
                el.glyph_style = el.glyph_style || 'standard';
                render();
                if (this.getAttribute('data-committed') !== '1' && $(this).is('select')) {
                    pushHistory('Change Close Icon Glyph');
                }
            });

        $(document)
            .off('input.zappoppopCloseHover change.zappoppopCloseHover')
            .on('input.zappoppopCloseHover change.zappoppopCloseHover', '#prop-elem-hover-bg, #prop-elem-hover-color, #prop-elem-hover-border', function(e) {
                var el = getActiveEl();
                if (!isClose(el)) return;
                el.hoverBgColor = $('#prop-elem-hover-bg').val();
                el.hoverBg = el.hoverBgColor;
                el.hoverTextColor = $('#prop-elem-hover-color').val();
                el.hoverColor = el.hoverTextColor;
                el.hoverBorderColor = $('#prop-elem-hover-border').val();
                applyHoverToStage(el);
                if (e.type === 'change') pushHistory('Change Close Hover State');
            });
    }

    function hookInspector() {
        var insp = window.ZapPopPopBuilderInspector;
        if (!insp || insp.__zappoppopCloseHooked) return;
        insp.__zappoppopCloseHooked = true;

        if (typeof insp.open === 'function') {
            var origOpen = insp.open;
            insp.open = function() {
                var res = origOpen.apply(this, arguments);
                scheduleHydrate();
                return res;
            };
        }
        if (typeof insp.switchTab === 'function') {
            var origSwitch = insp.switchTab;
            insp.switchTab = function() {
                var res = origSwitch.apply(this, arguments);
                scheduleHydrate();
                return res;
            };
        }
    }

    // Capture phase: runs even when other handlers call stopPropagation()
    window.addEventListener('click', function(e) {
        var t = e.target;
        if (!t || !t.closest) return;
        if (t.closest('.zappoppop-canvas-item, .zappoppop-layer-item, .zappoppop-insp-tab, #zappoppop-inspector-drawer')) {
            scheduleHydrate();
        }
    }, true);

    $(function() {
        bindControls();
        hookInspector();
        scheduleHydrate();
        setTimeout(hookInspector, 150);
        setTimeout(hookInspector, 450);
    });
})(window, jQuery);
/* === ZAPPOPPOP_CLOSE_GLYPH_HOVER_END === */
/* === ZAPPOPPOP_INSPECTOR_ICON_ONLY_ACTIVE_ON_CLICK_END === */


/* === ZAPPOPPOP_CLOSE_PRESET_HOVER_FIX_START === */
(function(window, $) {
    'use strict';
    function cid(r) { return String(r || '').replace(/^zappoppop-el-/, '').replace(/^el-/, '').trim(); }
    function activeEl() {
        var core = window.ZapPopPopBuilderCore;
        if (!core || !core.state) return null;
        var id = cid(core.state.activeId); if (!id) return null;
        var l = (core.state.canvases && core.state.canvases[core.state.currentCanvas || 1]) || [];
        for (var i = 0; i < l.length; i++) { if (l[i] && cid(l[i].id) === id) return l[i]; }
        return null;
    }
    function isClose(el) { return !!el && ['close', 'close_icon'].indexOf(String(el.type || '').toLowerCase()) !== -1; }
    function stage(el) {
        var id = cid(el.id);
        return $('#canvas-elem-' + id + ', #zappoppop-el-' + id + ', #el-' + id + ', .zappoppop-canvas-item[data-id="' + el.id + '"]').first();
    }
    function applyHover(el) {
        var $n = stage(el); if (!$n.length) return;
        var bg = el.hoverBgColor || el.hoverBg, fg = el.hoverTextColor || el.hoverColor, bd = el.hoverBorderColor || el.hoverBorder;
        if (!bg && !fg && !bd) return;
        $n.attr('data-has-hover', 'true');
        if (bg) $n[0].style.setProperty('--zappoppop-hover-bg', bg);
        if (fg) $n[0].style.setProperty('--zappoppop-hover-color', fg);
        if (bd) $n[0].style.setProperty('--zappoppop-hover-border', bd);
    }
    /* Phase: 35x35 preset (only for untouched / legacy 36x36 close layers) */
    function applyPreset(el) {
        if (el.sizePresetApplied) return false;
        var w = parseInt(el.width, 10) || 0, h = parseInt(el.height, 10) || 0;
        if (w !== 35 || h !== 35) { el.width = 35; el.height = 35; }
        el.sizePresetApplied = true;
        return true;
    }
    function syncDims(el) {
        $('#prop-width, #prop-size-width').val(parseInt(el.width, 10) || 35);
        $('#prop-height, #prop-size-height').val(parseInt(el.height, 10) || 35);
    }
    function showHoverUI() {
        $('#prop-btn-hover-section').css('display', 'block');
    }
    function run() {
        var el = activeEl(); if (!isClose(el)) return;
        if (applyPreset(el)) {
            if (window.ZapPopPopBuilderCanvas && window.ZapPopPopBuilderCanvas.renderCanvas) window.ZapPopPopBuilderCanvas.renderCanvas();
        }
        syncDims(el);
        $('#prop-close-glyph').val(el.closeGlyph || el.content || '\u00d7');
        showHoverUI();
        $('#prop-elem-hover-bg').val(el.hoverBgColor || el.hoverBg || '#0284c7');
        $('#prop-elem-hover-color').val(el.hoverTextColor || el.hoverColor || '#ffffff');
        $('#prop-elem-hover-border').val(el.hoverBorderColor || el.hoverBorder || '#0284c7');
        applyHover(el);
    }
    function schedule() { [0, 60, 200, 350, 700, 1200].forEach(function(ms) { setTimeout(run, ms); }); }

    function wrapRender() {
        var cv = window.ZapPopPopBuilderCanvas;
        if (!cv || cv.__closeHoverWrapped || typeof cv.renderCanvas !== 'function') return;
        cv.__closeHoverWrapped = true;
        var orig = cv.renderCanvas;
        cv.renderCanvas = function() {
            var r = orig.apply(this, arguments);
            try {
                var core = window.ZapPopPopBuilderCore;
                var l = (core && core.state && core.state.canvases && core.state.canvases[core.state.currentCanvas || 1]) || [];
                l.forEach(function(e) { if (isClose(e)) applyHover(e); });
            } catch (e) {}
            return r;
        };
    }
    /* Resize inputs -> model sync stays handled by existing #prop-width/#prop-height binding. */
    $(function() { wrapRender(); setTimeout(wrapRender, 150); setTimeout(wrapRender, 450); schedule(); });
    window.addEventListener('click', function(e) {
        var t = e.target;
        if (t && t.closest && t.closest('.zappoppop-canvas-item, .zappoppop-layer-item, .zappoppop-insp-tab, #zappoppop-inspector-drawer')) schedule();
    }, true);
})(window, jQuery);
/* === ZAPPOPPOP_CLOSE_PRESET_HOVER_FIX_END === */

/* === ZAPPOPPOP_CLOSE_HOVER_LIVE_START === */
(function(window, $) {
    'use strict';
    var MAP = { hoverBgColor: ['hoverBgColor','hoverBg'], hoverTextColor: ['hoverTextColor','hoverColor'], hoverBorderColor: ['hoverBorderColor','hoverBorder'] };
    function cid(r) { return String(r || '').replace(/^zappoppop-el-/, '').replace(/^el-/, '').trim(); }
    function list() {
        var c = window.ZapPopPopBuilderCore;
        return (c && c.state && c.state.canvases && c.state.canvases[c.state.currentCanvas || 1]) || [];
    }
    function byId(id) {
        id = cid(id);
        var l = list();
        for (var i = 0; i < l.length; i++) { if (l[i] && cid(l[i].id) === id) return l[i]; }
        return null;
    }
    function isClose(el) { return !!el && ['close', 'close_icon'].indexOf(String(el.type || '').toLowerCase()) !== -1; }
    function activeEl() { var c = window.ZapPopPopBuilderCore; return (c && c.state) ? byId(c.state.activeId) : null; }

    /* Inspector colour inputs (any duplicate) -> model of the active Close layer */
    $(document).off('input.zppCloseHv change.zppCloseHv')
      .on('input.zppCloseHv change.zppCloseHv',
          '[data-prop="hoverBgColor"], [data-prop="hoverTextColor"], [data-prop="hoverBorderColor"]',
          function(e) {
        var el = activeEl(); if (!isClose(el)) return;
        var prop = $(this).attr('data-prop'), val = $(this).val();
        (MAP[prop] || []).forEach(function(k) { el[k] = val; });
        $('[data-prop="' + prop + '"]').not(this).val(val);
        if (e.type === 'change' && window.ZapPopPopBuilderCore && window.ZapPopPopBuilderCore.pushHistory) {
            window.ZapPopPopBuilderCore.pushHistory('Change Close Hover State');
        }
    });

    /* Live hover on stage nodes (no dependency on data-type / CSS variables) */
    $(document).off('mouseenter.zppCloseHv mouseleave.zppCloseHv', '.zappoppop-canvas-item')
      .on('mouseenter.zppCloseHv', '.zappoppop-canvas-item', function() {
        var $n = $(this);
        var el = byId($n.attr('data-id') || $n.attr('id'));
        if (!isClose(el)) return;
        var bg = el.hoverBgColor || el.hoverBg, fg = el.hoverTextColor || el.hoverColor, bd = el.hoverBorderColor || el.hoverBorder;
        $n.data('zppHvSave', { bg: this.style.backgroundColor, color: this.style.color, bd: this.style.borderColor });
        if (bg) this.style.setProperty('background-color', bg, 'important');
        if (fg) this.style.setProperty('color', fg, 'important');
        if (bd) this.style.setProperty('border-color', bd, 'important');
    }).on('mouseleave.zppCloseHv', '.zappoppop-canvas-item', function() {
        var sv = $(this).data('zppHvSave'); if (!sv) return;
        this.style.removeProperty('background-color'); this.style.removeProperty('color'); this.style.removeProperty('border-color');
        if (sv.bg) this.style.backgroundColor = sv.bg;
        if (sv.color) this.style.color = sv.color;
        if (sv.bd) this.style.borderColor = sv.bd;
        $(this).removeData('zppHvSave');
    });
})(window, jQuery);
/* === ZAPPOPPOP_CLOSE_HOVER_LIVE_END === */

/* === ZAPPOPPOP_CLOSE_GLYPH_COLOR_START === */
(function(window,$){
    'use strict';
    function cid(r){return String(r||'').replace(/^zappoppop-el-/,'').replace(/^el-/,'').trim();}
    function activeEl(){
        var c=window.ZapPopPopBuilderCore; if(!c||!c.state)return null;
        var id=cid(c.state.activeId); if(!id)return null;
        var l=(c.state.canvases&&c.state.canvases[c.state.currentCanvas||1])||[];
        for(var i=0;i<l.length;i++){ if(l[i]&&cid(l[i].id)===id) return l[i]; }
        return null;
    }
    function run(){
        var el=activeEl();
        if(!el||['close','close_icon'].indexOf(String(el.type||'').toLowerCase())===-1) return;
        var v=/^#(?:[0-9a-f]{6}|[0-9a-f]{8})$/i.test(el.color||'')?el.color:'#64748b';
        $('#prop-close-glyph-color').val(v);
    }
    function sched(){[0,60,200,350,700,1200].forEach(function(ms){setTimeout(run,ms);});}
    window.addEventListener('click',function(e){
        var t=e.target;
        if(t&&t.closest&&t.closest('.zappoppop-canvas-item, .zappoppop-layer-item, .zappoppop-insp-tab, #zappoppop-inspector-drawer')) sched();
    },true);
    $(function(){sched();});
})(window,jQuery);
/* === ZAPPOPPOP_CLOSE_GLYPH_COLOR_END === */

/* === ZAPPOPPOP_BUTTON_START === */
(function(window,$){
    'use strict';
    var BTN=['button','link','link_btn'];
    function cid(r){return String(r||'').replace(/^zappoppop-el-/,'').replace(/^el-/,'').trim();}
    function activeEl(){
        var c=window.ZapPopPopBuilderCore; if(!c||!c.state)return null;
        var id=cid(c.state.activeId); if(!id)return null;
        var l=(c.state.canvases&&c.state.canvases[c.state.currentCanvas||1])||[];
        for(var i=0;i<l.length;i++){ if(l[i]&&cid(l[i].id)===id) return l[i]; }
        return null;
    }
    function isBtn(el){return !!el&&BTN.indexOf(String(el.type||'').toLowerCase())!==-1;}
    function T(v){return v===true||v==='true'||v===1||v==='1'||v==='on';}
    function rows(el){
        var a=$('#prop-button-action').val()||(el&&el.buttonAction)||'link';
        $('#prop-button-url-wrap').css('display',(a==='link'||a==='url')?'':'none');
        $('#prop-button-step-wrap').css('display',(a==='step'||a==='goto_canvas')?'':'none');
    }
    function run(){
        var el=activeEl(); if(!isBtn(el)) return;
        var changed=false;
        if(el.content===undefined||el.content===null){ el.content='Click Here'; changed=true; }
        if(!el.buttonAction) el.buttonAction='link';
        $('#prop-button-label, #prop-link-text').val(el.content);
        $('#prop-button-action').val(el.buttonAction);
        $('#prop-link-url').val(el.linkUrl||el.url||'');
        $('#prop-goto-canvas').val(el.goto_canvas||2);
        $('#prop-link-blank').prop('checked',T(el.linkBlank!==undefined?el.linkBlank:el.targetBlank));
        $('#prop-link-nofollow').prop('checked',T(el.nofollow));
        rows(el);
        if(changed&&window.ZapPopPopBuilderCanvas&&window.ZapPopPopBuilderCanvas.renderCanvas) window.ZapPopPopBuilderCanvas.renderCanvas();
    }
    function sched(){[0,60,200,350,700,1200].forEach(function(ms){setTimeout(run,ms);});}
    $(document).off('change.zppBtn input.zppBtn','#prop-button-action,#prop-link-blank,#prop-link-nofollow,#prop-link-url,#prop-logic-click-action')
      .on('change.zppBtn input.zppBtn','#prop-button-action,#prop-link-blank,#prop-link-nofollow,#prop-link-url,#prop-logic-click-action',function(){
        var el=activeEl(); if(!isBtn(el)) return;
        if(this.id==='prop-button-action'||this.id==='prop-logic-click-action'){
            el.buttonAction=$(this).val(); $('#prop-button-action,#prop-logic-click-action').val(el.buttonAction); rows(el);
        } else if(this.id==='prop-link-blank'){
            el.linkBlank=$(this).is(':checked'); el.targetBlank=el.linkBlank;
        } else if(this.id==='prop-link-nofollow'){
            el.nofollow=$(this).is(':checked');
        } else if(this.id==='prop-link-url'){
            el.linkUrl=$.trim($(this).val()); el.url=el.linkUrl;
        }
        if(window.ZapPopPopBuilderCore&&window.ZapPopPopBuilderCore.pushHistory&&this.type!=='text') window.ZapPopPopBuilderCore.pushHistory('Change Button');
    });
    window.addEventListener('click',function(e){
        var t=e.target;
        if(t&&t.closest&&t.closest('.zappoppop-canvas-item, .zappoppop-layer-item, .zappoppop-insp-tab, #zappoppop-inspector-drawer')) sched();
    },true);
    $(function(){sched();});
})(window,jQuery);
/* === ZAPPOPPOP_BUTTON_END === */


/* === ZAPPOPPOP_BUTTON_V2_START === */
(function(window, $) {
    'use strict';
    var BTN = ['button', 'link', 'link_btn', 'step_btn', 'step'];
    var MAP = { hoverBgColor: ['hoverBgColor', 'hoverBg'], hoverTextColor: ['hoverTextColor', 'hoverColor'], hoverBorderColor: ['hoverBorderColor', 'hoverBorder'] };

    function cid(r) { return String(r || '').replace(/^zappoppop-el-/, '').replace(/^el-/, '').trim(); }
    function list() { var c = window.ZapPopPopBuilderCore; return (c && c.state && c.state.canvases && c.state.canvases[c.state.currentCanvas || 1]) || []; }
    function byId(id) { id = cid(id); var l = list(); for (var i = 0; i < l.length; i++) { if (l[i] && cid(l[i].id) === id) return l[i]; } return null; }
    function activeEl() { var c = window.ZapPopPopBuilderCore; return (c && c.state) ? byId(c.state.activeId) : null; }
    function isBtn(el) { return !!el && BTN.indexOf(String(el.type || '').toLowerCase()) !== -1; }
    function T(v) { return v === true || v === 'true' || v === 1 || v === '1' || v === 'on'; }

    /* Style tab: hide Opacity & Visibility + Custom CSS; show Button Hover State (attribute-keyed, survives .show()) */
    if (!document.getElementById('zpp-btn-style-v2')) {
        var st = document.createElement('style');
        st.id = 'zpp-btn-style-v2';
        st.textContent =
            'html[data-zpp-btn="1"] #insp-section-opacity,' +
            'html[data-zpp-btn="1"] #insp-section-custom-css{display:none !important;}' +
            'html[data-zpp-btn="1"] #prop-btn-hover-section{display:block !important;}' +
            'html[data-zpp-btn="1"] .zappoppop-media-style-scope[data-style-elements^="button"]{display:none !important;}';
        (document.head || document.documentElement).appendChild(st);
    }

    function run() {
        var el = activeEl();
        var root = document.documentElement;
        if (!isBtn(el)) { root.removeAttribute('data-zpp-btn'); return; }
        root.setAttribute('data-zpp-btn', '1');

        /* 1) Label: preset value == placeholder ("Click Here") */
        var lbl = (el.content === undefined || el.content === null || String(el.content).replace(/\s+/g, '') === '') ? 'Click Here' : el.content;
        var needRender = (el.content !== lbl);
        el.content = lbl;
        $('#prop-button-label, #prop-link-text').val(lbl);

        /* 2) Click action fields + slide switches */
        if (!el.buttonAction) el.buttonAction = 'link';
        $('#prop-button-action').val(el.buttonAction);
        $('#prop-link-url').val(el.linkUrl || el.url || '');
        $('#prop-goto-canvas').val(el.goto_canvas || 2);
        $('#prop-link-blank').prop('checked', T(el.linkBlank !== undefined ? el.linkBlank : el.targetBlank));
        $('#prop-link-nofollow').prop('checked', T(el.nofollow));
        var a = el.buttonAction;
        $('#prop-button-url-wrap').css('display', (a === 'link' || a === 'url') ? '' : 'none');
        $('#prop-button-step-wrap').css('display', (a === 'step' || a === 'goto_canvas') ? '' : 'none');

        /* 4) Hover state values */
        $('[data-prop="hoverBgColor"]').val(el.hoverBgColor || el.hoverBg || '#0284c7');
        $('[data-prop="hoverTextColor"]').val(el.hoverTextColor || el.hoverColor || '#ffffff');
        $('[data-prop="hoverBorderColor"]').val(el.hoverBorderColor || el.hoverBorder || '#0284c7');

        if (needRender && window.ZapPopPopBuilderCanvas && typeof window.ZapPopPopBuilderCanvas.renderCanvas === 'function') {
            window.ZapPopPopBuilderCanvas.renderCanvas();
        }
    }
    function sched() { [0, 60, 200, 350, 700, 1200].forEach(function(ms) { setTimeout(run, ms); }); }

    /* Click action / URL / switches -> model */
    $(document).off('change.zppBtn2 input.zppBtn2', '#prop-button-action, #prop-link-url, #prop-link-blank, #prop-link-nofollow, #prop-goto-canvas')
      .on('change.zppBtn2 input.zppBtn2', '#prop-button-action, #prop-link-url, #prop-link-blank, #prop-link-nofollow, #prop-goto-canvas', function(e) {
        var el = activeEl(); if (!isBtn(el)) return;
        var id = this.id;
        if (id === 'prop-button-action') { el.buttonAction = $(this).val(); $('#prop-logic-click-action').val(el.buttonAction); }
        else if (id === 'prop-link-url') { el.linkUrl = $.trim($(this).val()); el.url = el.linkUrl; }
        else if (id === 'prop-link-blank') { el.linkBlank = $(this).is(':checked'); el.targetBlank = el.linkBlank; }
        else if (id === 'prop-link-nofollow') { el.nofollow = $(this).is(':checked'); }
        else if (id === 'prop-goto-canvas') { var n = parseInt($(this).val(), 10) || 2; el.goto_canvas = n; el.goto_screen = n; }
        if (id === 'prop-button-action') {
            var a = el.buttonAction;
            $('#prop-button-url-wrap').css('display', (a === 'link' || a === 'url') ? '' : 'none');
            $('#prop-button-step-wrap').css('display', (a === 'step' || a === 'goto_canvas') ? '' : 'none');
        }
        if (e.type === 'change' && window.ZapPopPopBuilderCore && window.ZapPopPopBuilderCore.pushHistory) window.ZapPopPopBuilderCore.pushHistory('Change Button');
    });

    /* Hover colours -> model (works with the duplicate inputs, mirrors them) */
    $(document).off('input.zppBtnHv change.zppBtnHv', '[data-prop="hoverBgColor"], [data-prop="hoverTextColor"], [data-prop="hoverBorderColor"]')
      .on('input.zppBtnHv change.zppBtnHv', '[data-prop="hoverBgColor"], [data-prop="hoverTextColor"], [data-prop="hoverBorderColor"]', function(e) {
        var el = activeEl(); if (!isBtn(el)) return;
        var prop = $(this).attr('data-prop'), val = $(this).val();
        (MAP[prop] || []).forEach(function(k) { el[k] = val; });
        $('[data-prop="' + prop + '"]').not(this).val(val);
        if (e.type === 'change' && window.ZapPopPopBuilderCore && window.ZapPopPopBuilderCore.pushHistory) window.ZapPopPopBuilderCore.pushHistory('Change Button Hover');
    });

    /* Live hover on the stage */
    $(document).off('mouseenter.zppBtnHv mouseleave.zppBtnHv', '.zappoppop-canvas-item')
      .on('mouseenter.zppBtnHv', '.zappoppop-canvas-item', function() {
        var $n = $(this), el = byId($n.attr('data-id') || $n.attr('id'));
        if (!isBtn(el)) return;
        var bg = el.hoverBgColor || el.hoverBg, fg = el.hoverTextColor || el.hoverColor, bd = el.hoverBorderColor || el.hoverBorder;
        $n.data('zppBtnSave', { bg: this.style.backgroundColor, color: this.style.color, bd: this.style.borderColor });
        if (bg) this.style.setProperty('background-color', bg, 'important');
        if (fg) this.style.setProperty('color', fg, 'important');
        if (bd) this.style.setProperty('border-color', bd, 'important');
    }).on('mouseleave.zppBtnHv', '.zappoppop-canvas-item', function() {
        var sv = $(this).data('zppBtnSave'); if (!sv) return;
        this.style.removeProperty('background-color'); this.style.removeProperty('color'); this.style.removeProperty('border-color');
        if (sv.bg) this.style.backgroundColor = sv.bg;
        if (sv.color) this.style.color = sv.color;
        if (sv.bd) this.style.borderColor = sv.bd;
        $(this).removeData('zppBtnSave');
    });

    window.addEventListener('click', function(e) {
        var t = e.target;
        if (t && t.closest && t.closest('.zappoppop-canvas-item, .zappoppop-layer-item, .zappoppop-insp-tab, #zappoppop-inspector-drawer')) sched();
    }, true);
    $(function() { sched(); });
})(window, jQuery);
/* === ZAPPOPPOP_BUTTON_V2_END === */

/* === ZAPPOPPOP_SUBMIT_TEXT_ALIGN_START === */
(function(window, $) {
    'use strict';
    var SECTION_ID = 'insp-section-submit-text-align';
    var ALIGNS = ['left', 'center', 'right'];

    function cid(r) { return String(r || '').replace(/^zappoppop-el-/, '').replace(/^el-/, '').trim(); }
    function activeEl() {
        var c = window.ZapPopPopBuilderCore;
        if (!c || !c.state || !c.state.activeId) return null;
        var id = cid(c.state.activeId);
        var l = (c.state.canvases && c.state.canvases[c.state.currentCanvas || 1]) || [];
        for (var i = 0; i < l.length; i++) { if (l[i] && cid(l[i].id) === id) return l[i]; }
        return null;
    }
    function isSubmit(el) { return !!el && ['submit', 'submit_btn'].indexOf(String(el.type || '').toLowerCase()) !== -1; }

    function ensureSection() {
        var $e = $('#' + SECTION_ID);
        if ($e.length) return $e;
        var pane = document.getElementById('insp-tab-style');
        if (!pane) return $();
        var btns = ALIGNS.map(function(a) {
            return '<button type="button" class="button zappoppop-submit-align-btn" data-align="' + a + '" aria-pressed="false" style="font-size:11px;font-weight:700;">' + a.charAt(0).toUpperCase() + a.slice(1) + '</button>';
        }).join('');
        var $s = $('<div class="zappoppop-insp-section" id="' + SECTION_ID + '" style="display:none;background:#0f172a;border:1px solid #1e293b;border-radius:0px;padding:12px;margin-bottom:12px;">' +
            '<div style="font-size:10px;font-weight:800;color:#38bdf8;text-transform:uppercase;margin-bottom:8px;">TEXT ALIGNMENT</div>' +
            '<div id="zappoppop-submit-align-group" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px;">' + btns + '</div></div>');
        var anchor = document.getElementById('insp-section-opacity');
        if (anchor) { $(anchor).before($s); } else { $(pane).prepend($s); }
        return $s;
    }

    function paint(align) {
        $('#zappoppop-submit-align-group .zappoppop-submit-align-btn').each(function() {
            var on = $(this).attr('data-align') === align;
            $(this).toggleClass('active button-primary', on).attr('aria-pressed', on ? 'true' : 'false');
        });
    }

    function hydrate() {
        var el = activeEl();
        var $s = ensureSection();
        if (!$s.length) return;
        if (!isSubmit(el)) { $s.hide(); return; }
        paint(ALIGNS.indexOf(el.textAlign) !== -1 ? el.textAlign : 'center');
        $s.show();
    }
    function sched() { [0, 60, 200, 450].forEach(function(ms) { setTimeout(hydrate, ms); }); }

    $(document).off('click.zppSubmitAlign', '#zappoppop-submit-align-group .zappoppop-submit-align-btn')
        .on('click.zppSubmitAlign', '#zappoppop-submit-align-group .zappoppop-submit-align-btn', function(e) {
            e.preventDefault(); e.stopPropagation();
            var el = activeEl(); if (!isSubmit(el)) return;
            var a = $(this).attr('data-align');
            if (ALIGNS.indexOf(a) === -1) return;
            el.textAlign = a; el.text_align = a;
            paint(a);
            var Reg = window.ZapPopPopElementsRegistry;
            if (window.ZapPopPopBuilderCanvas && typeof window.ZapPopPopBuilderCanvas.renderCanvas === 'function') { window.ZapPopPopBuilderCanvas.renderCanvas(); }
            if (Reg && typeof Reg.renderMediaElementStage === 'function') { try { Reg.renderMediaElementStage(el); } catch (err) {} }
            if (window.ZapPopPopBuilderCore && typeof window.ZapPopPopBuilderCore.pushHistory === 'function') { window.ZapPopPopBuilderCore.pushHistory('Change Submit Text Alignment'); }
        });

    function hook() {
        var insp = window.ZapPopPopBuilderInspector;
        if (!insp || insp.__zppSubmitAlignHooked) return;
        insp.__zppSubmitAlignHooked = true;
        ['open', 'switchTab'].forEach(function(fn) {
            if (typeof insp[fn] !== 'function') return;
            var orig = insp[fn];
            insp[fn] = function() { var r = orig.apply(this, arguments); sched(); return r; };
        });
    }

    window.addEventListener('click', function(e) {
        var t = e.target;
        if (t && t.closest && t.closest('.zappoppop-canvas-item, .zappoppop-layer-item, .zappoppop-insp-tab, #zappoppop-inspector-drawer')) sched();
    }, true);
    $(function() { ensureSection(); hook(); sched(); setTimeout(hook, 150); setTimeout(hook, 450); });
})(window, jQuery);
/* === ZAPPOPPOP_SUBMIT_TEXT_ALIGN_END === */


/* === ZAPPOPPOP_INSPECTOR_CLOSE_ONLY_X_START === */
(function(window) {
    'use strict';
    if (window.__zappoppopCloseOnlyXBound) return;
    window.__zappoppopCloseOnlyXBound = true;
    window.addEventListener('click', function(e) {
        var t = e.target;
        if (!t || !t.closest) return;
        if (t.closest('#zappoppop-inspector-drawer #zappoppop-inspector-close, #zappoppop-inspector-drawer .zappoppop-inspector-close')) {
            window.__zappoppopInspCloseAllowed = true;
            setTimeout(function() { window.__zappoppopInspCloseAllowed = false; }, 0);
        }
    }, true);
})(window);
/* === ZAPPOPPOP_INSPECTOR_CLOSE_ONLY_X_END === */

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
