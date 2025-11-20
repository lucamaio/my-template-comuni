<?php
global $posts, $the_query, $load_posts, $servizio, $load_card_type, $should_have_grey_background;
$max_posts = isset($_GET['max_posts']) ? $_GET['max_posts'] : 100;
$load_posts = 100;
$query = isset($_GET['search']) ? dci_removeslashes($_GET['search']) : null;

$args = array(
    's' => $query,
    'posts_per_page' => $max_posts,
    'post_type'      => 'servizio',
    'orderby'        => 'post_title',
    'order'          => 'ASC'
);
$the_query = new WP_Query($args);
$posts = $the_query->posts;
?> 
 <div id="tutti-servizi" class="bg-grey-dsk py-3">
    <form role="search" id="search-form" method="get" class="search-form">
        <button type="submit" class="d-none"></button>
        <div class="container">
            <div class="row">                
                <div class="col-12">
                    <h2 class="title-xxlarge mb-3 mt-3 mb-lg-10">
                        Esplora tutti i servizi
                    </h2>
                </div>
                <div class="pt-lg-20 pb-lg-20">
                    <div class="cmp-input-search">
                        <div class="form-group autocomplete-wrapper mb-2 mb-lg-4">
                            <div class="input-group">
                                <label for="autocomplete-two" class="visually-hidden">Cerca una parola chiave</label>
                                <input type="search" class="autocomplete form-control" placeholder="Cerca una parola chiave" id="autocomplete-two" name="search" value="<?php echo $query; ?>" data-bs-autocomplete="[]" />
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit" id="button-3">
                                        Invio
                                    </button>
                                </div>
                                <span class="autocomplete-icon" aria-hidden="true"><svg class="icon icon-sm icon-primary" role="img" aria-labelledby="autocomplete-label">
                                        <use href="#it-search"></use>
                                    </svg></span>
                            </div>
                        </div>
                        <p id="autocomplete-label" class="mb-4">
                            <?php 
                               if (strlen(dci_get_option('servizi_maggioli_url', 'servizi')) < 5) { 
                                 if ($the_query->found_posts > 0) : ?>
                                <strong><?php echo $the_query->found_posts; ?></strong> Servizi trovati in ordine alfabetico
                            <?php endif;  } ?>
                        </p>
                    </div>

                    <div class="row g-4" id="load-more">
                        <?php foreach ($posts as $servizio) {
                            $load_card_type = "servizio";
                            ?>
                                <!-- <div class="col-12 col-sm-6 col-lg-4">  -->
                                    <?php if (strlen(dci_get_option('servizi_maggioli_url', 'servizi')) < 5) { 
                                        get_template_part("template-parts/servizio/card");
                                    }
                                    ?>
                                <!-- </div> -->
                            <?php
                        } ?>
                    </div>

                    <?php     
                        if (strlen(dci_get_option('servizi_maggioli_url', 'servizi')) < 5) { ?>
                            <?php get_template_part("template-parts/search/more-results"); ?>
                        <?php } else { ?>
                             <?php get_template_part("template-parts/servizio/servizi_esterni_maggioli"); ?>
                        <?php } ?>
                </div>
            </div>
        </div>
    </form>
</div>
