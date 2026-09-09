<?php 

function dci_trasparenza_activation() {
    set_time_limit(400);  // Aumenta il timeout

    // Inserisce i termini di tassonomia
    $stats = insertTaxonomyTrasparenzaTerms();

    // Assegna i link standard solo durante il caricamento completo dei dati.
    dci_trasparenza_populate_standard_links();

    // Imposta un'opzione per indicare che il setup è avvenuto
    update_option("dci_has_installed", true);

    // Disabilita i commenti di default per i nuovi post
    if ('' != get_option('default_comment_status')) {
        update_option('default_comment_status', '');
    }

    return $stats;
}
add_action('after_switch_theme', 'dci_trasparenza_activation');
//dci_reload_trasparenza_option_page('themes.php', 'dci_trasparenza_activation');

/**
 * Funzione che popola i link standard per le sottovoci della Amministrazione Trasparente.
 * Attualemte i link standard sono:
 * - Disposizioni generali > Atti generali > Normativa: https://www.normattiva.it/
 * - Consulenti e collaboratori > Banca dati incarichi di consulenza PerlaPA: https://consulentipubblici.dfp.gov.it/
 * 
 * in futuro potranno essere aggiunti altri link standard.
 * Questa funzione viene chiamata durante l'attivazione del tema e può essere richiamata manualmente tramite la pagina di amministrazione dedicata alla ricarica dei dati della Trasparenza.
 * I link vengo aperti in una nuova finestra del browser per evitare di perdere la sessione di amministrazione del sito.
 */
function dci_trasparenza_populate_standard_links() {
    $links = [
        [
            'path' => ['Disposizioni generali', 'Atti generali', 'Normativa'],
            'url'  => 'https://www.normattiva.it/',
            'open_new_window' => true,
        ],
        [
            'path' => ['Consulenti e collaboratori', 'Banca dati incarichidi consulenza PerlaPA'],
            'url'  => 'https://consulentipubblici.dfp.gov.it/',
            'open_new_window' => true,
        ],
    ];

    foreach ($links as $link) {
        $parent = 0;

        foreach ($link['path'] as $name) {
            $terms = get_terms([
                'taxonomy'   => 'tipi_cat_amm_trasp',
                'hide_empty' => false,
                'parent'     => $parent,
                'name'       => $name,
                'number'     => 2,
            ]);

            if (is_wp_error($terms) || count($terms) !== 1 || $terms[0]->name !== $name) {
                continue 2;
            }

            $parent = (int) $terms[0]->term_id;
        }

        if ((string) get_term_meta($parent, 'term_url', true) !== $link['url']) {
            update_term_meta($parent, 'term_url', $link['url']);
        }

        if (array_key_exists('open_new_window', $link)) {
            $open_new_window = $link['open_new_window'] ? '1' : '0';
            if ((string) get_term_meta($parent, 'open_new_window', true) !== $open_new_window) {
                update_term_meta($parent, 'open_new_window', $open_new_window);
            }
        }
    }
}


/**
 * Funzione che consente di ricaricare tutti i dati della trasparenza, comprese le tassonomie e le descrizioni dei termini chiave.
 * Questa funzione è accessibile solo all'amministratore con ID 1 e può essere richiamata tramite la pagina di amministrazione dedicata alla ricarica dei dati della Trasparenza.
 * La voce che consente di ricaricare i dati della Trasparenza è situtata nel menù laterale di amministrazione sotto la voce "Aspetto" > "Ricarica Trasparenza".
 */
