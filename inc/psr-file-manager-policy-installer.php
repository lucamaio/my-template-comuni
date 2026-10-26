<?php
/** Installazione esplicita del blocco WP File Manager; nessuna scrittura al caricamento del tema. */
if (!defined('ABSPATH')) { exit; }

function psr_fmp_install_authorized() {
    return (int)get_current_user_id() === 1 && current_user_can('manage_options')
        && function_exists('psr_login_session_verified') && psr_login_session_verified(1);
}
function psr_fmp_payload_path() {
    return get_template_directory() . '/strumenti/mu-plugins/psr-file-manager-policy.php';
}
function psr_fmp_installed() {
    $source = psr_fmp_payload_path();
    $target = WPMU_PLUGIN_DIR . '/psr-file-manager-policy.php';
    return is_readable($source) && is_readable($target) && !is_link($target)
        && hash_file('sha256', $source) === hash_file('sha256', $target);
}
function psr_fmp_install_mu() {
    if (!psr_fmp_install_authorized()) { return new WP_Error('psr_fmp_forbidden', 'Operazione riservata a ID 1 con sessione OTP verificata.'); }
    $source = psr_fmp_payload_path();
    $directory = WPMU_PLUGIN_DIR;
    $target = $directory . '/psr-file-manager-policy.php';
    if (!is_readable($source) || is_link($directory) || is_link($target)) {
        return new WP_Error('psr_fmp_path', 'Payload mancante o percorso non consentito.');
    }
    if (is_file($target) && (!is_readable($target)
        || strpos(file_get_contents($target), 'PSR-FILE-MANAGER-POLICY') === false)) {
        return new WP_Error('psr_fmp_existing', 'File di destinazione non riconosciuto: nessuna sovrascrittura.');
    }
    if (!is_dir($directory) && !wp_mkdir_p($directory)) { return new WP_Error('psr_fmp_directory', 'Directory MU-plugin non scrivibile.'); }
    if (!is_writable($directory)) { return new WP_Error('psr_fmp_permissions', 'Directory MU-plugin non scrivibile.'); }
    if (psr_fmp_installed()) { return true; }
    $temporary = tempnam($directory, '.psr-fmp-');
    if (!$temporary) { return new WP_Error('psr_fmp_temporary', 'File temporaneo non disponibile.'); }
    if (realpath(dirname($temporary)) !== realpath($directory)) {
        @unlink($temporary); // Soltanto il file temporaneo appena creato.
        return new WP_Error('psr_fmp_temporary_path', 'File temporaneo fuori dalla directory prevista.');
    }
    $body = file_get_contents($source);
    $written = is_string($body) && file_put_contents($temporary, $body, LOCK_EX) === strlen($body);
    if (!$written || hash_file('sha256', $temporary) !== hash_file('sha256', $source)) {
        @unlink($temporary);
        return new WP_Error('psr_fmp_write', 'Copia non riuscita: destinazione conservata.');
    }
    @chmod($temporary, 0644);
    if (!@rename($temporary, $target)) {
        @unlink($temporary);
        return new WP_Error('psr_fmp_rename', 'Installazione atomica non riuscita.');
    }
    return psr_fmp_installed() ? true : new WP_Error('psr_fmp_verify', 'Verifica finale non riuscita.');
}
function psr_fmp_install_handler() {
    if (!psr_fmp_install_authorized()) { wp_die('Non autorizzato.', '', array('response'=>403)); }
    check_admin_referer('psr_fmp_install');
    $result = psr_fmp_install_mu();
    if (function_exists('psr_center_log_event')) {
        psr_center_log_event('security_setting_changed', is_wp_error($result) ? 'warning' : 'info',
            function_exists('psr_login_ip') ? psr_login_ip() : '', '', '', 'dashboard',
            is_wp_error($result) ? 'Blocco WP File Manager non installato: ' . $result->get_error_code() : 'Blocco WP File Manager installato come MU-plugin');
    }
    wp_safe_redirect(add_query_arg(array('page'=>'psr-security-center','tab'=>'settings',
        'psr_fmp_result'=>is_wp_error($result) ? 'failed' : 'installed'), admin_url('admin.php')));
    exit;
}
add_action('admin_post_psr_fmp_install', 'psr_fmp_install_handler');

function psr_fmp_render_setting() {
    if (!psr_fmp_install_authorized()) { return; }
    $installed = psr_fmp_installed();
    echo '<section class="psrc-card" style="margin-bottom:18px"><h2>Blocco WP File Manager</h2>';
    echo '<p>Stato: <strong>' . ($installed ? 'Installato' : 'Da installare o aggiornare') . '</strong>.</p>';
    echo '<p>Blocca WP File Manager gratuito e Pro prima del caricamento dei plugin. Libreria Media e upload nativi restano disponibili. Non elimina file e non modifica OTP o sessioni.</p>';
    echo '<p class="psrc-info psrc-alert">Il solo aggiornamento GitHub non attiva questo blocco. Il pulsante installa un singolo MU-plugin sul sito corrente; la protezione opera dalla richiesta successiva. I PHP autonomi richiedono controlli server separati.</p>';
    if (isset($_GET['psr_fmp_result']) && $_GET['psr_fmp_result'] === 'failed') {
        echo '<p role="alert">Installazione non riuscita. Verificare payload e permessi della directory MU-plugin tramite Plesk.</p>';
    }
    if (!$installed) {
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        wp_nonce_field('psr_fmp_install');
        echo '<input type="hidden" name="action" value="psr_fmp_install"><button type="submit" class="psrc-btn">Installa blocco WP File Manager su questo sito</button></form>';
    }
    echo '</section>';
}
add_action('psr_center_settings', 'psr_fmp_render_setting');
