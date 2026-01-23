<?php
global $numero_notizie_evidenziate;

$max_posts = isset($_GET['max_posts']) ? $_GET['max_posts'] : 200;
$load_posts = -1;
$prefix = '_dci_notizia_';

$numero_notizie_evidenziate = (int) $numero_notizie_evidenziate;
$hide_notizie_old = dci_get_option("ck_hide_notizie_old", "homepage") ?? null;
// Recupero solo le notizie evidenziate ordinate per priorità
$args = array(
    'post_type'      => 'notizia',
    'meta_type' => 'text_date_timestamp',
    'meta_query'     => array(
        array(
            'key'   => $prefix . 'evidenzia_home',
            'value' => 'on',
        ),
    ),
    'orderby'   => 'meta_value_num',
    'order'     => 'DESC',
    'posts_per_page' => $max_posts,
);

$the_query = new WP_Query($args);
$posts = $the_query->posts;

if (!$posts || count($posts) === 0) {
    return;
}
$count = 0;
?>

<?php if (count($posts) > 1 && $numero_notizie_evidenziate > 1): ?>
<h2 id="novita-in-evidenza" class="visually-hidden">Novità in evidenza</h2>

<div id="carosello-evidenza" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-inner">

        <?php foreach ($posts as $index => $post):
            if($count > $numero_notizie_evidenziate){
                continue;
            }
            setup_postdata($post);

            // Dati principali
            $img               = dci_get_meta("immagine", $prefix, $post->ID);
            $descrizione_breve = dci_get_meta("descrizione_breve", $prefix, $post->ID);
            $luogo_notizia     = dci_get_meta("luoghi", $prefix, $post->ID);

            // Tipo termine
            $tipo_terms = wp_get_post_terms($post->ID, 'tipi_notizia');
            $tipo       = ($tipo_terms && !is_wp_error($tipo_terms)) ? $tipo_terms[0] : null;

            // Data Pubblicazione
            $arrdata           = dci_get_data_pubblicazione_arr("data_pubblicazione", $prefix, $post->ID);
            $dayPubblicazione = $arrdata[0];
            $monthPubblicazione = $arrdata[1];
            $monthName  = date_i18n('M', mktime(0, 0, 0, $arrdata[1], 10));
            $yearPubblicazione = strlen($arrdata[2]) == 2 ? '20' . $arrdata[2] : $arrdata[2];


            // Data Scadenza
            $arrayDataScadenza =  dci_get_data_pubblicazione_arr("data_scadenza", $prefix, $post->ID);
            $dayScadenza = $arrayDataScadenza[0];
            $monthScadenza = $arrayDataScadenza[1];
            $monthScadenzaName  = date_i18n('M', mktime(0, 0, 0, $arrayDataScadenza[1], 10));
            $yearScadenza = strlen($arrayDataScadenza[2]) == 2 ? '20' . $arrayDataScadenza[2] : $arrayDataScadenza[2];
            
            // Sitemo le date
            $dayPubblicazione =  DateTime::createFromFormat('d/m/Y', "$$dayPubblicazione/$monthPubblicazione/$yearPubblicazione");
            $dataScadenza = DateTime::createFromFormat('d/m/Y', "$dayScadenza/$monthScadenza/$yearScadenza");
            
            // Leggo la data odierna
            $oggi = new DateTime();

            // verifico se posso visualizzare la notizia
            // if(isset($hide_notizie_old) && $hide_notizie_old === 'true' && $dataScadenza instanceof DateTime < $oggi instanceof DateTime){
            //     continue;
            // }
        ?>

        <div class="carousel-item <?php echo ($index === 0) ? 'active' : ''; ?>">
            <div class="container">
                <div class="row flex-column flex-lg-row align-items-center g-0">

                    <!-- Testo -->
                    <div class="col-12 col-lg-6 order-2 order-lg-1 d-flex align-items-center">
                        <div class="card-body">

                            <div class="category-top d-flex align-items-center mb-2">
                                <svg class="icon icon-sm me-2" aria-hidden="true">
                                    <use xlink:href="#it-calendar"></use>
                                </svg>

                                <?php if ($tipo): ?>
                                <span class="title-xsmall-semi-bold fw-semibold">
                                    <a href="<?php echo site_url('tipi_notizia/' . sanitize_title($tipo->name)); ?>"
                                        class="category title-xsmall-semi-bold fw-semibold">
                                        <?php echo strtoupper($tipo->name); ?>
                                    </a>
                                </span>
                                <?php endif; ?>
                            </div>

                            <a href="<?php echo get_permalink($post->ID); ?>" class="text-decoration-none">
                                <h3 class="card-title"><?php echo esc_html($post->post_title); ?></h3>
                            </a>

                            <p class="mb-2 font-serif">
                                <?php echo esc_html(wp_trim_words($descrizione_breve, 25, '...')); ?>
                            </p>

                            <?php if (is_array($luogo_notizia) && count($luogo_notizia)): ?>
                            <span class="data fw-normal">
                                <i class="fas fa-map-marker-alt me-1"></i>
                                <?php foreach ($luogo_notizia as $luogo_id):
                                    $luogo_post = get_post($luogo_id);
                                    if ($luogo_post):
                                        echo '<a href="' . esc_url(get_permalink($luogo_post->ID)) . '" class="card-text text-secondary text-uppercase pb-1">'
                                             . esc_html($luogo_post->post_title) . '</a> ';
                                    endif;
                                endforeach; ?>
                            </span>
                            <?php endif; ?>

                            <div class="row mt-2 mb-1">
                                <div class="col-6">
                                    <small>Data:</small>
                                    <p class="fw-semibold font-monospace">
                                        <?php echo esc_html($arrdata[0] . ' ' . $monthName . ' ' . $arrdata[2]); ?>
                                    </p>
                                </div>
                            </div>

                            <small>Argomenti:</small>
                            <?php get_template_part("template-parts/common/badges-argomenti"); ?>

                            <a class="read-more mt-4 d-inline-flex align-items-center"
                               href="<?php echo get_permalink($post->ID); ?>">
                                <span class="text">Vai alla pagina</span>
                                <svg class="icon ms-1">
                                    <use xlink:href="#it-arrow-right"></use>
                                </svg>
                            </a>

                        </div>
                    </div>

                    <!-- Immagine -->
                    <?php if ($img): ?>
                    <div class="col-12 col-lg-6 order-1 order-lg-2 col-img d-none d-lg-flex">
                        <?php dci_get_img($img, 'img-fluid img-evidenza'); ?>
                    </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>

        <?php 
            $count++;
        endforeach; wp_reset_postdata(); ?>

    </div>

    <button class="carousel-control-prev" type="button" data-bs-target="#carosello-evidenza" data-bs-slide="prev">
        <span class="carousel-control-prev-icon"></span>
        <span class="visually-hidden">Precedente</span>
    </button>

    <button class="carousel-control-next" type="button" data-bs-target="#carosello-evidenza" data-bs-slide="next">
        <span class="carousel-control-next-icon"></span>
        <span class="visually-hidden">Successivo</span>
    </button>
