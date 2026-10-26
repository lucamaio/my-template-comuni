<?php
/**
 * PSR Geo Login — blocca il login e il pannello WordPress quando il server
 * non riconosce l'IP pubblico come italiano (tutti i ruoli).
 * Caricato dal tema. Frontend: solo ban IP già confermati, mai geoblocking dei visitatori.
 *
 * Requisiti: WordPress con API HTTP e transients; PHP 7.4+.
 * IP: soltanto REMOTE_ADDR (mai intestazioni proxy non autenticate).
 * Servizio di geolocalizzazione: https://api.country.is/{ip}.
 * GeoIP non disponibile/non conclusivo: nega le sole aree protette.
 * Il recupero del gestore resta possibile da Plesk/WP-CLI, mai da header HTTP.
 */
if (!defined('ABSPATH')) { exit; }

/**
 * XML-RPC completamente disabilitato dal tema, per qualsiasi IP.
 * L'endpoint xmlrpc.php definisce XMLRPC_REQUEST prima di caricare WordPress:
 * il controllo avviene all'hook init, prima dell'elaborazione dei metodi XML-RPC.
 * Per un'eccezione temporanea: define('PSR_XMLRPC_DISABLED', false); in wp-config.php.
 * Non modifica firewall Plesk/Nginx e non blocca richieste statiche prima di PHP.
 */
function psr_geo_xmlrpc_hard_disable() {
    if (!defined('XMLRPC_REQUEST') || !XMLRPC_REQUEST) { return; }
    if (defined('PSR_XMLRPC_DISABLED') && !PSR_XMLRPC_DISABLED) { return; }

    // Un solo evento per IP/minuto: protegge il registro da scansioni ripetute.
    if (function_exists('psr_center_log_limited')) {
        $ip = psr_geo_public_ip();
        psr_center_log_limited('xmlrpc_denied', 'warning', $ip, '', '', 'xmlrpc',
            'Richiesta XML-RPC respinta (endpoint disabilitato)', 60);
    }
    if (function_exists('nocache_headers')) { nocache_headers(); }
    if (function_exists('status_header')) { status_header(403); }
    else { http_response_code(403); }
    if (!headers_sent()) {
        header('Content-Type: text/plain; charset=UTF-8');
        header('X-Robots-Tag: noindex, nofollow');
    }
    exit('XML-RPC disabilitato.');
}
if (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST) {
    add_action('init', 'psr_geo_xmlrpc_hard_disable', -1000);
}

function psr_geo_enabled() {
    return !defined('PSR_GEO_LOGIN_ENABLED') || (bool) PSR_GEO_LOGIN_ENABLED;
}
/** Blacklist globale cumulativa: applicata a login e amministrazione di ogni Comune.
 * L'IP confermato qui non puo' essere sbloccato dall'interfaccia.
 * E' possibile aggiungere altri indirizzi via PSR_GEO_EXTRA_DENY_IPS in wp-config.php.
 */
function psr_geo_global_deny_ips() {
    $list = array('45.148.232.225');
    if (defined('PSR_GEO_EXTRA_DENY_IPS')) {
        $extra = PSR_GEO_EXTRA_DENY_IPS;
        if (is_string($extra)) { $extra = explode(',', $extra); }
        if (is_array($extra)) { $list = array_merge($list, $extra); }
    }
    $valid = array();
    foreach ($list as $candidate) {
        $candidate = trim((string) $candidate);
        if (filter_var($candidate, FILTER_VALIDATE_IP)) { $valid[$candidate] = true; }
    }
    return array_keys($valid);
}
function psr_geo_ip_blacklisted($ip) {
    if (!filter_var($ip, FILTER_VALIDATE_IP)) { return false; }
    $packed = inet_pton($ip);
    foreach (psr_geo_global_deny_ips() as $candidate) {
        if ($packed === inet_pton($candidate)) { return true; }
    }
    return false;
}
function psr_geo_public_ip() {
    $ip = isset($_SERVER['REMOTE_ADDR']) ? trim((string) $_SERVER['REMOTE_ADDR']) : '';
    // Compatibilita' con IPv4 mappati in IPv6, senza fidarsi di header HTTP.
    if (stripos($ip, '::ffff:') === 0) {
        $v4 = substr($ip, 7);
        if (filter_var($v4, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) { $ip = $v4; }
    }
    return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) ? $ip : '';
}
/** Hash irreversibile dell'IP fiduciario: non si salva l'indirizzo in chiaro.
 * La rotazione delle chiavi WordPress invalida le impronte (fail closed).
 */
