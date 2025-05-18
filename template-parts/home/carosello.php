<?php
global $scheda;

$post = get_post($scheda) ?? null;
$img = dci_get_meta('immagine');
$descrizione_breve = dci_get_meta('descrizione_breve');
$icon = dci_get_post_type_icon_by_id($post->ID);

$page = get_page_by_path(dci_get_group($post->post_type));    

$page_macro_slug = dci_get_group($post->post_type);
$page_macro = get_page_by_path($page_macro_slug);

// Query per recuperare gli ultimi post per lo slider
$args = array(
    'post_type' => 'post', // Puoi cambiare questo con il tuo tipo di post personalizzato
    'posts_per_page' => 5, // Numero di post da mostrare nello slider
    'orderby' => 'date', // Ordine dei post
    'order' => 'DESC'
);
$recent_posts = new WP_Query($args);
?>

<main id="main-container" class="main-container redbrown">
    <h1 class="visually-hidden" id="main-container">Comune di Lucca</h1>
    <section id="head-section">
        <h2 class="visually-hidden">Contenuti in evidenza</h2>
        <div class="it-carousel-wrapper it-carousel-landscape-abstract splide" data-bs-carousel-splide data-splide='{"type":"loop", "autoplay":true, "interval":5000, "arrows": true, "breakpoints": {"768": {"arrows": false}}}'>
            <div class="splide__track">
                <ul class="splide__list">
                    <?php
                    // Verifica se ci sono post disponibili
                    if ($recent_posts->have_posts()) :
                        // Ciclo per mostrare i post nello slider
                        while ($recent_posts->have_posts()) : $recent_posts->the_post();
                            ?>
                            <li class="splide__slide">
                                <div class="it-single-slide-wrapper">
                                    <div class="it-text-slider-wrapper-outside">
                                        <div class="card-wrapper">
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="category-top">
                                                        <svg class="icon icon-sm" aria-hidden="true">
                                                            <use xlink:href="#it-calendar"></use>
                                                        </svg>
                                                        <span class="title-xsmall-semi-bold fw-semibold">notizia</span>
                                                        <span class="data"><?php echo get_the_date('d F Y'); ?></span>
                                                    </div>
                                                    <h3 class="card-title big-heading">
                                                        <a href="<?php the_permalink(); ?>" class="text-decoration-none">
                                                            <?php the_title(); ?>
                                                        </a>
                                                    </h3>
                                                    <p class="mb-4 subtitle-small pt-3 lora">
                                                        <?php echo wp_trim_words(get_the_excerpt(), 30); ?>
                                                    </p>

                                                    <ul class="d-flex flex-wrap gap-1">
                                                        <li>
                                                            <a class="chip chip-simple" href="#">
                                                                <span class="chip-label">Istruzione</span>
                                                            </a>
                                                        </li>
                                                    </ul>

                                                    <a class="read-more pb-3 mt-4 float-none d-block" href="<?php the_permalink(); ?>">
                                                        <span class="text">Vai alla novità</span>
                                                        <svg class="icon">
                                                            <use xlink:href="#it-arrow-right"></use>
                                                        </svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <a href="<?php the_permalink(); ?>">
                                        <div>
                                            <?php if (has_post_thumbnail()) : ?>
                                                <?php the_post_thumbnail('full', ['class' => 'attachment-slide size-slide']); ?>
                                            <?php endif; ?>
                                        </div>
                                    </a>
                                </div>
                            </li>
                        <?php endwhile; 
                        wp_reset_postdata(); // Reset della query
                    else : ?>
                        <li class="splide__slide">Nessuna notizia disponibile.</li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="splide__arrows">
                <button class="splide__arrow splide__arrow--prev">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 40" width="40" height="40">
                        <path d="m15.5 0.932-4.3 4.38 14.5 14.6-14.5 14.5 4.3 4.4 14.6-14.6 4.4-4.3-4.4-4.4-14.6-14.6z"></path>
                    </svg>
                </button>
            </div>
        </div>
    </section>
</main>

<script type="text/javascript" src="https://www.comune.lucca.it/app/themes/net7-comuni/dist/js/bootstrapItaliaJs.bundle.js?ver=6.7.2" id="nc-bootstrap-italia-js-js"></script>
<script src="https://cdn.jsdelivr.net/npm/@splidejs/splide@3.6.12/dist/js/splide.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  new Splide('.splide', {
    type: 'loop',
    autoplay: true,
    interval: 5000, // Intervallo di autoplay (in millisecondi)
    arrows: true,  // Attiva le frecce di navigazione
    pagination: false,  // Disabilita la paginazione
    pauseOnHover: false, // Non pausa l'autoplay al passaggio del mouse
    breakpoints: {
      768: {
        arrows: false, // Disabilita le frecce sui dispositivi con larghezza inferiore a 768px
      }
    }
  }).mount();
});
</script>

