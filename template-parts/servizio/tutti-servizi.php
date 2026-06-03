<?php
global $posts, $the_query, $load_posts, $servizio, $load_card_type, $should_have_grey_background, $tax_query;
$requested_max_posts = isset($_GET['max_posts']) ? absint($_GET['max_posts']) : 0;
$max_posts = ($requested_max_posts > 0) ? min($requested_max_posts, 300) : 100;
$load_posts = 100;
$query = isset($_GET['search']) ? dci_removeslashes($_GET['search']) : null;
$selected_category = isset($_GET['categoria']) ? absint($_GET['categoria']) : 0;
$is_maggioli_services = strlen(dci_get_option('servizi_maggioli_url', 'servizi')) >= 5;
$service_terms = get_terms(array(
    'taxonomy' => 'categorie_servizio',
    'hide_empty' => true,
    'orderby' => 'name',
    'order' => 'ASC',
));
$form_action = dci_get_template_page_url('page-templates/servizi.php') ?: get_post_type_archive_link('servizio') ?: home_url('/servizi/');
$reset_url = $form_action;
$tax_query = array();

$args = array(
    's' => $query,
    'posts_per_page' => $max_posts,
    'post_type'      => 'servizio',
    'post_status'    => 'publish',
    'orderby'        => 'post_title',
    'order'          => 'ASC'
);

if (!$is_maggioli_services && $selected_category > 0) {
    $tax_query = array(
        array(
            'taxonomy' => 'categorie_servizio',
            'field' => 'term_id',
            'terms' => array($selected_category),
        ),
    );
    $args['tax_query'] = $tax_query;
}

$the_query = new WP_Query($args);
$posts = $the_query->posts;
?> 
 <div id="tutti-servizi" class="bg-grey-dsk py-3">
    <form role="search" id="search-form" method="get" class="search-form" action="<?php echo esc_url($form_action); ?>#search-form">
        <button type="submit" class="d-none"></button>
        <div class="container">
            <div class="row">                
                <div class="col-12">
                    <h2 class="title-xxlarge mb-3 mt-3 mb-lg-10">
                        Esplora tutti i servizi
                    </h2>
                </div>
                <div class="pt-lg-20 pb-lg-20">
                    <div class="cmp-input-search dci-servizi-search">
                        <div class="row g-3 align-items-end">
                            <div class="col-12 col-lg-8">
                                <div class="form-group autocomplete-wrapper mb-0">
                                    <div class="input-group">
                                        <label for="autocomplete-two" class="visually-hidden">Cerca una parola chiave</label>
                                        <input type="search" class="autocomplete form-control" placeholder="Cerca una parola chiave" id="autocomplete-two" name="search" value="<?php echo esc_attr($query); ?>" data-bs-autocomplete="[]" />
                                        <span class="autocomplete-icon" aria-hidden="true"><svg class="icon icon-sm icon-primary" role="img" aria-labelledby="autocomplete-label">
                                                <use href="#it-search"></use>
                                            </svg></span>
                                    </div>
                                </div>
                            </div>
                            <?php if (!$is_maggioli_services) { ?>
                                <div class="col-12 col-lg-4">
                                    <div class="form-group mb-0">
                                        <label for="categoria-servizio" class="visually-hidden">Categoria</label>
                                        <select id="categoria-servizio" name="categoria" class="form-control">
                                            <option value="0">Seleziona categoria</option>
                                            <?php if (!is_wp_error($service_terms)) { ?>
                                                <?php foreach ($service_terms as $term) { ?>
                                                    <option value="<?php echo esc_attr($term->term_id); ?>" <?php selected($selected_category, (int) $term->term_id); ?>>
                                                        <?php echo esc_html($term->name); ?>
                                                    </option>
                                                <?php } ?>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                            <?php } ?>
                            <div class="col-12 col-md-6 col-lg-3 d-grid">
                                <button class="btn btn-primary" type="submit" id="button-3">Ricerca</button>
                            </div>
                            <div class="col-12 col-md-6 col-lg-3 d-grid">
                                <a class="btn btn-secondary" href="<?php echo esc_url($reset_url); ?>#search-form">Reimposta</a>
                            </div>
                        </div>
                        <p id="autocomplete-label" class="mb-4 mt-4">
                            <?php 
                               if (!$is_maggioli_services) {
                                 if ($the_query->found_posts > 0) : ?>
                                <strong><?php echo $the_query->found_posts; ?></strong> Servizi trovati in ordine alfabetico
                            <?php else : ?>
                                0 Servizi trovati
                            <?php endif;  } ?>
                        </p>
                    </div>

                    <div class="row g-4" id="load-more">
                        <?php foreach ($posts as $servizio) {
                            $load_card_type = "servizio";
                            ?>
                                <!-- <div class="col-12 col-sm-6 col-lg-4">  -->
                                    <?php if (!$is_maggioli_services) {
                                        get_template_part("template-parts/servizio/card");
                                    }
                                    ?>
                                <!-- </div> -->
                            <?php
                        } ?>
                    </div>

                    <?php     
                        if (!$is_maggioli_services) { ?>
                            <?php get_template_part("template-parts/search/more-results"); ?>
                        <?php } else { ?>
                             <?php get_template_part("template-parts/servizio/servizi_esterni_maggioli"); ?>
                        <?php } ?>
                </div>
            </div>
        </div>
    </form>
</div>
