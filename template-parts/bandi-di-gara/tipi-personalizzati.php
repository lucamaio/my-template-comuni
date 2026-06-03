<?php
// global $tipo_personalizzato;

// Se la pagina ha un tipo personalizzato, convertilo in chiave valida
$sezioni_valide = dci_get_sezioni_bando(); // ['pubblicazione', 'affidamento', 'esecutiva', 'sponsorizzazioni']
// $tipo_personalizzato_nome = get_queried_object()->name ?? '';
// $tipo_personalizzato = '';
// foreach ($sezioni_valide as $chiave => $label) {
//     if (strcasecmp($label, $tipo_personalizzato_nome) === 0) { // confronto case-insensitive
//         $tipo_personalizzato = $chiave;
//         break;
//     }
// }

$max_posts = isset($_GET['max_posts']) ? intval($_GET['max_posts']) : 10;
$main_search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$paged = max(
    1,
    (int) get_query_var('paged'),
    (int) get_query_var('page'),
    isset($_GET['paged']) ? absint($_GET['paged']) : 0
);

// Recupera i valori dei filtri dalla URL
$current_oggetto               = isset($_GET['oggetto']) ? sanitize_text_field($_GET['oggetto']) : '';
$current_cig                   = isset($_GET['cig']) ? sanitize_text_field($_GET['cig']) : '';
$current_procedura_contraente  = isset($_GET['procedura_contraente']) ? sanitize_text_field($_GET['procedura_contraente']) : '';
$current_stato                 = isset($_GET['stato']) ? sanitize_text_field($_GET['stato']) : '';
$current_anno                  = isset($_GET['anno']) ? intval($_GET['anno']) : '';

$form_action = '';
$current_object = get_queried_object();
if ($current_object instanceof WP_Term) {
    $term_link = get_term_link($current_object);
    $form_action = !is_wp_error($term_link) ? $term_link : '';
} elseif (get_queried_object_id()) {
    $form_action = get_permalink(get_queried_object_id());
}

// Funzioni ausiliarie
if (!function_exists('dci_get_available_years')) {
    function dci_get_available_years() {
        $years = [];
        $current_year = gmdate('Y');
        for ($i = $current_year; $i >= $current_year - 12; $i--) {
            $years[] = (string)$i;
        }
        return $years;
    }
}

if (!function_exists('dci_get_available_states')) {
    function dci_get_available_states() {
        return ['attivo'=>'Attivo','scaduto'=>'Scaduto'];
    }
}

// Costruzione query principale
$args = [
    'post_type'      => 'bando',
    'posts_per_page' => $max_posts,
    'meta_key'       => '_dci_bando_data_inizio',
    'orderby'        => 'meta_value_num',
    'order'          => 'DESC',
    'paged'          => $paged,
    's'              => $main_search_query
];

$meta_query_array = ['relation'=>'AND'];

// Filtri base
if (!empty($current_oggetto)) {
    $meta_query_array[] = ['key'=>'_dci_bando_oggetto','value'=>$current_oggetto,'compare'=>'LIKE'];
}
if (!empty($current_cig)) {
    $meta_query_array[] = ['key'=>'_dci_bando_cig','value'=>$current_cig,'compare'=>'LIKE'];
}
if (!empty($current_procedura_contraente)) {
    $meta_query_array[] = ['key'=>'_dci_bando_scleta_contraente','value'=>$current_procedura_contraente,'compare'=>'='];
}
if (!empty($current_anno)) {
    $start_of_year = strtotime("{$current_anno}-01-01 00:00:00");
    $end_of_year   = strtotime("{$current_anno}-12-31 23:59:59");
    $meta_query_array[] = [
        'key'=>'_dci_bando_data_inizio',
        'value'=>[$start_of_year,$end_of_year],
        'type'=>'NUMERIC',
        'compare'=>'BETWEEN'
    ];
}
if (!empty($current_stato)) {
    $today = current_time('timestamp');
    if ($current_stato==='attivo') {
        $meta_query_array[] = [
            'relation'=>'AND',
            ['key'=>'_dci_bando_data_inizio','value'=>$today,'type'=>'NUMERIC','compare'=>'<='],
            ['key'=>'_dci_bando_data_fine','value'=>$today,'type'=>'NUMERIC','compare'=>'>=']
        ];
    } elseif ($current_stato==='scaduto') {
        $meta_query_array[] = ['key'=>'_dci_bando_data_fine','value'=>$today,'type'=>'NUMERIC','compare'=>'<'];
    }
}

