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
                <h6 class="text-uppercase text-muted small">Soggeto</h6>
                <p class="mb-0"><strong><?php echo esc_html(get_post_meta(get_the_ID(), $prefix . 'soggetto', true)); ?></strong></p>
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
                        <h6 class="text-uppercase text-muted small">Atto di conferimento incarico</h6>
                        <p class="mb-0"><?php echo wp_kses_post(get_post_meta(get_the_ID(), $prefix . 'atto_conferimento_incarico', true)); ?></p>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-uppercase text-muted small">Compenso</h6>
                        <p class="mb-0"><?php echo wp_kses_post(get_post_meta(get_the_ID(), $prefix . 'compenso', true)); ?></p>
                    </div>
                    <div class="col-md-2">
                        <h6 class="text-uppercase text-muted small">Durata</h6>
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
                <h6 class="text-uppercase text-muted small">Curriculum</h6>
                 <p class="mb-0">
                    <?php
                    $curriculum = get_post_meta(get_the_ID(), $prefix . 'cv_allegati', true);

                    if (!empty($curriculum) && is_array($curriculum)) {
                        $i = 1;
                        foreach ($curriculum as $file_id => $file_data) {
                            // Forza l’uso dell’ID se disponibile
                            $attachment_id1 = intval($file_data['id'] ?? $file_id);
                            $file_url1 = wp_get_attachment_url($attachment_id);
                            $file_title1 = get_the_title($attachment_id);

                            if (!$file_url1) continue; // Salta se l'allegato non ha URL

                            // Fallback in caso di titolo vuoto
                            if (empty($file_title1)) {
                                $file_title1 = 'curriculum ' . $i;
                            }
                    ?>
                     <span class="d-inline-flex align-items-center mb-2 me-3">
                                <svg class="icon icon-sm me-1" aria-hidden="true">
                                    <use href="#it-file"></use>
                                </svg>
                                <span class="text fw-semibold">
                                    <a class="text-decoration-none" href="<?php echo esc_url($file_url1); ?>" target="_blank" rel="noopener noreferrer">
                                        <?php echo esc_html($file_title1); ?>
                                    </a>
                                </span>
                            </span>
                    <?php
                            $i++;
                        }
                    } else {
                        echo 'Nessun Curriculum';
                    }
                    ?>
                 </p>
            </div>
        </div>
    </div>
    </div>
</div>
