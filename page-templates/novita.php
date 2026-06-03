<?php
/* Template Name: Novità
 *
 * novita template file
 *
 * @package Design_Comuni_Italia
 */
global $post, $with_shadow;
$search_url = esc_url( home_url( '/' ));

get_header();
?>
	<main>
		<?php
		while ( have_posts() ) :
			the_post();
			
			$with_shadow = true;
			?>
			<?php get_template_part("template-parts/hero/hero"); ?>
			<?php dci_get_template_part_async("novita-evidenza"); ?>
			<?php dci_get_template_part_async("novita-tutte"); ?>
			<?php get_template_part("template-parts/novita/argomenti"); ?>
			<?php get_template_part("template-parts/common/valuta-servizio"); ?>
			<?php get_template_part("template-parts/common/assistenza-contatti"); ?>
		<?php 
			endwhile; // End of the loop.
		?>
	</main>

<?php
get_footer();



