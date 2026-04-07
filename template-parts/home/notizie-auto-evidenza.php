<?php
global $numero_notizie_evidenziate;

$numero_notizie_evidenziate = max(1, (int) $numero_notizie_evidenziate);
$prefix = '_dci_notizia_';
$hide_notizie_old = dci_get_option("ck_hide_notizie_old", "homepage");

/**
 * Query
 * - Recupera tutte le notizie evidenziate (schede manuali o automatiche)
 * - Se è attiva l'opzione per nascondere le notizie vecchie, il filtro viene applicato dopo
 */
$args = array(
    'post_type'      => 'notizia',
    'meta_query'     => array(
        array(
            'key'   => $prefix . 'evidenzia_home',
            'value' => 'on',
        ),
    ),
    'orderby'        => 'date',
    'order'          => 'DESC',
    'posts_per_page' => -1,
);

$query = new WP_Query($args);
$posts = $query->posts;

if (!$query->have_posts() || empty($posts)) {
    wp_reset_postdata();
    return;
}

/*
    Filtro i post validi
    - Se il check nascondi notizie vecchie è attivo, escludo le notizie con data di scadenza precedente a oggi
      solo se la data di scadenza è diversa dalla data di pubblicazione
    - Se invece il check non è attivo, includo tutte le notizie evidenziate senza filtrare per data di scadenza
*/

$oggi = new DateTime('today');
$valid_posts = []; // Array che conterrà i post validi dopo il filtro

foreach ($posts as $p) {

    // Controllo 0: verifico se ho già raggiunto il numero di notizie evidenziate da mostrare
    if (count($valid_posts) >= $numero_notizie_evidenziate) {
        break;
    }

    // Primo controllo sul check nascondi notizie vecchie
    if ($hide_notizie_old === 'true') {

        // Data pubblicazione
        $dataPubblicazione = null;
        $arrdata = dci_get_data_pubblicazione_arr("data_pubblicazione", $prefix, $p->ID);

        if (is_array($arrdata) && count($arrdata) >= 3) {
            $dayPubblicazione   = trim((string) $arrdata[0]);
            $monthPubblicazione = trim((string) $arrdata[1]);
            $yearPubblicazione  = trim((string) $arrdata[2]);

            $yearPubblicazione = strlen($yearPubblicazione) == 2 ? '20' . $yearPubblicazione : $yearPubblicazione;

            $dataPubblicazione = DateTime::createFromFormat(
                'd/m/Y',
                sprintf('%02d/%02d/%04d', (int) $dayPubblicazione, (int) $monthPubblicazione, (int) $yearPubblicazione)
            );

            if ($dataPubblicazione instanceof DateTime) {
                $dataPubblicazione->setTime(0, 0, 0);
            }
        }

        // Data scadenza
        $dataScadenza = null;
        $arrdataFine = dci_get_data_pubblicazione_arr("data_scadenza", $prefix, $p->ID);

        if (is_array($arrdataFine) && count($arrdataFine) >= 3) {
            $dayScadenza   = trim((string) $arrdataFine[0]);
            $monthScadenza = trim((string) $arrdataFine[1]);
            $yearScadenza  = trim((string) $arrdataFine[2]);

            $yearScadenza = strlen($yearScadenza) == 2 ? '20' . $yearScadenza : $yearScadenza;

            $dataScadenza = DateTime::createFromFormat(
                'd/m/Y',
                sprintf('%02d/%02d/%04d', (int) $dayScadenza, (int) $monthScadenza, (int) $yearScadenza)
            );

            if ($dataScadenza instanceof DateTime) {
                $dataScadenza->setTime(0, 0, 0);
            }
        }

        /**
         * Controllo:
         * escludo la notizia se:
         * - la data di scadenza esiste
         * - è precedente a oggi
         * - ed è diversa dalla data di pubblicazione
         *
         * Mantengo quindi il confronto tra data pubblicazione e data scadenza,
         * ma lo faccio confrontando il valore della data e non l'istanza dell'oggetto.
         */
        if (
            $dataScadenza instanceof DateTime &&
            $dataScadenza < $oggi &&
            (
                !($dataPubblicazione instanceof DateTime) ||
                $dataScadenza->format('Y-m-d') !== $dataPubblicazione->format('Y-m-d')
            )
        ) {
            continue;
        }

        // Allora imposto la notizia come valida
        $valid_posts[] = $p;

    } else {
        $valid_posts[] = $p;
    }
}

