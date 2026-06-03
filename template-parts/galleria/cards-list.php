<?php 
global $post;

$prefix = "_dci_galleria_";
$img = dci_get_meta('immagine', $prefix);
$descrizione = dci_get_meta('descrizione_breve', $prefix);
$tipo_terms = get_the_terms($post, 'tipi_galleria');
$tipo = $tipo_terms ? $tipo_terms[0]->name : '';

if(isset($img) && $img != null){ ?>

    <div class="gallery-item">
        <a href="<?= get_permalink(); ?>">
            <div class="dci-deferred-img-frame dci-gallery-image-frame">
                <div class="dci-deferred-img-frame__loader" aria-hidden="true">
                    <div class="dci-async-loader">
                        <span class="dci-async-loader__spinner"></span>
                        <span class="dci-async-loader__line dci-async-loader__line--long"></span>
                        <span class="dci-async-loader__line"></span>
                    </div>
                </div>
                <?php dci_get_deferred_img($img, 'gallery-image'); ?>
            </div>
            
            <!-- Badge del tipo -->
            <?php if(!empty($tipo)) : ?>
                <span class="gallery-type"><?= esc_html($tipo); ?></span>
            <?php endif; ?>

            <div class="overlay">
                <h3 class="gallery-title"><?= the_title(); ?></h3>
                <?php if(!empty($descrizione)){ ?>
                    <p class="gallery-description"><?= esc_html($descrizione) ?></p>
                <?php } ?>
            </div>
        </a>
    </div>
<?php } ?>
