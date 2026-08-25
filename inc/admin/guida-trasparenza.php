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
        || !current_user_can('edit_elementi_trasparenza')
    ) {
        return;
    }

    add_submenu_page(
        'edit.php?post_type=elemento_trasparenza',
        __('Guida alla pubblicazione', 'design_comuni_italia'),
        __('Guida alla pubblicazione', 'design_comuni_italia'),
        'edit_elementi_trasparenza',
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
        || !current_user_can('edit_elementi_trasparenza')
    ) {
        wp_die(
            esc_html__('Non hai i permessi per accedere a questa guida.', 'design_comuni_italia'),
            esc_html__('Accesso non consentito', 'design_comuni_italia'),
            array('response' => 403)
        );
    }

    $is_internal_portal = dci_get_option('ck_portalesoloperusoesterno') !== 'true';
    $can_create_items   = current_user_can('create_elementi_trasparenza');
    $can_publish_items  = current_user_can('publish_elementi_trasparenza');
    $new_item_url       = admin_url('post-new.php?post_type=elemento_trasparenza');
    $items_url          = admin_url('edit.php?post_type=elemento_trasparenza');
    $offices_url        = admin_url('edit.php?post_type=unita_organizzativa');
    $public_page_url    = home_url('/amministrazione-trasparente/');
    $documents_number   = $is_internal_portal ? 6 : 5;
    $verification_number = $is_internal_portal ? 7 : 6;
    $assistance_number  = $is_internal_portal ? 8 : 7;
    ?>
    <div class="wrap dci-admin-guide">
        <header class="dci-admin-guide__header">
            <p class="dci-admin-guide__label">
                <span class="dashicons dashicons-welcome-learn-more" aria-hidden="true"></span>
                <?php esc_html_e('Amministrazione Trasparente', 'design_comuni_italia'); ?>
            </p>
            <h1><?php esc_html_e('Guida alla pubblicazione', 'design_comuni_italia'); ?></h1>
            <p class="dci-admin-guide__intro">
                <?php esc_html_e('Istruzioni operative per creare, classificare, controllare e mantenere aggiornati i contenuti con il nuovo sistema di pubblicazione.', 'design_comuni_italia'); ?>
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
                    <li><a href="#nuovo-sistema"><span class="dashicons dashicons-info-outline" aria-hidden="true"></span><?php esc_html_e('Il nuovo sistema', 'design_comuni_italia'); ?></a></li>
                    <li><a href="#prima-di-iniziare"><span class="dashicons dashicons-clipboard" aria-hidden="true"></span><?php esc_html_e('Prima di iniziare', 'design_comuni_italia'); ?></a></li>
                    <li><a href="#compilazione"><span class="dashicons dashicons-edit-page" aria-hidden="true"></span><?php esc_html_e('Creare un elemento', 'design_comuni_italia'); ?></a></li>
                    <li><a href="#sezione"><span class="dashicons dashicons-category" aria-hidden="true"></span><?php esc_html_e('Scegliere la sezione', 'design_comuni_italia'); ?></a></li>
                    <?php if ($is_internal_portal) : ?>
                        <li><a href="#organi-politici"><span class="dashicons dashicons-groups" aria-hidden="true"></span><?php esc_html_e('Sindaco, Giunta e Consiglio', 'design_comuni_italia'); ?></a></li>
                    <?php endif; ?>
                    <li><a href="#documenti"><span class="dashicons dashicons-media-document" aria-hidden="true"></span><?php esc_html_e('Documenti e collegamenti', 'design_comuni_italia'); ?></a></li>
                    <li><a href="#verifica"><span class="dashicons dashicons-yes-alt" aria-hidden="true"></span><?php esc_html_e('Pubblicare e verificare', 'design_comuni_italia'); ?></a></li>
                    <li><a href="#assistenza"><span class="dashicons dashicons-sos" aria-hidden="true"></span><?php esc_html_e('Dubbi e assistenza', 'design_comuni_italia'); ?></a></li>
                </ol>
            </nav>

            <main class="dci-admin-guide__content">
                <section id="nuovo-sistema">
                    <span class="dci-admin-guide__number" aria-hidden="true">1</span>
                    <h2><?php esc_html_e('Il nuovo sistema di pubblicazione', 'design_comuni_italia'); ?></h2>
                    <p><?php esc_html_e('Nel nuovo sistema ogni contenuto ordinario viene creato come “Elemento Trasparenza” e associato a una sola sezione dell’alberatura. La sezione scelta stabilisce dove il contenuto comparirà sul sito pubblico.', 'design_comuni_italia'); ?></p>
                    <div class="dci-admin-guide__rule">
                        <span class="dashicons dashicons-lightbulb" aria-hidden="true"></span>
                        <p><strong><?php esc_html_e('Regola principale:', 'design_comuni_italia'); ?></strong> <?php esc_html_e('per pubblicare un nuovo dato, documento o collegamento usa “Aggiungi un Elemento Trasparenza”, quindi seleziona la sottosezione più specifica disponibile.', 'design_comuni_italia'); ?></p>
                    </div>
                    <?php if (!$can_publish_items) : ?>
                        <div class="notice notice-info inline">
                            <p><strong><?php esc_html_e('Il tuo profilo può preparare e modificare i contenuti, ma non pubblicarli direttamente.', 'design_comuni_italia'); ?></strong> <?php esc_html_e('Salva il lavoro come bozza e comunicalo al responsabile abilitato alla pubblicazione.', 'design_comuni_italia'); ?></p>
                        </div>
                    <?php endif; ?>
                    <p><?php esc_html_e('Le categorie principali servono a ordinare l’alberatura: non scegliere una macro-area quando esiste una sottosezione adatta. I redattori non devono creare, rinominare o spostare le categorie.', 'design_comuni_italia'); ?></p>
                    <div class="dci-admin-guide__quick-actions">
                        <?php if ($can_create_items) : ?>
                            <a class="button button-primary" href="<?php echo esc_url($new_item_url); ?>"><span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span><?php esc_html_e('Crea un elemento', 'design_comuni_italia'); ?></a>
                        <?php endif; ?>
                        <a class="button" href="<?php echo esc_url($items_url); ?>"><span class="dashicons dashicons-list-view" aria-hidden="true"></span><?php esc_html_e('Gestisci gli elementi', 'design_comuni_italia'); ?></a>
                        <a class="button" href="<?php echo esc_url($public_page_url); ?>" target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-visibility" aria-hidden="true"></span><?php esc_html_e('Apri la sezione pubblica', 'design_comuni_italia'); ?></a>
                    </div>
                    <p class="description"><?php esc_html_e('La pubblicazione deve rispettare gli obblighi applicabili, la protezione dei dati personali e le indicazioni organizzative dell’ente.', 'design_comuni_italia'); ?> <a href="https://www.normattiva.it/uri-res/N2Ls?urn:nir:stato:decreto.legislativo:2013-03-14;33!vig=" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Consulta il D.Lgs. 33/2013', 'design_comuni_italia'); ?></a>.</p>
                </section>

                <section id="prima-di-iniziare">
                    <span class="dci-admin-guide__number" aria-hidden="true">2</span>
                    <h2><?php esc_html_e('Prima di iniziare', 'design_comuni_italia'); ?></h2>
                    <p><?php esc_html_e('Una verifica di pochi minuti evita duplicati, collegamenti errati e documenti da sostituire subito dopo la pubblicazione.', 'design_comuni_italia'); ?></p>
                    <ul class="dci-admin-guide__checklist">
                        <li><?php esc_html_e('Cerca nell’elenco degli Elementi Trasparenza e sul sito pubblico: aggiorna un contenuto esistente se tratta lo stesso obbligo.', 'design_comuni_italia'); ?></li>
                        <li><?php esc_html_e('Individua la sottosezione corretta e verifica eventuali istruzioni del referente della Trasparenza.', 'design_comuni_italia'); ?></li>
                        <li><?php esc_html_e('Prepara un titolo comprensibile anche fuori dal contesto dell’ufficio, senza sigle non spiegate.', 'design_comuni_italia'); ?></li>
                        <li><?php esc_html_e('Raccogli la versione definitiva dei file e gli indirizzi completi dei collegamenti.', 'design_comuni_italia'); ?></li>
                        <li><?php esc_html_e('Controlla date, periodo di riferimento, ufficio responsabile e scadenza del prossimo aggiornamento.', 'design_comuni_italia'); ?></li>
                        <li><?php esc_html_e('Verifica che non siano presenti dati personali eccedenti, firme autografe non necessarie o informazioni da oscurare.', 'design_comuni_italia'); ?></li>
                    </ul>
                    <div class="notice notice-warning inline">
                        <p><strong><?php esc_html_e('In caso di dubbio sui dati personali:', 'design_comuni_italia'); ?></strong> <?php esc_html_e('non pubblicare il file finché il referente competente non ha confermato contenuto ed eventuali oscuramenti.', 'design_comuni_italia'); ?></p>
                    </div>
                </section>

                <section id="compilazione">
                    <span class="dci-admin-guide__number" aria-hidden="true">3</span>
                    <h2><?php esc_html_e('Creare e compilare un Elemento Trasparenza', 'design_comuni_italia'); ?></h2>
                    <ol class="dci-admin-guide__steps">
                        <li><strong><?php esc_html_e('Apri il modulo.', 'design_comuni_italia'); ?></strong><span><?php esc_html_e('Dal menu “Amministrazione Trasparente” scegli “Aggiungi un Elemento Trasparenza”.', 'design_comuni_italia'); ?></span></li>
                        <li><strong><?php esc_html_e('Inserisci il titolo.', 'design_comuni_italia'); ?></strong><span><?php esc_html_e('Descrivi con precisione il contenuto e, quando utile, indica anno o periodo di riferimento.', 'design_comuni_italia'); ?></span></li>
                        <li><strong><?php esc_html_e('Compila l’apertura.', 'design_comuni_italia'); ?></strong><span><?php esc_html_e('L’immagine è facoltativa. Usa la descrizione breve per una sintesi immediata, entro 1024 caratteri visibili.', 'design_comuni_italia'); ?></span></li>
                        <li><strong><?php esc_html_e('Seleziona la sezione.', 'design_comuni_italia'); ?></strong><span><?php esc_html_e('Cerca per parole chiave, scegli una sola sottosezione e controlla il riepilogo verde “Sezione selezionata”.', 'design_comuni_italia'); ?></span></li>
                        <li><strong><?php esc_html_e('Aggiungi il contenuto.', 'design_comuni_italia'); ?></strong><span><?php esc_html_e('Inserisci una descrizione di approfondimento, un collegamento, uno o più allegati oppure una combinazione di questi elementi.', 'design_comuni_italia'); ?></span></li>
                        <li><strong><?php esc_html_e('Salva o pubblica.', 'design_comuni_italia'); ?></strong><span><?php echo $can_publish_items ? esc_html__('Se il controllo non è concluso salva una bozza; usa “Pubblica” soltanto quando il contenuto è pronto per i cittadini.', 'design_comuni_italia') : esc_html__('Salva il contenuto come bozza e segnalalo al responsabile abilitato, che completerà il controllo e la pubblicazione.', 'design_comuni_italia'); ?></span></li>
                    </ol>

                    <h3><?php esc_html_e('A cosa servono i campi', 'design_comuni_italia'); ?></h3>
                    <dl class="dci-admin-guide__field-map">
                        <div><dt><?php esc_html_e('Titolo *', 'design_comuni_italia'); ?></dt><dd><?php esc_html_e('Nome pubblico dell’elemento. È obbligatorio.', 'design_comuni_italia'); ?></dd></div>
                        <div><dt><?php esc_html_e('Immagine', 'design_comuni_italia'); ?></dt><dd><?php esc_html_e('Immagine facoltativa mostrata nella card; carica soltanto un formato immagine.', 'design_comuni_italia'); ?></dd></div>
                        <div><dt><?php esc_html_e('Descrizione breve', 'design_comuni_italia'); ?></dt><dd><?php esc_html_e('Sintesi visibile negli elenchi e nella pagina, massimo 1024 caratteri.', 'design_comuni_italia'); ?></dd></div>
                        <div><dt><?php esc_html_e('Categoria Trasparenza *', 'design_comuni_italia'); ?></dt><dd><?php esc_html_e('Destinazione pubblica del contenuto. È obbligatoria e deve essere una sola.', 'design_comuni_italia'); ?></dd></div>
                        <div><dt><?php esc_html_e('Descrizione', 'design_comuni_italia'); ?></dt><dd><?php esc_html_e('Testo di approfondimento mostrato nella pagina di dettaglio.', 'design_comuni_italia'); ?></dd></div>
                        <div><dt><?php esc_html_e('Documenti e collegamenti *', 'design_comuni_italia'); ?></dt><dd><?php esc_html_e('Risorsa principale: inserisci almeno un file o un collegamento pertinente.', 'design_comuni_italia'); ?></dd></div>
                        <div><dt><?php esc_html_e('Extra', 'design_comuni_italia'); ?></dt><dd><?php esc_html_e('Opzioni per nuova scheda, apertura diretta ed evidenza. Usale solo quando servono.', 'design_comuni_italia'); ?></dd></div>
                        <div><dt><?php esc_html_e('Contenuti collegati', 'design_comuni_italia'); ?></dt><dd><?php esc_html_e('Rimandi facoltativi ad altri Elementi Trasparenza già pubblicati.', 'design_comuni_italia'); ?></dd></div>
                    </dl>
                </section>

                <section id="sezione">
                    <span class="dci-admin-guide__number" aria-hidden="true">4</span>
                    <h2><?php esc_html_e('Scegliere correttamente la sezione', 'design_comuni_italia'); ?></h2>
                    <p><?php esc_html_e('Scrivi una o più parole nel campo “Cerca la sezione in cui pubblicare”. Se l’elenco è lungo puoi usare “Espandi elenco”. Il numero accanto a una voce indica quanti contenuti risultano già pubblicati e può aiutare a riconoscere la sezione corretta.', 'design_comuni_italia'); ?></p>
                    <div class="dci-admin-guide__legend" aria-label="<?php esc_attr_e('Legenda delle sezioni', 'design_comuni_italia'); ?>">
                        <div class="dci-admin-guide__legend-item dci-admin-guide__legend-item--standard"><strong><?php esc_html_e('Sezione selezionabile', 'design_comuni_italia'); ?></strong><span><?php esc_html_e('Ha il pulsante di scelta: selezionala se corrisponde all’obbligo.', 'design_comuni_italia'); ?></span></div>
                        <div class="dci-admin-guide__legend-item dci-admin-guide__legend-item--custom"><strong><?php esc_html_e('Pubblicazione dedicata', 'design_comuni_italia'); ?></strong><span><?php esc_html_e('La voce viola è informativa e non accetta Elementi Trasparenza. Segui la destinazione indicata nella voce.', 'design_comuni_italia'); ?></span></div>
                        <div class="dci-admin-guide__legend-item dci-admin-guide__legend-item--link"><strong><?php esc_html_e('Categoria con link', 'design_comuni_italia'); ?></strong><span><?php esc_html_e('La voce ambra è gestita da un collegamento configurato e non è selezionabile.', 'design_comuni_italia'); ?></span></div>
                        <?php if ($is_internal_portal) : ?>
                            <div class="dci-admin-guide__legend-item dci-admin-guide__legend-item--political"><strong><?php esc_html_e('Pubblicazione automatica', 'design_comuni_italia'); ?></strong><span><?php esc_html_e('La voce azzurra riceve i dati dal relativo ufficio politico e non accetta Elementi Trasparenza.', 'design_comuni_italia'); ?></span></div>
                        <?php endif; ?>
                    </div>
                    <div class="notice notice-info inline">
                        <p><strong><?php esc_html_e('Dopo la pubblicazione:', 'design_comuni_italia'); ?></strong> <?php esc_html_e('la sezione viene bloccata per evitare spostamenti accidentali. Per correggerla usa “Modifica sezione”, leggi l’avviso, attendi lo sblocco e scegli la nuova destinazione.', 'design_comuni_italia'); ?></p>
                    </div>
                    <p><strong><?php esc_html_e('Se non trovi la voce corretta, non scegliere una categoria simile per tentativi:', 'design_comuni_italia'); ?></strong> <?php esc_html_e('salva la bozza e chiedi al referente della Trasparenza.', 'design_comuni_italia'); ?></p>
                </section>

                <?php if ($is_internal_portal) : ?>
                    <section id="organi-politici">
                        <span class="dci-admin-guide__number" aria-hidden="true">5</span>
                        <h2><?php esc_html_e('Eccezione: Sindaco, Giunta e Consiglio Comunale', 'design_comuni_italia'); ?></h2>
                        <div class="dci-admin-guide__rule dci-admin-guide__rule--political">
                            <span class="dashicons dashicons-groups" aria-hidden="true"></span>
                            <p><strong><?php esc_html_e('Solo per i portali interni:', 'design_comuni_italia'); ?></strong> <?php esc_html_e('le sezioni “Il Sindaco”, “Giunta Comunale” e “Consiglio Comunale” sono alimentate automaticamente dalle rispettive unità organizzative. Non creare un Elemento Trasparenza in queste tre voci.', 'design_comuni_italia'); ?></p>
                        </div>
                        <ol class="dci-admin-guide__steps">
                            <li><strong><?php esc_html_e('Controlla la Persona pubblica.', 'design_comuni_italia'); ?></strong><span><?php esc_html_e('La scheda della persona deve essere pubblicata e aggiornata, compresi incarico o ruolo politico e relativi documenti.', 'design_comuni_italia'); ?></span></li>
                            <li><strong><?php esc_html_e('Apri la relativa Unità organizzativa.', 'design_comuni_italia'); ?></strong><span><?php esc_html_e('Usa l’unità “Sindaco” o “Ufficio del Sindaco”, “Giunta Comunale” oppure “Consiglio Comunale”.', 'design_comuni_italia'); ?></span></li>
                            <li><strong><?php esc_html_e('Collega le persone.', 'design_comuni_italia'); ?></strong><span><?php esc_html_e('Nel riquadro “Struttura” indica il Responsabile e nel riquadro “Persone” compila “Persone che compongono la struttura”.', 'design_comuni_italia'); ?></span></li>
                            <li><strong><?php esc_html_e('Aggiorna e verifica.', 'design_comuni_italia'); ?></strong><span><?php esc_html_e('Salva l’unità organizzativa, poi apri la corrispondente sezione pubblica della Trasparenza e controlla componenti, ruoli e collegamenti.', 'design_comuni_italia'); ?></span></li>
                        </ol>
                        <p><?php esc_html_e('Il sistema considera anche il collegamento inverso dalla Persona pubblica all’organizzazione e gli incarichi politici associati. Per un risultato affidabile mantieni coerenti sia la scheda della persona sia quella dell’unità organizzativa.', 'design_comuni_italia'); ?></p>
                        <p><a class="button" href="<?php echo esc_url($offices_url); ?>"><span class="dashicons dashicons-building" aria-hidden="true"></span><?php esc_html_e('Vai alle Unità organizzative', 'design_comuni_italia'); ?></a></p>
                    </section>
                <?php endif; ?>

                <section id="documenti">
                    <span class="dci-admin-guide__number" aria-hidden="true"><?php echo esc_html((string) $documents_number); ?></span>
                    <h2><?php esc_html_e('Documenti, collegamenti e accessibilità', 'design_comuni_italia'); ?></h2>
                    <h3><?php esc_html_e('Scegli il campo adatto', 'design_comuni_italia'); ?></h3>
                    <ul>
                        <li><strong><?php esc_html_e('Collegamento principale:', 'design_comuni_italia'); ?></strong> <?php esc_html_e('usa un indirizzo completo, interno o esterno, quando la destinazione principale è una pagina web.', 'design_comuni_italia'); ?></li>
                        <li><strong><?php esc_html_e('Collegamenti aggiuntivi:', 'design_comuni_italia'); ?></strong> <?php esc_html_e('per ogni voce inserisci l’indirizzo completo e un testo che descriva chiaramente la destinazione; evita testi generici come “clicca qui”.', 'design_comuni_italia'); ?></li>
                        <li><strong><?php esc_html_e('Carica più file:', 'design_comuni_italia'); ?></strong> <?php esc_html_e('usa questo campo per allegare uno o più documenti scaricabili e stampabili.', 'design_comuni_italia'); ?></li>
                    </ul>
                    <h3><?php esc_html_e('Regole per file accessibili e riconoscibili', 'design_comuni_italia'); ?></h3>
                    <ul class="dci-admin-guide__checklist">
                        <li><?php esc_html_e('Pubblica il documento nativo digitale con testo selezionabile; non usare PDF composti, anche solo in parte, da scansioni di documenti cartacei.', 'design_comuni_italia'); ?></li>
                        <li><?php esc_html_e('Usa titoli, elenchi, tabelle e ordine di lettura corretti; per le immagini informative inserisci un’alternativa testuale nel documento.', 'design_comuni_italia'); ?></li>
                        <li><?php esc_html_e('Assegna un nome descrittivo, senza spazi superflui, e indica l’anno di riferimento e la data di pubblicazione o ultimo aggiornamento quando richiesti.', 'design_comuni_italia'); ?></li>
                        <li><?php esc_html_e('Apri il file prima del caricamento e controlla che sia completo, leggibile, non protetto da password e privo di pagine vuote.', 'design_comuni_italia'); ?></li>
                    </ul>
                    <div class="dci-admin-guide__example">
                        <h3><?php esc_html_e('Esempio di nome file', 'design_comuni_italia'); ?></h3>
                        <p><strong><?php esc_html_e('Chiaro:', 'design_comuni_italia'); ?></strong> piano-prevenzione-corruzione-2026.pdf</p>
                        <p><strong><?php esc_html_e('Da evitare:', 'design_comuni_italia'); ?></strong> scansione001_def2.pdf</p>
                    </div>
                    <h3><?php esc_html_e('Opzioni di apertura', 'design_comuni_italia'); ?></h3>
                    <p><strong><?php esc_html_e('“Apri in una nuova finestra”', 'design_comuni_italia'); ?></strong> <?php esc_html_e('va usato con moderazione, soprattutto per file o siti esterni. “Apri link in modo diretto” salta la pagina di dettaglio: attivalo soltanto quando l’elemento deve portare subito a una singola risorsa e non contiene spiegazioni o più allegati utili.', 'design_comuni_italia'); ?></p>
                </section>

                <section id="verifica">
                    <span class="dci-admin-guide__number" aria-hidden="true"><?php echo esc_html((string) $verification_number); ?></span>
                    <h2><?php esc_html_e('Pubblicare, verificare e mantenere aggiornato', 'design_comuni_italia'); ?></h2>
                    <p><?php esc_html_e('Prima di premere “Pubblica” rileggi il contenuto come se fossi un cittadino che non conosce l’ufficio o la pratica. Se mancano conferme o documenti, salva una bozza.', 'design_comuni_italia'); ?></p>
                    <h3><?php esc_html_e('Controllo finale', 'design_comuni_italia'); ?></h3>
                    <ul class="dci-admin-guide__checklist">
                        <li><?php esc_html_e('Titolo chiaro, data o periodo corretti e nessun duplicato.', 'design_comuni_italia'); ?></li>
                        <li><?php esc_html_e('Sottosezione più specifica selezionata e riepilogo verde coerente.', 'design_comuni_italia'); ?></li>
                        <li><?php esc_html_e('Descrizioni comprensibili, senza informazioni interne o istruzioni rivolte ai soli uffici.', 'design_comuni_italia'); ?></li>
                        <li><?php esc_html_e('Allegati definitivi, accessibili e privi di dati personali non necessari.', 'design_comuni_italia'); ?></li>
                        <li><?php esc_html_e('Collegamenti funzionanti, testi descrittivi e opzioni di apertura appropriate.', 'design_comuni_italia'); ?></li>
                        <li><?php esc_html_e('Stato “Pubblicato” soltanto se il contenuto può essere immediatamente consultato dai cittadini.', 'design_comuni_italia'); ?></li>
                    </ul>
                    <div class="dci-admin-guide__workflow" aria-label="<?php esc_attr_e('Flusso di verifica dopo la pubblicazione', 'design_comuni_italia'); ?>">
                        <span><?php esc_html_e('Pubblica', 'design_comuni_italia'); ?></span><span aria-hidden="true">→</span><span><?php esc_html_e('Apri “Visualizza”', 'design_comuni_italia'); ?></span><span aria-hidden="true">→</span><span><?php esc_html_e('Prova file e link', 'design_comuni_italia'); ?></span><span aria-hidden="true">→</span><span><?php esc_html_e('Controlla la sezione', 'design_comuni_italia'); ?></span>
                    </div>
                    <p><?php esc_html_e('Dopo la pubblicazione apri sempre la pagina pubblica e verifica titolo, descrizione, categoria, allegati e collegamenti. Programma inoltre i controlli periodici richiesti dall’ente: aggiorna il contenuto esistente, sostituisci i file superati e rimuovi o archivia soltanto secondo le regole di conservazione applicabili.', 'design_comuni_italia'); ?></p>
                </section>

                <section id="assistenza">
                    <span class="dci-admin-guide__number" aria-hidden="true"><?php echo esc_html((string) $assistance_number); ?></span>
                    <h2><?php esc_html_e('Dubbi, errori e assistenza', 'design_comuni_italia'); ?></h2>
                    <p><?php esc_html_e('Interrompi la pubblicazione e salva una bozza quando non conosci la sezione corretta, il documento contiene dati personali dubbi, la voce risulta non selezionabile o non disponi dei permessi necessari.', 'design_comuni_italia'); ?></p>
                    <p><?php esc_html_e('Comunica al referente interno per la Trasparenza o all’assistenza: titolo del contenuto, sezione prevista, collegamento della bozza e una descrizione precisa del problema. Non creare categorie o duplicati per aggirare un blocco.', 'design_comuni_italia'); ?></p>
                    <div class="dci-admin-guide__quick-actions">
                        <a class="button" href="<?php echo esc_url($items_url); ?>"><?php esc_html_e('Torna agli elementi', 'design_comuni_italia'); ?></a>
                        <a class="button" href="<?php echo esc_url($public_page_url); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Controlla il sito pubblico', 'design_comuni_italia'); ?></a>
                        <a class="button" href="https://guida-servizi.anticorruzione.it/it/help/trasparenza/amministrazione-trasparente/" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Consulta la guida ANAC', 'design_comuni_italia'); ?></a>
                    </div>
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
