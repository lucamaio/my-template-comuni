(function () {
    'use strict';

    /**
     * Verifica che il colore calcolato sia utilizzabile e non trasparente.
     *
     * @param {string} color Colore restituito da getComputedStyle().
     * @returns {boolean} True quando il colore puo essere usato dai componenti.
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
     * Espone il colore ai componenti della trasparenza che lo richiedono.
     *
     * @returns {void}
     */
    function init() {
        var wrapper = document.querySelector('.dci-at-wrap');
        var sidebars = document.querySelectorAll('.dci-amm-sidebar');
        if (!wrapper && !sidebars.length) {
            return;
        }

        var institutionColor = getInstitutionColor();
        if (wrapper && institutionColor) {
            wrapper.style.setProperty('--dci-at-entity-color', institutionColor);
        }
        sidebars.forEach(function (sidebar) {
            if (institutionColor) {
                sidebar.style.setProperty('--dci-amm-sidebar-accent', institutionColor);
            }
            var usefulLink = sidebar.querySelector('.dci-amm-sidebar__theme-link');
            var linkColor = usefulLink ? window.getComputedStyle(usefulLink).color : '';
            var accent = isUsableColor(linkColor) ? linkColor : institutionColor;
            if (accent) {
                sidebar.style.setProperty('--dci-amm-sidebar-accent', accent);
            }
            if (institutionColor) {
                sidebar.querySelectorAll('.dci-amm-sidebar__nav').forEach(function (nav) {
                    nav.style.setProperty('--dci-amm-sidebar-accent', institutionColor);
                });
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
}());
