<?php
/**
 * The template for displaying home
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Design_Comuni_Italia
 */
	
//Se il portale gestisce solo la nostra Trasparenza in modo esterno, indirizza all'home del comune.
$portalesoloperusoesterno = dci_get_option("ck_portalesoloperusoesterno");


if ($portalesoloperusoesterno==='true') {
    wp_redirect(dci_get_option("url_homesoloesterno"));
    exit;
}





get_header();


?>
	    
       <main id="main-container" class="main-container redbrown">

        <?php get_template_part("template-parts/home/carosello2"); ?>

        <h1 class="visually-hidden">
            <?php echo dci_get_option("nome_comune"); ?>
        </h1>
        <section id="head-section">
            <h2 class="visually-hidden">Contenuti in evidenza</h2>
            <?php
			$messages = dci_get_option( "messages", "home_messages" );
            if($messages && !empty($messages)) {
                get_template_part("template-parts/home/messages");
            }
		    ?>

            
            
	    <p></p>
            <?php get_template_part("template-parts/home/notizie"); ?>
            <?php get_template_part("template-parts/home/calendario"); ?>

        </section>


		   
        <section id="evidenza" class="evidence-section">
                <?php get_template_part("template-parts/home/argomenti"); ?>
            <div class="section py-5 pb-lg-80 px-lg-5 position-relative">
		        <?php get_template_part("template-parts/servizio/evidenza"); ?>
                <?php get_template_part("template-parts/home/siti","tematici"); ?>
            </div>
        </section>
		   
      
		   
        <section id="accesso-rapido" class="quick-access-section">

            <?php 
                $boxes = dci_get_option( "quickboxes", "accesso_rapido" );
                get_template_part("template-parts/home/accesso-rapido"); 
            ?>
        </section>
        
		<?php 
		    // Controlla se mostrare la galleria
            $mostra_gallery = dci_get_option('mostra_gallery', 'galleria');
            if ($mostra_gallery) {
                $stile_galleria = dci_get_option('stile_galleria','galleria') ?: null;
                if($stile_galleria === "solo-foto" || $stile_galleria ==="Solo foto"){
                     // Vecchia Galleria fotografica
                    get_template_part("template-parts/vivere-comune/galleria-foto");
                }else if($stile_galleria === "foto-gallery" || $stile_galleria ==="foto-gallery"){
                    // Nuova galleria
                    get_template_part("template-parts/galleria/home-gallery");
                }
		    }
		?>


        <?php get_template_part("template-parts/home/ricerca"); ?>

         <?php 
            $show_map = dci_get_option( "ck_show_map", "homepage" );
            if($show_map === 'true'){
                get_template_part("template-parts/vivere-comune/mappa");
            }

        ?>
        <?php get_template_part("template-parts/common/valuta-servizio"); ?>
        <?php get_template_part("template-parts/common/assistenza-contatti"); ?>
    </main>
<?php
get_footer();
?>