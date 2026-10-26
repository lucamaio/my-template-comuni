<?php
/**
 * PSR Security Guard — contenimento degli indicatori segnalati dal proprietario.
 * Integra la protezione del tema; NON protegge richieste dirette a file PHP.
 * I file PHP isolati vengono spostati FUORI dalla document root.
 * Eccezione esplicita: wpsecurity.log viene eliminato nei percorsi del sito.
 */
if (!defined('ABSPATH')) { exit; }

function psr_guard_signatures() {
    return array(
        '618493861791064b9f394f06af7e6d538fc46e25555fc398af8e5d6a1f20a483' => 'translanugos.php',
        '0aeadb135f09028c36e48b8a4bd1098c32b958bfc30a1d0786e96f16b81c8a8d' => 'transfraktur.php',
        '0b97745a9a7218923dc50844cde3bdce28d1efe347d04c3b091e80452ba2871d' => 'overcynophi.php',
        '079b0e4ff820394263e724cd6995dac8315c11a68d28d578c5ff9643703b63b1' => 'aciesest.php',
        'e68659bdf7a18ae32a27faef38ac972b3f17429bb3971de4ac01fda514d401b3' => 'acerbatesed.php',
        '65958f829ae60eccbec3d7de8812ac3dbc74d38ef53f217b0a0a1883e343deec' => 'microbearers.php',
        '3b2993596835e2d985a3147e9ce21fe655851f3c303da58ccaf17f9fb05c3358' => 'protomunsif.php',
        '20f91dbe6ded4b46c57f054608379b0e582c4e190277212923c0a37d89b562a7' => 'toddlingness.php',
        '57f33eb6ae24f9f52c9fa21ae43ddcc90a9d06bc5912f70fe9cb586760becbf3' => 'prequiche.php',
        'dd30dbc4c6d6e94cbf09368188479e6da73f4e5dcf6011f25b66a658429ca4ee' => 'MU recovery channel wp-link-helper (due copie con nomi diversi)',
        'd1e732a31072919f06448e0b0f65ed15a6df4fba7791d46dcd5d9069621798e5' => 'b.zip: includes/class-wlh-adopt.php',
        '96604128c7ffcb935807d039a6eef39177b5164b1231e1513e57a49c8965432f' => 'b.zip: includes/class-wlh-botstat.php',
        '5485bbad48b9727b446e5fadaf73e3d19bcdb95287f0901d52c5ec6517b07dc5' => 'b.zip: includes/class-wlh-cache.php',
        '78cae7a6c3cb4c559f3aeffe97f48c7fdcb4a9bfc8a68add9dc23d9374c261d7' => 'b.zip: includes/class-wlh-check.php',
        'b627f7819dc50b0782b33a1106bea5cc514e9c43e4c3a7d6c85c95be7176735c' => 'b.zip: includes/class-wlh-clean.php',
        '66d8feb78c8e65ab3884fe86a047e7dbd3692a3eb72a619268501982bcca8141' => 'b.zip: includes/class-wlh-client.php',
        '7c411ef757e2cb6e4435a503ececa100131b3ea8bb4bd34a5cc52b8153ca598a' => 'b.zip: includes/class-wlh-cron.php',
        '3583d7b82491100ba1472c9d4167c63878f22d3a723889a7495e67a7c1a57cf7' => 'b.zip: includes/class-wlh-deploy.php',
        '36b64f7a633f175d757b73f04affa038054366d2bb226db1abb5f17b7a5364b7' => 'b.zip: includes/class-wlh-neighbors.php',
        '447b17c53dd2054dbb05becdec4f1014d1ba48eb8623ebe1f02aa4320bfbba3e' => 'b.zip: includes/class-wlh-recovery-installer.php',
        '6b39cbe007d31a9d57fe788758c5e32781166f53fb3024ee1acf20c94b298406' => 'b.zip: includes/class-wlh-render.php',
        '897b6dea4d00f48cdab8aaa37fb48b4b3ff65df97bc241d79d2adfd30f7c9c54' => 'b.zip: includes/class-wlh-static.php',
        'bf6d62476e2b9f323f8843f0b6c9d12f117884de5b8efde874cbde27fb18af2c' => 'b.zip: includes/class-wlh-vault.php',
        '6f7fc24f06ef52c5fb24b4c35d76ba7e4efe7720ae93e123d951ea4702aa2996' => 'b.zip: includes/class-wlh-wchinstall.php',
        '8c186e2dbc4ae519e2425f2a7b0e68905bcfb0ae527759c357040dabfd36f020' => 'b.zip: mu/wlh-recovery.php',
        'a0c9d8801fa62537216491d57f2efbf7acce102bf23cb6e5056e6e23b3b74717' => 'b.zip: wlh-config.php',
        '5fc7a12ad306b66c51fad97f5619c3f6c61368a97ec352b2609239469bcb1b86' => 'b.zip: wp-link-helper.php',
        '8b8e098dbb01e5d8125a61b3624be565639a8a5028054e0caf0a7ec4da2f935e' => 'dismutation.php',
        '196224d7fd65aec3ecd1dc1bd7352d8c4b6d03d477a9d39cbbc45bac53cad702' => 'protosnitchy.php',
        'd8a06fe5c14bf71b24d9f8218a3a44bf6d8a29bd559b5c0fd65ab1f2eb431765' => '8p8d7zr0.php',
        '72f150a08c72fc704c702cb3ef209b23ef4964082e3067703a43e3b44b7d499d' => 'macroutter.php',
    );
}

