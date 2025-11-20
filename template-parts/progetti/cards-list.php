<?php 
global $post;

$nome_misura = dci_get_meta('nome_misura','_dci_progetto_', $post->ID);
$arrdata = dci_get_data_pubblicazione_arr("data_pubblicazione", '_dci_progetto_', $post->ID);
$monthName = date_i18n('M', mktime(0, 0, 0, $arrdata[1], 10));
$img = dci_get_meta('immagine', '_dci_progetto_', $post->ID);
$tipo_terms = get_the_terms($post->ID, 'tipi_progetto');

if ($tipo_terms && !is_wp_error($tipo_terms)) {
    $tipo = $tipo_terms[0];
} else {
    $tipo = null;
}

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

if (strlen($nome_misura) > 100) {
    $nome_misura = substr($nome_misura, 0, 97) . '...';
}					
// Controlla se il titolo contiene almeno 5 lettere maiuscole consecutive
if (preg_match('/[A-Z]{5,}/', $nome_misura)) {
    // Se sì, lo trasforma in minuscolo con la prima lettera maiuscola
    $nome_misura = ucfirst(strtolower($nome_misura));
}			


if ($img) {
?>
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card-wrapper border border-light rounded shadow-sm cmp-list-card-img cmp-list-card-img-hr">
            <div class="card no-after rounded">
            <div class="row g-2 g-md-0 flex-md-column">
                <div class="row g-2 g-md-0 flex-md-column">
                    <?php dci_get_img($img, 'rounded-top img-fluid img-responsive'); ?>
                </div>
                <div class="col-12 order-1 order-md-2">
                <div class="card-body">
                    <div class="category-top cmp-list-card-img__body">
                    <?php if ($tipo){?>
                            <svg class="icon">
                                <use xlink:href="#it-file"/>
                            </svg>
                            <a class="category text-decoration-none" href="<?php echo get_term_link($tipo->term_id); ?>">
                                <?php echo strtoupper($tipo->name); ?>
                            </a>
                    <?php } ?>
                    <span class="data"><?php echo $arrdata[0].' '.strtoupper($monthName).' '.$arrdata[2] ?></span>
                    </div>
                    <a class="text-decoration-none" href="<?php echo get_permalink(); ?>">
                        <h3 class="h5 card-title u-grey-light">
                            <?php echo $title; ?>
                        </h3>
                    </a>
                    <p class="card-text d-none d-md-block">
                        <?php echo $nome_misura; ?>
                    </p>
                </div>
                </div>
            </div>
            </div>
        </div>
    </div>
<?php } else { ?>
    <div class="col-md-6 col-xl-4">
        <div class="card-wrapper border border-light rounded shadow-sm cmp-list-card-img cmp-list-card-img-hr">
            <div class="card no-after rounded">
                <div class="row g-2 g-md-0 flex-md-column">
                    <div class="col-12 order-1 order-md-2">
                        <div class="card-body card-img-none rounded-top">
                            <div class="category-top cmp-list-card-img__body">
                            <?php if ($tipo){?>
                               
                                <svg class="icon">
                                    <use xlink:href="#it-file"/>
                                </svg>
  
                                <a class="category text-decoration-none" href="<?php echo get_term_link($tipo->term_id); ?>">
                                 <?php echo strtoupper($tipo->name); ?>
                             </a>
                            <?php } ?>
                                <span class="data"><?php echo $arrdata[0].' '.strtoupper($monthName).' '.$arrdata[2] ?></span>
                            </div>
                            <a class="text-decoration-none" href="<?php echo get_permalink(); ?>">
                                <h3 class="h5 card-title u-grey-light"><?php echo $title ?></h3>
                            </a>
                            <p class="card-text d-none d-md-block">
                                <?php echo $nome_misura; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
