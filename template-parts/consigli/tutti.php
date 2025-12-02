<?php
global $the_query, $load_posts, $load_card_type;

    $max_posts = isset($_GET['max_posts']) ? intval($_GET['max_posts']) : 12;
    $main_search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
    $paged = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $selected_year = isset($_GET['filter_year']) ? intval($_GET['filter_year']) : 0;

    // Anni disponibili basati sulla data di pubblicazione
    $years = $wpdb->get_col("
        SELECT DISTINCT YEAR(post_date)
        FROM {$wpdb->posts}
        WHERE post_type = 'consiglio'
        AND post_status = 'publish'
        ORDER BY post_date DESC
    ");


    $months = $wpdb->get_col("
        SELECT DISTINCT MONTH(post_date)
        FROM {$wpdb->posts}
        WHERE post_type = 'consiglio'
        AND post_status = 'publish'
        ORDER BY post_date DESC
    ");
    // $months = range(1, 12);
    $selected_month = isset($_GET['filter_month']) ? intval($_GET['filter_month']) : 0;

    // Costruzione argomenti WP_Query
    $args = [
        'post_type'      => 'consiglio',
        'posts_per_page' => $max_posts,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'paged'          => $paged,
    ];

    // Ordinamento per data di pubblicazione
    if (!empty($main_search_query)) {
        $args['s'] = $main_search_query;
    }

    // Filtro per anno e mese basato sulla data di pubblicazione
    if ($selected_year > 0) {
        $args['date_query'][] = [
            'year' => $selected_year,
        ];
    }
    if ($selected_month > 0) {
        $args['date_query'][] = [
            'month' => $selected_month,
        ];
    }

    $the_query = new WP_Query($args);

    // Prendi permalink pagina corrente (senza query string)
    $current_url = get_permalink();

    // Costruiamo la base URL per paginazione mantenendo tutti i parametri
    $base_url = add_query_arg(array(
        'search'      => $main_search_query ? $main_search_query : '',
        'filter_year' => $selected_year > 0 ? $selected_year : 0,
        'filter_month'=> $selected_month > 0 ? $selected_month : 0,
        'max_posts'   => $max_posts,
        'page'        => '%#%',
    ), $current_url);
    
?>


<div class="bg-grey-card py-5">
    <div class="container">
        <!-- Barra di ricerca e filtri -->
        <form method="get" class="mb-3 incarichi-filtro-form">
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
                            <?php echo esc_html($m); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="max-posts" class="form-label mb-0 me-2">Elementi per pagina:</label>
                <select id="max-posts" name="max_posts" class="form-select w-auto me-3">
                    <?php foreach ([5, 10, 12, 20, 50, 100] as $num) : ?>
                        <option value="<?php echo $num; ?>" <?php selected($max_posts, $num); ?>><?php echo $num; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="btn-row d-flex justify-content-center mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search me-2"></i>Filtra
                </button>
            </div>
        </form>

        <div class="row g-4">
            <?php if ($the_query->have_posts()) : ?>
                <?php while ($the_query->have_posts()) : $the_query->the_post(); ?>
                  
                        <?php $load_card_type = 'consiglio';
                        get_template_part('template-parts/consigli/cards-list');
                        ?>
                    
                <?php endwhile; ?>
                <?php wp_reset_postdata(); ?>

                <div class="row my-4">
                    <nav class="pagination-wrapper justify-content-center col-12" aria-label="Navigazione pagine">
                        <?php
                        $pagination_links = paginate_links(array(
                            'base'      => $base_url,
                            'format'    => '',
                            'current'   => $paged,
                            'total'     => $the_query->max_num_pages,
                            'prev_text' => __('&laquo; Precedente'),
                            'next_text' => __('Successivo &raquo;'),
                            'type'      => 'array',
                        ));

                        if ($pagination_links) : ?>
                            <ul class="pagination justify-content-center">
                                <?php foreach ($pagination_links as $link) :
                                    $active = strpos($link, 'current') !== false ? ' active' : '';
                                    $link = str_replace('<a ', '<a class="page-link" ', $link);
                                    $link = str_replace('<span class="current">', '<span class="page-link active" aria-current="page">', $link);
                                ?>
                                    <li class="page-item<?php echo $active; ?>"><?php echo $link; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </nav>
                </div>

            <?php else : ?>
                <div class="alert alert-info text-center" role="alert">
                    Nessun consiglio trovato.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php wp_reset_query(); ?>

<!-- STILE -->
<style>
/* Sfondo grigio precedente ripristinato */
.bg-grey-card {
    background-color: #f8f9fa;
    min-height: vh;
}

/* Form centrato e moderno */
form.incarichi-filtro-form {
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
form.incarichi-filtro-form:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
}
.form-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: center;
    gap: 1rem;
}
.btn-row {
    margin-top: 1rem;
}
form.incarichi-filtro-form label {
    font-weight: 600;
    color: #333;
    margin-bottom: 0;
    font-size: 1rem;
}
form.incarichi-filtro-form input[type="search"] {
    border: 2px solid #e0e0e0;
    border-radius: 0.5rem;
    min-width: 200px;
    max-width: 400px;
    padding: 0.5rem;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
}
form.incarichi-filtro-form select {
    border: 2px solid #e0e0e0;
    border-radius: 0.5rem;
    min-width: 120px;
    max-width: 250px;
    padding: 0.5rem;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
}
form.incarichi-filtro-form input[type="search"]:focus,
form.incarichi-filtro-form select:focus {
    border-color: #007bff;
    box-shadow: 0 0 10px rgba(0, 123, 255, 0.3);
    outline: none;
}
/* Usa colori del tema per il pulsante (assumendo Bootstrap o simili) */
form.incarichi-filtro-form button.btn-primary {
    padding: 0.75rem 2rem;
    font-weight: 600;
    border-radius: 0.5rem;
    cursor: pointer;
    border: none;
    transition: background 0.3s ease, box-shadow 0.3s ease, transform 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    /* Rimossi gradienti hardcoded per usare quelli del tema */
}
form.incarichi-filtro-form button.btn-primary:hover {
    /* Usa stili hover del tema */
    transform: scale(1.05);
}

/* Contenuto centrato */
.row.justify-content-center .col-12.col-md-6.col-lg-4 {
    display: flex;
    justify-content: center;
}

/* Paginazione con colori del tema (rimossi gradienti personalizzati) */
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
    border-radius: 0.5rem; /* Mantenuto arrotondato ma non pill */
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    min-width: 50px;
    text-align: center;
    background: rgba(255, 255, 255, 0.8); /* Sfondo leggero, ma usa colori tema */
    box-shadow: 0 4px 15px rgba(0, 123, 255, 0.2);
    position: relative;
    overflow: hidden;
}
.pagination-wrapper .page-link:hover {
    background-color: #007bff; /* Usa colori tema */
    color: white;
    box-shadow: 0 6px 25px rgba(0, 123, 255, 0.4);
    transform: translateY(-3px);
}
.pagination-wrapper .page-item.active .page-link {
    background-color: #007bff; /* Usa colori tema */
    border-color: #007bff;
    color: white;
    cursor: default;
    box-shadow: 0 8px 30px rgba(0, 123, 255, 0.5);
}

/* Alert centrato */
.alert {
    border-radius: 0.5rem;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}
</style>
