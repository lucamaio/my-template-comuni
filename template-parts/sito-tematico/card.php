<?php
global $sito_tematico_id;

$sito_tematico = get_post($sito_tematico_id);
$prefix = '_dci_sito_tematico_';
$st_descrizione = dci_get_meta('descrizione_breve', $prefix, $sito_tematico->ID);
$st_link = dci_get_meta('link',$prefix, $sito_tematico->ID);
$st_colore = dci_get_meta('colore',$prefix, $sito_tematico->ID);
$st_img = dci_get_meta('immagine',$prefix, $sito_tematico->ID);

$colore_sfondo = dci_get_meta('colore',$prefix, $sito_tematico->ID) ?: false;
$sfondo_scuro = $colore_sfondo ? is_this_dark_hex($colore_sfondo) : true;

$mostra_pagina = get_post_meta($sito_tematico->ID, $prefix . 'mostra_pagina', true);
$link_pagina = ((!empty($mostra_pagina) && $mostra_pagina) || empty($st_link)) ? get_permalink($sito_tematico->ID) : $st_link;
?>
<a href="<?php echo $link_pagina ?>" style="<?= ($colore_sfondo) ? 'background-color:'.$colore_sfondo : '' ?>" class="card card-teaser <?= $colore_sfondo ? '' : 'bg-primary' ?> rounded mt-0 p-3 shadow-sm border border-light sito-tematico-card" target="_blank">
    <?php if($st_img) { ?>
        <div class="avatar size-lg me-3">
            <?php dci_get_img($st_img); ?>
        </div>
    <?php } ?>
    <div class="card-body">
        <h3 class="card-title sito-tematico titolo-sito-tematico <?= $sfondo_scuro ? 'text-white':'text-dark' ?>">
            <?php echo $sito_tematico->post_title ?>
               <!-- Aggiungi l'icona SVG qui -->
            <svg class="icon icon-white" style="width: 20px; height: 20px; margin-left: 8px;">
                <use href="#it-external-link"></use>
            </svg>
        </h3>
        <p class="card-text text-sans-serif <?= $sfondo_scuro ? 'text-white':'' ?>">
            <?php echo $st_descrizione; ?>
        </p>
    </div>
</a>
<style>
/* Aggiunta dell'effetto hover per la card */
.sito-tematico-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease;
    position: relative; /* Necessario per il posizionamento assoluto dell'icona */
}

/* Hover: sollevamento della card */
.sito-tematico-card:hover {
    transform: translateY(-5px); /* Leggero sollevamento */
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2); /* Ombra più intensa */
}

/* Hover: Cambia colore del titolo */
.sito-tematico-card:hover .card-title {
    color: #0056b3; /* Colore blu scuro per il titolo durante hover */
}

/* Hover: Cambia colore di sfondo per card */
.sito-tematico-card:hover {
    background-color: #e0e0e0; /* Sfondo più chiaro su hover (se il colore sfondo è chiaro) */
}

/* Personalizzazione per il colore del titolo e descrizione */
.sito-tematico-card .card-title {
    font-size: 1.5rem;
    font-weight: 600;
    padding-right: 30px; /* Aggiungi un po' di padding a destra per fare spazio all'icona */
}

/* Colori del titolo e descrizione basati sul colore di sfondo */
.sito-tematico-card .text-dark {
    color: #333333; /* Colore scuro per il testo */
}

.sito-tematico-card .text-white {
    color: #ffffff; /* Colore chiaro per il testo su sfondo scuro */
}

/* Hover per sfondo chiaro con il colore definito */
.sito-tematico-card.bg-primary:hover {
    background-color: #0056b3; /* Colore di hover per background chiaro */
}

/* Stile dell'immagine (se presente) */
.sito-tematico-card .avatar {
    background-color: #FFFFFF;
    border-radius: 50%;
    padding: 2px;
    display: flex;
    justify-content: center;
    align-items: center;
}

.sito-tematico-card .avatar img {
    max-width: 100%; /* Per fare in modo che l'immagine si adatti correttamente */
}

/* Stile per la descrizione */
.sito-tematico-card .card-text {
    font-size: 1rem;
    color: #777777;
    line-height: 1.5;
    margin-top: 10px;
}

/* Aggiungi queste regole per posizionare l'icona in alto a destra */
.sito-tematico-card .card-title svg.icon-white {
    position: absolute;
    top: 10px; /* Posiziona l'icona a 10px dalla parte superiore della card */
    right: 10px; /* Posiziona l'icona a 10px dal lato destro della card */
    width: 20px; /* Imposta una dimensione fissa per l'icona */
    height: 20px; /* Imposta una dimensione fissa per l'icona */
    z-index: 2; /* Assicurati che l'icona sia sopra gli altri elementi */
    transition: transform 0.3s ease, fill 0.3s ease; /* Aggiungi transizioni per l'hover */
}

/* Hover: Aggiungi un effetto quando si passa sopra l'icona */
.sito-tematico-card .card-title svg.icon-white:hover {
    transform: scale(1.1); /* Leggera ingrandimento dell'icona al passaggio del mouse */
    fill: #f0f0f0 !important; /* Cambia colore dell'icona a bianco quando si passa sopra */
}

/* Modifica il margine della card-body se necessario per fare spazio all'icona */
.sito-tematico-card .card-body {
    padding: 15px; /* Aggiungi padding per fare spazio all'icona */
}

/* Aggiungi spazio tra l'icona e il contenuto (opzionale) */
.sito-tematico-card .card-title {
    margin-right: 40px; /* Spazio a destra per fare posto all'icona */
}




    
    
</style>
