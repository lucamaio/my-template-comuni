<?php 
global $orario_id;

if (!empty($orario_id)) :
    $orario_post = get_post($orario_id);

    if ($orario_post && $orario_post->post_type === 'orario') :

        $prefix = '_dci_orario_';
        $giorni_settimana = [
            'lun' => 'Lunedì',
            'mar' => 'Martedì',
            'mer' => 'Mercoledì',
            'gio' => 'Giovedì',
            'ven' => 'Venerdì',
            'sab' => 'Sabato',
            'dom' => 'Domenica'
        ];

        $data_inizio_raw = get_post_meta($orario_post->ID, $prefix . 'data_inizio', true);
        $data_fine_raw = get_post_meta($orario_post->ID, $prefix . 'data_fine', true);
        $descrizione = get_post_meta($orario_post->ID, $prefix.'descrizione', true);
        $data_inizio = $data_inizio_raw ? date_i18n('d F Y', strtotime($data_inizio_raw)) : null;
        $data_fine = $data_fine_raw ? date_i18n('d F Y', strtotime($data_fine_raw)) : null;
        ?>
        
        <div class="card shadow-sm mt-3 rounded-4 border-0" style="background-color: #f8f9fa;">
    <div class="card-body p-3">
        <h3 class="card-title h5 mb-2">
            <?php echo esc_html($orario_post->post_title); ?>
        </h3>

        <?php if (!empty($descrizione)): ?>
            <p class="text-muted mb-3">
                <?php echo esc_html($descrizione); ?>
            </p>
        <?php endif; ?>

        <?php if ($data_inizio || $data_fine): ?>
            <div class="mb-3 d-flex flex-wrap gap-2">
                <?php if ($data_inizio): ?>
                    <span class="badge bg-primary rounded-pill d-flex align-items-center">
                        <i class="bi bi-calendar2-check me-1"></i> Inizio: <?php echo $data_inizio; ?>
                    </span>
                <?php endif; ?>
                <?php if ($data_fine): ?>
                    <span class="badge bg-warning rounded-pill d-flex align-items-center">
                        <i class="bi bi-calendar2-x me-1"></i> Fine: <?php echo $data_fine; ?>
                    </span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="orari-apertura">
            <h4 class="h6 mb-3">Orari di apertura</h4>
            <div class="list-group list-group-flush">
                <?php foreach ($giorni_settimana as $abbr => $nome_giorno): 
                    $mattina = get_post_meta($orario_post->ID, $prefix.$abbr.'_mattina', true);
                    $pomeriggio = get_post_meta($orario_post->ID, $prefix.$abbr.'_pomeriggio', true);
                    $is_closed = empty($mattina) && empty($pomeriggio);
                ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center rounded-3 mb-1 shadow-sm" style="background-color:#fefefe;">
                        <div class="fw-semibold" style="min-width: 100px;">
                            <svg class="icon icon-sm me-2" aria-hidden="true">
                                <use xlink:href="#it-calendar"></use>
                            </svg>
                            <?php echo $nome_giorno; ?>
                        </div>
                        <div class="d-flex flex-wrap gap-3">
                            <?php if ($is_closed): ?>
                                <span class="badge bg-secondary rounded-pill">CHIUSO</span>
                            <?php else: ?>
                                <?php if ($mattina): ?>
                                    <span class="badge bg-light text-dark d-flex align-items-center border rounded-pill">
                                        <i class="bi bi-clock me-1"></i> <?php echo esc_html($mattina); ?>
                                    </span>
                                <?php endif; ?>
                                <?php if ($pomeriggio): ?>
                                    <span class="badge bg-light text-dark d-flex align-items-center border rounded-pill">
                                        <i class="bi bi-clock-history me-1"></i> <?php echo esc_html($pomeriggio); ?>
                                    </span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>
</div>

<?php 
    endif; 
endif; 
?>

