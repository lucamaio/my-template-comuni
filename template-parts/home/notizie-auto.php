<?php
global $the_query, $load_posts, $load_card_type;

// Limite massimo hard per evitare query troppo grandi via parametro GET.
$requested_max_posts = isset($_GET['max_posts']) ? absint($_GET['max_posts']) : 0;
$load_posts = -1;
// $query = isset($_GET['search']) ? dci_removeslashes($_GET['search']) : null;
$hide_notizie_old = dci_get_option("ck_hide_notizie_old", "homepage");
$notizie_home= dci_get_option("numero_notizie_home", "homepage");
$notizie_home = max(1, (int) $notizie_home);

// Mantiene abbastanza elementi per popolare la sezione, ma senza esplodere il carico.
if ($hide_notizie_old === 'true') {
    $max_posts = ($requested_max_posts > 0) ? min($requested_max_posts, 300) : max($notizie_home * 8, 60);
} else {
    // Se non serve filtrare "notizie vecchie", basta caricare il numero richiesto.
    $max_posts = ($requested_max_posts > 0) ? min($requested_max_posts, 300) : $notizie_home;
}

$args = array(
    //'s'         => $query,
    'post_type' => 'notizia',
    'meta_type' => 'text_date_timestamp',
    'orderby'   => 'meta_value_num',
    'order'     => 'desc',
    'posts_per_page' => $max_posts,
    'no_found_rows' => true,
    'ignore_sticky_posts' => true,
);

$the_query = new WP_Query($args);
$posts = $the_query->posts;

//usort($posts, function($a, $b) {
 //   return dci_get_data_pubblicazione_ts("data_pubblicazione", '_dci_notizia_', $b->ID) - dci_get_data_pubblicazione_ts("data_pubblicazione", '_dci_notizia_', $a->ID);
//});

$count=0;
// var_dump($posts);
foreach ($posts as $post) {
    if($count >= $notizie_home){
        break;
    }

    $load_card_type = 'notizia';
    $prefix = "_dci_notizia_";
    $mostra_scheda = false;

    if($hide_notizie_old === 'true'){

        // Data pubblicazione
        $arrdata = dci_get_data_pubblicazione_arr("data_pubblicazione", $prefix, $post->ID);
        $dayPubblicazione = $arrdata[0];
        $monthPubblicazione = $arrdata[1];
        $yearPubblicazione = strlen($arrdata[2]) == 2 ? '20' . $arrdata[2] : $arrdata[2];
        $dataPubblicazione = DateTime::createFromFormat('d/m/Y', "$dayPubblicazione/$monthPubblicazione/$yearPubblicazione");

        // Data scadenza
        $arrdataFine = dci_get_data_pubblicazione_arr("data_scadenza", $prefix, $post->ID);
        $dayScadenza = $arrdataFine[0];
        $monthScadenza = $arrdataFine[1];
        $yearScadenza = strlen($arrdataFine[2]) == 2 ? '20' . $arrdataFine[2] : $arrdataFine[2];
        $dataScadenza = DateTime::createFromFormat('d/m/Y', "$dayScadenza/$monthScadenza/$yearScadenza");
        
        // post_date ufficiale WordPress
        $post_date_str = $post->post_date;
        $post_date=substr($post_date_str,0,10);
        $data_post = DateTime::createFromFormat('Y-m-d', $post_date);
        
        // Verifica uguaglianza tra pubblicazione e scadenza
        if (
            ($dataPubblicazione instanceof DateTime && $dataScadenza instanceof DateTime && $dataPubblicazione == $dataScadenza) ||
            ($data_post instanceof DateTime && $dataScadenza instanceof DateTime && $data_post == $dataScadenza)
        ) {
            
            $dataScadenza = null;
        }

        // Mostra la scheda?
        $oggi = new DateTime();

        if ($dataScadenza == null || $dataScadenza >= $oggi) {
            $mostra_scheda = true;
        }
    } else {
        $mostra_scheda = true;
    }
    
         $hide_home = dci_get_meta("hide_home", '_dci_notizia_', $post->ID);

             if ($mostra_scheda && ($hide_home == null || $hide_home!='on'))
               {  $count++;?>
                    <div class="col-12 col-md-6 col-lg-4">
                        <?php get_template_part("template-parts/home/scheda-evidenza"); ?>
                    </div>
               <?php
                  }
            }
            wp_reset_postdata();
            ?>
