<?php
/**
 * Guida operativa nel pannello di Amministrazione Trasparente.
 *
 * @package Design_Comuni_Italia
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Registra la guida soltanto per i portali e gli utenti abilitati.
 */
function dci_guida_trasparenza_admin_menu() {
    if (
        'true' !== dci_get_option('ck_abilita_trasparenza')
        || !current_user_can('publish_elementi_trasparenza')
    ) {
        return;
    }

    add_submenu_page(
        'edit.php?post_type=elemento_trasparenza',
        __('Guida alla pubblicazione', 'design_comuni_italia'),
        __('Guida alla pubblicazione', 'design_comuni_italia'),
        'publish_elementi_trasparenza',
        'dci-guida-trasparenza',
        'dci_guida_trasparenza_render'
    );
}
add_action('admin_menu', 'dci_guida_trasparenza_admin_menu');

/**
 * Carica lo stile solo nella pagina della guida.
 *
 * @param string $hook_suffix Identificativo della schermata amministrativa.
 */
function dci_guida_trasparenza_admin_assets($hook_suffix) {
    if ('elemento_trasparenza_page_dci-guida-trasparenza' !== $hook_suffix) {
        return;
    }

    $stylesheet_path = get_theme_file_path('/assets/css/admin-guida-trasparenza.css');

    wp_enqueue_style(
        'dci-admin-guida-trasparenza',
        get_theme_file_uri('/assets/css/admin-guida-trasparenza.css'),
        array(),
        file_exists($stylesheet_path) ? (string) filemtime($stylesheet_path) : null
    );

    /* Riprende il colore istituzionale impostato nel CSS personalizzato dell'ente. */
    $custom_css = wp_get_custom_css();
    $guide_color = '#17334f';

    if (
        preg_match(
            '/\.it-header-center-wrapper[^\{]*\{[^\}]*background(?:-color)?\s*:\s*(#[0-9a-f]{3,8})/i',
            $custom_css,
            $color_match
        )
    ) {
        $guide_color = sanitize_hex_color($color_match[1]) ?: $guide_color;
    }

    wp_add_inline_style(
        'dci-admin-guida-trasparenza',
        sprintf('.dci-admin-guide{--dci-guide-accent:%s;}', $guide_color)
    );

    $script_path = get_theme_file_path('/assets/js/admin-guida-trasparenza.js');

    wp_enqueue_script(
        'dci-admin-guida-trasparenza',
        get_theme_file_uri('/assets/js/admin-guida-trasparenza.js'),
        array(),
        file_exists($script_path) ? (string) filemtime($script_path) : null,
        true
    );
}
add_action('admin_enqueue_scripts', 'dci_guida_trasparenza_admin_assets');

/**
 * Stampa il contenuto statico della guida.
 */
