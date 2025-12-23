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
            <img src="<?php echo $img ?>" 
                 alt="Paesaggio montano al tramonto con cime innevate e cielo arancione" 
                 class="gallery-image" 
                 onerror="this.style.backgroundColor='#667eea'; this.alt='Immagine non disponibile'">
            
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