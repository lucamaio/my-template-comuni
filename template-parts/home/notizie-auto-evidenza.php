<?php
global $numero_notizie_evidenziate;

$numero_notizie_evidenziate = max(1, (int) $numero_notizie_evidenziate);
$prefix = '_dci_notizia_';
$hide_notizie_old = dci_get_option("ck_hide_notizie_old", "homepage");
$posts_per_page = ($hide_notizie_old === 'true') ? max($numero_notizie_evidenziate * 5, 20) : $numero_notizie_evidenziate;

/**
 * Funzione di supporto:
 * restituisce la data custom come DateTime se presente e valida,
 * altrimenti null
 */
if (!function_exists('dci_get_custom_date_from_arr')) {
    function dci_get_custom_date_from_arr($field_name, $prefix, $post_id) {
        $arrdata = dci_get_data_pubblicazione_arr($field_name, $prefix, $post_id);

        if (is_array($arrdata) && count($arrdata) >= 3) {
            $day   = trim((string) $arrdata[0]);
            $month = trim((string) $arrdata[1]);
            $year  = trim((string) $arrdata[2]);

            $year = strlen($year) == 2 ? '20' . $year : $year;

            if ($day !== '' && $month !== '' && $year !== '') {
                $date = DateTime::createFromFormat(
                    'd/m/Y',
                    sprintf('%02d/%02d/%04d', (int) $day, (int) $month, (int) $year)
                );

                if ($date instanceof DateTime) {
                    $date->setTime(0, 0, 0);
                    return $date;
                }
            }
        }

        return null;
    }
}

/**
 * Funzione di supporto:
 * restituisce la data pubblicazione effettiva:
 * - custom se presente
 * - altrimenti data nativa WordPress
 */
if (!function_exists('dci_get_effective_publication_date')) {
    function dci_get_effective_publication_date($prefix, $post_id) {
        $custom_date = dci_get_custom_date_from_arr('data_pubblicazione', $prefix, $post_id);
        if ($custom_date instanceof DateTime) {
            return $custom_date;
        }

        $timestamp = get_post_timestamp($post_id);
        if ($timestamp) {
            $wp_date = new DateTime();
            $wp_date->setTimestamp($timestamp);
            $wp_date->setTime(0, 0, 0);
            return $wp_date;
        }

        return null;
    }
}

/**
 * Funzione di supporto:
 * restituisce i componenti della data da mostrare
 * - custom se presente
 * - altrimenti fallback alla data WordPress
 */
if (!function_exists('dci_get_display_publication_date_parts')) {
    function dci_get_display_publication_date_parts($prefix, $post_id) {
        $day = '';
        $month = '';
        $year = '';
        $monthName = '';

        $custom_date = dci_get_custom_date_from_arr('data_pubblicazione', $prefix, $post_id);

        if ($custom_date instanceof DateTime) {
            $day       = $custom_date->format('d');
            $month     = $custom_date->format('m');
            $year      = $custom_date->format('Y');
            $monthName = date_i18n('M', $custom_date->getTimestamp());

            return array(
                'day'       => $day,
                'month'     => $month,
                'year'      => $year,
                'monthName' => $monthName,
            );
        }

        $timestamp = get_post_timestamp($post_id);
        if ($timestamp) {
            $day       = date_i18n('d', $timestamp);
            $month     = date_i18n('m', $timestamp);
            $year      = date_i18n('Y', $timestamp);
            $monthName = date_i18n('M', $timestamp);
        }

        return array(
            'day'       => $day,
            'month'     => $month,
            'year'      => $year,
            'monthName' => $monthName,
        );
    }
}

/**
 * Query
 * - Recupera tutte le notizie evidenziate
 * - Se il check nascondi notizie vecchie è attivo, il filtro viene applicato dopo
 */
