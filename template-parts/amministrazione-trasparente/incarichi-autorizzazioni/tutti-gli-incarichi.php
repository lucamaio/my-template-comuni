<?php
global $post;

$max_posts = isset($_GET['max_posts']) ? intval($_GET['max_posts']) : 100;
$main_search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$paged = max(1, get_query_var('paged') ? get_query_var('paged') : (isset($_GET['paged']) ? intval($_GET['paged']) : 1));

$args = array(
    'post_type'      => 'incarichi_dip',
    'posts_per_page' => $max_posts,
    'orderby'        => 'date',    // Ordina per data pubblicazione
    'order'          => 'DESC',    // Dalla più recente alla meno recente
    'paged'          => $paged,
);

if ($main_search_query) {
    $args['s'] = $main_search_query;
}

$the_query = new WP_Query($args);
$prefix = "_dci_icad_";
?>

<?php if ($the_query->have_posts()) : ?>

    <?php while ($the_query->have_posts()) : $the_query->the_post();
        get_template_part('template-parts/amministrazione-trasparente/incarichi-autorizzazioni/card');
    endwhile;
    wp_reset_postdata(); ?>

    <div class="row my-4">
        <nav class="pagination-wrapper justify-content-center col-12" aria-label="Navigazione pagine">
            <?php 
            // Usa la funzione già presente nel tema (evita di ridefinirla)
            echo dci_bootstrap_pagination(); 
            ?>
        </nav>
    </div>

<?php else : ?>
    <div class="alert alert-info text-center" role="alert">
        Nessun incarico conferito trovato.
    </div>
<?php endif; ?>