function psr_guard_matches_slakersest($file) {
    if (!is_file($file) || is_link($file) || @filesize($file) > 1024 * 1024) { return false; }
    $contents = @file_get_contents($file);
    if (!is_string($contents)) { return false; }
    foreach (array('pelycogram','autosportswo','subperso','opacity:.03','function abu(','function dulbe(') as $marker) {
        if (strpos($contents, $marker) === false) { return false; }
    }
    return true;
}

/** Firma aggiuntiva della web shell overcoqu segnalata, senza esecuzione del PHP. */
function psr_guard_matches_overcoqu($file) {
    if (!is_file($file) || is_link($file)) { return false; }
    $bytes = @filesize($file);
    if ($bytes === false || $bytes < 1500 || $bytes > 1024 * 1024) { return false; }
    $body = @file_get_contents($file);
    if (!is_string($body)) { return false; }
    foreach (array('fluigrammeed', 'antiammono', 'amphoricit', 'opacity:.03',
        'function zxirqi(', 'function ztsi(', 'session_start(', '$_FILES[',
        'fopen(', 'fwrite(', 'rename(', 'unlink(') as $marker) {
        if (strpos($body, $marker) === false) { return false; }
    }
    return true;
}

/**
 * Famiglia delle web shell PHP camuffate con un login a opacita' 3%.
 * I nuovi nomi indicati dal proprietario, SENZA campioni allegati, non sono
 * prova sufficiente per una rimozione: isolare soltanto se il contenuto
 * presenta contemporaneamente queste caratteristiche molto specifiche.
 * Non esegue, non include e non valuta codice trovato sul disco.
 */
function psr_guard_matches_hidden_filemanager_family($file) {
    if (!is_file($file) || is_link($file)) { return false; }
    $bytes = @filesize($file);
    if ($bytes === false || $bytes < 1500 || $bytes > 1024 * 1024) { return false; }
    $body = @file_get_contents($file);
    if (!is_string($body)) { return false; }
    $markers = array('opacity:.03', 'session_start(', '$_SESSION[',
        '$_FILES[', 'scandir(', 'rawurldecode(', 'DIRECTORY_SEPARATOR',
        'htmlspecialchars(', 'fopen(', 'fwrite(', 'rename(', 'unlink(');
    foreach ($markers as $marker) {
        if (strpos($body, $marker) === false) { return false; }
    }
    // Questi campioni sono volutamente riempiti con istruzioni inattive.
    return substr_count($body, 'if(false)') >= 4;
}

/** I nuovi indicatori vengono trattati come sospetti finche' il contenuto non e' verificato. */
function psr_guard_new_shell_names() {
    return array('underpenup.php','paltererness.php','semibuxeous.php','transfraktur.php','translanugos.php','translanugoss.php','ultraruruing.php','microbearers.php',
        'morselledism.php','misemace.php','protomunsif.php','slakersest.php','overcoqu.php',
        'overcynophi.php','aciesest.php','acerbatesed.php',
        'wp-link-helper.php','wlh-recovery.php','wlh-config.php',
        'dismutation.php','protosnitchy.php','8p8d7zr0.php','macroutter.php');
}

/**
 * Firma stretta per le copie abusive del file manager 4.8 con accesso disattivato.
 * Viene usata SOLO per i nomi sospetti esplicitamente segnalati, per evitare
 * falsi positivi su normali plugin e strumenti amministrativi.
 */
function psr_guard_matches_unprotected_filemanager($file) {
    if (!is_file($file) || is_link($file)) { return false; }
    $bytes = @filesize($file);
    if ($bytes === false || $bytes < 10000 || $bytes > 1024 * 1024) { return false; }
    $body = @file_get_contents($file);
    if (!is_string($body)) { return false; }
    foreach (array('$dauth = false;', '$global_readonly = false;',
        '$edit_files = true;', '$ip_ruleset = \'OFF\';',
        'FM_USE_AUTH', 'FM_ROOT_PATH', 'fm_rdelete(') as $marker) {
        if (strpos($body, $marker) === false) { return false; }
    }
    return true;
}


