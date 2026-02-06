<?php
global $count, $scheda;

// Recupera l'opzione evidenziata
$post_ids = dci_get_option('notizia_evidenziata', 'homepage', true);
$prefix   = '_dci_notizia_';

if (is_array($post_ids) && count($post_ids) > 1) {
?>
<h2 id="novita-in-evidenza" class="visually-hidden">Novità in evidenza</h2>

<div id="carosello-evidenza" class="carousel slide" data-bs-ride="carousel"  data-bs-interval="6000" >
    <div class="carousel-inner">

        <?php
    foreach ($post_ids as $index => $post_id) {
        $post = get_post($post_id);
        if (!$post) {
            continue;
        }

        // Dati notizia
        $img               = dci_get_meta("immagine", $prefix, $post->ID);
        $arrdata           = dci_get_data_pubblicazione_arr("data_pubblicazione", $prefix, $post->ID);
        $descrizione_breve = dci_get_meta("descrizione_breve", $prefix, $post->ID);
        $luogo_notizia     = dci_get_meta("luoghi", $prefix, $post->ID);

        $tipo_terms = wp_get_post_terms($post->ID, 'tipi_notizia');
        $tipo       = ($tipo_terms && !is_wp_error($tipo_terms)) ? $tipo_terms[0] : null;

        $monthName = date_i18n('M', mktime(0, 0, 0, $arrdata[1], 10));
    ?>

        <div class="carousel-item <?php echo ($index === 0) ? 'active' : ''; ?>">
            <div class="container">
                <div class="row flex-column flex-lg-row align-items-center g-0">

                    <!-- Testo -->
                    <div class="col-12 col-lg-6 order-2 order-lg-1 d-flex align-items-center">
                        <div class="card-body">

                            <div class="category-top d-flex align-items-center mb-2">
                                <!-- <svg class="icon icon-sm me-2" aria-hidden="true">
                                    <use xlink:href="#it-calendar"></use>
                                </svg> -->

                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" class="icon icon-md me-2" style="width:18px !important;height:18px !important;" aria-hidden="true">
                                    <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/>
                                </svg>

                                <?php if ($tipo) { ?>
                                <span class="title-xsmall-semi-bold fw-semibold">
                                    <a href="<?php echo site_url('tipi_notizia/' . sanitize_title($tipo->name)); ?>"
                                        class="category text-decoration-none title-xsmall-semi-bold fw-semibold">
                                        <?php echo strtoupper($tipo->name); ?>
                                    </a>
                                </span>
                                <?php } ?>
                            </div>

                            <a href="<?php echo get_permalink($post->ID); ?>" class="text-decoration-none">
                                <h3 class="card-title">
                                    <?php
                  $title = mb_strlen($post->post_title) > 100
                      ? mb_substr($post->post_title, 0, 100) . '...'
                      : $post->post_title;

                  echo preg_match('/[A-Z]{5,}/', $title)
                      ? ucfirst(strtolower($title))
                      : $title;
                  ?>
                                </h3>
                            </a>

                            <p class="mb-2 font-serif">
                                <?php
                $desc = mb_strlen($descrizione_breve) > 150
                    ? mb_substr($descrizione_breve, 0, 150) . '...'
                    : $descrizione_breve;

                echo preg_match('/[A-Z]{5,}/', $desc)
                    ? ucfirst(strtolower($desc))
                    : $desc;
                ?>
                            </p>

                            <?php
              if (is_array($luogo_notizia) && count($luogo_notizia) > 0) {
                  echo '<span class="data fw-normal" style="align-items: center !important;">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" style="width:18px !important;height:18px !important;" class="me-1 icon icon-md" aria-hidden="true">
                            <!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.-->
                            <path d="M541.9 139.5C546.4 127.7 543.6 114.3 534.7 105.4C525.8 96.5 512.4 93.6 500.6 98.2L84.6 258.2C71.9 263 63.7 275.2 64 288.7C64.3 302.2 73.1 314.1 85.9 318.3L262.7 377.2L321.6 554C325.9 566.8 337.7 575.6 351.2 575.9C364.7 576.2 376.9 568 381.8 555.4L541.8 139.4z"/>
                        </svg>';
                  foreach ($luogo_notizia as $luogo_id) {
                      $luogo_post = get_post($luogo_id);
                      if ($luogo_post && !is_wp_error($luogo_post)) {
                          echo '<a href="' . esc_url(get_permalink($luogo_post->ID)) . '" class="card-text text-secondary text-uppercase  text-decoration-none pb-1">'
                               . esc_html($luogo_post->post_title) . '</a> ';
                      }
                  }
                  echo '</span>';
              } elseif (!empty($luogo_notizia)) {
                  echo '<span class="data fw-normal  text-decoration-none" style="align-items: center !important;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" style="width:18px !important;height:18px !important;" class="me-1 icon icon-md" aria-hidden="true">
                        <!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.-->
                        <path d="M541.9 139.5C546.4 127.7 543.6 114.3 534.7 105.4C525.8 96.5 512.4 93.6 500.6 98.2L84.6 258.2C71.9 263 63.7 275.2 64 288.7C64.3 302.2 73.1 314.1 85.9 318.3L262.7 377.2L321.6 554C325.9 566.8 337.7 575.6 351.2 575.9C364.7 576.2 376.9 568 381.8 555.4L541.8 139.4z"/>
                    </svg>'
                       . esc_html($luogo_notizia) . '</span>';
              }
              ?>

                            <div class="row mt-2 mb-1">
                                <div class="col-6">
                                    <small>Data:</small>
                                    <p class="fw-semibold font-monospace">
                                        <?php echo $arrdata[0] . ' ' . $monthName . ' ' . $arrdata[2]; ?>
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
                    <?php if ($img) { ?>
                    <div class="col-12 col-lg-6 order-1 order-lg-2 col-img d-none d-lg-flex">
                        <?php dci_get_img($img, 'img-fluid img-evidenza'); ?>
                    </div>
                    <?php } ?>

                </div>
            </div>
        </div>

        <?php } // fine foreach ?>
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

<?php
} elseif (!empty($post_ids)) {

    $post_id = is_array($post_ids) ? $post_ids[0] : $post_ids;
    $post    = get_post($post_id);

    if ($post) {
        $img               = dci_get_meta("immagine", $prefix, $post->ID);
        $arrdata           = dci_get_data_pubblicazione_arr("data_pubblicazione", $prefix, $post->ID);
        $descrizione_breve = dci_get_meta("descrizione_breve", $prefix, $post->ID);
        $luogo_notizia     = dci_get_meta("luoghi", $prefix, $post->ID);

        $tipo_terms = wp_get_post_terms($post->ID, 'tipi_notizia');
        $tipo       = ($tipo_terms && !is_wp_error($tipo_terms)) ? $tipo_terms[0] : null;

        $monthName = date_i18n('M', mktime(0, 0, 0, $arrdata[1], 10));
?>
<div class="row single-news single-news-custom">
    <h2 id="novita-in-evidenza" class="visually-hidden">Novità in evidenza</h2>
    <div class="row">
        <!-- Testo -->
        <div class="col-lg-5 order-2 order-lg-1">
            <div class="card mb-0">
                <div class="card-body pb-2">
                    <div class="category-top d-flex align-items-center mb-2">
                        <!-- <svg class="icon icon-sm me-2" aria-hidden="true">
                            <use xlink:href="#it-calendar"></use>
                        </svg> -->

                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" class="icon icon-md me-2" style="width:18px !important;height:18px !important;" aria-hidden="true">
                            <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/>
                        </svg>

                        <?php if ($tipo){ ?>
                        <span class="title-xsmall-semi-bold fw-semibold">
                            <a href="<?php echo site_url('tipi_notizia/' . sanitize_title($tipo->name)); ?>"
                                class="category title-xsmall-semi-bold fw-semibold text-decoration-none pb-1"><?php echo strtoupper($tipo->name); ?></a>
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
                        <span class="data fw-normal" style="align-items: center !important;">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" style="width:18px !important;height:18px !important;" class="me-1 icon icon-md" aria-hidden="true">
                                <!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.-->
                                <path d="M541.9 139.5C546.4 127.7 543.6 114.3 534.7 105.4C525.8 96.5 512.4 93.6 500.6 98.2L84.6 258.2C71.9 263 63.7 275.2 64 288.7C64.3 302.2 73.1 314.1 85.9 318.3L262.7 377.2L321.6 554C325.9 566.8 337.7 575.6 351.2 575.9C364.7 576.2 376.9 568 381.8 555.4L541.8 139.4z"/>
                            </svg>
                            <?php foreach ($luogo_notizia as $luogo_id):
                                $luogo_post = get_post($luogo_id);
                                if ($luogo_post):
                                    echo '<a href="' . esc_url(get_permalink($luogo_post->ID)) . '" class="card-text text-secondary text-uppercase text-decoration-none pb-1">'
                                        . esc_html($luogo_post->post_title) . '</a> ';
                                endif;
                            endforeach; ?>
                        </span>
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

        <!-- Immagine -->
        <?php if ($img) { ?>
        <div class="col-lg-6 offset-lg-1 order-1 order-lg-2 px-0 px-lg-2 col-img d-none d-lg-flex">
            <?php dci_get_img($img, 'img-fluid'); ?>
        </div>
        <?php } ?>
    </div>
</div>
<?php
}
}
?>

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

/* Forza righe interne a occupare tutta l’altezza */
#carosello-evidenza .carousel-item > .container,
#carosello-evidenza .carousel-item .row {
    height: 100%;
}