wp_reset_postdata();

$totale = count($valid_posts); // Numero di notizie valide da mostrare
$rendered = 0; // Contatore per il numero di notizie evidenziate renderizzate

if ($totale === 0) {
    return;
}
?>

<?php if ($totale > 1 && $numero_notizie_evidenziate > 1): ?>

<h2 id="novita-in-evidenza" class="visually-hidden" aria-label="Novità in evidenza">Novità in evidenza</h2>
<div id="carosello-evidenza" class="carousel slide" data-bs-ride="carousel" data-bs-interval="6000">
    <div class="carousel-inner">

        <?php foreach ($valid_posts as $index => $p) :

            setup_postdata($p);

            // Dati principali della notizia
            $img = dci_get_meta("immagine", $prefix, $p->ID);
            $descrizione_breve = dci_get_meta("descrizione_breve", $prefix, $p->ID);
            $luogo_notizia = dci_get_meta("luoghi", $prefix, $p->ID);

            // Tipo notizia
            $tipo_terms = wp_get_post_terms($p->ID, 'tipi_notizia');
            $tipo = (!empty($tipo_terms) && !is_wp_error($tipo_terms)) ? $tipo_terms[0] : null;

            /**
             * DATA PUBBLICAZIONE
             */
            $dataPubblicazione = null;
            $dayPubblicazione = '';
            $monthPubblicazione = '';
            $yearPubblicazione = '';
            $monthName = '';

            $arrdata = dci_get_data_pubblicazione_arr("data_pubblicazione", $prefix, $p->ID);

            if (is_array($arrdata) && count($arrdata) >= 3) {
                $dayPubblicazione   = trim((string) $arrdata[0]);
                $monthPubblicazione = trim((string) $arrdata[1]);
                $yearPubblicazione  = trim((string) $arrdata[2]);

                $yearPubblicazione = strlen($yearPubblicazione) == 2 ? '20' . $yearPubblicazione : $yearPubblicazione;

                $dataPubblicazione = DateTime::createFromFormat(
                    'd/m/Y',
                    sprintf('%02d/%02d/%04d', (int) $dayPubblicazione, (int) $monthPubblicazione, (int) $yearPubblicazione)
                );

                if ($dataPubblicazione instanceof DateTime) {
                    $monthName = date_i18n('M', mktime(0, 0, 0, (int) $monthPubblicazione, 10));
                }
            }

            $is_active = ($index === 0);
            ?>

            <div class="carousel-item <?php echo $is_active ? 'active' : ''; ?>">
                <div class="container">
                    <div class="row flex-column flex-lg-row align-items-center g-0">

                        <!-- TESTO -->
                        <div class="col-12 col-lg-6 order-2 order-lg-1 d-flex align-items-center">
                            <div class="card-body">

                                <div class="category-top d-flex align-items-center mb-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                         viewBox="0 0 16 16" class="icon icon-md me-2">
                                        <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/>
                                    </svg>

                                    <?php if ($tipo): ?>
                                        <span class="title-xsmall-semi-bold fw-semibold" aria-label="<?php echo esc_attr($tipo->name); ?>">
                                            <a href="<?php echo esc_url(site_url('tipi_notizia/' . sanitize_title($tipo->name))); ?>" class="category text-decoration-none">
                                                <?php echo esc_html(strtoupper($tipo->name)); ?>
                                            </a>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <a href="<?php echo esc_url(get_permalink($p->ID)); ?>" class="text-decoration-none">
                                    <h3 class="card-title" aria-label="<?php echo esc_attr($p->post_title); ?>">
                                        <?php echo esc_html(preg_match('/[A-Z]{5,}/', $p->post_title) ? ucfirst(strtolower($p->post_title)) : $p->post_title); ?>
                                    </h3>
                                </a>

                                <p class="mb-2 font-serif descrizione-breve-notizia" aria-label="<?php echo esc_attr($descrizione_breve); ?>">
                                    <?php echo esc_html(preg_match('/[A-Z]{5,}/', $descrizione_breve) ? ucfirst(strtolower($descrizione_breve)) : $descrizione_breve); ?>
                                </p>

                                <!-- Mostra eventuali luoghi -->
                                <?php if (is_array($luogo_notizia) && count($luogo_notizia)): ?>
                                    <span class="data fw-normal" style="align-items: center !important;" aria-label="Luoghi">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" style="width:18px !important;height:18px !important;" class="me-1 icon icon-md" aria-hidden="true">
                                            <path d="M541.9 139.5C546.4 127.7 543.6 114.3 534.7 105.4C525.8 96.5 512.4 93.6 500.6 98.2L84.6 258.2C71.9 263 63.7 275.2 64 288.7C64.3 302.2 73.1 314.1 85.9 318.3L262.7 377.2L321.6 554C325.9 566.8 337.7 575.6 351.2 575.9C364.7 576.2 376.9 568 381.8 555.4L541.8 139.4z"/>
                                        </svg>
                                        <?php foreach ($luogo_notizia as $luogo_id):
                                            $luogo_post = get_post($luogo_id);
                                            if ($luogo_post):
                                                echo '<a href="' . esc_url(get_permalink($luogo_post->ID)) . '" class="card-text text-secondary text-uppercase text-decoration-none pb-1" aria-label="' . esc_attr($luogo_post->post_title) . '">'
                                                    . esc_html($luogo_post->post_title) . '</a> ';
                                            endif;
                                        endforeach; ?>
                                    </span>
                                <?php endif; ?>

                                <?php if ($dayPubblicazione !== '' && $monthName !== '' && $yearPubblicazione !== ''): ?>
                                    <div class="row mt-2 mb-1">
                                        <div class="col-6">
                                            <small>Data:</small>
                                            <p class="fw-semibold font-monospace">
                                                <?php echo esc_html($dayPubblicazione . ' ' . $monthName . ' ' . $yearPubblicazione); ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                               
                                <!-- Argomenti -->
                               <?php
                                    $argomenti = wp_get_post_terms($p->ID, 'argomenti');
                                    if (!empty($argomenti) && !is_wp_error($argomenti)) :
                                    ?>
                                        <small>Argomenti:</small>
                                        <ul class="d-flex flex-wrap gap-1 list-unstyled mb-0">
                                            <?php foreach ($argomenti as $item) : ?>
                                                <li>
                                                    <a class="chip chip-simple" href="<?php echo esc_url(get_term_link($item)); ?>">
                                                        <span class="chip-label">
                                                            <?php echo esc_html($item->name); ?>
                                                        </span>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>

                                <a class="read-more mt-4 d-inline-flex align-items-center"
                                href="<?php echo esc_url(get_permalink($p->ID)); ?>">
                                    <span class="text">Vai alla pagina</span>
                                    <svg class="icon ms-1">
                                        <use xlink:href="#it-arrow-right"></use>
                                    </svg>
                                </a>
                            </div>
                        </div>

                        <!-- Immagine -->
                        <?php if ($img): ?>
                        <div class="col-12 col-lg-6 order-1 order-lg-2 col-img d-flex">
                            <?php dci_get_img($img, 'img-fluid img-evidenza'); ?>
                        </div>
                    <?php endif; ?>

                    </div>
                </div>
            </div>

            <?php
            $rendered++;
        endforeach;
        wp_reset_postdata();
        ?>

    </div>

    <?php if ($totale > 1) { ?>
        <button class="carousel-control-prev" type="button" data-bs-target="#carosello-evidenza" data-bs-slide="prev" aria-label="Precedente">
            <span class="carousel-control-prev-icon"></span>
            <span class="visually-hidden">Precedente</span>
        </button>

        <button class="carousel-control-next" type="button" data-bs-target="#carosello-evidenza" data-bs-slide="next" aria-label="Successivo">
            <span class="carousel-control-next-icon"></span>
            <span class="visually-hidden">Successivo</span>
        </button>
    <?php } ?>

