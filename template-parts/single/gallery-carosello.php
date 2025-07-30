<?php global $gallery; ?>
<?php if (!empty($gallery)) : ?>
    <div class="it-carousel-wrapper it-full-carousel it-standard-image splide" id="gallery-carousel">
        <div class="splide__track">
            <ul class="splide__list">
                <?php foreach ($gallery as $photo) : ?>
                    <li class="splide__slide">
                        <div class="it-single-slide-wrapper">
                            <div class="it-grid-item-wrapper">
                                <a href="<?= esc_url(wp_get_attachment_image_src(attachment_url_to_postid($photo), 'full')[0]); ?>" class="lightbox">
                                    <div class="bg-image">
                                        <?php dci_get_img($photo, 'img-gallery-custom'); ?>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="splide__pagination"></div>
    </div>
<?php else : ?>
    <p>Nessuna immagine disponibile nella galleria.</p>
<?php endif; ?>

<style>
    .bg-image {
        position: relative;
        width: 100%;
		padding-bottom: 10%;
        padding-top: 30%; /* 16:9 ratio */
        overflow: hidden;
        margin-bottom: 0px; /* Assicurati che non ci siano margini */
    }

    .bg-image img.img-gallery-custom {
        position: absolute;
        top: 0px;
        left: 0px;
        width: 100%;
        height: auto; /* Cambiato per mantenere il rapporto 16:9 */
        object-fit: cover;
        object-position: center;
    }

    /* Rimuovi margini e padding dalle slide */
    .splide__slide {
        margin: 0; /* Rimuovi margini tra le slide */
        padding: 0; /* Rimuovi padding se presente */
    }
</style>

<!-- Splide CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/css/splide.min.css" />

<!-- Splide JS -->
<script src="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/js/splide.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        new Splide('#gallery-carousel', {
            type: 'loop',
            autoplay: true,
            interval: 2000, // 2 secondi
            pauseOnHover: false,
            pauseOnFocus: false,
        }).mount();
    });
</script>