function dci_reload_trasparenza_option_page() {

    // Sicurezza: blocca accesso diretto via URL
    if (get_current_user_id() != 1) {
        wp_die('Non hai i permessi per accedere a questa pagina.');
    }

    $action = isset($_GET['action']) ? sanitize_key(wp_unslash($_GET['action'])) : '';

    if (in_array($action, ['reload', 'reload_descriptions', 'reload_ordering'], true)) {
        check_admin_referer('dci_trasparenza_' . $action);

        if ($action === 'reload') {
            $stats = dci_trasparenza_activation();
            $inserted = isset($stats['inserted']) ? (int) $stats['inserted'] : 0;
            $updated = isset($stats['updated']) ? (int) $stats['updated'] : 0;
            $descriptions = isset($stats['descriptions_updated']) ? (int) $stats['descriptions_updated'] : 0;
            $notice_class = empty($stats['descriptions_error']) ? 'notice-success' : 'notice-warning';
            $notice_message = empty($stats['descriptions_error']) ? 'Dati ricaricati con successo.' : 'Ricarica completata senza aggiornare le descrizioni.';
            echo '<div class="notice ' . esc_attr($notice_class) . ' is-dismissible"><p>' . esc_html($notice_message) . ' Voci inserite: <strong>' . esc_html($inserted) . '</strong>, voci aggiornate: <strong>' . esc_html($updated) . '</strong>, descrizioni aggiornate: <strong>' . esc_html($descriptions) . '</strong>.</p></div>';
        } elseif ($action === 'reload_descriptions') {
            $stats = insertTaxonomyTrasparenzaTerms(['descriptions']);
            $descriptions = isset($stats['descriptions_updated']) ? (int) $stats['descriptions_updated'] : 0;
            if (empty($stats['descriptions_error'])) {
                echo '<div class="notice notice-success is-dismissible"><p>Descrizioni ricaricate. Termini aggiornati: <strong>' . esc_html($descriptions) . '</strong>. Struttura, slug, visibilità e ordinamento non sono stati modificati.</p></div>';
            }
        } else {
            $stats = dci_reload_trasparenza_ordering();
            $ordering_updated = isset($stats['ordering_updated']) ? (int) $stats['ordering_updated'] : 0;
            $missing = isset($stats['missing']) ? (int) $stats['missing'] : 0;
            echo '<div class="notice notice-success is-dismissible"><p>Ordinamento ricaricato. Termini aggiornati: <strong>' . esc_html($ordering_updated) . '</strong>, termini della struttura predefinita non presenti e ignorati: <strong>' . esc_html($missing) . '</strong>. Nessun termine è stato creato o spostato.</p></div>';
        }
    }

    if (!empty($stats['descriptions_error'])) {
        echo '<div class="notice notice-warning"><p>' . esc_html($stats['descriptions_error']) . ' Le descrizioni esistenti sono state conservate.</p></div>';
    }

    $page_url = admin_url('themes.php?page=reload-trasparenza-theme-options');
    $reload_url = wp_nonce_url(add_query_arg('action', 'reload', $page_url), 'dci_trasparenza_reload');
    $descriptions_url = wp_nonce_url(add_query_arg('action', 'reload_descriptions', $page_url), 'dci_trasparenza_reload_descriptions');
    $ordering_url = wp_nonce_url(add_query_arg('action', 'reload_ordering', $page_url), 'dci_trasparenza_reload_ordering');

    echo "<div class='wrap'>";
    echo "<h1>Ricarica i dati della Trasparenza</h1>";
    echo '<p>Questa operazione reinserisce le tassonomie e opzioni di default relative alla sezione "Amministrazione Trasparente".</p>';
    echo '<div style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;">';
    echo '<a href="' . esc_url($reload_url) . '" class="button button-primary dci-reload-trasparenza-btn" data-confirm="Questa operazione ricarica l’intera struttura della Trasparenza. Continuare?">Ricarica Trasparenza</a>';
    echo '<a href="' . esc_url($descriptions_url) . '" class="button dci-reload-trasparenza-btn" data-confirm="Aggiornare le descrizioni predefinite dei termini già esistenti?">Ricarica descrizioni</a>';
    echo '<a href="' . esc_url($ordering_url) . '" class="button dci-reload-trasparenza-btn" data-confirm="Riallineare l’ordinamento dei termini già esistenti?">Ricarica ordinamento</a>';
    echo '<span id="dci-reload-trasparenza-loader" style="display:none; margin-left:12px; align-items:center;"><span class="spinner is-active" style="float:none; margin:0 8px 0 0;"></span>Ricaricamento in corso...</span>';
    echo '</div>';
    echo '<p class="description" style="margin-top:12px;">Le azioni “Ricarica descrizioni” e “Ricarica ordinamento” operano solo sui termini già presenti e non modificano struttura, slug o visibilità.</p>';
    echo "<script>
    document.addEventListener('DOMContentLoaded', function () {
        var reloadButtons = document.querySelectorAll('.dci-reload-trasparenza-btn');
        var loader = document.getElementById('dci-reload-trasparenza-loader');
        if (!reloadButtons.length || !loader) {
            return;
        }
        reloadButtons.forEach(function (button) {
            button.addEventListener('click', function (event) {
                var message = button.getAttribute('data-confirm');
                if (message && !window.confirm(message)) {
                    event.preventDefault();
                    return;
                }
                loader.style.display = 'inline-flex';
                reloadButtons.forEach(function (candidate) {
                    candidate.classList.add('disabled');
                    candidate.setAttribute('aria-disabled', 'true');
                    candidate.style.pointerEvents = 'none';
                });
            });
        });
    });
    </script>";
    echo "</div>";
}

function dci_add_trasparenza_theme_page() {

    // Mostra la pagina SOLO se utente ID = 1
    if (get_current_user_id() != 1) {
        return;
    }

    add_theme_page(
        'Ricarica Trasparenza',
        'Ricarica Trasparenza',
        'edit_theme_options',
        'reload-trasparenza-theme-options',
        'dci_reload_trasparenza_option_page'
    );
}

add_action('admin_menu', 'dci_add_trasparenza_theme_page');


/**
 * Funzione contente la struttura della tassonomia "tipi_cat_amm_trasp" e le relative descrizioni.
 * Questa struttura è utilizzata per popolare la tassonomia della sezione "Amministrazione Trasparente" e per fornire descrizioni dettagliate dei termini chiave.
 * La struttura è organizzata in un array multidimensionale, dove le chiavi rappresentano le categorie principali e i valori possono essere stringhe (per termini senza sottovoci) o array (per termini con sottovoci).
 * La voci sono frutto di un rigoroso lavoro di analisi dei contenuti normativi e da quanto segnalato da un controllo incrociato con un asseveratore esterno.
 */
