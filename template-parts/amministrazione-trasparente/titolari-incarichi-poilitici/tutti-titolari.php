<?php
 /*
    Note:

    1. Verificare se il portale è nostro

    In questo caso leggo direttamente le persone pubbliche del sito.

    Altrimenti, utilizzo la struttura personalizata simile a quella 'Titolari di incarichi di collaborazione o consulenza'
*/

$portale_esterno = dci_get_option("ck_portalesoloperusoesterno");
// var_dump($portale_esterno);

if($portale_esterno === 'false'){
    // Adesso, mi ricavo le UO politiche

    $parent_term = get_term_by('slug', 'struttura-politica', 'tipi_unita_organizzativa');

    $args = array(
        'post_type' => 'unita_organizzativa',
        'posts_per_page' => -1,
        'tax_query' => array(
            array(
                'taxonomy' => 'tipi_unita_organizzativa',
                'field'    => 'term_id',
                'terms'    => $parent_term ? $parent_term->term_id : 0,
                'include_children' => true,
            ),
        ),
    );

    $query = new WP_Query($args);

    // var_dump($query);


    ?>
    <div class="dci-at-wrap">
        <div class="row g-4">
           <div class="col-12 col-lg-8 pt-30 pt-lg-50 pb-lg-50">
                <?php
                if ($query->have_posts()) {
                    echo '<div class="row g-4">';

                    while ($query->have_posts()) {
                        $query->the_post();

                        // Escludi commissioni
                        $terms = wp_get_post_terms(get_the_ID(), 'tipi_unita_organizzativa');
                        $term_slugs = array_map(function($term) { return $term->slug; }, $terms);
                        if (in_array('commissione', $term_slugs, true)) {
                            continue;
                        }

                        $prefix = '_dci_unita_organizzativa_';
                        $description = dci_get_meta('descrizione_breve', $prefix, get_the_ID());
                        if (empty($description)) {
                            $description = get_the_excerpt();
                        }
                        ?>
                        <div class="col-12 col-md-6 col-lg-6">
                            <div class="card-wrapper border border-light rounded shadow-sm cmp-list-card-img cmp-list-card-img-hr h-100 dci-at-card-compact">
                                <div class="card no-after rounded h-100">
                                    <div class="row g-2 g-md-0 flex-md-column h-100">
                                        <div class="col-12 order-1 order-md-2 h-100">
                                            <div class="card-body card-img-none rounded-top h-100 d-flex flex-column">
                                                <span class="dci-at-card-icon mb-3" aria-hidden="true">
                                                    <svg class="icon icon-primary icon-sm">
                                                        <use href="#it-pa"></use>
                                                    </svg>
                                                </span>
                                                <a class="text-decoration-none" href="<?php the_permalink(); ?>">
                                                    <h3 class="h5 card-title mb-2"><?php the_title(); ?></h3>
                                                </a>
                                                <?php if (!empty($description)) { ?>
                                                    <p class="card-text mb-0"><?php echo esc_html($description); ?></p>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                    }

                    echo '</div>';
                    wp_reset_postdata();
                } else {
                    ?>
                    <p class="mb-0">Nessun titolare di incarico politico disponibile.</p>
                    <?php
                }
                ?>
            </div>
             <?php get_template_part("template-parts/amministrazione-trasparente/side-bar"); ?>
        </div>
    </div>
    <?php
}else{
    $link_consiglio = dci_get_option('link_consiglio_comunale', 'trasparenza');
    $link_sindaco = dci_get_option('link_sindaco', 'trasparenza');
    $link_giunta = dci_get_option('link_giunta_comunale', 'trasparenza');

    $static_offices = [];
    
    $static_offices = [
        [
            'title' => 'Consiglio Comunale',
            'description' => 'Il consiglio comunale è l’assemblea pubblica rappresentativa del Comune.',
            'link' => $link_consiglio,
        ],
        [
            'title' => 'Sindaco',
            'description' => 'Il Sindaco è l’organo rappresentativo dell’ente locale e garante dell’amministrazione.',
            'link' => $link_sindaco,
        ],
        [
            'title' => 'Giunta Comunale',
            'description' => 'La Giunta Comunale è organo di indirizzo esecutivo e gestione del Comune.',
            'link' => $link_giunta,
        ],
    ];
    ?>
    
 <div class="dci-at-wrap">
    <div class="row g-4">
        <div class="col-12 col-lg-8 pt-30 pt-lg-50 pb-lg-50">

            <?php if (!empty($static_offices)) { ?>
                
                <div class="row g-4">

                    <?php foreach ($static_offices as $office) { ?>
                        
                        <div class="col-12 col-md-6 col-lg-6">
                            <div class="card-wrapper border border-light rounded shadow-sm cmp-list-card-img cmp-list-card-img-hr h-100 dci-at-card-compact">
                                <div class="card no-after rounded h-100">
                                    <div class="row g-2 g-md-0 flex-md-column h-100">
                                        <div class="col-12 order-1 order-md-2 h-100">
                                            <div class="card-body card-img-none rounded-top h-100 d-flex flex-column">

                                                <span class="dci-at-card-icon mb-3" aria-hidden="true">
                                                    <svg class="icon icon-primary icon-sm">
                                                        <use href="#it-pa"></use>
                                                    </svg>
                                                </span>

                                                <a class="text-decoration-none" href="<?php echo esc_url($office['link']); ?>">
                                                    <h3 class="h5 card-title mb-2">
                                                        <?php echo esc_html($office['title']); ?>
                                                    </h3>
                                                </a>

                                                <p class="card-text mb-0">
                                                    <?php echo esc_html($office['description']); ?>
                                                </p>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php } ?>

                </div> <!-- ✅ CHIUSURA ROW -->

            <?php } ?>

        </div>

        <?php get_template_part("template-parts/amministrazione-trasparente/side-bar"); ?>

    </div>
</div>



<?php } ?>