<?php
global $argomento_full, $count;
$argomenti_evidenza = array();
for ($i = 1; $i <= 3; $i++) {  // Modifico il ciclo per arrivare fino a 3 (numero massimo di schede evidenziate)
    $argomento_option = dci_get_option('argomenti_evidenziati_' . $i, 'homepage');
    
    // Verifica se l'opzione è un array e contiene almeno un elemento, e se il primo elemento è un array
    $argomento = (is_array($argomento_option) && isset($argomento_option[0]) && is_array($argomento_option[0])) ? $argomento_option[0] : null;
    
    // riempimento array argomenti evidenza solo se esiste un argomento valido per quella posizione
    if ($argomento) {
        $argomenti_evidenza[$i] = $argomento;       
    }
}
$altri_argomenti = dci_get_option('argomenti_altri','homepage');

// Logica gestione sfondo sezione Argomenti in Evidenza
$check_immagini = dci_get_option('ch_show_sfondo_argomenti','homepage');

$img_default = get_template_directory_uri() . '/assets/img/bg_placeholder-blu.png';
$img_ricavata = dci_get_option('immagine-argomenti','homepage');
$img = (isset($img_ricavata) && !empty($img_ricavata) && $img_ricavata !== null) ? $img_ricavata : $img_default; // Imposta l'immagine ricavata o il placeholder se non è valida
$has_custom_bg = (isset($img_ricavata) && !empty($img_ricavata) && $img_ricavata !== null); // Variabile booleana per verificare se è stata impostata un'immagine personalizzata valida (diversa dal placeholder)
?>

<?php if (isset($argomenti_evidenza) && !empty($argomenti_evidenza)) { 
    // Verifico se mostrare o meno lo sfondo e se l'immagine è valida, altrimenti mostro la sezione senza sfondo ma con i contenuti evidenziati
   if(isset($check_immagini) && !empty($check_immagini) && $check_immagini === 'true' && $img != null) {?>
        <div class="it-hero-wrapper it-wrapped-container py-4 argomenti-evidenza-bg">
            <div class="container">
                <div class="row"> 
                    <h2 class="text-white title-xlarge mb-3">Argomenti in Evidenza</h2> 
                </div>
                <div>
                    <div class="card-wrapper card-teaser-wrapper card-teaser-wrapper-equal card-teaser-block-3">
                        <?php
                        if(is_array($argomenti_evidenza)) {
                            foreach ($argomenti_evidenza as $key => $argomento_full) {
                                $count = $key;
                                if ($argomento_full && isset($argomento_full['argomento_'.$count.'_argomento'])) {
                                    get_template_part("template-parts/home/scheda-argomento");
                                }
                            } 
                        } ?>
                    </div>
                </div>
            </div>
        </div>
    <?php } else{ ?>
        <div class="container">
            <div class="row"> 
                <h2 class="text-black title-xlarge mb-3">Argomenti in Evidenza</h2> 
            </div>
            <div>
                <div class="card-wrapper card-teaser-wrapper card-teaser-wrapper-equal card-teaser-block-3">
                    <?php
                    if(is_array($argomenti_evidenza)) {
                        foreach ($argomenti_evidenza as $key => $argomento_full) {
                            $count = $key;
                            if ($argomento_full && isset($argomento_full['argomento_'.$count.'_argomento'])) {
                                get_template_part("template-parts/home/scheda-argomento");
                            }
                        } 
                    } ?>
                </div>
            </div>
        </div>
    <?php } ?>
<?php }?>

