<?php
global $count, $scheda, $post, $numero_notizie_evidenziate;

// Recupero opzioni
$notizia_evidenziata_option = dci_get_option('notizia_evidenziata', 'homepage', true);
$post_id = (is_array($notizia_evidenziata_option) && isset($notizia_evidenziata_option[0])) ? $notizia_evidenziata_option[0] : null;

$hide_notizie_old = dci_get_option("ck_hide_notizie_old", "homepage");
$notizie_automatiche = dci_get_option("ck_notizie_automatico", "homepage");
$notizie_home = dci_get_option("numero_notizie_home", "homepage");

$notizie_evidenziate_automatiche = dci_get_option("ck_notizie_evidenza", "homepage") ?? null;
$numero_notizie_evidenziate = dci_get_option("numero_notizie_evidenza", "homepage") ?? 0;

// Recupero post evidenziato
$post = (!empty($post_id)) ? get_post($post_id) : null;

// Se esiste un post evidenziato, recupero i suoi meta dati
if ($post) {
    $typePost = $post->post_type;
    $prefix = '_dci_' . $typePost . '_';

    $img = dci_get_meta("immagine", $prefix, $post->ID);

    // Recupero data pubblicazione e formattazione del mese
    $arrdata = dci_get_data_pubblicazione_arr("data_pubblicazione", $prefix, $post->ID);
    if (is_array($arrdata) && isset($arrdata[1])) {
        $monthName = date_i18n('M', mktime(0, 0, 0, $arrdata[1], 10));
    }

    $descrizione_breve = dci_get_meta("descrizione_breve", $prefix, $post->ID);
    $argomenti = dci_get_meta("argomenti", $prefix, $post->ID);
    $luogo_notizia = dci_get_meta("luoghi", $prefix, $post->ID);

    // Recupero tipo notizia
    $tipo_terms = wp_get_post_terms($post->ID, 'tipi_notizia');
    $tipo = (!empty($tipo_terms) && !is_wp_error($tipo_terms)) ? $tipo_terms[0] : null;
}

// Schede manuali
$schede = [];
if ($notizie_automatiche === 'false') {
    for ($i = 1; $i <= 12; $i++) {
        $scheda_option = dci_get_option("schede_evidenziate_$i", 'homepage', true);
        $schede[] = (is_array($scheda_option) && isset($scheda_option[0])) ? $scheda_option[0] : null;
    }
}

$visualizza_pulsante = false; // Questa variabile servirà per decidere se mostrare il pulsante "Tutte le novità"
?>

<style>
#notizie .card-teaser-wrapper {
    max-width: 98%;
    margin: 0 auto;
}
@media (max-width: 768px) {
    #notizie .card-teaser-wrapper .card {
        max-width: 100%;
        flex: 0 0 100%;
    }
}
</style>

