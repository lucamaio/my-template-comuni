<?php
global $wpdb, $sezione;

$max_posts = dci_sanitize_posts_per_page(isset($_GET['max_posts']) ? $_GET['max_posts'] : 10, 10, 50);
$main_search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$paged = max(1, (int) get_query_var('paged'), (int) get_query_var('page'));
if ($paged < 2) {
    $paged = max(
        isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1,
        isset($_GET['page']) ? max(1, absint($_GET['page'])) : 1,
        isset($_GET['incarichi_dirig_page']) ? max(1, absint($_GET['incarichi_dirig_page'])) : 1,
        isset($_GET['incarico_dirig']) ? max(1, absint($_GET['incarico_dirig'])) : 1
    );
}
$selected_year = isset($_GET['filter_year']) ? intval($_GET['filter_year']) : 0;
$prefix = '_dci_incarico_dirigenziale_';
$current_section_key = '';

if (!empty($sezione)) {
    $section_value = trim((string) $sezione);
    $section_labels = function_exists('dci_incarico_dirigenziale_sections')
        ? dci_incarico_dirigenziale_sections()
        : array();

    if (array_key_exists($section_value, $section_labels)) {
        $current_section_key = $section_value;
    } else {
        $matched_section_key = array_search($section_value, $section_labels, true);
        if (false !== $matched_section_key) {
            $current_section_key = (string) $matched_section_key;
        }
    }
}

// Anni disponibili
$years = $wpdb->get_col("
    SELECT DISTINCT YEAR(post_date)
    FROM {$wpdb->posts}
    WHERE post_type = 'incarico_dirig'
      AND post_status = 'publish'
    ORDER BY post_date DESC
");


$args = array(
    'post_type'       => 'incarico_dirig',
    'posts_per_page'  => $max_posts,
    'orderby'        => 'meta_value_num',
    'order'          => 'DESC',
    'paged'              => $paged,
    's'               => $main_search_query, // Per la ricerca generica su titolo/contenuto
);

if ($current_section_key !== '') {
    $args['meta_query'] = array(
        array(
            'key'     => $prefix . 'sezione_pubblicazione',
            'value'   => $current_section_key,
            'compare' => '=',
        ),
    );
}

if (!empty($main_search_query)) {
    $args['s'] = $main_search_query;
}

if ($selected_year > 0) {
    $args['date_query'] = [
        [
            'year' => $selected_year,
        ]
    ];
}

// Query personalizzata
$the_query = new WP_Query($args);

// SEARCH BAR
?>
<form method="get" class="incarichi-filtro-form">
    <div class="incarichi-filtro-form__head">
        <h3 class="incarichi-filtro-form__title text-decoration-none">Filtra i titolari</h3>
        <p class="incarichi-filtro-form__intro text-decoration-none">Usa i campi qui sotto per trovare più velocemente i contenuti pubblicati.</p>
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
        <?php foreach ([5, 10, 20, 50] as $num) : ?>
            <option value="<?php echo $num; ?>" <?php selected($max_posts, $num); ?>><?php echo $num; ?></option>
        <?php endforeach; ?>
    </select>
    </div>

    <div class="btn-wrapper incarichi-filtro-form__actions">
        <button type="submit" class="btn btn-primary">Filtra</button>
    </div>
    </div>
</form>

<?php
if (function_exists('dci_render_trasparenza_not_applicable_notice')) {
    dci_render_trasparenza_not_applicable_notice();
}
?>

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

<?php if ($the_query->have_posts()){
    while ($the_query->have_posts()){
        $the_query->the_post();
        get_template_part('template-parts/amministrazione-trasparente/incarichi-dirigenziali/card');
    }
    wp_reset_postdata();?>
        <div class="row my-4">
        <nav class="pagination-wrapper justify-content-center col-12" aria-label="Navigazione pagine">
            <?php
            get_template_part(
                'template-parts/amministrazione-trasparente/paginazione-personalizzata',
                null,
                [
                    'query'    => $the_query,
                    'current'  => $paged,
                    'page_arg' => 'incarichi_dirig_page',
                ]
            );
            ?>
        </nav>
    </div>
<?php } else{?>
    <div class="alert alert-info text-center" role="alert">
        Nessun titolare di incarichi di collaborazione o consulenza trovato.
    </div>
<?php } ?>
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
.incarichi-filtro-form__head { margin-bottom: 1rem; }
.incarichi-filtro-form__title { margin-bottom: .35rem; font-size: 1.2rem; }
.incarichi-filtro-form__intro { margin-bottom: 0; }
.incarichi-filtro-form__grid { display: grid; grid-template-columns: minmax(220px, 2fr) repeat(2, minmax(170px, 1fr)) auto; gap: 1rem; align-items: end; }
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
    .incarichi-filtro-form__grid { grid-template-columns: 1fr 1fr; }
    .incarichi-filtro-form__field--search,
    .incarichi-filtro-form__actions { grid-column: 1 / -1; }
}
@media (max-width: 575.98px) {
    .incarichi-filtro-form__grid { grid-template-columns: 1fr; }
}
</style>
