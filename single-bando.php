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

<style>
    .dci-bando-summary {
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #e5e7eb;
    }

    .dci-bando-summary-title {
        margin-bottom: 1rem;
        font-size: 1.25rem;
        font-weight: 700;
        color: #17324d;
    }

    .dci-bando-summary-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .75rem;
    }

    .dci-bando-summary-item {
        padding: .85rem 1rem;
        border-radius: 10px;
        background: #f7f9fb;
        border: 1px solid #edf0f3;
    }

    .dci-bando-summary-label {
        display: block;
        margin-bottom: .25rem;
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: #5b6f82;
    }

    .dci-bando-summary-value {
        display: block;
        font-size: .95rem;
        font-weight: 600;
        line-height: 1.35;
        color: #17324d;
        word-break: break-word;
    }

    .dci-bando-summary-item-full {
        grid-column: 1 / -1;
    }

    .dci-bando-section-card {
        border-radius: 12px;
        border: 1px solid #edf0f3;
        box-shadow: none;
    }

    .dci-bando-table-head {
        background: #f7f9fb;
        border-radius: 8px;
    }

    .dci-bando-link-card {
        border-radius: 12px !important;
        border: 1px solid #edf0f3 !important;
        box-shadow: none !important;
    }

    .dci-bando-link-card .icon {
        flex: 0 0 auto;
        margin-right: .85rem;
    }

    .dci-bando-link-card .card-title {
        margin-bottom: .25rem;
    }

    .dci-bando-link-card .card-text {
        font-size: .9rem;
        color: #5b6f82;
    }

    @media (max-width: 767.98px) {
        .dci-bando-summary-grid {
            grid-template-columns: 1fr;
        }

        .dci-bando-summary-item {
            padding: .8rem;
        }
    }
