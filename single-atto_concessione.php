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

        $dci_atto_display_value = static function ($value) {
            if (is_array($value) || is_object($value)) {
                return '-';
            }

            $value = trim((string) $value);

            if (
                $value === ''
                || preg_match('/^null+$/i', $value)
                || strcasecmp($value, 'Non specificato') === 0
            ) {
                return '-';
            }

            return $value;
        };

        $stato_terms = get_the_terms($post->ID, 'tipi_stato_bando');
        $stato_atto = '-';

        if (!empty($stato_terms) && !is_wp_error($stato_terms)) {
            $stato_names = wp_list_pluck($stato_terms, 'name');
            $stato_names = array_filter(array_map('trim', $stato_names));
            $stato_atto = !empty($stato_names) ? implode(', ', $stato_names) : '-';
        }

        $descrizione_breve = get_post_meta($post->ID, $prefix . 'descrizione_breve', true);

        $data = get_the_date('j F Y', $post->ID);
        $anno_beneficio_raw = get_post_meta($post->ID, $prefix . 'anno_beneficio', true);
        $anno_beneficio_value = $dci_atto_display_value($anno_beneficio_raw);
        $anno_beneficio = $anno_beneficio_value === '-'
            ? '-'
            : (preg_match('/^\d{4}$/', $anno_beneficio_value)
                ? $anno_beneficio_value
                : date_i18n('Y', (int) $anno_beneficio_value));

        $importo_raw = get_post_meta($post->ID, $prefix . 'importo', true);
        $importo_numeric = floatval(str_replace(',', '.', preg_replace('/[^\d,]+/', '', $importo_raw)));
        $importo = $dci_atto_display_value($importo_raw) !== '-'
            ? number_format($importo_numeric, 2, ',', '.') . ' €'
            : '-';

        $importo_liquidato_raw = get_post_meta($post->ID, $prefix . 'importo_liquidato', true);
        $importo_liquidato_numeric = floatval(
            str_replace(',', '.', preg_replace('/[^\d,]+/', '', $importo_liquidato_raw))
        );
        $importo_liquidato_display = $dci_atto_display_value($importo_liquidato_raw) !== '-'
            ? number_format($importo_liquidato_numeric, 2, ',', '.') . ' €'
            : '-';

        $responsabile = $dci_atto_display_value(
            get_post_meta($post->ID, $prefix . 'responsabile', true)
        );
        $rag_incarico_display = $dci_atto_display_value(
            get_post_meta($post->ID, $prefix . 'rag_incarico', true)
        );
        $rag_soc = get_post_meta($post->ID, $prefix . 'ragione_sociale', true);
        $cod_fisc = get_post_meta($post->ID, $prefix . 'codice_fiscale', true);
        $codice_fiscale = $dci_atto_display_value($cod_fisc);
        $ragione_sociale = $dci_atto_display_value($rag_soc);

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
                                                            <li class="nav-item">
                                                                <a class="nav-link" href="#dati-principali">
                                                                    <span class="title-medium">Dati principali</span>
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

                    <style>
                        .dci-concession-summary {
                            --dci-concession-primary: var(--tema-primary, #0d2b45);
                            margin-bottom: 2rem;
                            padding-bottom: 1.5rem;
                            border-bottom: 1px solid #e5e7eb;
                        }

                        .dci-concession-summary__title {
                            margin-bottom: 1rem;
                            color: var(--dci-concession-primary);
                            font-size: 1.25rem;
                            font-weight: 700;
                        }

                        .dci-concession-summary__grid {
                            display: grid;
                            grid-template-columns: repeat(2, minmax(0, 1fr));
                            gap: 0.75rem;
                        }

                        .dci-concession-summary__item {
                            padding: 0.85rem 1rem;
                            background-color: #f7f9fb;
                            border: 1px solid #edf0f3;
                            border-radius: 10px;
                        }

                        .dci-concession-summary__item--full {
                            grid-column: 1 / -1;
                        }

                        .dci-concession-summary__label {
                            display: block;
                            margin-bottom: 0.25rem;
                            color: #5b6f82;
                            font-size: 0.72rem;
                            font-weight: 700;
                            letter-spacing: 0.04em;
                            text-transform: uppercase;
                        }

                        .dci-concession-summary__value {
                            display: block;
                            color: var(--dci-concession-primary);
                            font-size: 0.95rem;
                            font-weight: 600;
                            line-height: 1.4;
                            overflow-wrap: anywhere;
                        }

                        @media (max-width: 767.98px) {
                            .dci-concession-summary__grid {
                                grid-template-columns: 1fr;
                            }

                            .dci-concession-summary__item--full {
                                grid-column: auto;
                            }

                            .dci-concession-summary__item {
                                padding: 0.8rem;
                            }
                        }
                    </style>

                    <article class="it-page-section anchor-offset mt-5 dci-concession-summary"
                        aria-labelledby="dati-principali">
                        <h4 class="dci-concession-summary__title" id="dati-principali">
                            Dati principali
                        </h4>
                        <div class="dci-concession-summary__grid">
                            <div class="dci-concession-summary__item">
                                <span class="dci-concession-summary__label">Stato dell’atto</span>
                                <span class="dci-concession-summary__value">
                                    <?php echo esc_html($dci_atto_display_value($stato_atto)); ?>
                                </span>
                            </div>
                            <div class="dci-concession-summary__item">
                                <span class="dci-concession-summary__label">Data pubblicazione</span>
                                <span class="dci-concession-summary__value">
                                    <?php echo esc_html($dci_atto_display_value($data)); ?>
                                </span>
                            </div>
                            <div class="dci-concession-summary__item">
                                <span class="dci-concession-summary__label">Anno beneficio</span>
                                <span class="dci-concession-summary__value">
                                    <?php echo esc_html($dci_atto_display_value($anno_beneficio)); ?>
                                </span>
                            </div>
                            <div class="dci-concession-summary__item">
                                <span class="dci-concession-summary__label">Ragione sociale</span>
                                <span class="dci-concession-summary__value">
                                    <?php echo esc_html($ragione_sociale); ?>
                                </span>
                            </div>
                            <div class="dci-concession-summary__item">
                                <span class="dci-concession-summary__label">Codice fiscale / P.IVA</span>
                                <span class="dci-concession-summary__value">
                                    <?php echo esc_html($codice_fiscale); ?>
                                </span>
                            </div>
                            <div class="dci-concession-summary__item">
                                <span class="dci-concession-summary__label">Importo</span>
                                <span class="dci-concession-summary__value">
                                    <?php echo esc_html($importo); ?>
                                </span>
                            </div>
                            <div class="dci-concession-summary__item">
                                <span class="dci-concession-summary__label">Importo liquidato</span>
                                <span class="dci-concession-summary__value">
                                    <?php echo esc_html($importo_liquidato_display); ?>
                                </span>
                            </div>
                            <div class="dci-concession-summary__item">
                                <span class="dci-concession-summary__label">Responsabile</span>
                                <span class="dci-concession-summary__value">
                                    <?php echo esc_html($responsabile); ?>
                                </span>
                            </div>
                            <div class="dci-concession-summary__item dci-concession-summary__item--full">
                                <span class="dci-concession-summary__label">Ragione dell’incarico</span>
                                <span class="dci-concession-summary__value">
                                    <?php echo esc_html($rag_incarico_display); ?>
                                </span>
                            </div>
                        </div>
                    </article>
                    
                    <?php if (!empty($documenti) && is_array($documenti)) { ?>
                        <style>
                            .dci-document-resources {
                                --dci-document-primary: var(--tema-primary, #0d2b45);
                                --dci-document-hover: var(--tema-hover, #133b5c);
                                --dci-document-focus: var(--tema-focus, #1e5a8a);
                            }

                            .dci-document-resources__heading {
                                color: var(--dci-document-primary);
                            }

                            .dci-document-resources__grid {
                                row-gap: 1rem;
                            }

                            .dci-document-resources__item {
                                display: flex;
                            }

                            .dci-document-resources__card {
                                position: relative;
                                display: flex;
                                align-items: flex-start;
                                gap: 1rem;
                                width: 100%;
                                min-height: 112px;
                                padding: 1.15rem 3.25rem 1.15rem 1.15rem;
                                overflow: hidden;
                                color: var(--dci-document-primary);
                                background-color: #ffffff;
                                border-radius: 12px;
                                box-shadow: 0 8px 22px rgba(13, 43, 69, 0.08);
                                text-decoration: none;
                                transition: transform 0.2s ease, box-shadow 0.2s ease;
                            }

                            .dci-document-resources__card::before {
                                position: absolute;
                                inset: 0 auto 0 0;
                                width: 4px;
                                background: var(--dci-document-primary);
                                content: "";
                            }

                            .dci-document-resources__card:hover {
                                color: var(--dci-document-hover);
                                background-color: #fbfcfd;
                                box-shadow: 0 12px 28px rgba(13, 43, 69, 0.14);
                                text-decoration: none;
                                transform: translateY(-2px);
                            }

                            .dci-document-resources__card:focus-visible {
                                outline: 3px solid var(--dci-document-focus);
                                outline-offset: 3px;
                            }

                            .dci-document-resources__icon {
                                flex: 0 0 auto;
                                width: 42px;
                                height: 42px;
                                padding: 9px;
                                background-color: #f6f8fa;
                                border-radius: 10px;
                                box-shadow: 0 4px 12px rgba(13, 43, 69, 0.1);
                            }

                            .dci-document-resources__content {
                                min-width: 0;
                            }

                            .dci-document-resources__type {
                                display: block;
                                margin-bottom: 0.3rem;
                                color: var(--dci-document-primary);
                                font-size: 0.76rem;
                                font-weight: 700;
                                letter-spacing: 0.035em;
                                line-height: 1.25;
                                text-transform: uppercase;
                            }

                            .dci-document-resources__title {
                                display: block;
                                overflow-wrap: anywhere;
                                font-size: 1rem;
                                font-weight: 700;
                                line-height: 1.35;
                            }

                            .dci-document-resources__meta {
                                display: block;
                                margin-top: 0.35rem;
                                color: #455a64;
                                font-size: 0.85rem;
                                line-height: 1.35;
                            }

                            .dci-document-resources__arrow {
                                position: absolute;
                                top: 50%;
                                right: 1rem;
                                width: 22px;
                                height: 22px;
                                color: var(--dci-document-primary);
                                transform: translateY(-50%);
                            }

                            .dci-document-resources__arrow .icon {
                                width: 100%;
                                height: 100%;
                                fill: currentColor;
                            }

                            @media (prefers-reduced-motion: reduce) {
                                .dci-document-resources__card {
                                    transition: none;
                                }

                                .dci-document-resources__card:hover {
                                    transform: none;
                                }
                            }
                        </style>

                        <article class="it-page-section anchor-offset mt-5 dci-document-resources"
                            aria-labelledby="documenti">
                            <h4 class="h3 mb-3 dci-document-resources__heading" id="documenti">Documenti</h4>
                            <div class="row dci-document-resources__grid">
                                <?php foreach ($documenti as $file_key => $file_value) {
                                    $file_id = 0;
                                    $file_title = '';

                                    if (is_array($file_value)) {
                                        $file_id = absint($file_value['id'] ?? $file_key);
                                        $file_url = $file_value['url'] ?? '';
                                        $file_title = $file_value['title'] ?? '';
                                    } else {
                                        $file_url = (string) $file_value;
                                    }

                                    if (empty($file_url) && $file_id) {
                                        $file_url = wp_get_attachment_url($file_id);
                                    }

                                    if (empty($file_url)) {
                                        continue;
                                    }

                                    if (!$file_id) {
                                        $file_id = attachment_url_to_postid($file_url);
                                    }

                                    $allegato = $file_id ? get_post($file_id) : null;
                                    $file_path = (string) parse_url($file_url, PHP_URL_PATH);
                                    $file_extension = strtoupper((string) pathinfo($file_path, PATHINFO_EXTENSION));
                                    $file_name = urldecode((string) pathinfo($file_path, PATHINFO_FILENAME));
                                    $title_allegato = $file_title
                                        ?: ($allegato instanceof WP_Post ? get_the_title($allegato) : $file_name);
                                    $title_allegato = $title_allegato ?: 'Documento';
                                    $etichetta_documento = sprintf(
                                        'Apri il documento %s in una nuova scheda',
                                        wp_strip_all_tags($title_allegato)
                                    );
                                    ?>
                                    <div class="col-12 col-md-6 dci-document-resources__item">
                                        <a class="dci-document-resources__card"
                                            href="<?php echo esc_url($file_url); ?>"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            title="<?php echo esc_attr($etichetta_documento); ?>"
                                            aria-label="<?php echo esc_attr($etichetta_documento); ?>">
                                            <img class="dci-document-resources__icon"
                                                src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%230D2B45' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 2.75h8l4 4v14.5H6z'/%3E%3Cpath d='M14 2.75v4h4M9 12h6M9 16h6'/%3E%3C/svg%3E"
                                                alt="<?php echo esc_attr(sprintf('Icona del documento %s', wp_strip_all_tags($title_allegato))); ?>"
                                                width="42"
                                                height="42">
                                            <span class="dci-document-resources__content">
                                                <span class="dci-document-resources__type">Documento</span>
                                                <span class="dci-document-resources__title">
                                                    <?php echo esc_html($title_allegato); ?>
                                                </span>
                                                <?php if (!empty($file_extension)) { ?>
                                                    <span class="dci-document-resources__meta">
                                                        Formato <?php echo esc_html($file_extension); ?>
                                                    </span>
                                                <?php } ?>
                                            </span>
                                            <span class="dci-document-resources__arrow" aria-hidden="true">
                                                <svg class="icon">
                                                    <use href="#it-download"></use>
                                                </svg>
                                            </span>
                                        </a>
                                    </div>
                                <?php } ?>
                            </div>
                        </article>
                    <?php } ?>

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
</main>

<?php
    endwhile; // End of the loop.
    get_footer();
?>
