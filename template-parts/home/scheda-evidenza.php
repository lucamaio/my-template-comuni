<?php
global $scheda, $count;

$post = get_post($scheda['scheda_'.$count.'_contenuto'][0]);
$img = dci_get_meta('immagine');
$descrizione_breve = dci_get_meta('descrizione_breve');
$icon = dci_get_post_type_icon_by_id($post->ID);
$page = get_page_by_path( dci_get_group($post->post_type) ); 
$argomenti = dci_get_meta("argomenti", '_dci_notizia_', $post->ID);
$luogo_notizia = dci_get_meta("luoghi", '_dci_notizia_', $post->ID); // Recupera il luogo della notizia
$arrdata = dci_get_data_pubblicazione_arr("data_pubblicazione", '_dci_notizia_', $post->ID);
$monthName = date_i18n('M', mktime(0, 0, 0, $arrdata[1], 10));
$page_macro_slug = dci_get_group($post->post_type);
$page_macro = get_page_by_path($page_macro_slug);




$post_type = get_post_type($post->ID);
// Recupera l'oggetto del tipo di post
$post_type_object = get_post_type_object($post_type);
// Recupera il nome della tipologia in forma leggibile
$post_type_label = $post_type_object->labels->singular_name; // Nome singolare della tipologia


// Inizializza variabili per tipo e URL
$tipo_name = '';
$url_tipo = '#';



// Se il post_type_label è uguale a "Servizio", sostituisci il nome del tipo con "Servizio"
if ($post_type_label == 'Servizio') {
    // Recupera i termini associati al post nel taxonomy 'categorie_servizio'
    $tipo_terms = get_the_terms($post->ID, 'categorie_servizio');
    if ($tipo_terms && !is_wp_error($tipo_terms)) {
        $tipo = $tipo_terms[0]; // Assegna il primo termine
        $tipo_name = 'Servizio'; // Imposta il nome del tipo direttamente come 'Servizio'
        $url_tipo = '/servizi-categoria/' . sanitize_title($tipo->name); // URL corretto per i servizi
    }
} elseif ($post_type_label == 'Luogo') {
    // Se il post_type_label è "Luogo", recupera i termini associati al post nel taxonomy 'tipi_luogo'
    $tipo_terms = get_the_terms($post->ID, 'tipi_luogo');
    if ($tipo_terms && !is_wp_error($tipo_terms)) {
        $tipo = $tipo_terms[0]; // Assegna il primo termine
        $tipo_name = 'Luogo'; // Imposta il nome del tipo direttamente come 'Luogo'
        $url_tipo = '/tipi-luogo/' . sanitize_title($tipo->name); // URL corretto per i luoghi
    }
} elseif ($post_type_label == 'Evento') {
    // Se il post_type_label è "Evento", recupera i termini associati al post nel taxonomy 'tipi_evento'
    $tipo_terms = get_the_terms($post->ID, 'tipi_evento');
    if ($tipo_terms && !is_wp_error($tipo_terms)) {
        $tipo = $tipo_terms[0]; // Assegna il primo termine
        $tipo_name = 'Evento'; // Imposta il nome del tipo direttamente come 'Evento'
        $url_tipo = '/vivere-il-comune/tipo-evento/' . sanitize_title($tipo->name); // URL corretto per gli eventi
    }
} elseif ($post_type_label == 'Documento Pubblico') {
    // Se il post_type_label è "Evento", recupera i termini associati al post nel taxonomy 'tipi_evento'
    $tipo_terms = get_the_terms($post->ID, 'tipi_documento');
    if ($tipo_terms && !is_wp_error($tipo_terms)) {
        $tipo = $tipo_terms[0]; // Assegna il primo termine
        $tipo_name = 'Documento Pubblico'; // Imposta il nome del tipo direttamente come 'Documento'
        $url_tipo = '/tipi_documento/' . sanitize_title($tipo->name); // URL corretto per gli eventi
    }    
} elseif ($post_type_label == 'Notizia') {
    // Se il post_type_label è "Notizia", recupera i termini associati al post nel taxonomy 'tipi_notizia'
    $tipo_terms = get_the_terms($post->ID, 'tipi_notizia');
    if ($tipo_terms && !is_wp_error($tipo_terms)) {
        $tipo = $tipo_terms[0]; // Assegna il primo termine
        $tipo_name = $tipo->name; // Usa il nome del tipo
        $url_tipo = '/tipi_notizia/' . sanitize_title($tipo->name); // URL corretto per le notizie
    } else {
        // Se non ci sono termini associati, assegna un URL di fallback
        $tipo = null;
        $url_tipo = '#';
    }
} else {
    // Se il post_type_label non è né "Servizio" né "Luogo" né "Evento" né "Notizia", imposta un URL di fallback per "Novità"
    $tipo_name = 'Novità'; // Imposta il nome del tipo direttamente come 'Novità'
    $url_tipo = '#'; // Imposta un URL di fallback
}

    

?>


