<?php
/** Statistiche aggregate del pannello Elementi Trasparenza. */
if (!defined('ABSPATH')) {
    exit;
}
require_once dirname(__DIR__) . '/trasparenza-extra-sections.php';
require_once dirname(__DIR__) . '/trasparenza-overview-cache.php';

function dci_at_overview_post_types() {
    return array('elemento_trasparenza', 'atto_concessione', 'bando', 'titolare_incarico', 'incarico_dirig', 'incarichi_dip');
}

/** Integra le sezioni dedicate rispettando la suddivisione degli incarichi. */
function dci_at_overview_custom_occupied_ids($nodes, $occupied_ids, $totals_by_type, $manager_sections) {
    $occupied = array_fill_keys(array_map('intval', $occupied_ids), true);
    foreach ($nodes as $id => $node) {
        if (empty($node['custom_post_type'])) {
            continue;
        }
        // Nelle sezioni dedicate il template pubblico usa il CPT, non gli elementi generici.
        unset($occupied[$id]);
        $type = $node['custom_post_type'];
        $has_content = 'incarico_dirig' === $type
            ? ($node['custom_section'] !== '' && in_array($node['custom_section'], $manager_sections, true))
            : !empty($totals_by_type[$type]['published']);
        if ($has_content) {
            $occupied[$id] = true;
        }
    }
    return array_keys($occupied);
}

/**
 * Calcola visibilità effettiva e sezioni prive di elementi, senza query nei cicli.
 * Le sezioni esterne/non applicabili e le gestioni non incluse sono punti di accesso gestiti
 * altrove: non risultano vuote e rendono navigabili anche i loro contenitori.
 * Le descrizioni introduttive non sono considerate elementi pubblicati.
 */
function dci_at_overview_section_counts($nodes, $occupied_ids) {
    $visible = array();
    $visiting = array();
    $is_visible = static function ($id) use (&$is_visible, &$visible, &$visiting, $nodes) {
        if (isset($visible[$id])) {
            return $visible[$id];
        }
        if (!isset($nodes[$id]) || isset($visiting[$id])) {
            return false;
        }
        $visiting[$id] = true;
        $node = $nodes[$id];
        $visible[$id] = $node['visible'] && (!$node['parent'] || $is_visible($node['parent']));
        unset($visiting[$id]);
        return $visible[$id];
    };
    foreach (array_keys($nodes) as $id) {
        $is_visible($id);
    }

    // Anche una figlia nascosta rende la voce un contenitore, non una foglia.
    $has_children = array();
    foreach ($nodes as $node) {
        if ($node['parent']) {
            $has_children[$node['parent']] = true;
        }
    }
    $occupied = array_fill_keys(array_map('intval', $occupied_ids), true);
    $covered = array();
    foreach ($nodes as $id => $node) {
        if (!$visible[$id] || (!isset($occupied[$id]) && !$node['managed_elsewhere'])) {
            continue;
        }
        // Ogni antenato è visitato al massimo una volta; gestisce anche dati ciclici.
        while ($id && !isset($covered[$id]) && !empty($visible[$id])) {
            $covered[$id] = true;
            $id = $nodes[$id]['parent'];
        }
    }
    $empty_ids = array();
    foreach ($visible as $id => $shown) {
        if ($shown && !isset($has_children[$id]) && !isset($covered[$id])) {
            $empty_ids[] = (int) $id;
        }
    }
    return array('visible_sections' => count(array_filter($visible)), 'visible_section_ids' => array_keys(array_filter($visible)), 'empty_sections' => count($empty_ids), 'empty_section_ids' => $empty_ids);
}

/**
 * Confronta i termini ricevuti con i percorsi della struttura del tema.
 * Applica le stesse equivalenze ed esclusioni dell'elenco categorie.
 * Nessuna modifica ai dati.
 */
function dci_at_overview_extra_section_ids($nodes, $structure) {
    return dci_at_extra_section_ids($nodes, $structure);
}

