(function ($) {
    'use strict';

    /**
     * Blocca nell'interfaccia la destinazione gia' verificata dal server.
     * I controlli PHP al salvataggio restano la protezione definitiva.
     */
    $(function () {
        var context = document.querySelector('[data-dci-at-context="1"]');

        if (!context) {
            return;
        }

        var postType = context.getAttribute('data-post-type') || '';
        var termSlug = context.getAttribute('data-term-slug') || '';
        var sectionValue = context.getAttribute('data-section-value') || '';

        if (postType === 'elemento_trasparenza' && termSlug !== '') {
            window.setTimeout(function () {
                var inputs = document.querySelectorAll(
                    'input[name^="_dci_elemento_trasparenza_tipo_cat_amm_trasp"]'
                );
                var target = null;

                inputs.forEach(function (input) {
                    if (String(input.value) === termSlug) {
                        target = input;
                    }
                });

                if (!target) {
                    return;
                }

                inputs.forEach(function (input) {
                    input.disabled = input !== target;
                    input.setAttribute('aria-disabled', input !== target ? 'true' : 'false');
                });
                target.disabled = false;
                target.checked = true;
                target.dispatchEvent(new Event('change', { bubbles: true }));
                var taxonomyRow = target.closest('.cmb-row');
                if (taxonomyRow) {
                    taxonomyRow.classList.add('dci-at-context-locked');
                }
            }, 0);
        }

        if (postType === 'incarico_dirig' && sectionValue !== '') {
            var sectionField = document.querySelector(
                '[name="_dci_incarico_dirigenziale_sezione_pubblicazione"]'
            );

            if (!sectionField) {
                return;
            }

            sectionField.value = sectionValue;
            sectionField.disabled = true;
            sectionField.setAttribute('aria-disabled', 'true');
            var sectionRow = sectionField.closest('.cmb-row');
            if (sectionRow) {
                sectionRow.classList.add('dci-at-context-locked');
            }

            var preservedValue = document.createElement('input');
            preservedValue.type = 'hidden';
            preservedValue.name = sectionField.name;
            preservedValue.value = sectionValue;
            sectionField.insertAdjacentElement('afterend', preservedValue);
        }
    });
}(jQuery));
