/**
 * ZapPopPop Visual Builder: Floating Magenta Layers Panel Controller
 * Full 26-Element Stacking, Deep Duplication, Compact 30% Action Buttons & Sorting
 */
(function($) {
    'use strict';

    window.ZapPopPopBuilderLayers = {
        bindLayerSearch: function() {
        var self = this;
        $(document).off('input.zappoppop_layers_srch').on('input.zappoppop_layers_srch', '#zappoppop-layers-search', function() {
            var query = ($(this).val() || '').toLowerCase().trim();
            $('#zappoppop-layers-list li, .zappoppop-layer-item').each(function() {
                var $item = $(this);
                var title = ($item.find('.zappoppop-layer-title').text() || '').toLowerCase();
                if (!query || title.indexOf(query) !== -1) {
                    $item.show();
                } else {
                    $item.hide();
                }
            });
        });
    },

    bindInlineRename: function() {
        var self = this;
        $(document).off('dblclick.zappoppop_rename').on('dblclick.zappoppop_rename', '.zappoppop-layer-title-editable, .zappoppop-layer-title', function(e) {
            e.stopPropagation();
            var $span = $(this);
            var $item = $span.closest('.zappoppop-layer-item, li');
            var elId = $item.data('id') || $item.attr('data-element-id') || $item.attr('data-id');
            if (!elId) return;

            var curText = $span.text().trim();
            var $input = $('<input type="text" class="zappoppop-layer-rename-input" />')
                .val(curText)
                .css({
                    'width': '110px',
                    'height': '22px',
                    'font-size': '11px',
                    'padding': '1px 5px',
                    'background': '#0f172a',
                    'color': '#f8fafc',
                    'border': '1px solid #38bdf8',
                    'border-radius': '3px',
                    'outline': 'none'
                });

            $span.hide().after($input);
            $input.focus().select();

            var committed = false;
            function commitRename() {
                if (committed) return;
                committed = true;
                var newName = $input.val().trim() || curText;
                $input.remove();
                $span.text(newName).show();

                var core = window.ZapPopPopBuilderCore;
                if (core && core.state) {
                    var cur = core.state.currentCanvas || 1;
                    var list = (core.state.canvases && core.state.canvases[cur]) ? core.state.canvases[cur] : [];
                    var cleanRenameId = String(elId).replace(/^(zappoppop-el-|el-)/, '');
                    for (var i = 0; i < list.length; i++) {
                        if (String(list[i].id).replace(/^(zappoppop-el-|el-)/, '') === cleanRenameId || list[i].id === elId) {
                            list[i].name = newName;
                            list[i].label = newName;
                            break;
                        }
                    }
                    if (typeof core.pushHistory === 'function') {
                        core.pushHistory();
                    }
                }
                if ($('#prop-element-name').length) {
                    $('#prop-layer-name, #prop-element-name').val(newName);
                }
            }

            $input.on('blur', commitRename);
            $input.on('keydown', function(evt) {
                if (evt.key === 'Enter') {
                    evt.preventDefault();
                    commitRename();
                } else if (evt.key === 'Escape') {
                    evt.preventDefault();
                    committed = true;
                    $input.remove();
                    $span.show();
                }
            });
        });
    },

    bindBulkActions: function() {
        var self = this;
        // Bulk visibility toggle
        $(document).off('click.zappoppop_b_vis').on('click.zappoppop_b_vis', '#zappoppop-layers-bulk-vis', function(e) {
            e.preventDefault();
            var core = window.ZapPopPopBuilderCore;
            if (!core || !core.state) return;
            var cur = core.state.currentCanvas || 1;
            var list = (core.state.canvases && core.state.canvases[cur]) ? core.state.canvases[cur] : [];
            if (!list.length) return;

            var anyHidden = list.some(function(el) { return el.hidden === true; });
            var newHiddenState = !anyHidden;

            list.forEach(function(el) {
                el.hidden = newHiddenState;
                var $node = $('#zappoppop-el-' + el.id);
                if (newHiddenState) {
                    $node.hide();
                } else {
                    $node.show();
                }
            });

            $(this).toggleClass('active is-active', newHiddenState);
            $(this).find('.dashicons').toggleClass('dashicons-visibility', !newHiddenState).toggleClass('dashicons-hidden', newHiddenState);

            if (typeof self.renderLayers === 'function') {
                self.renderLayers();
            }
            if (typeof core.pushHistory === 'function') {
                core.pushHistory();
            }
        });

        // Bulk lock toggle
        $(document).off('click.zappoppop_b_lock').on('click.zappoppop_b_lock', '#zappoppop-layers-bulk-lock', function(e) {
            e.preventDefault();
            var core = window.ZapPopPopBuilderCore;
            if (!core || !core.state) return;
            var cur = core.state.currentCanvas || 1;
            var list = (core.state.canvases && core.state.canvases[cur]) ? core.state.canvases[cur] : [];
            if (!list.length) return;

            var anyLocked = list.some(function(el) { return el.locked === true; });
            var newLockState = !anyLocked;

            list.forEach(function(el) {
                el.locked = newLockState;
                var $node = $('#zappoppop-el-' + el.id);
                if (newLockState) {
                    $node.addClass('zappoppop-locked');
                    if ($node.hasClass('ui-draggable')) $node.draggable('disable');
                    if ($node.hasClass('ui-resizable')) $node.resizable('disable');
                } else {
                    $node.removeClass('zappoppop-locked');
                    if ($node.hasClass('ui-draggable')) $node.draggable('enable');
                    if ($node.hasClass('ui-resizable')) $node.resizable('enable');
                }
            });

            $(this).toggleClass('active is-active', newLockState);
            $(this).find('.dashicons').toggleClass('dashicons-unlock', !newLockState).toggleClass('dashicons-lock', newLockState);

            if (typeof self.renderLayers === 'function') {
                self.renderLayers();
            }
            if (typeof core.pushHistory === 'function') {
                core.pushHistory();
            }
        });
    },

    init: function() {
            this.bindPanelInteractions();
            this.bindEvents();
        this.renderLayers();
        this.bindInlineRename();
        },

        bindPanelInteractions: function() {
            var $panel = $('#zappoppop-floating-layers-panel');
            if (!$panel.length) return;

            // Make panel draggable within workspace
            $panel.draggable({
                handle: '#zappoppop-layers-header, .zappoppop-layers-panel-header',
                containment: '.zappoppop-builder-workspace',
                scroll: false,
                cursor: 'grab'
            });

            // Toggle collapse body
            $panel.off('click.toggleCollapse', '#zappoppop-layers-toggle-collapse, .zappoppop-layers-collapse-btn').on('click.toggleCollapse', '#zappoppop-layers-toggle-collapse, .zappoppop-layers-collapse-btn', function(e) {
                e.stopPropagation();
                $panel.toggleClass('collapsed');
            });
        },

        renderLayers: function() {
            var self = this;
            var $list = $('#zappoppop-layers-list');
            if (!$list.length) return;

            $list.empty();

            if (!window.ZapPopPopBuilderCanvas) return;
            var elements = window.ZapPopPopBuilderCanvas.getActiveElements();

            $('#zappoppop-layers-count').text(elements ? elements.length : 0);

            if (!elements || elements.length === 0) {
                $list.html('<div style="padding:14px 10px;text-align:center;color:#94a3b8;font-size:11px;font-style:italic;">No layers on this canvas. Click an element in the Ribbon Bar to add one.</div>');
                return;
            }

            var activeId = window.ZapPopPopBuilderCore ? window.ZapPopPopBuilderCore.state.activeId : null;

            // Sort elements by zIndex descending so top-most layer appears at top of list
            var sortedElements = elements.slice().sort(function(a, b) {
                return (b.zIndex || 10) - (a.zIndex || 10);
            });

            sortedElements.forEach(function(el) {
                var isSelected = activeId !== null && String(activeId) === String(el.id);
                var isLocked = !!el.locked;
                var isHidden = !!el.hidden;

                var iconClass = self.getLayerIcon(el.type || 'text');
                var defaultLabel = (el.type === 'text' || el.type === 'paragraph') ? 'Paragraph' : (function(s){s=String(s||'');return s.charAt(0).toUpperCase()+s.slice(1);})(el.type || 'Element');
                var layerTitle = el.name || defaultLabel;

                var $item = $('<div>')
                    .addClass('zappoppop-layer-item')
                    .attr('id', 'layer-item-' + el.id)
                    .attr('data-id', el.id)
                    .toggleClass('active', isSelected)
                    .toggleClass('is-locked', isLocked)
                    .toggleClass('is-hidden', isHidden);

                // 30% Smaller Action Buttons
                var itemHtml = 
                    '<div class="zappoppop-layer-drag-grip" title="Drag to reorder layer depth"><span class="dashicons dashicons-menu"></span></div>' +
                    '<div class="zappoppop-layer-icon"><span class="dashicons ' + iconClass + '"></span></div>' +
                    '<div class="zappoppop-layer-title zappoppop-layer-title-editable" title="' + self.escapeHtml(layerTitle) + '">' + self.escapeHtml(layerTitle) + '</div>' +
                    '<div class="zappoppop-layer-actions">' +
                        '<button type="button" class="zappoppop-layer-action-btn zappoppop-layer-btn-duplicate" title="Duplicate Layer" data-id="' + el.id + '"><span class="dashicons dashicons-admin-page"></span></button>' +
                        '<button type="button" class="zappoppop-layer-action-btn zappoppop-layer-btn-lock' + (isLocked ? ' is-active' : '') + '" title="' + (isLocked ? 'Unlock Layer' : 'Lock Layer') + '" data-id="' + el.id + '"><span class="dashicons ' + (isLocked ? 'dashicons-lock' : 'dashicons-unlock') + '"></span></button>' +
                        '<button type="button" class="zappoppop-layer-action-btn zappoppop-layer-btn-vis' + (isHidden ? ' is-active' : '') + '" title="' + (isHidden ? 'Show Layer' : 'Hide Layer') + '" data-id="' + el.id + '"><span class="dashicons ' + (isHidden ? 'dashicons-hidden' : 'dashicons-visibility') + '"></span></button>' +
                        '<button type="button" class="zappoppop-layer-action-btn zappoppop-layer-btn-del" title="Delete Layer" data-id="' + el.id + '"><span class="dashicons dashicons-trash"></span></button>' +
                    '</div>';

                $item.html(itemHtml);
                $list.append($item);
            });

            this.bindSortable();

            if (this._lastAction) {
                var la = this._lastAction;
                $('#layer-item-' + la.id + ' .zappoppop-layer-btn-' + la.kind).addClass('is-clicked');
            }
        },

        getLayerIcon: function(type) {
            var t = (type || 'text').toString().toLowerCase().trim();
            switch (t) {
                // Core Typography
                case 'title':       return 'dashicons-heading';
                case 'text':
                case 'paragraph':   return 'dashicons-editor-paragraph';

                // Media & Shapes
                case 'image':       return 'dashicons-format-image';
                case 'video':       return 'dashicons-video-alt3';
                case 'shape':       return 'dashicons-marker';

                // Form Inputs
                case 'textfield':   return 'dashicons-forms';
                case 'email':       return 'dashicons-email';
                case 'number':      return 'dashicons-calculator';
                case 'select':      return 'dashicons-menu-alt';
                case 'radios':      return 'dashicons-marker';
                case 'checkboxgroup': return 'dashicons-yes-alt';
                case 'checkboxes':  return 'dashicons-yes';
                case 'rating':      return 'dashicons-star-filled';
                case 'date':        return 'dashicons-calendar-alt';
                case 'slider':      return 'dashicons-leftright';
                case 'signature':   return 'dashicons-edit';

                // Gamification & Urgency
                case 'wheel':       return 'dashicons-update';
                case 'scratch':     return 'dashicons-tickets-alt';
                case 'countdown':   return 'dashicons-clock';
                case 'progress':    return 'dashicons-performance';
                case 'file':        return 'dashicons-upload';

                // Action Buttons & Triggers
                case 'submit':      return 'dashicons-yes-alt';
                case 'link_btn':    return 'dashicons-admin-links';
                case 'step_btn':    return 'dashicons-arrow-right-alt';
                case 'pay':         return 'dashicons-cart';
                case 'close_icon':  return 'dashicons-no-alt';

                // Custom HTML
                case 'html':        return 'dashicons-html';

                default:            return 'dashicons-admin-generic';
            }
        },

        bindSortable: function() {
            var self = this;
            var $list = $('#zappoppop-layers-list');
            if (!$list.length) return;

            $list.sortable({
                    distance: 8,
                    cancel: 'input,button,.zappoppop-layer-action-btn,.zappoppop-layer-actions',
                handle: '.zappoppop-layer-drag-grip',
                axis: 'y',
                containment: 'parent',
                tolerance: 'pointer',
                opacity: 0.85,
                stop: function() {
                    self.syncZIndexesFromList();
                }
            });
        },

        syncZIndexesFromList: function() {
            var $items = $('#zappoppop-layers-list').find('.zappoppop-layer-item');
            var total = $items.length;
            if (!total) return;

            var elements = window.ZapPopPopBuilderCanvas.getActiveElements();

            $items.each(function(index) {
                var id = $(this).attr('data-id');
                var newZ = (total - index) * 5 + 10;

                var el = elements.find(function(e) { return String(e.id) === String(id); });
                if (el) {
                    el.zIndex = newZ;
                    $('#el-' + id).css('z-index', newZ);
                }
            });

            if (window.ZapPopPopBuilderCore) {
                window.ZapPopPopBuilderCore.pushHistory();
            }
        },

        pushForInspector: function(isOpen) {
            var $panel = $('#zappoppop-floating-layers-panel');
            if (!$panel.length) return;

            var $workspace = $('#zappoppop-builder-workspace, .zappoppop-builder-workspace').first();
            var wsWidth = $workspace.width() || $(window).width();
            var drawerWidth = 360;
            var gutter = 16;
            var panelWidth = $panel.outerWidth() || 240;

            if (isOpen) {
                var hasInlineLeft = $panel[0].style.left !== '' && $panel[0].style.left !== 'auto';
                if (hasInlineLeft) {
                    var currentLeft = parseFloat($panel.css('left')) || 0;
                    var collisionThreshold = wsWidth - drawerWidth - gutter;

                    if (currentLeft + panelWidth > collisionThreshold) {
                        if ($panel.data('orig-left') === undefined) {
                            $panel.data('orig-left', currentLeft);
                        }
                        var pushedLeft = Math.max(gutter, collisionThreshold - panelWidth);
                        $panel.css({
                            left: pushedLeft + 'px',
                            right: 'auto',
                            transition: 'left 0.28s cubic-bezier(0.16, 1, 0.3, 1)'
                        });
                    }
                } else {
                    $panel.removeClass('is-dragged').addClass('docked-default');
                    $panel.css({
                        right: (drawerWidth + gutter) + 'px',
                        left: 'auto',
                        transition: 'right 0.28s cubic-bezier(0.16, 1, 0.3, 1)'
                    });
                }
            } else {
                if ($panel.data('orig-left') !== undefined) {
                    var origLeft = $panel.data('orig-left');
                    $panel.removeData('orig-left');
                    $panel.css({
                        left: origLeft + 'px',
                        right: 'auto',
                        transition: 'left 0.28s cubic-bezier(0.16, 1, 0.3, 1)'
                    });
                } else if (!$panel.hasClass('is-dragged') || ($panel[0].style.left === '' || $panel[0].style.left === 'auto')) {
                    $panel.addClass('docked-default');
                    $panel.css({
                        right: gutter + 'px',
                        left: 'auto',
                        transition: 'right 0.28s cubic-bezier(0.16, 1, 0.3, 1)'
                    });
                }
            }
        },

        pushLayerPanel: function(isOpen) {
            this.pushForInspector(isOpen);
        },

        bindEvents: function() {
            var self = this;

            // 1. Row click -> select on stage + slide in Layer Settings drawer
            $(document).off('click.zappoppop_layer_row').on('click.zappoppop_layer_row', '.zappoppop-layer-item', function(e) {
                if ($(e.target).closest('.zappoppop-layer-actions, .zappoppop-layer-drag-grip, input, button').length) return;
                var id = $(this).attr('data-id');
                if (!id) return;
                $('#zappoppop-layers-list .zappoppop-layer-action-btn').removeClass('is-clicked');
                self._lastAction = null;
                self.openLayerInspector(id);
            });

            // 2. Action icon highlight: only the icon that was clicked turns active
            $(document).off('click.zappoppop_layer_btnactive').on('click.zappoppop_layer_btnactive', '.zappoppop-layer-action-btn', function(e) {
                e.stopPropagation();
                var $btn = $(this);
                $('#zappoppop-layers-list .zappoppop-layer-action-btn').not($btn).removeClass('is-clicked');
                var wasOn = $btn.hasClass('is-clicked');
                $btn.toggleClass('is-clicked', !wasOn);
                var kind = $btn.hasClass('zappoppop-layer-btn-lock') ? 'lock' :
                           ($btn.hasClass('zappoppop-layer-btn-vis') ? 'vis' : null);
                self._lastAction = (kind && !wasOn) ? { id: String($btn.attr('data-id')), kind: kind } : null;
            });

            // 3. Lock / Unlock
            $(document).off('click.layerLock', '.zappoppop-layer-btn-lock').on('click.layerLock', '.zappoppop-layer-btn-lock', function(e) {
                e.stopPropagation();
                self.toggleLock($(this).attr('data-id'));
            });

            // 4. Visibility
            $(document).off('click.layerVis', '.zappoppop-layer-btn-vis').on('click.layerVis', '.zappoppop-layer-btn-vis', function(e) {
                e.stopPropagation();
                self.toggleVisibility($(this).attr('data-id'));
            });

            // 5. Duplicate
            $(document).off('click.layerDup', '.zappoppop-layer-btn-duplicate').on('click.layerDup', '.zappoppop-layer-btn-duplicate', function(e) {
                e.stopPropagation();
                self.duplicateLayer($(this).attr('data-id'));
            });

            // 6. Delete
            $(document).off('click.layerDel', '.zappoppop-layer-btn-del').on('click.layerDel', '.zappoppop-layer-btn-del', function(e) {
                e.stopPropagation();
                self.deleteLayer($(this).attr('data-id'));
            });
        },

        openLayerInspector: function(id) {
            var clean = String(id).replace(/^(zappoppop-el-|el-)/, '');
            var core = window.ZapPopPopBuilderCore;
            if (core && core.state) {
                core.state.activeId = clean;
            }
            if (window.ZapPopPopBuilderCanvas && typeof window.ZapPopPopBuilderCanvas.selectElement === 'function') {
                window.ZapPopPopBuilderCanvas.selectElement(clean);
            }
            if (window.ZapPopPopBuilderInspector && typeof window.ZapPopPopBuilderInspector.open === 'function') {
                window.ZapPopPopBuilderInspector.open(clean);
            }
            var reg = window.ZapPopPopElementsRegistry;
            if (reg && typeof reg.forceSlideInDrawer === 'function') {
                reg.forceSlideInDrawer(true);
            } else {
                var $drawer = $('#zappoppop-inspector-drawer');
                if ($drawer.length) {
                    $drawer.removeClass('is-closed closed').addClass('is-open open').css('display', 'flex');
                    $('body, .zappoppop-builder-workspace, .zappoppop-main-frame').addClass('panel-open inspector-open');
                    this.pushForInspector(true);
                }
            }
            $('.zappoppop-layer-item').removeClass('active');
            $('#layer-item-' + clean).addClass('active');
        },

        highlightLayer: function(id) {
            $('.zappoppop-layer-item').removeClass('active');
            var $item = $('#layer-item-' + id);
            if ($item.length) {
                $item.addClass('active');
                var $container = $('#zappoppop-layers-list');
                if ($container.length) {
                    var itemTop = $item.position().top;
                    if (itemTop < 0 || itemTop > $container.height() - 30) {
                        $container.scrollTop($container.scrollTop() + itemTop - 10);
                    }
                }
            }
        },

        toggleLock: function(id) {
            var elements = window.ZapPopPopBuilderCanvas.getActiveElements();
            var el = elements.find(function(e) { return String(e.id) === String(id); });
            if (!el) return;

            el.locked = !el.locked;

            var $node = $('#el-' + id);
            var $item = $('#layer-item-' + id);

            if (el.locked) {
                $node.addClass('zappoppop-locked');
                $item.addClass('is-locked');
                $item.find('.zappoppop-layer-btn-lock').addClass('active is-active');
                $item.find('.zappoppop-layer-btn-lock').attr('title', 'Unlock Layer')
                    .find('.dashicons').removeClass('dashicons-unlock').addClass('dashicons-lock');

                try {
                    $node.draggable('disable');
                    $node.resizable('disable');
                } catch(err) {}
            } else {
                $node.removeClass('zappoppop-locked');
                $item.removeClass('is-locked');
                $item.find('.zappoppop-layer-btn-lock').removeClass('active is-active');
                $item.find('.zappoppop-layer-btn-lock').attr('title', 'Lock Layer')
                    .find('.dashicons').removeClass('dashicons-lock').addClass('dashicons-unlock');

                try {
                    $node.draggable('enable');
                    $node.resizable('enable');
                } catch(err) {}
            }

            if (window.ZapPopPopBuilderCore) {
                window.ZapPopPopBuilderCore.pushHistory();
            }
        },

        toggleVisibility: function(id) {
            var elements = window.ZapPopPopBuilderCanvas.getActiveElements();
            var el = elements.find(function(e) { return String(e.id) === String(id); });
            if (!el) return;

            el.hidden = !el.hidden;

            var $node = $('#el-' + id);
            var $item = $('#layer-item-' + id);

            if (el.hidden) {
                $node.css('display', 'none');
                $item.addClass('is-hidden');
                $item.find('.zappoppop-layer-btn-vis').addClass('active is-active');
                $item.find('.zappoppop-layer-btn-vis').attr('title', 'Show Layer')
                    .find('.dashicons').removeClass('dashicons-visibility').addClass('dashicons-hidden');
            } else {
                $node.css('display', 'block');
                $item.removeClass('is-hidden');
                $item.find('.zappoppop-layer-btn-vis').removeClass('active is-active');
                $item.find('.zappoppop-layer-btn-vis').attr('title', 'Hide Layer')
                    .find('.dashicons').removeClass('dashicons-hidden').addClass('dashicons-visibility');
            }

            if (window.ZapPopPopBuilderCore) {
                window.ZapPopPopBuilderCore.pushHistory();
            }
        },

        duplicateLayer: function(id) {
            var elements = window.ZapPopPopBuilderCanvas.getActiveElements();
            var el = elements.find(function(e) { return String(e.id) === String(id); });
            if (!el) return;

            var clone = JSON.parse(JSON.stringify(el));
            var newId = 'layer_' + Date.now().toString(36) + '_' + Math.random().toString(36).substr(2, 4);
            clone.id = newId;

            var baseLabel = (el.type === 'text' || el.type === 'paragraph') ? 'Paragraph' : (el.name || (function(s){s=String(s||'');return s.charAt(0).toUpperCase()+s.slice(1);})(el.type));
            clone.name = baseLabel + ' (Copy)';

            clone.top = (parseInt(el.top, 10) || 0) + 15;
            clone.left = (parseInt(el.left, 10) || 0) + 15;

            var maxZ = elements.reduce(function(max, item) {
                return Math.max(max, parseInt(item.zIndex, 10) || 10);
            }, 10);
            clone.zIndex = maxZ + 5;

            elements.push(clone);

            window.ZapPopPopBuilderCanvas.renderCanvas();
            this.renderLayers();

            window.ZapPopPopBuilderCanvas.selectElement(newId);

            if (window.ZapPopPopBuilderCore) {
                window.ZapPopPopBuilderCore.pushHistory();
            }
        },

        deleteLayer: function(id) {
            var elements = window.ZapPopPopBuilderCanvas.getActiveElements();
            var idx = elements.findIndex(function(e) { return String(e.id) === String(id); });
            if (idx === -1) return;

            elements.splice(idx, 1);

            /* ZAPPOPPOP_KEEP_INSPECTOR_ON_DELETE: soft-deselect, never close the Layer Settings drawer */
            var _core = window.ZapPopPopBuilderCore;
            var _clean = function(v) { return String(v == null ? '' : v).replace(/^(zappoppop-el-|el-)/, ''); };
            if (_core && _core.state && _core.state.activeId != null && _clean(_core.state.activeId) === _clean(id)) {
                _core.state.activeId = null;
                window.ZapPopPopBuilderCanvas.selectedId = null;
                $('.zappoppop-canvas-item').removeClass('zappoppop-selected');
                $('.zappoppop-layer-item').removeClass('active');
                $('#zappoppop-alignment-toolbar').hide();
                $('#zappoppop-guide-v, #zappoppop-guide-h').hide();
            }

            window.ZapPopPopBuilderCanvas.renderCanvas();
            this.renderLayers();

            if (window.ZapPopPopBuilderCore) {
                window.ZapPopPopBuilderCore.pushHistory();
            }
        },

        escapeHtml: function(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }
    };

    window.ZapPopPopBuilder = window.ZapPopPopBuilder || {};
    window.ZapPopPopBuilder.Layers = window.ZapPopPopBuilderLayers;
})(jQuery);

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
