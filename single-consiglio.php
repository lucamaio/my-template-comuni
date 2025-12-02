<?php
/**
 * Template single Consiglio Comunale
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Design_Comuni_Italia
 */

global $uo_id, $inline, $audio;

get_header();
?>

<main>
    <?php while ( have_posts() ) : the_post(); 
    $prefix = '_dci_consiglio_';

        // Controllo opzione portale
        $check_portale = dci_get_option("ck_portaleElencoConsigliComunali");
        if ($check_portale !== 'true') {
            wp_safe_redirect( home_url('/') );
            exit;
        }

    
    $ordini_giorno     = dci_get_meta("ordini_giorno", $prefix, $post->ID);
    $descrizione_breve = dci_get_meta("descrizione_breve", $prefix, $post->ID);
    $data_arr          = dci_get_data_pubblicazione_arr("data", $prefix, $post->ID);

    $date = !empty($data_arr) 
        ? date_i18n('d F Y', mktime(0, 0, 0, $data_arr[1], $data_arr[0], $data_arr[2]))
        : '';

    $ora_inizio = dci_get_meta("ora_inizio", $prefix, $post->ID);
    $ora_fine   = dci_get_meta("ora_fine", $prefix, $post->ID);
    $documenti  = dci_get_meta("documenti", $prefix, $post->ID);
    $allegati   = dci_get_meta("allegati", $prefix, $post->ID);
    $a_cura_di  = dci_get_meta("a_cura_di", $prefix, $post->ID);
    $persone = dci_get_meta("partecipanti", $prefix, $post->ID);  // Partecipanti al consiglio NB: Non cambiare il nome della variabile
    $more_info = dci_get_meta("more_info", $prefix, $post->ID);
    $link_streaming = dci_get_meta("link_streaming", $prefix, $post->ID);