/** Nomi e percorsi ricavati dalla stessa mappa già caricata, senza query aggiuntive. */
function dci_at_overview_empty_section_list($nodes, $ids) {
    $items = array();
    foreach ($ids as $id) {
        $path = array();
        $visited = array($id => true);
        $parent = $nodes[$id]['parent'];
        while ($parent && isset($nodes[$parent]) && !isset($visited[$parent])) {
            $visited[$parent] = true;
            array_unshift($path, $nodes[$parent]['name']);
            $parent = $nodes[$parent]['parent'];
        }
        $items[] = array(
            'id' => $id,
            'name' => $nodes[$id]['name'],
            'slug' => $nodes[$id]['slug'],
            'path' => implode(' / ', $path),
            'levels' => array_merge($path, array($nodes[$id]['name'])),
            'description' => dci_at_overview_excerpt($nodes[$id]['description'] ?? ''),
            'normativa' => dci_at_overview_excerpt($nodes[$id]['normativa'] ?? ''),
        );
    }
    usort($items, static function ($a, $b) {
        return strnatcasecmp($a['path'] . ' / ' . $a['name'], $b['path'] . ' / ' . $b['name']);
    });
    return $items;
}

/**
 * Una lettura delle sezioni (con metadati in blocco) e al massimo tre query aggregate.
 * Non carica i post, non scarica documenti e non contatta servizi esterni.
 * Il risultato condiviso non contiene dati personali o contenuti riservati.
 */