/** La minuscola webshell comandi non contiene la firma del file manager. */
function psr_guard_matches_command_shell($file) {
    if (!is_file($file) || is_link($file)) { return false; }
    $bytes = @filesize($file);
    if ($bytes === false || $bytes < 60 || $bytes > 8192) { return false; }
    $body = @file_get_contents($file);
    if (!is_string($body) || strpos($body, '<?php') === false) { return false; }
    // Richiede l'uso diretto del parametro HTTP come comando di shell.
    return (bool) preg_match('~\bshell_exec\s*\(\s*\$_(?:REQUEST|GET|POST)\s*\[\s*[\'"](?:cmd|command|exec)[\'"]\s*\]~i', $body);
}

/**
 * Firma comportamentale stretta del canale di persistenza MU confermato:
 * NON basata sul nome casuale wp-<hex>.php, che da solo non prova nulla.
 * Applicabile soltanto ai PHP direttamente dentro mu-plugins.
 */
function psr_guard_matches_mu_recovery($file) {
    if (!is_file($file) || is_link($file)) { return false; }
    $bytes = @filesize($file);
    if ($bytes === false || $bytes > 1024 * 1024) { return false; }
    $body = @file_get_contents($file);
    if (!is_string($body)) { return false; }
    foreach (array('wprc_reinstall(', 'wprc_reclaim(', 'wprc_reactivate(',
        'wprc_optname(', 'WPRC_SLUG', 'wprc_plugin_dir(') as $marker) {
        if (strpos($body, $marker) === false) { return false; }
    }
    return true;
}

/** Blocca le chiamate tramite WordPress HTTP API al C2 rilevato nei campioni.
 * Non è un firewall: non intercetta chiamate cURL/stream eseguite direttamente.
 */
function psr_guard_prevent_known_egress($preempt, $args, $url) {
    if (defined('PSR_GUARD_BLOCK_KNOWN_EGRESS') && !PSR_GUARD_BLOCK_KNOWN_EGRESS) { return $preempt; }
    if ($preempt !== false || !is_string($url)) { return $preempt; }
    $host = parse_url($url, PHP_URL_HOST);
    if (!is_string($host)) { return $preempt; }
    $host = strtolower(rtrim($host, '.'));
    if ($host === 'cachelayer.top' || substr($host, -strlen('.cachelayer.top')) === '.cachelayer.top') {
        return new WP_Error('psr_guard_known_host_blocked', 'Destinazione remota non autorizzata bloccata dal controllo di sicurezza.');
    }
    return $preempt;
}
add_filter('pre_http_request', 'psr_guard_prevent_known_egress', 1, 3);

function psr_guard_path_within($child, $parent) {
    return $child === $parent || strpos($child, rtrim($parent, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR) === 0;
}

/**
 * Isola i file fuori dal web. Fail closed se la cartella sicura non è disponibile.
 * Si può impostare PSR_GUARD_QUARANTINE_BASE in wp-config.php per Plesk.
 */
function psr_guard_quarantine_base() {
    $docroot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
    if (!$docroot) { return false; }
    $base = defined('PSR_GUARD_QUARANTINE_BASE') ? PSR_GUARD_QUARANTINE_BASE : dirname($docroot) . DIRECTORY_SEPARATOR . '.psr-security-quarantine';
    if (!is_string($base) || $base === '' || (substr($base, 0, 1) !== DIRECTORY_SEPARATOR)) { return false; }
    if (is_link($base)) { return false; }
    if (!is_dir($base) && !@mkdir($base, 0700, true)) { return false; }
    $real = realpath($base);
    if (!$real || psr_guard_path_within($real, $docroot)) { return false; }
    // Non riporre mai la quarantena dentro temi, plugin o upload WordPress.
    $content = realpath(WP_CONTENT_DIR);
    if ($content && psr_guard_path_within($real, $content)) { return false; }
    @chmod($real, 0700);
    return is_writable($real) ? $real : false;
}

/** Quarantena atomica quando possibile, con nome non eseguibile. */
function psr_guard_isolate($source, $reason, &$events) {
    if (!file_exists($source) || is_link($source)) { return false; }
    $qbase = psr_guard_quarantine_base();
    if (!$qbase) {
        $events[] = 'NON ISOLATO (quarantena fuori web non disponibile): ' . $source;
        return false;
    }
    $isdir = is_dir($source);
    $name = preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($source));
    $suffix = $isdir ? '.folder.quarantined' : '.file.quarantined';
    try { $token = bin2hex(random_bytes(6)); } catch (Throwable $e) { return false; }
    $target = $qbase . DIRECTORY_SEPARATOR . gmdate('Ymd_His') . '_' . $token . '_' . $name . $suffix;
    // Il nome di destinazione non termina in .php, né si trova nella document root.
    if (@rename($source, $target)) {
        $events[] = 'ISOLATO ' . $reason . ': ' . $source . ' => ' . $target;
        return true;
    }
    $events[] = 'ISOLAMENTO FALLITO ' . $reason . ': ' . $source;
    return false;
}

