<?php
global $the_query, $load_card_type, $wpdb;

// Recupero filtri
$query = isset($_GET['search']) ? dci_removeslashes($_GET['search']) : null;
$selected_category = isset($_GET['categoria']) ? absint($_GET['categoria']) : 0;
$selected_month = isset($_GET['mese']) ? absint($_GET['mese']) : 0;
$selected_year = isset($_GET['anno']) ? absint($_GET['anno']) : 0;

// PAGINAZIONE
$paged_from_query = get_query_var('paged');
$paged_from_get = isset($_GET['paged']) ? absint($_GET['paged']) : 0;
$paged = max(1, (int) ($paged_from_query ? $paged_from_query : $paged_from_get));

$notizia_terms = get_terms(array(
    'taxonomy' => 'tipi_notizia',
    'hide_empty' => true,
    'orderby' => 'name',
    'order' => 'ASC',
));

$available_years = $wpdb->get_col($wpdb->prepare(
    "SELECT DISTINCT YEAR(post_date) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = %s ORDER BY post_date DESC",
    'notizia',
    'publish'
));

$form_action = dci_get_template_page_url('page-templates/novita.php') ?: get_post_type_archive_link('notizia') ?: home_url('/novita/');
$reset_url = $form_action;

// QUERY
$args = array(
    's' => $query,
    'posts_per_page' => 9,
    'post_type' => array('notizia'),
    'post_status' => 'publish',
    'ignore_sticky_posts' => true,
    'paged' => $paged,
);

if ($selected_category > 0) {
    $args['tax_query'] = array(
        array(
            'taxonomy' => 'tipi_notizia',
            'field' => 'term_id',
            'terms' => array($selected_category),
        ),
    );
}

$date_query = array();
if ($selected_month >= 1 && $selected_month <= 12) {
    $date_query['month'] = $selected_month;
}
if ($selected_year > 0) {
    $date_query['year'] = $selected_year;
}
if (!empty($date_query)) {
    $args['date_query'] = array($date_query);
}

$the_query = new WP_Query($args);
?>

<div class="bg-grey-card py-5">
    <form role="search" id="search-form" method="get" class="search-form" action="<?php echo esc_url($form_action); ?>#search-form">
        <div class="container">

            <h2 class="title-xxlarge mb-4">
                Esplora tutte le novità
            </h2>

            <div class="cmp-input-search dci-novita-search">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-lg-8">
                        <div class="form-group autocomplete-wrapper mb-0">
                            <div class="input-group">
                                <label for="autocomplete-two" class="visually-hidden">Cerca</label>
                                <input type="search"
                                       class="autocomplete form-control"
                                       placeholder="Cerca per parola chiave"
                                       id="autocomplete-two"
                                       name="search"
                                       value="<?php echo esc_attr($query); ?>" />
                                <span class="autocomplete-icon" aria-hidden="true">
                                    <svg class="icon icon-sm icon-primary">
                                        <use href="#it-search"></use>
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-4">
                        <div class="form-group mb-0">
                            <label for="categoria-notizia" class="visually-hidden">Categoria</label>
                            <select id="categoria-notizia" name="categoria" class="form-control">
                                <option value="0">Seleziona categoria</option>
                                <?php if (!is_wp_error($notizia_terms)) { ?>
                                    <?php foreach ($notizia_terms as $term) { ?>
                                        <option value="<?php echo esc_attr($term->term_id); ?>" <?php selected($selected_category, (int) $term->term_id); ?>>
                                            <?php echo esc_html($term->name); ?>
                                        </option>
                                    <?php } ?>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 col-lg-3">
                        <div class="form-group mb-0">
                            <label for="mese-notizia" class="visually-hidden">Mese</label>
                            <select id="mese-notizia" name="mese" class="form-control">
                                <option value="0">Seleziona mese</option>
                                <?php for ($month = 1; $month <= 12; $month++) { ?>
                                    <option value="<?php echo esc_attr($month); ?>" <?php selected($selected_month, $month); ?>>
                                        <?php echo esc_html(ucfirst(date_i18n('F', mktime(0, 0, 0, $month, 1)))); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 col-lg-3">
                        <div class="form-group mb-0">
                            <label for="anno-notizia" class="visually-hidden">Anno</label>
                            <select id="anno-notizia" name="anno" class="form-control">
                                <option value="0">Seleziona anno</option>
                                <?php foreach ($available_years as $year) { ?>
                                    <option value="<?php echo esc_attr($year); ?>" <?php selected($selected_year, (int) $year); ?>>
                                        <?php echo esc_html($year); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 col-lg-3 d-grid">
                        <button class="btn btn-primary" type="submit">Ricerca</button>
                    </div>
                    <div class="col-12 col-md-4 col-lg-3 d-grid">
                        <a class="btn btn-secondary" href="<?php echo esc_url($reset_url); ?>#search-form">Reimposta</a>
                    </div>
                </div>

                <p class="u-grey-light text-paragraph-card mt-4 mb-30 mt-lg-4 mb-lg-40">
                    <?php echo $the_query->found_posts; ?> notizie trovate
                </p>
            </div>

            <?php if ($the_query->have_posts()) : ?>

                <div class="row g-4">
                    <?php while ($the_query->have_posts()) : $the_query->the_post(); ?>

                        <?php
                        $load_card_type = 'notizia';
                        get_template_part('template-parts/novita/cards-list');
                        ?>

                    <?php endwhile; ?>
                </div>

                <!-- PAGINAZIONE -->
                <div class="row my-4">
                    <nav class="pagination-wrapper justify-content-center col-12" aria-label="Navigazione pagine">
                        <?php echo dci_bootstrap_pagination($the_query, false); ?>
                    </nav>
                </div>

            <?php else : ?>

                <?php get_template_part('template-parts/content', 'none'); ?>

            <?php endif; ?>

        </div>
    </form>
</div>

<?php wp_reset_postdata(); ?>
