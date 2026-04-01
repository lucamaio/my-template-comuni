<?php
/**
 * bando template file
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
    while (have_posts()):
        the_post();
        $user_can_view_post = dci_members_can_user_view_post(get_current_user_id(), $post->ID);

        $prefix = '_dci_bando_';
        $oggetto = dci_get_meta("oggetto", $prefix, $post->ID);
        $cig = dci_get_meta("cig", $prefix, $post->ID);
        $importo_aggiudicazione = dci_get_meta("importo_aggiudicazione", $prefix, $post->ID);
        $importo_liquidato = dci_get_meta("importo_somme_liquidate", $prefix, $post->ID);
        $struttura_proponente = dci_get_meta("struttura_proponente", $prefix, $post->ID);
        $cf_SA = dci_get_meta("cf_SA", $prefix, $post->ID);
        $scelta_contraente = dci_get_meta("scleta_contraente", $prefix, $post->ID);

        $operatori_group = get_post_meta(get_the_ID(), $prefix . 'operatori_group', true);
        $aggiudicatari_group = get_post_meta(get_the_ID(), $prefix . 'aggiudicatari_group', true);

        // --- DATA INIZIO ---
        $data_inizio_arr = dci_get_data_pubblicazione_arr("data_inizio", $prefix, $post->ID);
        $data_inizio = '';
        if (is_array($data_inizio_arr) && count($data_inizio_arr) === 3) {
            // Verifica se l'array è [giorno, mese, anno] o [anno, mese, giorno]
            $day   = (int) $data_inizio_arr[0];
            $month = (int) $data_inizio_arr[1];
            $year  = (int) $data_inizio_arr[2];

            // Se anno > 31 probabilmente l'array è [anno, mese, giorno]
            if ($year > 31) {
                $tmp   = $day;
                $day   = $data_inizio_arr[2];
                $month = $data_inizio_arr[1];
                $year  = $tmp;
            }

            if (checkdate($month, $day, $year)) {
                $data_inizio = date_i18n('d F Y', mktime(0, 0, 0, $month, $day, $year));
            }
        }

        // --- DATA FINE ---
        $data_fine_arr = dci_get_data_pubblicazione_arr("data_fine", $prefix, $post->ID);
        $data_fine = '';
        if (is_array($data_fine_arr) && count($data_fine_arr) === 3) {
            $day   = (int) $data_fine_arr[0];
            $month = (int) $data_fine_arr[1];
            $year  = (int) $data_fine_arr[2];

            if ($year > 31) {
                $tmp   = $day;
                $day   = $data_fine_arr[2];
                $month = $data_fine_arr[1];
                $year  = $tmp;
            }

            if (checkdate($month, $day, $year)) {
                $data_fine = date_i18n('d F Y', mktime(0, 0, 0, $month, $day, $year));
            }
        }

        $documenti = dci_get_meta("allegati", $prefix, $post->ID);

        // --- NUOVI LINK ---
        // $link_bdncp = dci_get_meta("link_bdncp", $prefix, $post->ID);
        $link_bdncp = 'https://dati.anticorruzione.it/superset/dashboard/dettaglio_cig/?cig=' . $cig;
        $link_piattaforma = dci_get_meta("link_piattaforma", $prefix, $post->ID);
        $atti_indizione = get_post_meta(get_the_ID(), $prefix . 'atti_indizione_group', true);
        $determine_aggiudicazione = get_post_meta(get_the_ID(), $prefix . 'determine_aggiudicazione_group', true);
        $altri_link = get_post_meta(get_the_ID(), $prefix . 'altri_link_group', true);
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
                    <h2 class="visually-hidden" data-audio>Dettagli della bando</h2>
                    <?php if (preg_match('/[A-Z]{5,}/', $oggetto)) {
                        echo '<p data-audio>' . ucfirst(strtolower($oggetto)) . '</p>';
                    } else {
                        echo '<p data-audio>' . $oggetto . '</p>';
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
                    <small>Data Inizio:</small>
                    <p class="fw-semibold font-monospace">
                        <?php echo $data_inizio ?: 'Non definita'; ?>
                    </p>
                </div>
                <?php if (!empty($data_fine)) { ?>
                <div class="col-6">
                    <small>Data Fine:</small>
                    <p class="fw-semibold font-monospace">
                        <?php echo $data_fine; ?>
                    </p>
                </div>
                <?php } ?>
            </div>
        </div>

        <div class="container">
            <div class="row border-top border-light row-column-border row-column-menu-left">
                <aside class="col-lg-4">
                    <div class="cmp-navscroll sticky-top" aria-labelledby="accordion-title-one">
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
                                                            <?php if (!empty($oggetto)) { ?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#oggetto">
                                                                        <span class="title-medium">Oggetto Bando</span>
                                                                    </a>
                                                                </li>
                                                            <?php } ?>
                                                            <?php if (is_array($operatori_group) && !empty($operatori_group)) { ?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#operatori-invitati">
                                                                        <span class="title-medium">Operatori Inviatati</span>
                                                                    </a>
                                                                </li>
                                                            <?php } ?>
                                                            <?php if (is_array($aggiudicatari_group) && !empty($aggiudicatari_group)) { ?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#aggiudicatori">
                                                                        <span class="title-medium">Aggiudicatori</span>
                                                                    </a>
                                                                </li>
                                                            <?php } ?>
                                                            <?php if (!empty($cig) && isset($cig)) { ?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#cig">
                                                                        <span class="title-medium">CIG</span>
                                                                    </a>
                                                                </li>
                                                            <?php } ?>
                                                            <?php if (!empty($importo_aggiudicazione) && isset($importo_aggiudicazione)) { ?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#aggiudicato">
                                                                        <span class="title-medium">Importo Aggiudicato</span>
                                                                    </a>
                                                                </li>
                                                            <?php } ?>
                                                            <?php if (!empty($importo_liquidato) && isset($importo_liquidato)) { ?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#liquidato">
                                                                        <span class="title-medium">Importo Liquidato</span>
                                                                    </a>
                                                                </li>
                                                            <?php } ?>
                                                            <?php if (!empty($scelta_contraente) && isset($scelta_contraente)) { ?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#scelta-contraente">
                                                                        <span class="title-medium">Procedura scelta contraente</span>
                                                                    </a>
                                                                </li>
                                                            <?php } ?>
                                                            <?php if (
                                                                !empty($link_bdncp) || !empty($link_piattaforma) || !empty($atti_indizione) || !empty($determine_aggiudicazione) || !empty($altri_link)
                                                            ) { ?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#link-esterni">
                                                                        <span class="title-medium">Link e riferimenti</span>
                                                                    </a>
                                                                </li>
                                                            <?php } ?>
                                                            <?php if (is_array($documenti) && !empty($documenti)) { ?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#documenti">
                                                                        <span class="title-medium">Documenti</span>
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
                <section class="col-lg-8 it-page-sections-container border-light mb-5">

                    <!-- OGGETTO -->
                    <?php if (!empty($oggetto)) { ?>
                        <article class="it-page-section anchor-offset" data-audio>
                            <h4 id="oggetto">Oggetto Bando</h4>
                            <div class="richtext-wrapper lora">
                                <?php
                                if (preg_match('/[A-Z]{5,}/', $oggetto)) {
                                    echo ucfirst(strtolower($oggetto));
                                } else {
                                    echo $oggetto;
                                }
                                ?>
                            </div>
                        </article>
                    <?php } ?>

                    <!-- OPERATORI -->
                    <?php if (!empty($operatori_group) && is_array($operatori_group)) { ?>
                        <article class="it-page-section anchor-offset mt-5">
                            <h4 id="operatori-invitati">Operatori Invitati/Partecipanti</h4>
                            <div class="card card-border-top mb-0">
                                <p class="mb-0" style="text-align:justify">
                                    Elenco degli operatori invitati a presentare offerte/numero di offerenti che hanno
                                    partecipato al procedimento
                                </p>
                                <div class="card-body px-0">
                                    <div class="row g-0 text-dark fw-bold border-bottom pb-1 mb-2 px-3">
                                        <div class="col-md-8">Ragione sociale</div>
                                        <div class="col-md-4">Codice fiscale/P.Iva</div>
                                    </div>
                                    <?php foreach ($operatori_group as $operatore) {
                                        $ragione_sociale = isset($operatore['ragione_sociale']) ? esc_html($operatore['ragione_sociale']) : 'Non definita';
                                        $codice_fiscale = isset($operatore['codice_fiscale']) ? esc_html($operatore['codice_fiscale']) : 'Non definito';
                                        ?>
                                        <div class="row g-0 py-1 border-bottom border-light-subtle px-3">
                                            <div class="col-md-8"><?php echo $ragione_sociale; ?></div>
                                            <div class="col-md-4"><?php echo $codice_fiscale; ?></div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </article>
                    <?php } ?>

                    <!-- AGGIUDICATARI -->
                    <?php if (!empty($aggiudicatari_group) && is_array($aggiudicatari_group)) { ?>
                        <article class="it-page-section anchor-offset mt-1">
                            <h4 id="aggiudicatori">Aggiudicatari</h4>
                            <div class="card card-border-top mb-0">
                                <p class="mb-0" style="text-align:justify">
                                    Elenco degli aggiudicatari del procedimento
                                </p>
                                <div class="card-body px-0">
                                    <div class="row g-0 text-dark fw-bold border-bottom pb-1 mb-2 px-3">
                                        <div class="col-md-8">Ragione sociale</div>
                                        <div class="col-md-4">Codice fiscale/P.Iva</div>
                                    </div>
                                    <?php foreach ($aggiudicatari_group as $aggiudicatore) {
                                        $ragione_sociale = isset($aggiudicatore['ragione_sociale']) ? esc_html($aggiudicatore['ragione_sociale']) : 'Non definita';
                                        $codice_fiscale = isset($aggiudicatore['codice_fiscale']) ? esc_html($aggiudicatore['codice_fiscale']) : 'Non definito';
                                        ?>
                                        <div class="row g-0 py-1 border-bottom border-light-subtle px-3">
                                            <div class="col-md-8"><?php echo $ragione_sociale; ?></div>
                                            <div class="col-md-4"><?php echo $codice_fiscale; ?></div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </article>
                    <?php } ?>

                    <!-- CIG -->
                    <?php if (!empty($cig) && isset($cig)) { ?>
                        <section class="it-page-section mb-3">
                            <h5 id="cig">CIG</h5>
                            <div class="richtext-wrapper lora" data-element="service-cost"><?php echo $cig ?></div>
                        </section>
                    <?php } ?>

                    <!-- IMPORTI -->
                    <?php if (!empty($importo_aggiudicazione) && isset($importo_aggiudicazione)) { ?>
                        <section class="it-page-section mb-3">
                            <h5 id="aggiudicato">Importo Aggiudicazione</h5>
                            <div class="richtext-wrapper lora" data-element="service-cost"><?php echo $importo_aggiudicazione ?></div>
                        </section>
                    <?php } ?>

                    <?php if (!empty($importo_liquidato) && isset($importo_liquidato)) { ?>
                        <section class="it-page-section mb-3">
                            <h5 id="liquidato">Importo Liquidato</h5>
                            <div class="richtext-wrapper lora" data-element="service-cost"><?php echo $importo_liquidato ?></div>
                        </section>
                    <?php } ?>

                    <!-- SCELTA CONTRAENTE -->
                    <?php if (!empty($scelta_contraente) && isset($scelta_contraente)) { ?>
                        <section class="it-page-section mb-3">
                            <h5 id="scelta-contraente">Procedura scelta contraente</h5>
                            <div class="chip chip-simple" data-element="service-status">
                                <span class="chip-label">
                                    <?php echo '<span class="text-primary">'.$scelta_contraente.'</span>' ?>
                                </span>  
                            </div>
                        </section>
                    <?php } ?>

                    <!-- LINK ESTERNI -->
                    <?php if (
                        !empty($link_bdncp) || !empty($link_piattaforma) || (is_array($atti_indizione) && !empty($atti_indizione)) || 
                        (is_array($determine_aggiudicazione) && !empty($determine_aggiudicazione)) || (is_array($altri_link) && !empty($altri_link))
                    ) { ?>
                        <article class="it-page-section anchor-offset mt-5">
                            <h4 id="link-esterni">Link e riferimenti esterni</h4>
                            <div class="card-wrapper card-teaser-wrapper card-teaser-wrapper-equal">

                                <?php //if(!empty($link_bdncp)) { ?>
                                    <div class="card card-teaser shadow-sm p-4 mt-3 rounded border border-light flex-nowrap">
                                        <svg class="icon"><use xlink:href="#it-link"></use></svg>
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                <a href="<?php echo esc_url($link_bdncp); ?>" target="_blank">BDNCP (ANAC)</a>
                                            </h5>
                                            <p class="card-text">Consulta la scheda del bando sulla Banca Dati Nazionale Contratti Pubblici.</p>
                                        </div>
                                    </div>
                                <?php //} ?>

                                <?php if(!empty($link_piattaforma)) { ?>
                                    <div class="card card-teaser shadow-sm p-4 mt-3 rounded border border-light flex-nowrap">
                                        <svg class="icon"><use xlink:href="#it-link"></use></svg>
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                <a href="<?php echo esc_url($link_piattaforma); ?>" target="_blank">Piattaforma di approvvigionamento</a>
                                            </h5>
                                        </div>
                                    </div>
                                <?php } ?>

                                <?php if(is_array($atti_indizione) && !empty($atti_indizione)) {
                                    foreach($atti_indizione as $atto){
                                        $url = isset($atto['url']) ? esc_url($atto['url']) : '';
                                        $title = isset($atto['title']) ? esc_html($atto['title']) : 'Atto di indizione';
                                        if(!$url) continue;
                                ?>
                                    <div class="card card-teaser shadow-sm p-4 mt-3 rounded border border-light flex-nowrap">
                                        <svg class="icon"><use xlink:href="#it-link"></use></svg>
                                        <div class="card-body">
                                            <h5 class="card-title"><a href="<?php echo $url; ?>" target="_blank"><?php echo $title; ?></a></h5>
                                        </div>
                                    </div>
                                <?php }} ?>

                                <?php if(is_array($determine_aggiudicazione) && !empty($determine_aggiudicazione)) {
                                    foreach($determine_aggiudicazione as $det){
                                        $url = isset($det['url']) ? esc_url($det['url']) : '';
                                        $title = isset($det['title']) ? esc_html($det['title']) : 'Determina di aggiudicazione';
                                        if(!$url) continue;
                                ?>
                                    <div class="card card-teaser shadow-sm p-4 mt-3 rounded border border-light flex-nowrap">
                                        <svg class="icon"><use xlink:href="#it-link"></use></svg>
                                        <div class="card-body">
                                            <h5 class="card-title"><a href="<?php echo $url; ?>" target="_blank"><?php echo $title; ?></a></h5>
                                        </div>
                                    </div>
                                <?php }} ?>

                                <?php if(is_array($altri_link) && !empty($altri_link)) {
                                    foreach($altri_link as $link){
                                        $url = isset($link['url']) ? esc_url($link['url']) : '';
                                        $label = isset($link['label']) ? esc_html($link['label']) : 'Link';
                                        if(!$url) continue;
                                ?>
                                    <div class="card card-teaser shadow-sm p-4 mt-3 rounded border border-light flex-nowrap">
                                        <svg class="icon"><use xlink:href="#it-link"></use></svg>
                                        <div class="card-body">
                                            <h5 class="card-title"><a href="<?php echo $url; ?>" target="_blank"><?php echo $label; ?></a></h5>
                                        </div>
                                    </div>
                                <?php }} ?>

                            </div>
                        </article>
                    <?php } ?>

                    <!-- DOCUMENTI -->
                    <?php if (is_array($documenti) && count($documenti)) { ?>
                        <article class="it-page-section anchor-offset mt-5">
                            <h4 id="documenti">Documenti</h4>
                            <div class="card-wrapper card-teaser-wrapper card-teaser-wrapper-equal">
                                <?php foreach ($documenti as $all_url) {
                                    $all_id = attachment_url_to_postid($all_url);
                                    $allegato = get_post($all_id);
                                    ?>
                                    <div class="card card-teaser shadow-sm p-4 mt-3 rounded border border-light flex-nowrap">
                                        <svg class="icon" aria-hidden="true">
                                            <use xlink:href="#it-clip"></use>
                                        </svg>
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                <a class="text-decoration-none" href="<?php echo get_the_guid($allegato); ?>"
                                                    aria-label="Scarica l'allegato <?php echo $allegato->post_title; ?>"
                                                    title="Scarica l'allegato <?php echo $allegato->post_title; ?>">

                                                    <?php
                                                    $title_allegato = $allegato->post_title;
                                                    if (strlen($title_allegato) > 50) {
                                                        $title_allegato = substr($title_allegato, 0, 50) . '...';
                                                    }
                                                    if (preg_match('/[A-Z]{5,}/', $title_allegato)) {
                                                        $title_allegato = ucfirst(strtolower($title_allegato));
                                                    }
                                                    echo $title_allegato;
                                                    ?>
                                                </a>
                                            </h5>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </article>
                    <?php } ?>

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