function dci_at_build_transparency_overview() {
    if (!function_exists('dci_tipi_cat_amm_trasp_array')) {
        return new WP_Error('dci_at_structure_unavailable', 'Struttura predefinita della Trasparenza non disponibile.');
    }

    $terms = get_terms(array(
        'taxonomy' => 'tipi_cat_amm_trasp',
        'hide_empty' => false,
        'orderby' => 'none',
        'update_term_meta_cache' => true,
    ));
    if (is_wp_error($terms)) {
        return $terms;
    }
    $custom = dci_elemento_trasparenza_get_custom_type_terms();
    $post_types = dci_at_overview_post_types();
    $manager_labels = function_exists('dci_incarico_dirigenziale_sections') ? dci_incarico_dirigenziale_sections() : array();
    $nodes = array();
    foreach ($terms as $term) {
        $political = function_exists('dci_at_contextual_has_complex_political_workflow')
            && dci_at_contextual_has_complex_political_workflow(array('mode' => 'standard', 'term' => $term));
        $custom_type = $custom[$term->name]['post_type'] ?? '';
        $count_custom = $custom_type !== '' && in_array($custom_type, $post_types, true);
        $manager_section = array_search($term->name, $manager_labels, true);
        $nodes[(int) $term->term_id] = array(
            'name' => $term->name,
            'slug' => $term->slug,
            'parent' => (int) $term->parent,
            'description' => $term->description,
            'normativa' => (string) get_term_meta($term->term_id, 'normativa', true),
            'visible' => '1' === (string) get_term_meta($term->term_id, 'visualizza_elemento', true),
            'custom_post_type' => $count_custom ? $custom_type : '',
            'custom_section' => false !== $manager_section ? (string) $manager_section : '',
            'managed_elsewhere' => (isset($custom[$term->name]) && !$count_custom) || $political
                || '' !== trim((string) get_term_meta($term->term_id, 'term_url', true))
                || '1' === (string) get_term_meta($term->term_id, 'obbligo_non_applicabile', true),
        );
    }

    global $wpdb;
    $placeholders = implode(', ', array_fill(0, count($post_types), '%s'));
    $totals = $wpdb->get_results($wpdb->prepare(
        "SELECT post_type, COUNT(*) AS published, MAX(post_modified_gmt) AS last_updated_gmt
         FROM {$wpdb->posts} WHERE post_type IN ($placeholders) AND post_status = %s
         GROUP BY post_type",
        array_merge($post_types, array('publish'))
    ), ARRAY_A);
    if (!is_array($totals) || $wpdb->last_error !== '') {
        return new WP_Error('dci_at_overview_unavailable', 'Statistiche temporaneamente non disponibili.');
    }
    $section_totals = $wpdb->get_results($wpdb->prepare(
        "SELECT tt.term_id, COUNT(DISTINCT p.ID) AS published
         FROM {$wpdb->term_taxonomy} tt
         INNER JOIN {$wpdb->term_relationships} tr ON tr.term_taxonomy_id = tt.term_taxonomy_id
         INNER JOIN {$wpdb->posts} p ON p.ID = tr.object_id
         WHERE tt.taxonomy = %s AND p.post_type = %s AND p.post_status = %s
         GROUP BY tt.term_id",
        'tipi_cat_amm_trasp', 'elemento_trasparenza', 'publish'
    ), ARRAY_A);
    if (!is_array($section_totals) || $wpdb->last_error !== '') {
        return new WP_Error('dci_at_overview_unavailable', 'Statistiche temporaneamente non disponibili.');
    }
    $direct_counts = array();
    foreach ($section_totals as $row) {
        $direct_counts[(int) $row['term_id']] = (int) $row['published'];
    }
    $occupied_ids = array_keys($direct_counts);
    $totals_by_type = array();
    $published = 0;
    $last_updated = null;
    foreach ($totals as $row) {
        $totals_by_type[$row['post_type']] = $row;
        $published += (int) $row['published'];
        if (!empty($row['last_updated_gmt']) && (null === $last_updated || $row['last_updated_gmt'] > $last_updated)) {
            $last_updated = $row['last_updated_gmt'];
        }
    }
    $manager_sections = array();
    $manager_counts = array();
    if (!empty($totals_by_type['incarico_dirig']['published'])) {
        // Metadati duplicati non moltiplicano i record nella stessa sezione.
        $manager_totals = $wpdb->get_results($wpdb->prepare(
            "SELECT pm.meta_value, COUNT(DISTINCT p.ID) AS published
             FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID
             WHERE p.post_type = %s AND p.post_status = %s AND pm.meta_key = %s
             GROUP BY pm.meta_value",
            'incarico_dirig', 'publish', '_dci_incarico_dirigenziale_sezione_pubblicazione'
        ), ARRAY_A);
        if (!is_array($manager_totals) || $wpdb->last_error !== '') {
            return new WP_Error('dci_at_overview_unavailable', 'Statistiche temporaneamente non disponibili.');
        }
        foreach ($manager_totals as $row) {
            $manager_counts[$row['meta_value']] = (int) $row['published'];
        }
        $manager_sections = array_keys($manager_counts);
    }
    $direct_ids = $occupied_ids;
    $occupied_ids = dci_at_overview_custom_occupied_ids($nodes, $occupied_ids, $totals_by_type, $manager_sections);
    $result = dci_at_overview_section_counts($nodes, $occupied_ids);
    $result['visible_section_list'] = dci_at_overview_empty_section_list($nodes, $result['visible_section_ids']);
    $result['empty_section_list'] = dci_at_overview_empty_section_list($nodes, $result['empty_section_ids']);
    // Riutilizza la visibilità effettiva: esclude anche i discendenti di voci nascoste.
    $visible_nodes = array_intersect_key($nodes, array_fill_keys($result['visible_section_ids'], true));
    $extra_ids = dci_at_overview_extra_section_ids($visible_nodes, dci_tipi_cat_amm_trasp_array());
    $result['extra_sections'] = count($extra_ids);
    $result['extra_section_list'] = dci_at_overview_empty_section_list($nodes, $extra_ids);
    $parent_ids = dci_at_overview_parent_content_ids($nodes, $result['visible_section_ids'], array_merge($direct_ids, $occupied_ids));
    $result['parent_sections'] = count($parent_ids);
    $result['parent_section_list'] = dci_at_overview_empty_section_list($nodes, $parent_ids);
    $section_counts = dci_at_overview_direct_counts($nodes, $direct_counts, $totals_by_type, $manager_counts);
    foreach (array('visible_section_list', 'empty_section_list', 'extra_section_list', 'parent_section_list') as $list_key) {
        foreach ($result[$list_key] as &$section) {
            $section['published'] = $section_counts[$section['id']] ?? 0;
        }
        unset($section);
    }
    unset($result['empty_section_ids'], $result['visible_section_ids']);
    $result['published'] = $published;
    $result['last_updated_gmt'] = $last_updated;
    $result['generated_at'] = time();
    return $result;
}

