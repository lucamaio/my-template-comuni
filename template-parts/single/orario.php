<?php 
global $orario_id;

if (!empty($orario_id)) :
    $orario_post = get_post($orario_id);

    if ($orario_post && $orario_post->post_type === 'orario') :

        $prefix = '_dci_orario_';
        $giorni_settimana = array(
            'lun' => 'Lunedì',
            'mar' => 'Martedì',
            'mer' => 'Mercoledì',
            'gio' => 'Giovedì',
            'ven' => 'Venerdì',
            'sab' => 'Sabato',
            'dom' => 'Domenica'
        );

        $data_inizio_raw = get_post_meta($orario_post->ID, $prefix . 'data_inizio', true);
        $data_fine_raw = get_post_meta($orario_post->ID, $prefix . 'data_fine', true);

        $data_inizio = $data_inizio_raw ? date_i18n('d F Y', strtotime($data_inizio_raw)) : null;
        $data_fine = $data_fine_raw ? date_i18n('d F Y', strtotime($data_fine_raw)) : null;
        ?>
        
        <div class="card shadow-sm mt-2 rounded-4 border-0">
            <div class="card-body p-3">
                <h3 class="card-title h5 mb-2">
                    <?php echo esc_html($orario_post->post_title); ?>
                </h3>

                <?php if($data_inizio || $data_fine): ?>
                    <div class="mb-2">
                        <?php if ($data_inizio): ?>
                            <span class="badge bg-primary me-2 rounded-pill">
                                <i class="bi bi-calendar2-check me-1"></i> Inizio: <?php echo $data_inizio; ?>
                            </span>
                        <?php endif; ?>
                        <?php if ($data_fine): ?>
                            <span class="badge bg-success rounded-pill">
                                <i class="bi bi-calendar2-x me-1"></i> Fine: <?php echo $data_fine; ?>
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Sezione Orari di apertura -->
                <div class="orari-apertura">
                    <h4 class="h6 mb-2">Orari di apertura</h4>
                    <table class="table table-sm table-borderless mb-0">
                        <tbody>
                        <?php foreach ($giorni_settimana as $abbr => $nome_giorno): 
                            $mattina = get_post_meta($orario_post->ID, $prefix.$abbr.'_mattina', true);
                            $pomeriggio = get_post_meta($orario_post->ID, $prefix.$abbr.'_pomeriggio', true);
                        ?>
                            <tr class="rounded-3 shadow-sm" style="background-color:#fdfdfd;">
                                <td style="width: 25%; font-weight: 600;"><?php echo $nome_giorno; ?></td>
                                <td>
                                    <?php if (empty($mattina) && empty($pomeriggio)): ?>
                                        <span class="badge bg-secondary rounded-pill">CHIUSO</span>
                                    <?php else: ?>
                                        <?php if ($mattina): ?>
                                            <span class="d-block"><i class="bi bi-clock me-1"></i> Mattina: <?php echo esc_html($mattina); ?></span>
                                        <?php endif; ?>
                                        <?php if ($pomeriggio): ?>
                                            <span class="d-block"><i class="bi bi-clock-history me-1"></i> Pomeriggio: <?php echo esc_html($pomeriggio); ?></span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <!-- Fine sezione orari -->

            </div>
        </div>

<?php 
    endif; 
endif; 
?>
    