<?php
require_once get_template_directory() . '/template-parts/amministrazione-trasparente/custom-section-card-helpers.php';
$prefix = '_dci_icad_';
$post_id = get_the_ID();
$data_pubblicazione = get_the_date('j F Y', $post_id);
$data_modifica = get_the_modified_date('j F Y', $post_id);
$mostra_aggiornamento = get_post_modified_time('U', true, $post_id) > get_post_time('U', true, $post_id)
    && get_the_modified_date('Y-m-d', $post_id) !== get_the_date('Y-m-d', $post_id);
$anno_conferimento = get_post_meta($post_id, $prefix . 'anno_conferimento', true);
$anno_conferimento_formatted = !empty($anno_conferimento) ? date_i18n('Y', intval($anno_conferimento)) : '-';
$soggetto_percettore = get_post_meta($post_id, $prefix . 'soggetto_percettore', true);
$dirigente = get_post_meta($post_id, $prefix . 'dirigente_non_dirigente', true);
$soggetto_conferente = get_post_meta($post_id, $prefix . 'soggetto_conferente', true);
$soggetto_dichiarante = get_post_meta($post_id, $prefix . 'soggetto_dichiarante', true);
$data_conferimento = get_post_meta($post_id, $prefix . 'data_conferimento_autorizzazione', true);
$durata = get_post_meta($post_id, $prefix . 'durata', true);
$compenso = get_post_meta($post_id, $prefix . 'compenso_lordo', true);
$compenso_numeric = floatval(str_replace(',', '.', preg_replace('/[^\d,]+/', '', $compenso)));

global $dci_dipendenti_card_style_printed;
if (empty($dci_dipendenti_card_style_printed)) :
    $dci_dipendenti_card_style_printed = true;
