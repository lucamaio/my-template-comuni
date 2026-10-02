<?php
require_once get_template_directory() . '/template-parts/amministrazione-trasparente/custom-section-card-helpers.php';
$atto_prefix = '_dci_atto_concessione_';
$ragione_sociale = get_post_meta(get_the_ID(), $atto_prefix . 'ragione_sociale', true) ?: 'Non specificato';
$codice_fiscale = get_post_meta(get_the_ID(), $atto_prefix . 'codice_fiscale', true) ?: 'Non specificato';
$responsabile = get_post_meta(get_the_ID(), $atto_prefix . 'responsabile', true) ?: 'Non specificato';
$anno_beneficio = get_post_meta(get_the_ID(), $atto_prefix . 'anno_beneficio', true);
$formatted_anno_beneficio = !empty($anno_beneficio) ? date_i18n('Y', $anno_beneficio) : '-';
$importo = get_post_meta(get_the_ID(), $atto_prefix . 'importo', true);
$importo_numeric = floatval(str_replace(',', '.', preg_replace('/[^\d,]+/', '', $importo)));
$rag_incarico = get_post_meta(get_the_ID(), $atto_prefix . 'rag_incarico', true);
$show_modified_date = get_post_modified_time('U', true, get_the_ID()) > get_post_time('U', true, get_the_ID());
global $dci_concessione_card_style_printed;
if (empty($dci_concessione_card_style_printed)) :
    $dci_concessione_card_style_printed = true;
