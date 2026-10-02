<?php
/** Informativa per lo storico dei bandi di gara e contratti. */
if (!function_exists('dci_user_can_view_trasparenza_archive') || !dci_user_can_view_trasparenza_archive()) {
    return;
}
$start_year = isset($args['start_year']) ? absint($args['start_year']) : (int) wp_date('Y') - 5;
$archive_count = isset($args['count']) ? absint($args['count']) : 0;
?>
<aside class="dci-at-notice mt-5 mb-4" role="note">
    <span class="dci-at-notice__label"><svg class="icon icon-sm" aria-hidden="true"><use href="#it-info-circle"></use></svg> Informazioni sulla pubblicazione</span>
    <h2 class="h5 mb-2">Contenuti storici riservati agli utenti autorizzati</h2>
    <p>Di seguito sono riportati i bandi di gara e contratti degli anni precedenti, non mostrati nell'elenco pubblico ordinario della sezione e consultabili dagli utenti autorizzati.</p>
    <p>Ai sensi dell'<a class="text-decoration-underline" href="https://www.gazzettaufficiale.it/atto/serie_generale/caricaArticolo?art.codiceRedazionale=13G00076&amp;art.dataPubblicazioneGazzetta=2013-04-05&amp;art.flagTipoArticolo=0&amp;art.idArticolo=8&amp;art.idGruppo=1&amp;art.idSottoArticolo=1&amp;art.idSottoArticolo1=10&amp;art.progressivo=0&amp;art.versione=1">art. 8, comma 3, del d.lgs. 33/2013</a>, il periodo ordinario di pubblicazione è di <strong>cinque anni</strong>, decorrenti dal <strong>1° gennaio dell'anno successivo</strong> a quello da cui decorre l'obbligo. La pubblicazione deve comunque proseguire finché gli atti producono i propri effetti, fatti salvi i diversi termini previsti dalla normativa.</p>
    <p><strong>Come si calcolano i cinque anni:</strong> se l'obbligo decorre nel 2020, il quinquennio va dal 1° gennaio 2021 al 31 dicembre 2025. La scadenza ordinaria non viene calcolata aggiungendo cinque anni al giorno e al mese di pubblicazione.</p>
    <p>I contenuti dello storico restano consultabili dagli utenti autorizzati, secondo i rispettivi livelli di accesso, per le attività amministrative interne, di verifica e controllo. Restano fermi gli obblighi di conservazione degli atti.</p>
    <p>Chiunque può richiedere l'accesso ai dati e ai documenti degli anni precedenti mediante <strong>accesso civico generalizzato</strong>, ai sensi dell'art. 5, comma 2, del d.lgs. 33/2013, senza necessità di motivazione e nel rispetto dei limiti dell'art. 5-bis. Le modalità di presentazione sono indicate nella sezione Accesso civico del portale; è inoltre possibile rivolgersi all'URP o all'ufficio che detiene i documenti.</p>
    <p class="mb-0">In questo elenco sono presenti <strong><?php echo esc_html(sprintf(
        _n('%s elemento', '%s elementi', $archive_count, 'design_comuni_italia'),
        number_format_i18n($archive_count)
    )); ?></strong> pubblicati prima del <strong><?php echo esc_html($start_year); ?></strong>.</p>
    <?php get_template_part('template-parts/bandi-di-gara/informativa-pubblicazione'); ?>
</aside>
