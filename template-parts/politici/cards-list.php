<?php 
global $posts;

$description = dci_get_meta('descrizione_breve');
$incarichi = dci_get_meta('incarichi'); // Assicurati che questo contenga tutti gli incarichi della persona

// Array per tenere traccia delle persone già visualizzate
$visualizzati = [];

foreach ($incarichi as $incarico) {
    $arrdata = explode( '-', dci_get_meta("data_inizio_incarico") );
    $tipo = get_the_terms($incarico, 'tipi_incarico')[0];
    $prefix = '_dci_incarico_';
    $nome_incarico = get_the_title($incarico);

    $monthName = date_i18n('M', mktime(0, 0, 0, $arrdata[1], 10));
    $img = dci_get_meta('foto'); // Foto associata all'incarico
    $persona_id = get_the_ID(); // ID della persona

    // Verifica se la persona è già stata visualizzata
    if (!in_array($persona_id, $visualizzati)) {
        // Aggiungi la persona all'array per evitare duplicazioni
        $visualizzati[] = $persona_id;

        // Inizia la visualizzazione della persona
        ?>

    
        <div class="col-md-6 col-xl-4">
            <div class="card-wrapper border border-light rounded shadow-sm cmp-list-card-img cmp-list-card-img-hr">
                <div class="card no-after rounded">
                    <div class="row g-2 g-md-0 flex-md-column">
                        <div class="col-12 order-1 order-md-2">
                            <div class="card-body card-img-none rounded-top">
                                <div class="category-top cmp-list-card-img__body">
                                    <span class="category cmp-list-card-img__body-heading-title underline">
                                    <a href="<?php echo get_permalink(); ?>" class="category cmp-list-card-img__body-heading-title underline"><?php echo the_title(); ?></a>
                                    </span>
                                    <span class="data"><?php echo $arrdata[0].' '.strtoupper($monthName).' '.$arrdata[2] ?></span>
                                </div>
                                   <h3 class="h5 card-title"></h3>
                                    <!-- Foto a sinistra -->
                                    <div class="col-4 col-md-3">
                                        <?php if ($img) { 
                                            dci_get_img($img, 'rounded img-fluid img-responsive foto-soft-style'); 
                                        } ?>
                                    </div>

                                    <!-- Elenco degli incarichi -->
                                    <div class="incarichi mt-2">
                                        <?php
                                        // Stampa tutti gli incarichi associati a questa persona
                                        foreach ($incarichi as $incarico) {
                                            $nome_incarico = get_the_title($incarico);
                                            echo '<span class="badge bg-primary badge-sm">' . $nome_incarico . '</span><br>'; // Ogni incarico su una nuova riga
                                        }
                                        ?>
                                    </div>
    
                                    <!-- Descrizione -->
                                    <p class="card-text mt-2" style="font-size: 0.9rem;">
                                        <?php echo $description; ?>
                                    </p>
    
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

      
      <?php 
    }
}
?>

<style>
/* Stile più leggero per la foto */
.foto-soft-style {
    border: 1px solid #ddd;              /* Bordo molto leggero */
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05); /* Ombra quasi impercettibile */
    padding: 2px;
    background-color: #fff;              /* Sfondo bianco per risalto se su sfondo grigio */
    border-radius: 6px;                  /* Angoli leggermente arrotondati */
}

</style>
