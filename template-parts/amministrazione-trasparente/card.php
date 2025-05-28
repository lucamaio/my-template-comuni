<?php
global $elemento;

$prefix = '_dci_elemento_trasparenza_';

// Metadati
$descrizione_breve = dci_get_meta('descrizione_breve', $prefix, $elemento->ID);
$ck_target         = dci_get_meta('open_in_new_tab', $prefix, $elemento->ID) === 'on';
$ck_link           = dci_get_meta('open_direct', $prefix, $elemento->ID) === 'on';
$url               = dci_get_meta('url', $prefix, $elemento->ID);
$documento         = dci_get_meta('file', $prefix, $elemento->ID);

// Inizializzazione del link
if ($ck_link) {
    // Link diretto (URL o file)
    if (!empty($url)) {
        $link = esc_url($url); // URL assoluto
    } elseif (!empty($documento)) {
        $link = esc_url($documento); // Es. URL file
    } else {
        $link = '#'; // fallback se mancano entrambi
    }
} else {
    // Link alla scheda del post
    $link = get_permalink($elemento->ID);
}

if ($elemento->post_status === "publish") :
?>
    <div class="cmp-card-latest-messages card-wrapper" data-bs-toggle="modal" data-bs-target="#">
        <div class="card shadow-sm px-4 pt-4 pb-4 rounded border border-light">
            <span class="visually-hidden">Categoria:</span>
            <div class="card-header border-0 p-0">
                <?php
                $categorie = get_the_terms($elemento->ID, 'tipi_cat_amm_trasp');
                if ($categorie && !is_wp_error($categorie)) {
                    foreach ($categorie as $cat) {
                        echo '<span class="badge bg-primary me-2">' . esc_html($cat->name) . '</span>';
                    }
                }
                ?>
            </div>
            <div class="card-body p-0 my-2">
                <h3 class="green-title-big t-primary mb-8">
                    <a class="text-decoration-none"
                       href="<?php echo esc_url($link); ?>"
                       <?php echo $ck_target ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>
                       data-element="service-link">
                        <?php echo esc_html($elemento->post_title); ?>
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
