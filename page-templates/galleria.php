<?php
/* Template Name: Galleria
 *
 
 */
global $post;
get_header();
$search_url = esc_url( home_url( '/' ));
?>
	<main>
		<?php
		while ( have_posts() ) :
			the_post();
			?>
            <?php
				get_template_part("template-parts/hero/hero");
				get_template_part("template-parts/galleria/tutte-gallerie");
				get_template_part("template-parts/galleria/categorie");
			    get_template_part("template-parts/common/valuta-servizio");
			    get_template_part("template-parts/common/assistenza-contatti");
			endwhile; // End of the loop.
		?>
	</main>
		
<?php
get_footer();