function psr_geo_trusted_digest($ip) {
    if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) { return ''; }
    $packed = @inet_pton($ip);
    return $packed === false ? '' : hash_hmac('sha256', 'psr-trusted-v1|' . bin2hex($packed), wp_salt('auth'));
}
function psr_geo_trusted_entries() {
    $entries = get_option('_psr_geo_trusted_ip_hashes', array());
    return is_array($entries) ? $entries : array();
}
function psr_geo_trusted_match($ip) {
    $digest = psr_geo_trusted_digest($ip);
    if ($digest === '' || psr_geo_ip_blacklisted($ip)) { return false; }
    $entries = psr_geo_trusted_entries();
    foreach ($entries as $candidate => $item) {
        if (is_string($candidate) && strlen($candidate) === 64 && hash_equals($candidate, $digest)) { return true; }
    }
    return false;
}
function psr_geo_ip_allowlisted($ip) {
    if ($ip === '') { return false; }
    if (psr_geo_trusted_match($ip)) { return true; }
    if (!defined('PSR_GEO_ALLOW_IPS')) { return false; }
    $list = PSR_GEO_ALLOW_IPS;
    if (is_string($list)) { $list = explode(',', $list); }
    if (!is_array($list)) { return false; }
    foreach ($list as $item) {
        $item = trim((string) $item);
        if ($item !== '' && filter_var($item, FILTER_VALIDATE_IP) && inet_pton($item) === inet_pton($ip)) {
            return true;
        }
    }
    return false;
}
function psr_geo_cache_key($ip) {
    // Il database non contiene l'IP in chiaro per questo lookup.
    return 'psr_geo_v1_' . substr(hash_hmac('sha256', $ip, wp_salt('auth')), 0, 40);
}
function psr_geo_country($ip) {
    if ($ip === '') { return array('country'=>'', 'source'=>'no_public_ip'); }
    // Un IP fidato non prova il Paese: nessuna whitelist aggira la politica Italia.
    $key = psr_geo_cache_key($ip);
    $cached = get_transient($key);
    if (is_array($cached) && isset($cached['country'], $cached['source'])) { return $cached; }

    $country = '';
    $source = 'geoip_unavailable';
    // API pubblica; interrogata solo per i tentativi di accesso al backend.
    // Timeout limitato; nessuna richiesta dal frontend pubblico.
    if (function_exists('wp_remote_get')) {
        try {
            $response = wp_remote_get('https://api.country.is/' . rawurlencode($ip), array(
                'timeout'=>2, 'redirection'=>0, 'sslverify'=>true, 'limit_response_size'=>1024,
                'reject_unsafe_urls'=>true, 'headers'=>array('Accept'=>'application/json'),
                'user-agent'=>'PSR-GeoLogin/1.0',
            ));
        } catch (Throwable $e) {
            // Il sito pubblico resta disponibile; le aree protette richiedono IT.
            $response = new WP_Error('psr_geo_http_failed', 'GeoIP non disponibile');
        }
        if (!is_wp_error($response) && (int) wp_remote_retrieve_response_code($response) === 200) {
            $body = wp_remote_retrieve_body($response);
            if (is_string($body) && strlen($body) < 1024) {
                $data = json_decode($body, true);
                if (is_array($data) && isset($data['country'], $data['ip']) && is_string($data['country']) && is_string($data['ip'])
                    && filter_var($data['ip'], FILTER_VALIDATE_IP) && inet_pton($data['ip']) === inet_pton($ip)) {
                    $value = strtoupper($data['country']);
                    if (preg_match('/^[A-Z]{2}$/D', $value) && !in_array($value, array('XX','ZZ'), true)) {
                        $country = $value;
                        $source = 'country.is';
                    }
                }
            }
        }
    }
    $result = array('country'=>$country, 'source'=>$source);
    // 24h per classificazioni confermate, 5 minuti se verifica non possibile.
    set_transient($key, $result, $country !== '' ? DAY_IN_SECONDS : 5 * MINUTE_IN_SECONDS);
    return $result;
}
function psr_geo_decision() {
    if (!empty($GLOBALS['psr_geo_request_decision'])) { return $GLOBALS['psr_geo_request_decision']; }
    $out = array('blocked'=>false, 'reason'=>'disabled', 'country'=>'', 'ip'=>'');
    if ((defined('WP_CLI') && WP_CLI) || (defined('DOING_CRON') && DOING_CRON)) {
        return $GLOBALS['psr_geo_request_decision'] = $out;
    }
    $ip = psr_geo_public_ip();
    $out['ip'] = $ip;
    // Questo blocco ha priorita' su allowlist, GeoIP, cache e disattivazione del filtro paese.
    if (psr_geo_ip_blacklisted($ip)) {
        $out['blocked'] = true;
        $out['reason'] = 'global_blacklist';
        return $GLOBALS['psr_geo_request_decision'] = $out;
    }
    if (!psr_geo_enabled()) {
        return $GLOBALS['psr_geo_request_decision'] = $out;
    }
    if ($ip === '') {
        $out['reason'] = 'ip_not_public_or_proxy';
        $out['blocked'] = true;
        return $GLOBALS['psr_geo_request_decision'] = $out;
    }
    $geo = psr_geo_country($ip);
    $out['country'] = $geo['country'];
    $out['reason'] = $geo['source'];
    $out['blocked'] = $geo['country'] !== 'IT';
    return $GLOBALS['psr_geo_request_decision'] = $out;
}
function psr_geo_deny_event($decision) {
    if (isset($decision['reason']) && $decision['reason'] === 'global_blacklist') { return 'ip_blacklist_denied'; }
    return empty($decision['country']) ? 'geo_unavailable' : 'geo_denied';
}
/** Registra SOLO tentativi effettivi di autenticazione su POST, mai visite GET.
 * Un IP estero viene respinto prima di verificare le credenziali, quindi
 * si conta come tentativo di autenticazione, non come password errata.
 * I blocchi temporanei seguono gli stessi livelli 5/10/15/25 di PSR Login.
 */