if (!function_exists("dci_tipi_cat_amm_trasp_array")) {
    function dci_tipi_cat_amm_trasp_array() {
        return [
            'Disposizioni generali' => [
                "Piano triennale per la prevenzione della corruzione e della trasparenza (PTPCT)",
                'Atti generali' => [  
                    'Normativa', // Inserire il link alla normativa di riferimento
                    'Riferimenti normativi su organizzazione e attività',
                    'Atti amministrativi generali',
                    'Documenti di programmazione strategico gestionale',
                    'Statuti e leggi regionali',
                    'Codice disciplinare e codice di condotta'
                ],
                "Oneri informativi per cittadini e imprese"=>[
                    'Scadenziario nuovi obblighi amministrativi' // Nuova sotto-voce richiesta da ANAC
                ]
            ],
            'Organizzazione' => [
                'Titolari di incarichi politici di amministrazione di direzione o di governo' =>[ // Pagina custom e sottovoci
                    "Il Sindaco",
                    "Giunta Comunale",
                    "Consiglio Comunale",
                    'Relazioni di inizio mandato',
                    'Relazioni di fine mandato'
                ],
                'Amministratori Cessati', // Nuova sottovoce
                "Sanzioni per mancata comunicazione dei dati",
                "Rendiconti gruppi consiliari regionali/provinciali" =>[
                    'Rendiconti gruppi consiliari regionali/provinciali',
                    'Atti degli organi di controllo'
                ],

                "Articolazione degli uffici" =>[ // Nuove sotto voci richieste dal ANAC
                    "Articolazione uffici",  // Pagina Custom nel sito
                    "Organigramma"
                ],
                "Telefono e posta elettronica" // Pagina Custom
            ],
            'Consulenti e collaboratori' => [
                'Banca dati incarichidi consulenza PerlaPA', // Link a PerlaPA
                'Titolari di incarichi di collaborazione o consulenza'
            ],
            'Personale' => [
                // Nuove sotto-voci
                'Titolari di incarichi dirigenziali amministrativi di vertice', 
                'Titolari di Incarichi dirigenziali (dirigenti non generali)' =>[
                    'Incarichi dirigenziali a qualsiasi titolo conferiti',
                    'Elenco posizioni dirigenziali discrezionali',
                    'Posti di funzioni disponibili',
                    'Ruoli dirigenti'
                ],
                'Dirigenti cessati',
                'Sanzioni per mancata comunicazione dei dati',

                'Posizioni organizzative',

                // Nuove sotto-sotto-voci
                'Dotazione organica' =>  [
                    'Costo annuale del personale',
                    'Costo personale tempo indeterminato'
                ],
                'Personale non a tempo indeterminato' =>[
                    'Costo del personale non a tempo indeterminato',
                    'Personale non a tempo indeterminato'
                ],
                'Tassi di assenza',
                "Incarichi conferiti e autorizzati ai dipendenti",
                "Contrattazione collettiva",

                "Contrattazione integrativa" =>[ // Nuove sotto-sotto-voci
                    'Contratti integrativi',
                    'Costi contratti integrativi'
                ],
                "OIV"
            ],
            'Bandi di concorso' => [
                'Concorsi' 
            ],
            'Performance'=> [
                'Sistema di misurazione e valutazione della performance',
                "Piano della Performance",
                "Relazione sulla Performance",
                "Ammontare complessivo dei premi",
                "Benessere organizzativo",
                "Dati relativi ai premi"
            ],
            'Enti controllati' =>[
                "Enti pubblici vigilati",
                "Società partecipate" =>[
                    'Dati società partecipate',
                    'Provvedimenti'
                ],
                "Enti di diritto privato controllati",
                "Rappresentazione grafica"
            ],
            "Attività e procedimenti"=>[ 
                "Tipologie di procedimento",
                "Monitoraggio tempi procedimentali",
                "Dichiarazioni sostitutive e acquisizione d'ufficio dei dati"
            ],
            "Provvedimenti" =>[
                "Provvedimenti organi indirizzo-politico",
                "Provvedimenti dirigenti amministrativi" // Nuovo nome
            ],
            "Bandi di Gara e contratti"=>[
                "Link alla Banca Dati Nazionale dei Contratti Pubblici BDNCP",
                // Procedimenti a partire dal 01/01/2024
                "Atti e documenti di carattere generale riferiti a tutte le procedure" => [
                    "Automatizzazione delle procedure",
                    "Acquisizione interesse realizzazione opere incompiute",
                    "Mancata redazione programmazione",
                    "Documenti sul sistema di qualificazione",
                    "Gravi illeciti professionali",
                    "Progetti di investimento pubblico"
                ],

                //    "Contratti Pubblici", // Sezione non più necessaria per la trasparenza
                // Sezioni che sostituiscono "Contratti Pubblici" per la trasparenza
                "Pubblicazione"=> [
                    'Dibattito pubblico',
                    'Documenti di gara'
                ],
                "Affidamento" =>[
                    'Composizione della commissione giudicatrice',
                    'Pari opportunità e inclusione lavorativa',
                    'Affidamenti Servizi pubblici locali (SPL)'
                ],
                "Esecutiva" =>[
                    'Collegio consultivo tecnico',
                    'Pari opportunità e inclusione lavorativa'
                ],
                "Sponsorizzazioni" =>[
                    'Contratti di sponsorizzazione'
                ],
                "Procedure di somma urgenza e di protezione civile",
                "Finanza di progetto",
                "Atti, documenti e link a BDNCP",

                 // Procedimenti fino al 31 / 12/2023
                "Procedimenti fino al 31/12/2023" => [
                    "Provvedimenti di esclusione e di ammissione",
                    "Informazioni sulle singole procedure in formato tabellare",
                    "Atti delle amministrazioni aggiudicatrici e degli enti aggiudicatori distintamente per ogni procedura"
                ]
            ],
            "Sovvenzioni , contributi sussidi, vantaggi economici"=>[
                "Criteri e modalità",
                "Atti di concessione",
                // "Elenchi" // Voce eliminata
            ],
            "Bilanci"=>[
                "Bilancio preventivo e consuntivo" =>[
                    'Bilancio consuntivo',
                    'Bilancio preventivo'
                ],
                "Piano degli indicatori e risultati attesi di bilancio"
            ],
            "Beni immobili e gestione patrimonio"=>[
                "Patrimonio immobiliare",
                "Canoni di locazione o affitto",
                "Beni confiscati alla criminalità organizzata e trasferiti agli enti locali"
            ],
            "Controlli e rilievi sull'amministrazione"=>[
                "Organismi indipendenti di valutazione, nuclei di valutazione o altri organismi con funzioni analoghe",
                "Organi di revisione amministrativa e contabile",
                "Corte dei conti",
                // "Altri organismi",  // Voce rimossa
                // "Stato dei rilievi" // Voce rimossa
            ],
            "Servizi Erogati"=>[
                "Carta dei servizi e standard di qualità",
                "Class action", // Nuova sotto-voce
                "Costi contabilizzati",
                "Liste di attesa", // sotto-voce spostata
                "Gestione dei rifiuti", // Nuova sotto-voce
                // "Tempi medi di erogazione dei servizi"  
                "Servizi in rete"
            ],
            "Pagamenti dell'amministrazione" => [
                    "Dati sui pagamenti",
                    "Dati sui pagamenti del servizio sanitario nazionale",
                    "Indicatore di tempestività dei pagamenti"=>[
                        'Ammontare complessivo dei debiti' // Nuova sotto-sotto-voce
                    ],
                    "IBAN e pagamenti informatici"
            ],
            "Opere pubbliche" => [
                "Nuclei di valutazione e verifica degli investimenti pubblici",
                "Atti di programmazione delle opere pubbliche",
                "Tempi costi e indicatori di realizzazione delle opere pubbliche"
            ],
            "Pianificazione e governo del territorio" => [
                "Pianificazione e governo del territorio"
            ],
            "Informazioni ambientali" => [
                "Informazioni ambientali",
                
                // nuove sotto-voci
                "Stato dell’ambiente",
                "Fattori Inquinanti",
                "Misure incidenti sull’ambiente e relative analisi d’impatto",
                "Misure protezione sull’ambiente e relative analisi d’impatto",
                "Relazioni sull’attuazione della legislazione",
                "Stato della salute e della sicurezza umana",
                "Relazione sullo stato dell’ambiente del ministero dell’ambiente e della tutela del territorio"
            ],
            "Strutture sanitarie private accreditate" => [
                "Strutture sanitarie private accreditate"
            ],
            "Interventi straordinari e di emergenza" => [
                "Interventi straordinari e di emergenza"
            ],
            "Altri contenuti" => [
                "Prevenzione della corruzione"=>[
                    // Nuove sotto-sotto-voci
                    "Piano triennale per la prevenzione della corruzione e della trasparenza",
                    "Responsabile della prevenzione della corruzione e della trasparenza",
                    "Regolamenti per la prevenzione e la repressione della corruzione e dell'illegalità",
                    "Relazione del responsabile della prevenzione della corruzione e della trasparenza",
                    "Provvedimenti adottati dall'A.N.AC. ed atti di adeguamento a tali provvedimenti",
                    "Atti di accertamento delle violazioni",
                    "Segnalazioni di illecito - whistleblower", // Aggiungere il link alla piattaforma
                    'Accesso Civico "semplice" concernente dati, documenti e informazioni soggetti a pubblicazione obbligatoria'
                ],
                "Accesso civico"=>[
                    'Accesso civico “generalizzato” concernente dati e documenti ulteriori',
                    'Registro degli accessi', 
                    'Catalogo dei dati , metadati e delle banche dei dati'
                ],
                "Accessibilità e Catalogo di dati, metadati e banche dati"=>[
                    'Regolamenti',
                    'Obiettivi di accessibilità',
                    'Azioni di sensibilizzazione rapporti con la società civile',
                ],
                "Azioni di sensibilizzazione e rapporti con la società civile" => [
                    "Autovetture di servizio"
                ],
                "Dati ulteriori" =>[ // Nuova sotto-voci
                    'Dati ulteriori',
                    "Project Financing",
                    "Piano triennale delle azioni positive",
                    "Provvedimenti CDS"
                ]
            ]
        ];
    }
}

