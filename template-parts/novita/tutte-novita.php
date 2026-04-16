<?php
global $the_query, $load_card_type;

// Recupero ricerca
$query = isset($_GET['search']) ? dci_removeslashes($_GET['search']) : null;

// PAGINAZIONE
// Se sto cercando → forza pagina 1
$paged_from_query = get_query_var('paged');
$paged_from_get = isset($_GET['paged']) ? absint($_GET['paged']) : 0;
$paged = max(1, (int) ($paged_from_query ? $paged_from_query : $paged_from_get));

// QUERY
$args = array(
    's'              => $query,
    'posts_per_page' => 9,
    'post_type'      => array('notizia'),
    'post_status'    => 'publish',
    'ignore_sticky_posts' => true,
    'paged'          => $paged
);

$the_query = new WP_Query($args);
?>

<div class="bg-grey-card py-5">
    <form role="search" id="search-form" method="get" class="search-form" action="#search-form">
        <div class="container">

            <h2 class="title-xxlarge mb-4">
                Esplora tutte le novità
            </h2>

            <div class="cmp-input-search">
                <div class="form-group autocomplete-wrapper mb-0">
                    <div class="input-group">

                        <label for="autocomplete-two" class="visually-hidden">Cerca</label>

                        <input type="search"
                               class="autocomplete form-control"
                               placeholder="Cerca per parola chiave"
                               id="autocomplete-two"
                               name="search"
                               value="<?php echo esc_attr($query); ?>" />

                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit">
                                Invio
                            </button>
                        </div>

                        <span class="autocomplete-icon" aria-hidden="true">
                            <svg class="icon icon-sm icon-primary">
                                <use href="#it-search"></use>
                            </svg>
                        </span>
                    </div>

                    <p class="u-grey-light text-paragraph-card mt-2 mb-30 mt-lg-3 mb-lg-40">
                        <?php echo $the_query->found_posts; ?> notizie trovate
                    </p>
                </div>
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