</div>

<?php else:
    $p = $valid_posts[0];
    setup_postdata($p);

    $img               = dci_get_meta("immagine", $prefix, $p->ID);
    $arrdata           = dci_get_data_pubblicazione_arr("data_pubblicazione", $prefix, $p->ID);
    $descrizione_breve = dci_get_meta("descrizione_breve", $prefix, $p->ID);
    $luogo_notizia     = dci_get_meta("luoghi", $prefix, $p->ID);

    $tipo_terms = wp_get_post_terms($p->ID, 'tipi_notizia');
    $tipo       = (!empty($tipo_terms) && !is_wp_error($tipo_terms)) ? $tipo_terms[0] : null;

    $dayPubblicazione = '';
    $monthPubblicazione = '';
    $yearPubblicazione = '';
    $monthName = '';

    if (is_array($arrdata) && count($arrdata) >= 3) {
        $dayPubblicazione   = trim((string) $arrdata[0]);
        $monthPubblicazione = trim((string) $arrdata[1]);
        $yearPubblicazione  = trim((string) $arrdata[2]);
        $yearPubblicazione  = strlen($yearPubblicazione) == 2 ? '20' . $yearPubblicazione : $yearPubblicazione;

        $monthName = date_i18n('M', mktime(0, 0, 0, (int) $monthPubblicazione, 10));
    }
