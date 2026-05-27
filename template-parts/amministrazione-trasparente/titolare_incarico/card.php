<?php 
global $prefix;

if (!isset($prefix)) {
    $prefix = '_dci_titolare_incarico_'; 
}

// Recupero campi
$oggetto     = get_post_meta(get_the_ID(), $prefix . 'oggetto', true);
$compenso    = get_post_meta(get_the_ID(), $prefix . 'compenso', true);
$data_inizio = get_post_meta(get_the_ID(), $prefix . 'data_inizio', true);
$data_fine   = get_post_meta(get_the_ID(), $prefix . 'data_fine', true);
$durata      = get_post_meta(get_the_ID(), $prefix . 'durata', true);
$atto        = get_post_meta(get_the_ID(), $prefix . 'atto_conferimento_incarico', true);
$situazioni  = get_post_meta(get_the_ID(), $prefix . 'situazioni_conflitto', true);

// Allegati
$allegati   = get_post_meta(get_the_ID(), $prefix . 'allegati', true);
$curriculum = get_post_meta(get_the_ID(), $prefix . 'cv_allegati', true);
?>

<div class="card mb-4 rounded-4 shadow-sm border">
    <div class="card-body">
        <!-- Titolo/Norma -->
        <h6 class="text-uppercase text-muted small">Titolo/Norma</h6>
        <h5 class="fw-bold mb-3">
            <?php echo esc_html(get_the_title()); ?>
        </h5>

        <!-- Dati principali -->
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <h6 class="text-uppercase text-muted small">Oggetto incarico</h6>
                <p class="mb-0"><?php echo $oggetto ? wp_kses_post($oggetto) : '-'; ?></p>
            </div>
            <div class="col-md-4">
                <h6 class="text-uppercase text-muted small">Atto di conferimento</h6>
                <p class="mb-0"><?php echo $atto ? esc_html($atto) : '-'; ?></p>
            </div>
            <div class="col-md-4">
                <h6 class="text-uppercase text-muted small">Compenso lordo</h6>
                <p class="mb-0"><?php echo $compenso ? esc_html($compenso) : '-'; ?></p>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <h6 class="text-uppercase text-muted small">Data inizio</h6>
                <p class="mb-0">
                    <?php echo $data_inizio ? date_i18n('d/m/Y', $data_inizio) : '-'; ?>
                </p>
            </div>
            <div class="col-md-4">
                <h6 class="text-uppercase text-muted small">Data fine</h6>
                <p class="mb-0">
                    <?php echo $data_fine ? date_i18n('d/m/Y', $data_fine) : '-'; ?>
                </p>
            </div>
            <div class="col-md-4">
                <h6 class="text-uppercase text-muted small">Durata</h6>
                <p class="mb-0"><?php echo $durata ? esc_html($durata) : '-'; ?></p>
            </div>
        </div>

        <!-- <div class="row g-3 mb-4">
            
        </div> -->

        <!-- Allegati -->
        <div class="row pt-3 border-top">
            <div class="col-md-6">
                <h6 class="text-uppercase text-muted small">Allegati</h6>
                <?php 
                if (!empty($allegati) && is_array($allegati)) {
                    $i = 1;
                    foreach ($allegati as $file_id => $file_data) {
                        $attachment_id = intval($file_data['id'] ?? $file_id);
                        $file_url  = wp_get_attachment_url($attachment_id);
                        $file_title = 'Allegato ' . $i;

                        if (!$file_url) continue;
                        echo '<p class="mb-1">
                                <svg class="icon icon-sm me-1"><use href="#it-file"></use></svg>
                                <a href="'.esc_url($file_url).'" target="_blank" rel="noopener">'.esc_html($file_title).'</a>
                              </p>';
                        $i++;
                    }
                } else {
                    echo '<p class="mb-0">Nessun allegato</p>';
                }
                ?>
            </div>
            <div class="col-md-6">
                <h6 class="text-uppercase text-muted small">Curriculum</h6>
                <?php 
                if (!empty($curriculum) && is_array($curriculum)) {
                    $i = 1;
                    foreach ($curriculum as $file_id => $file_data) {
                        $attachment_id = intval($file_data['id'] ?? $file_id);
                        $file_url = wp_get_attachment_url($attachment_id);
                        $file_title = 'Curriculum ' . $i;

                        if (!$file_url) continue;
                        echo '<p class="mb-1">
                                <svg class="icon icon-sm me-1"><use href="#it-file"></use></svg>
                                <a href="'.esc_url($file_url).'" target="_blank" rel="noopener">'.esc_html($file_title).'</a>
                              </p>';
                        $i++;
                    }
                } else {
                    echo '<p class="mb-0">Nessun curriculum</p>';
                }
                ?>
            </div>
        </div>

        <!-- Link dettaglio -->
        <div class="row mt-3 pt-3 border-top">
            <div class="col">
                <h6 class="text-uppercase text-muted small">Verifica conflitto di interessi</h6>
                <p class="mb-0"><?php echo $situazioni ? esc_html($situazioni) : '-'; ?></p>
            </div>
            <div class="col text-end">
                <a href="<?php the_permalink(); ?>" class="fw-semibold">
                    Clicca qui per consultare il dettaglio
                </a>
            </div>
        </div>
    </div>
</div>