/**
 * Funzione che restituisce un array dei tipi di procedura contraente.
 * Serve per popolare la tassonomia "tipi_procedura_contraente" nella sezione "Amministrazione Trasparente". 
 * In particolatre per la sezione "Bandi di Gara e contratti".
 */

if (!function_exists("dci_tipi_procedura_contraente_array")) {
    function dci_tipi_procedura_contraente_array() {
        return [
            "01 - Procedura aperta",
            "02 - Procedura ristretta",
            "03 - Procedura negoziata previa pubblicazione",
            "04 - Procedura negoziata senza previa pubblicazione",
            "05 - Dialogo competitivo",
            "06 - Procedura negoziata senza previa i nozione cl gara (settori speciali)",
            "07 - Sistema dinamico dl acquisizione",
            "08 - Affloamento in economia - cottimo fiduciario",
            "14 - Procedura selettiva ex art 238 c7, d.lgs.",
            "17 - Affidamento diretto ex art. 5 cella legge",
            "21 - Procedura ristretta derivante da avvisi con cui si indice la gara",
            "22 - Procedura negoziata previa indizione dl gara (settori speciali}",
            "23 - Affidamento diretto",
            "24 - Affidamento diretto a societa' in house",
            "25 - Affidamento diretto a societa raggruppate/consorziate o controllate nelle concessioni e nei partenariati",
            "26 - Affldamento diretto in adesione ad accordo quadro/convenzione",
            "27 - Confronto competitivo in adesione ad accordo quadro/convenzione",
            "28 - Procedura al sensi dei regolamenti degli organi costituzionali",
            "29 - Procedura ristretta semplificata",
            "30 - Procedura derivante oa legge regionale",
            "31 - Affidamento diretto per variante superiore al dell'importo contrattuale",
            "32 - Affidamento riservato",
            "33 - Procedura negoziata per affidamenti sotto soglia",
            "34 - Procedura art. 16 comma 2. opr 280/2001 per opere urbanizzazione a scomputo primarie sotto soglia comunitaria",
            "35 - Parternariato per l'innovazione",
            "36 - Affidamento diretto per lavori. servizi o forniture supplementari",
            "37 - Procedura competitiva con negoziazione",
            "38 - Procedura disciplinata da regolamento interno per settori speciali",
            "39 - Diretto per modifiche contrattuali o varianti per le quali é necessaria una nuova procedura dl affidamento",
        ];
    }
}