/** Elementi generici diretti e contenuti delle gestioni dedicate, senza sommare le figlie. */
function dci_at_overview_direct_counts($nodes, $direct_counts, $totals_by_type, $manager_counts) {
    foreach ($nodes as $id => $node) {
        $type = $node['custom_post_type'] ?? '';
        // I CPT dedicati sono distinti da elemento_trasparenza: nessuna duplicazione tra i due.
        $custom_count = 'incarico_dirig' === $type
            ? ($manager_counts[$node['custom_section']] ?? 0)
            : ($totals_by_type[$type]['published'] ?? 0);
        $direct_counts[$id] = (int) ($direct_counts[$id] ?? 0) + (int) $custom_count;
    }
    return $direct_counts;
}

/** Contenitori visibili con contenuto diretto, senza propagazione dagli antenati o dai figli. */
function dci_at_overview_parent_content_ids($nodes, $visible_ids, $direct_ids) {
    $parents = array();
    foreach ($nodes as $node) {
        if ($node['parent']) {
            $parents[(int) $node['parent']] = true;
        }
    }
    return array_map('intval', array_keys(array_intersect_key(
        $parents,
        array_fill_keys($visible_ids, true),
        array_fill_keys($direct_ids, true)
    )));
}

/** Data calcolata nel fuso orario del sito: primo gennaio di cinque anni fa. */
function dci_at_overview_empty_section_title() {
    return sprintf('Sezioni senza contenuti nel periodo dal 01/01/%d', (int) wp_date('Y') - 5);
}

/** Titoli e descrizioni condivisi da riepilogo ed endpoint degli elenchi. */
function dci_at_overview_panels() {
    return array(
        'visible' => array('title' => 'Sezioni visibili', 'description' => 'Tutte le sezioni visibili nell’alberatura pubblica. Sono escluse le sottosezioni con un genitore nascosto.', 'empty' => 'Nessuna sezione visibile rilevata.'),
        'empty' => array('title' => dci_at_overview_empty_section_title(), 'description' => 'Sezioni visibili senza figli e senza elementi pubblicati. Le voci con figli sono escluse anche quando i figli sono nascosti.', 'empty' => 'Nessuna sezione senza contenuti rilevata.'),
        'extra' => array('title' => 'Categorie extra', 'description' => 'Categorie visibili fuori dalla struttura corrente del tema. Sono riconosciute le denominazioni equivalenti Amministratori cessati / Cessati dall’incarico e Bandi di concorso / Concorsi. Sono esclusi Normativa, Link BDNCP, Consulta i bilanci per anno, Consultazione dati BDAP e tutte le sottovoci di Bandi di Gara e contratti › Procedimenti fino al 31/12/2023.', 'empty' => 'Nessuna categoria extra rilevata.'),
        'parent' => array('title' => 'Categorie con figli e contenuti', 'description' => 'Contenitori visibili con elementi pubblicati direttamente associati. Ogni categoria compare una sola volta.', 'empty' => 'Nessuna categoria con figli e contenuti diretti rilevata.'),
    );
}

/** Solo il contenitore: nessuna voce dell'elenco nell'HTML iniziale. */
function dci_at_render_overview_section_panel($kind, $panel, $total) {
    ?>
    <section id="dci-at-<?php echo esc_attr($kind); ?>-sections" class="dci-at-empty-sections" aria-labelledby="dci-at-<?php echo esc_attr($kind); ?>-title" data-overview-panel="<?php echo esc_attr($kind); ?>" hidden>
        <div class="dci-at-empty-sections__header">
            <h3 id="dci-at-<?php echo esc_attr($kind); ?>-title"><?php echo esc_html($panel['title']); ?> <span class="dci-at-empty-sections__badge"><?php echo esc_html(number_format_i18n($total)); ?></span></h3>
            <button type="button" id="dci-at-<?php echo esc_attr($kind); ?>-close" class="button"><span class="dashicons dashicons-no-alt" aria-hidden="true"></span>Chiudi elenco</button>
        </div>
        <p><?php echo esc_html($panel['description']); ?> Il numero dei contenuti si riferisce agli elementi pubblicati direttamente nella sezione, escluse le sottosezioni.</p>
        <p data-overview-list-status role="status" aria-live="polite"></p>
        <div class="dci-at-empty-sections__scroll" data-overview-items tabindex="0" role="region" aria-label="<?php echo esc_attr($panel['title']); ?>"></div>
        <div class="dci-at-overview-pagination" data-overview-pagination hidden>
            <button type="button" class="button" data-overview-prev>Precedente</button>
            <span data-overview-page-label aria-live="polite"></span>
            <button type="button" class="button" data-overview-next>Successiva</button>
        </div>
        <button type="button" class="button" data-overview-list-retry hidden>Riprova</button>
    </section>
    <?php
}

