<?php
/**
 * PSR Centro Sicurezza — audit login, blocchi, GeoIP e OTP email.
 * Solo amministratore WordPress ID 1; nessun dato pubblico o endpoint anonimo.
 * Database creato al primo evento/login o accesso alla console; non esegue scansioni filesystem.
 */
if (!defined('ABSPATH')) { exit; }

function psr_center_is_owner() {
    return (int) get_current_user_id() === 1 && current_user_can('manage_options');
}
function psr_center_tables() {
    global $wpdb;
    return array($wpdb->prefix . 'psr_security_events', $wpdb->prefix . 'psr_security_ip_state');
}
function psr_center_install() {
    static $did = null;
    if ($did !== null) { return $did; }
    if (get_option('_psr_center_schema_v', '') === '1') { return $did = true; }
    global $wpdb;
    list($events, $ips) = psr_center_tables();
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $charset = $wpdb->get_charset_collate();
    $sql_events = "CREATE TABLE $events (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        happened_at datetime NOT NULL,
        event_type varchar(32) NOT NULL,
        severity varchar(12) NOT NULL,
        ip varchar(45) NOT NULL DEFAULT '',
        username varchar(80) NOT NULL DEFAULT '',
        country varchar(2) NOT NULL DEFAULT '',
        source varchar(24) NOT NULL DEFAULT '',
        note varchar(191) NOT NULL DEFAULT '',
        PRIMARY KEY  (id),
        KEY happened_at (happened_at),
        KEY event_time (event_type,happened_at),
        KEY ip_time (ip,happened_at)
    ) $charset;";
    $sql_ips = "CREATE TABLE $ips (
        ip varchar(45) NOT NULL,
        attempts int(10) unsigned NOT NULL DEFAULT 0,
        blocked_until datetime DEFAULT NULL,
        first_seen datetime NOT NULL,
        last_seen datetime NOT NULL,
        origin varchar(12) NOT NULL DEFAULT 'auto',
        PRIMARY KEY  (ip),
        KEY blocked_until (blocked_until),
        KEY last_seen (last_seen)
    ) $charset;";
    dbDelta($sql_events);
    dbDelta($sql_ips);
    // Controllo effettivo: se il DB non consente CREATE TABLE non considerarlo installato.
    $e = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($events)));
    $i = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($ips)));
    if ($e !== $events || $i !== $ips) { return $did = false; }
    update_option('_psr_center_schema_v', '1', false);
    return $did = true;
}
function psr_center_clean_ip($ip) {
    $ip = trim((string) $ip);
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '';
}
function psr_center_log_event($event, $severity = 'info', $ip = '', $username = '', $country = '', $source = 'login', $note = '') {
    // Whitelist per impedire inquinamento del registro con dati arbitrari.
    $allowed = array('login_failed','login_blocked','login_success','otp_sent','otp_failed','otp_verified',
        'otp_delivery_failed','auth_probe','geo_denied','ip_blacklist_denied','xmlrpc_denied','geo_unavailable','manual_ban','manual_unban',
        'site_ban_denied','user_created','role_changed','account_email_changed','account_password_changed','user_deleted','admin_role_denied','trusted_ip_added','trusted_ip_removed','security_setting_changed','admin_role_denied','device_mismatch');
    if (!in_array($event, $allowed, true)) { return false; }
    if (!psr_center_install()) { return false; }
    global $wpdb;
    list($events) = psr_center_tables();
    $ip = psr_center_clean_ip($ip);
    $username = substr(sanitize_text_field((string)$username), 0, 80);
    $note = substr(sanitize_text_field((string)$note), 0, 191);
    $country = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', (string)$country), 0, 2));
    $severity = in_array($severity, array('info','warning','critical'), true) ? $severity : 'info';
    $source = substr(sanitize_key((string)$source), 0, 24);
    return (bool) $wpdb->insert($events, array(
        'happened_at'=>gmdate('Y-m-d H:i:s'), 'event_type'=>$event, 'severity'=>$severity,
        'ip'=>$ip, 'username'=>$username, 'country'=>$country, 'source'=>$source, 'note'=>$note,
    ), array('%s','%s','%s','%s','%s','%s','%s','%s'));
}
function psr_center_log_limited($event, $severity, $ip, $username = '', $country = '', $source = 'login', $note = '', $seconds = 60) {
    $ip = psr_center_clean_ip($ip);
    $key = '_psr_loglim_' . substr(hash_hmac('sha256', $event . '|' . $source . '|' . $ip, wp_salt('auth')), 0, 32);
    if (get_transient($key)) { return false; }
    set_transient($key, 1, max(30, (int)$seconds));
    return psr_center_log_event($event, $severity, $ip, $username, $country, $source, $note);
}
function psr_center_sync_ip($ip, $state, $origin = 'auto') {
    $ip = psr_center_clean_ip($ip);
    if (!$ip || !psr_center_install() || !is_array($state)) { return; }
    global $wpdb;
    list(, $ips) = psr_center_tables();
    $now = gmdate('Y-m-d H:i:s');
    $start = (int)($state['start'] ?? time());
    $until = (int)($state['until'] ?? 0);
    $wpdb->replace($ips, array(
        'ip'=>$ip, 'attempts'=>max(0,(int)($state['n'] ?? 0)),
        'blocked_until'=>$until > time() ? gmdate('Y-m-d H:i:s',$until) : null,
        'first_seen'=>gmdate('Y-m-d H:i:s', $start > 0 ? $start : time()),
        'last_seen'=>$now, 'origin'=>$origin === 'manual' ? 'manual' : 'auto',
    ), array('%s','%d','%s','%s','%s','%s'));
}
function psr_center_remove_ip($ip) {
    $ip = psr_center_clean_ip($ip);
    if (!$ip || !psr_center_install()) { return; }
    global $wpdb;
    list(, $ips) = psr_center_tables();
    $wpdb->delete($ips, array('ip'=>$ip), array('%s'));
}
/** Etichetta indicativa ricavata dallo User-Agent: NON identifica in modo univoco il PC. */
function psr_center_device_label($ua) {
    $ua=(string)$ua;
    if (stripos($ua,'Android')!==false) { $os='Android'; }
    elseif (stripos($ua,'iPhone')!==false || stripos($ua,'iPad')!==false) { $os='iOS/iPadOS'; }
    elseif (stripos($ua,'Windows')!==false) { $os='Windows'; }
    elseif (stripos($ua,'Macintosh')!==false || stripos($ua,'Mac OS')!==false) { $os='macOS'; }
    elseif (stripos($ua,'Linux')!==false) { $os='Linux'; }
    else { $os='Sistema non rilevato'; }
    if (stripos($ua,'Edg/')!==false || stripos($ua,'Edge/')!==false) { $browser='Edge'; }
    elseif (stripos($ua,'OPR/')!==false || stripos($ua,'Opera')!==false) { $browser='Opera'; }
    elseif (stripos($ua,'Firefox/')!==false || stripos($ua,'FxiOS/')!==false) { $browser='Firefox'; }
    elseif (stripos($ua,'Chrome/')!==false || stripos($ua,'CriOS/')!==false) { $browser='Chrome'; }
    elseif (stripos($ua,'Safari/')!==false) { $browser='Safari'; }
    else { $browser='Browser non rilevato'; }
    return $os . ' · ' . $browser;
}

function psr_center_on_login($username, $user) {
    if (!($user instanceof WP_User)) { return; }
    $ip = function_exists('psr_login_ip') ? psr_login_ip() : '';
    $note = 'Utente ID ' . (int)$user->ID;
    // Il token non viene mai mostrato. Questo identificativo casuale NON e' una credenziale.
    if (!empty($GLOBALS['psr_device_new_sid'])) {
        $note .= ' · Sessione ' . substr((string)$GLOBALS['psr_device_new_sid'], 0, 12);
    }
    $note .= ' · ' . psr_center_device_label((string)($_SERVER['HTTP_USER_AGENT'] ?? ''));
    psr_center_log_event('login_success', 'info', $ip, $user->user_login, '', 'login', $note);
}
add_action('wp_login', 'psr_center_on_login', 30, 2);
/** Auditing account (nessun blocco automatico e nessuna query sulle visite pubbliche).
 * Il controllo non sostituisce l'audit di modifiche dirette al DB o alle sessioni gia' compromesse.
 */
