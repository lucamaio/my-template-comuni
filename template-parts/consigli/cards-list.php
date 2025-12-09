<?php
global $post;
$prefix = '_dci_consiglio_';

$descrizione_breve = dci_get_meta('descrizione_breve', $prefix, $post->ID);

// Data pubblicazione (se usata)
$arrdata = dci_get_data_pubblicazione_arr("data_pubblicazione", $prefix, $post->ID);
$data_arr = dci_get_data_pubblicazione_arr("data", $prefix, $post->ID);

$date = '';
if (!empty($data_arr) && is_array($data_arr) && count($data_arr) >= 3 && checkdate((int)$data_arr[1], (int)$data_arr[0], (int)$data_arr[2])) {
    $timestamp = mktime(0, 0, 0, (int)$data_arr[1], (int)$data_arr[0], (int)$data_arr[2]);
    $date = date_i18n('d F Y', $timestamp);
}

$ora_inizio = dci_get_meta("ora_inizio", $prefix, $post->ID);
$ora_fine   = dci_get_meta("ora_fine", $prefix, $post->ID);

$title = get_the_title();
// Troncamento titolo e trasformazione maiuscole
if (strlen($title) > 100) {
    $title = substr($title, 0, 97) . '...';
}
if (preg_match('/[A-Z]{5,}/', $title)) {
    $title = ucfirst(strtolower($title));
}

if (strlen($descrizione_breve) > 100) {
    $descrizione_breve = substr($descrizione_breve, 0, 97) . '...';
}
if (preg_match('/[A-Z]{5,}/', $descrizione_breve)) {
    $descrizione_breve = ucfirst(strtolower($descrizione_breve));
}
?>

<div class="col-md-6 col-xl-4">
    <div class="card-wrapper border border-light card-rounded shadow-sm cmp-list-card-img cmp-list-card-img-hr">
        <div class="card no-after card-rounded">
            <div class="row g-2 g-md-0 flex-md-column">
                <div class="col-12 order-1 order-md-2">
                    <div class="card-body card-img-none rounded-top">
                        <h4 class="h6 mb-2 text-secondary d-none d-md-block">
                           <!-- <svg class="icon me-1" aria-hidden="true" width="8" height="8">
                                <use xlink:href="#it-file"></use>
                            </svg>                           -->
                        Consiglio Comunale</h4>
                        <a class="text-decoration-none" href="<?php echo get_permalink(); ?>" alt="<?php echo esc_attr($title); ?>">
                            <h3 class="h5 card-title u-grey-light"><?php echo $title; ?></h3>
                        </a>
                        <p class="card-text d-none d-md-block"><?php echo $descrizione_breve; ?></p>

                        <!-- Data e orari con icone -->
                        <div class="row g-2 mb-0 align-items-center" style="font-size: 0.875rem;">
                            <!-- Data -->
                            <div class="col-auto d-flex align-items-center mb-1 text-primary">
                                <svg class="icon me-1" aria-hidden="true" width="16" height="16">
                                    <use xlink:href="#it-calendar"></use>
                                </svg>
                                <span class="fw-semibold small mb-0">
                                    <?php echo !empty($date)
                                        ? '<time datetime="' . esc_attr(date('Y-m-d', strtotime($date))) . '">' . esc_html($date) . '</time>'
                                        : '—'; ?>
                                </span>
                            </div>

                            <!-- Ora inizio -->
                            <div class="col-auto d-flex align-items-center mb-1 text-success">
                                <svg class="icon me-1" aria-hidden="true" width="16" height="16">
                                    <use xlink:href="#it-clock"></use>
                                </svg>
                                <span class="fw-semibold small mb-0">
                                    <?php echo !empty($ora_inizio) ? date_i18n('H:i', strtotime($ora_inizio)) : '—'; ?>
                                </span>
                            </div>

                            <!-- Ora fine -->
                            <div class="col-auto d-flex align-items-center mb-1 text-danger">
                                <svg class="icon me-1" aria-hidden="true" width="16" height="16">
                                    <use xlink:href="#it-clock"></use>
                                </svg>
                                <span class="fw-semibold small mb-0">
                                    <?php echo !empty($ora_fine) ? date_i18n('H:i', strtotime($ora_fine)) : '—'; ?>
                                </span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>