/**
 * Funzione che restituisce un array dei tipi di stato bando.
 * Serve per popolare la tassonomia "tipi_stato_bando" nella sezione "Amministrazione Trasparente".
 * In particolare per la sezione "Bandi di Gara e contratti".
 */

if (!function_exists("dci_tipi_stato_bando_array")) {
    function dci_tipi_stato_bando_array() {
        return [
            "Attivo",
            "Scaduto",
            "Archiviato",
        ];
    }
}


/**
 * Carica e valida il catalogo locale delle descrizioni solo quando richiesto.
 * La cache dura una sola richiesta: nessuna lettura nella normale navigazione
 * e nessuna cache persistente da invalidare quando si modifica il JSON.
 *
 * @return array|WP_Error Mappa nome termine => descrizione, oppure errore.
 */
function dci_get_trasparenza_descriptions() {
    static $descriptions = null;

    if (null !== $descriptions) {
        return $descriptions;
    }

    $path = __DIR__ . '/comuni_trasparenza_descrizioni.json';
    if (!is_file($path) || !is_readable($path)) {
        $descriptions = new WP_Error('trasparenza_descriptions_unreadable', 'Caricamento descrizioni saltato: comuni_trasparenza_descrizioni.json è assente o non leggibile.');
        return $descriptions;
    }

    // Gestisce anche un errore di lettura successivo al controllo dei permessi.
    $json = @file_get_contents($path);
    if (false === $json) {
        $descriptions = new WP_Error('trasparenza_descriptions_read_failed', 'Caricamento descrizioni saltato: impossibile leggere comuni_trasparenza_descrizioni.json.');
        return $descriptions;
    }

    $catalog = json_decode($json);
    if (JSON_ERROR_NONE !== json_last_error() || !($catalog instanceof stdClass)
        || !isset($catalog->descrizioni) || !($catalog->descrizioni instanceof stdClass)
        || empty((array) $catalog->descrizioni)) {
        $descriptions = new WP_Error('trasparenza_descriptions_invalid', 'Caricamento descrizioni saltato: comuni_trasparenza_descrizioni.json deve contenere un oggetto "descrizioni" non vuoto.');
        return $descriptions;
    }

    $values = (array) $catalog->descrizioni;
    foreach ($values as $name => $description) {
        if (!is_string($name) || '' === trim($name) || !is_string($description) || '' === trim($description)) {
            $descriptions = new WP_Error('trasparenza_descriptions_invalid_entry', 'Caricamento descrizioni saltato: tutti i nomi e le descrizioni nel JSON devono essere testi non vuoti.');
            return $descriptions;
        }
    }

    $descriptions = $values;
    return $descriptions;
}

