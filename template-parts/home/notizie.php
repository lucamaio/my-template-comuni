<?php
global $count, $scheda;

$post_id = dci_get_option('notizia_evidenziata', 'homepage', true)[0] ?? null;
$prefix = '_dci_notizia_';

if ($post_id) {
    $post = get_post($post_id);
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
for ($i = 1; $i <= 20; $i++) {
    $schede[] = dci_get_option("schede_evidenziate_$i", 'homepage', true)[0] ?? null;
}
?>




<style>
    /* CSS mirato alla sezione delle schede evidenziate */
    #notizie .card-teaser-wrapper {
        max-width: 98%;
        /* Riduce la larghezza della sezione delle schede evidenziate */
        margin: 0 auto;
        /* Centra la sezione orizzontalmente */
    }

    /* Rendi le schede responsabili (più piccole su schermi piccoli) */
    @media (max-width: 768px) {
        #notizie .card-teaser-wrapper .card {
            max-width: 100%;
            /* Su schermi piccoli, ogni scheda occupa l'intera larghezza */
            flex: 0 0 100%;
        }
    }
</style>

<section id="notizie" aria-describedby="novita-in-evidenza">
    <div class="section-content">
        <div class="container">
            <!-- Richiamo la prima notizia in Evidenza -->
                      <?php  if ($post_id) {                                    
                              get_template_part("template-parts/home/notizia_in_evidenza");                  
                          }
                            ?>
            
            <?php if (!empty(array_filter($schede))) { ?>
                <div class="py-4">
                    <!-- Sezione delle schede -->
                    <div class="row mb-1">
                        <div class="card-wrapper px-0 <?php echo $overlapping; ?> card-teaser-wrapper card-teaser-wrapper-equal card-teaser-block-3">
                            <?php
                            $count = 1;
                            foreach ($schede as $scheda) {
                                if ($scheda) {
                                      get_template_part("template-parts/home/scheda-evidenza");
                                }
                                ++$count;
                            }
                            ?>
                        </div>                        
                    </div>
            
                    <!-- Mostra il pulsante solo se ci sono schede -->
                    <div class="row my-4 justify-content-md-center">
                        <a class="read-more pb-3" href="<?php echo dci_get_template_page_url("page-templates/novita.php"); ?>">
                            <button type="button" class="btn btn-outline-primary">Tutte le novità
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
