(function () {
    'use strict';

    function init() {
        ['empty', 'extra', 'parent'].forEach(initPanel);
    }

    function initPanel(kind) {
        var toggle = document.getElementById('dci-at-' + kind + '-toggle');
        var panel = document.getElementById('dci-at-' + kind + '-sections');
        var close = document.getElementById('dci-at-' + kind + '-close');
        if (!toggle || !panel || !close) {
            return;
        }
        var label = toggle.querySelector('[data-empty-toggle-label]');
        function setOpen(open) {
            panel.hidden = !open;
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            label.textContent = open ? 'Nascondi elenco' : 'Mostra elenco';
        }
        toggle.addEventListener('click', function () {
            setOpen(panel.hidden);
        });
        close.addEventListener('click', function () {
            setOpen(false);
            toggle.focus();
        });
        panel.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                setOpen(false);
                toggle.focus();
            }
        });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
}());
