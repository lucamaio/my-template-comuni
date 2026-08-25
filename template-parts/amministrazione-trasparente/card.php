<?php
global $elemento;
require_once get_template_directory() . '/template-parts/amministrazione-trasparente/custom-section-card-helpers.php';

$prefix = '_dci_elemento_trasparenza_';

// Metadati
$descrizione_breve = dci_get_meta('descrizione_breve', $prefix, $elemento->ID);
$ck_target         = dci_get_meta('open_in_new_tab', $prefix, $elemento->ID) === 'on';
$ck_link           = dci_get_meta('open_direct', $prefix, $elemento->ID) === 'on';
$in_evidenza       = dci_get_meta('in_evidenza', $prefix, $elemento->ID) === 'on';
$url               = dci_get_meta('url', $prefix, $elemento->ID);
$immagine          = dci_get_meta('immagine', $prefix, $elemento->ID);

$immagine_url = is_string($immagine) ? esc_url_raw($immagine) : '';
$immagine_id = $immagine_url !== '' ? attachment_url_to_postid($immagine_url) : 0;
$immagine_mime = $immagine_id ? (string) get_post_mime_type($immagine_id) : '';
$immagine_path = (string) wp_parse_url($immagine_url, PHP_URL_PATH);
$immagine_estensione = strtolower((string) pathinfo($immagine_path, PATHINFO_EXTENSION));
$immagine_formati = ['jpg', 'jpeg', 'jpe', 'png', 'gif', 'webp', 'avif', 'svg', 'bmp', 'ico', 'tif', 'tiff', 'heic', 'heif'];
$immagine_valida = $immagine_url !== '' && (
    strpos($immagine_mime, 'image/') === 0
    || in_array($immagine_estensione, $immagine_formati, true)
);
$immagine_alt = $immagine_id ? trim((string) get_post_meta($immagine_id, '_wp_attachment_image_alt', true)) : '';

if ($immagine_alt === '') {
    $immagine_alt = get_the_title($elemento->ID);
}

$documenti         = dci_get_meta('file', $prefix, $elemento->ID);
$link_documenti    = dci_get_meta('url_documento_group', $prefix, $elemento->ID);
$documento = is_array($documenti) && !empty($documenti) ? get_permalink($elemento->ID) : $documenti;
$data= get_the_date('j F Y', $elemento->ID);
$data_iso = get_the_date('c', $elemento->ID);

$ck_sowh_section = dci_get_option("ck_show_section", "Trasparenza");
$show_search_categories = !empty($args['show_search_categories']);
$risorse_card = [];

if (is_array($documenti)) {
    foreach ($documenti as $file_id => $file_url) {
        if (!is_string($file_url) || $file_url === '') {
            continue;
        }

        $file_path = (string) wp_parse_url($file_url, PHP_URL_PATH);
        $file_name = urldecode((string) basename($file_path));
        $attachment_id = is_numeric($file_id) ? (int) $file_id : 0;
        $file_title = dci_custom_section_attachment_title(
            $attachment_id,
            $file_url,
            $file_name !== '' ? $file_name : __('Documento allegato', 'design_comuni_italia')
        );

        $risorse_card[$file_url] = [
            'type'   => 'file',
            'url'    => $file_url,
            'name'   => $file_title,
            'target' => $ck_target,
        ];
    }
}

if (is_array($link_documenti)) {
    foreach ($link_documenti as $link_item) {
        if (!is_array($link_item) || empty($link_item['url_documento'])) {
            continue;
        }

        $link_url = (string) $link_item['url_documento'];
        $link_path = (string) wp_parse_url($link_url, PHP_URL_PATH);
        $link_host = (string) wp_parse_url($link_url, PHP_URL_HOST);
        $link_name = !empty($link_item['titolo'])
            ? trim(wp_strip_all_tags((string) $link_item['titolo']))
            : urldecode((string) basename($link_path));

        if ($link_name === '') {
            $link_name = $link_host !== '' ? $link_host : __('Documento collegato', 'design_comuni_italia');
        }

        $risorse_card[$link_url] = [
            'type'   => 'link',
            'url'    => $link_url,
            'name'   => $link_name,
            'target' => !empty($link_item['target_blank']),
        ];
    }
}