?>

<div class="row single-news single-news-custom">
    <h2 id="novita-in-evidenza" class="visually-hidden">Novità in evidenza</h2>

    <div class="row">
        <div class="col-lg-5 order-2 order-lg-1">
            <div class="card mb-0">
                <div class="card-body pb-2">
                    <div class="category-top d-flex align-items-center mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" class="icon icon-md me-2" style="width:18px !important;height:18px !important;" aria-hidden="true">
                            <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/>
                        </svg>
                        <?php if ($tipo) { ?>
                            <span class="title-xsmall-semi-bold fw-semibold">
                                <a href="<?php echo esc_url(site_url('tipi_notizia/' . sanitize_title($tipo->name))); ?>"
                                   class="category text-decoration-none title-xsmall-semi-bold fw-semibold">
                                    <?php echo esc_html(strtoupper($tipo->name)); ?>
                                </a>
                            </span>
                        <?php } ?>
                    </div>
                    

                    <a href="<?php echo esc_url(get_permalink($p->ID)); ?>" class="text-decoration-none">
                        <h3 class="card-title">
                            <?php echo esc_html(preg_match('/[A-Z]{5,}/', $p->post_title) ? ucfirst(strtolower($p->post_title)) : $p->post_title); ?>
                        </h3>
                    </a>

                    <p class="mb-2 font-serif descrizione-breve-notizia">
                        <?php echo esc_html(preg_match('/[A-Z]{5,}/', $descrizione_breve) ? ucfirst(strtolower($descrizione_breve)) : $descrizione_breve); ?>
                    </p>

                    <!-- Luoghi -->
                    <?php if (is_array($luogo_notizia) && count($luogo_notizia)): ?>
                        <span class="data fw-normal" style="align-items: center !important;">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" style="width:18px !important;height:18px !important;" class="me-1 icon icon-md" aria-hidden="true">
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
                    <?php if ($dayPubblicazione !== '' && $monthName !== '' && $yearPubblicazione !== ''): ?>
                        <div class="row mt-2 mb-1">
                            <div class="col-6">
                                <small>Data:</small>
                                <p class="fw-semibold font-monospace">
                                    <?php echo esc_html($dayPubblicazione . ' ' . $monthName . ' ' . $yearPubblicazione); ?>
                                </p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Argomenti -->
                    <?php
                        $argomenti = wp_get_post_terms($p->ID, 'argomenti');
                        if (!empty($argomenti) && !is_wp_error($argomenti)) :
                        ?>
                            <small>Argomenti:</small>
                            <ul class="d-flex flex-wrap gap-1 list-unstyled mb-0">
                                <?php foreach ($argomenti as $item) : ?>
                                    <li>
                                        <a class="chip chip-simple" href="<?php echo esc_url(get_term_link($item)); ?>">
                                            <span class="chip-label">
                                                <?php echo esc_html($item->name); ?>
                                            </span>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>

                <a class="read-more mt-4 d-inline-flex align-items-center"
                href="<?php echo esc_url(get_permalink($p->ID)); ?>">
                    <span class="text">Vai alla pagina</span>
                    <svg class="icon ms-1">
                        <use xlink:href="#it-arrow-right"></use>
                    </svg>
                </a>
                </div>
            </div>
        </div>

        <?php if ($img): ?>
        <div class="col-12 col-lg-6 offset-lg-1 order-1 order-lg-2 px-0 px-lg-2 col-img d-flex">
            <?php dci_get_img($img, 'img-fluid img-evidenza'); ?>
        </div>
    <?php endif; ?>

    </div>