function psr_geo_auth_probe($source = 'login', $username = '') {
    if (!empty($GLOBALS['psr_geo_probe_counted']) || strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'POST') { return; }
    // Una verifica GeoIP fallita non dimostra un tentativo estero: niente nuovi ban.
    if (empty(psr_geo_decision()['country'])) { return; }
    $ip = psr_geo_public_ip();
    if ($ip === '' || !function_exists('psr_login_rate_failure')) { return; }
    if (function_exists('psr_login_rate_blocked') && psr_login_rate_blocked($ip) > 0) { return; }
    $GLOBALS['psr_geo_probe_counted'] = true;
    psr_login_rate_failure($ip, 'auth_probe', (string)$username, (string)$source);
}
function psr_geo_deny($source = 'login', $note = 'Accesso amministrativo negato', $username = '') {
    if (function_exists('psr_center_log_limited')) {
        $d = psr_geo_decision();
        psr_center_log_limited(psr_geo_deny_event($d),'critical',(string)$d['ip'],
            (string)$username,(string)$d['country'],(string)$source,(string)$note,60);
    }
    if (function_exists('nocache_headers')) { nocache_headers(); }
    if (function_exists('status_header')) { status_header(403); }
    else { http_response_code(403); }
    if (!defined('DONOTCACHEPAGE')) { define('DONOTCACHEPAGE', true); }
    wp_die('Accesso amministrativo non consentito da questo indirizzo IP. Contatta il gestore del portale.',
        'PSR - Accesso riservato', array('response'=>403));
}
/** Blocchi per tentativi ripetuti: login non mostra neppure il modulo. */
function psr_geo_deny_rate_if_blocked($source = 'login') {
    $ip = psr_geo_public_ip();
    if ($ip === '' || !function_exists('psr_login_rate_blocked') || psr_login_rate_blocked($ip) <= 0) { return; }
    if (function_exists('psr_center_log_limited')) {
        psr_center_log_limited('login_blocked','warning',$ip,'','',(string)$source,
            'Interfaccia di accesso negata: IP sotto blocco progressivo',60);
    }
    if (function_exists('nocache_headers')) { nocache_headers(); }
    if (function_exists('status_header')) { status_header(403); }
    else { http_response_code(403); }
    wp_die('Troppi tentativi di accesso. Riprova piu tardi.', 'PSR - Accesso temporaneamente bloccato', array('response'=>403));
}
/** Ban di sito pubblico solo per IP esplicitamente bloccati, MAI per semplici IP esteri.
 * Una lettura transient per richiesta WordPress, nessun servizio GeoIP/scan/crawl.
 * Nginx/Apache deve coprire file statici e PHP indipendenti da WordPress.
 * Recupero via wp-config.php: define('PSR_SITE_BAN_ENABLED', false);
 */
