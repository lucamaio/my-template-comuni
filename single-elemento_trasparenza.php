<?php
/**
 *  elemento_trasparenza template file
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
        $user_can_view_post = dci_members_can_user_view_post(get_current_user_id(), $post->ID);

        $prefix= '_dci_elemento_trasparenza_';
        $descrizione_breve = dci_get_meta("descrizione_breve", $prefix, $post->ID);
        $file = dci_get_meta("file", $prefix, $post->ID);
        $url = dci_get_meta("url", $prefix, $post->ID);
        $data_pubblicazione_arr = dci_get_data_pubblicazione_arr("data_pubblicazione", $prefix, $post->ID);
        $data_pubblicazione = date_i18n('d F Y', mktime(0, 0, 0, $data_pubblicazione_arr[1], $data_pubblicazione_arr[0], $data_pubblicazione_arr[2]));

        $data_scadenza_arr = dci_get_data_pubblicazione_arr("data_scadenza", $prefix, $post->ID);
        $data_scadenza = date_i18n('d F Y', mktime(0, 0, 0, $data_scadenza_arr[1], $data_scadenza_arr[0], $data_scadenza_arr[2]));

        $documenti_collegati= dci_get_meta("post_trasparenza", $prefix, $post->ID);
        //$ck_link= dci_get_meta('open_direct', $prefix, $post->ID) === 'on';      

    ?>
    <div class="container" id="main-container">
        <div class="row">
            <div class="col px-lg-4">
                <?php get_template_part("template-parts/common/breadcrumb"); ?>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-8 px-lg-4 py-lg-2">
                <?php if (preg_match('/[A-Z]{5,}/', get_the_title())) {
                    echo '<h1 data-audio>' . ucfirst(strtolower(get_the_title())) . '</h1>';
                } else {
                    echo '<h1 data-audio>' . get_the_title() . '</h1>';
                } ?>
                <h2 class="visually-hidden" data-audio>Dettagli della notizia</h2>
                <?php if (preg_match('/[A-Z]{5,}/', $descrizione_breve)) {
                    echo '<p data-audio>' . ucfirst(strtolower($descrizione_breve)) . '</p>';
                } else {
                    echo '<p data-audio>' . $descrizione_breve . '</p>';
                } ?>     
            </div>
            <div class="col-lg-3 offset-lg-1">
                <?php
                $inline = true;
                get_template_part('template-parts/single/actions');
                ?>
            </div> 
        </div>
        <div class="row mt-5 mb-4">
            <div class="col-6">
                <small>Data pubblicazione:</small>
                <p class="fw-semibold font-monospace">
                    <?php echo $data_pubblicazione; ?>
                </p>
            </div>
            <?php if (!empty($data_scadenza)) { ?>
            <div class="col-6">
                <small>Data scadenza:</small>
                <p class="fw-semibold font-monospace">
                    <?php echo $data_scadenza; ?>
                </p>
            </div>
            <?php } ?>
        </div> 
    </div>

    <div class="container">
        <div class="row border-top border-light row-column-border row-column-menu-left">
            <aside class="col-lg-4">
                <div class="cmp-navscroll sticky-top" aria-labelledby="accordion-title-one">
                    <nav class="navbar it-navscroll-wrapper navbar-expand-lg" aria-label="Indice della pagina" data-bs-navscroll>
                        <div class="navbar-custom" id="navbarNavProgress">
                            <div class="menu-wrapper">
                                <div class="link-list-wrapper">
                                    <div class="accordion">
                                        <div class="accordion-item">
                                            <span class="accordion-header" id="accordion-title-one">
                                                <button class="accordion-button pb-10 px-3 text-uppercase"
                                                    type="button"
                                                    aria-controls="collapse-one"
                                                    aria-expanded="true"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#collapse-one">
                                                    INDICE DELLA PAGINA
                                                    <svg class="icon icon-sm icon-primary align-top">
                                                        <use xlink:href="#it-expand"></use>
                                                    </svg>
                                                </button>
                                            </span>
                                            <div class="progress">
                                                <div class="progress-bar it-navscroll-progressbar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <div id="collapse-one" class="accordion-collapse collapse show" role="region" aria-labelledby="accordion-title-one">
                                                <div class="accordion-body">
                                                    <ul class="link-list" data-element="page-index">
                                                        <?php if (!empty($descrizione_breve)) { ?>
                                                        <li class="nav-item">
                                                            <a class="nav-link" href="#descrizione">
                                                                <span class="title-medium">Descrizione</span>
                                                            </a>
                                                        </li>
                                                        <?php } ?>
                                                        <?php if (!empty($file)) { ?>
                                                        <li class="nav-item">
                                                            <a class="nav-link" href="#documenti">
                                                                <span class="title-medium">Documenti</span>
                                                            </a>
                                                        </li>
                                                        <?php } ?>
                                                        <?php if (!empty($url)) { ?>
                                                        <li class="nav-item">
                                                            <a class="nav-link" href="#url">
                                                                <span class="title-medium">Link alla pagina</span>
                                                            </a>
                                                        </li>
                                                        <?php } ?>
                                                         <?php if (!empty($documenti_collegati)) { ?>
                                                        <li class="nav-item">
                                                            <a class="nav-link" href="#doc">
                                                                <span class="title-medium">Documenti Collegati</span>
                                                            </a>
                                                        </li>
                                                        <?php } ?>
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
                <?php if (!empty($descrizione_breve)) { ?>
                <article class="it-page-section anchor-offset" data-audio>                        
                    <h4 id="descrizione">Descrizione</h4>
                    <div class="richtext-wrapper lora">
                        <?php 
                        if (preg_match('/[A-Z]{5,}/', $descrizione_breve)) {
                            echo ucfirst(strtolower($descrizione_breve));
                        } else {
                            echo $descrizione_breve;
                        }
                        ?>
                    </div>
                </article> 
                <?php } ?>

                <?php if (!empty($file)) { ?>
                <article class="it-page-section anchor-offset mt-5">
                    <h4 id="documenti">Documenti</h4>
                    <div class="card-wrapper card-teaser-wrapper card-teaser-wrapper-equal">
                        <div class="card card-teaser shadow-sm p-4 mt-3 rounded border border-light flex-nowrap">
                            <svg class="icon" aria-hidden="true">
                                <use xlink:href="#it-clip"></use>
                            </svg>
                            <div class="card-body">
                                <h5 class="card-title">
                                    <a class="text-decoration-none" href="<?php echo $file ?>" aria-label="Visualizza il documento" title="Visualizza il documento">
                                        Visualizza il documento
                                    </a>
                                </h5>
                            </div>
                        </div>
                    </div>
                </article>
                <?php } ?>

                <?php if (!empty($url)) { ?>
                <article class="it-page-section anchor-offset mt-5">
                    <h4 id="url">Link</h4>
                    <div class="card-wrapper card-teaser-wrapper card-teaser-wrapper-equal">
                        <div class="card card-teaser shadow-sm p-4 mt-3 rounded border border-light flex-nowrap">
                            <svg class="icon" aria-hidden="true">
                                <use xlink:href="#it-clip"></use>
                            </svg>
                            <div class="card-body">
                                <h5 class="card-title">
                                    <a class="text-decoration-none" href="<?php echo $url ?>" aria-label="Vai alla pagina" title="Vai alla pagina">
                                        Vai alla pagina
                                    </a>
                                </h5>
                            </div>
                        </div>
                    </div>
                </article>
                <?php } ?>

                <?php if (!empty($documenti_collegati)) { ?>
                <article class="it-page-section anchor-offset mt-5"">
                    <h2 class="h3 mb-3" id="doc">Documenti correlati</h2>
                    <div class="richtext-wrapper lora" data-element="service-document">
                        <div class="row">
                            <?php
                            foreach ($documenti_collegati as $documento_id) { ?>
                                <div class="col-12 col-md-6 mb-3 card-wrapper">
                                    <?php
                                    $documento = get_post($documento_id);
                                    get_template_part("template-parts/documento/card");
                                    ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </article>
                <?php }?>
            </section>
        </div> 
    </div>
    <?php get_template_part("template-parts/common/valuta-servizio"); ?>
    <?php get_template_part("template-parts/common/assistenza-contatti"); ?>
</main>

<?php
endwhile; // End of the loop.
get_footer();
?>
