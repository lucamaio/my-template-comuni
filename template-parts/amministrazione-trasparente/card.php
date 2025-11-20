<?php
global $elemento;

$prefix = '_dci_elemento_trasparenza_';

// Metadati
$descrizione_breve = dci_get_meta('descrizione_breve', $prefix, $elemento->ID);
$ck_target         = dci_get_meta('open_in_new_tab', $prefix, $elemento->ID) === 'on';
$ck_link           = dci_get_meta('open_direct', $prefix, $elemento->ID) === 'on';
$url               = dci_get_meta('url', $prefix, $elemento->ID);

$documenti         = dci_get_meta('file', $prefix, $elemento->ID);
$documento = is_array($documenti) && !empty($documenti) ? get_permalink($elemento->ID) : $documenti;
$data= get_the_date('j F Y', $elemento->ID);
$arrayDataPubblicazione = dci_get_data_pubblicazione_arr("data_pubblicazione", $prefix, $elemento->ID); 

// Mese scritto per intero con prima lettera maiuscola
$monthNamePubblicazione = ucfirst(strtolower(date_i18n('F', mktime(0, 0, 0, $arrayDataPubblicazione[1], 10))));

// Gestione anno a 4 cifre
$yearTwoDigits = intval($arrayDataPubblicazione[2]);
if ($yearTwoDigits < 100) {
    $yearFull = 2000 + $yearTwoDigits;
} else {
    $yearFull = $yearTwoDigits;
}

$ck_sowh_section = dci_get_option("ck_show_section", "Trasparenza");

if($ck_link && !empty($url)){
     $link = esc_url($url);
}else if($ck_link && !empty($documento)){
    $link = esc_url($documento);
}else{
    $link = get_permalink($elemento->ID);
}

if ($elemento->post_status === "publish") :
    $title=$elemento->post_title;
?>
<div class="cmp-card-latest-messages card-wrapper" data-bs-toggle="modal" data-bs-target="#">
    <div class="card shadow-sm px-4 pt-4 pb-4 rounded border border-light">
        <span class="visually-hidden">Categoria:</span>
        <div class="card-header border-0 p-0">
            <?php if ($ck_sowh_section === 'true') {?>
            <?php
                    $categorie = get_the_terms($elemento->ID, 'tipi_cat_amm_trasp');
                    if ($categorie && !is_wp_error($categorie)) {
                        foreach ($categorie as $cat) {
                            echo '<span class="badge bg-secondary me-2">' . esc_html($cat->name) . ' -  </span> -';
                        }
                    }
                }?>

            <span class="data">
                <?php echo($data);?>
                <?php //echo $arrayDataPubblicazione[0] . ' ' . $monthNamePubblicazione . ' ' . $yearFull; ?>
            </span>
            <!-- 
            <?php /* if($arrayDataPubblicazione[0]!=$arrayDataScadenza[0]) { ?>
                - <span class="data"><?php echo $arrayDataScadenza[0].' '.strtoupper($monthNameScadenza).' '.$arrayDataScadenza[2] ?></span>
            <?php } */ ?>
            -->
        </div>

        <div class="card-body p-0 my-2">
            <h3 class="green-title-big t-primary mb-8">
                <a class="text-decoration-none" href="<?php echo esc_url($link); ?>"
                    <?php echo $ck_target ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>
                    data-element="service-link">

                    <?php
                        if (preg_match('/[A-Z]{5,}/', $title)) {
                         //   echo esc_html($url); // stampa solo il testo dell'URL
                            $titolo_documento = ucfirst(strtolower($title));
                        } else {
                            $titolo_documento = $title;
                        }
                    ?>

                    <?php echo esc_html($titolo_documento); ?>
                </a>
            </h3>

            <?php if (!empty($descrizione_breve)) : ?>
            <p class="text-paragraph">
                <?php echo esc_html($descrizione_breve); ?>
            </p>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>
