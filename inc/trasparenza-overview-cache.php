<?php
/** Cache del riepilogo: lettura immediata, ricalcolo solo nelle richieste dedicate. */
if (!defined('ABSPATH')) {
    exit;
}

function dci_at_overview_cache_key() {
    return 'dci_at_overview_v12';
}

function dci_at_overview_cache_is_valid($stats) {
    return is_array($stats) && isset(
        $stats['visible_sections'], $stats['empty_sections'], $stats['published'],
        $stats['generated_at'], $stats['revision'],
        $stats['empty_section_list'], $stats['extra_sections'], $stats['extra_section_list'],
        $stats['parent_sections'], $stats['parent_section_list'], $stats['visible_section_list']
    );
}

function dci_at_get_transparency_overview() {
    $cached = get_transient(dci_at_overview_cache_key());
    return dci_at_overview_cache_is_valid($cached) ? $cached
        : new WP_Error('dci_at_overview_pending', 'Il riepilogo viene preparato dopo il caricamento della pagina.');
}

function dci_at_overview_revision() {
    return (string) get_option('dci_at_overview_revision_v12', '');
}

function dci_at_overview_needs_refresh($stats) {
    return !dci_at_overview_cache_is_valid($stats)
        || time() - (int) $stats['generated_at'] >= 15 * MINUTE_IN_SECONDS
        || $stats['revision'] !== dci_at_overview_revision();
}

function dci_at_overview_snapshot($stats) {
    return hash('sha256', $stats['generated_at'] . ':' . $stats['revision']);
}

/** Estratti di solo testo; i testi integrali restano nelle rispettive sezioni. */
function dci_at_overview_excerpt($text) {
    $text = trim(preg_replace('/\s+/u', ' ', html_entity_decode(
        wp_strip_all_tags((string) $text, true), ENT_QUOTES | ENT_HTML5, 'UTF-8'
    )));
    return mb_strlen($text, 'UTF-8') > 240
        ? rtrim(mb_substr($text, 0, 237, 'UTF-8')) . '...' : $text;
}

/** Rimozione atomica: non elimina il blocco acquisito nel frattempo da altri. */
function dci_at_overview_release_lock($lock) {
    global $wpdb;
    $wpdb->query($wpdb->prepare(
        "DELETE FROM {$wpdb->options} WHERE option_name = %s AND option_value = %s",
        'dci_at_overview_lock_v12', maybe_serialize($lock)
    ));
    wp_cache_delete('dci_at_overview_lock_v12', 'options');
    wp_cache_delete('notoptions', 'options');
}

function dci_at_overview_acquire_lock() {
    global $wpdb;
    $key = 'dci_at_overview_lock_v12';
    $previous = get_option($key, false);
    if (is_array($previous) && isset($previous['expires']) && $previous['expires'] < time()) {
        dci_at_overview_release_lock($previous);
    }
    $lock = array('token' => wp_generate_uuid4(), 'expires' => time() + 180);
    // INSERT IGNORE usa la chiave univoca delle opzioni e non sovrascrive
    // mai il proprietario esistente, anche con letture della cache simultanee.
    $inserted = $wpdb->query($wpdb->prepare(
        "INSERT IGNORE INTO {$wpdb->options} (option_name, option_value, autoload) VALUES (%s, %s, %s)",
        $key, maybe_serialize($lock), 'no'
    ));
    if (1 !== $inserted) {
        return false;
    }
    wp_cache_delete($key, 'options');
    wp_cache_delete('notoptions', 'options');
    return $lock;
}

function dci_at_overview_refresh_cache() {
    $cached = dci_at_get_transparency_overview();
    if (!dci_at_overview_needs_refresh($cached)) {
        return $cached;
    }
    if (get_transient('dci_at_overview_retry_v12')) {
        return new WP_Error('dci_at_overview_retry', 'Aggiornamento temporaneamente non disponibile. Riprova tra un minuto.');
    }
    $lock = dci_at_overview_acquire_lock();
    if (!$lock) {
        return new WP_Error('dci_at_overview_busy', 'Un aggiornamento è già in corso. Riprova tra qualche istante.');
    }
    try {
        // Un'altra richiesta potrebbe avere terminato prima dell'acquisizione.
        $cached = dci_at_get_transparency_overview();
        if (!dci_at_overview_needs_refresh($cached)) {
            return $cached;
        }
        $revision = dci_at_overview_revision();
        $result = dci_at_build_transparency_overview();
        if (is_wp_error($result)) {
            set_transient('dci_at_overview_retry_v12', true, MINUTE_IN_SECONDS);
            return $result;
        }
        $result['revision'] = $revision;
        set_transient(dci_at_overview_cache_key(), $result, DAY_IN_SECONDS);
        return $result;
    } catch (Throwable $error) {
        set_transient('dci_at_overview_retry_v12', true, MINUTE_IN_SECONDS);
        return new WP_Error('dci_at_overview_unavailable', 'Aggiornamento temporaneamente non disponibile. Riprova tra un minuto.');
    } finally {
        dci_at_overview_release_lock($lock);
    }
}

/** Entrambi gli endpoint sono disponibili soltanto agli operatori autorizzati. */
function dci_at_overview_ajax_check_access() {
    if (!current_user_can('edit_elementi_trasparenza')) {
        wp_send_json_error(array('message' => 'Non hai i permessi per consultare il riepilogo.'), 403);
    }
    check_ajax_referer('dci_at_overview', 'nonce');
}

add_action('wp_ajax_dci_at_overview_refresh', 'dci_at_overview_ajax_refresh');
function dci_at_overview_ajax_refresh() {
    dci_at_overview_ajax_check_access();
    $stats = dci_at_overview_refresh_cache();
    if (is_wp_error($stats)) {
        wp_send_json_error(array('message' => $stats->get_error_message()), 503);
    }
    ob_start();
    dci_at_render_overview_content($stats);
    $html = ob_get_clean();
    wp_send_json_success(array(
        'html' => $html,
        'snapshot' => dci_at_overview_snapshot($stats),
        'stale' => dci_at_overview_needs_refresh($stats),
    ));
}

add_action('wp_ajax_dci_at_overview_sections', 'dci_at_overview_ajax_sections');
function dci_at_overview_ajax_sections() {
    dci_at_overview_ajax_check_access();
    $kind = isset($_POST['kind']) && is_string($_POST['kind']) ? sanitize_key(wp_unslash($_POST['kind'])) : '';
    $panels = dci_at_overview_panels();
    if (!isset($panels[$kind])) {
        wp_send_json_error(array('message' => 'Elenco non valido.'), 400);
    }
    // Gli elenchi non avviano mai il ricalcolo delle statistiche.
    $stats = dci_at_get_transparency_overview();
    $snapshot = isset($_POST['snapshot']) && is_string($_POST['snapshot']) ? sanitize_text_field(wp_unslash($_POST['snapshot'])) : '';
    if (is_wp_error($stats) || !hash_equals(dci_at_overview_snapshot($stats), $snapshot)) {
        wp_send_json_error(array('message' => 'Il riepilogo è cambiato. Premi “Aggiorna statistiche” e riapri l’elenco.'), 409);
    }
    $items = $stats[$kind . '_section_list'];
    $pages = max(1, (int) ceil(count($items) / 50));
    $page = isset($_POST['page']) ? min($pages, max(1, (int) $_POST['page'])) : 1;
    ob_start();
    dci_at_render_overview_section_items($kind, array_slice($items, ($page - 1) * 50, 50));
    $html = ob_get_clean();
    wp_send_json_success(array('html' => $html, 'page' => $page, 'pages' => $pages, 'total' => count($items)));
}
