<?php
global $boxes;
$box_accesso_rapido = $boxes;
?>

<?php if (!empty($boxes)) { ?>
<div class="container py-5">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <h2 class="title-xxlarge mb-4">Accesso rapido</h2>

    <div class="row g-4 custom-styles">
        <?php foreach ($boxes as $box) {
            // Recupero delle variabili dal box
            $colore_sfondo = $box['colore'] ?? false; // Aggiungi colore se disponibile
            $sfondo_scuro = $colore_sfondo ? is_this_dark_hex($colore_sfondo) : true; // Controlla se il colore è scuro
        ?>
            <div class="col-md-6 col-xl-4">
                <a href="<?php echo $box['link_message']; ?>" style="<?= ($colore_sfondo) ? 'background-color:' . $colore_sfondo : '' ?>" class="card card-teaser <?= $colore_sfondo ? '' : 'bg-neutral' ?> rounded mt-0 p-3" target="_blank">
                    <div class="cmp-card-simple card-wrapper pb-0 rounded">
                        <div style="border: none;">
                            <div class="card-body d-flex align-items-center">
                                <?php if (isset($box['icona_message']) && $box['icona_message'] && array_key_exists('icon', $box) && !empty($box['icon'])) { ?>
                                    <div class="avatar size-lg me-3" style="min-width: 50px; width: 50px; height: 50px; display: flex; justify-content: center; align-items: center; background-color: #f0f0f0; border-radius: 50%;">
                                        <i class="fas fa-<?php echo htmlspecialchars($box['icon']); ?>" style="color: #555555; font-size: 24px;"></i>
                                    </div>
                                <?php } ?>
                                <div class="flex-grow-1">
                                    <h3 class="card-title t-primary title-xlarge text-dark" style="font-size: 1.5rem; line-height: 1.2;">
                                        <?php echo $box['titolo_message']; ?>
                                        <!-- Aggiungi l'icona SVG qui -->
                                        <svg class="icon icon-white" style="width: 20px; height: 20px; margin-left: 8px;">
                                            <use href="#it-external-link"></use>
                                        </svg>
                                    </h3>
                                    <?php if (isset($box['desc_message']) && $box['desc_message']) { ?>
                                        <p class="card-text text-sans-serif mb-0 description text-muted" style="font-size: 1rem; line-height: 1.5;">
                                            <?php echo $box['desc_message']; ?>
                                        </p>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        <?php } ?>
    </div>

</div>
<?php } ?>


<style>
/* Stile per la lista dei pulsanti accesso rapido */
.custom-styles .row {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
}

.custom-styles .card {
    background-color: #f9f9f9; /* Sfondo neutro chiaro per i pulsanti */
    border: 1px solid #e0e0e0; /* Bordo grigio chiaro per i pulsanti */
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1); /* Leggera ombra per dare profondità */
    transition: transform 0.3s ease, box-shadow 0.3s ease; /* Transizione per effetto hover */
    display: flex;
    flex-direction: column;
    height: 100%;
    min-height: 120px; /* Ridotto l'altezza minima delle card */
    position: relative; /* Essenziale per posizionare l'icona assoluta */
}

.custom-styles .card:hover {
    transform: translateY(-5px); /* Sollevamento al passaggio del mouse */
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2); /* Ombra più forte durante hover */
}

.custom-styles .card-body {
    display: flex;
    align-items: center;
    justify-content: space-between; /* Aggiunto per un migliore allineamento del contenuto */
    padding: 10px; /* Ridotto il padding per una card più compatta */
    flex-grow: 1; /* Consente al contenitore di crescere per riempire lo spazio disponibile */
}

.custom-styles .card-title {
    margin-bottom: 6px; /* Ridotto il margine inferiore per un aspetto più compatto */
    font-size: 1.2rem;
    color: #333333; /* Colore scuro per il titolo */
    flex-grow: 1; /* Consente al titolo di crescere e occupare spazio disponibile */
}

.custom-styles .description {
    font-size: 0.9rem; /* Ridotto la dimensione del testo per la descrizione */
    line-height: 1.4;
    color: #777777; /* Grigio chiaro per la descrizione */
    margin-top: 8px; /* Ridotto il margine tra titolo e descrizione */
}

/* Icona - Circolare e centrale con background chiaro */
.custom-styles .avatar i {
    color: #555555; /* Colore neutro per l'icona */
    font-size: 24px; /* Dimensione dell'icona */
}

