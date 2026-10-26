<?php
/**
 * PSR — Protezione delle operazioni pubbliche del portale comunale.
 * Controlli minimali prima dei gestori legacy, senza disabilitare i servizi
 * cittadini. Non sostituisce un WAF né il rate limit a livello server.
 */
if (!defined('ABSPATH')) { exit; }

/** Per emergenze durante un aggiornamento: PSR_PUBLIC_REQUEST_GUARD_ENABLED=false in wp-config.php. */
function psr_public_guard_enabled() {
    return !defined('PSR_PUBLIC_REQUEST_GUARD_ENABLED') || (bool) PSR_PUBLIC_REQUEST_GUARD_ENABLED;
}

function psr_public_guard_limits() {
    return array(
        'save_rating' => array('max' => 60, 'fields' => array(
            'action'=>80, 'title'=>300, 'star'=>8, 'radioResponse'=>600,
            'freeText'=>6000, 'page'=>2000,
        )),
        'save_richiesta_assistenza' => array('max' => 25, 'fields' => array(
            'action'=>80, 'title'=>300, 'nome'=>160, 'cognome'=>160,
            'email'=>254, 'telefono'=>60, 'categoria_servizio'=>32,
            'servizio'=>500, 'dettagli'=>6000, 'privacyChecked'=>8,
        )),
        'save_appuntamento' => array('max' => 25, 'fields' => array(
            'action'=>80, 'name'=>160, 'surname'=>160, 'email'=>254,
            'phone'=>60, 'moreDetails'=>6000, 'service'=>4000,
            'office'=>4000, 'appointment'=>4000, 'place'=>4000,
        )),
        'save_segnalazione_disservizio' => array('max' => 25, 'fields' => array(
            'action'=>80, 'nonce'=>128, 'website'=>300, 'nome'=>160,
            'cognome'=>160, 'email'=>254, 'telefono'=>60, 'luogo_id'=>80,
            'luogo'=>500, 'riferimento_luogo'=>500, 'tipologia'=>500,
            'motivo'=>600, 'dettagli'=>8000, 'privacy'=>8,
        )),
    );
}
function psr_public_guard_deny($action, $reason, $status, $message) {
    if (function_exists('psr_center_log_limited')) {
        $ip = function_exists('psr_geo_public_ip') ? psr_geo_public_ip() : '';
        psr_center_log_limited('public_form_denied', 'warning', $ip, '', '',
            'public_form', substr($action . ': ' . $reason, 0, 180), 300);
    }
    if ((int)$status === 429 && !headers_sent()) { header('Retry-After: 600'); }
    wp_send_json_error(array('message' => $message), (int)$status);
}
/** Validazione tipo/limiti prima di qualsiasi insert o invio email. */
function psr_public_guard_valid_fields($data, $action, $limits) {
    if (!is_array($data) || count($data) > 35) { return false; }
    $fields = $limits[$action]['fields'];
    foreach ($data as $name => $value) {
        if (!is_string($name) || !is_string($value)) { return false; }
        // Campi di integrazione futuri consentiti con un tetto di dimensione.
        $max = isset($fields[$name]) ? $fields[$name] : 2048;
        if (strlen($value) > $max) { return false; }
    }
    if ($action === 'save_rating') {
        return !empty($data['title']) && (empty($data['star']) || $data['star'] === 'null'
            || (ctype_digit($data['star']) && (int)$data['star'] >= 1 && (int)$data['star'] <= 5));
    }
    if ($action === 'save_richiesta_assistenza' || $action === 'save_segnalazione_disservizio') {
        foreach (array('nome','cognome','email') as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') { return false; }
        }
        if (!is_email(wp_unslash($data['email']))) { return false; }
        if ($action === 'save_richiesta_assistenza') {
            return !empty($data['servizio']) && ($data['privacyChecked'] ?? '') === '1';
        }
        return true; // Non duplica validazione nonce, privacy, honeypot già presente.
    }
    if ($action === 'save_appuntamento') {
        foreach (array('name','surname','email','service','office','moreDetails','appointment') as $field) {
            if (!isset($data[$field])) { return false; }
        }
        if (trim($data['name']) === '' || trim($data['surname']) === ''
            || !is_email(wp_unslash($data['email']))) { return false; }
        foreach (array('appointment','service','office') as $field) {
            $json = json_decode(wp_unslash($data[$field]), true);
            if (!is_array($json)) { return false; }
            if ($field === 'appointment') {
                foreach (array('startDate','endDate') as $date_field) {
                    if (!isset($json[$date_field]) || !is_string($json[$date_field])
                        || !preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/D', $json[$date_field])) { return false; }
                }
            }
        }
    }
    return true;
}
/** Finestra fissa di 10 minuti, per azione e IP pubblico del server. */
function psr_public_guard_rate_allow($action, $maximum) {
    $ip = function_exists('psr_geo_public_ip') ? psr_geo_public_ip() : '';
    if ($ip === '') { return true; } // Non fidarsi di header X-Forwarded-For.
    $bucket = (int) floor(time() / 600);
    $key = 'psr_pf_' . substr(hash_hmac('sha256', $action . '|' . $ip . '|' . $bucket, wp_salt('auth')), 0, 36);
    $state = get_transient($key);
    $count = is_numeric($state) ? (int)$state : 0;
    if ($count >= $maximum) { return false; }
    // Best effort: il limite applicativo non è atomico su tutti i backend cache.
    set_transient($key, $count + 1, 1200);
    return true;
}
function psr_public_guard_ajax_init() {
    if (!psr_public_guard_enabled()) { return; }
    if (basename((string)($_SERVER['SCRIPT_NAME'] ?? '')) !== 'admin-ajax.php') { return; }
    $action = $_REQUEST['action'] ?? null;
    if (!is_string($action)) { return; }
    $limits = psr_public_guard_limits();
    if (!isset($limits[$action])) { return; }
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        psr_public_guard_deny($action, 'http_method', 405, 'Metodo non consentito.');
    }
    if ((int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 65536) {
        psr_public_guard_deny($action, 'body_too_large', 413, 'Richiesta troppo grande.');
    }
    if (!psr_public_guard_valid_fields($_POST, $action, $limits)) {
        psr_public_guard_deny($action, 'invalid_fields', 422, 'Controlla i dati inseriti e riprova.');
    }
    if (!psr_public_guard_rate_allow($action, $limits[$action]['max'])) {
        psr_public_guard_deny($action, 'rate_limited', 429, 'Troppi invii da questa connessione. Riprova fra qualche minuto.');
    }
}
add_action('init', 'psr_public_guard_ajax_init', -100);

/** Blocca solo parametri malformati sui servizi REST di consultazione. */
function psr_public_guard_rest($result, $server, $request) {
    if (!psr_public_guard_enabled() || is_wp_error($result) || !is_object($request)
        || !method_exists($request, 'get_route')) { return $result; }
    $route = trim($request->get_route(), '/');
    if (!in_array($route, array('wp/v2/sedi/ufficio', 'wp/v2/servizi/ufficio',
        'wp/v2/appuntamenti/ufficio'), true)) { return $result; }
    foreach (array('id','title','month','year') as $field) {
        $value = $request->get_param($field);
        if ($value !== null && (!is_scalar($value) || strlen((string)$value) > 200)) {
            return new WP_Error('psr_public_invalid_query','Parametri della richiesta non validi.',array('status'=>400));
        }
    }
    if ($route === 'wp/v2/appuntamenti/ufficio') {
        $year = $request->get_param('year');
        $current_year = (int) wp_date('Y');
        if ($year !== null && $year !== '' && (!ctype_digit((string)$year)
            || (int)$year < $current_year - 1 || (int)$year > $current_year + 3)) {
            return new WP_Error('psr_public_invalid_year', 'Anno non valido per le prenotazioni.', array('status'=>400));
        }
    }
    return $result;
}
add_filter('rest_pre_dispatch','psr_public_guard_rest',-50,3);
