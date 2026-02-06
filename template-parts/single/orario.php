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

<div class="card shadow-sm mt-3 rounded-2 border-0" style="background-color: #f8f9fa;">
    <div class="card-body p-3">
        <h3 class="card-title h5 mb-2">
            <?php echo esc_html($orario_post->post_title); ?>
        </h3>

        <?php if (!empty($descrizione)): ?>
        <div class="richtext-wrapper lora mb-3">
            <?php echo esc_html($descrizione); ?>
        </div>
        <?php endif; ?>

        <?php if ($data_inizio || $data_fine): ?>
        <div class="mb-3 d-flex flex-wrap gap-2 richtext-wrapper lora">
            <?php if ($data_inizio): ?>
            <span class="badge bg-primary rounded-3 d-flex align-items-center me-2 py-2 px-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor"
                    class="bi bi-calendar-event me-2" viewBox="0 0 16 16">
                    <path d="M11 6.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5z" />
                    <path
                        d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z" />
                </svg>
                <strong>Inizio</strong>: <?php echo $data_inizio; ?>
            </span>
            <?php endif; ?>
            <?php if ($data_fine): ?>
            <span class="badge bg-danger rounded-3 d-flex align-items-center me-2 py-2 px-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor"
                    class="bi bi-calendar-x me-2" viewBox="0 0 16 16">
                    <path
                        d="M6.146 7.146a.5.5 0 0 1 .708 0L8 8.293l1.146-1.147a.5.5 0 1 1 .708.708L8.707 9l1.147 1.146a.5.5 0 0 1-.708.708L8 9.707l-1.146 1.147a.5.5 0 0 1-.708-.708L7.293 9 6.146 7.854a.5.5 0 0 1 0-.708" />
                    <path
                        d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z" />
                </svg>
                <strong>Fine</strong>: <?php echo $data_fine; ?>
            </span>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="orari-apertura">
            <h4 class="h6 mb-3">Orari di apertura</h4>
            <div class="list-group list-group-flush d-flex flex-column gap-2">
                <?php foreach ($giorni_settimana as $abbr => $nome_giorno): 
                    $mattina = get_post_meta($orario_post->ID, $prefix.$abbr.'_mattina', true);
                    $pomeriggio = get_post_meta($orario_post->ID, $prefix.$abbr.'_pomeriggio', true);
                    $is_closed = empty($mattina) && empty($pomeriggio);
                ?>
                <div class="list-group-item d-flex justify-content-between align-items-center rounded-2 shadow-sm"
                    style="background-color:#fefefe;">
                    <div class="" style="min-width: 100px;">
                        <svg class="icon icon-sm me-2" aria-hidden="true">
                            <use xlink:href="#it-calendar"></use>
                        </svg>
                        <span class="fw-semibold richtext-wrapper lora"><?php echo $nome_giorno; ?></span>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <?php if ($is_closed): ?>
                        <span class="badge bg-dark rounded-3 py-2 px-3">Chiuso</span>
                        <?php else: ?>
                        <?php if ($mattina): ?>
                        <span class="badge text-dark d-flex align-items-center border rounded-3 py-2 px-3"
                            style="background-color: #e9ecef;">
                            <i class="bi bi-clock me-1"></i> <?php echo esc_html($mattina); ?>
                        </span>
                        <?php endif; ?>
                        <?php if ($pomeriggio): ?>
                        <span class="badge text-dark d-flex align-items-center border rounded-3 py-2 px-3"
                            style="background-color: #e9ecef;">
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