<?php
/** Confronto locale degli URL: nessuna richiesta HTTP alla destinazione. */
function dci_at_link_is_internal_page($link, $page_url, $home_url, $page_id) {
    $link = trim((string) $link);
    if ($link === '' || $link[0] === '#' || $link[0] === '?') {
        return true;
    }
    $target = wp_parse_url($link);
    $home = wp_parse_url($home_url);
    $page = wp_parse_url($page_url);
    if ($target === false || $home === false || $page === false) {
        return false;
    }
    // Un portale esterno può avere lo stesso percorso: confrontare anche l'host.
    if (isset($target['host']) && strcasecmp($target['host'], $home['host'] ?? '') !== 0) {
        return false;
    }
    $normalize = static function ($path) {
        $segments = array();
        foreach (explode('/', rawurldecode((string) $path)) as $segment) {
            if ($segment === '' || $segment === '.') continue;
            if ($segment === '..') { array_pop($segments); continue; }
            $segments[] = $segment;
        }
        return '/' . implode('/', $segments);
    };
    $path = $target['path'] ?? '';
    if ($path !== '' && $path[0] !== '/') {
        $path = trailingslashit(dirname($page['path'] ?? '/')) . $path;
    }
    $path = $normalize($path);
    $home_path = $normalize($home['path'] ?? '/');
    $known_paths = array(
        $normalize($page['path'] ?? '/'),
        $normalize($home_path . '/amministrazione-trasparente/'),
        $normalize($home_path . '/index.php/amministrazione-trasparente/'),
    );
    if (in_array($path, $known_paths, true)) return true;

    // Supporto ai permalink semplici, anche con parametri aggiuntivi.
    $query = array();
    parse_str($target['query'] ?? '', $query);
    return in_array($path, array($home_path, $normalize($home_path . '/index.php')), true)
        && isset($query['page_id']) && is_scalar($query['page_id'])
        && (int) $query['page_id'] === (int) $page_id;
}