/* Per i pulsanti con sfondo neutro */
.custom-styles .card.bg-neutral {
    background-color: #fafafa; /* Colore neutro chiaro */
    color: #333; /* Colore scuro per il testo */
}

/* Effetto hover per il pulsante */
.custom-styles .card.bg-neutral:hover {
    background-color: #e0e0e0; /* Cambio sfondo su hover per effetto interattivo */
}

/* Colori del testo in caso di hover */
.custom-styles .card.bg-neutral:hover .card-title {
    color: #0056b3; /* Colore blu scuro per il titolo quando hover */
}

/* Icone per i pulsanti */
.custom-styles .avatar {
    background-color: #f0f0f0; /* Colore di sfondo grigio chiaro per l'avatar */
    border-radius: 50%; /* Forma circolare per l'icona */
    padding: 2px; /* Un po' di padding per l'icona */
}

/* Card spacing and layout */
.custom-styles .card-wrapper {
    width: 100%;
}

.custom-styles .card-body {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    padding: 10px; /* Ridotto il padding per una card più compatta */
}

/* Separazione tra pulsanti per dispositivi mobili */
@media (max-width: 768px) {
    .custom-styles .card-body {
        flex-direction: column; /* Colonne per schermi piccoli */
        text-align: center;
    }
    .custom-styles .card-title {
        font-size: 1.1rem; /* Font size ridotto sui dispositivi piccoli */
    }
}

/* Uniformità per altezza e larghezza dei pulsanti nella stessa riga */
.custom-styles .col-md-6, .custom-styles .col-xl-4 {
    display: flex;
    align-items: stretch; /* Allinea i contenuti in modo che tutti i pulsanti siano della stessa altezza */
}

.custom-styles .card {
    display: flex;
    flex-direction: column;
    height: 100%;
    min-height: 120px; /* Altezza minima uniforme per tutti i pulsanti */
}

.custom-styles .card-body {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    padding: 10px;
    flex-grow: 1;
}

/* Icona SVG in Accesso rapido */
.custom-styles .card-title svg.icon-white {
    fill: #000000 !important; /* Imposta il colore dell'icona a nero di default */
    position: absolute;
    top: 10px; /* Posiziona l'icona a 10px dalla parte superiore della card */
    right: 10px; /* Posiziona l'icona a 10px dal lato destro della card */
    width: 20px; /* Imposta una dimensione fissa */
    height: 20px; /* Imposta una dimensione fissa */
    z-index: 2; /* Assicurati che l'icona sia sopra gli altri elementi */
    transition: transform 0.3s ease, fill 0.3s ease; /* Transizione per l'effetto di colore e ingrandimento */
}

/* Aggiungi un effetto hover (opzionale) per l'icona */
.custom-styles .card-title svg.icon-white:hover {
    transform: scale(1.1); /* Leggera ingrandimento dell'icona al passaggio del mouse */
    fill: #f0f0f0 !important; /* Cambia colore dell'icona a bianco quando si passa sopra */
}


/* Modifica il margine della card-body se necessario per fare spazio all'icona */
.custom-styles .card-body {
    padding: 15px; /* Aggiungi padding per fare spazio all'icona */
}

/* Aggiungi spazio tra l'icona e il contenuto (opzionale) */
.custom-styles .card-title {
    margin-right: 40px; /* Spazio a destra per fare posto all'icona */
}



@media (max-width: 768px) {
    /* Card-body diventa riga con avatar a sinistra */
    .custom-styles .card-body {
        flex-direction: row;      /* avatar a sinistra, testo a destra */
        align-items: center;      /* centrato verticalmente */
        text-align: left;
        padding: 8px 12px;        /* regola padding se necessario */
    }

    /* Contenitore testo (titolo + descrizione) */
    .custom-styles .flex-grow-1 {
        display: flex;
        flex-direction: column;
        justify-content: center;  /* centra verticalmente rispetto all’avatar */
    }

    /* Avatar a sinistra con spazio a destra */
    .custom-styles .avatar {
        margin-right: 12px;       /* spazio tra avatar e testo */
        flex-shrink: 0;           /* non ridurre l’avatar */
    }

    /* Titolo e descrizione */
    .custom-styles .card-title {
        margin-bottom: 4px;
        margin-right: 0;
    }

    .custom-styles .description {
        margin: 0;
    }
}


   
</style>
