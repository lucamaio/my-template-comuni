<?php
/** Primitive atomiche per autenticazione. Nessuna tabella o servizio esterno aggiunto. */
if (!defined('ABSPATH')) { exit; }

function psr_auth_lock($resource, $wait_ms = 800) {
    global $wpdb;
    $key = '_psr_auth_lock_' . hash('sha256', $resource);
    $value = (time() + 600) . ':' . bin2hex(random_bytes(16));
    $end = microtime(true) + max(0, $wait_ms) / 1000;
    do {
        $inserted = $wpdb->query($wpdb->prepare("INSERT IGNORE INTO $wpdb->options (option_name,option_value,autoload) VALUES (%s,%s,'no')", $key, $value));
        if ($inserted === 1) {
            $GLOBALS['psr_auth_locks'][$key] = $value;
            return array($key, $value);
        }
        if ($inserted === false) { return false; }
        $old = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM $wpdb->options WHERE option_name=%s", $key));
        if (is_string($old) && (int)$old < time()) {
            $wpdb->query($wpdb->prepare("DELETE FROM $wpdb->options WHERE option_name=%s AND option_value=%s", $key, $old));
            continue;
        }
        if (microtime(true) >= $end) { break; }
        usleep(20000);
    } while (true);
    return false;
}
function psr_auth_unlock($lock) {
    if (!is_array($lock)) { return; }
    global $wpdb;
    $wpdb->query($wpdb->prepare("DELETE FROM $wpdb->options WHERE option_name=%s AND option_value=%s", $lock[0], $lock[1]));
    unset($GLOBALS['psr_auth_locks'][$lock[0]]);
}
function psr_auth_unlock_all() {
    foreach (($GLOBALS['psr_auth_locks'] ?? array()) as $key=>$value) { psr_auth_unlock(array($key,$value)); }
}
register_shutdown_function('psr_auth_unlock_all');
function psr_auth_release_password_lock() {
    psr_auth_unlock($GLOBALS['psr_auth_password_lock']??false);
    psr_auth_unlock($GLOBALS['psr_auth_capacity_lock']??false);
    unset($GLOBALS['psr_auth_password_lock'], $GLOBALS['psr_auth_password_busy'], $GLOBALS['psr_auth_capacity_lock']);
}
add_action('wp_login_failed', 'psr_auth_release_password_lock', 20);
add_action('wp_login', 'psr_auth_release_password_lock', 40);
function psr_auth_capacity() {
    $maximum=defined('PSR_AUTH_MAX_PARALLEL') ? min(64,max(1,(int)PSR_AUTH_MAX_PARALLEL)) : 16;
    $start=random_int(0,$maximum-1);
    // Pochi tentativi e nessuna attesa, anche se tutti gli slot sono occupati.
    for($i=0;$i<min(4,$maximum);$i++) {
        $lock=psr_auth_lock('capacity|' . (($start+$i)%$maximum),0);
        if ($lock) { return $lock; }
    }
    return false;
}
function psr_auth_before_password_hash($user, $password) {
    if (is_wp_error($user)) { return $user; }
    $error=psr_login_access_error();
    return is_wp_error($error) ? $error : $user;
}
add_filter('wp_authenticate_user', 'psr_auth_before_password_hash', PHP_INT_MAX, 2);

/** Rilegge i transient dopo il lock, senza copie locali gia' lette da altri worker. */
function psr_auth_fresh_transient($key) {
    if (wp_using_ext_object_cache()) { return wp_cache_get($key, 'transient', true); }
    wp_cache_delete('_transient_' . $key, 'options');
    wp_cache_delete('_transient_timeout_' . $key, 'options');
    wp_cache_delete('notoptions', 'options');
    return get_transient($key);
}
function psr_auth_claim_ticket($ticket, $deadline) {
    global $wpdb;
    $key = '_psr_otp_used_' . hash('sha256', $ticket);
    $claimed = $wpdb->query($wpdb->prepare("INSERT IGNORE INTO $wpdb->options (option_name,option_value,autoload) VALUES (%s,%s,'no')", $key, (string)max(time()+600,(int)$deadline+60))) === 1;
    if ($claimed && !wp_next_scheduled('psr_auth_cleanup_tickets')) { wp_schedule_single_event(time()+HOUR_IN_SECONDS, 'psr_auth_cleanup_tickets'); }
    return $claimed;
}
function psr_auth_cleanup_tickets() {
    global $wpdb;
    $wpdb->query($wpdb->prepare("DELETE FROM $wpdb->options WHERE option_name LIKE %s AND CAST(option_value AS DECIMAL(20,0)) < %d", $wpdb->esc_like('_psr_otp_used_').'%', time()));
    if ($wpdb->get_var($wpdb->prepare("SELECT option_id FROM $wpdb->options WHERE option_name LIKE %s LIMIT 1",$wpdb->esc_like('_psr_otp_used_').'%'))
        && !wp_next_scheduled('psr_auth_cleanup_tickets')) { wp_schedule_single_event(time()+HOUR_IN_SECONDS,'psr_auth_cleanup_tickets'); }
}
add_action('psr_auth_cleanup_tickets', 'psr_auth_cleanup_tickets');

