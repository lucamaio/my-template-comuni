<?php 
/**
 * Archivio Tassonomia trasparenza
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#custom-taxonomies
 * @package Design_Comuni_Italia
 */

global $title, $description, $data_element, $elemento, $sito_tematico_id, $siti_tematici, $tipo_personalizzato, $dci_amm_sidebar_column_classes;

$obj = get_queried_object();

if ($obj instanceof WP_Term && isset($obj->taxonomy) && $obj->taxonomy === 'tipi_cat_amm_trasp') {
    $term_url = trim((string) get_term_meta($obj->term_id, 'term_url', true));
    $open_new_window = get_term_meta($obj->term_id, 'open_new_window', true);

    if ($term_url !== '') {
        $redirect_url = esc_url_raw($term_url);

        if (!empty($redirect_url)) {
            if (!empty($open_new_window)) {
                $fallback_url = home_url('/amministrazione-trasparente');
                ?>
                <!doctype html>
                <html <?php language_attributes(); ?>>
                <head>
                    <meta charset="<?php bloginfo('charset'); ?>">
                    <meta name="viewport" content="width=device-width, initial-scale=1">
                    <title><?php echo esc_html($obj->name); ?></title>
                    <script>
                        window.addEventListener('load', function () {
                            window.open(<?php echo wp_json_encode($redirect_url); ?>, '_blank', 'noopener');
                            window.location.replace(<?php echo wp_json_encode($fallback_url); ?>);
                        });
                    </script>
                    <noscript>
                        <meta http-equiv="refresh" content="0;url=<?php echo esc_url($redirect_url); ?>">
                    </noscript>
                </head>
                <body></body>
                </html>
                <?php
                exit;
            }

            wp_redirect($redirect_url, 302);
            exit;
        }
    }
}

get_header();

$dci_amm_sidebar_column_classes = 'pt-30 pt-lg-50 pb-lg-50';

