<?php
/* Template Name: Progetti
 *
 * progetti template file
 *
 * @package Design_Comuni_Italia
 */
global $post, $with_shadow;
$search_url = esc_url( home_url( '/' ));

function info(){?>
<section class="hero-img mb-20 mb-lg-50">
    <div class="container">
        <div class="row">
            <h2>Cos’è il PNRR</h2>
            <p class="text-justify">
                Il Piano Nazionale di Ripresa e Resilienza (PNRR) è lo strumento che traccia 
                gli obiettivi, le riforme e gli investimenti che l’Italia intende realizzare 
                grazie all’utilizzo dei fondi europei di Next Generation EU, per attenuare 
                l’impatto economico e sociale della pandemia e rendere l’Italia un Paese più 
                equo, verde e inclusivo, con un’economia più competitiva, dinamica e 
                innovativa.
            </p>
            <p class="text-justify">
                Il PNRR annovera tre priorità trasversali condivise a livello europeo 
                (digitalizzazione e innovazione, transizione ecologica e inclusione sociale) 
                e si sviluppa lungo 16 Componenti, raggruppate in 6 missioni:
            </p>
            <ul class="text-justify" style="list-style-type: disc; margin-left:30px; padding:10px">
                <li>Digitalizzazione, innovazione, competitività, cultura e turismo (M1)</li>
                <li>Rivoluzione verde e transizione ecologica (M2)</li>
                <li>Infrastrutture per una mobilità sostenibile (M3)</li>
                <li>Istruzione e ricerca (M4)</li>
                <li>Inclusione e coesione (M5)</li>
                <li>Salute (M6)</li>
                <li>REPowerEU (M7)</li>
            </ul>
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
			<?php get_template_part("template-parts/progetti/categorie"); ?>
			<?php get_template_part("template-parts/progetti/tutti"); ?>
			<?php get_template_part("template-parts/common/valuta-servizio"); ?>
			<?php get_template_part("template-parts/common/assistenza-contatti"); ?>
		<?php 
			endwhile; // End of the loop.
		?>
	</main>

<?php
get_footer();
