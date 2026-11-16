/**
 * ZapPopPop Builder Registry Module: registry-compat
 * Split from builder-elements-registry.js (load order matters; see class-admin-assets.php)
 */
;(function(window) {
    if (window.ZapPopPopElementsRegistry) {
        if (typeof window.ZapPopPopElementsRegistry.renderStage !== 'function') {
            window.ZapPopPopElementsRegistry.renderStage = function(el) {
                if (!el || !el.type) return null;
                if (window.ZapPopPopElements && typeof window.ZapPopPopElements.renderStage === 'function') {
                    var rendered = window.ZapPopPopElements.renderStage(el);
                    if (rendered) return rendered;
                }
                var reg = this.registry || this.elements || {};
                if (reg[el.type] && typeof reg[el.type].renderStage === 'function') {
                    return reg[el.type].renderStage(el);
                }
                return null;
            };
        }
        if (typeof window.ZapPopPopElementsRegistry.updateStage !== 'function') {
            window.ZapPopPopElementsRegistry.updateStage = function(el, $node) {
                if (!el || !el.type) return false;
                var reg = this.registry || this.elements || {};
                if (reg[el.type] && typeof reg[el.type].updateStage === 'function') {
                    return reg[el.type].updateStage(el, $node);
                }
                return false;
            };
        }
    }
})(window);

;(function(window, $) {
    'use strict';
    if (!window.ZapPopPopElementsRegistry) return;
    var _origScope = window.ZapPopPopElementsRegistry.syncGranularElementStyleScope;
    window.ZapPopPopElementsRegistry.syncGranularElementStyleScope = function(elOrType) {
        if (typeof _origScope === 'function') {
            _origScope.apply(this, arguments);
        }
        var rawType = (elOrType && typeof elOrType === 'object') ? elOrType.type : elOrType;
        var type = window.ZapPopPopElementsRegistry.normalizeType
            ? window.ZapPopPopElementsRegistry.normalizeType(rawType)
            : String(rawType || '').toLowerCase();
        if (!type) return;

        var clickUrlTypes = ['image', 'icon', 'button', 'shape', 'label'];
        $('[data-logic-scope="click-url"]').toggle(clickUrlTypes.indexOf(type) !== -1);
        $('[data-logic-scope="close-cookie"]').toggle(type === 'close');
        $('[data-logic-scope="submit-routing"]').toggle(type === 'submit');
    };
})(window, jQuery);
