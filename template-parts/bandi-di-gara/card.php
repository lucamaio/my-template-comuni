<?php
global $prefix;

if ( ! isset( $prefix ) ) {
    $prefix = '_dci_bando_'; 
}

?>

<style>
    .dci-bando-card {
        font-size: .93rem;
    }

    .dci-bando-card .card-body {
        padding: 1rem 1.1rem;
    }

    .dci-bando-card h6.small {
        font-size: .72rem;
        letter-spacing: .04em;
    }

    .dci-bando-card p,
    .dci-bando-card span,
    .dci-bando-card small,
    .dci-bando-card strong {
        font-size: inherit;
        line-height: 1.45;
    }

    .dci-bando-card .text-muted.small,
    .dci-bando-card small.text-muted {
        font-size: .78rem !important;
    }

    .dci-bando-card__oggetto {
        font-size: .84rem;
        line-height: 1.4;
    }

    .dci-bando-card .btn.btn-link.btn-sm {
        font-size: .75rem;
        padding: .2rem 0;
    }

    .dci-bando-card__actions {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: .75rem;
        flex-wrap: wrap;
    }

    .dci-bando-card__bdncp {
        display: inline-flex;
        align-items: center;
        gap: .25rem;
        font-size: .75rem;
        padding: .2rem 0;
        text-decoration: none;
    }

    .dci-bando-card__bdncp:hover {
        text-decoration: underline;
    }

    .dci-bando-card__bdncp svg {
        width: 13px;
        height: 13px;
        flex-shrink: 0;
    }

    @media (max-width: 767.98px) {
        .dci-bando-card {
            font-size: .9rem;
        }

        .dci-bando-card .col-md-10 {
            padding-left: .75rem !important;
        }

        .dci-bando-card .col-md-2.border-end {
            border-right: 0 !important;
            border-bottom: 1px solid var(--bs-border-color-translucent);
            padding-right: 0 !important;
            padding-bottom: .75rem;
            margin-bottom: .75rem;
        }

        .dci-bando-card__actions {
            justify-content: flex-start;
            margin-top: .75rem;
        }
    }
</style>