<?php if ($img) { ?>
<div class="card card-teaser card-teaser-image card-flex no-after rounded shadow-sm border border-light mb-0">
    <div class="card-image-wrapper with-read-more">
        <div class="card-body p-3 u-grey-light">
            <div class="category-top">
                <span class="category title-xsmall-semi-bold fw-semibold">
                    <a href="<?php echo esc_url($url_tipo); ?>" class="category title-xsmall-semi-bold fw-semibold"><?php echo strtoupper(esc_html($tipo_name)); ?></a>
                </span>
                <?php if (is_array($arrdata) && count($arrdata)) { ?>
                    <span class="data fw-normal">
                        <?php echo esc_html($arrdata[0] . ' ' . $monthName . ' ' . $arrdata[2]); ?>
                    </span>          
                <?php } ?>
            </div>
            <?php
                // Controllo se il titolo contiene almeno 5 caratteri maiuscoli consecutivi
                if (preg_match('/[A-Z]{5,}/', $post->post_title)) {
                    echo  '<p class="card-title text-paragraph-medium u-grey-light">'.ucfirst(strtolower($post->post_title)).'</p>';
                } else {
                    echo '<p class="card-title text-paragraph-medium u-grey-light">'.$post->post_title.'</p>';
                }
                // Faccio lo stesso controllo per la descrizione
                if (preg_match('/[A-Z]{5,}/', $descrizione_breve)) {
                    echo  '<p class="text-paragraph-card u-grey-light m-0">'.ucfirst(strtolower($descrizione_breve)).'</p>';
                } else {
                    echo '<p class="text-paragraph-card u-grey-light m-0">'.$descrizione_breve.'</p>';
                }
                ?>
            <?php if (is_array($luogo_notizia) && count($luogo_notizia)) { ?><br><br>
            <span class="data fw-normal"><i class="fas fa-map-marker-alt"></i>  
                <?php 
                foreach ($luogo_notizia as $luogo_id) {
                    // Ottieni i dettagli del luogo
                    $luogo_post = get_post($luogo_id);
                    
                    if ($luogo_post && !is_wp_error($luogo_post)) {
                        // Stampa il nome del luogo come link
                        echo '<a href="' . esc_url(get_permalink($luogo_post->ID)) . '" title="' . esc_attr($luogo_post->post_title) . '" class="card-text text-secondary text-uppercase pb-3">' . esc_html($luogo_post->post_title) . '</a> ';
                    }
                }
                ?>
            </span>
          <?php } elseif (!empty($luogo_notizia)) { ?>
            <span class="data fw-normal"> | <i class="fas fa-map-marker-alt"></i>  
                <?php echo esc_html($luogo_notizia); ?>
            </span>
        <?php } ?>
           
            <?php 
            // Verifica se ci sono argomenti da visualizzare
            if (has_term('', 'argomenti', $post)) { 
            ?> <hr style="margin-bottom: 20px; width: 200px; height: 1px; background-color: grey; border: none;">
                <div class="card-body">Argomenti: 
                    <?php get_template_part("template-parts/common/badges-argomenti"); ?>
                </div>   
            <?php } ?>            
            <hr style="margin-bottom: 40px; width: 200px; height: 0px; background-color: grey; border: none;">

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
        <div class="card-image card-image-rounded pb-5">            
            <?php dci_get_img($img); ?>
        </div>
    </div>
</div>

<?php } else { ?>
<div class="card card-teaser no-after rounded shadow-sm mb-0 border border-light">
    <div class="card-body pb-5">
        <div class="category-top">
                <span class="category title-xsmall-semi-bold fw-semibold">
                    <a href="<?php echo esc_url($url_tipo); ?>" class="category title-xsmall-semi-bold fw-semibold"><?php echo strtoupper(esc_html($tipo_name)); ?></a>
                </span>
            <?php if (is_array($arrdata) && count($arrdata)) { ?>
                <span class="data fw-normal">
                    <?php echo esc_html($arrdata[0] . ' ' . $monthName . ' ' . $arrdata[2]); ?>
                </span>
            <?php } ?>
        </div>
        
            <?php
                // Controllo se il titolo contiene almeno 5 caratteri maiuscoli consecutivi
                if (preg_match('/[A-Z]{5,}/', $post->post_title)) {
                    echo  '<p class="card-title text-paragraph-medium u-grey-light">'.ucfirst(strtolower($post->post_title)).'</p>';
                } else {
                    echo '<p class="card-title text-paragraph-medium u-grey-light">'.$post->post_title.'</p>';
                }
                // Faccio lo stesso controllo per la descrizione
                if (preg_match('/[A-Z]{5,}/', $descrizione_breve)) {
                    echo  '<p class="text-paragraph-card u-grey-light m-0">'.ucfirst(strtolower($descrizione_breve)).'</p>';
                } else {
                    echo '<p class="text-paragraph-card u-grey-light m-0">'.$descrizione_breve.'</p>';
                }
                ?>
        <?php if (is_array($luogo_notizia) && count($luogo_notizia)) { ?><br><br>
            <span class="data fw-normal"> <i class="fas fa-map-marker-alt"></i> 
                <?php 
                foreach ($luogo_notizia as $luogo_id) {
                    // Ottieni i dettagli del luogo
                    $luogo_post = get_post($luogo_id);
                    
                    if ($luogo_post && !is_wp_error($luogo_post)) {
                        // Stampa il nome del luogo come link
                        echo '<a href="' . esc_url(get_permalink($luogo_post->ID)) . '" title="' . esc_attr($luogo_post->post_title) . '" class="card-text text-secondary text-uppercase pb-3">' . esc_html($luogo_post->post_title) . '</a> ';
                    }
                }
                ?>
            </span>
        <?php } elseif (!empty($luogo_notizia)) { ?>
            <span class="data fw-normal"> | <i class="fas fa-map-marker-alt"></i>
                <?php echo esc_html($luogo_notizia); ?>
            </span>
        <?php } ?>

            <?php 
            // Verifica se ci sono argomenti da visualizzare
            if (has_term('', 'argomenti', $post)) { 
            ?> <hr style="margin-bottom: 20px; width: 200px; height: 1px; background-color: grey; border: none;">
                <div class="card-body">Argomenti: 
                    <?php get_template_part("template-parts/common/badges-argomenti"); ?>
                </div>   
            <?php } ?>            
        <hr style="margin-bottom: 20px; width: 200px; height: 0px; background-color: grey; border: none;">    
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
<?php } ?>
