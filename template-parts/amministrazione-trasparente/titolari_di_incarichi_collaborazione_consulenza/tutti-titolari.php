<?php

$max_posts = isset($_GET['max_posts']) ? intval($_GET['max_posts']) : 10;
$main_search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

$args = array(
    'post_type'       => 'titolare_incarico_collaborazione_consulenza',
    'posts_per_page'  => $max_posts,
    // 'meta_key'        => '_dci_bando_data_inizio',
    // 'orderby'         => 'meta_value_num',
    // 'order'           => 'DESC',
    'paged'              => $paged,
    's'               => $main_search_query, // Per la ricerca generica su titolo/contenuto
);

$the_query = new WP_Query($args);
$prefix = "_dci_bando_";

// SEARCH BAR
?>



<?php if ($the_query->have_posts()){
    while ($the_query->have_posts()){
        $the_query->the_post();
        get_template_part('template-parts/amministrazione-trasparente/titolari_di_incarichi_collaborazione_consulenza/card');
    }
    wp_reset_postdata();?>
        <div class="row my-4">
        <nav class="pagination-wrapper justify-content-center col-12" aria-label="Navigazione pagine">
            <?php echo dci_bootstrap_pagination(); ?>
        </nav>
    </div>
<?php } else{?>
    <div class="alert alert-info text-center" role="alert">
        Nessun titolare di incarichi di collaborazione o consulenza trovato.
    </div>
<?php } ?>