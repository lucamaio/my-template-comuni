<?php
/* Template Name: Amministrazione Traparente
 *
 * Amministrazione Traparente template file
 *
 * @package Design_Comuni_Italia
 */
global $post, $with_shadow;
$search_url = esc_url( home_url( '/' ));


function info(){?>
<section class="hero-img mb-20 mb-lg-50">
    <div class="container">
        <div class="row">
            <p class="text-justify">Benvenuti nella sezione Amministrazione Trasparente.</p>
            <p class="text-justify">
                Qui troverai tutte le informazioni relative all'organizzazione e all'attività della nostra amministrazione,
                rese pubbliche nel rispetto del Decreto Legislativo 33/2013 (e successive modifiche) in materia di trasparenza.
            </p>
            <p class="text-justify">
                Crediamo fermamente nel valore della trasparenza come strumento fondamentale per garantire l'integrità, 
                l'efficienza e la responsabilità della pubblica amministrazione. Per questo, ci impegniamo a rendere ogni aspetto della nostra gestione chiaro,
                accessibile e facilmente consultabile da tutti i cittadini.
            </p>
        </div>
    </div>
</section>


<?php }

get_header();
?>
	<main>
		<?php
		while ( have_posts() ) :
			the_post();
			
			$with_shadow = true;
			?>
			<?php get_template_part("template-parts/progetti/hero"); ?>
                        <?php info();?>
			<?php get_template_part("template-parts/amministrazione-trasparente/categorie"); ?>
			<?php get_template_part("template-parts/common/valuta-servizio"); ?>
			<?php get_template_part("template-parts/common/assistenza-contatti"); ?>
		<?php 
			endwhile; // End of the loop.
		?>
	</main>

<?php
get_footer();?>

