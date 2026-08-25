(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var guide = document.querySelector('.dci-admin-guide');

        if (!guide) {
            return;
        }

        var content = guide.querySelector('.dci-admin-guide__content');
        var sections = Array.prototype.slice.call(content.querySelectorAll(':scope > section'));
        var search = guide.querySelector('#dci-admin-guide-search');
        var clearButton = guide.querySelector('.dci-admin-guide__clear');
        var results = guide.querySelector('.dci-admin-guide__results');
        var empty = guide.querySelector('.dci-admin-guide__empty');
        var pdfHelp = guide.querySelector('.dci-admin-guide__pdf-help');
        var moreButton = guide.querySelector('.dci-admin-guide__more');
        var outputMenu = guide.querySelector('.dci-admin-guide__output-popover');
        var printState = [];
        var originalTitle = document.title;

        sections.forEach(function (section, index) {
            var heading = section.querySelector('h2');
            var number = section.querySelector('.dci-admin-guide__number');
            var panel = document.createElement('div');
            var button = document.createElement('button');
            var title = document.createElement('span');
            var icon = document.createElement('span');
            var panelId = 'dci-guide-panel-' + (index + 1);

            panel.className = 'dci-admin-guide__panel';
            panel.id = panelId;

            Array.prototype.slice.call(section.children).forEach(function (child) {
                if (child !== heading && child !== number) {
                    panel.appendChild(child);
                }
            });

            button.className = 'dci-admin-guide__toggle';
            button.type = 'button';
            button.setAttribute('aria-expanded', index === 0 ? 'true' : 'false');
            button.setAttribute('aria-controls', panelId);

            title.className = 'dci-admin-guide__toggle-title';
            title.textContent = heading.textContent;
            icon.className = 'dashicons dashicons-arrow-down-alt2';
            icon.setAttribute('aria-hidden', 'true');

            button.appendChild(number);
            button.appendChild(title);
            button.appendChild(icon);
            heading.remove();
            section.insertBefore(button, section.firstChild);
            section.appendChild(panel);
            panel.hidden = index !== 0;

            button.addEventListener('click', function () {
                setSectionOpen(section, button.getAttribute('aria-expanded') !== 'true');
            });

            section.dataset.searchText = normalizeText(section.textContent);
        });

        function normalizeText(value) {
            return (value || '')
                .toLocaleLowerCase('it')
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .trim();
        }

        function setSectionOpen(section, open) {
            var button = section.querySelector('.dci-admin-guide__toggle');
            var panel = section.querySelector('.dci-admin-guide__panel');

            button.setAttribute('aria-expanded', open ? 'true' : 'false');
            panel.hidden = !open;
            section.classList.toggle('is-open', open);
        }

        function filterGuide() {
            var query = normalizeText(search.value);
            var visibleCount = 0;

            sections.forEach(function (section) {
                var matches = !query || section.dataset.searchText.indexOf(query) !== -1;

                section.hidden = !matches;
                if (matches) {
                    visibleCount += 1;
                    if (query) {
                        setSectionOpen(section, true);
                    }
                }
            });

            clearButton.hidden = !query;
            empty.hidden = visibleCount !== 0;
            results.textContent = query
                ? visibleCount + (visibleCount === 1 ? ' sezione trovata' : ' sezioni trovate')
                : '';
        }

        search.addEventListener('input', filterGuide);
        clearButton.addEventListener('click', function () {
            search.value = '';
            filterGuide();
            search.focus();
        });

        function setOutputMenuOpen(open) {
            moreButton.setAttribute('aria-expanded', open ? 'true' : 'false');
            outputMenu.hidden = !open;
        }

        moreButton.addEventListener('click', function () {
            setOutputMenuOpen(moreButton.getAttribute('aria-expanded') !== 'true');
        });

        document.addEventListener('click', function (event) {
            if (!event.target.closest('.dci-admin-guide__output-menu')) {
                setOutputMenuOpen(false);
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !outputMenu.hidden) {
                setOutputMenuOpen(false);
                moreButton.focus();
            }
        });

        guide.querySelectorAll('[data-guide-action]').forEach(function (control) {
            control.addEventListener('click', function () {
                var shouldOpen = control.dataset.guideAction === 'expand';

                sections.forEach(function (section) {
                    if (!section.hidden) {
                        setSectionOpen(section, shouldOpen);
                    }
                });
            });
        });

        function prepareOutput(mode) {
            printState = sections.map(function (section) {
                return {
                    hidden: section.hidden,
                    open: section.querySelector('.dci-admin-guide__toggle').getAttribute('aria-expanded') === 'true'
                };
            });

            sections.forEach(function (section) {
                section.hidden = false;
                setSectionOpen(section, true);
            });

            document.body.classList.add('dci-admin-guide-printing');
            document.title = 'Guida-alla-pubblicazione-Amministrazione-Trasparente';
            pdfHelp.hidden = mode !== 'pdf';
        }

        function restoreAfterOutput() {
            sections.forEach(function (section, index) {
                section.hidden = printState[index] ? printState[index].hidden : false;
                setSectionOpen(section, printState[index] ? printState[index].open : index === 0);
            });

            document.body.classList.remove('dci-admin-guide-printing');
            document.title = originalTitle;
            pdfHelp.hidden = true;
        }

        guide.querySelectorAll('[data-guide-output]').forEach(function (control) {
            control.addEventListener('click', function () {
                var mode = control.dataset.guideOutput;

                setOutputMenuOpen(false);
                prepareOutput(mode);
                window.setTimeout(function () {
                    window.print();
                }, 80);
            });
        });

        window.addEventListener('afterprint', restoreAfterOutput);

        guide.querySelectorAll('.dci-admin-guide__index a').forEach(function (link) {
            link.addEventListener('click', function () {
                var section = document.querySelector(link.getAttribute('href'));

                if (section) {
                    section.hidden = false;
                    setSectionOpen(section, true);
                }
            });
        });

        setSectionOpen(sections[0], true);
    });
}());
