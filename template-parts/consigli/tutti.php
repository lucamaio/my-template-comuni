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
        <!-- Form di ricerca e filtri -->
        <form method="get" class="mb-3 filter-form" role="search" aria-label="Filtri per ricerca">
            <div class="form-row d-flex align-items-center justify-content-center gap-2 flex-wrap">
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
                <select id="max-posts" name="max_posts" class="form-select w-auto me-3" aria-label="Seleziona numero di elementi per pagina">
                    <?php foreach ([3,6,9,12,18,21,30,60] as $num) : ?>
                        <option value="<?php echo esc_attr($num); ?>" <?php selected($max_posts, $num); ?>><?php echo esc_html($num); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <!-- Pulsanti Filtra e Reset -->
            <div class="btn-row d-flex justify-content-center mt-3 gap-2">
                <button type="submit" class="btn btn-primary btn-filter-reset" aria-label="Applica i filtri di ricerca">
                    <svg class="icon flex-shrink-0 me-2" aria-hidden="true" width="12" height="12" style="fill: #fff">
                        <use xlink:href="#it-search"></use>
                    </svg>
                    Filtra
                </button>

                <button type="reset" class="btn btn-danger btn-filter-reset" aria-label="Resetta tutti i filtri">
                    <svg class="icon flex-shrink-0 me-2" aria-hidden="true" width="12" height="12" style="fill: #fff">
                        <use xlink:href="#it-delete"></use>
                    </svg>
                    Reset
                </button>
            </div>
        </form>

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


<!-- STILE Migliorato -->
<style>
/* Sfondo grigio per la sezione */
.bg-grey-card {
    background-color: #f8f9fa;
    min-height: 75vh;
}

/* Form centrato, moderno e responsivo */
form.filter-form {
    padding: 1.5rem;
    background: rgba(255, 255, 255, 0.9);
    border-radius: 1rem;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.18);
    max-width: 100%;
    margin-bottom: 2rem;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
/* form.filter-form:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
} */

/* Layout del form */
.form-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: center;
    gap: 1rem;
}

/* Etichette */
form.filter-form label {
    font-weight: 600;
    color: #333;
    margin-bottom: 0;
    font-size: 1rem;
}

/* Input e select stilizzati */
form.filter-form input[type="search"],
form.filter-form select {
    border: 2px solid #e0e0e0;
    border-radius: 0.5rem;
    padding: 0.5rem;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
    min-width: 120px;
}
form.filter-form input[type="search"] {
    min-width: 200px;
    max-width: 400px;
}
form.filter-form input[type="search"]:focus,
form.filter-form select:focus {
    border-color: #007bff;
    box-shadow: 0 0 10px rgba(0, 123, 255, 0.3);
    outline: none;
}

/* Pulsanti Filtra e Reset uniformi */
.btn-row {
    margin-top: 1rem;
    display: flex;
    justify-content: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.btn-filter-reset {
    padding: 0.75rem 1.75rem;
    font-weight: 600;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
    cursor: pointer;
}

.btn-filter-reset:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
}

.btn-filter-reset.btn-primary {
    background-color: #007bff;
    border-color: #007bff;
    color: #fff;
}

.btn-filter-reset.btn-primary:hover {
    background-color: #0056b3;
    border-color: #0056b3;
    color: #fff;
}

.btn-filter-reset.btn-danger {
    background-color: #dc3545;
    border-color: #dc3545;
    color: #fff;
}

.btn-filter-reset.btn-danger:hover {
    background-color: #a71d2a;
    border-color: #a71d2a;
    color: #fff;
}

/* Paginazione centrata e responsiva */
.pagination-wrapper .pagination {
    display: flex;
    justify-content: center;
    list-style: none;
    padding-left: 0;
    margin-top: 2rem;
    gap: 0.5rem;
    flex-wrap: wrap;
}
.pagination-wrapper .page-link {
    display: block;
    padding: 0.75rem 1.25rem;
    color: #007bff;
    border: 2px solid #007bff;
    border-radius: 0.5rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    min-width: 50px;
    text-align: center;
    background: rgba(255, 255, 255, 0.8);
    box-shadow: 0 4px 15px rgba(0, 123, 255, 0.2);
}
.pagination-wrapper .page-link:hover {
    background-color: #007bff;
    color: white;
    box-shadow: 0 6px 25px rgba(0, 123, 255, 0.4);
    transform: translateY(-3px);
}
.pagination-wrapper .page-item.active .page-link {
    background-color: #007bff;
    border-color: #007bff;
    color: white;
    cursor: default;
    box-shadow: 0 8px 30px rgba(0, 123, 255, 0.5);
}

/* Alert centrato e stilizzato */
.alert {
    border-radius: 0.5rem;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

/* Responsività per dispositivi mobili */
@media (max-width: 768px) {
    .form-row {
        flex-direction: column;
        align-items: stretch;
    }
    form.filter-form input[type="search"] {
        min-width: unset;
        max-width: unset;
    }
    .btn-row {
        flex-direction: column;
    }
}
.card-rounded {
    border-radius: 0.47rem; /* Puoi aumentare a 1.5rem o 2rem per arrotondare ancora di più */
}
</style>
