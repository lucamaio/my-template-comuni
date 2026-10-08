<?php
/** Estrazione del solo modulo Siscom Bilanci, senza HTML o script remoti. */

/** L'allowlist riguarda il provider verificato, non uno specifico Comune. */
function dci_bilanci_source($url) {
    $url = dci_bilanci_validate_url($url);
    if ($url === '') { return false; }
    $parts = wp_parse_url($url);
    if (strtolower($parts['host']) !== 'www.servizipubblicaamministrazione.it'
        || !preg_match('~^/cms/pubblicazioni/Home/tabid/[0-9]+/Default\.aspx$~i', $parts['path'] ?? '')
        || isset($parts['fragment'])) { return false; }
    parse_str($parts['query'] ?? '', $query);
    if (array_diff(array_keys($query), array('Ente', 'Anno', 'Tipo'))
        || !isset($query['Ente']) || !is_string($query['Ente'])
        || trim($query['Ente']) === '' || strlen($query['Ente']) > 160
        || preg_match('~[\x00-\x1f\x7f<>/\\\\]~', $query['Ente'])) { return false; }
    $year = $query['Anno'] ?? '';
    $type = $query['Tipo'] ?? '';
    if (!dci_bilanci_valid_state($year, $type)) { return false; }
    return array(
        'base' => 'https://www.servizipubblicaamministrazione.it' . $parts['path'],
        'ente' => $query['Ente'], 'year' => $year, 'type' => $type,
    );
}

function dci_bilanci_valid_state($year, $type) {
    return is_string($year) && is_string($type)
        && ($year === '' || preg_match('/^[12][0-9]{3}$/D', $year))
        && ($type === '' || ($year !== '' && strlen($type) <= 120
            && !preg_match('/[\x00-\x1f\x7f<>]/', $type)));
}

function dci_bilanci_remote_url($source, $year, $type) {
    $query = array('Ente' => $source['ente']);
    if ($year !== '') { $query['Anno'] = $year; }
    if ($type !== '') { $query['Tipo'] = $type; }
    return $source['base'] . '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
}

/** Solo stati estratti dal provider ottengono una firma, mai un URL fornito dal browser. */
function dci_bilanci_signature($source, $year, $type) {
    return hash_hmac('sha256', dci_bilanci_remote_url($source, $year, $type), wp_salt('auth'));
}

function dci_bilanci_local_url($source, $year = '', $type = '') {
    $url = get_permalink(get_queried_object_id());
    if ($year === '') { return $url; }
    return add_query_arg(array(
        'bilanci_anno' => $year, 'bilanci_tipo' => $type,
        'bilanci_firma' => dci_bilanci_signature($source, $year, $type),
    ), $url);
}

function dci_bilanci_request_state($source) {
    if (!isset($_GET['bilanci_anno']) && !isset($_GET['bilanci_tipo']) && !isset($_GET['bilanci_firma'])) {
        return array('year' => $source['year'], 'type' => $source['type']);
    }
    $year = isset($_GET['bilanci_anno']) ? wp_unslash($_GET['bilanci_anno']) : '';
    $type = isset($_GET['bilanci_tipo']) ? wp_unslash($_GET['bilanci_tipo']) : '';
    $signature = $_GET['bilanci_firma'] ?? '';
    if (!dci_bilanci_valid_state($year, $type) || !is_string($signature)
        || !hash_equals(dci_bilanci_signature($source, $year, $type), $signature)) { return false; }
    return array('year' => $year, 'type' => $type);
}

