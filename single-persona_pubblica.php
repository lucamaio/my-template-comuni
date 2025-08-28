<?php
/**
 * Persona pubblica template file
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Design_Comuni_Italia
 */
global $uo_id, $file_url, $hide_arguments;

get_header();
?>
<main>
    <?php
    while (have_posts()):
        the_post();
        $user_can_view_post = dci_members_can_user_view_post(get_current_user_id(), $post->ID);

        // Recupero dei metadati con controlli
        $nome = dci_get_meta("nome") ?? '';
        $cognome = dci_get_meta("cognome") ?? '';
        $descrizione_breve = dci_get_meta("descrizione_breve") ?? '';
        $competenze = dci_get_wysiwyg_field("competenze") ?? '';

        $foto_id = dci_get_meta("foto_id");
        
        $img = wp_get_attachment_image_src($foto_id, "item-gallery");
        
        $img1 = dci_get_meta('foto'); // Foto associata all'incarico
        
        $data_insediamento = dci_get_meta("data_inizio_incarico") ?? '';

        $responsabili = dci_get_meta("responsabile") ?? [];
        $responsabile = !empty($responsabili) ? $responsabili[0] : null;

        // Recupera gli incarichi, se esistono
        $incarichi = dci_get_meta("incarichi") ?? []; // Recupera tutti gli incarichi associati al post
    
        if (!empty($incarichi)) {
            // Prende il primo incarico (se esiste) per mostrare il titolo e il tipo di incarico
            $incarico = get_the_title($incarichi[0]);

            // Recupero dei termini di tipo incarico
            $tipo_incarico_terms = get_the_terms(get_post($incarichi[0]), 'tipi_incarico');

            // Controllo se i termini di tipo incarico esistono e non ci sono errori
            if (!empty($tipo_incarico_terms) && !is_wp_error($tipo_incarico_terms) && isset($tipo_incarico_terms[0])) {
                $tipo_incarico = $tipo_incarico_terms[0]->name;
            } else {
                $tipo_incarico = ''; // Valore di fallback se non ci sono termini
            }
        } else {
            $incarico = ''; // Valore di fallback se non ci sono incarichi
            $tipo_incarico = ''; // Valore di fallback se non ci sono incarichi
        }

        $compensi = !empty($incarichi) ? dci_get_meta("compensi", '_dci_incarico_', $incarichi[0]) : [];

        $organizzazioni = dci_get_meta("organizzazioni") ?? [];
        $biografia = dci_get_meta("biografia") ?? '';
        $curriculum_vitae = dci_get_meta("curriculum_vitae") ?? '';
        $situazione_patrimoniale = dci_get_meta("situazione_patrimoniale") ?? '';
        $situazione_patrimoniale_id = dci_get_meta("situazione_patrimoniale_id") ?? '';
        $dichiarazione_redditi = dci_get_meta("dichiarazione_redditi") ?? '';
        $spese_elettorali = dci_get_meta("spese_elettorali") ?? '';
        $descrizione = dci_get_wysiwyg_field("descrizione_estesa") ?? '';
        $punti_contatto = dci_get_meta("punti_contatto") ?? [];

        $prefix = '_dci_punto_contatto_';
        $contatti = [];
        foreach ($punti_contatto as $pc_id) {
            $contatto = dci_get_full_punto_contatto($pc_id);
            if ($contatto) {
                array_push($contatti, $contatto);
            }
        }
        
        
        $altre_cariche = dci_get_meta("altre_cariche") ?? [];
        
        function convertToPlain($text)
        {
            $text = str_replace(array("\r", "\n"), '', $text);
            $text = str_replace('"', '\"', $text);
            $text = str_replace('&nbsp;', ' ', $text);

            return trim(strip_tags($text));
        }
        ?>
    </main>
    <script type="application/ld+json" data-element="metatag">{
                            "name": "<?= $nome ?>",
                            "cognome": "<?= $cognome; ?>",
                            "incarico": "<?= $incarico; ?>";
                    }</script>

    <div class="container" id="main-container">
        <div class="row">
            <div class="col-12 col-lg-10">
                <?php get_template_part("template-parts/common/breadcrumb"); ?>
            </div>
        </div>
    </div>