/** Recupero email solo dalla console del server, con flag esplicito in wp-config.php. */
function psr_auth_email_maintenance() {
    return defined('WP_CLI') && WP_CLI && defined('PSR_ALLOW_ADMIN_EMAIL_RECOVERY') && PSR_ALLOW_ADMIN_EMAIL_RECOVERY;
}
function psr_auth_admin_email() {
    if (defined('PSR_ADMIN_EMAIL')) { return is_string(PSR_ADMIN_EMAIL) && is_email(PSR_ADMIN_EMAIL) ? PSR_ADMIN_EMAIL : ''; }
    global $wpdb;
    $pinned = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM $wpdb->options WHERE option_name=%s", '_psr_login_email_to'));
    if (is_string($pinned) && is_email($pinned)) { return $pinned; }
    $email = $wpdb->get_var("SELECT user_email FROM $wpdb->users WHERE ID=1");
    return is_string($email) && is_email($email) ? $email : '';
}
function psr_auth_protect_admin_data($data, $update, $user_id, $userdata = array()) {
    if (!$update || (int)$user_id !== 1 || psr_auth_email_maintenance()) { return $data; }
    $email = psr_auth_admin_email();
    if ($email !== '') { $data['user_email'] = $email; }
    // Difesa anche per integrazioni che chiamano wp_update_user senza verificare
    // edit_user. Il reset nativo e la manutenzione CLI continuano a funzionare.
    if (!(defined('WP_CLI') && WP_CLI) && !psr_login_privilege_authorized()) {
        global $wpdb;
        $previous_hash=$wpdb->get_var("SELECT user_pass FROM $wpdb->users WHERE ID=1");
        if (is_string($previous_hash) && $previous_hash!=='') { $data['user_pass']=$previous_hash; }
    }
    return $data;
}
add_filter('wp_pre_insert_user_data', 'psr_auth_protect_admin_data', PHP_INT_MAX, 4);
function psr_auth_protect_pin($value) {
    if (psr_auth_email_maintenance()) { return $value; }
    if (!empty($GLOBALS['psr_auth_restore_email']) && is_email($GLOBALS['psr_auth_restore_email'])) { return $GLOBALS['psr_auth_restore_email']; }
    $email = psr_auth_admin_email();
    return $email !== '' ? $email : '';
}
add_filter('sanitize_option__psr_login_email_to', 'psr_auth_protect_pin', PHP_INT_MAX);
function psr_auth_before_delete_pin($option) {
    if ($option === '_psr_login_email_to' && !psr_auth_email_maintenance()) { $GLOBALS['psr_auth_restore_email'] = psr_auth_admin_email(); }
}
function psr_auth_after_delete_pin($option) {
    if ($option !== '_psr_login_email_to' || empty($GLOBALS['psr_auth_restore_email'])) { return; }
    $email=$GLOBALS['psr_auth_restore_email'];
    add_option($option, $email, '', false);
    unset($GLOBALS['psr_auth_restore_email']);
}
add_action('delete_option', 'psr_auth_before_delete_pin', PHP_INT_MAX);
add_action('deleted_option', 'psr_auth_after_delete_pin', PHP_INT_MAX);