function psr_geo_site_ban_enabled() {
    if (defined('PSR_SITE_BAN_ENABLED')) { return (bool) PSR_SITE_BAN_ENABLED; }
    return get_option('_psr_site_ban_enabled', '1') === '1';
}
function psr_geo_site_ban_reason($ip) {
    if ($ip === '') { return ''; }
    if (psr_geo_ip_blacklisted($ip)) { return 'global_blacklist'; }
    if (function_exists('psr_login_rate_state')) {
        $state = psr_login_rate_state($ip);
        // 5/10 errori: lockout solo login. 15+ errori oppure ban manuale: sito WordPress.
        if ((int)($state['until'] ?? 0) > time() &&
            ((int)($state['n'] ?? 0) >= 15 || !empty($state['site_ban']))) { return 'rate_ban'; }
    }
    return '';
}
function psr_geo_site_ban_gate() {
    if (!psr_geo_site_ban_enabled() || (defined('WP_CLI') && WP_CLI)
        || (defined('DOING_CRON') && DOING_CRON)) { return; }
    $ip = psr_geo_public_ip();
    $reason = psr_geo_site_ban_reason($ip);
    if ($reason === '') { return; }
    if (function_exists('psr_center_log_limited')) {
        psr_center_log_limited('site_ban_denied','critical',$ip,'','','frontend',
            $reason === 'global_blacklist' ? 'Richiesta WordPress: blacklist permanente' : 'Richiesta WordPress: ban temporaneo', 300);
    }
    nocache_headers();
    if (function_exists('status_header')) { status_header(403); }
    if (!defined('DONOTCACHEPAGE')) { define('DONOTCACHEPAGE', true); }
    wp_die('Accesso non disponibile da questo indirizzo IP.', 'Accesso non consentito', array('response'=>403));
}
add_action('init', 'psr_geo_site_ban_gate', 0);

// Login standard, recupero password e OTP: nessun modulo per gli IP esteri.
function psr_geo_login_init() {
    $d = psr_geo_decision();
    if (!empty($d['blocked'])) {
        // Contiamo soltanto POST del modulo di accesso, non i GET o i reset password.
        $action = isset($_REQUEST['action']) ? sanitize_key((string)wp_unslash($_REQUEST['action'])) : 'login';
        if ($action === '' || $action === 'login') {
            $username = isset($_POST['log']) && is_string($_POST['log']) ? (string)wp_unslash($_POST['log']) : '';
            psr_geo_auth_probe('login', $username);
        }
        psr_geo_deny('login', 'Login / OTP / recupero password respinto');
    }
    psr_geo_deny_rate_if_blocked('login');
}
add_action('login_init', 'psr_geo_login_init', 0);

// Blocca anche i percorsi che invocano wp_authenticate senza usare wp-login.php.
function psr_geo_authenticate($user, $username, $password) {
    if (psr_geo_decision()['blocked']) {
        // Copre anche i login personalizzati che chiamano wp_authenticate.
        psr_geo_auth_probe('authenticate', (string)$username);
        if (function_exists('psr_center_log_limited')) {
            $d = psr_geo_decision();
            psr_center_log_limited(psr_geo_deny_event($d),'critical',(string)$d['ip'],(string)$username,(string)$d['country'],'authenticate','Tentativo login respinto',60);
        }
        return new WP_Error('psr_geo_foreign_ip', 'Accesso amministrativo non consentito da questo indirizzo IP.');
    }
    return $user;
}
add_filter('authenticate', 'psr_geo_authenticate', 0, 3);

/** Identifica solo le azioni AJAX/POST chiaramente legate al login.
 * NON bloccare admin-ajax.php o admin-post.php nel loro insieme: appuntamenti,
 * assistenza, moduli e servizi pubblici del Comune potrebbero dipenderne.
 * Gli endpoint inventati da plugin terzi vanno censiti e protetti a parte.
 */
