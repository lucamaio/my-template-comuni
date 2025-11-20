<?php

$max_posts = isset($_GET['max_posts']) ? intval($_GET['max_posts']) : 10;
$main_search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

// Recupera i valori attuali dei filtri dalla URL per mantenere lo stato della ricerca
$current_oggetto               = isset($_GET['oggetto']) ? sanitize_text_field($_GET['oggetto']) : '';
$current_cig                   = isset($_GET['cig']) ? sanitize_text_field($_GET['cig']) : '';
$current_procedura_contraente  = isset($_GET['procedura_contraente']) ? sanitize_text_field($_GET['procedura_contraente']) : '';
$current_stato                 = isset($_GET['stato']) ? sanitize_text_field($_GET['stato']) : '';
$current_anno                  = isset($_GET['anno']) ? intval($_GET['anno']) : '';


// Funzione per ottenere gli anni disponibili (es. ultimi 10 anni, o da un CPT specifico)
if ( ! function_exists( 'dci_get_available_years' ) ) {
    function dci_get_available_years() {
        $years = array();
        $current_year = gmdate('Y');
        for ($i = $current_year; $i >= $current_year - 12; $i--) { // Ultimi 10 anni
            $years[] = (string) $i;
        }
        return $years;
    }
}

// Funzione per ottenere gli stati disponibili (es. Pubblicato, Scaduto, Chiuso, ecc.)
if ( ! function_exists( 'dci_get_available_states' ) ) {
    function dci_get_available_states() {
        return [
            'attivo'    => 'Attivo',
            'scaduto'   => 'Scaduto',
            // 'archiviato' => 'Archiviato', // Questo stato richiede un meta field specifico o una logica più complessa se basato su date
        ];
    }
}

// Funzione per ottenere i tipi di procedura contraente
// QUESTA È LA FUNZIONE CHE HO SPOSTATO PIÙ IN ALTO E INCLUSO NELL'if (!function_exists)
if ( ! function_exists( 'dci_tipi_procedura_contraente_array' ) ) {
    function dci_tipi_procedura_contraente_array() {
        return [
            "01 - Procedura aperta",
            "02 - Procedura ristretta",
            "03 - Procedura negoziata previa pubblicazione",
            "04 - Procedura negoziata senza previa pubblicazione",
            "05 - Dialogo competitivo",
            "06 - Procedura negoziata senza previa i nozione cl gara (settori speciali)",
            "07 - Sistema dinamico dl acquisizione",
            "08-Affloamento in economia - cottimo fiduciario",
            "14-Procedura selettiva ex art 238 c7, d.lgs.",
            "17-Affidamento diretto ex art. 5 cella legge",
            "21-Procedura ristretta derivante da avvisi con cui si indice la gara",
            "22-Procedura negoziata previa indizione dl gara (settori speciali}",
            "23-Affloamento diretto",
            "24-Affloamento diretto a societa' in house",
            "25-Affloamento diretto a societa raggruppate/consorziate o controllate nelle concessioni e nei partenariati",
            "26.Affldamento diretto in adesione ad accordo quadro/convenzione",
            "27 -Confronto competitivo in adesione ad accordo quadro/convenzione",
            "28. Procedura al sensi dei regolamenti degli organi costituzionali",
            "29 - Procedura ristretta semplificata",
            "30 - Procedura derivante oa legge regionale",
            "31 -Affidamento diretto per variante superiore al dell'importo contrattuale",
            "32-Affidamento riservato",
            "33 -Procedura negoziata per affidamenti sotto soglia",
            "34 - Procedura art. 16 comma 2. opr 280/2001 per opere urbanizzazione a scomputo primarie sotto soglia comunitaria",
            "35. Parternariato per l'innovazione",
            "36.Affloamento diretto per lavori. servizi o forniture supplementari",
            "37 - Procedura competitiva con negoziazione",
            "38. Procedura disciplinata da regolamento interno per settori speciali",
            "39 - Diretto per modifiche contrattuali o varianti per le quali é necessaria una nuova procedura dl affidamento",
        ];
    }
}

// --- FINE DEFINIZIONI FUNZIONI NECESSARIE ---


$args = array(
    'post_type'       => 'bando',
    'posts_per_page'  => $max_posts,
    'meta_key'        => '_dci_bando_data_inizio',
    'orderby'         => 'meta_value_num',
    'order'           => 'DESC',
    'paged'              => $paged,
    's'               => $main_search_query, // Per la ricerca generica su titolo/contenuto
);

$meta_query_array = array(); // Inizializza l'array per le meta query
$meta_query_array['relation'] = 'AND'; // Combina tutti i filtri con AND

// Filtro per Oggetto
if ( ! empty( $current_oggetto ) ) {
    $meta_query_array[] = array(
        'key'     => '_dci_bando_oggetto',
        'value'   => $current_oggetto,
        'compare' => 'LIKE',
    );
}

// Filtro per CIG
if ( ! empty( $current_cig ) ) {
    $meta_query_array[] = array(
        'key'     => '_dci_bando_cig',
        'value'   => $current_cig,
        'compare' => 'LIKE',
    );
}

// Filtro per Procedura scelta Contraente
if ( ! empty( $current_procedura_contraente ) ) {
    $meta_query_array[] = array(
        'key'     => '_dci_bando_scleta_contraente',
        'value'   => $current_procedura_contraente,
        'compare' => '=',
    );
}

// Filtro per Anno
if ( ! empty( $current_anno ) ) {
    $start_of_year = strtotime("{$current_anno}-01-01 00:00:00");
    $end_of_year = strtotime("{$current_anno}-12-31 23:59:59");

    $meta_query_array[] = array(
        'key'     => '_dci_bando_data_inizio',
        'value'   => array( $start_of_year, $end_of_year ),
        'type'    => 'NUMERIC',
        'compare' => 'BETWEEN',
    );
}