/** Quote recupero password separate dai ban login: non bloccano un account bersagliato. */
function psr_auth_recovery_errors($errors, $user) {
    $access = psr_login_access_error();
    if (is_wp_error($access)) { return $access; }
    $ip=psr_login_ip();
    if ($ip === '') { $errors->add('psr_reset_denied','Richiesta non disponibile.'); return $errors; }
    $keys=array('_psr_reset_ip_'.hash_hmac('sha256',$ip,wp_salt('auth'))=>10);
    if ($user instanceof WP_User) { $keys['_psr_reset_user_'.(int)$user->ID]=3; }
    $locks=array();
    try {
        foreach ($keys as $key=>$maximum) {
            $lock=psr_auth_lock($key);
            if (!$lock) { $errors->add('psr_reset_busy','Richiesta non disponibile.'); return $errors; }
            $locks[]=$lock;
        }
        $states=array(); $now=time();
        foreach ($keys as $key=>$maximum) {
            $state=psr_auth_fresh_transient($key);
            if (!is_array($state) || $now-(int)($state['start']??0)>=HOUR_IN_SECONDS) { $state=array('start'=>$now,'last'=>0,'n'=>0); }
            if ((int)$state['n'] >= $maximum || ($maximum===3 && (int)$state['last']>$now-60)) {
                $errors->add('psr_reset_throttled','Richiesta non disponibile.'); return $errors;
            }
            $state['n']++; $state['last']=$now; $states[$key]=$state;
        }
        foreach ($states as $key=>$state) {
            if (!set_transient($key,$state,HOUR_IN_SECONDS)) { $errors->add('psr_reset_storage','Richiesta non disponibile.'); return $errors; }
        }
    } finally { foreach(array_reverse($locks) as $lock) { psr_auth_unlock($lock); } }
    return $errors;
}
add_filter('lostpassword_errors', 'psr_auth_recovery_errors', PHP_INT_MAX, 2);
function psr_auth_lostpassword_form() { wp_nonce_field('psr_password_recovery','psr_reset_nonce'); }
add_action('lostpassword_form', 'psr_auth_lostpassword_form');
function psr_auth_lostpassword_submit() {
    if (($_SERVER['REQUEST_METHOD']??'GET') !== 'POST') { return; }
    $nonce=isset($_POST['psr_reset_nonce']) && is_string($_POST['psr_reset_nonce']) ? wp_unslash($_POST['psr_reset_nonce']) : '';
    if (!wp_verify_nonce($nonce,'psr_password_recovery')) { wp_die('Richiesta non valida. Ricarica il modulo.', '', array('response'=>403)); }
    retrieve_password(); // Mantiene generazione, hash e invio nativi; risposta pubblica uniforme.
    wp_safe_redirect(add_query_arg('checkemail','confirm',wp_login_url())); exit;
}
add_action('login_form_lostpassword', 'psr_auth_lostpassword_submit', 1);
add_action('login_form_retrievepassword', 'psr_auth_lostpassword_submit', 1);
function psr_auth_reset_expiry($seconds) { return min(max(1,(int)$seconds),30*MINUTE_IN_SECONDS); }
add_filter('password_reset_expiration', 'psr_auth_reset_expiry', PHP_INT_MAX);
function psr_auth_reset_strength($errors, $user) {
    if (!($user instanceof WP_User) || !isset($_POST['pass1']) || $_POST['pass1']==='') { return; }
    if (!is_string($_POST['pass1']) || strlen(wp_unslash($_POST['pass1']))<12 || strlen(wp_unslash($_POST['pass1']))>4096) {
        $errors->add('psr_password_length','Usa una password o frase segreta di almeno 12 caratteri (massimo 4096 byte).');
    }
}
add_action('validate_password_reset', 'psr_auth_reset_strength', PHP_INT_MAX, 2);
function psr_auth_recovery_mail($mail, $key, $login, $user) {
    if ($user instanceof WP_User && (int)$user->ID===1) { $mail['to']=psr_auth_admin_email(); }
    $GLOBALS['psr_auth_sensitive_mail']=$mail;
    return $mail;
}
add_filter('retrieve_password_notification_email', 'psr_auth_recovery_mail', PHP_INT_MAX, 4);
function psr_auth_mail_arguments($mail) {
    $safe=$GLOBALS['psr_auth_sensitive_mail']??null;
    if (!is_array($safe)) { return $mail; }
    foreach(array('to','subject','message','headers') as $key) { $mail[$key]=$safe[$key]??''; }
    $mail['attachments']=array(); $mail['embeds']=array();
    return $mail;
}
add_filter('wp_mail', 'psr_auth_mail_arguments', PHP_INT_MAX);
function psr_auth_mail_transport($mailer) {
    if (empty($GLOBALS['psr_auth_sensitive_mail'])) { return; }
    $mail=$GLOBALS['psr_auth_sensitive_mail'];
    // Rimuove anche eventuali destinatari CC/BCC aggiunti da filtri SMTP precedenti.
    $mailer->clearAllRecipients();
    if (!$mailer->addAddress($mail['to'])) { throw new \PHPMailer\PHPMailer\Exception('Destinatario di sicurezza non valido.'); }
    $mailer->Subject=$mail['subject']; $mailer->Body=$mail['message']; $mailer->AltBody='';
    $mailer->isHTML(false);
    $mailer->clearAttachments();
    if ($mailer->Mailer === 'smtp') {
        $mailer->SMTPAutoTLS=true;
        $mailer->SMTPSecure=(int)$mailer->Port===465 ? 'ssl' : 'tls';
        $mailer->SMTPOptions['ssl']=array('verify_peer'=>true,'verify_peer_name'=>true,'allow_self_signed'=>false);
        $mailer->Timeout=min(max(1,(int)$mailer->Timeout),15);
    }
}
add_action('phpmailer_init', 'psr_auth_mail_transport', PHP_INT_MAX);
function psr_auth_mail_done($unused=null) { unset($GLOBALS['psr_auth_sensitive_mail']); }
function psr_auth_mail_short_circuit($result) { if ($result!==null) { psr_auth_mail_done(); } return $result; }
add_action('wp_mail_succeeded', 'psr_auth_mail_done', PHP_INT_MAX);
add_action('wp_mail_failed', 'psr_auth_mail_done', PHP_INT_MAX);
add_filter('pre_wp_mail', 'psr_auth_mail_short_circuit', PHP_INT_MAX);
function psr_auth_login_headers() {
    nocache_headers();
    if (!headers_sent()) {
        header('Referrer-Policy: no-referrer'); header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN'); header("Content-Security-Policy: frame-ancestors 'self'");
    }
}
add_action('login_init', 'psr_auth_login_headers', PHP_INT_MAX);

