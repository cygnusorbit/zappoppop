(function(window, $) {
    'use strict';
    window.ZapPopPopLog = window.ZapPopPopLog || {};

    var Payload = {
        currentRawPayload: '',

        init: function() {
            this.bindInspector();
            this.bindDismissal();
            this.bindCopy();
        },

        bindInspector: function() {
            var self = this;
            $('.zappoppop-view-payload-btn').on('click', function() {
                var $btn = $(this);
                var id = $btn.data('id');
                var type = $btn.data('type');
                var msg = $btn.data('msg');
                var date = $btn.data('date');
                var payload = $btn.data('payload');

                $('#zappoppop-modal-log-title').text('#' + id + ' • ' + type + ' (' + date + ')');
                $('#zappoppop-modal-log-desc').text(msg);

                var prettyJson = '';
                try {
                    var parsed = (typeof payload === 'string') ? JSON.parse(payload) : payload;
                    prettyJson = JSON.stringify(parsed, null, 2);
                } catch(e) {
                    prettyJson = String(payload);
                }

                self.currentRawPayload = prettyJson;
                $('#zappoppop-modal-payload-code').text(prettyJson);
                $('#zappoppop-payload-modal').css('display', 'flex');
            });
        },

        bindDismissal: function() {
            $('#zappoppop-payload-close, #zappoppop-payload-done').on('click', function() {
                $('#zappoppop-payload-modal').hide();
            });

            $('#zappoppop-payload-modal').on('click', function(e) {
                if (e.target === this) {
                    $(this).hide();
                }
            });
        },

        bindCopy: function() {
            var self = this;
            $('#zappoppop-payload-copy-btn').on('click', function() {
                if (navigator.clipboard && self.currentRawPayload) {
                    navigator.clipboard.writeText(self.currentRawPayload);
                    var orig = $(this).text();
                    $(this).text('Copied!');
                    var $selfBtn = $(this);
                    setTimeout(function() { $selfBtn.text(orig); }, 1500);
                }
            });
        }
    };

    window.ZapPopPopLog.Payload = Payload;
})(window, jQuery);
