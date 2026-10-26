<?php
/**
 * PSR Guard - Audit contenuti WordPress. Solo ID utente 1 + manage_options.
 * Rilevamento preliminare, nessuna eliminazione o modifica dei contenuti.
 * Elaborazione manuale in blocchi per non influenzare le visite pubbliche.
 */
if (!defined('ABSPATH')) { exit; }
if (!function_exists('psr_ca_allowed')) {
    function psr_ca_allowed() {
        return (int) get_current_user_id() === 1 && current_user_can('manage_options');
    }
}

function psr_ca_menu() {
    if (!psr_ca_allowed()) { return; }
    add_management_page('PSR - Controllo contenuti', 'PSR Controllo contenuti',
        'manage_options', 'psr-content-audit', 'psr_ca_render');
}
add_action('admin_menu', 'psr_ca_menu');

function psr_ca_require_access() {
    if (!psr_ca_allowed()) {
        wp_send_json_error(array('message' => 'Accesso non autorizzato'), 403);
    }
    check_ajax_referer('psr_ca_manual_scan', 'nonce');
}

function psr_ca_date($candidate) {
    if (!is_string($candidate) || !preg_match('/^\\d{4}-\\d{2}-\\d{2}$/D', $candidate)) { return false; }
    $dt = DateTimeImmutable::createFromFormat('!Y-m-d', $candidate, new DateTimeZone('UTC'));
    return $dt && $dt->format('Y-m-d') === $candidate ? $candidate : false;
}

function psr_ca_start() {
    psr_ca_require_access();
    $since = isset($_POST['since']) ? psr_ca_date(wp_unslash($_POST['since'])) : false;
    if (!$since) { wp_send_json_error(array('message' => 'Data di inizio non valida'), 400); }
    $state = array('started' => gmdate('c'), 'finished' => false, 'truncated' => false,
        'since' => $since, 'cursor' => 0, 'checked' => 0, 'suspicious_count' => 0,
        'recent_count' => 0, 'suspicious' => array(), 'recent' => array());
    update_option('psr_ca_report_v1', $state, false);
    wp_send_json_success(array('finished' => false, 'checked' => 0));
}
add_action('wp_ajax_psr_ca_start', 'psr_ca_start');

/** Firme di contenuti spam / SEO injection; non eseguire il contenuto. */
function psr_ca_indicators($text) {
    if (!is_string($text) || $text === '') { return array(); }
    // Tutte le espressioni sono volutamente specifiche: una parola isolata come
    // "slot" o "casino" non basta mai per accusare una pagina comunale.
    $rules = array(
        'Marca spam AGUSTOTO' => '~\\bagustoto\\b~iu',
        'Spam scommesse indonesiano' => '~\\b(?:slot\\s+gacor|judi\\s+online|situs\\s+togel|bandar\\s+togel|daftar\\s+slot|agen\\s+togel)\\b~iu',
        'Dominio remoto compromesso noto' => '~(?:cachelayer\\.top|(?:https?:)?//[^/\\s"\\x27]*\\b(?:agustoto|slotgacor)\\b)~iu',
        'Riferimento a persistenza WP Link Helper' => '~\\b(?:wp-link-helper|wlh_health_event|wlh_recovery|wprc_reinstall)\\b~i',
        'JavaScript con decodifica ed esecuzione' => '~\\beval\\s*\\(\\s*(?:atob|base64_decode)\\s*\\(~i',
        'Redirect HTML automatico' => '~<meta\\b[^>]*http-equiv\\s*=\\s*["\\x27]?refresh\\b[^>]*\\burl\\s*=~i',
        'Link nascosto tramite stile' => '~<a\\b[^>]*style\\s*=\\s*["\\x27][^"\\x27]*(?:display\\s*:\\s*none|visibility\\s*:\\s*hidden)[^"\\x27]*["\\x27]~i',
    );
    $found = array();
    foreach ($rules as $name => $pattern) {
        if (preg_match($pattern, $text)) { $found[] = $name; }
    }
    // Un iframe Google Maps / YouTube / Vimeo e' normale su molti siti comunali.
    // Segnaliamo soltanto iframe verso altri domini, senza rimuoverli.
    if (preg_match_all('~<iframe\b[^>]*\bsrc\s*=\s*["\\x27]?(https?://[^"\\x27>\s]+)~i', $text, $frames)) {
        foreach ($frames[1] as $url) {
            $host = strtolower((string) parse_url($url, PHP_URL_HOST));
            $trusted = array('google.com','google.it','youtube.com','youtube-nocookie.com',
                'youtu.be','vimeo.com','openstreetmap.org');
            $good = false;
            foreach ($trusted as $domain) {
                if ($host === $domain || substr($host, -strlen('.'.$domain)) === '.'.$domain) {
                    $good = true;
                    break;
                }
            }
            if ($host !== '' && !$good) {
                $found[] = 'Iframe esterno da verificare';
                break;
            }
        }
    }
    return array_values(array_unique($found));
}

