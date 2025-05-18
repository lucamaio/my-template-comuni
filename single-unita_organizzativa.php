<?php
/**
 *  Unità Organizzativa template file
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Design_Comuni_Italia
 */
global $uo_id, $file_url, $hide_arguments;

get_header();
?>
	<main>
	    <?php
	    while ( have_posts() ) :
	        the_post();
	        $user_can_view_post = dci_members_can_user_view_post(get_current_user_id(), $post->ID);
	
	        $prefix = '_dci_unita_organizzativa_';
	        $documenti = dci_get_meta("documenti", $prefix, $post->ID);
	        
	        // Initialize variables to avoid errors
            $img = dci_get_meta("immagine",$prefix, $post->ID) ?? '';
	        $sottotitolo = dci_get_meta("sottotitolo") ?? '';
	        $descrizione_breve = dci_get_meta("descrizione_breve") ?? '';
	        $competenze = dci_get_wysiwyg_field("competenze") ?? '';
	        $responsabili = dci_get_meta("responsabile") ?? [];
	        $responsabile = !empty($responsabili) ? $responsabili[0] : null;
	
	        $incarichi = dci_get_meta("incarichi", '_dci_persona_pubblica_', $responsabile) ?? [];
	        $incarico = !empty($incarichi) ? get_the_title($incarichi[0]) : '';
	
	        $tipologie = get_the_terms($post, 'tipi_unita_organizzativa') ?? [];
	        $tipologia = !empty($tipologie) ? $tipologie[0]->name : '';
	
	        $area_riferimento = dci_get_meta("unita_organizzativa_genitore") ?? '';
	        $persone = dci_get_meta("persone_struttura") ?? [];
	        $allegati = dci_get_meta("allegati", $prefix, $post->ID) ?? [];
	        $sede_principale = dci_get_meta("sede_principale") ?? '';
	        $servizi = dci_get_meta("elenco_servizi_offerti") ?? [];
	        $descrizione = dci_get_wysiwyg_field("descrizione_estesa") ?? '';
	        $punti_contatto = dci_get_meta("contatti") ?? [];
	
	        $contatti = [];
	        foreach ($punti_contatto as $pc_id) {
	            $contatto = dci_get_full_punto_contatto($pc_id);
	            if ($contatto) {
	                $contatti[] = $contatto;
	            }
	        }
	
	        $more_info = dci_get_wysiwyg_field("ulteriori_informazioni") ?? '';
	        $condizioni_servizio = dci_get_meta("condizioni_servizio") ?? '';
	        //$uo_id = intval(dci_get_meta("unita_responsabile")) ?? 0;
	        $argomenti = get_the_terms($post, 'argomenti') ?? [];
	        
	        // valori per metatag
	        // $categorie = get_the_terms($post, 'categorie_servizio') ?? [];
	        // $categoria_servizio = !empty($categorie) ? $categorie[0]->name : '';		    

	
	        // if (isset($canale_fisico_uffici[0])) {
	        //     $ufficio = get_post($canale_fisico_uffici[0]);
	        //     if ($ufficio) {
	        //         $luogo_id = dci_get_meta('sede_principale', '_dci_unita_organizzativa_', $ufficio->ID) ?? null;
	        //         $indirizzo = dci_get_meta('indirizzo', '_dci_luogo_', $luogo_id) ?? '';
	        //         $quartiere = dci_get_meta('quartiere', '_dci_luogo_', $luogo_id) ?? '';
	        //         $cap = dci_get_meta('cap', '_dci_luogo_', $luogo_id) ?? '';
	        //     }
	        // }
	
	        // function convertToPlain($text) {
	        //     $text = str_replace(array("\r", "\n"), '', $text);
	        //     $text = str_replace('"', '\"', $text);
	        //     $text = str_replace('&nbsp;', ' ', $text);
	        //     return trim(strip_tags($text));
	        // }
	        ?>
	</main>

            <script type="application/ld+json" data-element="metatag">{
                    "name": "<?= $post->post_title; ?>",
                    "UO_Type": "<?= $tipologia; ?>",
            }</script>