/* Titolo: massimo 3 righe (desktop) */
#carosello-evidenza .card-title {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Descrizione breve: massimo 3 righe */
#carosello-evidenza .card-body p {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Mantiene l’immagine sempre visibile anche con testo lungo */
#carosello-evidenza .col-img {
    align-items: flex-start !important;
}

/* Limite dimensione immagine per evitare overflow */
#carosello-evidenza img.img-evidenza{
    max-height: 350px !important; /* Forzatura neccessaria per evitare l'oscuramento del testo e la descrizione della notizia */
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
    min-height: 200px;
}

/* Immagine: stile base */
#carosello-evidenza img.img-evidenza {
    max-width: 80%;
    max-height: 200px;
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
        padding: 0 1rem;
        min-height: 400px;
    }

    /* Immagine in desktop */
    #carosello-evidenza img.img-evidenza {
        max-width: auto;
        max-height: auto;
        margin-left: auto;
        margin-right: 0;
    }
}

/* Stili singolo post con classe personalizzata */
.single-news.single-news-custom .row .col-lg-6.offset-lg-1.order-1.order-lg-2 {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    min-height: 500px;
    padding-right: 3rem;
    /* margine più ampio a destra immagine */
}

/* Immagine singola */
.single-news.single-news-custom .col-lg-6.offset-lg-1 img.img-fluid.img-evidenza {
    max-width: 100%;
    max-height: 400px;
    width: auto;
    height: auto;
    object-fit: contain;
    display: block;
    margin-left: auto;
}

