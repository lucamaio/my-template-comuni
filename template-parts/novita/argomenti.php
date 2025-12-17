<?php
    $categotrie = get_terms('tipi_notizia', array(
        'hide_empty' => false,
    ) );

    $argomenti_evidenza = dci_get_option('argomenti','novita');
    // var_dump($argomenti_evidenza);
?>

<div class="container py-5" id="categoria">
    <h2 class="title-xxlarge mb-4">Esplora per categoria</h2>
    <div class="row g-4">       
        <?php foreach ($categotrie as $categoria) {           
        ?>
        <div class="col-md-6 col-xl-4">
            <div class="cmp-card-simple card-wrapper pb-0 rounded border border-light">
              <div class="card shadow-sm rounded">
                <div class="card-body">
                    <a class="text-decoration-none" href="<?php echo get_term_link($categoria->term_id); ?>" data-element="news-category-link"><h3 class="card-title t-primary title-xlarge"><?php echo ucfirst($categoria->name); ?></h3></a>
                    <p class="titillium text-paragraph mb-0 description">
                        <?php echo $categoria->description; ?>
                    </p>
                </div>
              </div>
            </div>
          </div>
        <?php } ?>
    </div>
</div>

<?php if(isset($argomenti_evidenza) && count($argomenti_evidenza)>0){ ?>
<div class="container py-4" id="argomenti">
     <h3 class="title-xlarge mb-4">Argomenti</h3>
        <div class="row pt-20">
            <div class="col-12">
                <div class="button-group">
                            <?php
                            // Ciclo per gli altri argomenti
                            foreach ($argomenti_evidenza as $i => $arg_id) {
                                $argomento = get_term_by('term_taxonomy_id', $arg_id);
                                if ($argomento) {
                                    $url = get_term_link($argomento->term_id, 'argomenti');
                            ?>
                            
                            <a href="<?php echo esc_url($url); ?>" class="btn-argomento" style="display: inline-flex; align-items: center; gap: 8px;">
                            <svg class="icon text-primary" style="width:20px; height:30px; display:inline-block; vertical-align:middle;">
                                <use xlink:href="#it-bookmark"></use>
                            </svg>
                            <?php echo esc_html($argomento->name); ?>
                            </a>
                            
                            <?php
                                }
                            }
                            ?>
                    <br>
                </div>
                 <div style="display: flex; justify-content: center; margin-top: 20px;">
                    <a href="<?php echo dci_get_template_page_url('page-templates/argomenti.php'); ?>"  
                    class="btn btn-primary">
                        Mostra tutti
                    </a>
                </div>
            </div>
    </div>
</div>
<!-- Stile sezione argomenti -->
 <style>
/* Allineamento degli elementi dentro la riga */
.container .row.pt-20 {
    text-align: left; /* Allinea il testo e gli altri contenuti a sinistra */
}

/* Gruppo di pulsanti "Altri argomenti" */
.container .row.pt-20 .button-group {
    display: flex;
    flex-wrap: wrap;
    gap: 16px; /* Spazio tra i pulsanti */
    padding: 0;
    margin: 0;
    justify-content: flex-start; /* Allinea i pulsanti a sinistra */
}

/* Gruppo di pulsanti "Altri argomenti" - Evita che i pulsanti occupino l'intera larghezza */
.container .row.pt-20 .button-group a.btn-argomento {
    display: inline-block;
    padding: 10px 16px;
    background-color: #ffffff;
    color: #333;
    font-size: 1rem;
    font-weight: 500;
    border: 2px solid #dcdcdc;
    margin-top: -5px; /* Aggiungi un po' di margine sopra i pulsanti per farli avvicinare al titolo */
    border-radius: 8px; 
    text-decoration: none;
    text-align: center;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

/* Hover: Solo effetto di sollevamento e ombra */
.container .row.pt-20 .button-group a.btn-argomento:hover {
    background-color: #ffffff;
    color: #333;
    border-color: #dcdcdc;
    transform: translateY(-4px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}

/* Titolo "Altri argomenti" sopra il container */
.container .row.pt-20 h3.title-xsmall-bold.text.u-grey-light {
    font-size: 1.2rem;
    font-weight: 600;
    color: #333;
    letter-spacing: 0.5px;
    white-space: normal; /* Permette al testo di andare a capo */
    overflow: visible;
    text-overflow: unset;
    margin-top: 10px;
    margin-bottom: 10px; /* Ridotto la distanza tra il titolo e i pulsanti */

}

/* Pulsante "Mostra tutti" */
.container .row.pt-20 .btn.btn-primary {
    margin-left: 0; /* Allinea il pulsante a sinistra */
    display: inline-flex;
    align-items: center;
}

.container .row.pt-20 .btn.btn-primary i.fa-arrow-right {
    margin-left: 8px; /* Distanza tra il testo e la freccia */
}

/* Media queries per schermi più piccoli */
@media (max-width: 768px) {
    .container .row.pt-20 .title-xsmall-bold.text.u-grey-light {
        font-size: 1rem;
    }

    .container .row.pt-20 .button-group {
        gap: 12px;
    }

    .container .row.pt-20 .button-group a.btn-argomento {
        padding: 8px 14px;
        font-size: 0.9rem;
    }
}
</style>
<?php } ?>