<div class="container" id="main-container">
    <div class="row">
        <div class="col-12 col-lg-10">
            <?php get_template_part("template-parts/common/breadcrumb"); ?>
        </div>
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-lg-8 px-lg-4 py-lg-2">
            <div class="cmp-heading pb-3 pb-lg-4">
                <div class="row">
                    <div class="col-12">
                        <h1 class="title-xxxlarge text-start" data-element="service-title">
                            <?php the_title(); ?>
                        </h1>
                        
                        <p class="subtitle-small mb-3 text-start" data-element="service-description">
                            <?php echo $descrizione_breve; ?>
                        </p>
                        
                        <?php 
                        // Assicurati che le variabili siano definite prima di usarle
                        $canale_digitale_link = $canale_digitale_link ?? ''; // Imposta a stringa vuota se non è definita
                        $canale_digitale_label = $canale_digitale_label ?? 'Link'; // Fornisci un'etichetta di default
                        
                        if (!empty($canale_digitale_link)) { // Controlla se il link non è vuoto
                        ?>
                            <button type="button" class="btn btn-primary fw-bold" onclick="location.href='<?php echo esc_url($canale_digitale_link); ?>';">
                                <span class=""><?php echo esc_html($canale_digitale_label); ?></span>
                            </button>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Colonna per le azioni -->
        <div class="col-lg-3 offset-lg-1 mt-5 mt-lg-0">
            <?php
                $hide_arguments = true;
                get_template_part('template-parts/single/actions');
            ?>
        </div>
    </div>
    <hr class="d-none d-lg-block mt-2"/>
</div>


            <div class="container">
                <div class="row row-column-menu-left mt-4 mt-lg-80 pb-lg-80 pb-40">
                    <div class="col-12 col-lg-3 mb-4 border-col">
                        <div class="cmp-navscroll sticky-top" aria-labelledby="accordion-title-one">
                            <nav class="navbar it-navscroll-wrapper navbar-expand-lg" aria-label="Indice della pagina" data-bs-navscroll>
                                <div class="navbar-custom" id="navbarNavProgress">
                                    <div class="menu-wrapper">
                                        <div class="link-list-wrapper">
                                            <div class="accordion">
                                                <div class="accordion-item">
                                                    <span class="accordion-header" id="accordion-title-one">
                                                    
                                                <?php if ($img) { ?>
                                                    <style>
                                                        /* Stile più leggero per la foto */
                                                        .img-resized {
                                                            /* border: 1px solid #ddd;              Bordo molto leggero */
                                                            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05); /* Ombra quasi impercettibile */
                                                            padding: 2px;
                                                            background-color: #fff;              /* Sfondo bianco per risalto se su sfondo grigio */
                                                            border-radius: 8px;                  /* Angoli leggermente arrotondati */
                                                        }
                                                        
                                                        </style>
                                                    <section class="hero-img mb-20 mb-lg-50 aling-itmes-center">
                                                            <center>
                                                                <div class="img-wrapper">
                                                                    <!-- Applica la classe CSS img-resized per ridimensionare l'immagine -->
                                                                    <?php dci_get_img($img, 'rounded img-fluid img-responsive foto-soft-style img-resized'); ?>
                                                                </div>
                                                            </center>
                                               
                                                    </section>
                                                <?php } ?>
                                                        <button class="accordion-button pb-10 px-3 text-uppercase" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-one" aria-expanded="true" aria-controls="collapse-one">
                                                            Indice della pagina
                                                            <svg class="icon icon-xs right">
                                                                <use href="#it-expand"></use>
                                                            </svg>
                                                        </button>
                                                    </span>
                                                    <div class="progress">
                                                        <div class="progress-bar it-navscroll-progressbar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                    <div id="collapse-one" class="accordion-collapse collapse show" role="region" aria-labelledby="accordion-title-one">
                                                        <div class="accordion-body">
                                                            <ul class="link-list" data-element="page-index">
                                                                <?php if ($competenze) { ?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#competenze">
                                                                        <span class="title-medium">Competenze</span>
                                                                    </a>
                                                                </li>
                                                                <?php } ?>
                                                                <?php if ( $area_riferimento ) { ?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#uo_riferimento">
                                                                        <?php if($tipologie=="ufficio"){?>
                                                                            <span class="title-medium">Area di Riferimento</span>
                                                                        <?php }else{?>
                                                                            <span class="title-medium">Uffici collegati</span>
                                                                        <?php }?>
                                                                    </a>
                                                                </li>
                                                                <?php } ?>
                                                                <?php if ( is_array($responsabili)) { ?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#responsabile">
                                                                        <span class="title-medium">Responsabile</span>
                                                                    </a>
                                                                </li>
                                                                <?php } ?>
                                                                <?php if ( is_array($persone) ) { ?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#persone">
                                                                        <span class="title-medium">Persone</span>
                                                                    </a>
                                                                </li>
                                                                <?php } ?>
                                                                <?php if ( is_array($servizi) ) { ?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#servizi">
                                                                        <span class="title-medium">Servizi collegati</span>
                                                                    </a>
                                                                </li>
                                                                <?php } ?>
                                                                <?php if (isset($more_info)AND !empty($more_info)){ ?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#more-info">
                                                                        <span class="title-medium">Ulteriori informazioni</span>
                                                                    </a>
                                                                </li>
                                                                <?php } ?>
								  <?php 
									// Assicurati che le variabili siano definite prima di usarle
									$sede_principale = $sede_principale ?? null; // Imposta a null se non è definita
									$altre_sedi = $altre_sedi ?? null; // Imposta a null se non è definita
									
									if (!empty($sede_principale)) { ?>
									    <li class="nav-item">
									        <a class="nav-link" href="#sede">
									            <span class="title-medium">Sede principale</span>
									        </a>
									    </li>
									<?php } ?>
									
									<?php if (!empty($altre_sedi)) { ?>
									    <li class="nav-item">
									        <a class="nav-link" href="#altre-sedi">
									            <span class="title-medium">Altre sedi</span>
									        </a>
									    </li>
								<?php } ?>
                                                                <?php if ( $allegati ) { ?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#allegati">
                                                                        <span class="title-medium">Allegati</span>
                                                                    </a>
                                                                </li>
                                                                <?php } ?>
       
                                                                <?php if ( $contatti ) { ?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#contatti">
                                                                        <span class="title-medium">Contatti</span>
                                                                    </a>
                                                                </li>
                                                                <?php } ?>
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
                            <section class="it-page-section mb-30">
                                <h2 class="title-xxlarge mb-3" id="competenze">Competenze</h2>
                                <div class="richtext-wrapper lora">
                                    <?php echo $competenze ?>
                                </div>
                            </section>
                            <?php if ($tipologia) { ?>
                            <section class="it-page-section mb-30">
                                <h2 class="title-xxlarge mb-3" id="tipo_organizzazione">Tipologia di Organizzazione</h2>
                                <div class="richtext-wrapper lora"><?php echo $tipologia; ?></div>
                            </section>
                            <?php } ?>
				
				<?php if ($area_riferimento && is_array($area_riferimento) && count($area_riferimento) > 0) { ?>
				    <section class="it-page-section mb-30" id="uo_riferimento">
                        <?php if($tipologia=="ufficio"){
                            echo '<h2 class="title-xxlarge mb-3" id="costs">Area di Riferimento</h2>';
                            echo '<div class="richtext-wrapper lora">';
				            foreach ($area_riferimento as $uo_id) {
				                get_template_part("template-parts/unita-organizzativa/card-full");
				            } 
				            echo '</div>';
                        }else{
                            echo '<h2 class="title-xxlarge mb-3" id="costs">Uffici collegati</h2>';
                            echo '<div class="richtext-wrapper lora">';
                            echo '<div class="row">';
                            foreach ($area_riferimento as $uo_id) {
                                get_template_part("template-parts/unita-organizzativa/card-custom");
                            }
                            
                            echo '</div>'; 
                            echo '</div>'; 
                            
                        } ?>
				    </section>
				<?php } ?>


                            <section class="it-page-section" id="responsabile">
			      <?php if ( $responsabile ) {?>
                                <h2 class="mb-3" >Responsabile</h2>                                
                                <div class="row">
                                    <div class="col-12 col-md-8 col-lg-6 mb-30">
                                        <div class="cmp-card-latest-messages mb-3 mb-30">
                                        	<div class="card card-bg px-4 pt-4 pb-4 rounded">
							<div class="card-header border-0 p-0">
							    <?php 
							    // Assicurati che la variabile sia definita prima di usarla
							    $nome_incarico = $nome_incarico ?? ''; // Imposta a stringa vuota se non è definita
							
							    // Controlla se $nome_incarico non è vuoto prima di visualizzarlo
							    if (!empty($nome_incarico)) { ?>
							        <a class="text-decoration-none title-xsmall-bold mb-2 category text-uppercase" href="#">
							            <?php echo esc_html($nome_incarico); ?>
							        </a>
							    <?php } ?>
							</div>
                                                   <div class="card-body p-0 my-2">
                                                      <div class="card-content">
                                                        
                                                         <h4 class="h5"><a href="<?php echo get_permalink($responsabile); ?>"><?php echo dci_get_meta('nome', '_dci_persona_pubblica_', $responsabile); ?> <?php echo dci_get_meta('cognome', '_dci_persona_pubblica_', $responsabile); ?></a></h4>
                                                         <p class="text-paragraph"><?php echo dci_get_meta('descrizione_breve', '_dci_persona_pubblica_', $responsabile); ?></p>
                                                      </div>
                                                   </div>
                                                   <!-- /card-body -->
                                               </div>
                                        </div>
                                    </div>
                                </div>
                                <?php } ?>
                            </section>
                            <section class="it-page-section" id="persone">
                                <h2 class="mb-3">Persone</h2>
                                <div class="row">
                                      <?php 
                                    $with_border = true;
                                      get_template_part("template-parts/single/persone");
                                    ?>

                                    <br>
                                </div>
                            </section>
                            <?php  
