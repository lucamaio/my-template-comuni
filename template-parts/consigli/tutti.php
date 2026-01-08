<?php
// Inizializzazione delle variabili globali per la query e il caricamento dei post
global $the_query, $load_posts, $load_card_type;

// Recupero e sanitizzazione dei parametri GET con valori di default
$max_posts = isset($_GET['max_posts']) ? intval($_GET['max_posts']) : 12; // Numero massimo di post per pagina (default 12)
$main_search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : ''; // Query di ricerca sanitizzata
$paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1; // Pagina corrente (minimo 1) - CAMBIATO DA 'page' A 'paged'
$selected_year = isset($_GET['filter_year']) ? intval($_GET['filter_year']) : 0; // Anno selezionato (0 = tutti)
$selected_month = isset($_GET['filter_month']) ? intval($_GET['filter_month']) : 0; // Mese selezionato (0 = tutti)

// Recupero degli anni disponibili basati sulla data di pubblicazione dei post di tipo 'consiglio' pubblicati
$years = $wpdb->get_col($wpdb->prepare("
    SELECT DISTINCT YEAR(post_date)
    FROM {$wpdb->posts}
    WHERE post_type = %s
    AND post_status = %s
    ORDER BY post_date DESC
", 'consiglio', 'publish'));

// Recupero dei mesi disponibili, filtrati per anno se selezionato, per ottimizzare le opzioni
$month_query = "
    SELECT DISTINCT MONTH(post_date)
    FROM {$wpdb->posts}
    WHERE post_type = %s
    AND post_status = %s
";
$month_params = ['consiglio', 'publish'];
if ($selected_year > 0) {
    $month_query .= " AND YEAR(post_date) = %d";
    $month_params[] = $selected_year;
}
$month_query .= " ORDER BY post_date DESC";
$months = $wpdb->get_col($wpdb->prepare($month_query, ...$month_params));

// Array per mappare numeri mesi a nomi italiani (per UX migliore)
$month_names = [
    1 => 'Gennaio', 2 => 'Febbraio', 3 => 'Marzo', 4 => 'Aprile', 5 => 'Maggio', 6 => 'Giugno',
    7 => 'Luglio', 8 => 'Agosto', 9 => 'Settembre', 10 => 'Ottobre', 11 => 'Novembre', 12 => 'Dicembre'
];

// Costruzione degli argomenti per WP_Query
$args = [
    'post_type'      => 'consiglio', // Tipo di post personalizzato
    'posts_per_page' => $max_posts, // Numero di post per pagina
    'orderby'        => 'date', // Ordinamento per data
    'order'          => 'DESC', // Ordine decrescente
    'paged'          => $paged, // Pagina corrente per paginazione
];

// Aggiunta della ricerca se presente
if (!empty($main_search_query)) {
    $args['s'] = $main_search_query;
}

// Filtro per anno e mese basato sulla data di pubblicazione
if ($selected_year > 0) {
    $args['date_query'][] = ['year' => $selected_year];
}
if ($selected_month > 0) {
    $args['date_query'][] = ['month' => $selected_month];
}

// Esecuzione della query
$the_query = new WP_Query($args);

// Recupero del permalink della pagina corrente senza query string
$current_url = get_permalink();

// Costruzione della base URL per la paginazione, mantenendo tutti i parametri
$base_url = add_query_arg([
    'search'      => $main_search_query ?: '',
    'filter_year' => $selected_year > 0 ? $selected_year : '',
    'filter_month'=> $selected_month > 0 ? $selected_month : '',
    'max_posts'   => $max_posts,
    'paged'        => '%#%', // Placeholder per paginate_links - CAMBIATO DA 'page' A 'paged'
], $current_url);
?>

<div class="bg-grey-card py-5">
    <div class="container">
        <h2 class="title-xxlarge mb-4">
                Esplora tutti i Consigli Comunali
        </h2>
        <!-- Form di ricerca e filtri -->
<!-- FORM FILTRO (STILE INCARICHI) -->
<form method="get" class="mb-3 d-flex align-items-center flex-wrap gap-2 consigli-filtro-form">

    <label for="search" class="form-label mb-0 me-2">Cerca:</label>
    <input
        type="search"
        id="search"
        name="search"
        class="form-control me-3"
        placeholder="Cerca..."
        value="<?php echo esc_attr($main_search_query); ?>"
    >

    <label for="filter-year" class="form-label mb-0 me-2">Anno:</label>
    <select id="filter-year" name="filter_year" class="form-select w-auto me-3">
        <option value="0" <?php selected($selected_year, 0); ?>>Tutti gli anni</option>
        <?php foreach ($years as $y) : ?>
            <option value="<?php echo esc_attr($y); ?>" <?php selected($selected_year, $y); ?>>
                <?php echo esc_html($y); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="filter-month" class="form-label mb-0 me-2">Mese:</label>
    <select id="filter-month" name="filter_month" class="form-select w-auto me-3">
        <option value="0" <?php selected($selected_month, 0); ?>>Tutti i mesi</option>
        <?php foreach ($months as $m) : ?>
            <option value="<?php echo esc_attr($m); ?>" <?php selected($selected_month, $m); ?>>
                <?php echo esc_html($month_names[$m] ?? $m); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="max-posts" class="form-label mb-0 me-2">Elementi per pagina:</label>
    <select id="max-posts" name="max_posts" class="form-select w-auto me-3">
        <?php foreach ([3,6,9,12,18,30] as $num) : ?>
            <option value="<?php echo esc_attr($num); ?>" <?php selected($max_posts, $num); ?>>
                <?php echo esc_html($num); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <div class="btn-wrapper">
        <button type="submit" class="btn btn-primary">Filtra</button>
    </div>

</form>

        <p id="autocomplete-label" class="u-grey-light text-paragraph-card mt-2 mb-30 mt-lg-3 mb-lg-40">
            <?php echo $the_query->found_posts; ?> consigli comunali trovati in ordine di data pubblicazione.
        </p>

        <div class="row g-4">
            <?php if ($the_query->have_posts()) : ?>
                <?php while ($the_query->have_posts()) : $the_query->the_post(); ?>
                    <?php 
                    // Caricamento del template part per le card dei consigli
                    $load_card_type = 'consiglio';
                    get_template_part('template-parts/consigli/cards-list');
                    ?>
                <?php endwhile; ?>
                <?php wp_reset_postdata(); ?>

                <!-- Paginazione -->
                <div class="row my-4">
                    <nav class="pagination-wrapper justify-content-center col-12" aria-label="Navigazione pagine risultati">
                        <?php
                        $pagination_links = paginate_links([
                            'base'      => $base_url,
                            'format'    => '',
                            'current'   => $paged,
                            'total'     => $the_query->max_num_pages,
                            'prev_text' => __('&laquo; Precedente'),
                            'next_text' => __('Successivo &raquo;'),
                            'type'      => 'array',
                        ]);
                        if ($pagination_links) : ?>
                            <ul class="pagination justify-content-center">
                                <?php foreach ($pagination_links as $link) :
                                    $active = strpos($link, 'current') !== false ? ' active' : '';
                                    $link = str_replace('<a ', '<a class="page-link" ', $link);
                                    $link = str_replace('<span class="current">', '<span class="page-link active" aria-current="page">', $link);
                                ?>
                                    <li class="page-item<?php echo esc_attr($active); ?>"><?php echo $link; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </nav>
                </div>
            <?php else : ?>
                <div class="alert alert-info text-center" role="alert">
                    <i class="bi bi-info-circle me-2" aria-hidden="true"></i>Nessun consiglio trovato con i filtri applicati.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php wp_reset_query(); ?>


<style>

/* ================================
   FORM FILTRO CONSIGLI – STILE INCARICHI
   ================================ */

form.consigli-filtro-form {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
    gap: 1rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 0.5rem;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    margin: 0 0 2rem 0;
}

form.consigli-filtro-form label.form-label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0;
    align-self: center;
}

form.consigli-filtro-form input[type="search"],
form.consigli-filtro-form select.form-select {
    flex-grow: 1;
    min-width: 120px;
    max-width: 250px;
    border: 1.5px solid #ced4da;
    transition: border-color 0.3s ease;
    border-radius: 0.35rem;
    padding: 0.45rem 0.65rem;
}

form.consigli-filtro-form input[type="search"]:focus,
form.consigli-filtro-form select.form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 6px rgba(13, 110, 253, 0.3);
    outline: none;
}

