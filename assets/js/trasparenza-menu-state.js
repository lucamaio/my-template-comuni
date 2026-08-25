(function () {
    'use strict';

    var STORAGE_PREFIX = 'dciAtMenuState:v1:';

    /**
     * Restituisce una chiave distinta per ogni pagina, evitando interferenze
     * tra portali WordPress ospitati sullo stesso dominio.
     *
     * @returns {string} Chiave utilizzata nel localStorage.
     */
    function getStorageKey() {
        return STORAGE_PREFIX + window.location.pathname.replace(/\/+$/, '/');
    }

    /**
     * Legge lo stato salvato senza interrompere la pagina se il browser blocca
     * il localStorage (modalita privata, policy aziendali o quota esaurita).
     *
     * @returns {{categories: string[], nested: string[]}|null} Stato valido o null.
     */
    function readState() {
        try {
            var state = JSON.parse(window.localStorage.getItem(getStorageKey()));

            if (!state || !Array.isArray(state.categories) || !Array.isArray(state.nested)) {
                return null;
            }

            return {
                categories: state.categories.filter(function (id) {
                    return typeof id === 'string';
                }),
                nested: state.nested.filter(function (id) {
                    return typeof id === 'string';
                })
            };
        } catch (error) {
            return null;
        }
    }

    /**
     * Aggiorna testo e attributi accessibili dei comandi delle sottosezioni.
     *
     * @param {HTMLElement} toggle Pulsante associato al pannello.
     * @param {boolean} isExpanded Indica se il pannello e aperto.
     * @returns {void}
     */
    function setNestedToggleState(toggle, isExpanded) {
        toggle.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');
        toggle.classList.toggle('is-open', isExpanded);

        var label = toggle.querySelector('.js-subcat-toggle-label');
        if (label) {
            label.textContent = isExpanded ? 'Nascondi sottovoci' : 'Mostra sottovoci';
        }
    }

    /**
     * Allinea il comando globale allo stato effettivo delle sezioni principali.
     *
     * @returns {void}
     */
    function updateToggleAllState() {
        var panels = Array.prototype.slice.call(document.querySelectorAll('.js-category-content'));
        var button = document.getElementById('toggle-all-btn');

        if (!button || panels.length === 0) {
            return;
        }

        var allOpen = panels.every(function (panel) {
            return window.getComputedStyle(panel).display !== 'none';
        });
        var label = button.querySelector('.js-toggle-all-label');

        button.classList.toggle('is-open', allOpen);
        if (label) {
            label.textContent = allOpen ? 'Chiudi tutte le sezioni' : 'Espandi tutte le sezioni';
        }
    }

    /**
     * Applica soltanto gli ID ancora presenti nel DOM. In questo modo termini
     * eliminati o riorganizzati non producono errori ne stato obsoleto.
     *
     * @param {{categories: string[], nested: string[]}} state Stato memorizzato.
     * @returns {void}
     */
    function restoreState(state) {
        var categoryIds = new Set(state.categories);
        var nestedIds = new Set(state.nested);

        document.querySelectorAll('.js-category-content[id]').forEach(function (panel) {
            var isExpanded = categoryIds.has(panel.id);
            var title = document.querySelector('.title-custom[data-target="' + panel.id + '"]');

            panel.style.display = isExpanded ? 'block' : 'none';
            if (title) {
                title.classList.toggle('is-open', isExpanded);
                title.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');
            }
        });

        document.querySelectorAll('.js-subcat-children[id]').forEach(function (panel) {
            var isExpanded = nestedIds.has(panel.id);
            panel.hidden = !isExpanded;

            document.querySelectorAll('.js-subcat-toggle').forEach(function (toggle) {
                if (toggle.getAttribute('aria-controls') === panel.id) {
                    setNestedToggleState(toggle, isExpanded);
                }
            });
        });

        updateToggleAllState();
    }

    /**
     * Memorizza esclusivamente gli ID dei pannelli aperti. Non vengono salvati
     * dati personali, contenuti o informazioni dell'utente.
     *
     * @returns {void}
     */
    function saveState() {
        var categoryPanels = document.querySelectorAll('.js-category-content[id]');

        if (categoryPanels.length === 0) {
            return;
        }

        var state = {
            categories: Array.prototype.filter.call(categoryPanels, function (panel) {
                return window.getComputedStyle(panel).display !== 'none';
            }).map(function (panel) {
                return panel.id;
            }),
            nested: Array.prototype.filter.call(
                document.querySelectorAll('.js-subcat-children[id]'),
                function (panel) {
                    return !panel.hidden;
                }
            ).map(function (panel) {
                return panel.id;
            })
        };

        try {
            window.localStorage.setItem(getStorageKey(), JSON.stringify(state));
        } catch (error) {
            // Il menu continua a funzionare normalmente anche senza persistenza.
        }
    }

    /**
     * Inizializza la persistenza dopo la creazione del menu e osserva soltanto
     * i comandi appartenenti all'Amministrazione Trasparente.
     *
     * @returns {void}
     */
    function init() {
        if (!document.querySelector('.js-category-content')) {
            return;
        }

        var state = readState();
        if (state) {
            restoreState(state);
        }

        document.addEventListener('click', function (event) {
            if (!event.target.closest('.title-custom, .js-subcat-toggle, #toggle-all-btn')) {
                return;
            }

            window.setTimeout(saveState, 0);
        });

        document.addEventListener('keydown', function (event) {
            if (
                (event.key === 'Enter' || event.key === ' ')
                && event.target.closest('.title-custom:not(.no-children)')
            ) {
                window.setTimeout(saveState, 0);
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
}());