/**
 * Richiesta esplicita: cancellare il file chiamato esattamente wpsecurity.log.
 * Solo file REGOLARI situati dentro la directory WordPress; non segue link
 * simbolici, non cancella cartelle o altri .log, non esegue il contenuto.
 * Chi vuole conservarlo prima della cancellazione deve salvarne copia da Plesk.
 */
function psr_guard_delete_wpsecurity_log($file, &$events, &$detections, &$removed) {
    if (basename($file) !== 'wpsecurity.log') { return false; }
    $root = realpath(ABSPATH);
    $parent = realpath(dirname($file));
    if (!$root || !$parent || !psr_guard_path_within($parent, $root)) {
        $events[] = 'LOG NON ELIMINATO (percorso esterno a WordPress): ' . $file;
        return false;
    }
    if (is_link($file)) {
        $events[] = 'LOG NON ELIMINATO (link simbolico): ' . $file;
        return false;
    }
    clearstatcache(true, $file);
    $before = @lstat($file);
    if (!$before || !is_file($file) || (($before['mode'] & 0170000) !== 0100000)) { return false; }
    $detections++;
    // Controllo d'identita' del file prima di eliminarlo, senza modificarlo.
    clearstatcache(true, $file);
    $after = @lstat($file);
    if (!$after || is_link($file) || $before['dev'] !== $after['dev'] ||
        $before['ino'] !== $after['ino'] || $before['size'] !== $after['size'] ||
        $before['mtime'] !== $after['mtime']) {
        $events[] = 'LOG NON ELIMINATO (cambiato durante il controllo): ' . $file;
        return false;
    }
    if (@unlink($file)) {
        $removed++;
        $events[] = 'ELIMINATO wpsecurity.log (dimensione ' . (int)$before['size'] . ' byte): ' . $file;
        return true;
    }
    $events[] = 'ELIMINAZIONE FALLITA wpsecurity.log (verificare permessi): ' . $file;
    return false;
}


/**
 * Firma strutturale molto specifica del plugin malevolo WP Link Helper.
 * Non dipende dal nome del file e NON esegue i sorgenti da analizzare.
 */
function psr_guard_has_php_symbol($body, $kind, $name) {
    // Le firme scritte in stringhe/commenti (compreso questo scanner) non sono codice.
    if (!function_exists('token_get_all')) { return false; }
    $tokens = array_values(array_filter(token_get_all($body), static function ($token) {
        return !is_array($token) || !in_array($token[0], array(T_WHITESPACE, T_COMMENT, T_DOC_COMMENT), true);
    }));
    foreach ($tokens as $i => $token) {
        if (!is_array($token)) { continue; }
        if ($kind === 'class' && $token[0] === T_CLASS) {
            $next = $tokens[$i + 1] ?? null;
            if (is_array($next) && $next[0] === T_STRING && strcasecmp($next[1], $name) === 0) { return true; }
        }
        if ($kind === 'constant' && ($token[0] === T_STRING
            || (defined('T_NAME_FULLY_QUALIFIED') && $token[0] === constant('T_NAME_FULLY_QUALIFIED')))
            && strtolower(ltrim($token[1], '\\')) === 'define' && ($tokens[$i + 1] ?? null) === '(') {
            $argument = $tokens[$i + 2] ?? null;
            if (is_array($argument) && $argument[0] === T_CONSTANT_ENCAPSED_STRING
                && in_array($argument[1], array("'" . $name . "'", '"' . $name . '"'), true)) { return true; }
        }
    }
    return false;
}
function psr_guard_matches_wlh_main($file) {
    if (!is_file($file) || is_link($file)) { return false; }
    $size = @filesize($file);
    if ($size === false || $size < 8000 || $size > 400000) { return false; }
    $body = @file_get_contents($file);
    if (!is_string($body)) { return false; }
    foreach (array("define('WLH_VERSION'", "define('WLH_OPT_ENDPOINT'",
        "define('WLH_OPT_API_TOKEN'", 'WLH_Recovery_Installer::ensure_installed()',
        'wlh_hide_self', 'class-wlh-recovery-installer.php') as $marker) {
        if (strpos($body, $marker) === false) { return false; }
    }
    return psr_guard_has_php_symbol($body, 'constant', 'WLH_VERSION')
        && psr_guard_has_php_symbol($body, 'constant', 'WLH_OPT_ENDPOINT')
        && psr_guard_has_php_symbol($body, 'constant', 'WLH_OPT_API_TOKEN');
}

