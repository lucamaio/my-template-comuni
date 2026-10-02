<?php
require_once get_template_directory() . '/template-parts/amministrazione-trasparente/custom-section-card-helpers.php';
$bando_prefix = '_dci_bando_';
$post_id = get_the_ID();
$mostra_aggiornamento = get_post_modified_time('U', true, $post_id) > get_post_time('U', true, $post_id)
    && get_the_modified_date('Y-m-d', $post_id) !== get_the_date('Y-m-d', $post_id);
$struttura_proponente = trim((string) get_post_meta($post_id, $bando_prefix . 'struttura_proponente', true));
$cig = trim((string) get_post_meta($post_id, $bando_prefix . 'cig', true));
$link_bdncp = $cig !== '' ? 'https://dati.anticorruzione.it/superset/dashboard/dettaglio_cig/?cig=' . urlencode($cig) : '';
$oggetto = trim(wp_strip_all_tags((string) get_post_meta($post_id, $bando_prefix . 'oggetto', true)));
$data_inizio = get_post_meta($post_id, $bando_prefix . 'data_inizio', true);
$data_fine = get_post_meta($post_id, $bando_prefix . 'data_fine', true);
$format_importo = static function ($value) {
    $value = trim((string) $value);
    if ($value === '') { return '-'; }
    $numeric = floatval(preg_replace('/[^\d.,]+/', '', str_replace(',', '.', $value)));
    return $numeric !== 0.0 ? number_format($numeric, 2, ',', '.') . ' €' : '-';
};
$procedura = [];
$terms = get_the_terms($post_id, 'tipi_procedura_contraente');
if (!empty($terms) && !is_wp_error($terms)) {
    foreach ($terms as $term) {
        if (!empty($term->name)) { $procedura[] = dci_custom_section_card_text($term->name, 160); }
    }
}
$operator_groups = [
    ['Operatori invitati / partecipanti', get_post_meta($post_id, $bando_prefix . 'operatori_group', true)],
    ['Operatori aggiudicatari', get_post_meta($post_id, $bando_prefix . 'aggiudicatari_group', true)],
];
global $dci_bando_card_style_printed;
if (empty($dci_bando_card_style_printed)) :
    $dci_bando_card_style_printed = true;
