<?php
global $sito_tematico_id;

$sito_tematico = get_post($sito_tematico_id);
$prefix = '_dci_sito_tematico_';
$st_descrizione = dci_get_meta('descrizione_breve', $prefix, $sito_tematico->ID);
$st_link = dci_get_meta('link',$prefix, $sito_tematico->ID);
$st_colore = dci_get_meta('colore',$prefix, $sito_tematico->ID);
$st_img = dci_get_meta('immagine',$prefix, $sito_tematico->ID);

$colore_sfondo = dci_get_meta('colore',$prefix, $sito_tematico->ID) ?: false;
$sfondo_scuro = $colore_sfondo ? is_this_dark_hex($colore_sfondo) : false;

$mostra_pagina = get_post_meta($sito_tematico->ID, $prefix . 'mostra_pagina', true);
$link_pagina = ((!empty($mostra_pagina) && $mostra_pagina) || empty($st_link)) ? get_permalink($sito_tematico->ID) : $st_link;
?>

<a href="<?php echo $link_pagina ?>" 
   style="<?= ($colore_sfondo) ? 'background-color:'.$colore_sfondo : 'background-color:#f7f7f7;' ?>; border-radius: 1px;" 
   class="card card-teaser sito-tematico-page mt-0 p-2 shadow-sm" 
   target="_blank">

   <!-- Icona in alto a destra -->
    <svg class="icon external-icon">
        <use href="#it-external-link"></use>
    </svg>

    <div class="card-body" style="background-color: #f7f7f7; padding: 12px; border-radius: 8px; display: flex; flex-direction: column;">

        <div style="display: flex; align-items: center; margin-bottom: 5px;">
            <?php if($st_img) { ?>
                <div class="avatar size-lg me-2" style="flex-shrink: 0;">
                    <?php dci_get_img($st_img); ?>
                </div>
            <?php } ?>

            <h3 class="card-title sito-tematico titolo-sito-tematico <?= $sfondo_scuro ? 'text-black' : 'text-dark' ?>"
                style="font-size: 1.1rem; font-weight: 600; color: <?= $sfondo_scuro ? '#333' : '#000' ?>; margin-bottom: 0;">
                <?php echo $sito_tematico->post_title ?>
            </h3>
        </div>

        <p class="card-text text-sans-serif <?= $sfondo_scuro ? 'text-black' : '' ?>"
           style="color: <?= $sfondo_scuro ? '#333' : '#555' ?>; font-size: 0.85rem; margin-bottom: 0;">
            <?php echo $st_descrizione; ?>
        </p>

    </div>
</a>

<style>
.sito-tematico-page {
    position: relative;
    color: <?= $sfondo_scuro ? '#333' : '#000' ?>; /* Colore del testo principale */
}

.sito-tematico-page .external-icon {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 20px;
    height: 20px;
    fill: currentColor; /* Usa lo stesso colore del testo */
    transition: transform 0.3s ease, fill 0.3s ease;
}

.sito-tematico-page:hover .external-icon {
    transform: scale(1.1);
    fill: currentColor;
}
</style>


