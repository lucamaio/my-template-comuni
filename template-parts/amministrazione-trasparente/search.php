<?php
/**
 * Prepara la ricerca globale dell'Amministrazione Trasparente.
 *
 * Il file viene incluso da categorie.php e rende disponibili nello stesso
 * scope i dati usati dal modulo e dall'elenco dei risultati.
 */

$at_search_term = isset($_GET['at_search']) && is_string($_GET['at_search'])
    ? sanitize_text_field(wp_unslash($_GET['at_search']))
    : '';
$at_search_page = isset($_GET['at_page']) && is_scalar($_GET['at_page'])
    ? max(1, absint($_GET['at_page']))
    : 1;
$at_search_query = null;
$at_category_results = [];
$at_search_too_short = $at_search_term !== ''
    && mb_strlen($at_search_term, 'UTF-8') < 2;
$at_search_post_types = ['elemento_trasparenza'];
$at_search_section_terms = [];

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
];

foreach ($at_custom_search_types as $at_post_type => $at_search_config) {
    if (
        dci_get_option($at_search_config['option'], 'Trasparenza') !== 'true'
        || !post_type_exists($at_post_type)
    ) {
        continue;
    }

    $at_section_term = get_term_by(
        'name',
        $at_search_config['section'],
        'tipi_cat_amm_trasp'
    );

    if (
        !($at_section_term instanceof WP_Term)
        || !$at_term_is_public($at_section_term)
    ) {
        continue;
    }

    $at_search_post_types[] = $at_post_type;
    $at_search_section_terms[$at_post_type] = $at_section_term;
}

if ($at_search_term === '' || $at_search_too_short) {
    return;
}

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
}

$at_search_query = new WP_Query([
    'post_type'           => array_values(array_unique($at_search_post_types)),
    'post_status'         => 'publish',
    'posts_per_page'      => 10,
    'paged'               => $at_search_page,
    's'                   => $at_search_term,
    'orderby'             => 'date',
    'order'               => 'DESC',
    'ignore_sticky_posts' => true,
]);
