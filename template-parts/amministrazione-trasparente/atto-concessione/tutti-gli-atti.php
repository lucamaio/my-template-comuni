<?php
global $post;
$max_posts = isset($_GET['max_posts']) ? intval($_GET['max_posts']) : 10;
$main_search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

$args = array(
    'post_type'       => 'atto_concessione',
    'posts_per_page'  => $max_posts,
    'orderby'         => 'meta_value_num',
    'order'           => 'DESC',
    'paged'              => $paged,
);


$the_query = new WP_Query($args);
$prefix = "_dci_atto_concessione_";
?>

<?php if ($the_query->have_posts()) : ?>
        
        <?php while ($the_query->have_posts()) : $the_query->the_post();
            get_template_part('template-parts/amministrazione-trasparente/atto-concessione/card');
        endwhile;
        wp_reset_postdata();?>
        <div class="row my-4">
    <nav class="pagination-wrapper justify-content-center col-12" aria-label="Navigazione pagine">
        <?php echo dci_bootstrap_pagination(); ?>
    </nav>
</div>
    <?php else : ?>
        <div class="alert alert-info text-center" role="alert">
            Nessun atto di concessione trovato.
        </div>
    <?php endif; ?>