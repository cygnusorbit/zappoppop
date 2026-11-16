/**
 * ZapPopPop Element: Paragraph Body Text
 * Cross-browser Stage Renderer & Inspector Settings
 */
(function($) {
    'use strict';

    if (!window.ZapPopPopElements) return;

    var paragraphDefinition = {
        label: 'Paragraph Text',
        icon: 'dashicons-editor-paragraph',

        getDefaults: function() {
            return {
                width: 300,
                height: 80,
                content: 'Add your descriptive body copy here. Explain the offer, details, or terms clearly.',
                tag: 'p',
                fontSize: 14,
                fontWeight: '400',
                color: '#475569',
                textAlign: 'left',
                lineHeight: '1.5',
                letterSpacing: '0px'
            };
        },

        renderStage: function(el) {
            var text = el.content !== undefined ? el.content : (el.text || '');
            var tag = el.tag || 'p';
            var align = el.textAlign || 'left';
            var lh = el.lineHeight || '1.5';
            var ls = el.letterSpacing || '0px';

            return '<' + tag + ' style="margin:0;padding:0;width:100%;height:100%;font-size:inherit;font-weight:inherit;' +
                'color:inherit;text-align:' + align + ';line-height:' + lh + ';letter-spacing:' + ls + ';' +
                'word-break:break-word;user-select:none;pointer-events:none;box-sizing:border-box;">' +
                text +
                '</' + tag + '>';
        },

        renderSettings: function(el, $container) {
            var text = el.content !== undefined ? el.content : (el.text || '');
            var tag = el.tag || 'p';

            var html = '<div class="element-panel">' +
                '<h4 style="margin:0 0 10px 0;font-size:12px;color:#1e293b;text-transform:uppercase;font-weight:700;">Paragraph Settings</h4>' +
                '<div style="margin-bottom:8px;">' +
                    '<label>Paragraph Body Text</label>' +
                    '<textarea id="insp-para-text" rows="4" class="zappoppop-insp-input" style="resize:vertical;">' + text + '</textarea>' +
                '</div>' +
                '<div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:8px;">' +
                    '<div>' +
                        '<label>Wrapper Tag</label>' +
                        '<select id="insp-para-tag" class="zappoppop-insp-input">' +
                            '<option value="p"' + (tag === 'p' ? ' selected' : '') + '>P (Paragraph)</option>' +
                            '<option value="div"' + (tag === 'div' ? ' selected' : '') + '>DIV Container</option>' +
                            '<option value="span"' + (tag === 'span' ? ' selected' : '') + '>SPAN Inline</option>' +
                        '</select>' +
                    '</div>' +
                    '<div>' +
                        '<label>Line Height</label>' +
                        '<input type="text" id="insp-para-lh" value="' + (el.lineHeight || '1.5') + '" placeholder="e.g. 1.5" class="zappoppop-insp-input">' +
                    '</div>' +
                '</div>' +
            '</div>';

            $container.append(html);

            $('#insp-para-text').on('input change', function() {
                el.content = $(this).val();
                el.text = el.content;
                window.ZapPopPopBuilderCanvas.renderCanvas();
            }).on('change', function() {
                window.ZapPopPopBuilderCore.pushHistory();
            });

            $('#insp-para-tag').on('change', function() {
                el.tag = $(this).val();
                window.ZapPopPopBuilderCanvas.renderCanvas();
                window.ZapPopPopBuilderCore.pushHistory();
            });

            $('#insp-para-lh').on('input change', function() {
                el.lineHeight = $(this).val();
                window.ZapPopPopBuilderCanvas.renderCanvas();
            }).on('change', function() {
                window.ZapPopPopBuilderCore.pushHistory();
            });
        }
    };

    window.ZapPopPopElements.register('paragraph', paragraphDefinition);
    window.ZapPopPopElements.register('text', paragraphDefinition);
})(jQuery);
