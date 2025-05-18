<?php
global $count, $scheda;

$post_id = dci_get_option('notizia_evidenziata', 'homepage', true)[0] ?? null;
$prefix = '_dci_notizia_';

if ($post_id) {
    $post = get_post($post_id);
    $img = dci_get_meta("immagine", $prefix, $post->ID);
    $arrdata = dci_get_data_pubblicazione_arr("data_pubblicazione", $prefix, $post->ID);
    $monthName = date_i18n('M', mktime(0, 0, 0, $arrdata[1], 10));
    $descrizione_breve = dci_get_meta("descrizione_breve", $prefix, $post->ID);
    $argomenti = dci_get_meta("argomenti", $prefix, $post->ID);
    $luogo_notizia = dci_get_meta("luoghi", $prefix, $post->ID);
    

    $tipo_terms = wp_get_post_terms($post->ID, 'tipi_notizia');    
    if ($tipo_terms && !is_wp_error($tipo_terms)) {
        $tipo = $tipo_terms[0];  // Prendi il primo termine trovato
    } else {
        $tipo = null;  // Nessun termine trovato
    }

}


?>

            <h2 id="novita-in-evidenza" class="visually-hidden">Novità in evidenza</h2>     
                <div class="row">
                    <!-- Colonna con i dettagli della notizia -->
                    <div class="col-lg-5 order-2 order-lg-1">
                        <div class="card mb-0"> <!-- Ridotto spazio con mb-0 -->
                            <div class="card-body pb-2"> <!-- Ridotto spazio con pb-2 -->
                                <div class="category-top d-flex align-items-center">
                                    <svg class="icon icon-sm me-2" aria-hidden="true">
                                        <use xlink:href="#it-calendar"></use>
                                    </svg>
                                    <?php if ($tipo): ?>
                                        <span class="title-xsmall-semi-bold fw-semibold">
                                            <a href="<?php echo site_url('tipi_notizia/' . sanitize_title($tipo->name)); ?>" class="category title-xsmall-semi-bold fw-semibold"><?php echo strtoupper($tipo->name); ?></a>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <a href="<?php echo get_permalink($post->ID); ?>" class="text-decoration-none">
                                    <?php
                                    // Controllo se il titolo contiene almeno 5 caratteri maiuscoli consecutivi
                                    if (preg_match('/[A-Z]{5,}/', $post->post_title)) {
                                        $titolo = ucfirst(strtolower($post->post_title));
                                    } else {
                                        $titolo = $post->post_title;
                                    }
                                    ?>
                                    <h3 class="card-title"><?php echo $titolo; ?></h3>
                                </a>
                                             
                                    <?php
                                    if (preg_match('/[A-Z]{5,}/', $descrizione_breve)) {
                                        // Se c'è una sequenza di 5 o più lettere maiuscole
                                        echo '<p class="mb-2 font-serif">' . ucfirst(strtolower($descrizione_breve)) . '</p>';
                                    } else {
                                        // Se non c'è una sequenza di lettere maiuscole
                                        echo '<p class="mb-2 font-serif">' . $descrizione_breve . '</p>';
                                    }
                                    ?>
                                                              

                                <!-- Luoghi -->
                                <?php if (is_array($luogo_notizia) && count($luogo_notizia)) { ?>
                                    <span class="data fw-normal"><i class="fas fa-map-marker-alt"></i>
                                        <?php
                                        foreach ($luogo_notizia as $luogo_id) {
                                            $luogo_post = get_post($luogo_id);
                                            if ($luogo_post && !is_wp_error($luogo_post)) {
                                                echo '<a href="' . esc_url(get_permalink($luogo_post->ID)) . '" title="' . esc_attr($luogo_post->post_title) . '" class="card-text text-secondary text-uppercase pb-1">' . esc_html($luogo_post->post_title) . '</a> ';
                                            }
                                        }
                                        ?>
                                    </span>
                                <?php } elseif (!empty($luogo_notizia)) { ?>
                                    <span class="data fw-normal"><i class="fas fa-map-marker-alt"></i>
                                        <?php echo esc_html($luogo_notizia); ?>
                                    </span>
                                <?php } ?>

                                <!-- Data pubblicazione -->
                                <div class="row mt-2 mb-1"> <!-- Ridotto margine tra elementi -->
                                    <div class="col-6">
                                        <small>Data:</small>
                                        <p class="fw-semibold font-monospace">
                                            <?php if (is_array($arrdata) && count($arrdata)) { ?>
                                                <span class="data fw-normal">
                                                    <?php echo $arrdata[0] . ' ' . $monthName . ' ' . $arrdata[2]; ?>
                                                </span>
                                            <?php } ?>
                                        </p>
                                    </div>
                                </div>

                                <!-- Argomenti -->
                                <small>Argomenti: </small>
                                <?php get_template_part("template-parts/common/badges-argomenti"); ?>

                                <a class="read-more" href="<?php echo get_permalink($post->ID); ?>"
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
                    <!-- Colonna con l'immagine -->
                    <div class="col-lg-6 offset-lg-1 order-1 order-lg-2 px-0 px-lg-2">
                        <?php if ($img) {
                            dci_get_img($img, 'img-fluid');
                        } ?>
                    </div>
                </div>