</div>

<?php else: 
$post = $posts[0];
setup_postdata($post);

$img               = dci_get_meta("immagine", $prefix, $post->ID);
$arrdata           = dci_get_data_pubblicazione_arr("data_pubblicazione", $prefix, $post->ID);
$descrizione_breve = dci_get_meta("descrizione_breve", $prefix, $post->ID);
$luogo_notizia     = dci_get_meta("luoghi", $prefix, $post->ID);

$tipo_terms = wp_get_post_terms($post->ID, 'tipi_notizia');
$tipo       = ($tipo_terms && !is_wp_error($tipo_terms)) ? $tipo_terms[0] : null;
$monthName  = date_i18n('M', mktime(0, 0, 0, $arrdata[1], 10));
?>

<div class="row single-news single-news-custom">
    <h2 id="novita-in-evidenza" class="visually-hidden">Novità in evidenza</h2>

    <div class="row">
        <div class="col-lg-5 order-2 order-lg-1">
            <div class="card mb-0">
                <div class="card-body pb-2">
                    <div class="category-top d-flex align-items-center mb-2">
                        <svg class="icon icon-sm me-2" aria-hidden="true">
                            <use xlink:href="#it-calendar"></use>
                        </svg>
                        <?php if ($tipo){ ?>
                        <span class="title-xsmall-semi-bold fw-semibold">
                            <a href="<?php echo site_url('tipi_notizia/' . sanitize_title($tipo->name)); ?>"
                                class="category title-xsmall-semi-bold fw-semibold"><?php echo strtoupper($tipo->name); ?></a>
                        </span>
                        <?php } ?>
                    </div>
                    <a href="<?php echo get_permalink($post->ID); ?>" class="text-decoration-none">
                        <h3 class="card-title">
                            <?php echo preg_match('/[A-Z]{5,}/', $post->post_title) ? ucfirst(strtolower($post->post_title)) : $post->post_title; ?>
                        </h3>
                    </a>

                    <p class="mb-2 font-serif">
                        <?php echo preg_match('/[A-Z]{5,}/', $descrizione_breve) ? ucfirst(strtolower($descrizione_breve)) : $descrizione_breve; ?>
                    </p>

                    <!-- Luoghi -->
                    <?php if (is_array($luogo_notizia) && count($luogo_notizia)): ?>
                    <span class="data fw-normal"><i class="fas fa-map-marker-alt me-1"></i>
                        <?php foreach ($luogo_notizia as $luogo_id):
                $luogo_post = get_post($luogo_id);
                if ($luogo_post && !is_wp_error($luogo_post)) {
                  echo '<a href="' . esc_url(get_permalink($luogo_post->ID)) . '" class="card-text text-secondary text-uppercase pb-1">' . esc_html($luogo_post->post_title) . '</a> ';
                }
              endforeach; ?>
                    </span>
                    <?php elseif (!empty($luogo_notizia)): ?>
                    <span class="data fw-normal"><i
                            class="fas fa-map-marker-alt me-1"></i><?php echo esc_html($luogo_notizia); ?></span>
                    <?php endif; ?>

                    <!-- Data -->
                    <div class="row mt-2 mb-1">
                        <div class="col-6">
                            <small>Data:</small>
                            <p class="fw-semibold font-monospace">
                                <?php echo $arrdata[0] . ' ' . $monthName . ' ' . $arrdata[2]; ?></p>
                        </div>
                    </div>

                    <!-- Argomenti -->
                    <small>Argomenti: </small>
                    <?php get_template_part("template-parts/common/badges-argomenti"); ?>

                    <a class="read-more mt-4 d-inline-flex align-items-center"
                        href="<?php echo get_permalink($post->ID); ?>">
                        <span class="text">Vai alla pagina</span>
                        <svg class="icon ms-1">
                            <use xlink:href="#it-arrow-right"></use>
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <?php if ($img): ?>
        <div class="col-lg-6 offset-lg-1 order-1 order-lg-2 px-0 px-lg-2">
            <?php dci_get_img($img, 'img-fluid'); ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php wp_reset_postdata(); endif; ?>


