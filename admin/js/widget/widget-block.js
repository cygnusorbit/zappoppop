(function (wp) {
    'use strict';
    if (!wp || !wp.blocks || !wp.element) { return; }

    var el = wp.element.createElement;
    var be = wp.blockEditor || wp.editor;
    var InspectorControls = be.InspectorControls;
    var comps = wp.components;
    var data = window.zappoppopBlockData || { popups: [] };
    var __ = wp.i18n.__;
    var sprintf = wp.i18n.sprintf;

    function popupOptions() {
        var opts = [{ label: __('\u2014 Choose a popup \u2014', 'zappoppop'), value: '' }];
        (data.popups || []).forEach(function (p) {
            opts.push({ label: p.title + ' (' + p.uid + ')', value: p.uid });
        });
        return opts;
    }

    var MODES = [
        { label: __('Inline Popup', 'zappoppop'), value: 'inline' },
        { label: __('Onload Popup', 'zappoppop'), value: 'onload' }
    ];

    wp.blocks.registerBlockType('zappoppop/popup', {
        title: __('ZapPopPop Popup', 'zappoppop'),
        description: __('Embed a popup inline or open it on page load.', 'zappoppop'),
        icon: 'megaphone',
        category: 'widgets',
        keywords: ['popup', 'zappoppop', 'modal', 'lead'],
        attributes: {
            uid: { type: 'string', default: '' },
            mode: { type: 'string', default: 'inline' },
            delay: { type: 'integer', default: 0 }
        },

        edit: function (props) { /* ZAPPOPPOP_BLOCK_EDIT_V2 */
            var a = props.attributes;
            var set = props.setAttributes;
            var hasList = (data.popups || []).length > 0;

            function buildControls(prefix) {
                var c = [];
                if (hasList) {
                    c.push(el(comps.SelectControl, {
                        key: prefix + 'uid', label: __('Popup', 'zappoppop'), value: a.uid, options: popupOptions(),
                        onChange: function (v) { set({ uid: v }); }
                    }));
                } else {
                    c.push(el(comps.TextControl, {
                        key: prefix + 'uidtxt', label: __('Popup UID (no published popups found)', 'zappoppop'), value: a.uid || '',
                        onChange: function (v) { set({ uid: String(v || '').toLowerCase().replace(/[^a-z0-9_\-]/g, '') }); }
                    }));
                }
                c.push(el(comps.SelectControl, {
                    key: prefix + 'mode', label: __('Embed Type', 'zappoppop'), value: a.mode, options: MODES,
                    onChange: function (v) { set({ mode: v }); }
                }));
                if (a.mode === 'onload') {
                    c.push(el(comps.TextControl, {
                        key: prefix + 'delay', label: __('Onload Delay (seconds)', 'zappoppop'), type: 'number', min: 0,
                        value: String(a.delay || 0),
                        onChange: function (v) { set({ delay: Math.max(0, parseInt(v, 10) || 0) }); }
                    }));
                }
                return c;
            }

            var modeLabel = a.mode === 'onload' ? __('Onload Popup', 'zappoppop') : __('Inline Popup', 'zappoppop');
            var summary = a.uid
                ? (modeLabel + ' \u2192 ' + a.uid +
                   (a.mode === 'onload' ? ' ' + sprintf(
                       /* translators: %d: onload delay in seconds. */
                       __('(delay %ds)', 'zappoppop'), a.delay || 0) : ''))
                : __('Choose a popup below.', 'zappoppop');

            return el('div', { style: { border: '1px dashed #94a3b8', borderRadius: '0px', padding: '14px', background: '#f8fafc' } },
                el(InspectorControls, null,
                    el(comps.PanelBody, { title: __('ZapPopPop Popup', 'zappoppop'), initialOpen: true }, buildControls('s_'))
                ),
                el('strong', null, __('ZapPopPop Popup', 'zappoppop')),
                el('p', { style: { margin: '6px 0 10px', color: '#475569' } }, summary),
                el('div', null, buildControls('b_'))
            );
        },

        save: function () { return null; } /* dynamic block: PHP renders on the front end */
    });
})(window.wp);