</style>

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
        $more_info = dci_get_meta("more_info", $prefix, $post->ID);

        // Campo salvato nel metabox come "_dci_bando_cf_sa"
        $cf_SA = dci_get_meta("cf_sa", $prefix, $post->ID);

        // Vecchio campo testuale mantenuto per compatibilità, ma la scelta del contraente viene letta anche dalla tassonomia
        $scelta_contraente = dci_get_meta("scleta_contraente", $prefix, $post->ID);

        $operatori_group = get_post_meta(get_the_ID(), $prefix . 'operatori_group', true);
        $aggiudicatari_group = get_post_meta(get_the_ID(), $prefix . 'aggiudicatari_group', true);

        // --- STATO BANDO ---
        $stato_bando = '';
        $stato_terms = get_the_terms(get_the_ID(), 'tipi_stato_bando');


        if (!empty($stato_terms) && !is_wp_error($stato_terms)) {
            $stato_output = array();

            foreach ($stato_terms as $term) {
                if (!empty($term->name)) {
                    $stato_output[] = esc_html($term->name);
                }
            }

            $stato_bando = !empty($stato_output) ? implode(', ', $stato_output) : '';
        }

        // --- PROCEDURA SCELTA CONTRAENTE ---
        $scelta_contraente_tax = '';
        $scelta_contraente_terms = get_the_terms(get_the_ID(), 'tipi_procedura_contraente');

        if (!empty($scelta_contraente_terms) && !is_wp_error($scelta_contraente_terms)) {
            $scelta_output = array();

            foreach ($scelta_contraente_terms as $term) {
                if (!empty($term->name)) {
                    $scelta_output[] = esc_html($term->name);
                }
            }

            $scelta_contraente_tax = !empty($scelta_output) ? implode(', ', $scelta_output) : '';
        }

        if (empty($scelta_contraente_tax) && !empty($scelta_contraente)) {
            $scelta_contraente_tax = esc_html($scelta_contraente);
        }

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
        $link_bdncp = !empty($cig) ? 'https://dati.anticorruzione.it/superset/dashboard/dettaglio_cig/?cig=' . urlencode($cig) : '';
        $link_piattaforma = dci_get_meta("link_piattaforma", $prefix, $post->ID);
        $atti_indizione = get_post_meta(get_the_ID(), $prefix . 'atti_indizione_group', true);
        $determine_aggiudicazione = get_post_meta(get_the_ID(), $prefix . 'determine_aggiudicazione_group', true);
        $altri_link = get_post_meta(get_the_ID(), $prefix . 'altri_link_group', true);

        // Controllo presenza effettiva di atti di indizione con link o file
        $has_atti_indizione = false;

        if (is_array($atti_indizione) && !empty($atti_indizione)) {
            foreach ($atti_indizione as $atto) {
                $atto_url = isset($atto['url']) ? trim($atto['url']) : '';
                $atto_file = isset($atto['file']) ? trim($atto['file']) : '';
                $atto_file_id = isset($atto['file_id']) ? absint($atto['file_id']) : 0;

                if (!empty($atto_url) || !empty($atto_file) || !empty($atto_file_id)) {
                    $has_atti_indizione = true;
                    break;
                }
            }
        }

        // Controllo presenza effettiva di determine con link o file
        $has_determine_aggiudicazione = false;

        if (is_array($determine_aggiudicazione) && !empty($determine_aggiudicazione)) {
            foreach ($determine_aggiudicazione as $det) {
                $det_url = isset($det['url']) ? trim($det['url']) : '';
                $det_file = isset($det['file']) ? trim($det['file']) : '';
                $det_file_id = isset($det['file_id']) ? absint($det['file_id']) : 0;

                if (!empty($det_url) || !empty($det_file) || !empty($det_file_id)) {
                    $has_determine_aggiudicazione = true;
                    break;
                }
            }
        }

        // Controllo presenza effettiva di altri link
        $has_altri_link = false;

        if (is_array($altri_link) && !empty($altri_link)) {
            foreach ($altri_link as $link) {
                $link_url = isset($link['url']) ? trim($link['url']) : '';

                if (!empty($link_url)) {
                    $has_altri_link = true;
                    break;
                }
            }
        }
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
                        echo '<h1 data-audio>' . esc_html(ucfirst(strtolower(get_the_title()))) . '</h1>';
                    } else {
                        echo '<h1 data-audio>' . esc_html(get_the_title()) . '</h1>';
                    } ?>

                    <h2 class="visually-hidden" data-audio>Dettagli della bando</h2>

                    <?php if (!empty($oggetto)) { ?>
                        <?php if (preg_match('/[A-Z]{5,}/', wp_strip_all_tags($oggetto))) {
                            echo '<p data-audio>' . wp_kses_post(ucfirst(strtolower($oggetto))) . '</p>';
                        } else {
                            echo '<p data-audio>' . wp_kses_post($oggetto) . '</p>';
                        } ?>
                    <?php } ?>
                </div>

                <div class="col-lg-3 offset-lg-1">
                    <?php
                    $inline = true;
                    get_template_part('template-parts/single/actions');
                    ?>
                </div>
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
                                                            <li class="nav-item">
                                                                <a class="nav-link" href="#informazioni-principali">
                                                                    <span class="title-medium">Informazioni principali</span>
                                                                </a>
                                                            </li>

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
                                                                        <span class="title-medium">Operatori Invitati</span>
                                                                    </a>
                                                                </li>
                                                            <?php } ?>

                                                            <?php if (is_array($aggiudicatari_group) && !empty($aggiudicatari_group)) { ?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#aggiudicatori">
                                                                        <span class="title-medium">Aggiudicatari</span>
                                                                    </a>
                                                                </li>
                                                            <?php } ?>

                                                            <?php if (
                                                                !empty($link_bdncp) || !empty($link_piattaforma) || $has_atti_indizione || $has_determine_aggiudicazione || $has_altri_link
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

                    <!-- RIEPILOGO INFORMAZIONI BANDO -->
                    <article class="it-page-section anchor-offset dci-bando-summary" data-audio>
                        <h4 id="informazioni-principali" class="dci-bando-summary-title">Informazioni principali</h4>

                        <div class="dci-bando-summary-grid">
                            <div class="dci-bando-summary-item">
                                <span class="dci-bando-summary-label">CIG</span>
                                <span class="dci-bando-summary-value">
                                    <?php echo !empty($cig) ? esc_html($cig) : '-'; ?>
                                </span>
                            </div>

                            <div class="dci-bando-summary-item">
                                <span class="dci-bando-summary-label">Stato pubblicazione</span>
                                <span class="dci-bando-summary-value">
                                    <?php echo !empty($stato_bando) ? $stato_bando : '-'; ?>
                                </span>
                            </div>

                            <div class="dci-bando-summary-item">
                                <span class="dci-bando-summary-label">Data inizio</span>
                                <span class="dci-bando-summary-value">
                                    <?php echo !empty($data_inizio) ? esc_html($data_inizio) : '-'; ?>
                                </span>
                            </div>

                            <div class="dci-bando-summary-item">
                                <span class="dci-bando-summary-label">Data fine</span>
                                <span class="dci-bando-summary-value">
                                    <?php echo !empty($data_fine) ? esc_html($data_fine) : '-'; ?>
                                </span>
                            </div>

                            <div class="dci-bando-summary-item">
                                <span class="dci-bando-summary-label">Importo di aggiudicazione</span>
                                <span class="dci-bando-summary-value">
                                    <?php echo !empty($importo_aggiudicazione) ? esc_html($importo_aggiudicazione) : '-'; ?>
                                </span>
                            </div>

                            <div class="dci-bando-summary-item">
                                <span class="dci-bando-summary-label">Importo somme liquidate</span>
                                <span class="dci-bando-summary-value">
                                    <?php echo !empty($importo_liquidato) ? esc_html($importo_liquidato) : '-'; ?>
                                </span>
                            </div>

                            <div class="dci-bando-summary-item">
                                <span class="dci-bando-summary-label">Struttura proponente</span>
                                <span class="dci-bando-summary-value">
                                    <?php echo !empty($struttura_proponente) ? esc_html($struttura_proponente) : '-'; ?>
                                </span>
                            </div>

                            <div class="dci-bando-summary-item">
                                <span class="dci-bando-summary-label">Codice fiscale SA</span>
                                <span class="dci-bando-summary-value">
                                    <?php echo !empty($cf_SA) ? esc_html($cf_SA) : '-'; ?>
                                </span>
                            </div>

                            <div class="dci-bando-summary-item dci-bando-summary-item-full">
                                <span class="dci-bando-summary-label">Procedura scelta contraente</span>
                                <span class="dci-bando-summary-value">
                                    <?php echo !empty($scelta_contraente_tax) ? $scelta_contraente_tax : '-'; ?>
                                </span>
                            </div>
                        </div>
                    </article>

                    <!-- OGGETTO -->
                    <?php if (!empty($oggetto)) { ?>
                        <article class="it-page-section anchor-offset" data-audio>
                            <h4 id="oggetto">Oggetto Bando</h4>
                            <div class="richtext-wrapper lora">
                                <?php
                                if (preg_match('/[A-Z]{5,}/', wp_strip_all_tags($oggetto))) {
                                    echo wp_kses_post(ucfirst(strtolower($oggetto)));
                                } else {
                                    echo wp_kses_post($oggetto);
                                }
                                ?>
                            </div>
                        </article>
                    <?php } ?>

                    <!-- OPERATORI -->
                    <?php if (!empty($operatori_group) && is_array($operatori_group)) { ?>
                        <article class="it-page-section anchor-offset mt-5">
                            <h4 id="operatori-invitati">Operatori Invitati/Partecipanti</h4>

                            <div class="card card-border-top mb-0 dci-bando-section-card">
                                <p class="mb-0" style="text-align:justify">
                                    Elenco degli operatori invitati a presentare offerte/numero di offerenti che hanno
                                    partecipato al procedimento.
                                </p>

                                <div class="card-body px-0">
                                    <div class="row g-0 text-dark fw-bold border-bottom pb-2 mb-2 px-3 dci-bando-table-head">
                                        <div class="col-md-8">Ragione sociale</div>
                                        <div class="col-md-4">Codice fiscale/P.IVA</div>
                                    </div>

                                    <?php foreach ($operatori_group as $operatore) {
                                        $ragione_sociale = isset($operatore['ragione_sociale']) && trim($operatore['ragione_sociale']) !== '' ? esc_html($operatore['ragione_sociale']) : '-';
                                        $codice_fiscale = isset($operatore['codice_fiscale']) && trim($operatore['codice_fiscale']) !== '' ? esc_html($operatore['codice_fiscale']) : '-';
                                        ?>
                                        <div class="row g-0 py-2 border-bottom border-light-subtle px-3">
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
                        <article class="it-page-section anchor-offset mt-5">
                            <h4 id="aggiudicatori">Aggiudicatari</h4>

                            <div class="card card-border-top mb-0 dci-bando-section-card">
                                <p class="mb-0" style="text-align:justify">
                                    Elenco degli aggiudicatari del procedimento.
                                </p>

                                <div class="card-body px-0">
                                    <div class="row g-0 text-dark fw-bold border-bottom pb-2 mb-2 px-3 dci-bando-table-head">
                                        <div class="col-md-8">Ragione sociale</div>
                                        <div class="col-md-4">Codice fiscale/P.IVA</div>
                                    </div>

                                    <?php foreach ($aggiudicatari_group as $aggiudicatore) {
                                        $ragione_sociale = isset($aggiudicatore['ragione_sociale']) && trim($aggiudicatore['ragione_sociale']) !== '' ? esc_html($aggiudicatore['ragione_sociale']) : '-';
                                        $codice_fiscale = isset($aggiudicatore['codice_fiscale']) && trim($aggiudicatore['codice_fiscale']) !== '' ? esc_html($aggiudicatore['codice_fiscale']) : '-';
                                        ?>
                                        <div class="row g-0 py-2 border-bottom border-light-subtle px-3">
                                            <div class="col-md-8"><?php echo $ragione_sociale; ?></div>
                                            <div class="col-md-4"><?php echo $codice_fiscale; ?></div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </article>
                    <?php } ?>

                    <!-- LINK ESTERNI -->
                    <?php if (
                        !empty($link_bdncp) || !empty($link_piattaforma) || $has_atti_indizione || $has_determine_aggiudicazione || $has_altri_link
                    ) { ?>
                        <?php
                        $riferimenti_esterni = array();

                        if (!empty($link_bdncp)) {
                            $riferimenti_esterni[] = array(
                                'url'     => $link_bdncp,
                                'titolo'  => 'BDNCP (ANAC)',
                                'is_file' => false,
                            );
                        }

                        if (!empty($link_piattaforma)) {
                            $riferimenti_esterni[] = array(
                                'url'     => $link_piattaforma,
                                'titolo'  => 'Piattaforma di approvvigionamento',
                                'is_file' => false,
                            );
                        }

                        if (is_array($atti_indizione)) {
                            foreach ($atti_indizione as $atto) {
                                $url = isset($atto['url']) ? trim((string) $atto['url']) : '';
                                $file_value = $atto['file'] ?? '';
                                $file_url = is_array($file_value)
                                    ? trim((string) ($file_value['url'] ?? ''))
                                    : trim((string) $file_value);
                                $file_id = isset($atto['file_id']) ? absint($atto['file_id']) : 0;
                                $is_file = $file_id || $file_url !== '';
                                $link_finale = $file_id
                                    ? wp_get_attachment_url($file_id)
                                    : ($file_url !== '' ? $file_url : $url);

                                if (empty($link_finale)) {
                                    continue;
                                }

                                $riferimenti_esterni[] = array(
                                    'url'     => $link_finale,
                                    'titolo'  => !empty($atto['title']) ? trim((string) $atto['title']) : 'Atto di indizione',
                                    'is_file' => (bool) $is_file,
                                );
                            }
                        }

                        if (is_array($determine_aggiudicazione)) {
                            foreach ($determine_aggiudicazione as $det) {
                                $url = isset($det['url']) ? trim((string) $det['url']) : '';
                                $file_value = $det['file'] ?? '';
                                $file_url = is_array($file_value)
                                    ? trim((string) ($file_value['url'] ?? ''))
                                    : trim((string) $file_value);
                                $file_id = isset($det['file_id']) ? absint($det['file_id']) : 0;
                                $is_file = $file_id || $file_url !== '';
                                $link_finale = $file_id
                                    ? wp_get_attachment_url($file_id)
                                    : ($file_url !== '' ? $file_url : $url);

                                if (empty($link_finale)) {
                                    continue;
                                }

                                $riferimenti_esterni[] = array(
                                    'url'     => $link_finale,
                                    'titolo'  => !empty($det['title']) ? trim((string) $det['title']) : 'Determina di aggiudicazione',
                                    'is_file' => (bool) $is_file,
                                );
                            }
                        }

                        if (is_array($altri_link)) {
                            foreach ($altri_link as $link) {
                                $url = isset($link['url']) ? trim((string) $link['url']) : '';

                                if ($url === '') {
                                    continue;
                                }

                                $riferimenti_esterni[] = array(
                                    'url'     => $url,
                                    'titolo'  => !empty($link['label']) ? trim((string) $link['label']) : 'Link',
                                    'is_file' => false,
                                );
                            }
                        }
                        ?>

                        <style>
                            .dci-bando-resources {
                                --dci-resource-primary: var(--tema-primary, #0d2b45);
                                --dci-resource-hover: var(--tema-hover, #133b5c);
                                --dci-resource-focus: var(--tema-focus, #1e5a8a);
                            }

                            .dci-bando-resources__heading {
                                color: var(--dci-resource-primary);
                            }

                            .dci-bando-resources__grid {
                                row-gap: 1rem;
                            }

                            .dci-bando-resources__item {
                                display: flex;
                            }

                            .dci-bando-resources__card {
                                position: relative;
                                display: flex;
                                align-items: flex-start;
                                gap: 1rem;
                                width: 100%;
                                min-height: 112px;
                                padding: 1.15rem 3.25rem 1.15rem 1.15rem;
                                overflow: hidden;
                                color: var(--dci-resource-primary);
                                background-color: #ffffff;
                                border-radius: 12px;
                                box-shadow: 0 8px 22px rgba(13, 43, 69, 0.08);
                                text-decoration: none;
                                transition: transform 0.2s ease, box-shadow 0.2s ease;
                            }

                            .dci-bando-resources__card::before {
                                position: absolute;
                                inset: 0 auto 0 0;
                                width: 4px;
                                background: var(--dci-resource-primary);
                                content: "";
                            }

                            .dci-bando-resources__card:hover {
                                color: var(--dci-resource-hover);
                                background-color: #fbfcfd;
                                box-shadow: 0 12px 28px rgba(13, 43, 69, 0.14);
                                text-decoration: none;
                                transform: translateY(-2px);
                            }

                            .dci-bando-resources__card:focus-visible {
                                outline: 3px solid var(--dci-resource-focus);
                                outline-offset: 3px;
                            }

                            .dci-bando-resources__icon {
                                flex: 0 0 auto;
                                width: 42px;
                                height: 42px;
                                padding: 9px;
                                background-color: #f6f8fa;
                                border-radius: 10px;
                                box-shadow: 0 4px 12px rgba(13, 43, 69, 0.1);
                            }

                            .dci-bando-resources__content {
                                min-width: 0;
                            }

                            .dci-bando-resources__type {
                                display: block;
                                margin-bottom: 0.3rem;
                                color: var(--dci-resource-primary);
                                font-size: 0.76rem;
                                font-weight: 700;
                                letter-spacing: 0.035em;
                                line-height: 1.25;
                                text-transform: uppercase;
                            }

                            .dci-bando-resources__title {
                                display: block;
                                overflow-wrap: anywhere;
                                font-size: 1rem;
                                font-weight: 700;
                                line-height: 1.35;
                            }

                            .dci-bando-resources__meta {
                                display: block;
                                margin-top: 0.35rem;
                                overflow: hidden;
                                color: #455a64;
                                font-size: 0.85rem;
                                line-height: 1.35;
                                text-overflow: ellipsis;
                                white-space: nowrap;
                            }

                            .dci-bando-resources__arrow {
                                position: absolute;
                                top: 50%;
                                right: 1rem;
                                width: 22px;
                                height: 22px;
                                color: var(--dci-resource-primary);
                                transform: translateY(-50%);
                            }

                            .dci-bando-resources__arrow .icon {
                                width: 100%;
                                height: 100%;
                                fill: currentColor;
                            }

                            @media (prefers-reduced-motion: reduce) {
                                .dci-bando-resources__card {
                                    transition: none;
                                }

                                .dci-bando-resources__card:hover {
                                    transform: none;
                                }
                            }
                        </style>

                        <article class="it-page-section anchor-offset mt-5 dci-bando-resources"
                            aria-labelledby="link-esterni">
                            <h4 class="h3 mb-3 dci-bando-resources__heading" id="link-esterni">
                                Link e riferimenti esterni
                            </h4>
                            <div class="row dci-bando-resources__grid">
                                <?php foreach ($riferimenti_esterni as $riferimento) {
                                    $riferimento_url = $riferimento['url'];
                                    $riferimento_titolo = $riferimento['titolo'];
                                    $is_file = !empty($riferimento['is_file']);
                                    $riferimento_path = (string) parse_url($riferimento_url, PHP_URL_PATH);
                                    $riferimento_host = (string) parse_url($riferimento_url, PHP_URL_HOST);
                                    $riferimento_estensione = strtoupper(
                                        (string) pathinfo($riferimento_path, PATHINFO_EXTENSION)
                                    );
                                    $tipo_riferimento = $is_file ? 'Documento' : 'Link esterno';
                                    $meta_riferimento = $is_file
                                        ? ($riferimento_estensione ? 'Formato ' . $riferimento_estensione : 'Documento allegato')
                                        : $riferimento_host;
                                    $etichetta_riferimento = $is_file
                                        ? sprintf('Apri il documento %s in una nuova scheda', $riferimento_titolo)
                                        : sprintf('Apri il link %s in una nuova scheda', $riferimento_titolo);
                                    $icon_src = $is_file
                                        ? "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%230D2B45' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 2.75h8l4 4v14.5H6z'/%3E%3Cpath d='M14 2.75v4h4M9 12h6M9 16h6'/%3E%3C/svg%3E"
                                        : "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%230D2B45' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M10 13a5 5 0 0 0 7.07.07l2-2A5 5 0 0 0 12 4l-1.15 1.15'/%3E%3Cpath d='M14 11a5 5 0 0 0-7.07-.07l-2 2A5 5 0 0 0 12 20l1.15-1.15'/%3E%3C/svg%3E";
                                    ?>
                                    <div class="col-12 col-md-6 dci-bando-resources__item">
                                        <a class="dci-bando-resources__card"
                                            href="<?php echo esc_url($riferimento_url); ?>"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            title="<?php echo esc_attr($etichetta_riferimento); ?>"
                                            aria-label="<?php echo esc_attr($etichetta_riferimento); ?>">
                                            <img class="dci-bando-resources__icon"
                                                src="<?php echo esc_attr($icon_src); ?>"
                                                alt="<?php echo esc_attr(sprintf('Icona %s', $tipo_riferimento)); ?>"
                                                width="42"
                                                height="42">
                                            <span class="dci-bando-resources__content">
                                                <span class="dci-bando-resources__type">
                                                    <?php echo esc_html($tipo_riferimento); ?>
                                                </span>
                                                <span class="dci-bando-resources__title">
                                                    <?php echo esc_html($riferimento_titolo); ?>
                                                </span>
                                                <?php if (!empty($meta_riferimento)) { ?>
                                                    <span class="dci-bando-resources__meta">
                                                        <?php echo esc_html($meta_riferimento); ?>
                                                    </span>
                                                <?php } ?>
                                            </span>
                                            <span class="dci-bando-resources__arrow" aria-hidden="true">
                                                <svg class="icon">
                                                    <use href="<?php echo $is_file ? '#it-download' : '#it-external-link'; ?>"></use>
                                                </svg>
                                            </span>
                                        </a>
                                    </div>
                                <?php } ?>
                            </div>
                        </article>
                    <?php } ?>

                    <!-- DOCUMENTI -->
                    <?php if (is_array($documenti) && count($documenti)) { ?>
                        <article class="it-page-section anchor-offset mt-5">
                            <h4 id="documenti">Documenti</h4>

                            <div class="card-wrapper card-teaser-wrapper card-teaser-wrapper-equal">
                                <?php foreach ($documenti as $all_url) {
                                    if (empty($all_url)) {
                                        continue;
                                    }

                                    $all_id = attachment_url_to_postid($all_url);
                                    $allegato = $all_id ? get_post($all_id) : null;

                                    $allegato_url = $all_id ? wp_get_attachment_url($all_id) : $all_url;
                                    $title_allegato = $allegato && !empty($allegato->post_title) ? $allegato->post_title : basename(parse_url($all_url, PHP_URL_PATH));

                                    if (strlen($title_allegato) > 50) {
                                        $title_allegato = substr($title_allegato, 0, 50) . '...';
                                    }

                                    if (preg_match('/[A-Z]{5,}/', $title_allegato)) {
                                        $title_allegato = ucfirst(strtolower($title_allegato));
                                    }
                                    ?>
                                    <div class="card card-teaser p-4 mt-3 rounded border border-light flex-nowrap dci-bando-link-card">
                                        <svg class="icon" aria-hidden="true">
                                            <use xlink:href="#it-clip"></use>
                                        </svg>

                                        <div class="card-body">
                                            <h5 class="card-title">
                                                <a 
                                                    class="text-decoration-none" 
                                                    href="<?php echo esc_url($allegato_url); ?>"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    aria-label="Scarica l'allegato <?php echo esc_attr($title_allegato); ?>"
                                                    title="Scarica l'allegato <?php echo esc_attr($title_allegato); ?>"
                                                >
                                                    <?php echo esc_html($title_allegato); ?>
                                                </a>
                                            </h5>
                                            <p class="card-text">Documento allegato</p>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </article>
                    <?php } ?>

                    <?php if(!empty($more_info)) { ?>
                        <article class="it-page-section anchor-offset mt-5">
                            <h4 id="informazioni-aggiuntive">Informazioni aggiuntive</h4>

                            <div class="richtext-wrapper lora">
                                <?php echo wp_kses_post(wpautop($more_info)); ?>
                            </div>
                        </article>
                    <?php } ?>

                    <div class="mb-0">
                        <?php get_template_part('template-parts/single/page_bottom'); ?>
                    </div>
                    
                </section>
            </div>
        </div>

        <?php 

        // Se il portale gestisce solo la nostra Trasparenza in modo esterno, indirizza all'home del comune.
        $portalesoloperusoesterno = dci_get_option("ck_portalesoloperusoesterno");

        // Se è attiva la trasparenza esterna, non visualizzare questi elementi
        if ($portalesoloperusoesterno !== 'true') { 
            get_template_part("template-parts/common/valuta-servizio"); 
            get_template_part("template-parts/common/assistenza-contatti"); 
        }?>

    <?php
    endwhile; // End of the loop.
    ?>
</main>

<?php
get_footer();
?>