if (!function_exists('dci_render_trasparenza_light_bg_style')) {
    function dci_render_trasparenza_light_bg_style()
    {
        ?>
        <style>
            :root {
                /* --dci-at-primary: var(--bs-primary, rgb(6, 62, 138));
                --dci-at-primary-dark: var(--bs-primary, rgb(6, 62, 138));
                --dci-at-primary-soft: #f3f7fb;
                --dci-at-border: #dfe7f0;
                --dci-at-text: #455a64; */
            }

            .dci-at-tools {
                background: #ffffff;
                border: 1px solid var(--dci-at-border);
                border-radius: 8px;
                box-shadow: 0 10px 30px rgba(23, 50, 77, 0.08);
                padding: 1.25rem;
                margin-bottom: 1.75rem;
            }

            .dci-at-tools__title {
                margin-bottom: 0.35rem;
                font-size: 1.35rem;
            }

            .dci-at-tools__intro {
                margin-bottom: 1rem;
            }

            .dci-at-tools .cmp-input-search {
                margin-bottom: 0;
            }

            .dci-at-search-row {
                display: flex;
                align-items: stretch;
            }

            .dci-at-search-row .input-group {
                align-items: stretch;
            }

            .dci-at-tools .form-control,
            .dci-at-tools .autocomplete {
                min-height: 52px;
                border: 2px solid #b8c9da;
                background: #fff;
                border-radius: 6px;
            }

            .dci-at-search-row .autocomplete {
                padding-left: 2.65rem;
                border-top-right-radius: 0;
                border-bottom-right-radius: 0;
            }

            .dci-at-search-row .input-group-append {
                display: flex;
            }

            .dci-at-tools .form-control:focus,
            .dci-at-tools .autocomplete:focus {
                border-color: var(--dci-at-primary);
                box-shadow: 0 0 0 0.2rem rgba(6, 62, 138, 0.16);
            }

            .dci-at-tools .btn-primary {
                min-width: 130px;
                font-weight: 700;
                border-radius: 0 6px 6px 0;
            }

            .dci-at-tools .btn-primary:hover,
            .dci-at-tools .btn-primary:focus {
                filter: brightness(0.96);
            }

            .dci-at-tools .autocomplete-icon .icon,
            .dci-at-empty__icon .icon {
                fill: var(--dci-at-primary);
            }

            .dci-at-tools__count {
                margin-top: 0.9rem;
                margin-bottom: 0;
                padding: 0.75rem 0.9rem;
                background: var(--dci-at-primary-soft);
                border-left: 4px solid var(--dci-at-primary);
                border-radius: 6px;
            }

            .dci-at-order {
                margin-top: 1.1rem;
                padding-top: 1rem;
                border-top: 1px solid #e6edf5;
            }

            .dci-at-order__label {
                display: block;
                position: static;
                margin-bottom: 0.45rem;
                padding: 0;
                font-size: 0.95rem;
                font-weight: 700;
                background: transparent;
                transform: none;
                line-height: 1.4;
            }

            .dci-at-order .form-control {
                padding: 0.75rem 1rem;
                appearance: auto;
            }

            .dci-at-tools a,
            .dci-at-empty a,
            .dci-at-tools .text-decoration-none,
            .dci-at-empty .text-decoration-none {
                color: var(--dci-at-primary) !important;
                text-decoration-color: var(--dci-at-primary) !important;
            }

            .dci-at-tools a:hover,
            .dci-at-empty a:hover,
            .dci-at-tools .text-decoration-none:hover,
            .dci-at-empty .text-decoration-none:hover {
                color: var(--dci-at-primary) !important;
                text-decoration-color: var(--dci-at-primary) !important;
            }

            .dci-at-empty {
                display: flex;
                align-items: center;
                gap: 1rem;
                padding: 1.2rem 1.35rem;
                border: 1px solid var(--dci-at-border);
                border-left: 5px solid var(--dci-at-primary);
                border-radius: 6px;
                background: #ffffff;
            }

            .dci-at-empty__icon {
                flex: 0 0 auto;
                width: 2.25rem;
                height: 2.25rem;
                border: 1px solid #8aa0b8;
                border-radius: 999px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                color: var(--dci-at-primary);
            }

            .dci-at-empty__icon .icon {
                fill: currentColor;
            }

            .dci-at-empty__content {
                min-width: 0;
            }

            .dci-at-empty__title {
                margin-bottom: 0.2rem;
                font-size: 1rem;
                font-weight: 700;
            }

            .dci-at-empty__text {
                margin-bottom: 0;
            }

            .dci-at-layout {
                padding-bottom: 2.5rem;
            }

            @media (max-width: 767.98px) {
                .dci-at-tools {
                    padding: 1rem;
                }

                .dci-at-tools .btn-primary {
                    min-width: 100px;
                }

                .dci-at-empty {
                    align-items: flex-start;
                }

                .dci-at-layout {
                    padding-bottom: 2rem;
                }
            }
        </style>
        <?php
    }
}

dci_render_trasparenza_light_bg_style();

// Recupera il numero di pagina corrente.
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

$max_posts = isset($_GET['max_posts']) ? intval($_GET['max_posts']) : 10;
$load_posts = -1;
$query = isset($_GET['search']) ? dci_removeslashes($_GET['search']) : null;

$prefix = '_dci_elemento_trasparenza_';

// Gestione dell'ordinamento
$order = isset($_GET['order_type']) ? $_GET['order_type'] : 'data_desc'; // Default è data_desc

$args = array(
    's' => $query,
    'posts_per_page' => $max_posts,
    'post_type' => 'elemento_trasparenza',
    'tipi_cat_amm_trasp' => $obj->slug,
    'paged' => $paged,
);

// Gestione dell'ordinamento
if ($order === 'alfabetico_asc' || $order === 'alfabetico_desc') {
    $args['orderby'] = 'title';
    $args['order'] = ($order === 'alfabetico_desc') ? 'DESC' : 'ASC';
} else {
    // Ordinamento per data di pubblicazione del post (post_date)
    $args['orderby'] = 'date';
    $args['order'] = ($order === 'data_desc') ? 'DESC' : 'ASC';
}