/** Analizza un numero limitato di metadati testuali (compresi ACF e Elementor). */
function psr_ca_scan_meta($post_id) {
    global $wpdb;
    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT meta_key, LEFT(meta_value, 32768) AS meta_value FROM {$wpdb->postmeta} WHERE post_id = %d ORDER BY meta_id ASC LIMIT 40",
        $post_id
    ), ARRAY_A);
    if (!is_array($rows)) { return array(); }
    $hits = array(); $bytes = 0;
    foreach ($rows as $row) {
        $key = (string) $row['meta_key'];
        // I contenuti editoriali CMB2 del tema sono privati soltanto come meta_key.
        if ($key !== '' && $key[0] === '_' && $key !== '_elementor_data' && strpos($key, '_dci_') !== 0) { continue; }
        $value = $row['meta_value'];
        if (!is_string($value)) { continue; }
        // Per ogni pagina max 160 KiB di metadati (niente scansione di media/binary).
        if ($bytes >= 163840) { break; }
        $slice = substr($value, 0, min(strlen($value), 65536, 163840 - $bytes));
        $bytes += strlen($slice);
        foreach (psr_ca_indicators($slice) as $hit) { $hits[$hit] = true; }
    }
    return array_keys($hits);
}

function psr_ca_batch() {
    psr_ca_require_access();
    global $wpdb;
    $state = get_option('psr_ca_report_v1', false);
    if (!is_array($state) || !isset($state['cursor'], $state['since'])) {
        wp_send_json_error(array('message' => 'Avvia prima una scansione'), 400);
    }
    if (!empty($state['finished'])) {
        wp_send_json_success(array('finished' => true, 'checked' => (int) $state['checked']));
    }
    // Carico limitato: 15 oggetti/post per richiesta AJAX, max 20.000 oggetti.
    $batch_size = 15;
    $ids = $wpdb->get_col($wpdb->prepare(
        "SELECT ID FROM {$wpdb->posts} WHERE ID > %d AND post_type NOT IN
        ('revision','attachment','nav_menu_item','custom_css','customize_changeset','oembed_cache','user_request','wp_global_styles')
        AND post_status NOT IN ('auto-draft','trash','inherit') ORDER BY ID ASC LIMIT %d",
        (int) $state['cursor'], $batch_size
    ));
    if (!is_array($ids)) { wp_send_json_error(array('message' => 'Lettura database non riuscita'), 500); }
    $since = strtotime($state['since'] . ' 00:00:00 UTC');
    foreach ($ids as $id) {
        $post = get_post((int) $id);
        $state['cursor'] = (int) $id;
        if (!$post) { continue; }
        $state['checked']++;
        $raw = (string) $post->post_title . "\\n" . (string) $post->post_excerpt . "\\n" . (string) $post->post_content;
        // Limite sul contenuto singolo: gli HTML molto grandi potrebbero essere parzialmente esaminati.
        $hits = psr_ca_indicators(substr($raw, 0, 1048576));
        $hits = array_values(array_unique(array_merge($hits, psr_ca_scan_meta((int) $id))));
        $modified = (string) $post->post_modified_gmt;
        $date = (string) $post->post_date_gmt;
        $modified_time = $modified && $modified !== '0000-00-00 00:00:00' ? strtotime($modified . ' UTC') : false;
        $recent = $modified_time && $modified_time >= $since;
        $info = array(
            'id' => (int) $id,
            'title' => (string) $post->post_title,
            'type' => (string) $post->post_type,
            'status' => (string) $post->post_status,
            'created' => $date,
            'modified' => $modified,
        );
        if ($hits) {
            $state['suspicious_count']++;
            if (count($state['suspicious']) < 300) {
                $entry = $info;
                $entry['hits'] = $hits;
                $entry['priority'] = count(array_intersect($hits, array(
                    'Marca spam AGUSTOTO', 'Spam scommesse indonesiano',
                    'Dominio remoto compromesso noto',
                    'Riferimento a persistenza WP Link Helper',
                    'JavaScript con decodifica ed esecuzione'
                ))) ? 'Alta' : 'Da verificare';
                $state['suspicious'][] = $entry;
            }
        }
        if ($recent) {
            $state['recent_count']++;
            if (count($state['recent']) < 300) { $state['recent'][] = $info; }
        }
    }
    if (count($ids) < $batch_size || (int) $state['checked'] >= 20000) {
        $state['finished'] = true;
        $state['ended'] = gmdate('c');
        $state['truncated'] = (int) $state['checked'] >= 20000;
    }
    update_option('psr_ca_report_v1', $state, false);
    wp_send_json_success(array('finished' => (bool) $state['finished'],
        'checked' => (int) $state['checked'], 'suspicious' => (int) $state['suspicious_count'],
        'recent' => (int) $state['recent_count'], 'truncated' => (bool) $state['truncated']));
}
add_action('wp_ajax_psr_ca_batch', 'psr_ca_batch');