</div>

<?php wp_reset_postdata(); endif; ?>

<style>

    .argomenti-wrapper {
    display: block;
    width: 100%;
    margin-left: 0 !important;
    padding-left: 0 !important;
}

.argomenti-badges {
    display: block;
    width: 100%;
}

.argomenti-badges * {
    max-width: 100%;
}
#carosello-evidenza .card-body .read-more,
.single-news.single-news-custom .card-body .read-more {
    align-self: flex-start;
    margin-left: 0 !important;
    padding-left: 0 !important;
    text-align: left;
    align-self: flex-start;
}

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
#carosello-evidenza .card-body .descrizione-breve-notizia {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Mantiene l’immagine sempre visibile anche con testo lungo */
#carosello-evidenza .col-img {
    align-items: flex-start !important;
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
    max-height: 350px !important;
}

/* Testo della card */
#carosello-evidenza .card-body {
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    min-height: 320px;
    padding: 0 1rem;
}

/* Versione desktop (da 992px in su) */
@media (min-width: 992px) {
    #carosello-evidenza .card-body {
        padding: 0 1rem;
    }

    #carosello-evidenza .col-img {
        justify-content: flex-end;
        padding: 0 1rem;
        min-height: 400px;
    }

    #carosello-evidenza img.img-evidenza {
        max-width: auto;
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
.single-news.single-news-custom .descrizione-breve-notizia {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
}
.single-news.single-news-custom .row .col-lg-5.order-2.order-lg-1 {
    padding-left: 0rem;
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

/* Vecchio stile immagine singola */
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
}
*/

.single-news .row .col-lg-5.order-2.order-lg-1 {
    padding-left: 1.5rem;
    padding-right: 1rem;
}

/* Modifica per centrare l'immagine sui dispositivi mobili */
@media (max-width: 991px) {

    #carosello-evidenza .col-img,
    .single-news.single-news-custom .col-img {
        justify-content: center;
        align-items: center;
        min-height: auto;
        padding: 0.75rem 1rem 1rem 1rem;
    }

    #carosello-evidenza img.img-evidenza,
    .single-news.single-news-custom .col-img img.img-fluid.img-evidenza {
        display: block;
        margin: 0 auto;
        max-width: 78%;
        max-height: 220px !important;
        width: auto;
        height: auto;
        object-fit: contain;
    }

    #carosello-evidenza .carousel-item {
        min-height: auto;
    }

    #carosello-evidenza .card-title {
        -webkit-line-clamp: 4;
    }

    #carosello-evidenza .card-body,
    .single-news.single-news-custom .card-body {
        min-height: auto;
    }
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