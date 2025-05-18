<?php
/**
 * Progetto template file
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Design_Comuni_Italia
 */

global $uo_id, $inline;

get_header();
?>

    <main>
        <?php 
        while ( have_posts() ) {
            the_post();
            $user_can_view_post = dci_members_can_user_view_post(get_current_user_id(), $post->ID);

            $prefix= '_dci_progetto_';
            $nome_misura = dci_get_meta("nome_misura", $prefix, $post->ID);
            $descrizione_scopo = dci_get_meta("descrizione_scopo", $prefix, $post->ID);
            $data_pubblicazione_arr = dci_get_data_pubblicazione_arr("data_pubblicazione", $prefix, $post->ID);
            $date = date_i18n('d F Y', mktime(0, 0, 0, $data_pubblicazione_arr[1], $data_pubblicazione_arr[0], $data_pubblicazione_arr[2]));
            
            $missioni = get_the_terms($post, 'tipi_progetto');
            $img = dci_get_meta('immagine', $prefix, $uo_id);

            $componente=dci_get_meta("componente",$prefix,$post->ID);
            $investimento=dci_get_meta("investimento",$prefix,$post->ID);
            $intervento=dci_get_meta("intervento",$prefix,$post->ID);
            $titolare=dci_get_meta("titolare",$prefix,$post->ID);
            $attuatore=dci_get_option("nome_comune");
            $cup=dci_get_meta("cup",$prefix,$post->ID);

            $importo=dci_get_meta("importo",$prefix,$post->ID);
            $modalita_accesso=dci_get_meta("modalita",$prefix,$post->ID);
            $attivita_finanziata=dci_get_meta("attivita",$prefix,$post->ID);
            $avanzamento_progetto=dci_get_meta("avanzamento",$prefix,$post->ID);

            $file_avanzamento= dci_get_meta("avanzamento_allegati", $prefix, $post->ID);
            $atti = dci_get_meta("atti", $prefix, $post->ID);
            $allegati = dci_get_meta("allegati", $prefix, $post->ID);

            $a_cura_di = dci_get_meta("a_cura_di", $prefix, $post->ID);

            ?>
            <div class="container" id="main-container">
                <div class="row">
                    <div class="col px-lg-4">
                        <?php get_template_part("template-parts/common/breadcrumb"); ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-8 px-lg-4 py-lg-2">
                        <h1 data-audio><?php the_title(); ?></h1>
                        <h2 class="visually-hidden" data-audio>Dettagli della notizia</h2>
                        <p data-audio>
                            <?php echo $nome_misura; ?>
                        </p>
                        
                        <div class="row mt-5 mb-4">
                            <div class="col-6">
                                <small>Data di pubblicazione:</small>
                                <p class="fw-semibold font-monospace">
                                    <?php echo $date; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 offset-lg-1">
                        <?php
                        $inline = true;
                        get_template_part('template-parts/single/actions2');
                        ?>
                    </div>                 
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
                                                        <button
                                                            class="accordion-button pb-10 px-3 text-uppercase"
                                                            type="button"
                                                            aria-controls="collapse-one"
                                                            aria-expanded="true"
                                                            data-bs-toggle="collapse"
                                                            data-bs-target="#collapse-one"
                                                        >INDICE DELLA PAGINA
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
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#descrizione">
                                                                    <span class="title-medium">Descrizione e scopo</span>
                                                                    </a>
                                                                </li>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#dettagli">
                                                                    <span class="title-medium">Dettagli</span>
                                                                    </a>
                                                                </li>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#importo">
                                                                    <span class="title-medium">Importo Finanziato</span>
                                                                    </a>
                                                                </li>
                                                                <?php if(isset($modalita_accesso) AND !empty($modalita_accesso)){ ?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#modalita">
                                                                    <span class="title-medium">Modalità di Accesso al Finanziamento</span>
                                                                    </a>
                                                                </li>
                                                                <?php }?>
                                                                <?php if(isset($attivita_finanziata) AND !empty($attivita_finanziata)){?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#attivita">
                                                                    <span class="title-medium">Attività Finanziate</span>
                                                                    </a>
                                                                </li>
                                                                <?php } ?>
                                                                <?php if(isset($avanzamento_progetto) AND !empty($avanzamento_progetto)){?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#avanzamento">
                                                                    <span class="title-medium">Avanzamento del progetto</span>
                                                                    </a>
                                                                </li>
                                                                <?php } ?>
                                                                <?php if(isset($atti) AND !empty($atti)){?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#atti">
                                                                    <span class="title-medium">Atti legislativi e amministrativi</span>
                                                                    </a>
                                                                </li>
                                                                <?php } ?>
                                                                <?php if(isset($allegati) AND !empty($allegati)){?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#allegati">
                                                                    <span class="title-medium">Altri allegati</span>
                                                                    </a>
                                                                </li>
                                                                <?php } ?>
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
                    <div class="col-lg-8 it-page-sections-container border-light">
                        <div class="container-fluid my-3">
                            <div class="row">
                                <figure class="figure px-0 img-small">
                                    <img
                                        src="<?php echo $img; ?>"
                                        class="figure-img img-fluid"
                                        width="500"
                                    />
                                </figure>
                            </div>
                        </div>
                    <article class="it-page-section anchor-offset" data-audio>                        
                        <h4 class="h4" id="descrizione">Descrizione e Scopo</h4>
                        <div class="richtext-wrapper lora text-justify">
                            <?php echo $descrizione_scopo; ?>
                        </div>
                    </article>
                    <br>

                    <article class="it-page-section anchor-offset" data-audio>                        
                        <h4 class="h4" id="dettagli">Dettagli</h4>
                        <div class="richtext-wrapper lora text-justify">
                            <strong>Missione: </strong><?php foreach ($missioni as $missione) { ?>
                                <?= $missione->name?><br>
                            <?php }?>
                            <strong>Componente: </strong> <?= $componente; ?><br>
                            <strong>Investimento: </strong> <?= $investimento; ?><br>
                            <strong>Intervento: </strong> <?= $intervento; ?><br>
                            <strong>Titolore: </strong> <?= $titolare; ?><br>
                            <strong>Soggetto Attuatore: </strong> <?= $attuatore; ?><br>
                            <strong>CUP: </strong> <?= $cup; ?><br>
                        </div>
                    </article>
                    <br>
                    
                    <?php if(isset($importo) AND !empty($importo)){?>
                        <article class="it-page-section anchor-offset" data-audio>                        
                            <h4 class="h4" id="importo">Importo Finanziato</h4>
                            <div class="richtext-wrapper lora  text-justify">
                                <?= $importo; ?>
                            </div>
                            <br>
                            <div class="col-12 footer-items-wrapper logo-wrapper">
                                    <img class="ue-logo" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRnDeHmoF5L5Fkqq-Ohesy45F6z-_ku02O2Fg&s" width="50%">
                            </div>  
                        </article>
                        <br>
                    <?php }?>
                   
                    <?php if(isset($modalita_accesso) AND !empty($modalita_accesso)){?>
                        <article class="it-page-section anchor-offset" data-audio>                        
                            <h4 class="h4" id="modalita">Modalità di Accesso al Finanziamento</h4>
                            <div class="richtext-wrapper lora text-justify">
                                <?= $modalita_accesso; ?>
                            </div>
                        </article>
                        <br>
                    <?php }?>

                    
                    <?php if(isset($attivita_finanziata)AND !empty($attivita_finanziata)){?>
                        <article class="it-page-section anchor-offset" data-audio>                        
                            <h4 class="h4" id="attivita">Modalità di Accesso al Finanziamento</h4>
                            <div class="richtext-wrapper lora text-justify">
                                <?= $attivita_finanziata; ?>
                            </div>
                        </article>
                        <br>
                    <?php } ?>
                    
                    <?php if(isset($avanzamento_progetto) AND !empty($avanzamento_progetto)){?>
                        <article class="it-page-section anchor-offset mt-5">
                            <h4 class="h4" id="avanzamento">Avanzamento del Progetto</h4>
                            <div class="card-wrapper card-teaser-wrapper card-teaser-wrapper-equal  text-justify">
                                <?= $avanzamento_progetto; ?>
                            </div>
                            <?php if(is_array($file_avanzamento) AND count($file_avanzamento)){?>
                                <div class="card-wrapper card-teaser-wrapper card-teaser-wrapper-equal text-justify">
                                <?php foreach ($file_avanzamento as $all_url) {
                                    $all_id = attachment_url_to_postid($all_url);
                                    $file = get_post($all_id);
                                ?>
                                    <div class="card card-teaser shadow-sm p-4 mt-3 rounded border border-light flex-nowrap">
                                        <svg class="icon" aria-hidden="true">
                                        <use
                                            xlink:href="#it-clip"
                                        ></use>
                                        </svg>
                                        <div class="card-body">
                                        <h5 class="card-title">
                                            <a class="text-decoration-none" target="_blanck" href="<?php echo get_the_guid($file); ?>" aria-label="Scarica l'allegato <?php echo $file->post_title; ?>" title="Scarica il file <?php echo $file->post_title; ?>">
                                                <?php echo $file->post_title; ?>
                                            </a>
                                        </h5>
                                        </div>
                                    </div>
                                <?php } ?>
                                </div>
                            <?php } ?>
                        </article>
                    <?php }?>
                    
                    <?php if( is_array($atti) && count($atti) ) { ?>
                    <article class="it-page-section anchor-offset mt-5">
                        <h4 id="atti">Atti Legislativi e Amministrativi</h4>
                        <div class="card-wrapper card-teaser-wrapper card-teaser-wrapper-equal">
                            <?php foreach ($atti as $all_url) {
                                $all_id = attachment_url_to_postid($all_url);
                                $atto = get_post($all_id);
                            ?>
                            <div class="card card-teaser shadow-sm p-4 mt-3 rounded border border-light flex-nowrap">
                                <svg class="icon" aria-hidden="true">
                                <use
                                    xlink:href="#it-clip"
                                ></use>
                                </svg>
                                <div class="card-body">
                                <h5 class="card-title">
                                    <a class="text-decoration-none" target="_blanck" href="<?php echo get_the_guid($atto); ?>" aria-label="Scarica l'allegato <?php echo $atto->post_title; ?>" title="Scarica l'atto <?php echo $atto->post_title; ?>">
                                        <?php echo $atto->post_title; ?>
                                    </a>
                                </h5>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                    </article>
                    <?php } ?>

                    <?php if( is_array($allegati) && count($allegati) ) { ?>
                    <article class="it-page-section anchor-offset mt-5">
                        <h4 id="allegati">Alti Allegati</h4>
                        <div class="card-wrapper card-teaser-wrapper card-teaser-wrapper-equal">
                            <?php foreach ($allegati as $all_url) {
                                $all_id = attachment_url_to_postid($all_url);
                                $allegato = get_post($all_id);
                            ?>
                            <div class="card card-teaser shadow-sm p-4 mt-3 rounded border border-light flex-nowrap">
                                <svg class="icon" aria-hidden="true">
                                <use
                                    xlink:href="#it-clip"
                                ></use>
                                </svg>
                                <div class="card-body">
                                <h5 class="h5" class="card-title">
                                    <a class="text-decoration-none" target="_blanck" href="<?php echo get_the_guid($allegato); ?>" aria-label="Scarica l'allegato <?php echo $allegato->post_title; ?>" title="Scarica l'allegato <?php echo $allegato->post_title; ?>">
                                        <?php echo $allegato->post_title; ?>
                                    </a>
                                </h5>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                    </article>
                    <?php } ?>

                    <article class="it-page-section anchor-offset mt-5">
                        <h4 class="h4" id="a-cura-di">A cura di</h4>
                        <div class="row">
                        <div class="col-12 col-sm-8">
                            <h6><small>Questa pagina è gestita da</small></h6>
                            <?php foreach ($a_cura_di as $uo_id) {
                                $with_border = true;
                                get_template_part("template-parts/unita-organizzativa/card");
                            } ?>
                        </div>
                    </article>

                    <article
                        id="ulteriori-informazioni"
                        class="it-page-section anchor-offset mt-5"
                    >
                        <h4 class="mb-3">Ulteriori informazioni</h4>
                    </article>
                    <?php get_template_part('template-parts/single/page_bottom'); ?>
                </section>
                </div>
            </div>
            </div>
            <?php get_template_part("template-parts/common/valuta-servizio"); ?>
        <?php } ?>
    </main>
    <?php
get_footer();?>