/** Renderizza al massimo 50 voci, con permessi valutati per l'utente corrente. */
function dci_at_render_overview_section_items($kind, $items) {
    $panels = dci_at_overview_panels();
    if (empty($items)) {
        echo '<p>' . esc_html($panels[$kind]['empty']) . '</p>';
        return;
    }
    $can_manage = current_user_can('manage_options') && current_user_can('edit_tipi_cat_amm_trasp');
    $group_by_parent = in_array($kind, array('extra', 'parent'), true);
    $groups = array();
    foreach ($items as $section) {
        $group_name = $group_by_parent
            ? ($section['path'] !== '' ? $section['path'] : 'Sezioni principali')
            : ($section['path'] !== '' ? explode(' / ', $section['path'], 2)[0] : $section['name']);
        $groups[$group_name][] = $section;
    }
    ksort($groups, SORT_NATURAL | SORT_FLAG_CASE);
    ?>
                <?php foreach ($groups as $group_name => $sections) : ?>
                    <section class="dci-at-empty-sections__group">
                        <h4><span class="dashicons dashicons-category" aria-hidden="true"></span><span><?php echo esc_html(str_replace(' / ', ' › ', $group_name)); ?></span></h4>
                        <ul class="dci-at-empty-sections__list">
                            <?php foreach ($sections as $section) : ?>
                                <li>
                                    <div class="dci-at-empty-sections__entry">
                                        <strong><span class="dashicons dashicons-media-document" aria-hidden="true"></span><span><?php echo esc_html($section['name']); ?></span></strong>
                                        <?php if (!empty($section['description'])) : ?>
                                            <div class="dci-at-empty-sections__description"><?php echo esc_html($section['description']); ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($section['normativa'])) : ?>
                                            <div class="dci-at-empty-sections__normativa"><span>Normativa:</span> <?php echo esc_html($section['normativa']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <span class="dci-at-section-published">
                                        <span class="dashicons dashicons-admin-page" aria-hidden="true"></span>
                                        <?php echo esc_html(number_format_i18n($section['published'] ?? 0)); ?>
                                        <?php echo 1 === (int) ($section['published'] ?? 0) ? 'contenuto' : 'contenuti'; ?>
                                        <span class="screen-reader-text"> pubblicati direttamente in questa sezione</span>
                                    </span>
                                    <div class="dci-at-empty-sections__actions">
                                        <a class="button dci-at-empty-sections__open" href="<?php echo esc_url(add_query_arg('tipi_cat_amm_trasp', $section['slug'], home_url('/'))); ?>" target="_blank" rel="noopener noreferrer">
                                            <span class="dashicons dashicons-external" aria-hidden="true"></span>
                                            Apri sezione<span class="screen-reader-text">: <?php echo esc_html($section['name']); ?> (nuova scheda)</span>
                                        </a>
                                        <?php if ($can_manage) : ?>
                                            <a class="button dci-at-empty-sections__manage" href="<?php echo esc_url(add_query_arg(array('taxonomy' => 'tipi_cat_amm_trasp', 'tag_ID' => (int) $section['id'], 'post_type' => 'elemento_trasparenza'), admin_url('term.php'))); ?>">
                                                <span class="dashicons dashicons-admin-generic" aria-hidden="true"></span>
                                                Gestisci categoria<span class="screen-reader-text">: <?php echo esc_html($section['name']); ?></span>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </section>
                <?php endforeach; ?>
    <?php
}

