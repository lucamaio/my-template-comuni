<?php
/**
 * PSR Security Guard - scanner forense CLI (SOLA LETTURA).
 * Uso: php psr-scan-cli.php --root=/var/www/vhosts/sito/httpdocs [--out=/.../private/report.json]
 *      php psr-scan-cli.php --vhosts=/var/www/vhosts [--out=/var/.../private/report.json]
 * Nessuna esecuzione del codice controllato. Nessuna quarantena automatica.
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
error_reporting(E_ALL);
ini_set('display_errors', 'stderr');
set_time_limit(0);

function psr_scli_usage() {
    fwrite(STDERR, "Uso: php psr-scan-cli.php (--root=/PATH/WORDPRESS | --vhosts=/var/www/vhosts) [--out=/PERCORSO/PRIVATO.json] [--max=150000]\n");
}
function psr_scli_within($p, $root) {
    return $p === $root || strncmp($p, rtrim($root, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR,
        strlen(rtrim($root, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR)) === 0;
}
function psr_scli_find_wp($top) {
    $found = []; $queue = [[$top, 0]]; $seen = [];
    while ($queue) {
        [$dir, $depth] = array_shift($queue);
        $rp = realpath($dir);
        if (!$rp || isset($seen[$rp]) || !psr_scli_within($rp, $top)) { continue; }
        $seen[$rp] = true;
        if (is_file($rp.'/wp-settings.php') && (is_file($rp.'/wp-config.php') ||
            is_file(dirname($rp).'/wp-config.php'))) {
            $found[] = $rp;
            continue;
        }
        if ($depth >= 5 || count($seen) > 50000) { continue; }
        $entries = @scandir($rp);
        if (!is_array($entries)) { continue; }
        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..' || $entry === 'logs' ||
                $entry === 'backup' || $entry === 'backups' || $entry === 'tmp' ||
                $entry === '.git' || $entry === '.plesk' || $entry === '.psr-security-quarantine') { continue; }
            $next = $rp.'/'.$entry;
            if (!is_link($next) && is_dir($next)) { $queue[] = [$next, $depth+1]; }
        }
    }
    return array_values(array_unique($found));
}
function psr_scli_signatures() {
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
function psr_scli_check($f, $root, $signatures) {
    $name = strtolower(basename($f));
    $size = @filesize($f);
    $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    $isPhp = ($extension === 'php' || $extension === 'phtml' || $extension === 'phar');
    $isText = in_array($extension, ['html','htm','js','txt'], true) || $name === '.htaccess' || $name === 'feed.xml';
    if (!$isPhp && !$isText && !in_array($name, ['b.zip','wp-link-helper.zip','wpsecurity.log'], true)) { return null; }
    if ($name === 'wpsecurity.log') {
        return ['severity'=>'DA_VERIFICARE','reason'=>'wpsecurity.log: indicatore noto, da verificare prima di cancellarlo'];
    }
    if ($size === false || $size < 0 || $size > 2*1024*1024) { return null; }
    $hash = @hash_file('sha256', $f);
    if (is_string($hash) && isset($signatures[$hash])) {
        return ['severity'=>'CONFERMATO','reason'=>'SHA256 identico al campione: '.$signatures[$hash],'sha256'=>$hash];
    }
    if (in_array($name, ['b.zip','wp-link-helper.zip'], true)) {
        if ($hash === '02557e594938d6d1ee03e9b6dd9e22ea149adde59a1b18e5d6c3e0aa7963e5a6') {
            return ['severity'=>'CONFERMATO','reason'=>'Archivio WP Link Helper campione esatto','sha256'=>$hash];
        }
        return null;
    }
    $body = @file_get_contents($f);
    if (!is_string($body)) { return null; }
    $markers = function(array $items) use ($body) {
        foreach ($items as $m) { if (strpos($body, $m) === false) { return false; } }
        return true;
    };
    // Indicatori SEO del caso Comune di Ali': SOLO SEGNALAZIONE.
    // La verifica Google e i file token possono essere autentici. La loro
    // presenza non autorizza quarantena o dichiarazione di infezione.
    if ($name === 'feed.xml' && $markers([
        '<rss version="2.0"', '<language>id</language>',
        'Discovery update for crawlers.', '/amministrazione/'])) {
        return ['severity'=>'DA_VERIFICARE',
            'reason'=>'RSS anomalo del campione: lingua indonesiana e feed di scoperta crawler, verificare provenienza e timestamp'];
    }
    if ($name === 'google9f36808e9fb31ed2.html' &&
        trim($body) === 'google-site-verification: google9f36808e9fb31ed2.html') {
        return ['severity'=>'DA_VERIFICARE',
            'reason'=>'File Google Search Console osservato durante incidente: controllare proprietari e metodo di verifica, non rimuovere automaticamente'];
    }
    if ($name === 'dd295d873d45d6f7d4c13dfe51de5987.txt' &&
        trim($body) === 'dd295d873d45d6f7d4c13dfe51de5987') {
        return ['severity'=>'DA_VERIFICARE',
            'reason'=>'File token esadecimale osservato durante incidente: identificare chi lo ha creato, non rimuovere automaticamente'];
    }
    if ($isPhp) {
        $masked = $markers(['opacity:.03','session_start(','$_SESSION[','$_FILES[',
            'scandir(','rawurldecode(','DIRECTORY_SEPARATOR','htmlspecialchars(',
            'fopen(','fwrite(','rename(','unlink(']) && substr_count($body,'if(false)') >= 4;
        if ($masked) { return ['severity'=>'ALTA_PROBABILITA', 'reason'=>'File manager occultato (firma comportamentale)']; }
        if ($markers(['$dauth = false;', '$global_readonly = false;', '$edit_files = true;',
            "\$ip_ruleset = 'OFF';", 'FM_USE_AUTH','FM_ROOT_PATH','fm_rdelete('])) {
            return ['severity'=>'ALTA_PROBABILITA', 'reason'=>'File manager PHP non autenticato con modifica/cancellazione'];
        }
        if (preg_match('~\bshell_exec\s*\(\s*\$_(?:GET|POST|REQUEST)\s*\[~i', $body) &&
            preg_match('/\$_(?:GET|POST|REQUEST)\s*\[/', $body)) {
            return ['severity'=>'ALTA_PROBABILITA', 'reason'=>'Comando OS passato da richiesta HTTP a shell_exec'];
        }
        if ($markers(["define('WLH_VERSION',", "define('WLH_OPT_API_TOKEN',",
            "add_filter('all_plugins'", 'function wlh_hide_self(',
            'WLH_Recovery_Installer::ensure_installed()'])) {
            return ['severity'=>'ALTA_PROBABILITA', 'reason'=>'Plugin WP Link Helper persistente'];
        }
        if ($markers(['function wprc_reinstall(', 'function wprc_reclaim(',
            'function wprc_plugin_dir(', 'WPRC_SLUG'])) {
            return ['severity'=>'ALTA_PROBABILITA', 'reason'=>'MU-plugin canale recovery WP Link Helper'];
        }
        if (preg_match('~/(?:uploads|cache)/.*\.(?:php|phtml|phar)$~i', $f)) {
            return ['severity'=>'DA_VERIFICARE', 'reason'=>'PHP eseguibile in uploads/cache (non necessariamente malware)'];
        }
        if (preg_match('/^(?:wp-[a-f0-9]{12}|(?:translanugos|translanugoss|transfraktur|slakersest|overcoqu|aciesest|macroutter|protosnitchy|dismutation|acerbatesed|overcynophi|protomunsif))\.php$/i', $name)) {
            return ['severity'=>'DA_VERIFICARE','reason'=>'Nome associato ai precedenti incidenti, contenuto differente'];
        }
        if (preg_match('/\beval\s*\(\s*(?:base64_decode|gzinflate)\s*\(/i', $body)) {
            return ['severity'=>'DA_VERIFICARE','reason'=>'PHP con eval di codice decodificato'];
        }
    }
    // Utile per portali SEO spam: non conclude che WordPress sia stato alterato.
    if ($isText && preg_match('/AGUSTOTO|slot\s+gacor|bandar\s+togel/i', $body)) {
        return ['severity'=>'DA_VERIFICARE','reason'=>'Contenuto spam/gioco esterno al portale comunale'];
    }
    if ($name === '.htaccess' && preg_match('/cachelayer\.top|AGUSTOTO/i', $body)) {
        return ['severity'=>'DA_VERIFICARE','reason'=>'Direttive .htaccess con indicatori del malware'];
    }
    return null;
}
function psr_scli_walk($root,$signatures,$max,&$findings,&$checked,&$cut) {
    $stack = [$root]; $files = 0;
    while ($stack) {
        $dir = array_pop($stack);
        $entries = @scandir($dir);
        if (!is_array($entries)) { continue; }
        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') { continue; }
            $full = $dir.'/'.$entry;
            if (is_link($full)) { continue; }
            if (is_dir($full)) {
                if (in_array($entry, ['.git','.psr-security-quarantine','node_modules','vendor'], true)) { continue; }
                $real = realpath($full);
                if ($real && psr_scli_within($real, $root)) { $stack[] = $real; }
                continue;
            }
            if (!is_file($full)) { continue; }
            $checked++;
            if ($checked > $max) { $cut=true; return; }
            $hit = psr_scli_check($full, $root, $signatures);
            if ($hit && count($findings) < 3000) {
                $hit['path'] = $full;
                $findings[] = $hit;
            }
        }
    }
}
$args = getopt('', ['root:', 'vhosts:', 'out:', 'max:', 'help']);
if (isset($args['help']) || (isset($args['root']) === isset($args['vhosts']))) { psr_scli_usage(); exit(2); }
$anchor = $args['root'] ?? $args['vhosts'];
$anchor = is_string($anchor) ? realpath($anchor) : false;
if (!$anchor || !is_dir($anchor)) { fwrite(STDERR,"Cartella inesistente o non accessibile.\n"); exit(2); }
$sites = isset($args['root']) ? [$anchor] : psr_scli_find_wp($anchor);
if (isset($args['root']) && !is_file($anchor.'/wp-settings.php')) {
    fwrite(STDERR,"Il percorso --root deve essere la directory WordPress con wp-settings.php.\n"); exit(2);
}
$max = isset($args['max']) ? max(100,min(300000,(int)$args['max'])) : 150000;
$all = [];
foreach ($sites as $site) {
    $found=[]; $checked=0; $cut=false;
    psr_scli_walk($site, psr_scli_signatures(), $max, $found, $checked, $cut);
    $loader = $site.'/wp-content/themes/design-comuni-wordpress-theme/functions.php';
    $mod = $site.'/wp-content/themes/design-comuni-wordpress-theme/inc/psr-security-guard.php';
    $active = is_file($loader) && is_readable($loader) && is_file($mod) && is_readable($mod)
        && strpos((string)@file_get_contents($loader), 'psr-security-guard.php') !== false;
    $all[] = ['site'=>$site, 'psr_files_present_and_referenced'=>$active,
        'guard_file_exists'=>is_file($mod), 'checked'=>$checked, 'limit_reached'=>$cut,
        'findings'=>$found];
}
$summary=['CONFERMATO'=>0,'ALTA_PROBABILITA'=>0,'DA_VERIFICARE'=>0];
foreach ($all as $report) {
    foreach ($report['findings'] as $hit) { $summary[$hit['severity']]++; }
}
$report=['tool'=>'PSR forensics read-only scanner','generated_utc'=>gmdate('c'),
    'site_count'=>count($all),'counts'=>$summary, 'sites'=>$all,
    'note'=>'Un rilevamento euristico non dimostra da solo compromissione. Scanner in sola lettura.'];
$json=json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE);
if ($json===false) { fwrite(STDERR,"Impossibile serializzare report.\n");exit(1); }
if (isset($args['out'])) {
    $output = (string)$args['out'];
    if ($output === '' || !str_starts_with($output, DIRECTORY_SEPARATOR)) { fwrite(STDERR,"--out deve essere assoluto.\n");exit(2); }
    $parent = realpath(dirname($output));
    if (!$parent) { fwrite(STDERR,"Cartella report inesistente.\n");exit(2); }
    foreach ($sites as $site) {
        if (psr_scli_within($parent,$site)) {
            fwrite(STDERR,"Per sicurezza il report va SALVATO FUORI da httpdocs.\n");exit(2);
        }
    }
    if (@file_put_contents($output, $json.PHP_EOL, LOCK_EX) === false) {
        fwrite(STDERR,"Impossibile scrivere il report.\n");exit(1);
    }
    @chmod($output,0600);
    echo "Rapporto scritto: $output\n";
} else { echo $json.PHP_EOL; }
fwrite(STDERR,"Siti controllati: ".count($all)." | Confermati: {$summary['CONFERMATO']} | Alta probabilita': {$summary['ALTA_PROBABILITA']} | Da verificare: {$summary['DA_VERIFICARE']}\n");
