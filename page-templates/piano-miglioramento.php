<?php
/* Template Name: piano_miglioramento
 *
 * note legali template file
 *
 * @package Design_Comuni_Italia
 */
global $post;
get_header();

?>
	<main>
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<?php get_template_part("template-parts/hero/hero"); ?>
<main>
  <div class="container">
    <div class="col-12">
            <article class="richtext-wrapper lora">
                <p>
                    Nell'ottica di migliorare l'esperienza utente del seguente portale istituzionale,
                    verranno nei prossimi mesi intraprese delle azioni e applicate delle modifiche volte a velocizzare il caricamento del sito e dei servizi
                    per rispettare il Criterio "C.SE.4.1 - Velocità e tempi di risposta".                </p>
                <p>
                    In particolare, facendo riferimento al report
                    prodotto da <a href="https://pagespeed.web.dev/" target="_blank" rel="noopener noreferrer">Lighthouse
                        PageSpeed Insights</a>,
                    la performance del sito può essere incrementata nei punti seguenti:
                </p>
                <dl>
                    <dt>First Contentful Paint (FCP)</dt>
                    <dd>
                        <p>
                            <strong>Cos'è?</strong>
                            È una metrica incentrata sull'utente per misurare la
                            velocità di caricamento percepita perché segna il primo punto nella sequenza temporale di
                            caricamento
                            della pagina in cui l'utente può vedere qualsiasi cosa sullo schermo: un FCP veloce aiuta a
                            rassicurare l'utente che sta succedendo qualcosa.
                        </p>
                        <p>
                            <strong>Problema e azioni che verranno intraprese per risolverlo:</strong>
                            Alcune risorse JS e CSS bloccano la prima visualizzazione della pagina. Verrà implementato il
                            caricamento asincrono delle risorse più pesanti, e caricare solamente le risorse necessarie alla
                            corretta visualizzazione del contenuto. Verrà inoltre implementato il caricamento lento per le
                            immagini.
                        </p>
                 
                    </dd>

                    <dt>Largest Contentful Paint (LCP)</dt>
                    <dd>
                        <p>
                            <strong>Cos'è?</strong>
                            È una metrica Core Web Vital per misurare la
                            velocità di caricamento percepita perché segna il punto nella sequenza temporale di caricamento
                            della pagina in cui il contenuto principale della pagina è stato probabilmente caricato: un LCP
                            veloce aiuta a rassicurare l'utente che la pagina è utile.
                        </p>
                        <p>
                            <strong>Problema e azioni che verranno intraprese per risolverlo:</strong>
                            Alcune risorse JS e CSS bloccano la prima visualizzazione della pagina. Verrà implementato il
                            caricamento asincrono delle risorse più pesanti, e caricare solamente le risorse necessarie alla
                            corretta visualizzazione del contenuto. Verrà inoltre implementato il caricamento lento per le
                            immagini.
                        </p>             
                    </dd>

                    <dt>Total Blocking Time (TBT)</dt>
                    <dd>
                        <p>
                            <strong>Cos'è?</strong>
                            È una metrica di laboratorio per misurare la reattività
                            del carico perché aiuta a quantificare la gravità di quanto non interattiva è una pagina prima
                            che
                            diventi interattiva in modo affidabile: un TBT basso aiuta a garantire che la pagina sia
                            utilizzabile.
                        </p>
                        <p>
                            <strong>Problema e azioni che verranno intraprese per risolverlo:</strong>
                            Le risorse JS verranno divise per poter essere caricate un pezzo alla volta, all'occorrenza.
                        </p>
                    
                    </dd>

                    <dt>Time To Interactive (TTI)</dt>
                    <dd>
                        <p>
                            <strong>Cos'è?</strong>
                            È una metrica che indica quanto tempo la pagina impiega per diventare pienamente interattiva,
                            ossia quando
                            è presente il contenuto (FCP) e la pagina risponde alle interazioni dell'utente.
                        </p>
                        <p>
                            <strong>Problema e azioni che verranno intraprese per risolverlo:</strong>
                            Le risorse JS verranno divise e compresse per poter essere caricate un pezzo alla volta, all'occorrenza.
                        </p>
                  
                    </dd>

                    <dt>Cumulative Layout Shift (CLS)</dt>
                    <dd>
                        <p>
                            <strong>Cos'è?</strong>
                            È una metrica Core Web Vital incentrata sull'utente per misurare la
                            stabilità visiva perché aiuta a quantificare la frequenza con
                            cui gli utenti sperimentano cambiamenti di layout imprevisti: un CLS basso aiuta a garantire che
                            la
                            pagina sia piacevole.
                        </p>
                        <p>
                            <strong>Problema e azioni che verranno intraprese per risolverlo:</strong>
                            Alcune immagini non presentano gli attributi width e height. Verranno aggiunti tali attributi.
                        </p>
                   
                    </dd>

                    <dt>Speed Index</dt>
                    <dd>
                        <p>
                            <strong>Cos'è?</strong>
                            Misura la velocità con cui il contenuto viene visualizzato visivamente durante il caricamento
                            della
                            pagina.
                        </p>
                        <p>
                            <strong>Problema e azioni che verranno intraprese per risolverlo:</strong>
                            Verrà effettuato un refactor del backend per poter ottimizzare l'esecuzione del codice e di
                            conseguenza il caricamento delle pagine web. Verrà, infine, ricreata l'infrastruttura server,
                            incrementandone le risorse e ottimizzandone l'uso, per poter eseguire l'applicazione e i servizi
                            associati in modo più
                            performante.
                        </p>
           
                    </dd>
                </dl><br><br>
		    <p><strong>Il piano di miglioramento prevede di recepire, entro il 2025, i consigli riportati da Google Lighthouse maggiormente impattanti sul punteggio di ciascuna metrica. L'obiettivo è di conseguire stabilmente un punteggio superiore a 50 nella compilazione dei tipi di istanze più frequenti.</strong></p>
            </article>
        </div>
</div>


			<?php get_template_part("template-parts/common/valuta-servizio"); ?>
			<?php get_template_part("template-parts/common/assistenza-contatti"); ?>
							
		<?php 
			endwhile; // End of the loop.
		?>
	</main>
<?php
get_footer();