<div class="card mb-2 rounded-3 bg-body-secondary shadow-sm dci-bando-card">
    <div class="card-body">
        <div class="row g-0">
            <div class="col-md-2 border-end border-light-subtle pe-3">
                <h6 class="text-uppercase text-muted small">Lotto</h6>

                <?php
                $struttura_proponente = trim((string) get_post_meta(get_the_ID(), $prefix . 'struttura_proponente', true));
                $cig = trim((string) get_post_meta(get_the_ID(), $prefix . 'cig', true));
                $link_bdncp = $cig !== '' ? 'https://dati.anticorruzione.it/superset/dashboard/dettaglio_cig/?cig=' . urlencode($cig) : '';
                ?>

                <p class="mb-0">
                    <strong><?php echo $struttura_proponente !== '' ? esc_html($struttura_proponente) : '-'; ?></strong>
                </p>

                <p class="text-muted small mb-0">
                    CIG: <?php echo $cig !== '' ? esc_html($cig) : '-'; ?>
                </p>
            </div>

            <div class="col-md-10 ps-4">
                <div class="row mb-3">
                    <div class="col-12">
                        <h6 class="text-uppercase text-muted small">Oggetto bando</h6>

                        <?php
                        $oggetto = trim(wp_strip_all_tags((string) get_post_meta(get_the_ID(), $prefix . 'oggetto', true)));
                        ?>

                        <p class="mb-0 dci-bando-card__oggetto">
                            <?php echo $oggetto !== '' ? esc_html($oggetto) : '-'; ?>
                        </p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <h6 class="text-uppercase text-muted small">Operatori invitati/partecipanti</h6>

                        <?php
                        $operatori_invitati = get_post_meta(get_the_ID(), $prefix . 'operatori_group', true);
                        $operatori_invitati_presenti = false;

                        if (!empty($operatori_invitati) && is_array($operatori_invitati)) :
                            foreach ($operatori_invitati as $operatore) :
                                $ragione_sociale = isset($operatore['ragione_sociale']) ? trim((string) $operatore['ragione_sociale']) : '';
                                $codice_fiscale  = isset($operatore['codice_fiscale']) ? trim((string) $operatore['codice_fiscale']) : '';

                                if ($ragione_sociale === '' && $codice_fiscale === '') {
                                    continue;
                                }

                                $operatori_invitati_presenti = true;
                        ?>
                                <p class="mb-0">
                                    <strong><?php echo $ragione_sociale !== '' ? esc_html($ragione_sociale) : '-'; ?></strong><br>
                                    <span class="text-muted small"><?php echo $codice_fiscale !== '' ? esc_html($codice_fiscale) : '-'; ?></span>
                                </p>
                        <?php
                            endforeach;
                        endif;

                        if (!$operatori_invitati_presenti) :
                        ?>
                            <p class="mb-0">-</p>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-4">
                        <h6 class="text-uppercase text-muted small">Operatori aggiudicatari</h6>

                        <?php
                        $operatori_aggiudicatari = get_post_meta(get_the_ID(), $prefix . 'aggiudicatari_group', true);
                        $operatori_aggiudicatari_presenti = false;

                        if (!empty($operatori_aggiudicatari) && is_array($operatori_aggiudicatari)) :
                            foreach ($operatori_aggiudicatari as $aggiudicatario) :
                                $ragione_sociale = isset($aggiudicatario['ragione_sociale']) ? trim((string) $aggiudicatario['ragione_sociale']) : '';
                                $codice_fiscale  = isset($aggiudicatario['codice_fiscale']) ? trim((string) $aggiudicatario['codice_fiscale']) : '';

                                if ($ragione_sociale === '' && $codice_fiscale === '') {
                                    continue;
                                }

                                $operatori_aggiudicatari_presenti = true;
                        ?>
                                <p class="mb-0">
                                    <strong><?php echo $ragione_sociale !== '' ? esc_html($ragione_sociale) : '-'; ?></strong><br>
                                    <span class="text-muted small"><?php echo $codice_fiscale !== '' ? esc_html($codice_fiscale) : '-'; ?></span>
                                </p>
                        <?php
                            endforeach;
                        endif;

                        if (!$operatori_aggiudicatari_presenti) :
                        ?>
                            <p class="mb-0">-</p>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-2">
                        <h6 class="text-uppercase text-muted small">Tempi</h6>

                        <p class="mb-0">
                            <small class="text-muted">Data inizio:</small><br>
                            <?php
                            $data_inizio = get_post_meta(get_the_ID(), $prefix . 'data_inizio', true);
                            echo !empty($data_inizio) ? esc_html(date_i18n('d/m/Y', $data_inizio)) : '-';
                            ?>
                        </p>

                        <p class="mb-0">
                            <small class="text-muted">Data fine:</small><br>
                            <?php
                            $data_fine = get_post_meta(get_the_ID(), $prefix . 'data_fine', true);
                            echo !empty($data_fine) ? esc_html(date_i18n('d/m/Y', $data_fine)) : '-';
                            ?>
                        </p>
                    </div>

                    <div class="col-md-2">
                        <h6 class="text-uppercase text-muted small">Importo</h6>

                        <p class="mb-0">
                            <small class="text-muted">Aggiudicato:</small><br>
                            <?php
                            $importo_aggiudicazione = trim((string) get_post_meta(get_the_ID(), $prefix . 'importo_aggiudicazione', true));

                            if ($importo_aggiudicazione !== '') {
                                $importo_aggiudicazione_numeric = floatval(preg_replace('/[^\d.,]+/', '', str_replace(',', '.', $importo_aggiudicazione)));
                                echo $importo_aggiudicazione_numeric !== 0.0 ? esc_html(number_format($importo_aggiudicazione_numeric, 2, ',', '.')) . '€' : '-';
                            } else {
                                echo '-';
                            }
                            ?>
                        </p>

                        <p class="mb-0">
                            <small class="text-muted">Liquidato:</small><br>
                            <?php
                            $somme_liquidate = trim((string) get_post_meta(get_the_ID(), $prefix . 'importo_somme_liquidate', true));

                            if ($somme_liquidate !== '') {
                                $somme_liquidate_numeric = floatval(preg_replace('/[^\d.,]+/', '', str_replace(',', '.', $somme_liquidate)));
                                echo $somme_liquidate_numeric !== 0.0 ? esc_html(number_format($somme_liquidate_numeric, 2, ',', '.')) . '€' : '-';
                            } else {
                                echo '-';
                            }
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
                            if (!empty($term->name)) {
                                $output[] = esc_html($term->name);
                            }
                        }

                        echo !empty($output) ? implode(', ', $output) : '-'; // Se fosse multi-selezione, altrimenti basta $terms[0]->name
                    } else {
                        echo '-'; // Nessun termine assegnato
                    }
                    ?>
                </p>
            </div>

            <div class="col-md-6 text-end">
                <div class="dci-bando-card__actions">
                    <?php if ($link_bdncp !== '') : ?>
                        <a 
                            href="<?php echo esc_url($link_bdncp); ?>" 
                            class="btn btn-link btn-sm dci-bando-card__bdncp"
                            target="_blank" 
                            rel="noopener noreferrer"
                            title="Apri il bando di gara sulla BDNCP"
                            aria-label="Apri il bando di gara sulla BDNCP"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path fill="currentColor" d="M14 3h7v7h-2V6.41l-9.29 9.3-1.42-1.42 9.3-9.29H14V3ZM5 5h6v2H7v10h10v-4h2v6H5V5Z"/>
                            </svg>
                            <span>Link BDNCP</span>
                        </a>
                    <?php endif; ?>

                    <a href="<?php echo esc_url(get_permalink()); ?>" class="btn btn-link btn-sm">
                        Clicca qui per consultare il dettaglio
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>