/** Icone vettoriali locali: nessuna libreria o richiesta esterna. */
function dci_at_overview_icon($kind) {
    $paths = array(
        'blue' => '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/>',
        'orange' => '<path d="M3 7V5a1 1 0 0 1 1-1h5l2 3h9a1 1 0 0 1 1 1v11a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V7Z"/><path d="M9 14h6"/>',
        'extra' => '<rect x="3" y="3" width="6" height="6" rx="1.5"/><rect x="15" y="3" width="6" height="6" rx="1.5"/><rect x="3" y="15" width="6" height="6" rx="1.5"/><path d="M18 14v8m-4-4h8"/>',
        'parent' => '<rect x="8" y="2" width="8" height="6" rx="1.5"/><path d="M12 8v5M5 16v-3h14v3"/><rect x="2" y="16" width="6" height="6" rx="1.5"/><rect x="16" y="16" width="6" height="6" rx="1.5"/>',
        'green' => '<path d="M13 2H5a1 1 0 0 0-1 1v18a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1V9l-7-7Z"/><path d="M13 2v7h7M8 15l3 3 5-5"/>',
        'purple' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
    );
    if (isset($paths[$kind])) {
        // Il markup proviene esclusivamente dalla mappa statica sopra.
        echo '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $paths[$kind] . '</svg>';
    }
}

/** Indicatori, senza dati dimostrativi o conteggi ricavati dai filtri della lista. */
function dci_at_render_overview_cards() {
    $stats = dci_at_get_transparency_overview();
    ?>
    <div id="dci-at-overview" data-ajax-url="<?php echo esc_url(admin_url('admin-ajax.php')); ?>" data-nonce="<?php echo esc_attr(wp_create_nonce('dci_at_overview')); ?>" data-refresh="<?php echo dci_at_overview_needs_refresh($stats) ? '1' : '0'; ?>" data-snapshot="<?php echo is_wp_error($stats) ? '' : esc_attr(dci_at_overview_snapshot($stats)); ?>">
        <p data-overview-status role="status" aria-live="polite" hidden></p>
        <div data-overview-content><?php dci_at_render_overview_content($stats); ?></div>
        <noscript><p>Abilita JavaScript per aggiornare il riepilogo e aprire gli elenchi.</p></noscript>
    </div>
    <?php
}

