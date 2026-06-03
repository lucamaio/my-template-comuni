<?php 
// Galleria impostata in Configurazione > Vivere l'ente
global $gallery;

// Recupero opzione grezza
$raw_gallery = dci_get_option('immagine', 'homepage');

// Adatta a nuovo formato (array), gestendo retrocompatibilità
if (is_string($raw_gallery) && !empty($raw_gallery)) {
    $gallery = [$raw_gallery];
} elseif (is_array($raw_gallery)) {
    $gallery = $raw_gallery;
} else {
    $gallery = [];
}

?>

<?php if (count($gallery) > 0) : ?>
<section id="carosello" class="carosello-section">
    <?php if (count($gallery) === 1): ?>
        <?php $url_immagine = array_values($gallery)[0]; ?>
        <div class="bg-image dci-deferred-img-frame dci-home-hero-deferred">
            <div class="dci-deferred-img-frame__loader" aria-hidden="true">
                <div class="dci-async-loader">
                    <span class="dci-async-loader__spinner"></span>
                    <span class="dci-async-loader__line dci-async-loader__line--long"></span>
                    <span class="dci-async-loader__line"></span>
                </div>
            </div>
            <?php dci_get_deferred_img($url_immagine, 'immagine-home'); ?>
        </div>
        <style>
            .bg-image img {
                width: 100%;
                height: 450px;
                object-fit: cover;
                object-position: center;
            }
        </style>
    <?php else: ?>
        <?php get_template_part("template-parts/single/gallery-carosello"); ?>
    <?php endif; ?>
</section>
<?php endif; ?>
