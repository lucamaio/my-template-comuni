<?php
/**
 * Pagina di gestione permessi categorie per ruolo
 * File: tipologia_gestioneruolo_amm.php
 */

/**
 * CREA CAPABILITY E COLLEGA ALLA TASSONOMIA
 */

add_filter('user_has_cap', 'dci_limita_capacita_trasparenza', 10, 4);

function dci_limita_capacita_trasparenza($allcaps, $caps, $args, $user) {
    // Controlla che la capability richiesta sia la nostra personalizzata
    if (in_array('gestione_permessi_trasparenza', $caps)) {

        // Lista degli ID utente autorizzati   ES: 1, 5 , 6
        // Per ora solo il nostro utente visiona permessi trasparente e aggiungere e modificare le tipologie
        $utenti_autorizzati = [1]; // <-- Aggiungi qui gli ID che vuoi abilitare

        if (in_array($user->ID, $utenti_autorizzati)) {
            $allcaps['gestione_permessi_trasparenza'] = true;
        } else {
            $allcaps['gestione_permessi_trasparenza'] = false;
        }
    }

    return $allcaps;
}



// Collega tassonomia alla capability
add_action('init', 'dci_collega_capacita_tassonomia', 11);
function dci_collega_capacita_tassonomia() {
    $taxonomy = 'tipi_cat_amm_trasp';
    global $wp_taxonomies;

    if (!empty($wp_taxonomies[$taxonomy])) {
        $wp_taxonomies[$taxonomy]->cap = (object) array(
            'manage_terms' => 'gestione_permessi_trasparenza',
            'edit_terms'   => 'gestione_permessi_trasparenza',
            'delete_terms' => 'gestione_permessi_trasparenza',
            'assign_terms' => 'gestione_permessi_trasparenza',
        );
    }
}

/**
 * VOCE DI MENU E GESTIONE PERMESSI
 */

// Aggiungi voce al menu admin
add_action('admin_menu', 'dci_add_permessi_ruoli_submenu');
function dci_add_permessi_ruoli_submenu() {
    if ( current_user_can('gestione_permessi_trasparenza') ) {
        add_submenu_page(
            'edit.php?post_type=elemento_trasparenza',
            __('Gestione Permessi Ruoli', 'design_comuni_italia'),
            __('Permessi Trasparenza', 'design_comuni_italia'),
            'gestione_permessi_trasparenza',
            'gestione_permessi_ruoli',
            'dci_render_permessi_ruoli_page'
        );
    }
}

