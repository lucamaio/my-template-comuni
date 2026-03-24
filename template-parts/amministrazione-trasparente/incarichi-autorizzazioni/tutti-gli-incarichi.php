<?php
// Evita redirect automatici di WordPress che rovinano i parametri custom
remove_filter('template_redirect', 'redirect_canonical');

global $wpdb;

$max_posts = isset($_GET['max_posts']) ? intval($_GET['max_posts']) : 5;
$main_search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$paged = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$selected_year = isset($_GET['filter_year']) ? intval($_GET['filter_year']) : 0;

// Prendi gli anni disponibili dai post pubblicati (per la combo)
$years = $wpdb->get_col("
    SELECT DISTINCT YEAR(post_date)
    FROM {$wpdb->posts}
    WHERE post_type = 'incarichi_dip' 
      AND post_status = 'publish'
    ORDER BY post_date DESC
");

// Costruiamo argomenti per WP_Query
$args = array(
    'post_type'      => 'incarichi_dip',
    'posts_per_page' => $max_posts,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'paged'          => $paged,
);

if (!empty($main_search_query)) {
    $args['s'] = $main_search_query;
}

if ($selected_year > 0) {
    $args['date_query'] = array(
        array(
            'year' => $selected_year,
        ),
    );
}

// Query personalizzata
$the_query = new WP_Query($args);

// Prendi permalink pagina corrente (senza query string)
$current_url = get_permalink();

// Costruiamo la base URL per paginazione mantenendo tutti i parametri
$base_url = add_query_arg(array(
    'search'      => $main_search_query ? $main_search_query : '',
    'filter_year' => $selected_year > 0 ? $selected_year : 0,
    'max_posts'   => $max_posts,
    'page'        => '%#%',
), $current_url);
?>

<!-- FORM FILTRO -->
<form method="get" class="incarichi-filtro-form">
    <div class="incarichi-filtro-form__head">
        <h3 class="incarichi-filtro-form__title text-decoration-none">Filtra gli incarichi</h3>
        <p class="incarichi-filtro-form__intro text-decoration-none">Seleziona i criteri utili per trovare rapidamente gli incarichi pubblicati.</p>
    </div>
    <div class="incarichi-filtro-form__grid">
    <div class="incarichi-filtro-form__field incarichi-filtro-form__field--search">
    <label for="search" class="form-label">Cerca</label>
    <input type="search" id="search" name="search" class="form-control" placeholder="Cerca..." value="<?php echo esc_attr($main_search_query); ?>">
    </div>

    <div class="incarichi-filtro-form__field">
    <label for="filter-year" class="form-label">Anno</label>
    <select id="filter-year" name="filter_year" class="form-select">
        <option value="0" <?php selected($selected_year, 0); ?>>Tutti gli anni</option>
        <?php foreach ($years as $y) : ?>
            <option value="<?php echo esc_attr($y); ?>" <?php selected($selected_year, $y); ?>>
                <?php echo esc_html($y); ?>
            </option>
        <?php endforeach; ?>
    </select>
    </div>

    <div class="incarichi-filtro-form__field">
    <label for="max-posts" class="form-label">Elementi per pagina</label>
    <select id="max-posts" name="max_posts" class="form-select">
        <?php foreach ([5, 10, 20, 50, 100] as $n) : ?>
            <option value="<?php echo $n; ?>" <?php selected($max_posts, $n); ?>><?php echo $n; ?></option>
        <?php endforeach; ?>
    </select>
    </div>

    <div class="btn-wrapper incarichi-filtro-form__actions">
        <button type="submit" class="btn btn-primary">Filtra</button>
    </div>
    </div>
</form>

<?php if ($the_query->have_posts()) : ?>

    <?php while ($the_query->have_posts()) : $the_query->the_post(); ?>
        <?php get_template_part('template-parts/amministrazione-trasparente/incarichi-autorizzazioni/card'); ?>
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
        Nessun incarico conferito trovato.
    </div>
<?php endif; ?>

<!-- STILE FORM E PAGINAZIONE -->
<style>
form.incarichi-filtro-form {
    padding: 1.1rem;
    background: #fff;
    border: 1px solid #dfe7f0;
    border-radius: 8px;
    box-shadow: 0 10px 24px rgba(23,50,77,.07);
    margin: 0 0 2rem 0;
}
.incarichi-filtro-form__head { margin-bottom: 1rem; }
.incarichi-filtro-form__title { margin-bottom: .35rem; font-size: 1.2rem; }
.incarichi-filtro-form__intro { margin-bottom: 0; }
.incarichi-filtro-form__grid { display: grid; grid-template-columns: minmax(220px, 2fr) repeat(2, minmax(170px, 1fr)) auto; gap: 1rem; align-items: end; }

form.incarichi-filtro-form label.form-label {
    font-weight: 600;
    color: #17324d;
    margin-bottom: .45rem;
}

form.incarichi-filtro-form input[type="search"],
form.incarichi-filtro-form select.form-select {
    min-height: 48px;
    max-width: none;
    width: 100%;
    border: 1px solid #c7d4e2;
    border-radius: 6px;
    transition: border-color 0.3s ease;
}

form.incarichi-filtro-form input[type="search"]:focus,
form.incarichi-filtro-form select.form-select:focus {
    border-color: var(--bs-primary, rgb(6, 62, 138));
    box-shadow: 0 0 0 .2rem rgba(6, 62, 138, .12);
    outline: none;
}

.btn-wrapper {
    margin-left: 0;
    align-self: end;
}

form.incarichi-filtro-form button.btn-primary {
    padding: 0.65rem 1.5rem;
    font-weight: 600;
    border-radius: 6px;
    min-height: 48px;
    cursor: pointer;
    transition: background-color 0.3s ease, box-shadow 0.3s ease;
}

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
    color: var(--bs-primary, rgb(6, 62, 138));
    border: 1.5px solid var(--bs-primary, rgb(6, 62, 138));
    border-radius: 0.4rem;
    font-weight: 600;
    text-decoration: none;
    transition: background-color 0.25s ease, color 0.25s ease, box-shadow 0.25s ease;
    min-width: 40px;
    text-align: center;
}

.pagination-wrapper .page-link:hover {
    background-color: var(--bs-primary, rgb(6, 62, 138));
    color: white;
    box-shadow: 0 0 8px rgba(13, 110, 253, 0.5);
    text-decoration: none;
}

.pagination-wrapper .page-item.active .page-link,
.pagination-wrapper .page-link[aria-current="page"] {
    background-color: var(--bs-primary, rgb(6, 62, 138));
    border-color: var(--bs-primary, rgb(6, 62, 138));
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

@media (max-width: 576px) {
    .incarichi-filtro-form__grid { grid-template-columns: 1fr; }
}

@media (max-width: 991.98px) {
    .incarichi-filtro-form__grid { grid-template-columns: 1fr 1fr; }
    .incarichi-filtro-form__field--search,
    .incarichi-filtro-form__actions { grid-column: 1 / -1; }
}
</style>