?>
<style>
    .dci-concessione-card {
        margin-bottom: 1.5rem;
        border: 1px solid #d9e2ec;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 8px 24px rgba(23, 50, 77, .08);
        overflow: hidden;
    }

    .dci-concessione-card__meta-divider {
        margin: 0 0 1.25rem;
        border: 0;
        border-top: 1px solid #455a64 !important;
    }
    .dci-concessione-card__label {
        display: block;
        margin-bottom: .3rem;
        color: #4f6173;
        font-size: .75rem;
        font-weight: 700;
        letter-spacing: .035em;
        line-height: 1.25;
        text-transform: uppercase;
    }
    .dci-concessione-card__meta {
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
    .dci-concessione-card__meta .icon {
        flex: 0 0 auto;
        width: 1rem;
        height: 1rem;
        fill: currentColor;
    }
    .dci-concessione-card__body {
        padding: 1.5rem;
    }
    .dci-concessione-card__title {
        margin: 0;
        color: currentColor;
        font-size: 1.25rem;
        line-height: 1.35;
    }
    .dci-concessione-card__section {
        margin-top: 1.25rem;
        padding-top: 1.25rem;
        border-top: 1px solid #e4ebf2;
    }
    .dci-concessione-card__long-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: .75rem;
    }
    .dci-concessione-card__long-field {
        min-width: 0;
        padding: 1.15rem;
        border: 1px solid #e1e7ed;
        border-radius: 8px;
        background: #f3f5f7;
    }
    .dci-concessione-card__facts {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0;
        overflow: hidden;
        border: 1px solid #e1e8ef;
        border-radius: 8px;
    }
    .dci-concessione-card__fact {
        display: flex;
        align-items: flex-start;
        gap: .7rem;
        min-width: 0;
        padding: 1rem;
    }
    .dci-concessione-card__fact + .dci-concessione-card__fact {
        border-left: 1px solid #e1e8ef;
    }
    .dci-concessione-card__fact-icon {
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
    .dci-concessione-card__fact-icon .icon {
        width: 1rem;
        height: 1rem;
        fill: currentColor;
    }
    .dci-concessione-card__fact-content {
        min-width: 0;
    }
    .dci-concessione-card__documents {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }
    .dci-concessione-card__field {
        min-width: 0;
        padding: 1rem;
        border: 1px solid #e4ebf2;
        border-radius: 6px;
        background: #fff;
    }
    .dci-concessione-card__long-field .dci-concessione-card__value {
        white-space: normal;
    }
    .dci-concessione-card__value {
        margin: 0;
        color: #263b4d;
        line-height: 1.55;
        overflow-wrap: anywhere;
    }
    .dci-concessione-card__document {
        display: flex;
        align-items: flex-start;
        gap: .4rem;
        margin: .4rem 0 0;
    }
    .dci-concessione-card__document .icon {
        flex: 0 0 auto;
        margin-top: .1rem;
        fill: currentColor;
    }
    .dci-concessione-card__document a {
        color: currentColor;
        text-decoration: none;
        overflow-wrap: anywhere;
    }
    .dci-concessione-card__document a:hover { text-decoration: underline; }
    .dci-concessione-card__footer {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 1rem;
        align-items: end;
    }
    .dci-concessione-card__actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: .65rem;
    }
    @media (max-width: 767.98px) {
        .dci-concessione-card__long-grid,
        .dci-concessione-card__facts,
        .dci-concessione-card__documents { grid-template-columns: 1fr; }
        .dci-concessione-card__fact + .dci-concessione-card__fact {
            border-top: 1px solid #dce5ed;
            border-left: 0;
        }
        .dci-concessione-card__footer { grid-template-columns: 1fr; }
        .dci-concessione-card__actions { justify-content: flex-start; }
    }
    @media (min-width: 768px) and (max-width: 991.98px) {
        .dci-concessione-card__facts { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .dci-concessione-card__fact:nth-child(3) {
            border-top: 1px solid #e1e8ef;
            border-left: 0;
        }
        .dci-concessione-card__fact:nth-child(4) { border-top: 1px solid #e1e8ef; }
    }
    @media (min-width: 768px) {
        .dci-concessione-card__facts--two { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
</style>
<?php endif; ?>
<article class="dci-concessione-card t-primary">
    <div class="dci-concessione-card__body">
        <span class="dci-concessione-card__meta">
            <svg class="icon" aria-hidden="true" focusable="false"><use href="#it-calendar"></use></svg>
            Pubblicato il <time datetime="<?php echo esc_attr(get_the_date('c', get_the_ID())); ?>"><?php echo esc_html(get_the_date('j F Y', get_the_ID())); ?></time>
            <?php if ($show_modified_date) { ?>
                <span aria-hidden="true">–</span>
                Aggiornato il <time datetime="<?php echo esc_attr(get_the_modified_date('c', get_the_ID())); ?>"><?php echo esc_html(get_the_modified_date('j F Y', get_the_ID())); ?></time>
            <?php } ?>
        </span>
        <hr class="dci-concessione-card__meta-divider" aria-hidden="true">
        <header>
            <span class="dci-concessione-card__label">Oggetto dell’atto di concessione</span>
            <h3 class="dci-concessione-card__title"><?php echo esc_html(dci_custom_section_card_text(get_the_title(), 95)); ?></h3>
        </header>
        <section class="dci-concessione-card__section" aria-label="Oggetto della concessione">
            <div class="dci-concessione-card__long-field">
                <span class="dci-concessione-card__label">Ragione dell'incarico</span>
                <p class="dci-concessione-card__value"><?php echo esc_html(dci_custom_section_card_text($rag_incarico, 220)); ?></p>
            </div>
        </section>
        <section class="dci-concessione-card__section" aria-label="Dati del beneficiario">
            <div class="dci-concessione-card__long-field">
                <span class="dci-concessione-card__label">Beneficiario</span>
                <p class="dci-concessione-card__value"><?php echo esc_html(dci_custom_section_card_text($ragione_sociale, 95)); ?></p>
                <div class="mt-3">
                    <span class="dci-concessione-card__label">Codice fiscale / P.IVA del beneficiario</span>
                    <p class="dci-concessione-card__value"><?php echo esc_html($codice_fiscale); ?></p>
                </div>
            </div>
        </section>
        <section class="dci-concessione-card__section" aria-label="Responsabile dell'atto">
            <div class="dci-concessione-card__field">
                <span class="dci-concessione-card__label">Responsabile dell'atto</span>
                <p class="dci-concessione-card__value"><?php echo esc_html(dci_custom_section_card_text($responsabile, 95)); ?></p>
            </div>
        </section>
        <section class="dci-concessione-card__section" aria-label="Dati della concessione">
            <div class="dci-concessione-card__facts dci-concessione-card__facts--two">
                <?php
                $atto_facts = [
                    ['Anno beneficio', $formatted_anno_beneficio, 'it-calendar', ''],
                    ['Importo', $importo_numeric !== 0.0 ? number_format($importo_numeric, 2, ',', '.') . ' €' : '-', 'it-card', ''],
                ];
                foreach ($atto_facts as [$label, $value, $icon, $detail]) { ?>
                    <div class="dci-concessione-card__fact">
                        <span class="dci-concessione-card__fact-icon" aria-hidden="true"><svg class="icon"><use href="#<?php echo esc_attr($icon); ?>"></use></svg></span>
                        <div class="dci-concessione-card__fact-content">
                            <span class="dci-concessione-card__label"><?php echo esc_html($label); ?></span>
                            <p class="dci-concessione-card__value"><?php echo esc_html($value); ?></p>
                            <?php if ($detail !== '') { ?><p class="dci-concessione-card__value small"><?php echo esc_html($detail); ?></p><?php } ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </section>
        <section class="dci-concessione-card__section" aria-label="Documenti della concessione">
            <div class="dci-concessione-card__field">
                <span class="dci-concessione-card__label">Allegati</span>
                <div class="dci-concessione-card__value">
                    <?php
                    $allegati = get_post_meta(get_the_ID(), $atto_prefix . 'allegati', true);

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
                            <span class="dci-concessione-card__document">
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

                </div>
            </div>
        </section>
        <footer class="dci-concessione-card__section">
            <div class="dci-concessione-card__actions dci-at-card-actions">
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
        </footer>
    </div>
</article>