/** Blocca aumenti di privilegi anche tramite user_meta e ruoli amministrativi personalizzati. */
function psr_auth_sensitive_caps($caps) {
    $sensitive=array('manage_options','promote_users','edit_users','activate_plugins','install_plugins','edit_plugins','update_core');
    $found=array();
    foreach ((array)$caps as $name=>$enabled) {
        if (!$enabled) { continue; }
        if (in_array($name,$sensitive,true)) { $found[$name]=true; }
        $role=get_role($name);
        if ($role) { foreach($sensitive as $cap) { if(!empty($role->capabilities[$cap])) { $found[$cap]=true; } } }
    }
    return $found;
}
function psr_auth_guard_cap_metadata($check, $user_id, $meta_key, $value, $previous='') {
    if ($check!==null || !preg_match('/_capabilities$/D',(string)$meta_key) || !psr_login_admin_guard_enabled()
        || (defined('WP_CLI') && WP_CLI)) { return $check; }
    $old=psr_auth_sensitive_caps(get_user_meta((int)$user_id,$meta_key,true));
    $new=psr_auth_sensitive_caps($value);
    if (((int)$user_id===1 && array_diff_key($old,$new))
        || (array_diff_key($new,$old) && !psr_login_privilege_authorized())) { return false; }
    return $check;
}
add_filter('add_user_metadata', 'psr_auth_guard_cap_metadata', PHP_INT_MAX, 5);
add_filter('update_user_metadata', 'psr_auth_guard_cap_metadata', PHP_INT_MAX, 5);
function psr_auth_guard_delete_caps($check, $user_id, $meta_key, $value, $delete_all) {
    if ($check===null && psr_login_admin_guard_enabled() && !(defined('WP_CLI') && WP_CLI)
        && ((int)$user_id===1 || $delete_all) && preg_match('/_capabilities$/D',(string)$meta_key)) { return false; }
    return $check;
}
add_filter('delete_user_metadata', 'psr_auth_guard_delete_caps', PHP_INT_MAX, 5);
