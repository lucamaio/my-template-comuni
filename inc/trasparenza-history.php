<?php
/** Registro limitato delle ricariche. Nessun hook o accesso DB nella navigazione pubblica. */
if (!defined('ABSPATH')) { exit; }

function dci_at_history_types() {
    return ['structure' => 'Struttura Trasparenza', 'descriptions' => 'Descrizioni', 'ordering' => 'Ordinamento', 'normativa' => 'Riferimenti normativi', 'setup' => 'Prima inizializzazione'];
}

function dci_at_history_text($value, $limit = 120) {
    $text = trim(wp_strip_all_tags((string) $value));
    if (function_exists('mb_substr')) { return mb_substr($text, 0, $limit, 'UTF-8'); }
    return preg_match('/^.{0,' . (int) $limit . '}/us', $text, $match) ? $match[0] : '';
}

/** Accumula in memoria soltanto le modifiche riuscite, senza query aggiuntive per termine. */
function dci_at_history_change($field, $id, $name, $before, $after) {
    global $dci_at_history_context;
    if (!is_array($dci_at_history_context)) { return; }
    $dci_at_history_context['entry']['changed']++;
    if (count($dci_at_history_context['entry']['changes']) >= 50) { return; }
    $dci_at_history_context['entry']['changes'][] = [
        'field' => dci_at_history_text($field, 32), 'id' => (int) $id,
        'name' => dci_at_history_text($name),
        'before' => dci_at_history_text($before), 'after' => dci_at_history_text($after),
    ];
}

function dci_at_history_prefix($type) { return 'dci_at_history_v1_' . $type . '_'; }

/** Rimuove i record oltre i dieci più recenti usando l'indice option_name. */
function dci_at_history_prune($type) {
    global $wpdb;
    $pattern = $wpdb->esc_like(dci_at_history_prefix($type)) . '%';
    $old = $wpdb->get_col($wpdb->prepare(
        "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s ORDER BY option_name DESC LIMIT 100 OFFSET 10", $pattern
    ));
    if (!empty($wpdb->last_error)) { $GLOBALS['dci_at_history_save_failed'] = true; return; }
    if (!$old) { return; }
    $placeholders = implode(',', array_fill(0, count($old), '%s'));
    if ($wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name IN ($placeholders)", $old)) === false) {
        $GLOBALS['dci_at_history_save_failed'] = true;
        return;
    }
    foreach ($old as $key) { wp_cache_delete($key, 'options'); }
}

function dci_at_history_finish($result, $interrupted = false) {
    global $dci_at_history_context;
    if (!is_array($dci_at_history_context)) { return; }
    $context = $dci_at_history_context;
    $dci_at_history_context = null;
    $entry = $context['entry'];
    $entry['finished'] = time();
    $entry['duration_ms'] = (int) round((microtime(true) - $context['clock']) * 1000);
    $entry['status'] = $interrupted || is_wp_error($result) ? 'error' : 'success';
    $entry['summary'] = [];
    if (is_wp_error($result)) {
        $entry['message'] = dci_at_history_text($result->get_error_message(), 240);
    } elseif (is_array($result)) {
        foreach ($result as $key => $value) {
            if (count($entry['summary']) >= 16) { break; }
            if (is_int($value) || is_float($value)) {
                $entry['summary'][dci_at_history_text($key, 40)] = (int) $value;
            }
            if ($value && (strpos((string) $key, 'error') !== false || $key === 'missing' || strpos((string) $key, '_missing') !== false)) {
                $entry['status'] = 'warning';
                if (is_string($value)) { $entry['message'] = dci_at_history_text($value, 240); }
            }
        }
    }
    // Tetto rigido anche con caratteri multibyte o nomi particolarmente lunghi.
    while (strlen(serialize($entry)) > 24576 && $entry['changes']) { array_pop($entry['changes']); }
    if (!update_option($context['key'], $entry, false)) {
        $GLOBALS['dci_at_history_save_failed'] = true;
    }
    dci_at_history_prune($entry['type']);
}