<style>
/* Contenitore del carosello */
#carosello-evidenza {
    position: relative;
    overflow: hidden;
}

/* Altezza minima slide */
#carosello-evidenza .carousel-item {
    min-height: 500px;
}

/* Evita overflow visivo */
#carosello-evidenza .carousel-inner {
    border-radius: 0;
    overflow: hidden;
}

/* Immagine: container grigio */
#carosello-evidenza .col-img {
    display: flex;
    align-items: center;
    justify-content: center;
    /* centrato per mobile */
    padding: 0 1rem;
    min-height: 300px;
}

/* Immagine: stile base */
#carosello-evidenza img.img-evidenza {
    max-width: 90%;
    max-height: 300px;
    width: auto;
    height: auto;
    object-fit: contain;
    display: block;
    margin: 0 auto;
    /* centrato per mobile */
}

/* Testo della card */
#carosello-evidenza .card-body {
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    /* Allinea contenuto in alto */
    min-height: 320px;
    padding: 0 1rem;
}

/* Versione desktop (da 992px in su) */
@media (min-width: 992px) {
    #carosello-evidenza .card-body {
        padding: 0 1rem;
    }

    /* Box immagine */
    #carosello-evidenza .col-img {
        justify-content: flex-end;
        /* spinge immagine a destra */
        padding: 0 2rem;
        min-height: 400px;
    }

    /* Immagine in desktop */
    #carosello-evidenza img.img-evidenza {
        max-width: 100%;
        max-height: 100%;
        margin-left: auto;
        margin-right: 0;
    }
}

/* Stili singolo post con classe personalizzata */
.single-news.single-news-custom .row .col-lg-6.offset-lg-1.order-1.order-lg-2 {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    min-height: 400px;
    padding-right: 5rem;
    /* margine più ampio a destra immagine */
}

.single-news.single-news-custom .row .col-lg-6.offset-lg-1.order-1.order-lg-2 img.img-fluid {
    max-width: 90%;
    max-height: 400px;
    width: auto;
    height: auto;
    object-fit: contain;
    display: block;
    margin-left: auto;
}

.single-news.single-news-custom .row .col-lg-5.order-2.order-lg-1 {
    padding-left: 0rem;
    /* spostato più a sinistra */
    padding-right: 0rem;
}

/* Stili originali singolo post senza classe custom (per sicurezza) */
.single-news .row .col-lg-6.offset-lg-1.order-1.order-lg-2 {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    min-height: 400px;
    padding-right: 7rem;
}

.single-news .row .col-lg-6.offset-lg-1.order-1.order-lg-2 img.img-fluid {
    max-width: 90%;
    max-height: 400px;
    width: auto;
    height: auto;
    object-fit: contain;
    display: block;
    margin-left: auto;
    transform: translateX(19px);
}

.single-news .row .col-lg-5.order-2.order-lg-1 {
    padding-left: 1.5rem;
    padding-right: 1rem;
}

/* Modifica per centrare l'immagine sui dispositivi mobili */
@media (max-width: 991px) {

    /* Centra l'immagine nel carosello e nei post singoli su mobile */
    #carosello-evidenza .col-img,
    .single-news.single-news-custom .row .col-lg-6.offset-lg-1.order-1.order-lg-2 {
        justify-content: center;
        /* Centra orizzontalmente */
    }

    /* Centra l'immagine su mobile */
    #carosello-evidenza img.img-evidenza,
    .single-news.single-news-custom .row .col-lg-6.offset-lg-1.order-1.order-lg-2 img.img-fluid {
        margin: 0 auto;
        /* Centra l'immagine */
        max-width: 100%;
        height: auto;
    }
}
</style>