/** Identifica varianti dell'installer che ricrea i MU-plugin nascosti. */
function psr_guard_matches_wlh_recovery_installer($file) {
    if (!is_file($file) || is_link($file)) { return false; }
    $size = @filesize($file);
    if ($size === false || $size < 3500 || $size > 300000) { return false; }
    $body = @file_get_contents($file);
    if (!is_string($body)) { return false; }
    foreach (array('class WLH_Recovery_Installer', 'function ensure_installed(',
        'WLH_Vault::stash(', 'function target_paths(', 'mu_filename()') as $marker) {
        if (strpos($body, $marker) === false) { return false; }
    }
    return psr_guard_has_php_symbol($body, 'class', 'WLH_Recovery_Installer');
}

/** Un archivio generico b.zip NON basta: si isola solo il byte-per-byte confermato. */
function psr_guard_verify_wlh_archive($file, &$events, &$detections, &$removed) {
    if (!is_file($file) || is_link($file) || !is_readable($file)) { return; }
    $n = strtolower(basename($file));
    if ($n !== 'b.zip' && $n !== 'wp-link-helper.zip') { return; }
    $size = @filesize($file);
    if ($size === false || $size < 1000 || $size > 1024 * 1024) { return; }
    $hash = @hash_file('sha256', $file);
    if (!is_string($hash) || !hash_equals('02557e594938d6d1ee03e9b6dd9e22ea149adde59a1b18e5d6c3e0aa7963e5a6', $hash)) { return; }
    $detections++;
    $before = @lstat($file);
    $again = @hash_file('sha256', $file);
    $after = @lstat($file);
    if (!$before || !$after || is_link($file) || $before['dev'] !== $after['dev'] ||
        $before['ino'] !== $after['ino'] || $again !== $hash) {
        $events[] = 'ARCHIVIO MALWARE MODIFICATO DURANTE IL CONTROLLO: ' . $file;
        return;
    }
    if (psr_guard_isolate($file, 'archivio WP Link Helper b.zip confermato SHA256', $events)) { $removed++; }
}