function dci_at_history_finish_safely($result, $interrupted = false) {
    try { dci_at_history_finish($result, $interrupted); }
    catch (Throwable $error) {
        $GLOBALS['dci_at_history_context'] = null;
        $GLOBALS['dci_at_history_save_failed'] = true;
    }
}

/** Un record indipendente per richiesta: nessun array condiviso da sovrascrivere in concorrenza. */
function dci_at_history_run($type, $callback) {
    global $dci_at_history_context;
    if (!isset(dci_at_history_types()[$type]) || is_array($dci_at_history_context)) { return $callback(); }
    $clock = microtime(true);
    $key = dci_at_history_prefix($type) . sprintf('%020.0f', $clock * 1000000) . '_' . wp_generate_password(12, false, false);
    $user = wp_get_current_user();
    $entry = ['type' => $type, 'started' => time(), 'finished' => 0, 'status' => 'running',
        'user_id' => (int) $user->ID, 'user' => dci_at_history_text($user->display_name), 'changed' => 0, 'changes' => []];
    try { $saved = add_option($key, $entry, '', false); }
    catch (Throwable $error) { $saved = false; }
    if (!$saved) {
        $GLOBALS['dci_at_history_save_failed'] = true;
        return $callback();
    }
    $dci_at_history_context = ['key' => $key, 'clock' => $clock, 'entry' => $entry];
    try { dci_at_history_prune($type); }
    catch (Throwable $error) { $GLOBALS['dci_at_history_save_failed'] = true; }
    static $shutdown_registered = false;
    if (!$shutdown_registered) {
        register_shutdown_function(static function () {
            if (is_array($GLOBALS['dci_at_history_context'] ?? null)) {
                dci_at_history_finish_safely(new WP_Error('interrupted', 'Operazione interrotta prima del completamento; verificare le modifiche parziali.'), true);
            }
        });
        $shutdown_registered = true;
    }
    try {
        $result = $callback();
    } catch (Throwable $error) {
        dci_at_history_finish_safely(new WP_Error('exception', 'Operazione interrotta da un errore; consultare il registro PHP.'), true);
        throw $error;
    }
    dci_at_history_finish_safely($result);
    return $result;
}

