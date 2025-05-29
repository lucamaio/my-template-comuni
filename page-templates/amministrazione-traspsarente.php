<?php
/* Template Name: Amministrazione Trasparente
 *
 * Amministrazione Traparente template file
 *
 * @package Design_Comuni_Italia
 */
global $post, $with_shadow;
$search_url = esc_url( home_url( '/' ));
$link_amministrazione=dci_get_option("link_ammtrasparente");

if(isset($link_amministrazione) && !empty($link_amministrazione) && $link_amministrazione!=null ) {
    header("Location: $link_amministrazione");
     exit;
}

function info(){?>
<section class="hero-img mb-20 mb-lg-50">
    <div class="container">
        <div class="row">
            <p class="text-justify">Benvenuti nella sezione dedicata all’Amministrazione Trasparente.</p>
            <p class="text-justify">
                Questa area è realizzata in ottemperanza al Decreto Legislativo 14 marzo 2013, n. 33, e successive modificazioni, 
                con l’obiettivo di garantire la massima accessibilità alle informazioni concernenti l’organizzazione, l’attività e l’utilizzo delle risorse pubbliche da parte dell’amministrazione.
            </p>
            <p class="text-justify">
                La trasparenza è un principio cardine del nostro operato, volto a promuovere la legalità, l’efficienza e la partecipazione attiva dei cittadini. 
                Tutti i dati e i documenti pubblicati sono consultabili in maniera chiara, ordinata e facilmente reperibile, nel rispetto dei criteri di completezza, aggiornamento e semplicità d’accesso.
            </p>
            <p class="text-justify">
                Invitiamo cittadini, professionisti e operatori del settore a utilizzare questo strumento come punto di riferimento per conoscere, controllare e interagire con l’attività amministrativa in modo consapevole e informato.
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