function psr_ca_table($items, $kind) {
    if (!$items) { echo '<p>Nessuna voce da mostrare per questa categoria.</p>'; return; }
    if ($kind === 'recent') {
        usort($items, function($a, $b) { return strcmp($b['modified'], $a['modified']); });
    } else {
        usort($items, function($a, $b) {
            $ah = isset($a['priority']) && $a['priority'] === 'Alta' ? 1 : 0;
            $bh = isset($b['priority']) && $b['priority'] === 'Alta' ? 1 : 0;
            return $ah === $bh ? strcmp($b['modified'], $a['modified']) : ($bh - $ah);
        });
    }
    echo '<div style="max-height:480px;overflow:auto"><table class="widefat striped"><thead><tr>';
    echo '<th>Pagina / contenuto</th><th>Tipo / stato</th><th>Ultima modifica (UTC)</th>';
    if ($kind === 'suspicious') { echo '<th>Priorità</th><th>Indicatori da verificare</th>'; }
    echo '<th>Apri</th></tr></thead><tbody>';
    foreach ($items as $item) {
        $id = (int) $item['id'];
        echo '<tr><td><strong>' . esc_html($item['title'] !== '' ? $item['title'] : '(senza titolo)') . '</strong> <small>#' . $id . '</small></td>';
        echo '<td>' . esc_html($item['type']) . ' / ' . esc_html($item['status']) . '</td>';
        echo '<td>' . esc_html($item['modified']) . '</td>';
        if ($kind === 'suspicious') {
            echo '<td><strong>' . esc_html(isset($item['priority']) ? $item['priority'] : 'Da verificare') . '</strong></td>';
            echo '<td>' . esc_html(implode(' · ', $item['hits'])) . '</td>';
        }
        $url = get_edit_post_link($id, '');
        echo '<td>' . ($url ? '<a href="' . esc_url($url) . '">Modifica</a>' : '—') . '</td></tr>';
    }
    echo '</tbody></table></div>';
}

