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
   <main> 
		<?php
            $medias= dci_get_option( "quickboxes", "multimedia" );
            get_template_part("template-parts/home/multimedia");
			
		    get_template_part("template-parts/common/valuta-servizio");
			get_template_part("template-parts/common/assistenza-contatti");
							
		?>
	</main>


 <?php
 get_footer();
 ?>
