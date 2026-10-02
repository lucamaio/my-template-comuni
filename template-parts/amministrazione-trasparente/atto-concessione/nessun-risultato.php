<?php
/** Avviso per l'elenco pubblico vuoto, con lo stile delle sezioni standard. */
global $wpdb;

$empty_start_year = (int) $args['start_year'];
$empty_has_filters = !empty($args['has_filters']);
$empty_page_out_of_range = (int) $args['paged'] > 1;
$empty_archive_count = null;

// Solo un conteggio aggregato: i contenuti dello storico restano riservati.
// Non dedurre l'assenza di pubblicazioni da una ricerca o pagina senza risultati.
if (!$empty_has_filters && !$empty_page_out_of_range) {
    $empty_archive_sql = $wpdb->prepare('post_date < %s', $empty_start_year . '-01-01 00:00:00');
    $empty_archive_count = $wpdb->get_var(
        "SELECT COUNT(*) FROM {$wpdb->posts}
         WHERE post_type = 'atto_concessione' AND post_status = 'publish'
         AND {$empty_archive_sql}"
    );
}
$empty_access_url = '';
if ((int) $empty_archive_count > 0) {
    foreach (['accesso-civico-generalizzato-concernente-dati-e-documenti-ulteriori', 'accesso-civico'] as $empty_access_slug) {
        $empty_access_term = get_term_by('slug', $empty_access_slug, 'tipi_cat_amm_trasp');
        if (!$empty_access_term instanceof WP_Term) {
            continue;
        }
        $empty_access_link = get_term_link($empty_access_term);
        if (!is_wp_error($empty_access_link)) {
            $empty_access_url = $empty_access_link;
            break;
        }
    }
}
?>
<div class="dci-at-empty text-decoration-none" role="status" aria-live="polite">
    <span class="dci-at-empty__icon" aria-hidden="true">
        <svg class="icon icon-sm"><use href="#it-info-circle"></use></svg>
    </span>
    <div class="dci-at-empty__content">
        <?php if ($empty_page_out_of_range) { ?>
            <p class="dci-at-empty__title text-decoration-none">Nessun elemento in questa pagina</p>
            <p class="dci-at-empty__text text-decoration-none">La pagina richiesta non contiene risultati. Premi “Filtra” per tornare alla prima pagina mantenendo i criteri selezionati.</p>
        <?php } elseif ($empty_has_filters) { ?>
            <p class="dci-at-empty__title text-decoration-none">Nessun risultato con i filtri selezionati</p>
            <p class="dci-at-empty__text text-decoration-none">Nell'elenco pubblico non risultano atti di concessione corrispondenti alla ricerca o all'anno selezionato. Prova a modificare il testo di ricerca oppure seleziona “Tutti gli anni”.</p>
        <?php } elseif ($empty_archive_count !== null) { ?>
            <p class="dci-at-empty__title text-decoration-none">
                <?php echo (int) $empty_archive_count > 0
                    ? 'Sono presenti elementi degli anni precedenti'
                    : 'Nessun elemento ancora pubblicato'; ?>
            </p>
            <div class="dci-at-empty__text text-decoration-none">
                <p class="mb-2">In questa sezione, dal <strong><?php echo esc_html($empty_start_year); ?></strong> a oggi, non risultano nuovi elementi pubblicati relativi a atti di concessione.</p>
                <?php if ((int) $empty_archive_count > 0) { ?>
                    <p class="mb-2">Sono tuttavia presenti nello storico
                        <strong><?php echo esc_html(sprintf(
                            _n('%s elemento', '%s elementi', (int) $empty_archive_count, 'design_comuni_italia'),
                            number_format_i18n((int) $empty_archive_count)
                        )); ?></strong>
                        pubblicati prima del <strong><?php echo esc_html($empty_start_year); ?></strong>. Questi contenuti non sono più mostrati nell'elenco pubblico ordinario.</p>
                    <p class="mb-2"><?php echo !empty($args['can_view_archive'])
                        ? 'Puoi consultare i contenuti storici nell’elenco riservato riportato sotto.'
                        : 'La consultazione diretta dello storico nel portale è riservata agli utenti autorizzati.'; ?></p>
                    <div class="dci-at-empty__access">
                        <span class="dci-at-empty__access-title">Come richiedere i dati e i documenti degli anni precedenti</span>
                        <p class="mb-2">Chiunque può presentare al Comune una richiesta di <strong>accesso civico generalizzato</strong> ai sensi dell'art. 5, comma 2, del d.lgs. 33/2013, indicando i dati o i documenti che desidera consultare, ad esempio il beneficiario, l'oggetto dell'atto e il periodo di riferimento, se conosciuti. La richiesta non deve essere motivata. L'accesso è valutato nel rispetto dei limiti dell'art. 5-bis, anche a tutela dei dati personali.</p>
                        <?php if ($empty_access_url !== '') { ?>
                            <a class="dci-at-empty__action" href="<?php echo esc_url($empty_access_url); ?>">Consulta le modalità per richiedere l'accesso civico</a>
                        <?php } else { ?>
                            <p class="mb-0">Per conoscere le modalità di presentazione, rivolgiti all'Ufficio relazioni con il pubblico (URP) o all'ufficio comunale che detiene i documenti.</p>
                        <?php } ?>
                    </div>
                <?php } else { ?>
                    <p class="mb-2">Non risultano, inoltre, elementi pubblicati negli anni precedenti.</p>
                <?php } ?>
            </div>
            <div class="dci-at-empty__law">
                <span class="dci-at-empty__law-title">Durata dell'obbligo di pubblicazione</span>
                Per gli atti di concessione, l'<a class="text-decoration-underline" href="https://www.gazzettaufficiale.it/atto/serie_generale/caricaArticolo?art.codiceRedazionale=13G00076&amp;art.dataPubblicazioneGazzetta=2013-04-05&amp;art.flagTipoArticolo=0&amp;art.idArticolo=8&amp;art.idGruppo=1&amp;art.idSottoArticolo=1&amp;art.idSottoArticolo1=10&amp;art.progressivo=0&amp;art.versione=1">art. 8, comma 3, del d.lgs. 33/2013</a> prevede, in via ordinaria, la pubblicazione per <strong>cinque anni</strong>, decorrenti dal <strong>1° gennaio dell'anno successivo</strong> a quello da cui decorre l'obbligo, e comunque fino a quando gli atti producono i propri effetti, fatti salvi i diversi termini previsti dalla normativa. Ad esempio, se l'obbligo decorre nel 2020, il quinquennio va dal 1° gennaio 2021 al 31 dicembre 2025.
            </div>
        <?php } else { ?>
            <p class="dci-at-empty__title text-decoration-none">Nessun contenuto attualmente disponibile nell'elenco pubblico</p>
            <p class="dci-at-empty__text text-decoration-none">Al momento non ci sono elementi da mostrare.</p>
        <?php } ?>
    </div>
</div>
