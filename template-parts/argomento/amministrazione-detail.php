<?php
global $argomento;

$amministrazione_page = isset($_GET['amministrazione_page']) ? max(1, intval($_GET['amministrazione_page'])) : 1;

$args = array(
    'post_type'      => dci_get_post_types_grouped('amministrazione'),
    'posts_per_page' => 3,
    'paged'          => $amministrazione_page,
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

$amministrazione_query = new WP_Query($args);

if ($amministrazione_query->max_num_pages > 0 && $amministrazione_page > $amministrazione_query->max_num_pages) {
    $amministrazione_page = $amministrazione_query->max_num_pages;
    $args['paged'] = $amministrazione_page;
    $amministrazione_query = new WP_Query($args);
}
?>
<section id="amministrazione">
    <div class="pb-40 pt-40 pt-lg-80">
        <div class="container">
            <div class="row row-title">
                <div class="col-12">
                    <h3 class="u-grey-light border-bottom border-semi-dark pb-2 pb-lg-3 title-large-semi-bold">
                        Amministrazione
                    </h3>
                </div>
            </div>
            <div class="row mx-0">
                <div class="card-wrapper px-0 card-teaser-wrapper card-teaser-wrapper-equal card-teaser-block-3">
                    <?php if ($amministrazione_query->have_posts()) : ?>
                        <?php while ($amministrazione_query->have_posts()) : $amministrazione_query->the_post(); ?>
                            <?php
                            $description = dci_get_meta('descrizione_breve');
                            $img = dci_get_meta('immagine');
                            $tipi_amministrazione = get_the_terms(get_the_ID(), 'tipi_unita_organizzativa');
                            $tipo_amministrazione = (!empty($tipi_amministrazione) && !is_wp_error($tipi_amministrazione)) ? $tipi_amministrazione[0] : null;
                            ?>
                            <div class="card card-teaser card-teaser-image card-flex no-after rounded shadow-sm border border-light mb-0">
                                <div class="card-image-wrapper with-read-more">
                                    <div class="card-body p-3">
                                        <div class="category-top">
                                            <?php if ($tipo_amministrazione) : ?>
                                                <a class="title-xsmall-semi-bold fw-semibold text-decoration-none"
                                                    href="<?php echo esc_url(get_term_link($tipo_amministrazione->term_id)); ?>"><?php echo esc_html($tipo_amministrazione->name); ?></a>
                                            <?php endif; ?>
                                        </div>
                                        <h4 class="card-title text-paragraph-medium u-grey-light">
                                            <a href="<?php echo esc_url(get_permalink()); ?>" class="text-decoration-none"><?php the_title(); ?></a>
                                        </h4>
                                        <p class="text-paragraph-card u-grey-light m-0"><?php echo esc_html($description); ?></p>
                                    </div>
                                    <?php if ($img) : ?>
                                        <div class="card-image card-image-rounded">
                                            <?php dci_get_img($img); ?>
                                        </div>
                                    <?php endif; ?>
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
            <?php if ($amministrazione_query->max_num_pages > 1) : ?>
                <div class="row my-4">
                    <nav class="pagination-wrapper justify-content-center col-12" aria-label="Navigazione pagine amministrazione">
                        <?php
                        $pagination_links = paginate_links(array(
                            'base'         => esc_url(add_query_arg('amministrazione_page', '%#%')),
                            'format'       => '',
                            'current'      => $amministrazione_page,
                            'total'        => $amministrazione_query->max_num_pages,
                            'type'         => 'array',
                            'show_all'     => false,
                            'end_size'     => 3,
                            'mid_size'     => 1,
                            'prev_next'    => true,
                            'prev_text'    => __('« '),
                            'next_text'    => __(' »'),
                            'add_args'     => false,
                            'add_fragment' => '#amministrazione',
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
                        onclick="location.href='<?php echo esc_url(dci_get_template_page_url('page-templates/amministrazione.php')); ?>'">
                        Tutta l'amministrazione
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>
<?php wp_reset_postdata(); ?>
