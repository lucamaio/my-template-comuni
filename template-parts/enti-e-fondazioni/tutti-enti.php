<?php
global $the_query, $load_posts, $load_card_type;

$max_posts = isset($_GET['max_posts']) ? $_GET['max_posts'] : 1000000;
$load_posts = 9;

$query = isset($_GET['search']) ? dci_removeslashes($_GET['search']) : null;

/**
 * 1) recupero termine padre "altra-struttura"
 */
$parent_term = get_term_by('slug', 'altra-struttura', 'tipi_unita_organizzativa');

/**
 * 2) WP_Query con include_children = true
 */
$args = array(
    's'              => $query,
    'posts_per_page' => $max_posts,
    'post_type'      => 'unita_organizzativa',
    'orderby'        => 'post_title',
    'order'          => 'ASC',
    'tax_query'      => array(
        array(
            'taxonomy'         => 'tipi_unita_organizzativa',
            'field'            => 'term_id',
            'terms'            => $parent_term->term_id,
            'include_children' => true,   // 👈 prende ente, fondazione, ecc
            'operator'         => 'IN',
        ),
    ),
);

$the_query = new WP_Query($args);

/**
 * 3) estrai direttamente i post (niente array_filter)
 */
$posts = $the_query->posts;
?>



<div class="bg-grey-card py-5">
    <form role="search" id="search-form" method="get" class="search-form">
    <button type="submit" class="d-none"></button>
        <div class="container">
            <h2 class="title-xxlarge mb-4">
                Esplora gli enti e le fondazioni
            </h2>
            <div>
                <div class="cmp-input-search">
                    <div class="form-group autocomplete-wrapper mb-0">
                        <div class="input-group">
                        <label for="autocomplete-two" class="visually-hidden"
                        >Cerca</label
                        >
                        <input
                        type="search"
                        class="autocomplete form-control"
                        placeholder="Cerca per parola chiave"
                        id="autocomplete-two"
                        name="search"
                        value="<?php echo $query; ?>"
                        data-bs-autocomplete="[]"
                        />
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit" id="button-3">
                                Invio
                            </button>
                        </div>
                        <span class="autocomplete-icon" aria-hidden="true"
                        ><svg
                            class="icon icon-sm icon-primary"
                            role="img"
                            aria-labelledby="autocomplete-label"
                        >
                            <use
                            href="#it-search"
                            ></use></svg></span>
                        </div>
                        <p
                        id="autocomplete-label"
                        class="u-grey-light text-paragraph-card mt-2 mb-4 mt-lg-3 mb-lg-40"
                        >
                        <?php echo count($posts); ?> enti trovati in ordine alfabetico
                        </p>
                    </div>
                </div>
            </div>
            <div class="row g-4" id="load-more">
                <?php 
                    $load_card_type = 'enti-e-fondazioni';
                    foreach ($posts as $post) {get_template_part('template-parts/enti-e-fondazioni/cards-list');
                }?>
            </div>
            <?php get_template_part("template-parts/search/more-results"); ?>
        </div>
    </form>
</div>