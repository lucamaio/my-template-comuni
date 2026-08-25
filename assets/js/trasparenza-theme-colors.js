(function () {
    'use strict';

    /**
     * Verifica che il colore calcolato sia utilizzabile e non trasparente.
     *
     * @param {string} color Colore restituito da getComputedStyle().
     * @returns {boolean} True quando il colore puo essere ereditato dalle card.
     */
    function isUsableColor(color) {
        return Boolean(
            color
            && color !== 'transparent'
            && color !== 'rgba(0, 0, 0, 0)'
        );
    }

    /**
     * Recupera il colore realmente applicato dal CSS aggiuntivo di WordPress.
     * L'ordine rispecchia le principali aree cromatiche dell'intestazione.
     *
     * @returns {string} Colore dell'ente oppure stringa vuota.
     */
    function getInstitutionColor() {
        var selectors = [
            '.it-header-center-wrapper',
            '.it-header-navbar-wrapper',
            '.it-header-slim-wrapper'
        ];

        for (var index = 0; index < selectors.length; index += 1) {
            var element = document.querySelector(selectors[index]);

            if (!element) {
                continue;
            }

            var color = window.getComputedStyle(element).backgroundColor;
            if (isUsableColor(color)) {
                return color;
            }
        }

        return '';
    }

    /**
     * Espone il colore soltanto al componente Articolazione uffici, evitando
     * effetti collaterali sulle altre pagine o sui CSS personalizzati esistenti.
     *
     * @returns {void}
     */
    function init() {
        var wrapper = document.querySelector('.dci-at-wrap');
        if (!wrapper) {
            return;
        }

        var institutionColor = getInstitutionColor();
        if (institutionColor) {
            wrapper.style.setProperty('--dci-at-entity-color', institutionColor);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
}());
