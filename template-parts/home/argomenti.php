<?php
global $argomento_full, $count;
$argomenti_evidenza = array();
for ($i = 1; $i <= 9; $i++) {
    $argomento = dci_get_option('argomenti_evidenziati_' . $i, 'homepage')[0] ?? null;
    if ($argomento) {
        $argomenti_evidenza[$i] = $argomento;       
    }
}
$altri_argomenti = dci_get_option('argomenti_altri','homepage');
?>

<div class="container">
    <?php if (!empty($argomenti_evidenza)) { ?>
    <div class="row"> 
        <h2 class="text-black title-xlarge mb-3">Argomenti in Evidenza</h2> 
    </div>
    <div>
        <div class="card-wrapper card-teaser-wrapper card-teaser-wrapper-equal card-teaser-block-3">
            <?php
            if(is_array($argomenti_evidenza)) {
                foreach ($argomenti_evidenza as $key => $argomento_full) {
                    $count = $key;
                    if ($argomento_full && isset($argomento_full['argomento_'.$count.'_argomento'])) {
                        get_template_part("template-parts/home/scheda-argomento");
                    }
                } 
            } ?>
        </div>
    </div>
    <?php } ?>

    <?php if ($altri_argomenti) { ?>
    <div class="row pt-30">
        <div class="col-12">
            <h2 class="mb-2 title-xsmall-bold text u-grey-light" ><font color="black" size="3">Altri argomenti</font></h2>
        </div>
    </div>

    <div class="row pt-30">
        <div class="col-12">
            <div class="button-group">
                
                        <?php
                        // Ciclo per gli altri argomenti
                        foreach ($altri_argomenti as $i => $arg_id) {
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

                    <a href="<?php echo dci_get_template_page_url('page-templates/argomenti.php'); ?>"  class="btn btn-primary" style="margin-left: 40px;">
                        Mostra tutti
                    </a>

                <br>
            </div>
        </div>

    </div>
    <?php } ?>
</div>
<br><br>
<style>
/* Allineamento degli elementi dentro la riga */
.container .row.pt-30 {
    text-align: left; /* Allinea il testo e gli altri contenuti a sinistra */
}

/* Gruppo di pulsanti "Altri argomenti" */
.container .row.pt-30 .button-group {
    display: flex;
    flex-wrap: wrap;
    gap: 16px; /* Spazio tra i pulsanti */
    padding: 0;
    margin: 0;
    justify-content: flex-start; /* Allinea i pulsanti a sinistra */
}

/* Gruppo di pulsanti "Altri argomenti" - Evita che i pulsanti occupino l'intera larghezza */
.container .row.pt-30 .button-group a.btn-argomento {
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
.container .row.pt-30 .button-group a.btn-argomento:hover {
    background-color: #ffffff;
    color: #333;
    border-color: #dcdcdc;
    transform: translateY(-4px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}

/* Titolo "Altri argomenti" sopra il container */
.container .row.pt-30 h3.title-xsmall-bold.text.u-grey-light {
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
.container .row.pt-30 .btn.btn-primary {
    margin-left: 0; /* Allinea il pulsante a sinistra */
    display: inline-flex;
    align-items: center;
}

.container .row.pt-30 .btn.btn-primary i.fa-arrow-right {
    margin-left: 8px; /* Distanza tra il testo e la freccia */
}

/* Media queries per schermi più piccoli */
@media (max-width: 768px) {
    .container .row.pt-30 .title-xsmall-bold.text.u-grey-light {
        font-size: 1rem;
    }

    .container .row.pt-30 .button-group {
        gap: 12px;
    }

    .container .row.pt-30 .button-group a.btn-argomento {
        padding: 8px 14px;
        font-size: 0.9rem;
    }
}
</style>