/* Pulsante Filtra */
.btn-wrapper {
    flex-shrink: 0;
    margin-left: auto;
    align-self: flex-start;
}

form.consigli-filtro-form button.btn-primary {
    padding: 0.45rem 1.5rem;
    font-weight: 600;
    border-radius: 0.4rem;
    height: 38px;
    cursor: pointer;
    transition: background-color 0.3s ease, box-shadow 0.3s ease;
}

form.consigli-filtro-form button.btn-primary:hover {
    background-color: #0b5ed7;
    box-shadow: 0 4px 8px rgba(11, 94, 215, 0.4);
}

/* ================================
   PAGINAZIONE (in stile incarichi)
   ================================ */

.pagination-wrapper .pagination {
    display: flex;
    justify-content: center;
    list-style: none;
    padding-left: 0;
    margin-top: 1.5rem;
    gap: 0.5rem;
}

.pagination-wrapper .page-link {
    display: block;
    padding: 0.5rem 0.9rem;
    color: #0d6efd;
    border: 1.5px solid #0d6efd;
    border-radius: 0.4rem;
    font-weight: 600;
    text-decoration: none;
    transition: background-color 0.25s ease, color 0.25s ease, box-shadow 0.25s ease;
    min-width: 40px;
    text-align: center;
}

.pagination-wrapper .page-link:hover {
    background-color: #0d6efd;
    color: white;
    box-shadow: 0 0 8px rgba(13, 110, 253, 0.5);
    text-decoration: none;
}

.pagination-wrapper .page-item.active .page-link,
.pagination-wrapper .page-link[aria-current="page"] {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: white;
    cursor: default;
    box-shadow: 0 0 12px rgba(13, 110, 253, 0.75);
}

.pagination-wrapper .page-item.disabled .page-link {
    color: #6c757d;
    pointer-events: none;
    background-color: transparent;
    border-color: transparent;
    cursor: default;
}

/* ================================
   RESPONSIVE
   ================================ */

@media (max-width: 576px) {
    form.consigli-filtro-form {
        flex-direction: column;
    }

    .btn-wrapper {
        margin-left: 0;
        width: 100%;
        margin-top: 0.5rem;
        align-self: stretch;
        display: flex;
        justify-content: flex-start;
    }

    form.consigli-filtro-form button.btn-primary {
        width: auto;
        height: 38px;
    }
}

</style>


