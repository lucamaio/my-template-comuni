<?php

/**
 *  Unità Organizzativa template file
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
    while (have_posts()) :
        the_post();
        $user_can_view_post = dci_members_can_user_view_post(get_current_user_id(), $post->ID);

        $prefix = '_dci_unita_organizzativa_';
        $documenti = dci_get_meta("documenti", $prefix, $post->ID);

        // Initialize variables to avoid errors
        $img = dci_get_meta("immagine", $prefix, $post->ID) ?? '';
        $sottotitolo = dci_get_meta("sottotitolo") ?? '';
        $descrizione_breve = dci_get_meta("descrizione_breve") ?? '';
        $competenze = dci_get_wysiwyg_field("competenze") ?? '';
        $responsabili = dci_get_meta("responsabile") ?? [];
        $referente= dci_get_meta("assessore_riferimento") ?? null;

        $tipologie = get_the_terms($post, 'tipi_unita_organizzativa') ?? [];
        $tipologia = !empty($tipologie) ? $tipologie[0]->name : '';

        $area_riferimento = dci_get_meta("unita_organizzativa_genitore") ?? '';
        $persone = dci_get_meta("persone_struttura") ?? [];
        $allegati = dci_get_meta("allegati", $prefix, $post->ID) ?? [];
        $sede_principale = dci_get_meta("sede_principale") ?? '';
        $servizi = dci_get_meta("elenco_servizi_offerti") ?? [];
        $descrizione = dci_get_wysiwyg_field("descrizione_estesa") ?? '';
        $punti_contatto = dci_get_meta("contatti") ?? [];
        $orario_id = dci_get_meta("orario_uo", $prefix, $post->ID) ?? '';
        
        $contatti = [];
        foreach ($punti_contatto as $pc_id) {
            $contatto = dci_get_full_punto_contatto($pc_id);
            if ($contatto) {
                $contatti[] = $contatto;
            }
        }
        
        $more_info = dci_get_wysiwyg_field("ulteriori_informazioni") ?? '';
        //$uo_id = intval(dci_get_meta("unita_responsabile")) ?? 0;
        $argomenti = get_the_terms($post, 'argomenti') ?? [];

        $mostra_servizi=dci_get_option('ck_servizi', 'amministrazione');

        // valori per metatag
        // $categorie = get_the_terms($post, 'categorie_servizio') ?? [];
        // $categoria_servizio = !empty($categorie) ? $categorie[0]->name : '';		    


        // if (isset($canale_fisico_uffici[0])) {
        //     $ufficio = get_post($canale_fisico_uffici[0]);
        //     if ($ufficio) {
        //         $luogo_id = dci_get_meta('sede_principale', '_dci_unita_organizzativa_', $ufficio->ID) ?? null;
        //         $indirizzo = dci_get_meta('indirizzo', '_dci_luogo_', $luogo_id) ?? '';
        //         $quartiere = dci_get_meta('quartiere', '_dci_luogo_', $luogo_id) ?? '';
        //         $cap = dci_get_meta('cap', '_dci_luogo_', $luogo_id) ?? '';
        //     }
        // }

        // function convertToPlain($text) {
        //     $text = str_replace(array("\r", "\n"), '', $text);
        //     $text = str_replace('"', '\"', $text);
        //     $text = str_replace('&nbsp;', ' ', $text);
        //     return trim(strip_tags($text));
        // }
    ?>

</main>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
<script type="application/ld+json" data-element="metatag">
    {
        "name": "<?= $post->post_title; ?>",
        "UO_Type": "<?= $tipologia; ?>",
    }
</script>

<style>
    .disabled-link {
        pointer-events: none; /* disabilita il clic */
        text-decoration: none;
        cursor: default;
    }
    /* Stile più leggero per la foto */
    .img-resized {
        /* border: 1px solid #ddd;              Bordo molto leggero */
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        /* Ombra quasi impercettibile */
        padding: 2px;
        background-color: #fff;
        /* Sfondo bianco per risalto se su sfondo grigio */
        border-radius: 8px;
        /* Angoli leggermente arrotondati */
    }
    #servizi .row>* {
        margin-bottom: 1.5rem;
        /* oppure 3rem o 48px per un distanziamento maggiore */
    }
</style>