function psr_geo_sensitive_ajax_action($action) {
    if (!is_string($action)) { return false; }
    $action = strtolower(trim($action));
    return in_array($action, array(
        'login','ajaxlogin','ajax_login','wp_login','wp_ajax_login','user_login',
        'login_user','login_ajax','login_with_ajax','user_authenticate',
        'authenticate','do_login','custom_login','woocommerce_ajax_login',
        'jwt_login','jwt_auth','token_login','get_auth_token',
        'lostpassword','lost_password','reset_password','resetpass'
    ), true);
}
function psr_geo_admin_init() {
    $script = strtolower(basename((string)($_SERVER['SCRIPT_NAME'] ?? '')));
    $is_public_dispatcher = in_array($script, array('admin-ajax.php','admin-post.php'), true);
    $authenticated = is_user_logged_in();
    $sensitive = $is_public_dispatcher && psr_geo_sensitive_ajax_action(
        isset($_REQUEST['action']) && is_string($_REQUEST['action']) ? (string)wp_unslash($_REQUEST['action']) : ''
    );
    if ($is_public_dispatcher && !$authenticated && !$sensitive) { return; }
    $d = psr_geo_decision();
    if (!empty($d['blocked'])) {
        if ($sensitive) { psr_geo_auth_probe('admin_ajax', ''); }
        psr_geo_deny($is_public_dispatcher ? 'admin_ajax' : 'admin',
            $is_public_dispatcher ? 'Endpoint autenticazione AJAX/POST respinto' : 'Pannello amministrativo respinto');
    }
    if ($sensitive) { psr_geo_deny_rate_if_blocked('admin_ajax'); }
}
add_action('admin_init', 'psr_geo_admin_init', 0);

/** Prima di auth_redirect(): nega anche wp-admin anonimo senza mostrare il login. */
function psr_geo_early_admin_gate() {
    if (is_admin()) { psr_geo_admin_init(); }
}
add_action('init', 'psr_geo_early_admin_gate', 2);

/** REST pubblica disponibile ai cittadini, solo token/login/API account riservate.
 * wp-json/wp/v2/pages o pubblici GET non devono essere bloccati per Paese.
 */
function psr_geo_rest_auth_route($route, $method) {
    $route = strtolower('/' . ltrim((string)$route, '/'));
    $route = preg_replace('~/+~', '/', $route);
    $method = strtoupper((string)$method);
    if ($method === 'OPTIONS') { return false; }
    // I token/login restano protetti anche se un plugin li espone tramite GET.
    if (preg_match('~^/(?:jwt-auth/v1/token(?:/|$)|simple-jwt-login/v1/auth(?:/|$)|wp-oauth/v1/token(?:/|$))~', $route)) {
        return true;
    }
    if ($method !== 'GET' && $method !== 'HEAD' && preg_match('~^/wp/v2/users(?:/|$)~', $route)) { return true; }
    // Non bloccare API token di servizi pubblici o endpoint di iscrizione
    // che non siano inequivocabilmente sistemi di autenticazione.
    return (bool)preg_match('~/(?:login|authenticate|authentication|auth/token|oauth/token|sign-in|signin)(?:/|$)~', $route);
}
function psr_geo_rest_sensitive_gate($result, $server, $request) {
    if (is_wp_error($result) || !is_object($request) || !method_exists($request, 'get_route') || !method_exists($request, 'get_method')) { return $result; }
    $route = $request->get_route();
    $method = $request->get_method();
    if (!psr_geo_rest_auth_route($route, $method)) { return $result; }
    $d = psr_geo_decision();
    if (!empty($d['blocked'])) {
        psr_geo_auth_probe('rest_auth');
        if (function_exists('psr_center_log_limited')) {
            psr_center_log_limited(psr_geo_deny_event($d), 'critical',(string)$d['ip'], '',
                (string)$d['country'],'rest_auth','REST auth respinta: ' . substr((string)$route, 0, 125),60);
        }
        return new WP_Error('psr_geo_rest_auth_denied', 'Operazione di autenticazione non consentita da questo IP.', array('status'=>403));
    }
    $ip = psr_geo_public_ip();
    if ($ip !== '' && function_exists('psr_login_rate_blocked') && psr_login_rate_blocked($ip) > 0) {
        if (function_exists('psr_center_log_limited')) {
            psr_center_log_limited('login_blocked','warning',$ip,'','','rest_auth',
                'REST autenticazione negata: IP già bloccato',60);
        }
        return new WP_Error('psr_login_rest_locked','Accesso temporaneamente bloccato.',array('status'=>403));
    }
    return $result;
}
add_filter('rest_pre_dispatch','psr_geo_rest_sensitive_gate',0,3);

