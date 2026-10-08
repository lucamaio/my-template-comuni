(function () {
    'use strict';

    var content = document.getElementById('bilanci-content');
    var breadcrumb = document.getElementById('bilanci-breadcrumb');
    var status = document.getElementById('bilanci-navigation-status');
    if (!content || !breadcrumb || !status || !window.fetch || !window.AbortController
        || !window.DOMParser || !window.history.pushState) {
        return;
    }

    // Sono ammessi soltanto gli stati della stessa pagina, anche con permalink semplici.
    function pageKey(url) {
        var key = new URL(url.href);
        ['bilanci_anno', 'bilanci_tipo', 'bilanci_firma'].forEach(function (name) {
            key.searchParams.delete(name);
        });
        key.hash = '';
        key.searchParams.sort();
        return key.href;
    }

    var baseKey = pageKey(new URL(window.location.href));
    var renderedUrl = window.location.href;
    var pending = null;
    var requestId = 0;

    function isLocalState(url) {
        return url.origin === window.location.origin && pageKey(url) === baseKey;
    }

    function navigate(url, fromHistory) {
        var id = ++requestId;
        if (pending) { pending.abort(); }
        var controller = new AbortController();
        pending = controller;
        var timeout = window.setTimeout(function () { controller.abort(); }, 12000);
        content.setAttribute('aria-busy', 'true');
        status.dataset.state = 'loading';
        status.textContent = status.dataset.loading;

        // La risposta passa dal normale template WP: firme, permessi e cache restano gli stessi.
        window.fetch(url.href, {
            signal: controller.signal,
            credentials: 'same-origin',
            cache: 'no-store'
        }).then(function (response) {
            if (!response.ok || !isLocalState(new URL(response.url))
                || !(response.headers.get('content-type') || '').includes('text/html')) {
                throw new Error('Invalid response');
            }
            return response.text();
        }).then(function (html) {
            if (id !== requestId) { return; }
            var next = new DOMParser().parseFromString(html, 'text/html');
            var nextContent = next.getElementById('bilanci-content');
            var nextBreadcrumb = next.getElementById('bilanci-breadcrumb');
            if (!nextContent || !nextBreadcrumb || nextContent.dataset.bilanciReady !== 'true') {
                throw new Error('Content unavailable');
            }
            // Non importare script dalla pagina completa: header e menu restano intatti.
            [nextContent, nextBreadcrumb].forEach(function (fragment) {
                fragment.querySelectorAll('script').forEach(function (script) { script.remove(); });
            });
            if (!fromHistory && url.href !== renderedUrl) {
                window.history.pushState(null, '', url.href);
            }
            content.replaceWith(nextContent);
            breadcrumb.replaceWith(nextBreadcrumb);
            content = nextContent;
            breadcrumb = nextBreadcrumb;
            renderedUrl = url.href;
            if (next.title) { document.title = next.title; }
            status.dataset.state = 'loaded';
            status.textContent = status.dataset.loaded;
            var heading = content.querySelector('#bilanci-service-title');
            if (heading) { heading.focus({ preventScroll: true }); }
            // Mantieni la posizione, salvo quando il contenuto sarebbe fuori schermo.
            var bounds = content.getBoundingClientRect();
            if (bounds.bottom < 0 || bounds.top >= window.innerHeight) {
                content.scrollIntoView({ block: 'start' });
            }
        }).catch(function () {
            // Richieste superate da un altro clic non devono avviare navigazioni obsolete.
            if (id !== requestId) { return; }
            if (fromHistory) {
                window.location.reload();
            } else {
                window.location.assign(url.href);
            }
        }).finally(function () {
            window.clearTimeout(timeout);
            if (id === requestId) {
                pending = null;
                content.removeAttribute('aria-busy');
            }
        });
    }

    document.addEventListener('click', function (event) {
        if (event.defaultPrevented || event.button !== 0 || event.ctrlKey || event.metaKey
            || event.shiftKey || event.altKey) { return; }
        var link = event.target.closest('a');
        if (!link || link.hasAttribute('download') || (link.target && link.target !== '_self')) { return; }
        if (!(content.contains(link) && link.hasAttribute('data-bilanci-nav'))
            && !breadcrumb.contains(link)) { return; }
        var url = new URL(link.href, window.location.href);
        if (url.hash || !isLocalState(url)) { return; }
        event.preventDefault();
        navigate(url, false);
    });

    window.addEventListener('popstate', function () {
        var url = new URL(window.location.href);
        if (!isLocalState(url)) { window.location.reload(); return; }
        if (url.href === renderedUrl && !pending) { return; }
        navigate(url, true);
    });
}());
