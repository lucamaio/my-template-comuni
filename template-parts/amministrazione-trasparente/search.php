<?php
/**
 * Prepara la ricerca globale dell'Amministrazione Trasparente.
 *
 * Il file viene incluso da categorie.php e rende disponibili nello stesso
 * scope i dati usati dal modulo e dall'elenco dei risultati.
 */

global $wpdb;

$at_search_term = isset($_GET['at_search']) && is_string($_GET['at_search'])
    ? sanitize_text_field(wp_unslash($_GET['at_search']))
    : '';
$at_search_page = isset($_GET['at_page']) && is_scalar($_GET['at_page'])
    ? max(1, absint($_GET['at_page']))
    : 1;
$at_search_order = isset($_GET['at_order']) && is_scalar($_GET['at_order'])
    ? sanitize_key(wp_unslash($_GET['at_order']))
    : 'data_desc';
$at_search_section = isset($_GET['at_section']) && is_scalar($_GET['at_section'])
    ? absint($_GET['at_section'])
    : 0;
$at_search_year = isset($_GET['at_year']) && is_scalar($_GET['at_year'])
    ? absint($_GET['at_year'])
    : 0;
$at_search_orders = [
    'data_desc'       => ['orderby' => 'date', 'order' => 'DESC'],
    'data_asc'        => ['orderby' => 'date', 'order' => 'ASC'],
    'alfabetico_asc'  => ['orderby' => 'title', 'order' => 'ASC'],
    'alfabetico_desc' => ['orderby' => 'title', 'order' => 'DESC'],
];

if (!isset($at_search_orders[$at_search_order])) {
    $at_search_order = 'data_desc';
}

$at_search_query = null;
$at_category_results = [];
$at_search_term_length = function_exists('mb_strlen')
    ? mb_strlen($at_search_term, 'UTF-8')
    : strlen($at_search_term);
$at_search_too_short = $at_search_term !== ''
    && $at_search_term_length < 2;
$at_search_has_text = !$at_search_too_short && $at_search_term !== '';
$at_search_has_filters = false;
$at_search_post_types = ['elemento_trasparenza'];
$at_search_section_terms = [];
$at_custom_section_terms = [];
$at_search_section_options = [];
$at_section_filter_custom_types = [];
$at_section_filter_incarico_sections = [];

/*
 * Una sezione è ricercabile solo se non è nascosta, direttamente o tramite
 * uno dei suoi contenitori. I termini storici senza meta restano pubblici.
 */
$at_term_is_public = static function ($term) {
    $current_term = $term;

    while ($current_term instanceof WP_Term) {
        if ((string) get_term_meta($current_term->term_id, 'visualizza_elemento', true) === '0') {
            return false;
        }

        if ((int) $current_term->parent <= 0) {
            break;
        }

        $current_term = get_term((int) $current_term->parent, 'tipi_cat_amm_trasp');
        if (is_wp_error($current_term)) {
            return false;
        }
    }

    return true;
};

$at_custom_search_types = [
    'bando' => [
        'option' => 'ck_bandidigaratemplatepersonalizzato',
        'section' => 'Atti, documenti e link a BDNCP',
    ],
    'atto_concessione' => [
        'option' => 'ck_attidiconcessione',
        'section' => 'Atti di concessione',
    ],
    'titolare_incarico' => [
        'option' => 'ck_titolariIncarichiCollaborazioneConsulenzaTemplatePersonalizzato',
        'section' => 'Titolari di incarichi di collaborazione o consulenza',
    ],
    'incarichi_dip' => [
        'option' => 'ck_incarichieautorizzazioniaidipendenti',
        'section' => 'Incarichi conferiti e autorizzati ai dipendenti',
    ],
    'incarico_dirig' => [
        'option' => 'ck_incarichidirigenzialitemplatepersonalizzato',
        'enabled_when' => 'not_false_empty',
        'sections' => function_exists('dci_incarico_dirigenziale_sections')
            ? dci_incarico_dirigenziale_sections()
            : [
                'vertice' => 'Titolari di incarichi dirigenziali amministrativi di vertice',
                'dirigenti' => 'Incarichi dirigenziali a qualsiasi titolo conferiti',
            ],
        'section_meta' => '_dci_incarico_dirigenziale_sezione_pubblicazione',
    ],
];

