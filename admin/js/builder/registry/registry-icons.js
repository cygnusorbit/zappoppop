/**
 * ZapPopPop Builder Registry Module: registry-icons
 * Split from builder-elements-registry.js (load order matters; see class-admin-assets.php)
 */
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
/* === ZAPPOPPOP_INSPECTOR_ICON_ONLY_ACTIVE_ON_CLICK_END === */
