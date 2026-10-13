(function () {
    'use strict';

    function init() {
        initNotices();
        var root = document.getElementById('dci-at-overview');
        if (root) {
            initOverview(root);
        }
    }

    function initNotices() {
        var notices = document.querySelector('.dci-at-notices[data-dci-at-notices-key]');
        if (!notices) {
            return;
        }
        var key = notices.getAttribute('data-dci-at-notices-key');
        try {
            var saved = window.localStorage.getItem(key);
            if (saved === 'closed' || saved === 'open') {
                notices.open = saved === 'open';
            }
        } catch (error) {
            // Il controllo resta utilizzabile anche se il browser blocca lo storage.
        }
        notices.addEventListener('toggle', function () {
            try {
                window.localStorage.setItem(key, notices.open ? 'open' : 'closed');
            } catch (error) {
                // Nessuna richiesta al server per memorizzare questa preferenza.
            }
        });
    }

    function initOverview(root) {
        var content = root.querySelector('[data-overview-content]');
        var status = root.querySelector('[data-overview-status]');
        var refresh = root.querySelector('[data-overview-refresh]');
        var refreshing = false;
        var version = 0;

        function setStatus(message) {
            status.textContent = message;
            status.hidden = !message;
        }

        function request(action, params) {
            var body = new URLSearchParams(params || {});
            body.set('action', action);
            body.set('nonce', root.getAttribute('data-nonce'));
            return window.fetch(root.getAttribute('data-ajax-url'), {
                method: 'POST', credentials: 'same-origin', body: body
            }).then(function (response) {
                return response.json().catch(function () {
                    throw new Error('Risposta non disponibile. Ricarica la pagina o riprova.');
                }).then(function (result) {
                    if (!response.ok || !result || !result.success) {
                        throw new Error(result && result.data && result.data.message
                            ? result.data.message : 'Richiesta non riuscita. Ricarica la pagina o riprova.');
                    }
                    return result.data;
                });
            });
        }

        function refreshOverview() {
            if (refreshing) {
                return;
            }
            refreshing = true;
            refresh.disabled = true;
            setStatus('Aggiornamento delle statistiche in corso…');
            request('dci_at_overview_refresh').then(function (result) {
                version += 1;
                content.innerHTML = result.html;
                refresh = root.querySelector('[data-overview-refresh]');
                refresh.disabled = true;
                refresh.addEventListener('click', refreshOverview);
                root.setAttribute('data-snapshot', result.snapshot);
                root.setAttribute('data-refresh', result.stale ? '1' : '0');
                initPanels();
                setStatus(result.stale ? 'Sono presenti nuove modifiche. Premi “Aggiorna statistiche” per aggiornare nuovamente il riepilogo.' : 'Statistiche aggiornate.');
            }).catch(function (error) {
                setStatus(error.message || 'Aggiornamento non riuscito. Premi “Aggiorna statistiche” per riprovare.');
            }).finally(function () {
                refreshing = false;
                refresh.disabled = false;
            });
        }

        function initPanels() {
            ['visible', 'empty', 'extra', 'parent'].forEach(function (kind) {
                var toggle = content.querySelector('#dci-at-' + kind + '-toggle');
                var panel = content.querySelector('#dci-at-' + kind + '-sections');
                if (!toggle || !panel) {
                    return;
                }
                var close = panel.querySelector('#dci-at-' + kind + '-close');
                var label = toggle.querySelector('[data-empty-toggle-label]');
                var items = panel.querySelector('[data-overview-items]');
                var listStatus = panel.querySelector('[data-overview-list-status]');
                var pagination = panel.querySelector('[data-overview-pagination]');
                var pageLabel = panel.querySelector('[data-overview-page-label]');
                var previous = panel.querySelector('[data-overview-prev]');
                var next = panel.querySelector('[data-overview-next]');
                var retry = panel.querySelector('[data-overview-list-retry]');
                var currentPage = 0;
                var pages = 1;
                var loading = false;
                var requestedPage = 1;
                var panelVersion = version;

                function setOpen(open) {
                    panel.hidden = !open;
                    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                    label.textContent = open ? 'Nascondi elenco' : 'Mostra elenco';
                    if (open && !currentPage) {
                        loadPage(1);
                    }
                }

                function loadPage(page) {
                    if (loading || refreshing || panelVersion !== version) {
                        return;
                    }
                    loading = true;
                    requestedPage = page;
                    retry.hidden = true;
                    previous.disabled = true;
                    next.disabled = true;
                    items.setAttribute('aria-busy', 'true');
                    listStatus.textContent = 'Caricamento elenco…';
                    request('dci_at_overview_sections', {
                        kind: kind, page: String(page), snapshot: root.getAttribute('data-snapshot')
                    }).then(function (result) {
                        if (panelVersion !== version) {
                            return;
                        }
                        items.innerHTML = result.html;
                        items.scrollTop = 0;
                        currentPage = result.page;
                        pages = result.pages;
                        pagination.hidden = pages <= 1;
                        pageLabel.textContent = 'Pagina ' + currentPage + ' di ' + pages;
                        listStatus.textContent = result.total ? 'Pagina ' + currentPage + ' di ' + pages + ' · ' + result.total + ' voci complessive' : '';
                    }).catch(function (error) {
                        if (panelVersion !== version) {
                            return;
                        }
                        listStatus.textContent = error.message || 'Elenco non disponibile. Riprova.';
                        retry.hidden = false;
                    }).finally(function () {
                        loading = false;
                        items.setAttribute('aria-busy', 'false');
                        previous.disabled = currentPage <= 1;
                        next.disabled = currentPage >= pages;
                    });
                }

                toggle.addEventListener('click', function () { setOpen(panel.hidden); });
                close.addEventListener('click', function () { setOpen(false); toggle.focus(); });
                panel.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape') { setOpen(false); toggle.focus(); }
                });
                previous.addEventListener('click', function () { loadPage(currentPage - 1); });
                next.addEventListener('click', function () { loadPage(currentPage + 1); });
                retry.addEventListener('click', function () { loadPage(requestedPage); });
            });
        }

        if (!window.fetch || !window.URLSearchParams) {
            refresh.disabled = true;
            setStatus('Usa un browser aggiornato per consultare e aggiornare le statistiche.');
            return;
        }
        initPanels();
        refresh.addEventListener('click', refreshOverview);
        if (root.getAttribute('data-refresh') === '1') {
            refreshOverview();
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
}());