foreach ($at_custom_search_types as $at_post_type => $at_search_config) {
    $at_search_option_value = dci_get_option($at_search_config['option'], 'Trasparenza');
    $at_search_is_enabled = ($at_search_config['enabled_when'] ?? '') === 'not_false_empty'
        ? ($at_search_option_value !== 'false' && $at_search_option_value !== '')
        : ($at_search_option_value === 'true');

    if (
        !$at_search_is_enabled
        || !post_type_exists($at_post_type)
    ) {
        continue;
    }

    $at_section_names = $at_search_config['sections'] ?? [$at_search_config['section']];
    $at_public_section_terms = [];

    foreach ($at_section_names as $at_section_key => $at_section_name) {
        $at_section_term = get_term_by(
            'name',
            $at_section_name,
            'tipi_cat_amm_trasp'
        );

        if (
            !($at_section_term instanceof WP_Term)
            || !$at_term_is_public($at_section_term)
        ) {
            continue;
        }

        $at_public_section_terms[(string) $at_section_key] = $at_section_term;
    }

    if (empty($at_public_section_terms)) {
        continue;
    }

    $at_search_post_types[] = $at_post_type;
    $at_search_section_terms[$at_post_type] = reset($at_public_section_terms);
    $at_custom_section_terms[$at_post_type] = [
        'terms' => $at_public_section_terms,
        'section_meta' => $at_search_config['section_meta'] ?? '',
    ];
}

$at_search_post_types = array_values(array_unique($at_search_post_types));

$at_public_section_terms = get_terms([
    'taxonomy'   => 'tipi_cat_amm_trasp',
    'hide_empty' => false,
    'orderby'    => 'name',
    'order'      => 'ASC',
]);

if (!is_wp_error($at_public_section_terms)) {
    foreach ($at_public_section_terms as $at_public_section_term) {
        if (!$at_term_is_public($at_public_section_term)) {
            continue;
        }

        $at_search_section_options[(int) $at_public_section_term->term_id] = $at_public_section_term;
    }
}

if ($at_search_section > 0 && empty($at_search_section_options[$at_search_section])) {
    $at_search_section = 0;
}

if ($at_search_year > 0) {
    $current_year = (int) gmdate('Y');
    if ($at_search_year < $current_year - 9 || $at_search_year > $current_year) {
        $at_search_year = 0;
    }
}

$at_search_has_filters = $at_search_section > 0 || $at_search_year > 0;

if ($at_search_section > 0) {
    foreach ($at_search_section_terms as $at_post_type => $at_section_term) {
        if ($at_section_term instanceof WP_Term && (int) $at_section_term->term_id === $at_search_section) {
            $at_section_filter_custom_types[] = $at_post_type;
        }
    }

    if (!empty($at_custom_section_terms['incarico_dirig']['terms'])) {
        foreach ($at_custom_section_terms['incarico_dirig']['terms'] as $at_section_key => $at_section_term) {
            if ($at_section_term instanceof WP_Term && (int) $at_section_term->term_id === $at_search_section) {
                $at_section_filter_incarico_sections[] = (string) $at_section_key;
            }
        }
    }
}

if (!$at_search_has_text && !$at_search_has_filters) {
    return;
}

if ($at_search_has_text) {
    $at_category_query = get_terms([
        'taxonomy'   => 'tipi_cat_amm_trasp',
        'hide_empty' => false,
        'search'     => $at_search_term,
        'orderby'    => 'name',
        'order'      => 'ASC',
        'number'     => 50,
    ]);

    if (!is_wp_error($at_category_query)) {
        $at_category_results = array_values(array_filter($at_category_query, $at_term_is_public));

        if ($at_search_section > 0) {
            $at_category_results = array_values(array_filter(
                $at_category_results,
                static function ($term) use ($at_search_section) {
                    return (int) $term->term_id === $at_search_section
                        || (int) $term->parent === $at_search_section;
                }
            ));
        }
    }
}

$at_query_args = [
    'post_type'           => $at_search_post_types,
    'post_status'         => 'publish',
    'posts_per_page'      => 10,
    'paged'               => $at_search_page,
    'orderby'             => $at_search_orders[$at_search_order]['orderby'],
    'order'               => $at_search_orders[$at_search_order]['order'],
    'ignore_sticky_posts' => true,
];

if ($at_search_has_text) {
    $at_query_args['s'] = $at_search_term;
}

