<?php
global $prefix;
require_once get_template_directory() . '/template-parts/amministrazione-trasparente/custom-section-card-helpers.php';

if ( ! isset( $prefix ) ) {
    $prefix = '_dci_icad_';
}
?>

<?php
global $dci_custom_section_card_style_printed;
if (empty($dci_custom_section_card_style_printed)) :
    $dci_custom_section_card_style_printed = true;
?>
<style>
    .dci-custom-section-card {
        margin-bottom: 1.25rem !important;
        border: 1px solid #d7e2ec !important;
        border-radius: 4px !important;
        background: #fff !important;
        box-shadow: 0 8px 22px rgba(23, 50, 77, .07) !important;
        overflow: hidden;
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }
    .dci-custom-section-card:hover {
        border-color: #c9d7e5 !important;
        box-shadow: 0 12px 28px rgba(23, 50, 77, .11) !important;
        transform: translateY(-1px);
    }
    .dci-custom-section-card .card-body { padding: 1.35rem; }
    .dci-custom-section-card .border-top { border-top-color: #e4ebf2 !important; }
    .dci-custom-section-card h5,
    .dci-custom-section-card h6,
    .dci-custom-section-card strong,
    .dci-custom-section-card a:not(.btn) { color: currentColor; }
    .dci-custom-section-card .text-muted,
    .dci-custom-section-card small { color: #5c6f82 !important; }
    .dci-custom-section-card .btn-link {
        color: currentColor;
        font-weight: 700;
        text-decoration: none;
    }
    .dci-custom-section-card .btn-link:hover { text-decoration: underline; }
    .dci-custom-section-card .icon { fill: currentColor; }
    @media (max-width: 767.98px) {
        .dci-custom-section-card .ps-4 { padding-left: 0 !important; }
        .dci-custom-section-card .text-end { text-align: left !important; margin-top: .75rem; }
    }
</style>
<?php endif; ?>

<div class="card mb-2 rounded-3 bg-body-secondary shadow-sm dci-custom-section-card t-primary">
    <div class="card-body">
        <div class="row g-0">
            <div class="col-md-12 ps-4">
                <div class="row mb-3">
                    <div class="col-12">
                        <h6 class="text-uppercase text-muted small">Titolo/Norma</h6>
                        <p class="mb-0"><strong><?php echo esc_html(dci_custom_section_card_text(get_the_title(), 95)); ?></strong></p>
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
                        <span class="d-block"><?php echo esc_html(dci_custom_section_card_text($soggetto_dichiarante, 55)); ?></span>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <small class="text-uppercase text-muted d-block">Soggetto percettore</small>
                        <?php
                        $soggetto_percettore = get_post_meta(get_the_ID(), $prefix . 'soggetto_percettore', true);
                        ?>
                        <span class="d-block"><?php echo esc_html(dci_custom_section_card_text($soggetto_percettore, 55)); ?></span>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <small class="text-uppercase text-muted d-block">Dirigente/Non Dirigente</small>
                        <?php
                        $dirigente = get_post_meta(get_the_ID(), $prefix . 'dirigente_non_dirigente', true);
                        ?>
                        <span class="d-block"><?php echo esc_html(dci_custom_section_card_text($dirigente, 45)); ?></span>
                    </div>
                </div>

                <div class="row align-items-center mb-3">
                    <div class="col-md-3 col-sm-6">
                        <small class="text-uppercase text-muted d-block">Soggetto Conferente</small>
                        <?php
                        $soggetto_conferente = get_post_meta(get_the_ID(), $prefix . 'soggetto_conferente', true);
                        ?>
                        <span class="d-block"><?php echo esc_html(dci_custom_section_card_text($soggetto_conferente, 55)); ?></span>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <small class="text-uppercase text-muted d-block">Data conferimento autorizzazione</small>
                        <?php
                        $data_conferimento = get_post_meta(get_the_ID(), $prefix . 'data_conferimento_autorizzazione', true);
                        echo esc_html(dci_custom_section_card_date($data_conferimento));
                        ?>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <small class="text-uppercase text-muted d-block">Durata</small>
                        <?php
                        $durata = get_post_meta(get_the_ID(), $prefix . 'durata', true);
                        ?>
                        <span class="d-block"><?php echo esc_html(dci_custom_section_card_text($durata, 45)); ?></span>
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
                            $stored_url = is_array($file_data)
                                ? (string) ($file_data['url'] ?? '')
                                : (is_string($file_data) ? $file_data : '');
                            $attachment_id = dci_custom_section_attachment_id(
                                intval(is_array($file_data) ? ($file_data['id'] ?? $file_id) : $file_id),
                                $stored_url
                            );
                            $file_url = $attachment_id > 0 ? wp_get_attachment_url($attachment_id) : $stored_url;
                            $file_title = dci_custom_section_attachment_title(
                                $attachment_id,
                                $file_url,
                                sprintf(__('Allegato %d', 'design_comuni_italia'), $i)
                            );

                            if (!$file_url) continue; // Salta se l'allegato non ha URL

                    ?>
                            <span class="d-inline-flex align-items-center mb-2 me-3">
                                <svg class="icon icon-sm me-1" aria-hidden="true">
                                    <use href="#it-file"></use>
                                </svg>
                                <span class="text fw-semibold">
                                    <a class="text-decoration-none" href="<?php echo esc_url($file_url); ?>" target="_blank" rel="noopener noreferrer">
                                        <?php echo esc_html(dci_custom_section_card_text($file_title, 65)); ?>
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

            <div class="col-md-6 text-end dci-at-card-actions">
                <a href="<?php echo esc_url(get_permalink()); ?>" class="dci-at-card-detail-action btn btn-primary btn-sm">
                    <span><?php esc_html_e('Apri dettaglio', 'design_comuni_italia'); ?></span>
                    <svg class="icon icon-sm ms-1" aria-hidden="true" focusable="false"><use href="#it-arrow-right"></use></svg>
                </a>
                <?php
                if (function_exists('dci_render_trasparenza_edit_link')) {
                    dci_render_trasparenza_edit_link(get_the_ID());
                }
                ?>
            </div>
        </div>
    </div>
</div>