/** Converte la risposta in record scalari; nessun markup remoto raggiunge il template. */
function dci_bilanci_parse($html, $source, $state) {
    if (!class_exists('DOMDocument') || !is_string($html) || strlen($html) > 524288
        || stripos($html, '<!ENTITY') !== false) { return new WP_Error('bilanci_markup'); }
    $previous = libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    try {
        $loaded = $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
    } finally {
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
    }
    if (!$loaded) { return new WP_Error('bilanci_markup'); }
    $xpath = new DOMXPath($dom);
    $modules = $xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " ModMODULISiscomBilanciTrasparenzaC ")]');
    if ($modules->length !== 1) { return new WP_Error('bilanci_structure'); }
    $module = $modules->item(0);
    $panels = $xpath->query('.//div[substring(@id, string-length(@id) - string-length("_SiscomBilanciTrasparenza_Panel1") + 1) = "_SiscomBilanciTrasparenza_Panel1"]', $module);
    if ($panels->length !== 1) { return new WP_Error('bilanci_structure'); }
    foreach ($xpath->query('.//script | .//style | .//iframe | .//object | .//embed', $module) as $unsafe) {
        $unsafe->parentNode->removeChild($unsafe);
    }
    $titles = $xpath->query('.//span[substring(@id, string-length(@id) - string-length("_SiscomBilanciTrasparenza_Label3") + 1) = "_SiscomBilanciTrasparenza_Label3"]', $module);
    $title = $titles->length ? trim($titles->item(0)->textContent) : 'Bilanci';
    $links = $xpath->query('.//a[contains(concat(" ", normalize-space(@class), " "), " SiscomTitolo ")]', $panels->item(0));
    if ($links->length > 500 || strlen($title) > 1000) { return new WP_Error('bilanci_size'); }
    if (!$links->length && (trim($panels->item(0)->textContent) !== '' || $xpath->query('.//a', $panels->item(0))->length)) {
        return new WP_Error('bilanci_structure');
    }
    $rows = array();
    foreach ($links as $link) {
        $label = trim($link->textContent);
        // Il provider usa anche spazi non codificati negli href degli anni/tipi.
        $url = dci_bilanci_validate_url(str_replace(' ', '%20', trim($link->getAttribute('href'))));
        if ($url === '' || $label === '' || strlen($label) > 1000) { continue; }
        $target = dci_bilanci_source($url);
        if ($target && $target['base'] === $source['base'] && $target['ente'] === $source['ente']) {
            $next_year = $state['year'] === '' && $target['year'] !== '' && $target['type'] === '';
            $next_type = $state['year'] !== '' && $state['type'] === ''
                && $target['year'] === $state['year'] && $target['type'] !== '';
            if ($next_year || $next_type) {
                $rows[] = array('label' => $label, 'kind' => 'section', 'year' => $target['year'], 'type' => $target['type']);
            }
            continue;
        }
        $parts = wp_parse_url($url);
        $decoded_path = rawurldecode($parts['path'] ?? '');
        $segments = explode('/', $decoded_path);
        $extension = strtolower(pathinfo($decoded_path, PATHINFO_EXTENSION));
        if (strtolower($parts['host']) === 'www.servizipubblicaamministrazione.it'
            && !isset($parts['query']) && !isset($parts['fragment'])
            && count($segments) === 6 && $segments[1] === 'bilanci'
            && $segments[2] === $source['ente'] && $segments[3] === $state['year']
            && $segments[4] === $state['type'] && $state['type'] !== ''
            && !preg_match('/[\x00-\x1f\x7f%\\\\]/', $decoded_path)
            && !in_array('..', $segments, true) && !in_array('.', $segments, true)
            && in_array($extension, array('pdf', 'xls', 'xlsx', 'csv', 'ods', 'doc', 'docx', 'odt', 'zip', 'p7m'), true)) {
            $rows[] = array('label' => $label, 'kind' => 'document', 'url' => $url, 'format' => strtoupper($extension));
        }
    }
    if ($links->length && !$rows) { return new WP_Error('bilanci_links'); }
    return array('title' => $title, 'rows' => $rows);
}

/** Cache 15 minuti, ultima copia valida 24 ore, retry errori dopo 90 secondi. */
function dci_bilanci_fetch($source, $state) {
    $url = dci_bilanci_remote_url($source, $state['year'], $state['type']);
    $key = 'dci_bilanci_v1_' . md5($url);
    $cache = get_transient($key);
    $stale = is_array($cache) && isset($cache['data'], $cache['time']);
    if ($stale && $cache['time'] > time() - 900) {
        return array('data' => $cache['data'], 'stale' => false);
    }
    $fallback = $stale ? array('data' => $cache['data'], 'stale' => true) : false;
    if (get_transient($key . '_retry')) { return $fallback; }
    // Lock atomico tramite API WP: evita richieste concorrenti sulla stessa risorsa.
    $lock = $key . '_lock';
    $expires = get_option($lock);
    if ($expires && (int) $expires < time()) { delete_option($lock); }
    if (!add_option($lock, time() + 15, '', false)) { return $fallback; }
    try {
        $response = wp_safe_remote_get($url, array(
            'timeout' => 3, 'redirection' => 0, 'limit_response_size' => 524288,
            'sslverify' => true, 'cookies' => array(),
            'headers' => array('Accept' => 'text/html'),
        ));
        $data = false;
        $content_type = is_wp_error($response) ? '' : wp_remote_retrieve_header($response, 'content-type');
        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200
            && is_string($content_type) && stripos($content_type, 'text/html') === 0) {
            $body = wp_remote_retrieve_body($response);
            if (strlen($body) < 524288) { $data = dci_bilanci_parse($body, $source, $state); }
        }
        if (is_array($data)) {
            set_transient($key, array('data' => $data, 'time' => time()), 86400);
            delete_transient($key . '_retry');
            return array('data' => $data, 'stale' => false);
        }
        set_transient($key . '_retry', 1, 90);
        return $fallback;
    } finally {
        delete_option($lock);
    }
}

/** Unico ingresso dal template: mai fetch da altre pagine o da parametri URL arbitrari. */
function dci_bilanci_view() {
    if (!is_page_template('page-templates/bilanci.php') || 'true' !== dci_get_option('ck_abilita_trasparenza')) { return false; }
    $source = dci_bilanci_source(dci_get_option('url_bilanci', 'trasparenza'));
    if (!$source) { return false; }
    $state = dci_bilanci_request_state($source);
    if (!$state) { return false; }
    static $results = array();
    $key = dci_bilanci_remote_url($source, $state['year'], $state['type']);
    if (!array_key_exists($key, $results)) { $results[$key] = dci_bilanci_fetch($source, $state); }
    if (!$results[$key]) { return false; }
    return array_merge($results[$key], array('source' => $source, 'state' => $state));
}
