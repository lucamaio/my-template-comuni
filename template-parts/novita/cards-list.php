<?php 
global $post;

$description = dci_get_meta('descrizione_breve');
$arrdata = dci_get_data_pubblicazione_arr("data_pubblicazione", '_dci_notizia_', $post->ID);
$monthName = date_i18n('M', mktime(0, 0, 0, $arrdata[1], 10));
$img = dci_get_meta('immagine');
$tipo_terms = get_the_terms($post->ID, 'tipi_notizia');
$luogo_notizia = dci_get_meta("luoghi", '_dci_notizia_', $post->ID);

$argomenti = get_the_terms($post->ID, 'argomenti');

if ($tipo_terms && !is_wp_error($tipo_terms)) {
    $tipo = $tipo_terms[0];
} else {
    $tipo = null;
}
if ($img) {
?>
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card-wrapper border border-light rounded shadow-sm cmp-list-card-img cmp-list-card-img-hr">
            <div class="card no-after rounded">
            <div class="row g-2 g-md-0 flex-md-column">
                <div class="row g-2 g-md-0 flex-md-column">
                    <?php dci_get_img($img, 'rounded-top img-fluid img-responsive'); ?>
                </div>
                <div class="col-12 order-1 order-md-2">
                <div class="card-body">
                    <div class="category-top cmp-list-card-img__body">
                    <?php if ($tipo){ ?>  
                                <a class="category text-decoration-none" href="<?php echo get_term_link($tipo->term_id); ?>">
                                 <?php echo strtoupper($tipo->name); ?>
                             </a>
                    <?php } ?>
                    <span class="data"><?php echo $arrdata[0].' '.strtoupper($monthName).' '.$arrdata[2] ?></span>
                    </div>
                    <a class="text-decoration-none" href="<?php echo get_permalink(); ?>">
                          <h3 class="h5 card-title u-grey-light"><?php
                                        // Recupera il titolo della pagina
                    					$title = get_the_title();					
                    					// Se il titolo supera i 100 caratteri, lo tronca e aggiunge "..."
                    					if (strlen($title) > 100) {
                    					    $title = substr($title, 0, 97) . '...';
                    					}					
                    					// Controlla se il titolo contiene almeno 5 lettere maiuscole consecutive
                    					if (preg_match('/[A-Z]{5,}/', $title)) {
                    					    // Se sì, lo trasforma in minuscolo con la prima lettera maiuscola
                    					    $title = ucfirst(strtolower($title));
                    					}				

                                        echo $title;
                                       ?>
                                    </h3>
                    </a>
                              <p class="card-text">
                                    <?php 
                           
                    					$description1 = $description;			
                    					if (preg_match('/[A-Z]{5,}/', $description1)) {
                    					    // Se sì, lo trasforma in minuscolo con la prima lettera maiuscola
                    					    $description1 = ucfirst(strtolower($description1));
                    					}					
                    					// Aggiunge il titolo alla lista degli elementi
                                     echo $description1; ?>
                                    </p>
                                               <?php if (is_array($luogo_notizia) && count($luogo_notizia)) { ?>
                                <span class="data fw-normal"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-map-marker-alt" viewBox="0 0 16 16">
                                                <path d="M8 0C4.686 0 2 3.582 2 7c0 4.418 6 9 6 9s6-4.582 6-9c0-3.418-2.686-7-6-7zm0 11.5s-3-3.736-3-4.5c0-1.314 1.343-2.5 3-2.5s3 1.186 3 2.5c0 .764-3 4.5-3 4.5z"/>
                                            </svg> 
                                    <?php foreach ($luogo_notizia as $luogo_id) {
                                        $luogo_post = get_post($luogo_id);
                                        if ($luogo_post && !is_wp_error($luogo_post)) {
                                            echo '<a href="' . esc_url(get_permalink($luogo_post->ID)) . '" title="' . esc_attr($luogo_post->post_title) . '" class="card-text text-secondary text-uppercase pb-3">' . esc_html($luogo_post->post_title) . '</a> ';
                                        }
                                    } ?>
                                </span>
                            <?php } elseif (!empty($luogo_notizia)) { ?>
                                <span class="data fw-normal"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-map-marker-alt" viewBox="0 0 16 16">
                                    <path d="M8 0C4.686 0 2 3.582 2 7c0 4.418 6 9 6 9s6-4.582 6-9c0-3.418-2.686-7-6-7zm0 11.5s-3-3.736-3-4.5c0-1.314 1.343-2.5 3-2.5s3 1.186 3 2.5c0 .764-3 4.5-3 4.5z"/>
                                </svg> 
                                    <?php echo esc_html($luogo_notizia); ?>
                                </span>
                            <?php } ?>
                            
                            <?php if (is_array($argomenti) && count($argomenti)) { ?>
                                <div class="mt-2">
                                    <span class="subtitle-small">Argomenti:</span>
                                    <ul class="d-flex flex-wrap gap-1">
                                        <?php foreach ($argomenti as $argomento) { 
                                            if ($argomento && !is_wp_error($argomento)) { ?>
                                                <li>
                                                    <a href="<?php echo esc_url(get_term_link($argomento->term_id)); ?>" class="chip chip-simple">
                                                        <span class="chip-label"><?php echo esc_html($argomento->name); ?></span>
                                                    </a>
                                                </li>
                                            <?php } ?>
                                        <?php } ?>
                                    </ul>
                                </div>
                            <?php } ?>                   
                        <br>
                 </div>
                                          <a class="read-more ps-3"
                       href="<?php echo esc_url(get_permalink($post->ID)); ?>"
                       aria-label="Vai alla pagina <?php echo esc_attr($post->post_title); ?>" 
                       title="Vai alla pagina <?php echo esc_attr($post->post_title); ?>" 
                       style="display: inline-flex; align-items: center; margin-top: 30px;"> <!-- Ho aggiunto un margin-top per separare il tasto dagli argomenti -->
                        <span class="text">Vai alla pagina</span>
                        <svg class="icon">
                            <use xlink:href="#it-arrow-right"></use>
                        </svg>
                    </a>
                     </div>                  
                </div>                 
              </div>
            </div>

    </div>
<?php } else { ?>
    <div class="col-md-6 col-xl-4">
        <div class="card-wrapper border border-light rounded shadow-sm cmp-list-card-img cmp-list-card-img-hr">
            <div class="card no-after rounded">
                <div class="row g-2 g-md-0 flex-md-column">
                    <div class="col-12 order-1 order-md-2">
                        <div class="card-body card-img-none rounded-top">
                            <div class="category-top cmp-list-card-img__body">
                            <?php if ($tipo){?>
                              
                                <a class="category text-decoration-none" href="<?php echo get_term_link($tipo->term_id); ?>">
                                 <?php echo strtoupper($tipo->name); ?>
                             </a>
                            <?php } ?>
                                <span class="data"><?php echo $arrdata[0].' '.strtoupper($monthName).' '.$arrdata[2] ?></span>
                            </div>
                            <a class="text-decoration-none" href="<?php echo get_permalink(); ?>">                                
                                    <h3 class="h5 card-title u-grey-light"><?php
                                        // Recupera il titolo della pagina
                    					$title = get_the_title();					
                    					// Se il titolo supera i 100 caratteri, lo tronca e aggiunge "..."
                    					if (strlen($title) > 100) {
                    					    $title = substr($title, 0, 97) . '...';
                    					}					
                    					// Controlla se il titolo contiene almeno 5 lettere maiuscole consecutive
                    					if (preg_match('/[A-Z]{5,}/', $title)) {
                    					    // Se sì, lo trasforma in minuscolo con la prima lettera maiuscola
                    					    $title = ucfirst(strtolower($title));
                    					}				

                                        echo $title;
                                       ?>
                                    </h3>
                               
                            </a>
                            <p class="card-text d-none d-md-block">                                       
                                  <?php 
                                  // Recupera il titolo della pagina
                    					$description1 = $description;				
                    					// Controlla se il titolo contiene almeno 5 lettere maiuscole consecutive
                    					if (preg_match('/[A-Z]{5,}/', $description1)) {
                    					    // Se sì, lo trasforma in minuscolo con la prima lettera maiuscola
                    					    $description1 = ucfirst(strtolower($description1));
                    					}					
                    					// Aggiunge il titolo alla lista degli elementi
                                     echo $description1; ?>
                            </p>
                            
                            <?php if (is_array($luogo_notizia) && count($luogo_notizia)) { ?>
                                <span class="data fw-normal"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-map-marker-alt" viewBox="0 0 16 16">
                                                            <path d="M8 0C4.686 0 2 3.582 2 7c0 4.418 6 9 6 9s6-4.582 6-9c0-3.418-2.686-7-6-7zm0 11.5s-3-3.736-3-4.5c0-1.314 1.343-2.5 3-2.5s3 1.186 3 2.5c0 .764-3 4.5-3 4.5z"/>
                                                        </svg> 
                                    <?php foreach ($luogo_notizia as $luogo_id) {
                                        $luogo_post = get_post($luogo_id);
                                        if ($luogo_post && !is_wp_error($luogo_post)) {
                                            echo '<a href="' . esc_url(get_permalink($luogo_post->ID)) . '" title="' . esc_attr($luogo_post->post_title) . '" class="card-text text-secondary text-uppercase pb-3">' . esc_html($luogo_post->post_title) . '</a> ';
                                        }
                                    } ?>
                                </span>
                            <?php } elseif (!empty($luogo_notizia)) { ?>
                                <span class="data fw-normal"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-map-marker-alt" viewBox="0 0 16 16">
                                                <path d="M8 0C4.686 0 2 3.582 2 7c0 4.418 6 9 6 9s6-4.582 6-9c0-3.418-2.686-7-6-7zm0 11.5s-3-3.736-3-4.5c0-1.314 1.343-2.5 3-2.5s3 1.186 3 2.5c0 .764-3 4.5-3 4.5z"/>
                                            </svg> 
                                    <?php echo esc_html($luogo_notizia); ?>
                                </span>
                            <?php } ?>
                            
                            <?php if (is_array($argomenti) && count($argomenti)) { ?>
                                <div class="mt-2">
                                    <span class="subtitle-small">Argomenti:</span>
                                    <ul class="d-flex flex-wrap gap-1">
                                        <?php foreach ($argomenti as $argomento) { 
                                            if ($argomento && !is_wp_error($argomento)) { ?>
                                                <li>
                                                    <a href="<?php echo esc_url(get_term_link($argomento->term_id)); ?>" class="chip chip-simple">
                                                        <span class="chip-label"><?php echo esc_html($argomento->name); ?></span>
                                                    </a>
                                                </li>
                                            <?php } ?>
                                        <?php } ?>
                                    </ul>
                                </div>
                            <?php } ?>
                            <br>
                        </div>
                        <a class="read-more ps-3"
                       href="<?php echo esc_url(get_permalink($post->ID)); ?>"
                       aria-label="Vai alla pagina <?php echo esc_attr($post->post_title); ?>" 
                       title="Vai alla pagina <?php echo esc_attr($post->post_title); ?>" 
                       style="display: inline-flex; align-items: center; margin-top: 30px;"> <!-- Margine aggiunto -->
                        <span class="text">Vai alla pagina</span>
                        <svg class="icon">
                            <use xlink:href="#it-arrow-right"></use>
                        </svg>
                    </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php } ?>

