<?php
/** Avviso per l'elenco vuoto: distingue la ricerca dalla sezione senza filtri. */
$empty_has_filters = !empty($args['has_filters']);
$empty_page_out_of_range = (int) ($args['paged'] ?? 1) > 1;
$duration_reference_date = current_datetime()->setTime(0, 0);
$duration_cessation_cutoff = $duration_reference_date->modify('-3 years');
$duration_cessation_example_end = $duration_cessation_cutoff->modify('+3 years');
$duration_fallback_start_year = (int) $duration_reference_date->format('Y') - 3;
?>
<div class="dci-at-empty text-decoration-none" role="status" aria-live="polite">
    <span class="dci-at-empty__icon" aria-hidden="true">
        <svg class="icon icon-sm"><use href="#it-info-circle"></use></svg>
    </span>
    <div class="dci-at-empty__content">
        <?php if ($empty_has_filters || $empty_page_out_of_range) { ?>
            <p class="dci-at-empty__text text-decoration-none mb-0">Nessun elemento trovato con i criteri di ricerca selezionati.</p>
        <?php } else { ?>
            <div class="dci-at-empty__law">
                <span class="dci-at-empty__law-title">Durata dell'obbligo di pubblicazione</span>
                <p class="mb-2">Per gli incarichi di collaborazione o consulenza, l'<a class="text-decoration-underline" href="https://www.gazzettaufficiale.it/atto/serie_generale/caricaArticolo?art.codiceRedazionale=13G00076&amp;art.dataPubblicazioneGazzetta=2013-04-05&amp;art.flagTipoArticolo=0&amp;art.idArticolo=15&amp;art.idGruppo=4&amp;art.idSottoArticolo=1&amp;art.idSottoArticolo1=10&amp;art.progressivo=0&amp;art.versione=1">art. 15, comma 4, del d.lgs. 33/2013</a> prevede la pubblicazione entro tre mesi dal conferimento e per i tre anni successivi alla cessazione dell'incarico. Gli incarichi ancora in corso o cessati da meno di tre anni restano nell'elenco pubblico, anche se pubblicati in anni precedenti.</p>
                <p class="mb-2">Alla data del <strong><?php echo esc_html(wp_date('j F Y', $duration_reference_date->getTimestamp())); ?></strong>, gli incarichi con una data di cessazione compilata restano nell’elenco pubblico se cessati dal <strong><?php echo esc_html(wp_date('j F Y', $duration_cessation_cutoff->getTimestamp())); ?></strong> in poi. Il giorno del terzo anniversario della cessazione è compreso.</p>
                <p class="mb-2">Esempio di calcolo: un incarico cessato il <strong><?php echo esc_html(wp_date('j F Y', $duration_cessation_cutoff->getTimestamp())); ?></strong> resta pubblicato fino al <strong><?php echo esc_html(wp_date('j F Y', $duration_cessation_example_end->getTimestamp())); ?></strong>, anche se la pubblicazione risale a un anno precedente.</p>
                <p class="mb-0">Per le schede prive sia della data di inizio sia della data di cessazione, il filtro del portale considera le pubblicazioni dal <strong>1° gennaio <?php echo esc_html($duration_fallback_start_year); ?></strong>. Questo criterio dell’elenco non sostituisce il termine normativo calcolato dalla cessazione effettiva.</p>
            </div>
        <?php } ?>

    </div>
</div>
