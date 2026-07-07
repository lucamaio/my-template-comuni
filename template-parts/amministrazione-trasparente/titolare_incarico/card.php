<?php 
global $prefix;
require_once get_template_directory() . '/template-parts/amministrazione-trasparente/custom-section-card-helpers.php';

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
    .dci-custom-section-card .fw-semibold {
        color: currentColor;
        text-decoration: none;
    }
    .dci-custom-section-card .fw-semibold:hover { text-decoration: underline; }
    .dci-custom-section-card .icon { fill: currentColor; }
    @media (max-width: 767.98px) {
        .dci-custom-section-card .text-end { text-align: left !important; margin-top: .75rem; }
    }
</style>
<?php endif; ?>

<div class="card mb-4 rounded-4 shadow-sm border dci-custom-section-card t-primary">
    <div class="card-body">
        <!-- Titolo/Norma -->
        <h6 class="text-uppercase text-muted small">Titolo/Norma</h6>
        <h5 class="fw-bold mb-3">
            <?php echo esc_html(dci_custom_section_card_text(get_the_title(), 95)); ?>
        </h5>

        <!-- Dati principali -->
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <h6 class="text-uppercase text-muted small">Oggetto incarico</h6>
                <p class="mb-0"><?php echo esc_html(dci_custom_section_card_text($oggetto, 110)); ?></p>
            </div>
            <div class="col-md-4">
                <h6 class="text-uppercase text-muted small">Atto di conferimento</h6>
                <p class="mb-0"><?php echo esc_html(dci_custom_section_card_text($atto, 70)); ?></p>
            </div>
            <div class="col-md-4">
                <h6 class="text-uppercase text-muted small">Compenso lordo</h6>
                <p class="mb-0"><?php echo esc_html(dci_custom_section_card_text($compenso, 45)); ?></p>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <h6 class="text-uppercase text-muted small">Data inizio</h6>
                <p class="mb-0">
                    <?php echo esc_html(dci_custom_section_card_date($data_inizio)); ?>
                </p>
            </div>
            <div class="col-md-4">
                <h6 class="text-uppercase text-muted small">Data fine</h6>
                <p class="mb-0">
                    <?php echo esc_html(dci_custom_section_card_date($data_fine)); ?>
                </p>
            </div>
            <div class="col-md-4">
                <h6 class="text-uppercase text-muted small">Durata</h6>
                <p class="mb-0"><?php echo esc_html(dci_custom_section_card_text($durata, 45)); ?></p>
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
                                <a href="'.esc_url($file_url).'" target="_blank" rel="noopener">'.esc_html(dci_custom_section_card_text($file_title, 65)).'</a>
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
                                <a href="'.esc_url($file_url).'" target="_blank" rel="noopener">'.esc_html(dci_custom_section_card_text($file_title, 65)).'</a>
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
                <p class="mb-0"><?php echo esc_html(dci_custom_section_card_text($situazioni, 90)); ?></p>
            </div>
            <div class="col text-end">
                <a href="<?php the_permalink(); ?>" class="fw-semibold">
                    Clicca qui per consultare il dettaglio
                </a>
            </div>
        </div>
    </div>
</div>
