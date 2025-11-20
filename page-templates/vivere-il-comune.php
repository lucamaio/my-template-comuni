<?php
/* Template Name: Vivere il comune
 *
 * Vivere il comune template file
 *
 * @package Design_Comuni_Italia
 */
global $post;
get_header();

?>
	<main>
		<?php
		while ( have_posts() ) :
			the_post();
			
			$img = dci_get_option('immagine', 'vivi');
			$didascalia = dci_get_option('didascalia', 'vivi');
			$data_element = 'data-element="page-name"';
			?>
			<?php get_template_part("template-parts/hero/hero"); ?>	
			<section class="hero-img mb-20 mb-lg-50">
				<div class="container">
					<div class="row">
				<?php if($img != null) { ?>
				<section class="it-hero-small-size cmp-hero-img-small">
					<div class="img-responsive-wrapper">
						<div class="img-responsive">
							<div class="img-wrapper">
								<?php dci_get_img($img); ?>
							</div>
						</div>
					</div>
				</section>
				<?php } ?>
						<p class="title-big cmp-hero-img-big__description">
							<?php echo $didascalia; ?>
						</p>
					</div>
				</div>
			</section>
			<?php get_template_part("template-parts/vivere-comune/eventi"); ?>
			<?php get_template_part("template-parts/vivere-comune/luoghi"); ?>
			<?php get_template_part("template-parts/vivere-comune/argomenti"); ?>
			
			<?php 
				    // Controlla se mostrare la galleria
				$mostra_gallery_vivereilcomune = dci_get_option('mostra_gallery_vivereilcomune', 'galleria');
				if ($mostra_gallery_vivereilcomune) {
				$stile_galleria = dci_get_option('stile_galleria','galleria') ?: null;
				if($stile_galleria === "solo-foto" || $stile_galleria ==="Solo foto"){
					// Vecchia Galleria fotografica
					get_template_part("template-parts/vivere-comune/galleria-foto");
				}else if($stile_galleria === "foto-gallery" || $stile_galleria ==="foto-gallery"){
					// Nuova galleria
					get_template_part("template-parts/galleria/home-gallery");
				}
		    }?>

			<?php get_template_part("template-parts/vivere-comune/mappa"); ?>
			<?php get_template_part("template-parts/common/valuta-servizio"); ?>
			<?php get_template_part("template-parts/common/assistenza-contatti"); ?>
							
		<?php 
			endwhile; // End of the loop.
		?>
	</main>

<?php
get_footer();
