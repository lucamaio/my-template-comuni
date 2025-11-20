<?php
/* Template Name: bandi-di-gara
 *
 * Bandi di Gara template file
 *
 * @package Design_Comuni_Italia
 */
global $post, $with_shadow;
$search_url = esc_url( home_url( '/' ));

$tipi_documento = get_terms( array(
    'taxonomy' => 'tipi_documento',
    'hide_empty' => false,
) );

get_header();

?>


	<main>
		<?php
		while ( have_posts() ) :
			the_post();
			
			?>
			<?php 
				$with_shadow = true;
				get_template_part("template-parts/hero/hero"); 
			?>
			
			<?php get_template_part("template-parts/bandi-di-gara/tutti-bandi"); ?>

			<?php get_template_part("template-parts/common/valuta-servizio"); ?>
			<?php get_template_part("template-parts/common/assistenza-contatti"); ?>
							
		<?php 
			endwhile; // End of the loop.
		?>
	</main>

<?php
get_footer();