/**
 *  Funzione che popola l'amministrazione trasparente andando a caricare l'albero delle tassonomie e le descrizioni dei termini chiave.
 */
function insertTaxonomyTrasparenzaTerms( $operations = ['structure', 'descriptions'], $dry_run = false ) {
    $operations = array_values(array_intersect(
        (array) $operations,
        ['structure', 'descriptions']
    ));

    $stats = [
        'inserted' => 0,
        'updated' => 0,
        'descriptions_updated' => 0,
    ];

    /**
     * Logica per inserire la struttura della tassonomia "tipi_cat_amm_trasp" e le tassonomie correlate.
    */
    if (in_array('structure', $operations, true)) {    

        // Categorie Trasparenza
        $tipi_cat_amm_trasp_array = dci_tipi_cat_amm_trasp_array();
        // recursionInsertTaxonomy( $tipi_cat_amm_trasp_array, 'tipi_cat_amm_trasp' );
        $ordine = 1;
        recursionInsertTaxonomy1( $tipi_cat_amm_trasp_array, 'tipi_cat_amm_trasp', 0, $ordine, $stats );

        // Tipi di procedura contraente
        $tipi_procedura_contraente_array = dci_tipi_procedura_contraente_array();
        recursionInsertTaxonomy( $tipi_procedura_contraente_array, 'tipi_procedura_contraente' );

        // Tipi di stato bando
        $tipi_stato_bando_array = dci_tipi_stato_bando_array();
        recursionInsertTaxonomy( $tipi_stato_bando_array, 'tipi_stato_bando' );
    }


    /**
     *  Logica per aggioranare le descrizioni delle categorie della Amministrazione Trasparente.
    */
    if (in_array('descriptions', $operations, true)) {
        $descrizioni = dci_get_trasparenza_descriptions();
        if (is_wp_error($descrizioni)) {
            $stats['descriptions_error'] = $descrizioni->get_error_message();
            return $stats;
        }

        foreach ( $descrizioni as $term_name => $new_desc ) {
            dci_update_term_description(
                $term_name,
                'tipi_cat_amm_trasp',
                $new_desc,
                $stats,
                $dry_run
            );
        }
    }

    return $stats;
}

/**
 * Aggiorna la descrizione di un termine se assente o diversa.
 *
 * @param string $term_name Nome del termine.
 * @param string $taxonomy  Tassonomia di appartenenza.
 * @param string $new_desc  Nuova descrizione (testo con \n\n per i paragrafi).
 */
function dci_update_term_description( $term_name, $taxonomy, $new_desc, &$stats = null, $dry_run = false ) {
    static $terms_by_taxonomy = [];

    if (!isset($terms_by_taxonomy[$taxonomy])) {
        $all_terms = get_terms([
            'taxonomy'   => $taxonomy,
            'hide_empty' => false,
            'update_term_meta_cache' => false,
        ]);
        $terms_by_taxonomy[$taxonomy] = [];

        if (!is_wp_error($all_terms)) {
            foreach ($all_terms as $available_term) {
                $terms_by_taxonomy[$taxonomy][$available_term->name][] = $available_term;
            }
        }
    }

    $terms = $terms_by_taxonomy[$taxonomy][$term_name] ?? [];

    if ( empty( $terms ) ) {
        return;
    }

    foreach ( $terms as $term ) {
        if ( empty( $term->description ) || $term->description !== $new_desc ) {
            $updated = $dry_run
                ? ['term_id' => (int) $term->term_id]
                : wp_update_term(
                    $term->term_id,
                    $taxonomy,
                    [ 'description' => $new_desc ]
                );

            if (!is_wp_error($updated)) {
                if (!$dry_run) {
                    // Mantiene coerente la cache anche con più chiamate nella richiesta.
                    $term->description = $new_desc;
                }
                if (is_array($stats)) {
                    $stats['descriptions_updated']++;
                }
            }
        }
    }
}

/**
 * Riallinea esclusivamente il meta di ordinamento dei termini già esistenti.
 * Non crea termini e non modifica nome, slug, parent o visibilità.
 *
 * @param bool $dry_run Se true calcola le modifiche senza salvarle.
 * @return array
 */
function dci_reload_trasparenza_ordering( $dry_run = false ) {
    $taxonomy = 'tipi_cat_amm_trasp';
    $all_terms = get_terms([
        'taxonomy'   => $taxonomy,
        'hide_empty' => false,
    ]);
    $stats = [
        'ordering_updated' => 0,
        'unchanged'        => 0,
        'missing'          => 0,
        'errors'           => 0,
    ];

    if (is_wp_error($all_terms)) {
        $stats['errors']++;
        return $stats;
    }

    $term_lookup = [];
    foreach ($all_terms as $term) {
        $normalized_name = mb_strtolower(
            dci_normalize_trasparenza_term_name($term->name),
            'UTF-8'
        );
        $term_lookup[(int) $term->parent][$normalized_name][] = $term;
    }

    $order = 1;
    dci_apply_trasparenza_ordering(
        dci_tipi_cat_amm_trasp_array(),
        0,
        true,
        $order,
        $term_lookup,
        $stats,
        $dry_run
    );

    return $stats;
}

