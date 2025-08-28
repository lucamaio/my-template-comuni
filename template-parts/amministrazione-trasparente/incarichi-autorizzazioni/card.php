<?php
global $prefix;

if ( ! isset( $prefix ) ) {
    $prefix = '_dci_icad_';
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
                        <small class="text-uppercase text-muted d-block">Anno di Conferimento</small>
                        <?php
                        $anno_conferimento = get_post_meta(get_the_ID(), $prefix . 'anno_conferimento', true);
                        $anno_conferimento_formatted = !empty($anno_conferimento) ? date_i18n('Y', intval($anno_conferimento)) : '-';
                        ?>
                        <span class="d-block"><?php echo esc_html($anno_conferimento_formatted); ?></span>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <small class="text-uppercase text-muted d-block">Soggetto dichiarante</small>
                        <?php
                        $soggetto_dichiarante = get_post_meta(get_the_ID(), $prefix . 'soggetto_dichiarante', true);
                        ?>
                        <span class="d-block"><?php echo !empty($soggetto_dichiarante) ? esc_html($soggetto_dichiarante) : '-'; ?></span>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <small class="text-uppercase text-muted d-block">Soggetto percettore</small>
                        <?php
                        $soggetto_percettore = get_post_meta(get_the_ID(), $prefix . 'soggetto_percettore', true);
                        ?>
                        <span class="d-block"><?php echo !empty($soggetto_percettore) ? esc_html($soggetto_percettore) : '-'; ?></span>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <small class="text-uppercase text-muted d-block">Dirigente/Non Dirigente</small>
                        <?php
                        $dirigente = get_post_meta(get_the_ID(), $prefix . 'dirigente_non_dirigente', true);
                        ?>
                        <span class="d-block"><?php echo !empty($dirigente) ? esc_html($dirigente) : '-'; ?></span>
                    </div>
                </div>

                <div class="row align-items-center mb-3">
                    <div class="col-md-3 col-sm-6">
                        <small class="text-uppercase text-muted d-block">Soggetto Conferente</small>
                        <?php
                        $soggetto_conferente = get_post_meta(get_the_ID(), $prefix . 'soggetto_conferente', true);
                        ?>
                        <span class="d-block"><?php echo !empty($soggetto_conferente) ? esc_html($soggetto_conferente) : '-'; ?></span>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <small class="text-uppercase text-muted d-block">Data conferimento autorizzazione</small>
                        <?php
                        $data_conferimento = get_post_meta(get_the_ID(), $prefix . 'data_conferimento_autorizzazione', true);
                        echo $data_conferimento ? date_i18n('j F Y', intval($data_conferimento)) : '-';
                        ?>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <small class="text-uppercase text-muted d-block">Durata</small>
                        <?php
                        $durata = get_post_meta(get_the_ID(), $prefix . 'durata', true);
                        ?>
                        <span class="d-block"><?php echo !empty($durata) ? esc_html($durata) : '-'; ?></span>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <small class="text-uppercase text-muted d-block">Compenso Lordo</small>
                        <?php
                        $compenso = get_post_meta(get_the_ID(), $prefix . 'compenso_lordo', true);
                        $compenso_numeric = floatval(str_replace(',', '.', preg_replace('/[^\d,]+/', '', $compenso)));
                        ?>
                        <span class="d-block"><?php echo $compenso_numeric !== 0.0 ? esc_html(number_format($compenso_numeric, 2, ',', '.')) . '€' : '-'; ?></span>
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
