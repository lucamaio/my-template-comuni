<?php global $post;
// var_dump($post);

$prefix = "_dci_galleria_";
$img = dci_get_meta('immagine', $prefix);
$descrizione = dci_get_meta('descrizione_breve', $prefix);

if(isset($img) && $img != null){ ?>

    <div class="gallery-item">
        <a href="<?=  get_permalink(); ?>">
            <img src="<?php echo $img ?>" alt="Paesaggio montano al tramonto con cime innevate e cielo arancione" class="gallery-image" onerror="this.style.backgroundColor='#667eea'; this.alt='Immagine non disponibile'">
            <div class="overlay">
                    <h3 class="gallery-title"><?= the_title(); ?></h3>
                    <?php if(isset($descrizione) && $descrizione != null){ ?>
                        <p class="gallery-description"><?= $descrizione ?></p>
                    <?php } ?>
            </div>
        </a>
    </div>
<?php } ?>