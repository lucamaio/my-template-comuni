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

        // Variabili non utilizzate per la galleria
        $foto_id = dci_get_meta("foto_id");
        $img = wp_get_attachment_image_src($foto_id, "item-gallery");
       
        // Galleria foto
        $gallery = dci_get_meta("gallery");

        $img1 = dci_get_meta('foto'); // Foto associata all'incarico

        $data_insediamento = dci_get_meta("data_inizio_incarico") ?? '';
        $data_conclusione_incarico = dci_get_meta("data_conclusione_incarico") ?? '';

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

        // Recupero di altri metadati
        $compensi = !empty($incarichi) ? dci_get_meta("compensi", '_dci_incarico_', $incarichi[0]) : [];

        $organizzazioni = dci_get_meta("organizzazioni") ?? [];
        $biografia = dci_get_meta("biografia") ?? '';
        $curriculum_vitae = dci_get_meta("curriculum_vitae") ?? '';
        $situazione_patrimoniale = dci_get_meta("situazione_patrimoniale") ?? '';
        $situazione_patrimoniale_id = dci_get_meta("variazione_situazione_patrimoniale") ?? [];
        $dichiarazione_redditi = dci_get_meta("dichiarazione_redditi") ?? '';
        $spese_elettorali = dci_get_meta("spese_elettorali") ?? '';
        $descrizione = dci_get_wysiwyg_field("descrizione_estesa") ?? '';
        $punti_contatto = dci_get_meta("punti_contatto") ?? [];
        $deleghe = dci_get_meta("deleghe") ?? '';
        $ulteriori_informazioni = dci_get_meta("ulteriori_informazioni") ?? '';

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
<script type="application/ld+json" data-element="metatag">
    {
        "name": "<?= $nome ?>",
        "cognome": "<?= $cognome; ?>",
        "incarico": "<?= $incarico; ?>";
    }