<div class="container">
    <div class="row">
        <div class="col-12 col-lg-10">
            <div class="cmp-heading pb-3 pb-lg-4">
                <div class="row">
                    <div class="col-12 col-lg-8">
                        <h1 class="title-xxxlarge text-start" data-element="service-title">
                            <?php
                            // Recupera il titolo della pagina
                            $title = get_the_title();
                            // Se il titolo supera i 100 caratteri, lo tronca e aggiunge "..."
                            if (strlen($title) > 100) {
                                $title = substr($title, 0, 97) . '...';
                            }
                            // Controlla se il titolo contiene almeno 5 lettere maiuscole consecutive
                            if (preg_match('/[A-Z]{5,}/', $title)) {
                                // Se sì, lo trasforma in minuscolo con la prima lettera maiuscola
                                $title = ucfirst(strtolower($title));
                            }

                            echo $title;
                            ?>
                        </h1>

                        <p class="subtitle-small mb-3 text-start" data-element="service-description">
                            <?php

                            $description1 = $descrizione_breve;
                            if (preg_match('/[A-Z]{5,}/', $description1)) {
                                // Se sì, lo trasforma in minuscolo con la prima lettera maiuscola
                                $description1 = ucfirst(strtolower($description1));
                            }
                            // Aggiunge il titolo alla lista degli elementi
                            echo $description1; ?>
                        </p>
                    </div>

                    <!-- Colonna per le azioni, che rimane a destra -->
                    <div class="col-12 col-lg-3 offset-lg-1 mt-5 mt-lg-0">
                        <?php
                        $hide_arguments = true;
                        get_template_part('template-parts/single/actions');
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <hr class="d-none d-lg-block mt-2" />
</div>

    <div class="container">
        <div class="row row-column-menu-left mt-4 mt-lg-80 pb-lg-80 pb-40">
            <div class="col-12 col-lg-3 mb-4 border-col">
                <div class="cmp-navscroll sticky-top" aria-labelledby="accordion-title-one">
                    <nav class="navbar it-navscroll-wrapper navbar-expand-lg" aria-label="Indice della pagina"
                        data-bs-navscroll>
                        <div class="navbar-custom" id="navbarNavProgress">
                            <div class="menu-wrapper">
                                <div class="link-list-wrapper">
                                    <div class="accordion">
                                        <div class="accordion-item">
                                            <span class="accordion-header" id="accordion-title-one">

                                                        <style>
                                                        /* Stile più leggero per la foto */
                                                        .img-resized {
                                                            border: 1px solid #ddd;              /* Bordo molto leggero */
                                                            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05); /* Ombra quasi impercettibile */
                                                            padding: 2px;
                                                            background-color: #fff;              /* Sfondo bianco per risalto se su sfondo grigio */
                                                            border-radius: 6px;                  /* Angoli leggermente arrotondati */
                                                        }
                                                        
                                                        </style>



                                                <?php if ($img1) { ?>
                                                    <section class="hero-img mb-20 mb-lg-50">
                                                   
                                                            <center>
                                                                <div class="img-wrapper">
                                                                    <!-- Applica la classe CSS img-resized per ridimensionare l'immagine -->
                                                                    <?php dci_get_img($img1, 'rounded img-fluid img-responsive foto-soft-style img-resized'); ?>
                                                                </div>
                                                            </center>
                                               
                                                    </section>
                                                <?php } ?>


                                                
                                                <button class="accordion-button pb-10 px-3 text-uppercase" type="button"
                                                    data-bs-toggle="collapse" data-bs-target="#collapse-one"
                                                    aria-expanded="true" aria-controls="collapse-one">
                                                    Indice della pagina
                                                    <svg class="icon icon-xs right">
                                                        <use href="#it-expand"></use>
                                                    </svg>
                                                </button>
                                            </span>

                                            <div class="progress">
                                                <div class="progress-bar it-navscroll-progressbar" role="progressbar"
                                                    aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>

                                            <div id="collapse-one" class="accordion-collapse collapse show" role="region"
                                                aria-labelledby="accordion-title-one">
                                                <div class="accordion-body">
                                                    <ul class="link-list" data-element="page-index">
                                                        <?php if ($incarichi) { ?>
                                                            <li class="nav-item">
                                                                <a class="nav-link" href="#incarichi">
                                                                    <span>Incarichi</span>
                                                                </a>
                                                            </li>
                                                        <?php } ?>
                                                        <?php if ($compensi) { ?>
                                                            <li class="nav-item">
                                                                <a class="nav-link" href="#compensi">
                                                                    <span>Compensi</span>
                                                                </a>
                                                            </li>
                                                        <?php } ?>
                                                        <?php if (isset($data_insediamento) and !empty($data_insediamento and $data_insediamento != NULL)) { ?>
                                                            <li class="nav-item">
                                                                <a class="nav-link" href="#data">
                                                                    <span>Data di
                                                                        <?php if ($tipo_incarico == "politico") {
                                                                            echo 'Insediamento';
                                                                        } else {
                                                                            echo 'inizio incarico';
                                                                        } ?></span>
                                                                </a>
                                                            </li>
                                                        <?php } ?>
                                                        <?php if ($organizzazioni) { ?>
                                                            <li class="nav-item">
                                                                <a class="nav-link" href="#organizzazioni">
                                                                    <span>Organizzazione</span>
                                                                </a>
                                                            </li>
                                                        <?php } ?>
                                                        <?php if ($competenze) { ?>
                                                            <li class="nav-item">
                                                                <a class="nav-link" href="#competenze">
                                                                    <span>Competenze</span>
                                                                </a>
                                                            </li>
                                                        <?php } ?>
                                                        <?php if ($biografia) { ?>
                                                            <li class="nav-item">
                                                                <a class="nav-link" href="#bio">
                                                                    <span>Biografia</span>
                                                                </a>
                                                            </li>
                                                        <?php } ?>
                                                        <?php if ($contatti) { ?>
                                                            <li class="nav-item">
                                                                <a class="nav-link" href="#contatti">
                                                                    <span>Contatti</span>
                                                                </a>
                                                            </li>
                                                        <?php } ?>
                                                        <?php if ($curriculum_vitae) { ?>
                                                            <li class="nav-item">
                                                                <a class="nav-link" href="#cv">
                                                                    <span>Curriculum Vitae</span>
                                                                </a>
                                                            </li>
                                                        <?php } ?>
                                                        <?php if ($situazione_patrimoniale) { ?>
                                                            <li class="nav-item">
                                                                <a class="nav-link" href="#situazione-patrimoniale">
                                                                    <span>Situazione patrimoniale</span>
                                                                </a>
                                                            </li>
                                                        <?php } ?>
                                                        <?php if ($dichiarazione_redditi) { ?>
                                                            <li class="nav-item">
                                                                <a class="nav-link" href="#dichiarazione-redditi">
                                                                    <span>Dichiarazione dei redditi</span>
                                                                </a>
                                                            </li>
                                                        <?php } ?>
                                                        <?php if ($spese_elettorali) { ?>
                                                            <li class="nav-item">
                                                                <a class="nav-link" href="#spese-elettorali">
                                                                    <span>Spese elettorali</span>
                                                                </a>
                                                            </li>
                                                        <?php } ?>
                                                        <?php if (isset($allegati) && !empty($allegati)) { ?>
                                                            <li class="nav-item">
                                                                <a class="nav-link" href="#altre-cariche">
                                                                    <span>Allegati</span>
                                                                </a>
                                                            </li>
                                                        <?php } ?>

                                                        <?php if ($uo_id) { ?>
                                                            <li class="nav-item">
                                                                <a class="nav-link" href="#contacts">
                                                                    <span>Contatti</span>
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
            </div>
            <div class="col-12 col-lg-8 offset-lg-1">

                
                <div class="it-page-sections-container">
                    <?php if (!empty($incarichi)) { ?>
                        <section class="it-page-section mb-30">
                            <h2 class="title-xxlarge mb-3" id="who-needs">Incarichi</h2>
                            <div class="richtext-wrapper lora">
                                <?php
                                foreach ($incarichi as $incarico_id) {
                                    // Ottieni il titolo dell'incarico
                                    $incarico_title = get_the_title($incarico_id);

                                    // Recupera i termini di tipo incarico
                                    $tipo_incarico_terms = get_the_terms($incarico_id, 'tipi_incarico');
                                    $tipo_incarico = (!empty($tipo_incarico_terms) && !is_wp_error($tipo_incarico_terms)) ? $tipo_incarico_terms[0]->name : 'N/A';

                                    // Mostra ogni incarico                                                    
                                    echo '<div class="richtext-wrapper lora">' . esc_html($incarico_title) . '</div>';
                                    // Aggiungi ulteriori informazioni se necessario
                        
                                }
                                ?>
                            </div>
                        </section>
                    <?php } ?>
                    <?php if ($tipo_incarico) { ?>
                        <section class="it-page-section mb-30">
                            <h2 class="title-xxlarge mb-3" id="incarichi">Tipo di Incarico</h2>
                            <div class="richtext-wrapper lora"><?php echo $tipo_incarico ?></div>
                        </section>
                    <?php } ?>
                    <?php if ($compensi) { ?>
                        <section class="it-page-section mb-30">
                            <h2 class="title-xxlarge mb-3" id="compensi">Compensi</h2>
                            <div class="richtext-wrapper lora">
                                <?php echo $compensi ?>
                            </div>
                        </section>
                    <?php } ?>
                    <?php if (isset($data_insediamento) && !empty($data_insediamento)) { ?>
                        <section class="it-page-section mb-30">
                            <h2 class="title-xxlarge mb-3" id="data">Data di
                                <?php if ($tipo_incarico == "politico") {
                                    echo 'Insediamento';
                                } else {
                                    echo 'inizio incarico';
                                } ?>
                            </h2>
                            <div class="richtext-wrapper lora"><?php echo $data_insediamento; ?></div>
                        </section>
                    <?php } ?>
                    <?php if ($organizzazioni) { ?>
                        <section class="it-page-section mb-30">
                            <h2 class="title-xxlarge mb-3" id="organizzazioni">Organizzazione</h2>
                            <div class="richtext-wrapper lora">
                                <?php foreach ($organizzazioni as $uo_id) {
                                    get_template_part("template-parts/unita-organizzativa/card-full");
                                } ?>
                            </div>
                        </section>
                    <?php } ?>
                    <?php if ($competenze) { ?>
                        <section class="it-page-section mb-30">
                            <h2 class="title-xxlarge mb-3" id="competenze">Competenze</h2>
                            <div class="richtext-wrapper lora">
                                <?php echo $competenze ?>
                            </div>
                        </section>
                    <?php } ?>
                    <?php if ($biografia) { ?>
                        <section class="it-page-section mb-30">
                            <h2 class="title-xxlarge mb-3" id="bio">Biografia</h2>
                            <div class="richtext-wrapper lora">
                                <?php echo $biografia ?>
                            </div>
                        </section>
                    <?php } ?>
                    <?php if ($punti_contatto) { ?>
                        <section class="it-page-section mb-30">
                            <h2 class="title-xxlarge mb-3" id="contatti">Contatti</h2>
                            <div class="richtext-wrapper lora">
                                <?php foreach ($punti_contatto as $pc_id) {
                                    get_template_part('template-parts/single/punto-contatto');
                                } ?>
                            </div>
                        </section>
                        <?php if ($curriculum_vitae) { ?>
                            <article id="documenti" class="it-page-section anchor-offset mt-5">
                                <h3>Curriculum Vitae</h3>
                                <div class="card-wrapper card-teaser-wrapper card-teaser-wrapper-equal" id="cv">
                                    <?php
                                    if ($curriculum_vitae) {
                                        $documento_id = attachment_url_to_postid($curriculum_vitae);
                                        $documento = get_post($documento_id);
                                        ?>
                                        <div class="card card-teaser shadow-sm p-4 mt-3 rounded border border-light flex-nowrap">
                                            <svg class="icon" aria-hidden="true">
                                                <use xlink:href="#it-clip"></use>
                                            </svg>
                                            <div class="card-body">
                                                <h5 class="card-title">
                                                    <a class="text-decoration-none" href="<?php echo $curriculum_vitae; ?>"
                                                        aria-label="Visualizza il documento <?php echo $documento->post_title; ?>"
                                                        title="Scarica il documento <?php echo $documento->post_title; ?>">
                                                        <?php echo $documento->post_title; ?>
                                                    </a>
                                                </h5>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </article>
                            <?php if ($situazione_patrimoniale) { ?>
                                <article id="situazione-patrimoniale" class="it-page-section anchor-offset mt-5">
                                    <h3>Situazione patrimoniale</h3>
                                    <div class="card-wrapper card-teaser-wrapper card-teaser-wrapper-equal">
                                        <?php
                                        if ($situazione_patrimoniale) {
                                            $documento_id = attachment_url_to_postid($situazione_patrimoniale);
                                            $documento = get_post($documento_id);
                                            ?>
                                            <div class="card card-teaser shadow-sm p-4 mt-3 rounded border border-light flex-nowrap">
                                                <svg class="icon" aria-hidden="true">
                                                    <use xlink:href="#it-clip"></use>
                                                </svg>
                                                <div class="card-body">
                                                    <h5 class="card-title">
                                                        <a class="text-decoration-none" href="<?php echo $situazione_patrimoniale; ?>"
                                                            aria-label="Visualizza il documento <?php echo $documento->post_title; ?>"
                                                            title="Scarica il documento <?php echo $documento->post_title; ?>">
                                                            <?php echo $documento->post_title; ?>
                                                        </a>
                                                    </h5>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </article>
                            <?php } ?>
                            <?php if ($dichiarazione_redditi) { ?>
                                <article id="dichiarazione-redditi" class="it-page-section anchor-offset mt-5">
                                    <h3>Dichiarazione dei redditi</h3>
                                    <div class="card-wrapper card-teaser-wrapper card-teaser-wrapper-equal">
                                        <?php
                                        if ($dichiarazione_redditi) {
                                            foreach ($dichiarazione_redditi as $dichiarazione) {
                                                $documento_id = attachment_url_to_postid($dichiarazione);
                                                $documento = get_post($documento_id);
                                                ?>
                                                <div class="card card-teaser shadow-sm p-4 mt-3 rounded border border-light flex-nowrap">
                                                    <svg class="icon" aria-hidden="true">
                                                        <use xlink:href="#it-clip"></use>
                                                    </svg>
                                                    <div class="card-body">
                                                        <h5 class="card-title">
                                                            <a class="text-decoration-none" href="<?php echo $dichiarazione; ?>"
                                                                aria-label="Visualizza il documento <?php echo $documento->post_title; ?>"
                                                                title="Scarica il documento <?php echo $documento->post_title; ?>">
                                                                <?php echo $documento->post_title; ?>
                                                            </a>
                                                        </h5>
                                                    </div>
                                                </div>
                                            <?php } ?>
                                        <?php }
                            } ?>

                                </div>
                            </article>
                            <?php
                            if ($spese_elettorali) {
                                foreach ($spese_elettorali as $spesa) {
                                    $documento_id = attachment_url_to_postid($spesa);
                                    $documento = get_post($documento_id);
                                    ?>
                                    <article id="spese-elettorali" class="it-page-section anchor-offset mt-5">
                                        <h3>Spese elettorali</h3>
                                        <div class="card-wrapper card-teaser-wrapper card-teaser-wrapper-equal">

                                            <div class="card card-teaser shadow-sm p-4 mt-3 rounded border border-light flex-nowrap">
                                                <svg class="icon" aria-hidden="true">
                                                    <use xlink:href="#it-clip"></use>
                                                </svg>
                                                <div class="card-body">
                                                    <h5 class="card-title">
                                                        <a class="text-decoration-none" href="<?php echo $spesa; ?>"
                                                            aria-label="Visualizza il documento <?php echo $documento->post_title; ?>"
                                                            title="Scarica il documento <?php echo $documento->post_title; ?>">
                                                            <?php echo $documento->post_title; ?>
                                                        </a>
                                                    </h5>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    </div>
                                <?php } ?>
                            </article>
                            <?php if ($altre_cariche) { ?>
                                <article id="altre-cariche" class="it-page-section anchor-offset mt-5">
                                    <h3>Altre cariche</h3>
                                    <div class="card-wrapper card-teaser-wrapper card-teaser-wrapper-equal">
                                        <div class="richtext-wrapper lora">
                                            <?php foreach ($altre_cariche as $documento) {
                                                $documento_id = attachment_url_to_postid($documento);
                                                $documento = get_post($documento_id);
                                                ?>
                                                <div class="card card-teaser shadow-sm p-4 mt-3 rounded border border-light flex-nowrap">
                                                    <svg class="icon" aria-hidden="true">
                                                        <use xlink:href="#it-clip"></use>
                                                    </svg>
                                                    <div class="card-body">
                                                        <h5 class="card-title">
                                                            <a class="text-decoration-none" href="<?php echo $curriculum_vitae; ?>"
                                                                aria-label="Visualizza il documento <?php echo $documento->post_title; ?>"
                                                                title="Scarica il documento <?php echo $documento->post_title; ?>">
                                                                <?php echo $documento->post_title; ?>
                                                            </a>
                                                        </h5>
                                                    </div>
                                                </div>

                                            <?php } ?>
                                        </div>
                                </article>
                            <?php } ?>

                        <?php } ?>
                    <?php } ?>

                </div>
            </div>
        </div>
    </div>
    </div>
    <?php get_template_part("template-parts/common/valuta-servizio"); ?>
    <?php get_template_part('template-parts/single/more-posts', 'carousel'); ?>
    <?php get_template_part("template-parts/common/assistenza-contatti"); ?>

    <?php
    endwhile; // End of the loop.
    ?>
</main>
<?php
get_footer();

?>