$args = array(
    'post_type'           => 'notizia',
    'meta_query'          => array(
        array(
            'key'   => $prefix . 'evidenzia_home',
            'value' => 'on',
        ),
    ),
    'orderby'             => 'date',
    'order'               => 'DESC',
    'posts_per_page'      => -1,
    'no_found_rows'       => true,
    'ignore_sticky_posts' => true,
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
      solo se la data di scadenza è diversa dalla data di pubblicazione effettiva
    - Se il check non è attivo, includo tutte le notizie evidenziate
*/
$oggi = new DateTime('today');
$oggi->setTime(0, 0, 0);

$valid_posts = array();

foreach ($posts as $p) {

    if (count($valid_posts) >= $numero_notizie_evidenziate) {
        break;
    }

    if ($hide_notizie_old === 'true') {

        $dataPubblicazione = dci_get_effective_publication_date($prefix, $p->ID);
        $dataScadenza = dci_get_custom_date_from_arr('data_scadenza', $prefix, $p->ID);

        /**
         * Escludo la notizia se:
         * - la data di scadenza esiste
         * - è precedente a oggi
         * - ed è diversa dalla data di pubblicazione effettiva
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

        $valid_posts[] = $p;

    } else {
        $valid_posts[] = $p;
    }
}

wp_reset_postdata();

$totale = count($valid_posts);
$rendered = 0;

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
             * - custom se presente
             * - fallback a data WordPress se assente
             */
            $date_parts = dci_get_display_publication_date_parts($prefix, $p->ID);
            $dayPubblicazione   = $date_parts['day'];
            $monthPubblicazione = $date_parts['month'];
            $yearPubblicazione  = $date_parts['year'];
            $monthName          = $date_parts['monthName'];

            $is_active = ($index === 0);
            ?>

            <div class="carousel-item <?php echo $is_active ? 'active' : ''; ?>">
                <div class="container">
                    <div class="row flex-column flex-lg-row align-items-start align-items-lg-center g-0">

                        <!-- IMMAGINE -->
                        <?php if ($img) { ?>
                            <div class="col-12 col-lg-6 order-1 order-lg-2 col-img d-none d-lg-flex">
                                <?php dci_get_img($img, 'img-fluid img-evidenza'); ?>
                            </div>
                        <?php } ?>

                        <!-- TESTO -->
                        <div class="col-12 col-lg-6 order-2 order-lg-1 d-flex align-items-start">
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
                                    <span class="data fw-normal luogo-wrapper" style="align-items: center !important;" aria-label="Luoghi">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" style="width:16px !important;height:16px !important;" class="me-1 icon icon-md" aria-hidden="true">
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
                                            <p class="fw-semibold font-monospace mb-0">
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
                                    <small class="mt-2">Argomenti:</small>
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
    $descrizione_breve = dci_get_meta("descrizione_breve", $prefix, $p->ID);
    $luogo_notizia     = dci_get_meta("luoghi", $prefix, $p->ID);

    $tipo_terms = wp_get_post_terms($p->ID, 'tipi_notizia');
    $tipo       = (!empty($tipo_terms) && !is_wp_error($tipo_terms)) ? $tipo_terms[0] : null;

    $date_parts = dci_get_display_publication_date_parts($prefix, $p->ID);
    $dayPubblicazione   = $date_parts['day'];
    $monthPubblicazione = $date_parts['month'];
    $yearPubblicazione  = $date_parts['year'];
    $monthName          = $date_parts['monthName'];
?>

<div class="row single-news single-news-custom">
    <h2 id="novita-in-evidenza" class="visually-hidden">Novità in evidenza</h2>

    <div class="row align-items-start align-items-lg-center">
        <?php if ($img) { ?>
            <div class="col-lg-6 offset-lg-1 order-1 order-lg-2 px-0 px-lg-2 col-img d-none d-lg-flex">
                <?php dci_get_img($img, 'img-fluid img-evidenza'); ?>
            </div>
        <?php } ?>

        <div class="col-12 col-lg-5 order-2 order-lg-1">
            <div class="card mb-0">
                <div class="card-body pb-2">
                    <div class="category-top d-flex align-items-center mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" class="icon icon-md me-2"  aria-hidden="true">
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
                        <span class="data fw-normal luogo-wrapper" style="align-items: center !important;">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" style="width:16px !important;height:16px !important;" class="me-1 icon icon-md" aria-hidden="true">
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
                                <p class="fw-semibold font-monospace mb-0">
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
                        <small class="mt-2">Argomenti:</small>
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
    </div>
</div>

<?php wp_reset_postdata(); endif; ?>

<style>

/* SOLO per questo componente (carosello + singolo) */
#carosello-evidenza .icon,
#carosello-evidenza .icon-md,
.single-news.single-news-custom .icon,
.single-news.single-news-custom .icon-md {
    width: 18px !important;
    height: 18px !important;
    min-width: 18px;
    min-height: 18px;
}

#carosello-evidenza svg[class*="icon"],
.single-news.single-news-custom svg[class*="icon"] {
    width: 18px !important;
    height: 18px !important;
}

#carosello-evidenza .card-body .read-more,
.single-news.single-news-custom .card-body .read-more {
    align-self: flex-start;
    margin-left: 0 !important;
    padding-left: 0 !important;
    text-align: left;
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

/* Desktop: le righe occupano tutta l’altezza */
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

/* Evita overflow visivo desktop */
#carosello-evidenza .carousel-inner {
    border-radius: 0;
    overflow: hidden;
}

/* Box immagine */
#carosello-evidenza .col-img {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 1rem;
    min-height: 200px;
}

/* Immagine carosello */
#carosello-evidenza img.img-evidenza {
    max-width: 80%;
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

/* Luoghi */
.luogo-wrapper {
    display: flex;
    flex-wrap: wrap;
    gap: 0.25rem;
}

/* Versione desktop */
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
        margin-left: auto;
        margin-right: 0;
    }
}

/* Stili singolo post */
.single-news.single-news-custom .row .col-lg-6.offset-lg-1.order-1.order-lg-2 {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    min-height: 500px;
    padding-right: 3rem;
}

.single-news.single-news-custom .col-img img.img-fluid.img-evidenza {
    max-width: 100%;
    max-height: 400px;
    width: auto;
    height: auto;
    object-fit: contain;
    display: block;
    margin-left: auto;
}

.single-news.single-news-custom h3.card-title {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
}

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

.single-news .row .col-lg-6.offset-lg-1.order-1.order-lg-2 {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    min-height: 450px;
    padding-right: 7rem;
}

.single-news .row .col-lg-5.order-2.order-lg-1 {
    padding-left: 1.5rem;
    padding-right: 1rem;
}

/* MOBILE */
@media (max-width: 991px) {

    /* Centra l'immagine nel carosello e nei post singoli su mobile */
    #carosello-evidenza .col-img,
    .single-news.single-news-custom .row .col-lg-6.offset-lg-1.order-1.order-lg-2 {
        justify-content: center;
    }

    /* Centra l'immagine su mobile */
    #carosello-evidenza img.img-evidenza,
    .single-news.single-news-custom .row .col-lg-6.offset-lg-1.order-1.order-lg-2 img.img-fluid {
        margin: 0 auto;
        max-width: 80%;
        height: auto;
    }

    #carosello-evidenza .carousel-item {
        min-height: 480px;
    }

    #carosello-evidenza .card-title {
        -webkit-line-clamp: 4;
    }
}

</style>