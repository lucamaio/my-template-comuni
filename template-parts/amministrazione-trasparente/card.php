<?php
global $elemento;

$prefix = '_dci_elemento_trasparenza_';

// Metadati
$descrizione_breve = dci_get_meta('descrizione_breve', $prefix, $elemento->ID);
$ck_target         = dci_get_meta('open_in_new_tab', $prefix, $elemento->ID) === 'on';
$ck_link           = dci_get_meta('open_direct', $prefix, $elemento->ID) === 'on';
$url               = dci_get_meta('url', $prefix, $elemento->ID);

$documenti         = dci_get_meta('file', $prefix, $elemento->ID);
$documento = is_array($documenti) && !empty($documenti) ? get_permalink($elemento->ID) : $documenti;
$data= get_the_date('j F Y', $elemento->ID);

$ck_sowh_section = dci_get_option("ck_show_section", "Trasparenza");
$show_search_categories = !empty($args['show_search_categories']);

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
<div class="cmp-card-latest-messages card-wrapper" data-bs-toggle="modal" data-bs-target="#">
    <div class="card shadow-sm px-4 pt-4 pb-4 rounded border border-light">
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
                    <div class="dci-at-result-categories">
                        <span class="dci-at-result-categories__label">Pubblicato in:</span>
                        <ul class="dci-at-result-categories__list">
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
                                $cat_name_short = mb_strlen($cat_name, 'UTF-8') > 70
                                    ? rtrim(mb_substr($cat_name, 0, 70, 'UTF-8')) . '...'
                                    : $cat_name;
                                ?>
                                <li>
                                    <a
                                        class="dci-at-result-categories__link"
                                        href="<?php echo esc_url($cat_link); ?>"
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

            <span class="data">
                <?php echo($data);?>
                <?php //echo $arrayDataPubblicazione[0] . ' ' . $monthNamePubblicazione . ' ' . $yearFull; ?>
            </span>
            <!-- 
            <?php /* if($arrayDataPubblicazione[0]!=$arrayDataScadenza[0]) { ?>
                - <span class="data"><?php echo $arrayDataScadenza[0].' '.strtoupper($monthNameScadenza).' '.$arrayDataScadenza[2] ?></span>
            <?php } */ ?>
            -->
        </div>

        <div class="card-body p-0 my-2">
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
            <p class="text-paragraph">
                <?php echo esc_html($descrizione_breve); ?>
            </p>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>
