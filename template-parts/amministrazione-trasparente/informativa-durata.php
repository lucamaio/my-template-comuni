<?php
/**
 * Informativa generale PUBBLICA sulla durata dell'obbligo di pubblicazione.
 * Non richiede autenticazione o abilitazione allo storico e non mostra documenti
 * riservati. E richiamata dagli avvisi pubblici degli elenchi vuoti e, quando
 * previsto dal chiamante, anche dagli avvisi dello storico riservato.
 */
$duration_reference_date = current_datetime()->setTime(0, 0);
$duration_current_year = (int) $duration_reference_date->format('Y');
$duration_public_start_year = $duration_current_year - 5;
$duration_is_dirigenziale = ($args['post_type'] ?? '') === 'incarico_dirig';
$duration_cessation_example = $duration_reference_date->modify('-3 years');
$duration_cessation_example_end = $duration_cessation_example->modify('+3 years');
?>
<div class="dci-at-empty__law">
    <strong class="dci-at-empty__law-title">Durata dell’obbligo di pubblicazione</strong>
    <?php if ($duration_is_dirigenziale) { ?>
        <p class="mb-2">Per gli incarichi dirigenziali, l’art. 14, comma 2, del d.lgs. 33/2013 prevede la pubblicazione per i tre anni successivi alla cessazione dell’incarico, fatte salve le disposizioni specifiche sulle informazioni patrimoniali. Il triennio decorre dalla data effettiva di cessazione, considerando giorno, mese e anno.</p>
        <p class="mb-2">Esempio di calcolo: per un incarico cessato il <strong><?php echo esc_html(wp_date('j F Y', $duration_cessation_example->getTimestamp())); ?></strong>, il triennio si compie il <strong><?php echo esc_html(wp_date('j F Y', $duration_cessation_example_end->getTimestamp())); ?></strong>.</p>
    <?php } else { ?>
        <p class="mb-2">In via ordinaria, l’art. 8, comma 3, del d.lgs. 33/2013 prevede che dati, informazioni e documenti restino pubblicati per cinque anni, a decorrere dal 1° gennaio dell’anno successivo a quello da cui decorre l’obbligo di pubblicazione, e comunque finché gli atti producono effetti, salvo i diversi termini previsti dalla legge.</p>
        <p class="mb-2">Esempio di calcolo: per un obbligo decorrente nel <strong><?php echo esc_html($duration_public_start_year); ?></strong>, il quinquennio ordinario va dal <strong>1° gennaio <?php echo esc_html($duration_public_start_year + 1); ?></strong> al <strong>31 dicembre <?php echo esc_html($duration_public_start_year + 5); ?></strong>.</p>
    <?php } ?>
    <p class="mb-2">Nell’anno <strong><?php echo esc_html($duration_current_year); ?></strong>, l’elenco pubblico ordinario del portale utilizza come soglia le pubblicazioni dal <strong>1° gennaio <?php echo esc_html($duration_public_start_year); ?></strong> in poi.</p>
    <?php if ($duration_is_dirigenziale) { ?>
        <p class="mb-2">La suddivisione dell’elenco per anno di pubblicazione non determina, da sola, la scadenza dell’obbligo relativo al singolo incarico, che dipende dalla sua cessazione effettiva.</p>
    <?php } ?>
    <a class="dci-at-empty__source" href="https://www.normattiva.it/eli/id/2013/04/05/13G00076/CONSOLIDATED" target="_blank" rel="noopener noreferrer">Consulta il d.lgs. 33/2013 su Normattiva</a>
</div>
