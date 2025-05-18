<?php
/* Template Name: note-legali
 *
 * note legali template file
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
			?>
			<?php get_template_part("template-parts/hero/hero"); ?>
			
			<div class="container">
    <div class="row mt-lg-50">
        <div class="col-12 col-lg-3 d-lg-block mb-4 d-none">
            <div class="cmp-navscroll sticky-top" aria-labelledby="accordion-title-one">
                <nav class="navbar it-navscroll-wrapper navbar-expand-lg" data-bs-navscroll="">
                    <div class="navbar-custom" id="navbarPageIndexNavProgress">
                        <div class="menu-wrapper">
                            <div class="link-list-wrapper">
                                <div class="accordion">
                                    <div class="accordion-item">
                                        <span class="accordion-header" id="accordion-title-one">
                                            <button class="accordion-button pb-10 px-3 text-uppercase" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-one" aria-expanded="true" aria-controls="collapse-one">
                                                Indice della pagina
                                                <svg role="img" class="icon icon-xs right">
                                                    <use href="https://comune.sedriano.mi.it/wp-content/themes/si-agid-pa-theme/node_modules/bootstrap-italia/dist/svg/sprites.svg#it-expand"></use>
                                                </svg>
                                            </button>
                                        </span>
                                        <div class="progress">
                                            <div class="progress-bar it-navscroll-progressbar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>
                                        </div>
                                        <div id="collapse-one" class="accordion-collapse collapse show" role="region" aria-labelledby="accordion-title-one">
                                            <div class="accordion-body">
                                                <ul class="link-list" data-element="page-index">
                                                    <li class="nav-item">
                                                        <a class="nav-link active" href="#licenza"><span>Licenza dei contenuti</span></a>
                                                    </li>
                                                                                                                                                                                                                                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
        <div class="col-12 col-lg-8 offset-lg-1">
            <div class="it-page-sections-container">
            <article id="cos-e" class="it-page-section anchor-offset">
 

  <h1 data-element="legal-notes-section">Licenza dei contenuti</h1>
			<p> Nella presente pagina sono riportate le Note legali relative all’utilizzo del Sito.</p>
			<p> Il presente documento potrà essere modificato in qualsiasi momento e senza preavviso.
				L’Utente è pertanto invitato a prendere regolarmente visione dell’ultima versione aggiornata delle stesse, disponibile
				e permanentemente accessibile da qualunque pagina del sito, cliccando sul collegamento “Note legali”.
			</p>
		  <h1>Copyright</h1>
		  <p> La visualizzazione, il download e qualunque utilizzo dei dati pubblicati sul presente tipo
			  comporta accettazione delle presenti note legali e delle condizioni della licenza con cui sono pubblicati.</p>
			<p data-element="legal-notes-body"> In applicazione del principio open by default ai sensi dell’articolo 52 del decreto legislativo 7 marzo 2005, n. 82 (CAD) e salvo dove diversamente specificato
				(compresi i contenuti incorporati di terzi), i dati, i documenti e le informazioni pubblicati sul sito sono rilasciati con licenza CC-BY 4.0.
			</p>
			<p data-element="legal-notes-body"> Gli utenti sono quindi liberi di condividere (riprodurre, distribuire, comunicare al pubblico, esporre in pubblico),
				rappresentare, eseguire e recitare questo materiale con qualsiasi mezzo e formato e modificare (trasformare il materiale e utilizzarlo per opere
				derivate) per qualsiasi fine, anche commerciale con il solo onere di attribuzione, senza apporre restrizioni aggiuntive.</p>
		  <h1>Utilizzo del sito e download</h1>
			<p> Tutti i documenti pubblicati sul presente sito sono conformi e corrispondenti agli atti originali.</p>
			<p> In nessun caso il Comune può essere ritenuto responsabile dei danni di qualsiasi natura causati direttamente o indirettamente dall'accesso al sito,
				dall'incapacità o impossibilità di accedervi.
			</p>
			<p> Il Comune, inoltre, non potrà essere ritenuto in alcun modo responsabile per i servizi di connettività utilizzati dagli Utenti per l’accesso al Portale.
			</p>
			<p> I documenti presenti in questo sito per lo scaricamento (download) quali ad esempio documentazione tecnica, documentazione normativa, software ecc., salvo diversa
				indicazione, sono liberamente e gratuitamente disponibili, in caso contrario viene prodotto un avviso come premessa nell'uso degli stessi.
			</p>
		  <h1>Accesso a siti esterni collegati</h1>
			<p> I collegamenti a siti esterni, indicati nel presente sito, sono forniti come semplice servizio agli utenti, con esclusione di ogni responsabilità sulla correttezza
				e sulla completezza dell’insieme dei collegamenti indicati.
			</p>
			<p> L’indicazione dei collegamenti non implica da parte del Comune alcun tipo di approvazione o condivisione di responsabilità in relazione alla legittimità,
				alla completezza e alla correttezza delle informazioni contenute nei siti indicati.
			</p>
		  <h2>Privacy</h2>
			<p> La privacy degli utenti è molto importante. Per questo motivo è stata predisposta un’apposita Informativa sulla Privacy che l’Utente, utilizzando il presente sito, accetta.
			</p>
		    
	    
	    </article>
                
                
                
                
                
                
                <article id="contatti" class="it-page-section anchor-offset mt-5">
                                                        </article>

                <section class="it-page-section">
                <div class="row variable-gutters">
                            <div class="col-lg-12">
                                <?php get_template_part( "template-parts/single/bottom" ); ?>
                            </div><!-- /col-lg-9 -->
                        </div><!
                </section>
            </div>
        </div>
    </div>
</div>


			<?php get_template_part("template-parts/common/valuta-servizio"); ?>
			<?php get_template_part("template-parts/common/assistenza-contatti"); ?>
							
		<?php 
			endwhile; // End of the loop.
		?>
	</main>

<?php
get_footer();