function dci_guida_trasparenza_render() {
    if (
        'true' !== dci_get_option('ck_abilita_trasparenza')
        || !current_user_can('publish_elementi_trasparenza')
    ) {
        wp_die(
            esc_html__('Non hai i permessi per accedere a questa guida.', 'design_comuni_italia'),
            esc_html__('Accesso non consentito', 'design_comuni_italia'),
            array('response' => 403)
        );
    }
    ?>
    <div class="wrap dci-admin-guide">
        <header class="dci-admin-guide__header">
            <p class="dci-admin-guide__label">
                <span class="dashicons dashicons-welcome-learn-more" aria-hidden="true"></span>
                <?php esc_html_e('Amministrazione Trasparente', 'design_comuni_italia'); ?>
            </p>
            <h1><?php esc_html_e('Guida alla pubblicazione', 'design_comuni_italia'); ?></h1>
            <p class="dci-admin-guide__intro">
                <?php esc_html_e('Indicazioni pratiche per preparare, controllare e pubblicare correttamente i contenuti della Trasparenza.', 'design_comuni_italia'); ?>
            </p>
        </header>

        <div class="dci-admin-guide__toolbar">
            <div class="dci-admin-guide__search-wrap" role="search">
                <span class="dashicons dashicons-search" aria-hidden="true"></span>
                <label class="screen-reader-text" for="dci-admin-guide-search"><?php esc_html_e('Cerca nella guida', 'design_comuni_italia'); ?></label>
                <input
                    id="dci-admin-guide-search"
                    class="dci-admin-guide__search"
                    type="search"
                    placeholder="<?php esc_attr_e('Cerca nella guida…', 'design_comuni_italia'); ?>"
                    autocomplete="off"
                >
                <button class="dci-admin-guide__clear" type="button" hidden><?php esc_html_e('Cancella', 'design_comuni_italia'); ?></button>
            </div>
            <div class="dci-admin-guide__toolbar-actions" aria-label="<?php esc_attr_e('Comandi delle sezioni', 'design_comuni_italia'); ?>">
                <button class="button" type="button" data-guide-action="expand"><?php esc_html_e('Espandi tutto', 'design_comuni_italia'); ?></button>
                <button class="button" type="button" data-guide-action="collapse"><?php esc_html_e('Chiudi tutto', 'design_comuni_italia'); ?></button>
                <div class="dci-admin-guide__output-menu">
                    <button
                        class="button dci-admin-guide__more"
                        type="button"
                        aria-expanded="false"
                        aria-controls="dci-admin-guide-output-menu"
                        aria-label="<?php esc_attr_e('Altre azioni', 'design_comuni_italia'); ?>"
                    >
                        <span class="dashicons dashicons-ellipsis" aria-hidden="true"></span>
                    </button>
                    <div id="dci-admin-guide-output-menu" class="dci-admin-guide__output-popover" hidden>
                        <button type="button" data-guide-output="print">
                            <span class="dashicons dashicons-printer" aria-hidden="true"></span>
                            <?php esc_html_e('Stampa', 'design_comuni_italia'); ?>
                        </button>
                        <button type="button" data-guide-output="pdf">
                            <span class="dashicons dashicons-pdf" aria-hidden="true"></span>
                            <?php esc_html_e('Esporta PDF', 'design_comuni_italia'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <p class="dci-admin-guide__results" role="status" aria-live="polite"></p>
        <div class="notice notice-info inline dci-admin-guide__pdf-help" hidden role="status">
            <p><?php esc_html_e('Nella finestra che si apre, seleziona “Salva come PDF” come destinazione e conferma con “Salva”.', 'design_comuni_italia'); ?></p>
        </div>

        <div class="dci-admin-guide__layout">
            <nav class="dci-admin-guide__index" aria-label="<?php esc_attr_e('Indice della guida', 'design_comuni_italia'); ?>">
                <h2><?php esc_html_e('In questa guida', 'design_comuni_italia'); ?></h2>
                <ol>
                    <li><a href="#premessa"><span class="dashicons dashicons-info-outline" aria-hidden="true"></span><?php esc_html_e('Premessa e informazioni principali', 'design_comuni_italia'); ?></a></li>
                    <li><a href="#portale"><span class="dashicons dashicons-admin-site-alt3" aria-hidden="true"></span><?php esc_html_e('Il nostro portale', 'design_comuni_italia'); ?></a></li>
                    <li><a href="#procedura"><span class="dashicons dashicons-edit-page" aria-hidden="true"></span><?php esc_html_e('Pubblicazione sulla Trasparenza', 'design_comuni_italia'); ?></a></li>
                    <li><a href="#tipologie-personalizzate"><span class="dashicons dashicons-screenoptions" aria-hidden="true"></span><?php esc_html_e('Tipologie personalizzate', 'design_comuni_italia'); ?></a></li>
                    <li><a href="#competenze"><span class="dashicons dashicons-groups" aria-hidden="true"></span><?php esc_html_e('Le nostre competenze sul portale', 'design_comuni_italia'); ?></a></li>
                    <li><a href="#assistenza"><span class="dashicons dashicons-sos" aria-hidden="true"></span><?php esc_html_e('Assistenza', 'design_comuni_italia'); ?></a></li>
                </ol>
            </nav>

            <main class="dci-admin-guide__content">
                <section id="premessa">
    <span class="dci-admin-guide__number" aria-hidden="true">1</span>
    <h2><?php esc_html_e('Premessa e informazioni principali', 'design_comuni_italia'); ?></h2>

    <h3><?php esc_html_e('Cos’è l’Amministrazione Trasparente', 'design_comuni_italia'); ?></h3>

    <p>
        <?php esc_html_e(
            'L’Amministrazione Trasparente è la sezione obbligatoria del sito istituzionale attraverso la quale le Pubbliche Amministrazioni rendono accessibili ai cittadini dati, documenti e informazioni relativi alla propria organizzazione, attività, utilizzo delle risorse pubbliche, procedimenti e risultati.',
            'design_comuni_italia'
        ); ?>
    </p>

    <p>
        <?php esc_html_e(
            'La trasparenza amministrativa ha lo scopo di garantire la conoscibilità dell’azione della Pubblica Amministrazione, favorire forme diffuse di controllo sul perseguimento delle funzioni istituzionali e sull’utilizzo delle risorse pubbliche, prevenire fenomeni di corruzione e promuovere legalità, integrità e responsabilità nell’attività amministrativa.',
            'design_comuni_italia'
        ); ?>
    </p>

    <p>
        <?php esc_html_e(
            'Il principale riferimento normativo è il Decreto Legislativo 14 marzo 2013, n. 33, che disciplina il diritto di accesso civico e gli obblighi di pubblicità, trasparenza e diffusione delle informazioni da parte delle Pubbliche Amministrazioni. Il decreto stabilisce quali dati devono essere pubblicati, le modalità di pubblicazione, i criteri di qualità delle informazioni e l’organizzazione della sezione “Amministrazione Trasparente”.',
            'design_comuni_italia'
        ); ?>
    </p>

    <p>
        <strong><?php esc_html_e('Riferimento normativo principale:', 'design_comuni_italia'); ?></strong><br>
        <a
            href="https://www.normattiva.it/uri-res/N2Ls?urn:nir:stato:decreto.legislativo:2013-03-14;33"
            target="_blank"
            rel="noopener noreferrer"
        >
            <?php esc_html_e('Decreto Legislativo 14 marzo 2013, n. 33 – Normattiva', 'design_comuni_italia'); ?>
        </a>
    </p>

    <h3><?php esc_html_e('Come deve essere organizzata', 'design_comuni_italia'); ?></h3>

    <p>
        <?php esc_html_e(
            'La sezione deve essere organizzata secondo la struttura e le sottosezioni previste dalla normativa e dalle indicazioni dell’Autorità Nazionale Anticorruzione (ANAC). Ogni documento, dato o informazione deve essere inserito nella corretta sottosezione e mantenuto aggiornato per il periodo previsto dalla normativa.',
            'design_comuni_italia'
        ); ?>
    </p>

    <p>
        <?php esc_html_e(
            'I contenuti pubblicati devono rispettare, tra gli altri, i principi di completezza, aggiornamento, tempestività, comprensibilità, integrità, semplicità di consultazione, accessibilità, conformità ai documenti originali e riutilizzabilità. La sezione deve inoltre essere liberamente consultabile e non deve richiedere registrazione o autenticazione da parte dell’utente.',
            'design_comuni_italia'
        ); ?>
    </p>

    <h3><?php esc_html_e('Aggiornamenti ANAC', 'design_comuni_italia'); ?></h3>

    <p>
        <?php esc_html_e(
            'La struttura dell’Amministrazione Trasparente e le modalità con cui devono essere rappresentati e pubblicati i dati non devono essere considerate immutabili. ANAC aggiorna periodicamente indicazioni, schemi di pubblicazione, modalità tecniche e obblighi applicativi, anche in conseguenza di modifiche normative.',
            'design_comuni_italia'
        ); ?>
    </p>

    <p>
        <?php esc_html_e(
            'È pertanto consigliato consultare periodicamente il portale istituzionale e la documentazione pubblicata da ANAC, verificando eventuali aggiornamenti della struttura delle sottosezioni, degli schemi di pubblicazione e dei relativi obblighi. Questa verifica è importante per mantenere la sezione conforme alla normativa vigente ed evitare pubblicazioni incomplete, non aggiornate o collocate in sezioni non più corrette.',
            'design_comuni_italia'
        ); ?>
    </p>

    <p>
        <strong><?php esc_html_e('Riferimenti ANAC:', 'design_comuni_italia'); ?></strong>
    </p>

    <ul>
        <li>
            <a
                href="https://guida-servizi.anticorruzione.it/it/help/trasparenza/amministrazione-trasparente/"
                target="_blank"
                rel="noopener noreferrer"
            >
                <?php esc_html_e('Guida ANAC – Amministrazione Trasparente', 'design_comuni_italia'); ?>
            </a>
        </li>

        <li>
            <a
                href="https://www.anticorruzione.it/-/piano-nazionale-anticorruzione-2025"
                target="_blank"
                rel="noopener noreferrer"
            >
                <?php esc_html_e('Piano Nazionale Anticorruzione 2025 – Delibera ANAC n. 19/2026', 'design_comuni_italia'); ?>
            </a>
        </li>

        <li>
            <a
                href="https://www.anticorruzione.it/"
                target="_blank"
                rel="noopener noreferrer"
            >
                <?php esc_html_e('Portale istituzionale ANAC', 'design_comuni_italia'); ?>
            </a>
        </li>
    </ul>

    <div class="notice notice-warning inline">
        <p>
            <strong><?php esc_html_e('Attenzione:', 'design_comuni_italia'); ?></strong>
            <?php esc_html_e(
                'pubblica soltanto dati pertinenti all’obbligo normativo, verifica sempre la presenza di informazioni personali e controlla periodicamente eventuali aggiornamenti normativi o indicazioni pubblicate da ANAC.',
                'design_comuni_italia'
            ); ?>
        </p>
    </div>
</section>

<section id="portale">
    <span class="dci-admin-guide__number" aria-hidden="true">2</span>
    <h2><?php esc_html_e('Il nostro portale', 'design_comuni_italia'); ?></h2>
    <p><?php esc_html_e('Il portale istituzionale organizza informazioni, servizi e documenti in aree collegate tra loro. Ogni contenuto deve essere inserito nella sezione corretta, usando la tipologia prevista e compilando con attenzione tutti i campi richiesti.', 'design_comuni_italia'); ?></p>
    <p><?php esc_html_e('Prima di creare un nuovo contenuto verifica che non sia già presente, individua la struttura responsabile e prepara testi, date, collegamenti e allegati. Una corretta classificazione rende le informazioni più facili da trovare e da mantenere aggiornate.', 'design_comuni_italia'); ?></p>
    <div class="dci-admin-guide__example">
        <h3><?php esc_html_e('Principi da seguire', 'design_comuni_italia'); ?></h3>
        <p><?php esc_html_e('Usa titoli chiari, testi sintetici, fonti ufficiali, collegamenti stabili e documenti accessibili. Evita duplicazioni, abbreviazioni poco comprensibili e informazioni prive di data o referente.', 'design_comuni_italia'); ?></p>
    </div>
</section>

<section id="procedura">
    <span class="dci-admin-guide__number" aria-hidden="true">3</span>
    <h2><?php esc_html_e('Come si pubblica sulla Trasparenza e regole di pubblicazione', 'design_comuni_italia'); ?></h2>

    <ol class="dci-admin-guide__steps">
        <li>
            <strong><?php esc_html_e('Apri “Amministrazione Trasparente”.', 'design_comuni_italia'); ?></strong>
            <span><?php esc_html_e('Seleziona “Aggiungi nuovo” dal menu laterale.', 'design_comuni_italia'); ?></span>
        </li>

        <li>
            <strong><?php esc_html_e('Compila i campi.', 'design_comuni_italia'); ?></strong>
            <span><?php esc_html_e('Inserisci titolo, descrizione e categoria prevista.', 'design_comuni_italia'); ?></span>
        </li>

        <li>
            <strong><?php esc_html_e('Aggiungi documenti o collegamenti.', 'design_comuni_italia'); ?></strong>
            <span><?php esc_html_e('Usa file accessibili oppure una fonte ufficiale stabile.', 'design_comuni_italia'); ?></span>
        </li>

        <li>
            <strong><?php esc_html_e('Controlla e pubblica.', 'design_comuni_italia'); ?></strong>
            <span><?php esc_html_e('Verifica i dati inseriti prima di rendere pubblico il contenuto.', 'design_comuni_italia'); ?></span>
        </li>
    </ol>

    <h3><?php esc_html_e('Documenti e accessibilità', 'design_comuni_italia'); ?></h3>
    <p><?php esc_html_e('Preferisci documenti nativi digitali con testo selezionabile, titoli strutturati e ordine di lettura corretto. Evita le scansioni quando è disponibile il file originale e assegna ai file nomi chiari e descrittivi.', 'design_comuni_italia'); ?></p>

    <div class="dci-admin-guide__example">
        <h3><?php esc_html_e('Nomi dei file', 'design_comuni_italia'); ?></h3>
        <p><strong><?php esc_html_e('Nome chiaro:', 'design_comuni_italia'); ?></strong> piano-triennale-prevenzione-corruzione-2026.pdf</p>
        <p><strong><?php esc_html_e('Nome da evitare:', 'design_comuni_italia'); ?></strong> scansione001_def.pdf</p>
    </div>

    <?php
    /*
     * Per aggiungere un'immagine statica:
     * 1. copiarla in assets/images/guida-trasparenza/;
     * 2. aggiungere qui un elemento <figure> con un tag <img>;
     * 3. usare get_theme_file_uri() per costruire l'indirizzo e
     *    inserire sempre un testo alternativo significativo.
     */
    ?>
</section>
                <section id="tipologie-personalizzate">
                    <span class="dci-admin-guide__number" aria-hidden="true">4</span>
                    <h2><?php esc_html_e('Come si pubblica nelle tipologie personalizzate', 'design_comuni_italia'); ?></h2>
                    <p><?php esc_html_e('Le tipologie personalizzate raccolgono contenuti con campi e regole specifiche, come uffici, persone, luoghi, servizi, notizie o altri elementi previsti dal portale. Seleziona dal menu la tipologia corretta e usa “Aggiungi nuovo”.', 'design_comuni_italia'); ?></p>
                    <p><?php esc_html_e('Compila i campi nell’ordine proposto, collega eventuali contenuti già presenti e controlla anteprima, date e visibilità prima della pubblicazione. Non usare una tipologia diversa solo perché contiene campi simili.', 'design_comuni_italia'); ?></p>
                    <div class="dci-admin-guide__example">
                        <h3><?php esc_html_e('Prima di pubblicare', 'design_comuni_italia'); ?></h3>
                        <p><?php esc_html_e('Verifica titolo, stato di pubblicazione, immagine o allegati, collegamenti ad altre sezioni, responsabile del contenuto e data del prossimo aggiornamento.', 'design_comuni_italia'); ?></p>
                    </div>
                </section>

                <section id="competenze">
                    <span class="dci-admin-guide__number" aria-hidden="true">5</span>
                    <h2><?php esc_html_e('Le nostre competenze sul portale', 'design_comuni_italia'); ?></h2>
                    <p><?php esc_html_e('Ogni redattore opera esclusivamente nelle aree e sui contenuti assegnati. È responsabile della correttezza dei dati inseriti, della qualità dei documenti, della scelta della sezione e del rispetto delle scadenze di aggiornamento.', 'design_comuni_italia'); ?></p>
                    <p><?php esc_html_e('Dopo la pubblicazione è necessario aprire il contenuto sul sito e controllare titolo, categoria, allegati e collegamenti. Le verifiche periodiche permettono di aggiornare, correggere o archiviare i contenuti secondo le regole dell’ente.', 'design_comuni_italia'); ?></p>
                </section>

                <section id="assistenza">
                    <span class="dci-admin-guide__number" aria-hidden="true">6</span>
                    <h2><?php esc_html_e('Assistenza', 'design_comuni_italia'); ?></h2>
                    <p><?php esc_html_e('Se non trovi la categoria corretta o hai dubbi sui dati da pubblicare, interrompi la procedura e contatta il referente interno per la Trasparenza o l’assistenza del portale.', 'design_comuni_italia'); ?></p>
                </section>
                <div class="dci-admin-guide__empty" hidden>
                    <span class="dashicons dashicons-search" aria-hidden="true"></span>
                    <h2><?php esc_html_e('Nessun risultato', 'design_comuni_italia'); ?></h2>
                    <p><?php esc_html_e('Prova a usare parole diverse o cancella la ricerca.', 'design_comuni_italia'); ?></p>
                </div>
            </main>
        </div>
    </div>
    <?php
}
