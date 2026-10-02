<?php
/** Durata normativa, distinta dalla soglia per anno usata dagli elenchi. */
$is_dirigenziale = ($args['post_type'] ?? '') === 'incarico_dirig';
?>
<div class="dci-at-empty__law">
    <span class="dci-at-empty__law-title">Durata dell'obbligo di pubblicazione</span>
    <?php if ($is_dirigenziale) { ?>
        <p class="mb-2">Per gli incarichi dirigenziali, l'<a class="text-decoration-underline" href="https://www.anticorruzione.it/-/titolari-di-incarichi-politici-art.-14-co.-1-d.lgs-33/2013-">art. 14, comma 2, del d.lgs. 33/2013, secondo i chiarimenti ANAC</a> prevede la pubblicazione per i <strong>tre anni successivi alla cessazione dell'incarico</strong>, fatte salve le disposizioni specifiche sulle informazioni patrimoniali. Il triennio si calcola dalla data effettiva di cessazione, considerando giorno, mese e anno.</p>
        <p class="mb-0">L'elenco del portale è attualmente suddiviso per anno di pubblicazione. L'inserimento di un contenuto nello storico sulla base dell'anno non attesta, da solo, la cessazione del relativo obbligo di pubblicazione.</p>
    <?php } else { ?>
        <p class="mb-2">Gli incarichi conferiti e autorizzati ai dipendenti sono oggetto di pubblicazione ai sensi dell'art. 18 del d.lgs. 33/2013. L'<a class="text-decoration-underline" href="https://www.gazzettaufficiale.it/atto/serie_generale/caricaArticolo?art.codiceRedazionale=13G00076&amp;art.dataPubblicazioneGazzetta=2013-04-05&amp;art.flagTipoArticolo=0&amp;art.idArticolo=8&amp;art.idGruppo=1&amp;art.idSottoArticolo=1&amp;art.idSottoArticolo1=10&amp;art.progressivo=0&amp;art.versione=1">art. 8, comma 3</a> prevede, in via ordinaria, <strong>cinque anni</strong> dal <strong>1° gennaio dell'anno successivo</strong> a quello da cui decorre l'obbligo, e comunque fino a quando gli atti producono effetti, fatti salvi i diversi termini previsti dalla normativa.</p>
        <p class="mb-0">Ad esempio, se l'obbligo decorre nel 2020, il quinquennio va dal 1° gennaio 2021 al 31 dicembre 2025.</p>
    <?php } ?>
</div>