// Filtro tipo personalizzato
// if (!empty($tipo_personalizzato)) {
//     $meta_query_array[] = ['key'=>'_dci_bando_sezione','value'=>$tipo_personalizzato,'compare'=>'='];
// }

// Applica meta_query se ci sono filtri
if (count($meta_query_array) > 1) {
    $args['meta_query'] = $meta_query_array;
}

// Esegui query
$the_query = new WP_Query($args);
?>

<style>
    .dci-filter-panel {
        background: #ffffff;
        border: 1px solid #dfe7f0;
        border-radius: 8px;
        box-shadow: 0 10px 24px rgba(23, 50, 77, 0.07);
        padding: 1.1rem;
        margin-bottom: 1.5rem;
    }

    .dci-filter-panel__title {
        margin-bottom: .35rem;
        font-size: 1.2rem;
    }

    .dci-filter-panel__intro {
        margin-bottom: 1rem;
    }

    .dci-filter-panel .form-control,
    .dci-filter-panel .form-select {
        min-height: 48px;
        border: 1px solid #c7d4e2;
        border-radius: 6px;
        box-shadow: none;
    }

    .dci-filter-panel .form-control:focus,
    .dci-filter-panel .form-select:focus {
        border-color: var(--bs-primary, rgb(6, 62, 138));
        box-shadow: 0 0 0 .2rem rgba(6, 62, 138, .12);
    }

    .dci-filter-panel .btn-primary {
        min-height: 48px;
        border-radius: 6px;
        font-weight: 700;
    }
</style>

<div class="search-bar-container dci-filter-panel">
    <form role="search" method="get" class="search-form" action="<?php echo esc_url($form_action); ?>">
        <h3 class="dci-filter-panel__title text-decoration-none">Filtra i contenuti</h3>
        <p class="dci-filter-panel__intro text-decoration-none">Affina la ricerca usando i filtri disponibili per questa sezione.</p>
        <div class="row g-3">
            <!-- Filtri come nel tuo codice originale -->
            <!-- Oggetto -->
            <div class="col-md-6 col-lg-4">
                <input type="text" class="form-control" id="oggetto" name="oggetto" placeholder="Oggetto" value="<?php echo esc_attr($current_oggetto); ?>">
            </div>
            <!-- CIG -->
            <div class="col-md-6 col-lg-4">
                <input type="text" class="form-control" id="cig" name="cig" placeholder="CIG" value="<?php echo esc_attr($current_cig); ?>">
            </div>
            <!-- Procedura -->
            <div class="col-md-6 col-lg-4">
                <select class="form-select" id="procedura_contraente" name="procedura_contraente">
                    <option value="">Procedura scelta Contraente</option>
                    <?php foreach (dci_tipi_procedura_contraente_array() as $proc) {
                        echo '<option value="'.esc_attr($proc).'"'.selected($current_procedura_contraente,$proc,false).'>'.esc_html($proc).'</option>';
                    } ?>
                </select>
            </div>
            <!-- Stato -->
            <div class="col-md-6 col-lg-4">
                <select class="form-select" id="stato" name="stato">
                    <option value="">Stato</option>
                    <?php foreach (dci_get_available_states() as $key=>$label) {
                        echo '<option value="'.esc_attr($key).'"'.selected($current_stato,$key,false).'>'.esc_html($label).'</option>';
                    } ?>
                </select>
            </div>
            <!-- Anno -->
            <div class="col-md-6 col-lg-4">
                <select class="form-select" id="anno" name="anno">
                    <option value="">Anno</option>
                    <?php foreach (dci_get_available_years() as $year) {
                        echo '<option value="'.esc_attr($year).'"'.selected($current_anno,$year,false).'>'.esc_html($year).'</option>';
                    } ?>
                </select>
            </div>
            <!-- Submit -->
            <div class="col-12 col-md-4 col-lg-3">
                <button type="submit" class="btn btn-primary w-100">Cerca</button>
            </div>
        </div>
    </form>
</div>

<?php if ($the_query->have_posts()) : ?>
    <?php while ($the_query->have_posts()) : $the_query->the_post();
        get_template_part('template-parts/bandi-di-gara/card');
    endwhile; wp_reset_postdata(); ?>
    <div class="row my-4">
        <nav class="pagination-wrapper justify-content-center col-12" aria-label="Navigazione pagine">
            <?php echo dci_bootstrap_pagination($the_query, false); ?>
        </nav>
    </div>
<?php else: ?>
    <div class="alert alert-info text-center">Nessun bando trovato.</div>
<?php endif; ?>
