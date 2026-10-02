<?php
/** Avviso riservato per lo storico delle sezioni del personale. */
if (!function_exists('dci_user_can_view_trasparenza_archive') || !dci_user_can_view_trasparenza_archive()) {
    return;
}
$personale_start_year = isset($args['start_year']) ? absint($args['start_year']) : (int) wp_date('Y') - 5;
$personale_count = isset($args['count']) ? absint($args['count']) : 0;
?>
<aside class="dci-at-notice mt-5 mb-4" role="note">
    <span class="dci-at-notice__label"><svg class="icon icon-sm" aria-hidden="true"><use href="#it-info-circle"></use></svg> Informazioni sulla pubblicazione</span>
    <h2 class="h5 mb-2">Contenuti storici riservati agli utenti autorizzati</h2>
    <p>Di seguito sono riportati i contenuti degli anni precedenti non mostrati nell'elenco pubblico ordinario di questa sezione. La consultazione diretta dello storico nel portale è riservata agli utenti autorizzati, secondo i rispettivi livelli di accesso, per le attività amministrative interne, di verifica e controllo. Restano fermi gli obblighi di conservazione degli atti.</p>
    <?php get_template_part('template-parts/amministrazione-trasparente/personale-informativa', null, ['post_type' => $args['post_type'] ?? '']); ?>
    <p class="mt-3">Chiunque può richiedere i dati e i documenti degli anni precedenti mediante <strong>accesso civico generalizzato</strong>, ai sensi dell'art. 5, comma 2, del d.lgs. 33/2013, senza necessità di motivazione e nel rispetto dei limiti dell'art. 5-bis. Per le modalità di presentazione consulta la sezione Accesso civico del portale oppure rivolgiti all'URP o all'ufficio che detiene i documenti.</p>
    <p class="mb-0">In questo elenco sono presenti <strong><?php echo esc_html(sprintf(
        _n('%s elemento', '%s elementi', $personale_count, 'design_comuni_italia'),
        number_format_i18n($personale_count)
    )); ?></strong> pubblicati prima del <strong><?php echo esc_html($personale_start_year); ?></strong>.</p>
</aside>
