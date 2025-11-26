<?php 
global $post;
$prefix ='_dci_consiglio_';
$descrizione_breve = dci_get_meta('descrizione_breve',$prefix, $post->ID);
$arrdata = dci_get_data_pubblicazione_arr("data_pubblicazione", $prefix, $post->ID);
$monthName = date_i18n('M', mktime(0, 0, 0, $arrdata[1], 10));


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

if (strlen($descrizione_breve) > 100) {
    $descrizione_breve = substr($descrizione_breve, 0, 97) . '...';
}					
// Controlla se il titolo contiene almeno 5 lettere maiuscole consecutive
if (preg_match('/[A-Z]{5,}/', $descrizione_breve)) {
    // Se sì, lo trasforma in minuscolo con la prima lettera maiuscola
    $descrizione_breve = ucfirst(strtolower($descrizione_breve));
}			
?>
    <div class="col-md-6 col-xl-4">
        <div class="card-wrapper border border-light rounded shadow-sm cmp-list-card-img cmp-list-card-img-hr">
            <div class="card no-after rounded">
                <div class="row g-2 g-md-0 flex-md-column">
                    <div class="col-12 order-1 order-md-2">
                        <div class="card-body card-img-none rounded-top">
                            <!-- <div class="category-top cmp-list-card-img__body">
                                <span class="data"><?php // echo $arrdata[0].' '.strtoupper($monthName).' '.$arrdata[2] ?></span>
                            </div> -->
                            <a class="text-decoration-none" href="<?php echo get_permalink(); ?>">
                                <h3 class="h5 card-title u-grey-light"><?php echo $title ?></h3>
                            </a>
                            <p class="card-text d-none d-md-block">
                                <?php echo $descrizione_breve; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

