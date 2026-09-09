<?php
/** Spostamento controllato delle sole associazioni alla tassonomia Trasparenza. */
if (!defined('ABSPATH')) {
    exit;
}

function dci_at_bulk_move_allowed() {
    $taxonomy = get_taxonomy('tipi_cat_amm_trasp');
    return 1 === (int) get_current_user_id()
        && in_array('administrator', (array) wp_get_current_user()->roles, true)
        && current_user_can('manage_options')
        && $taxonomy
        && current_user_can($taxonomy->cap->assign_terms);
}

add_filter('bulk_actions-edit-elemento_trasparenza', 'dci_at_bulk_move_action');
function dci_at_bulk_move_action($actions) {
    if (dci_at_bulk_move_allowed()) {
        $actions['dci_at_move_section'] = 'Sposta in un’altra sezione';
    }
    return $actions;
}

add_action('manage_posts_extra_tablenav', 'dci_at_bulk_move_controls');
function dci_at_bulk_move_controls($which) {
    $screen = get_current_screen();
    if (!$screen || 'edit-elemento_trasparenza' !== $screen->id || 'top' !== $which || !dci_at_bulk_move_allowed()) {
        return;
    }
    $options = dci_get_visible_amministrazione_terms();
    ?>
    <section id="dci-at-bulk-move" class="dci-at-bulk-move" aria-labelledby="dci-at-bulk-move-title" hidden>
        <div class="dci-at-bulk-move__heading">
            <span class="dashicons dashicons-move" aria-hidden="true"></span>
            <div>
                <h3 id="dci-at-bulk-move-title">Sposta in un’altra sezione</h3>
                <p>Assegna gli elementi selezionati a una nuova sezione della Trasparenza.</p>
            </div>
            <span class="dci-at-bulk-move__limit">Massimo 50 elementi</span>
        </div>
        <div class="dci-at-bulk-move__fields">
        <div class="dci-at-bulk-move__destination">
        <label for="dci-at-move-target">Sezione di destinazione</label>
        <select id="dci-at-move-target" name="dci_at_move_target" aria-describedby="dci-at-move-help" disabled>
            <option value="">Scegli la nuova sezione…</option>
            <?php foreach ($options as $id => $label) : ?>
                <?php if ('1' === (string) get_term_meta($id, 'obbligo_non_applicabile', true)) { continue; } ?>
                <option value="<?php echo esc_attr($id); ?>"><?php echo esc_html($label); ?></option>
            <?php endforeach; ?>
        </select>
        </div>
        <label class="dci-at-bulk-move__confirmation">
            <input type="checkbox" name="dci_at_move_confirm" value="1" disabled>
            <span><strong>Confermo lo spostamento</strong>La nuova sezione sostituirà tutte le sezioni attualmente associate agli elementi selezionati.</span>
        </label>
        </div>
        <p id="dci-at-move-help" class="dci-at-bulk-move__help">Scegli la destinazione, conferma lo spostamento e premi <strong>Applica</strong>. I contenuti e gli allegati vengono conservati.</p>
    </section>
    <noscript><p>Per usare “Sposta in un’altra sezione” è necessario abilitare JavaScript nel browser.</p></noscript>
    <?php
}

add_action('admin_enqueue_scripts', 'dci_at_bulk_move_assets');
function dci_at_bulk_move_assets() {
    $screen = get_current_screen();
    if (!$screen || 'edit-elemento_trasparenza' !== $screen->id || !dci_at_bulk_move_allowed()) {
        return;
    }
    $path = get_template_directory() . '/inc/admin-js/trasparenza-bulk-move.js';
    wp_enqueue_script('dci-at-bulk-move', get_template_directory_uri() . '/inc/admin-js/trasparenza-bulk-move.js', array(), filemtime($path), true);
}

/** Legge gli ID effettivi dal database, senza usare cache delle relazioni. */
function dci_at_bulk_move_term_ids($post_id) {
    $ids = wp_get_object_terms($post_id, 'tipi_cat_amm_trasp', array('fields' => 'ids'));
    if (is_wp_error($ids)) {
        return $ids;
    }
    $ids = array_values(array_unique(array_map('intval', $ids)));
    sort($ids);
    return $ids;
}

