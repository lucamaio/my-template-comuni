<?php
global $the_query, $elemento, $load_card_type;

$current_term = get_queried_object();
$paged = max(
    1,
    (int) get_query_var('paged'),
    (int) get_query_var('page'),
    isset($_GET['paged']) ? absint($_GET['paged']) : 0
);
$max_posts = dci_sanitize_posts_per_page(isset($_GET['max_posts']) ? $_GET['max_posts'] : 10, 10, 50);
$query = isset($_GET['search']) ? dci_removeslashes($_GET['search']) : null;
$order = isset($_GET['order_type']) ? sanitize_key($_GET['order_type']) : 'data_desc';

$args = array(
    'post_type' => 'elemento_trasparenza',
    'post_status' => 'publish',
    'posts_per_page' => $max_posts,
    'paged' => $paged,
    'ignore_sticky_posts' => true,
);

if ($current_term instanceof WP_Term && $current_term->taxonomy === 'tipi_cat_amm_trasp') {
    $args['tax_query'] = array(
        array(
            'taxonomy' => 'tipi_cat_amm_trasp',
            'field' => 'term_id',
            'terms' => array((int) $current_term->term_id),
            'include_children' => false,
        ),
    );
}

if ($query !== null && $query !== '') {
    $args['s'] = $query;
}

if ($order === 'alfabetico_asc' || $order === 'alfabetico_desc') {
    $args['orderby'] = 'title';
    $args['order'] = $order === 'alfabetico_desc' ? 'DESC' : 'ASC';
} else {
    $args['orderby'] = 'date';
    $args['order'] = $order === 'data_asc' ? 'ASC' : 'DESC';
}

$the_query = new WP_Query($args);
?>

<?php if ($the_query->found_posts != 0) { ?>
    <?php $categoria = $the_query->posts; ?>
    <div class="row g-4" id="load-more">
        <?php foreach ($categoria as $elemento) {
            $load_card_type = "elemento_trasparenza";
            get_template_part("template-parts/amministrazione-trasparente/card");
        } ?>
    </div>
<?php } else { ?>
    <div class="dci-at-empty text-decoration-none" role="status" aria-live="polite">
        <span class="dci-at-empty__icon" aria-hidden="true">
            <svg class="icon icon-sm">
                <use href="#it-info-circle"></use>
            </svg>
        </span>
        <div class="dci-at-empty__content">
            <p class="dci-at-empty__title text-decoration-none">Nessun contenuto disponibile</p>
            <p class="dci-at-empty__text text-decoration-none">Non ci sono elementi o post da mostrare con i filtri attuali. Prova a cambiare ricerca o ordinamento.</p>
        </div>
    </div>
<?php } ?>

<?php
$pagination_args = array();
if ($query !== null && $query !== '') {
    $pagination_args['search'] = $query;
}
if (!empty($order)) {
    $pagination_args['order_type'] = $order;
}
if (!empty($max_posts)) {
    $pagination_args['max_posts'] = (int) $max_posts;
}

$pagination_base = str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999)));
$pagination_format = '?paged=%#%';
if ($current_term instanceof WP_Term && $current_term->taxonomy === 'tipi_cat_amm_trasp') {
    $term_link = get_term_link($current_term);
    if (!is_wp_error($term_link)) {
        $pagination_base = trailingslashit($term_link) . 'page/%#%/';
        $pagination_format = '';
    }
}

$pages = paginate_links(array(
    'base' => esc_url($pagination_base),
    'format' => $pagination_format,
    'current' => $paged,
    'total' => max(1, (int) $the_query->max_num_pages),
    'type' => 'array',
    'show_all' => false,
    'end_size' => 3,
    'mid_size' => 1,
    'prev_next' => true,
    'prev_text' => __('« '),
    'next_text' => __(' »'),
    'add_args' => $pagination_args,
    'add_fragment' => ''
));

$pagination_markup = '';
if (is_array($pages)) {
    $pagination_markup = '<div class="pagination"><ul class="pagination">';
    foreach ($pages as $page_link) {
        $pagination_markup .= '<li class="page-item' . (strpos($page_link, 'current') !== false ? ' active' : '') . '">' . str_replace('page-numbers', 'page-link', $page_link) . '</li>';
    }
    $pagination_markup .= '</ul></div>';
}
?>
<?php if ($pagination_markup !== '') { ?>
<div class="row my-4">
    <div class="col-12 d-flex">
        <nav class="pagination-wrapper" aria-label="Navigazione pagine">
            <?php echo $pagination_markup; ?>
        </nav>
    </div>
</div>
<?php } ?>

<?php wp_reset_postdata(); ?>
