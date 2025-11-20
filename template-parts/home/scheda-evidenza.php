<?php
global $scheda, $count, $post;

// Verifico se la variabile globale $post se non essite ed diversa da null

if(!isset($post) && !empty($post) && $post!=null){
    $post = get_post($scheda['scheda_' . $count . '_contenuto'][0]);
}

$img = dci_get_meta('immagine');
$descrizione_breve = dci_get_meta('descrizione_breve');
$icon = dci_get_post_type_icon_by_id($post->ID);
$page = get_page_by_path(dci_get_group($post->post_type));
$argomenti = dci_get_meta("argomenti", '_dci_notizia_', $post->ID);
$luogo_notizia = dci_get_meta("luoghi", '_dci_notizia_', $post->ID);
$arrdata = dci_get_data_pubblicazione_arr("data_pubblicazione", '_dci_notizia_', $post->ID);
$monthName = date_i18n('M', mktime(0, 0, 0, $arrdata[1], 10));
$page_macro_slug = dci_get_group($post->post_type);
$page_macro = get_page_by_path($page_macro_slug);

$post_type = get_post_type($post->ID);
$post_type_object = get_post_type_object($post_type);
$post_type_label = $post_type_object->labels->singular_name;

$tipo_name = '';
$url_tipo = '#';

switch ($post_type_label) {
    case 'Servizio':
        $tipo_terms = get_the_terms($post->ID, 'categorie_servizio');
        if ($tipo_terms && !is_wp_error($tipo_terms)) {
            $tipo = $tipo_terms[0];
            $tipo_name = 'Servizio';
            $url_tipo = '/servizi-categoria/' . sanitize_title($tipo->name);
        }
        break;
    case 'Luogo':
        $tipo_terms = get_the_terms($post->ID, 'tipi_luogo');
        if ($tipo_terms && !is_wp_error($tipo_terms)) {
            $tipo = $tipo_terms[0];
            $tipo_name = 'Luogo';
            $url_tipo = '/tipi-luogo/' . sanitize_title($tipo->name);
        }
        break;
    case 'Evento':
        $tipo_terms = get_the_terms($post->ID, 'tipi_evento');
        if ($tipo_terms && !is_wp_error($tipo_terms)) {
            $tipo = $tipo_terms[0];
            $tipo_name = 'Evento';
            $url_tipo = '/vivere-il-comune/tipo-evento/' . sanitize_title($tipo->name);
        }
        break;
    case 'Documento Pubblico':
        $tipo_terms = get_the_terms($post->ID, 'tipi_documento');
        if ($tipo_terms && !is_wp_error($tipo_terms)) {
            $tipo = $tipo_terms[0];
            $tipo_name = 'Documento Pubblico';
            $url_tipo = '/tipi_documento/' . sanitize_title($tipo->name);
        }
        break;
    case 'Unità Organizzativa':
        $tipo_terms = get_the_terms($post->ID, 'tipi_unita_organizzativa');
        if ($tipo_terms && !is_wp_error($tipo_terms)) {
            $tipo = $tipo_terms[0];
            $tipo_name = 'Unita Organizzativa';
            $url_tipo = '/amministrazione/unita_organizzativa/';
            if ($tipo->slug == "ufficio") {
                $url_tipo = $url_tipo . "uffici";
            } else if ($tipo->sulg == "area") {
                $url_tipo = $url_tipo . "aree-amministrative";
            } else if ($tipo->slug == "consiglio comunale") {
                $url_tipo = $url_tipo . "consiglio-comunale";
            }
        }
        break;
    case 'Notizia':
        $tipo_terms = get_the_terms($post->ID, 'tipi_notizia');
        if ($tipo_terms && !is_wp_error($tipo_terms)) {
            $tipo = $tipo_terms[0];
            $tipo_name = $tipo->name;
            $url_tipo = '/tipi_notizia/' . sanitize_title($tipo->name);
        } else {
            $tipo = null;
            $url_tipo = '#';
        }
        break;
        
    case 'Dataset':
        $tipo_name = 'Dataset';
        $url_tipo = '/dataset';
        break;

    default:
        $tipo_name = 'Novità';
        $url_tipo = '#';
} 
?>


<div class="card-wrapper border border-light rounded shadow-sm cmp-list-card-img cmp-list-card-img-hr h-100">
    <div class="card no-after rounded h-100 d-flex flex-column">
        <?php if ($img) { ?>
            <div class="d-flex flex-column">
                <div style="max-width: 420px;">
                  <center><?php dci_get_img($img, 'rounded-top img-fluid img-responsive'); ?></center>
                </div>
            </div>
        <?php } ?>
        <div class="card-body d-flex flex-column">
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

            <h3 class="h5 card-title text-justify u-grey-light">
                <?php
                $title = get_the_title();
                if (strlen($title) > 100) {
                    $title = substr($title, 0, 97) . '...';
                }
                if (preg_match('/[A-Z]{5,}/', $title)) {
                    $title = ucfirst(strtolower($title));
                }
                echo esc_html($title);
                ?>
            </h3>
            
            <?php if (preg_match('/[A-Z]{5,}/', $descrizione_breve)) {
                echo  '<p class="text-paragraph-card u-grey-light m-0 text-justify">' . ucfirst(strtolower($descrizione_breve)) . '</p>';
            } else {
                echo '<p class="text-paragraph-card u-grey-light m-0 text-justify">' . $descrizione_breve . '</p>';
            } ?>

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

            <?php if (has_term('', 'argomenti', $post)) { ?>
                <hr style="margin-bottom: 20px; width: 100%; height: 1px; background-color: grey; border: none;">
               <div class="card-body p-0" style="text-align: left; font-weight: 600;">
                    Argomenti:
                    <?php get_template_part("template-parts/common/badges-argomenti"); ?>
                </div>
            <?php } ?>
            <hr style="margin-bottom: 20px; width: 200px; height: 0px; background-color: grey; border: none;">
        
                    <a class="read-more d-inline-flex align-items-center"
                        href="<?php echo esc_url(get_permalink($post->ID)); ?>"
                        aria-label="Vai alla pagina <?php echo esc_attr($post->post_title); ?>"
                        title="Vai alla pagina <?php echo esc_attr($post->post_title); ?>"
                        style="margin-left: 0 !important; padding-left: 0 !important; margin-top: 30px;">
                        <span class="text">Vai alla pagina</span>
                        <svg class="icon ms-1">
                            <use xlink:href="#it-arrow-right"></use>
                        </svg>
                    </a>


            
        </div>
    </div>
</div>
