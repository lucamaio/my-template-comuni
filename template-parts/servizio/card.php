<?php
global $servizio, $hide_categories;

$prefix = '_dci_servizio_';
$categorie = get_the_terms($servizio->ID, 'categorie_servizio');
$descrizione_breve = dci_get_meta('descrizione_breve', $prefix, $servizio->ID);

if($servizio->post_status == "publish") {
    ?>
    <div class="col-12 col-sm-6 col-lg-4"> 
        <div class="cmp-card-latest-messages card-wrapper" data-bs-toggle="modal" data-bs-target="#">
            <div class="card shadow-sm px-4 pt-4 pb-4 rounded border border-light">
                <?php if (!$hide_categories) { ?>
                <span class="visually-hidden">Categoria:</span>
                <div class="card-header border-0 p-0">
                    <?php if (is_array($categorie) && count($categorie)) {
                        $count = 1;
                        foreach ($categorie as $categoria) {
                            echo $count == 1 ? '' : ' - ';
                            echo '<a class="text-decoration-none title-xsmall-bold mb-2 category text-uppercase" href="'.get_term_link($categoria->term_id).'">';
                            echo $categoria->name ;                                    
                            echo '</a>';
                            ++$count;
                        }
                    }                        
                    ?>
                </div>
                <?php } ?>
                <div class="card-body p-0 my-2">
                    <h3 class="green-title-big t-primary mb-8">
                        <a class="text-decoration-none" href="<?php echo get_permalink($servizio->ID); ?>" data-element="service-link"><?php echo $servizio->post_title; ?></a>
                    </h3>
                    <p class="text-paragraph">
                        <?php echo $descrizione_breve; ?>
                    </p>
                    
                    <?php
                    // Recupero le date del servizio
                    $data_inizio_servizio = dci_get_meta('data_inizio_servizio', $prefix, $servizio->ID);
                    $data_fine_servizio = dci_get_meta('data_fine_servizio', $prefix, $servizio->ID);

                    // Converte le date
                    $startDate = DateTime::createFromFormat('d/m/Y', $data_inizio_servizio);
                    $endDate = $data_fine_servizio ? DateTime::createFromFormat('d/m/Y', $data_fine_servizio) : null;
                    $oggi = new DateTime();

                    // Valuta se il servizio è attivo
                    $stato_attivo = true;
                    if ($startDate && $endDate && $startDate < $endDate) {
                        $stato_attivo = ($oggi >= $startDate && $oggi <= $endDate);
                    }


                    // Verifico lo stato del pulsante generale, se è false lo segnalo 
                    $checkbox_stato = get_post_meta($servizio->ID, '_dci_servizio_stato', true);
                       if ($checkbox_stato == 'false') {
                            $stato_attivo = false;
                       }

                    // Mostra badge di stato
                    echo '<div class="mt-2">';  // Maggiore margine per separarlo dal resto
                    echo '<span class="badge ' . ($stato_attivo ? 'bg-success' : 'bg-danger') . ' text-white">';
                    echo $stato_attivo ? 'Attivo' : 'Non attivo';
                    echo '</span>';
                    echo '</div>';

                    // Mostra periodo se valido
                    if ($startDate && $endDate) {
                        echo '<div class="service-period mt-2">';
                        echo '<small><strong>Periodo:</strong> ' . $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y') . '</small>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    <?php
}
?>