function dci_at_render_overview_content($stats) {
    if (is_wp_error($stats)) {
        ?>
        <div class="dci-at-overview-recorded">
            <span class="dashicons dashicons-clock" aria-hidden="true"></span>
            <div><span class="dci-at-overview-recorded__label">Ultima rilevazione delle statistiche</span><strong>Rilevazione in preparazione</strong></div>
            <p>Il riepilogo viene preparato dopo il caricamento della pagina. Gli strumenti di gestione restano utilizzabili.</p>
            <button type="button" class="button dci-at-overview-refresh" data-overview-refresh><span class="dashicons dashicons-update" aria-hidden="true"></span>Aggiorna statistiche</button>
        </div>
        <?php
        return;
    }
    $updated = !empty($stats['last_updated_gmt']) && '0000-00-00 00:00:00' !== $stats['last_updated_gmt']
        ? strtotime($stats['last_updated_gmt'] . ' UTC') : false;
    $cards = array(
        array('blue', 'category', 'Sezioni visibili', number_format_i18n($stats['visible_sections']), 'Sezioni e sottosezioni dell’alberatura pubblica'),
        array('orange', 'warning', dci_at_overview_empty_section_title(), number_format_i18n($stats['empty_sections']), 'Solo sezioni visibili senza figli e senza elementi pubblicati'),
        array('extra', 'networking', 'Categorie extra', number_format_i18n($stats['extra_sections']), 'Voci visibili fuori dalla struttura del tema'),
        array('parent', 'portfolio', 'Categorie con figli e contenuti', number_format_i18n($stats['parent_sections']), 'Contenitori visibili con elementi pubblicati direttamente al loro interno'),
        array('green', 'media-document', 'Elementi pubblicati', number_format_i18n($stats['published']), 'Elementi Trasparenza e cinque tipologie personalizzate'),
        array('purple', 'calendar-alt', 'Ultimo aggiornamento', $updated ? wp_date(get_option('date_format'), $updated) : '—', $updated ? 'Ultima modifica a un elemento pubblicato · ' . wp_date(get_option('time_format'), $updated) : 'Nessun elemento pubblicato'),
    );
    ?>
    <div class="dci-at-overview-recorded">
        <span class="dashicons dashicons-clock" aria-hidden="true"></span>
        <div>
            <span class="dci-at-overview-recorded__label">Ultima rilevazione delle statistiche</span>
            <time datetime="<?php echo esc_attr(wp_date('c', $stats['generated_at'])); ?>"><?php echo esc_html(wp_date(get_option('date_format') . ' · ' . get_option('time_format'), $stats['generated_at'])); ?></time>
        </div>
        <p>I numeri riportano la situazione dei contenuti pubblicati alla data e all’ora indicate.</p>
        <button type="button" class="button dci-at-overview-refresh" data-overview-refresh><span class="dashicons dashicons-update" aria-hidden="true"></span>Aggiorna statistiche</button>
    </div>
    <dl class="dci-at-overview__grid" aria-label="Statistiche Amministrazione Trasparente">
        <?php foreach ($cards as $card) : ?>
            <div class="dci-at-overview-card dci-at-overview-card--<?php echo esc_attr($card[0]); ?>">
                <dt>
                    <span class="dci-at-overview-card__icon" aria-hidden="true"><?php dci_at_overview_icon($card[0]); ?></span>
                    <?php echo esc_html($card[2]); ?>
                </dt>
                <dd>
                    <strong class="dci-at-overview-card__value"><?php echo esc_html($card[3]); ?></strong>
                    <span class="dci-at-overview-card__detail"><?php echo esc_html($card[4]); ?></span>
                    <?php if (in_array($card[0], array('blue', 'orange', 'extra', 'parent'), true)) : ?>
                        <?php $panel_kind = array('blue' => 'visible', 'orange' => 'empty')[$card[0]] ?? $card[0]; ?>
                        <button type="button" id="dci-at-<?php echo esc_attr($panel_kind); ?>-toggle" class="dci-at-overview-card__toggle" aria-expanded="false" aria-controls="dci-at-<?php echo esc_attr($panel_kind); ?>-sections">
                            <span data-empty-toggle-label>Mostra elenco</span>
                            <span class="screen-reader-text">: <?php echo esc_html($card[2]); ?></span>
                        </button>
                    <?php endif; ?>
                </dd>
            </div>
        <?php endforeach; ?>
    </dl>
    <?php
    foreach (dci_at_overview_panels() as $kind => $panel) {
        dci_at_render_overview_section_panel($kind, $panel, $stats[$kind === 'visible' ? 'visible_sections' : ($kind === 'empty' ? 'empty_sections' : ($kind === 'extra' ? 'extra_sections' : 'parent_sections'))]);
    }
    ?>
    <section class="dci-at-overview-help" aria-label="Come leggere le statistiche">
        <div>
            <h3><span class="dashicons dashicons-chart-bar" aria-hidden="true"></span>A cosa servono</h3>
            <p>Le statistiche aiutano a organizzare l’Amministrazione Trasparente: mostrano le sezioni visibili, quelle senza contenuti, le categorie extra e i contenitori con pubblicazioni da verificare. Usa <strong>Mostra elenco</strong> per individuare le voci interessate e aprire la relativa sezione.</p>
            <p>Gli elenchi vengono caricati solo quando li apri, con <strong>50 voci per pagina</strong>. Il numero dei contenuti di ogni sezione comprende gli elementi pubblicati direttamente al suo interno, senza sommare quelli delle sottosezioni.</p>
        </div>
        <div>
            <h3><span class="dashicons dashicons-update" aria-hidden="true"></span>Quando si aggiornano</h3>
            <p>All’apertura del pannello il riepilogo si aggiorna se sono trascorsi <strong>15 minuti</strong> dall’ultima rilevazione oppure se sono stati modificati contenuti, categorie o impostazioni rilevanti. Se nessuno consulta il pannello, non vengono eseguiti ricalcoli.</p>
            <p>Durante l’aggiornamento resta visibile l’ultimo risultato disponibile, conservato per un massimo di <strong>24 ore</strong>. Puoi richiedere la verifica con <strong>Aggiorna statistiche</strong>: se i dati sono ancora aggiornati, vengono riutilizzati.</p>
        </div>
        <p class="dci-at-overview-help__scope"><strong>Cosa viene conteggiato:</strong> gli Elementi Trasparenza pubblicati e i contenuti pubblicati di Atti di concessione, Bandi di gara, Titolari di incarichi, Incarichi dirigenziali e Incarichi conferiti e autorizzati. I collegamenti esterni, gli obblighi non applicabili e le altre gestioni dedicate non vengono segnalati come sezioni vuote.</p>
    </section>
    <?php
}

