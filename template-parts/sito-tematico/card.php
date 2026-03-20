<?php
global $sito_tematico_id;

$sito_tematico = get_post($sito_tematico_id);
$prefix = '_dci_sito_tematico_';

$st_descrizione = dci_get_meta('descrizione_breve', $prefix, $sito_tematico->ID);
$st_link = dci_get_meta('link', $prefix, $sito_tematico->ID);
$st_colore = dci_get_meta('colore', $prefix, $sito_tematico->ID);
$st_img = dci_get_meta('immagine', $prefix, $sito_tematico->ID);

$colore_sfondo = dci_get_meta('colore', $prefix, $sito_tematico->ID) ?: false;
$sfondo_scuro = $colore_sfondo ? is_this_dark_hex($colore_sfondo) : true;

$mostra_pagina = get_post_meta($sito_tematico->ID, $prefix . 'mostra_pagina', true);

$link_pagina = ((!empty($mostra_pagina) && $mostra_pagina) || empty($st_link))
    ? get_permalink($sito_tematico->ID)
    : $st_link;
?>

<a  target="_blank" href="<?php echo esc_url($link_pagina); ?>"
   style="<?= ($colore_sfondo) ? 'background-color:' . $colore_sfondo : '' ?>"
   class="card card-teaser <?= $colore_sfondo ? '' : 'bg-primary' ?> rounded mt-0 p-3 shadow-sm border border-light sito-tematico-card">

    <?php if ($st_img) { ?>
        <div class="avatar size-lg me-3">
            <?php dci_get_img($st_img); ?>
        </div>
    <?php } ?>

    <div class="card-body">

        <h3 class="card-title sito-tematico titolo-sito-tematico <?= $sfondo_scuro ? 'text-white' : 'text-dark' ?>">
            <?php echo $sito_tematico->post_title ?>

            <!-- ICONA -->
            <svg class="icon icon-white" style="width:20px;height:20px;margin-left:8px;">
                <use href="#it-external-link"></use>
            </svg>

        </h3>

        <p class="card-text text-sans-serif <?= $sfondo_scuro ? 'text-white' : '' ?>">
            <?php echo $st_descrizione; ?>
        </p>

    </div>

</a>


<style>

/* ===============================
   CARD
================================ */

.sito-tematico-card {
    transition: transform 0.3s ease,
                box-shadow 0.3s ease,
                background-color 0.3s ease;

    position: relative;

    /* FIX ANDROID TAP */
    touch-action: manipulation;
}

/* Hover */
.sito-tematico-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    background-color: #e0e0e0;
}

/* Titolo hover */
.sito-tematico-card:hover .card-title {
    color: #0056b3;
}


/* ===============================
   TESTO
================================ */

.sito-tematico-card .card-title {
    font-size: 1.5rem;
    font-weight: 600;
    padding-right: 30px;
    margin-right: 40px;
}

.sito-tematico-card .text-dark {
    color: #333333;
}

.sito-tematico-card .text-white {
    color: #ffffff;
}

.sito-tematico-card .card-text {
    font-size: 1rem;
    color: #777777;
    line-height: 1.5;
    margin-top: 10px;
}


/* ===============================
   AVATAR
================================ */

.sito-tematico-card .avatar {
    background-color: #FFFFFF;
    border-radius: 50%;
    padding: 2px;

    display: flex;
    justify-content: center;
    align-items: center;
}

.sito-tematico-card .avatar img {
    max-width: 100%;
}


/* ===============================
   ICONA SVG (FIX CLICK)
================================ */

.sito-tematico-card .card-title svg.icon-white {
    position: absolute;

    top: 10px;
    right: 10px;

    width: 20px;
    height: 20px;

    z-index: 2;

    transition: transform 0.3s ease,
                fill 0.3s ease;

    /* 🔴 FIX PRINCIPALE */
    pointer-events: none;
}


/* Hover icona */
.sito-tematico-card .card-title svg.icon-white:hover {
    transform: scale(1.1);
    fill: #f0f0f0 !important;
}


/* ===============================
   CARD BODY
================================ */

.sito-tematico-card .card-body {
    padding: 15px;
}


/* ===============================
   FIX MOBILE HOVER
================================ */

@media (hover: none) {

    .sito-tematico-card:hover {
        transform: none;
        box-shadow: none;
        background-color: inherit;
    }

}

</style>