/**
 * Sposta un elemento già autorizzato. La destinazione viene aggiunta e riletta
 * prima di rimuovere le vecchie associazioni: un inserimento fallito non le perde.
 * Il metadato conserva la situazione precedente all'ultimo tentativo, per recupero.
 */
function dci_at_bulk_move_one($post_id, $target_id) {
    $before = dci_at_bulk_move_term_ids($post_id);
    if (is_wp_error($before)) {
        return 'failed';
    }
    if (array($target_id) === $before) {
        return 'unchanged';
    }

    $backup = array(
        'term_ids' => $before,
        'target_id' => $target_id,
        'user_id' => get_current_user_id(),
        'created_at' => gmdate('c'),
        'operation_id' => wp_generate_uuid4(),
    );
    if (!update_post_meta($post_id, '_dci_at_last_section_move', $backup)) {
        return 'failed';
    }

    $added = wp_add_object_terms($post_id, array($target_id), 'tipi_cat_amm_trasp');
    $after_add = dci_at_bulk_move_term_ids($post_id);
    if (is_wp_error($added) || is_wp_error($after_add) || !in_array($target_id, $after_add, true)) {
        return 'failed';
    }

    // Se le associazioni sono cambiate nel frattempo, non rimuovere nulla.
    $expected = array_values(array_unique(array_merge($before, array($target_id))));
    sort($expected);
    if ($after_add !== $expected) {
        return 'failed';
    }

    $old_ids = array_values(array_diff($before, array($target_id)));
    if ($old_ids) {
        wp_remove_object_terms($post_id, $old_ids, 'tipi_cat_amm_trasp');
    }
    $after = dci_at_bulk_move_term_ids($post_id);
    if (array($target_id) !== $after) {
        // Recupero conservativo: riaggiunge le origini senza cancellare altro.
        if ($before) {
            wp_add_object_terms($post_id, $before, 'tipi_cat_amm_trasp');
        }
        return 'failed';
    }
    return 'moved';
}

add_filter('handle_bulk_actions-edit-elemento_trasparenza', 'dci_at_bulk_move_handle', 10, 3);
function dci_at_bulk_move_handle($redirect, $action, $post_ids) {
    if ('dci_at_move_section' !== $action) {
        return $redirect;
    }
    if (!dci_at_bulk_move_allowed()) {
        wp_die('Non hai i permessi per spostare gli elementi.', '', array('response' => 403));
    }
    check_admin_referer('bulk-posts');
    $redirect = remove_query_arg(array('dci_at_move_target', 'dci_at_move_confirm', 'dci_at_move_status', 'dci_at_moved', 'dci_at_unchanged', 'dci_at_skipped', 'dci_at_failed'), $redirect);
    $post_ids = array_values(array_unique(array_filter(array_map('absint', (array) $post_ids))));
    if (!$post_ids || count($post_ids) > 50) {
        return add_query_arg('dci_at_move_status', 'limit', $redirect);
    }
    if (!isset($_REQUEST['dci_at_move_confirm']) || '1' !== $_REQUEST['dci_at_move_confirm']) {
        return add_query_arg('dci_at_move_status', 'confirm', $redirect);
    }
    $raw_target = $_REQUEST['dci_at_move_target'] ?? '';
    $target_id = is_scalar($raw_target) ? absint($raw_target) : 0;
    $options = dci_get_visible_amministrazione_terms();
    $target = $target_id ? get_term($target_id, 'tipi_cat_amm_trasp') : null;
    if (!$target instanceof WP_Term || !isset($options[$target_id])
        || '1' === (string) get_term_meta($target_id, 'obbligo_non_applicabile', true)) {
        return add_query_arg('dci_at_move_status', 'target', $redirect);
    }

    // Lock atomico, non autoload, per evitare due spostamenti massivi concorrenti.
    // Non scade automaticamente: un processo interrotto richiede verifica prima
    // di rimuovere il lock, per non sovrapporsi a un processo ancora in esecuzione.
    // add_option() usa un UPSERT e non è un mutex affidabile in caso di concorrenza.
    global $wpdb;
    $lock_token = wp_generate_uuid4();
    $locked = $wpdb->query($wpdb->prepare(
        "INSERT IGNORE INTO {$wpdb->options} (option_name, option_value, autoload) VALUES (%s, %s, %s)",
        'dci_at_bulk_move_lock', $lock_token, 'no'
    ));
    if (1 !== $locked) {
        return add_query_arg('dci_at_move_status', 'busy', $redirect);
    }
    $counts = array('moved' => 0, 'unchanged' => 0, 'skipped' => 0);
    $failed = array();
    $started = microtime(true);
    $was_deferred = wp_defer_term_counting();
    try {
        wp_defer_term_counting(true);
        foreach ($post_ids as $post_id) {
            if (microtime(true) - $started > 15) {
                $counts['skipped']++;
                continue;
            }
            $post = get_post($post_id);
            if (!$post || 'elemento_trasparenza' !== $post->post_type
                || in_array($post->post_status, array('trash', 'auto-draft'), true)
                || !current_user_can('edit_post', $post_id) || wp_check_post_lock($post_id)) {
                $counts['skipped']++;
                continue;
            }
            $result = dci_at_bulk_move_one($post_id, $target_id);
            if ('failed' === $result) {
                $failed[] = $post_id;
                // Un errore può indicare problemi al database: interrompi le scritture.
                break;
            }
            $counts[$result]++;
        }
        $counts['skipped'] = count($post_ids) - $counts['moved'] - $counts['unchanged'] - count($failed);
    } finally {
        try {
            wp_defer_term_counting($was_deferred);
        } finally {
            $wpdb->query($wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name = %s AND option_value = %s",
                'dci_at_bulk_move_lock', $lock_token
            ));
        }
    }
    return add_query_arg(array(
        'dci_at_move_status' => 'done',
        'dci_at_moved' => $counts['moved'],
        'dci_at_unchanged' => $counts['unchanged'],
        'dci_at_skipped' => $counts['skipped'],
        'dci_at_failed' => implode(',', $failed),
    ), $redirect);
}

