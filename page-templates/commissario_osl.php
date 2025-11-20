<?php
/* Template Name: Commissario-OSL
 *
 * Commissario template file
 *
 * @package Design_Comuni_Italia
 */
global $post, $with_shadow;
$search_url = esc_url( home_url( '/' ));

function info(){?>


<?php }

get_header();
?>
	<main>
		<?php
		while ( have_posts() ) :
			the_post();
			
			$with_shadow = true;
			?>
			<?php  get_template_part("template-parts/commissario_osl/hero"); ?>
                        <?php info();?>
			<?php  get_template_part("template-parts/commissario_osl/categorie"); ?>
			<?php  get_template_part("template-parts/commissario_osl/tutti"); ?>
			<?php get_template_part("template-parts/common/valuta-servizio"); ?>
			<?php get_template_part("template-parts/common/assistenza-contatti"); ?>
		<?php 
			endwhile; // End of the loop.
		?>
	</main>

<?php
get_footer();
