<?php
/** Informativa specifica per lo storico dei titolari di incarichi. */
if (!function_exists('dci_user_can_view_trasparenza_archive') || !dci_user_can_view_trasparenza_archive()) {
    return;
}

$start_year = isset($args['start_year']) ? absint($args['start_year']) : (int) wp_date('Y') - 3;
$archive_count = isset($args['count']) ? absint($args['count']) : 0;
?>
<aside class="dci-at-notice mt-5 mb-4" role="note">
    <span class="dci-at-notice__label"><svg class="icon icon-sm" aria-hidden="true"><use href="#it-info-circle"></use></svg> Informazioni sulla pubblicazione</span>
    <h2 class="h5 mb-2">Contenuti storici riservati agli utenti autorizzati</h2>
    <p>Di seguito sono riportati i contenuti storici non mostrati nell'elenco pubblico ordinario di questa sezione e consultabili dagli utenti autorizzati.</p>
    <p>Ai sensi dell'<strong>art. 15, comma 4, del decreto legislativo 14 marzo 2013, n. 33</strong>, i dati relativi agli incarichi di collaborazione o consulenza devono essere pubblicati entro tre mesi dal conferimento e mantenuti online per i <strong>tre anni successivi alla cessazione dell'incarico</strong>. Si applica quindi il termine specifico previsto per questi incarichi, fatto salvo dall'art. 8, comma 3, del medesimo decreto.</p>
    <p><strong>Come si calcolano i tre anni:</strong> il termine decorre dalla data effettiva di cessazione dell'incarico, considerando <strong>giorno, mese e anno</strong>. Per questi incarichi non si applica la decorrenza dal 1° gennaio dell'anno successivo prevista dalla regola generale dell'art. 8, comma 3. Ad esempio, per un incarico cessato il <strong>5 ottobre 2023</strong>, il triennio si compie il <strong>5 ottobre 2026</strong>: fino a tale data il contenuto resta nell'elenco pubblico.</p>
    <p>Una volta decorso il termine applicabile, viene meno l'obbligo di pubblicazione dei relativi contenuti nella sezione <strong>Amministrazione Trasparente</strong>. La sola data di pubblicazione non determina la cessazione dell'obbligo: occorre considerare la data di cessazione dell'incarico.</p>
    <p>I contenuti dello storico restano consultabili dagli utenti autorizzati, secondo i rispettivi livelli di accesso, per le attività amministrative interne, di verifica e controllo. Restano fermi gli obblighi di conservazione degli atti.</p>
    <p>I soggetti esterni possono richiedere l'accesso ai dati e ai documenti ai sensi dell'<strong>art. 5 del d.lgs. 33/2013</strong>, nel rispetto dei limiti previsti dall'<strong>art. 5-bis</strong> e delle altre disposizioni applicabili in materia di accesso.</p>
    <p class="mb-0">
        In questo elenco sono presenti <strong><?php echo esc_html(number_format_i18n($archive_count)); ?> elementi</strong>
        pubblicati prima del <strong><?php echo esc_html($start_year); ?></strong> e riferiti a incarichi cessati da oltre tre anni.
        Gli elementi privi di una data di cessazione valida restano nell'elenco pubblico.
    </p>
</aside>
