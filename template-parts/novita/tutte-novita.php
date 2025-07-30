<?php
global $the_query, $load_posts, $load_card_type;

    $max_posts = isset($_GET['max_posts']) ? $_GET['max_posts'] : 9; 
    $load_posts = 9;
    $query = isset($_GET['search']) ? dci_removeslashes($_GET['search']) : null;

    $hide_news_old = dci_get_option('ck_hide_notizie_old', 'novita', 'false');
    $today = date('Y-m-d');

// Costruisci la query
$args = array(
    's' => $query,
    'post_type' => 'notizia',
    'orderby' => 'date',
    'order' => 'DESC',
    'posts_per_page' => $max_posts,
    'meta_query' => array(),
);

// Se il checkbox è true, filtra le notizie con data di scadenza >= oggi
if ($hide_news_old === 'true') {
    $args['meta_query'][] = array(
        'relation' => 'OR',
        array(
            'key' => 'data_scadenza',
            'value' => $today,
            'compare' => '>=',
            'type' => 'DATE'
        ),
        array(
            'key' => 'data_scadenza',
            'compare' => 'NOT EXISTS' // Per le notizie senza data di scadenza (null)
        )
    );
}

    $the_query = new WP_Query( $args );
    $posts = $the_query->posts;

  //  usort($posts, function($a, $b) {
  //      return dci_get_data_pubblicazione_ts("data_pubblicazione", '_dci_notizia_', $b->ID) - dci_get_data_pubblicazione_ts("data_pubblicazione", '_dci_notizia_', $a->ID);
   // });

    $posts = array_slice($posts, 0, $max_posts);

    $args = array(
        's'                 => $query,
        'posts_per_page'    => $max_posts,
        'post_type' => array('notizia')
    );

    $the_query = new WP_Query( $args );
?>


<div class="bg-grey-card py-5">
    <form role="search" id="search-form" method="get" class="search-form" action="#search-form">
        <div class="container">
            <h2 class="title-xxlarge mb-4">
                Esplora tutte le novità
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
                            <?php echo $the_query->found_posts; ?> notizie trovate in ordine alfabetico
                        </p>
                    </div>
                </div>
            </div>
            <div class="row g-4" id="load-more">
                <?php
                foreach ( $posts as $post ) {
                    $load_card_type = 'notizia';
                    get_template_part('template-parts/novita/cards-list');
                }
                wp_reset_postdata();
                ?>
            </div>
            <?php get_template_part("template-parts/search/more-results"); ?>
        </div>
    </form>
</div>
<?php wp_reset_query(); ?>