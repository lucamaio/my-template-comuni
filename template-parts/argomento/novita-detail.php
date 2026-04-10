<?php
global $argomento;

// PAGINAZIONE PERSONALIZZATA
$novita_page = isset($_GET['novita_page']) ? max(1, intval($_GET['novita_page'])) : 1;

// QUERY
$args = array(
    'post_type'      => array('notizia', 'evento'),
    'posts_per_page' => 3,
    'paged'          => $novita_page,
    'tax_query'      => array(
        array(
            'taxonomy' => 'argomenti',
            'field'    => 'slug',
            'terms'    => $argomento->slug,
        ),
    ),
);

$the_query = new WP_Query($args);

// Sicurezza: se qualcuno apre una pagina oltre il massimo, torno all'ultima valida
if ($the_query->max_num_pages > 0 && $novita_page > $the_query->max_num_pages) {
    $novita_page = $the_query->max_num_pages;

    $args['paged'] = $novita_page;
    $the_query = new WP_Query($args);
}
?>

<section id="novita">
    <div class="bg-grey-card pt-40 pt-md-100 pb-50">
        <div class="container">

            <div class="row row-title">
                <div class="col-12">
                    <h3 class="u-grey-light border-bottom border-semi-dark pb-2 pb-lg-3 mt-lg-3 title-large-semi-bold">
                        Novità
                    </h3>
                </div>
            </div>

            <div class="row g-4 pt-4 mt-lg-2 pb-lg-4">

                <?php if ($the_query->have_posts()) : ?>
                    <?php while ($the_query->have_posts()) : $the_query->the_post(); ?>

                        <?php
                        $post_id       = get_the_ID();
                        $post_type     = get_post_type($post_id);
                        ?>

                        <?php if ($post_type === 'evento') : ?>

                            <?php get_template_part('template-parts/evento/card-full'); ?>

                        <?php else : ?>

                            <?php get_template_part('template-parts/novita/cards-list'); ?>

                        <?php endif; ?>

                    <?php endwhile; ?>
                <?php else : ?>
                    <div class="col-12">
                        <div class="alert alert-info">
                            Al momento non sono presenti contenuti disponibili.
                        </div>
                    </div>
                <?php endif; ?>

            </div>

            <?php if ($the_query->max_num_pages > 1) : ?>
                <div class="row my-4">
                    <nav class="pagination-wrapper justify-content-center col-12" aria-label="Navigazione pagine">
                        <?php
                        $pagination_links = paginate_links(array(
                            'base'      => esc_url(add_query_arg('novita_page', '%#%')),
                            'format'    => '',
                            'current'   => $novita_page,
                            'total'     => $the_query->max_num_pages,
                            'type'      => 'array',
                            'show_all'  => false,
                            'end_size'  => 3,
                            'mid_size'  => 1,
                            'prev_next' => true,
                            'prev_text' => __('« '),
                            'next_text' => __(' »'),
                            'add_args'  => false,
                            'add_fragment' => '#novita',
                        ));

                        if ($pagination_links) :
                        ?>
                            <div class="pagination">
                                <ul class="pagination">
                                    <?php foreach ($pagination_links as $link) : ?>
                                        <li class="page-item<?php echo strpos($link, 'current') !== false ? ' active' : ''; ?>">
                                            <?php echo str_replace('page-numbers', 'page-link', $link); ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </nav>
                </div>
            <?php endif; ?>

            <div class="row mt-lg-2">
                <div class="col-12 col-lg-3 offset-lg-9">
                    <button type="button"
                            class="btn btn-primary text-button w-100"
                            onclick="location.href='<?php echo esc_url(dci_get_template_page_url('page-templates/novita.php')); ?>'">
                        Tutte le novità
                    </button>
                </div>
            </div>

        </div>
    </div>
</section>

<?php wp_reset_postdata(); ?>
