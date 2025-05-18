<?php
    global $uo_id, $with_border;
    $ufficio = get_post( $uo_id );

    $prefix = '_dci_unita_organizzativa_';
    $img = dci_get_meta('immagine', $prefix, $uo_id);
    $punti_contatto = dci_get_meta('contatti', $prefix, $uo_id);
    $sede_principale = dci_get_meta("sede_principale", $prefix, $uo_id);
    $descrizione_breve = dci_get_meta("descrizione_breve", $prefix, $uo_id);
    $responsabili = dci_get_meta("responsabile", $prefix, $uo_id);
    $responsabile = $responsabili[0];


    $nome_incarico = '';

    $prefix = '_dci_punto_contatto_';
    $contatti = array();
    foreach ($punti_contatto as $pc_id) {
        $contatto = dci_get_full_punto_contatto($pc_id);
        array_push($contatti, $contatto);
    }
    $other_contacts = array(
        'linkedin',
        'skype',
        'telegram',
        'twitter',
        'whatsapp'
    );
    
    if(!$with_border) {
?>

<div class="card card-teaser shadow-sm p-4s rounded border border-light flex-nowrap">
    <svg class="icon">
        <use xlink:href="#it-pa"></use>
    </svg>
    <div class="card-body">
	    
        <h5 class="card-title">
            <a class="text-decoration-none" href="<?php echo get_permalink($ufficio->ID); ?>" data-element="service-area">
                <?php echo $ufficio->post_title; ?>
            </a>
	   <p class="subtitle-small mb-3" data-element="service-description">
		<?php echo $descrizione_breve ?>
           </p>
        </h5>
         <h6 class="border-top border-light mt-2 mb-0 pt-2"></h6>	
        <small>Contatti Area:</small><p></p>  	    
        <div class="card-text">          
                    <?php if ($sede_principale) { ?>
                            <section class="it-page-section">
                              <div class="field--name-field-ita-indirizzo">                                  
                                <p>
                                    <a target="_blank" 
                                    aria-label="Apri la mappa  <?php echo dci_get_meta("indirizzo", '_dci_luogo_', $sede_principale); ?>"
				    title="Indirizzo <?php echo dci_get_meta("indirizzo", '_dci_luogo_', $sede_principale); ?>"
                                    href="https://www.google.com/maps/search/?api=1&amp;query=<?php echo dci_get_meta("indirizzo", '_dci_luogo_', $sede_principale); ?>">						 
					<svg class="icon" viewBox="0 0 24 24"><g>
					<g>
						<path d="M17.7,5.3C16,2.2,12,1.1,8.9,2.8s-4.3,5.7-2.5,8.8L12,22l5.7-10.4c0.5-1,0.8-2,0.8-3.1S18.2,6.3,17.7,5.3z M16.8,11.1
							L12,19.9l-4.8-8.8c-0.5-0.8-0.7-1.7-0.7-2.7C6.5,5.4,9,3,12,3s5.5,2.5,5.5,5.5C17.5,9.4,17.3,10.3,16.8,11.1z"></path>
						<path d="M12,5c-1.9,0-3.5,1.6-3.5,3.5S10.1,12,12,12s3.5-1.6,3.5-3.5S13.9,5,12,5z M12,11c-1.4,0-2.5-1.1-2.5-2.5S10.6,6,12,6
							s2.5,1.1,2.5,2.5S13.4,11,12,11z"></path>
					</g>
					</g></svg><?php echo dci_get_meta("indirizzo", '_dci_luogo_', $sede_principale); ?>
                                    </a>
                              </p> 
                              </div>
                            </section>
                    <?php } ?> 
                 <?php foreach ($contatti as $full_contatto) { ?>
                        <div class="card-text mb-3">
                            <?php if (array_key_exists('indirizzo', $full_contatto) && is_array($full_contatto['indirizzo']) && count($full_contatto['indirizzo'])) {
                                echo '<div class="mb-3">';
                                foreach ($full_contatto['indirizzo'] as $value) {
                                    echo '<p>' .$value;
                                    if ($value) {
                                        echo $value;
                                    }
                                    echo '</p>';
                                }
                                echo '</div>';
                            } ?>                         
                            <?php if (array_key_exists('telefono', $full_contatto) && is_array($full_contatto['telefono']) && count($full_contatto['telefono'])) {
                                foreach ($full_contatto['telefono'] as $value) {
                            ?>
			             <p><svg class="icon">
			               <use xlink:href="#it-telephone"></use>
			              </svg>
                                        <a target="_blank" aria-label="contatta telefonicamente tramite il numero <?php echo $value; ?>" title="chiama <?php echo $value; ?>" href="tel:<?php echo $value; ?>">
                                            <?php echo $value; ?>
                                        </a>
                                    </p>
                            <?php
                                }
                            } ?>
	               <?php if (array_key_exists('url', $full_contatto) && is_array($full_contatto['url']) && count($full_contatto['url'])) {
                                foreach ($full_contatto['url'] as $value) { ?>
                                    <p>
                                        <a target="_blank" aria-label="scopri di pi첫 su <?php echo $value; ?> - link esterno - apertura nuova scheda" title="vai sul sito <?php echo $value; ?>" href="<?php echo $value; ?>">
                                           <?php echo $value; ?>
                                        </a>
                                    </p>
                            <?php }
                        } ?>

                       <?php if (array_key_exists('email', $full_contatto) && is_array($full_contatto['email']) && count($full_contatto['email'])) {
                                foreach ($full_contatto['email'] as $value) { ?>
                                    <p>
                                        <svg class="icon">
		                            <use xlink:href="#it-mail"></use>
		                        </svg> 
                                        <a target="_blank" aria-label="invia un'email <?php echo $value; ?>" title="invia un'email a  <?php echo $value; ?>" href="mailto:<?php echo $value; ?>">
                                             <?php echo $value; ?>
                                        </a>
                                    </p>
                            <?php }
                         } ?>	


                       <?php if (array_key_exists('pec', $full_contatto) && is_array($full_contatto['pec']) && count($full_contatto['pec'])) {
                                foreach ($full_contatto['pec'] as $value) { ?>
                                    <p>
                                        <svg class="icon">
		                            <use xlink:href="#it-mail"></use>
		                        </svg> 
                                        <a target="_blank" aria-label="invia una pec a  <?php echo $value; ?>" title="invia una pec a  <?php echo $value; ?>" href="mailto:<?php echo $value; ?>">
                                             <?php echo $value; ?>
                                        </a>
                                    </p>
                            <?php }
                         } ?>	
	        <?php foreach ($other_contacts as $type) {
                        if ( isset($full_contatto[$type]) && is_array($full_contatto[$type]) && count ($full_contatto[$type]) ) {
                            foreach ($full_contatto[$type] as $value) {
                                echo '<p>'.$type.': '.$value.'</p>';
                            }
                        } 
                    } ?>
                        </div>
                    <?php } ?>
		      <?php if ( $responsabile ) {?>
		    	<h6 class="border-top border-light mt-2 mb-0 pt-2"></h6>	
	                <div id="contacts"><small>Responsabile Area:</small><p></p>   </div>     
			           <section class="it-page-section">                
                       
	                                <div class="row">
	                                    <div class="col-12 col-md-8 col-lg-6 mb-30">
	                                        <div class="cmp-card-latest-messages mb-3 mb-30">
	                                        	<div class="card card-bg px-4 pt-4 pb-4 rounded">
	                                                    <div class="card-header border-0 p-0">
	                                                             <a class="text-decoration-none title-xsmall-bold mb-2 category text-uppercase" href="#"><?php echo 
	                                                             $nome_incarico; ?></a>
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
	                        </section>
			    <?php } ?>
                </div>
    </div>
	
</div><p></p>
<?php } else { ?>
<div class="card card-teaser card-teaser-info rounded shadow-sm p-4 me-3">
            <svg class="icon">
                <use xlink:href="#it-pa"></use>
            </svg>
            <div class="card-body pe-3">
                <h3 class="card-title h5">
                    <a href="<?php echo get_permalink($ufficio->ID); ?>">
                        <?php echo $ufficio->post_title; ?>
                    </a>			
		<p class="subtitle-small mb-3" data-element="service-description">
			<?php echo $descrizione_breve ?>
                </p>
                </h5>
		<h6 class="border-top border-light mt-2 mb-0 pt-2"></h6>
		<small>Contatti Area:</small><p></p>  	
                <div class="card-text">
                    <?php if ($sede_principale) { ?>
                            <section class="it-page-section">
                              <div class="field--name-field-ita-indirizzo">                                  
                                <p>
                                    <a target="_blank" 
                                    aria-label="Apri la mappa  <?php echo dci_get_meta("indirizzo", '_dci_luogo_', $sede_principale); ?>"
				    title="Indirizzo <?php echo dci_get_meta("indirizzo", '_dci_luogo_', $sede_principale); ?>"
                                    href="https://www.google.com/maps/search/?api=1&amp;query=<?php echo dci_get_meta("indirizzo", '_dci_luogo_', $sede_principale); ?>">
					<svg class="icon" viewBox="0 0 24 24"><g>
					<g>
						<path d="M17.7,5.3C16,2.2,12,1.1,8.9,2.8s-4.3,5.7-2.5,8.8L12,22l5.7-10.4c0.5-1,0.8-2,0.8-3.1S18.2,6.3,17.7,5.3z M16.8,11.1
							L12,19.9l-4.8-8.8c-0.5-0.8-0.7-1.7-0.7-2.7C6.5,5.4,9,3,12,3s5.5,2.5,5.5,5.5C17.5,9.4,17.3,10.3,16.8,11.1z"></path>
						<path d="M12,5c-1.9,0-3.5,1.6-3.5,3.5S10.1,12,12,12s3.5-1.6,3.5-3.5S13.9,5,12,5z M12,11c-1.4,0-2.5-1.1-2.5-2.5S10.6,6,12,6
							s2.5,1.1,2.5,2.5S13.4,11,12,11z"></path>
					</g>
					</g></svg><?php echo dci_get_meta("indirizzo", '_dci_luogo_', $sede_principale); ?>
                                    </a>
                              </p> 
                              </div>
                            </section>
                    <?php } ?>       

                 <?php foreach ($contatti as $full_contatto) { ?>
                        <div class="card-text mb-3">
                            <?php if (array_key_exists('indirizzo', $full_contatto) && is_array($full_contatto['indirizzo']) && count($full_contatto['indirizzo'])) {
                                echo '<div class="mb-3">';
                                foreach ($full_contatto['indirizzo'] as $value) {
                                    echo '<p>' .$value;
                                    if ($value) {
                                        echo $value;
                                    }
                                    echo '</p>';
                                }
                                echo '</div>';
                            } ?>                         
                            <?php if (array_key_exists('telefono', $full_contatto) && is_array($full_contatto['telefono']) && count($full_contatto['telefono'])) {
                                foreach ($full_contatto['telefono'] as $value) {
                            ?>
			             <p><svg class="icon">
			               <use xlink:href="#it-telephone"></use>
			              </svg>
                                        <a target="_blank" aria-label="contatta telefonicamente tramite il numero <?php echo $value; ?>" title="chiama <?php echo $value; ?>" href="tel:<?php echo $value; ?>">
                                            <?php echo $value; ?>
                                        </a>
                                    </p>
                            <?php
                                }
                            } ?>
	               <?php if (array_key_exists('url', $full_contatto) && is_array($full_contatto['url']) && count($full_contatto['url'])) {
                                foreach ($full_contatto['url'] as $value) { ?>
                                    <p>
                                        <a target="_blank" aria-label="scopri di pi첫 su <?php echo $value; ?> - link esterno - apertura nuova scheda" title="vai sul sito <?php echo $value; ?>" href="<?php echo $value; ?>">
                                           <?php echo $value; ?>
                                        </a>
                                    </p>
                            <?php }
                        } ?>

                       <?php if (array_key_exists('email', $full_contatto) && is_array($full_contatto['email']) && count($full_contatto['email'])) {
                                foreach ($full_contatto['email'] as $value) { ?>
                                    <p>
                                        <svg class="icon">
		                            <use xlink:href="#it-mail"></use>
		                        </svg> 
                                        <a target="_blank" aria-label="invia un'email <?php echo $value; ?>" title="invia un'email a  <?php echo $value; ?>" href="mailto:<?php echo $value; ?>">
                                             <?php echo $value; ?>
                                        </a>
                                    </p>
                            <?php }
                         } ?>	


                       <?php if (array_key_exists('pec', $full_contatto) && is_array($full_contatto['pec']) && count($full_contatto['pec'])) {
                                foreach ($full_contatto['pec'] as $value) { ?>
                                    <p>
                                        <svg class="icon">
		                            <use xlink:href="#it-mail"></use>
		                        </svg> 
                                        <a target="_blank" aria-label="invia una pec a  <?php echo $value; ?>" title="invia una pec a  <?php echo $value; ?>" href="mailto:<?php echo $value; ?>">
                                             <?php echo $value; ?>
                                        </a>
                                    </p>
                            <?php }
                         } ?>	
	

                    
                    <?php foreach ($other_contacts as $type) {
                        if ( isset($full_contatto[$type]) && is_array($full_contatto[$type]) && count ($full_contatto[$type]) ) {
                            foreach ($full_contatto[$type] as $value) {
                                echo '<p>'.$type.': '.$value.'</p>';
                            }
                        } 
                    } ?>
                        </div>
                    <?php } ?>
	      <?php if ( $responsabile ) {?>
	    	<h6 class="border-top border-light mt-2 mb-0 pt-2"></h6>	
                <div id="contacts"><small>Responsabile Area:</small><p></p>   </div>     
		           <section class="it-page-section">                

                                <div class="row">
                                    <div class="col-12 col-md-8 col-lg-6 mb-30">
                                        <div class="cmp-card-latest-messages mb-3 mb-30">
                                        	<div class="card card-bg px-4 pt-4 pb-4 rounded">
                                                    <div class="card-header border-0 p-0">
                                                             <a class="text-decoration-none title-xsmall-bold mb-2 category text-uppercase" href="#"><?php echo 
                                                             $nome_incarico; ?></a>
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
                        </section>
		    <?php } ?>
               </div>
            </div>	 
    </div><p></p>
<?php } 
$with_border = false;
?>
