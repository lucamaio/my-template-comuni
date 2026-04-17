<?php
global $the_query, $load_posts, $load_card_type;

// Limite massimo hard per evitare query troppo grandi via parametro GET.
$requested_max_posts = isset($_GET['max_posts']) ? absint($_GET['max_posts']) : 0;

$load_posts = -1;
// $query = isset($_GET['search']) ? dci_removeslashes($_GET['search']) : null;

$hide_notizie_old = dci_get_option("ck_hide_notizie_old", "homepage");
$notizie_home = dci_get_option("numero_notizie_home", "homepage");
$notizie_home     = max(1, (int) $notizie_home);

$prefix = '_dci_notizia_';


/**
 * Mantiene abbastanza elementi per popolare la sezione,
 * ma senza allargare troppo la query.
 */
if ($hide_notizie_old === 'true') {
    $max_posts = ($requested_max_posts > 0) ? min($requested_max_posts, 50) : max($notizie_home * 4, 20);
} else {
    // Se non serve filtrare "notizie vecchie", basta caricare il numero richiesto.
    $max_posts = ($requested_max_posts > 0) ? min($requested_max_posts, 300) : $notizie_home;
}

$args = array(
    //'s'                   => $query,
    'post_type'            => 'notizia',
    'meta_key'             => $prefix . 'data_pubblicazione',
    'orderby'              => 'meta_value_num',
    'order'                => 'DESC',
    'posts_per_page'       => $max_posts,
    'no_found_rows'        => true,
    'ignore_sticky_posts'  => true,
    'update_post_meta_cache' => true,
    'update_post_term_cache' => false,
);

$the_query = new WP_Query($args);
$posts = $the_query->posts;

if (empty($posts)) {
    wp_reset_postdata();
    return;
}

$count = 0;
$oggi = strtotime('today');

// var_dump($posts);
foreach ($posts as $post) {
    if ($count >= $notizie_home) {
        break;
    }

    $load_card_type = 'notizia';
    $mostra_scheda = false;
    $hide_home = dci_get_meta("hide_home", $prefix, $post->ID);

    if ($hide_notizie_old === 'true') {
        $timestamp_pubblicazione = dci_get_notizia_timestamp_from_meta($post->ID, $prefix . 'data_pubblicazione', true);

        $timestamp_scadenza = dci_get_notizia_timestamp_from_meta( $post->ID, $prefix . 'data_scadenza',false);

        /**
         * Se la scadenza coincide con la pubblicazione effettiva,
         * la consideriamo come "nessuna vera scadenza".
         */
        if ($timestamp_scadenza > 0 && $timestamp_pubblicazione > 0 && date('Y-m-d', $timestamp_scadenza) === date('Y-m-d', $timestamp_pubblicazione)) {
            $timestamp_scadenza = 0;
        }

        /**
         * Mostra la scheda se:
         * - non ha scadenza
         * - oppure la scadenza è oggi o futura
         */
        if ($timestamp_scadenza === 0 || $timestamp_scadenza >= $oggi) {
            $mostra_scheda = true;
        }
    } else {
        $mostra_scheda = true;
    }

    if ($mostra_scheda) {
        $count++;

        setup_postdata($post);
        ?>
        <div class="col-12 col-md-6 col-lg-4">
            <?php get_template_part("template-parts/home/scheda-evidenza"); ?>
        </div>
        <?php
    }
}

wp_reset_postdata();
?>