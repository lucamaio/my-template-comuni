<?php
global $count, $scheda, $post;

$post_id = dci_get_option('notizia_evidenziata', 'homepage', true)[0] ?? null;

if ($post_id) {
    $post = get_post($post_id);
    $typePost = $post->post_type;
    $prefix = null;
    switch ($typePost) {
        case 'luogo':
            $prefix = '_dci_luogo_';
            break;
        case 'evento':
            $prefix = '_dci_evento_';
            break;
        case 'documento_pubblico':
            $prefix = '_dci_documento_pubblico_';
            break;
        case 'servizio':
            $prefix = '_dci_servizio_';
            break;
        case 'unita_organizzativa':
            $prefix = '_dci_unita_organizzativa_';
            break;
        case 'dataset':
            $prefix = '_dci_dataset_';
            break;
        case 'notizia':
            $prefix = '_dci_notizia_';
            break;
    }
    $img = dci_get_meta("immagine", $prefix, $post->ID);
    $arrdata = dci_get_data_pubblicazione_arr("data_pubblicazione", $prefix, $post->ID);
    $monthName = date_i18n('M', mktime(0, 0, 0, $arrdata[1], 10));
    $descrizione_breve = dci_get_meta("descrizione_breve", $prefix, $post->ID);
    $argomenti = dci_get_meta("argomenti", $prefix, $post->ID);
    $luogo_notizia = dci_get_meta("luoghi", $prefix, $post->ID);

    $hide_notizie_old = dci_get_option("ck_hide_notizie_old", "homepage");

    $tipo_terms = wp_get_post_terms($post->ID, 'tipi_notizia');
    if ($tipo_terms && !is_wp_error($tipo_terms)) {
        $tipo = $tipo_terms[0];  // Prendi il primo termine trovato
    } else {
        $tipo = null;  // Nessun termine trovato
    }
}

$schede = [];
for ($i = 1; $i <= 20; $i++) {
    $schede[] = dci_get_option("schede_evidenziate_$i", 'homepage', true)[0] ?? null;
}
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
            <?php if ($post_id) {
                get_template_part("template-parts/home/notizia_in_evidenza");
            } ?>

            <?php if (!empty(array_filter($schede))) { ?>
                <div class="py-4">
                    <div class="row g-4">
                        <?php
                        $count = 1;
                        $schede_mostrate=0;
                        foreach ($schede as $scheda) {
                            if ($scheda) {
                                if($schede_mostrate%6 ==0 && $schede_mostrate > 0 && $count > $schede_mostrate) {
                                    echo '$Multiplo: ' . $schede_mostrate . '<br>';
                                }
                                $post = get_post($scheda['scheda_' . $count . '_contenuto'][0]);
                                $post_id = $post->ID;
                                $typePost = $post->post_type;
                                $prefix = null;
                                switch ($typePost) {
                                    case 'luogo':
                                        $prefix = '_dci_luogo_';
                                        break;
                                    case 'evento':
                                        $prefix = '_dci_evento_';
                                        break;
                                    case 'documento_pubblico':
                                        $prefix = '_dci_documento_pubblico_';
                                        break;
                                    case 'servizio':
                                        $prefix = '_dci_servizio_';
                                        break;
                                    case 'unita_organizzativa':
                                        $prefix = '_dci_unita_organizzativa_';
                                        break;
                                    case 'dataset':
                                        $prefix = '_dci_dataset_';
                                        break;
                                    case 'notizia':
                                        $prefix = '_dci_notizia_';
                                        break;
                                }
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
                                if($dataPubblicazione == $dataScadenza && $date!=null){
                                    $dataScadenza=null;
                                }
                                // echo((empty($dataScadenza)));
                                // var_dump($date);
                                // echo 'date: ' . $date . '<br>';
                                // echo 'Data di pubblicazione:  ' . $dataPubblicazione->format('d/m/Y') . ' ';
                                // echo 'Data di scadenza: ' . ($dataScadenza ? $dataScadenza->format('d/m/Y') : 'Nessuna scadenza') . '<br>';
                                // echo 'is empty: ' . (empty($dataScadenza) ? 'true' : 'false') . '<br>';
                                // echo 'is null: ' . ($dataScadenza == null ? 'true' : 'false') . '<br>';
                                // echo 'date is null: ' . ($date == null ? 'true' : 'false') . '<br>';
                                // echo 'date is empty: ' . (empty($date) ? 'true' : 'false') . '<br>';

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

                                if ($mostra_scheda) {
                                    $schede_mostrate++;
                        ?>
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

                    <div class="row my-4 justify-content-md-center">
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
            <?php } ?>
        </div>
    </div>
</section>
