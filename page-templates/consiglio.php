<?php
/* Template Name: Consiglio Comunale
 *
 * consiglio comunale template file
 *
 * @package Design_Comuni_Italia
 */

global $post, $with_shadow, $url_img;

// URL home e immagine di default
$search_url = esc_url( home_url( '/' ));
$url_img = "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRnDeHmoF5L5Fkqq-Ohesy45F6z-_ku02O2Fg&s";

// Controllo opzione portale
$check_portale = dci_get_option("ck_portaleElencoConsigliComunali");
if ($check_portale !== 'true') {
    wp_safe_redirect( home_url('/') );
    exit;
}

// Funzione per visualizzare le informazioni introduttive
function info() { ?>
    <section class="hero-img mb-20 mb-lg-50">
        <div class="container">
            <div class="row">
                <p class="text-justify">
                    <br>Benvenuti nella sezione dedicata al Consiglio Comunale, il luogo istituzionale in cui vengono discusse e deliberate le questioni di interesse pubblico per il nostro Comune.
                    Qui troverete informazioni aggiornate sulle sedute consiliari, compresi ordini del giorno, verbali, documenti ufficiali e allegati relativi alle decisioni adottate.
                </p>
                <p class="text-justify">
                    Questa pagina garantisce massima trasparenza e accessibilità ai cittadini, permettendo di seguire in maniera chiara le attività e le decisioni del Consiglio Comunale.
                    Potrete consultare facilmente i partecipanti alle sedute, le unità organizzative coinvolte e qualsiasi documento correlato alle riunioni.
                </p>
                <p class="text-justify">
                    La sezione viene aggiornata regolarmente e rappresenta uno strumento utile sia per i cittadini interessati alla partecipazione civica, sia per operatori e professionisti che necessitano di informazioni ufficiali per motivi amministrativi, legali o di studio.
                    Vi invitiamo a esplorare i contenuti disponibili e a fare riferimento ai documenti ufficiali per ogni approfondimento.
                </p>
            </div>
        </div>
    </section>
<?php }

get_header();
?>

<main>
    <?php while ( have_posts() ) : the_post(); 
        $with_shadow = true; 
    ?>
        <?php get_template_part("template-parts/hero/hero"); ?>
        <?php info(); ?>
        <?php get_template_part("template-parts/consigli/tutti"); ?>

        <?php 
        // Se il portale gestisce solo la Trasparenza in modo esterno, mostra solo la home
        $portalesoloperusoesterno = dci_get_option("ck_portalesoloperusoesterno");
        if ($portalesoloperusoesterno !== 'true') {                
            get_template_part("template-parts/common/valuta-servizio");
            get_template_part("template-parts/common/assistenza-contatti");
        } 
        ?>

    <?php endwhile; ?>
</main>

<?php get_footer(); ?>