function psr_center_account_actor() {
    $id = get_current_user_id();
    return $id ? 'Autore ID ' . (int)$id : 'Autore non identificato';
}
function psr_center_on_user_created($user_id) {
    $user=get_userdata((int)$user_id);
    if (!$user) { return; }
    psr_center_log_event('user_created','warning',function_exists('psr_login_ip')?psr_login_ip():'',
        $user->user_login,'','users','Utente ID '.(int)$user_id.' creato · '.psr_center_account_actor());
}
add_action('user_register', 'psr_center_on_user_created', 20, 1);
function psr_center_on_user_role($user_id, $role, $old_roles) {
    $user=get_userdata((int)$user_id);
    $name=$user ? $user->user_login : '';
    $important=$role==='administrator' || in_array('administrator', (array)$old_roles,true);
    psr_center_log_event('role_changed',$important?'critical':'warning',function_exists('psr_login_ip')?psr_login_ip():'',
        $name,'','users','ID '.(int)$user_id.' · ruolo '.sanitize_key($role).' · '.psr_center_account_actor());
}
add_action('set_user_role', 'psr_center_on_user_role', 20, 3);
function psr_center_on_account_modified($user_id, $old_user_data) {
    if (!($old_user_data instanceof WP_User)) { return; }
    $new_user=get_userdata((int)$user_id);
    if (!$new_user) { return; }
    $ip=function_exists('psr_login_ip')?psr_login_ip():'';
    if ($new_user->user_email !== $old_user_data->user_email) {
        psr_center_log_event('account_email_changed', (int)$user_id===1?'critical':'warning',
            $ip,$new_user->user_login,'','users','Email account ID '.(int)$user_id.' modificata · '.psr_center_account_actor());
    }
    if ($new_user->user_pass !== $old_user_data->user_pass) {
        psr_center_log_event('account_password_changed',(int)$user_id===1?'critical':'warning',
            $ip,$new_user->user_login,'','users','Password account ID '.(int)$user_id.' modificata · '.psr_center_account_actor());
    }
}
add_action('profile_update', 'psr_center_on_account_modified', 20, 2);
function psr_center_on_user_deleted($user_id) {
    $user=get_userdata((int)$user_id);
    psr_center_log_event('user_deleted','critical',function_exists('psr_login_ip')?psr_login_ip():'',
        $user?$user->user_login:'','','users','Utente ID '.(int)$user_id.' eliminato · '.psr_center_account_actor());
}
add_action('delete_user','psr_center_on_user_deleted', 20, 1);

function psr_center_cleanup() {
    if (!psr_center_install()) { return; }
    global $wpdb;
    list($events, $ips) = psr_center_tables();
    $days = (int)get_option('_psr_center_retention', 30);
    if (!in_array($days, array(7,14,30,60,90), true)) { $days=30; }
    $cutoff = gmdate('Y-m-d H:i:s', time()-$days*DAY_IN_SECONDS);
    $wpdb->query($wpdb->prepare("DELETE FROM $events WHERE happened_at < %s LIMIT 3000", $cutoff));
    $wpdb->query($wpdb->prepare("DELETE FROM $ips WHERE last_seen < %s AND (blocked_until IS NULL OR blocked_until < %s) LIMIT 3000", $cutoff, gmdate('Y-m-d H:i:s')));
}
add_action('psr_center_daily_cleanup', 'psr_center_cleanup');
function psr_center_schedule_cleanup() {
    if (!wp_next_scheduled('psr_center_daily_cleanup')) {
        wp_schedule_event(time() + HOUR_IN_SECONDS, 'daily', 'psr_center_daily_cleanup');
    }
}
add_action('admin_init', 'psr_center_schedule_cleanup');

function psr_center_menu() {
    if (!psr_center_is_owner()) { return; }
    add_menu_page('PSR Centro Sicurezza', 'PSR Sicurezza', 'manage_options', 'psr-security-center',
        'psr_center_page', 'dashicons-shield-alt', 58);
    add_submenu_page('psr-security-center', 'Panoramica', 'Panoramica', 'manage_options', 'psr-security-center', 'psr_center_page');
    // Le vecchie pagine restano disponibili per compatibilita' ma non duplicano il menu Strumenti.
    remove_submenu_page('tools.php','psr-login-security');
    remove_submenu_page('tools.php','psr-geo-login');
}
add_action('admin_menu', 'psr_center_menu', 999);
/**
 * Carica un CSS reale (compatibile con cache/CSP) e non un handle vuoto.
 * Il controllo su page garantisce il caricamento anche se il nome hook varia.
 * Non esegue nulla sulle pagine pubbliche né sugli altri pannelli WordPress.
 */
