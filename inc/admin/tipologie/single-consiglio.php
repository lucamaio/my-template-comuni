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
    <?php 
    while ( have_posts() ) :
        the_post();

        $prefix = '_dci_consiglio_';

        $ordini_giorno     = dci_get_meta("ordini_giorno", $prefix, $post->ID);
        $descrizione_breve = dci_get_meta("descrizione_breve", $prefix, $post->ID);
        $data_arr          = dci_get_data_pubblicazione_arr("data", $prefix, $post->ID);

        // Format data
        $date = !empty($data_arr) 
            ? date_i18n('d F Y', mktime(0, 0, 0, $data_arr[1], $data_arr[0], $data_arr[2]))
            : '';

        $ora_inizio = dci_get_meta("ora_inizio", $prefix, $post->ID);
        $ora_fine   = dci_get_meta("ora_fine", $prefix, $post->ID);

        $documenti  = dci_get_meta("documenti", $prefix, $post->ID);
        $allegati   = dci_get_meta("allegati", $prefix, $post->ID);
        $a_cura_di  = dci_get_meta("a_cura_di", $prefix, $post->ID);
    ?>
    <div class="container" id="main-container">
        <div class="row">
            <div class="col px-lg-4">
                <?php get_template_part("template-parts/common/breadcrumb"); ?>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 px-lg-4 py-lg-2">

                <?php 
                // Titolo
                $title = get_the_title();
                if (preg_match('/[A-Z]{5,}/', $title)) {
                    $title = ucfirst(strtolower($title));
                }
                echo '<h1 data-audio>' . esc_html($title) . '</h1>';
                ?>

                <h2 class="visually-hidden" data-audio>Dettagli del Consiglio Comunale</h2>

                <?php 
                // Descrizione breve
                if (!empty($descrizione_breve)) {
                    if (preg_match('/[A-Z]{5,}/', $descrizione_breve)) {
                        $descrizione_breve = ucfirst(strtolower($descrizione_breve));
                    }
                    echo '<p data-audio>' . esc_html($descrizione_breve) . '</p>';
                }
                ?>

                <!-- Data e Orari -->
                <div class="row mt-5 mb-4">
                    <div class="col-6">
                        <small>Data:</small>
                        <p class="fw-semibold font-monospace">
                            <?php if (!empty($date)) : ?>
                                <time datetime="<?php echo date('Y-m-d', strtotime($date)); ?>">
                                    <?php echo esc_html($date); ?>
                                </time>
                            <?php else : ?>
                                —
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="col-6">
                        <small>Ora inizio:</small>
                        <p class="fw-semibold">
                            <?php if (!empty($ora_inizio)) : ?>
                                <time datetime="<?php echo date('H:i', strtotime($ora_inizio)); ?>">
                                    <?php echo date_i18n('H:i', strtotime($ora_inizio)); ?>
                                </time>
                            <?php else : ?>
                                —
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="col-6">
                        <small>Ora fine:</small>
                        <p class="fw-semibold">
                            <?php if (!empty($ora_fine)) : ?>
                                <time datetime="<?php echo date('H:i', strtotime($ora_fine)); ?>">
                                    <?php echo date_i18n('H:i', strtotime($ora_fine)); ?>
                                </time>
                            <?php else : ?>
                                —
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Colonna destra: azioni -->
            <div class="col-lg-3 offset-lg-1">
                <?php
                $inline = true;
                get_template_part('template-parts/single/actions');
                ?>
            </div>
        </div>
    </div>

    <!-- Contenuto principale -->
    <div class="container">
        <div class="row border-top border-light row-column-border row-column-menu-left">

            <!-- Aside indice pagina -->
            <aside class="col-lg-4">
                <div class="cmp-navscroll sticky-top" aria-labelledby="accordion-title-one">
                    <nav class="navbar it-navscroll-wrapper navbar-expand-lg" aria-label="Indice della pagina" data-bs-navscroll>
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
                                                        <li class="nav-item">
                                                            <a class="nav-link" href="#descrizione">
                                                                <span class="title-medium">Descrizione</span>
                                                            </a>
                                                        </li>
                                                        <?php if (is_array($documenti) && count($documenti)) : ?>
                                                        <li class="nav-item">
                                                            <a class="nav-link" href="#documenti">
                                                                <span class="title-medium">Documenti</span>
                                                            </a>
                                                        </li>
                                                        <?php endif; ?>
                                                        <?php if (is_array($allegati) && count($allegati)) : ?>
                                                        <li class="nav-item">
                                                            <a class="nav-link" href="#allegati">
                                                                <span class="title-medium">Allegati</span>
                                                            </a>
                                                        </li>
                                                        <?php endif; ?>
                                                        <li class="nav-item">
                                                            <a class="nav-link" href="#a-cura-di">
                                                                <span class="title-medium">A cura di</span>
                                                            </a>
                                                        </li>
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

            <!-- Contenuti sezione principale -->
            <section class="col-lg-8 it-page-sections-container border-light">

                <?php get_template_part('template-parts/single/image-large'); ?>

                <!-- Descrizione -->
                <article class="it-page-section anchor-offset" data-audio>
                    <h4 id="descrizione">Descrizione</h4>
                    <div class="richtext-wrapper lora">
                        <?php echo esc_html($descrizione_breve); ?>
                    </div>
                </article>

                <!-- Documenti -->
                <?php if (is_array($documenti) && count($documenti)) : ?>
                <article class="it-page-section anchor-offset mt-5">
                    <h4 id="documenti">Documenti</h4>
                    <div class="card-wrapper card-teaser-wrapper card-teaser-wrapper-equal">
                        <?php foreach ($documenti as $doc_id) :
                            $documento = get_post($doc_id); ?>
                        <div class="card card-teaser shadow-sm p-4 mt-3 rounded border border-light flex-nowrap">
                            <svg class="icon" aria-hidden="true"><use xlink:href="#it-clip"></use></svg>
                            <div class="card-body">
                                <h5 class="card-title">
                                    <a class="text-decoration-none" href="<?php echo get_permalink($doc_id); ?>"
                                        aria-label="Visualizza il documento <?php echo esc_attr($documento->post_title); ?>"
                                        title="Visualizza il documento <?php echo esc_attr($documento->post_title); ?>">
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
                <?php if (is_array($allegati) && count($allegati)) : ?>
                <article class="it-page-section anchor-offset mt-5">
                    <h4 id="allegati">Allegati</h4>
                    <div class="card-wrapper card-teaser-wrapper card-teaser-wrapper-equal">
                        <?php foreach ($allegati as $all_url) :
                            $all_id   = attachment_url_to_postid($all_url);
                            $allegato = get_post($all_id);
                            if (!$allegato) continue;

                            $title_allegato = $allegato->post_title;
                            if (strlen($title_allegato) > 50) {
                                $title_allegato = substr($title_allegato, 0, 50) . '...';
                            }
                            if (preg_match('/[A-Z]{5,}/', $title_allegato)) {
                                $title_allegato = ucfirst(strtolower($title_allegato));
                            }
                        ?>
                        <div class="card card-teaser shadow-sm p-4 mt-3 rounded border border-light flex-nowrap">
                            <svg class="icon" aria-hidden="true"><use xlink:href="#it-clip"></use></svg>
                            <div class="card-body">
                                <h5 class="card-title">
                                    <a class="text-decoration-none" href="<?php echo get_the_guid($allegato); ?>"
                                        aria-label="Scarica l'allegato <?php echo esc_attr($allegato->post_title); ?>"
                                        title="Scarica l'allegato <?php echo esc_attr($allegato->post_title); ?>">
                                        <?php echo esc_html($title_allegato); ?>
                                    </a>
                                </h5>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </article>
                <?php endif; ?>

                <!-- A cura di -->
                <article class="it-page-section anchor-offset mt-5">
                    <h4 id="a-cura-di">A cura di</h4>
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

                <!-- Ulteriori informazioni -->
                <article id="ulteriori-informazioni" class="it-page-section anchor-offset mt-5">
                    <h4 class="mb-3">Ulteriori informazioni</h4>
                </article>

                <?php get_template_part('template-parts/single/page_bottom'); ?>
            </section>
        </div>
    </div>

    <?php get_template_part("template-parts/common/valuta-servizio"); ?>

    <?php endwhile; ?>
</main>

<?php get_footer(); ?>
