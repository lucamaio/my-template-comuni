<?php
/* Template Name: Progetti
 *
 * progetti template file
 *
 * @package Design_Comuni_Italia
 */
global $post, $with_shadow, $url_img;
$search_url = esc_url( home_url( '/' ));
$url_img="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRnDeHmoF5L5Fkqq-Ohesy45F6z-_ku02O2Fg&s";
function info(){?>
<section class="hero-img mb-10 mb-lg-10">
    <div class="container">
        <div class="row">
            <h2>Attuazione delle misure del PNRR</h2>

            <p class="text-justify">
                Il Piano Nazionale di Ripresa e Resilienza (PNRR) è lo strumento attraverso cui 
                l’Italia utilizza le risorse del programma europeo 
                <a href="https://www.consilium.europa.eu/it/policies/eu-recovery-plan/" 
                   target="_blank" rel="noopener noreferrer" class="text-primary text-decoration-none">
                    Next Generation EU
                </a>, 
                finalizzato a sostenere la ripresa economica e sociale e a promuovere uno sviluppo 
                sostenibile, innovativo e inclusivo.
            </p>

            <p class="text-justify">
                Questa sezione è dedicata alla descrizione delle misure previste dal PNRR e al 
                monitoraggio dello stato di attuazione degli interventi finanziati, in un’ottica 
                di trasparenza amministrativa e di informazione verso i cittadini.
            </p>

            <p class="text-justify">
                Il Piano si articola lungo tre priorità trasversali condivise a livello europeo:
                <strong>digitalizzazione e innovazione</strong>, <strong>transizione ecologica</strong> 
                e <strong>inclusione sociale</strong>.  
                Le azioni sono organizzate in 6 Missioni principali, cui si aggiunge il capitolo 
                dedicato a <strong>REPowerEU</strong>.
            </p>

            <ul class="text-justify" style="list-style-type: disc; margin-left:30px; padding:10px">
                <li>Digitalizzazione, innovazione, competitività, cultura e turismo (Missione 1)</li>
                <li>Rivoluzione verde e transizione ecologica (Missione 2)</li>
                <li>Infrastrutture per una mobilità sostenibile (Missione 3)</li>
                <li>Istruzione e ricerca (Missione 4)</li>
                <li>Inclusione e coesione (Missione 5)</li>
                <li>Salute (Missione 6)</li>
                <li>REPowerEU</li>
            </ul>

            <p class="text-justify">
                Per approfondire il Piano e consultare documentazione ufficiale:
            </p>

            <ul class="text-justify" style="list-style-type: disc; margin-left:30px; padding:10px">
                <li>
                    <a href="https://www.italiadomani.gov.it" 
                       target="_blank" rel="noopener noreferrer" class="text-primary text-decoration-none">
                        Italia Domani – Portale ufficiale del PNRR
                    </a>
                </li>
                <li>
                    <a href="https://commission.europa.eu/strategy-and-policy/recovery-plan-europe_it" 
                       target="_blank" rel="noopener noreferrer" class="text-primary text-decoration-none">
                        Commissione Europea – Recovery Plan for Europe
                    </a>
                </li>
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
			<?php get_template_part("template-parts/single/hero-custom"); ?>
            <?php info();?>
			<?php get_template_part("template-parts/progetti/categorie"); ?>
			<?php get_template_part("template-parts/progetti/tutti"); ?>

		        <?php 
			//Se il portale gestisce solo la nostra Trasparenza in modo esterno, indirizza all'home del comune.
			$portalesoloperusoesterno = dci_get_option("ck_portalesoloperusoesterno");

			if ($portalesoloperusoesterno !== 'true') {				
			
			            get_template_part("template-parts/common/valuta-servizio");
			            get_template_part("template-parts/common/assistenza-contatti");
			 } ?>

		

		<?php 
			endwhile; // End of the loop.
		?>
	</main>

<?php
get_footer();