?>
<style>
.dci-dipendenti-card { margin-bottom:1.5rem; border:1px solid #d9e2ec; border-radius:12px; background:#fff; box-shadow:0 8px 24px rgba(23,50,77,.08); overflow:hidden; }
.dci-dipendenti-card__body { padding:1.5rem; }
.dci-dipendenti-card__meta { display:flex; align-items:center; flex-wrap:wrap; gap:.35rem; color:#455a64; font-weight:600; margin:0 0 1.25rem; padding-bottom:1rem; border-bottom:1px solid #455a64; }
.dci-dipendenti-card__meta .icon { width:1rem; height:1rem; }
.dci-dipendenti-card .icon { fill:currentColor; flex:0 0 auto; }
.dci-dipendenti-card__title { margin:0; font-size:1.25rem; line-height:1.35; overflow-wrap:anywhere; }
.dci-dipendenti-card__label { display:block; margin:0 0 .3rem; color:#4f6173; font-size:.75rem; font-weight:700; letter-spacing:.035em; line-height:1.25; text-transform:uppercase; }
.dci-dipendenti-card__section { margin-top:1.25rem; padding-top:1.25rem; border-top:1px solid #e4ebf2; }
.dci-dipendenti-card__section-title { margin:0 0 .75rem; font-size:1rem; font-weight:700; color:currentColor; }
.dci-dipendenti-card__people { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:1rem; margin:0; }
.dci-dipendenti-card__field { min-width:0; padding:1.15rem; border:1px solid #e1e7ed; border-radius:8px; background:#f3f5f7; }
.dci-dipendenti-card__value { margin:0; color:#263b4d; line-height:1.55; overflow-wrap:anywhere; }
.dci-dipendenti-card__facts { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); margin:0; border:1px solid #e1e8ef; border-radius:8px; overflow:hidden; }
.dci-dipendenti-card__fact { display:flex; align-items:flex-start; gap:.7rem; min-width:0; padding:1rem; }
.dci-dipendenti-card__fact + .dci-dipendenti-card__fact { border-left:1px solid #e1e8ef; }
.dci-dipendenti-card__fact > div { min-width:0; }
.dci-dipendenti-card__fact-icon { display:inline-flex; align-items:center; justify-content:center; flex:0 0 auto; width:2rem; height:2rem; border-radius:50%; color:#455a64; background:#eef2f5; }
.dci-dipendenti-card__fact-icon .icon { width:1rem; height:1rem; }
.dci-dipendenti-card__documents { padding:1rem; border:1px solid #e4ebf2; border-radius:6px; background:#fff; }
.dci-dipendenti-card__document { display:flex; align-items:flex-start; gap:.4rem; margin:.4rem 0 0; }
.dci-dipendenti-card__document .icon { margin-top:.1rem; }
.dci-dipendenti-card__document-text { min-width:0; }
.dci-dipendenti-card__document a { color:currentColor; font-weight:400; line-height:1.55; overflow-wrap:anywhere; }
.dci-dipendenti-card__document a:hover, .dci-dipendenti-card__document a:focus-visible { text-decoration:underline !important; }
.dci-dipendenti-card__actions { display:flex; flex-wrap:wrap; justify-content:flex-end; gap:.65rem; }
@media (min-width:768px) and (max-width:991.98px) {
    .dci-dipendenti-card__facts { grid-template-columns:repeat(2,minmax(0,1fr)); }
    .dci-dipendenti-card__fact:nth-child(n+3) { border-top:1px solid #e1e8ef; }
    .dci-dipendenti-card__fact:nth-child(3) { border-left:0; }
}
@media (max-width:767.98px) {
    .dci-dipendenti-card__people, .dci-dipendenti-card__facts { grid-template-columns:1fr; }
    .dci-dipendenti-card__fact + .dci-dipendenti-card__fact { border-left:0; border-top:1px solid #e1e8ef; }
    .dci-dipendenti-card__actions { justify-content:flex-start; }
}
</style>
<?php endif; ?>
<article class="dci-dipendenti-card t-primary">
    <div class="dci-dipendenti-card__body">
        <p class="dci-dipendenti-card__meta">
            <svg class="icon" aria-hidden="true"><use href="#it-calendar"></use></svg>
            Pubblicato il <time datetime="<?php echo esc_attr(get_the_date('Y-m-d', $post_id)); ?>"><?php echo esc_html($data_pubblicazione); ?></time>
            <?php if ($mostra_aggiornamento) { ?>
                <span aria-hidden="true">&ndash;</span>
                Aggiornato il <time datetime="<?php echo esc_attr(get_the_modified_date('Y-m-d', $post_id)); ?>"><?php echo esc_html($data_modifica); ?></time>
            <?php } ?>
        </p>
        <header>
            <span class="dci-dipendenti-card__label">Incarico conferito o autorizzato</span>
            <h3 class="dci-dipendenti-card__title"><?php echo esc_html(dci_custom_section_card_text(get_the_title(), 95)); ?></h3>
        </header>
        <section class="dci-dipendenti-card__section" aria-label="Titolare dell'incarico">
            <h4 class="dci-dipendenti-card__section-title">Titolare dell'incarico</h4>
            <dl class="dci-dipendenti-card__people">
                <div class="dci-dipendenti-card__field">
                    <dt class="dci-dipendenti-card__label">Soggetto percettore</dt>
                    <dd class="dci-dipendenti-card__value"><?php echo esc_html(dci_custom_section_card_text($soggetto_percettore, 150)); ?></dd>
                </div>
                <div class="dci-dipendenti-card__field">
                    <dt class="dci-dipendenti-card__label">Qualifica: dirigente / non dirigente</dt>
                    <dd class="dci-dipendenti-card__value"><?php echo esc_html(dci_custom_section_card_text($dirigente, 95)); ?></dd>
                </div>
            </dl>
        </section>
        <section class="dci-dipendenti-card__section" aria-label="Conferimento e dichiarazione">
            <h4 class="dci-dipendenti-card__section-title">Conferimento e dichiarazione</h4>
            <dl class="dci-dipendenti-card__people">
                <div class="dci-dipendenti-card__field">
                    <dt class="dci-dipendenti-card__label">Soggetto conferente</dt>
                    <dd class="dci-dipendenti-card__value"><?php echo esc_html(dci_custom_section_card_text($soggetto_conferente, 150)); ?></dd>
                </div>
                <div class="dci-dipendenti-card__field">
                    <dt class="dci-dipendenti-card__label">Soggetto dichiarante</dt>
                    <dd class="dci-dipendenti-card__value"><?php echo esc_html(dci_custom_section_card_text($soggetto_dichiarante, 150)); ?></dd>
                </div>
            </dl>
        </section>
        <section class="dci-dipendenti-card__section" aria-label="Periodo e compenso">
            <h4 class="dci-dipendenti-card__section-title">Periodo e compenso</h4>
            <div class="dci-dipendenti-card__facts">
                <?php
                $incarico_facts = [
                    ['Anno di conferimento', $anno_conferimento_formatted, 'it-calendar'],
                    ['Data di conferimento / autorizzazione', dci_custom_section_card_date($data_conferimento), 'it-calendar'],
                    ['Durata', dci_custom_section_card_text($durata, 95), 'it-clock'],
                    ['Compenso lordo', $compenso_numeric !== 0.0 ? number_format($compenso_numeric, 2, ',', '.') . ' €' : '-', 'it-card'],
                ];
                foreach ($incarico_facts as [$label, $value, $icon]) { ?>
                    <div class="dci-dipendenti-card__fact">
                        <span class="dci-dipendenti-card__fact-icon" aria-hidden="true"><svg class="icon"><use href="#<?php echo esc_attr($icon); ?>"></use></svg></span>
                        <div>
                            <span class="dci-dipendenti-card__label"><?php echo esc_html($label); ?></span>
                            <p class="dci-dipendenti-card__value"><?php echo esc_html($value); ?></p>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </section>
        <section class="dci-dipendenti-card__section" aria-label="Documenti dell'incarico">
            <div class="dci-dipendenti-card__documents">
                <h4 class="dci-dipendenti-card__label">Allegati</h4>
                <div class="dci-dipendenti-card__value">
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
                            <span class="dci-dipendenti-card__document">
                                <svg class="icon icon-sm me-1" aria-hidden="true">
                                    <use href="#it-file"></use>
                                </svg>
                                <span class="dci-dipendenti-card__document-text">
                                    <a class="text-decoration-none" href="<?php echo esc_url($file_url); ?>" target="_blank" rel="noopener noreferrer">
                                        <?php echo esc_html(dci_custom_section_card_text($file_title, 65)); ?>
                                    </a>
                                </span>
                            </span>
                    <?php
                            $i++;
                        }
                    } else {
                        echo 'Nessun allegato disponibile';
                    }
                    ?>

                </div>
            </div>
        </section>
        <footer class="dci-dipendenti-card__section dci-dipendenti-card__actions dci-at-card-actions">
            <a href="<?php echo esc_url(get_permalink()); ?>" class="dci-at-card-detail-action btn btn-primary btn-sm">
                <span><?php esc_html_e('Apri dettaglio', 'design_comuni_italia'); ?></span>
                <svg class="icon icon-sm ms-1" aria-hidden="true" focusable="false"><use href="#it-arrow-right"></use></svg>
            </a>
            <?php if (function_exists('dci_render_trasparenza_edit_link')) { dci_render_trasparenza_edit_link($post_id); } ?>
        </footer>
    </div>
</article>
