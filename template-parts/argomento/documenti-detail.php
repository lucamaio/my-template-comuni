<?php
global $argomento;

$documenti_page = isset($_GET['documenti_page']) ? max(1, intval($_GET['documenti_page'])) : 1;

$args = array(
    'post_type'      => dci_get_post_types_grouped('documenti-e-dati'),
    'posts_per_page' => 3,
    'paged'          => $documenti_page,
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

$documenti_query = new WP_Query($args);

if ($documenti_query->max_num_pages > 0 && $documenti_page > $documenti_query->max_num_pages) {
    $documenti_page = $documenti_query->max_num_pages;
    $args['paged'] = $documenti_page;
    $documenti_query = new WP_Query($args);
}
?>

<section id="documenti">
    <div class="pb-40 pt-40 pt-lg-80">
        <div class="container">
            <div class="row row-title">
                <div class="col-12">
                    <h3 class="u-grey-light border-bottom border-semi-dark pb-2 pb-lg-3 title-large-semi-bold">
                        Documenti
                    </h3>
                </div>
            </div>
            <div class="row mx-0">
                <div class="card-wrapper px-0 card-teaser-wrapper card-teaser-wrapper-equal card-teaser-block-3">
                    <?php if ($documenti_query->have_posts()) : ?>
                        <?php while ($documenti_query->have_posts()) : $documenti_query->the_post(); ?>
                            <?php
                            $description = dci_get_meta('descrizione_breve');
                            $tipi_documento = get_the_terms(get_the_ID(), 'tipi_documento');
                            $tipo_documento = (!empty($tipi_documento) && !is_wp_error($tipi_documento)) ? $tipi_documento[0] : null;
                            $title = get_the_title();

                            if (strlen($title) > 100) {
                                $title = substr($title, 0, 97) . '...';
                            }

                            if (preg_match('/[A-Z]{5,}/', $title)) {
                                $title = ucfirst(strtolower($title));
                            }

                            $description1 = $description;
                            if (preg_match('/[A-Z]{5,}/', $description1)) {
                                $description1 = ucfirst(strtolower($description1));
                            }
                            ?>
                            <div class="card card-teaser card-teaser-image card-flex no-after rounded shadow-sm border border-light mb-0">
                                <div class="card-image-wrapper with-read-more">
                                    <div class="card-body p-3">
                                        <div class="category-top">
                                            <?php if ($tipo_documento) : ?>
                                                <a class="title-xsmall-semi-bold fw-semibold text-decoration-none"
                                                    href="<?php echo esc_url(get_term_link($tipo_documento->term_id)); ?>"><?php echo esc_html($tipo_documento->name); ?></a>
                                            <?php endif; ?>
                                        </div>
                                        <h4 class="card-title text-paragraph-medium u-grey-light">
                                            <a href="<?php echo esc_url(get_permalink()); ?>" class="text-decoration-none">
                                                <?php echo esc_html($title); ?>
                                            </a>
                                        </h4>
                                        <p class="text-paragraph-card u-grey-light m-0"><?php echo esc_html($description1); ?></p>
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
            <?php if ($documenti_query->max_num_pages > 1) : ?>
                <div class="row my-4">
                    <nav class="pagination-wrapper justify-content-center col-12" aria-label="Navigazione pagine documenti">
                        <?php
                        $pagination_links = paginate_links(array(
                            'base'         => esc_url(add_query_arg('documenti_page', '%#%')),
                            'format'       => '',
                            'current'      => $documenti_page,
                            'total'        => $documenti_query->max_num_pages,
                            'type'         => 'array',
                            'show_all'     => false,
                            'end_size'     => 3,
                            'mid_size'     => 1,
                            'prev_next'    => true,
                            'prev_text'    => __('« '),
                            'next_text'    => __(' »'),
                            'add_args'     => false,
                            'add_fragment' => '#documenti',
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
                        onclick="location.href='<?php echo esc_url(dci_get_template_page_url('page-templates/documenti-e-dati.php')); ?>'">
                        Tutti i documenti
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>
<?php wp_reset_postdata(); ?>