<div class="container" id="main-container">
    <div class="row">
        <div class="col-12 col-lg-10">
            <?php get_template_part("template-parts/common/breadcrumb"); ?>
        </div>
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-lg-8 px-lg-4 py-lg-2">
            <div class="cmp-heading pb-3 pb-lg-4">
                <div class="row">
                    <div class="col-12">
                        <h1 class="title-xxxlarge text-start" data-element="service-title">
                            <?php the_title(); ?>
                        </h1>
                        <p class="subtitle-small mb-3 text-start" data-element="service-description">
                            <?php echo $descrizione_breve; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Colonna per le azioni -->
        <div class="col-lg-3 offset-lg-1 mt-5 mt-lg-0">
            <?php
            $hide_arguments = true;
            get_template_part('template-parts/single/actions');
            ?>
        </div>
    </div>
    <hr class="d-none d-lg-block mt-2" />
</div>


<div class="container">
    <div class="row row-column-menu-left mt-4 mt-lg-80 pb-lg-80 pb-40">
        <div class="col-12 col-lg-3 mb-4 border-col">
            <div class="cmp-navscroll sticky-top" aria-labelledby="accordion-title-one">
                <nav class="navbar it-navscroll-wrapper navbar-expand-lg" aria-label="Indice della pagina" data-bs-navscroll>
                    <div class="navbar-custom" id="navbarNavProgress">
                        <div class="menu-wrapper">
                            <div class="link-list-wrapper">
                                <div class="accordion">
                                    <div class="accordion-item">
                                        <span class="accordion-header" id="accordion-title-one">
                                            <?php if ($img) { ?>
                                                <section class="hero-img mb-20 mb-lg-50 aling-itmes-center">
                                                    <center>
                                                        <div class="img-wrapper">
                                                            <!-- Applica la classe CSS img-resized per ridimensionare l'immagine -->
                                                            <?php dci_get_img($img, 'rounded img-fluid img-responsive foto-soft-style img-resized'); ?>
                                                        </div>
                                                    </center>
                                                </section>
                                            <?php } ?>
                                            <button class="accordion-button pb-10 px-3 text-uppercase" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-one" aria-expanded="true" aria-controls="collapse-one">
                                                Indice della pagina
                                                <svg class="icon icon-xs right">
                                                    <use href="#it-expand"></use>
                                                </svg>
                                            </button>
                                        </span>
                                        <div class="progress">
                                            <div class="progress-bar it-navscroll-progressbar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <div id="collapse-one" class="accordion-collapse collapse show" role="region" aria-labelledby="accordion-title-one">
                                            <div class="accordion-body">
                                                <ul class="link-list" data-element="page-index">
                                                    <?php if ($competenze) { ?>
                                                        <li class="nav-item">
                                                            <a class="nav-link" href="#competenze">
                                                                <span class="title-medium">Competenze</span>
                                                            </a>
                                                        </li>
                                                    <?php } ?>
                                                    <?php if ($area_riferimento) { ?>
                                                        <li class="nav-item">
                                                            <a class="nav-link" href="#uo_riferimento">
                                                                <?php if ($tipologie == "ufficio") { ?>
                                                                    <span class="title-medium">Area di Riferimento</span>
                                                                <?php } else { ?>
                                                                    <span class="title-medium">Uffici collegati</span>
                                                                <?php } ?>
                                                            </a>
                                                        </li>
                                                    <?php } ?>
                                                    <?php if (is_array($responsabili) && !empty($responsabili) && $responsabili!=null) { ?>
                                                        <li class="nav-item">
                                                            <a class="nav-link" href="#responsabile">
                                                                <span class="title-medium">Responsabili</span>
                                                            </a>
                                                        </li>
                                                    <?php } ?>
                                                    <?php if (!empty($referente)) { ?>
                                                        <li class="nav-item">
                                                            <a class="nav-link" href="#referente">
                                                                <span class="title-medium">Referente</span>
                                                            </a>
                                                        </li>
                                                    <?php } ?>
                                                    <?php if (is_array($persone)) { ?>
                                                        <li class="nav-item">
                                                            <a class="nav-link" href="#persone">
                                                                <span class="title-medium">Persone</span>
                                                            </a>
                                                        </li>
                                                    <?php } ?>
                                                    <?php if (is_array($servizi) && !empty($servizi)) { ?>
                                                        <li class="nav-item">
                                                            <a class="nav-link" href="#servizi">
                                                                <span class="title-medium">Servizi collegati</span>
                                                            </a>
                                                        </li>
                                                    <?php } ?>
                                                    <?php if (isset($more_info) and !empty($more_info)) { ?>
                                                        <li class="nav-item">
                                                            <a class="nav-link" href="#more-info">
                                                                <span class="title-medium">Ulteriori informazioni</span>
                                                            </a>
                                                        </li>
                                                    <?php } ?>
                                                    <?php
                                                    // Assicurati che le variabili siano definite prima di usarle
                                                    $sede_principale = $sede_principale ?? null; // Imposta a null se non è definita
                                                    $altre_sedi = $altre_sedi ?? null; // Imposta a null se non è definita

                                                    if (!empty($sede_principale)) { ?>
                                                        <li class="nav-item">
                                                            <a class="nav-link" href="#sede">
                                                                <span class="title-medium">Sede principale</span>
                                                            </a>
                                                        </li>
                                                    <?php } ?>

                                                    <?php if (!empty($altre_sedi)) { ?>
                                                        <li class="nav-item">
                                                            <a class="nav-link" href="#altre-sedi">
                                                                <span class="title-medium">Altre sedi</span>
                                                            </a>
                                                        </li>
                                                    <?php } ?>
                                                    <?php if ($allegati) { ?>
                                                        <li class="nav-item">
                                                            <a class="nav-link" href="#allegati">
                                                                <span class="title-medium">Allegati</span>
                                                            </a>
                                                        </li>
                                                    <?php } ?>
                                                     <?php if ($orario_id) { ?>
                                                        <li class="nav-item">
                                                            <a class="nav-link" href="#orario">
                                                                <span class="title-medium">Orario</span>
                                                            </a>
                                                        </li>
                                                    <?php } ?>

                                                    <?php if ($contatti) { ?>
                                                        <li class="nav-item">
                                                            <a class="nav-link" href="#contatti">
                                                                <span class="title-medium">Contatti</span>
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
                <section class="it-page-section mb-30">
                    <h2 class="title-xxlarge mb-3" id="competenze">Competenze</h2>
                    <div class="richtext-wrapper lora">
                        <?php echo $competenze ?>
                    </div>
                </section>
                <?php if ($tipologia) { ?>
                    <section class="it-page-section mb-30">
                        <h2 class="title-xxlarge mb-3" id="tipo_organizzazione">Tipologia di Organizzazione</h2>
                        <div class="richtext-wrapper lora"><?php echo $tipologia; ?></div>
                    </section>
                <?php } ?>

                <?php if ($area_riferimento && is_array($area_riferimento) && count($area_riferimento) > 0) { ?>
                    <section class="it-page-section mb-30" id="uo_riferimento">
                        <?php if ($tipologia == "ufficio") {
                            echo '<h2 class="title-xxlarge mb-3" id="costs">Area di Riferimento</h2>';
                            echo '<div class="richtext-wrapper lora">';
                            foreach ($area_riferimento as $uo_id) {
                                get_template_part("template-parts/unita-organizzativa/card-full");
                            }
                            echo '</div>';
                        } else {
                            echo '<h2 class="title-xxlarge mb-3" id="costs">Uffici collegati</h2>';
                            echo '<div class="richtext-wrapper lora">';
                            echo '<div class="row">';
                            foreach ($area_riferimento as $uo_id) {
                                get_template_part("template-parts/unita-organizzativa/card-custom");
                            }

                            echo '</div>';
                            echo '</div>';
                        } ?>
                    </section>
                <?php } ?>

                <?php if (!empty($responsabili) && is_array($responsabili)) { ?>
                <section class="it-page-section" id="responsabile">
                        <h2 class="mb-3">Responsabili</h2>
                        <div class="row">
                            <?php foreach ($responsabili as $responsabile_id) { ?>
                                <div class="col-12 col-md-8 col-lg-6 mb-auto">
                                    <div class="cmp-card-latest-messages mb-3 mb-25">
                                        <div class="card card-bg px-4 pt-4 pb-4 rounded">
                                            <div class="card-header border-0 p-0 mb-2" style="flex-direction: column !important;">
                                                <?php
                                                
                                                $responsabile_post = get_post($responsabile_id);
                                                if (!$responsabile_post) continue;

                                                $responsabile_nome = get_the_title($responsabile_post);
                                                $responsabile_link = get_permalink($responsabile_post) ?? "#"; 
                                                $responsabile_incarichi = dci_get_meta('incarichi', '_dci_persona_pubblica_', $responsabile_id);
                                                $responsabile_descrizione =dci_get_meta('descrizione_breve', '_dci_persona_pubblica_', $responsabile_id);

                                                
                                                if (!empty($responsabile_incarichi) && is_array($responsabile_incarichi)) {
                                                    foreach ($responsabile_incarichi as $incarico_id) {
                                                        $incarico_post = get_post($incarico_id);
                                                        if (!$incarico_post) continue;

                                                        $incarico_nome = get_the_title($incarico_post);
                                                        //$incarico_link = get_permalink($incarico_post) ?: "#";  // Disattivo il link coretto in quanto questa sezione si deve implementare
                                                        $incarico_link = "#";
                                                ?>
                                                    <a class="text-decoration-none title-xsmall-bold category d-block disabled-link" style="color: var(--bs-secondary) !important; text-decoration: none !important; max-width: 92% !important;  pointer-events: none;"
                                                        href="<?php echo esc_url($incarico_link); ?>">
                                                        <?= esc_html($incarico_nome); ?>
                                                    </a>
                                                    <?php }
                                                } 

                                                // $incarichi = dci_get_meta("incarichi", '_dci_persona_pubblica_', $responsabile) ?? [];
                                                // $incarico = !empty($incarichi) ? get_the_title($incarichi[0]) : '';
                                                // $responsabile_di=dci_get_meta("responsabile_di", '_dci_persona_pubblica_', $responsabile);
                                                // $nome_incarico = !empty($incarico) ? $incarico : (!empty($responsabile_di) ? "Responsabile ".get_the_title($responsabile_di) : '');
                                            ?>
                                            </div>   
                                            <div class="card-body p-0">
                                                <h4 class="h6 mb-1">
                                                    <a href="<?= esc_url($responsabile_link); ?>" class="text-decoration-none">
                                                        <?= esc_html($responsabile_nome); ?>
                                                    </a>
                                                </h4>
                                                <?php if (!empty($responsabile_descrizione)) { ?>
                                                <p class="text-paragraph text-justify mb-0">
                                                    <?= esc_html($responsabile_descrizione); ?>
                                                </p>
                                                <?php } ?>
                                            </div>                                        
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                </section>
                <?php } ?>

                <!-- Sezione Assessore Referente -->
                <?php if ($referente && $referente != null) { ?>
                <section class="it-page-section" id="referente">
                        <h2 class="mb-3">Referente</h2>
                        <div class="row">
                                <div class="col-12 col-md-8 col-lg-6 mb-30">
                                    <div class="cmp-card-latest-messages mb-3 mb-30">
                                        <div class="card card-bg px-4 pt-4 pb-4 rounded">
                                            <div class="card-header border-0 p-0">
                                                <?php
                                                $referente_id = $referente;
                                                $referente_post = get_post($referente);

                                                $referente_nome = get_the_title($referente_post);
                                                $referente_descrizione =dci_get_meta('descrizione_breve', '_dci_persona_pubblica_', $referente_id);
                                                $referente_link = get_permalink($referente_post) ?? "#"; 

                                                $referente_incarichi = dci_get_meta('incarichi', '_dci_persona_pubblica_', $referente_id);
                                                // Stampo gli incarichi del referente
                                                if (!empty($referente_incarichi) && is_array($referente_incarichi)) {
                                                    foreach ($referente_incarichi as $incarico_id) {
                                                        $incarico_post = get_post($incarico_id);
                                                        if (!$incarico_post) continue;

                                                        $incarico_nome = get_the_title($incarico_post);
                                                        //$incarico_link = get_permalink($incarico_post) ?: "#";  // Disattivo il link coretto in quanto questa sezione si deve implementare
                                                        $incarico_link = "#";
                                                ?>
                                                    <a class="text-decoration-none title-xsmall-bold category text-uppercase d-block disabled-link"
                                                        href="<?php echo esc_url($incarico_link); ?>">
                                                        <?php echo esc_html($incarico_nome); ?>
                                                    </a> 
                                                    <?php }
                                                } 
                                            ?>
                                            </div>
                                            <div class="card-body p-0 my-2">
                                                <div class="card-content">
                                                    <h4 class="h5">
                                                        <a href="<?php echo $referente_link ?>">
                                                            <?php echo $referente_nome; ?>
                                                        </a>
                                                    </h4>
                                                    <p class="text-paragraph">
                                                        <?php echo $referente_descrizione; ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        </div>
                </section>
                <?php } ?>

                <!-- Sezione Persone -->
                <section class="it-page-section" id="persone">
                    <h2 class="mb-3">Persone</h2>
                    <div class="row">
                        <?php
                        $with_border = true;
                        get_template_part("template-parts/single/persone");
                        ?>
                        <br>
                    </div>
                </section>

                <!-- Sezione Servizi -->
                <?php
                if (strlen(dci_get_option('servizi_maggioli_url', 'servizi')) < 5 && ($mostra_servizi=='true' || !$mostra_servizi) ) {
                    if ($servizi && is_array($servizi) && count($servizi) > 0) { ?>
                        <section id="servizi" class="it-page-section anchor-offset mt-5 mb-4 ">
                            <h3>Servizi collegati</h3>
                            <div class="row  gy-5">
                                <?php
                                $i = 0;
                                foreach ($servizi as $servizio_id) {
                                    $servizio = get_post($servizio_id);
                                    $with_border = true;
                                    get_template_part("template-parts/servizio/card");
                                    $i++;
                                    if ($i % 3 == 0 && $i < count($servizi)) {
                                        echo '</div><div class="row">';
                                    }
                                } ?>
                            </div>
                        </section>
                <?php
                    }
                }
                ?>

                <!-- Sezione Ulteriori Informazioni -->

                <?php if ($more_info) {  ?>
                    <section class="it-page-section mb-30">
                        <h2 class="title-xxlarge mb-3" id="more-info">Ulteriori informazioni</h2>
                        <div class="richtext-wrapper lora">
                            <?php echo $more_info ?>
                        </div>
                    </section>
                <?php }  ?>


                <?php if ($sede_principale) { ?>
                    <p></p>
                    <section class="it-page-section" id="sede">
                        <h3 class="mt-4">Sede principale</h3>
                        <div class="row">
                            <div class="col-12 col-md-8 col-lg-6 mb-30">
                                <div class="card-wrapper rounded h-auto mt-10">
                                    <div class="card card-teaser shadow-sm p-4s rounded border border-light flex-nowrap">
                                        <div class="card-body pe-3">
                                            <p class="card-title text-paragraph-regular-medium-semi mb-3">
                                                <a href="<?php echo get_permalink($sede_principale); ?>">
                                                    <?php echo get_the_title($sede_principale); ?>
                                                </a>
                                            </p>
                                            <div class="card-text">
                                                <p><?php echo dci_get_meta("indirizzo", '_dci_luogo_', $sede_principale); ?></p>
                                                <p><?php echo dci_get_meta("descrizione_breve", '_dci_luogo_', $sede_principale); ?></
                                                        </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    </section>
                <?php } ?>

                <!-- Sezione Allegati -->
                <?php if ($allegati && is_array($allegati) && count($allegati) > 0) { ?>
                    <section id="allegati" class="it-page-section mb-4">
                        <h2 class="h3 my-2">Documenti</h2>
                        <div class="row">
                            <?php foreach ($allegati as $allegato_id) { ?>
                                <div class="col-md-6 col-sm-12 mb-3 card-wrapper">
                                    <?php
                                    $documento = get_post($allegato_id);
                                    $with_border = true;
                                    get_template_part("template-parts/documento/card"); ?>
                                </div>
                            <?php  } ?>
                        </div>
                    </section>
                <?php } ?>

                <!-- Sezione Orario -->
                
                <?php if(isset($orario_id) && $orario_id != null){?>
                    <section class="it-page-section mb-30">
                        <h3 class="title-xlarge mb-3" id="orario">Orario</h3>
                        <div class="richtext-wrapper lora">
                            <?php
                                get_template_part('template-parts/single/orario');
                            ?>
                        </div>
                    </section>
                <?php } ?>
                
                <section class="it-page-section mb-30">
                    <h2 class="title-xxlarge mb-3" id="contatti">Contatti</h2>
                    <div class="richtext-wrapper lora">
                        <?php foreach ($punti_contatto as $pc_id) {
                            get_template_part('template-parts/single/punto-contatto');
                        } ?>
                    </div>
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


