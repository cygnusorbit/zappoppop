/**
 * ZapPopPop Dashboard: Embed & Shortcode Modal Engine
 * Cross-Browser Compatibility: Safari, Firefox, Chrome, Edge
 */
(function(window, $) {
    'use strict';
    window.ZapPopPopDashboardEmbed = window.ZapPopPopDashboardEmbed || {};

    var Embed = {
        init: function() {
            this.bindTriggers();
            this.bindCopyButtons();
            this.bindClose();
        },

        bindTriggers: function() {
            var self = this;
            $(document).off('click.zappoppopDashEmbed', '.btn-embed-popup, .zappoppop-action-embed')
                .on('click.zappoppopDashEmbed', '.btn-embed-popup, .zappoppop-action-embed', function(e) {
                    e.preventDefault();
                    var $t = $(this);
                    var $row = $t.closest('tr');
                    var uid = $.trim(String($t.attr('data-uid') || $row.attr('data-uid') || $row.find('input[name="bulk_ids[]"]').val() || ''));
                    if (!uid) return;
                    var status = String($t.attr('data-status') || $row.attr('data-status') || 'publish');
                    self.open(uid, status);
                });
        },

        open: function(uid, status) {
            $('#zappoppop-dash-sc-field').val('[zappoppop uid="' + uid + '"]');
            $('#zappoppop-dash-btnsc-field').val('[zappoppop uid="' + uid + '" mode="button" text="Open Popup"]');
            $('#zappoppop-dash-btn-field').val('<a href="#" class="zappoppop-trigger" data-zappoppop-id="' + uid + '">Open Popup</a>');
            $('#zappoppop-dash-js-field').val("window.ZapPopPop && window.ZapPopPop.show('" + uid + "');");
            $('#zappoppop-dash-embed-uid').text(uid);
            $('#zappoppop-dash-embed-draft').toggle(status !== 'publish' && status !== 'published');
            $('#zappoppop-dash-embed-modal').stop(true, true).css({
                display: 'flex', alignItems: 'center', justifyContent: 'center', opacity: 0
            }).animate({ opacity: 1 }, 150);
        },

        close: function() {
            var $m = $('#zappoppop-dash-embed-modal');
            if (!$m.is(':visible')) return;
            $m.stop(true, true).animate({ opacity: 0 }, 120, function() {
                $m.css({ display: 'none', opacity: 1 });
            });
        },

        copyText: function(text, $btn) {
            var origHtml = $btn.html();

            function triggerSuccess() {
                $btn.html('<span class="dashicons dashicons-yes" style="font-size:12px;width:12px;height:12px;"></span> Copied!');
                setTimeout(function() {
                    $btn.html(origHtml);
                }, 1500);
            }

            // Universal Cross-Browser Fallback Function (Safari / Non-HTTPS safe)
            function execCommandFallback(str) {
                var textArea = document.createElement('textarea');
                textArea.value = str;
                textArea.style.position = 'fixed';
                textArea.style.top = '0';
                textArea.style.left = '-9999px';
                textArea.style.opacity = '0';
                textArea.setAttribute('readonly', '');
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                textArea.setSelectionRange(0, 99999);

                var successful = false;
                try {
                    successful = document.execCommand('copy');
                } catch (err) {
                    successful = false;
                }
                document.body.removeChild(textArea);
                if (successful) {
                    triggerSuccess();
                } else {
                    window.prompt(wp.i18n.__('Copy to clipboard (Cmd/Ctrl+C, Enter):', 'zappoppop'), str);
                }
            }

            // Modern W3C Clipboard API with Synchronous Fallback
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(function() {
                    triggerSuccess();
                }).catch(function() {
                    execCommandFallback(text);
                });
            } else {
                execCommandFallback(text);
            }
        },

        bindCopyButtons: function() {
            var self = this;

            // 1. Direct table row chip click
            $(document).off('click.zappoppopDashEmbed', '.zappoppop-shortcode-chip').on('click.zappoppopDashEmbed', '.zappoppop-shortcode-chip', function(e) {
                e.preventDefault();
                var text = $(this).text().trim();
                self.copyText(text, $(this));
            });

            // 2. Modal copy button clicks
            $(document).off('click.zappoppopDashEmbed', '.zappoppop-btn-copy-input').on('click.zappoppopDashEmbed', '.zappoppop-btn-copy-input', function(e) {
                e.preventDefault();
                var targetId = $(this).data('target');
                var text = $('#' + targetId).val();
                if (text) {
                    self.copyText(text, $(this));
                }
            });
        },

        bindClose: function() {
            var self = this;
            $(document).off('click.zappoppopDashEmbed', '#zappoppop-dash-embed-close')
                .on('click.zappoppopDashEmbed', '#zappoppop-dash-embed-close', function(e) {
                    e.preventDefault();
                    self.close();
                });
            $(document).off('mousedown.zappoppopDashEmbed', '#zappoppop-dash-embed-modal')
                .on('mousedown.zappoppopDashEmbed', '#zappoppop-dash-embed-modal', function(e) {
                    if (e.target === this) self.close();
                });
            $(document).off('keydown.zappoppopDashEmbed')
                .on('keydown.zappoppopDashEmbed', function(e) {
                    if (e.key === 'Escape' || e.keyCode === 27) self.close();
                });
            $(document).off('focus.zappoppopDashEmbed', '#zappoppop-dash-embed-modal input[readonly]')
                .on('focus.zappoppopDashEmbed', '#zappoppop-dash-embed-modal input[readonly]', function() { this.select(); });
        }
    };

    window.ZapPopPopDashboardEmbed = Embed;
})(window, jQuery);
