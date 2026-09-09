<?php
/** Statistiche aggregate del pannello Elementi Trasparenza. */
if (!defined('ABSPATH')) {
    exit;
}

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
 * Normalizza solo gli spazi: una voce rinominata o collocata sotto un altro
 * genitore non coincide con la struttura predefinita. Nessuna modifica ai dati.
 */
function dci_at_overview_extra_section_ids($nodes, $structure) {
    $normalize = static function ($name) {
        return preg_replace('/\s+/u', ' ', trim((string) $name));
    };
    $expected = array();
    $index = static function ($items, $parents = array()) use (&$index, &$expected, $normalize) {
        foreach ($items as $key => $value) {
            $name = is_int($key) ? $value : $key;
            $path = array_merge($parents, array($normalize($name)));
            $expected[json_encode($path)] = true;
            if (is_array($value)) {
                $index($value, $path);
            }
        }
    };
    $index($structure);

    $paths = array();
    $visiting = array();
    $resolve = static function ($id) use (&$resolve, &$paths, &$visiting, $nodes, $normalize) {
        if (array_key_exists($id, $paths)) {
            return $paths[$id];
        }
        if (!isset($nodes[$id]) || isset($visiting[$id])) {
            return null;
        }
        $visiting[$id] = true;
        $parent_path = $nodes[$id]['parent'] ? $resolve($nodes[$id]['parent']) : array();
        $paths[$id] = null === $parent_path ? null : array_merge($parent_path, array($normalize($nodes[$id]['name'])));
        unset($visiting[$id]);
        return $paths[$id];
    };
    $extra = array();
    foreach (array_keys($nodes) as $id) {
        $path = $resolve($id);
        if (null === $path || !isset($expected[json_encode($path)])) {
            $extra[] = (int) $id;
        }
    }
    return $extra;
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
            'description' => trim((string) ($nodes[$id]['description'] ?? '')),
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
function dci_at_get_transparency_overview() {
    $cached = get_transient('dci_at_overview_v8');
    if (is_array($cached) && isset($cached['visible_sections'], $cached['empty_sections'], $cached['published'], $cached['generated_at'], $cached['empty_section_list'], $cached['extra_sections'], $cached['extra_section_list'], $cached['parent_sections'], $cached['parent_section_list'])) {
        return $cached;
    }
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
    foreach (array('extra_section_list', 'parent_section_list') as $list_key) {
        foreach ($result[$list_key] as &$section) {
            $section['published'] = $section_counts[$section['id']] ?? 0;
        }
        unset($section);
    }
    unset($result['empty_section_ids'], $result['visible_section_ids']);
    $result['published'] = $published;
    $result['last_updated_gmt'] = $last_updated;
    $result['generated_at'] = time();
    set_transient('dci_at_overview_v8', $result, 5 * MINUTE_IN_SECONDS);
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

/** Pannello comune per gli elenchi della panoramica, già presenti nella cache. */
function dci_at_render_overview_section_panel($kind, $title, $items, $description, $empty_message, $group_by_parent = false) {
    ?>
    <section id="dci-at-<?php echo esc_attr($kind); ?>-sections" class="dci-at-empty-sections" aria-labelledby="dci-at-<?php echo esc_attr($kind); ?>-title" hidden>
        <div class="dci-at-empty-sections__header">
            <div class="dci-at-empty-sections__title">
                <span class="dci-at-empty-sections__eyebrow">Dettaglio panoramica</span>
                <h3 id="dci-at-<?php echo esc_attr($kind); ?>-title"><?php echo esc_html($title); ?> <span class="dci-at-empty-sections__badge"><?php echo esc_html(number_format_i18n(count($items))); ?></span></h3>
            </div>
            <button type="button" id="dci-at-<?php echo esc_attr($kind); ?>-close" class="button">Chiudi elenco</button>
        </div>
        <?php if (empty($items)) : ?>
            <p><?php echo esc_html($empty_message); ?></p>
        <?php else : ?>
            <p><?php echo esc_html($description); ?></p>
            <?php if ('empty' === $kind) : ?>
                <p>Ogni riga identifica una sezione senza contenuti, evidenziata con l’etichetta «Senza contenuti». Le altre categorie indicano esclusivamente il percorso.</p>
                <?php
                $max_levels = 1;
                foreach ($items as $section) {
                    $max_levels = max($max_levels, count($section['levels'] ?? array($section['name'])));
                }
                ?>
                <div class="dci-at-empty-hierarchy" tabindex="0" role="region" aria-label="Sezioni senza contenuti suddivise per livello gerarchico">
                    <table>
                        <thead>
                            <tr>
                                <?php for ($level = 1; $level <= $max_levels; $level++) : ?>
                                    <th scope="col"><?php echo 1 === $level ? 'Categoria principale' : 'Categoria livello ' . esc_html($level); ?></th>
                                <?php endfor; ?>
                                <th scope="col">Descrizione</th>
                                <th scope="col"><span class="screen-reader-text">Azioni</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $section) : ?>
                                <?php $levels = $section['levels'] ?? array($section['name']); ?>
                                <tr>
                                    <?php for ($level = 0; $level < $max_levels; $level++) : ?>
                                        <td data-label="<?php echo 0 === $level ? 'Categoria principale' : 'Categoria livello ' . esc_attr($level + 1); ?>">
                                            <?php if (isset($levels[$level])) : ?>
                                                <?php if ($level === count($levels) - 1) : ?>
                                                    <strong class="dci-at-empty-hierarchy__empty-name"><?php echo esc_html($levels[$level]); ?></strong>
                                                    <span class="dci-at-empty-hierarchy__empty-badge">Senza contenuti</span>
                                                <?php else : ?>
                                                    <span class="dci-at-empty-hierarchy__ancestor"><span class="screen-reader-text">Percorso: </span><?php echo esc_html($levels[$level]); ?></span>
                                                <?php endif; ?>
                                            <?php else : ?>
                                                <span aria-hidden="true">—</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endfor; ?>
                                    <td class="dci-at-empty-hierarchy__description" data-label="Descrizione">
                                        <?php echo $section['description'] !== '' ? esc_html($section['description']) : '<span class="dci-at-empty-hierarchy__missing">Descrizione non disponibile</span>'; ?>
                                    </td>
                                    <td class="dci-at-empty-hierarchy__action">
                                        <a class="dci-at-empty-sections__open" href="<?php echo esc_url(add_query_arg('tipi_cat_amm_trasp', $section['slug'], home_url('/'))); ?>" target="_blank" rel="noopener noreferrer">
                                            <span>Apri<span class="screen-reader-text">: <?php echo esc_html($section['name']); ?> (nuova scheda)</span></span>
                                            <span class="dashicons dashicons-external" aria-hidden="true"></span>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else : ?>
            <?php
            $empty_groups = array();
            foreach ($items as $section) {
                $root_name = $group_by_parent
                    ? ($section['path'] !== '' ? $section['path'] : 'Sezioni principali')
                    : ($section['path'] !== '' ? explode(' / ', $section['path'], 2)[0] : $section['name']);
                $empty_groups[$root_name][] = $section;
            }
            ksort($empty_groups, SORT_NATURAL | SORT_FLAG_CASE);
            ?>
            <div class="dci-at-empty-sections__scroll" tabindex="0" role="region" aria-label="Elenco delle categorie raggruppate per gerarchia">
                <?php foreach ($empty_groups as $root_name => $sections) : ?>
                    <section class="dci-at-empty-sections__group">
                        <h4>
                            <span class="dashicons dashicons-category" aria-hidden="true"></span>
                            <span><?php echo esc_html(str_replace(' / ', ' › ', $root_name)); ?></span>
                            <span class="dci-at-empty-sections__group-count"><?php echo esc_html(number_format_i18n(count($sections))); ?><span class="screen-reader-text"> voci</span></span>
                        </h4>
                        <ul class="dci-at-empty-sections__list">
                            <?php foreach ($sections as $section) : ?>
                                <li>
                                    <div class="dci-at-empty-sections__entry">
                                        <div class="dci-at-empty-sections__path">
                                            <span class="screen-reader-text">Percorso: </span>Amministrazione Trasparente
                                            <?php if ($section['path'] !== '') : ?>
                                                <span aria-hidden="true"> › </span><?php echo esc_html(str_replace(' / ', ' › ', $section['path'])); ?>
                                            <?php endif; ?>
                                        </div>
                                        <strong><?php echo esc_html($section['name']); ?></strong>
                                        <?php if (isset($section['published'])) : ?>
                                            <span class="dci-at-section-published">
                                                <?php echo esc_html(number_format_i18n($section['published'])); ?>
                                                <?php echo 1 === (int) $section['published'] ? 'elemento pubblicato' : 'elementi pubblicati'; ?>
                                                <span class="screen-reader-text"> direttamente in questa sezione, escluse le figlie</span>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <a class="dci-at-empty-sections__open" href="<?php echo esc_url(add_query_arg('tipi_cat_amm_trasp', $section['slug'], home_url('/'))); ?>" target="_blank" rel="noopener noreferrer">
                                        <span>Apri sezione<span class="screen-reader-text">: <?php echo esc_html($section['name']); ?> (nuova scheda)</span></span>
                                        <span class="dashicons dashicons-external" aria-hidden="true"></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </section>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </section>
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
    if (is_wp_error($stats)) {
        echo '<p class="dci-at-overview__note" role="status">Statistiche temporaneamente non disponibili. Gli strumenti di gestione restano utilizzabili.</p>';
        return;
    }
    $updated = !empty($stats['last_updated_gmt']) && '0000-00-00 00:00:00' !== $stats['last_updated_gmt']
        ? strtotime($stats['last_updated_gmt'] . ' UTC') : false;
    $cards = array(
        array('blue', 'category', 'Sezioni visibili', number_format_i18n($stats['visible_sections']), 'Sezioni e sottosezioni dell’alberatura pubblica'),
        array('orange', 'warning', 'Sezioni senza contenuti', number_format_i18n($stats['empty_sections']), 'Solo sezioni visibili senza figli e senza elementi pubblicati'),
        array('extra', 'networking', 'Categorie extra', number_format_i18n($stats['extra_sections']), 'Voci visibili fuori dalla struttura del tema'),
        array('parent', 'portfolio', 'Categorie con figli e contenuti', number_format_i18n($stats['parent_sections']), 'Contenitori visibili con elementi pubblicati direttamente al loro interno'),
        array('green', 'media-document', 'Elementi pubblicati', number_format_i18n($stats['published']), 'Elementi Trasparenza e cinque tipologie personalizzate'),
        array('purple', 'calendar-alt', 'Ultimo aggiornamento', $updated ? wp_date(get_option('date_format'), $updated) : '—', $updated ? 'Ultima modifica a un elemento pubblicato · ' . wp_date(get_option('time_format'), $updated) : 'Nessun elemento pubblicato'),
    );
    ?>
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
                    <?php if (in_array($card[0], array('orange', 'extra', 'parent'), true)) : ?>
                        <?php $panel_kind = 'orange' === $card[0] ? 'empty' : $card[0]; ?>
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
    dci_at_render_overview_section_panel(
        'empty', 'Sezioni senza contenuti', $stats['empty_section_list'],
        'Solo sezioni visibili senza figli e senza elementi pubblicati, comprese le tipologie personalizzate considerate dalla panoramica. Le voci con figli sono escluse anche quando i figli sono nascosti.',
        'Nessuna sezione senza contenuti rilevata.'
    );
    dci_at_render_overview_section_panel(
        'extra', 'Categorie extra', $stats['extra_section_list'],
        'Categorie visibili non presenti con lo stesso nome e percorso nella struttura predefinita del tema, raggruppate per genitore attuale. Sono escluse le categorie nascoste e quelle con un genitore nascosto.',
        'Nessuna categoria extra rilevata.', true
    );
    ?>
    <?php
    dci_at_render_overview_section_panel(
        'parent', 'Categorie con figli e contenuti', $stats['parent_section_list'],
        'Categorie visibili con almeno una figlia e con elementi pubblicati direttamente associati, incluse le tipologie personalizzate considerate dalla panoramica. I contenuti presenti soltanto nelle sottosezioni non sono conteggiati. Ogni categoria compare una sola volta.',
        'Nessuna categoria con figli e contenuti diretti rilevata.', true
    );
    ?>
    <p class="dci-at-overview__note">
        Sono inclusi gli elementi pubblicati di Atti di concessione, Bandi di gara, Titolari di incarichi, Incarichi dirigenziali e Incarichi conferiti e autorizzati.
        Le sezioni dedicate attive sono verificate in base ai rispettivi contenuti. Restano esclusi dal conteggio delle sezioni vuote i collegamenti esterni, gli obblighi non applicabili e le altre gestioni dedicate.
        Rilevazione: <?php echo esc_html(wp_date(get_option('date_format') . ' · ' . get_option('time_format'), $stats['generated_at'])); ?>.
        Il riepilogo viene ricalcolato dopo le modifiche o alla prima apertura dopo 5 minuti.
    </p>
    <?php
}

/** Una sola invalidazione a fine richiesta, anche in caso di operazioni massive. */
function dci_at_overview_mark_dirty() {
    add_action('shutdown', 'dci_at_overview_flush_cache', 100);
}
function dci_at_overview_flush_cache() {
    delete_transient('dci_at_overview_v8');
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
    if (in_array($key, array('visualizza_elemento', 'term_url', 'obbligo_non_applicabile'), true)) {
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