if (strlen(dci_get_option('servizi_maggioli_url', 'servizi')) < 5) {                                     
    if ($servizi && is_array($servizi) && count($servizi) > 0) { ?>
        <article id="servizi" class="it-page-section anchor-offset mt-5">
            <h3>Servizi collegati</h3>
            <div class="row">
                <?php 
                $i = 0;
                foreach ($servizi as $servizio_id) { 
                    $servizio = get_post($servizio_id);
                    $with_border = true;
                ?>
                    <div class="col-12 col-md-6 mb-4">
                        <?php get_template_part("template-parts/servizio/card"); ?>
                    </div>
                    <?php 
                    $i++;
                    // Chiudo e riapro la riga ogni 2 card se necessario (opzionale con Bootstrap, utile se si desidera maggiore controllo)
                    if ($i % 2 == 0 && $i < count($servizi)) {
                        echo '</div><div class="row">';
                    }
                } ?>
            </div>
        </article>
<?php 
    } 
} 
?>

<?php if ( $more_info ) {  ?>
                            <section class="it-page-section mb-30">
                                <h2 class="title-xxlarge mb-3" id="more-info">Ulteriori informazioni</h2>
                                <div class="richtext-wrapper lora">
                                    <?php echo $more_info ?>
                                </div>
                            </section>
                            <?php }  ?>

				
                            <?php if ($sede_principale){ ?>
			    <p></p>
                            <section class="it-page-section" id="sede">
                                  <h3 class="mt-4">Sede principale</h3>
                                <div class="row">
                                    <div class="col-12 col-md-8 col-lg-6 mb-30">
                                        <div class="card-wrapper rounded h-auto mt-10">
                                            <div class="card card-teaser shadow-sm p-4s rounded border border-light flex-nowrap">
                                            <div class="card-body pe-3">
                                                <p class="card-title text-paragraph-regular-medium-semi mb-3">
                                                <a href="<?php echo get_permalink($sede_principale); ?>">
						    <?php echo get_the_title($sede_principale); ?>
						</a>
                                                </p>
                                                <div class="card-text">
                                                     <p><?php echo dci_get_meta("indirizzo", '_dci_luogo_', $sede_principale); ?></p>
                                                         <p><?php echo dci_get_meta("descrizione_breve", '_dci_luogo_', $sede_principale); ?></
                                                </div>
                                            </div>
                                        </div>
                                        </div>
                                    </div>                                    
                                </div>
                            </section>
                            <?php } ?>

   				
	                <?php if ($allegati && is_array($allegati) && count($allegati) > 0) { ?>
	                            <section id="allegati" class="it-page-section mb-4">
	                                <h2 class="h3 my-2">Documenti</h2>
	                                <div class="row">
	                                    <?php foreach ($allegati as $allegato_id) { ?>
	                                        <div class="col-md-6 col-sm-12 mb-3 card-wrapper">
	                                            <?php
	                                            $documento = get_post($allegato_id);
	                                            $with_border = true;
	                                            get_template_part("template-parts/documento/card"); ?>
	                                        </div>
	                                    <?php  } ?>
	                                </div>
	                            </section>
	                        <?php } ?>

				
                            <section class="it-page-section mb-30">
                                <h2 class="title-xxlarge mb-3" id="contatti">Contatti</h2>
                                <div class="richtext-wrapper lora">
					<?php foreach ($punti_contatto as $pc_id) {
	                                    get_template_part('template-parts/single/punto-contatto');
	                                } ?>   
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php get_template_part("template-parts/common/valuta-servizio"); ?>
        <?php get_template_part('template-parts/single/more-posts', 'carousel'); ?>
        <?php get_template_part("template-parts/common/assistenza-contatti"); ?>

        <?php
        endwhile; // End of the loop.
        ?>
    </main>
<?php
get_footer();

 ?>

