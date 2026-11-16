/**
 * ZapPopPop Dashboard: Import Modal Controller
 */
(function($) {
    'use strict';

    window.ZapPopPopDashboardImport = {
        /* ZAPPOPPOP_IMPORT_V2 */
        fileName: '',

        init: function() {
            this.bindModal();
            this.bindFile();
            this.bindSubmit();
        },

        getVars: function() {
            return window.zappoppop_vars || {
                ajax_url: (typeof ajaxurl !== 'undefined') ? ajaxurl : '/wp-admin/admin-ajax.php',
                nonce: ''
            };
        },

        openModal: function() {
            $('#zappoppop-import-modal').css('display', 'flex');
        },

        closeModal: function() {
            $('#zappoppop-import-modal').css('display', 'none');
            $('#zappoppop-import-file').val('');
            $('#zappoppop-import-raw').val('');
            this.fileName = '';
        },

        bindModal: function() {
            var self = this;
            $(document).on('click', '#zappoppop-btn-open-import, #zappoppop-import-popup-btn', function(e) {
                e.preventDefault();
                self.openModal();
            });
            $(document).on('click', '#zappoppop-import-close, #zappoppop-import-cancel, .zappoppop-close-import-modal', function(e) {
                e.preventDefault();
                self.closeModal();
            });
            $(document).on('mousedown', '#zappoppop-import-modal', function(e) {
                if (e.target === this) { self.closeModal(); }
            });
            $(document).on('keydown', function(e) {
                if (e.which === 27 && $('#zappoppop-import-modal').css('display') !== 'none') { self.closeModal(); }
            });
        },

        bindFile: function() {
            var self = this;
            $(document).on('change', '#zappoppop-import-file', function() {
                var input = this;
                if (!input.files || !input.files[0]) { return; }
                var file = input.files[0];
                if (!window.FileReader) {
                    alert(wp.i18n.__('Your browser cannot read files. Please paste the JSON instead.', 'zappoppop'));
                    return;
                }
                self.fileName = (file.name || '').replace(/\.json$/i, '');
                var reader = new FileReader();
                reader.onload = function(evt) {
                    $('#zappoppop-import-raw').val(String(evt.target.result || ''));
                };
                reader.onerror = function() {
                    alert(wp.i18n.__('Unable to read the selected file.', 'zappoppop'));
                };
                reader.readAsText(file);
            });
        },

        bindSubmit: function() {
            var self = this;
            $(document).on('click', '#zappoppop-import-submit', function(e) {
                e.preventDefault();
                var $btn = $(this);
                if ($btn.prop('disabled')) { return; }

                var raw = $.trim(String($('#zappoppop-import-raw').val() || '')).replace(/^\uFEFF/, '');
                if (!raw) {
                    alert(wp.i18n.__('Please choose a JSON file or paste JSON first.', 'zappoppop'));
                    return;
                }

                var parsed;
                try { parsed = JSON.parse(raw); } catch (err) {
                    alert(wp.i18n.__('Invalid JSON:', 'zappoppop') + ' ' + err.message);
                    return;
                }
                if (!parsed || typeof parsed !== 'object' || $.isArray(parsed)) {
                    alert(wp.i18n.__('Invalid popup definition. Expected a JSON object.', 'zappoppop'));
                    return;
                }

                var title = '';
                if (parsed.meta && parsed.meta.title) { title = parsed.meta.title; }
                else if (parsed.title) { title = parsed.title; }
                else if (parsed.settings && parsed.settings.title) { title = parsed.settings.title; }
                else if (self.fileName) { title = self.fileName; }

                var vars = self.getVars();
                var origHtml = $btn.html();
                $btn.prop('disabled', true).text('Importing...');

                $.ajax({
                    url: vars.ajax_url,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'zappoppop_import_popup',
                        nonce: vars.nonce,
                        title: title,
                        import_data: raw
                    },
                    success: function(res) {
                        if (res && res.success) {
                            $btn.text('Imported!');
                            setTimeout(function() { window.location.reload(); }, 400);
                        } else {
                            alert((res && res.data && res.data.message) || wp.i18n.__('Import failed.', 'zappoppop'));
                            $btn.prop('disabled', false).html(origHtml);
                        }
                    },
                    error: function() {
                        alert(wp.i18n.__('Server error while importing popup.', 'zappoppop'));
                        $btn.prop('disabled', false).html(origHtml);
                    }
                });
            });
        }
    };
})(jQuery);