if ($at_search_year > 0) {
    $at_query_args['date_query'] = [
        [
            'year' => $at_search_year,
        ],
    ];
}

$at_section_filter = null;
if ($at_search_section > 0) {
    $at_section_filter = [
        'term_id' => $at_search_section,
        'custom_types' => array_values(array_unique(array_filter(
            $at_section_filter_custom_types,
            static function ($post_type) {
                return $post_type !== 'incarico_dirig';
            }
        ))),
        'incarico_sections' => array_values(array_unique($at_section_filter_incarico_sections)),
        'incarico_meta_key' => '_dci_incarico_dirigenziale_sezione_pubblicazione',
    ];

    $at_query_args['dci_at_section_filter'] = $at_section_filter;
}

$at_section_filter_clauses = static function ($clauses, $query) use ($wpdb) {
    $filter = $query->get('dci_at_section_filter');
    if (empty($filter) || empty($filter['term_id'])) {
        return $clauses;
    }

    $term_id = (int) $filter['term_id'];
    $custom_types = array_map('sanitize_key', (array) ($filter['custom_types'] ?? []));
    $incarico_sections = array_map('sanitize_key', (array) ($filter['incarico_sections'] ?? []));
    $incarico_meta_key = (string) ($filter['incarico_meta_key'] ?? '');

    $clauses['join'] .= " LEFT JOIN {$wpdb->term_relationships} dci_at_tr ON dci_at_tr.object_id = {$wpdb->posts}.ID";
    $clauses['join'] .= " LEFT JOIN {$wpdb->term_taxonomy} dci_at_tt ON dci_at_tt.term_taxonomy_id = dci_at_tr.term_taxonomy_id AND dci_at_tt.taxonomy = 'tipi_cat_amm_trasp'";

    if (!empty($incarico_sections) && $incarico_meta_key !== '') {
        $clauses['join'] .= $wpdb->prepare(
            " LEFT JOIN {$wpdb->postmeta} dci_at_incarico_section ON dci_at_incarico_section.post_id = {$wpdb->posts}.ID AND dci_at_incarico_section.meta_key = %s",
            $incarico_meta_key
        );
    }

    $where_parts = [
        $wpdb->prepare(
            "({$wpdb->posts}.post_type = 'elemento_trasparenza' AND dci_at_tt.term_id = %d)",
            $term_id
        ),
    ];

    if (!empty($custom_types)) {
        $custom_type_placeholders = implode(',', array_fill(0, count($custom_types), '%s'));
        $where_parts[] = $wpdb->prepare(
            "({$wpdb->posts}.post_type IN ($custom_type_placeholders))",
            $custom_types
        );
    }

    if (!empty($incarico_sections) && $incarico_meta_key !== '') {
        $section_placeholders = implode(',', array_fill(0, count($incarico_sections), '%s'));
        $where_parts[] = $wpdb->prepare(
            "({$wpdb->posts}.post_type = 'incarico_dirig' AND dci_at_incarico_section.meta_value IN ($section_placeholders))",
            $incarico_sections
        );
    }

    $clauses['where'] .= ' AND (' . implode(' OR ', $where_parts) . ')';
    $clauses['distinct'] = 'DISTINCT';

    return $clauses;
};

if ($at_section_filter !== null) {
    add_filter('posts_clauses', $at_section_filter_clauses, 10, 2);
}

$at_search_query = new WP_Query($at_query_args);

if ($at_section_filter !== null) {
    remove_filter('posts_clauses', $at_section_filter_clauses, 10);
}

if ($at_search_query->have_posts()) {
    foreach ($at_search_query->posts as $at_search_post) {
        if (
            !($at_search_post instanceof WP_Post)
            || empty($at_custom_section_terms[$at_search_post->post_type]['section_meta'])
        ) {
            continue;
        }

        $at_section_key = (string) get_post_meta(
            $at_search_post->ID,
            $at_custom_section_terms[$at_search_post->post_type]['section_meta'],
            true
        );

        if (
            $at_section_key !== ''
            && !empty($at_custom_section_terms[$at_search_post->post_type]['terms'][$at_section_key])
        ) {
            $at_search_section_terms[$at_search_post->ID] = $at_custom_section_terms[$at_search_post->post_type]['terms'][$at_section_key];
        }
    }
}