?>

    <div class="container py-4" id="main-container">
        <div class="row">
            <div class="col px-lg-4">
                <?php get_template_part("template-parts/common/breadcrumb"); ?>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-lg-8 px-lg-4 py-lg-2">
                <?php 
                    $title = get_the_title();
                    if (preg_match('/[A-Z]{5,}/', $title)) $title = ucfirst(strtolower($title));
                ?>
                <h1 class="mb-3" data-audio><?php echo esc_html($title); ?></h1>

                <?php if (!empty($descrizione_breve)) : 
                    if (preg_match('/[A-Z]{5,}/', $descrizione_breve)) $descrizione_breve = ucfirst(strtolower($descrizione_breve));
                ?>
                <p class="mb-4" data-audio><?php echo esc_html($descrizione_breve); ?></p>
                <?php endif; ?>

                <!-- Data e orari con icone -->
                <div class="row g-3 mb-0 align-items-center">
                    <!-- Data -->
                    <div class="col-12 col-md-auto d-flex align-items-center mb-2 mb-md-0">
                        <svg class="icon me-2" aria-hidden="true">
                            <use xlink:href="#it-calendar"></use>
                        </svg>
                        <p class="fw-semibold font-monospace mb-0">
                            <?php echo !empty($date) ? '<time datetime="'.date('Y-m-d', strtotime($date)).'">'.esc_html($date).'</time>' : '—'; ?>
                        </p>
                    </div>

                    <!-- Ora inizio -->
                    <div class="col-6 col-md-auto d-flex align-items-center">
                        <svg class="icon me-2" aria-hidden="true">
                            <use xlink:href="#it-clock"></use>
                        </svg>
                        <p class="fw-semibold mb-0">
                            <?php echo !empty($ora_inizio) ? date_i18n('H:i', strtotime($ora_inizio)) : '—'; ?>
                        </p>
                    </div>

                    <!-- Ora fine -->
                    <div class="col-6 col-md-auto d-flex align-items-center">
                        <svg class="icon me-2" aria-hidden="true">
                            <use xlink:href="#it-clock"></use>
                        </svg>
                        <p class="fw-semibold mb-0">
                            <?php echo !empty($ora_fine) ? date_i18n('H:i', strtotime($ora_fine)) : '—'; ?>
                        </p>
                    </div>
                </div>

            </div>

            <div class="col-lg-3 offset-lg-1">
                <?php $inline = true; get_template_part('template-parts/single/actions'); ?>
            </div>
        </div>
    </div>

    <div class="container mt-5">
        <div class="row border-top border-light row-column-border row-column-menu-left">
            <aside class="col-lg-4 mb-4 mb-lg-0">
                <div class="cmp-navscroll sticky-top">
                    <nav class="navbar it-navscroll-wrapper navbar-expand-lg" aria-label="Indice della pagina"
                        data-bs-navscroll>
                        <div class="navbar-custom" id="navbarNavProgress">
                            <div class="menu-wrapper">
                                <div class="link-list-wrapper">
                                    <div class="accordion">
                                        <div class="accordion-item">
                                            <span class="accordion-header" id="accordion-title-one">
                                                <button class="accordion-button pb-10 px-3 text-uppercase" type="button"
                                                    aria-controls="collapse-one" aria-expanded="true"
                                                    data-bs-toggle="collapse" data-bs-target="#collapse-one">
                                                    INDICE DELLA PAGINA
                                                    <svg class="icon icon-sm icon-primary align-top">
                                                        <use xlink:href="#it-expand"></use>
                                                    </svg>
                                                </button>
                                            </span>
                                            <div class="progress">
                                                <div class="progress-bar it-navscroll-progressbar" role="progressbar"
                                                    aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <div id="collapse-one" class="accordion-collapse collapse show"
                                                role="region" aria-labelledby="accordion-title-one">
                                                <div class="accordion-body">
                                                    <ul class="link-list" data-element="page-index">
                                                        <li class="nav-item"><a class="nav-link"
                                                                href="#ordini_giorno"><span class="title-medium">Ordini
                                                                    del giorno</span></a></li>
                                                        <?php if (!empty($documenti)) : ?>
                                                        <li class="nav-item"><a class="nav-link" href="#documenti"><span
                                                                    class="title-medium">Documenti</span></a></li>
                                                        <?php endif; ?>
                                                        <?php if (!empty($allegati)) : ?>
                                                        <li class="nav-item"><a class="nav-link" href="#allegati"><span
                                                                    class="title-medium">Allegati</span></a></li>
                                                        <?php endif; ?>
                                                        <?php if(is_array($persone) && count($persone)) { ?>
                                                        <li class="nav-item"><a class="nav-link"
                                                                href="#partecipanti"><span
                                                                    class="title-medium">Partecipanti</span></a></li>
                                                        <?php } ?>
                                                        <?php if(!empty($more_info)) { ?>
                                                        <li class="nav-item"><a class="nav-link" href="#more-info"><span
                                                                    class="title-medium">Ulteriori
                                                                    Informazioni</span></a></li>
                                                        <?php } ?>
                                                        <?php if(!empty($link_streaming)) { ?>
                                                            <li class="nav-item"><a class="nav-link" href="#link_streaming"><span
                                                                    class="title-medium">Consiglio in Streaming</span></a></li>
                                                        <?php } ?>
                                                        <li class="nav-item"><a class="nav-link" href="#a-cura-di"><span
                                                                    class="title-medium">A cura di</span></a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </nav>
                </div>
            </aside>

            <section class="col-lg-8 it-page-sections-container border-light">
                <?php //get_template_part('template-parts/single/image-large'); ?>

                <!-- Ordini del giorno -->
                <?php if(isset($ordini_giorno) && !empty($ordini_giorno)) { ?>
                <article class="it-page-section anchor-offset mb-5" id="ordini_giorno" data-audio>
                    <h4>Ordini del giorno</h4>
                    <div class="richtext-wrapper lora">
                        <?php echo esc_html($ordini_giorno); ?>
                    </div>
                </article>
                <?php } ?>

                <!-- Documenti -->
                <?php if (!empty($documenti)) : ?>
                <article class="it-page-section anchor-offset mb-5" id="documenti">
                    <h4>Documenti</h4>
                    <div class="card-wrapper card-teaser-wrapper">
                        <?php foreach ($documenti as $doc_id) :
                        $documento = get_post($doc_id); ?>
                        <div class="card card-teaser shadow-sm p-3 mt-3 rounded border border-light flex-nowrap">
                            <svg class="icon" aria-hidden="true">
                                <use xlink:href="#it-clip"></use>
                            </svg>
                            <div class="card-body">
                                <h5 class="card-title">
                                    <a class="text-decoration-none" href="<?php echo get_permalink($doc_id); ?>">
                                        <?php echo esc_html($documento->post_title); ?>
                                    </a>
                                </h5>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </article>
                <?php endif; ?>

                <!-- Allegati -->
                <?php if (!empty($allegati)) : ?>
                <article class="it-page-section anchor-offset mb-5" id="allegati">
                    <h4>Allegati</h4>
                    <div class="card-wrapper card-teaser-wrapper">
                        <?php foreach ($allegati as $all_url) :
                        $all_id   = attachment_url_to_postid($all_url);
                        $allegato = get_post($all_id);
                        if (!$allegato) continue;

                        $title_allegato = $allegato->post_title;
                        if (strlen($title_allegato) > 50) $title_allegato = substr($title_allegato, 0, 50) . '...';
                        if (preg_match('/[A-Z]{5,}/', $title_allegato)) $title_allegato = ucfirst(strtolower($title_allegato));
                    ?>
                        <div class="card card-teaser shadow-sm p-3 mt-3 rounded border border-light flex-nowrap">
                            <svg class="icon" aria-hidden="true">
                                <use xlink:href="#it-clip"></use>
                            </svg>
                            <div class="card-body">
                                <h5 class="card-title">
                                    <a class="text-decoration-none" href="<?php echo get_the_guid($allegato); ?>">
                                        <?php echo esc_html($title_allegato); ?>
                                    </a>
                                </h5>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </article>
                <?php endif; ?>

                <?php if(is_array($persone) && count($persone)) { ?>
                <article class="it-page-section anchor-offset mt-5">
                    <h4 id="partecipanti">Partecipanti</h4>
                    <div class="row">
                        <?php get_template_part("template-parts/single/persone"); ?>
                    </div>
                </article>
                <?php } ?>

                <?php if(!empty($more_info)) { ?>
                <article class="it-page-section anchor-offset mt-5 mb-30">
                    <h4 id="more-info">Ulteriori Informazioni</h4>
                    <div class="richtext-wrapper lora">
                        <?php echo esc_html($more_info); ?>
                    </div>
                </article>
                <?php } ?>

                <!-- // link consiglio streaming -->
                <?php if(!empty($link_streaming)) { ?>
                <article class="it-page-section anchor-offset mt-5 mb-30">
                    <h4 id="link_streaming">Link streaming</h4>
                    <div class="card card-teaser shadow-sm p-3 mt-3 rounded border border-light flex-nowrap">
                            <svg class="icon" aria-hidden="true">
                                <use xlink:href="#it-clip"></use>
                            </svg>
                            <div class="card-body">
                                <h5 class="card-title">
                                    <a class="text-decoration-none" href="<?php echo esc_url($link_streaming); ?>" target="_blank">
                                        Link alla diretta streaming del seduta del Consiglio Comunale
                                    </a>
                                </h5>
                            </div>
                        </div>
                </article>
                <?php } ?>

                <!-- A cura di -->
                <article class="it-page-section anchor-offset mb-5" id="a-cura-di">
                    <h4>A cura di</h4>
                    <div class="row">
                        <div class="col-12 col-sm-8">
                            <h6><small>Questa pagina è gestita da</small></h6>
                            <?php foreach ($a_cura_di as $uo_id) :
                            $with_border = true;
                            get_template_part("template-parts/unita-organizzativa/card");
                        endforeach; ?>
                        </div>
                    </div>
                </article>

                <?php get_template_part('template-parts/single/page_bottom'); ?>
            </section>
        </div>
    </div>

    <?php get_template_part("template-parts/common/valuta-servizio"); ?>
    <?php endwhile; ?>
</main>

<?php get_footer(); ?>