function psr_ca_render() {
    if (!psr_ca_allowed()) { wp_die('Accesso negato', '', array('response' => 403)); }
    $report = get_option('psr_ca_report_v1', false);
    $since = is_array($report) && !empty($report['since']) ? $report['since'] : gmdate('Y-m-d', strtotime('-30 days'));
    $nonce = wp_create_nonce('psr_ca_manual_scan');
    ?>
    <div class="wrap">
      <h1>PSR Security Guard — Controllo contenuti</h1>
      <p><strong>Accesso riservato all'amministratore WordPress ID 1.</strong> La scansione non viene eseguita durante le visite pubbliche.</p>
      <div class="notice notice-info inline"><p>Questa analisi segnala contenuti sospetti e modifiche registrate da WordPress. <strong>Non prova da sola chi abbia modificato una pagina.</strong> Malware che sovrappone una pagina via file, .htaccess, cache o codice PHP può non lasciare traccia nel database. Le date possono essere alterate. Non viene cancellato nulla.</p></div>
      <p><label for="psr-ca-since">Mostra modifiche dal </label>
         <input type="date" id="psr-ca-since" value="<?php echo esc_attr($since); ?>" />
         <button type="button" class="button button-primary" id="psr-ca-start">Avvia controllo manuale</button>
         <span id="psr-ca-progress" aria-live="polite"></span></p>
      <?php if (is_array($report)) : ?>
      <h2>Ultima scansione</h2>
      <p><strong><?php echo number_format_i18n((int) $report['checked']); ?></strong> contenuti esaminati;
         <strong><?php echo number_format_i18n((int) $report['suspicious_count']); ?></strong> con indicatori sospetti;
         <strong><?php echo number_format_i18n((int) $report['recent_count']); ?></strong> modificati dal <?php echo esc_html($report['since']); ?>.
         <?php echo !empty($report['finished']) ? 'Scansione conclusa.' : 'Scansione incompleta: riavviarla.'; ?></p>
      <?php if (!empty($report['truncated'])) : ?><div class="notice notice-warning inline"><p>Limite di 20.000 contenuti raggiunto: rapporto parziale.</p></div><?php endif; ?>
      <?php if (count($report['suspicious']) >= 300 || count($report['recent']) >= 300) : ?><div class="notice notice-warning inline"><p>Sono mostrate al massimo 300 righe per elenco; i contatori includono tutte le corrispondenze.</p></div><?php endif; ?>
      <h2>Contenuti con indicatori da verificare</h2>
      <?php psr_ca_table($report['suspicious'], 'suspicious'); ?>
      <h2>Contenuti modificati nel periodo selezionato</h2>
      <?php psr_ca_table($report['recent'], 'recent'); ?>
      <?php endif; ?>
      <p><em>Campione iniziale da verificare: <code>https://www.comune.ali.me.it/come-raggiungerci/</code>. Non è considerato infetto soltanto perché l'URL è stato segnalato.</em></p>
    </div>
    <script>
    (function(){
      'use strict';
      var start = document.getElementById('psr-ca-start');
      var progress = document.getElementById('psr-ca-progress');
      var nonce = <?php echo wp_json_encode($nonce); ?>;
      var endpoint = <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>;
      var stopped = false;
      function request(action, more) {
        var data = new URLSearchParams();
        data.append('action', action); data.append('nonce', nonce);
        if (more) data.append('since', more);
        return fetch(endpoint, {method:'POST', credentials:'same-origin', body:data})
          .then(function(r) { return r.json(); })
          .then(function(resp) {
            if (!resp.success) { throw new Error(resp.data && resp.data.message || 'Errore scansione'); }
            return resp.data;
          });
      }
      function next() {
        if (stopped) return;
        request('psr_ca_batch').then(function(s) {
          progress.textContent = ' Esaminati: ' + s.checked + ' · sospetti: ' + s.suspicious + ' · modificati: ' + s.recent;
          if (s.finished) { progress.textContent += ' · completato'; location.reload(); }
          else { next(); }
        }).catch(function(err) { stopped=true; start.disabled=false; progress.textContent=' Errore: ' + err.message; });
      }
      start.addEventListener('click', function(){
        stopped=false; start.disabled=true;
        var d=document.getElementById('psr-ca-since').value;
        progress.textContent=' Avvio controllo...';
        request('psr_ca_start',d).then(next).catch(function(err){stopped=true;start.disabled=false;progress.textContent=' Errore: '+err.message;});
      });
    })();
    </script>
    <?php
}