$the_query = new WP_Query($args);
// $pagination_markup = trim((string) dci_bootstrap_pagination());



$siti_tematici = !empty(dci_get_option("siti_tematici", "trasparenza")) ? dci_get_option("siti_tematici", "trasparenza") : [];
?>

<main>
    <?php
    $title = $obj->name;
    $description = $obj->description;
    $data_element = 'data-element="page-name"';
    get_template_part("template-parts/hero/hero");
    ?>

    <div class="bg-grey-card dci-at-layout">
        
      <?php 
          if ($obj->name == "Contratti Pubblici" && dci_get_option("ck_bandidigaratemplatepersonalizzato", "Trasparenza") !== 'false' && dci_get_option("ck_bandidigaratemplatepersonalizzato", "Trasparenza") !== '') 
               { 
        ?>
                <div class="container my-5">
                    <div class="row g-4">
                        <h2 class="visually-hidden">Esplora tutti i bandi di gara</h2>
                        <div class="col-12 col-lg-8 pt-20 pt-lg-20 pb-lg-20">
                            <?php get_template_part("template-parts/bandi-di-gara/tutti-bandi"); ?>
                        </div>
                        <?php get_template_part("template-parts/amministrazione-trasparente/side-bar"); ?>
                    </div>
                </div>
            </div>
    
    <?php } else if ($obj->name == "Atti di concessione" && dci_get_option("ck_attidiconcessione", "Trasparenza") !== 'false' && dci_get_option("ck_attidiconcessione", "Trasparenza") !== '') { ?>
            <div class="container my-5">
                <div class="row g-4">
                    <h2 class="visually-hidden">Esplora tutti gli Atti di Concessione</h2>
                    <div class="col-12 col-lg-8 pt-20 pt-lg-20 pb-lg-20">
                        <?php get_template_part("template-parts/amministrazione-trasparente/atto-concessione/tutti-gli-atti"); ?>
                    </div>
                    <?php get_template_part("template-parts/amministrazione-trasparente/side-bar"); ?> 
                </div>
            </div>
        </div>
     <?php } else if ($obj->name == "Incarichi conferiti e autorizzati ai dipendenti"  && dci_get_option("ck_incarichieautorizzazioniaidipendenti", "Trasparenza") !== 'false' && dci_get_option("ck_incarichieautorizzazioniaidipendenti", "Trasparenza") !== '') { ?>
            <div class="container my-5">
                <div class="row g-4">
                    <h2 class="visually-hidden">Esplora tutti gli Incarichi conferiti e autorizzati ai dipendenti</h2>
                    <div class="col-12 col-lg-8 pt-20 pt-lg-20 pb-lg-20">
                        <?php get_template_part("template-parts/amministrazione-trasparente/incarichi-autorizzazioni/tutti-gli-incarichi"); ?>
                    </div>
                    <?php get_template_part("template-parts/amministrazione-trasparente/side-bar"); ?> 
                </div>
            </div>
        </div>		    
    <?php } else if ($obj->name == "Titolari di incarichi di collaborazione o consulenza" && dci_get_option("ck_titolariIncarichiCollaborazioneConsulenzaTemplatePersonalizzato", "Trasparenza") !== 'false' && dci_get_option("ck_titolariIncarichiCollaborazioneConsulenzaTemplatePersonalizzato", "Trasparenza") !== '') 
               { 
        ?>
            <div class="container my-5">
                <div class="row g-4">
                    <h2 class="visually-hidden">Esplora tutti i Titolari di incarichi di collaborazione o consulenza</h2>
                    <div class="col-12 col-lg-8 pt-20 pt-lg-20 pb-lg-20">
                        <?php get_template_part("template-parts/amministrazione-trasparente/titolare_incarico/tutti-titolari"); ?>
                    </div>
                    <?php get_template_part("template-parts/amministrazione-trasparente/side-bar"); ?>
                </div>
            </div>
        </div>
    
    <?php }  else if(($obj->name === "Pubblicazione" || $obj->name === "Affidamento" || $obj->name === "Esecutiva" || $obj->name === "Sponsorizzazioni") && dci_get_option("ck_bandidigaratemplatepersonalizzato", "Trasparenza") !== 'false' && dci_get_option("ck_bandidigaratemplatepersonalizzato", "Trasparenza") !== '' ){?>
        <div class="container my-5">
                    <div class="row g-4">
                        <h2 class="visually-hidden">Esplora tutti i bandi di gara</h2>
                        <div class="col-12 col-lg-8 pt-20 pt-lg-20 pb-lg-20">
                            <?php 
                            $tipo_personalizzato = get_queried_object()->name;                       
                            get_template_part("template-parts/bandi-di-gara/tipi-personalizzati"); ?>
                        </div>
                        <?php get_template_part("template-parts/amministrazione-trasparente/side-bar"); ?>
                    </div>
                </div>
            </div>
   <?php } else if($obj->name === "Telefono e posta elettronica" && dci_get_option("ck_portalesoloperusoesterno") !== 'True' ){?>
        <div class="container my-5">
            <div class="row g-4">
                <h2 class="visually-hidden">Esplora i contatti del ente</h2>
                <div class="col-12 col-lg-8 pt-20 pt-lg-20 pb-lg-20">
                    <?php get_template_part("template-parts/amministrazione-trasparente/contatti/tutti-contatti"); ?>
                </div>
                <?php get_template_part("template-parts/amministrazione-trasparente/side-bar"); ?>
            </div>
        </div>
   <?php } else if($obj->name === "Articolazione uffici" && dci_get_option("ck_portalesoloperusoesterno") !== 'True' ){?>
        <div class="container py-5">
            <h2 class="visually-hidden">Esplora l'articolazione degli uffici comunali</h2>
            <?php get_template_part("template-parts/amministrazione-trasparente/articolazione-uffici/tutti-uffici"); ?>
        </div>
            </div>
   <?php } else if($obj->name === "Titolari di incarichi politici di amministrazione di direzione o di governo" ){?>
        <div class="container py-5">
            <h2 class="visually-hidden">Esplora i Titolari di incarichi politici di amministrazione di direzione o di governo </h2>
            <?php get_template_part("template-parts/amministrazione-trasparente/titolari-incarichi-poilitici/tutti-titolari"); ?>
        </div>
            </div>
   <?php }else { ?>
        
        <form role="search" id="search-form" method="get" class="search-form">
            <button type="submit" class="d-none"></button>
            <div class="container">
                <div class="row">
                    <h2 class="visually-hidden">Esplora tutti i documenti della trasparenza</h2>

                    <!-- Colonna sinistra: risultati -->
                    <div class="col-12 col-lg-8 pt-30 pt-lg-50 pb-lg-50">
                        <div class="dci-at-tools" aria-label="Strumenti di ricerca e ordinamento">
                            <h3 class="dci-at-tools__title text-decoration-none">Cerca e ordina i contenuti</h3>
                            <p class="dci-at-tools__intro text-decoration-none">Usa la ricerca per trovare rapidamente un documento e scegli l'ordinamento che preferisci.</p>

                            <div class="cmp-input-search">
                                <div class="form-group autocomplete-wrapper mb-2 mb-lg-3">
                                    <div class="input-group dci-at-search-row">
                                        <label for="autocomplete-two" class="visually-hidden">Cerca una parola chiave</label>
                                        <input type="search" class="autocomplete form-control"
                                            placeholder="Cerca una parola chiave" id="autocomplete-two" name="search"
                                            value="<?php echo $query; ?>" data-bs-autocomplete="[]">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="submit" id="button-3">Cerca</button>
                                        </div>
                                        <span class="autocomplete-icon" aria-hidden="true">
                                            <svg class="icon icon-sm icon-primary" role="img" aria-labelledby="autocomplete-label">
                                                <use href="#it-search"></use>
                                            </svg>
                                        </span>
                                    </div>
                                </div>
                                <p id="autocomplete-label" class="dci-at-tools__count text-decoration-none">
                                    <strong class="text-decoration-none"><?php echo $the_query->found_posts; ?></strong> elementi trovati in ordine
                                    <?php echo ($order == 'alfabetico_asc' || $order == 'alfabetico_desc') ? "alfabetico" : "di pubblicazione"; ?>
                                    <?php echo ($order == 'desc' || $order == 'alfabetico_desc') ? "(Discendente)" : "(Ascendente)"; ?>
                                </p>
                            </div>

                            <div class="form-group mb-0 dci-at-order">
                                <label for="order-select" class="dci-at-order__label text-decoration-none">Ordina per</label>
                                <select id="order-select" name="order_type" class="form-control">
                                    <option value="data_desc" <?php echo ($order == 'data_desc') ? 'selected' : ''; ?>>Data (Descendente)</option>
                                    <option value="data_asc" <?php echo ($order == 'data_asc') ? 'selected' : ''; ?>>Data (Ascendente)</option>
                                    <option value="alfabetico_asc" <?php echo ($order == 'alfabetico_asc') ? 'selected' : ''; ?>>Alfabetico (Ascendente)</option>
                                    <option value="alfabetico_desc" <?php echo ($order == 'alfabetico_desc') ? 'selected' : ''; ?>>Alfabetico (Discendente)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Risultati della ricerca -->
                        <?php if ($the_query->found_posts != 0) { ?>
                            <?php $categoria = $the_query->posts; ?>
                            <div class="row g-4" id="load-more">
                                <?php foreach ($categoria as $elemento) {
                                    $load_card_type = "elemento_trasparenza";
                                    get_template_part("template-parts/amministrazione-trasparente/card");
                                } ?>
                            </div>
                        <?php } else { ?>
                            <div class="dci-at-empty text-decoration-none" role="status" aria-live="polite">
                                <span class="dci-at-empty__icon" aria-hidden="true">
                                    <svg class="icon icon-sm">
                                        <use href="#it-info-circle"></use>
                                    </svg>
                                </span>
                                <div class="dci-at-empty__content">
                                    <p class="dci-at-empty__title text-decoration-none">Nessun contenuto disponibile</p>
                                    <p class="dci-at-empty__text text-decoration-none">Non ci sono elementi o post da mostrare con i filtri attuali. Prova a cambiare ricerca o ordinamento.</p>
                                </div>
                            </div>
                        <?php } ?>
                    </div>

                    <!-- Colonna destra: link utili -->
                    <?php get_template_part("template-parts/amministrazione-trasparente/side-bar"); ?>
                </div>

                
                <?php $pagination_markup = trim((string) dci_bootstrap_pagination($the_query, false)); ?>
                <?php if ($pagination_markup !== '') { ?>
                <div class="row my-4">
                    <div class="col-12 d-flex">
                        <nav class="pagination-wrapper" aria-label="Navigazione pagine">
                            <?php echo $pagination_markup; ?>
                        </nav>
                    </div>
                </div>
                <?php } ?>
            </div>
        </form>
    <?php } ?>
    </div>
</main>

<?php


//Se il portale gestisce solo la nostra Trasparenza in modo esterno, indirizza all'home del comune.
$portalesoloperusoesterno = dci_get_option("ck_portalesoloperusoesterno");

// Se è attiva la trasparenza esterna, non visualizzare questi elementi
if ($portalesoloperusoesterno !== 'true') {
            get_template_part("template-parts/common/valuta-servizio");
            get_template_part("template-parts/common/assistenza-contatti");
}


get_footer();
?>

<script>
    document.getElementById('order-select').addEventListener('change', function() {
        setTimeout(function() {
            document.getElementById('search-form').submit();
        }, 100);
    });
</script>

<?php $dci_amm_sidebar_column_classes = ''; ?>