// Render pagina
function dci_render_permessi_ruoli_page() {
    $ruoli = wp_roles()->roles;
    $ruolo_selezionato = isset($_GET['ruolo']) ? sanitize_text_field($_GET['ruolo']) : '';
    $categorie = get_terms(array(
        'taxonomy' => 'tipi_cat_amm_trasp',
        'hide_empty' => false,
    ));
    ?>
    <div class="wrap">
        <h1><?php _e('Permessi per Ruolo - Trasparenza', 'design_comuni_italia'); ?></h1>
        <div style="display: flex; gap: 2rem; align-items: flex-start;">

            <!-- COLONNA RUOLI -->
            <div style="width: 250px;">
                <h2><?php _e('Ruoli', 'design_comuni_italia'); ?></h2>
                <ul style="list-style: none; padding: 0;">
                    <?php foreach ($ruoli as $slug => $dati): ?>
                        <li style="margin-bottom: 0.5rem;">
                            <a href="<?php echo admin_url('admin.php?page=gestione_permessi_ruoli&ruolo=' . esc_attr($slug)); ?>"
                               style="<?php echo ($slug === $ruolo_selezionato) ? 'font-weight: bold;' : ''; ?>">
                                <?php echo esc_html(translate_user_role($dati['name'])); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- COLONNA CATEGORIE -->
            <div style="flex: 1;">
                <?php if ($ruolo_selezionato): ?>
                    <h2><?php echo sprintf(__('Permessi per il ruolo: %s', 'design_comuni_italia'), translate_user_role($ruoli[$ruolo_selezionato]['name'])); ?></h2>

                    <form method="post">
                        <?php wp_nonce_field('salva_permessi_ruoli', 'permessi_ruoli_nonce'); ?>
                        <input type="hidden" name="ruolo" value="<?php echo esc_attr($ruolo_selezionato); ?>">
                        <p>
                            <button type="button" class="button" id="seleziona-tutti"><?php _e('Seleziona tutto', 'design_comuni_italia'); ?></button>
                            <button type="button" class="button" id="deseleziona-tutti"><?php _e('Deseleziona tutto', 'design_comuni_italia'); ?></button>
                            <button type="button" class="button button-secondary" id="proponi-contabilita" style="margin-left: 2rem;"><?php _e('Proponi abilitazioni Contabilità', 'design_comuni_italia'); ?></button>
                        </p>

                        <table class="widefat fixed striped">
                            <thead>
                                <tr>
                                    <th><?php _e('Categoria', 'design_comuni_italia'); ?></th>
                                    <th><?php _e('Accesso consentito?', 'design_comuni_italia'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                function stampa_gerarchia_categorie($termini, $ruolo_selezionato, $parent_id = 0, $livello = 0) {
                                    foreach ($termini as $term) {
                                        if ($term->parent != $parent_id) continue;
                                        $excluded_roles = get_term_meta($term->term_id, 'excluded_roles', true);
                                        if (is_string($excluded_roles)) {
                                            $excluded_roles = maybe_unserialize($excluded_roles);
                                        }
                                        if (!is_array($excluded_roles)) {
                                            $excluded_roles = [];
                                        }
                                        $checked = !in_array($ruolo_selezionato, $excluded_roles);

                                        if ($livello === 0) {
                                            $symbol = '●';
                                        } elseif ($livello === 1) {
                                            $symbol = '➤';
                                        } elseif ($livello === 2) {
                                            $symbol = '➔';
                                        } else {
                                            $symbol = str_repeat('·', $livello - 2);
                                        }
                                        $indent_px = $livello * 20;

                                        echo '<tr>';
                                        $style = 'padding-left: ' . esc_attr($indent_px) . 'px;';
                                        if ($livello === 0) {
                                            $style .= ' font-weight: bold;';
                                        }
                                        echo '<td><span style="' . esc_attr($style) . '">' . esc_html($symbol) . ' ' . esc_html($term->name) . '</span></td>';
                                        echo '<td><input type="checkbox" name="permessi_ruolo[]" value="' . esc_attr($term->term_id) . '" ' . checked($checked, true, false) . '></td>';
                                        echo '</tr>';

                                        stampa_gerarchia_categorie($termini, $ruolo_selezionato, $term->term_id, $livello + 1);
                                    }
                                }
                                stampa_gerarchia_categorie($categorie, $ruolo_selezionato);
                                ?>
                            </tbody>
                        </table>

                        <p><?php submit_button(__('Salva Permessi', 'design_comuni_italia'), 'primary', 'salva_permessi_ruolo', false); ?></p>
                    </form>

<script>
document.getElementById('seleziona-tutti').addEventListener('click', function () {
    document.querySelectorAll('input[type="checkbox"][name="permessi_ruolo[]"]').forEach(cb => cb.checked = true);
});
document.getElementById('deseleziona-tutti').addEventListener('click', function () {
    document.querySelectorAll('input[type="checkbox"][name="permessi_ruolo[]"]').forEach(cb => cb.checked = false);
});
document.getElementById('proponi-contabilita').addEventListener('click', function () {
    const rows = document.querySelectorAll('table tbody tr');
    let bilanciFound = false;
    let startSelecting = false;
    document.querySelectorAll('input[type="checkbox"][name="permessi_ruolo[]"]').forEach(cb => cb.checked = false);
    rows.forEach(row => {
        const label = row.querySelector('td span');
        const checkbox = row.querySelector('input[type="checkbox"]');
        if (!label || !checkbox) return;
        const text = label.textContent.trim().toLowerCase();
        if (text.includes('bilanci')) {
            checkbox.checked = true;
            startSelecting = true;
            bilanciFound = true;
            return;
        }
        if (startSelecting) {
            const style = window.getComputedStyle(label);
            const paddingLeft = parseInt(style.paddingLeft);
            if (paddingLeft <= 20) {
                startSelecting = false;
            } else {
                checkbox.checked = true;
            }
        }
    });
    if (!bilanciFound) {
        alert("⚠️ Nessuna voce 'Bilanci' trovata.");
    }
});
</script>
                <?php else: ?>
                    <p><?php _e('Seleziona un ruolo a sinistra per gestire i permessi.', 'design_comuni_italia'); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
}

// Gestione salvataggio permessi
add_action('admin_init', 'dci_salva_permessi_ruoli');
function dci_salva_permessi_ruoli() {
    if (
        isset($_POST['salva_permessi_ruolo']) &&
        isset($_POST['permessi_ruoli_nonce']) &&
        wp_verify_nonce($_POST['permessi_ruoli_nonce'], 'salva_permessi_ruoli')
    ) {
        $ruolo = sanitize_text_field($_POST['ruolo']);
        $tutti_termini = get_terms(array(
            'taxonomy' => 'tipi_cat_amm_trasp',
            'hide_empty' => false,
        ));
        $permessi_consentiti = isset($_POST['permessi_ruolo']) ? array_map('intval', $_POST['permessi_ruolo']) : array();
        foreach ($tutti_termini as $term) {
            $excluded = get_term_meta($term->term_id, 'excluded_roles', true);
            $excluded = is_array($excluded) ? $excluded : [];
            if (in_array($term->term_id, $permessi_consentiti)) {
                if (in_array($ruolo, $excluded)) {
                    $excluded = array_diff($excluded, [$ruolo]);
                    $excluded = array_values($excluded);
                    if (empty($excluded)) {
                        delete_term_meta($term->term_id, 'excluded_roles');
                    } else {
                        update_term_meta($term->term_id, 'excluded_roles', $excluded);
                    }
                }
            } else {
                if (!in_array($ruolo, $excluded)) {
                    $excluded[] = $ruolo;
                    update_term_meta($term->term_id, 'excluded_roles', array_values(array_unique($excluded)));
                }
            }
        }
        wp_safe_redirect(admin_url('admin.php?page=gestione_permessi_ruoli&ruolo=' . urlencode($ruolo) . '&aggiornato=1'));
        exit;
    }
}
