<?php
global $prefix;

if ( ! isset( $prefix ) ) {
    $prefix = '_dci_bando_'; 
}

?>

<div class="card mb-2 rounded-3 bg-body-secondary shadow-sm">
    <div class="card-body">
        <div class="row g-0">
            <div class="col-md-2 border-end border-light-subtle pe-3">
                <h6 class="text-uppercase text-muted small">Lotto</h6>
                <p class="mb-0"><strong><?php echo esc_html(get_post_meta(get_the_ID(), $prefix . 'struttura_proponente', true)); ?></strong></p>
                <p class="text-muted small mb-0">CIG: <?php echo esc_html(get_post_meta(get_the_ID(), $prefix . 'cig', true)); ?></p>
            </div>
            <div class="col-md-10 ps-4">
                <div class="row mb-3">
                    <div class="col-12">
                        <h6 class="text-uppercase text-muted small">Oggetto bando</h6>
                        <p class="mb-0"><?php echo wp_kses_post(get_post_meta(get_the_ID(), $prefix . 'oggetto', true)); ?></p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <h6 class="text-uppercase text-muted small">Operatori invitati/partecipanti</h6>
                        <?php
                        $operatori_invitati = get_post_meta(get_the_ID(), $prefix . 'operatori_group', true);

                        if (!empty($operatori_invitati) && is_array($operatori_invitati)) :
                            foreach ($operatori_invitati as $operatore) :
                                $ragione_sociale = isset($operatore['ragione_sociale']) ? $operatore['ragione_sociale'] : '';
                                $codice_fiscale = isset($operatore['codice_fiscale']) ? $operatore['codice_fiscale'] : '';
                        ?>
                                <p class="mb-0">
                                    <strong><?php echo esc_html($ragione_sociale); ?></strong><br>
                                    <span class="text-muted small"><?php echo esc_html($codice_fiscale); ?></span>
                                </p>
                        <?php
                            endforeach;
                        else :
                            echo '<p class="mb-0">Non definiti</p>';
                        endif;
                        ?>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-uppercase text-muted small">Operatori aggiudicatari</h6>
                        <?php
                        $operatori_aggiudicatari = get_post_meta(get_the_ID(), $prefix . 'aggiudicatari_group', true);

                        if (!empty($operatori_aggiudicatari) && is_array($operatori_aggiudicatari)) :
                            foreach ($operatori_aggiudicatari as $aggiudicatario) :
                                $ragione_sociale = isset($aggiudicatario['ragione_sociale']) ? $aggiudicatario['ragione_sociale'] : '';
                                $codice_fiscale = isset($aggiudicatario['codice_fiscale']) ? $aggiudicatario['codice_fiscale'] : '';
                        ?>
                                <p class="mb-0">
                                    <strong><?php echo esc_html($ragione_sociale); ?></strong><br>
                                    <span class="text-muted small"><?php echo esc_html($codice_fiscale); ?></span>
                                </p>
                        <?php
                            endforeach;
                        else :
                            echo '<p class="mb-0">Non definiti</p>';
                        endif;
                        ?>
                    </div>
                    <div class="col-md-2">
                        <h6 class="text-uppercase text-muted small">Tempi</h6>
                        <p class="mb-0">
                            <small class="text-muted">Data inizio:</small><br>
                            <?php
                            $data_inizio = get_post_meta(get_the_ID(), $prefix . 'data_inizio', true);
                            echo $data_inizio ? date_i18n('d/m/Y', $data_inizio) : '-';
                            ?>
                        </p>
                        <p class="mb-0">
                            <small class="text-muted">Data fine:</small><br>
                            <?php
                            $data_fine = get_post_meta(get_the_ID(), $prefix . 'data_fine', true);
                            echo $data_fine ? date_i18n('d/m/Y', $data_fine) : '-';
                            ?>
                        </p>
                    </div>
                    <div class="col-md-2">
                        <h6 class="text-uppercase text-muted small">Importo</h6>
                        <p class="mb-0">
                            <small class="text-muted">Aggiudicato:</small><br>
                            <?php
                            $importo_aggiudicazione = get_post_meta(get_the_ID(), $prefix . 'importo_aggiudicazione', true);
                            $importo_aggiudicazione_numeric = floatval(preg_replace('/[^\d.,]+/', '', str_replace(',', '.', $importo_aggiudicazione)));
                            echo $importo_aggiudicazione_numeric !== 0.0 ? esc_html(number_format($importo_aggiudicazione_numeric, 2, ',', '.')) . '€' : '-';
                            ?>
                        </p>
                        <p class="mb-0">
                            <small class="text-muted">Liquidato:</small><br>
                            <?php
                            $somme_liquidate = get_post_meta(get_the_ID(), $prefix . 'importo_somme_liquidate', true);
                            $somme_liquidate_numeric = floatval(preg_replace('/[^\d.,]+/', '', str_replace(',', '.', $somme_liquidate)));
                            echo $somme_liquidate_numeric !== 0.0 ? esc_html(number_format($somme_liquidate_numeric, 2, ',', '.')) . '€' : '-';
                            ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-3 pt-3 border-top border-light-subtle">
            <div class="col-md-6">
                <h6 class="text-uppercase text-muted small">Procedura scelta contraente</h6>
                <p class="mb-0">
                    <?php
                    $terms = get_the_terms(get_the_ID(), 'tipi_procedura_contraente'); // Usa il nome corretto della tassonomia
                    if (!empty($terms) && !is_wp_error($terms)) {
                        $output = array();
                        foreach ($terms as $term) {
                            $output[] = esc_html($term->name);
                        }
                        echo implode(', ', $output); // Se fosse multi-selezione, altrimenti basta $terms[0]->name
                    } else {
                        echo '-'; // Nessun termine assegnato
                    }
                    ?>
                </p>
            </div>
            <div class="col-md-6 text-end">
                <a href="<?php echo esc_url(get_permalink()); ?>" class="btn btn-link btn-sm">Clicca qui per consultare il dettaglio</a>
            </div>
        </div>
    </div>
</div>
