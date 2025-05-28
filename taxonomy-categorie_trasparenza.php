<?php

/**
 * Archivio Tassonomia trasparenza
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#custom-taxonomies
 * @package Design_Comuni_Italia
 */

 global $title, $description, $data_element, $elemento;

get_header();
$obj = get_queried_object();
$max_posts = isset($_GET['max_posts']) ? $_GET['max_posts'] : 3;
$load_posts = -1;
$query = isset($_GET['search']) ? dci_removeslashes($_GET['search']) : null;
$args = array(
    's' => $query,
    'posts_per_page' => $max_posts,
    'post_type'      => 'elemento_trasparenza',
    'tipi_cat_amm_trasp' => $obj->slug,
);
$the_query = new WP_Query($args);
$categoria = $the_query->posts;

$additional_filter = array(
    array(
        'taxonomy' => 'tipi_cat_amm_trasp',
        'field' => 'slug',
        'terms' => $obj->slug
    )
);
//var_dump($obj);
//$categoria = get_queried_object();
?>
<main>
    <?php
    $title = $obj->name;
    $description = $obj->description;
    $data_element = 'data-element="page-name"';
    //var_dump($title);
    get_template_part("template-parts/hero/hero");
    ?>
    <div class="bg-grey-card">
        <form role="search" id="search-form" method="get" class="search-form">
            <button type="submit" class="d-none"></button>
            <div class="container">
                <div class="row ">
                    <h2 class="visually-hidden">Esplora tutti i servizi</h2>
                    <div class="col-12 col-lg-8 pt-30 pt-lg-50 pb-lg-50">
                        <div class="cmp-input-search">
                            <div class="form-group autocomplete-wrapper mb-2 mb-lg-4">
                                <div class="input-group">
                                    <label for="autocomplete-two" class="visually-hidden">Cerca una parola chiave</label>
                                    <input type="search"
                                        class="autocomplete form-control"
                                        placeholder="Cerca una parola chiave"
                                        id="autocomplete-two"
                                        name="search"
                                        value="<?php echo $query; ?>"
                                        data-bs-autocomplete="[]">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="submit" id="button-3">
                                            Invio
                                        </button>
                                    </div>
                                    <span class="autocomplete-icon" aria-hidden="true">
                                        <svg class="icon icon-sm icon-primary" role="img" aria-labelledby="autocomplete-label">
                                            <use href="#it-search"></use>
                                        </svg>
                                    </span>
                                </div>
                            </div>
                            <p id="autocomplete-label" class="mb-4"><strong><?php echo $the_query->found_posts; ?> </strong>elementi trovati in ordine alfabetico</p>
                        </div>
                        <div class="row g-4" id="load-more">
                            <?php foreach ($categoria as $elemento) {
                                $load_card_type = "elemento_trasparenza";
                                get_template_part("template-parts/amministrazione-trasparente/card");
                            } ?>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</main>
<?php
get_template_part("template-parts/common/valuta-servizio");
get_template_part("template-parts/common/assistenza-contatti");
get_footer();
?>