/**
 * Applica ricorsivamente l'ordine previsto ai soli termini individuati
 * nello stesso ramo gerarchico.
 */
function dci_apply_trasparenza_ordering(
    $terms,
    $parent_id,
    $parent_exists,
    &$order,
    &$term_lookup,
    &$stats,
    $dry_run = false
) {
    foreach ((array) $terms as $key => $children) {
        if (is_int($key)) {
            $term_name = $children;
            $children = [];
        } else {
            $term_name = $key;
        }

        $term = null;
        if ($parent_exists) {
            $normalized_name = mb_strtolower(
                dci_normalize_trasparenza_term_name($term_name),
                'UTF-8'
            );

            if (!empty($term_lookup[(int) $parent_id][$normalized_name])) {
                $term = array_shift($term_lookup[(int) $parent_id][$normalized_name]);
            }
        }

        if ($term instanceof WP_Term) {
            $current_order = (string) get_term_meta($term->term_id, 'ordinamento', true);

            if ($current_order !== (string) $order) {
                $updated = $dry_run
                    ? true
                    : update_term_meta($term->term_id, 'ordinamento', $order);

                if ($updated !== false) {
                    $stats['ordering_updated']++;
                } else {
                    $stats['errors']++;
                }
            } else {
                $stats['unchanged']++;
            }
        } else {
            $stats['missing']++;
        }

        $order++;

        if (!empty($children) && is_array($children)) {
            dci_apply_trasparenza_ordering(
                $children,
                $term instanceof WP_Term ? (int) $term->term_id : 0,
                $term instanceof WP_Term,
                $order,
                $term_lookup,
                $stats,
                $dry_run
            );
        }
    }
}

/**
 * Recupera il primo termine che corrisponde esattamente al nome richiesto.
 *
 * @param string $term_name
 * @param string $taxonomy
 * @param int|null $parent
 * @return WP_Term|false
 */
function dci_find_trasparenza_term_by_name( $term_name, $taxonomy, $parent = null ) {
    $terms = get_terms(
        [
            'taxonomy'   => $taxonomy,
            'hide_empty' => false,
            'name'       => $term_name,
        ]
    );

    if ( is_wp_error( $terms ) || empty( $terms ) ) {
        return false;
    }

    foreach ( $terms as $term ) {
        if ( null === $parent || (int) $term->parent === (int) $parent ) {
            return $term;
        }
    }

    return false;
}

/**
 * Normalizza gli spazi per intercettare termini legacy creati con doppi spazi.
 *
 * @param string $term_name
 * @return string
 */
function dci_normalize_trasparenza_term_name( $term_name ) {
    return preg_replace( '/\s+/u', ' ', trim( (string) $term_name ) );
}

/**
 * Cerca un termine per nome normalizzato e parent.
 *
 * @param string $term_name
 * @param string $taxonomy
 * @param int    $parent
 * @return WP_Term|false
 */
function dci_find_trasparenza_term_by_normalized_name( $term_name, $taxonomy, $parent = 0 ) {
    $terms = get_terms(
        [
            'taxonomy'   => $taxonomy,
            'hide_empty' => false,
            'parent'     => (int) $parent,
        ]
    );

    if ( is_wp_error( $terms ) || empty( $terms ) ) {
        return false;
    }

    $normalized_term_name = dci_normalize_trasparenza_term_name( $term_name );

    foreach ( $terms as $term ) {
        if ( dci_normalize_trasparenza_term_name( $term->name ) === $normalized_term_name ) {
            return $term;
        }
    }

    return false;
}

/**
 * Costruisce uno slug univoco per la tassonomia Trasparenza tenendo conto del parent.
 *
 * @param string $term_name
 * @param string $taxonomy
 * @param int    $parent
 * @return string
 */
function dci_build_trasparenza_term_slug( $term_name, $taxonomy, $parent = 0 ) {
    $base_slug = sanitize_title( $term_name );

    if ( (int) $parent <= 0 ) {
        return $base_slug;
    }

    $ancestors = get_ancestors( $parent, $taxonomy, 'taxonomy' );
    $ancestors = array_reverse( $ancestors );
    $parts     = [];

    foreach ( $ancestors as $ancestor_id ) {
        $ancestor = get_term( $ancestor_id, $taxonomy );

        if ( $ancestor && ! is_wp_error( $ancestor ) && ! empty( $ancestor->slug ) ) {
            $parts[] = $ancestor->slug;
        }
    }

    $parent_term = get_term( $parent, $taxonomy );
    if ( $parent_term && ! is_wp_error( $parent_term ) && ! empty( $parent_term->slug ) ) {
        $parts[] = $parent_term->slug;
    }

    $parts[] = $base_slug;

    return implode( '-', array_filter( $parts ) );
}

/**
 * Recupera un termine esistente oppure lo crea se manca.
 * In fase di reload riallinea sempre parent e slug per evitare duplicati.
 *
 * @param string $term_name Nome del termine.
 * @param string $taxonomy  Tassonomia di appartenenza.
 * @param int    $parent    ID del termine genitore.
 * @return array{term_id:int,action:string}
 */
