<?php
// Evita redirect canonici che possono trasformare URL filtri/paginazione in route tassonomia non valide
remove_filter('template_redirect', 'redirect_canonical');

global $wpdb;

// Lettura parametri da URL
$max_posts = dci_sanitize_posts_per_page(isset($_GET['max_posts']) ? $_GET['max_posts'] : 10, 10, 50);
$main_search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$paged_from_query = max(1, (int) get_query_var('paged'));
$paged_from_page  = max(1, (int) get_query_var('page'));
$paged_from_get   = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : (isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1);
$paged_from_custom = isset($_GET['atti_page']) ? max(1, absint($_GET['atti_page'])) : 1;
$paged = max($paged_from_query, $paged_from_page, $paged_from_get, $paged_from_custom);
$selected_year = isset($_GET['filter_year']) ? intval($_GET['filter_year']) : 0;

// Anni disponibili
$years = $wpdb->get_col("
    SELECT DISTINCT YEAR(post_date)
    FROM {$wpdb->posts}
    WHERE post_type = 'atto_concessione'
      AND post_status = 'publish'
    ORDER BY post_date DESC
");


$search_post_ids = array();
if (!empty($main_search_query)) {
    $search_like = '%' . $wpdb->esc_like($main_search_query) . '%';

    $meta_keys = array(
        '_dci_atto_concessione_ragione_sociale',
        '_dci_atto_concessione_codice_fiscale',
        '_dci_atto_concessione_responsabile',
        '_dci_atto_concessione_rag_incarico',
        '_dci_atto_concessione_importo',
    );

    $meta_placeholders = implode(',', array_fill(0, count($meta_keys), '%s'));

    $sql = "SELECT DISTINCT p.ID
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID
            WHERE p.post_type = 'atto_concessione'
              AND p.post_status = 'publish'
              AND (p.post_title LIKE %s OR p.post_content LIKE %s OR (pm.meta_key IN ($meta_placeholders) AND pm.meta_value LIKE %s))";

    $prepared = $wpdb->prepare(
        $sql,
        array_merge(array($search_like, $search_like), $meta_keys, array($search_like))
    );

    $search_post_ids = array_map('intval', (array) $wpdb->get_col($prepared));

    if (empty($search_post_ids)) {
        $search_post_ids = array(0);
    }
}

// Costruzione argomenti WP_Query
$args = [
    'post_type'      => 'atto_concessione',
    'posts_per_page' => $max_posts,
    'orderby'        => 'meta_value_num',
    'order'          => 'DESC',
    'paged'          => $paged,
    'no_found_rows'  => false,
];

if (!empty($main_search_query)) {
    $args['post__in'] = $search_post_ids;
}

if ($selected_year > 0) {
    $args['date_query'] = [
        [
            'year' => $selected_year,
        ]
    ];
}

$the_query = new WP_Query($args);

?>

<!-- FORM FILTRI -->
<form method="get" class="incarichi-filtro-form">
    <div class="incarichi-filtro-form__head">
        <h3 class="incarichi-filtro-form__title text-decoration-none">Filtra gli atti</h3>
        <p class="incarichi-filtro-form__intro text-decoration-none">Cerca per parola chiave e limita i risultati per anno o numero di elementi.</p>
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
        <?php foreach ([5, 10, 20, 50, 100] as $num) : ?>
            <option value="<?php echo $num; ?>" <?php selected($max_posts, $num); ?>><?php echo $num; ?></option>
        <?php endforeach; ?>
    </select>
    </div>

    <div class="btn-wrapper incarichi-filtro-form__actions">
        <button type="submit" class="btn btn-primary">Filtra</button>
    </div>
    </div>
</form>

<p class="dci-results-count mb-4 text-decoration-none" role="status">
    <strong>
        <?php
        printf(
            esc_html__('Totale elementi: %s', 'design_comuni_italia'),
            esc_html(number_format_i18n((int) $the_query->found_posts))
        );
        ?>
    </strong>
</p>

<?php if ($the_query->have_posts()) : ?>

    <?php while ($the_query->have_posts()) : $the_query->the_post(); ?>
        <?php get_template_part('template-parts/amministrazione-trasparente/atto-concessione/card'); ?>
    <?php endwhile; ?>
    <?php wp_reset_postdata(); ?>

 <div class="row my-4">
        <nav class="pagination-wrapper justify-content-center col-12" aria-label="Navigazione pagine">
            <?php
            get_template_part(
                'template-parts/amministrazione-trasparente/paginazione-personalizzata',
                null,
                [
                    'query'    => $the_query,
                    'current'  => $paged,
                    'page_arg' => 'atti_page',
                ]
            );
            ?>
        </nav>
    </div>

<?php else : ?>
    <div class="alert alert-info text-center" role="alert">
        Nessun incarico conferito trovato.
    </div>
<?php endif; ?>

<!-- STILE -->
<style>
form.incarichi-filtro-form {
    padding: 1.1rem;
    background: #fff;
    border: 1px solid #dfe7f0;
    border-radius: 8px;
    box-shadow: 0 10px 24px rgba(23,50,77,.07);
    max-width: 100%;
    margin-bottom: 2rem;
}

.incarichi-filtro-form__head {
    margin-bottom: 1rem;
}

.incarichi-filtro-form__title {
    margin-bottom: .35rem;
    font-size: 1.2rem;
}

.incarichi-filtro-form__intro {
    margin-bottom: 0;
}

.incarichi-filtro-form__grid {
    display: grid;
    grid-template-columns: minmax(220px, 2fr) repeat(2, minmax(170px, 1fr)) auto;
    gap: 1rem;
    align-items: end;
}

form.incarichi-filtro-form label {
    font-weight: 600;
    color: #17324d;
    margin-bottom: .45rem;
}
form.incarichi-filtro-form input[type="search"],
form.incarichi-filtro-form select {
    border: 1px solid #c7d4e2;
    min-height: 48px;
    max-width: none;
    width: 100%;
    border-radius: 6px;
}
form.incarichi-filtro-form input[type="search"]:focus,
form.incarichi-filtro-form select:focus {
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

/* PAGINAZIONE */
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
}
.pagination-wrapper .page-item.active .page-link {
    background-color: var(--bs-primary, rgb(6, 62, 138));
    border-color: var(--bs-primary, rgb(6, 62, 138));
    color: white;
    cursor: default;
    box-shadow: 0 0 12px rgba(13, 110, 253, 0.75);
}
@media (max-width: 991.98px) {
    .incarichi-filtro-form__grid {
        grid-template-columns: 1fr 1fr;
    }

    .incarichi-filtro-form__field--search,
    .incarichi-filtro-form__actions {
        grid-column: 1 / -1;
    }
}

@media (max-width: 575.98px) {
    .incarichi-filtro-form__grid {
        grid-template-columns: 1fr;
    }
}
</style>
