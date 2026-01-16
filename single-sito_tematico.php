<?php
/**
 * Sito Tematico template file
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Design_Comuni_Italia
 */

get_header();
?>
   <?php
/**
 * Sito Tematico template file
 */

get_header();
?>
<main>
    <?php
    while ( have_posts() ) :
        the_post();
        $user_can_view_post = dci_members_can_user_view_post(get_current_user_id(), $post->ID);
        $descrizione_breve = dci_get_meta("descrizione_breve");
        $post_id = get_the_ID();
        $prefix = '_dci_sito_tematico_';

        $mostra_pagina = get_post_meta($post_id, $prefix . 'mostra_pagina', true);
        $link_principale = get_post_meta($post_id, $prefix . 'link', true);
        $user_can_view_post = dci_members_can_user_view_post(get_current_user_id(), $post->ID);
        $descrizione_breve = dci_get_meta("descrizione_breve");

        if (!$mostra_pagina && !empty($link_principale)) {
            wp_redirect(esc_url($link_principale));
            exit;
        }
        
        // Nuovo campo: descrizione_completa
        $descrizione_completa = dci_get_meta('descrizione_completa', $prefix, $post_id);
        ?>
        
        <div class="container px-4 my-4" id="main-container">
            <div class="row">
                <div class="col px-lg-4">
                    <?php get_template_part("template-parts/common/breadcrumb"); ?>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8 px-lg-4 py-lg-2">
                    <h1 data-audio><?php the_title(); ?></h1>
                    <p data-audio><?php echo $descrizione_breve; ?></p>

                    <?php if (!empty($descrizione_completa)) : ?>
                        <div class="descrizione-completa mt-3 mb-4">
                            <?php echo esc_html($descrizione_completa); ?>
                        </div>
                    <?php endif; ?>

                </div>

                <div class="col-lg-3 offset-lg-1">
                    <?php 
                    $inline = true;
                    get_template_part('template-parts/single/actions');
                    ?>
                </div>
            </div>
        </div>
        <?php
        $img_id = dci_get_meta('immagine', $prefix, $post_id);
        $mostra_immagine = get_post_meta($post_id, $prefix . 'mostra_immagine', true) === '' ? false : get_post_meta($post_id, $prefix . 'mostra_immagine', true);
        
         if ($mostra_immagine && $img_id) { ?>
                <section class="hero-img mb-15ì2 mb-lg-30 aling-itmes-center">
                    <center>
                        <div class="img-wrapper">
                            <!-- Applica la classe CSS img-resized per ridimensionare l'immagine -->
                            <?php dci_get_img($img_id, 'rounded img-fluid img-responsive foto-soft-style img-resized'); ?>
                        </div>
                    </center>
                </section>
            <?php } ?>
       
        <article class="article-wrapper" data-audio>
            <div class="bg-grey-dsk mb-0">
                <div class="container sito-links-wrapper">
                    <div class="row">
                        <div class="col-lg-12">
                            <?php the_content(); ?>
                        </div>
                    </div>

                   <h2 style="font-size:22px; margin-bottom:20px;">Link disponibili</h2>

<?php
$link_multipli = get_post_meta($post_id, $prefix . 'url_link', true);

if (!empty($link_multipli)) { ?>
    
    <div class="sito-links-grid">

        <?php foreach ($link_multipli as $item) {
            $url = isset($item['url_link_page']) ? $item['url_link_page'] : '';
            $descrizione = isset($item['descrizione']) ? $item['descrizione'] : '';
            $titolo = isset($item['titolo']) ? $item['titolo'] : '';
            $blank = isset($item['target_blank']) && $item['target_blank'] ? ' target="_blank" rel="noopener"' : '';
            
            if (empty($url)) continue;
        ?>

            <a href="<?php echo esc_url($url); ?>" 
               class="sito-link-card sito-tematico-card rounded-3 border border-light shadow-sm p-3"
               <?php echo $blank; ?>>

                <div class="card-body p-0">

                    <h3 class="card-title sito-tematico titolo-sito-tematico text-dark mb-2 d-flex align-items-center" 
                        style="font-size:1.25rem; font-weight:600;">

                        <!-- Icona richiesta accanto al titolo -->
                        <svg class="icon icon-primary me-2" aria-hidden="true" style="min-width: 1.2rem;">
                            <use href="#it-link"></use>
                        </svg>

                        <?php echo esc_html($titolo ?: 'Link'); ?>
                    </h3>

                    <?php if (!empty($descrizione)) { ?>
                    <p class="card-text text-secondary" style="font-size:0.95rem;">
                        <?php echo esc_html($descrizione); ?>
                    </p>
                    <?php } ?>

                </div>

            </a>

        <?php } ?>

    </div>

<?php } else { ?>

    <p>Nessun link disponibile.</p>

<?php } ?>


                    <div class="row">
                        <div class="col-lg-12">
                            <?php get_template_part("template-parts/single/bottom"); ?>
                        </div>
                    </div>

                </div> <!-- /container -->
            </div> <!-- /bg-grey-dsk -->
        </article>
        <?php get_template_part("template-parts/common/valuta-servizio"); ?>

    <?php endwhile; ?>
</main>

<style>

.sito-tematico-img img {
    max-width: 150px; 
    height: auto;
    border-radius: 12px;
}

/* Griglia */
.sito-links-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
}

/* Card uniforme + arrotondata */
.sito-link-card {
    background: #ffffff;
    border-radius: 12px; /* Bordo arrotondato come prima */
    text-decoration: none;
    display: block;
    transition: transform 0.25s ease, box-shadow 0.25s ease, background-color 0.25s ease;
    position: relative;
}

/* Effetto hover */
.sito-link-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 18px rgba(0,0,0,0.16);
    background-color: #f0f0f0;
}

/* Titolo */
.sito-link-card .card-title {
    padding-right: 35px;
    color: #333;
}

/* Descrizione */
.sito-link-card .card-text {
    color: #666;
}

/* Icona vicino al titolo */
.sito-link-card svg.icon-primary {
    width: 20px;
    height: 20px;
}

/* Mobile */
@media (max-width: 900px) {
    .sito-links-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 600px) {
    .sito-links-grid { grid-template-columns: repeat(1, 1fr); }
}

</style>



<?php get_footer(); ?>