</script>

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
    <div class="row row-column-menu-left mt-4 mt-lg-80 pb-lg-30 pb-30">
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
                                                    border: 1px solid #ddd;
                                                    /* Bordo molto leggero */
                                                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
                                                    /* Ombra quasi impercettibile */
                                                    padding: 2px;
                                                    background-color: #fff;
                                                    /* Sfondo bianco per risalto se su sfondo grigio */
                                                    border-radius: 6px;
                                                    /* Angoli leggermente arrotondati */
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
                                                            <a class="nav-link" href="#data-inizio">
                                                                <span>Data di
                                                                    <?php if ($tipo_incarico == "politico") {
                                                                        echo 'Insediamento';
                                                                    } else {
                                                                        echo 'inizio incarico';
                                                                    } ?></span>
                                                            </a>
                                                        </li>
                                                    <?php } ?>
                                                    <?php if (isset($data_conclusione_incarico) and !empty($data_conclusione_incarico and $data_conclusione_incarico != NULL)) { ?>
                                                        <li class="nav-item">
                                                            <a class="nav-link" href="#data-fine">
                                                                <span>Data di
                                                                    <?php
                                                                    echo 'conclusione incarico';
                                                                    ?></span>
                                                            </a>
                                                        </li>
                                                    <?php } ?>
                                                    <?php if ($organizzazioni) { ?>
                                                        <li class="nav-item">
                                                            <a class="nav-link" href="#organizzazioni">
                                                                <span>Organizzazione/i</span>
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
                                                    <?php if ($deleghe) { ?>
                                                        <li class="nav-item">
                                                            <a class="nav-link" href="#deleghe">
                                                                <span>Deleghe</span>
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
                                                    <?php if ($curriculum_vitae) { ?>
                                                        <li class="nav-item">
                                                            <a class="nav-link" href="#cv">
                                                                <span>Curriculum Vitae</span>
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
                                                    <?php if ($situazione_patrimoniale) { ?>
                                                        <li class="nav-item">
                                                            <a class="nav-link" href="#situazione-patrimoniale">
                                                                <span>Situazione patrimoniale</span>
                                                            </a>
                                                        </li>
                                                    <?php } ?>
                                                    <?php if($situazione_patrimoniale_id){?>
                                                        <li class="nav-item">
                                                            <a class="nav-link" href="#variazione-situazione-patrimoniale">
                                                                <span>Variazione Situazione patrimoniale</span>
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
                                                    <?php if (isset($altre_cariche) && !empty($altre_cariche)) { ?>
                                                        <li class="nav-item">
                                                            <a class="nav-link" href="#altre-cariche">
                                                                <span>Altre cariche</span>
                                                            </a>
                                                        </li>
                                                    <?php } ?>
                                                    <!-- Nuova sezione per la galleria delle foto -->
                                                    <?php
                                                        if($gallery){?>
                                                            <li class="nav-item">
                                                                <a class="nav-link" href="#gallery">
                                                                    <span>Galleria</span>
                                                                </a>
                                                            </li>
                                                        <?php }
                                                    ?>

                                                    <!-- Sposto la sezione contatti alla fine dell'indice, prima di ulteriori informazioni -->
                                                    <?php if ($contatti) { ?>
                                                        <li class="nav-item">
                                                            <a class="nav-link" href="#contatti">
                                                                <span>Contatti</span>
                                                            </a>
                                                        </li>
                                                    <?php } ?>
                                                    <!-- Aggiungo la voce per ulteriori informazioni in fondo all'indice -->
                                                    <li class="nav-item">
                                                        <a class="nav-link" href="#ulteriori-informazioni">
                                                            <span>Ulteriori informazioni</span>
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
        </div>
        <div class="col-12 col-lg-8 offset-lg-1">
            <div class="it-page-sections-container">
                <?php if (!empty($incarichi)) { ?>
                    <section class="it-page-section mb-20">
                        <h2 class="title-xlarge mb-3" id="incarichi">Incarichi</h2>
                        <div class="richtext-wrapper lora">
                            <div class="incarichi-accordion">
                                <!-- Scripèt per gestire l'accordion degli incarichi -->
                                <script>
                                    document.addEventListener("DOMContentLoaded", function() {

                                        const headers = document.querySelectorAll(".accordion-header-incarico");

                                        headers.forEach(header => {
                                            header.addEventListener("click", function() {

                                                const content = this.nextElementSibling;

                                                // Chiude eventuale sezione aperta
                                                document.querySelectorAll(".accordion-content").forEach(item => {
                                                    if (item !== content) {
                                                        item.style.display = "none";
                                                    }
                                                });

                                                document.querySelectorAll(".accordion-header-incarico").forEach(h => {
                                                    if (h !== this) {
                                                        h.classList.remove("active");
                                                    }
                                                });

                                                // Toggle
                                                if (content.style.display === "block") {
                                                    content.style.display = "none";
                                                    this.classList.remove("active");
                                                } else {
                                                    content.style.display = "block";
                                                    this.classList.add("active");
                                                }

                                            });
                                        });

                                    });
                                </script>
                                <!-- Ciclo per mostrare gli incarichi -->
                                <?php
                                foreach ($incarichi as $incarico_id) :

                                    $incarico_title = get_the_title($incarico_id);

                                    // Recupera i termini di tipo incarico
                                    $tipo_incarico_terms = get_the_terms($incarico_id, 'tipi_incarico');
                                    $tipo_incarico = (!empty($tipo_incarico_terms) && !is_wp_error($tipo_incarico_terms))
                                        ? $tipo_incarico_terms[0]->name
                                        : 'N/A';

                                    // Date
                                    $data_inizio_incarico = dci_get_meta("data_inizio_incarico", '_dci_incarico_', $incarico_id) ?? '';
                                    $data_insediamento = dci_get_meta("data_insediamento", '_dci_incarico_', $incarico_id) ?? ''; // Solo per i politici
                                    $data_fine_incarico = dci_get_meta("data_conclusione_incarico", '_dci_incarico_', $incarico_id) ?? '';

                                    // Unità Organizzativa
                                    $uo_id = dci_get_meta("unita_organizzativa", '_dci_incarico_', $incarico_id);
                                    $titolo_uo = $uo_id ? get_the_title($uo_id) : '';

                                    // Respoinsabile
                                    $resp_id = dci_get_meta("responsabile_struttura", '_dci_incarico_', $incarico_id);
                                    $titolo_resp = $resp_id ? get_the_title($resp_id) : '';

                                    // Compenso e Importi Viaggi spese
                                    $compenso_incarico = dci_get_meta("compensi", '_dci_incarico_', $incarico_id);
                                    $importi_viaggi_servizi = dci_get_meta("importi_viaggi_servizi", '_dci_incarico_', $incarico_id);

                                    // Atto di nomina
                                    $atto_nomina = dci_get_meta("atto_nomina", '_dci_incarico_', $incarico_id);
                                    $titolo_atto_nomina = $atto_nomina ? get_the_title(attachment_url_to_postid($atto_nomina)) : '';

                                    // Formatto le date in formato italiano (gg/mm/aaaa) se sono presenti

                                    if (!empty($data_inizio_incarico)) {
                                        $data_inizio_incarico = date_i18n('d F Y', date($data_inizio_incarico));
                                    }

                                    if (!empty($data_insediamento)) {
                                        $data_insediamento = date_i18n('d F Y', date($data_insediamento));
                                    }

                                    if (!empty($data_fine_incarico)) {
                                        $data_fine_incarico = date_i18n('d F Y', date($data_fine_incarico));
                                    }

                                    // Campo Ulteriuori Informazioni
                                    $more_info_incarico = dci_get_meta("ulteriori_informazioni","_dci_incarico_", $incarico_id);
                                ?>

                                    <div class="accordion-item-incarico">
                                        <button class="accordion-header-incarico" data-target="incarico-<?php echo esc_attr($incarico_id); ?>">
                                            <span><?php echo esc_html($incarico_title); ?></span>
                                            <span class="accordion-icon">+</span>
                                        </button>

                                        <div id="incarico-<?php echo esc_attr($incarico_id); ?>" class="accordion-content">
                                            <p class="richtext-wrapper lora mt-0"><strong>Tipo di incarico:</strong> <?php echo esc_html($tipo_incarico); ?></p>

                                            <?php if (!empty($data_inizio_incarico)) { ?>
                                                <p class="richtext-wrapper lora"><strong>Data inizio incarico:</strong> <?php echo esc_html($data_inizio_incarico); ?></p>
                                            <?php } ?>

                                            <?php if (!empty($data_insediamento)) { ?>
                                                <p class="richtext-wrapper lora"><strong>Data insediamento:</strong> <?php echo esc_html($data_insediamento); ?></p>
                                            <?php } ?>

                                            <?php if (!empty($data_fine_incarico)) : ?>
                                                <p class="richtext-wrapper lora"><strong>Data conclusione incarico:</strong> <?php echo esc_html($data_fine_incarico); ?></p>
                                            <?php endif; ?>

                                            <?php if (!empty($titolo_uo)) : ?>
                                                <p class="richtext-wrapper lora"><strong>Unità Organizzativa:</strong> <a href="<?php echo get_permalink($uo_id); ?>" class="text-decoration-none" style="margin-bottom: 1px !important;"><?php echo esc_html($titolo_uo); ?></a></p>
                                            <?php endif; ?>

                                            <?php if (!empty($titolo_resp)) : ?>
                                                <p class="richtext-wrapper lora"><strong>Responsabile della struttura:</strong> <a href="<?php echo get_permalink($resp_id); ?>" class="text-decoration-none" style="margin-bottom: 1px !important;"><?php echo esc_html($titolo_resp); ?></a></p>
                                            <?php endif; ?>

                                            <?php if (!empty($atto_nomina)) : ?>
                                                <p class="richtext-wrapper lora"><strong>Atto di nomina:</strong> <a href="<?php echo esc_url($atto_nomina); ?>" class="text-decoration-none" style="margin-bottom: 1px !important;"><?php echo esc_html($titolo_atto_nomina); ?></a></p>
                                            <?php endif; ?>

                                            <?php if (!empty($compenso_incarico)) : ?>
                                                <p class="richtext-wrapper lora"><strong>Compenso:</strong> <?php echo esc_html($compenso_incarico); ?></p>
                                            <?php endif; ?>

                                            <?php if (!empty($importi_viaggi_servizi)) : ?>
                                                <p class="richtext-wrapper lora"><strong>Importi di viaggio e/o servizio:</strong> <?php echo esc_html($importi_viaggi_servizi); ?></p>
                                            <?php endif; ?>

                                            <?php if(!empty($more_info_incarico)){?>
                                                <p class="richtext-wrapper lora"><strong>Ulteriori Informazioni:</strong> <?php echo esc_html($more_info_incarico); ?></p>
                                            <?php } ?>
                                        </div>
                                    </div>

                                <?php endforeach; ?>

                            </div>
                        </div>
                    </section>
                <?php } ?>
                <!-- Nascondo tipo incarico perchè mostra un solo tipo anche qualora la persona abbia più incarichi di tipi diversi, creando confusione. Se si vuole mostrare il tipo di incarico, va mostrato all'interno dell'accordion di ogni incarico, così da essere chiaro a quale incarico si riferisce. -->
                <?php // if ($tipo_incarico) { 
                ?>
                <!-- <section class="it-page-section mb-20">
                            <h2 class="title-xxlarge mb-3" id="incarichi">Tipo di Incarico</h2>
                            <div class="richtext-wrapper lora"><?php //echo $tipo_incarico 
                                                                ?></div>
                        </section> -->
                <?php // } 
                ?>
                
                <?php if ($compensi) { ?>
                    <section class="it-page-section mb-20">
                        <h2 class="title-xlarge mb-3" id="compensi">Compensi</h2>
                        <div class="richtext-wrapper lora">
                            <?php echo $compensi ?>
                        </div>
                    </section>
                <?php } ?>

                <!-- Queste variabile devono essere visualizzate solo quando la persona non presenta alcun icarico e sono configurate -->
                <?php if (isset($data_insediamento) && !empty($data_insediamento)) { ?>
                    <section class="it-page-section mb-20">
                        <h2 class="title-xlarge mb-3" id="data-inizio">Data di
                            <?php if ($tipo_incarico == "politico") {
                                echo 'insediamento';
                            } else {
                                echo 'inizio incarico';
                            } ?>
                        </h2>
                        <div class="richtext-wrapper lora"><?php echo $data_insediamento; ?></div>
                    </section>
                <?php } ?>
                <!-- Aggiungo la sezione per la data di conclusione dell'incarico -->
                <?php if (isset($data_conclusione_incarico) && !empty($data_conclusione_incarico)) { ?>
                    <section class="it-page-section mb-20">
                        <h2 class="title-xlarge mb-3" id="data-fine">Data di
                            <?php
                            echo 'conclusione incarico';
                            ?>
                        </h2>
                        <div class="richtext-wrapper lora"><?php echo $data_conclusione_incarico; ?></div>
                    </section>
                <?php } ?>

                <!-- Mostro tutte le UO collegate alla persona -->
                <?php if ($organizzazioni) { ?>
                    <section class="it-page-section mb-20">
                        <h2 class="title-xlarge mb-3" id="organizzazioni">Organizzazione/i</h2>
                        <div class="richtext-wrapper lora">
                            <?php foreach ($organizzazioni as $uo_id) {
                                get_template_part("template-parts/unita-organizzativa/card-full");
                            } ?>
                        </div>
                    </section>
                <?php } ?>

                <!-- Sezione competenze - da utilizzare se non vi sono incarichi o si desidere aggiungere delle informazioni extra -->
                <?php if ($competenze) { ?>
                    <section class="it-page-section mb-20">
                        <h2 class="title-xlarge mb-3" id="competenze">Competenze</h2>
                        <div class="richtext-wrapper lora">
                            <?php echo $competenze; ?>
                        </div>
                    </section>
                <?php } ?>

                <!-- Sezione deleghe - da utilizzare per i politici -->
                <?php if ($deleghe) { ?>
                    <section class="it-page-section mb-20">
                        <h2 class="title-xlarge mb-3" id="deleghe">Deleghe</h2>
                        <div class="richtext-wrapper lora">
                            <?php echo $deleghe; ?>
                        </div>
                    </section>
                <?php } ?>

                <?php if ($biografia) { ?>
                    <section class="it-page-section mb-20">
                        <h2 class="title-xlarge mb-3" id="bio">Biografia</h2>
                        <div class="richtext-wrapper lora">
                            <?php echo $biografia ?>
                        </div>
                    </section>
                <?php } ?>

                <?php if ($curriculum_vitae) { ?>
                    <article id="cv" class="it-page-section anchor-offset mt-5">
                        <h2 class="title-xlarge mb-3">Curriculum Vitae</h2>
                        <div class="card-wrapper card-teaser-wrapper card-teaser-wrapper-equal">
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
                                            <a class="text-decoration-none" href="<?php echo $curriculum_vitae; ?>" target="_blank"
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
                <?php if ($situazione_patrimoniale) { ?>
                    <article id="situazione-patrimoniale" class="it-page-section anchor-offset mt-5">
                        <h2 class="title-xlarge mb-3">Situazione patrimoniale</h2>
                        <div class="card-wrapper card-teaser-wrapper card-teaser-wrapper-equal">
                            <?php echo $situazione_patrimoniale; ?>
                        </div>
                    </article>
                <?php } ?>

                <?php if (!empty($situazione_patrimoniale_id)) : ?>
                    <article id="variazione-situazione-patrimoniale" class="it-page-section anchor-offset mt-5">
                        <h2 class="title-xlarge mb-3">Variazione situazione patrimoniale</h2>

                        <div class="card-wrapper card-teaser-wrapper card-teaser-wrapper-equal">

                            <?php foreach ($situazione_patrimoniale_id as $doc) :

                                // Determina se $doc è URL o ID
                                if (is_numeric($doc)) {
                                    $documento = get_post($doc);
                                    $url = wp_get_attachment_url($doc);
                                } else {
                                    $url = $doc;
                                    $documento_id = attachment_url_to_postid($doc);
                                    $documento = $documento_id ? get_post($documento_id) : null;
                                }

                                $titolo = $documento ? $documento->post_title : basename($url);
                            ?>

                                <?php if ($url) : ?>
                                    <div class="card card-teaser shadow-sm p-4 mt-3 rounded border border-light flex-nowrap">
                                        <svg class="icon" aria-hidden="true">
                                            <use xlink:href="#it-clip"></use>
                                        </svg>
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                <a class="text-decoration-none"
                                                href="<?php echo esc_url($url); ?>"
                                                target="_blank"
                                                aria-label="Visualizza il documento <?php echo esc_attr($titolo); ?>"
                                                title="Scarica il documento <?php echo esc_attr($titolo); ?>">
                                                    <?php echo esc_html($titolo); ?>
                                                </a>
                                            </h5>
                                        </div>
                                    </div>
                                <?php endif; ?>

                            <?php endforeach; ?>

                        </div>
                    </article>
                <?php endif; ?>
                <?php if ($dichiarazione_redditi) { ?>
                    <article id="dichiarazione-redditi" class="it-page-section anchor-offset mt-3">
                        <h2 class="title-xlarge mb-3">Dichiarazione dei redditi</h2>
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
                                                    target="_blank"
                                                    aria-label="Visualizza il documento <?php echo $documento->post_title; ?>"
                                                    title="Scarica il documento <?php echo $documento->post_title; ?>">
                                                    <?php echo $documento->post_title; ?>
                                                </a>
                                            </h5>
                                        </div>
                                    </div>
                                <?php } ?>
                            <?php } ?>
                        </div>
                    </article>
                <?php } ?>

               <?php if (!empty($spese_elettorali)) { ?>
                    <article id="spese-elettorali" class="it-page-section anchor-offset mt-3">
                        <h2 class="title-xlarge mb-3">Spese elettorali</h2>

                        <div class="card-wrapper card-teaser-wrapper card-teaser-wrapper-equal">
                            <?php foreach ($spese_elettorali as $spesa) :

                                $documento_id = attachment_url_to_postid($spesa);
                                $documento = $documento_id ? get_post($documento_id) : null;

                                $titolo = $documento ? $documento->post_title : basename($spesa);
                            ?>
                                <div class="card card-teaser shadow-sm p-4 mt-3 rounded border border-light flex-nowrap">
                                    <svg class="icon" aria-hidden="true">
                                        <use xlink:href="#it-clip"></use>
                                    </svg>

                                    <div class="card-body">
                                        <h5 class="card-title">
                                            <a class="text-decoration-none"
                                            href="<?php echo esc_url($spesa); ?>"
                                            target="_blank"
                                            aria-label="Visualizza il documento <?php echo esc_attr($titolo); ?>"
                                            title="Scarica il documento <?php echo esc_attr($titolo); ?>">
                                                <?php echo esc_html($titolo); ?>
                                            </a>
                                        </h5>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </article>
                <?php } ?>

                <?php if (!empty($altre_cariche)) { ?>
                    <article id="altre-cariche" class="it-page-section anchor-offset mt-3">
                        <h2 class="title-xlarge mb-3">Altre cariche</h2>

                        <div class="card-wrapper card-teaser-wrapper card-teaser-wrapper-equal">
                            <div class="richtext-wrapper lora">

                                <?php foreach ($altre_cariche as $file_url) :

                                    $documento_id = attachment_url_to_postid($file_url);
                                    $documento = $documento_id ? get_post($documento_id) : null;

                                    $titolo = $documento ? $documento->post_title : basename($file_url);
                                ?>
                                    <div class="card card-teaser shadow-sm p-4 mt-3 rounded border border-light flex-nowrap">
                                        <svg class="icon" aria-hidden="true">
                                            <use xlink:href="#it-clip"></use>
                                        </svg>

                                        <div class="card-body">
                                            <h5 class="card-title">
                                                <a class="text-decoration-none"
                                                href="<?php echo esc_url($file_url); ?>"
                                                target="_blank"
                                                aria-label="Visualizza il documento <?php echo esc_attr($titolo); ?>"
                                                title="Scarica il documento <?php echo esc_attr($titolo); ?>">
                                                    <?php echo esc_html($titolo); ?>
                                                </a>
                                            </h5>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </article>
                <?php } ?>

            <?php if ($gallery && $gallery !== []){?>
                <section class="it-page-section mb-20 mt-5">
                <h2 class="title-xlarge mb-3" id="gallery">Galleria</h2>
                <article class="it-page-section it-grid-list-wrapper anchor-offset mt-5">
                        <?php get_template_part("template-parts/single/gallery"); ?>
                    </article>
                </section>
                <!-- Stile galleria foto -->
                <style>
                .it-grid-item-wrapper {
                    /* Imposta una larghezza fissa per il contenitore dell'immagine/audio se necessario */
                    width: 100%;
                }

                .img-responsive-wrapper {
                    display: flex;
                    justify-content: center; /* Centratura orizzontale */
                }

                </style>
            <?php } ?>


            <!-- CONTATTI -->
            <?php if (!empty($punti_contatto)) { ?>
                <section class="it-page-section mb-20 mt-3">
                    <h2 class="title-xlarge mb-3" id="contatti">Contatti</h2>

                    <div class="richtext-wrapper lora">
                        <?php foreach ($punti_contatto as $pc_id) {
                            get_template_part('template-parts/single/punto-contatto');
                        } ?>
                    </div>
                </section>
            <?php } ?>

                <!-- Aggiungo la sezione ulteriori informazioni in fondo alla pagina -->
                <section class="it-page-section anchor-offset mt-3">
                    <h4 class="mb-auto" id="ulteriori-informazioni">Ulteriori informazioni</h4>
                    <?php if ($ulteriori_informazioni) { ?>
                        <div class="richtext-wrapper lora">
                            <?php echo esc_html($ulteriori_informazioni); ?>
                        </div>
                    <?php } ?>
                    <!-- Visulizzo la data di ultima modifica della persona -->
                    <?php get_template_part('template-parts/single/page_bottom'); ?>
                </section>
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
<!-- Stili per l'accordion degli incarichi -->
<style>
    .incarichi-accordion {
        width: 100%;
        max-width: 100%;
        margin: 20px 0 40px 0;
    }

    .accordion-item-incarico {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        margin-bottom: 8px;
        background: #ffffff;
        overflow: hidden;
    }

    .accordion-header-incarico {
        width: 100%;
        background: #ffffff;
        border: none;
        padding: 18px 22px;
        text-align: left;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: background 0.2s ease;
    }

    .accordion-header-incarico:hover {
        background: #f3f4f6;
    }

    .accordion-icon {
        font-size: 16px;
        transition: transform 0.3s ease;
    }

    .accordion-header-incarico.active .accordion-icon {
        transform: rotate(45deg);
    }

    .accordion-content {
        display: none;
        padding: 18px 22px 22px 22px;
        background: #fafafa;
    }

    .accordion-content p,
    .accordion-content a {
        margin-bottom: 10px;
        font-size: 15px;
        line-height: 1.6;
    }

    .accordion-content a {
        display: inline-block;
    }
</style>