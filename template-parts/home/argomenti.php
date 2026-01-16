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

// check visualizza immagine:
$check_immagini = dci_get_option('ch_show_sfondo_argomenti','homepage');

$img_default = get_template_directory_uri() . '/assets/img/bg_placeholder-blu.png';
$img_ricavata = dci_get_option('immagine-argomenti','homepage');
$img = isset($img_ricavata) && !empty($img_ricavata) & $img_ricavata !==  null ? $img_ricavata : $img_default;
?>

<?php if (!empty($argomenti_evidenza)) { 
   if(isset($check_immagini) && !empty($check_immagini) && $check_immagini === 'true' && $img != null) {?>
<div class="it-hero-wrapper it-wrapped-container py-4 argomenti-evidenza-bg">
    <div class="container">
        <div class="row"> 
            <h2 class="text-white title-xlarge mb-3">Argomenti in Evidenza</h2> 
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
    </div>
</div>
<?php } else{ ?>
     <div class="container">
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
    </div>
<?php } }?>

<div class="container">
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
/* Sfondo sezione Argomenti in Evidenza */
.argomenti-evidenza-bg {
    background-image: url('<?= $img ?>');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    position: relative;
    padding: 60px 0;
    overflow: hidden; /* Assicura che il pseudo-elemento non esca dai bordi */
}

/* Overlay sfocato e opaco */
.argomenti-evidenza-bg::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.2); /* Opacità nera: 0.4 puoi regolare */
    /* backdrop-filter: blur(0.1rem);  */
    z-index: 1;
}

/* Contenuto sopra l'overlay */
.argomenti-evidenza-bg > * {
    position: relative;
    z-index: 2;
}


/* Allineamento degli elementi dentro la riga */
.container .row.pt-30 {
    text-align: left; 
}

/* Gruppo di pulsanti "Altri argomenti" */
.container .row.pt-30 .button-group {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    padding: 0;
    margin: 0;
    justify-content: flex-start;
}

/* Pulsanti "Altri argomenti" */
.container .row.pt-30 .button-group a.btn-argomento {
    display: inline-block;
    padding: 10px 16px;
    background-color: #ffffff;
    color: #333;
    font-size: 1rem;
    font-weight: 500;
    border: 2px solid #dcdcdc;
    margin-top: -5px;
    border-radius: 8px; 
    text-decoration: none;
    text-align: center;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

/* Hover pulsanti */
.container .row.pt-30 .button-group a.btn-argomento:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}

/* Titolo "Altri argomenti" */
.container .row.pt-30 h3.title-xsmall-bold.text.u-grey-light {
    font-size: 1.2rem;
    font-weight: 600;
    color: #333;
    letter-spacing: 0.5px;
    margin-top: 10px;
    margin-bottom: 10px;
}

/* Pulsante "Mostra tutti" */
.container .row.pt-30 .btn.btn-primary {
    margin-left: 0;
    display: inline-flex;
    align-items: center;
}

/* Media queries */
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
