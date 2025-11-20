<?php
/**
 * The template for displaying archive
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#archive
 *
 * @package Design_Comuni_Italia
 */

global $post, $with_shadow, $title, $tipo_notizia;
$search_url = esc_url( home_url( '/' ));
$taxonomy = get_queried_object();

if (!$title)
    $title = $taxonomy->name;
if (is_term_publicly_viewable($taxonomy)){
	$tipo_notizia =  $taxonomy;
}

get_header();

?>
    <main>
		<?php

			$with_shadow = true;
			get_template_part("template-parts/hero/hero");
			?>
			<?php get_template_part("template-parts/novita/evidenza"); ?>
			<?php get_template_part("template-parts/novita/tutte-novita"); ?>
			<?php get_template_part("template-parts/novita/argomenti"); ?>
			<?php get_template_part("template-parts/common/valuta-servizio"); ?>
			<?php get_template_part("template-parts/common/assistenza-contatti"); ?>

    </main>

<?php
get_footer();