// Filtro per Stato (Logica dinamica basata su date)
if ( ! empty( $current_stato ) ) {
    $today = current_time('timestamp');

    if ( $current_stato === 'attivo' ) {
        $meta_query_array[] = array(
            'relation' => 'AND',
            array(
                'key'     => '_dci_bando_data_inizio',
                'value'   => $today,
                'type'    => 'NUMERIC',
                'compare' => '<=',
            ),
            array(
                'key'     => '_dci_bando_data_fine',
                'value'   => $today,
                'type'    => 'NUMERIC',
                'compare' => '>=',
            ),
        );
    } elseif ( $current_stato === 'scaduto' ) {
        $meta_query_array[] = array(
            'key'     => '_dci_bando_data_fine',
            'value'   => $today,
            'type'    => 'NUMERIC',
            'compare' => '<',
        );
    }
}

// Applica la meta_query solo se ci sono filtri attivi (più di solo 'relation' => 'AND')
if ( count( $meta_query_array ) > 1 ) {
    $args['meta_query'] = $meta_query_array;
}

$the_query = new WP_Query($args);
$prefix = "_dci_bando_";
?>

<div class="search-bar-container bg-light p-4 mb-4 rounded shadow-sm" style="background-color: #f0f0f0 !important;">
    <form role="search" method="get" class="search-form" action="<?php // echo esc_url(home_url('/')); ?>">
        <input type="hidden" name="post_type" value="bando" />
        <div class="row g-3">
            <div class="col-md-6 col-lg-4">
                <label for="oggetto" class="form-label visually-hidden" style="color: #333;"><?php _e('Oggetto', 'design_comuni_italia'); ?></label>
                <input type="text" class="form-control" id="oggetto" name="oggetto" placeholder="<?php esc_attr_e('Oggetto', 'design_comuni_italia'); ?>" value="<?php echo esc_attr($current_oggetto); ?>" style="background-color: #ffffff; color: #333; border: 1px solid #ccc;">
            </div>
            <div class="col-md-6 col-lg-4">
                <label for="cig" class="form-label visually-hidden" style="color: #333;"><?php _e('CIG', 'design_comuni_italia'); ?></label>
                <input type="text" class="form-control" id="cig" name="cig" placeholder="<?php esc_attr_e('CIG', 'design_comuni_italia'); ?>" value="<?php echo esc_attr($current_cig); ?>" style="background-color: #ffffff; color: #333; border: 1px solid #ccc;">
            </div>
            <div class="col-md-6 col-lg-4">
                <label for="procedura_contraente" class="form-label visually-hidden" style="color: #333;"><?php _e('Procedura scelta Contraente', 'design_comuni_italia'); ?></label>
                <select class="form-select" id="procedura_contraente" name="procedura_contraente" style="background-color: #ffffff; color: #333; border: 1px solid #ccc;">
                    <option value=""><?php _e('Procedura scelta Contraente', 'design_comuni_italia'); ?></option>
                    <?php
                    // Ora la funzione è definita qui sopra e può essere chiamata
                    $procedure = dci_tipi_procedura_contraente_array();
                    foreach ($procedure as $proc) {
                        echo '<option value="' . esc_attr($proc) . '"' . selected($current_procedura_contraente, $proc, false) . '>' . esc_html($proc) . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-6 col-lg-4">
                <label for="stato" class="form-label visually-hidden" style="color: #333;"><?php _e('Stato', 'design_comuni_italia'); ?></label>
                <select class="form-select" id="stato" name="stato" style="background-color: #ffffff; color: #333; border: 1px solid #ccc;">
                    <option value=""><?php _e('Stato', 'design_comuni_italia'); ?></option>
                    <?php
                    $states = dci_get_available_states();
                    foreach ($states as $key => $label) {
                        echo '<option value="' . esc_attr($key) . '"' . selected($current_stato, $key, false) . '>' . esc_html($label) . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-6 col-lg-4">
                <label for="anno" class="form-label visually-hidden" style="color: #333;"><?php _e('Anno', 'design_comuni_italia'); ?></label>
                <select class="form-select" id="anno" name="anno" style="background-color: #ffffff; color: #333; border: 1px solid #ccc;">
                    <option value=""><?php _e('Anno', 'design_comuni_italia'); ?></option>
                    <?php
                    $years = dci_get_available_years();
                    foreach ($years as $year) {
                        echo '<option value="' . esc_attr($year) . '"' . selected($current_anno, $year, false) . '>' . esc_html($year) . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="col-12 col-md-auto">
                <button type="submit" class="btn btn-primary form-control" style="padding: 0.18rem 0.7rem; font-size: 0.875rem;">
                    <svg class="icon" style="fill:#FFFFFF;"><use href="#it-search"></use></svg> <?php _e('Cerca', 'design_comuni_italia'); ?>
                </button>
            </div>
        </div>
    </form>
</div>



    <?php if ($the_query->have_posts()) : ?>
        <?php while ($the_query->have_posts()) : $the_query->the_post();
            get_template_part('template-parts/bandi-di-gara/card');
        endwhile;
        wp_reset_postdata();?>
        <div class="row my-4">
    <nav class="pagination-wrapper justify-content-center col-12" aria-label="Navigazione pagine">
        <?php echo dci_bootstrap_pagination(); ?>
    </nav>
</div>
    <?php else : ?>
        <div class="alert alert-info text-center" role="alert">
            Nessun bando trovato.
        </div>
    <?php endif; ?>
