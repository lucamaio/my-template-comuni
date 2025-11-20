<?php

/**
 * atto_concessione template file
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
        // Le variabili di post meta vengono recuperate qui, prima del markup principale
        // Dato che è un single.php, il loop have_posts() girerà una sola volta.
        $user_can_view_post = dci_members_can_user_view_post(get_current_user_id(), $post->ID);

        $prefix = '_dci_atto_concessione_';
        $stato_atto = get_post_meta($post->ID, $prefix . 'stato_atto', true);
        $descrizione_breve = get_post_meta($post->ID, $prefix . 'descrizione_breve', true); // Assicurati sia lo stesso meta key

        $data = get_the_date('j F Y', $post->ID);
        $anno_beneficio_raw = get_post_meta($post->ID, $prefix . 'anno_beneficio', true);
        // Formatta l'anno beneficio se è un timestamp, altrimenti usa il valore così com'è se è già l'anno.
        $anno_beneficio = !empty($anno_beneficio_raw) ? date_i18n('Y', $anno_beneficio_raw) : '-';

        $importo_raw = get_post_meta($post->ID, $prefix . 'importo', true);
        // Pulizia e formattazione dell'importo
        $importo_numeric = floatval(str_replace(',', '.', preg_replace('/[^\d,]+/', '', $importo_raw)));
        $importo = $importo_numeric !== 0.0 ? esc_html(number_format($importo_numeric, 2, ',', '.')) . '€' : 'Non specificato';

        $responsabile = !empty(get_post_meta($post->ID, $prefix . 'responsabile', true)) ? get_post_meta($post->ID, $prefix . 'responsabile', true) : "Non specificato";
        
        $rag_incarico = get_post_meta($post->ID, $prefix . 'rag_incarico', true); // Recupera il valore della tassonomia o meta box
        $rag_incarico_display = !empty($rag_incarico) ? esc_html($rag_incarico) : 'Non specificato';

        $rag_soc = get_post_meta($post->ID, $prefix . 'ragione_sociale', true);
        $cod_fisc = get_post_meta($post->ID, $prefix . 'codice_fiscale', true);

        $codice_fiscale = !empty($cod_fisc) ? esc_html($cod_fisc) : 'Non specificato';
        $ragione_sociale = !empty($rag_soc) ? esc_html($rag_soc) : 'Non specificato';

        // Gli allegati, assumendo che dci_get_meta restituisca un array di URL
        $documenti = get_post_meta($post->ID, $prefix . 'allegati', true);

        // Se allegati è un campo 'file_list' di CMB2, $documenti sarà un array di array,
        // altrimenti è un array di URL come sembrava nel codice precedente.
        // Se è 'file_list', il foreach dovrà ciclare su $file_id => $file_data e usare $file_data['url'], $file_data['title'].
        // Ho mantenuto la logica per array di URL e poi ricerca dell'ID.
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
                    $title_display = get_the_title();
                    if (preg_match('/[A-Z]{5,}/', $title_display)) {
                        echo '<h1 data-audio>' . ucfirst(strtolower($title_display)) . '</h1>';
                    } else {
                        echo '<h1 data-audio>' . $title_display . '</h1>';
                    }
                    ?>
                    <h2 class="visually-hidden" data-audio>Dettagli Atto di Concessione</h2>
                    <?php if (!empty($descrizione_breve)) {
                        $desc_display = $descrizione_breve;
                        if (preg_match('/[A-Z]{5,}/', $desc_display)) {
                            echo '<p data-audio>' . ucfirst(strtolower($desc_display)) . '</p>';
                        } else {
                            echo '<p data-audio>' . $desc_display . '</p>';
                        }
                    }
                    ?>
                </div>
                <div class="col-lg-3 offset-lg-1">
                    <?php
                    $inline = true; // Imposta la variabile globale per il template part
                    get_template_part('template-parts/single/actions');
                    ?>
                </div>
            </div>
            <div class="row mt-5 mb-4">
                <div class="col-6">
                    <small>Data Pubblicazione:</small>
                    <p class="fw-semibold font-monospace">
                        <?php echo esc_html($data); ?>
                    </p>
                </div>
                <div class="col-6">
                    <small>Anno Beneficio:</small>
                    <p class="fw-semibold font-monospace">
                        <?php echo esc_html($anno_beneficio); ?>
                    </p>
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
                                                            <?php if (!empty($descrizione_breve)) { ?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#desc-breve">
                                                                        <span class="title-medium">Descrizione Breve</span>
                                                                    </a>
                                                                </li>
                                                            <?php } ?>
                                                            <?php if ($ragione_sociale !== 'Non specificato' || $codice_fiscale !== 'Non specificato') { ?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#beneficiario">
                                                                        <span class="title-medium">Beneficiario</span>
                                                                    </a>
                                                                </li>
                                                            <?php } ?>
                                                            <?php if ($importo !== 'Non specificato') { ?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#importo">
                                                                        <span class="title-medium">Importo</span>
                                                                    </a>
                                                                </li>
                                                            <?php } ?>
                                                            <?php
                                                            // Recupero l'importo liquidato qui, se esiste un meta box per esso
                                                            $importo_liquidato_raw = get_post_meta($post->ID, $prefix . 'importo_liquidato', true);
                                                            $importo_liquidato = floatval(str_replace(',', '.', preg_replace('/[^\d,]+/', '', $importo_liquidato_raw)));
                                                            if ($importo_liquidato !== 0.0) { ?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#liquidato">
                                                                        <span class="title-medium">Importo Liquidato</span>
                                                                    </a>
                                                                </li>
                                                            <?php } ?>
                                                            <li class="nav-item">
                                                                <a class="nav-link" href="#more-info">
                                                                    <span class="title-medium">Ulteriori informazioni</span>
                                                                </a>
                                                            </li>
                                                            <?php if (!empty($documenti) && is_array($documenti)) { ?>
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
                    <?php if (!empty($descrizione_breve)) { ?>
                        <article class="it-page-section anchor-offset" data-audio>
                            <h4 id="desc-breve">Descrizione Breve</h4>
                            <div class="richtext-wrapper lora">
                                <?php
                                $desc_text = $descrizione_breve;
                                if (preg_match('/[A-Z]{5,}/', $desc_text)) {
                                    echo ucfirst(strtolower($desc_text));
                                } else {
                                    echo $desc_text;
                                }
                                ?>
                            </div>
                        </article>
                    <?php } ?>

                    <?php if ($ragione_sociale !== 'Non specificato' || $codice_fiscale !== 'Non specificato') { ?>
                        <article class="it-page-section anchor-offset mt-5">
                            <h4 id="beneficiario">Beneficiario</h4>
                            <div class="card card-border-top mb-0">
                                <p class="mb-0" style="text-align:justify">
                                    <strong>Ragione Sociale: </strong> <?php echo $ragione_sociale; ?>
                                </p>
                                <p class="mb-0" style="text-align:justify">
                                    <strong>Codice Fiscale: </strong> <?php echo $codice_fiscale; ?>
                                </p>
                            </div>
                        </article>
                    <?php } ?>

                    <?php if ($importo !== 'Non specificato') { ?>
                        <section class="it-page-section mb-3">
                            <h5 id="importo">Importo</h5>
                            <div class="richtext-wrapper lora" data-element="service-cost">
                                <?php echo $importo; ?>
                            </div>
                        </section>
                    <?php } ?>

                    <?php if ($importo_liquidato !== 0.0) { // Controlla la variabile già formattata/pulita ?>
                        <section class="it-page-section mb-3">
                            <h5 id="liquidato">Importo Liquidato</h5>
                            <div class="richtext-wrapper lora" data-element="service-cost">
                                <?php echo esc_html(number_format($importo_liquidato, 2, ',', '.')) . '€'; ?>
                            </div>
                        </section>
                    <?php } ?>

                    <section class="it-page-section mb-3">
                        <h5 id="more-info">Ulteriori informazioni</h5>
                        <div class="card card-border-top mb-0">
                            <p class="mb-0" style="text-align:justify">
                                <strong>Ragione dell'incarico: </strong> <?php echo $rag_incarico_display; ?>
                            </p>
                            <p class="mb-0" style="text-align:justify">
                                <strong>Responsabile: </strong> <?php echo $responsabile; ?>
                            </p>
                        </div>
                    </section>
                    
                    <?php if (!empty($documenti) && is_array($documenti)) { ?>
                        <article class="it-page-section anchor-offset mt-5">
                            <h4 id="documenti">Documenti</h4>
                            <div class="card-wrapper card-teaser-wrapper card-teaser-wrapper-equal">
                                <?php
                                foreach ($documenti as $file_url) { // Assumo che $documenti sia un array di URL
                                    $file_id = attachment_url_to_postid($file_url); // Ottieni l'ID dal URL
                                    $allegato = get_post($file_id); // Ottieni l'oggetto WP_Post per l'allegato

                                    if ($allegato) { // Assicurati che l'allegato esista
                                        $title_allegato = $allegato->post_title;
                                        
                                        if (strlen($title_allegato) > 50) {
                                            $title_allegato = substr($title_allegato, 0, 50) . '...';
                                        }
                                        if (preg_match('/[A-Z]{5,}/', $title_allegato)) {
                                            $title_allegato = ucfirst(strtolower($title_allegato));
                                        }
                                        ?>
                                        <div class="card card-teaser shadow-sm p-4 mt-3 rounded border border-light flex-nowrap">
                                            <svg class="icon" aria-hidden="true">
                                                <use xlink:href="#it-clip"></use>
                                            </svg>
                                            <div class="card-body">
                                                <h5 class="card-title">
                                                    <a class="text-decoration-none" href="<?php echo esc_url($file_url); ?>"
                                                        aria-label="Scarica l'allegato <?php echo esc_attr($allegato->post_title); ?>"
                                                        title="Scarica l'allegato <?php echo esc_attr($allegato->post_title); ?>"
                                                        target="_blank" rel="noopener noreferrer">
                                                        <?php echo esc_html($title_allegato); ?>
                                                    </a>
                                                </h5>
                                            </div>
                                        </div>
                                    <?php
                                    }
                                } ?>
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