<?php
/**
 * Avviso RISERVATO dello storico delle sezioni del personale.
 *
 * Visibile solo agli utenti autenticati e abilitati allo storico: il controllo
 * dci_user_can_view_trasparenza_archive() richiede manage_options oppure
 * visualizza_storico_trasparenza. Il solo accesso con credenziali non basta.
 * Se il controllo manca o restituisce false, il return seguente impedisce
 * qualsiasi output di questo file. Il chiamante lo usa in presenza di risultati
 * nella query dello storico; l'avviso non appartiene all'elenco pubblico vuoto.
 */
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
    <?php if ($args['show_publication_duration'] ?? true) { ?>
        <?php get_template_part('template-parts/amministrazione-trasparente/personale-informativa', null, ['post_type' => $args['post_type'] ?? '']); ?>
    <?php } ?>
    <p class="mb-0">In questo elenco sono presenti <strong><?php echo esc_html(sprintf(
        _n('%s elemento', '%s elementi', $personale_count, 'design_comuni_italia'),
        number_format_i18n($personale_count)
    )); ?></strong> pubblicati prima del <strong><?php echo esc_html($personale_start_year); ?></strong>.</p>
</aside>