/* Titolo notizia singola: massimo 3 righe */
.single-news.single-news-custom h3.card-title {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Descrizione breve notizia singola: massimo 3 righe */
.single-news.single-news-custom p.font-serif {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
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
    min-height: 450px;
    padding-right: 7rem;
}

/*  Vecchio stile immagine singola */
/*
    .single-news .row .col-lg-6.offset-lg-1.order-1.order-lg-2 img.img-fluid {
    max-width: 80%;
    max-height: 200px;
    width: auto;
    height: auto;
    object-fit: contain;
    display: block;
    margin-left: auto;
    transform: translateX(19px);
} */

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
        max-width: 80%;
        height: auto;
    }

     #carosello-evidenza .carousel-item {
        min-height: 480px;
    }

    #carosello-evidenza .card-title {
        -webkit-line-clamp: 4;
    }

    /* #carosello-evidenza .carousel-control-prev-icon,
    #carosello-evidenza .carousel-control-next-icon {
        width: 36px;
        height: 36px;
        background-size: 16px 16px;
    } */
}

/* Freccette di navigazione */
/* #carosello-evidenza .carousel-control-prev,
#carosello-evidenza .carousel-control-next {
    width: 56px;
    opacity: 1;
    color: #000 !important;
    fill: #000 !important;
} */

/* Posizionamento più elegante */
/* #carosello-evidenza .carousel-control-prev {
    left: 0.5rem;
} */

/* #carosello-evidenza .carousel-control-next {
    right: 0.5rem;
} */

/* Icona freccia: cerchio moderno */    
/* #carosello-evidenza .carousel-control-prev-icon,
#carosello-evidenza .carousel-control-next-icon {
    width: 44px;
    height: 44px;
    fill: #fff !important;
    coloe: #fff !important;
    background-color: rgba(0, 0, 0, 0.35); 
    background-size: 20px 20px;
    border-radius: 50%;
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.2s ease;
} */

/* Hover */
/* #carosello-evidenza .carousel-control-prev:hover .carousel-control-prev-icon,
#carosello-evidenza .carousel-control-next:hover .carousel-control-next-icon {
    background-color: rgba(0, 0, 0, 0.6);
    transform: scale(1.12);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.25);
} */

/* Focus accessibile */
/* #carosello-evidenza .carousel-control-prev:focus-visible,
#carosello-evidenza .carousel-control-next:focus-visible {
    outline: 2px solid rgba(0, 0, 0, 0.6);
    outline-offset: 3px;
} */
</style>