add_action('admin_notices', 'dci_at_bulk_move_notice');
function dci_at_bulk_move_notice() {
    $screen = get_current_screen();
    if (!$screen || 'edit-elemento_trasparenza' !== $screen->id || !dci_at_bulk_move_allowed()
        || empty($_GET['dci_at_move_status']) || !is_string($_GET['dci_at_move_status'])) {
        return;
    }
    $messages = array(
        'limit' => 'Seleziona da 1 a 50 elementi per operazione. Nessun elemento è stato spostato.',
        'confirm' => 'Conferma la sostituzione delle sezioni prima di applicare lo spostamento. Nessun elemento è stato spostato.',
        'target' => 'Seleziona una sezione interna disponibile per gli Elementi Trasparenza e con obbligo applicabile. Nessun elemento è stato spostato.',
        'busy' => 'Uno spostamento è già in corso oppure è stato interrotto. Nessuna nuova operazione avviata. Se il blocco persiste, richiedi una verifica tecnica.',
    );
    $status = sanitize_key($_GET['dci_at_move_status']);
    $message = $messages[$status] ?? '';
    if ('done' === $status) {
        $number = static function ($key) { return isset($_GET[$key]) && is_scalar($_GET[$key]) ? absint($_GET[$key]) : 0; };
        $message = sprintf('Spostamento concluso. Spostati: %d. Già nella destinazione: %d. Non elaborati (permessi, blocco di modifica, limite di tempo o interruzione): %d.', $number('dci_at_moved'), $number('dci_at_unchanged'), $number('dci_at_skipped'));
        if (!empty($_GET['dci_at_failed']) && is_string($_GET['dci_at_failed'])) {
            $message .= ' Operazione interrotta per errore: verifica le associazioni dell’elemento ID ' . absint($_GET['dci_at_failed']) . '. La copia delle sezioni precedenti è conservata, se il salvataggio è riuscito.';
        }
    }
    if ($message !== '') {
        echo '<div class="notice notice-info is-dismissible"><p>' . esc_html($message) . '</p></div>';
    }
}