function psr_center_is_own_screen($hook = '') {
    if (!psr_center_is_owner()) { return false; }
    $page = isset($_GET['page']) && is_string($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
    return in_array($page, array('psr-security-center','psr-login-security','psr-geo-login'), true)
        || in_array((string)$hook, array('toplevel_page_psr-security-center',
            'tools_page_psr-login-security','tools_page_psr-geo-login'), true);
}
function psr_center_enqueue($hook) {
    if (!psr_center_is_own_screen($hook)) { return; }
    $relative = '/inc/psr-security-center.css';
    $local = get_template_directory() . $relative;
    if (is_file($local)) {
        // filemtime invalida le cache al prossimo aggiornamento GitHub/Plesk.
        wp_enqueue_style('psr-security-center', get_template_directory_uri() . $relative,
            array(), (string) filemtime($local), 'all');
    }
}
add_action('admin_enqueue_scripts', 'psr_center_enqueue');
/**
 * Fallback limitato all'area PSR, per gli hosting Plesk che non emettono
 * correttamente gli stili aggiunti con enqueue o hanno problemi di cache.
 * Il CSS e' statico: nessun dato utente viene interpolato nello style.
 */
function psr_center_css_fallback() {
    if (!psr_center_is_own_screen()) { return; }
    echo '<style id="psr-security-center-inline-fallback" type="text/css">',
        psr_center_css(), "\n", psr_center_css_modern(), "</style>\n";
}
add_action('admin_head', 'psr_center_css_fallback', 99);
function psr_center_css() {
    return '.psrc{max-width:1440px;margin:24px 24px 30px 0;color:#172d42}.psrc *{box-sizing:border-box}.psrc-header{background:linear-gradient(118deg,#092b50,#155b94);color:#fff;border-radius:15px;padding:25px 30px;display:flex;gap:18px;align-items:center;justify-content:space-between;flex-wrap:wrap}.psrc-header h1{color:#fff;font-size:27px;line-height:1.2;margin:3px 0 8px}.psrc-header p{margin:0;color:#d8e8f5}.psrc-label{font-size:11px;letter-spacing:1.2px;text-transform:uppercase;font-weight:800;color:#90d5ff}.psrc-chip{padding:9px 15px;border:1px solid #7bb3dd;color:#fff;border-radius:30px;font-weight:700;background:#ffffff16}.psrc-tabs{display:flex;gap:8px;flex-wrap:wrap;margin:20px 0}.psrc-tabs a{text-decoration:none;padding:11px 16px;border-radius:9px;background:white;border:1px solid #d9e3eb;color:#304862;font-weight:650}.psrc-tabs a.active{background:#16548a;color:#fff;border-color:#16548a}.psrc-kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:14px;margin-bottom:20px}.psrc-card{background:#fff;border:1px solid #dce5ed;border-radius:13px;padding:19px 20px;box-shadow:0 3px 14px #163b5610}.psrc-card .label{color:#667b8e;font-size:13px;font-weight:700}.psrc-card .value{font-size:29px;color:#102f4d;font-weight:800;margin:7px 0}.psrc-card .detail{color:#738598;font-size:12px}.psrc-layout{display:grid;grid-template-columns:minmax(0,2fr) minmax(270px,1fr);gap:18px}.psrc h2{font-size:18px;margin:0 0 18px;color:#183c60}.psrc h3{margin:16px 0 8px}.psrc-bars{display:grid;grid-template-columns:repeat(7,minmax(0,1fr));gap:14px;align-items:end;height:198px;padding-top:15px}.psrc-bar-cell{height:100%;display:flex;flex-direction:column;justify-content:flex-end;gap:8px;text-align:center;font-size:12px;color:#667b8e}.psrc-bar{background:linear-gradient(#278bd7,#165b96);min-height:4px;border-radius:7px 7px 3px 3px}.psrc-table{width:100%;border-collapse:collapse}.psrc-table th{text-align:left;color:#647a90;background:#f2f6fa;font-size:12px;letter-spacing:.15px}.psrc-table th,.psrc-table td{border-bottom:1px solid #e5edf3;padding:12px;vertical-align:middle}.psrc-table tr:hover td{background:#fafcff}.psrc-pill{display:inline-block;padding:4px 9px;border-radius:25px;font-size:11px;font-weight:800;background:#eaf0f6;color:#365b7a}.psrc-pill.critical,.psrc-pill.danger{background:#ffe6e6;color:#a31f2b}.psrc-pill.warning{background:#fff3d8;color:#946000}.psrc-pill.ok{background:#e6f6ee;color:#126341}.psrc-toolbar{display:flex;flex-wrap:wrap;gap:10px;align-items:end;margin:0 0 16px}.psrc-toolbar label{font-size:12px;font-weight:700;color:#586d80;display:block;margin:0 0 4px}.psrc-toolbar select,.psrc-toolbar input{min-width:155px;max-width:100%}.psrc-btn{display:inline-block;border:0;background:#165b91;color:#fff!important;padding:8px 14px;text-decoration:none;border-radius:7px;font-weight:700;cursor:pointer}.psrc-btn.secondary{background:#eaf1f7;color:#244e71!important}.psrc-muted{color:#627a8c}.psrc-info{border-left:4px solid #2885c5;background:#edf7ff;border-radius:5px;padding:13px 15px;margin:15px 0}.psrc-alert{border-left-color:#c98227;background:#fff7ea}.psrc-actions{display:flex;gap:8px;flex-wrap:wrap;align-items:center}.psrc-actions form{display:inline-flex;gap:6px;align-items:center;margin:0}.psrc .tablenav-pages{margin:15px 0}.psrc .tablenav-pages a{margin:0 4px}.psrc-small{font-size:12px}.psrc table code{white-space:nowrap}@media(max-width:1050px){.psrc-layout{grid-template-columns:1fr}}@media(max-width:782px){.psrc{margin:12px 10px 20px 0}.psrc-header{padding:20px}.psrc-card{padding:14px}.psrc-table{display:block;overflow-x:auto;white-space:nowrap}}';
}
function psr_center_css_modern() {
    return <<<'PSRCSS'
.psrc{--psr-navy:#0d2546;--psr-blue:#1473bc;--psr-sky:#e9f3ff;--psr-muted:#667b92;--psr-stroke:#e4ebf3;max-width:1560px;margin:20px 24px 45px 0;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Inter,sans-serif;color:#1a3451}
.psrc-header{position:relative;isolation:isolate;overflow:hidden;align-items:stretch;min-height:205px;padding:30px 34px;border:1px solid #215c90;border-radius:20px;background:linear-gradient(122deg,#0c294e 0%,#0e467d 54%,#166ba2 100%);box-shadow:0 14px 36px #14416b26}
.psrc-header:before{content:"";position:absolute;width:360px;height:360px;right:-80px;top:-190px;background:radial-gradient(circle,#91d9ff55,transparent 70%);z-index:-1}
.psrc-header:after{content:"";position:absolute;right:20%;bottom:-105px;width:250px;height:250px;border:1px solid #d1ecff32;border-radius:50%;z-index:-1}
.psrc-hero-copy{max-width:730px}.psrc-label{color:#aedbff;font-weight:800;letter-spacing:1.65px;font-size:11px}.psrc-header h1{color:#fff;font-weight:800;font-size:34px;letter-spacing:-.7px;margin:12px 0}.psrc-header h1 .dashicons{height:auto;width:auto;font-size:35px;vertical-align:middle;margin-right:12px;color:#8fd4ff}.psrc-header p{font-size:14px;color:#d6e8f8;max-width:560px;line-height:1.65}
.psrc-hero-checks{display:flex;flex-wrap:wrap;gap:9px;margin-top:22px}.psrc-hero-checks span{display:inline-flex;align-items:center;border:1px solid #ffffff2e;color:#f3fbff;background:#ffffff12;border-radius:24px;padding:7px 12px;font-weight:650;font-size:12px}.psrc-hero-checks .dashicons{font-size:16px;width:16px;height:16px;margin-right:6px;color:#8debd4}
.psrc-hero-side{display:flex;flex-direction:column;align-items:flex-end;justify-content:space-between;min-width:180px;gap:12px}.psrc-hero-side .psrc-chip{font-size:12px;border-color:#acd5ef87;background:#ffffff10;padding:11px 16px}.psrc-hero-meta{text-align:right;color:#d4ecff;font-size:12px;line-height:1.6}.psrc-hero-meta b{color:#fff}
.psrc-tabs{margin:22px 0 23px;padding:7px;background:#eef3f9;border:1px solid #e0e8f0;border-radius:14px;gap:6px}.psrc-tabs a{padding:12px 15px;background:transparent;border:1px solid transparent;border-radius:9px;color:#516983;font-weight:680;transition:background .12s ease,box-shadow .12s ease}.psrc-tabs a:hover{background:#fff;color:#0d487b}.psrc-tabs a.active{background:#fff;color:#0f5388;border:1px solid #d6e5f3;box-shadow:0 3px 10px #12385e10}.psrc-tabs a.active:before{content:"";display:inline-block;width:6px;height:6px;border-radius:50%;background:#1884cb;margin:0 8px 1px 0}
.psrc-kpis{gap:16px}.psrc-kpis .psrc-card{position:relative;padding:21px 22px 20px;min-height:130px;overflow:hidden}.psrc-kpis .psrc-card:before{content:"";position:absolute;left:0;top:17px;bottom:17px;width:4px;border-radius:0 5px 5px 0;background:#258ecb}.psrc-kpis .psrc-card:nth-child(2):before{background:#e58b37}.psrc-kpis .psrc-card:nth-child(3):before{background:#6b88c9}.psrc-kpis .psrc-card:nth-child(4):before{background:#39a588}.psrc-kpis .psrc-card:nth-child(5):before{background:#8f71c5}.psrc-kpis .psrc-card .label{font-size:12px;font-weight:720;color:#68809a}.psrc-kpis .psrc-card .value{font-size:33px;margin:10px 0 5px;letter-spacing:-.8px}.psrc-kpis .psrc-card .detail{font-size:11.5px;color:#788ba0}
.psrc-card{background:#fff;border:1px solid var(--psr-stroke);border-radius:16px;box-shadow:0 4px 22px #173b5d0b;padding:24px 26px}.psrc-card h2{font-size:18px;line-height:1.35;font-weight:760;letter-spacing:-.2px;color:#153b5e;margin:0 0 17px}.psrc-card p{font-size:13px;line-height:1.7;color:#576f86}.psrc-card h2+p{margin-top:0}.psrc-eyebrow{display:block;text-transform:uppercase;font-size:10px;letter-spacing:1.4px;font-weight:800;color:#2683c1;margin-bottom:9px}
.psrc-table-wrap{max-width:100%;overflow:auto;border:1px solid #e4ecf3;border-radius:10px}.psrc-table{border-collapse:collapse;width:100%;font-size:12.5px}.psrc-table th{background:#f4f8fb;color:#607990;white-space:nowrap;padding:12px 13px;text-transform:uppercase;letter-spacing:.45px;font-weight:750;font-size:10.5px}.psrc-table td{padding:13px;border-bottom:1px solid #edf1f6;color:#2b445e}.psrc-table tbody tr:last-child td{border-bottom:0}.psrc-table code{background:#f0f5fa;border:1px solid #e3ebf2;color:#194a75;border-radius:5px;padding:4px 7px;font-size:11px}.psrc-pill{border-radius:6px;padding:6px 9px;letter-spacing:.1px}.psrc-pill.ok{background:#e3f6ef;color:#076d4e}.psrc-pill.warning{background:#fff2da;color:#91600a}.psrc-pill.critical,.psrc-pill.danger{background:#ffe9e8;color:#a82c38}
.psrc-btn{padding:10px 15px;border-radius:8px;background:#1768a5;box-shadow:0 2px 3px #0b477510;line-height:1.15;transition:background .1s ease}.psrc-btn:hover{background:#09578c;color:#fff}.psrc-btn.secondary:hover{background:#dce8f2;color:#194e70}.psrc-toolbar{gap:12px}.psrc-toolbar>div{min-width:150px}.psrc-toolbar label{color:#456783}.psrc-toolbar select,.psrc-toolbar input:not([type=checkbox]){min-height:37px;border:1px solid #d5e0eb;border-radius:7px;box-shadow:none}.psrc-toolbar input:focus,.psrc-toolbar select:focus{border-color:#1779bf;box-shadow:0 0 0 2px #bde5ff}
.psrc-info{background:#eef7ff;border-left-color:#348cca;color:#355775}.psrc-alert{background:#fff7e8;border-left-color:#d79432;color:#765c33}.psrc-bars{height:215px;background:linear-gradient(180deg,transparent 49.5%,#edf1f6 50%,transparent 50.5%);background-size:100% 50%;padding:10px}.psrc-bar{background:linear-gradient(180deg,#3aaee9,#17649f);box-shadow:0 4px 9px #1468a620}.psrc-small{font-size:11.5px}.psrc-section-head{display:flex;justify-content:space-between;align-items:center;gap:12px}.psrc-form-layout{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px}.psrc-card .psrc-muted{color:#728499}
.psrc .notice{margin:16px 0;padding:8px 14px;border-radius:8px}
.psrc .notice,.psrc .notice p,.psrc .notice strong{color:#243c55}
@media(max-width:900px){.psrc-header{padding:22px;min-height:0}.psrc-hero-side{display:none}.psrc-tabs{display:flex;overflow-x:auto;flex-wrap:nowrap}.psrc-tabs a{flex:none;white-space:nowrap}.psrc .psrc-layout{grid-template-columns:1fr}}
@media(max-width:600px){.psrc{margin:13px 8px 35px 0}.psrc-header h1{font-size:25px}.psrc-header h1 .dashicons{font-size:26px}.psrc-card{padding:17px 15px}.psrc-kpis{grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.psrc-kpis .psrc-card{min-height:116px;padding:16px}.psrc-kpis .psrc-card .value{font-size:27px}.psrc-hero-checks{margin-top:14px}}
PSRCSS;
}
function psr_center_url($tab = 'overview', $extra = array()) {
    return add_query_arg(array_merge(array('page'=>'psr-security-center','tab'=>$tab),$extra), admin_url('admin.php'));
}
function psr_center_label($type) {
    $labels = array('login_failed'=>'Password errata','login_blocked'=>'Login già bloccato','login_success'=>'Accesso riuscito',
        'otp_sent'=>'OTP email inviato','otp_failed'=>'OTP errato','otp_verified'=>'OTP verificato',
        'otp_delivery_failed'=>'Invio OTP fallito','auth_probe'=>'Tentativo login da IP estero','geo_denied'=>'IP estero bloccato','ip_blacklist_denied'=>'IP in blacklist bloccato','xmlrpc_denied'=>'XML-RPC bloccato','geo_unavailable'=>'GeoIP non disponibile',
        'manual_ban'=>'Ban manuale','manual_unban'=>'Sblocco manuale',
        'site_ban_denied'=>'Sito negato a IP bannato','user_created'=>'Utente creato',
        'role_changed'=>'Ruolo modificato','account_email_changed'=>'Email account modificata',
        'account_password_changed'=>'Password account modificata','user_deleted'=>'Utente eliminato',
        'trusted_ip_added'=>'IP fidato aggiunto','trusted_ip_removed'=>'IP fidato revocato',
        'security_setting_changed'=>'Impostazione di sicurezza modificata','admin_role_denied'=>'Promozione admin respinta');
    return $labels[$type] ?? $type;
}
function psr_center_local_date($utc) {
    if (!$utc) { return '—'; }
    return esc_html(get_date_from_gmt($utc, 'd/m/Y H:i:s'));
}
function psr_center_fetch_stats() {
    global $wpdb;
    list($events,$ips) = psr_center_tables();
    $day = gmdate('Y-m-d H:i:s', time()-DAY_IN_SECONDS);
    $week = gmdate('Y-m-d H:i:s', time()-7*DAY_IN_SECONDS);
    $now = gmdate('Y-m-d H:i:s');
    return array(
        'failed24'=>(int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $events WHERE happened_at >= %s AND event_type IN ('login_failed','otp_failed','auth_probe')",$day)),
        'admin7'=>(int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $events WHERE happened_at >= %s AND event_type IN ('user_created','role_changed','account_email_changed','account_password_changed','user_deleted','admin_role_denied')",$week)),
        'geo7'=>(int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $events WHERE happened_at >= %s AND event_type IN ('geo_denied','ip_blacklist_denied')",$week)),
        'otp7'=>(int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $events WHERE happened_at >= %s AND event_type='otp_verified'",$week)),
        'blocked'=>(int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $ips WHERE blocked_until >= %s",$now)),
        'events7'=>(int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $events WHERE happened_at >= %s",$week)),
    );
}
function psr_center_process_action() {
    if (!psr_center_is_owner() || ($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !isset($_POST['psr_center_action'])) { return; }
    check_admin_referer('psr_center_action');
    $action = sanitize_key(wp_unslash($_POST['psr_center_action']));
    $redirect_tab = in_array($action,array('retention','site_ban'),true) ? 'settings' : (strpos($action,'trusted_')===0 ? 'trusted' : 'blocks');
    if ($action === 'retention') {
        $days=(int)($_POST['days'] ?? 30);
        if (in_array($days,array(7,14,30,60,90),true)) { update_option('_psr_center_retention',$days,false); }
    } elseif ($action === 'site_ban') {
        $enabled = isset($_POST['site_ban_enabled']) && (string)$_POST['site_ban_enabled'] === '1' ? '1' : '0';
        update_option('_psr_site_ban_enabled',$enabled,false);
        psr_center_log_event('security_setting_changed','warning',function_exists('psr_login_ip')?psr_login_ip():'',
            '', '', 'dashboard','Blocco sito IP bannati: '.($enabled==='1'?'attivo':'disattivo'));
    } elseif ($action === 'trusted_add') {
        $ip = psr_center_clean_ip(sanitize_text_field(wp_unslash($_POST['ip'] ?? '')));
        $label = substr(sanitize_text_field(wp_unslash($_POST['label'] ?? 'Sede')),0,64);
        $digest = function_exists('psr_geo_trusted_digest') ? psr_geo_trusted_digest($ip) : '';
        if ($digest === '' || (function_exists('psr_geo_ip_blacklisted') && psr_geo_ip_blacklisted($ip)) || count(psr_geo_trusted_entries()) >= 30) {
            wp_safe_redirect(psr_center_url('trusted',array('psr_msg'=>'refused')));exit;
        }
        $all=psr_geo_trusted_entries();
        $all[$digest]=array('label'=>$label?:'IP fidato', 'added'=>time());
        update_option('_psr_geo_trusted_ip_hashes',$all,false);
        psr_center_log_event('trusted_ip_added','warning','',get_userdata(1)->user_login,'','dashboard',
            'Nuova sede fidata: '.($label?:'IP fidato').' (IP non registrato)');
    } elseif ($action === 'trusted_remove') {
        $digest = sanitize_text_field(wp_unslash($_POST['digest'] ?? ''));
        $all=psr_geo_trusted_entries();
        if (isset($all[$digest]) && is_string($digest) && preg_match('/^[a-f0-9]{64}$/D',$digest)) {
            $old_label=(string)($all[$digest]['label'] ?? 'IP fidato');
            unset($all[$digest]);
            update_option('_psr_geo_trusted_ip_hashes',$all,false);
            psr_center_log_event('trusted_ip_removed','warning','',get_userdata(1)->user_login,'','dashboard',
                'Revocata sede fidata: '.$old_label);
        }
    } elseif (in_array($action, array('ban','unban'), true)) {
        $ip = psr_center_clean_ip(sanitize_text_field(wp_unslash($_POST['ip'] ?? '')));
        if (!$ip || (function_exists('psr_geo_ip_blacklisted') && psr_geo_ip_blacklisted($ip)) || $ip === (function_exists('psr_login_ip') ? psr_login_ip() : '') || (function_exists('psr_geo_ip_allowlisted') && psr_geo_ip_allowlisted($ip))) {
            wp_safe_redirect(psr_center_url('blocks',array('psr_msg'=>'refused')));exit;
        }
        if ($action === 'unban') {
            delete_transient(psr_login_ip_key($ip));
            psr_center_remove_ip($ip);
            psr_center_log_event('manual_unban','warning', $ip, '', '', 'dashboard','Revocato da ID 1');
        } else {
            $seconds=(int)($_POST['duration'] ?? 3600);
            if (!in_array($seconds,array(3600,86400,604800),true)) { $seconds = 3600; }
            $state=psr_login_rate_state($ip);
            $now=time();
            $state['start'] = $state['start'] ?: $now;
            $state['until']=max((int)$state['until'],$now+$seconds);
            $state['site_ban'] = true;
            set_transient(psr_login_ip_key($ip),$state,8*DAY_IN_SECONDS);
            psr_center_sync_ip($ip,$state,'manual');
            psr_center_log_event('manual_ban','critical',$ip,'','','dashboard','Durata ' . ($seconds/3600) . ' ore, ID 1');
        }
    }
    wp_safe_redirect(psr_center_url($redirect_tab,array('psr_msg'=>'saved'))); exit;
}
add_action('admin_init','psr_center_process_action', 20);
function psr_center_export_csv() {
    if (!psr_center_is_owner()) { wp_die('Non autorizzato','',array('response'=>403)); }
    check_admin_referer('psr_center_export');
    if (!psr_center_install()) { wp_die('Registro indisponibile.'); }
    global $wpdb;
    list($events) = psr_center_tables();
    $cutoff=gmdate('Y-m-d H:i:s',time()-30*DAY_IN_SECONDS);
    $rows=$wpdb->get_results($wpdb->prepare("SELECT happened_at,event_type,severity,ip,username,country,source,note FROM $events WHERE happened_at >= %s ORDER BY id DESC LIMIT 5000",$cutoff),ARRAY_A);
    nocache_headers();
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="psr-security-events-' . gmdate('Ymd-His') . '.csv"');
    header('X-Content-Type-Options: nosniff');
    $out=fopen('php://output','w');
    fwrite($out,"\xEF\xBB\xBF");
    fputcsv($out,array('UTC','Evento','Gravita','IP','Username','Paese','Fonte','Note'),';');
    foreach ($rows as $row) {
        $cells=array_values($row);
        foreach ($cells as &$v) {
            $v=str_replace(array("\r","\n"),' ',(string)$v);
            if (preg_match('/^[\s\x00-\x1f]*[=+@\-]/u',$v)) { $v="'".$v; }
        } unset($v);
        fputcsv($out,$cells,';');
    }
    fclose($out);exit;
}
add_action('admin_post_psr_center_export','psr_center_export_csv');
function psr_center_page($forced_tab = '') {
    if (!psr_center_is_owner()) { wp_die('Non autorizzato','',array('response'=>403)); }
    $tab = $forced_tab ?: sanitize_key(wp_unslash($_GET['tab'] ?? 'overview'));
    if (!in_array($tab,array('overview','events','sessions','blocks','accounts','trusted','geo','otp','antispam','settings'),true)) { $tab='overview'; }
    $ready = psr_center_install();
    echo '<div class="wrap psrc"><header class="psrc-header"><div class="psrc-hero-copy"><div class="psrc-label">PSR SECURITY SUITE <span style="opacity:.5">/</span> CONTROL CENTER</div><h1><span class="dashicons dashicons-shield-alt" aria-hidden="true"></span>Centro Sicurezza</h1><p>Monitoraggio accessi, difesa degli account e controllo delle minacce. I risultati provengono dagli eventi registrati su questo Comune.</p><div class="psrc-hero-checks"><span><i class="dashicons dashicons-yes" aria-hidden="true"></i>OTP email tutti gli utenti</span><span><i class="dashicons dashicons-yes" aria-hidden="true"></i>Filtro Italia</span><span><i class="dashicons dashicons-yes" aria-hidden="true"></i>Ban IP progressivi</span></div></div><div class="psrc-hero-side"><span class="psrc-chip">&#128274; Accesso riservato ID 1</span><span class="psrc-hero-meta">Area di gestione<br><b>Controlli e registri privati</b></span></div></header>';
    $tabs=array('overview'=>'Panoramica','events'=>'Tentativi e attività','sessions'=>'Accessi riusciti e PC','blocks'=>'IP bloccati','accounts'=>'Utenti e privilegi','trusted'=>'IP fidati','geo'=>'Filtro geografico','otp'=>'OTP email','antispam'=>'Antispam','settings'=>'Impostazioni');
    echo '<nav class="psrc-tabs" aria-label="Sezioni centro sicurezza">';
    foreach ($tabs as $key=>$label) { echo '<a class="'.($tab===$key?'active':'').'" href="'.esc_url(psr_center_url($key)).'">'.esc_html($label).'</a>'; }
    echo '</nav>';
    if (!$ready) { echo '<div class="notice notice-error"><p><strong>Il database non consente di creare le tabelle PSR.</strong> Verifica i permessi CREATE TABLE dell’utente MySQL e consulta i log Plesk. Il login continua a funzionare, ma il registro eventi non è disponibile.</p></div></div>';return; }
    if (isset($_GET['psr_msg'])) { echo '<div class="notice notice-'.($_GET['psr_msg']==='saved'?'success':'warning').'"><p>'.($_GET['psr_msg']==='saved'?'Operazione completata.':'Operazione rifiutata: IP non valido, IP attuale o IP in whitelist.').'</p></div>'; }
    switch($tab) {
        case 'overview': psr_center_overview();break;
        case 'events': psr_center_events();break;
        case 'sessions': psr_center_sessions();break;
        case 'blocks': psr_center_blocks();break;
        case 'accounts': psr_center_accounts();break;
        case 'trusted': psr_center_trusted();break;
        case 'geo': psr_center_geo();break;
        case 'otp': psr_center_otp();break;
        case 'antispam': if (function_exists('psr_antispam_admin_page')) { psr_antispam_admin_page(); } break;
        case 'settings': psr_center_settings();break;
    }
    echo '<p class="psrc-muted psrc-small" style="margin:20px 0">I dati sono visibili solo all’ID 1 e riguardano gli eventi PSR successivi all’installazione. L’accesso al database non sostituisce la protezione Plesk/WAF né i report Wordfence.</p></div>';
}
function psr_center_overview() {
    global $wpdb;
    list($events) = psr_center_tables();
    $s=psr_center_fetch_stats();
    $kpis=array(
        array('Tentativi sospetti · 24 ore',$s['failed24'],'Password / OTP / login esteri'),
        array('IP bloccati · adesso',$s['blocked'],'Blocchi non scaduti'),
        array('Accessi GeoIP / blacklist negati · 7 giorni',$s['geo7'],'Campionati per IP e percorso'),
        array('OTP verificati · 7 giorni',$s['otp7'],'Verifiche di tutti gli utenti'),
        array('Modifiche account · 7 giorni',$s['admin7'],'Creazione, ruoli e credenziali'),
    );
    echo '<div class="psrc-kpis">';foreach($kpis as $k){echo '<div class="psrc-card"><div class="label">'.esc_html($k[0]).'</div><div class="value">'.(int)$k[1].'</div><div class="detail">'.esc_html($k[2]).'</div></div>';}echo '</div>';
    if ($s['admin7'] > 0) { echo '<div class="psrc-info psrc-alert"><strong>Attenzione:</strong> sono state registrate <strong>'.(int)$s['admin7'].'</strong> modifiche agli account negli ultimi 7 giorni. <a href="'.esc_url(psr_center_url('accounts')).'">Esamina gli utenti e i privilegi →</a></div>'; }
    echo '<div class="psrc-layout"><section class="psrc-card"><h2>Attività di sicurezza negli ultimi 7 giorni</h2>';
    $days=array();for($n=6;$n>=0;$n--){$date=gmdate('Y-m-d',strtotime('-'.$n.' days'));$days[$date]=0;}
    $rows=$wpdb->get_results($wpdb->prepare("SELECT DATE(happened_at) AS day,COUNT(*) AS num FROM $events WHERE happened_at >= %s GROUP BY DATE(happened_at)",gmdate('Y-m-d H:i:s',time()-7*DAY_IN_SECONDS)));
    foreach($rows as $r){if(isset($days[$r->day])){$days[$r->day]=(int)$r->num;}}
    $max=max(1,max($days));echo '<div class="psrc-bars" role="img" aria-label="Grafico eventi negli ultimi sette giorni">';
    foreach($days as $date=>$v){$h=max(4,round(($v/$max)*156));echo '<div class="psrc-bar-cell"><strong>'.(int)$v.'</strong><div class="psrc-bar" style="height:'.(int)$h.'px" title="'.esc_attr($date.' - '.$v.' eventi').'"></div><span>'.esc_html(date('d/m',strtotime($date))).'</span></div>';}
    echo '</div><p class="psrc-muted psrc-small">Tutti gli eventi registrati da PSR · la geolocalizzazione estera è limitata a un evento per IP/percorso al minuto.</p></section>';
    echo '<section class="psrc-card"><h2>Stato delle protezioni</h2><table class="psrc-table">';
    $checks=array('OTP email tutti gli utenti'=>function_exists('psr_login_email_issue'),'Blocco progressivo IP'=>function_exists('psr_login_rate_failure'),
        'GeoIP accessi'=>function_exists('psr_geo_enabled') && psr_geo_enabled(),'Blacklist globale IP'=>function_exists('psr_geo_ip_blacklisted'),'Registro eventi'=>true,'Blocco siti per IP bannati'=>function_exists('psr_geo_site_ban_enabled') && psr_geo_site_ban_enabled(),'Protezione web shell'=>function_exists('psr_guard_new_shell_names'));
    foreach($checks as $name=>$on){echo '<tr><td>'.esc_html($name).'</td><td><span class="psrc-pill '.($on?'ok':'warning').'">'.($on?'Attivo':'Da verificare').'</span></td></tr>';}
    echo '</table><p class="psrc-muted psrc-small">L’indicatore verifica la presenza del modulo, non garantisce che il server sia completamente protetto.</p></section></div>';
    echo '<div class="psrc-card" style="margin-top:18px"><h2>Ultimi eventi</h2>';psr_center_event_table(10, '', '', 1);echo '<p><a class="psrc-btn secondary" href="'.esc_url(psr_center_url('events')).'">Apri il registro completo →</a> <a class="psrc-btn secondary" href="'.esc_url(psr_center_url('sessions')).'">Accessi riusciti e PC →</a></p></div>';
}
function psr_center_event_table($limit, $event_filter='', $ip_filter='', $page=1) {
    global $wpdb;
    list($events) = psr_center_tables();
    $where='WHERE 1=1';$vals=array();
    if ($event_filter !== '') {$where.=' AND event_type=%s';$vals[]=$event_filter;}
    if ($ip_filter !== '') {$where.=' AND ip=%s';$vals[]=$ip_filter;}
    $offset=max(0,($page-1)*$limit);
    $sql="SELECT * FROM $events $where ORDER BY id DESC LIMIT %d OFFSET %d";
    $vals[]=$limit;$vals[]=$offset;
    $rows=$wpdb->get_results($wpdb->prepare($sql,$vals));
    echo '<table class="psrc-table"><thead><tr><th>Data / ora</th><th>Evento</th><th>IP pubblico</th><th>Account tentato</th><th>Paese</th><th>Dettagli</th></tr></thead><tbody>';
    if (!$rows) {echo '<tr><td colspan="6">Nessun evento registrato per questi filtri.</td></tr>';}
    foreach($rows as $r){$cls=$r->severity==='critical'?'critical':($r->severity==='warning'?'warning':'ok');
        echo '<tr><td>'.psr_center_local_date($r->happened_at).'</td><td><span class="psrc-pill '.esc_attr($cls).'">'.esc_html(psr_center_label($r->event_type)).'</span></td><td><code>'.esc_html($r->ip ?: '—').'</code></td><td>'.esc_html($r->username ?: '—').'</td><td>'.esc_html($r->country ?: '—').'</td><td>'.esc_html($r->note ?: $r->source).'</td></tr>';
    }
    echo '</tbody></table>';return count($rows);
}
function psr_center_events(){
    global $wpdb;
    list($events)=psr_center_tables();
    $types=array(''=>'Tutti gli eventi','login_failed'=>'Password errata','login_blocked'=>'Login bloccato','login_success'=>'Accesso riuscito','otp_sent'=>'OTP richiesto','otp_failed'=>'OTP errato','otp_verified'=>'OTP verificato','otp_delivery_failed'=>'Errore invio OTP','auth_probe'=>'Tentativo login estero','geo_denied'=>'Accesso estero negato','ip_blacklist_denied'=>'IP blacklist permanente','xmlrpc_denied'=>'XML-RPC bloccato','geo_unavailable'=>'GeoIP non disponibile','manual_ban'=>'Ban manuale','manual_unban'=>'Sblocco manuale',
        'site_ban_denied'=>'Sito bloccato','user_created'=>'Utente creato','role_changed'=>'Ruolo cambiato',
        'account_email_changed'=>'Email modificata','account_password_changed'=>'Password cambiata',
        'user_deleted'=>'Utente eliminato','trusted_ip_added'=>'IP fidato aggiunto',
        'trusted_ip_removed'=>'IP fidato rimosso','security_setting_changed'=>'Impostazioni sicurezza');
    $type=sanitize_key(wp_unslash($_GET['event']??''));if(!array_key_exists($type,$types)){$type='';}
    $ip=psr_center_clean_ip(sanitize_text_field(wp_unslash($_GET['ip']??'')));
    $page=max(1,min(500,(int)($_GET['paged']??1)));
    echo '<div class="psrc-card"><h2>Registro accessi e tentativi</h2><form class="psrc-toolbar" method="get" action="'.esc_url(admin_url('admin.php')).'">';
    echo '<input type="hidden" name="page" value="psr-security-center"><input type="hidden" name="tab" value="events">';
    echo '<div><label for="psr-event">Tipo evento</label><select id="psr-event" name="event">';foreach($types as $v=>$label){echo '<option value="'.esc_attr($v).'" '.selected($v,$type,false).'>'.esc_html($label).'</option>';}echo '</select></div>';
    echo '<div><label for="psr-ip">Indirizzo IP</label><input id="psr-ip" type="text" name="ip" placeholder="Filtra IP esatto" value="'.esc_attr($ip).'"></div><button class="psrc-btn" type="submit">Applica filtri</button><a class="psrc-btn secondary" href="'.esc_url(psr_center_url('events')).'">Azzera</a></form>';
    echo '<p class="psrc-muted psrc-small">Sono registrati IP, nomi utente tentati ed eventi; non vengono salvate password o codici OTP. I tentativi GeoIP ripetuti vengono campionati per evitare saturazione.</p>';
    psr_center_event_table(30,$type,$ip,$page);
    $where='WHERE 1=1';$params=array();if($type!==''){$where.=' AND event_type=%s';$params[]=$type;}if($ip!==''){$where.=' AND ip=%s';$params[]=$ip;}
    $count=$params?(int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $events $where",$params)):(int)$wpdb->get_var("SELECT COUNT(*) FROM $events");
    $pages=(int)ceil($count/30);echo '<div class="tablenav-pages"><span>'.(int)$count.' eventi · pagina '.(int)$page.' di '.max(1,$pages).'</span>';
    if($page>1){echo '<a href="'.esc_url(psr_center_url('events',array('event'=>$type,'ip'=>$ip,'paged'=>$page-1))).'">← Precedente</a>';}
    if($page<$pages){echo '<a href="'.esc_url(psr_center_url('events',array('event'=>$type,'ip'=>$ip,'paged'=>$page+1))).'">Successiva →</a>';}
    echo '</div>';
    echo '<p><a class="psrc-btn secondary" href="'.esc_url(wp_nonce_url(admin_url('admin-post.php?action=psr_center_export'),'psr_center_export')).'">Esporta CSV ultimi 30 giorni (max 5.000 eventi)</a></p></div>';
}
function psr_center_action_form($action,$ip,$label,$duration=0) {
    echo '<form method="post">';wp_nonce_field('psr_center_action');
    echo '<input type="hidden" name="psr_center_action" value="'.esc_attr($action).'"><input type="hidden" name="ip" value="'.esc_attr($ip).'">';
    if($duration){echo '<input type="hidden" name="duration" value="'.(int)$duration.'">';}
    echo '<button class="psrc-btn secondary" type="submit" onclick="return confirm(\'Confermi l’operazione per questo IP?\')">'.esc_html($label).'</button></form>';
}
function psr_center_blocks(){
    global $wpdb;list($events,$ips)=psr_center_tables();
    $now=gmdate('Y-m-d H:i:s');
    $rows=$wpdb->get_results($wpdb->prepare("SELECT s.*, (SELECT e.username FROM $events e WHERE e.ip=s.ip AND e.username <> '' ORDER BY e.id DESC LIMIT 1) AS last_username FROM $ips s WHERE s.blocked_until > %s ORDER BY s.blocked_until DESC LIMIT 250",$now));
    echo '<div class="psrc-card"><h2>Blacklist globale permanente (GitHub)</h2><p class="psrc-info psrc-alert">Questi IP sono bloccati dal modulo Geo Login su tutti i Comuni aggiornati, indipendentemente dal Paese e dai ban temporanei. Non possono essere sbloccati dal pannello: la revoca richiede un aggiornamento controllato del codice o della configurazione.</p><table class="psrc-table"><thead><tr><th>Indirizzo IP</th><th>Regola</th><th>Scadenza</th><th>Stato</th></tr></thead><tbody>';
    $hard_ips = function_exists('psr_geo_global_deny_ips') ? psr_geo_global_deny_ips() : array();
    foreach ($hard_ips as $hard_ip) { echo '<tr><td><code>'.esc_html($hard_ip).'</code></td><td>WordPress (incluso sito pubblico se abilitato)</td><td>Non scade</td><td><span class="psrc-pill critical">Bloccato</span></td></tr>'; }
    if (!$hard_ips) { echo '<tr><td colspan="4">Modulo blacklist non disponibile</td></tr>'; }
    echo '</tbody></table></div>';
    echo '<div class="psrc-card" style="margin-top:18px"><h2>Indirizzi IP attualmente bloccati</h2><p class="psrc-info">La blacklist permanente, i ban manuali e i ban severi (15+ errori) negano anche le pagine pubbliche elaborate da WordPress, se l’opzione è attiva. I blocchi iniziali (5/10 errori) limitano soltanto il login, per ridurre il rischio di negare servizi ai cittadini. I file statici e PHP indipendenti richiedono una regola Plesk/WAF. I blocchi temporanei scadono automaticamente. Gli accessi esteri vengono negati dal filtro GeoIP e non compaiono necessariamente in questa tabella. La lista include gli IP tracciati da questa versione di PSR.</p>';
    echo '<table class="psrc-table"><thead><tr><th>IP</th><th>Account tentato</th><th>Tentativi</th><th>Tipo blocco</th><th>Ultima attività</th><th>Scadenza</th><th>Gestione</th></tr></thead><tbody>';
    if(!$rows){echo '<tr><td colspan="7">Nessun IP bloccato in questo momento.</td></tr>';}
    foreach($rows as $r){echo '<tr><td><code>'.esc_html($r->ip).'</code></td><td>'.esc_html($r->last_username ?: '—').'</td><td>'.(int)$r->attempts.'</td><td><span class="psrc-pill warning">'.esc_html($r->origin==='manual'?'Manuale':'Automatico').'</span></td><td>'.psr_center_local_date($r->last_seen).'</td><td>'.psr_center_local_date($r->blocked_until).'</td><td>';psr_center_action_form('unban',$r->ip,'Sblocca');echo '</td></tr>';}
    echo '</tbody></table></div><div class="psrc-card" style="margin-top:18px"><h2>Blocca o sblocca manualmente un IP</h2><form method="post" class="psrc-toolbar">';wp_nonce_field('psr_center_action');
    echo '<div><label>IP pubblico</label><input required name="ip" placeholder="Indirizzo IPv4 / IPv6"></div><div><label>Durata ban</label><select name="duration"><option value="3600">1 ora</option><option value="86400">24 ore</option><option value="604800">7 giorni</option></select></div><button class="psrc-btn" name="psr_center_action" value="ban">Blocca IP</button><button class="psrc-btn secondary" name="psr_center_action" value="unban">Sblocca IP</button></form><p class="psrc-muted">Per sicurezza non puoi bloccare il tuo IP corrente o uno autorizzato dalla whitelist GeoIP. Non inserire IP condivisi senza verificarne il proprietario.</p></div>';
}
function psr_center_geo(){
    $d=function_exists('psr_geo_decision')?psr_geo_decision():array('ip'=>'','country'=>'','reason'=>'missing','blocked'=>false);
    echo '<div class="psrc-kpis"><div class="psrc-card"><div class="label">Filtro Italia</div><div class="value" style="font-size:21px">'.(function_exists('psr_geo_enabled')&&psr_geo_enabled()?'Attivo':'Non disponibile').'</div><div class="detail">Login e pannello di tutti i ruoli</div></div><div class="psrc-card"><div class="label">IP rilevato</div><div class="value" style="font-size:21px">'.esc_html($d['ip']?:'Non pubblico').'</div><div class="detail">REMOTE_ADDR senza header non verificati</div></div><div class="psrc-card"><div class="label">Paese</div><div class="value">'.esc_html($d['country']?:'—').'</div><div class="detail">'.esc_html($d['reason']).'</div></div></div>';
    echo '<div class="psrc-card"><h2>Protezione geografica degli accessi</h2><p>Login, OTP e amministrazione richiedono un <strong>IP pubblico riconosciuto come italiano</strong>. Gli IP esteri o non verificabili ricevono HTTP 403 prima del modulo. La navigazione pubblica non viene filtrata per Paese.</p><p class="psrc-info psrc-alert"><strong>GeoIP non disponibile:</strong> le aree protette restano chiuse anche per ID 1. Prima del rilascio verifica GeoIP, SMTP e IP reale in Plesk. Il recupero operativo richiede accesso al server.</p><p>Gli IP fidati non aggirano il filtro Italia. Le intestazioni HTTP non autenticate non sono utilizzate per riconoscere l’IP.</p></div>';
}

/**
 * Visualizzazione in sola lettura: sessioni WordPress realmente attive + audit login.
 * Non modifica cookie, permessi, durata delle sessioni, ne' autorizza altri utenti.
 */
function psr_center_sessions() {
    if (!psr_center_is_owner()) { wp_die('Non autorizzato', '', array('response'=>403)); }
    global $wpdb;
    list($events)=psr_center_tables();
    $manager=WP_Session_Tokens::get_instance(1);
    $sessions=$manager->get_all();
    if (!is_array($sessions)) { $sessions=array(); }
    usort($sessions, static function($a,$b) { return (int)($b['login']??0) - (int)($a['login']??0); });
    $total_sessions=count($sessions);
    $sessions=array_slice($sessions, 0, 100);
    $current=$manager->get(wp_get_session_token());
    $current_id=is_array($current) ? (string)($current['psr_device_id']??'') : '';
    $has_proof=0;
    foreach ($sessions as $one) { if (is_array($one) && !empty($one['psr_device_proof'])) { $has_proof++; } }
    $enabled=function_exists('psr_login_device_binding_enabled') && psr_login_device_binding_enabled();
    echo '<div class="psrc-kpis">';
    echo '<div class="psrc-card"><div class="label">Sessioni attive ID 1 (dettaglio)</div><div class="value">'.(int)$total_sessions.'</div><div class="detail">Sessioni WordPress non scadute</div></div>';
    echo '<div class="psrc-card"><div class="label">Sessioni con prova browser</div><div class="value">'.(int)$has_proof.'</div><div class="detail">Conteggio ID 1 · regola applicata a tutti</div></div>';
    echo '<div class="psrc-card"><div class="label">Vincolo browser (tutti gli utenti)</div><div class="value" style="font-size:20px">'.($enabled?'OBBLIGATORIO':'SOLO RIPRISTINO').'</div><div class="detail">'.($enabled?'Sessioni di tutti gli utenti protette':'Solo prova browser sospesa da wp-config.php').'</div></div>';
    echo '</div>';
    echo '<div class="psrc-card"><span class="psrc-eyebrow">ACCOUNT PRINCIPALE · ID 1</span><h2>PC e browser associati alle sessioni</h2>';
    echo '<p>Il controllo delle sessioni protette e dei relativi limiti si applica a <strong>tutti gli utenti WordPress</strong>; in questa tabella sono mostrati i dettagli riservati dell’amministratore principale. Ogni browser/dispositivo che supera OTP crea una sessione distinta. Le informazioni su sistema e browser sono <strong>indicative</strong>: derivano dallo User-Agent e non provano quale PC fisico sia in uso. Il codice sessione mostrato non costituisce un token di accesso.</p>';
    echo '<div class="psrc-table-wrap"><table class="psrc-table"><thead><tr><th>Sessione</th><th>PC / browser</th><th>IP durante accesso</th><th>Accesso</th><th>Scadenza</th><th>Verifica</th></tr></thead><tbody>';
    if (!$sessions) { echo '<tr><td colspan="6">Nessuna sessione attiva.</td></tr>'; }
    foreach ($sessions as $s) {
        if (!is_array($s)) { continue; }
        $id=(string)($s['psr_device_id']??'');
        $now=(bool)($id!=='' && $id===$current_id);
        $name=psr_center_device_label((string)($s['ua']??''));
        $ip=psr_center_clean_ip((string)($s['psr_device_ip']??($s['ip']??'')));
        $at=(int)($s['psr_otp_verified_at']??($s['login']??0));
        $expiry=(int)($s['expiration']??0);
        $protected=!empty($s['psr_device_proof']);
        $otp=!empty($s['psr_otp_verified_at']);
        echo '<tr'.($now?' style="background:#edf7ff"':'').'>';
        echo '<td><strong>'.($id!==''?esc_html(substr($id,0,12)):'Sessione pre-fix').'</strong> '.($now?'<span class="psrc-pill ok">Questo PC / browser</span>':'').'</td>';
        echo '<td>'.esc_html($name).'</td><td><code>'.esc_html($ip?:'Non disponibile').'</code></td>';
        echo '<td>'.($at?esc_html(wp_date('d/m/Y H:i:s',$at)):'—').'</td>';
        echo '<td>'.($expiry?esc_html(wp_date('d/m/Y H:i:s',$expiry)):'—').'</td>';
        echo '<td><span class="psrc-pill '.($protected?'ok':'warning').'">'.($protected?'Prova browser':'Senza prova browser').'</span>'.($otp?' <span class="psrc-pill ok">OTP PSR</span>':' <span class="psrc-pill">Pre-fix / non attestata</span>').'</td></tr>';
    }
    echo '</tbody></table></div>';
    if ($total_sessions > 100) { echo '<p class="psrc-muted">Visualizzate le 100 sessioni piu recenti.</p>'; }
    echo '<p class="psrc-info psrc-alert"><strong>Protezione obbligatoria per tutti gli account:</strong> al primo avvio della versione v3 le vecchie sessioni WordPress sono revocate e ogni utente deve ripetere password e OTP. Durata massima 12 ore; inattività massima 60 minuti. Ogni PC/browser deve eseguire un login distinto. Per emergenze da Plesk: <code>define(&#39;PSR_DEVICE_BINDING_ENABLED&#39;, false);</code> sospende soltanto il secondo cookie, non il controllo sulla sessione/OTP. Due cookie rubati assieme restano riutilizzabili: non è un vincolo hardware.</p>';
    echo '</div>';
    echo '<div class="psrc-card" style="margin-top:18px"><span class="psrc-eyebrow">REGISTRO ACCESSI</span><h2>Ultimi 50 accessi riusciti</h2><p>Il registro storico mostra utente e IP; i dettagli PC/sessione sono disponibili soltanto per i nuovi accessi effettuati con questa versione. La conservazione segue quella degli eventi PSR.</p>';
    $rows=$wpdb->get_results($wpdb->prepare("SELECT * FROM $events WHERE event_type=%s ORDER BY id DESC LIMIT %d", 'login_success', 50));
    echo '<div class="psrc-table-wrap"><table class="psrc-table"><thead><tr><th>Data / ora</th><th>Utente</th><th>IP pubblico</th><th>Esito</th><th>PC / sessione</th></tr></thead><tbody>';
    if (!$rows) { echo '<tr><td colspan="5">Nessun accesso riuscito registrato.</td></tr>'; }
    $owner=get_userdata(1);
    foreach($rows as $r) {
        $highlight=isset($r->username) && $owner instanceof WP_User && (string)$r->username===(string)$owner->user_login;
        echo '<tr'.($highlight?' style="background:#f2faf6"':'').'>';
        echo '<td>'.psr_center_local_date($r->happened_at).'</td><td>'.esc_html((string)$r->username).'</td><td><code>'.esc_html($r->ip?:'—').'</code></td>';
        echo '<td><span class="psrc-pill ok">Accesso riuscito</span></td><td>'.esc_html((string)$r->note).'</td></tr>';
    }
    echo '</tbody></table></div></div>';
}

function psr_center_otp(){
    $u=get_userdata(1);$mail=function_exists('psr_login_email_target')?psr_login_email_target($u):'';
    $pinned=(string)get_option('_psr_login_email_to','');
    echo '<div class="psrc-kpis"><div class="psrc-card"><div class="label">Metodo verifica ID 1</div><div class="value" style="font-size:21px">OTP via email</div><div class="detail">8 cifre · monouso · 5 minuti</div></div><div class="psrc-card"><div class="label">Email destinazione</div><div class="value" style="font-size:17px;overflow-wrap:anywhere">'.esc_html($mail ? (function_exists('psr_login_mask_email_for_display') ? psr_login_mask_email_for_display($mail) : 'INDIRIZZO PROTETTO') : 'NON CONFIGURATA').'</div><div class="detail">'.($pinned?'Vincolata dopo verifica riuscita':'Non ancora vincolata').'</div></div><div class="psrc-card"><div class="label">Protezione login</div><div class="value" style="font-size:21px">Progressiva</div><div class="detail">5 / 10 / 15 / 25 tentativi</div></div></div>';
    echo '<div class="psrc-card"><h2>Politica OTP e anti brute-force</h2><p>Il codice viene spedito solo <strong>dopo che la password è corretta</strong>. Non viene memorizzato in chiaro e non è mostrato nel registro.</p><p><strong>Blocchi:</strong> 5 errori → 10 min · 10 → 1 ora · 15 → 24 ore · 25 → 7 giorni.</p><p class="psrc-info psrc-alert">Verifica SMTP prima degli aggiornamenti. In caso di mancato recapito OTP, l’accesso dell’utente resta negato; il recupero richiede Plesk/WP-CLI, non un pulsante pubblico.</p><p>Il cambio della casella email vincolata non è permesso da qui, per non consentire la deviazione dei codici in caso di sessione compromessa.</p></div>';
}
function psr_center_settings(){
    do_action('psr_center_settings');
    $days=(int)get_option('_psr_center_retention',30);
    echo '<div class="psrc-card"><h2>Conservazione dei registri</h2><p>Per limitare l’esposizione di dati personali, IP e username tentati vengono conservati per un periodo configurabile; gli eventi più vecchi vengono eliminati con manutenzione giornaliera.</p>';
    echo '<form method="post" class="psrc-toolbar">';wp_nonce_field('psr_center_action');
    echo '<input type="hidden" name="psr_center_action" value="retention"><div><label>Conservazione</label><select name="days">';foreach(array(7,14,30,60,90) as $v){echo '<option value="'.$v.'" '.selected($v,$days,false).'>'.$v.' giorni</option>';}echo '</select></div><button class="psrc-btn" type="submit">Salva impostazione</button></form>';
    echo '<p class="psrc-muted">La conservazione riguarda solo PSR, non i log web del server o Wordfence.</p></div>';
    $enabled = function_exists('psr_geo_site_ban_enabled') && psr_geo_site_ban_enabled();
    $forced=defined('PSR_SITE_BAN_ENABLED');
    echo '<div class="psrc-card" style="margin-top:18px"><h2>Blocco completo degli IP bannati</h2><p>Gli IP in blacklist permanente, con ban manuale o con almeno 15 errori non possono aprire le pagine elaborate da WordPress. I blocchi iniziali (5/10 errori) limitano solo il login; i semplici IP esteri possono visitare il sito pubblico.</p>';
    echo '<div class="psrc-info psrc-alert">Per bloccare anche URL statici, file PHP autonomi e web shell serve una regola <strong>Nginx/Apache/WAF a livello Plesk</strong>. Se il traffico arriva da proxy/CDN, verificare prima che <code>REMOTE_ADDR</code> rappresenti l’IP reale.</div>';
    echo '<form method="post" class="psrc-toolbar">';wp_nonce_field('psr_center_action');
    echo '<input type="hidden" name="psr_center_action" value="site_ban"><label><input type="checkbox" name="site_ban_enabled" value="1" '.checked($enabled,true,false).' '.($forced?'disabled':'').'> Blocca la navigazione WordPress agli IP bannati</label>';
    if ($forced) { echo '<p class="psrc-muted">Controllo imposto da PSR_SITE_BAN_ENABLED in wp-config.php.</p>'; }
    else { echo '<button class="psrc-btn" type="submit">Salva protezione sito</button>'; }
    echo '</form><p class="psrc-muted">Ripristino d’emergenza: <code>define(&#39;PSR_SITE_BAN_ENABLED&#39;, false);</code> in wp-config.php, prima del caricamento WordPress.</p></div>';
    echo '<div class="psrc-card" style="margin-top:18px"><h2>Funzionamento e privacy</h2><p><strong>Carico minimo:</strong> scritture nel database quando avvengono eventi di autenticazione o sicurezza; nessuna scansione dei file durante la normale navigazione.</p><p><strong>Visibilità:</strong> menu, esportazione e comandi protetti lato server e accessibili soltanto all’utente WordPress ID 1 con permessi <code>manage_options</code>.</p><p><strong>Autenticazione:</strong> i blocchi restano indipendenti dal pannello; un problema di visualizzazione non disabilita OTP o controllo IP.</p></div>';
}

/** Pannello account soltanto ID 1: una query sugli amministratori e audit già raccolto. */
function psr_center_accounts() {
    global $wpdb;
    list($events)=psr_center_tables();
    echo '<div class="psrc-card"><div class="psrc-section-head"><div><span class="psrc-eyebrow">IDENTITÀ E PRIVILEGI</span><h2>Amministratori del portale</h2><p>Verifica sempre gli amministratori che non riconosci. Il registro non registra le modifiche SQL fatte direttamente da una web shell.</p></div></div>';
    $users=get_users(array('role'=>'administrator','number'=>100,'orderby'=>'ID','order'=>'ASC'));
    echo '<div class="psrc-table-wrap"><table class="psrc-table"><thead><tr><th>ID</th><th>Account</th><th>Registrazione</th><th>Controllo</th></tr></thead><tbody>';
    if (!$users) { echo '<tr><td colspan="4">Nessun amministratore trovato.</td></tr>'; }
    foreach ($users as $user) {
        echo '<tr><td><strong>#'.(int)$user->ID.'</strong></td><td>'.esc_html($user->user_login).'</td><td>'.esc_html($user->user_registered).'</td><td><span class="psrc-pill '.((int)$user->ID===1?'ok':'warning').'">'.'OTP email obbligatorio'.'</span></td></tr>';
    }
    echo '</tbody></table></div><p class="psrc-info psrc-alert">L’OTP email PSR ora protegge tutti gli utenti. La promozione ad amministratore è riservata all’ID 1 con sessione OTP verificata; verifica comunque eventuali account sconosciuti e il corretto recapito delle email.</p></div>';
    echo '<div class="psrc-card" style="margin-top:18px"><h2>Ultimi cambiamenti agli account</h2>';
    $types="'user_created','role_changed','account_email_changed','account_password_changed','user_deleted','admin_role_denied'";
    $rows=$wpdb->get_results("SELECT * FROM $events WHERE event_type IN ($types) ORDER BY id DESC LIMIT 40");
    echo '<div class="psrc-table-wrap"><table class="psrc-table"><thead><tr><th>Data / ora</th><th>Evento</th><th>Account</th><th>IP</th><th>Dettaglio</th></tr></thead><tbody>';
    if (!$rows) { echo '<tr><td colspan="5">Nessuna modifica agli account registrata dopo l’installazione.</td></tr>'; }
    foreach($rows as $r) {
        echo '<tr><td>'.psr_center_local_date($r->happened_at).'</td><td><span class="psrc-pill '.($r->severity==='critical'?'critical':'warning').'">'.esc_html(psr_center_label($r->event_type)).'</span></td><td>'.esc_html($r->username).'</td><td><code>'.esc_html($r->ip).'</code></td><td>'.esc_html($r->note).'</td></tr>';
    }
    echo '</tbody></table></div></div>';
}

function psr_center_trusted() {
    $entries=function_exists('psr_geo_trusted_entries')?psr_geo_trusted_entries():array();
    echo '<div class="psrc-card"><span class="psrc-eyebrow">RETE FIDATA</span><h2>IP fidati con impronta crittografica</h2>';
    echo '<p>Le impronte vengono salvate come <strong>HMAC SHA-256</strong>. Le sedi fidate restano protette dai ban manuali involontari, ma <strong>non aggirano il filtro Italia</strong>: anche questi indirizzi devono risultare italiani. OTP, password, ban automatici e blacklist restano obbligatori. Gli eventi di sicurezza possono registrare gli IP in chiaro.</p>';
    echo '<div class="psrc-table-wrap"><table class="psrc-table"><thead><tr><th>Nome</th><th>Impronta (abbreviata)</th><th>Inserito</th><th>Revoca</th></tr></thead><tbody>';
    if (!$entries) { echo '<tr><td colspan="4">Nessun IP fidato configurato.</td></tr>'; }
    foreach($entries as $digest=>$entry){
        if (!is_string($digest) || !preg_match('/^[a-f0-9]{64}$/D',$digest)) { continue; }
        $label=is_array($entry)?(string)($entry['label']??'IP fidato'):'IP fidato';
        $date=is_array($entry)&&!empty($entry['added'])?wp_date('d/m/Y H:i',(int)$entry['added']):'—';
        echo '<tr><td>'.esc_html($label).'</td><td><code>'.esc_html(substr($digest,0,12)).'…</code></td><td>'.esc_html($date).'</td><td><form method="post">';
        wp_nonce_field('psr_center_action');
        echo '<input type="hidden" name="psr_center_action" value="trusted_remove"><input type="hidden" name="digest" value="'.esc_attr($digest).'"><button class="psrc-btn secondary" onclick="return confirm(&quot;Revocare questa sede fidata?&quot;)" type="submit">Revoca</button></form></td></tr>';
    }
    echo '</tbody></table></div></div>';
    echo '<div class="psrc-card" style="margin-top:18px"><h2>Aggiungi una sede fidata</h2><form method="post" class="psrc-toolbar">';
    wp_nonce_field('psr_center_action');
    echo '<input type="hidden" name="psr_center_action" value="trusted_add"><div><label>Nome riconoscibile</label><input type="text" name="label" maxlength="64" placeholder="Ufficio comunale" required></div><div><label>IP pubblico</label><input type="text" name="ip" placeholder="IPv4 / IPv6" required></div><button type="submit" class="psrc-btn">Salva impronta IP</button></form><p class="psrc-muted">Dopo il salvataggio l’IP non sarà più visibile. Se cambia, revoca la vecchia impronta e aggiungi il nuovo. Non inserire IP di VPN/proxy condivisi con utenti sconosciuti.</p></div>';
}
