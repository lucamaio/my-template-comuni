<?php
global $prefix;

if ( ! isset( $prefix ) ) {
    $prefix = '_dci_atto_concessione_';
}
?>

<div class="card mb-2 rounded-3 bg-body-secondary shadow-sm">
    <div class="card-body">
        <div class="row g-0">
            <div class="col-md-12 ps-4">
                <div class="row mb-3">
                    <div class="col-12">
                        <h6 class="text-uppercase text-muted small">Titolo/Norma</h6>
                        <p class="mb-0"><strong><?php the_title(); ?></strong></p>
                    </div>
                </div>
                <div class="row align-items-center mb-3">
                    <div class="col-md-3 col-sm-6">
                        <small class="text-uppercase text-muted d-block">Beneficiario</small>
                        <?php
                        $rag_soc = get_post_meta(get_the_ID(), $prefix . 'ragione_sociale', true);
                        $cod_fisc = get_post_meta(get_the_ID(), $prefix . 'codice_fiscale', true);

                        $codice_fiscale = !empty($cod_fisc) ? $cod_fisc : 'Non specificato';
                        $ragione_sociale = !empty($rag_soc) ? $rag_soc : 'Non specificato';
                        ?>
                        <span class="d-block"><?php echo esc_html($ragione_sociale); ?></span>
                        <span class="text-muted small d-block"><?php echo esc_html($codice_fiscale); ?></span>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <small class="text-uppercase text-muted d-block">Responsabile</small>
                        <?php
                        $responsabile = !empty(get_post_meta(get_the_ID(), $prefix . 'responsabile', true)) ? get_post_meta(get_the_ID(), $prefix . 'responsabile', true) : "Non specificato";
                        ?>
                        <span class="d-block"><?php echo esc_html($responsabile); ?></span>
                    </div>

                    <div class="col-md-2 col-sm-6">
                        <small class="text-uppercase text-muted d-block">Tempi</small>
                        <?php
                        $post_date = get_the_date('j F Y', get_the_ID());
                        $anno_beneficio = get_post_meta(get_the_ID(), $prefix . 'anno_beneficio', true);
                        $formatted_anno_beneficio = !empty($anno_beneficio) ? date_i18n('Y', $anno_beneficio) : '-';
                        ?>
                        <span class="d-block">Pubblicato: <?php echo $post_date ? esc_html($post_date) : '-'; ?></span>
                        <span class="d-block">Beneficio: <?php echo $formatted_anno_beneficio; ?></span>
                    </div>

                    <div class="col-md-2 col-sm-6">
                        <small class="text-uppercase text-muted d-block">Importo</small>
                        <?php
                        $importo = get_post_meta(get_the_ID(), $prefix . 'importo', true);
                        $importo_numeric = floatval(str_replace(',', '.', preg_replace('/[^\d,]+/', '', $importo)));
                        ?>
                        <span class="d-block"><?php echo $importo_numeric !== 0.0 ? esc_html(number_format($importo_numeric, 2, ',', '.')) . '€' : '-'; ?></span>
                    </div>

                    <div class="col-md-2 col-sm-12">
                        <small class="text-uppercase text-muted d-block">Ragione dell'incarico</small>
                        <?php
                        $rag_incarico = get_post_meta(get_the_ID(), $prefix . 'rag_incarico', true);
                        ?>
                        <span class="d-block"><?php echo !empty($rag_incarico) ? esc_html($rag_incarico) : '-'; ?></span>
                    </div>
                </div>
                </div>
        </div>
        <div class="row mt-3 pt-3 border-top border-light-subtle">
            <div class="col-md-6">
                <h6 class="text-uppercase text-muted small">Allegati</h6>
                <p class="mb-0">
                    <?php
                    $allegati = get_post_meta(get_the_ID(), $prefix . 'allegati', true);

                    if (!empty($allegati) && is_array($allegati)) {
                        $i = 1;
                        foreach ($allegati as $file_id => $file_data) {
                            // Forza l’uso dell’ID se disponibile
                            $attachment_id = intval($file_data['id'] ?? $file_id);
                            $file_url = wp_get_attachment_url($attachment_id);
                            $file_title = get_the_title($attachment_id);

                            if (!$file_url) continue; // Salta se l'allegato non ha URL

                            // Fallback in caso di titolo vuoto
                            if (empty($file_title)) {
                                $file_title = 'Allegato ' . $i;
                            }
                    ?>
                            <span class="d-inline-flex align-items-center mb-2 me-3">
                                <svg class="icon icon-sm me-1" aria-hidden="true">
                                    <use href="#it-file"></use>
                                </svg>
                                <span class="text fw-semibold">
                                    <a class="text-decoration-none" href="<?php echo esc_url($file_url); ?>" target="_blank" rel="noopener noreferrer">
                                        <?php echo esc_html($file_title); ?>
                                    </a>
                                </span>
                            </span>
                    <?php
                            $i++;
                        }
                    } else {
                        echo 'Nessun Allegato';
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