function dci_upsert_trasparenza_term( $term_name, $taxonomy, $parent = 0 ) {
    $slug    = dci_build_trasparenza_term_slug( $term_name, $taxonomy, $parent );
    $term_id = 0;
    $term    = dci_find_trasparenza_term_by_name( $term_name, $taxonomy, $parent );

    if ( ! $term ) {
        $term = dci_find_trasparenza_term_by_normalized_name( $term_name, $taxonomy, $parent );
    }

    if ( ! $term ) {
        $term = get_term_by( 'slug', $slug, $taxonomy );
    }

    if ( $term instanceof WP_Term ) {
        $term_id = (int) $term->term_id;
        $args    = [];
        $action  = 'unchanged';

        if ( $term->name !== $term_name ) {
            $args['name'] = $term_name;
        }

        if ( (int) $term->parent !== (int) $parent ) {
            $args['parent'] = (int) $parent;
        }

        if ( $term->slug !== $slug ) {
            $args['slug'] = $slug;
        }

        if ( ! empty( $args ) ) {
            $updated = wp_update_term( $term_id, $taxonomy, $args );

            if ( ! is_wp_error( $updated ) && isset( $updated['term_id'] ) ) {
                $term_id = (int) $updated['term_id'];
            }
            $action = 'updated';
        }

        return [
            'term_id' => $term_id,
            'action'  => $action,
        ];
    }

    $result = wp_insert_term(
        $term_name,
        $taxonomy,
        [
            'parent' => (int) $parent,
            'slug'   => $slug,
        ]
    );

    if ( is_wp_error( $result ) ) {
        if ( 'term_exists' === $result->get_error_code() ) {
            return [
                'term_id' => (int) $result->get_error_data(),
                'action'  => 'unchanged',
            ];
        }

        return [
            'term_id' => 0,
            'action'  => 'error',
        ];
    }

    return [
        'term_id' => isset( $result['term_id'] ) ? (int) $result['term_id'] : 0,
        'action'  => 'inserted',
    ];
}

/**
 * Inserisce / aggiorna i termini e IMPOSTA SEMPRE
 *   – meta 'ordinamento' progressivo
 *   – meta 'visualizza_elemento'  (0 = nascosto, 1 = visibile)
 *   – aggiorna sempre lo slug in base al nome del termine
 */

function recursionInsertTaxonomy1( $terms, $taxonomy, $parent = 0, &$ordine = 1, &$stats = null ) {

    foreach ( $terms as $key => $children ) {

        if ( is_int( $key ) ) {
            $term_name = $children;
            $children  = [];
        } else {
            $term_name = $key;
        }

        $upsert  = dci_upsert_trasparenza_term( $term_name, $taxonomy, $parent );
        $term_id = isset( $upsert['term_id'] ) ? (int) $upsert['term_id'] : 0;

        if ( ! $term_id ) {
            continue;
        }

        if ( is_array( $stats ) ) {
            if ( isset( $upsert['action'] ) && 'inserted' === $upsert['action'] ) {
                $stats['inserted']++;
            } elseif ( isset( $upsert['action'] ) && 'updated' === $upsert['action'] ) {
                $stats['updated']++;
            }
        }

        $current_order = (string) get_term_meta( $term_id, 'ordinamento', true );
        $current_visible = (string) get_term_meta( $term_id, 'visualizza_elemento', true );
        $new_visible = dci_should_hide_trasparenza_term( $term_name ) ? '0' : '1';

        if ( $current_order !== (string) $ordine ) {
            update_term_meta( $term_id, 'ordinamento', $ordine );
            if ( is_array( $stats ) && ( ! isset( $upsert['action'] ) || 'inserted' !== $upsert['action'] ) ) {
                $stats['updated']++;
            }
        }

        if ( $current_visible !== $new_visible ) {
            update_term_meta( $term_id, 'visualizza_elemento', $new_visible );
            if ( is_array( $stats ) && ( ! isset( $upsert['action'] ) || 'inserted' !== $upsert['action'] ) ) {
                $stats['updated']++;
            }
        }
        







        

        $ordine++;

        if ( ! empty( $children ) && is_array( $children ) ) {
            recursionInsertTaxonomy1( $children, $taxonomy, $term_id, $ordine, $stats );
        }
    }
}






/**
 * Termini che NON devono comparire nei radio‑button di CMB2.
 * Scrivi i nomi esattamente come compaiono nell’array principale.
 */
function dci_should_hide_trasparenza_term( $term_name ) {
    $term_name = mb_strtolower( trim( (string) $term_name ) );

    foreach ( dci_terms_to_hide() as $hide_term ) {
        if ( $term_name === mb_strtolower( trim( (string) $hide_term ) ) ) {
            return true;
        }
    }

    return false;
}

function dci_terms_to_hide() {
    return [
        // 'Incarichi conferiti e autorizzati ai dipendenti', // Non lo devi nascondere in quanto è una voce pricipale della tassonomia e serve per raggruppare i sotto‑termini.
        'Contratti Pubblici',
        // 'Pubblicazione',
        // 'Affidamento',
        // 'Esecutiva',
        // 'Sponsorizzazioni',
        // 'Atti di concessione',
    ];
}


?>








