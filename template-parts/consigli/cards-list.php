<?php
global $post;
$prefix = '_dci_consiglio_';

$descrizione_breve = dci_get_meta('descrizione_breve', $prefix, $post->ID);

// Data pubblicazione (se usata)
$arrdata = dci_get_data_pubblicazione_arr("data_pubblicazione", $prefix, $post->ID);

$data_arr = dci_get_data_pubblicazione_arr("data", $prefix, $post->ID);

$date = '';
if ( ! empty($data_arr)
     && is_array($data_arr)
     && count($data_arr) >= 3
     && checkdate( (int) $data_arr[1], (int) $data_arr[0], (int) $data_arr[2] ) ) {
    $timestamp = mktime(0, 0, 0, (int) $data_arr[1], (int) $data_arr[0], (int) $data_arr[2]);
    $date = date_i18n('d F Y', $timestamp);
}
$ora_inizio = dci_get_meta("ora_inizio", $prefix, $post->ID);
$ora_fine   = dci_get_meta("ora_fine",   $prefix, $post->ID);



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
                           <p class="card-text d-none d-md-block">
                                Consiglio Comunale
                            </p>
                            <a class="text-decoration-none" href="<?php echo get_permalink(); ?>">
                                <h3 class="h5 card-title u-grey-light"><?php echo $title ?></h3>
                            </a>
                             
                            <p class="card-text d-none d-md-block">
                                <?php echo $descrizione_breve; ?>
                            </p>

                             <!-- Data e orari con icone -->
                              <div class="row g-2 mb-0 align-items-center" style="font-size: 0.875rem;">
                                <!-- Data -->
                                <div class="col-auto d-flex align-items-center mb-1">
                                  <svg class="icon me-1" aria-hidden="true" width="16" height="16">
                                    <use xlink:href="#it-calendar"></use>
                                  </svg>
                                  <p class="fw-semibold mb-0">
                                    <?php echo !empty($date)
                                      ? '<time datetime="' . esc_attr( date('Y-m-d', strtotime($date)) ) . '">'
                                          . esc_html($date) .
                                        '</time>'
                                      : '—'; ?>
                                  </p>
                                </div>
                              
                                <!-- Ora inizio -->
                                <div class="col-auto d-flex align-items-center mb-1">
                                  <svg class="icon me-1" aria-hidden="true" width="16" height="16">
                                    <use xlink:href="#it-clock"></use>
                                  </svg>
                                  <p class="fw-semibold mb-0">
                                    <?php echo !empty($ora_inizio) ? date_i18n('H:i', strtotime($ora_inizio)) : '—'; ?>
                                  </p>
                                </div>
                              
                                <!-- Ora fine -->
                                <div class="col-auto d-flex align-items-center mb-1">
                                  <svg class="icon me-1" aria-hidden="true" width="16" height="16">
                                    <use xlink:href="#it-clock"></use>
                                  </svg>
                                  <p class="fw-semibold mb-0">
                                    <?php echo !empty($ora_fine) ? date_i18n('H:i', strtotime($ora_fine)) : '—'; ?>
                                  </p>
                                </div>
                              </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
