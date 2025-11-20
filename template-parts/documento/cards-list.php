<?php 
    global $post;

    $description = dci_get_meta('descrizione_breve');
    if ($post->post_type == 'documento_pubblico') {
        $ufficio_id = dci_get_meta('ufficio_responsabile', '_dci_documento_pubblico_', $post->ID)[0] ?? '';
        $ufficio = get_post($ufficio_id);

        $url_documento = dci_get_meta('url_documento', '_dci_documento_pubblico_', $post->ID) ?? '';
        $file_documento = dci_get_meta('file_documento', '_dci_documento_pubblico_', $post->ID);
        $link_documento = ($url_documento != '') ? $url_documento : $file_documento;
        //var_dump($link_documento);
    }

    if ($post->post_type == 'dataset') {
        $tipo = '';
        // Recupera il valore di "data_modifica"
        $data_modifica = dci_get_meta("data_modifica");
        
        // Verifica se il valore di "data_modifica" è valido e non vuoto
        if (!empty($data_modifica) && strtotime($data_modifica) !== false) {
            // Se è valido, esplodi la data
            $arrdata = explode('-', date('d-m-Y', strtotime($data_modifica)));
        } else {
            // Se non è valido, lascia arrdata vuoto
            $arrdata = [];
        }
    } else {
        // Recupera il valore di "data_protocollo"
        $data_protocollo = dci_get_meta("data_protocollo");
        
        // Assicurati che "data_protocollo" sia valido
        if (!empty($data_protocollo) && strtotime($data_protocollo) !== false) {
            $arrdata = explode('-', date('d-m-Y', strtotime($data_protocollo)));
        } else {
            $arrdata = [];
        }
        
        // Imposta tipo in base al termine associato
        $tipo = get_the_terms($post->term_id, 'tipi_documento')[0] ?? ''; // Usa null coalescing per evitare errori
    }



    // Verifica se la data modificata è disponibile e valida
   // $data_modifica = dci_get_meta("data_modifica");
   //  if (!empty($data_modifica)) {
   //     $arrdata = explode('-', date('d-m-Y', $data_modifica));
   // } else {
        // Usa la data di protocollo se la data di modifica non è valida
    //    $arrdata = explode('-', dci_get_meta("data_protocollo"));
   // }

    // Se $arrdata è vuoto, prendi la data di pubblicazione del post
    if (empty($arrdata) || count($arrdata) !== 3) {
        $arrdata = explode('-', get_the_date('d-m-Y', $post->ID));
    }

    // Verifica se arrdata ha un formato valido prima di usarlo
    if (count($arrdata) === 3) {
        $monthName = date_i18n('M', mktime(0, 0, 0, $arrdata[1], 10));
    } else {
        // In caso di formato non valido, usa la data di pubblicazione come fallback
        $arrdata = explode('-', get_the_date('d-m-Y', $post->ID));
        $monthName = date_i18n('M', mktime(0, 0, 0, $arrdata[1], 10));
    }

    $img = dci_get_meta('immagine');
    if ($img) {
?>

    <div class="col-md-6 col-xl-4">
        <div class="card-wrapper border border-light rounded shadow-sm cmp-list-card-img cmp-list-card-img-hr">
            <div class="card no-after rounded">
                <div class="row g-2 g-md-0 flex-md-column">
                    <div class="col-4 order-2 order-md-1">
                        <?php dci_get_img($img, 'rounded-top img-fluid img-responsive'); ?>
                    </div>
                    <div class="col-8 order-1 order-md-2">
                        <div class="card-body">
                            <div class="category-top cmp-list-card-img__body">
                                            <?php if (isset($tipo) && isset($tipo->term_id)) : ?>
                                            <a class="text-decoration-none" href="<?php echo get_term_link($tipo->term_id); ?>"><font color="black"  size="2"><?php echo isset($tipo->name) ? strtoupper($tipo->name) : 'DATASET'; ?></font></a>
                                            <?php else : ?>
                                            <a class="text-decoration-none" href="/dataset"><font color="black"  size="2"><?php echo isset($tipo->name) ? strtoupper($tipo->name) : 'DATASET'; ?></font></a>
                                            <?php endif; ?>
                                <span class="data"  size="1"><?php echo $arrdata[0].' '.$monthName.' '.$arrdata[2] ?></span>
                            </div>
                            <a class="text-decoration-none" href="<?php echo get_permalink(); ?>">
                                <h3 class="h5 card-title"><?php
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
                                       ?></h3>
                            </a>
                            <p class="card-text d-none d-md-block">
                            <?php 
                           
                           $description1 = $description;			
                           if (preg_match('/[A-Z]{5,}/', $description1)) {
                               // Se sì, lo trasforma in minuscolo con la prima lettera maiuscola
                               $description1 = ucfirst(strtolower($description1));
                           }					
                           // Aggiunge il titolo alla lista degli elementi
                        echo $description1; ?>
                            </p>
                            <hr style="margin-bottom: 40px; width: 200px; height: 1px; background-color: grey; border: none;">
                            <a class="read-more ps-3"
                               href="<?php echo esc_url(get_permalink($post->ID)); ?>"
                               aria-label="Vai alla pagina <?php echo esc_attr($post->post_title); ?>" 
                               title="Vai alla pagina <?php echo esc_attr($post->post_title); ?>" 
                               style="display: inline-flex; align-items: center; margin-top: 30px;">
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
    </div>

<?php } else { ?>
    <div class="col-md-6 col-xl-4">
        <div class="card-wrapper border border-light rounded shadow-sm cmp-list-card-img cmp-list-card-img-hr">
            <div class="card no-after rounded">
                <div class="row g-2 g-md-0 flex-md-column">
                    <div class="col-12 order-1 order-md-2">
                        <div class="card-body card-img-none rounded-top">
                            <div class="category-top cmp-list-card-img__body">
                                <span class="category cmp-list-card-img__body-heading-title underline">                                    
                                    <span class="text">
                                        <?php if (isset($tipo) && isset($tipo->term_id)) : ?>
                                                <a class="text-decoration-none" href="<?php echo get_term_link($tipo->term_id); ?>"><font color="black" size="2"><?php echo isset($tipo->name) ? strtoupper($tipo->name) : 'DATASET'; ?></font>
                                            </a>
                                            <?php else : ?>
                                             <a class="text-decoration-none" href="/dataset"><font color="black" size="2"><?php echo isset($tipo->name) ? strtoupper($tipo->name) : 'DATASET'; ?></font></a></font>
                                            <?php endif; ?>
                                        <font color="grey" size="1"><span class="data"><?php echo $arrdata[0].' '.strtoupper($monthName).' '.$arrdata[2] ?></span></font>
                                    </span>      
                                </span>
                              </div>
                            <a class="text-decoration-none" href="<?php echo get_permalink(); ?>">
                                <h3 class="h5 card-title"><?php
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
                                       ?></h3>
                            </a>
                            
                            <p class="card-text d-none d-md-block">                              
                            <?php 
                           
                           $description1 = $description;			
                           if (preg_match('/[A-Z]{5,}/', $description1)) {
                               // Se sì, lo trasforma in minuscolo con la prima lettera maiuscola
                               $description1 = ucfirst(strtolower($description1));
                           }					
                           // Aggiunge il titolo alla lista degli elementi
                        echo $description1; ?>
                            </p>
                            <hr style="margin-bottom: 40px; width: 200px; height: 1px; background-color: grey; border: none;">
                            <a class="read-more ps-3"
                               href="<?php echo esc_url(get_permalink($post->ID)); ?>"
                               aria-label="Vai alla pagina <?php echo esc_attr($post->post_title); ?>" 
                               title="Vai alla pagina <?php echo esc_attr($post->post_title); ?>" 
                               style="display: inline-flex; align-items: center; margin-top: 30px;">
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
    </div>
<?php } ?>
