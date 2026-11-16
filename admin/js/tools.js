/**
 * ZapPopPop Tools: Master Coordinator
 */
(function($) {
    'use strict';

    /* form.submit() drops the clicked button's name, so the PHP handler never ran. */
    window.ZapPopPopToolsFallback = {
        submit: function(form, key) {
            if (!form) { return; }
            if (key && !form.querySelector('input[type="hidden"][name="' + key + '"]')) {
                var i = document.createElement('input');
                i.type = 'hidden'; i.name = key; i.value = '1';
                form.appendChild(i);
            }
            HTMLFormElement.prototype.submit.call(form);
        }
    };

    /* Destructive actions require typing RESET. */
    window.ZapPopPopTypedConfirm = function(message) {
        var v = window.prompt(message + '\n\n' + wp.i18n.__('Type RESET to confirm.', 'zappoppop'));
        return v !== null && v.replace(/^\s+|\s+$/g, '') === 'RESET';
    };

    window.ZapPopPopTools = {
        init: function() {
            if (window.ZapPopPopToolsSystem) {
                window.ZapPopPopToolsSystem.init();
            }
            if (window.ZapPopPopToolsDatabase) {
                window.ZapPopPopToolsDatabase.init();
            }
            if (window.ZapPopPopToolsPortability) {
                window.ZapPopPopToolsPortability.init();
            }
        }
    };

    $(document).ready(function() {
        window.ZapPopPopTools.init();
    });
})(jQuery);