<section id="notizie" aria-describedby="novita-in-evidenza">
    <div class="section-content">
        <div class="container">

        <?php 
            // Logica per la visualizzazione della notizia in evidenza 
            if (!empty($post_id) && ($notizie_evidenziate_automatiche === 'false' || empty($notizie_evidenziate_automatiche))) {
                // Logica per visualizzare la notizia in evidenza manuale 'standard'
                get_template_part("template-parts/home/notizia_in_evidenza"); 
            } 
            elseif ($notizie_evidenziate_automatiche === 'true' && $numero_notizie_evidenziate > 0) {
                // Logica per visualizzare le notizie in evidenza automatiche
                get_template_part("template-parts/home/notizie-auto-evidenza");
            } 

        // Logica per la visualizzazione delle schede evidenziate manuali
        if (!empty($schede) && count(array_filter($schede)) > 0) { ?>
            <div class="py-4">
                <div class="row g-4">

                <?php
                $count = 1;
                $visualizza_pulsante = true;

                // Ciclo attraverso le schede manuali
                foreach ($schede as $scheda) {

                    // verifico che la scheda non sia vuota
                    if (empty($scheda)) {
                        // Se la scheda è vuota, passo alla successiva
                        $count++;
                        continue;
                    }

                    // Recupero il post associato alla scheda
                    $key = 'scheda_' . $count . '_contenuto';

                    // Verifico che la chiave esista e che non sia vuota
                    if (empty($scheda[$key][0])) {
                        $count++;
                        continue;
                    }

                    // Recupero il post associato alla scheda
                    $post = get_post($scheda[$key][0]);

                    // Se il post non esiste, passo alla scheda successiva
                    if (!$post) {
                        $count++;
                        continue;
                    }

                    // Recupero il tipo di post e il prefisso per i meta
                    $post_id = $post->ID;

                    $typePost = $post->post_type ?? '';
                    // Se il tipo di post è vuoto, passo alla scheda successiva
                    if(empty($typePost)) {
                        $count++;
                        continue;
                    }

                    $prefix = '_dci_' . $typePost . '_';

                    // DATA PUBBLICAZIONE
                    $arrdata = dci_get_data_pubblicazione_arr("data_pubblicazione", $prefix, $post->ID);

                    if (!is_array($arrdata) || count($arrdata) < 3) {
                        $count++;
                        continue;
                    }

                    $dayPubblicazione = $arrdata[0];
                    $monthPubblicazione = $arrdata[1];
                    $yearPubblicazione = $arrdata[2];

                    if (strlen($yearPubblicazione) == 2) {
                        $yearPubblicazione = '20' . $yearPubblicazione;
                    }

                    $dataPubblicazione = DateTime::createFromFormat('d/m/Y', "$dayPubblicazione/$monthPubblicazione/$yearPubblicazione");

                    // DATA SCADENZA
                    $arrdataFine = dci_get_data_pubblicazione_arr("data_scadenza", $prefix, $post->ID);

                    if (is_array($arrdataFine) && count($arrdataFine) >= 3) {

                        $dayScadenza = $arrdataFine[0];
                        $monthScadenza = $arrdataFine[1];
                        $yearScadenza = $arrdataFine[2];

                        if (strlen($yearScadenza) == 2) {
                            $yearScadenza = '20' . $yearScadenza;
                        }

                        $dataScadenza = DateTime::createFromFormat('d/m/Y', "$dayScadenza/$monthScadenza/$yearScadenza");

                    } else {
                        $dataScadenza = null;
                    }

                    // Controllo se la data di pubblicazione è uguale alla data di scadenza
                    if ($dataPubblicazione == $dataScadenza && $dataPubblicazione != null) {
                        $dataScadenza = null;
                    }

                    // LOGICA VISIBILITÀ
                    /*
                    Mostro la scheda se:
                    - Il tipo di post è diverso da "notizia"
                    - Il tipo di post è "notizia" e l'opzione "nascondi notizie vecchie" è disabilitata
                    - Il tipo di post è "notizia", l'opzione "nascondi notizie vecchie" è abilitata e la data di scadenza è vuota o successiva alla data odierna
                    */
                    $oggi = new DateTime();
                    $mostra_scheda = false;

                    // Controllo se il tipo di post è "notizia" e applico la logica in base all'opzione "nascondi notizie vecchie"
                    if ($typePost === 'notizia') {
                        // Se l'opzione "nascondi notizie vecchie" è abilitata, mostro la scheda solo se la data di scadenza è vuota o successiva alla data odierna
                        if ($hide_notizie_old === 'true') {
                            if (empty($dataScadenza) || $dataScadenza >= $oggi) {
                                $mostra_scheda = true;
                            }
                        } else {
                            $mostra_scheda = true;
                        }
                    } else {
                        $mostra_scheda = true;
                    }

                    // Se la scheda è da mostrare, procedo a visualizzarla
                    if ($mostra_scheda) { ?>
                        <div class="col-12 col-md-6 col-lg-4">
                            <?php get_template_part("template-parts/home/scheda-evidenza"); ?>
                        </div>
                    <?php }

                    // passo alla scheda successiva incrementando il contatore
                    $count++;
                }
                ?>
                </div>
            </div>
    <?php } 
    elseif ($notizie_automatiche === 'true' && $notizie_home > 0) { 
        // Logica per la visualizzazione delle notizie automatiche quando non ci sono schede manuali
        $visualizza_pulsante = true; ?>
        <div class="py-4">
            <div class="row g-4">
                <?php 
                    // Richiamo la logica per visualizzare le notizie automatiche
                    get_template_part("template-parts/home/notizie-auto"); 
                ?>
            </div>
        </div>
    <?php } ?>

    <?php 
    // Verifico se devo mostrare il pulsante "Tutte le novità". Lo mostro se è stata visualizzata almeno una scheda manuale o se sono state visualizzate notizie automatiche (ovvero se $visualizza_pulsante è true)
    if ($visualizza_pulsante) { ?>

    <div class="row my-2 justify-content-md-center">
        <a class="read-more pb-3" href="<?php echo dci_get_template_page_url("page-templates/novita.php"); ?>">
            <button type="button" class="btn btn-outline-primary">
            Tutte le novità
            <svg class="icon">
                <use xlink:href="#it-arrow-right"></use>
            </svg>
            </button>
        </a>
    </div>
    <?php } ?>
    </div>
</div>
</section>