<?php if (!empty($altri_argomenti) && is_array($altri_argomenti)) { ?>
    <div class="container">
        <div class="row pt-30">
            <div class="col-12">
                <h2 class="mb-2 title-xsmall-bold text u-grey-light">
                    <span class="text-black fs-6">Altri argomenti</span>
                </h2>
            </div>
        </div>

        <div class="row pt-30">
            <div class="col-12">
                <div class="button-group">
                    
                    <?php
                    // Ciclo per mostrare gli argomenti selezionati nelle opzioni "Altri argomenti"
                    foreach ($altri_argomenti as $i => $arg_id) {
                    
                        $argomento = get_term_by('term_taxonomy_id', (int) $arg_id);

                        // Verifica che l'argomento esista e non sia un errore
                        if (!$argomento || is_wp_error($argomento)) {
                            continue;
                        }

                        // Ottieni il link alla pagina dell'argomento
                        $url = get_term_link($argomento->term_id, 'argomenti');

                        // Verifica che il link sia valido
                        if (is_wp_error($url)) {
                            continue;
                        }
                        ?>
                        
                        <a href="<?php echo esc_url($url); ?>" class="btn-argomento" style="display: inline-flex; align-items: center; gap: 8px;">
                            <svg class="icon text-primary" style="width:20px; height:30px; display:inline-block; vertical-align:middle;" aria-hidden="true">
                                <use xlink:href="#it-bookmark"></use>
                            </svg>
                            <?php echo esc_html($argomento->name); ?>
                        </a>
                        
                    <?php } ?>

                    <a href="<?php echo esc_url(dci_get_template_page_url('page-templates/argomenti.php')); ?>" class="btn btn-primary" style="margin-left: 40px;">
                        Mostra tutti
                    </a>
                </div>
            </div>
        </div>
    </div>
    <br><br>
<?php } ?>

<style>
/* Sfondo sezione Argomenti in Evidenza (dinamico sul colore tema primaria) */
.argomenti-evidenza-bg {
    background-color: var(--bs-primary, #026e64);
    <?php if ($has_custom_bg) { ?>
    background-image: url('<?php echo esc_url($img); ?>');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    <?php } ?>
    position: relative;
    padding: 60px 0;
    overflow: hidden;
    isolation: isolate;
}

/* Layer geometrico simile al precedente sfondo immagine */
.argomenti-evidenza-bg::before {
    content: "";
    position: absolute;
    inset: 0;
    z-index: 1;
    background:
        <?php if (!$has_custom_bg) { ?>
        linear-gradient(160deg, rgba(255, 255, 255, 0.08) 8%, transparent 8%) 0 0 / 42% 100% no-repeat,
        linear-gradient(20deg, rgba(255, 255, 255, 0.09) 10%, transparent 10%) 100% 0 / 48% 100% no-repeat,
        <?php } ?>
        linear-gradient(120deg, rgba(0, 0, 0, 0.22), rgba(0, 0, 0, 0.1));
}

/* Contenuto sopra l'overlay */
.argomenti-evidenza-bg > * {
    position: relative;
    z-index: 2;
}


/* Allineamento degli elementi dentro la riga */
.container .row.pt-30 {
    text-align: left; 
}

/* Gruppo di pulsanti "Altri argomenti" */
.container .row.pt-30 .button-group {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    padding: 0;
    margin: 0;
    justify-content: flex-start;
}

/* Pulsanti "Altri argomenti" */
.container .row.pt-30 .button-group a.btn-argomento {
    display: inline-block;
    padding: 10px 16px;
    background-color: #ffffff;
    color: #333;
    font-size: 1rem;
    font-weight: 500;
    border: 2px solid #dcdcdc;
    margin-top: -5px;
    border-radius: 8px; 
    text-decoration: none;
    text-align: center;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

/* Hover pulsanti */
.container .row.pt-30 .button-group a.btn-argomento:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}

/* Titolo "Altri argomenti" */
.container .row.pt-30 h3.title-xsmall-bold.text.u-grey-light {
    font-size: 1.2rem;
    font-weight: 600;
    color: #333;
    letter-spacing: 0.5px;
    margin-top: 10px;
    margin-bottom: 10px;
}

/* Pulsante "Mostra tutti" */
.container .row.pt-30 .btn.btn-primary {
    margin-left: 0;
    display: inline-flex;
    align-items: center;
}

/* Media queries */
@media (max-width: 768px) {
    .container .row.pt-30 .title-xsmall-bold.text.u-grey-light {
        font-size: 1rem;
    }
    .container .row.pt-30 .button-group {
        gap: 12px;
    }
    .container .row.pt-30 .button-group a.btn-argomento {
        padding: 8px 14px;
        font-size: 0.9rem;
    }
}
</style>