// I client REST gia' autenticati di qualsiasi ruolo non aggirano il gate.
function psr_geo_rest_authentication($result) {
    if (is_wp_error($result)) { return $result; }
    if (is_user_logged_in() && psr_geo_decision()['blocked']) {
        if (function_exists('psr_center_log_limited')) {
            $d = psr_geo_decision();
            psr_center_log_limited(psr_geo_deny_event($d),'critical',(string)$d['ip'],'',(string)$d['country'],'rest','API REST autenticata respinta',60);
        }
        return new WP_Error('psr_geo_foreign_ip', 'Accesso amministrativo non consentito da questo indirizzo IP.', array('status'=>403));
    }
    return $result;
}
add_filter('rest_authentication_errors', 'psr_geo_rest_authentication', 100);

// XML-RPC rimane disabilitato per tutti con psr_geo_xmlrpc_hard_disable().
// Il filtro aggiuntivo conserva la protezione se si applica una deroga esplicita.
function psr_geo_xmlrpc_enabled($enabled) {
    if (psr_geo_decision()['blocked']) {
        if (function_exists('psr_center_log_limited')) {
            $d=psr_geo_decision();
            psr_center_log_limited(psr_geo_deny_event($d),'critical',(string)$d['ip'],'',(string)$d['country'],'xmlrpc','XML-RPC estero respinto',60);
        }
        return false;
    }
    return $enabled;
}
add_filter('xmlrpc_enabled', 'psr_geo_xmlrpc_enabled', 0);

// Avviso privato, solo ID 1, se IP non affidabile o GeoIP non disponibile.
function psr_geo_admin_notice() {
    if (!psr_geo_enabled() || (int) get_current_user_id() !== 1 || !current_user_can('manage_options')) { return; }
    $d = psr_geo_decision();
    if ($d['reason'] !== 'geoip_unavailable' && $d['reason'] !== 'ip_not_public_or_proxy') { return; }
    echo '<div class="notice notice-warning"><p><strong>PSR Geo Login:</strong> impossibile determinare in modo affidabile il Paese dell\'IP (' . esc_html($d['reason']) . '). Le aree protette richiedono un IP italiano verificato. Controlla GeoIP e REMOTE_ADDR in Plesk.</p></div>';
}
add_action('admin_notices', 'psr_geo_admin_notice', 2);

function psr_geo_tools_menu() {
    if ((int) get_current_user_id() !== 1 || !current_user_can('manage_options')) { return; }
    add_management_page('PSR Geo Login', 'PSR Geo Login', 'manage_options', 'psr-geo-login', 'psr_geo_tools_page');
}
add_action('admin_menu', 'psr_geo_tools_menu');
function psr_geo_tools_page() {
    if ((int) get_current_user_id() !== 1 || !current_user_can('manage_options')) {
        wp_die('Non autorizzato', '', array('response'=>403));
    }
    if (function_exists('psr_center_page')) { psr_center_page('geo'); return; }
    $d = psr_geo_decision();
    echo '<div class="wrap"><h1>PSR Geo Login — riservato ID 1</h1>';
    echo '<p>Stato filtro: <strong>' . (psr_geo_enabled() ? 'Attivo' : 'Disattivato da wp-config.php') . '</strong></p>';
    echo '<p>IP riconosciuto dal server: <code>' . esc_html($d['ip'] ?: '(non pubblico)') . '</code></p>';
    echo '<p>Paese rilevato: <strong>' . esc_html($d['country'] ?: 'non determinabile') . '</strong>. Origine: <code>' . esc_html($d['reason']) . '</code></p>';
    echo '<p>Le aree protette sono disponibili soltanto quando il Paese riconosciuto e\' IT. GeoIP non disponibile o REMOTE_ADDR non pubblico: accesso negato. Gli IP fidati non aggirano questo controllo.</p>';
    echo '<p>Per un filtro rigoroso, indipendente da WordPress, configura GeoIP/WAF sul server. Non usare X-Forwarded-For senza un proxy esplicitamente attendibile.</p></div>';
}
