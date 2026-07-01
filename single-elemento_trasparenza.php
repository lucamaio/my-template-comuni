<?php

/**
 *  elemento_trasparenza template file
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Design_Comuni_Italia
 */

global $uo_id, $inline, $audio;


?>

<main>
    <?php
    while (have_posts()) :
            the_post();
        
            $user_can_view_post = dci_members_can_user_view_post(get_current_user_id(), $post->ID);
    
            $prefix = '_dci_elemento_trasparenza_';
            $descrizione_breve = dci_get_meta("descrizione_breve", $prefix, $post->ID);
            $descrizione = dci_get_meta("descrizione", $prefix, $post->ID);
            $file = dci_get_meta("file", $prefix, $post->ID);
            $url1 = dci_get_meta("url", $prefix, $post->ID);
            $url_documento_group = get_post_meta(get_the_ID(), $prefix . 'url_documento_group', true);
            //var_dump($url_documento_group);

            $data= get_the_date('j F Y', $post->ID);
            
            //$data_pubblicazione_arr = dci_get_data_pubblicazione_arr("data_pubblicazione", $prefix, $post->ID);
            //$data_pubblicazione = date_i18n('d F Y', mktime(0, 0, 0, $data_pubblicazione_arr[1], $data_pubblicazione_arr[0], $data_pubblicazione_arr[2]));
    
            //$data_scadenza_arr = dci_get_data_pubblicazione_arr("data_scadenza", $prefix, $post->ID);
            //$data_scadenza = date_i18n('d F Y', mktime(0, 0, 0, $data_scadenza_arr[1], $data_scadenza_arr[0], $data_scadenza_arr[2]));
    
            
            $elementi_collegati = dci_get_meta("post_trasparenza", $prefix, $post->ID);
            
        
             $ck_link = dci_get_meta('open_direct', $prefix, $post->ID);
                if (!empty($ck_link)) {
                
                    // ✅ Redirect automatico su file se presente
                    if (is_array($file) && !empty($file)) {
                        foreach ($file as $url) {
                            if (!empty($url)) {
                                $file_url = esc_url($url);
                                echo "<script>window.location.href = '" . esc_js($file_url) . "';</script>";
                                exit;
                            }
                        }
                    } elseif (is_string($file) && !empty($file)) {
                        $file_url = esc_url($file);
                        echo "<script>window.location.href = '" . esc_js($file_url) . "';</script>";
                        exit;
                    }
                
                    // ✅ Se nessun file valido, prova con i link
                    if (!empty($url1)) {
                        $link_url = esc_url($url1);
                        echo "<script>window.location.href = '" . esc_js($link_url) . "';</script>";
                        exit;
                    } elseif (!empty($url_documento_group)) {
                        if (is_array($url_documento_group)) {
                            foreach ($url_documento_group as $url) {
                                if (!empty($url)) {
                                    $link_url = esc_url($url);
                                    echo "<script>window.location.href = '" . esc_js($link_url) . "';</script>";
                                    exit;
                                }
                            }
                        } else {
                            $link_url = esc_url($url_documento_group);
                            echo "<script>window.location.href = '" . esc_js($link_url) . "';</script>";
                            exit;
                        }
                    }
                }

       get_header(); 
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
                        <?php echo $data; ?>
                    </p>
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
                                                            <?php if (!empty($descrizione)) { ?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#descrizione">
                                                                        <span class="title-medium">Descrizione</span>
                                                                    </a>
                                                                </li>
                                                            <?php } ?>
                                                            <?php if (is_array($file) && !empty($file)) { ?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#documenti">
                                                                        <span class="title-medium">Documenti</span>
                                                                    </a>
                                                                </li>
                                                            <?php } ?>
                                                            <?php if (!empty($url1)) { ?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#url">
                                                                        <span class="title-medium">Link alla pagina</span>
                                                                    </a>
                                                                </li>
                                                            <?php } ?>
                                                            <?php if (!empty($elementi_collegati)) { ?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#doc">
                                                                        <span class="title-medium">Elementi trasparenza correlati</span>
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
                    <?php if (!empty($descrizione) && $descrizione !== '' && $descrizione !== null) { ?>
                        <article class="it-page-section anchor-offset" data-audio>
                            <h4 id="descrizione" class="h3 mb-3 dci-transparency-resources__heading">Descrizione</h4>
                            <div class="richtext-wrapper lora">
                                <?php
                                if (preg_match('/[A-Z]{5,}/', $descrizione)) {
                                    echo ucfirst(strtolower($descrizione));
                                } else {
                                    echo $descrizione;
                                }
                                ?>
                            </div>
                        </article>
                    <?php } ?>

                    <?php if (
                        (is_array($file) && !empty($file))
                        || !empty($url1)
                        || !empty($url_documento_group)
                    ) { ?>
                        <style>
                            .dci-transparency-resources {
                                --dci-resource-primary: var(--tema-primary, #0d2b45);
                                --dci-resource-hover: var(--tema-hover, #133b5c);
                                --dci-resource-focus: var(--tema-focus, #1e5a8a);
                            }

                            .dci-transparency-resources__heading {
                                color: var(--dci-resource-primary);
                            }

                            .dci-transparency-resources__grid {
                                row-gap: 1rem;
                            }

                            .dci-transparency-resources__item {
                                display: flex;
                            }

                            .dci-transparency-resources__card {
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

                            .dci-transparency-resources__card::before {
                                position: absolute;
                                inset: 0 auto 0 0;
                                width: 4px;
                                background: var(--dci-resource-primary);
                                content: "";
                            }

                            .dci-transparency-resources__card:hover {
                                color: var(--dci-resource-hover);
                                background-color: #fbfcfd;
                                box-shadow: 0 12px 28px rgba(13, 43, 69, 0.14);
                                text-decoration: none;
                                transform: translateY(-2px);
                            }

                            .dci-transparency-resources__card:focus-visible {
                                outline: 3px solid var(--dci-resource-focus);
                                outline-offset: 3px;
                            }

                            .dci-transparency-resources__icon {
                                flex: 0 0 auto;
                                width: 42px;
                                height: 42px;
                                padding: 9px;
                                background-color: #f6f8fa;
                                border-radius: 10px;
                                box-shadow: 0 4px 12px rgba(13, 43, 69, 0.1);
                            }

                            .dci-transparency-resources__content {
                                min-width: 0;
                            }

                            .dci-transparency-resources__type {
                                display: block;
                                margin-bottom: 0.3rem;
                                color: var(--dci-resource-primary);
                                font-size: 0.76rem;
                                font-weight: 700;
                                letter-spacing: 0.035em;
                                line-height: 1.25;
                                text-transform: uppercase;
                            }

                            .dci-transparency-resources__title {
                                display: block;
                                overflow-wrap: anywhere;
                                font-size: 1rem;
                                font-weight: 700;
                                line-height: 1.35;
                            }

                            .dci-transparency-resources__meta {
                                display: block;
                                margin-top: 0.35rem;
                                overflow: hidden;
                                color: #455a64;
                                font-size: 0.85rem;
                                line-height: 1.35;
                                text-overflow: ellipsis;
                                white-space: nowrap;
                            }

                            .dci-transparency-resources__arrow {
                                position: absolute;
                                top: 50%;
                                right: 1rem;
                                width: 22px;
                                height: 22px;
                                color: var(--dci-resource-primary);
                                transform: translateY(-50%);
                            }

                            .dci-transparency-resources__arrow .icon {
                                width: 100%;
                                height: 100%;
                                fill: currentColor;
                            }

                            @media (prefers-reduced-motion: reduce) {
                                .dci-transparency-resources__card {
                                    transition: none;
                                }

                                .dci-transparency-resources__card:hover {
                                    transform: none;
                                }
                            }
                        </style>
                    <?php } ?>

                    <?php if (is_array($file) && !empty($file)) { ?>
                        <article class="it-page-section anchor-offset mt-5 dci-transparency-resources"
                            aria-labelledby="documenti">
                            <h4 class="h3 mb-3 dci-transparency-resources__heading" id="documenti">
                                Documenti
                            </h4>
                            <div class="row dci-transparency-resources__grid">
                                <?php foreach ($file as $file_url) {
                                    if (empty($file_url)) {
                                        continue;
                                    }

                                    $documento_id = attachment_url_to_postid($file_url);
                                    $documento = $documento_id ? get_post($documento_id) : null;
                                    $file_path = (string) parse_url($file_url, PHP_URL_PATH);
                                    $file_extension = strtoupper((string) pathinfo($file_path, PATHINFO_EXTENSION));
                                    $file_name = urldecode((string) pathinfo($file_path, PATHINFO_FILENAME));
                                    $titolo = $documento instanceof WP_Post
                                        ? get_the_title($documento)
                                        : ($file_name ?: 'Documento');
                                    $etichetta_documento = sprintf(
                                        'Visualizza il documento: %s',
                                        wp_strip_all_tags($titolo)
                                    );
                                    ?>
                                    <div class="col-12 col-md-6 dci-transparency-resources__item">
                                        <a class="dci-transparency-resources__card"
                                            href="<?php echo esc_url($file_url); ?>"
                                            title="<?php echo esc_attr($etichetta_documento); ?>"
                                            aria-label="<?php echo esc_attr($etichetta_documento); ?>">
                                            <img class="dci-transparency-resources__icon"
                                                src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%230D2B45' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 2.75h8l4 4v14.5H6z'/%3E%3Cpath d='M14 2.75v4h4M9 12h6M9 16h6'/%3E%3C/svg%3E"
                                                alt="<?php echo esc_attr(sprintf('Icona del documento %s', wp_strip_all_tags($titolo))); ?>"
                                                width="42"
                                                height="42">
                                            <span class="dci-transparency-resources__content">
                                                <span class="dci-transparency-resources__type">Documento</span>
                                                <span class="dci-transparency-resources__title">
                                                    <?php echo esc_html($titolo); ?>
                                                </span>
                                                <?php if (!empty($file_extension)) { ?>
                                                    <span class="dci-transparency-resources__meta">
                                                        Formato <?php echo esc_html($file_extension); ?>
                                                    </span>
                                                <?php } ?>
                                            </span>
                                            <span class="dci-transparency-resources__arrow" aria-hidden="true">
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

                    <?php if (!empty($url1) || !empty($url_documento_group)) { ?>
                        <article class="it-page-section anchor-offset mt-5 dci-transparency-resources"
                            aria-labelledby="url">
                            <h4 class="h3 mb-3 dci-transparency-resources__heading" id="url">Link</h4>
                            <div class="row dci-transparency-resources__grid">
                                <?php if (!empty($url1)) {
                                    $url1_host = (string) parse_url($url1, PHP_URL_HOST);
                                    $etichetta_url1 = 'Vai alla pagina collegata';
                                    ?>
                                    <div class="col-12 col-md-6 dci-transparency-resources__item">
                                        <a class="dci-transparency-resources__card"
                                            href="<?php echo esc_url($url1); ?>"
                                            title="<?php echo esc_attr($etichetta_url1); ?>"
                                            aria-label="<?php echo esc_attr($etichetta_url1); ?>">
                                            <img class="dci-transparency-resources__icon"
                                                src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%230D2B45' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M10 13a5 5 0 0 0 7.07.07l2-2A5 5 0 0 0 12 4l-1.15 1.15'/%3E%3Cpath d='M14 11a5 5 0 0 0-7.07-.07l-2 2A5 5 0 0 0 12 20l1.15-1.15'/%3E%3C/svg%3E"
                                                alt="Icona link alla pagina collegata"
                                                width="42"
                                                height="42">
                                            <span class="dci-transparency-resources__content">
                                                <span class="dci-transparency-resources__type">Link</span>
                                                <span class="dci-transparency-resources__title">Vai alla pagina</span>
                                                <?php if (!empty($url1_host)) { ?>
                                                    <span class="dci-transparency-resources__meta">
                                                        <?php echo esc_html($url1_host); ?>
                                                    </span>
                                                <?php } ?>
                                            </span>
                                            <span class="dci-transparency-resources__arrow" aria-hidden="true">
                                                <svg class="icon">
                                                    <use href="#it-chevron-right"></use>
                                                </svg>
                                            </span>
                                        </a>
                                    </div>
                                <?php } ?>

                                <?php
                                if (!empty($url_documento_group) && is_array($url_documento_group)) {
                                    foreach ($url_documento_group as $link_item) {
                                        $raw_url = !empty($link_item['url_documento'])
                                            ? $link_item['url_documento']
                                            : '';

                                        if (empty($raw_url)) {
                                            continue;
                                        }

                                        $link_path = (string) parse_url($raw_url, PHP_URL_PATH);
                                        $link_host = (string) parse_url($raw_url, PHP_URL_HOST);
                                        $nome = !empty($link_item['titolo'])
                                            ? wp_strip_all_tags($link_item['titolo'])
                                            : urldecode(basename($link_path));
                                        $nome = $nome ?: 'Vai al link';
                                        $target_blank = !empty($link_item['target_blank']);
                                        $etichetta_link = $target_blank
                                            ? sprintf('Apri %s in una nuova scheda', $nome)
                                            : sprintf('Vai al link: %s', $nome);
                                        ?>
                                        <div class="col-12 col-md-6 dci-transparency-resources__item">
                                            <a class="dci-transparency-resources__card"
                                                href="<?php echo esc_url($raw_url); ?>"
                                                <?php if ($target_blank) { ?>
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                <?php } ?>
                                                title="<?php echo esc_attr($etichetta_link); ?>"
                                                aria-label="<?php echo esc_attr($etichetta_link); ?>">
                                                <img class="dci-transparency-resources__icon"
                                                    src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%230D2B45' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M10 13a5 5 0 0 0 7.07.07l2-2A5 5 0 0 0 12 4l-1.15 1.15'/%3E%3Cpath d='M14 11a5 5 0 0 0-7.07-.07l-2 2A5 5 0 0 0 12 20l1.15-1.15'/%3E%3C/svg%3E"
                                                    alt="<?php echo esc_attr(sprintf('Icona del link %s', $nome)); ?>"
                                                    width="42"
                                                    height="42">
                                                <span class="dci-transparency-resources__content">
                                                    <span class="dci-transparency-resources__type">
                                                        <?php echo $target_blank ? 'Link esterno' : 'Link'; ?>
                                                    </span>
                                                    <span class="dci-transparency-resources__title">
                                                        <?php echo esc_html($nome); ?>
                                                    </span>
                                                    <?php if (!empty($link_host)) { ?>
                                                        <span class="dci-transparency-resources__meta">
                                                            <?php echo esc_html($link_host); ?>
                                                        </span>
                                                    <?php } ?>
                                                </span>
                                                <span class="dci-transparency-resources__arrow" aria-hidden="true">
                                                    <svg class="icon">
                                                        <use href="<?php echo $target_blank ? '#it-external-link' : '#it-chevron-right'; ?>"></use>
                                                    </svg>
                                                </span>
                                            </a>
                                        </div>
                                    <?php }
                                } ?>
                            </div>
                        </article>
                    <?php } ?>

                    <?php if (!empty($elementi_collegati)) { ?>
                        <style>
                            .dci-related-transparency {
                                --dci-related-primary: var(--tema-primary, #0d2b45);
                                --dci-related-hover: var(--tema-hover, #133b5c);
                                --dci-related-focus: var(--tema-focus, #1e5a8a);
                            }

                            .dci-related-transparency__heading {
                                color: var(--dci-related-primary);
                            }

                            .dci-related-transparency__grid {
                                row-gap: 1rem;
                            }

                            .dci-related-transparency__item {
                                display: flex;
                            }

                            .dci-related-transparency__card {
                                position: relative;
                                display: flex;
                                align-items: flex-start;
                                gap: 1rem;
                                width: 100%;
                                min-height: 126px;
                                padding: 1.25rem;
                                overflow: hidden;
                                color: var(--dci-related-primary);
                                background-color: #ffffff;
                                border-radius: 12px;
                                box-shadow: 0 8px 24px rgba(13, 43, 69, 0.09);
                                text-decoration: none;
                                transition: transform 0.2s ease, box-shadow 0.2s ease;
                            }

                            .dci-related-transparency__card::before {
                                position: absolute;
                                inset: 0 auto 0 0;
                                width: 4px;
                                background: var(--dci-related-primary);
                                content: "";
                            }

                            .dci-related-transparency__card:hover {
                                color: var(--dci-related-hover);
                                background-color: #fbfcfd;
                                box-shadow: 0 12px 30px rgba(13, 43, 69, 0.15);
                                text-decoration: none;
                                transform: translateY(-2px);
                            }

                            .dci-related-transparency__card:focus-visible {
                                outline: 3px solid var(--dci-related-focus);
                                outline-offset: 3px;
                            }

                            .dci-related-transparency__icon {
                                flex: 0 0 auto;
                                width: 42px;
                                height: 42px;
                                padding: 9px;
                                background-color: #f6f8fa;
                                border-radius: 10px;
                                box-shadow: 0 4px 12px rgba(13, 43, 69, 0.1);
                            }

                            .dci-related-transparency__content {
                                min-width: 0;
                                padding-right: 1.75rem;
                            }

                            .dci-related-transparency__eyebrow {
                                display: block;
                                margin-bottom: 0.35rem;
                                color: var(--dci-related-primary);
                                font-size: 0.78rem;
                                font-weight: 700;
                                letter-spacing: 0.035em;
                                line-height: 1.25;
                                text-transform: uppercase;
                            }

                            .dci-related-transparency__title {
                                display: block;
                                font-size: 1.05rem;
                                font-weight: 700;
                                line-height: 1.35;
                            }

                            .dci-related-transparency__description {
                                display: block;
                                margin-top: 0.45rem;
                                color: #455a64;
                                font-size: 0.92rem;
                                font-weight: 400;
                                line-height: 1.45;
                            }

                            .dci-related-transparency__arrow {
                                position: absolute;
                                top: 50%;
                                right: 1.15rem;
                                width: 22px;
                                height: 22px;
                                color: var(--dci-related-primary);
                                transform: translateY(-50%);
                            }

                            .dci-related-transparency__arrow .icon {
                                width: 100%;
                                height: 100%;
                                fill: currentColor;
                            }

                            @media (prefers-reduced-motion: reduce) {
                                .dci-related-transparency__card {
                                    transition: none;
                                }

                                .dci-related-transparency__card:hover {
                                    transform: none;
                                }
                            }
                        </style>

                        <article class="it-page-section anchor-offset mt-5 dci-related-transparency"
                            aria-labelledby="doc">
                            <h2 class="h3 mb-3 dci-related-transparency__heading" id="doc">
                                Elementi della trasparenza correlati
                            </h2>
                            <div data-element="service-document">
                                <div class="row dci-related-transparency__grid">
                                    <?php foreach ($elementi_collegati as $elemento_id) {
                                        $elemento = get_post($elemento_id);

                                        if (!$elemento instanceof WP_Post) {
                                            continue;
                                        }

                                        $titolo_elemento = get_the_title($elemento);
                                        $url_elemento = get_permalink($elemento);
                                        $descrizione_elemento = dci_get_meta(
                                            'descrizione_breve',
                                            '_dci_elemento_trasparenza_',
                                            $elemento->ID
                                        );
                                        $descrizione_elemento = preg_replace(
                                            '/\s+/u',
                                            ' ',
                                            trim(wp_strip_all_tags((string) $descrizione_elemento))
                                        );
                                        $descrizione_elemento = wp_html_excerpt(
                                            $descrizione_elemento,
                                            100,
                                            '...'
                                        );
                                        $etichetta_link = sprintf(
                                            'Vai all’elemento della trasparenza: %s',
                                            wp_strip_all_tags($titolo_elemento)
                                        );
                                        ?>
                                        <div class="col-12 col-md-6 dci-related-transparency__item">
                                            <a class="dci-related-transparency__card"
                                                href="<?php echo esc_url($url_elemento); ?>"
                                                title="<?php echo esc_attr($etichetta_link); ?>"
                                                aria-label="<?php echo esc_attr($etichetta_link); ?>">
                                                <img class="dci-related-transparency__icon"
                                                    src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%230D2B45' stroke-width='1.7' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M3 9h18L12 3zM4 20h16M3 22h18M6 10v8M10 10v8M14 10v8M18 10v8'/%3E%3C/svg%3E"
                                                    alt="<?php echo esc_attr(sprintf('Icona amministrazione trasparente per %s', wp_strip_all_tags($titolo_elemento))); ?>"
                                                    width="42"
                                                    height="42">
                                                <span class="dci-related-transparency__content">
                                                    <span class="dci-related-transparency__eyebrow">
                                                        Amministrazione trasparente
                                                    </span>
                                                    <span class="dci-related-transparency__title">
                                                        <?php echo esc_html($titolo_elemento); ?>
                                                    </span>
                                                    <?php if (!empty($descrizione_elemento)) { ?>
                                                        <span class="dci-related-transparency__description">
                                                            <?php echo esc_html($descrizione_elemento); ?>
                                                        </span>
                                                    <?php } ?>
                                                </span>
                                                <span class="dci-related-transparency__arrow" aria-hidden="true">
                                                    <svg class="icon">
                                                        <use href="#it-chevron-right"></use>
                                                    </svg>
                                                </span>
                                            </a>
                                        </div>
                                    <?php } ?>
                                </div>
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
