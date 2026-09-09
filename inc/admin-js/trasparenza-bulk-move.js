(function () {
    'use strict';

    function init() {
        var form = document.getElementById('posts-filter');
        var panel = document.getElementById('dci-at-bulk-move');
        if (!form || !panel) {
            return;
        }

        var actions = Array.from(form.querySelectorAll('select[name="action"], select[name="action2"]'));
        var fields = panel.querySelectorAll('select, input');
        var confirmation = panel.querySelector('input[name="dci_at_move_confirm"]');
        var navigation = panel.closest('.tablenav');
        var moveAction = 'dci_at_move_section';

        function update() {
            var visible = actions.some(function (select) { return select.value === moveAction; });
            panel.hidden = !visible;
            if (navigation) {
                navigation.classList.toggle('dci-at-bulk-move-is-open', visible);
            }
            fields.forEach(function (field) { field.disabled = !visible; });
            if (!visible && confirmation) {
                confirmation.checked = false;
            }
        }

        actions.forEach(function (select) {
            select.addEventListener('change', function () {
                actions.forEach(function (other) {
                    if (other === select) {
                        return;
                    }
                    if (select.value === moveAction) {
                        other.value = moveAction;
                    } else if (other.value === moveAction) {
                        other.value = '-1';
                    }
                });
                update();
                if (select.name === 'action2' && !panel.hidden) {
                    panel.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                }
            });
        });

        window.addEventListener('pageshow', update);
        update();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
}());
