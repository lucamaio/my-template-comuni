<?php
global $the_query, $load_posts, $load_card_type;

$per_page = 6;
$load_posts = $per_page;
$query = isset($_GET['search']) ? dci_removeslashes($_GET['search']) : null;
$paged_from_query = max((int) get_query_var('paged'), (int) get_query_var('page'));
$paged_from_get = isset($_GET['luoghi_page']) ? absint($_GET['luoghi_page']) : 0;
if ($paged_from_get < 1 && isset($_GET['paged'])) {
    $paged_from_get = absint($_GET['paged']);
}
$paged = max(1, (int) ($paged_from_query ? $paged_from_query : $paged_from_get));

$args = array(
    's' => $query,
    'posts_per_page' => $per_page,
    'post_type'      => 'luogo',
    'post_status'    => 'publish',
    'ignore_sticky_posts' => true,
    'orderby'        => 'post_title',
    'order'          => 'ASC',
    'paged'          => $paged,
);
$the_query = new WP_Query($args);

?>


<div class="bg-grey-card py-5">
    <form role="search" id="search-form" method="get" class="search-form">
        <button type="submit" class="d-none"></button>
        <div class="container">
            <h2 class="title-xxlarge mb-4">
                Esplora tutti i luoghi
            </h2>
            <div>
                <div class="cmp-input-search">
                    <div class="form-group autocomplete-wrapper mb-0">
                        <div class="input-group">
                            <label for="autocomplete-two" class="visually-hidden">Cerca</label>
                            <input type="search" class="autocomplete form-control" placeholder="Cerca per parola chiave"
                                id="autocomplete-two" name="search" value="<?php echo $query; ?>"
                                data-bs-autocomplete="[]" />
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit" id="button-3">
                                    Invio
                                </button>
                            </div>
                            <span class="autocomplete-icon" aria-hidden="true"><svg class="icon icon-sm icon-primary"
                                    role="img" aria-labelledby="autocomplete-label">
                                    <use href="#it-search"></use>
                                </svg>
                            </span>
                        </div>
                        <p id="autocomplete-label" class="u-grey-light text-paragraph-card mt-2 mb-30 mt-lg-3 mb-lg-40">
                            <?php echo $the_query->found_posts; ?> luoghi trovati in ordine alfabetico
                        </p>
                    </div>
                </div>
            </div>
            <div class="row g-4" id="load-more">
                <?php
                if ($the_query->have_posts()) {
                    while ($the_query->have_posts()) {
                        $the_query->the_post();
                        $load_card_type = 'luogo';
                        get_template_part('template-parts/luogo/card-full');
                    }
                } else {
                    get_template_part('template-parts/content', 'none');
                }
                ?>
            </div>
            <?php if ($the_query->max_num_pages > 1) : ?>
            <div class="row my-4">
                <nav class="pagination-wrapper justify-content-center col-12" aria-label="Navigazione pagine luoghi">
                    <?php
                    $pagination_links = paginate_links(array(
                        'base'         => esc_url(add_query_arg('luoghi_page', '%#%')),
                        'format'       => '',
                        'current'      => $paged,
                        'total'        => $the_query->max_num_pages,
                        'type'         => 'array',
                        'show_all'     => false,
                        'end_size'     => 3,
                        'mid_size'     => 1,
                        'prev_next'    => true,
                        'prev_text'    => __('« '),
                        'next_text'    => __(' »'),
                        'add_fragment' => '#search-form',
                    ));
                    if ($pagination_links) :
                    ?>
                    <div class="pagination">
                        <ul class="pagination">
                            <?php foreach ($pagination_links as $link) : ?>
                                <li class="page-item <?php echo strpos($link, 'current') !== false ? 'active' : ''; ?>">
                                    <?php echo str_replace('page-numbers', 'page-link', $link); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </form>
</div>
<?php wp_reset_postdata(); ?>