function psr_guard_verify_file($file, $name, &$events, &$detections, &$removed) {
    if (!is_file($file) || is_link($file) || !is_readable($file)) { return; }
    $size = @filesize($file);
    if ($size === false || $size > 1024 * 1024) {
        $events[] = 'FILE NON ANALIZZATO (dimensione): ' . $file;
        return;
    }
    $hash = @hash_file('sha256', $file);
    $signatures = psr_guard_signatures();
    $matched = is_string($hash) && isset($signatures[$hash]);
    $variant = !$matched && $name === 'slakersest.php' && psr_guard_matches_slakersest($file);
    $variantOvercoqu = !$matched && $name === 'overcoqu.php' && psr_guard_matches_overcoqu($file);
    $newName = in_array($name, psr_guard_new_shell_names(), true);
    $familyMatch = !$matched && !$variant && !$variantOvercoqu && $newName && psr_guard_matches_hidden_filemanager_family($file);
    $unprotectedFM = !$matched && !$variant && !$variantOvercoqu && !$familyMatch && $newName && psr_guard_matches_unprotected_filemanager($file);
    $commandShell = !$matched && !$variant && !$variantOvercoqu && !$familyMatch && !$unprotectedFM
        && $newName && psr_guard_matches_command_shell($file);
    $wlhMain = !$matched && psr_guard_matches_wlh_main($file);
    $wlhInstall = !$matched && !$wlhMain && psr_guard_matches_wlh_recovery_installer($file);
    $muRoot = realpath(WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'mu-plugins');
    $isMuRootFile = $muRoot && realpath(dirname($file)) === $muRoot;
    $recovery = !$matched && !$variant && !$familyMatch && !$unprotectedFM && $isMuRootFile && psr_guard_matches_mu_recovery($file);
    if ($matched || $variant || $variantOvercoqu || $familyMatch || $unprotectedFM || $commandShell || $recovery || $wlhMain || $wlhInstall) {
        $detections++;
        $before = @lstat($file);
        $again = @hash_file('sha256', $file);
        $after = @lstat($file);
        if (!$before || !$after || $before['dev'] !== $after['dev'] || $before['ino'] !== $after['ino'] ||
            $again !== $hash || is_link($file) || ($variant && !psr_guard_matches_slakersest($file)) ||
            ($variantOvercoqu && !psr_guard_matches_overcoqu($file)) ||
            ($familyMatch && !psr_guard_matches_hidden_filemanager_family($file)) ||
            ($unprotectedFM && !psr_guard_matches_unprotected_filemanager($file)) ||
            ($commandShell && !psr_guard_matches_command_shell($file)) ||
            ($recovery && !psr_guard_matches_mu_recovery($file)) ||
            ($wlhMain && !psr_guard_matches_wlh_main($file)) ||
            ($wlhInstall && !psr_guard_matches_wlh_recovery_installer($file))) {
            $events[] = 'FILE CAMBIATO DURANTE LA VERIFICA: ' . $file;
            return;
        }
        $reason = $matched ? 'SHA256 confermato' : ($wlhMain ? 'plugin WP Link Helper malevolo' : ($wlhInstall ? 'installer MU nascosto WP Link Helper' : ($recovery ? 'persistenza MU wp-link-helper verificata' : ($commandShell ? 'webshell comandi da HTTP verificata' : ($unprotectedFM ? 'file manager PHP privo di autenticazione verificato' : ($familyMatch ? 'firma comportamentale webshell verificata' : ($variantOvercoqu ? 'variante overcoqu confermata' : 'variante slakersest confermata')))))));
        if (psr_guard_isolate($file, $reason, $events)) { $removed++; }
        return;
    }
    // Indicatore euristico in sola segnalazione per nomi NUOVI: il nome non
    // determina la cancellazione; il codice sospetto non viene mai eseguito.
    // Una quarantena automatica senza firma certa puo' bloccare siti validi.
    if (!$newName && realpath($file) !== realpath(__FILE__) && (psr_guard_matches_hidden_filemanager_family($file) ||
        psr_guard_matches_unprotected_filemanager($file) ||
        psr_guard_matches_command_shell($file))) {
        $events[] = 'WEB SHELL PROBABILE (nuovo nome, NON isolata): ' . $file;
        return;
    }
    if (in_array($name, array('translanugos.php','translanugoss.php','toddlingness.php','prequiche.php','slakersest.php',
        'overcoqu.php','wp-e92754ad0d8a.php','wp-b697fed82d65.php',
        'wp-d5086527eae9.php','wp-ce624c7f52b7.php',
        'underpenup.php','paltererness.php','semibuxeous.php','transfraktur.php','ultraruruing.php','microbearers.php',
        'morselledism.php','misemace.php','protomunsif.php','overcynophi.php','aciesest.php','acerbatesed.php',
        'wp-link-helper.php','wlh-recovery.php','wlh-config.php','class-wlh-recovery-installer.php',
        'dismutation.php','protosnitchy.php','8p8d7zr0.php','macroutter.php'), true) ||
        ($isMuRootFile && preg_match('/^wp-[a-f0-9]{12}\.php$/i', $name))) {
        $events[] = 'FILE SOSPETTO DA VERIFICARE (non isolato): ' . $file;
    }
}

/**
 * Le cartelle di plugin sospetti richiedono una verifica del contenuto.
 * Il solo nome NON e' mai sufficiente per una quarantena.
 * Plesk wp-toolkit non viene toccato.
 * Interruttore di emergenza: define('PSR_GUARD_AUTO_ISOLATE_NAMED_PLUGIN_PATHS', false);
 */