function dci_at_history_render() {
    if (get_current_user_id() != 1 || !current_user_can('edit_theme_options')) { return; }
    global $wpdb;
    $labels = dci_at_history_types();
    $filter = isset($_GET['at_history_type']) && is_string($_GET['at_history_type']) ? sanitize_key($_GET['at_history_type']) : '';
    if (!isset($labels[$filter])) { $filter = ''; }
    $entries = [];
    echo '<h2>Ultime operazioni di ricarica</h2>';
    if (!empty($GLOBALS['dci_at_history_save_failed'])) {
        echo '<div class="notice notice-warning"><p>Non è stato possibile salvare completamente lo storico. Verifica l’esito della ricarica separatamente.</p></div>';
    }
    echo '<p>Ultime 10 operazioni per tipo; fino a 50 modifiche per operazione, con valori abbreviati. Lo storico parte dall’attivazione di questa funzione.</p>';
    echo '<form method="get" action="' . esc_url(admin_url('themes.php')) . '"><input type="hidden" name="page" value="reload-trasparenza-theme-options"><label for="at-history-type">Operazioni da consultare </label><select id="at-history-type" name="at_history_type"><option value="">Ultima operazione per tipo</option>';
    foreach ($labels as $type => $label) { echo '<option value="' . esc_attr($type) . '"' . selected($filter, $type, false) . '>' . esc_html($label) . '</option>'; }
    echo '</select> <button class="button" type="submit">Mostra storico</button></form><ul>';
    foreach ($labels as $type => $label) {
        $rows = $wpdb->get_col($wpdb->prepare(
            "SELECT option_value FROM {$wpdb->options} WHERE option_name LIKE %s ORDER BY option_name DESC LIMIT %d",
            $wpdb->esc_like(dci_at_history_prefix($type)) . '%', $filter === $type ? 10 : 1
        ));
        $last = null;
        foreach ($rows as $raw) {
            $entry = maybe_unserialize($raw);
            if (!is_array($entry) || empty($entry['started']) || ($entry['type'] ?? '') !== $type) { continue; }
            if ($filter === '' || $filter === $type) { $entries[] = $entry; }
            if ($last === null) { $last = $entry; }
        }
        echo '<li><strong>' . esc_html($label) . ':</strong> ' . ($last ? esc_html(wp_date('d/m/Y H:i:s', $last['started'])) : 'nessuna operazione registrata') . '</li>';
    }
    echo '</ul>';
    if (!$entries) { return; }
    usort($entries, static function ($a, $b) { return $b['started'] <=> $a['started']; });
    $statuses = ['running' => 'Avviata — completamento non registrato', 'success' => 'Completata', 'warning' => 'Completata con segnalazioni', 'error' => 'Errore / interruzione'];
    echo '<details><summary>Mostra lo storico e le modifiche</summary><table class="widefat striped"><thead><tr><th>Operazione e data</th><th>Utente</th><th>Esito</th><th>Modifiche</th></tr></thead><tbody>';
    foreach ($entries as $entry) {
        echo '<tr><td>' . esc_html($labels[$entry['type']]) . '<br>' . esc_html(wp_date('d/m/Y H:i:s', $entry['started']));
        if (!empty($entry['finished'])) { echo '<br>Fine: ' . esc_html(wp_date('d/m/Y H:i:s', $entry['finished'])); }
        echo '</td><td>' . esc_html($entry['user']) . ' (ID ' . (int) $entry['user_id'] . ')</td><td>' . esc_html($statuses[$entry['status']] ?? $entry['status']);
        if (isset($entry['duration_ms'])) { echo '<br>' . esc_html(number_format_i18n($entry['duration_ms'] / 1000, 2)) . ' s'; }
        if (!empty($entry['message'])) { echo '<br>' . esc_html($entry['message']); }
        echo '</td><td>' . (int) $entry['changed'] . ' modifiche registrate';
        if (!empty($entry['summary'])) {
            echo '<details><summary>Riepilogo operazione</summary><ul>';
            $stat_labels = ['created'=>'Categorie create', 'renamed'=>'Categorie rinominate', 'moved'=>'Categorie spostate', 'inserted'=>'Categorie inserite', 'updated'=>'Aggiornamenti', 'descriptions_updated'=>'Descrizioni aggiornate', 'descriptions_errors'=>'Errori descrizioni', 'ordering_updated'=>'Ordinamenti aggiornati', 'normativa_updated'=>'Riferimenti aggiornati', 'unchanged'=>'Invariati', 'missing'=>'Senza corrispondenza', 'errors'=>'Errori', 'normativa_missing'=>'Riferimenti senza corrispondenza', 'normativa_errors'=>'Errori riferimenti', 'normativa_unchanged'=>'Riferimenti invariati'];
            foreach ($entry['summary'] as $key => $value) { echo '<li>' . esc_html($stat_labels[$key] ?? $key) . ': ' . (int) $value . '</li>'; }
            echo '</ul></details>';
        }
        if (!empty($entry['changes'])) {
            echo '<details><summary>Dettagli (' . count($entry['changes']) . ' di ' . (int) $entry['changed'] . ')</summary><ul>';
            foreach ($entry['changes'] as $change) {
                echo '<li>' . esc_html($change['name']) . ' (ID ' . (int) $change['id'] . ') — ' . esc_html($change['field']) . ': ' . esc_html($change['before']) . ' → ' . esc_html($change['after']) . '</li>';
            }
            echo '</ul></details>';
        }
        echo '</td></tr>';
    }
    echo '</tbody></table></details>';
}
