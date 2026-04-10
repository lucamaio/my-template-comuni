<?php
global $argomento;

$servizi_page = isset($_GET['servizi_page']) ? max(1, intval($_GET['servizi_page'])) : 1;

$args = array(
    'post_type'      => dci_get_post_types_grouped('servizi'),
    'posts_per_page' => 3,
    'paged'          => $servizi_page,
    'tax_query'      => array(
        array(
            'taxonomy' => 'argomenti',
            'field'    => 'slug',
            'terms'    => $argomento->slug,
        ),
    ),
    'orderby'        => 'date',
    'order'          => 'DESC',
);

$servizi_query = new WP_Query($args);

if ($servizi_query->max_num_pages > 0 && $servizi_page > $servizi_query->max_num_pages) {
    $servizi_page = $servizi_query->max_num_pages;
    $args['paged'] = $servizi_page;
    $servizi_query = new WP_Query($args);
}
?>

<section id="servizi">
    <div class="pb-40 pt-40 pt-lg-80">
        <div class="container">
            <div class="row row-title">
                <div class="col-12">
                    <h3 class="u-grey-light border-bottom border-semi-dark pb-2 pb-lg-3 title-large-semi-bold">
                        Servizi
                    </h3>
                </div>
            </div>
            <div class="row mx-0">
                <div class="card-wrapper px-0 card-teaser-wrapper card-teaser-wrapper-equal card-teaser-block-3">
                    <?php if ($servizi_query->have_posts()) : ?>
                        <?php while ($servizi_query->have_posts()) : $servizi_query->the_post(); ?>
                            <?php
                            $description = dci_get_meta('descrizione_breve');
                            $categorie_servizio = get_the_terms(get_the_ID(), 'categorie_servizio');
                            $categoria_servizio = (!empty($categorie_servizio) && !is_wp_error($categorie_servizio)) ? $categorie_servizio[0] : null;
                            ?>
                            <div class="card card-teaser card-teaser-image card-flex no-after rounded shadow-sm border border-light mb-0">
                                <div class="card-image-wrapper with-read-more">
                                    <div class="card-body p-3">
                                        <div class="category-top">
                                            <?php if ($categoria_servizio) : ?>
                                                <a class="title-xsmall-semi-bold fw-semibold text-decoration-none"
                                                    href="<?php echo esc_url(get_term_link($categoria_servizio->term_id)); ?>"><?php echo esc_html($categoria_servizio->name); ?></a>
                                            <?php endif; ?>
                                        </div>
                                        <h4 class="card-title text-paragraph-medium u-grey-light">
                                            <a href="<?php echo esc_url(get_permalink()); ?>" class="text-decoration-none"><?php the_title(); ?></a>
                                        </h4>
                                        <p class="text-paragraph-card u-grey-light m-0"><?php echo esc_html($description); ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <div class="col-12">
                            <div class="alert alert-info">
                                Al momento non sono presenti contenuti disponibili.
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php if ($servizi_query->max_num_pages > 1) : ?>
                <div class="row my-4">
                    <nav class="pagination-wrapper justify-content-center col-12" aria-label="Navigazione pagine servizi">
                        <?php
                        $pagination_links = paginate_links(array(
                            'base'         => esc_url(add_query_arg('servizi_page', '%#%')),
                            'format'       => '',
                            'current'      => $servizi_page,
                            'total'        => $servizi_query->max_num_pages,
                            'type'         => 'array',
                            'show_all'     => false,
                            'end_size'     => 3,
                            'mid_size'     => 1,
                            'prev_next'    => true,
                            'prev_text'    => __('« '),
                            'next_text'    => __(' »'),
                            'add_args'     => false,
                            'add_fragment' => '#servizi',
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
            <div class="row mt-4">
                <div class="col-12 col-lg-3 offset-lg-9">
                    <button type="button" class="btn btn-primary text-button w-100"
                        onclick="location.href='<?php echo esc_url(dci_get_template_page_url('page-templates/servizi.php')); ?>'">
                        Tutti i servizi
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>
<?php wp_reset_postdata(); ?>