/** Una sola invalidazione a fine richiesta, anche in caso di operazioni massive. */
function dci_at_overview_mark_dirty() {
    add_action('shutdown', 'dci_at_overview_flush_cache', 100);
}
function dci_at_overview_flush_cache() {
    // Conserva l'ultimo risultato leggibile e segnala la necessità di aggiornarlo.
    update_option('dci_at_overview_revision_v12', wp_generate_uuid4(), false);
}
foreach (dci_at_overview_post_types() as $dci_overview_type) {
    add_action('save_post_' . $dci_overview_type, 'dci_at_overview_mark_dirty');
}
unset($dci_overview_type);

function dci_at_overview_deleted_post($post_id, $post) {
    if ($post && in_array($post->post_type, dci_at_overview_post_types(), true)) {
        dci_at_overview_mark_dirty();
    }
}
add_action('deleted_post', 'dci_at_overview_deleted_post', 10, 2);

function dci_at_overview_term_changed($term_id, $tt_id, $taxonomy) {
    if ('tipi_cat_amm_trasp' === $taxonomy) {
        dci_at_overview_mark_dirty();
    }
}
add_action('created_term', 'dci_at_overview_term_changed', 10, 3);
add_action('edited_term', 'dci_at_overview_term_changed', 10, 3);
add_action('delete_term', 'dci_at_overview_term_changed', 10, 3);

function dci_at_overview_relationship_changed($object_id, $tt_ids, $taxonomy) {
    if ('tipi_cat_amm_trasp' === $taxonomy) {
        dci_at_overview_mark_dirty();
    }
}
add_action('added_term_relationship', 'dci_at_overview_relationship_changed', 10, 3);
add_action('deleted_term_relationships', 'dci_at_overview_relationship_changed', 10, 3);

function dci_at_overview_meta_changed($meta_id, $term_id, $key) {
    if (in_array($key, array('visualizza_elemento', 'term_url', 'obbligo_non_applicabile', 'normativa'), true)) {
        dci_at_overview_mark_dirty();
    }
}
add_action('added_term_meta', 'dci_at_overview_meta_changed', 10, 3);
add_action('updated_term_meta', 'dci_at_overview_meta_changed', 10, 3);
add_action('deleted_term_meta', 'dci_at_overview_meta_changed', 10, 3);

function dci_at_overview_post_meta_changed($meta_id, $post_id, $key) {
    if ('_dci_incarico_dirigenziale_sezione_pubblicazione' === $key) {
        dci_at_overview_mark_dirty();
    }
}
add_action('added_post_meta', 'dci_at_overview_post_meta_changed', 10, 3);
add_action('updated_post_meta', 'dci_at_overview_post_meta_changed', 10, 3);
add_action('deleted_post_meta', 'dci_at_overview_post_meta_changed', 10, 3);

function dci_at_overview_options_changed($option) {
    if (in_array(strtolower((string) $option), array('trasparenza', 'dci_options'), true)) {
        dci_at_overview_mark_dirty();
    }
}
add_action('added_option', 'dci_at_overview_options_changed');
add_action('updated_option', 'dci_at_overview_options_changed');
add_action('deleted_option', 'dci_at_overview_options_changed');

add_action('admin_enqueue_scripts', 'dci_at_overview_assets');
function dci_at_overview_assets() {
    $screen = get_current_screen();
    if (!$screen || 'edit-elemento_trasparenza' !== $screen->id) {
        return;
    }
    $path = get_template_directory() . '/inc/admin-js/trasparenza-overview.js';
    wp_enqueue_script('dci-at-overview', get_template_directory_uri() . '/inc/admin-js/trasparenza-overview.js', array(), filemtime($path), true);
}