function psr_guard_targeted_scan(&$events, &$detections, &$removed) {
    $content = realpath(WP_CONTENT_DIR);
    if (!$content) { return; }
    // Sondaggio rapido degli indicatori in directory precise, non ricorsivo.
    // Consente il rilevamento entro il prossimo controllo (WP-Cron non e' garantito
    // su siti senza traffico) senza rallentare tutte le richieste WordPress.
    $roots = array(ABSPATH, WP_CONTENT_DIR, WP_PLUGIN_DIR,
        WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'mu-plugins',
        WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'uploads');
    foreach ($roots as $root) {
        $realRoot = realpath($root);
        if (!$realRoot || !psr_guard_path_within($realRoot, realpath(ABSPATH))) { continue; }
        $logPath = $realRoot . DIRECTORY_SEPARATOR . 'wpsecurity.log';
        if (file_exists($logPath) || is_link($logPath)) {
            psr_guard_delete_wpsecurity_log($logPath, $events, $detections, $removed);
        }
        foreach (array('b.zip','wp-link-helper.zip') as $archiveName) {
            $candidateZip = $realRoot . DIRECTORY_SEPARATOR . $archiveName;
            if (is_file($candidateZip) && !is_link($candidateZip)) {
                psr_guard_verify_wlh_archive($candidateZip, $events, $detections, $removed);
            }
        }
        foreach (psr_guard_new_shell_names() as $suspect) {
            $candidate = $realRoot . DIRECTORY_SEPARATOR . $suspect;
            if (is_file($candidate) && !is_link($candidate)) {
                psr_guard_verify_file($candidate, $suspect, $events, $detections, $removed);
            }
        }
    }
    $plugins = realpath(WP_PLUGIN_DIR);
    if ($plugins && psr_guard_path_within($plugins, $content)) {
        foreach (array('wp-link-helper', 'wordpress-security') as $name) {
            $path = $plugins . DIRECTORY_SEPARATOR . $name;
            if (!is_dir($path) || is_link($path)) { continue; }
            // Non isolare mai una cartella di plugin basandosi soltanto sul nome.
            // WP Link Helper viene messo in quarantena solo se il file principale
            // contiene le firme strutturali verificate del campione malevolo.
            $verified = $name === 'wp-link-helper' && psr_guard_matches_wlh_main(
                $path . DIRECTORY_SEPARATOR . 'wp-link-helper.php');
            if (!$verified) {
                $events[] = 'CARTELLA PLUGIN DA VERIFICARE (non isolata per solo nome): ' . $path;
                continue;
            }
            $detections++;
            if (!defined('PSR_GUARD_AUTO_ISOLATE_NAMED_PLUGIN_PATHS') || PSR_GUARD_AUTO_ISOLATE_NAMED_PLUGIN_PATHS) {
                if (psr_guard_isolate($path, 'WP Link Helper verificato tramite contenuto', $events)) { $removed++; }
            } else {
                $events[] = 'PLUGIN MALEVOLO VERIFICATO (isolamento disattivato): ' . $path;
            }
        }
    }
    // b.zip contiene il codice che installa wp-cache-helper, ma NON il sorgente
    // di wp-cache-helper: segnala la cartella senza cancellarla per solo nome.
    if ($plugins) {
        $wch = $plugins . DIRECTORY_SEPARATOR . 'wp-cache-helper';
        if (is_dir($wch) && !is_link($wch)) {
            $events[] = 'PLUGIN WP-CACHE-HELPER DA VERIFICARE (non isolato per solo nome): ' . $wch;
        }
    }
    // Copie della build precedente tenute dal canale di autoripristino.
    $contentEntries = @scandir($content);
    if (is_array($contentEntries)) {
        $examined = 0;
        foreach ($contentEntries as $entry) {
            if (++$examined > 250) { break; }
            if (!preg_match('/^wp-[a-f0-9]{12}-prev$/i', $entry)) { continue; }
            $old = $content . DIRECTORY_SEPARATOR . $entry;
            if (is_link($old) || !is_dir($old)) { continue; }
            $main = $old . DIRECTORY_SEPARATOR . 'wp-link-helper.php';
            if (psr_guard_matches_wlh_main($main)) {
                $detections++;
                if (psr_guard_isolate($old, 'build precedente WP Link Helper riconosciuta', $events)) { $removed++; }
            } else {
                $events[] = 'CARTELLA ROLLBACK DA VERIFICARE (non isolata): ' . $old;
            }
        }
    }
    // Sono soltanto indicatori nel database: non alterare wp_options in automatico.
    if (function_exists('get_option')) {
        foreach (array('wlh_key','wlh_cdn','wlh_snippet','wlh_adopted') as $opt) {
            if (get_option($opt, false) !== false) {
                $events[] = 'PERSISTENZA DATABASE DA VERIFICARE (nessun valore mostrato): ' . $opt;
            }
        }
    }
    if (function_exists('wp_next_scheduled') && wp_next_scheduled('wlh_health_event')) {
        $events[] = 'WP-CRON SOSPETTO DA VERIFICARE: wlh_health_event';
    }
    $mu = $content . DIRECTORY_SEPARATOR . 'mu-plugins';
    if (is_dir($mu) && !is_link($mu)) {
        // Solo PHP di primo livello: il contenuto Plesk wp-toolkit/ non viene toccato.
        // Nomi wp-<hex> variabili: verifica i byte, NON rimuove per nome.
        $entries = @scandir($mu);
        if (is_array($entries)) {
            $count = 0;
            foreach ($entries as $entry) {
                if ($entry === '.' || $entry === '..' || !preg_match('/\.php$/i', $entry)) { continue; }
                if (++$count > 500) {
                    $events[] = 'SCANSIONE MU PARZIALE: oltre 500 PHP di primo livello';
                    break;
                }
                $candidate = $mu . DIRECTORY_SEPARATOR . $entry;
                if (is_file($candidate) && !is_link($candidate)) {
                    psr_guard_verify_file($candidate, strtolower($entry), $events, $detections, $removed);
                }
            }
        }
    }
}

