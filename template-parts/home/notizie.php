<?php
global $count, $scheda, $post, $numero_notizie_evidenziate;

$post_id = dci_get_option('notizia_evidenziata', 'homepage', true)[0] ?? null;
$hide_notizie_old = dci_get_option("ck_hide_notizie_old", "homepage");
$notizie_automatiche = dci_get_option("ck_notizie_automatico", "homepage");
$notizie_home= dci_get_option("numero_notizie_home", "homepage");


$notizie_evidenziate_automatiche = dci_get_option("ck_notizie_evidenza", "homepage") ?? null;
$numero_notizie_evidenziate = dci_get_option("numero_notizie_evidenza", "homepage") ?? 0;

if ($post_id) {
    $post = get_post($post_id);
    $typePost = $post->post_type;
    $prefix = '_dci_' . $typePost . '_';  // Prefix per le funzioni di recupero dei metadati

    $img = dci_get_meta("immagine", $prefix, $post->ID);
    $arrdata = dci_get_data_pubblicazione_arr("data_pubblicazione", $prefix, $post->ID);
    $monthName = date_i18n('M', mktime(0, 0, 0, $arrdata[1], 10));
    $descrizione_breve = dci_get_meta("descrizione_breve", $prefix, $post->ID);
    $argomenti = dci_get_meta("argomenti", $prefix, $post->ID);
    $luogo_notizia = dci_get_meta("luoghi", $prefix, $post->ID);

    $tipo_terms = wp_get_post_terms($post->ID, 'tipi_notizia');
    if ($tipo_terms && !is_wp_error($tipo_terms)) {
        $tipo = $tipo_terms[0];  // Prendi il primo termine trovato
    } else {
        $tipo = null;  // Nessun termine trovato
    }
}

$schede = [];

if($notizie_automatiche ==='false'){
    for ($i = 1; $i <= 12; $i++) {
        $schede[] = dci_get_option("schede_evidenziate_$i", 'homepage', true)[0] ?? null;
    }
}

$visualizza_pulsante=false; // Aggiungo questa variabile per verificare se devo visualizzare il pulsante 'Tutte le novità'
?>

<style>
    /* CSS mirato alla sezione delle schede evidenziate */
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
            <?php if ($post_id && ($notizie_evidenziate_automatiche === 'false' || !isset($notizie_evidenziate_automatiche) || empty($notizie_evidenziate_automatiche))) {
                get_template_part("template-parts/home/notizia_in_evidenza");
            } else if($notizie_evidenziate_automatiche === 'true' && $numero_notizie_evidenziate > 0 ){
                get_template_part("template-parts/home/notizie-auto-evidenza");
            }?>

            <?php if (!empty(array_filter($schede))) { ?>
                <div class="py-4">
                    <div class="row g-4">
                        <?php
                        $count = 1;
                        $visualizza_pulsante=true;
                        foreach ($schede as $scheda) {
                            if ($scheda) {

                                $post = get_post($scheda['scheda_' . $count . '_contenuto'][0]);
                                $post_id = $post->ID;
                                $typePost = $post->post_type;
                                $prefix = '_dci_' . $typePost . '_';

                                // Salvo le date per la pubblicazione e scadenza

                                $date = dci_get_meta("data_pubblicazione", $prefix, $post->ID);

                                $arrdata = dci_get_data_pubblicazione_arr("data_pubblicazione", $prefix, $post->ID);
                                $dayPubblicazione = $arrdata[0];
                                $monthPubblicazione = $arrdata[1];
                                $yearPubblicazione = $arrdata[2];

                                if (strlen($yearPubblicazione) == 2) {
                                    $yearPubblicazione = '20' . $yearPubblicazione;
                                }
                                $dataPubblicazione = DateTime::createFromFormat('d/m/Y', "$dayPubblicazione/$monthPubblicazione/$yearPubblicazione");

                                $arrdataFine = dci_get_data_pubblicazione_arr("data_scadenza", $prefix, $post->ID);
                                $dayScadenza = $arrdataFine[0];
                                $monthScadenza = $arrdataFine[1];
                                $yearScadenza = $arrdataFine[2];

                                if (strlen($yearScadenza) == 2) {
                                    $yearScadenza = '20' . $yearScadenza;
                                }
                                $dataScadenza = DateTime::createFromFormat('d/m/Y', "$dayScadenza/$monthScadenza/$yearScadenza");
                                // Controllo se la data di pubblicazione è uguale alla data di scadenza
                                if ($dataPubblicazione == $dataScadenza && $date != null) {
                                    $dataScadenza = null;
                                }

                                // recupero la data odierna e effettuo il controllo per mostrare la scheda

                                $oggi = new DateTime();
                                $mostra_scheda = false;
                                if ($typePost === 'notizia') {
                                    if ($hide_notizie_old === 'true') {
                                        if (empty($dataScadenza) || ($dataScadenza >= $oggi && $dataScadenza != null)) {
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
                        <?php
                                }
                            }
                            ++$count;
                        }
                        ?>
                    </div>
            <?php } else if($notizie_automatiche ==='true' && $notizie_home >0){ 
                 $visualizza_pulsante=true;
                 ?>
                    <div class="py-4">
                        <div class="row g-4">
                        <?php get_template_part("template-parts/home/notizie-auto"); ?>
                        </div>
                    </div>
            <?php }
            ?>
            <?php if($visualizza_pulsante){?>
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
                </div>  
            <?php }?>
        </div>
    </div>
</section>