?>
<style>
.dci-bando-card { margin-bottom:1.5rem; border:1px solid #d9e2ec; border-radius:12px; background:#fff; box-shadow:0 8px 24px rgba(23,50,77,.08); overflow:hidden; }
.dci-bando-card__body { padding:1.5rem; }
.dci-bando-card__meta { display:flex; align-items:center; flex-wrap:wrap; gap:.35rem; color:#455a64; font-weight:600; margin:0 0 1.25rem; padding-bottom:1rem; border-bottom:1px solid #455a64; }
.dci-bando-card__meta .icon { width:1rem; height:1rem; }
.dci-bando-card .icon { fill:currentColor; flex:0 0 auto; }
.dci-bando-card__title { margin:0; font-size:1.25rem; line-height:1.35; overflow-wrap:anywhere; }
.dci-bando-card__label { display:block; margin:0 0 .3rem; color:#4f6173; font-size:.75rem; font-weight:700; letter-spacing:.035em; line-height:1.25; text-transform:uppercase; }
.dci-bando-card__section { margin-top:1.25rem; padding-top:1.25rem; border-top:1px solid #e4ebf2; }
.dci-bando-card__section-title { margin:0 0 .75rem; font-size:1rem; font-weight:700; color:currentColor; }
.dci-bando-card__people { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:1rem; margin:0; }
.dci-bando-card__field { min-width:0; padding:1.15rem; border:1px solid #e1e7ed; border-radius:8px; background:#f3f5f7; }
.dci-bando-card__value { margin:0; color:#263b4d; line-height:1.55; overflow-wrap:anywhere; }
.dci-bando-card__facts { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); margin:0; border:1px solid #e1e8ef; border-radius:8px; overflow:hidden; }
.dci-bando-card__fact { display:flex; align-items:flex-start; gap:.7rem; min-width:0; padding:1rem; }
.dci-bando-card__fact + .dci-bando-card__fact { border-left:1px solid #e1e8ef; }
.dci-bando-card__fact > div { min-width:0; }
.dci-bando-card__fact-icon { display:inline-flex; align-items:center; justify-content:center; flex:0 0 auto; width:2rem; height:2rem; border-radius:50%; color:#455a64; background:#eef2f5; }
.dci-bando-card__fact-icon .icon { width:1rem; height:1rem; }
.dci-bando-card__documents { padding:1rem; border:1px solid #e4ebf2; border-radius:6px; background:#fff; }
.dci-bando-card__document { display:flex; align-items:flex-start; gap:.4rem; margin:.4rem 0 0; }
.dci-bando-card__document .icon { margin-top:.1rem; }
.dci-bando-card__document-text { min-width:0; }
.dci-bando-card__document a { color:currentColor; font-weight:400; line-height:1.55; overflow-wrap:anywhere; }
.dci-bando-card__document a:hover, .dci-bando-card__document a:focus-visible { text-decoration:underline !important; }
.dci-bando-card__actions { display:flex; flex-wrap:wrap; justify-content:flex-end; gap:.65rem; }
@media (min-width:768px) and (max-width:991.98px) {
    .dci-bando-card__facts { grid-template-columns:repeat(2,minmax(0,1fr)); }
    .dci-bando-card__fact:nth-child(n+3) { border-top:1px solid #e1e8ef; }
    .dci-bando-card__fact:nth-child(3) { border-left:0; }
}
@media (max-width:767.98px) {
    .dci-bando-card__people, .dci-bando-card__facts { grid-template-columns:1fr; }
    .dci-bando-card__fact + .dci-bando-card__fact { border-left:0; border-top:1px solid #e1e8ef; }
    .dci-bando-card__actions { justify-content:flex-start; }
}
</style>
<?php endif; ?>
<article class="dci-bando-card t-primary">
    <div class="dci-bando-card__body">
        <p class="dci-bando-card__meta">
            <svg class="icon" aria-hidden="true"><use href="#it-calendar"></use></svg>
            Pubblicato il <time datetime="<?php echo esc_attr(get_the_date('Y-m-d', $post_id)); ?>"><?php echo esc_html(get_the_date('j F Y', $post_id)); ?></time>
            <?php if ($mostra_aggiornamento) { ?>
                <span aria-hidden="true">&ndash;</span>
                Aggiornato il <time datetime="<?php echo esc_attr(get_the_modified_date('Y-m-d', $post_id)); ?>"><?php echo esc_html(get_the_modified_date('j F Y', $post_id)); ?></time>
            <?php } ?>
        </p>
        <header>
            <span class="dci-bando-card__label">Oggetto della procedura</span>
            <h3 class="dci-bando-card__title"><?php echo esc_html(dci_custom_section_card_text($oggetto !== '' ? $oggetto : get_the_title(), 220)); ?></h3>
        </header>
        <section class="dci-bando-card__section" aria-label="Dati della procedura">
            <h4 class="dci-bando-card__section-title">Dati della procedura</h4>
            <dl class="dci-bando-card__people">
                <div class="dci-bando-card__field">
                    <dt class="dci-bando-card__label">Struttura proponente</dt>
                    <dd class="dci-bando-card__value"><?php echo esc_html(dci_custom_section_card_text($struttura_proponente, 150)); ?></dd>
                </div>
                <div class="dci-bando-card__field">
                    <dt class="dci-bando-card__label">CIG &ndash; Codice identificativo gara</dt>
                    <dd class="dci-bando-card__value"><?php echo esc_html($cig !== '' ? $cig : 'Non indicato'); ?></dd>
                </div>
                <div class="dci-bando-card__field" style="grid-column:1 / -1;">
                    <dt class="dci-bando-card__label">Procedura di scelta del contraente</dt>
                    <dd class="dci-bando-card__value"><?php echo esc_html($procedura ? implode(', ', $procedura) : 'Non indicata'); ?></dd>
                </div>
            </dl>
        </section>
        <section class="dci-bando-card__section" aria-label="Operatori economici">
            <h4 class="dci-bando-card__section-title">Operatori economici</h4>
            <div class="dci-bando-card__people">
                <?php foreach ($operator_groups as [$group_label, $operators]) { ?>
                    <div class="dci-bando-card__field">
                        <h5 class="dci-bando-card__label"><?php echo esc_html($group_label); ?></h5>
                        <?php
                        $has_operators = false;
                        if (is_array($operators)) {
                            foreach ($operators as $operator) {
                                if (!is_array($operator)) { continue; }
                                $ragione_sociale = trim((string) ($operator['ragione_sociale'] ?? ''));
                                $codice_fiscale = trim((string) ($operator['codice_fiscale'] ?? ''));
                                if ($ragione_sociale === '' && $codice_fiscale === '') { continue; }
                                $has_operators = true;
                                ?>
                                <div class="mb-3">
                                    <p class="dci-bando-card__value"><strong><?php echo esc_html(dci_custom_section_card_text($ragione_sociale, 150)); ?></strong></p>
                                    <p class="dci-bando-card__value small">Codice fiscale / P.IVA: <?php echo esc_html($codice_fiscale !== '' ? $codice_fiscale : 'Non indicato'); ?></p>
                                </div>
                            <?php }
                        }
                        if (!$has_operators) { ?>
                            <p class="dci-bando-card__value">Nessun operatore indicato</p>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        </section>
        <section class="dci-bando-card__section" aria-label="Tempi e importi">
            <h4 class="dci-bando-card__section-title">Tempi e importi</h4>
            <div class="dci-bando-card__facts">
                <?php
                $bando_facts = [
                    ['Data di inizio', dci_custom_section_card_date($data_inizio), 'it-calendar'],
                    ['Data di fine', dci_custom_section_card_date($data_fine), 'it-calendar'],
                    ['Importo di aggiudicazione', $format_importo(get_post_meta($post_id, $bando_prefix . 'importo_aggiudicazione', true)), 'it-card'],
                    ['Somme liquidate', $format_importo(get_post_meta($post_id, $bando_prefix . 'importo_somme_liquidate', true)), 'it-card'],
                ];
                foreach ($bando_facts as [$label, $value, $icon]) { ?>
                    <div class="dci-bando-card__fact">
                        <span class="dci-bando-card__fact-icon" aria-hidden="true"><svg class="icon"><use href="#<?php echo esc_attr($icon); ?>"></use></svg></span>
                        <div>
                            <span class="dci-bando-card__label"><?php echo esc_html($label); ?></span>
                            <p class="dci-bando-card__value"><?php echo esc_html($value); ?></p>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </section>
        <section class="dci-bando-card__section" aria-label="Collegamento alla BDNCP">
            <div class="dci-bando-card__documents">
                <h4 class="dci-bando-card__label">Banca Dati Nazionale dei Contratti Pubblici</h4>
                <?php if ($link_bdncp !== '') { ?>
                    <div class="dci-bando-card__document">
                        <svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M14 3h7v7h-2V6.41l-9.29 9.3-1.42-1.42 9.3-9.29H14V3ZM5 5h6v2H7v10h10v-4h2v6H5V5Z"/></svg>
                        <a href="<?php echo esc_url($link_bdncp); ?>" target="_blank" rel="noopener noreferrer">Consulta la procedura nella BDNCP<span class="visually-hidden"> (si apre in una nuova scheda)</span></a>
                    </div>
                <?php } else { ?>
                    <p class="dci-bando-card__value">Collegamento non disponibile: CIG non indicato.</p>
                <?php } ?>
            </div>
        </section>
        <footer class="dci-bando-card__section dci-bando-card__actions dci-at-card-actions">
            <a href="<?php echo esc_url(get_permalink()); ?>" class="dci-at-card-detail-action btn btn-primary btn-sm">
                <span><?php esc_html_e('Apri dettaglio', 'design_comuni_italia'); ?></span>
                <svg class="icon icon-sm ms-1" aria-hidden="true" focusable="false"><use href="#it-arrow-right"></use></svg>
            </a>
            <?php if (function_exists('dci_render_trasparenza_edit_link')) { dci_render_trasparenza_edit_link($post_id); } ?>
        </footer>
    </div>
</article>