function psr_guard_scan() {
    if (get_transient('psr_guard_running')) { return; }
    set_transient('psr_guard_running', 1, 10 * MINUTE_IN_SECONDS);
    $started = microtime(true); $checked = 0; $detections = 0; $removed = 0; $events = array();
    try {
        psr_guard_targeted_scan($events, $detections, $removed);
        $root = realpath(ABSPATH);
        if (!$root) { throw new RuntimeException('Root WordPress non disponibile'); }
        $dir = new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS);
        $it = new RecursiveIteratorIterator($dir, RecursiveIteratorIterator::LEAVES_ONLY);
        foreach ($it as $item) {
            if ($checked >= 20000 || microtime(true) - $started >= 12 || count($events) >= 100) { break; }
            if ($item->isLink() || !$item->isFile()) { continue; }
            $checked++;
            $name = strtolower($item->getFilename());
            if ($name === 'wpsecurity.log') {
                psr_guard_delete_wpsecurity_log($item->getPathname(), $events, $detections, $removed);
                continue;
            }
            if ($name === 'b.zip' || $name === 'wp-link-helper.zip') {
                psr_guard_verify_wlh_archive($item->getPathname(), $events, $detections, $removed);
                continue;
            }
            if (strtolower(pathinfo($name, PATHINFO_EXTENSION)) !== 'php') { continue; }
            psr_guard_verify_file($item->getPathname(), $name, $events, $detections, $removed);
        }
    } catch (Throwable $e) { $events[] = 'Scansione interrotta: ' . $e->getMessage(); }
    $report = array('when' => gmdate('c'), 'checked' => $checked, 'detections' => $detections,
        'removed' => $removed, 'events' => array_slice($events, 0, 100));
    update_option('psr_guard_last_report', $report, false);
    foreach ($report['events'] as $event) { error_log('[PSR GUARD] ' . $event); }
    delete_transient('psr_guard_running');
}

function psr_guard_install_schedule() {
    if (!wp_next_scheduled('psr_guard_scan')) {
        wp_schedule_event(time() + 30, 'hourly', 'psr_guard_scan');
    }
}
add_action('init', 'psr_guard_install_schedule', 20);
add_action('psr_guard_scan', 'psr_guard_scan');

/** Breve controllo mirato ogni 5 minuti, senza scansione ricorsiva del sito. */
function psr_guard_quick_scan() {
    if (get_transient('psr_guard_quick_scan_cooldown')) { return; }
    set_transient('psr_guard_quick_scan_cooldown', 1, 5 * MINUTE_IN_SECONDS);
    $events = array(); $det = 0; $iso = 0;
    psr_guard_targeted_scan($events, $det, $iso);
    if ($iso > 0) {
        // Gli MU-plugin hanno gia' eseguito il bootstrap prima del tema.
        // Un callback malevolo su shutdown potrebbe ripristinare il plugin nello
        // stesso request: un secondo controllo, registrato DOPO quel callback,
        // lo isola nuovamente. Non garantisce protezione contro tutte le persistenze.
        add_action('shutdown', 'psr_guard_post_shutdown_scan', PHP_INT_MAX);
    }
    if ($events) {
        foreach ($events as $event) { error_log('[PSR GUARD QUICK] ' . $event); }
        if (count($events) > 0) {
            update_option('psr_guard_quick_report', array('when' => gmdate('c'), 'events' => $events), false);
        }
    }
}
add_action('init', 'psr_guard_quick_scan', 30);

function psr_guard_post_shutdown_scan() {
    $events = array(); $det = 0; $iso = 0;
    psr_guard_targeted_scan($events, $det, $iso);
    foreach ($events as $event) { error_log('[PSR GUARD SHUTDOWN] ' . $event); }
}

function psr_guard_admin_notice() {
    // Avvisi riservati esclusivamente all'amministratore WordPress con ID 1.
    // Scansioni, quarantena e logging non dipendono dall'utente collegato.
    if (get_current_user_id() !== 1 || !current_user_can('manage_options')) { return; }
    $report = get_option('psr_guard_last_report');
    $quick = get_option('psr_guard_quick_report');
    if ((!is_array($report) || empty($report['events'])) && (!is_array($quick) || empty($quick['events']))) { return; }
    echo '<div class="notice notice-warning"><p><strong>PSR Security Guard:</strong> possibili file non autorizzati rilevati o isolati. Verificare i log PHP e la quarantena su Plesk. Nessun elemento di wp-toolkit è stato rimosso automaticamente.</p></div>';
}
add_action('admin_notices', 'psr_guard_admin_notice');


/**
 * Analisi contenuti WordPress solo su richiesta dell'amministratore ID 1.
 * Nessuna scansione del database sul front-end pubblico.
 */
if (function_exists('is_admin') && is_admin()) {
    $psr_audit_path = __DIR__ . '/psr-content-audit.php';
    if (is_file($psr_audit_path) && is_readable($psr_audit_path)) {
        require_once $psr_audit_path;
    }
}
