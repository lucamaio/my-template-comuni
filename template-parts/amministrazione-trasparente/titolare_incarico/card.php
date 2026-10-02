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
$data_pubblicazione = get_the_date('j F Y', get_the_ID());
$data_modifica = get_the_modified_date('j F Y', get_the_ID());
$mostra_aggiornamento = (int) get_the_modified_time('U', get_the_ID()) > (int) get_the_time('U', get_the_ID())
    && get_the_modified_date('Y-m-d', get_the_ID()) !== get_the_date('Y-m-d', get_the_ID());

// Allegati
$allegati   = get_post_meta(get_the_ID(), $prefix . 'allegati', true);
$curriculum = get_post_meta(get_the_ID(), $prefix . 'cv_allegati', true);
?>

<?php
global $dci_titolare_incarico_card_style_printed;
if (empty($dci_titolare_incarico_card_style_printed)) :
    $dci_titolare_incarico_card_style_printed = true;
?>
<style>
    .dci-titolare-card {
        margin-bottom: 1.5rem;
        border: 1px solid #d9e2ec;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 8px 24px rgba(23, 50, 77, .08);
        overflow: hidden;
    }

    .dci-titolare-card__meta-divider {
        margin: 0 0 1.25rem;
        border: 0;
        border-top: 1px solid #455a64 !important;
    }
    .dci-titolare-card__label {
        display: block;
        margin-bottom: .3rem;
        color: #4f6173;
        font-size: .75rem;
        font-weight: 700;
        letter-spacing: .035em;
        line-height: 1.25;
        text-transform: uppercase;
    }
    .dci-titolare-card__meta {
        display: inline-flex;
        align-items: center;
        flex-wrap: wrap;
        gap: .35rem;
        margin-bottom: .85rem;
        color: #455a64;
        font-size: 1rem;
        font-weight: 600;
        line-height: 1.4;
    }
    .dci-titolare-card__meta .icon {
        flex: 0 0 auto;
        width: 1rem;
        height: 1rem;
        fill: currentColor;
    }
    .dci-titolare-card__body {
        padding: 1.5rem;
    }
    .dci-titolare-card__title {
        margin: 0;
        color: currentColor;
        font-size: 1.25rem;
        line-height: 1.35;
    }
    .dci-titolare-card__section {
        margin-top: 1.25rem;
        padding-top: 1.25rem;
        border-top: 1px solid #e4ebf2;
    }
    .dci-titolare-card__long-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: .75rem;
    }
    .dci-titolare-card__long-field {
        min-width: 0;
        padding: 1.15rem;
        border: 1px solid #e1e7ed;
        border-radius: 8px;
        background: #f3f5f7;
    }
    .dci-titolare-card__facts {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0;
        overflow: hidden;
        border: 1px solid #e1e8ef;
        border-radius: 8px;
    }
    .dci-titolare-card__fact {
        display: flex;
        align-items: flex-start;
        gap: .7rem;
        min-width: 0;
        padding: 1rem;
    }
    .dci-titolare-card__fact + .dci-titolare-card__fact {
        border-left: 1px solid #e1e8ef;
    }
    .dci-titolare-card__fact-icon {
        display: inline-flex;
        flex: 0 0 auto;
        align-items: center;
        justify-content: center;
        width: 2rem;
        height: 2rem;
        border-radius: 50%;
        color: #455a64;
        background: #eef2f5;
    }
    .dci-titolare-card__fact-icon .icon {
        width: 1rem;
        height: 1rem;
        fill: currentColor;
    }
    .dci-titolare-card__fact-content {
        min-width: 0;
    }
    .dci-titolare-card__documents {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }
    .dci-titolare-card__field {
        min-width: 0;
        padding: 1rem;
        border: 1px solid #e4ebf2;
        border-radius: 6px;
        background: #fff;
    }
    .dci-titolare-card__long-field .dci-titolare-card__value {
        white-space: normal;
    }
    .dci-titolare-card__value {
        margin: 0;
        color: #263b4d;
        line-height: 1.55;
        overflow-wrap: anywhere;
    }
    .dci-titolare-card__document {
        display: flex;
        align-items: flex-start;
        gap: .4rem;
        margin: .4rem 0 0;
    }
    .dci-titolare-card__document .icon {
        flex: 0 0 auto;
        margin-top: .1rem;
        fill: currentColor;
    }
    .dci-titolare-card__document a {
        color: currentColor;
        text-decoration: none;
        overflow-wrap: anywhere;
    }
    .dci-titolare-card__document a:hover { text-decoration: underline; }
    .dci-titolare-card__footer {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 1rem;
        align-items: end;
    }
    .dci-titolare-card__actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: .65rem;
    }
    @media (max-width: 767.98px) {
        .dci-titolare-card__long-grid,
        .dci-titolare-card__facts,
        .dci-titolare-card__documents { grid-template-columns: 1fr; }
        .dci-titolare-card__fact + .dci-titolare-card__fact {
            border-top: 1px solid #dce5ed;
            border-left: 0;
        }
        .dci-titolare-card__footer { grid-template-columns: 1fr; }
        .dci-titolare-card__actions { justify-content: flex-start; }
    }
    @media (min-width: 768px) and (max-width: 991.98px) {
        .dci-titolare-card__facts { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .dci-titolare-card__fact:nth-child(3) {
            border-top: 1px solid #e1e8ef;
            border-left: 0;
        }
        .dci-titolare-card__fact:nth-child(4) { border-top: 1px solid #e1e8ef; }
    }
</style>
<?php endif; ?>

<article class="dci-titolare-card t-primary">
    <div class="dci-titolare-card__body">
        <span class="dci-titolare-card__meta">
            <svg class="icon" aria-hidden="true" focusable="false"><use href="#it-calendar"></use></svg>
            <?php esc_html_e('Pubblicato il', 'design_comuni_italia'); ?>
            <time datetime="<?php echo esc_attr(get_the_date('Y-m-d', get_the_ID())); ?>">
                <?php echo esc_html($data_pubblicazione); ?>
            </time>
            <?php if ($mostra_aggiornamento) { ?>
                <span aria-hidden="true">–</span>
                <?php esc_html_e('Aggiornato il', 'design_comuni_italia'); ?>
                <time datetime="<?php echo esc_attr(get_the_modified_date('Y-m-d', get_the_ID())); ?>">
                    <?php echo esc_html($data_modifica); ?>
                </time>
            <?php } ?>
        </span>

        <hr class="dci-titolare-card__meta-divider" aria-hidden="true">

        <header>
            <span class="dci-titolare-card__label">Titolare dell’incarico di collaborazione o consulenza</span>
            <h3 class="dci-titolare-card__title">
            <?php echo esc_html(dci_custom_section_card_text(get_the_title(), 95)); ?>
            </h3>
        </header>

        <section class="dci-titolare-card__section" aria-label="Dati principali dell'incarico">
            <div class="dci-titolare-card__long-grid">
                <div class="dci-titolare-card__long-field">
                    <span class="dci-titolare-card__label">Oggetto incarico</span>
                    <p class="dci-titolare-card__value"><?php echo esc_html(dci_custom_section_card_text($oggetto, 220)); ?></p>
                </div>
                <div class="dci-titolare-card__long-field">
                    <span class="dci-titolare-card__label">Atto di conferimento</span>
                    <p class="dci-titolare-card__value"><?php echo esc_html(dci_custom_section_card_text($atto, 220)); ?></p>
                </div>
            </div>
        </section>

        <section class="dci-titolare-card__section" aria-label="Dati economici e durata dell'incarico">
            <div class="dci-titolare-card__facts">
                <div class="dci-titolare-card__fact">
                    <span class="dci-titolare-card__fact-icon" aria-hidden="true">
                        <svg class="icon"><use href="#it-card"></use></svg>
                    </span>
                    <div class="dci-titolare-card__fact-content">
                        <span class="dci-titolare-card__label">Compenso lordo</span>
                        <p class="dci-titolare-card__value"><?php echo esc_html(dci_custom_section_card_text($compenso, 45)); ?></p>
                    </div>
                </div>
                <div class="dci-titolare-card__fact">
                    <span class="dci-titolare-card__fact-icon" aria-hidden="true">
                        <svg class="icon"><use href="#it-calendar"></use></svg>
                    </span>
                    <div class="dci-titolare-card__fact-content">
                        <span class="dci-titolare-card__label">Data inizio</span>
                        <p class="dci-titolare-card__value"><?php echo esc_html(dci_custom_section_card_date($data_inizio)); ?></p>
                    </div>
                </div>
                <div class="dci-titolare-card__fact">
                    <span class="dci-titolare-card__fact-icon" aria-hidden="true">
                        <svg class="icon"><use href="#it-calendar"></use></svg>
                    </span>
                    <div class="dci-titolare-card__fact-content">
                        <span class="dci-titolare-card__label">Data fine</span>
                        <p class="dci-titolare-card__value"><?php echo esc_html(dci_custom_section_card_date($data_fine)); ?></p>
                    </div>
                </div>
                <div class="dci-titolare-card__fact">
                    <span class="dci-titolare-card__fact-icon" aria-hidden="true">
                        <svg class="icon"><use href="#it-clock"></use></svg>
                    </span>
                    <div class="dci-titolare-card__fact-content">
                        <span class="dci-titolare-card__label">Durata</span>
                        <p class="dci-titolare-card__value"><?php echo esc_html(dci_custom_section_card_text($durata, 45)); ?></p>
                    </div>
                </div>
            </div>
        </section>

        <section class="dci-titolare-card__section" aria-label="Documenti dell'incarico">
            <div class="dci-titolare-card__documents">
            <div class="dci-titolare-card__field">
                <span class="dci-titolare-card__label">Allegati</span>
                <?php 
                if (!empty($allegati) && is_array($allegati)) {
                    $i = 1;
                    foreach ($allegati as $file_id => $file_data) {
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

                        if (!$file_url) continue;
                        echo '<p class="dci-titolare-card__document">
                                <svg class="icon icon-sm" aria-hidden="true"><use href="#it-file"></use></svg>
                                <a href="'.esc_url($file_url).'" target="_blank" rel="noopener">'.esc_html(dci_custom_section_card_text($file_title, 65)).'</a>
                              </p>';
                        $i++;
                    }
                } else {
                    echo '<p class="dci-titolare-card__value">Nessun allegato</p>';
                }
                ?>
            </div>
            <div class="dci-titolare-card__field">
                <span class="dci-titolare-card__label">Curriculum</span>
                <?php 
                if (!empty($curriculum) && is_array($curriculum)) {
                    $i = 1;
                    foreach ($curriculum as $file_id => $file_data) {
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
                            sprintf(__('Curriculum %d', 'design_comuni_italia'), $i)
                        );

                        if (!$file_url) continue;
                        echo '<p class="dci-titolare-card__document">
                                <svg class="icon icon-sm" aria-hidden="true"><use href="#it-file"></use></svg>
                                <a href="'.esc_url($file_url).'" target="_blank" rel="noopener">'.esc_html(dci_custom_section_card_text($file_title, 65)).'</a>
                              </p>';
                        $i++;
                    }
                } else {
                    echo '<p class="dci-titolare-card__value">Nessun curriculum</p>';
                }
                ?>
            </div>
            </div>
        </section>

        <footer class="dci-titolare-card__section dci-titolare-card__footer">
            <div>
                <span class="dci-titolare-card__label">Verifica conflitto di interessi</span>
                <p class="dci-titolare-card__value"><?php echo esc_html(dci_custom_section_card_text($situazioni, 90)); ?></p>
            </div>
            <div class="dci-titolare-card__actions dci-at-card-actions">
                <a href="<?php the_permalink(); ?>" class="dci-at-card-detail-action btn btn-primary btn-sm">
                    <span><?php esc_html_e('Apri dettaglio', 'design_comuni_italia'); ?></span>
                    <svg class="icon icon-sm ms-1" aria-hidden="true" focusable="false"><use href="#it-arrow-right"></use></svg>
                </a>
                <?php
                if (function_exists('dci_render_trasparenza_edit_link')) {
                    dci_render_trasparenza_edit_link(get_the_ID());
                }
                ?>
            </div>
        </footer>
    </div>
</article>
