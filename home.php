<?php
/**
 * The template for displaying home
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Design_Comuni_Italia
 */
get_header();
?>
	    
       <main id="main-container" class="main-container redbrown">
       <?php
            $immagine = dci_get_option("immagine", "homepage");
            if ($immagine != null) { ?>
            <div class="bg-image">
                <?php dci_get_img($immagine, 'immagine-home'); ?>
            </div>
        <?php } ?>


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
            <div class="section py-5 pb-lg-80 px-lg-5 position-relative">
                <?php get_template_part("template-parts/home/argomenti"); ?>
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
		?>  
		<?php get_template_part("template-parts/vivere-comune/galleria-foto"); ?>
		<?php 
		    } // Fine controllo se mostrare la galleria 
		?>


        <?php get_template_part("template-parts/home/ricerca"); ?>
        <?php get_template_part("template-parts/common/valuta-servizio"); ?>
        <?php get_template_part("template-parts/common/assistenza-contatti"); ?>
    </main>
<?php
get_footer();
?>
<style>
    .bg-image img {
        width: 100%;
        height: 450px;
        object-fit: cover;
        object-position: center;
    }
</style>