if (is_string($url) && $url !== '') {
    $url_host = (string) wp_parse_url($url, PHP_URL_HOST);
    $risorse_card[$url] = [
        'type'   => 'link',
        'url'    => $url,
        'name'   => $url_host !== '' ? $url_host : __('Pagina collegata', 'design_comuni_italia'),
        'target' => $ck_target,
    ];
}

$risorse_card = array_values($risorse_card);
$risorse_card_visibili = array_slice($risorse_card, 0, 10);
$risorse_card_rimanenti = max(0, count($risorse_card) - count($risorse_card_visibili));

if($ck_link && !empty($url)){
     $link = esc_url($url);
}else if($ck_link && !empty($documento)){
    $link = esc_url($documento);
}else{
    $link = get_permalink($elemento->ID);
}

if ($elemento->post_status === "publish") :
    $title=$elemento->post_title;
?>
<div class="cmp-card-latest-messages card-wrapper<?php echo $in_evidenza ? ' dci-at-card--featured' : ''; ?>" data-bs-toggle="modal" data-bs-target="#">
    <div
        class="card shadow-sm px-4 pt-4 pb-4 rounded border border-light"
        <?php if ($in_evidenza) { ?>
            style="background:#f7f9fb;border:1px solid #c7d4e2!important;border-left:4px solid #5c7f99!important;"
        <?php } ?>
    >
        <div class="<?php echo $immagine_valida ? 'row g-4 align-items-start' : ''; ?>">
        <?php if ($immagine_valida) { ?>
            <a
                href="<?php echo esc_url($link); ?>"
                class="dci-at-card-image col-12 col-md-4 col-lg-3"
                style="display:block;overflow:hidden;border-radius:.25rem;"
                <?php echo $ck_target ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>
                aria-label="<?php echo esc_attr(sprintf(__('Apri %s', 'design_comuni_italia'), $title)); ?>"
            >
                <img
                    src="<?php echo esc_url($immagine_url); ?>"
                    alt="<?php echo esc_attr($immagine_alt); ?>"
                    loading="lazy"
                    decoding="async"
                    style="display:block;width:100%;height:12rem;object-fit:cover;border-radius:.25rem;"
                >
            </a>
        <?php } ?>
        <div class="dci-at-card-content<?php echo $immagine_valida ? ' col-12 col-md-8 col-lg-9' : ''; ?>" style="min-width:0;">
        <?php if ($in_evidenza) { ?>
            <div class="dci-at-featured-label" style="display:flex;justify-content:flex-start;margin-bottom:.6rem;">
                <span
                    class="dci-at-featured-badge"
                    style="display:inline-flex;align-items:center;padding:.2rem .5rem;color:#334e68;background:#e9f0f5;border:1px solid #c7d4e2;border-radius:.25rem;font-size:.78rem;line-height:1.25;font-weight:600;"
                >In evidenza</span>
            </div>
        <?php } ?>
        <span class="visually-hidden">Categoria:</span>
        <div class="card-header border-0 p-0">
            <?php if ($show_search_categories) {
                $search_section_term = $args['section_term'] ?? null;
                $categorie = $search_section_term instanceof WP_Term
                    ? [$search_section_term]
                    : get_the_terms($elemento->ID, 'tipi_cat_amm_trasp');
                if ($categorie && !is_wp_error($categorie)) {
                    $categorie_visibili = [];

                    foreach ($categorie as $cat) {
                        $categoria_corrente = $cat;

                        while ($categoria_corrente instanceof WP_Term) {
                            $visibilita = (string) get_term_meta(
                                $categoria_corrente->term_id,
                                'visualizza_elemento',
                                true
                            );

                            /*
                             * I termini creati prima dell'introduzione del campo non
                             * hanno il meta: sono pubblici. Solo il valore esplicito
                             * "0" identifica una voce nascosta.
                             */
                            if ($visibilita !== '0') {
                                $categorie_visibili[$categoria_corrente->term_id] = $categoria_corrente;
                                break;
                            }

                            if ((int) $categoria_corrente->parent <= 0) {
                                break;
                            }

                            $categoria_corrente = get_term(
                                (int) $categoria_corrente->parent,
                                'tipi_cat_amm_trasp'
                            );

                            if (is_wp_error($categoria_corrente)) {
                                break;
                            }
                        }
                    }

                    $categorie = array_values($categorie_visibili);
                }

                if (!empty($categorie)) { ?>
                    <div
                        class="dci-at-result-categories"
                        style="display:flex;align-items:center;gap:.35rem;margin-bottom:.55rem;color:#455a64;font-size:.82rem;line-height:1.35;"
                    >
                        <svg
                            class="icon"
                            style="flex:0 0 auto;width:.95rem;height:.95rem;fill:currentColor;"
                            aria-hidden="true"
                        >
                            <use href="#it-folder"></use>
                        </svg>
                        <span
                            class="dci-at-result-categories__label"
                            style="flex:0 0 auto;font-size:.82rem;line-height:1.35;font-weight:600;white-space:nowrap;"
                        >Pubblicato in:</span>
                        <ul
                            class="dci-at-result-categories__list"
                            style="display:flex;align-items:center;flex-wrap:wrap;gap:.2rem .55rem;min-width:0;margin:0;padding:0;list-style:none;"
                        >
                            <?php foreach ($categorie as $cat) {
                                $cat_link = get_term_link($cat);
                                $cat_url = trim((string) get_term_meta($cat->term_id, 'term_url', true));
                                $cat_new_window = !empty(get_term_meta($cat->term_id, 'open_new_window', true));
                                $cat_is_external = $cat_url !== '';

                                if ($cat_is_external) {
                                    $cat_link = $cat_url;
                                }

                                if (is_wp_error($cat_link)) {
                                    continue;
                                }

                                $cat_name = function_exists('dci_format_trasparenza_section_title')
                                    ? dci_format_trasparenza_section_title($cat->name)
                                    : $cat->name;
                                $cat_name_short = mb_strlen($cat_name, 'UTF-8') > 60
                                    ? rtrim(mb_substr($cat_name, 0, 57, 'UTF-8')) . '...'
                                    : $cat_name;
                                ?>
                                <li style="display:flex;align-items:center;min-width:0;margin:0;">
                                    <a
                                        class="dci-at-result-categories__link"
                                        href="<?php echo esc_url($cat_link); ?>"
                                        style="display:inline-block;max-width:100%;overflow:hidden;color:#17324d;font-size:.82rem;line-height:1.35;font-weight:600;text-decoration:underline;text-decoration-thickness:1px;text-underline-offset:2px;text-overflow:ellipsis;white-space:nowrap;"
                                        title="<?php echo esc_attr($cat_name); ?>"
                                        aria-label="<?php echo esc_attr(sprintf('Sezione: %s', $cat_name)); ?>"
                                        <?php if ($cat_is_external && $cat_new_window) { ?>
                                            target="_blank" rel="noopener noreferrer"
                                        <?php } ?>
                                    >
                                        <?php echo esc_html($cat_name_short); ?>
                                        <?php if ($cat_is_external) { ?>
                                            <svg class="icon icon-xs dci-at-result-categories__external" aria-hidden="true">
                                                <use href="#it-external-link"></use>
                                            </svg>
                                        <?php } ?>
                                    </a>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                <?php }
            } elseif ($ck_sowh_section === 'true') {?>
            <?php
                    $categorie = get_the_terms($elemento->ID, 'tipi_cat_amm_trasp');
                    if ($categorie && !is_wp_error($categorie)) {
                        foreach ($categorie as $cat) {
                            echo '<span class="badge bg-secondary me-2">' . esc_html($cat->name) . ' -  </span> -';
                        }
                    }
                }?>

        </div>

        <div class="card-body p-0 my-2">
            <span
                class="data"
                style="display:inline-flex;align-items:center;gap:.35rem;margin-bottom:.6rem;color:#455a64;font-size:1rem;line-height:1.4;font-weight:600;"
            >
                <svg
                    class="icon"
                    style="flex:0 0 auto;width:1rem;height:1rem;fill:currentColor;"
                    aria-hidden="true"
                >
                    <use href="#it-calendar"></use>
                </svg>
                Pubblicato il
                <time datetime="<?php echo esc_attr($data_iso); ?>">
                    <?php echo esc_html($data); ?>
                </time>
            </span>

            <h3 class="green-title-big t-primary mb-8">
                <a class="text-decoration-none" href="<?php echo esc_url($link); ?>"
                    <?php echo $ck_target ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>
                    data-element="service-link">

                    <?php
                        if (preg_match('/[A-Z]{5,}/', $title)) {
                         //   echo esc_html($url); // stampa solo il testo dell'URL
                            $titolo_documento = ucfirst(strtolower($title));
                        } else {
                            $titolo_documento = $title;
                        }
                    ?>

                    <?php echo esc_html($titolo_documento); ?>
                </a>
            </h3>

            <?php if (!empty($descrizione_breve)) : ?>
            <div class="text-paragraph dci-at-card-description" style="overflow-wrap:anywhere;">
                <?php echo wp_kses_post(wpautop((string) $descrizione_breve)); ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($risorse_card_visibili)) { ?>
            <div
                    class="dci-at-card-resources"
                    style="margin-top:.85rem;padding-top:.75rem;border-top:1px solid #dfe7f0;"
                >
                    <span
                        style="display:block;margin-bottom:.4rem;color:#455a64;font-size:.82rem;font-weight:600;"
                    >Allegati e link</span>
                    <ul style="display:grid;gap:.35rem;margin:0;padding:0;list-style:none;">
                        <?php foreach ($risorse_card_visibili as $risorsa_card) {
                            $risorsa_is_file = $risorsa_card['type'] === 'file';
                            $risorsa_path = (string) wp_parse_url($risorsa_card['url'], PHP_URL_PATH);
                            $risorsa_estensione = strtolower((string) pathinfo($risorsa_path, PATHINFO_EXTENSION));
                            $risorsa_icona = $risorsa_is_file ? '#it-file' : '#it-link';
                            $risorsa_colore = $risorsa_is_file ? '#455a64' : '#175cd3';
                            $risorsa_formato = $risorsa_is_file && $risorsa_estensione !== ''
                                ? strtoupper($risorsa_estensione)
                                : __('LINK', 'design_comuni_italia');

                            if ($risorsa_estensione === 'pdf') {
                                $risorsa_colore = '#b42318';
                            } elseif (in_array($risorsa_estensione, ['zip', 'rar', '7z', 'tar', 'gz'], true)) {
                                $risorsa_icona = '#it-folder';
                                $risorsa_colore = '#9a6700';
                            } elseif (in_array($risorsa_estensione, ['doc', 'docx', 'odt', 'rtf'], true)) {
                                $risorsa_colore = '#175cd3';
                            } elseif (in_array($risorsa_estensione, ['xls', 'xlsx', 'ods', 'csv'], true)) {
                                $risorsa_colore = '#18794e';
                            } elseif (in_array($risorsa_estensione, ['ppt', 'pptx', 'odp'], true)) {
                                $risorsa_colore = '#c2410c';
                            } elseif (in_array($risorsa_estensione, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'avif'], true)) {
                                $risorsa_colore = '#7e22ce';
                            }

                            $risorsa_nome_completo = preg_replace(
                                '/\s+/u',
                                ' ',
                                trim((string) $risorsa_card['name'])
                            );

                            if (preg_match('/\p{Lu}{7,}/u', $risorsa_nome_completo)) {
                                $risorsa_nome_minuscolo = mb_strtolower($risorsa_nome_completo, 'UTF-8');
                                $risorsa_nome_completo = mb_strtoupper(
                                    mb_substr($risorsa_nome_minuscolo, 0, 1, 'UTF-8'),
                                    'UTF-8'
                                ) . mb_substr($risorsa_nome_minuscolo, 1, null, 'UTF-8');
                            }

                            $risorsa_nome_visualizzato = mb_strlen($risorsa_nome_completo, 'UTF-8') > 60
                                ? rtrim(mb_substr($risorsa_nome_completo, 0, 57, 'UTF-8')) . '...'
                                : $risorsa_nome_completo;
                            $risorsa_label = sprintf(
                                $risorsa_is_file
                                    ? __('Apri il documento: %s', 'design_comuni_italia')
                                    : __('Apri il link: %s', 'design_comuni_italia'),
                                $risorsa_nome_completo
                            );
                            ?>
                            <li style="min-width:0;">
                                <a
                                    href="<?php echo esc_url($risorsa_card['url']); ?>"
                                    style="display:inline-flex;align-items:center;gap:.4rem;max-width:100%;color:#17324d;font-size:.86rem;font-weight:600;text-decoration:none;"
                                    aria-label="<?php echo esc_attr($risorsa_label); ?>"
                                    title="<?php echo esc_attr($risorsa_nome_completo); ?>"
                                    target="_blank" rel="noopener noreferrer"
                                >
                                    <svg
                                        class="icon icon-sm"
                                        style="flex:0 0 auto;width:1.15rem;height:1.15rem;fill:<?php echo esc_attr($risorsa_colore); ?>;"
                                        aria-hidden="true"
                                    >
                                        <use href="<?php echo esc_attr($risorsa_icona); ?>"></use>
                                    </svg>
                                    <span
                                        aria-hidden="true"
                                        style="display:inline-flex;flex:0 0 auto;align-items:center;justify-content:center;min-width:2.5rem;height:1.35rem;padding:0 .3rem;color:#fff;background:<?php echo esc_attr($risorsa_colore); ?>;border-radius:.2rem;font-size:.66rem;line-height:1;font-weight:700;"
                                    ><?php echo esc_html($risorsa_formato); ?></span>
                                    <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                        <?php echo esc_html($risorsa_nome_visualizzato); ?>
                                    </span>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>

                    <?php if ($risorse_card_rimanenti > 0) { ?>
                        <a
                            href="<?php echo esc_url(get_permalink($elemento->ID)); ?>"
                            style="display:inline-block;margin-top:.45rem;color:#455a64;font-size:.8rem;font-weight:600;"
                        >
                            <?php
                            printf(
                                esc_html(_n(
                                    '+ %s altra risorsa',
                                    '+ %s altre risorse',
                                    $risorse_card_rimanenti,
                                    'design_comuni_italia'
                                )),
                                esc_html(number_format_i18n($risorse_card_rimanenti))
                            );
                            ?>
                        </a>
                    <?php } ?>
                </div>
            <?php } ?>

            <div class="dci-at-card-actions" style="margin-top:1.75rem;">
                <a
                    href="<?php echo esc_url(get_permalink($elemento->ID)); ?>"
                    class="dci-at-card-detail dci-at-card-detail-action btn btn-primary btn-sm text-decoration-none"
                    aria-label="<?php echo esc_attr(sprintf(__('Apri il dettaglio di %s', 'design_comuni_italia'), $title)); ?>"
                >
                    <span><?php esc_html_e('Apri dettaglio', 'design_comuni_italia'); ?></span>
                    <svg
                        class="icon icon-sm"
                        style="width:1rem;height:1rem;fill:currentColor;"
                        aria-hidden="true"
                    >
                        <use href="#it-arrow-right"></use>
                    </svg>
                </a>
                <?php
                if (function_exists('dci_render_trasparenza_edit_link')) {
                    dci_render_trasparenza_edit_link($elemento->ID);
                }
                ?>
            </div>
        </div>
        </div>
        </div>
    </div>
</div>
<?php endif; ?>
