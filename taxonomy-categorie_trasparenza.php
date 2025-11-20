<?php 
/**
 * Archivio Tassonomia trasparenza
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#custom-taxonomies
 * @package Design_Comuni_Italia
 */

global $title, $description, $data_element, $elemento, $sito_tematico_id, $siti_tematici;

get_header();
$obj = get_queried_object();

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



$siti_tematici = !empty(dci_get_option("siti_tematici", "trasparenza")) ? dci_get_option("siti_tematici", "trasparenza") : [];
?>

<main>
    <?php
    $title = $obj->name;
    $description = $obj->description;
    $data_element = 'data-element="page-name"';
    get_template_part("template-parts/hero/hero");
    get_template_part("template-parts/amministrazione-trasparente/sottocategorie");
    ?>

    <div class="bg-grey-card">
        
      <?php 
          if ($obj->name == "Contratti Pubblici" && dci_get_option("ck_bandidigaratemplatepersonalizzato", "Trasparenza") !== 'false' && dci_get_option("ck_bandidigaratemplatepersonalizzato", "Trasparenza") !== '') 
               { 
        ?>
            <div class="container my-5">
                <div class="row">
                    <h2 class="visually-hidden">Esplora tutti i bandi di gara</h2>
                    <div class="col-12 col-lg-8 pt-20 pt-lg-20 pb-lg-20"></div>
                    <div class="row g-3" id="load-more">
                        <?php get_template_part("template-parts/bandi-di-gara/tutti-bandi"); ?>
                    </div>
                 <?php // get_template_part("template-parts/amministrazione-trasparente/side-bar"); ?>
                </div>
            </div>
        </div>
    
    <?php }else if ($obj->name == "Atti di concessione" && dci_get_option("ck_attidiconcessione", "Trasparenza") !== 'false' && dci_get_option("ck_attidiconcessione", "Trasparenza") !== '') { ?>
            <div class="container my-5">
                <div class="row">
                    <h2 class="visually-hidden">Esplora tutti gli Atti di Concessione</h2>
                    <div class="col-12 col-lg-8 pt-20 pt-lg-20 pb-lg-20"></div>
                    <div class="row g-3" id="load-more">
                        <?php get_template_part("template-parts/amministrazione-trasparente/atto-concessione/tutti-gli-atti"); ?>
                    </div>
                    <?php // get_template_part("template-parts/amministrazione-trasparente/side-bar"); ?> 
                </div>
            </div>
        </div>
     <?php }else if ($obj->name == "Incarichi conferiti e autorizzati ai dipendenti"  && dci_get_option("ck_incarichieautorizzazioniaidipendenti", "Trasparenza") !== 'false' && dci_get_option("ck_incarichieautorizzazioniaidipendenti", "Trasparenza") !== '') { ?>
            <div class="container my-5">
                <div class="row">
                    <h2 class="visually-hidden">Esplora tutti gli Incarichi conferiti e autorizzati ai dipendenti</h2>
                    <div class="col-12 col-lg-8 pt-20 pt-lg-20 pb-lg-20"></div>
                    <div class="row g-3" id="load-more">
                        <?php get_template_part("template-parts/amministrazione-trasparente/incarichi-autorizzazioni/tutti-gli-incarichi"); ?>
                    </div>
                    <?php // get_template_part("template-parts/amministrazione-trasparente/side-bar"); ?> 
                </div>
            </div>
        </div>		    
    <?php } else if ($obj->name == "Titolari di incarichi di collaborazione o consulenza" && dci_get_option("ck_titolariIncarichiCollaborazioneConsulenzaTemplatePersonalizzato", "Trasparenza") !== 'false' && dci_get_option("ck_titolariIncarichiCollaborazioneConsulenzaTemplatePersonalizzato", "Trasparenza") !== '') 
               { 
        ?>
            <div class="container my-5">
                <div class="row">
                    <h2 class="visually-hidden">Esplora tutti i Titolari di incarichi di collaborazione o consulenza</h2>
                    <div class="col-12 col-lg-8 pt-20 pt-lg-20 pb-lg-20"></div>
                    <div class="row g-3" id="load-more">
                        <?php get_template_part("template-parts/amministrazione-trasparente/titolare_incarico/tutti-titolari"); ?>
                    </div>
                 <?php // get_template_part("template-parts/amministrazione-trasparente/side-bar"); ?>
                </div>
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
                        <div class="cmp-input-search">
                            <div class="form-group autocomplete-wrapper mb-2 mb-lg-4">
                                <div class="input-group">
                                    <label for="autocomplete-two" class="visually-hidden">Cerca una parola chiave</label>
                                    <input type="search" class="autocomplete form-control"
                                        placeholder="Cerca una parola chiave" id="autocomplete-two" name="search"
                                        value="<?php echo $query; ?>" data-bs-autocomplete="[]">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="submit" id="button-3">Invio</button>
                                    </div>
                                    <span class="autocomplete-icon" aria-hidden="true">
                                        <svg class="icon icon-sm icon-primary" role="img" aria-labelledby="autocomplete-label">
                                            <use href="#it-search"></use>
                                        </svg>
                                    </span>
                                </div>
                            </div>
                            <p id="autocomplete-label" class="mb-4">
                                <strong><?php echo $the_query->found_posts; ?></strong> elementi trovati in ordine
                                <?php echo ($order == 'alfabetico_asc' || $order == 'alfabetico_desc') ? "alfabetico" : "di pubblicazione"; ?>
                                <?php echo ($order == 'desc' || $order == 'alfabetico_desc') ? "(Discendente)" : "(Ascendente)"; ?>
                            </p>
                        </div>

                        <!-- Sezione per ordinamento -->
                        <div class="form-group mb-4">
                             <span style="font-size: 1.2rem; font-weight: bold; color: #333;">Ordina per</span>
                            <select id="order-select" name="order_type" class="form-control" style="width: 100%; padding: 0.75rem 1rem; font-size: 1rem;">
                                <option value="data_desc" <?php echo ($order == 'data_desc') ? 'selected' : ''; ?>>Data (Descendente)</option>
                                <option value="data_asc" <?php echo ($order == 'data_asc') ? 'selected' : ''; ?>>Data (Ascendente)</option>
                                <option value="alfabetico_asc" <?php echo ($order == 'alfabetico_asc') ? 'selected' : ''; ?>>Alfabetico (Ascendente)</option>
                                <option value="alfabetico_desc" <?php echo ($order == 'alfabetico_desc') ? 'selected' : ''; ?>>Alfabetico (Discendente)</option>
                            </select>
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
                            <div class="alert alert-info text-center" role="alert">
                                Nessun post trovato.
                            </div>
                        <?php } ?>
                    </div>

                    <!-- Colonna destra: link utili -->
                    <?php get_template_part("template-parts/amministrazione-trasparente/side-bar"); ?>

                    <div class="row my-4">
                        <nav class="pagination-wrapper justify-content-center col-12" aria-label="Navigazione pagine">
                            <?php echo dci_bootstrap_pagination(); ?>
                        </nav>
                    </div>
                </div>
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





